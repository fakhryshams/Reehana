<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\AdminModel\Order\WebOrder;
use App\Services\FedExService;
use Exception;
use App\AdminModel\Order\AdminOrderStatusHistory;
use App\AdminModel\Order\AdminOrderStatus;
use App\Helpers\Helper;
use App\UserModel\OrderDetail;

class UpdatePickupStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fedex:update-pickup-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update FedEx pickup status for pending orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function logStatusHistory($orderId, $statusId, $code)
    {
        $lastHistory = AdminOrderStatusHistory::where('order_id', $orderId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastHistory || $lastHistory->order_status_id != $statusId || $lastHistory->fedex_status_code != $code) {
            $history = new AdminOrderStatusHistory();
            $history->order_id = $orderId;
            $history->order_status_id = $statusId;
            $history->type = 'system';
            $history->fedex_status_code = $code;
            $history->save();
        }
    }

    public function handle()
    {
        // Find orders where tracking number is present and status is not final (e.g. delivered, cancelled, returned)
        // Adjust the status IDs as per your config
        $finalStatuses = [
            config('custom_web.delivered'), 
            config('custom_web.cancel'), 
            config('custom_web.return')
        ];

        $orders = WebOrder::whereNotNull('tracking_number')
                          ->whereNotIn('order_status', $finalStatuses)
                          ->get();
        
        if ($orders->isEmpty()) {
            $this->info('No pending orders with tracking numbers found.');
            return 0;
        }

        $this->info('Found ' . $orders->count() . ' orders to check.');

        $fedexService = new FedExService();

        foreach ($orders as $order) {
            try {
                $trackingNumber = $order->tracking_number;
                $statusChanged  = false;

                \Log::info('FedEx Tracking Number: ' . $trackingNumber);
                
                $response = $fedexService->trackStatus($trackingNumber);
                
                $trackResults = $response['output']['completeTrackResults'][0]['trackResults'][0] ?? null;
                
                if ($trackResults && isset($trackResults['latestStatusDetail'])) {
                    $detail = $trackResults['latestStatusDetail'];
                    $code = $detail['code'] ?? ''; // e.g., PU, OC, DL
                    $desc = $detail['description'] ?? '';

                    $this->info("Order #{$order->id} [{$trackingNumber}]: {$code} - {$desc}");

                    // Map specific statuses to our DB
                    // 'Ready to Ship' is basically when label is created but not picked up (OC - Order Created or similar)
                    
                    $order->fedex_tracking_status = $code;
                    $order->pickup_response = json_encode($trackResults);
                    
                    if (isset($detail['scanLocation'])) {
                         $loc = $detail['scanLocation'];
                         $locationStr = ($loc['city'] ?? '') . ', ' . ($loc['stateOrProvinceCode'] ?? '') . ', ' . ($loc['countryCode'] ?? '');
                         $order->pickup_location = trim($locationStr, ', ');
                    }

                    if ($code === 'HL') {
                        // Ready to Ship
                        $this->logStatusHistory($order->id, 1, $code);
                        $this->info(" -> Updated to Hold at Location");
                    }

                    if ($code === 'OC') {
                        // Ready to Ship
                        if ($order->order_status != 2) { // Assuming 2 is Ready to Ship
                            $order->order_status = 2; 
                            $statusChanged = true;
                            $this->logStatusHistory($order->id, 2, $code);
                            $this->info(" -> Updated to Ready to Ship");
                        }
                    }

                    if ($code === 'PU' || $code === 'DP' || $code === 'AF' || $code === 'IT') {
                        // Picked Up / Departed / At facility / In Transit -> Shipped
                         if ($order->order_status != 3) { // Assuming 3 is Shipped
                             $order->order_status = 3; 
                             $statusChanged = true;
                             $this->logStatusHistory($order->id, 3, $code);
                             $this->info(" -> Updated to Shipped");
                         }
                         
                         if (!$order->shipped_at) {
                             $order->shipped_at = now();
                             $this->info(" -> Set Shipped At");
                         }
                         
                         if ($code === 'PU') {
                             $order->pickup_status = 'PICKED_UP';
                         }

                    } elseif ($code === 'DL') { // Delivered
                         $deliveredStatus = config('custom_web.delivered'); // 4
                         if ($order->order_status != $deliveredStatus) {
                             $order->order_status = $deliveredStatus;
                             $statusChanged = true;
                             $this->logStatusHistory($order->id, $deliveredStatus, $code);
                             $this->info(" -> Updated to Delivered");
                         }
                         
                         if (!$order->delivered_at) {
                             $order->delivered_at = now();
                             $this->info(" -> Set Delivered At");
                         }
                         
                         $order->pickup_status = 'DELIVERED'; // Assuming this status exists or just for tracking

                    } elseif ($code === 'CA') { // Cancelled
                        $cancelStatus = config('custom_web.cancel'); // 5
                        if ($order->order_status != $cancelStatus) {
                             $order->order_status = $cancelStatus;
                             $statusChanged = true;
                             $this->logStatusHistory($order->id, $cancelStatus, $code);
                             $this->info(" -> Updated to Cancelled");
                        }
                        $order->pickup_status = 'CANCELLED';

                    } elseif ($code === 'RS' || $code === 'RE') { // Returned to Shipper (fedex code RS often used)
                        $returnStatus = config('custom_web.return'); // 6
                        if ($order->order_status != $returnStatus) {
                             $order->order_status = $returnStatus;
                             $statusChanged = true;
                             $this->logStatusHistory($order->id, $returnStatus, $code);
                             $this->info(" -> Updated to Return");
                        }
                        $order->pickup_status = 'RETURNED';
                    }
                    
                    $order->save();

                    if ($statusChanged) {
                        $this->sendStatusUpdateMail($order);
                    }
                } else {
                    $this->warn("Order #{$order->id}: No track results found.");
                }

            } catch (Exception $e) {
                $this->error("Error checking Order #{$order->id}: " . $e->getMessage());
            }
        }

        return 0;
    }

    private function sendStatusUpdateMail(WebOrder $order)
    {
        try {
            $shipping_address = $order->getShippingAddress();
            if (!$shipping_address || empty($shipping_address->email)) {
                $this->warn("Order #{$order->id}: status changed but no shipping email found.");
                return false;
            }

            $adminOrderStatus = AdminOrderStatus::find($order->order_status);
            $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : '';
            $app_name         = Helper::getAppName();
            $customerCareEmail = Helper::getCustomerCareEmail();

            $from = [
                'email' => $admin_mail,
                'name'  => $app_name
            ];
            $to = [
                'email' => $shipping_address->email,
                'name'  => trim(ucfirst($shipping_address->first_name) . ' ' . ucfirst($shipping_address->last_name))
            ];
            $cc = ['email' => ''];

            $statusLabel = $adminOrderStatus ? $adminOrderStatus->order_status : 'Updated';
            $subject     = $app_name . "-Order #" . $order->id . " Status Updated - " . $statusLabel;
            $message     = \View::make(
                'web.emails.order_status_update.index',
                compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail', 'customerCareEmail')
            )->render();

            $phpMail      = new \App\Mail\CommonMailSender();
            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);

            if (isset($mailResponse['status']) && $mailResponse['status']) {
                $this->info(" -> Status email sent for Order #{$order->id}");
                return true;
            }

            $this->warn("Order #{$order->id}: status changed but email send failed.");
            return false;
        } catch (Exception $e) {
            $this->error("Order #{$order->id}: failed to send status email - " . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Http\Controllers\Admin\Order;

use App\AdminModel\AdminCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use App\User;
use App\AdminModel\AdminLanguage;
use App\AdminModel\Order\WebOrder;
use App\UserModel\OrderDetail;
use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\Order\AdminOrderStatusHistory;
use App\AdminModel\AdminCountry;
use App\Exports\OrderNotCompleteExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderDetailExport;
use App\AdminModel\CourierCompany\CourierCompany;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Order\OrderPaymentLog;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use App\UserModel\UserAddress;
use URL;
use App\AdminModel\Order\PaymentLink;
use App\Services\PaymentCalculationService;
use App\Services\FedExService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\UserModel\TemporaryOrder;
use App\UserModel\TemporaryOrderDetail;

class OrderController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.order';

    private function buildWebUrl(string $path): string
    {
        $baseUrl = rtrim(config('custom.web_url', config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['coupon']      = $request->coupon;
        $filter['orderType']   = $request->_ty;
        $orders                = new WebOrder();
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::getOrderStatus();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        $customizedBadgeColor = config('order.customized.type');
        return view('admin.order.order.index', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country', 'customizedBadgeColor'));
    }

    /**
     * Validate customized order fields instead of regular stock checks.
     * Returns array with status true/false and optional message.
     */
    private function validateCustomizedOrder($order)
    {
        // Basic validation: ensure order has order details with positive quantities
        $orderProductDetail = OrderDetail::where('order_id', $order->id)->get();
        if (!$orderProductDetail || count($orderProductDetail) == 0) {
            return ['status' => false, 'message' => 'Customized order contains no items.'];
        }

        foreach ($orderProductDetail as $record) {
            if (!isset($record->quantity) || intval($record->quantity) <= 0) {
                return ['status' => false, 'message' => 'One or more customized items have invalid quantity.'];
            }
        }

        // Validate that customized billing/address details exist (at least one of the expected fields)
        $hasCustomDetails = false;
        if (!empty($order->custom_order_details)) {
            $decoded = json_decode($order->custom_order_details, true);
            if ($decoded && count($decoded) > 0) {
                // Accept if JSON has any content
                $hasCustomDetails = true;
            }
        }

        $customBillingFields = ['custom_billing_country_id', 'custom_billing_city', 'custom_billing_address_detail'];
        foreach ($customBillingFields as $f) {
            if (!empty($order->{$f})) {
                $hasCustomDetails = true;
                break;
            }
        }

        if (!$hasCustomDetails) {
            return ['status' => false, 'message' => 'Customized order missing address or customization details.'];
        }

        return ['status' => true];
    }

    private function getSuccessfulPaymentTotal($orderId)
    {
        return PaymentCalculationService::getSuccessfulPaymentTotal($orderId);
    }

    private function getCalculatedPendingAmount($order, $successfulPaidTotal = null)
    {
        return PaymentCalculationService::calculateOrderPayments($order)['pending_amount'];
    }

    private function getRelatedPaymentOrderIds($order)
    {
        $orderIds = [(int) $order->id];

        if (!empty($order->parent_id)) {
            $orderIds[] = (int) $order->parent_id;
        }

        return array_values(array_unique(array_filter($orderIds)));
    }

    private function getLatestPendingPaymentLink($order)
    {
        return PaymentLink::whereIn('order_id', $this->getRelatedPaymentOrderIds($order))
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();
    }

    private function invalidateParentMixedPaymentLinks($order)
    {
        if (!$order || empty($order->parent_id)) {
            return;
        }

        $pendingParentLinks = PaymentLink::where('order_id', $order->parent_id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingParentLinks as $paymentLink) {
            $gatewayResponse = is_array($paymentLink->gateway_response)
                ? $paymentLink->gateway_response
                : (json_decode($paymentLink->gateway_response, true) ?: []);

            $splitPayment = $gatewayResponse['split_payment'] ?? null;
            if (
                is_array($splitPayment) &&
                (int) ($splitPayment['custom_order_id'] ?? 0) === (int) $order->id
            ) {
                $gatewayResponse['invalidated_reason'] = 'custom_order_paid_separately';
                $gatewayResponse['invalidated_custom_order_id'] = (int) $order->id;
                $gatewayResponse['invalidated_at'] = now()->toDateTimeString();

                $paymentLink->status = 'rejected';
                $paymentLink->gateway_response = $gatewayResponse;
                $paymentLink->save();
            }
        }
    }

    public function vieworder($id)
    {
        $orderDetail = WebOrder::find($id);

        if ($orderDetail) {
            $tax                = \App\Helpers\Helper::getTax();
            $orderStatus        = AdminOrderStatus::getOrderStatus();
            $allOrderStatus     = AdminOrderStatus::getAllOrderStatus();
            $orderProductDetail = OrderDetail::where('order_id', $id)->get();
            $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
            $currency           = Helper::getDefaultCurrency();
            $badgeColor         = config('order.badge');
            $courier            = CourierCompany::getCourierCompany();

            $shippingAddress = $orderDetail->getShippingAddress();


            $addressQr = "";

            if (isset($shippingAddress) && ($shippingAddress->address_url || $orderDetail->address_url)) {
                $writer = new PngWriter();

                // Create QR code

                $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                    ->setEncoding(new Encoding('UTF-8'))
                    //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                    ->setSize(300)
                    ->setMargin(10)
                    //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                    ->setForegroundColor(new Color(0, 0, 0))
                    ->setBackgroundColor(new Color(255, 255, 255));

                // Create generic logo

                $logo = Logo::create(public_path() . '/logo.png')
                    ->setResizeToWidth(100);

                // Create generic label
                $label = Label::create('Shipping Address')
                    ->setTextColor(new Color(255, 0, 0));

                $result = $writer->write($qrCode, $logo, $label);

                //header('Content-Type: ' . $result->getMimeType());
                $addressQr = $result->getDataUri();
            }



            return view('admin.order.order.detail', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'allOrderStatus', 'courier', 'shippingAddress', 'addressQr'));
        }

        $status       = 'error';
        $message_text = Lang::get($this->langFile . '.record_not_found');

        return redirect()->route('admin.order.index')->with($status, $message_text);
    }

    public function customizedViewOrder($id)
    {
        $orderDetail = WebOrder::find($id);

        if ($orderDetail && $orderDetail->custom_order_type) {
            $tax                = \App\Helpers\Helper::getTax();
            $orderStatus        = AdminOrderStatus::getOrderStatus();
            $allOrderStatus     = AdminOrderStatus::getAllOrderStatus();
            $orderProductDetail = OrderDetail::where('order_id', $id)->get();
            $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
            $currency           = Helper::getDefaultCurrency();
            $badgeColor         = config('order.badge');
            $customizedBadgeColor = config('order.customized.type');
            $courier            = CourierCompany::getCourierCompany();

            $shippingAddress = $orderDetail->getCustomeShippingAddress();

            $addressQr = "";

            $paymentLink = PaymentLink::where('order_id', $id)->get();
            $paymentHistoryLinks = PaymentLink::where('order_id', $id)
                ->orderByDesc('created_at')
                ->get();
            $relatedPendingPaymentLink = $this->getLatestPendingPaymentLink($orderDetail);

            $country  = AdminCountry::getCountry(1);

            $pickupUser = \App\User::where('pickupstore', 1)->pluck('id');

            $address = \App\UserModel\UserAddress::where('user_id', $orderDetail->user_id)->where('is_active', 1);
            if ($pickupUser) {
                $address->orWhereIn('user_id', $pickupUser)->where('is_active', 1);
            }
            $addresses = $address->orderBy('pickupstore', 'desc')->orderBy('created_at', 'desc')->get();

            // if (isset($shippingAddress) && ($shippingAddress['address_url'] || $orderDetail['address_url'])) {
            //     $writer = new PngWriter();

            //     // Create QR code

            //     $qrCode = QrCode::create($orderDetail['address_url'] ?  $orderDetail['address_url'] : $shippingAddress['address_url'])
            //         ->setEncoding(new Encoding('UTF-8'))
            //         //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
            //         ->setSize(300)
            //         ->setMargin(10)
            //         //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            //         ->setForegroundColor(new Color(0, 0, 0))
            //         ->setBackgroundColor(new Color(255, 255, 255));

            //     // Create generic logo

            //     $logo = Logo::create(public_path() . '/logo.png')
            //         ->setResizeToWidth(100);

            //     // Create generic label
            //     $label = Label::create('Shipping Address')
            //         ->setTextColor(new Color(255, 0, 0));

            //     $result = $writer->write($qrCode, $logo, $label);

            //     //header('Content-Type: ' . $result->getMimeType());
            //     $addressQr = $result->getDataUri();
            // }

            return view('admin.order.order.customized-detail', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'customizedBadgeColor', 'allOrderStatus', 'courier', 'shippingAddress', 'addressQr', 'paymentLink', 'paymentHistoryLinks', 'country', 'addresses', 'relatedPendingPaymentLink'));
        }

        $status       = 'error';
        $message_text = Lang::get($this->langFile . '.record_not_found');

        return redirect()->route('admin.order.customized-order')->with($status, $message_text);
    }

    public function saveNote(Request $request)
    {
        if ($request->order_note && $request->order_id) {
            $orderDetail = WebOrder::find($request->order_id);

            $orderDetail->order_note = $request->order_note;
            if ($orderDetail->save()) {
                return redirect()->back()->with('success', 'Note updated successfully');
            } else {
                return redirect()->back()->with('error', 'Somthing went wrong');
            }
        }
    }

    public function updateAddress(Request $request)
    {

        if ($request->order_id) {
            $orderDetail = WebOrder::find($request->order_id);

            $orderDetail->address_url = $request->address_url;
            if ($orderDetail->save()) {
                return redirect()->back()->with('success', 'Address URL updated successfully');
            } else {
                return redirect()->back()->with('error', 'Somthing went wrong');
            }
        }
    }

    public function updateOrderStatus(Request $request)
    {
        $validatedData = $request->validate([
            'orders_status' => 'required',
        ], [
            'orders_status.required' => Lang::get($this->langFile . '.error-select-orders_status'),
        ]);

        DB::beginTransaction();
        try {
            $orderDetail = WebOrder::find($request->orders_id);

            if ($orderDetail) {
                $successfulPaidTotal = $this->getSuccessfulPaymentTotal($orderDetail->id);
                $pendingAmount = $this->getCalculatedPendingAmount($orderDetail, $successfulPaidTotal);

                // For purchase_order customized orders, require pending amount to be zero
                // before allowing status updates. Return a friendly message if not.
                if ($orderDetail->custom_order_type === 'purchase_order' && $pendingAmount > 0) {
                    DB::rollBack();
                    $status = 'error';
                    $message_text = 'Please update the due amount before proceeding.';
                    return redirect()->back()->with($status, $message_text);
                }

                $isPartialCancelReturn = false;

                $cancelReturn = isset($request->cancel_return) ? $request->cancel_return : false;

                $lastUpdatedStatus = AdminOrderStatusHistory::where('order_id', $orderDetail->id)->orderBy('id', 'desc')->first();

                if ($cancelReturn) {
                    $orderDetail->cancel_return_flag = 1;
                    $orderProductCount               = OrderDetail::where(['order_id' => $orderDetail->id, 'is_active' => 1])->count();
                    if (count($cancelReturn) == $orderProductCount) {
                        $orderDetail->order_status = $request->orders_status;

                        $orderStatusHistory                  = new AdminOrderStatusHistory();
                        $orderStatusHistory->order_id        = $orderDetail->id;
                        $orderStatusHistory->order_status_id = $request->orders_status;
                        $orderStatusHistory->type = 'admin';
                        $orderStatusHistory->save();
                    } else {
                        $isPartialCancelReturn = true;
                    }
                } else {
                    $orderDetail->order_status    = $request->orders_status;
                    $orderDetail->tracking_number = $request->tracking_number;
                    $orderDetail->courier_id      = $request->courier_id;

                    $orderStatusHistory                  = new AdminOrderStatusHistory();
                    $orderStatusHistory->order_id        = $orderDetail->id;
                    $orderStatusHistory->order_status_id = $request->orders_status;
                    $orderStatusHistory->type = 'admin';
                    $orderStatusHistory->save();
                }

                if ($orderDetail->save()) {
                    if ($cancelReturn) {
                        $productPrice          = 0;
                        /*
                         * For Discount
                         */
                        $discountPer           = $orderDetail->discount_per;
                        $discountApplied       = 0;
                        /*
                         * For Coupon Code
                         */
                        $couponDiscountPer     = $orderDetail->coupon_discount;
                        $couponDiscountApplied = 0;

                        foreach ($cancelReturn as $orderDetailId) {
                            $orderProductDetail = OrderDetail::where(['id' => $orderDetailId, 'order_id' => $orderDetail->id])->first();
                            if ($orderProductDetail) {
                                $individualProductPrice = $orderProductDetail->total_price;
                                $productPrice           += $individualProductPrice;
                                $productQuantity        = $orderProductDetail->quantity;

                                if ($discountPer) {
                                    $individualDiscount = ($individualProductPrice * $discountPer) / 100;
                                    $discountApplied    += $individualDiscount;
                                }

                                if ($couponDiscountPer) {
                                    $individualDiscount    = ($individualProductPrice * $couponDiscountPer) / 100;
                                    $couponDiscountApplied += $individualDiscount;
                                }

                                $orderProductDetail->order_status = $request->orders_status;
                                $orderProductDetail->is_active    = 0;

                                if ($orderProductDetail->save()) {
                                    /*
                                     * Have to increase quantity of product
                                     */
                                    $orderProductId = $orderProductDetail->product_id;
                                    $orderSizeId    = $orderProductDetail->size_id;
                                    $orderColorId   = $orderProductDetail->color_id;
                                    $orderQuantity  = $orderProductDetail->quantity;
                                    $productDetail  = \App\AdminModel\Product\AdminProduct::withTrashed()->find($orderProductId);
                                    $productDetail->increment('product_quantity', $orderQuantity); // Increase quantity of specific product in order

                                    $productDetail->save();

                                    if ($orderSizeId || $orderColorId) {
                                        $attribute = AdminProductAttribute::where(['products_id' => $orderProductId, 'size_id' => $orderSizeId, 'color_id' => $orderColorId])->first();
                                        if ($attribute) {
                                            $attribute->increment('quantity', $orderQuantity); // Increase quantity of specific product in order
                                        } else {
                                            $attribute              = new AdminProductAttribute();
                                            $attribute->products_id = $orderProductId;
                                            $attribute->size_id     = $orderSizeId;
                                            $attribute->color_id    = $orderColorId;
                                            $attribute->quantity    = $orderQuantity;
                                            $attribute->save();
                                        }
                                    }
                                }
                            }
                        }
                        /*
                         * Have to get order Last Status, If Last order status is -- Shipped or Ready to Shipped
                         * ... then don't deduct Shipping Charge.... 
                         * ... else Deduct Shipping Charge
                         */

                        $deductShippingFlag = false;

                        if (!$isPartialCancelReturn) {
                            if ($lastUpdatedStatus) {
                                $lastUpdatedStatus = $lastUpdatedStatus->order_status_id;
                            } else {
                                $lastUpdatedStatus = Helper::getOrderDefaultStatus();
                            }

                            if (!in_array($lastUpdatedStatus, config('custom.deductShippingChargeExcept', [2, 3]))) {
                                $deductShippingFlag = true;
                            }

                            if ($orderDetail->gift_wrap) {
                                $orderDetail->gift_wrap = 0;
                                $orderDetail->save();
                            }
                        }

                        $orderDetail->order_price            = $orderDetail->order_price - $productPrice;
                        $orderDetail->discount               = $orderDetail->discount - $discountApplied;
                        $orderDetail->coupon_discount_amount = $orderDetail->coupon_discount_amount - $couponDiscountApplied;

                        $updatedOrderTotal = $orderDetail->order_price + $orderDetail->delivery_price + $orderDetail->tax - $orderDetail->discount - $orderDetail->coupon_discount_amount;
                        if ($deductShippingFlag) {
                            $updatedOrderTotal           = $updatedOrderTotal - $orderDetail->delivery_price;
                            $orderDetail->delivery_price = 0;
                        }
                        $orderDetail->order_total = $updatedOrderTotal;
                        $orderDetail->save();

                        $orderProductDetail  = OrderDetail::whereIn('id', $request->cancel_return)->get();
                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$orderProductDetail[0]->order_status];
                    } else {
                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$orderDetail->order_status];
                        $orderProductDetail  = OrderDetail::where('order_id', $orderDetail->id)->where('is_active', 1)->get();
                    }

                    /*
                     * Mail Sending Code
                     */


                    $currency = Helper::getDefaultCurrency();

                    $user = User::find($orderDetail->user_id);

                    $locale = "1";

                    if ($user && isset($user->locale)) {
                        $languagedata = AdminLanguage::find($user->locale);
                        if ($languagedata) {
                            $locale = $languagedata->id;
                            $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                        } else {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }
                    } else {
                        $locale = 1;
                        $view   = 'admin.order.order.en.mail';
                    }

                    $mailFrom = Helper::sendMailFrom();
                    $appName  = Helper::getAppName();
                    $mailTo   = $user->email;
                    $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                    $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                    $courier  = ($orderDetail->courier_id) ? CourierCompany::find($orderDetail->courier_id) : "";

                    $reciveEmail       = Helper::getSettingById(18);
                    $reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";

                    $phpMail           = new \App\Mail\CommonMailSender();
                    $customerCareEmail = Helper::getCustomerCareEmail();
                    $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                    $mailTo            = ['email' => $mailTo, 'name' => $userName];
                    $cc['email'] = $reciveAdminEmail;

                    $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                    //echo $message;die;
                    if ($mailFrom && $mailTo && $subject && $message) {
                        $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message, false, $cc);
                    }

                    /*
                     * /.Mail Sending Code
                     */

                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                } else {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } else {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        } catch (Throwable $e) {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->back()->with($status, $message_text);
    }

    public function cancelOrderStatus(Request $request, $orderId)
    {
        $orderId     = base64_decode($orderId);
        $orderDetail = WebOrder::find($orderId);
        if ($orderDetail) {
            $currentOrderStatus        = $orderDetail->order_status;
            $orderDetail->order_status = config('custom_web.cancel', 5);
            if ($orderDetail->save()) {
                if ($currentOrderStatus != config('custom_web.Pending') && $currentOrderStatus != config('custom_web.cancel') && $currentOrderStatus != config('custom_web.return')) {
                    $orderProductData = $orderDetail->getOrderDetail(1);
                    foreach ($orderProductData as $productData) {
                        $orderProductId = $productData->product_id;
                        $orderSizeId    = $productData->size_id;
                        $orderColorId   = $productData->color_id;
                        $orderQuantity  = $productData->quantity;
                        $productDetail  = \App\AdminModel\Product\AdminProduct::withTrashed()->find($orderProductId);
                        $productDetail->increment('product_quantity', $orderQuantity); // Increase quantity of specific product in order

                        $productDetail->save();

                        if ($orderSizeId || $orderColorId) {
                            $attribute = AdminProductAttribute::where(['products_id' => $orderProductId, 'size_id' => $orderSizeId, 'color_id' => $orderColorId])->first();
                            if ($attribute) {
                                $attribute->increment('quantity', $orderQuantity); // Increase quantity of specific product in order
                            } else {
                                $attribute              = new AdminProductAttribute();
                                $attribute->products_id = $orderProductId;
                                $attribute->size_id     = $orderSizeId;
                                $attribute->color_id    = $orderColorId;
                                $attribute->quantity    = $orderQuantity;
                                $attribute->save();
                            }
                        }
                    }
                }

                $orderStatusHistory                  = new AdminOrderStatusHistory();
                $orderStatusHistory->order_id        = $orderDetail->id;
                $orderStatusHistory->order_status_id = config('custom_web.cancel', 5);
                $orderStatusHistory->type = 'admin';
                if ($orderStatusHistory->save()) {
                    /*
                     * Mail Sending Code
                     */

                    $orderStatusPastForm = config('order.pastForm');
                    $orderStatusText     = $orderStatusPastForm[$orderDetail->order_status];
                    $orderProductDetail  = OrderDetail::where('order_id', $orderDetail->id)->get();
                    $currency            = Helper::getDefaultCurrency();

                    $user = User::withTrashed()->find($orderDetail->user_id);

                    $locale = "1";

                    if ($user && isset($user->locale)) {
                        $languagedata = AdminLanguage::find($user->locale);
                        if ($languagedata) {
                            $locale = $languagedata->id;
                            $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                        } else {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }
                    } else {
                        $locale = 1;
                        $view   = 'admin.order.order.en.mail';
                    }

                    $mailFrom = Helper::sendMailFrom();
                    $appName  = Helper::getAppName();
                    $mailTo   = $user->email;
                    $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                    $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                    $courier  = ($orderDetail->courier_id) ? CourierCompany::find($orderDetail->courier_id) : "";

                    $phpMail           = new \App\Mail\CommonMailSender();
                    $customerCareEmail = Helper::getCustomerCareEmail();
                    $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                    $mailTo            = ['email' => $mailTo, 'name' => $userName];

                    $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                    //echo $message;die;
                    if ($mailFrom && $mailTo && $subject && $message) {
                        $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message);
                    }

                    /*
                     * /.Mail Sending Code
                     */

                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                } else {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } else {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } else {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->back()->with($status, $message_text);
    }

    /* /
     * get order in complete data
     */

    public function orderNotCompleteData(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['exclude_customized'] = true;
        $orders                = new WebOrder();
        $status                = 1;
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status, true);
        $currency              = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::all();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        return view('admin.order.order.pending-data', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country'));
    }

    /*
     * export data 
     */

    public function orderIndexExport()
    {
        return Excel::download(new OrderDetailExport(), config('excel.order_data'));
    }

    public function orderNotProcessExport()
    {
        return Excel::download(new OrderNotCompleteExport(), config('excel.order_not_complete_data'));
    }

    public function customizedOrderData(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;
        $orders                = new WebOrder();
        $status                = 1;
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status, true, true);
        $currency              = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::all();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        $customizedBadgeColor  = config('order.customized.type');
        return view('admin.order.order.customized-data', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country', 'customizedBadgeColor'));
    }

    public function customizedPendingOrderData(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;
        $orders                = new WebOrder();
        $status                = 0;
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status, true, false, true);
        $currency              = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::all();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        $customizedBadgeColor  = config('order.customized.type');
        return view('admin.order.order.incomplete-customized-data', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country', 'customizedBadgeColor'));
    }

    public function customizedOrderExport(Request $request)
    {
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;

        return Excel::download(
            new \App\Exports\CustomizedOrderExport($searchText, $sortKey, $sortVal, $filter),
            config('excel.customized_orders')
        );
    }

    public function customizedIncompleteOrderExport(Request $request)
    {
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;

        return Excel::download(
            new \App\Exports\CustomizedIncompleteOrderExport($searchText, $sortKey, $sortVal, $filter),
            config('excel.customized_incomplete_orders')
        );
    }

    /**
     * Temporary Orders Listing
     */
    public function temporaryOrderData(Request $request)
    {
        $sorting = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['orderType'] = $request->_ty;
        $filter['customOrderType'] = $request->_cty;

        $tempOrderModel = new TemporaryOrder();
        $orders = $tempOrderModel->getAllAdminData($searchText, $sortKey, $sortVal, $pageLength, $filter);

        $currency = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }

        $orderType = (new \App\UserModel\Order())->getCustomOrderTypes();
        $orderStatusList = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'ready_to_ship' => 'Ready to Ship',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
        ];
        $customizedBadgeColor = config('order.customized.type');

        return view('admin.order.order.temporary-orders-data', compact(
            'sortKey',
            'sortVal',
            'searchText',
            'orders',
            'pageLength',
            'currency',
            'filter',
            'orderType',
            'orderStatusList',
            'customizedBadgeColor'
        ));
    }

    /**
     * View routing decision: redirect to Actual Order view if exists, otherwise Temporary Order view
     */
    public function viewTemporaryOrderRoute($id)
    {
        $tempOrder = TemporaryOrder::find($id);
        if (!$tempOrder) {
            return redirect()->route('admin.order.temporary-orders')->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        $linkedOrder = $tempOrder->getLinkedOrder();
        if ($linkedOrder) {
            return redirect()->route('admin.order.customized-pending-order.vieworder', ['id' => $linkedOrder->id]);
        }

        return redirect()->route('admin.order.temporary-orders.view', ['id' => $tempOrder->id]);
    }

    /**
     * Dedicated Temporary Order Detail View (for temporary orders without an actual order)
     */
    public function temporaryOrderDetail($id)
    {
        $tempOrder = TemporaryOrder::with(['details', 'user', 'country'])->find($id);
        if (!$tempOrder) {
            return redirect()->route('admin.order.temporary-orders')->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        $linkedOrder = $tempOrder->getLinkedOrder();
        $currency = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $customizedBadgeColor = config('order.customized.type');

        $country = AdminCountry::getCountry(1);

        $addresses = collect();
        if ($tempOrder->user_id) {
            $pickupUser = \App\User::where('pickupstore', 1)->pluck('id');
            $addressQuery = \App\UserModel\UserAddress::where('user_id', $tempOrder->user_id)->where('is_active', 1);
            if ($pickupUser && count($pickupUser)) {
                $addressQuery->orWhereIn('user_id', $pickupUser)->where('is_active', 1);
            }
            $addresses = $addressQuery->orderBy('pickupstore', 'desc')->orderBy('created_at', 'desc')->get();
        }

        return view('admin.order.order.temporary-detail', compact(
            'tempOrder',
            'linkedOrder',
            'currency',
            'customizedBadgeColor',
            'country',
            'addresses'
        ));
    }

    /**
     * Update Existing Temporary Order
     */
    public function updateTemporaryOrder(Request $request, $id = null)
    {
        $targetId = $id ?: $request->input('temporary_order_id');

        $baseValidator = Validator::make(array_merge($request->all(), ['temporary_order_id' => $targetId]), [
            'temporary_order_id' => 'required|exists:temporary_orders,id',
            'cust_name'          => 'nullable|string',
            'cust_phone'         => 'nullable|string',
            'cust_email'         => 'nullable|email',
            'cust_country_id'    => 'nullable',
            'cust_city'          => 'nullable',
            'cust_note'          => 'nullable|string',
            'custom_shipping'    => 'nullable|numeric|min:0',
            'custom_discount_type' => 'nullable|in:flat,percentage',
            'custom_discount_val'  => 'nullable|numeric|min:0',
        ]);

        if ($baseValidator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $baseValidator->errors()->first(),
                'errors'  => $baseValidator->errors()->toArray(),
            ], 422);
        }

        $tempOrder = TemporaryOrder::with('details')->findOrFail($targetId);

        $uploadedItemImages = $request->file('custom_item_image', []);
        $existingItemImages = array_map([$this, 'normalizeCustomizedImagePath'], $request->input('custom_item_existing_image', []));
        $activeItemIndexes = $this->getActiveCustomizedItemIndexes($request);

        DB::beginTransaction();
        try {
            // Update customer and address fields
            if ($request->has('cust_name')) {
                $tempOrder->customer_name = $request->cust_name;
            }
            if ($request->has('cust_phone')) {
                $tempOrder->customer_phone = $request->cust_phone;
            }
            if ($request->has('cust_email')) {
                $tempOrder->customer_email = $request->cust_email;
            }
            if ($request->has('cust_country_id')) {
                $tempOrder->country_id = $request->cust_country_id ?: null;
            }
            if ($request->has('cust_city_id')) {
                $tempOrder->city_id = $request->cust_city_id ?: null;
            }
            if ($request->has('cust_city')) {
                $tempOrder->city = $request->cust_city;
            }
            if ($request->has('cust_block')) {
                $tempOrder->block = $request->cust_block;
            }
            if ($request->has('cust_street')) {
                $tempOrder->street = $request->cust_street;
            }
            if ($request->has('cust_house_number')) {
                $tempOrder->house_number = $request->cust_house_number;
            }
            if ($request->has('cust_floor')) {
                $tempOrder->floor = $request->cust_floor;
            }
            if ($request->has('cust_address_detail')) {
                $tempOrder->address_detail = $request->cust_address_detail;
            }
            if ($request->has('cust_address_url')) {
                $tempOrder->address_url = $request->cust_address_url;
            }
            if ($request->has('cust_address_title')) {
                $tempOrder->address_title_text = $request->cust_address_title;
            }
            if ($request->has('customer_address_id')) {
                $tempOrder->customer_address_id = $request->customer_address_id ?: null;
            }
            if ($request->has('cust_note')) {
                $tempOrder->note = $request->cust_note;
            }
            if ($request->filled('custom_req_type')) {
                $tempOrder->type = $request->custom_req_type;
            }

            // Products
            $subTotal = 0;
            $items = [];
            $previousItemImages = $tempOrder->details->pluck('image')->filter()->toArray();
            $retainedItemImages = [];

            if (!empty($activeItemIndexes)) {
                $itemValidator = Validator::make($request->all(), $this->buildCustomizedItemValidationRules($activeItemIndexes));
                if ($itemValidator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => $itemValidator->errors()->first(),
                        'errors'  => $itemValidator->errors()->toArray(),
                    ], 422);
                }

                TemporaryOrderDetail::where('temporary_order_id', $tempOrder->id)->forceDelete();

                foreach ($activeItemIndexes as $sortIdx => $i) {
                    $title = $request->input("custom_product_title.$i", '');
                    $price = (float) $request->input("custom_price.$i", 0);
                    $qty   = (int) $request->input("custom_qty.$i", 0);
                    $total = round($price * $qty, 3);
                    $itemImage = $existingItemImages[$i] ?? null;

                    if (isset($uploadedItemImages[$i]) && $uploadedItemImages[$i]) {
                        $itemImage = $this->storeCustomizedReferenceImage(
                            $uploadedItemImages[$i],
                            $itemImage
                        );
                    }

                    if (!empty($itemImage)) {
                        $retainedItemImages[] = $itemImage;
                    }

                    $subTotal += $total;

                    $itemCode = $request->custom_item_code[$i] ?? '';
                    $desc     = $request->custom_desc[$i] ?? '';
                    $color    = $request->custom_color[$i] ?? '';
                    $size     = $request->custom_size[$i] ?? '';

                    TemporaryOrderDetail::create([
                        'temporary_order_id' => $tempOrder->id,
                        'product_name'       => $title,
                        'item_code'          => $itemCode,
                        'description'        => $desc,
                        'color'              => $color,
                        'size'               => $size,
                        'price'              => $price,
                        'quantity'           => $qty,
                        'total_price'        => $total,
                        'image'              => $itemImage,
                        'sort_order'         => $sortIdx,
                    ]);

                    $items[] = [
                        'item_code'     => $itemCode,
                        'product_title' => $title,
                        'description'   => $desc,
                        'color'         => $color,
                        'size'          => $size,
                        'price'         => $price,
                        'quantity'      => $qty,
                        'total'         => $total,
                        'image'         => $itemImage,
                    ];
                }

                $this->deleteObsoleteCustomizedImages($previousItemImages, $retainedItemImages);
            } else {
                $subTotal = (float) $tempOrder->subtotal;
            }

            // Financials
            $shipping = $request->filled('custom_shipping') ? (float) $request->custom_shipping : (float) $tempOrder->shipping_price;
            $discountType = $request->filled('custom_discount_type') ? $request->custom_discount_type : $tempOrder->discount_type;
            $discountVal = $request->filled('custom_discount_val') ? (float) $request->custom_discount_val : (float) $tempOrder->discount_val;

            $discountAmount = 0;
            if ($discountType === 'percentage') {
                $discountAmount = ($subTotal * $discountVal) / 100;
            } elseif ($discountType === 'flat') {
                $discountAmount = $discountVal;
            }
            $discountAmount = min($discountAmount, $subTotal);
            $grandTotal = max(0, round($subTotal + $shipping - $discountAmount, 3));

            $advance = $request->filled('advance_payment') ? (float) $request->advance_payment : (float) $tempOrder->advance_price;
            if ($tempOrder->type === 'quotation') {
                $advance = 0.0;
            } else {
                $advance = min($advance, $grandTotal);
            }
            $pendingAmount = max(0, round($grandTotal - $advance, 3));

            $tempOrder->subtotal = $subTotal;
            $tempOrder->shipping_price = $shipping;
            $tempOrder->discount_type = $discountType;
            $tempOrder->discount_val = $discountVal;
            $tempOrder->discount_amount = $discountAmount;
            $tempOrder->grand_total = $grandTotal;
            $tempOrder->advance_price = $advance;
            $tempOrder->pending_amount = $pendingAmount;

            $customOrderDetails = json_decode($tempOrder->custom_order_details, true) ?? [];
            $customOrderDetails['customer'] = [
                'name'           => $tempOrder->customer_name,
                'email'          => $tempOrder->customer_email,
                'phone'          => $tempOrder->customer_phone,
                'country_id'     => $tempOrder->country_id,
                'city_id'        => $tempOrder->city_id,
                'city'           => $tempOrder->city,
                'block'          => $tempOrder->block,
                'street'         => $tempOrder->street,
                'house_number'   => $tempOrder->house_number,
                'floor'          => $tempOrder->floor,
                'address_detail' => $tempOrder->address_detail,
                'address_url'    => $tempOrder->address_url,
                'note'           => $tempOrder->note,
            ];
            $customOrderDetails['items'] = $items;
            $customOrderDetails['shipping'] = $shipping;
            $customOrderDetails['discount_type'] = $discountType;
            $customOrderDetails['discount_val'] = $discountVal;
            $customOrderDetails['discount_amount'] = $discountAmount;
            $customOrderDetails['subtotal'] = $subTotal;
            $customOrderDetails['grand_total'] = $grandTotal;
            $customOrderDetails['advance_price'] = $advance;
            $customOrderDetails['pending_amount'] = $pendingAmount;
            $generatePendingLinkReq = (bool) $request->input('generate_pending_payment_link', $request->input('generate_pending_link', 0));
            $customOrderDetails['payment']['generate_pending_link'] = $generatePendingLinkReq ? 1 : 0;
            $customOrderDetails['calculation']['generate_pending_link'] = $generatePendingLinkReq ? 1 : 0;
            $customOrderDetails['generate_pending_link'] = $generatePendingLinkReq ? 1 : 0;
            $tempOrder->custom_order_details = json_encode($customOrderDetails);

            $tempOrder->save();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Temporary Order updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update temporary order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Temporary Order
     */
    public function deleteTemporaryOrder($id)
    {
        $tempOrder = TemporaryOrder::find($id);
        if ($tempOrder) {
            DB::beginTransaction();
            try {
                TemporaryOrderDetail::where('temporary_order_id', $tempOrder->id)->delete();
                $tempOrder->delete();
                DB::commit();
                $status = 'success';
                $message_text = Lang::get($this->langFile . '.delete-success');
                $message_text = str_replace("#module#", 'Temporary Order', $message_text);
            } catch (\Throwable $e) {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.delete-error');
                $message_text = str_replace("#module#", 'temporary order', $message_text);
            }
        } else {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.order.temporary-orders')->with($status, $message_text);
    }

    /* /
     * change order status from pending to complete
     */

    public function processedOrder($id)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $order       = WebOrder::find($id);
        $orderStatus = AdminOrderStatus::where('order_status', 'Process')->first();
        $flag        = true;
        if ($order && $order->order_status == config('custom_web.Pending')) {
            $successfulPaidTotal = $this->getSuccessfulPaymentTotal($order->id);
            $pendingAmount = $this->getCalculatedPendingAmount($order, $successfulPaidTotal);
            $hasPendingPaymentLink = PaymentLink::where('order_id', $order->id)
                ->where('status', 'pending')
                ->exists();
            if ($hasPendingPaymentLink && $pendingAmount > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Payment link is still pending. Please wait for payment or cancel the link before proceeding.');
            }
            if ($order->custom_order_type === 'purchase_order' && $pendingAmount > 0) {
                $status = 'error';
                $message_text = 'Please update the due amount before proceeding.';
                return redirect()->back()->with($status, $message_text);
            }
            /*
             * Check Coupon code if applied
             */
            if ($order->coupon_discount_amount && $order->coupon_id) {
                $checkCoupon = \App\AdminModel\Coupon\AdminCoupon::where(['id' => $order->coupon_id, 'coupon_code' => $order->coupon_code])->first();

                if ($checkCoupon) {
                    $user        = User::find($order->user_id);
                    $checkCoupon = Helper::checkCouponStatus($order->coupon_code, $user);
                    if (!$checkCoupon['status']) {
                        $flag         = false;
                        $status       = 'error';
                        $message_text = $checkCoupon['message'];
                    } else {
                        $checkCoupon = $checkCoupon['data'];
                    }
                } else {
                    $flag         = false;
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.coupon_code_not_exist');
                }
            }
            if ($flag) {
                DB::beginTransaction();
                try {
                    // Load order product details for later use (mailing, calculations)
                    $orderProductDetail = OrderDetail::where('order_id', $order->id)->get();

                    // If this is a customized order, validate customized-order specific data
                    if ($order->custom_order_type) {
                        $validateCustom = $this->validateCustomizedOrder($order);
                        if (!$validateCustom['status']) {
                            DB::rollback();
                            return redirect()->back()->with('error', $validateCustom['message']);
                        }

                        $order->order_status = $orderStatus->id;
                    } else {
                        // Regular order: perform stock availability checks and decrement stock
                        $orderProductDetail = OrderDetail::where('order_id', $order->id)->get();
                        foreach ($orderProductDetail as $record) {
                            $availQuantityFlag = true;

                            $adminProduct = AdminProduct::find($record->product_id);
                            if ($adminProduct && ($adminProduct->product_quantity >= $record->quantity)) {
                                $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                                $adminProduct->save();

                                if ($adminProduct->have_size || $adminProduct->have_color) {
                                    if ($record->size_id || $record->color_id) {
                                        $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id, 'size_id' => $record->size_id, 'color_id' => $record->color_id])->first();
                                        if ($adminProductAttriutes) {
                                            if ($adminProductAttriutes->quantity >= $record->quantity) {
                                                $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                                $adminProductAttriutes->save();
                                            } else {
                                                $availQuantityFlag = false;
                                            }
                                        } else {
                                            $availQuantityFlag = false;
                                        }
                                    }
                                }
                            } else {
                                $availQuantityFlag = false;
                            }

                            if (!$availQuantityFlag) {
                                DB::rollback();
                                $status       = 'error';
                                $productName  = $record->getProduct(1);
                                $productName  = $productName ? ucfirst($productName->products_name) : '';
                                $message_text = Lang::get($this->langFile . '.available_quantity_less');
                                $message_text = str_replace("#product#", $productName, $message_text);
                                return redirect()->back()->with($status, $message_text);
                            }
                        }
                        $order->order_status = $orderStatus->id;
                    }

                    if ($order->save()) {
                        // For customized orders, update the order detail rows to reflect processing
                        if ($order->custom_order_type) {
                            foreach ($orderProductDetail as $od) {
                                $od->order_status = $orderStatus->id;
                                $od->is_active    = 1;
                                $od->save();
                            }
                        }
                        if (isset($checkCoupon) && $checkCoupon) {
                            if (isset($checkCoupon['allow_multiple']) && $checkCoupon['allow_multiple'] == 1) {
                                /*
                                 * If allow multiple then don't update is_applied
                                 */
                            } else {
                                $checkCoupon->is_applied = 1;
                                $checkCoupon->save();
                            }
                        }
                        /*
                         * Mail Sending Code
                         */

                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$order->order_status];
                        $currency            = Helper::getDefaultCurrency();

                        $user = User::find($order->user_id);

                        $locale = "1";

                        if ($user && isset($user->locale)) {
                            $languagedata = AdminLanguage::find($user->locale);
                            if ($languagedata) {
                                $locale = $languagedata->id;
                                $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                            } else {
                                $locale = 1;
                                $view   = 'admin.order.order.en.mail';
                            }
                        } else {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }

                        $mailFrom = Helper::sendMailFrom();
                        $appName  = Helper::getAppName();
                        $mailTo   = $user->email;
                        $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                        $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                        $courier  = ($order->courier_id) ? CourierCompany::find($order->courier_id) : "";

                        $phpMail = new \App\Mail\CommonMailSender();

                        $customerCareEmail = Helper::getCustomerCareEmail();
                        $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                        $mailTo            = ['email' => $mailTo, 'name' => $userName];
                        $reciveEmail       = Helper::getSettingById(18);
                        $reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";
                        $cc['email'] = $reciveAdminEmail;


                        $orderDetail = $order;

                        $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                        //echo $message;die;

                        if ($mailFrom && $mailTo && $subject && $message) {
                            $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message, false, $cc);
                        }

                        /*
                         * /.Mail Sending Code
                         */

                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.update-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        if ($order->custom_order_type === 'purchase_order') {
            return redirect()->route('admin.order.customized-order')->with($status, $message_text);
        }
        return redirect()->route('admin.order.order-not-complete')->with($status, $message_text);
    }

    /*
     * print orde
     */

    public function printOrder($id)
    {
        $orderDetail        = WebOrder::find($id);
        $tax                = Helper::getTax();
        $orderStatus        = AdminOrderStatus::getOrderStatus();
        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
        $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
        $currency           = Helper::getDefaultCurrency();
        $badgeColor         = config('order.badge');
        $courier            = CourierCompany::find($orderDetail->courier_id);

        $shippingAddress = $orderDetail->getShippingAddress();

        $addressQr = "";

        if (isset($shippingAddress) && ($shippingAddress->address_url || $orderDetail->address_url)) {
            $writer = new PngWriter();

            // Create QR code
            $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                ->setEncoding(new Encoding('UTF-8'))
                //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                ->setSize(300)
                ->setMargin(10)
                //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));
            // Create generic logo

            $logo = Logo::create(public_path() . '/logo.png')
                ->setResizeToWidth(100);

            // Create generic label
            $label = Label::create('Shipping Address')
                ->setTextColor(new Color(255, 0, 0));

            $result = $writer->write($qrCode, $logo, $label);

            //header('Content-Type: ' . $result->getMimeType());
            $addressQr = $result->getDataUri();
        }

        if ($orderDetail && $orderDetail->custom_order_type) {
            return view('admin.order.order.print-customized', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr', 'courier'));
        }
        return view('admin.order.order.print', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr', 'courier'));
    }


    public function deliveryprintOrder($id)
    {
        $orderDetail        = WebOrder::find($id);
        $tax                = Helper::getTax();
        $orderStatus        = AdminOrderStatus::getOrderStatus();
        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
        $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
        $currency           = Helper::getDefaultCurrency();
        $badgeColor         = config('order.badge');
        $courier            = CourierCompany::find($orderDetail->courier_id);

        $shippingAddress = $orderDetail->getShippingAddress();

        $addressQr = "";

        if (isset($shippingAddress) && ($shippingAddress->address_url || $orderDetail->address_url)) {
            $writer = new PngWriter();

            // Create QR code
            $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                ->setEncoding(new Encoding('UTF-8'))
                //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                ->setSize(300)
                ->setMargin(10)
                //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));

            // Create generic logo

            $logo = Logo::create(public_path() . '/logo.png')
                ->setResizeToWidth(100);

            // Create generic label
            $label = Label::create('Shipping Address')
                ->setTextColor(new Color(255, 0, 0));

            $result = $writer->write($qrCode, $logo, $label);

            //header('Content-Type: ' . $result->getMimeType());
            $addressQr = $result->getDataUri();
        }

        if ($orderDetail && $orderDetail->custom_order_type) {
            return view('admin.order.order.delivery-print-customized', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr', 'courier'));
        }
        return view('admin.order.order.delivery-print', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr', 'courier'));
    }

    /*
     * Delete Order
     */

    public function deleteOrder($id, $page)
    {
        /*
         * Delete orde which has status either : Cancel or Return
         */
        $orderDetail = WebOrder::find($id);
        if ($orderDetail) {
            if (collect(config('custom_web.can_delete_status'))->contains($orderDetail->order_status)) {
                DB::beginTransaction();
                /*
                 * Delete Order from 'order_detail' and 'order' table
                 */
                try {
                    $orderDetailDelete = OrderDetail::where('order_id', $orderDetail->id)->delete();
                    $orderDelete       = $orderDetail->delete();
                    if ($orderDetailDelete && $orderDelete) {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    } else {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } else {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.cant_delete_order');
            }
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        if ($page == 'on') {
            return redirect()->route('admin.order.order-not-complete')->with($status, $message_text);
        } elseif ($page == 'customized') {
            return redirect()->route('admin.order.customized-order')->with($status, $message_text);
        }
        return redirect()->route('admin.order.index')->with($status, $message_text);
    }

    public function updateDueAmount(Request $request, $orderId)
    {
        $orderId = base64_decode($orderId);
        $order   = WebOrder::find($orderId);

        if (!$order) {
            return back()->with('error', 'Order not found');
        }

        if ($order->custom_order_type !== 'purchase_order') {
            return back()->with('error', 'Invalid order type');
        }

        DB::beginTransaction();

        try {
            $paidAmount = floatval(str_replace(',', '', $request->paid_amount ?? 0));
            $dueAmount  = floatval(str_replace(',', '', $request->due_amount ?? 0));
            $orderTotal = floatval($order->order_total);

            $discountType  = $request->discount_type;
            $discountValue = floatval($request->discount_value);
            $discountPer   = 0;

            $currentDue = floatval($order->pending_amount);

            if ($currentDue <= 0) {
                return back()->with('error', 'No due amount available');
            }

            // Calculate discount on DUE
            if ($discountType === 'percentage') {
                $discountAmount = ($currentDue * $discountValue) / 100;
                $order->discount_per = $discountValue;
                $discountPer   = floatval($request->discount_value);
            } else {
                $discountAmount = $discountValue;
                $order->discount_per = 0;
            }

            // discount cannot exceed due
            $discountAmount = min($discountAmount, $currentDue);
            $discountAmount = round($discountAmount, 2);

            $remainingDue = $currentDue - $discountAmount;

            // $order->paid_amount = round(
            //     $order->paid_amount + $discountAmount + $remainingDue,
            //     2
            // );

            $order->pending_amount = 0;

            // Store discount info
            $order->discount_type = $discountType;
            $order->discount      = $discountAmount;

            // Remove coupon completely
            $order->coupon_code = null;
            $order->coupon_id = null;
            $order->coupon_discount_amount = 0;
            $order->coupon_discount = 0;

            // Create a payment log entry recording what changed
            try {
                // reload order to ensure saved values
                $finalAmount = null;
                if (isset($discountAmount) && $discountAmount >= 0) {
                    $finalAmount = round(max(0, $orderTotal - $discountAmount), 3);
                } else {
                    $finalAmount = $orderTotal;
                }

                $log = OrderPaymentLog::create([
                    'order_id' => $order->id,
                    // 'coupon_code_id' => $couponData->coupon_id ?? $order->coupon_id ?? null,
                    // 'coupon_discount_amount' => round($discountAmount, 3),
                    'discount_type' => $discountType,
                    'discount_value' => $discountAmount,
                    'discount_per' => $discountPer,
                    'total_amount' => round($orderTotal, 3),
                    'due_amount' => $dueAmount,
                    'final_amount' => $finalAmount,
                    'user_id' => auth()->id() ?? null,
                    'meta' => json_encode([
                        'paid_input' => $paidAmount,
                        'due_input' => $dueAmount,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue,
                        'discount_per' => $discountPer,
                        // 'coupon_code' => $couponCode,
                    ]),
                ]);
            } catch (\Throwable $logEx) {
                // logging failure should not block the main transaction; just continue
            }

            $order->save();

            DB::commit();

            return back()->with('success', 'Due amount fully settled and marked as paid');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function convertQuotationToOrder($id)
    {
        $quotation = WebOrder::find($id);

        if (!$quotation) {
            return back()->with('error', 'Quotation not found');
        }

        if ($quotation->custom_order_type !== 'quotation') {
            return back()->with('error', 'This is not a quotation order');
        }

        DB::beginTransaction();

        try {

            /* ============================
         * Create New Order (Purchase)
         * ============================ */
            $newOrder = $quotation->replicate();
            $newOrder->is_from_quotation = 1;
            $newOrder->custom_order_type = 'purchase_order';
            $newOrder->order_status     = 7;       // adjust if needed
            $newOrder->created_at       = now();
            $newOrder->updated_at       = now();
            $newOrder->save();

            $orderDetail = OrderDetail::where('order_id', $id)->get();

            /* ============================
         * Copy Order Details
         * ============================ */
            foreach ($orderDetail as $detail) {
                $newDetail = $detail->replicate();
                $newDetail->order_id = $newOrder->id;
                $newDetail->created_at = now();
                $newDetail->updated_at = now();
                $newDetail->save();
            }

            DB::commit();

            if ($newOrder->id) {
                $quotation->is_add_to_po_order = 1;
                $quotation->save();
            }

            return back()->with(
                'success',
                'Quotation converted to Purchase Order successfully. New Order ID: #' . $newOrder->id
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function updateDiscount(Request $request, $id)
    {
        $order = WebOrder::findOrFail($id);

        /**
         * IMPORTANT:
         * Discount is applied ONLY on product subtotal (order_price)
         * Shipping & paid amount are NOT part of discount validation
         */
        $subtotal = (float) $order->order_price;

        $request->validate([
            'discount_type' => 'required|in:flat,percentage',
            'discount_value' => [
                'required',
                'numeric',
                function ($attr, $value, $fail) use ($request, $subtotal) {

                    // Percentage validation
                    if ($request->discount_type === 'percentage') {
                        if ($value < 1 || $value > 100) {
                            $fail('Percentage discount must be between 1 and 100.');
                        }
                    }

                    // Flat validation
                    if ($request->discount_type === 'flat') {
                        if ($value < 0) {
                            $fail('Discount cannot be negative.');
                        }

                        if ($value > $subtotal) {
                            $fail('Flat discount cannot exceed subtotal.');
                        }
                    }
                }
            ]
        ]);

        /**
         * Calculate discount amount
         */
        if ($request->discount_type === 'percentage') {
            $discountAmount = ($subtotal * $request->discount_value) / 100;
        } else {
            $discountAmount = $request->discount_value;
        }

        // Extra safety (never exceed subtotal)
        $discountAmount = min($discountAmount, $subtotal);

        // Update due amount & order total
        $successfulPaidTotal = $this->getSuccessfulPaymentTotal($order->id);
        $pending_amount = round(max(0, ($subtotal + $order->delivery_price - $discountAmount) - $successfulPaidTotal), 3);
        $order_total    = $subtotal + $order->delivery_price - $discountAmount;

        /**
         * Update order
         */
        $order->update([
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'discount'       => $discountAmount,
            'pending_amount' => $pending_amount,
            'order_total'    => $order_total
        ]);

        return back()->with('success', 'Discount updated successfully.');
    }

    public function updateShipping(Request $request, $id)
    {
        $request->validate([
            'delivery_price' => 'required|numeric|min:0'
        ]);

        $order = WebOrder::findOrFail($id);

        $subtotal = (float) $order->order_price;
        $discountAmount = (float) $order->discount ?? 0;

        // Update due amount & order total
        $successfulPaidTotal = $this->getSuccessfulPaymentTotal($order->id);
        $pending_amount = round(max(0, ($subtotal + $request->delivery_price - $discountAmount) - $successfulPaidTotal), 3);
        $order_total    = $subtotal + $request->delivery_price - $discountAmount;

        /**
         * Update order
         */
        $order->update([
            'delivery_price' => $request->delivery_price,
            'pending_amount' => $pending_amount,
            'order_total'    => $order_total
        ]);

        return back()->with('success', 'Shipping charge updated successfully.');
    }

    // public function generatePaymentLink($id)
    // {
    //     $order = WebOrder::findOrFail($id);

    //     session()->put('customer_order_id', $id);

    //     $merchant   = env('PAYMENT_MERCHANT', ''); #Merchant code
    //     $basURL     = env('PAYMENT_URL', '');
    //     $username   = env('PAYMENT_USERNAME', '');
    //     $password   = env('PAYMENT_PASSWORD', '');
    //     $apiKey     = env('PAYMENT_API_KEY', '');
    //     $websiteURL = URL::to('/');

    //     $successurl = route('web.checkout.payment_success');
    //     $failururl  = route('web.checkout.payment_error');

    //     $payment_type = config('custom_web.payment_type');

    //     $payment_type = $payment_type[5];

    //     $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
    //     $customOrder = null;
    //     $products = [];

    //     if (!empty($order->custom_order_details)) {
    //         $customOrder = json_decode($order->custom_order_details, true);
    //     }
    //     $customCustomer = $customOrder['customer'] ?? null;

    //     $address = "";

    //     if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
    //         $address = $hasPickupStoreAddressSelected;
    //     }

    //     $successurl = route('web.checkout.payment_success');
    //     $failururl  = route('web.checkout.payment_error');

    //     if (!empty($customOrder['items']) && is_array($customOrder['items'])) {
    //         foreach ($customOrder['items'] as $item) {
    //             $title =
    //                 !empty($item['product_title'])
    //                 ? $item['product_title']
    //                 : (
    //                     !empty($item['description'])
    //                     ? $item['description']
    //                     : (
    //                         !empty($item['item_code'])
    //                         ? $item['item_code']
    //                         : 'Customized Order'
    //                     )
    //                 );

    //             $products[] = [
    //                 'name'        => $title,
    //                 'description' => $title, // keep same fallback for description
    //                 'quantity'   => isset($item['quantity']) && $item['quantity'] > 0
    //                     ? (int) $item['quantity']
    //                     : 1,
    //                 // Price must be PER UNIT
    //                 'price'      => Helper::formatNumber(
    //                     isset($item['price']) ? $item['price'] : 0
    //                 ),
    //             ];
    //         }
    //     }

    //     //dd($final_total);
    //     $post_data = array(
    //         "products" => $products,
    //         "order" => array(
    //             "id" => strval($id),
    //             "reference" => "",
    //             "description" => "",
    //             "currency" => "KWD",
    //             "amount" => (float) $order->pending_amount
    //         ),
    //         "language" => "en",
    //         "reference" => array(
    //             "id" => strval($id)
    //         ),
    //         "customer" => array(
    //             'uniqueId' => '',
    //             'name' => is_array($customCustomer) && !empty($customCustomer['name']) ? $customCustomer['name'] : $address->first_name . ' ' . $address->last_name,
    //             'email' => is_array($customCustomer) && !empty($customCustomer['email']) ? $customCustomer['email'] : $address->email,
    //             'mobile' => ''
    //         ),
    //         'returnUrl' => $successurl,
    //         'cancelUrl' => $failururl,
    //         "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
    //         "customerExtraData" => "",
    //         "total_price" => (float) $order->pending_amount
    //     );

    //     // $curl = curl_init();

    //     // curl_setopt_array($curl, array(
    //     //     CURLOPT_URL => 'https://uapi.upayments.com/api/v1/charge',
    //     //     CURLOPT_RETURNTRANSFER => true,
    //     //     CURLOPT_ENCODING => '',
    //     //     CURLOPT_MAXREDIRS => 10,
    //     //     CURLOPT_TIMEOUT => 0,
    //     //     CURLOPT_FOLLOWLOCATION => true,
    //     //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     //     CURLOPT_CUSTOMREQUEST => 'POST',
    //     //     CURLOPT_POSTFIELDS => json_encode($post_data),
    //     //     CURLOPT_HTTPHEADER => array(
    //     //         'Content-Type: application/json',
    //     //         'Authorization: Bearer NJo4MGY9db49ID9MvaWf543OV2L4BgQc1v0H0Ix2'
    //     //     ),
    //     // ));

    //     // $response = curl_exec($curl);
    //     // $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Get the HTTP status code
    //     // curl_close($curl);

    //     // $res = json_decode($response);

    //     // if ($res && isset($res->status) && $res->status) {
    //         // $red_url = $res->data->link;
    //         $red_url = "https://apiv2.upayments.com?session_id=202610004530019808606012252112345539564899875752";
    //         $this->sendCustomOrderMail($red_url, $customCustomer, $order);
    //         dd($red_url);
    //     // } else {
    //     //     return back()->with('error', \Lang::get($this->langFile . '.payment-error'));
    //     // }
    // }

    public function generatePaymentLink(Request $request, $id)
    {
        try{
        // $request->validate([
        //     'payment_method_id' => 'required'
        // ]);

        $order = WebOrder::findOrFail($id);

        $calc = PaymentCalculationService::calculateOrderPayments($order);
        $pendingAmount = $calc['pending_amount'];
        if ($pendingAmount <= 0) {
            return back()->with('error', 'This order is already fully paid.');
        }
        
        $isAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($order);
        $isCustomPurchaseOrder = ($order->custom_order_type === 'purchase_order' || $order->order_type === 'custom' || !empty($order->custom_order_details));

        $advanceInput = $request->input('advance_payment');

        if ($isCustomPurchaseOrder && !$isAdvanceDone && ($advanceInput === null || $advanceInput === '')) {
            session()->put('error', 'Advance payment must be completed before generating the pending amount payment link.');
            session()->flash('error', 'Advance payment must be completed before generating the pending amount payment link.');
            return back()->with('error', 'Advance payment must be completed before generating the pending amount payment link.');
        }

        // Cancel all old pending payment links for this order
        PaymentLink::where('order_id', $order->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'gateway_response' => DB::raw("JSON_SET(COALESCE(gateway_response, '{}'), '$.cancelled_reason', 'New payment link generated', '$.cancelled_at', '" . now()->toDateTimeString() . "', '$.cancelled_by', " . (auth()->id() ?? 'NULL') . ")")
            ]);
        
        $linkAmount = $pendingAmount;

        if ($advanceInput !== null && $advanceInput !== '') {
            $request->validate([
                'advance_payment' => 'required|numeric|min:0.001'
            ]);
            $requestedAmount = round((float) $advanceInput, 3);
            $validation = PaymentCalculationService::validatePaymentAmount($order, $requestedAmount);
            if (!$validation['valid']) {
                return back()->with('error', $validation['message'])->withInput();
            }
            $linkAmount = $requestedAmount;
        }

        $isAdvanceLink = ($linkAmount > 0 && ($linkAmount < $pendingAmount || !$isAdvanceDone));

        session()->put('customer_order_id', $id);

        $merchant   = env('PAYMENT_MERCHANT', ''); #Merchant code
        $basURL     = env('PAYMENT_URL', '');
        $username   = env('PAYMENT_USERNAME', '');
        $password   = env('PAYMENT_PASSWORD', '');
        $apiKey     = env('PAYMENT_API_KEY', '');
        $websiteURL = URL::to('/');

        $successurl = $this->buildWebUrl(route('web.checkout.payment_success', [], false));
        $failururl  = $this->buildWebUrl(route('web.checkout.payment_error', [], false));

        $payment_type = config('custom_web.payment_type');

        $payment_type = $payment_type[1];

        $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
        $customOrder = null;
        $products = [];

        if (!empty($order->custom_order_details)) {
            $customOrder = json_decode($order->custom_order_details, true);
        }
        $customCustomer = is_array($customOrder['customer'] ?? null) ? $customOrder['customer'] : null;

        $address = null;
        if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
            $address = $hasPickupStoreAddressSelected;
        }
        if (!$address) {
            $address = $order->getShippingAddress();
        }

        $user = $order->getUser();

        $customerName = !empty($customCustomer['name'])
            ? $customCustomer['name']
            : (($address && !empty($address->first_name))
                ? $address->first_name . ' ' . $address->last_name
                : ($user && !empty($user->first_name)
                    ? $user->first_name . ' ' . $user->last_name
                    : 'Customer'));

        $customerEmail = !empty($customCustomer['email'])
            ? $customCustomer['email'] : null;

        $shouldSendEmail = !empty($customerEmail);

        if (!empty($customOrder['items']) && is_array($customOrder['items'])) {
            foreach ($customOrder['items'] as $item) {
                $title =
                    !empty($item['product_title'])
                    ? $item['product_title']
                    : (
                        !empty($item['description'])
                        ? $item['description']
                        : (
                            !empty($item['item_code'])
                            ? $item['item_code']
                            : 'Customized Order'
                        )
                    );

                $products[] = [
                    'name'        => $title,
                    'description' => $title, // keep same fallback for description
                    'quantity'   => isset($item['quantity']) && $item['quantity'] > 0
                        ? (int) $item['quantity']
                        : 1,
                    // Price must be PER UNIT
                    'price'      => Helper::formatNumber(
                        isset($item['price']) ? $item['price'] : 0
                    ),
                ];
            }
        }

        $paymentRequestId = uniqid('pay_', true);

        $post_data = array(
            "products" => $products,
            "order" => array(
                "id" => strval($id),
                "reference" => strval($paymentRequestId),
                "description" => "",
                "currency" => $order->currency_code ?? config('custom.currency'),
                "amount" => $linkAmount
            ),
            "paymentGateway" => array(
                // "src" => $payment_type ?? "knet"
                "src" => "knet"
            ),
            "language" => "en",
            "reference" => array(
                "id" => strval($paymentRequestId)
            ),
            "customer" => array(
                'uniqueId' => '',
                'name' => $customerName,
                'email' => $customerEmail,
                'mobile' => ''
            ),
            'returnUrl' => $successurl,
            'cancelUrl' => $failururl,
            "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
            "customerExtraData" => json_encode([
                "payment_request_id" => $paymentRequestId
            ]),
            "total_price" => $linkAmount
        );
                
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $basURL . 'charge',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Get the HTTP status code
        curl_close($curl);

        $res = json_decode($response);

        if ($res && isset($res->status) && $res->status) {
            $red_url = $res->data->link;

            $paymentLink = PaymentLink::create([
                'order_id'       => $order->id,
                'payment_link'   => $red_url,
                'payment_request_id'=> $paymentRequestId,
                'amount'         => $linkAmount,
                'generated_from' => 'admin',
                'generated_by'   => auth()->id(),
                'status'         => 'pending',
                'payment_type'   => 1,
                'is_advance_payment' => $isAdvanceLink ? 1 : 0,
                'customer_email' => $customerEmail,
                'email_sent_count' => $shouldSendEmail ? 1 : 0,
                'last_email_sent_at' => $shouldSendEmail ? now() : null
            ]);
            if ($shouldSendEmail) {
                $customCustomer = is_array($customCustomer) ? $customCustomer : [];
                $customCustomer['email'] = $customerEmail;
                $this->sendCustomOrderMail($this->buildWebUrl(route('web.checkout.payment_review', ['token' => $paymentRequestId], false)), $customCustomer, $order);
                return back()->with('success', 'Payment link generated and email sent.');
            }

            return back()->with('warning', 'Payment link generated, but email was not sent because the customer email is missing.');
        } else {
            return back()->with('error', \Lang::get($this->langFile . '.payment-error'));
        }
        }
        catch(\Throwable $e){
            dd($e->getMessage());
            return back()->with('error', 'Unable to generate payment link. ' . $e->getMessage());
        }
    }

    public function recordManualPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'manual_payment_amount' => 'required|numeric|min:0.001',
            'manual_payment_type' => 'required',
            'manual_paid_at' => 'nullable|date',
            'manual_reference_no' => 'nullable|string|max:191',
            'manual_note' => 'nullable|string|max:1000',
            'is_advance_payment' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $order = WebOrder::where('id', $id)->lockForUpdate()->first();

            if (!$order) {
                DB::rollBack();
                return back()->with('error', 'Order not found')->withInput();
            }

            if ($order->custom_order_type !== 'purchase_order') {
                DB::rollBack();
                return back()->with('error', 'Manual payment is allowed only for purchase orders')->withInput();
            }

            if ($order->order_status == config('custom_web.cancel')) {
                DB::rollBack();
                return back()->with('error', 'Cannot record payment for canceled order')->withInput();
            }

            $manualAmount = round((float) $validated['manual_payment_amount'], 3);
            $validation = PaymentCalculationService::validatePaymentAmount($order, $manualAmount);
            if (!$validation['valid']) {
                DB::rollBack();
                return back()->with('error', $validation['message'])->withInput();
            }

            // Cancel all old pending payment links for this order
            PaymentLink::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'gateway_response' => DB::raw("JSON_SET(COALESCE(gateway_response, '{}'), '$.cancelled_reason', 'Manual payment recorded', '$.cancelled_at', '" . now()->toDateTimeString() . "', '$.cancelled_by', " . (auth()->id() ?? 'NULL') . ")")
                ]);

            $paymentRequestId = uniqid('manual_', true);
            $paidAt = !empty($validated['manual_paid_at']) ? $validated['manual_paid_at'] : now();

            $isAdvancePayment = (bool) $request->input('is_advance_payment', false);

            PaymentLink::create([
                'order_id' => $order->id,
                'payment_link' => '',
                'payment_request_id' => $paymentRequestId,
                'amount' => $manualAmount,
                'generated_from' => 'admin',
                'generated_by' => auth()->id(),
                'status' => 'paid',
                'paid_amount' => $manualAmount,
                'paid_at' => $paidAt,
                'gateway_response' => [
                    'source' => 'manual_admin',
                    'reference_no' => $validated['manual_reference_no'] ?? '',
                    'note' => $validated['manual_note'] ?? '',
                    'recorded_by' => auth()->id(),
                ],
                'payment_type' => $validated['manual_payment_type'],
                'is_advance_payment' => $isAdvancePayment ? 1 : 0,
            ]);

            PaymentCalculationService::syncOrderBalance($order);
            $this->invalidateParentMixedPaymentLinks($order);

            DB::commit();
            return back()->with('success', 'Manual payment recorded successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to record manual payment. ' . $e->getMessage())->withInput();
        }
    }

    public function sendCustomOrderMail($paymentLink, $customCustomer, $order)
    {
        try {
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
            $reciveEmail      = Helper::getSettingById(18);
            $reciveAdminEmail = $reciveEmail ? $reciveEmail->setting_val : "";

            $customerCareEmail = Helper::getCustomerCareEmail();
            $app_name          = Helper::getAppName();

            $from['email'] = $admin_mail;
            $from['name']  = $app_name;

            // Parse custom details once
            $custName = $customCustomer['name'] ?? 'Customer';

            // If this is a quotation, use the quotation view and prefer the customer email from custom details
            $mailTo = $customCustomer['email'] ?? null;
            $view   = 'web.emails.payment_due_link';

            if (!$mailTo) return false; // Cannot send without email

            $to['email'] = $mailTo;
            $to['name']  = ucfirst($custName);

            $cc['email'] = $reciveAdminEmail;

            $typeLabel = 'Order';
            $subject = $app_name . ' - Payment Due for Order #' . $order->id;

            // Pass customDetails so the view can render items/customer info
            $message = \View::make($view, [
                'order'            => $order,
                'regularOrder'     => null,
                'customOrder'      => $order,
                'paymentLink'      => $paymentLink,
                'customerName'     => $custName,
                'currency'         => $order->currency_code ?? config('custom.currency'),
                'appName'          => $app_name,
                'customerCareEmail'=> $customerCareEmail,
            ])->render();

            $phpMail      = new \App\Mail\CommonMailSender();
            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);

            if (isset($mailResponse['status']) && $mailResponse['status']) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            // swallow and return false to avoid breaking order flow
            return false;
        }
    }

    public function sendPaymentLinkEmail(Request $request, $paymentLinkId)
    {
        $paymentLink = PaymentLink::findOrFail($paymentLinkId);
        if (empty($request->email)) {
            return back()->with('error', 'Customer email is missing for this payment link.');
        }

        $order = WebOrder::find($paymentLink->order_id);
        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        $customDetails = json_decode($order->custom_order_details, true) ?: [];
        $customCustomer = $customDetails['customer'] ?? [];
        $customCustomer['email'] = $request->email;

        $sent = $this->sendCustomOrderMail(
            $this->buildWebUrl(route('web.checkout.payment_review', ['token' => $paymentLink->payment_request_id], false)),
            $customCustomer,
            $order
        );

        if ($sent) {
            $paymentLink->email_sent_count = (int) $paymentLink->email_sent_count + 1;
            $paymentLink->last_email_sent_at = now();
            $paymentLink->save();
            return back()->with('success', 'Payment link email sent.');
        }

        return back()->with('error', 'Unable to send payment link email.');
    }
    public function updateItemDetail(Request $request)
    {
        $request->validate([
            'order_detail_id' => 'required|exists:orders_detail,id',
            'product_name'    => 'required|string|max:191',
            'product_price'   => 'required|numeric|min:0',
            'quantity'        => 'required|numeric|min:1',
            'custom_attributes' => 'nullable|array'
        ]);

        $orderDetailItem = OrderDetail::find($request->order_detail_id);
        $order = WebOrder::find($orderDetailItem->order_id);

        if ($order->order_status != config('custom_web.Pending')) {
             return back()->with('error', 'Cannot update items when order status is not Pending.');
        }

        // Update Item
        $orderDetailItem->product_name = $request->product_name;
        $orderDetailItem->product_price = $request->product_price;
        $orderDetailItem->quantity = $request->quantity;
        $orderDetailItem->total_price = $request->product_price * $request->quantity;

        // Custom Attributes - remove any empty keys
        $attributes = [];
        if ($request->has('custom_attributes')) {
            foreach ($request->custom_attributes as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $attributes[$key] = $value;
                }
            }
        }
        $orderDetailItem->custom_attributes = !empty($attributes) ? json_encode($attributes) : null;
        $orderDetailItem->save();

        // Recalculate Order Totals
        // Re-fetch all items to sum up
        $allOrderItems = OrderDetail::where('order_id', $order->id)
                                    ->where('is_active', 1)
                                    ->get();

        $subtotal = $allOrderItems->sum('total_price');

        // Gift wrap addition if exists (logic from view)
        if ($order->gift_wrap) {
            $subtotal += $order->gift_wrap;
        }

        // Calculate Discount
        $discountAmount = 0;
        if ($order->discount_type === 'percentage') {
             if ($order->discount_value) {
                 $discountAmount = ($subtotal * $order->discount_value) / 100;
             }
        } elseif ($order->discount_type === 'flat') {
             $discountAmount = $order->discount_value;
             // Cap at subtotal
             $discountAmount = min($discountAmount, $subtotal);
        } else {
             // Fallback to existing 'discount' column if no type set/modification done
             $discountAmount = $order->discount;
        }

        // Update Order
        $deliveryPrice = $order->delivery_price;
        $successfulPaidTotal = $this->getSuccessfulPaymentTotal($order->id);
        $pending_amount = round(max(0, ($subtotal + $deliveryPrice - $discountAmount) - $successfulPaidTotal), 3);
        $order_total    = $subtotal + $deliveryPrice - $discountAmount;

        $order->update([
            'order_price'    => $subtotal, // Subtotal
            'discount'       => $discountAmount,
            'pending_amount' => $pending_amount,
            'order_total'    => $order_total
        ]);

        return back()->with('success', 'Item updated successfully.');
    }

    public function updateFullOrder(Request $request)
    {
        $baseValidator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',

            // Customer (optional)
            'cust_name'  => 'nullable|string',
            'cust_phone' => 'nullable|string',
            'cust_email' => 'nullable|email',
            'cust_country_id' => 'nullable',
            'cust_city' => 'nullable',
            'cust_note' => 'nullable|string',

            // Optional
            'custom_shipping' => 'nullable|numeric|min:0',
            'custom_discount_type' => 'nullable|in:flat,percentage',
            'custom_discount_val'  => 'nullable|numeric|min:0',
            'custom_reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($baseValidator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $baseValidator->errors()->first(),
                'errors' => $baseValidator->errors()->toArray(),
            ], 422);
        }

        $validated = $baseValidator->validated();

        $order = WebOrder::findOrFail($validated['order_id']);
        $uploadedItemImages = $request->file('custom_item_image', []);
        $existingItemImages = array_map([$this, 'normalizeCustomizedImagePath'], $request->input('custom_item_existing_image', []));
        $activeItemIndexes = $this->getActiveCustomizedItemIndexes($request);

        if ($order->order_status != config('custom_web.Pending')) {
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be updated'
            ], 422);
        }

        DB::beginTransaction();

        try {
            /** ---------------- CUSTOMER JSON (MERGE SAFE) ---------------- */
            $customOrderDetails = json_decode($order->custom_order_details, true) ?? [];
            $customOrderDetails['customer'] = array_merge(
                $customOrderDetails['customer'] ?? [],
                array_filter([
                    'name' => $request->cust_name,
                    'email' => $request->cust_email,
                    'phone' => $request->cust_phone,
                    'country_id' => $request->cust_country_id,
                    'city_id' => $request->cust_city_id,
                    'city' => $request->cust_city,
                    'block' => $request->cust_block,
                    'street' => $request->cust_street,
                    'house_number' => $request->cust_house_number,
                    'floor' => $request->cust_floor,
                    'address_detail' => $request->cust_address_detail,
                    'address_url' => $request->cust_address_url,
                    'note' => $request->cust_note,
                ], fn($v) => !(is_null($v) || (is_string($v) && trim($v) === '')))
            );

            if ($request->hasFile('custom_reference_image')) {
                $customOrderDetails['reference_image'] = $this->storeCustomizedReferenceImage(
                    $request->file('custom_reference_image'),
                    $customOrderDetails['reference_image'] ?? null
                );
            }

            /** ---------------- PRODUCTS (ONLY IF PROVIDED) ---------------- */
            $subTotal = $order->order_price;

            if (!empty($activeItemIndexes)) {
                $itemValidator = Validator::make($request->all(), $this->buildCustomizedItemValidationRules($activeItemIndexes));

                if ($itemValidator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => $itemValidator->errors()->first(),
                        'errors' => $itemValidator->errors()->toArray(),
                    ], 422);
                }

                OrderDetail::where('order_id', $order->id)->delete();

                $items = [];
                $subTotal = 0;
                $previousItemImages = [];
                foreach (($customOrderDetails['items'] ?? []) as $existingItem) {
                    if (!empty($existingItem['image'])) {
                        $previousItemImages[] = $existingItem['image'];
                    }
                }
                $retainedItemImages = [];

                foreach ($activeItemIndexes as $i) {
                    $title = $request->input("custom_product_title.$i", '');
                    $price = (float) $request->input("custom_price.$i", 0);
                    $qty   = (int) $request->input("custom_qty.$i", 0);
                    $total = $price * $qty;
                    $itemImage = $existingItemImages[$i] ?? null;

                    if (isset($uploadedItemImages[$i]) && $uploadedItemImages[$i]) {
                        $itemImage = $this->storeCustomizedReferenceImage(
                            $uploadedItemImages[$i],
                            $itemImage
                        );
                    }

                    if (!empty($itemImage)) {
                        $retainedItemImages[] = $itemImage;
                    }

                    $subTotal += $total;

                    $attrs = [
                        'item_code' => $request->custom_item_code[$i] ?? '',
                        'color' => $request->custom_color[$i] ?? '',
                        'size' => $request->custom_size[$i] ?? '',
                        'image' => $itemImage,
                    ];

                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_name' => $title,
                        'product_price' => $price,
                        'quantity' => $qty,
                        'total_price' => $total,
                        'custom_attributes' => json_encode($attrs),
                        'note' => $request->custom_desc[$i] ?? '',
                        'is_active' => 1,
                    ]);

                    $items[] = [
                        'item_code' => $attrs['item_code'],
                        'product_title' => $title,
                        'description' => $request->custom_desc[$i] ?? '',
                        'color' => $attrs['color'],
                        'size' => $attrs['size'],
                        'price' => $price,
                        'quantity' => $qty,
                        'total' => $total,
                        'image' => $itemImage,
                    ];
                }

                $this->deleteObsoleteCustomizedImages($previousItemImages, $retainedItemImages);
                $customOrderDetails['items'] = $items;
            }

            /** ---------------- SHIPPING & DISCOUNT ---------------- */
            $existingShipping = isset($customOrderDetails['shipping'])
                ? (float) $customOrderDetails['shipping']
                : (
                    !is_null($order->custom_shipping)
                        ? (float) $order->custom_shipping
                        : (float) $order->delivery_price
                );
            $shipping = $request->filled('custom_shipping')
                ? (float) $request->custom_shipping
                : $existingShipping;

            $existingDiscountType = $customOrderDetails['discount_type']
                ?? $order->custom_discount_type
                ?? $order->discount_type;
            $discountType = $request->filled('custom_discount_type')
                ? $request->custom_discount_type
                : $existingDiscountType;

            $existingDiscountVal = isset($customOrderDetails['discount_val'])
                ? (float) $customOrderDetails['discount_val']
                : (
                    !is_null($order->custom_discount_val)
                        ? (float) $order->custom_discount_val
                        : (
                            !is_null($order->discount_value) && (float) $order->discount_value > 0
                                ? (float) $order->discount_value
                                : (
                                    $order->discount_type === 'percentage'
                                        ? (float) $order->discount_per
                                        : (float) $order->discount
                                )
                        )
                );
            $discountVal  = $request->filled('custom_discount_val')
                ? (float) $request->custom_discount_val
                : $existingDiscountVal;

            $discount = 0;
            $discountPer = null;

            if ($discountType === 'percentage') {
                $discountPer = $discountVal;
                $discount = ($subTotal * $discountVal) / 100;
            } elseif ($discountType === 'flat') {
                $discount = min($discountVal, $subTotal);
            }

            $customOrderDetails['shipping'] = $shipping;
            $customOrderDetails['discount_type'] = $discountType;
            $customOrderDetails['discount_val'] = $discountVal;
            $customOrderDetails['calculation'] = array_merge(
                $customOrderDetails['calculation'] ?? [],
                [
                    'sub_total' => $subTotal,
                    'shipping' => $shipping,
                    'discount_type' => $discountType,
                    'discount_value' => $discountVal,
                    'discount_amount' => $discount,
                ]
            );

            $grandTotal = $subTotal + $shipping - $discount;
            if ($order->gift_wrap) $grandTotal += $order->gift_wrap;

            $customOrderDetails['calculation']['grand_total'] = $grandTotal;

            if ($grandTotal < $order->paid_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Advance payment exceeds order total'
                ], 422);
            }

            $successfulPaidTotal = $this->getSuccessfulPaymentTotal($order->id);

            /** ---------------- SAVE ORDER ---------------- */
            $order->update([
                'order_price' => $subTotal,
                'delivery_price' => $shipping,
                'discount_type' => $discountType,
                'discount_value' => $discountVal,
                'discount_per' => $discountPer,
                'discount' => $discount,
                'order_total' => $grandTotal,
                'pending_amount' => round(max(0, $grandTotal - $successfulPaidTotal), 3),
                'custom_order_details' => json_encode($customOrderDetails),
                'custom_billing_country_id' => $request->filled('cust_country_id') ? $request->cust_country_id : $order->custom_billing_country_id,
                'custom_billing_city_id' => $request->filled('cust_city_id') ? $request->cust_city_id : $order->custom_billing_city_id,
                'custom_billing_city' => $request->filled('cust_city') ? $request->cust_city : $order->custom_billing_city,
                'custom_billing_block' => $request->filled('cust_block') ? $request->cust_block : $order->custom_billing_block,
                'custom_billing_street' => $request->filled('cust_street') ? $request->cust_street : $order->custom_billing_street,
                'custom_billing_house_number' => $request->filled('cust_house_number') ? $request->cust_house_number : $order->custom_billing_house_number,
                'custom_billing_floor' => $request->filled('cust_floor') ? $request->cust_floor : $order->custom_billing_floor,
                'custom_billing_address_detail' => $request->filled('cust_address_detail') ? $request->cust_address_detail : $order->custom_billing_address_detail,
                'custom_billing_address_url' => $request->filled('cust_address_url') ? $request->cust_address_url : $order->custom_billing_address_url,
                'custom_shipping' => $request->filled('custom_shipping') ? $request->custom_shipping : $order->custom_shipping,
                'custom_discount_type' => $request->filled('custom_discount_type') ? $request->custom_discount_type : $order->custom_discount_type,
                'custom_discount_val' => $request->filled('custom_discount_val') ? $request->custom_discount_val : $order->custom_discount_val,
                'custom_discount_per' => $request->filled('custom_discount_per') ? $request->custom_discount_per : $order->custom_discount_per,
                'custom_discount' => $request->filled('custom_discount') ? $request->custom_discount : $order->custom_discount,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getActiveCustomizedItemIndexes(Request $request): array
    {
        $indexes = [];
        foreach ([
            $request->input('custom_item_code', []),
            $request->input('custom_product_title', []),
            $request->input('custom_desc', []),
            $request->input('custom_color', []),
            $request->input('custom_size', []),
            $request->input('custom_price', []),
            $request->input('custom_qty', []),
            $request->input('custom_item_existing_image', []),
            $request->file('custom_item_image', []),
        ] as $group) {
            if (is_array($group)) {
                $indexes = array_merge($indexes, array_keys($group));
            }
        }

        $indexes = array_values(array_unique($indexes));
        sort($indexes);

        return array_values(array_filter($indexes, function ($index) use ($request) {
            return $this->hasMeaningfulCustomizedItemValue($request, $index);
        }));
    }

    private function hasMeaningfulCustomizedItemValue(Request $request, $index): bool
    {
        $uploadedFiles = $request->file('custom_item_image', []);
        if (!empty($uploadedFiles[$index])) {
            return true;
        }

        $existingImages = $request->input('custom_item_existing_image', []);
        if (!empty($existingImages[$index])) {
            return true;
        }

        $textFields = [
            $request->input("custom_item_code.$index", ''),
            $request->input("custom_product_title.$index", ''),
            $request->input("custom_desc.$index", ''),
            $request->input("custom_color.$index", ''),
            $request->input("custom_size.$index", ''),
        ];

        foreach ($textFields as $field) {
            if (trim((string) $field) !== '') {
                return true;
            }
        }

        $numericFields = [
            $request->input("custom_price.$index", ''),
            $request->input("custom_qty.$index", ''),
        ];

        foreach ($numericFields as $field) {
            if (is_numeric($field) && (float) $field > 0) {
                return true;
            }
        }

        return false;
    }

    private function buildCustomizedItemValidationRules(array $activeItemIndexes): array
    {
        $rules = [];

        foreach ($activeItemIndexes as $index) {
            $rules["custom_item_existing_image.$index"] = 'nullable|string';
            $rules["custom_item_image.$index"] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';
            $rules["custom_item_code.$index"] = 'nullable|string';
            $rules["custom_product_title.$index"] = 'nullable|string';
            $rules["custom_desc.$index"] = 'nullable|string';
            $rules["custom_color.$index"] = 'nullable|string';
            $rules["custom_size.$index"] = 'nullable|string';
            $rules["custom_price.$index"] = 'nullable|numeric|min:0';
            $rules["custom_qty.$index"] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function requestPickup($id)
    {
        $orderDetail = WebOrder::find($id);

        if ($orderDetail) {
            if (!$orderDetail->canRequestPickup()) {
                return back()->with('error', 'Pickup not allowed.');
            }

            $fedexService = new FedExService();

            $pickupResponse = $fedexService->createPickupRequest([
                'packageCount' => 1,
                'totalWeight' => [
                    'units' => 'KG',
                    'value' => 0.5 // you can calculate actual weight
                ],
                'carrierCode' => 'FDXE'
            ]);

            $readyToShipStatus = config('order.status.readyToShip', 2);
            $statusChanged = $orderDetail->order_status != $readyToShipStatus;

            $updateData = [
                'pickup_confirmation_code' => $pickupResponse['output']['pickupConfirmationCode'],
                'pickup_location' => $pickupResponse['output']['location'],
                'pickup_status' => 'REQUESTED',
                'pickup_response' => json_encode($pickupResponse),
                'pickup_requested_at' => now(),
            ];

            if ($statusChanged) {
                $updateData['order_status'] = $readyToShipStatus;
            }

            $orderDetail->update($updateData);

            if ($statusChanged) {
                $orderStatusHistory = new AdminOrderStatusHistory();
                $orderStatusHistory->order_id = $orderDetail->id;
                $orderStatusHistory->order_status_id = $readyToShipStatus;
                $orderStatusHistory->type = 'admin';
                $orderStatusHistory->save();
            }

            return back()->with('success', 'Pickup request sent successfully.');
        }
        else {
            return redirect()->back()->with('error', 'Order not found');
        }
    }

    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'trade_document' => 'required|file|mimes:pdf|max:5120'
        ]);

        $orderDetail = WebOrder::find($id);

        if (!$orderDetail) {
            return back()->with('error', 'Order not found.');
        }

        if (!$orderDetail->tracking_number) {
            return back()->with('error', 'Tracking number not found.');
        }

        $file = $request->file('trade_document');

        try {

            $fedexService = new FedExService();

            $base64File = base64_encode(
                file_get_contents($file->getRealPath())
            );

            $response = $fedexService->uploadTradeDocument(
                $orderDetail->tracking_number,
                $file->getClientOriginalName(),
                $base64File
            );


            return back()->with('success', 'Trade document uploaded successfully.');

        } catch (\Exception $e) {

            \Log::error('FedEx ETD Upload Error: ' . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    private function storeCustomizedReferenceImage($file, $existingPath = null)
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        $filename = trim($filename, '_');
        $filename = $filename ?: 'reference_image';

        $fileNameToStore = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $directory = public_path('storage/customized');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $fileNameToStore);
        $path = 'customized/' . $fileNameToStore;

        $existingPath = $this->normalizeCustomizedImagePath($existingPath);
        if ($path && $existingPath) {
            $this->deleteCustomizedImageFile($existingPath);
        }

        return $path;
    }

    private function deleteObsoleteCustomizedImages(array $previousPaths, array $retainedPaths)
    {
        $pathsToDelete = array_diff(
            array_filter($previousPaths),
            array_filter($retainedPaths)
        );

        foreach ($pathsToDelete as $path) {
            $this->deleteCustomizedImageFile($path);
        }
    }

    private function normalizeCustomizedImagePath($path)
    {
        if (!$path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        return ltrim(preg_replace('/^public\//', '', $path), '/');
    }

    private function deleteCustomizedImageFile($path)
    {
        $path = $this->normalizeCustomizedImagePath($path);
        if (!$path) {
            return;
        }

        $publicFile = public_path('storage/' . $path);
        if (file_exists($publicFile)) {
            @unlink($publicFile);
        }

        $legacyStoragePath = 'public/' . $path;
        if (Storage::disk('local')->exists($legacyStoragePath)) {
            Storage::disk('local')->delete($legacyStoragePath);
        }
    }

    public function recoverOrder($id)
    {
        $orderId     = base64_decode($id);
        $order = WebOrder::find($orderId);

        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        if ($order->order_status != config('custom_web.cancel')) {
            return back()->with('error', 'Only cancelled orders can be recovered.');
        }

        $order->update([
            'order_status' => config('custom_web.Pending'),
        ]);

        return back()->with('success', 'Order recovered successfully.');
    }

    public function customizedOrderQuotationData(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;
        $orders                = new WebOrder();
        $status                = 0;
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status, true, true, true, true);
        $currency              = Helper::getDefaultCurrency();
        if ($currency) {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::all();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        $customizedBadgeColor  = config('order.customized.type');

        return view('admin.order.order.quotation-to-customized-order-data', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country', 'customizedBadgeColor'));
    }

    public function customizedOrderQuotationExport(Request $request)
    {
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['show_customized_only'] = true;
        $filter['orderType']   = $request->_ty;
        $filter['customOrderType'] = $request->_cty;

        return Excel::download(
            new \App\Exports\QuotationToCustomizedOrderExport($searchText, $sortKey, $sortVal, $filter),
            config('excel.customized_quotation_to_order')
        );
    }
}

<?php

namespace App\Services;

use App\AdminModel\Order\PaymentLink;
use App\AdminModel\Order\WebOrder;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use App\UserModel\TemporaryOrder;
use App\UserModel\TemporaryOrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomizedOrderSyncService
{
    /**
     * Check if a customer is allowed to edit the given order.
     * Rule: Only allowed when order_status == 7 (Pending).
     *
     * @param Order|WebOrder|object|null $order
     * @return bool
     */
    public static function canCustomerEdit($order)
    {
        if (!$order) {
            return true; // Unlinked draft is always editable by its owner
        }

        $pendingStatus = config('custom_web.Pending', 7);
        return (int) $order->order_status === (int) $pendingStatus;
    }

    /**
     * Invalidate all in-flight unpaid payment links for an order.
     *
     * @param int|string $orderId
     * @param string $reason
     * @return int Number of invalidated links
     */
    public static function invalidatePendingPaymentLinks($orderId, $reason = 'order_total_modified')
    {
        $pendingLinks = PaymentLink::where('order_id', $orderId)
            ->where('status', 'pending')
            ->get();

        $count = 0;
        foreach ($pendingLinks as $link) {
            $resp = is_array($link->gateway_response)
                ? $link->gateway_response
                : (json_decode($link->gateway_response, true) ?: []);

            $resp['invalidated_reason'] = $reason;
            $resp['invalidated_at'] = now()->toDateTimeString();
            $resp['invalidated_by'] = Auth::check() ? Auth::id() : null;

            $link->status = 'rejected';
            $link->gateway_response = $resp;
            $link->save();
            $count++;
        }

        return $count;
    }

    /**
     * Synchronize a modified TemporaryOrder and its payload to the linked Actual Order.
     * Executed inside a caller-managed database transaction.
     *
     * @param TemporaryOrder $tempOrder
     * @param Order|WebOrder $order
     * @param array $customizedItems Array of customized item line rows
     * @param array $financials Calculation parameters (shipping, discount_type, discount_val)
     * @param string|null $expectedUpdatedAt Stale form concurrency token
     * @return array Result array with status, message, and recalculated financials
     */
    public static function syncDraftToActualOrder(TemporaryOrder $tempOrder, $order, array $customizedItems, array $financials = [], $expectedUpdatedAt = null)
    {
        // 1. Guard check: User ownership & IDOR
        if (Auth::check()) {
            $authId = (int) Auth::id();
            if ((int) $tempOrder->user_id !== $authId) {
                return [
                    'status' => false,
                    'message' => 'Unauthorized: You do not own this customized draft.',
                    'code' => 403,
                ];
            }

            if ((int) $order->user_id !== $authId) {
                return [
                    'status' => false,
                    'message' => 'Unauthorized: You do not own this order.',
                    'code' => 403,
                ];
            }
        }

        if ((int) $tempOrder->user_id !== (int) $order->user_id) {
            return [
                'status' => false,
                'message' => 'User ownership mismatch between draft and actual order.',
                'code' => 403,
            ];
        }

        // Verify mutual relationship between draft and actual order
        $orderCustomDetails = is_array($order->custom_order_details) 
            ? $order->custom_order_details 
            : (json_decode($order->custom_order_details, true) ?: []);
        $linkedTempId = $orderCustomDetails['temporary_order_id'] ?? null;

        $tempCustomDetails = is_array($tempOrder->custom_order_details)
            ? $tempOrder->custom_order_details
            : (json_decode($tempOrder->custom_order_details, true) ?: []);
        $linkedOrderId = $tempCustomDetails['order_id'] ?? null;

        if ($linkedTempId && (int) $linkedTempId !== (int) $tempOrder->id) {
            return [
                'status' => false,
                'message' => 'Draft does not belong to the specified order.',
                'code' => 403,
            ];
        }

        if ($linkedOrderId && (int) $linkedOrderId !== (int) $order->id) {
            return [
                'status' => false,
                'message' => 'Order reference does not match the draft linkage.',
                'code' => 403,
            ];
        }

        // 2. Guard check: Editability
        if (!self::canCustomerEdit($order)) {
            return [
                'status' => false,
                'message' => 'This order is currently being processed and cannot be modified directly.',
                'code' => 422,
            ];
        }

        // 3. Acquire pessimistic locks on both records in consistent order (Order then Temp)
        $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
        $lockedTemp = TemporaryOrder::where('id', $tempOrder->id)->lockForUpdate()->first();

        if (!$lockedOrder || !$lockedTemp) {
            return [
                'status' => false,
                'message' => 'Failed to lock order records for synchronization.',
                'code' => 500,
            ];
        }

        // 4. Optimistic concurrency / stale form submission check
        if ($expectedUpdatedAt !== null && !empty($expectedUpdatedAt)) {
            $currentUpdatedTimestamp = $lockedTemp->updated_at ? $lockedTemp->updated_at->toDateTimeString() : null;
            if ($currentUpdatedTimestamp && $currentUpdatedTimestamp !== $expectedUpdatedAt) {
                return [
                    'status' => false,
                    'message' => 'Order data has been modified in another session. Please refresh and try again.',
                    'code' => 409,
                ];
            }
        }

        $previousOrderTotal = round((float) ($lockedOrder->order_total ?? 0), 3);
        $previousPendingAmount = round((float) ($lockedOrder->pending_amount ?? 0), 3);

        // 5. Update Actual Order customer/billing details from draft
        if (!empty($lockedTemp->customer_phone)) $lockedOrder->cphone = $lockedTemp->customer_phone;
        if (!empty($lockedTemp->customer_email)) $lockedOrder->email = $lockedTemp->customer_email;
        $draftAddressId = $lockedTemp->customer_address_id ?? $lockedTemp->billing_address_id ?? null;
        if (!empty($draftAddressId)) {
            $lockedOrder->billing_address_id = $draftAddressId;
            if (empty($lockedOrder->shipping_address_id)) {
                $lockedOrder->shipping_address_id = $draftAddressId;
            }
        }
        if (!empty($lockedTemp->country_id)) $lockedOrder->custom_billing_country_id = $lockedTemp->country_id;
        if (!empty($lockedTemp->city)) $lockedOrder->custom_billing_city = $lockedTemp->city;
        if (!empty($lockedTemp->block)) $lockedOrder->custom_billing_block = $lockedTemp->block;
        if (!empty($lockedTemp->street)) $lockedOrder->custom_billing_street = $lockedTemp->street;
        if (!empty($lockedTemp->house_number)) $lockedOrder->custom_billing_house_number = $lockedTemp->house_number;
        if (!empty($lockedTemp->floor)) $lockedOrder->custom_billing_floor = $lockedTemp->floor;
        if (!empty($lockedTemp->address_detail)) $lockedOrder->custom_billing_address_detail = $lockedTemp->address_detail;
        if (!empty($lockedTemp->address_url)) $lockedOrder->custom_billing_address_url = $lockedTemp->address_url;

        // 6. Authoritative Server-Side Financial Recalculation
        $calculatedSubTotal = 0.0;
        foreach ($customizedItems as $item) {
            $unitPrice = round((float) ($item['price'] ?? 0), 3);
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $calculatedSubTotal += round($unitPrice * $qty, 3);
        }

        $subTotal = round($calculatedSubTotal, 3);
        $shipping = round((float) ($financials['shipping'] ?? $lockedOrder->delivery_price ?? 0), 3);
        $discountType = $financials['discount_type'] ?? $lockedOrder->discount_type ?? null;
        $discountVal = round((float) ($financials['discount_val'] ?? $lockedOrder->discount_per ?? 0), 3);

        $discountAmount = 0.0;
        if ($discountType === 'percentage') {
            $cappedPercent = min(100.0, max(0.0, $discountVal));
            $discountAmount = round(($subTotal * $cappedPercent) / 100, 3);
        } elseif ($discountType === 'flat') {
            $discountAmount = round(min($subTotal, max(0.0, $discountVal)), 3);
        }

        $grandTotal = max(0.0, round($subTotal + $shipping - $discountAmount, 3));

        // Fetch live successful payments from ledger or advance price on customized draft
        $linkPaid = PaymentCalculationService::getSuccessfulPaymentTotal($lockedOrder->id);
        $draftAdvance = (float) ($lockedTemp->advance_price ?? 0);
        $totalPaid = ($linkPaid > 0) ? $linkPaid : $draftAdvance;
        $isAdvanceDonePrior = PaymentCalculationService::isAdvancePaymentDone($lockedOrder);

        // STAGE 2 Validation: Once an advance payment has been successfully completed,
        // the customer MUST NOT be allowed to reduce the Order Total below the already paid advance amount.
        if ($isAdvanceDonePrior && $totalPaid > 0 && $grandTotal < $totalPaid) {
            return [
                'status' => false,
                'message' => 'Order total cannot be less than the paid advance amount of ' . number_format($totalPaid, 3, '.', '') . ' KWD.',
                'field' => 'advance_payment',
                'code' => 422,
            ];
        }

        // 7. Synchronize Actual Order Line Items (`orders_detail`)
        OrderDetail::where('order_id', $lockedOrder->id)->forceDelete();

        foreach ($customizedItems as $item) {
            $unitPrice = round((float) ($item['price'] ?? 0), 3);
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $lineTotal = round($unitPrice * $qty, 3);

            $cDetail = new OrderDetail();
            $cDetail->order_id = $lockedOrder->id;
            $cDetail->product_id = isset($item['product_id']) && !empty($item['product_id']) ? (int) $item['product_id'] : null;
            $cDetail->product_name = $item['product_title'] ?? ($item['description'] ?? 'Customized Product');
            $cDetail->product_price = $unitPrice;
            $cDetail->quantity = $qty;
            $cDetail->total_price = $lineTotal;
            $cDetail->order_status = $lockedOrder->order_status;
            $cDetail->is_active = 1;

            $attribs = [
                'color' => $item['color'] ?? '',
                'size' => $item['size'] ?? '',
                'item_code' => $item['item_code'] ?? '',
                'description' => $item['description'] ?? '',
                'image' => $item['image'] ?? '',
            ];
            $cDetail->custom_attributes = json_encode($attribs);
            $cDetail->save();
        }

        $lockedOrder->order_price = $subTotal;
        $lockedOrder->delivery_price = $shipping;
        $lockedOrder->discount = $discountAmount;
        $lockedOrder->discount_type = $discountType;
        $lockedOrder->discount_per = ($discountType === 'percentage') ? $discountVal : 0;
        $lockedOrder->order_total = $grandTotal;

        $pendingAmount = max(0.0, round($grandTotal - $totalPaid, 3));
        $excessCredit = max(0.0, round($totalPaid - $grandTotal, 3));

        $beforePaid = $lockedOrder->paid_amount;
        $beforePending = $lockedOrder->pending_amount;
        $lockedOrder->paid_amount = $totalPaid;
        $lockedOrder->pending_amount = $pendingAmount;
        if ($linkPaid > 0 || (int)$lockedOrder->payment_method === 4 || (int)$lockedOrder->payment_method === 6) {
            $lockedOrder->is_advance_payment_done = 1;
        } else {
            $lockedOrder->is_advance_payment_done = 0;
        }

        PaymentCalculationService::logPaymentDebug([
            'order_id' => $lockedOrder->id,
            'temporary_order_id' => $lockedTemp->id,
            'user_id' => $lockedOrder->user_id,
            'field' => 'paid_amount',
            'before' => $beforePaid,
            'request' => $lockedTemp->advance_price,
            'calculated' => $totalPaid,
            'written' => $lockedOrder->paid_amount,
            'source' => 'CustomizedOrderSyncService::syncDraftToActualOrder',
        ]);

        PaymentCalculationService::logPaymentDebug([
            'order_id' => $lockedOrder->id,
            'temporary_order_id' => $lockedTemp->id,
            'user_id' => $lockedOrder->user_id,
            'field' => 'pending_amount',
            'before' => $beforePending,
            'request' => $lockedTemp->pending_amount,
            'calculated' => $pendingAmount,
            'written' => $lockedOrder->pending_amount,
            'source' => 'CustomizedOrderSyncService::syncDraftToActualOrder',
        ]);

        // 8. Update Snapshot JSON in custom_order_details
        $orderCustomDetails['temporary_order_id'] = $lockedTemp->id;
        $orderCustomDetails['items'] = $customizedItems;
        $orderCustomDetails['shipping'] = $shipping;
        $orderCustomDetails['discount_type'] = $discountType;
        $orderCustomDetails['discount_val'] = $discountVal;
        $orderCustomDetails['discount_amount'] = $discountAmount;
        $orderCustomDetails['subtotal'] = $subTotal;
        $orderCustomDetails['grand_total'] = $grandTotal;
        $orderCustomDetails['paid_amount'] = $totalPaid;
        $orderCustomDetails['pending_amount'] = $pendingAmount;
        $orderCustomDetails['excess_credit_amount'] = $excessCredit;
        $orderCustomDetails['last_synchronized_at'] = now()->toDateTimeString();

        $lockedOrder->custom_order_details = json_encode($orderCustomDetails);
        $lockedOrder->save();

        // 9. Payment link invalidation: ONLY if payable balance or order total actually changed
        $totalChanged = (abs($previousOrderTotal - $grandTotal) > 0.0001);
        $pendingChanged = (abs($previousPendingAmount - $pendingAmount) > 0.0001);

        if ($totalChanged || $pendingChanged) {
            self::invalidatePendingPaymentLinks($lockedOrder->id, 'order_total_modified');
        }

        // 10. Update TemporaryOrder financial projection to match
        $lockedTemp->subtotal = $subTotal;
        $lockedTemp->shipping_price = $shipping;
        $lockedTemp->discount_type = $discountType;
        $lockedTemp->discount_val = $discountVal;
        $lockedTemp->discount_amount = $discountAmount;
        $lockedTemp->grand_total = $grandTotal;
        $lockedTemp->advance_price = $totalPaid;
        $lockedTemp->pending_amount = $pendingAmount;

        $tempCustomDetails['order_id'] = $lockedOrder->id;
        $tempCustomDetails['excess_credit_amount'] = $excessCredit;
        $tempCustomDetails['paid_amount'] = $totalPaid;
        $tempCustomDetails['pending_amount'] = $pendingAmount;
        if (isset($tempCustomDetails['payment'])) {
            $tempCustomDetails['payment']['paid'] = $totalPaid;
            $tempCustomDetails['payment']['pending'] = $pendingAmount;
            $tempCustomDetails['payment']['advance'] = $totalPaid;
        }
        if (isset($tempCustomDetails['calculation'])) {
            $tempCustomDetails['calculation']['paid'] = $totalPaid;
            $tempCustomDetails['calculation']['pending'] = $pendingAmount;
            $tempCustomDetails['calculation']['advance'] = $totalPaid;
        }
        $lockedTemp->custom_order_details = json_encode($tempCustomDetails);
        $lockedTemp->save();

        return [
            'status' => true,
            'message' => 'Order and draft synchronized successfully.',
            'data' => [
                'order_id' => $lockedOrder->id,
                'temporary_order_id' => $lockedTemp->id,
                'subtotal' => $subTotal,
                'shipping' => $shipping,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'excess_credit' => $excessCredit,
                'links_invalidated' => ($totalChanged || $pendingChanged),
            ]
        ];
    }

    /**
     * Synchronize Actual Order (when updated by Admin) back to TemporaryOrder draft.
     * Two-Way Synchronization support.
     *
     * @param Order|WebOrder $order
     * @return array
     */
    public static function syncActualOrderToDraft($order)
    {
        if (!$order || $order->order_type !== 'custom') {
            return ['status' => false, 'message' => 'Not a custom order.'];
        }

        $tempOrderId = null;
        $orderCustomDetails = is_array($order->custom_order_details) 
            ? $order->custom_order_details 
            : (json_decode($order->custom_order_details, true) ?: []);

        if (!empty($orderCustomDetails['temporary_order_id'])) {
            $tempOrderId = $orderCustomDetails['temporary_order_id'];
        }

        $tempOrder = null;
        if ($tempOrderId) {
            $tempOrder = TemporaryOrder::where('id', $tempOrderId)->where('user_id', $order->user_id)->first();
        }

        if (!$tempOrder) {
            return ['status' => false, 'message' => 'Linked temporary order draft not found.'];
        }

        // Lock both
        $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
        $lockedTemp = TemporaryOrder::where('id', $tempOrder->id)->lockForUpdate()->first();

        // Sync customer / address fields
        if (!empty($lockedOrder->cphone)) $lockedTemp->customer_phone = $lockedOrder->cphone;
        if (!empty($lockedOrder->email)) $lockedTemp->customer_email = $lockedOrder->email;
        if (!empty($lockedOrder->billing_address_id)) {
            $lockedTemp->customer_address_id = $lockedOrder->billing_address_id;
        }
        if (!empty($lockedOrder->custom_billing_country_id)) $lockedTemp->country_id = $lockedOrder->custom_billing_country_id;
        if (!empty($lockedOrder->custom_billing_city)) $lockedTemp->city = $lockedOrder->custom_billing_city;
        if (!empty($lockedOrder->custom_billing_block)) $lockedTemp->block = $lockedOrder->custom_billing_block;
        if (!empty($lockedOrder->custom_billing_street)) $lockedTemp->street = $lockedOrder->custom_billing_street;
        if (!empty($lockedOrder->custom_billing_house_number)) $lockedTemp->house_number = $lockedOrder->custom_billing_house_number;
        if (!empty($lockedOrder->custom_billing_floor)) $lockedTemp->floor = $lockedOrder->custom_billing_floor;
        if (!empty($lockedOrder->custom_billing_address_detail)) $lockedTemp->address_detail = $lockedOrder->custom_billing_address_detail;
        if (!empty($lockedOrder->custom_billing_address_url)) $lockedTemp->address_url = $lockedOrder->custom_billing_address_url;

        // Sync financial totals
        $lockedTemp->subtotal = round((float) ($lockedOrder->order_price ?? 0), 3);
        $lockedTemp->shipping_price = round((float) ($lockedOrder->delivery_price ?? 0), 3);
        $lockedTemp->discount_type = $lockedOrder->discount_type;
        $lockedTemp->discount_val = round((float) ($lockedOrder->discount_per ?? 0), 3);
        $lockedTemp->discount_amount = round((float) ($lockedOrder->discount ?? 0), 3);
        $lockedTemp->grand_total = round((float) ($lockedOrder->order_total ?? 0), 3);
        $actualPaid = round((float) ($lockedOrder->paid_amount ?? 0), 3);
        $lockedTemp->advance_price = $actualPaid;
        $lockedTemp->pending_amount = round((float) ($lockedOrder->pending_amount ?? 0), 3);
        $lockedTemp->status = TemporaryOrder::mapOrderStatusToTempStatus($lockedOrder->order_status);

        // Sync items to TemporaryOrderDetail
        TemporaryOrderDetail::where('temporary_order_id', $lockedTemp->id)->forceDelete();
        $actualDetails = OrderDetail::where('order_id', $lockedOrder->id)->get();
        foreach ($actualDetails as $sortIdx => $detail) {
            $attribs = is_array($detail->custom_attributes) ? $detail->custom_attributes : (json_decode($detail->custom_attributes, true) ?: []);
            TemporaryOrderDetail::create([
                'temporary_order_id' => $lockedTemp->id,
                'product_name' => $detail->product_name ?? '',
                'item_code' => $attribs['item_code'] ?? '',
                'description' => $attribs['description'] ?? '',
                'color' => $attribs['color'] ?? '',
                'size' => $attribs['size'] ?? '',
                'price' => $detail->product_price ?? 0,
                'quantity' => $detail->quantity ?? 1,
                'total_price' => $detail->total_price ?? 0,
                'image' => $attribs['image'] ?? '',
                'sort_order' => $sortIdx,
            ]);
        }

        $tempCustomDetails = is_array($lockedTemp->custom_order_details) 
            ? $lockedTemp->custom_order_details 
            : (json_decode($lockedTemp->custom_order_details, true) ?: []);
        $tempCustomDetails['order_id'] = $lockedOrder->id;
        $tempCustomDetails['paid_amount'] = $actualPaid;
        $tempCustomDetails['pending_amount'] = (float)$lockedOrder->pending_amount;
        if (isset($tempCustomDetails['payment'])) {
            $tempCustomDetails['payment']['paid'] = $actualPaid;
            $tempCustomDetails['payment']['pending'] = (float)$lockedOrder->pending_amount;
            $tempCustomDetails['payment']['advance'] = $actualPaid;
        }
        if (isset($tempCustomDetails['calculation'])) {
            $tempCustomDetails['calculation']['paid'] = $actualPaid;
            $tempCustomDetails['calculation']['pending'] = (float)$lockedOrder->pending_amount;
            $tempCustomDetails['calculation']['advance'] = $actualPaid;
        }
        $lockedTemp->custom_order_details = json_encode($tempCustomDetails);
        $lockedTemp->save();

        return [
            'status' => true,
            'message' => 'Draft synchronized from actual order successfully.',
            'temporary_order_id' => $lockedTemp->id,
        ];
    }
}

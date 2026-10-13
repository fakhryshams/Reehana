<?php

namespace App\Services;

use App\AdminModel\Order\PaymentLink;
use App\UserModel\Order;
use Illuminate\Support\Facades\DB;

class PaymentCalculationService
{
    /**
     * Get the total amount paid through successful payment links for an order.
     *
     * @param int|string $orderId
     * @return float
     */
    public static function getSuccessfulPaymentTotal($orderId)
    {
        if (empty($orderId)) {
            return 0.0;
        }

        $linkPaid = (float) PaymentLink::where('order_id', $orderId)
            ->where('status', 'paid')
            ->sum(DB::raw('COALESCE(paid_amount, amount)'));

        $order = Order::find($orderId);
        $orderPaid = 0.0;
        if ($order && !empty($order->is_advance_payment_done)) {
            $orderPaid = (float) ($order->paid_amount ?? 0);
        }

        return round(max($linkPaid, $orderPaid), 3);
    }

    /**
     * Standardized Debug Logger for Order/TemporaryOrder/PaymentLink writes.
     *
     * @param array $data
     * @return void
     */
    public static function logPaymentDebug(array $data)
    {
        $timestamp = now()->toDateTimeString();
        $orderId = $data['order_id'] ?? 'N/A';
        $tempOrderId = $data['temporary_order_id'] ?? 'N/A';
        $userId = auth()->id() ?? ($data['user_id'] ?? 'Guest/System');
        $field = $data['field'] ?? 'paid_amount';
        $before = isset($data['before']) ? number_format((float)$data['before'], 3, '.', '') : 'N/A';
        $requestVal = isset($data['request']) ? (is_numeric($data['request']) ? number_format((float)$data['request'], 3, '.', '') : $data['request']) : 'N/A';
        $calculated = isset($data['calculated']) ? number_format((float)$data['calculated'], 3, '.', '') : 'N/A';
        $written = isset($data['written']) ? number_format((float)$data['written'], 3, '.', '') : 'N/A';
        $source = $data['source'] ?? 'Unknown';

        $msg = "\n[PAYMENT DEBUG] [{$timestamp}]\n" .
               "Order ID: {$orderId} | Temp Order ID: {$tempOrderId} | User ID: {$userId}\n" .
               "Field: {$field}\n" .
               "BEFORE: {$before}\n" .
               "REQUEST: {$requestVal}\n" .
               "CALCULATED_FROM_LEDGER: {$calculated}\n" .
               "WRITTEN: {$written}\n" .
               "SOURCE: {$source}\n";

        \Log::info($msg);
    }

    /**
     * Calculate comprehensive payment summary for an order.
     * Single Source of Truth for order financials.
     *
     * @param Order|object $order
     * @return array
     */
    public static function calculateOrderPayments($order)
    {
        if (!$order) {
            return [
                'total_amount' => 0.0,
                'total_paid' => 0.0,
                'pending_amount' => 0.0,
                'payment_status' => 'unpaid',
                'is_fully_paid' => false,
                'is_partially_paid' => false,
                'paid_transactions_total' => 0.0,
            ];
        }

        $orderTotal = round((float) ($order->order_total ?? 0), 3);
        $paidTransactionsTotal = self::getSuccessfulPaymentTotal($order->id);

        // For customized purchase orders, confirmed paid amount MUST originate from the payment ledger (or in-store completion).
        // If not a customized order, account for legacy/direct paid_amount stored on order.
        $isCustomPO = ($order->order_type ?? '') === 'custom' || !empty($order->custom_order_type);
        if ($isCustomPO) {
            $totalPaid = $paidTransactionsTotal;
        } else {
            $directPaidAmount = round((float) ($order->paid_amount ?? 0), 3);
            $totalPaid = max($paidTransactionsTotal, $directPaidAmount);
        }

        $pendingAmount = max(0.0, round($orderTotal - $totalPaid, 3));
        $isFullyPaid = ($orderTotal > 0 && $pendingAmount <= 0.0001);
        $isPartiallyPaid = (!$isFullyPaid && $totalPaid > 0.0001);

        $paymentStatus = 'unpaid';
        if ($isFullyPaid) {
            $paymentStatus = 'paid';
        } elseif ($isPartiallyPaid) {
            $paymentStatus = 'partially_paid';
        }

        return [
            'total_amount' => $orderTotal,
            'total_paid' => $totalPaid,
            'paid_amount' => $totalPaid, // alias for backwards compatibility
            'pending_amount' => $pendingAmount,
            'payment_status' => $paymentStatus,
            'is_fully_paid' => $isFullyPaid,
            'is_partially_paid' => $isPartiallyPaid,
            'paid_transactions_total' => $paidTransactionsTotal,
        ];
    }

    /**
     * Validate whether a requested payment amount can be accepted for an order.
     *
     * @param Order|\App\AdminModel\Order\WebOrder|object $order
     * @param float|int|string $requestedAmount
     * @return array ['valid' => bool, 'message' => string, 'pending_amount' => float]
     */
    public static function validatePaymentAmount($order, $requestedAmount)
    {
        if (!$order) {
            return [
                'valid' => false,
                'message' => 'Order not found.',
                'pending_amount' => 0.0,
            ];
        }

        if (($order->custom_order_type ?? '') === 'quotation') {
            return [
                'valid' => false,
                'message' => 'Payments are not applicable to quotations.',
                'pending_amount' => 0.0,
            ];
        }

        $cancelStatus = config('custom_web.cancel', 5);
        if ($order->order_status == $cancelStatus) {
            return [
                'valid' => false,
                'message' => 'Cannot accept payment for a cancelled order.',
                'pending_amount' => 0.0,
            ];
        }

        $summary = self::calculateOrderPayments($order);
        $pendingAmount = $summary['pending_amount'];
        $amount = round((float) $requestedAmount, 3);

        if ($summary['is_fully_paid'] || $pendingAmount <= 0.0001) {
            return [
                'valid' => false,
                'message' => 'This order is already fully paid.',
                'pending_amount' => 0.0,
            ];
        }

        if ($amount <= 0) {
            return [
                'valid' => false,
                'message' => 'Payment amount must be greater than zero.',
                'pending_amount' => $pendingAmount,
            ];
        }

        // Allow a tiny epsilon (0.0001) for float rounding comparison
        if ($amount > round($pendingAmount + 0.0001, 3)) {
            return [
                'valid' => false,
                'message' => 'Payment amount (' . $amount . ') exceeds the current pending balance (' . $pendingAmount . ').',
                'pending_amount' => $pendingAmount,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid payment amount.',
            'pending_amount' => $pendingAmount,
        ];
    }

    /**
     * Recalculate and synchronize order model's paid_amount, pending_amount, and advance flag.
     *
     * @param Order|\App\AdminModel\Order\WebOrder|object $order
     * @param float|null $newPaidAmount
     * @return mixed
     */
    public static function syncOrderBalance($order, $newPaidAmount = null)
    {
        $summary = self::calculateOrderPayments($order);
        $totalPaid = $newPaidAmount !== null ? round((float) $newPaidAmount, 3) : $summary['total_paid'];
        $pendingAmount = max(0.0, round((float) $order->order_total - $totalPaid, 3));

        $order->paid_amount = $totalPaid;
        $order->pending_amount = $pendingAmount;

        if ($totalPaid > 0) {
            $order->is_advance_payment_done = 1;
        }

        $order->save();

        return $order;
    }

    /**
     * Check whether an advance payment has been successfully completed for an order.
     *
     * @param Order|\App\AdminModel\Order\WebOrder|int|string|null $order
     * @return bool
     */
    public static function isAdvancePaymentDone($order)
    {
        if (!$order) {
            return false;
        }

        if (is_numeric($order)) {
            $order = Order::find($order);
        }

        if (!$order) {
            return false;
        }

        // Check for confirmed paid payment link
        $hasPaidLink = PaymentLink::where('order_id', $order->id)
            ->where('status', 'paid')
            ->exists();

        if ($hasPaidLink) {
            return true;
        }

        // If an advance payment link was rejected and no successful payment exists
        $hasRejectedAdvance = PaymentLink::where('order_id', $order->id)
            ->where('is_advance_payment', 1)
            ->where('status', 'rejected')
            ->exists();

        if ($hasRejectedAdvance) {
            return false;
        }

        $paidTotal = self::getSuccessfulPaymentTotal($order->id);
        if ($paidTotal > 0.0001) {
            return true;
        }

        $isCustom = ($order->order_type ?? '') === 'custom' || !empty($order->custom_order_type) || !empty($order->custom_order_details);
        if ($isCustom) {
            return false;
        }

        // Direct flag on non-custom order (e.g. standard orders)
        if (!empty($order->is_advance_payment_done)) {
            return true;
        }

        return false;
    }

    /**
     * Build payment state descriptor for frontend and APIs.
     *
     * @param \App\UserModel\TemporaryOrder|null $tempOrder
     * @param Order|\App\AdminModel\Order\WebOrder|null $order
     * @return array
     */
    public static function getPaymentState($tempOrder = null, $order = null)
    {
        $linkedOrder = $order;
        if (!$linkedOrder && $tempOrder) {
            $linkedOrder = $tempOrder->getLinkedOrder();
        }

        $grandTotal = $tempOrder ? (float) $tempOrder->grand_total : ($linkedOrder ? (float) $linkedOrder->order_total : 0.0);
        $advanceVal = $tempOrder ? (float) ($tempOrder->advance_price ?? 0) : 0.0;
        $totalPaid = $linkedOrder ? self::getSuccessfulPaymentTotal($linkedOrder->id) : 0.0;
        $effectiveAdvance = max($advanceVal, $totalPaid);
        $pendingVal = max(0.0, round($grandTotal - $effectiveAdvance, 3));
        $isAdvanceDone = $linkedOrder ? self::isAdvancePaymentDone($linkedOrder) : false;

        $tempDetails = $tempOrder ? (is_array($tempOrder->custom_order_details) ? $tempOrder->custom_order_details : json_decode($tempOrder->custom_order_details, true)) : [];
        $generatePendingRequested = !empty($tempDetails['payment']['generate_pending_link']) || !empty($tempDetails['generate_pending_link']);

        $isAdvanceRequired = ($advanceVal > 0.0001);
        $canGeneratePendingLink = ($pendingVal > 0.0001) && (!$isAdvanceRequired || $isAdvanceDone);

        return [
            'is_existing_order' => $linkedOrder ? true : false,
            'order_id' => $linkedOrder ? $linkedOrder->id : null,
            'advance_required' => $isAdvanceRequired,
            'advance_completed' => $isAdvanceDone,
            'advance_amount' => $effectiveAdvance,
            'paid_amount' => $totalPaid,
            'pending_amount' => $pendingVal,
            'can_generate_pending_link' => $canGeneratePendingLink,
            'generate_pending_link_requested' => $generatePendingRequested && $canGeneratePendingLink,
        ];
    }

    /**
     * Calculate financial summary for customized order items, shipping, and discount.
     *
     * @param float $subTotal
     * @param float $shipping
     * @param string|null $discountType ('percentage' | 'flat' | null)
     * @param float $discountVal
     * @return array
     */
    public static function calculateCustomizedOrderFinancials($subTotal, $shipping = 0.0, $discountType = null, $discountVal = 0.0)
    {
        $subTotal = max(0.0, round((float) $subTotal, 3));
        $shipping = max(0.0, round((float) $shipping, 3));
        $discountVal = max(0.0, round((float) $discountVal, 3));
        $discountAmount = 0.0;

        if ($discountType === 'percentage') {
            $cappedPercent = min(100.0, max(0.0, $discountVal));
            $discountAmount = round(($subTotal * $cappedPercent) / 100, 3);
        } elseif ($discountType === 'flat') {
            $discountAmount = round(min($subTotal, $discountVal), 3);
        }

        $discountAmount = min($discountAmount, $subTotal + $shipping);
        $grandTotal = max(0.0, round($subTotal + $shipping - $discountAmount, 3));

        return [
            'subtotal' => $subTotal,
            'shipping' => $shipping,
            'discount_type' => $discountType,
            'discount_val' => $discountVal,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Single Source of Truth for Customized Order Payable Grand Total.
     *
     * Business Rules:
     * - CREATE MODE:
     *   - If Advance Payment > 0: Payable amount = min(Advance, Grand Total)
     *   - If Advance Payment = 0: Payable amount = 0 (Must NOT become payable merely because total > 0)
     *   - Generate Pending Link is NOT applicable.
     * - EDIT MODE:
     *   - If Generate Pending Amount Payment Link is CHECKED:
     *     - Payable amount = Pending Amount (max(0, Grand Total - Paid Amount))
     *   - If Generate Pending Amount Payment Link is UNCHECKED:
     *     - If Advance Payment has already been paid: Payable amount = 0
     *     - If Advance Payment was NOT paid yet:
     *       - If Advance Payment > 0: Payable amount = min(Advance, Grand Total)
     *       - If Advance Payment = 0: Payable amount = 0 (MUST NOT be added to payable Grand Total)
     *
     * @param bool $isCreateMode
     * @param float $customGrandTotal
     * @param float $advancePrice
     * @param float $paidAmount
     * @param bool $generatePendingRequested
     * @return float
     */
    public static function calculateCustomizedPayableAmount($isCreateMode, $customGrandTotal, $advancePrice = 0.0, $paidAmount = 0.0, $generatePendingRequested = false)
    {
        $customGrandTotal = max(0.0, round((float) $customGrandTotal, 3));
        $advancePrice = max(0.0, round((float) $advancePrice, 3));
        $paidAmount = max(0.0, round((float) $paidAmount, 3));
        $pendingAmount = max(0.0, round($customGrandTotal - $paidAmount, 3));

        if ($isCreateMode) {
            return ($advancePrice > 0.0001) ? min($advancePrice, $customGrandTotal) : 0.0;
        }

        if ($generatePendingRequested) {
            return $pendingAmount;
        }

        if ($paidAmount > 0.0001) {
            return 0.0;
        }

        return ($advancePrice > 0.0001) ? min($advancePrice, $customGrandTotal) : 0.0;
    }

    /**
     * Resolve complete customized order financial and payment projection from session or array.
     *
     * @param array|null $customOrder
     * @return array
     */
    public static function resolveCustomizedOrderPayableFromSession($customOrder)
    {
        if (empty($customOrder) || !is_array($customOrder)) {
            return [
                'has_custom_order' => false,
                'is_create_mode' => true,
                'subtotal' => 0.0,
                'shipping' => 0.0,
                'discount_type' => null,
                'discount_val' => 0.0,
                'discount_amount' => 0.0,
                'grand_total' => 0.0,
                'advance_price' => 0.0,
                'paid_amount' => 0.0,
                'pending_amount' => 0.0,
                'payable_amount' => 0.0,
                'generate_pending_requested' => false,
                'is_advance_done' => false,
                'temporary_order_id' => null,
                'order_id' => null,
                'type' => null,
            ];
        }

        $items = $customOrder['items'] ?? [];
        $subTotal = 0.0;
        if (is_array($items)) {
            foreach ($items as $item) {
                $subTotal += (float) ($item['total'] ?? 0);
            }
        }

        $shipping = (float) ($customOrder['shipping'] ?? ($customOrder['payment']['shipping'] ?? ($customOrder['calculation']['shipping'] ?? 0)));
        $discountType = $customOrder['discount_type'] ?? ($customOrder['payment']['discount_type'] ?? ($customOrder['calculation']['discount_type'] ?? null));
        $discountVal = (float) ($customOrder['discount_val'] ?? ($customOrder['calculation']['discount_value'] ?? 0));

        $financials = self::calculateCustomizedOrderFinancials($subTotal, $shipping, $discountType, $discountVal);
        $grandTotal = $financials['grand_total'];

        $tempDraftId = $customOrder['temporary_order_id'] ?? null;
        $orderId = $customOrder['order_id'] ?? null;
        $existingLinkedOrder = null;

        if ($tempDraftId) {
            $tmpModel = \App\UserModel\TemporaryOrder::find($tempDraftId);
            if ($tmpModel) {
                $existingLinkedOrder = $tmpModel->getLinkedOrder();
            }
        }
        if (!$existingLinkedOrder && $orderId) {
            $existingLinkedOrder = Order::find($orderId);
        }

        $isCreateMode = empty($tempDraftId) && empty($existingLinkedOrder);
        $reqType = $customOrder['type'] ?? 'purchase_order';

        $paidAmount = 0.0;
        $isAdvanceDone = false;
        if ($existingLinkedOrder) {
            $paidAmount = self::getSuccessfulPaymentTotal($existingLinkedOrder->id);
            $isAdvanceDone = self::isAdvancePaymentDone($existingLinkedOrder);
        }

        $advanceInput = 0.0;
        if (isset($customOrder['payment']['advance'])) {
            $advanceInput = (float) str_replace(',', '', (string) $customOrder['payment']['advance']);
        } elseif (isset($customOrder['calculation']['advance'])) {
            $advanceInput = (float) $customOrder['calculation']['advance'];
        } elseif (isset($customOrder['advance_price'])) {
            $advanceInput = (float) $customOrder['advance_price'];
        }

        $effectiveAdvance = ($paidAmount > 0) ? $paidAmount : $advanceInput;
        $pendingAmount = max(0.0, round($grandTotal - $effectiveAdvance, 3));

        $generatePendingReq = !empty($customOrder['payment']['generate_pending_link']) || !empty($customOrder['generate_pending_link']);
        $generatePendingAllowed = (!$isCreateMode && $reqType !== 'quotation' && $generatePendingReq && $pendingAmount > 0.0001);

        $payableAmount = 0.0;
        if ($reqType === 'quotation') {
            $payableAmount = 0.0;
        } else {
            $payableAmount = self::calculateCustomizedPayableAmount(
                $isCreateMode,
                $grandTotal,
                $effectiveAdvance,
                $paidAmount,
                $generatePendingAllowed
            );
        }

        return [
            'has_custom_order' => !empty($items),
            'is_create_mode' => $isCreateMode,
            'subtotal' => $financials['subtotal'],
            'shipping' => $financials['shipping'],
            'discount_type' => $financials['discount_type'],
            'discount_val' => $financials['discount_val'],
            'discount_amount' => $financials['discount_amount'],
            'grand_total' => $grandTotal,
            'advance_price' => $effectiveAdvance,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'payable_amount' => $payableAmount,
            'generate_pending_requested' => $generatePendingAllowed,
            'is_advance_done' => $isAdvanceDone,
            'temporary_order_id' => $tempDraftId,
            'order_id' => $existingLinkedOrder ? $existingLinkedOrder->id : $orderId,
            'type' => $reqType,
        ];
    }
}


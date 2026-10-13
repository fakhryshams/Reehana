<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AdminModel\Order\PaymentLink;
use App\AdminModel\Order\WebOrder;
use App\Services\PaymentCalculationService;
use Illuminate\Support\Facades\DB;

auth()->loginUsingId(1);

echo "=== STARTING PARTIAL PAYMENTS TEST SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function testAssert($condition, $description) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] " . $description . "\n";
        $passCount++;
    } else {
        echo "[FAIL] " . $description . "\n";
        $failCount++;
    }
}

DB::beginTransaction();

try {
    // 1. Create a dummy purchase order with total = 10.000 KWD
    $po = new WebOrder();
    $po->user_id = 1;
    $po->order_price = 10.000;
    $po->order_total = 10.000;
    $po->paid_amount = 0;
    $po->pending_amount = 10.000;
    $po->custom_order_type = 'purchase_order';
    $po->order_type = 'custom';
    $po->order_status = config('custom_web.Pending');
    $po->is_advance_payment_done = 0;
    $po->save();

    $orderId = $po->id;

    // Test 1: Initial state calculation
    $calc0 = PaymentCalculationService::calculateOrderPayments($po);
    testAssert($calc0['paid_amount'] === 0.0 && $calc0['pending_amount'] === 10.0 && !$calc0['is_fully_paid'], 'Test 1: Initial PO with 10 KWD total has 0 paid and 10 pending');

    // Test 2: Validation on positive amount <= pending
    $val1 = PaymentCalculationService::validatePaymentAmount($po, 2.000);
    testAssert($val1['valid'] === true, 'Test 2: Validation allows 2.000 KWD payment on 10.000 KWD pending');

    // Test 3: Validation on negative amount
    $valNeg = PaymentCalculationService::validatePaymentAmount($po, -5.000);
    testAssert($valNeg['valid'] === false, 'Test 3: Validation rejects negative payment amount');

    // Test 4: Validation on zero amount
    $valZero = PaymentCalculationService::validatePaymentAmount($po, 0);
    testAssert($valZero['valid'] === false, 'Test 4: Validation rejects zero payment amount');

    // Test 5: Validation on overpayment (> 10 KWD)
    $valOver = PaymentCalculationService::validatePaymentAmount($po, 12.000);
    testAssert($valOver['valid'] === false, 'Test 5: Validation rejects overpayment (12.000 KWD on 10.000 KWD pending)');

    // Test 6: Record First Payment (2 KWD - Pay At Shop / Manual)
    $link1 = PaymentLink::create([
        'order_id' => $orderId,
        'payment_link' => '',
        'payment_request_id' => 'test_req_1',
        'amount' => 2.000,
        'paid_amount' => 2.000,
        'paid_at' => now(),
        'status' => 'paid',
        'payment_type' => 6, // Pay at shop
        'is_advance_payment' => 1,
        'gateway_response' => ['source' => 'manual_admin', 'reference_no' => 'REF001']
    ]);

    PaymentCalculationService::syncOrderBalance($po);
    $po->refresh();

    $calc1 = PaymentCalculationService::calculateOrderPayments($po);
    testAssert($calc1['paid_amount'] === 2.0 && $calc1['pending_amount'] === 8.0 && !$calc1['is_fully_paid'], 'Test 6: After 2 KWD payment, paid is 2.0 and pending is 8.0 KWD');
    testAssert((float)$po->paid_amount === 2.0 && (float)$po->pending_amount === 8.0 && (int)$po->is_advance_payment_done === 1, 'Test 7: Order model synced with paid_amount=2.0, pending_amount=8.0, is_advance_payment_done=1');

    // Test 8: Validation on overpayment against remaining pending (e.g. 8.500 KWD when 8.000 pending)
    $valOver2 = PaymentCalculationService::validatePaymentAmount($po, 8.500);
    testAssert($valOver2['valid'] === false, 'Test 8: Validation rejects 8.500 KWD payment when remaining pending is 8.000 KWD');

    // Test 9: Online Payment Link Creation (pending status, 8.000 KWD)
    $link2 = PaymentLink::create([
        'order_id' => $orderId,
        'payment_link' => 'https://gateway.example.com/pay/test_req_2',
        'payment_request_id' => 'test_req_2',
        'amount' => 8.000,
        'status' => 'pending',
        'payment_type' => 1,
        'is_advance_payment' => 0
    ]);

    // Check that pending link does NOT alter paid_amount until marked paid
    $calcPending = PaymentCalculationService::calculateOrderPayments($po);
    testAssert($calcPending['paid_amount'] === 2.0 && $calcPending['pending_amount'] === 8.0, 'Test 9: Pending payment link does not increase paid_amount');

    // Test 10: Successful Payment Completion (Online 8.000 KWD)
    $link2->status = 'paid';
    $link2->paid_amount = 8.000;
    $link2->paid_at = now();
    $link2->save();

    PaymentCalculationService::syncOrderBalance($po);
    $po->refresh();

    $calc2 = PaymentCalculationService::calculateOrderPayments($po);
    testAssert($calc2['paid_amount'] === 10.0 && $calc2['pending_amount'] === 0.0 && $calc2['is_fully_paid'], 'Test 10: After second payment of 8.0 KWD, paid is 10.0 and pending is 0.0 (fully paid)');
    testAssert((float)$po->paid_amount === 10.0 && (float)$po->pending_amount === 0.0, 'Test 11: Order model reflects fully paid state (paid_amount=10.0, pending_amount=0.0)');

    // Test 12: Validation on fully paid order rejects further payments
    $valFull = PaymentCalculationService::validatePaymentAmount($po, 1.000);
    testAssert($valFull['valid'] === false, 'Test 12: Validation rejects payment attempt on fully paid order');

    // Test 13: Multiple N arbitrary partial payments (New PO: 15 KWD total, paid in 4 parts: 3.5, 4.0, 2.5, 5.0)
    $po2 = new WebOrder();
    $po2->user_id = 1;
    $po2->order_price = 15.000;
    $po2->order_total = 15.000;
    $po2->paid_amount = 0;
    $po2->pending_amount = 15.000;
    $po2->custom_order_type = 'purchase_order';
    $po2->order_type = 'custom';
    $po2->order_status = config('custom_web.Pending');
    $po2->is_advance_payment_done = 0;
    $po2->save();

    $parts = [3.500, 4.000, 2.500, 5.000];
    $accum = 0;
    foreach ($parts as $idx => $part) {
        $accum += $part;
        PaymentLink::create([
            'order_id' => $po2->id,
            'payment_link' => '',
            'payment_request_id' => 'test_multi_' . $idx,
            'amount' => $part,
            'paid_amount' => $part,
            'paid_at' => now(),
            'status' => 'paid',
            'payment_type' => 1,
            'is_advance_payment' => $idx === 0 ? 1 : 0
        ]);
        PaymentCalculationService::syncOrderBalance($po2);
        $po2->refresh();
        $calcN = PaymentCalculationService::calculateOrderPayments($po2);
        testAssert($calcN['paid_amount'] === round($accum, 3) && $calcN['pending_amount'] === round(15.0 - $accum, 3), "Test 13." . ($idx+1) . ": Multi-payment step {$idx}: paid={$accum}, pending=" . (15.0 - $accum));
    }
    testAssert($po2->pending_amount == 0 && $po2->paid_amount == 15.0, 'Test 14: Arbitrary N partial payments reach exact zero balance without floating point drift');

    // Test 15: Quotation Isolation
    $qo = new WebOrder();
    $qo->user_id = 1;
    $qo->order_price = 20.000;
    $qo->order_total = 20.000;
    $qo->custom_order_type = 'quotation';
    $qo->order_type = 'custom';
    $qo->order_status = config('custom_web.Pending');
    $qo->save();

    $valQo = PaymentCalculationService::validatePaymentAmount($qo, 5.000);
    testAssert($valQo['valid'] === false, 'Test 15: Quotations are isolated and reject payment processing');

    // Test 16: Cancelled Order Isolation
    $cancelledPo = new WebOrder();
    $cancelledPo->user_id = 1;
    $cancelledPo->order_price = 10.000;
    $cancelledPo->order_total = 10.000;
    $cancelledPo->custom_order_type = 'purchase_order';
    $cancelledPo->order_type = 'custom';
    $cancelledPo->order_status = config('custom_web.cancel');
    $cancelledPo->save();

    $valCancelled = PaymentCalculationService::validatePaymentAmount($cancelledPo, 5.000);
    testAssert($valCancelled['valid'] === false, 'Test 16: Cancelled POs reject payment attempts');

} finally {
    DB::rollBack();
}

echo "\n=== SUMMARY: {$passCount} PASSED, {$failCount} FAILED ===\n";

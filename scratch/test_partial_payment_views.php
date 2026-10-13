<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\AdminModel\Order\PaymentLink;
use App\AdminModel\Order\WebOrder;
use App\Services\PaymentCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

auth()->loginUsingId(1);

echo "=== TESTING PARTIAL PAYMENT VIEWS RENDERING ===\n\n";

DB::beginTransaction();

try {
    $po = new WebOrder();
    $po->user_id = 1;
    $po->order_price = 10.000;
    $po->order_total = 10.000;
    $po->paid_amount = 2.000;
    $po->pending_amount = 8.000;
    $po->custom_order_type = 'purchase_order';
    $po->order_type = 'custom';
    $po->order_status = config('custom_web.Pending');
    $po->is_advance_payment_done = 1;
    $po->payment_method = 1;
    $po->custom_order_details = json_encode([
        'customer' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '12345678',
            'city' => 'Kuwait City',
            'block' => '1',
            'street' => 'Street 10',
            'house_number' => '5',
        ],
        'items' => [
            [
                'product_title' => 'Custom Item 1',
                'description' => 'Test Description',
                'price' => 10.000,
                'quantity' => 1,
                'total' => 10.000,
                'color' => 'Blue',
                'size' => 'L',
                'item_code' => 'ITM-001'
            ]
        ]
    ]);
    $po->save();

    // Create payment link
    $pl = PaymentLink::create([
        'order_id' => $po->id,
        'payment_link' => 'https://gateway.example.com/pay/test_token',
        'payment_request_id' => 'test_token_123',
        'amount' => 8.000,
        'status' => 'pending',
        'payment_type' => 1,
        'is_advance_payment' => 0
    ]);

    // Test View 1: customized_order_summary_print
    echo "Rendering customized_order_summary_print...\n";
    $html1 = View::make('web.checkout.customized_order_summary_print', [
        'order' => $po,
        'orderProductDetail' => [],
        'paymentLink' => $pl,
    ])->render();
    echo "[PASS] customized_order_summary_print rendered (" . strlen($html1) . " bytes)\n";

    // Test View 2: order_detail_customized
    echo "Rendering order_detail_customized...\n";
    $html2 = View::make('web.user.order_detail_customized', [
        'order' => $po,
        'orderProductDetail' => [],
        'paymentLinks' => $pl,
    ])->render();
    echo "[PASS] order_detail_customized rendered (" . strlen($html2) . " bytes)\n";

    // Test View 3: payment_review_customized
    echo "Rendering payment_review_customized...\n";
    $html3 = View::make('web.checkout.payment_review_customized', [
        'order' => $po,
        'paymentLink' => $pl,
        'orderProductDetail' => [],
    ])->render();
    echo "[PASS] payment_review_customized rendered (" . strlen($html3) . " bytes)\n";

    echo "\n=== ALL VIEWS RENDERED SUCCESSFULLY ===\n";

} finally {
    DB::rollBack();
}

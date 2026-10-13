<?php

namespace Tests\Feature;

use App\AdminModel\Order\PaymentLink;
use App\AdminModel\Order\WebOrder;
use App\Http\Controllers\Web\Checkout\CheckoutController;
use App\Services\CustomizedOrderSyncService;
use App\Services\PaymentCalculationService;
use App\User;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use App\UserModel\TemporaryOrder;
use App\UserModel\TemporaryOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomizedOrderProductionAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function createTestUser($prefix = 'audit_user_')
    {
        $user = new User();
        $user->first_name = 'Test';
        $user->last_name = 'Customer';
        $user->email = $prefix . uniqid() . '@example.com';
        $user->password = bcrypt('secret123');
        $user->mobile = '965' . rand(10000000, 99999999);
        $user->is_active = 1;
        $user->save();
        return $user;
    }

    /** @test */
    public function test_01_save_new_customized_draft_creates_pending_temporary_order()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->customer_name = 'Test Customer';
        $temp->customer_phone = $user->mobile;
        $temp->subtotal = 10.000;
        $temp->shipping_price = 2.000;
        $temp->grand_total = 12.000;
        $temp->pending_amount = 12.000;
        $temp->save();

        $this->assertDatabaseHas('temporary_orders', [
            'id' => $temp->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'grand_total' => 12.000,
        ]);
    }

    /** @test */
    public function test_02_edit_unsaved_draft_updates_existing_record_without_duplication()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        $initialCount = TemporaryOrder::where('user_id', $user->id)->count();

        // Update existing draft
        $temp->subtotal = 15.000;
        $temp->grand_total = 15.000;
        $temp->save();

        $newCount = TemporaryOrder::where('user_id', $user->id)->count();

        $this->assertEquals($initialCount, $newCount);
        $this->assertEquals(15.000, (float) $temp->fresh()->grand_total);
    }

    /** @test */
    public function test_03_place_order_keeps_temporary_order_pending_and_links_actual_order()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->custom_order_type = 'purchase_order';
        $order->order_status = 7; // Pending
        $order->order_price = 10.000;
        $order->order_total = 10.000;
        $order->paid_amount = 0.000;
        $order->pending_amount = 10.000;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $tempCustomDetails = ['order_id' => $order->id];
        $temp->custom_order_details = json_encode($tempCustomDetails);
        $temp->save();

        $this->assertEquals('pending', $temp->fresh()->status);
        $this->assertEquals(7, $order->fresh()->order_status);
        $this->assertEquals($order->id, $temp->getLinkedOrder()->id);
    }

    /** @test */
    public function test_04_advance_payment_2_on_total_10_yields_paid_2_and_pending_8()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->save();

        $link = new PaymentLink();
        $link->order_id = $order->id;
        $link->payment_request_id = 'PR_ADV_01_' . uniqid();
        $link->amount = 2.000;
        $link->paid_amount = 2.000;
        $link->status = 'paid';
        $link->save();

        $calc = PaymentCalculationService::calculateOrderPayments($order);
        $this->assertEquals(2.000, $calc['total_paid']);
        $this->assertEquals(8.000, $calc['pending_amount']);
    }

    /** @test */
    public function test_05_edit_order_total_10_to_15_with_2_paid_yields_pending_13()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->grand_total = 10.000;
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // 2 KWD paid
        $link = new PaymentLink();
        $link->order_id = $order->id;
        $link->payment_request_id = 'PR_05_' . uniqid();
        $link->amount = 2.000;
        $link->paid_amount = 2.000;
        $link->status = 'paid';
        $link->save();

        // Customer edits items: new total = 15 KWD
        $items = [
            [
                'product_title' => 'Custom Item A',
                'price' => 15.000,
                'quantity' => 1,
                'total' => 15.000,
            ]
        ];

        $res = CustomizedOrderSyncService::syncDraftToActualOrder(
            $temp,
            $order,
            $items,
            ['shipping' => 0, 'discount_amount' => 0]
        );

        $this->assertTrue($res['status']);
        $this->assertEquals(15.000, $res['data']['grand_total']);
        $this->assertEquals(2.000, $res['data']['paid_amount']);
        $this->assertEquals(13.000, $res['data']['pending_amount']);
        $this->assertEquals(0.000, $res['data']['excess_credit']);

        // Historical payment of 2 KWD must remain untouched in ledger
        $this->assertEquals(2.000, (float) $link->fresh()->paid_amount);
        $this->assertEquals('paid', $link->fresh()->status);
    }

    /** @test */
    public function test_06_stale_payment_link_paid_after_order_increased_is_credited_properly()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // 1. Initial 2 KWD advance
        $linkAdv = new PaymentLink();
        $linkAdv->order_id = $order->id;
        $linkAdv->payment_request_id = 'PR_ADV_' . uniqid();
        $linkAdv->amount = 2.000;
        $linkAdv->paid_amount = 2.000;
        $linkAdv->status = 'paid';
        $linkAdv->save();

        // 2. Pending 8 KWD payment link generated
        $linkPending = new PaymentLink();
        $linkPending->order_id = $order->id;
        $linkPending->payment_request_id = 'PR_STALE_8_' . uniqid();
        $linkPending->amount = 8.000;
        $linkPending->status = 'pending';
        $linkPending->save();

        // 3. Order is edited to 15 KWD -> linkPending is invalidated to 'rejected'
        $items = [
            ['product_title' => 'Custom Item B', 'price' => 15.000, 'quantity' => 1, 'total' => 15.000]
        ];
        CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, $items, []);

        $this->assertEquals('rejected', $linkPending->fresh()->status);

        // 4. In-flight gateway callback completes for the 8 KWD link
        $checkoutController = new CheckoutController();
        $fakeRequest = new Request([
            'requested_order_id' => $order->id,
            'trn_udf' => json_encode(['payment_request_id' => $linkPending->payment_request_id]),
            'Result' => 'Captured',
        ]);

        $checkoutController->paymentSuccess($fakeRequest);

        // Link should now be recorded as 'paid'
        $this->assertEquals('paid', $linkPending->fresh()->status);
        $this->assertEquals(8.000, (float) $linkPending->fresh()->paid_amount);

        // Live calculation must now be 10 Paid (2 + 8) and 5 Pending (15 - 10)
        $calc = PaymentCalculationService::calculateOrderPayments($order->fresh());
        $this->assertEquals(10.000, $calc['total_paid']);
        $this->assertEquals(5.000, $calc['pending_amount']);
    }

    /** @test */
    public function test_07_pay_remaining_5_yields_15_paid_and_0_pending()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 15.000;
        $order->order_status = 7;
        $order->save();

        // Link 1: 2 KWD
        $l1 = new PaymentLink();
        $l1->order_id = $order->id;
        $l1->payment_request_id = 'PR_7_1_' . uniqid();
        $l1->amount = 2.000;
        $l1->paid_amount = 2.000;
        $l1->status = 'paid';
        $l1->save();

        // Link 2: 8 KWD
        $l2 = new PaymentLink();
        $l2->order_id = $order->id;
        $l2->payment_request_id = 'PR_7_2_' . uniqid();
        $l2->amount = 8.000;
        $l2->paid_amount = 8.000;
        $l2->status = 'paid';
        $l2->save();

        // Link 3: 5 KWD
        $l3 = new PaymentLink();
        $l3->order_id = $order->id;
        $l3->payment_request_id = 'PR_7_3_' . uniqid();
        $l3->amount = 5.000;
        $l3->paid_amount = 5.000;
        $l3->status = 'paid';
        $l3->save();

        $calc = PaymentCalculationService::calculateOrderPayments($order);
        $this->assertEquals(15.000, $calc['total_paid']);
        $this->assertEquals(0.000, $calc['pending_amount']);
        $this->assertTrue($calc['is_fully_paid']);
    }

    /** @test */
    public function test_08_edit_order_total_10_to_7_with_2_paid_yields_pending_5()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // 2 KWD paid
        $l = new PaymentLink();
        $l->order_id = $order->id;
        $l->payment_request_id = 'PR_8_' . uniqid();
        $l->amount = 2.000;
        $l->paid_amount = 2.000;
        $l->status = 'paid';
        $l->save();

        // Edit down to 7 KWD
        $items = [
            ['product_title' => 'Reduced Item', 'price' => 7.000, 'quantity' => 1, 'total' => 7.000]
        ];
        $res = CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, $items, []);

        $this->assertTrue($res['status']);
        $this->assertEquals(7.000, $res['data']['grand_total']);
        $this->assertEquals(2.000, $res['data']['paid_amount']);
        $this->assertEquals(5.000, $res['data']['pending_amount']);
        $this->assertEquals(0.000, $res['data']['excess_credit']);
    }

    /** @test */
    public function test_09_edit_order_total_10_to_1_with_2_paid_yields_excess_credit_1()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // 2 KWD paid
        $l = new PaymentLink();
        $l->order_id = $order->id;
        $l->payment_request_id = 'PR_9_' . uniqid();
        $l->amount = 2.000;
        $l->paid_amount = 2.000;
        $l->status = 'paid';
        $l->save();

        // Edit down to 1 KWD when 2 KWD is already paid -> MUST BE REJECTED with 422
        $items = [
            ['product_title' => 'Tiny Item', 'price' => 1.000, 'quantity' => 1, 'total' => 1.000]
        ];
        $res = CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, $items, []);

        $this->assertFalse($res['status']);
        $this->assertEquals(422, $res['code']);
        $this->assertStringContainsString('Order total cannot be less than the paid advance amount of 2.000 KWD', $res['message']);
    }

    /** @test */
    public function test_10_duplicate_gateway_callback_is_strictly_idempotent()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 10.000;
        $order->order_status = 7;
        $order->save();

        $link = new PaymentLink();
        $link->order_id = $order->id;
        $link->payment_request_id = 'PR_DUP_' . uniqid();
        $link->amount = 4.000;
        $link->status = 'pending';
        $link->save();

        $controller = new CheckoutController();
        $fakeReq = new Request([
            'requested_order_id' => $order->id,
            'trn_udf' => json_encode(['payment_request_id' => $link->payment_request_id]),
            'Result' => 'Captured',
        ]);

        // First callback
        $controller->paymentSuccess($fakeReq);
        $this->assertEquals(4.000, (float) $order->fresh()->paid_amount);

        // Second duplicate callback
        $controller->paymentSuccess($fakeReq);
        $this->assertEquals(4.000, (float) $order->fresh()->paid_amount);
        $this->assertEquals(1, PaymentLink::where('payment_request_id', $link->payment_request_id)->count());
    }

    /** @test */
    public function test_11_cross_user_modification_is_blocked_by_idor_protection()
    {
        $userA = $this->createTestUser('userA_');
        $userB = $this->createTestUser('userB_');

        // User A owns draft and order
        $tempA = new TemporaryOrder();
        $tempA->user_id = $userA->id;
        $tempA->status = 'pending';
        $tempA->save();

        $orderA = new Order();
        $orderA->user_id = $userA->id;
        $orderA->order_type = 'custom';
        $orderA->order_status = 7;
        $orderA->custom_order_details = json_encode(['temporary_order_id' => $tempA->id]);
        $orderA->save();

        $tempA->custom_order_details = json_encode(['order_id' => $orderA->id]);
        $tempA->save();

        // User B logs in and attempts to modify User A's order
        Auth::login($userB);

        $res = CustomizedOrderSyncService::syncDraftToActualOrder($tempA, $orderA, [
            ['price' => 20, 'quantity' => 1]
        ], []);

        $this->assertFalse($res['status']);
        $this->assertEquals(403, $res['code']);
    }

    /** @test */
    public function test_12_customer_direct_edit_blocked_after_order_status_is_process()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'processing';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 1; // Process
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        $res = CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, [
            ['price' => 50, 'quantity' => 1]
        ], []);

        $this->assertFalse($res['status']);
        $this->assertEquals(422, $res['code']);
    }

    /** @test */
    public function test_13_status_transitions_mirror_actual_order_to_temporary_order()
    {
        $user = $this->createTestUser();
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new WebOrder();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        // 7 -> 1 (Process)
        $order->order_status = 1;
        $order->save();
        $this->assertEquals('processing', $temp->fresh()->status);

        // 1 -> 2 (Ready to Ship)
        $order->order_status = 2;
        $order->save();
        $this->assertEquals('ready_to_ship', $temp->fresh()->status);

        // 2 -> 3 (Shipped)
        $order->order_status = 3;
        $order->save();
        $this->assertEquals('shipped', $temp->fresh()->status);

        // 3 -> 4 (Delivered)
        $order->order_status = 4;
        $order->save();
        $this->assertEquals('completed', $temp->fresh()->status);

        // -> 5 (Cancelled)
        $order->order_status = 5;
        $order->save();
        $this->assertEquals('cancelled', $temp->fresh()->status);
    }

    /** @test */
    public function test_14_address_or_phone_only_edit_preserves_valid_pending_payment_link()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->customer_phone = '96511111111';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 7;
        $order->order_total = 10.000;
        $order->pending_amount = 10.000;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // Active pending payment link for 10 KWD
        $link = new PaymentLink();
        $link->order_id = $order->id;
        $link->payment_request_id = 'PR_ADDR_' . uniqid();
        $link->amount = 10.000;
        $link->status = 'pending';
        $link->save();

        // Customer edits ONLY phone / address without changing items/total
        $temp->customer_phone = '96599999999';
        $temp->city = 'Hawalli';
        $temp->save();

        $items = [
            ['product_title' => 'Same Item', 'price' => 10.000, 'quantity' => 1, 'total' => 10.000]
        ];

        $res = CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, $items, []);

        $this->assertTrue($res['status']);
        $this->assertFalse($res['data']['links_invalidated']);
        $this->assertEquals('pending', $link->fresh()->status);
        $this->assertEquals('96599999999', $order->fresh()->cphone);
    }

    /** @test */
    public function test_15_stale_request_optimistic_concurrency_is_detected()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        $staleToken = '2020-01-01 00:00:00'; // Old stale timestamp

        $res = CustomizedOrderSyncService::syncDraftToActualOrder(
            $temp,
            $order,
            [['price' => 10, 'quantity' => 1]],
            [],
            $staleToken
        );

        $this->assertFalse($res['status']);
        $this->assertEquals(409, $res['code']);
    }

    /** @test */
    public function test_16_actual_to_temporary_two_way_synchronization()
    {
        $user = $this->createTestUser();
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        $order = new WebOrder();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 7;
        $order->order_price = 25.000;
        $order->order_total = 25.000;
        $order->pending_amount = 25.000;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        // Create OrderDetail on actual order
        $od = new OrderDetail();
        $od->order_id = $order->id;
        $od->product_name = 'Admin Added Item';
        $od->product_price = 25.000;
        $od->quantity = 1;
        $od->total_price = 25.000;
        $od->save();

        $res = CustomizedOrderSyncService::syncActualOrderToDraft($order);

        $this->assertTrue($res['status']);
        $this->assertEquals(25.000, (float) $temp->fresh()->grand_total);
        $this->assertEquals(1, TemporaryOrderDetail::where('temporary_order_id', $temp->id)->count());
    }

    /** @test */
    public function test_17_cancelled_order_blocks_new_payment_links()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 5; // Cancelled
        $order->order_total = 10.000;
        $order->save();

        $val = PaymentCalculationService::validatePaymentAmount($order, 10.000);
        $this->assertFalse($val['valid']);
        $this->assertStringContainsString('cancelled', strtolower($val['message']));
    }

    /** @test */
    public function test_18_quotation_type_blocks_payment_links()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->custom_order_type = 'quotation';
        $order->order_status = 7;
        $order->order_total = 10.000;
        $order->save();

        $val = PaymentCalculationService::validatePaymentAmount($order, 10.000);
        $this->assertFalse($val['valid']);
        $this->assertStringContainsString('quotation', strtolower($val['message']));
    }

    /** @test */
    public function test_19_financial_calculation_percentage_and_flat_discounts()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->status = 'pending';
        $temp->save();

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_status = 7;
        $order->custom_order_details = json_encode(['temporary_order_id' => $temp->id]);
        $order->save();

        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // Subtotal = 100, Shipping = 10, Discount = 20% (20 KWD) -> Grand Total = 90 KWD
        $items = [
            ['price' => 50.000, 'quantity' => 2, 'total' => 100.000]
        ];
        $res = CustomizedOrderSyncService::syncDraftToActualOrder($temp, $order, $items, [
            'shipping' => 10.000,
            'discount_type' => 'percentage',
            'discount_val' => 20.0,
        ]);

        $this->assertTrue($res['status']);
        $this->assertEquals(100.000, $res['data']['subtotal']);
        $this->assertEquals(10.000, $res['data']['shipping']);
        $this->assertEquals(20.000, $res['data']['discount_amount']);
        $this->assertEquals(90.000, $res['data']['grand_total']);
    }

    /** @test */
    public function test_20_financial_accounting_ledger_immutability_and_balance()
    {
        $user = $this->createTestUser();
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->order_total = 30.000;
        $order->order_status = 7;
        $order->save();

        // 3 separate historical payments
        $p1 = new PaymentLink(['order_id' => $order->id, 'payment_request_id' => 'PL_A_' . uniqid(), 'amount' => 5.0, 'paid_amount' => 5.0, 'status' => 'paid']);
        $p1->save();
        $p2 = new PaymentLink(['order_id' => $order->id, 'payment_request_id' => 'PL_B_' . uniqid(), 'amount' => 10.0, 'paid_amount' => 10.0, 'status' => 'paid']);
        $p2->save();
        $p3 = new PaymentLink(['order_id' => $order->id, 'payment_request_id' => 'PL_C_' . uniqid(), 'amount' => 15.0, 'paid_amount' => 15.0, 'status' => 'paid']);
        $p3->save();

        $calc = PaymentCalculationService::calculateOrderPayments($order);
        $this->assertEquals(30.000, $calc['total_paid']);
        $this->assertEquals(0.000, $calc['pending_amount']);
        $this->assertTrue($calc['is_fully_paid']);

        // Assert that all 3 payment link records exist distinctly in DB
        $this->assertEquals(3, PaymentLink::where('order_id', $order->id)->where('status', 'paid')->count());
    }

    /** @test */
    public function test_21_first_and_second_place_order_updates_same_actual_order_id()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Create Temporary Draft
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->customer_name = 'Customer One';
        $temp->customer_phone = $user->mobile;
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        // 2. First Place Order
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'customer' => ['name' => 'Customer One', 'phone' => $user->mobile, 'email' => $user->email],
            'items' => [
                ['product_title' => 'Item 1', 'price' => 10.000, 'quantity' => 1, 'total' => 10.000]
            ],
            'shipping' => 0,
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_TEST_1', 7, true, 4);

        $firstOrder = $temp->fresh()->getLinkedOrder();
        $this->assertNotNull($firstOrder);
        $firstOrderId = $firstOrder->id;
        $this->assertEquals(10.000, (float)$firstOrder->order_total);
        $this->assertEquals(1, OrderDetail::where('order_id', $firstOrderId)->count());

        // 3. Customer reopens and edits the same draft -> Total becomes 15 KWD (2 items)
        $temp->fresh();
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'customer' => ['name' => 'Customer One Edited', 'phone' => $user->mobile, 'email' => $user->email],
            'items' => [
                ['product_title' => 'Item 1', 'price' => 10.000, 'quantity' => 1, 'total' => 10.000],
                ['product_title' => 'Item 2', 'price' => 5.000, 'quantity' => 1, 'total' => 5.000],
            ],
            'shipping' => 0,
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        // 4. Customer clicks Place Order AGAIN
        $checkoutController->createOrder('PAY_ID_TEST_2', 7, true, 4);

        // 5. Assert: SAME Order ID is updated, NO duplicate order created
        $allOrdersForUser = Order::where('user_id', $user->id)->get();
        $this->assertCount(1, $allOrdersForUser);
        $this->assertEquals($firstOrderId, $allOrdersForUser->first()->id);
        $this->assertEquals(15.000, (float)$allOrdersForUser->first()->order_total);

        // Order details synchronized for the same order
        $this->assertEquals(2, OrderDetail::where('order_id', $firstOrderId)->count());

        // 6. Third Place Order on same draft
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'customer' => ['name' => 'Customer One Third', 'phone' => $user->mobile, 'email' => $user->email],
            'items' => [
                ['product_title' => 'Item 3', 'price' => 20.000, 'quantity' => 1, 'total' => 20.000],
            ],
            'shipping' => 0,
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);
        $checkoutController->createOrder('PAY_ID_TEST_3', 7, true, 4);

        $this->assertCount(1, Order::where('user_id', $user->id)->get());
        $this->assertEquals($firstOrderId, Order::where('user_id', $user->id)->first()->id);
        $this->assertEquals(20.000, (float)Order::where('user_id', $user->id)->first()->order_total);
        $this->assertEquals(1, OrderDetail::where('order_id', $firstOrderId)->count());
    }

    /** @test */
    public function test_22_place_order_after_order_enters_process_status_blocks_modification()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Create Temporary Draft
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->save();

        // 2. Place Order
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item A', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 0],
        ]);
        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_PROCESS_1', 7, true, 4);

        $order = $temp->fresh()->getLinkedOrder();
        $this->assertNotNull($order);

        // 3. Admin changes order status to Process (1)
        $order->order_status = 1;
        $order->save();

        // 4. Customer attempts to place modified order on same draft
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item B Hacked', 'price' => 50.0, 'quantity' => 1, 'total' => 50.0]],
            'payment' => ['type' => 4, 'advance' => 0],
        ]);

        $checkoutController->createOrder('PAY_ID_PROCESS_2', 7, true, 4);

        // Assert that the order total remains 10.0 and no new order was created
        $this->assertCount(1, Order::where('user_id', $user->id)->get());
        $this->assertEquals(10.000, (float)$order->fresh()->order_total);
        $this->assertEquals(1, (int)$order->fresh()->order_status);
    }

    /** @test */
    public function test_23_advance_payment_preservation_when_placed_order_is_edited_and_placed_again()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Initial Temp Order Total = 10 KWD
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item A', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_ADV_1', 7, true, 4);

        $order = $temp->fresh()->getLinkedOrder();
        $this->assertNotNull($order);
        $orderId = $order->id;

        // 2. Customer edits order total from 10 KWD -> 15 KWD
        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [
                ['product_title' => 'Item A', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0],
                ['product_title' => 'Item B', 'price' => 5.0, 'quantity' => 1, 'total' => 5.0],
            ],
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        // 3. Customer places order again
        $checkoutController->createOrder('PAY_ID_ADV_2', 7, true, 4);

        $orderUpdated = Order::find($orderId);
        $this->assertEquals(15.000, (float)$orderUpdated->order_total);
        $this->assertEquals(2.000, (float)$orderUpdated->paid_amount);
        $this->assertEquals(13.000, (float)$orderUpdated->pending_amount);

        // Historical payment link record must remain intact
        $this->assertEquals(1, PaymentLink::where('order_id', $orderId)->where('status', 'paid')->count());
        $this->assertEquals(2.000, (float)PaymentLink::where('order_id', $orderId)->where('status', 'paid')->first()->paid_amount);
    }

    /** @test */
    public function test_24_user_cannot_place_order_using_another_users_temporary_order_id()
    {
        $userA = $this->createTestUser('userA_idor_');
        $userB = $this->createTestUser('userB_idor_');

        // User A creates draft
        $tempA = new TemporaryOrder();
        $tempA->user_id = $userA->id;
        $tempA->type = 'purchase_order';
        $tempA->status = 'pending';
        $tempA->grand_total = 10.000;
        $tempA->save();

        // User B logs in and attempts to place an order passing User A's draft ID
        Auth::login($userB);

        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $tempA->id,
            'items' => [['product_title' => 'Stolen Item', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 0],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_IDOR_TEST', 7, true, 4);

        // Assert: Temp A must NOT be linked to User B's order
        $this->assertNull($tempA->fresh()->getLinkedOrder());
    }

    /** @test */
    public function test_25_save_customized_order_reopen_and_edit_preserves_advance_paid_in_ledger()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Initial Temp Order Total = 10 KWD
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->advance_price = 2.000;
        $temp->pending_amount = 8.000;
        $temp->save();

        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item 1', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_25_1', 7, true, 4);

        $order = $temp->fresh()->getLinkedOrder();
        $this->assertNotNull($order);
        $this->assertEquals(2.000, (float)$order->paid_amount);

        // 2. Customer reopens draft in CartController via getTemporaryOrderDetail
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $detailResponse = $cartController->getTemporaryOrderDetail($temp->id);
        $detailData = json_decode($detailResponse->getContent(), true)['data'];
        $this->assertEquals(2.000, (float)$detailData['payment']['advance']);
        $this->assertEquals(2.000, (float)$detailData['payment']['paid']);
        $this->assertEquals(8.000, (float)$detailData['payment']['pending']);

        // 3. Customer edits item total to 15 KWD and submits saveCustomizedOrder with advance_payment = 2.000
        $editReq = new Request([
            'temporary_order_id' => $temp->id,
            'custom_req_type' => 'purchase_order',
            'cust_phone' => $user->mobile,
            'cust_name' => 'Customer Name',
            'custom_price' => [0 => '15.000'],
            'custom_qty' => [0 => '1'],
            'custom_desc' => [0 => 'Updated Item Description'],
            'custom_product_title' => [0 => 'Updated Item Title'],
            'custom_shipping' => '0',
            'advance_payment' => '2.000',
        ]);

        $saveResponse = $cartController->submitCustomizedOrder($editReq);
        $saveData = json_decode($saveResponse->getContent(), true);
        $this->assertTrue($saveData['status']);

        // 4. Assert: TemporaryOrder and Actual Order paid_amount is NOT zero
        $this->assertEquals(2.000, (float)$temp->fresh()->advance_price);
        $this->assertEquals(13.000, (float)$temp->fresh()->pending_amount);
        $this->assertEquals(2.000, (float)$order->fresh()->paid_amount);
        $this->assertEquals(13.000, (float)$order->fresh()->pending_amount);

        // 5. Customer clicks Place Order again
        $checkoutController->createOrder('PAY_ID_25_2', 7, true, 4);

        $this->assertEquals(1, Order::where('user_id', $user->id)->count());
        $this->assertEquals($order->id, Order::where('user_id', $user->id)->first()->id);
        $this->assertEquals(15.000, (float)$order->fresh()->order_total);
        $this->assertEquals(2.000, (float)$order->fresh()->paid_amount);
        $this->assertEquals(13.000, (float)$order->fresh()->pending_amount);
    }

    /** @test */
    public function test_26_save_customized_order_with_advance_zero_does_not_wipe_historical_paid_amount()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item 1', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 2.000],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_26_1', 7, true, 4);

        $order = $temp->fresh()->getLinkedOrder();
        $this->assertEquals(2.000, (float)$order->paid_amount);

        // Customer submits saveCustomizedOrder with advance_payment = 0
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $editReq = new Request([
            'temporary_order_id' => $temp->id,
            'custom_req_type' => 'purchase_order',
            'cust_phone' => $user->mobile,
            'cust_name' => 'Customer Name',
            'custom_price' => [0 => '12.000'],
            'custom_qty' => [0 => '1'],
            'custom_desc' => [0 => 'Item Description'],
            'custom_product_title' => [0 => 'Item Title'],
            'advance_payment' => '0',
        ]);

        $saveResp = $cartController->submitCustomizedOrder($editReq);
        $saveData = json_decode($saveResp->getContent(), true);
        $this->assertTrue($saveData['status']);

        // Historical paid amount MUST remain 2.000, NOT 0
        $this->assertEquals(2.000, (float)$temp->fresh()->advance_price);
        $this->assertEquals(10.000, (float)$temp->fresh()->pending_amount);
        $this->assertEquals(2.000, (float)$order->fresh()->paid_amount);
        $this->assertEquals(10.000, (float)$order->fresh()->pending_amount);
    }

    /** @test */
    public function test_27_save_customized_order_with_missing_advance_field_preserves_paid_amount()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->subtotal = 10.000;
        $temp->grand_total = 10.000;
        $temp->save();

        session()->put('customized_order_details', [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'items' => [['product_title' => 'Item 1', 'price' => 10.0, 'quantity' => 1, 'total' => 10.0]],
            'payment' => ['type' => 4, 'advance' => 3.000],
        ]);

        $checkoutController = new CheckoutController();
        $checkoutController->createOrder('PAY_ID_27_1', 7, true, 4);

        $order = $temp->fresh()->getLinkedOrder();
        $this->assertEquals(3.000, (float)$order->paid_amount);

        // Customer edits address/item with NO advance field sent at all
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $editReq = new Request([
            'temporary_order_id' => $temp->id,
            'custom_req_type' => 'purchase_order',
            'custom_price' => [0 => '10.000'],
            'custom_qty' => [0 => '1'],
            'custom_desc' => [0 => 'Item Description'],
            'custom_product_title' => [0 => 'Item Title'],
            'cust_phone' => '96588888888',
        ]);

        $cartController->submitCustomizedOrder($editReq);

        // Historical paid amount MUST remain 3.000
        $this->assertEquals(3.000, (float)$temp->fresh()->advance_price);
        $this->assertEquals(7.000, (float)$temp->fresh()->pending_amount);
        $this->assertEquals(3.000, (float)$order->fresh()->paid_amount);
        $this->assertEquals(7.000, (float)$order->fresh()->pending_amount);
    }

    /** @test */
    public function test_28_real_frontend_workflow_reopen_edit_replace_and_subsequent_modifications()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // Step 1: Initial Customized Order (Total = 10, advance = 2)
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Real Flow Test User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'cust_country_id' => 1,
            'cust_city' => 'Kuwait City',
            'payment_type' => 1,
            'advance_payment' => 2.000,
            'custom_desc' => ['Initial Item'],
            'custom_product_title' => ['Initial Product'],
            'custom_price' => [10.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);

        $resp1 = $cartController->submitCustomizedOrder($req1);
        $data1 = json_decode($resp1->getContent(), true);
        $this->assertTrue($data1['status']);
        $tempDraftId = $data1['data']['temporary_order_id'];

        // Step 2: Place Order (First place)
        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals(10.000, (float)$order->order_total);

        // Step 3: Advance payment completed (PaymentLink marked 'paid')
        $paymentLink = PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'amount' => 2.000,
            'paid_amount' => 2.000,
            'status' => 'paid',
            'gateway_transaction_id' => 'TXN_' . uniqid(),
            'notes' => 'Advance Payment Successful',
        ]);
        $order->paid_amount = 2.000;
        $order->pending_amount = 8.000;
        $order->is_advance_payment_done = 1;
        $order->save();

        // Step 4: Reopen via getTemporaryOrderDetail API
        $detailResp = $cartController->getTemporaryOrderDetail($tempDraftId);
        $detailData = json_decode($detailResp->getContent(), true);
        $this->assertTrue($detailData['status']);
        $this->assertEquals(2.000, (float)$detailData['data']['payment']['advance']);
        $this->assertEquals(2.000, (float)$detailData['data']['payment']['paid']);

        // Step 5: Edit order - New Total = 15, advance form input = 0
        $reqEdit1 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Real Flow Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Edited Item 15'],
            'custom_product_title' => ['Product Total 15'],
            'custom_price' => [15.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respEdit1 = $cartController->submitCustomizedOrder($reqEdit1);
        $this->assertTrue(json_decode($respEdit1->getContent(), true)['status']);

        // Step 6: Place Order again (Re-place)
        $rePlacedOrderId = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId, 'Must update SAME Order ID');

        $order->refresh();
        $this->assertEquals(15.000, (float)$order->order_total);
        $this->assertEquals(2.000, (float)$order->paid_amount, 'Historical paid amount must remain 2.000');
        $this->assertEquals(13.000, (float)$order->pending_amount);

        // Step 7: Edit order again - New Total = 7, advance form input = 0
        $reqEdit2 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Real Flow Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Edited Item 7'],
            'custom_product_title' => ['Product Total 7'],
            'custom_price' => [7.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqEdit2);
        $rePlacedOrderId2 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId2, 'Must update SAME Order ID');

        $order->refresh();
        $this->assertEquals(7.000, (float)$order->order_total);
        $this->assertEquals(2.000, (float)$order->paid_amount, 'Paid amount must remain 2.000');
        $this->assertEquals(5.000, (float)$order->pending_amount);

        // Step 8: Edit order again - Attempt New Total = 1 (when 2 is paid) -> MUST BE REJECTED
        $reqEditInvalid = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Real Flow Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Edited Item 1'],
            'custom_product_title' => ['Product Total 1'],
            'custom_price' => [1.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respInvalid = $cartController->submitCustomizedOrder($reqEditInvalid);
        $this->assertEquals(422, $respInvalid->getStatusCode());

        // Step 9: Edit order to Total = 2 (equal to paid advance) -> SUCСEEDS with pending = 0
        $reqEditValid = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Real Flow Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Edited Item 2'],
            'custom_product_title' => ['Product Total 2'],
            'custom_price' => [2.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respValid = $cartController->submitCustomizedOrder($reqEditValid);
        $this->assertTrue($respValid->getData()->status);
        $rePlacedOrderId3 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId3, 'Must update SAME Order ID');

        $order->refresh();
        $this->assertEquals(2.000, (float)$order->order_total);
        $this->assertEquals(2.000, (float)$order->paid_amount, 'Paid amount must remain 2.000');
        $this->assertEquals(0.000, (float)$order->pending_amount);

        // Verify payment link was NOT deleted or invalidated
        $this->assertEquals('paid', $paymentLink->fresh()->status);
        $this->assertEquals(2.000, (float)$paymentLink->fresh()->paid_amount);
    }

    /** @test */
    public function test_29_customized_order_77_kwd_advance_25_kwd_lifecycle_and_pending_recalculation()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // 1. Initial Customized Order: Total = 77.000, Advance = 25.000
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Custom 77 Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'cust_country_id' => 1,
            'cust_city' => 'Kuwait City',
            'payment_type' => 1,
            'advance_payment' => 25.000,
            'custom_desc' => ['Custom Gown'],
            'custom_product_title' => ['Gown 77'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);

        $resp1 = $cartController->submitCustomizedOrder($req1);
        $data1 = json_decode($resp1->getContent(), true);
        $this->assertTrue($data1['status']);
        $tempDraftId = $data1['data']['temporary_order_id'];

        $tempOrder = TemporaryOrder::find($tempDraftId);
        $this->assertEquals(77.000, (float)$tempOrder->grand_total);
        $this->assertEquals(25.000, (float)$tempOrder->advance_price);
        $this->assertEquals(52.000, (float)$tempOrder->pending_amount, 'TemporaryOrder pending_amount must be 77 - 25 = 52');

        // 2. Place Order (First place) and complete advance payment
        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals(77.000, (float)$order->order_total);
        $this->assertEquals(25.000, (float)$order->paid_amount);
        $this->assertEquals(52.000, (float)$order->pending_amount, 'Order pending_amount must be 77 - 25 = 52');

        // Customer completes the 25 KWD advance payment
        PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'amount' => 25.000,
            'paid_amount' => 25.000,
            'status' => 'paid',
            'gateway_transaction_id' => 'TXN_TEST29_' . uniqid(),
            'notes' => 'Advance Payment Successful',
            'is_advance_payment' => 1,
        ]);
        $order->paid_amount = 25.000;
        $order->is_advance_payment_done = 1;
        $order->save();

        // 3. Edit Order: New Total = 100.000 (advance remains 25.000)
        $reqEdit1 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Custom 77 Customer',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Custom Gown Edited 100'],
            'custom_product_title' => ['Gown 100'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqEdit1);
        $rePlacedOrderId1 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId1, 'Must update SAME Order ID');

        $order->refresh();
        $tempOrder->refresh();
        $this->assertEquals(100.000, (float)$order->order_total);
        $this->assertEquals(25.000, (float)$order->paid_amount);
        $this->assertEquals(75.000, (float)$order->pending_amount, 'Order pending_amount must be 100 - 25 = 75');
        $this->assertEquals(100.000, (float)$tempOrder->grand_total);
        $this->assertEquals(25.000, (float)$tempOrder->advance_price);
        $this->assertEquals(75.000, (float)$tempOrder->pending_amount, 'TempOrder pending_amount must be 100 - 25 = 75');

        // 4. Edit Order: New Total = 50.000 (advance remains 25.000)
        $reqEdit2 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Custom 77 Customer',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Custom Gown Edited 50'],
            'custom_product_title' => ['Gown 50'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqEdit2);
        $rePlacedOrderId2 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId2, 'Must update SAME Order ID');

        $order->refresh();
        $tempOrder->refresh();
        $this->assertEquals(50.000, (float)$order->order_total);
        $this->assertEquals(25.000, (float)$order->paid_amount);
        $this->assertEquals(25.000, (float)$order->pending_amount, 'Order pending_amount must be 50 - 25 = 25');
        $this->assertEquals(50.000, (float)$tempOrder->grand_total);
        $this->assertEquals(25.000, (float)$tempOrder->advance_price);
        $this->assertEquals(25.000, (float)$tempOrder->pending_amount, 'TempOrder pending_amount must be 50 - 25 = 25');

        // 5. Edit Order: Attempt Total = 20.000 when paid is 25.000 -> MUST BE REJECTED with 422
        $reqEditInvalid = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Custom 77 Customer',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Custom Gown Edited 20'],
            'custom_product_title' => ['Gown 20'],
            'custom_price' => [20.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respInvalid = $cartController->submitCustomizedOrder($reqEditInvalid);
        $this->assertEquals(422, $respInvalid->getStatusCode());

        // 6. Edit Order: Total = 25.000 (equal to paid advance 25.000 -> pending = 0)
        $reqEditValid = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Custom 77 Customer',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'custom_desc' => ['Custom Gown Edited 25'],
            'custom_product_title' => ['Gown 25'],
            'custom_price' => [25.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respValid = $cartController->submitCustomizedOrder($reqEditValid);
        $this->assertTrue($respValid->getData()->status);
        $rePlacedOrderId3 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId3, 'Must update SAME Order ID');

        $order->refresh();
        $tempOrder->refresh();
        $this->assertEquals(25.000, (float)$order->order_total);
        $this->assertEquals(25.000, (float)$order->paid_amount);
        $this->assertEquals(0.000, (float)$order->pending_amount, 'Order pending_amount must be 0 when total == advance');
        $this->assertEquals(25.000, (float)$tempOrder->grand_total);
        $this->assertEquals(25.000, (float)$tempOrder->advance_price);
        $this->assertEquals(0.000, (float)$tempOrder->pending_amount, 'TempOrder pending_amount must be 0');
    }

    /** @test */
    public function test_30_staged_payment_advance_not_done_prohibits_pending_link_and_rejected_advance_keeps_stage1()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // 1. Initial Customized Order: Total = 77, Advance = 25
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 1 User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 25.000,
            'custom_desc' => ['Stage 1 Gown'],
            'custom_product_title' => ['Gown 77'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp1 = $cartController->submitCustomizedOrder($req1);
        $data1 = json_decode($resp1->getContent(), true);
        $tempDraftId = $data1['data']['temporary_order_id'];

        // Place initial order
        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);

        // Advance payment link exists but is REJECTED
        PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'amount' => 25.000,
            'status' => 'rejected',
            'gateway_transaction_id' => 'TXN_ADV_REJ_' . uniqid(),
            'notes' => 'Advance Payment Rejected',
            'is_advance_payment' => 1,
        ]);

        // Customer edits order and attempts to generate pending amount payment link (55.000)
        $reqEdit = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 1 User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 25.000,
            'generate_pending_payment_link' => 1, // User attempts to force pending link
            'custom_desc' => ['Stage 1 Gown 80'],
            'custom_product_title' => ['Gown 80'],
            'custom_price' => [80.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respEdit = $cartController->submitCustomizedOrder($reqEdit);
        $dataEdit = json_decode($respEdit->getContent(), true);
        $this->assertTrue($dataEdit['status']);

        // Check modal API response
        $detailResp = $cartController->getTemporaryOrderDetail($tempDraftId);
        $detailData = json_decode($detailResp->getContent(), true);
        $this->assertFalse($detailData['data']['payment_state']['advance_completed']);
        $this->assertFalse($detailData['data']['payment_state']['can_generate_pending_link'], 'can_generate_pending_link MUST be false when advance is not completed');
        $this->assertFalse($detailData['data']['payment_state']['generate_pending_link_requested'], 'generate_pending_link MUST be rejected by server');

        // Customer re-places order
        $rePlacedOrderId = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $rePlacedOrderId);

        // Verify that NO pending payment link was generated for 55.000 KWD
        $pendingLink55 = PaymentLink::where('order_id', $order->id)
            ->where('amount', 55.000)
            ->exists();
        $this->assertFalse($pendingLink55, 'System MUST NOT generate a 55 KWD pending payment link while advance is incomplete');

        // Verify Admin controller rejects pending payment link generation
        $adminOrderController = new \App\Http\Controllers\Admin\Order\OrderController();
        $adminReq = new Request([]); // No advance_payment specified -> attempts pending amount link
        $adminResp = $adminOrderController->generatePaymentLink($adminReq, $order->id);
        $this->assertEquals(302, $adminResp->getStatusCode());
        $this->assertEquals('Advance payment must be completed before generating the pending amount payment link.', session('error'));
    }

    /** @test */
    public function test_31_staged_payment_advance_done_controls_pending_link_via_checkbox()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // 1. Initial Customized Order: Total = 77, Advance = 25
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 2 User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 25.000,
            'custom_desc' => ['Stage 2 Gown'],
            'custom_product_title' => ['Gown 77'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp1 = $cartController->submitCustomizedOrder($req1);
        $data1 = json_decode($resp1->getContent(), true);
        $tempDraftId = $data1['data']['temporary_order_id'];

        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);

        // Advance payment SUCCESSFUL (PaymentLink paid)
        PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'amount' => 25.000,
            'paid_amount' => 25.000,
            'status' => 'paid',
            'gateway_transaction_id' => 'TXN_ADV_PAID_' . uniqid(),
            'notes' => 'Advance Payment Successful',
            'is_advance_payment' => 1,
        ]);
        $order->paid_amount = 25.000;
        $order->pending_amount = 52.000;
        $order->is_advance_payment_done = 1;
        $order->save();

        // 2. Check modal API response now
        $detailResp = $cartController->getTemporaryOrderDetail($tempDraftId);
        $detailData = json_decode($detailResp->getContent(), true);
        $this->assertTrue($detailData['data']['payment_state']['advance_completed']);
        $this->assertTrue($detailData['data']['payment_state']['can_generate_pending_link'], 'can_generate_pending_link MUST be true when advance is completed');
        $this->assertFalse($detailData['data']['payment_state']['generate_pending_link_requested'], 'Default unchecked');

        // 3. Edit Order with Checkbox UNCHECKED: New Total = 100, pending = 75
        $reqEditUnchecked = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 2 User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'generate_pending_payment_link' => 0, // UNCHECKED
            'custom_desc' => ['Stage 2 Gown 100'],
            'custom_product_title' => ['Gown 100'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqEditUnchecked);
        $checkoutController->createOrder('', 7, false, 1);

        // Verify NO pending payment link was generated when unchecked
        $pendingLink75 = PaymentLink::where('order_id', $order->id)
            ->where('amount', 75.000)
            ->where('status', 'pending')
            ->exists();
        $this->assertFalse($pendingLink75, 'NO pending payment link when checkbox is unchecked');

        // 4. Edit Order with Checkbox CHECKED: New Total = 100, pending = 75
        $reqEditChecked = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 2 User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'generate_pending_payment_link' => 1, // CHECKED
            'custom_desc' => ['Stage 2 Gown 100'],
            'custom_product_title' => ['Gown 100'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqEditChecked);
        $sess = session('customized_order_details');
        $this->assertEquals(1, $sess['payment']['generate_pending_link']);
        $this->assertEquals(75.000, (float)$sess['calculation']['pending']);

        // 5. When Total < Paid (e.g. Total = 20, Paid = 25) -> MUST BE REJECTED with 422
        $reqEditInvalid = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 2 User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Stage 2 Gown 20'],
            'custom_product_title' => ['Gown 20'],
            'custom_price' => [20.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respInvalid = $cartController->submitCustomizedOrder($reqEditInvalid);
        $this->assertEquals(422, $respInvalid->getStatusCode());

        // 6. When Total == Paid (e.g. Total = 25, Paid = 25 -> pending = 0) -> can_generate_pending_link MUST be false
        $reqEditEqual = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Stage 2 User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Stage 2 Gown 25'],
            'custom_product_title' => ['Gown 25'],
            'custom_price' => [25.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respEqual = $cartController->submitCustomizedOrder($reqEditEqual);
        $this->assertTrue($respEqual->getData()->status);
        $detailResp2 = $cartController->getTemporaryOrderDetail($tempDraftId);
        $detailData2 = json_decode($detailResp2->getContent(), true);
        $this->assertFalse($detailData2['data']['payment_state']['can_generate_pending_link'], 'can_generate_pending_link MUST be false when pending amount is 0');
    }

    /** @test */
    public function test_32_advance_payment_edit_from_24_to_22_updates_all_sources_consistently()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        // 1. Initial Order Creation: Total = 77, Advance = 24, Pending = 53
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        $reqCreate = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Advance Test Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 24.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Custom Item 1'],
            'custom_product_title' => ['Custom Item 1'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);

        $createResp = $cartController->submitCustomizedOrder($reqCreate);
        $tempOrder = TemporaryOrder::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($tempOrder);
        $this->assertEquals(77.000, (float)$tempOrder->grand_total);
        $this->assertEquals(24.000, (float)$tempOrder->advance_price);
        $this->assertEquals(53.000, (float)$tempOrder->pending_amount);

        // Place initial order
        $orderResp = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(77.000, (float)$order->order_total);
        $this->assertEquals(24.000, (float)$order->paid_amount);
        $this->assertEquals(53.000, (float)$order->pending_amount);

        // 2. Edit the SAME order: Advance changes from 24.000 to 22.000
        $reqEdit = new Request([
            'temporary_order_id' => $tempOrder->id,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Advance Test Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 22.000,
            'generate_pending_payment_link' => 0,
            'custom_desc' => ['Custom Item 1 Updated'],
            'custom_product_title' => ['Custom Item 1 Updated'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);

        $editResp = $cartController->submitCustomizedOrder($reqEdit);
        $tempOrderFresh = $tempOrder->fresh();
        $this->assertEquals(22.000, (float)$tempOrderFresh->advance_price, 'temporary_orders.advance_price must be 22.000');
        $this->assertEquals(55.000, (float)$tempOrderFresh->pending_amount, 'temporary_orders.pending_amount must be 55.000');

        // Re-place the same order
        $rePlaceResp = $checkoutController->createOrder('', 7, false, 1);
        $orderFresh = $order->fresh();

        // 3. Assert SAME order ID
        $this->assertEquals($order->id, $orderFresh->id, 'Must update SAME order record');

        // 4. Assert DB columns
        $this->assertEquals(77.000, (float)$orderFresh->order_total, 'orders.order_total must be 77.000');
        $this->assertEquals(22.000, (float)$orderFresh->paid_amount, 'orders.paid_amount must be 22.000');
        $this->assertEquals(55.000, (float)$orderFresh->pending_amount, 'orders.pending_amount must be 55.000');

        // 5. Assert JSON snapshot fields
        $orderJson = json_decode($orderFresh->custom_order_details, true);
        $this->assertEquals(22.000, (float)$orderJson['payment']['advance']);
        $this->assertEquals(55.000, (float)$orderJson['calculation']['pending']);

        $tempJson = json_decode($tempOrderFresh->custom_order_details, true);
        $this->assertEquals(22.000, (float)$tempJson['payment']['advance']);
        $this->assertEquals(55.000, (float)$tempJson['calculation']['pending']);

        // 6. Test syncDraftToActualOrder directly with new draft advance = 22.000
        $syncRes = CustomizedOrderSyncService::syncDraftToActualOrder(
            $tempOrderFresh,
            $orderFresh,
            [['product_title' => 'Custom Item 1 Updated', 'price' => 77.000, 'quantity' => 1, 'total' => 77.000]],
            []
        );
        $this->assertTrue($syncRes['status']);
        $this->assertEquals(22.000, (float)$orderFresh->fresh()->paid_amount);
        $this->assertEquals(55.000, (float)$orderFresh->fresh()->pending_amount);
    }

    /** @test */
    public function test_33_payment_review_page_displays_advance_and_pending_accurately()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // 1. Create Customized Order: Total = 77, Advance = 22, Pending = 55
        $req = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Payment Review Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 22.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Custom Gown'],
            'custom_product_title' => ['Custom Gown'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp = $cartController->submitCustomizedOrder($req);
        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);

        // Advance Payment Link
        $advLink = PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'payment_request_id' => 'PR_ADV_' . uniqid(),
            'amount' => 22.000,
            'status' => 'pending',
            'is_advance_payment' => 1,
        ]);

        // Render Payment Review for Advance Link
        $viewAdv = view('web.checkout.payment_review_customized', [
            'order' => $order,
            'address' => $order->getUserAddress(),
            'orderProductDetail' => OrderDetail::where('order_id', $order->id)->get(),
            'paymentLink' => $advLink,
        ])->render();

        $this->assertStringContainsString('77.000', $viewAdv, 'Payment Review must display Total 77.000');
        $this->assertStringContainsString('22.000', $viewAdv, 'Payment Review must display Advance 22.000');
        $this->assertStringContainsString('55.000', $viewAdv, 'Payment Review must display Pending 55.000');
        $this->assertEquals(22.000, (float)$advLink->amount, 'Advance Link must charge 22.000');

        // Complete Advance Payment
        $advLink->status = 'paid';
        $advLink->paid_amount = 22.000;
        $advLink->save();

        $order->paid_amount = 22.000;
        $order->pending_amount = 55.000;
        $order->is_advance_payment_done = 1;
        $order->save();

        // Create Pending Payment Link
        $pendingLink = PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'payment_request_id' => 'PR_PENDING_' . uniqid(),
            'amount' => 55.000,
            'status' => 'pending',
            'is_advance_payment' => 0,
        ]);

        // Render Payment Review for Pending Link
        $viewPending = view('web.checkout.payment_review_customized', [
            'order' => $order->fresh(),
            'address' => $order->getUserAddress(),
            'orderProductDetail' => OrderDetail::where('order_id', $order->id)->get(),
            'paymentLink' => $pendingLink,
        ])->render();

        $this->assertStringContainsString('77.000', $viewPending, 'Payment Review for Pending Link must display Total 77.000');
        $this->assertStringContainsString('22.000', $viewPending, 'Payment Review for Pending Link must display Advance 22.000');
        $this->assertStringContainsString('55.000', $viewPending, 'Payment Review for Pending Link must display Pending 55.000');
        $this->assertEquals(55.000, (float)$pendingLink->amount, 'Pending Link must charge 55.000');
    }

    /** @test */
    public function test_34_knet_callback_marks_payment_link_paid_and_updates_order_and_temporary_order()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Setup TemporaryOrder
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->subtotal = 30.000;
        $temp->shipping_price = 50.000;
        $temp->discount_type = 'flat';
        $temp->discount_val = 3.000;
        $temp->discount_amount = 3.000;
        $temp->grand_total = 77.000;
        $temp->advance_price = 22.000;
        $temp->pending_amount = 55.000;
        $temp->save();

        // 2. Setup Actual Order
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->custom_order_type = 'purchase_order';
        $order->order_status = 7;
        $order->order_price = 30.000;
        $order->delivery_price = 50.000;
        $order->discount = 3.000;
        $order->discount_type = 'flat';
        $order->order_total = 77.000;
        $order->paid_amount = 0.000;
        $order->pending_amount = 77.000;
        $order->is_advance_payment_done = 0;
        $order->custom_order_details = json_encode([
            'temporary_order_id' => $temp->id,
            'type' => 'purchase_order',
            'payment' => ['advance' => 22.000, 'paid' => 0.000, 'pending' => 55.000],
        ]);
        $order->save();

        $token = 'pay_' . uniqid();
        $paymentLink = PaymentLink::create([
            'order_id' => $order->id,
            'amount' => 22.000,
            'status' => 'pending',
            'payment_request_id' => $token,
            'payment_type' => 'knet',
            'is_advance_payment' => 1,
            'generated_from' => 'system',
        ]);

        // Simulate KNET/UPayments Callback Payload
        $callbackPayload = [
            'OrderID' => (string) $order->id,
            'PaymentID' => '10062521000010600',
            'Result' => 'CAPTURED',
            'trn_udf' => json_encode(['payment_request_id' => $token]),
            'trackId' => '01a085def82c2402de6cb32d1a5d1d9fv2',
        ];

        $request = new Request($callbackPayload);
        $controller = new CheckoutController();
        $response = $controller->paymentSuccess($request);

        // Verification 1: PaymentLink state
        $paymentLink->refresh();
        $this->assertEquals('paid', $paymentLink->status);
        $this->assertEquals(22.000, (float)$paymentLink->paid_amount);
        $this->assertNotNull($paymentLink->paid_at);

        // Verification 2: Order state
        $order->refresh();
        $this->assertEquals(77.000, (float)$order->order_total);
        $this->assertEquals(22.000, (float)$order->paid_amount);
        $this->assertEquals(55.000, (float)$order->pending_amount);
        $this->assertEquals(1, (int)$order->is_advance_payment_done);

        // Verification 3: TemporaryOrder state
        $temp->refresh();
        $this->assertEquals(22.000, (float)$temp->advance_price);
        $this->assertEquals(55.000, (float)$temp->pending_amount);

        // Verification 4: paymentSummary View renders Success
        $viewResult = $controller->paymentSummary($order->id, $token)->render();
        $this->assertStringContainsString('Your order has been successfully placed', $viewResult);
        $this->assertStringNotContainsString('Your Order Failed', $viewResult);
    }

    /** @test */
    public function test_35_knet_callback_variations_and_failures()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->custom_order_type = 'purchase_order';
        $order->order_status = 7;
        $order->order_total = 77.000;
        $order->paid_amount = 0.000;
        $order->pending_amount = 77.000;
        $order->is_advance_payment_done = 0;
        $order->save();

        $token = 'pay_test_' . uniqid();
        $paymentLink = PaymentLink::create([
            'order_id' => $order->id,
            'amount' => 25.000,
            'status' => 'pending',
            'payment_request_id' => $token,
            'payment_type' => 'knet',
        ]);

        // 1. Gateway Failure Callback
        $failedCallback = [
            'OrderID' => (string) $order->id,
            'PaymentID' => '10062521000099999',
            'Result' => 'NOT CAPTURED',
            'trn_udf' => json_encode(['payment_request_id' => $token]),
        ];

        $requestFail = new Request($failedCallback);
        $controller = new CheckoutController();
        $controller->paymentSuccess($requestFail);

        $paymentLink->refresh();
        $this->assertEquals('failed', $paymentLink->status);
        $order->refresh();
        $this->assertEquals(0.000, (float)$order->paid_amount);
        $this->assertEquals(0, (int)$order->is_advance_payment_done);
    }

    /** @test */
    public function test_36_strict_acceptance_test_1_to_13_lifecycle_suite()
    {
        $user = $this->createTestUser('strict_audit_');
        Auth::login($user);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // -------------------------------------------------------------
        // TEST 1: Existing order, advance not done
        // Expected: Advance editable, pending checkbox hidden
        // -------------------------------------------------------------
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Strict Gown Item'],
            'custom_product_title' => ['Strict Gown 77'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp1 = $cartController->submitCustomizedOrder($req1);
        $tempDraftId = $resp1->getData()->data->temporary_order_id;
        $orderId = $checkoutController->createOrder('', 7, false, 1);
        $order = Order::find($orderId);

        $detail1 = $cartController->getTemporaryOrderDetail($tempDraftId)->getData(true);
        $this->assertEquals(0, $detail1['data']['is_advance_payment_done'], 'TEST 1: Advance not completed');
        $this->assertFalse($detail1['data']['can_generate_pending_link'], 'TEST 1: Pending checkbox hidden');

        // -------------------------------------------------------------
        // Mark Advance Payment Paid (20.000 KWD)
        // -------------------------------------------------------------
        $advLink = PaymentLink::create([
            'order_id' => $order->id,
            'web_user_id' => $user->id,
            'amount' => 20.000,
            'paid_amount' => 20.000,
            'status' => 'paid',
            'payment_request_id' => 'PR_STRICT_' . uniqid(),
            'is_advance_payment' => 1,
        ]);
        $order->paid_amount = 20.000;
        $order->pending_amount = 57.000;
        $order->is_advance_payment_done = 1;
        $order->save();

        // -------------------------------------------------------------
        // TEST 2: Existing order, advance done
        // Expected: Advance field readonly, pending checkbox visible, unchecked by default
        // -------------------------------------------------------------
        $detail2 = $cartController->getTemporaryOrderDetail($tempDraftId)->getData(true);
        $this->assertEquals(1, $detail2['data']['is_advance_payment_done'], 'TEST 2: Advance is done');
        $this->assertTrue($detail2['data']['can_generate_pending_link'], 'TEST 2: Pending checkbox visible');
        $this->assertFalse($detail2['data']['payment_state']['generate_pending_link_requested'], 'TEST 2: Checkbox unchecked by default');
        $this->assertEquals(20.000, (float)$detail2['data']['payment']['advance'], 'TEST 2: Advance amount matches paid amount');

        // -------------------------------------------------------------
        // TEST 3: Advance done, total >= advance (Paid = 20, Total = 50)
        // Expected: Update succeeds, Pending = 30
        // -------------------------------------------------------------
        $req3 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 0,
            'custom_desc' => ['Strict Gown 50'],
            'custom_product_title' => ['Strict Gown 50'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp3 = $cartController->submitCustomizedOrder($req3);
        $this->assertTrue($resp3->getData()->status, 'TEST 3: Update succeeds');
        $order->refresh();
        $this->assertEquals(50.000, (float)$order->order_total, 'TEST 3: Order total is 50');
        $this->assertEquals(20.000, (float)$order->paid_amount, 'TEST 3: Paid amount is 20');
        $this->assertEquals(30.000, (float)$order->pending_amount, 'TEST 3: Pending is 30');

        // -------------------------------------------------------------
        // TEST 4: Advance done, total == advance (Paid = 20, Total = 20)
        // Expected: Update succeeds, Pending = 0, Pending checkbox hidden/disabled
        // -------------------------------------------------------------
        $req4 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 0,
            'custom_desc' => ['Strict Gown 20'],
            'custom_product_title' => ['Strict Gown 20'],
            'custom_price' => [20.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp4 = $cartController->submitCustomizedOrder($req4);
        $this->assertTrue($resp4->getData()->status, 'TEST 4: Update succeeds');
        $order->refresh();
        $this->assertEquals(20.000, (float)$order->order_total);
        $this->assertEquals(20.000, (float)$order->paid_amount);
        $this->assertEquals(0.000, (float)$order->pending_amount, 'TEST 4: Pending is 0');
        $detail4 = $cartController->getTemporaryOrderDetail($tempDraftId)->getData(true);
        $this->assertFalse($detail4['data']['can_generate_pending_link'], 'TEST 4: Checkbox hidden when pending is 0');

        // -------------------------------------------------------------
        // TEST 5: Advance done, total < advance (Paid = 20, Total = 19)
        // Expected: 422 validation error, No DB changes
        // -------------------------------------------------------------
        $req5 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Strict Gown 19'],
            'custom_product_title' => ['Strict Gown 19'],
            'custom_price' => [19.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp5 = $cartController->submitCustomizedOrder($req5);
        $this->assertEquals(422, $resp5->getStatusCode(), 'TEST 5: 422 error on total < paid');
        $this->assertStringContainsString('Order total cannot be less than the paid advance amount of 20.000 KWD', $resp5->getData()->message);
        $order->refresh();
        $this->assertEquals(20.000, (float)$order->order_total, 'TEST 5: DB total unchanged');

        // -------------------------------------------------------------
        // TEST 6: Advance done + checkbox unchecked (Total = 50, Pending = 30)
        // Expected: No pending payment link
        // -------------------------------------------------------------
        $req6 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 0, // UNCHECKED
            'custom_desc' => ['Strict Gown 50'],
            'custom_product_title' => ['Strict Gown 50'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($req6);
        $rePlacedId = $checkoutController->createOrder('', 7, false, 1);
        $pendingLink30 = PaymentLink::where('order_id', $order->id)
            ->where('amount', 30.000)
            ->where('status', 'pending')
            ->exists();
        $this->assertFalse($pendingLink30, 'TEST 6: No pending payment link when unchecked');

        // -------------------------------------------------------------
        // TEST 7: Advance done + checkbox checked (Total = 100, Paid = 20 -> Pending = 80)
        // Expected: Pending payment link generated for current pending amount (80 KWD)
        // -------------------------------------------------------------
        $req7 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1, // CHECKED
            'custom_desc' => ['Strict Gown 100'],
            'custom_product_title' => ['Strict Gown 100'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($req7);
        $sess7 = session('customized_order_details');
        $this->assertEquals(1, $sess7['payment']['generate_pending_link']);
        $this->assertEquals(80.000, (float)$sess7['calculation']['pending']);

        // -------------------------------------------------------------
        // TEST 8: Advance not done + malicious API request with generate_pending_link = true
        // Expected: Backend rejects request
        // -------------------------------------------------------------
        $orderUnpaid = new Order();
        $orderUnpaid->user_id = $user->id;
        $orderUnpaid->order_type = 'custom';
        $orderUnpaid->order_status = 7;
        $orderUnpaid->order_total = 77.000;
        $orderUnpaid->paid_amount = 0.000;
        $orderUnpaid->pending_amount = 77.000;
        $orderUnpaid->is_advance_payment_done = 0;
        $orderUnpaid->save();

        $tempUnpaid = new TemporaryOrder();
        $tempUnpaid->user_id = $user->id;
        $tempUnpaid->type = 'purchase_order';
        $tempUnpaid->status = 'pending';
        $tempUnpaid->advance_price = 20.000;
        $tempUnpaid->pending_amount = 57.000;
        $tempUnpaid->grand_total = 77.000;
        $tempUnpaid->custom_order_details = json_encode(['order_id' => $orderUnpaid->id]);
        $tempUnpaid->save();
        $orderUnpaid->custom_order_details = json_encode(['temporary_order_id' => $tempUnpaid->id]);
        $orderUnpaid->save();

        $reqMalicious = new Request([
            'temporary_order_id' => $tempUnpaid->id,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Malicious User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1, // Malicious request when advance is 0
            'custom_desc' => ['Item'],
            'custom_product_title' => ['Item'],
            'custom_price' => [77.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqMalicious);
        $sessMalicious = session('customized_order_details');
        $this->assertEquals(0, $sessMalicious['payment']['generate_pending_link'], 'TEST 8: Malicious generate_pending_link suppressed');

        // -------------------------------------------------------------
        // TEST 9: Advance done + malicious POST attempts advance_payment = 0
        // Expected: Existing paid advance remains unchanged (20.000)
        // -------------------------------------------------------------
        $req9 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 0.000, // Attempt to wipe advance
            'custom_desc' => ['Strict Gown 50'],
            'custom_product_title' => ['Strict Gown 50'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($req9);
        $order->refresh();
        $this->assertEquals(20.000, (float)$order->paid_amount, 'TEST 9: Paid advance cannot be overwritten to 0');

        // -------------------------------------------------------------
        // TEST 10: Advance done + malicious POST attempts advance_payment = 50
        // Expected: Existing paid advance remains unchanged (20.000)
        // -------------------------------------------------------------
        $req10 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 50.000, // Attempt to inflate advance
            'custom_desc' => ['Strict Gown 50'],
            'custom_product_title' => ['Strict Gown 50'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($req10);
        $order->refresh();
        $this->assertEquals(20.000, (float)$order->paid_amount, 'TEST 10: Paid advance cannot be inflated to 50');

        // -------------------------------------------------------------
        // TEST 11: Double-click save / idempotency
        // Expected: Same order ID, consistent financial state
        // -------------------------------------------------------------
        $idempotentId1 = $checkoutController->createOrder('', 7, false, 1);
        $idempotentId2 = $checkoutController->createOrder('', 7, false, 1);
        $this->assertEquals($order->id, $idempotentId1, 'TEST 11: Idempotency check 1');
        $this->assertEquals($order->id, $idempotentId2, 'TEST 11: Idempotency check 2');

        // -------------------------------------------------------------
        // TEST 12: Existing order re-place
        // Expected: Same ActualOrder ID (order->id), never creates a new order
        // -------------------------------------------------------------
        $this->assertEquals($order->id, $rePlacedId, 'TEST 12: Re-place maintains same ActualOrder ID');

        // -------------------------------------------------------------
        // TEST 13: Process status lock
        // Expected: Edit/re-place remains blocked when order status >= Process (1)
        // -------------------------------------------------------------
        $order->order_status = 1; // Process status
        $order->save();

        $req13 = new Request([
            'temporary_order_id' => $tempDraftId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Strict Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Strict Gown 60'],
            'custom_product_title' => ['Strict Gown 60'],
            'custom_price' => [60.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp13 = $cartController->submitCustomizedOrder($req13);
        $this->assertEquals(422, $resp13->getStatusCode(), 'TEST 13: Order in process status blocked');
        $this->assertStringContainsString('cannot be modified directly', $resp13->getData()->message);
    }

    /** @test */
    public function test_37_pending_payment_checkbox_dropdown_dependency_matrix_suite()
    {
        $userA = $this->createTestUser('dropdown_user_a_');
        $userB = $this->createTestUser('dropdown_user_b_');
        Auth::login($userA);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // -------------------------------------------------------------
        // RECORD A: Existing order + advance_done = 0 (Paid = 0, Total = 70)
        // -------------------------------------------------------------
        $reqA = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'cust_email' => $userA->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Draft A Item'],
            'custom_product_title' => ['Draft A Item'],
            'custom_price' => [70.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respA = $cartController->submitCustomizedOrder($reqA);
        $tempDraftAId = $respA->getData()->data->temporary_order_id;
        $orderAId = $checkoutController->createOrder('', 7, false, 1);
        $orderA = Order::find($orderAId);

        // Verification via API (Record A)
        $detailA = $cartController->getTemporaryOrderDetail($tempDraftAId)->getData(true);
        $this->assertEquals(0, $detailA['data']['is_advance_payment_done']);
        $this->assertFalse($detailA['data']['can_generate_pending_link']);

        // TEST 1: Existing order + advance_done = 0 + generate_pending_payment_link = 1
        // Expected: Backend rejects / suppresses pending link generation
        $reqMaliciousA = new Request([
            'temporary_order_id' => $tempDraftAId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Draft A Item'],
            'custom_product_title' => ['Draft A Item'],
            'custom_price' => [70.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqMaliciousA);
        $sessA = session('customized_order_details');
        $this->assertEquals(0, $sessA['payment']['generate_pending_link'], 'TEST 1: Pending link generation blocked when advance_done = 0');

        // -------------------------------------------------------------
        // RECORD B: Existing order + advance_done = 1 + pending > 0 (Paid = 20, Total = 50 -> Pending = 30)
        // -------------------------------------------------------------
        $reqB = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'cust_email' => $userA->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Draft B Item'],
            'custom_product_title' => ['Draft B Item'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respB = $cartController->submitCustomizedOrder($reqB);
        $tempDraftBId = $respB->getData()->data->temporary_order_id;
        $orderBId = $checkoutController->createOrder('', 7, false, 1);
        $orderB = Order::find($orderBId);

        // Mark advance payment completed for Record B
        PaymentLink::create([
            'order_id' => $orderB->id,
            'web_user_id' => $userA->id,
            'amount' => 20.000,
            'paid_amount' => 20.000,
            'status' => 'paid',
            'payment_request_id' => 'PR_B_' . uniqid(),
            'is_advance_payment' => 1,
        ]);
        $orderB->paid_amount = 20.000;
        $orderB->pending_amount = 30.000;
        $orderB->is_advance_payment_done = 1;
        $orderB->save();

        // Verification via API (Record B)
        $detailB = $cartController->getTemporaryOrderDetail($tempDraftBId)->getData(true);
        $this->assertEquals(1, $detailB['data']['is_advance_payment_done']);
        $this->assertTrue($detailB['data']['can_generate_pending_link']);
        $this->assertFalse($detailB['data']['payment_state']['generate_pending_link_requested']);

        // TEST 2: Existing order + advance_done = 1 + pending > 0 + checkbox false (0)
        // Expected: No pending payment link created
        $reqBUnchecked = new Request([
            'temporary_order_id' => $tempDraftBId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 0, // Unchecked
            'custom_desc' => ['Draft B Item'],
            'custom_product_title' => ['Draft B Item'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqBUnchecked);
        $sessBUnchecked = session('customized_order_details');
        $this->assertEquals(0, $sessBUnchecked['payment']['generate_pending_link'], 'TEST 2: Checkbox false results in generate_pending_link = 0');
        $checkoutController->createOrder('', 7, false, 1);
        $this->assertFalse(
            PaymentLink::where('order_id', $orderB->id)->where('amount', 30.000)->where('status', 'pending')->exists(),
            'TEST 2: No pending link created when unchecked'
        );

        // TEST 3: Existing order + advance_done = 1 + pending > 0 + checkbox true (1)
        // Expected: Pending payment link created for current pending amount (30.000)
        $reqBChecked = new Request([
            'temporary_order_id' => $tempDraftBId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1, // Checked
            'custom_desc' => ['Draft B Item'],
            'custom_product_title' => ['Draft B Item'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqBChecked);
        $sessBChecked = session('customized_order_details');
        $this->assertEquals(1, $sessBChecked['payment']['generate_pending_link'], 'TEST 3: Checkbox true stored in session');
        $this->assertEquals(30.000, (float)$sessBChecked['calculation']['pending']);

        // -------------------------------------------------------------
        // RECORD C: Existing order + advance_done = 1 + pending = 0 (Paid = 20, Total = 20)
        // -------------------------------------------------------------
        $reqC = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'cust_email' => $userA->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Draft C Item'],
            'custom_product_title' => ['Draft C Item'],
            'custom_price' => [20.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respC = $cartController->submitCustomizedOrder($reqC);
        $tempDraftCId = $respC->getData()->data->temporary_order_id;
        $orderCId = $checkoutController->createOrder('', 7, false, 1);
        $orderC = Order::find($orderCId);

        // Mark advance completed (20 KWD)
        PaymentLink::create([
            'order_id' => $orderC->id,
            'web_user_id' => $userA->id,
            'amount' => 20.000,
            'paid_amount' => 20.000,
            'status' => 'paid',
            'payment_request_id' => 'PR_C_' . uniqid(),
            'is_advance_payment' => 1,
        ]);
        $orderC->paid_amount = 20.000;
        $orderC->pending_amount = 0.000;
        $orderC->is_advance_payment_done = 1;
        $orderC->save();

        // TEST 4: advance_done = 1 + pending = 0 + checkbox true
        // Expected: can_generate_pending_link is false and generate_pending_link is suppressed (0)
        $detailC = $cartController->getTemporaryOrderDetail($tempDraftCId)->getData(true);
        $this->assertFalse($detailC['data']['can_generate_pending_link'], 'TEST 4: can_generate_pending_link is false when pending = 0');

        $reqCChecked = new Request([
            'temporary_order_id' => $tempDraftCId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1, // Attempted on pending = 0
            'custom_desc' => ['Draft C Item'],
            'custom_product_title' => ['Draft C Item'],
            'custom_price' => [20.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($reqCChecked);
        $sessCChecked = session('customized_order_details');
        $this->assertEquals(0, $sessCChecked['payment']['generate_pending_link'], 'TEST 4: Link generation suppressed when pending = 0');

        // -------------------------------------------------------------
        // TEST 5: Customer tries another customer's old record (IDOR protection)
        // Expected: Rejected with 404 / unauthorized
        // -------------------------------------------------------------
        Auth::login($userB);
        $detailOther = $cartController->getTemporaryOrderDetail($tempDraftBId);
        $this->assertEquals(404, $detailOther->getStatusCode(), 'TEST 5: Accessing another user draft rejected');

        $reqOther = new Request([
            'temporary_order_id' => $tempDraftBId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User B Impersonator',
            'cust_phone' => $userB->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Hacked Item'],
            'custom_product_title' => ['Hacked Item'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respOther = $cartController->submitCustomizedOrder($reqOther);
        $this->assertEquals(404, $respOther->getStatusCode(), 'TEST 5: Submitting another user draft rejected');

        // -------------------------------------------------------------
        // TEST 6: Process-or-beyond order
        // Expected: Edit/re-place blocked
        // -------------------------------------------------------------
        Auth::login($userA);
        $orderB->order_status = 1; // Process status
        $orderB->save();

        $reqLocked = new Request([
            'temporary_order_id' => $tempDraftBId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'User A',
            'cust_phone' => $userA->mobile,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Draft B Edit'],
            'custom_product_title' => ['Draft B Edit'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $respLocked = $cartController->submitCustomizedOrder($reqLocked);
        $this->assertEquals(422, $respLocked->getStatusCode(), 'TEST 6: Modifying processed order blocked with 422');
    }

    /** @test */
    public function test_38_pending_payment_link_generation_exact_single_deduction_scenarios()
    {
        $user = $this->createTestUser('audit_user_pending_calc_');
        Auth::login($user);
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $checkoutController = new CheckoutController();

        // SCENARIO 1 (Case A): Total = 100, Advance Paid = 20 -> Pending = 80 -> Pending Link = 80 (NOT 60)
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Pending Test User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Item 100 KWD'],
            'custom_product_title' => ['Item 100 KWD'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $resp1 = $cartController->submitCustomizedOrder($req1);
        $tempDraft1Id = $resp1->getData()->data->temporary_order_id;
        $order1Id = $checkoutController->createOrder('', 7, false, 1);
        $order1 = Order::find($order1Id);

        // Record 20.000 Advance Payment
        PaymentLink::create([
            'order_id' => $order1->id,
            'web_user_id' => $user->id,
            'amount' => 20.000,
            'paid_amount' => 20.000,
            'status' => 'paid',
            'payment_request_id' => 'PR_TEST1_' . uniqid(),
            'is_advance_payment' => 1,
        ]);
        $order1->paid_amount = 20.000;
        $order1->pending_amount = 80.000;
        $order1->is_advance_payment_done = 1;
        $order1->save();

        // Edit order with Generate Pending Link checked
        $editReq1 = new Request([
            'temporary_order_id' => $tempDraft1Id,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Pending Test User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Item 100 KWD'],
            'custom_product_title' => ['Item 100 KWD'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($editReq1);
        $sess1 = session('customized_order_details');
        $this->assertEquals(80.000, (float)$sess1['calculation']['pending']);

        // Check calculation service
        $calc1 = PaymentCalculationService::calculateOrderPayments($order1);
        $this->assertEquals(100.000, $calc1['total_amount']);
        $this->assertEquals(20.000, $calc1['total_paid']);
        $this->assertEquals(80.000, $calc1['pending_amount']);

        // SCENARIO 2 (Case B): Total = 150, Advance Paid = 20 -> Pending = 130 -> Pending Link = 130
        $order1->order_total = 150.000;
        $order1->save();
        $calc2 = PaymentCalculationService::calculateOrderPayments($order1);
        $this->assertEquals(130.000, $calc2['pending_amount']);

        // SCENARIO 3 (Case C): Total = 77, Advance Paid = 20 -> Pending = 57 -> Pending Link = 57
        $order1->order_total = 77.000;
        $order1->save();
        $calc3 = PaymentCalculationService::calculateOrderPayments($order1);
        $this->assertEquals(57.000, $calc3['pending_amount']);

        // SCENARIO 4 (Case D & Multiple Payments): Total = 100, Payment 1 = 20 paid, Payment 2 = 10 paid -> Pending = 70
        $order1->order_total = 100.000;
        $order1->save();
        PaymentLink::create([
            'order_id' => $order1->id,
            'web_user_id' => $user->id,
            'amount' => 10.000,
            'paid_amount' => 10.000,
            'status' => 'paid',
            'payment_request_id' => 'PR_TEST1_SECOND_' . uniqid(),
            'is_advance_payment' => 0,
        ]);
        $calc4 = PaymentCalculationService::calculateOrderPayments($order1);
        $this->assertEquals(30.000, $calc4['total_paid']);
        $this->assertEquals(70.000, $calc4['pending_amount']);

        // SCENARIO 5: Submit pending-link request with tampered frontend advance = 60
        // Expected backend: Ignores frontend amount and calculates authoritative pending balance
        $editReqTampered = new Request([
            'temporary_order_id' => $tempDraft1Id,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Pending Test User',
            'cust_phone' => $user->mobile,
            'payment_type' => 1,
            'advance_payment' => 60.000, // Tampered frontend advance
            'generate_pending_payment_link' => 1,
            'custom_desc' => ['Item 100 KWD'],
            'custom_product_title' => ['Item 100 KWD'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 0,
        ]);
        $cartController->submitCustomizedOrder($editReqTampered);
        $sessTampered = session('customized_order_details');
        // Authoritative ledger total paid is 30.000, so pending must be 100 - 30 = 70.000
        $this->assertEquals(70.000, (float)$sessTampered['calculation']['pending']);
    }

    /** @test */
    public function test_39_customized_order_preview_displays_dynamic_temporary_po_qo_number()
    {
        $user = $this->createTestUser('audit_user_preview_num_');
        Auth::login($user);
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();

        // 1. Submit Purchase Order (PO) draft
        $reqPO = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'PO Preview User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Customized PO Item 1'],
            'custom_product_title' => ['Customized PO Item 1'],
            'custom_price' => [100.000],
            'custom_qty' => [1],
            'custom_shipping' => 5.000,
        ]);
        $respPO = $cartController->submitCustomizedOrder($reqPO);
        $tempPOId = $respPO->getData()->data->temporary_order_id;
        $this->assertNotEmpty($tempPOId);

        // Call customizedPreview for PO
        $previewReqPO = new Request(['source' => 'cart']);
        $previewViewPO = $cartController->customizedPreview($previewReqPO);
        $renderedHtmlPO = $previewViewPO->render();

        // Verify T-PO #<id> in header and title
        $this->assertStringContainsString("T-PO", $renderedHtmlPO);
        $this->assertStringContainsString("#" . $tempPOId, $renderedHtmlPO);
        $this->assertStringContainsString("T-PO #" . $tempPOId, $renderedHtmlPO);

        // 2. Submit Quotation Order (QO) draft
        $reqQO = new Request([
            'custom_req_type' => 'quotation',
            'cust_name' => 'QO Preview User',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 0,
            'custom_desc' => ['Customized Quotation Item 1'],
            'custom_product_title' => ['Customized Quotation Item 1'],
            'custom_price' => [250.000],
            'custom_qty' => [2],
            'custom_shipping' => 0,
        ]);
        $respQO = $cartController->submitCustomizedOrder($reqQO);
        $tempQOId = $respQO->getData()->data->temporary_order_id;
        $this->assertNotEmpty($tempQOId);

        // Call customizedPreview for QO
        $previewReqQO = new Request(['source' => 'cart']);
        $previewViewQO = $cartController->customizedPreview($previewReqQO);
        $renderedHtmlQO = $previewViewQO->render();

        // Verify T-QO #<id> in header and title
        $this->assertStringContainsString("T-QO", $renderedHtmlQO);
        $this->assertStringContainsString("#" . $tempQOId, $renderedHtmlQO);
        $this->assertStringContainsString("T-QO #" . $tempQOId, $renderedHtmlQO);

        // 3. Graceful fallback when temporary order ID is missing/invalid
        session()->put('customized_order_details.temporary_order_id', null);
        $previewViewFallback = $cartController->customizedPreview(new Request(['source' => 'cart']));
        $renderedHtmlFallback = $previewViewFallback->render();
        $this->assertNotEmpty($renderedHtmlFallback);
        $this->assertStringContainsString("customized-items", $renderedHtmlFallback);
    }

    /** @test */
    public function test_40_cart_fresh_customized_order_flow_and_multi_order_safety()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        session()->forget(['customized_order_details', 'customized_items', 'customer_order_id']);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();

        // 1. Create 1st Customized Order
        $req1 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'First Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 20.000,
            'custom_desc' => ['Order One Item'],
            'custom_product_title' => ['Product One'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'custom_shipping' => 2.000,
        ]);

        $resp1 = $cartController->submitCustomizedOrder($req1);
        $this->assertTrue($resp1->getData()->status);
        $tempOrder1Id = $resp1->getData()->data->temporary_order_id;
        $this->assertNotEmpty($tempOrder1Id);

        $tempOrder1 = TemporaryOrder::find($tempOrder1Id);
        $this->assertNotNull($tempOrder1);
        $this->assertEquals(52.000, (float)$tempOrder1->grand_total);
        $this->assertEquals(20.000, (float)$tempOrder1->advance_price);

        // 2. Simulate Cart view render and verify checkbox/edit link are NOT rendered in checked/edit state
        $cartView = $cartController->index();
        $renderedCartHtml = $cartView->render();

        // Checkbox must NOT have checked attribute
        $this->assertStringNotContainsString('class="customized-order-toggle-checkbox"' . "\n" . '                                    checked', $renderedCartHtml);
        // Edit icon must NOT be active
        $this->assertStringNotContainsString('class="customized-order-edit-link"', $renderedCartHtml);
        $this->assertStringNotContainsString('fas fa-edit customized-order-edit-icon', $renderedCartHtml);

        // 3. User creates a NEW fresh Customized Order without passing temporary_order_id
        $req2 = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Second Customer',
            'cust_phone' => $user->mobile,
            'cust_email' => $user->email,
            'payment_type' => 1,
            'advance_payment' => 35.000,
            'custom_desc' => ['Order Two Item'],
            'custom_product_title' => ['Product Two'],
            'custom_price' => [80.000],
            'custom_qty' => [2],
            'custom_shipping' => 5.000,
        ]);

        $resp2 = $cartController->submitCustomizedOrder($req2);
        $this->assertTrue($resp2->getData()->status);
        $tempOrder2Id = $resp2->getData()->data->temporary_order_id;
        $this->assertNotEmpty($tempOrder2Id);

        // Verify that TempOrder 2 is a separate new record and does NOT overwrite TempOrder 1
        $this->assertNotEquals($tempOrder1Id, $tempOrder2Id);

        $tempOrder1Fresh = TemporaryOrder::find($tempOrder1Id);
        $tempOrder2Fresh = TemporaryOrder::find($tempOrder2Id);

        $this->assertNotNull($tempOrder1Fresh);
        $this->assertNotNull($tempOrder2Fresh);

        $this->assertEquals(52.000, (float)$tempOrder1Fresh->grand_total);
        $this->assertEquals(20.000, (float)$tempOrder1Fresh->advance_price);
        $this->assertEquals('First Customer', $tempOrder1Fresh->customer_name);

        $this->assertEquals(165.000, (float)$tempOrder2Fresh->grand_total);
        $this->assertEquals(35.000, (float)$tempOrder2Fresh->advance_price);
        $this->assertEquals('Second Customer', $tempOrder2Fresh->customer_name);
    }

    /**
     * @test
     * Audit: Verify Customized Order Modal renders completely fresh with empty fields and Reset button
     */
    public function test_customized_order_modal_renders_fresh_with_reset_button()
    {
        $this->withoutExceptionHandling();
        $storeUser = $this->createTestUser();
        $storeUser->user_type = 'physical_store_user';
        $storeUser->save();
        $this->actingAs($storeUser);

        // Put previous customized order details in session to test that view does NOT prefill them into the modal
        session([
            'customized_order_details' => [
                'temporary_order_id' => 9999,
                'type' => 'quotation',
                'customer' => [
                    'phone' => '99887766',
                    'email' => 'old_cust@example.com',
                    'name' => 'Old Customer',
                    'block' => '5',
                    'street' => 'Old Street',
                    'house_number' => '12',
                ],
                'items' => [
                    [
                        'product_title' => 'Old Custom Item',
                        'price' => 50.000,
                        'quantity' => 2,
                        'total' => 100.000,
                    ]
                ],
            ]
        ]);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $view = $cartController->index();
        $content = $view->render();

        // 1. Reset, Close, and Save buttons must exist in modal footer with correct order: Close -> Reset -> Save
        $this->assertStringContainsString('id="reset_customized_order"', $content, 'Reset button must be present in modal');
        $this->assertStringContainsString('<button type="button" class="btn btn-primary" data-dismiss="modal">', $content, 'Close button must use btn-primary styling');
        $this->assertStringContainsString('id="submit_customized_order"', $content, 'Save button must be present');

        $closePos = strpos($content, 'data-dismiss="modal"');
        $resetPos = strpos($content, 'id="reset_customized_order"');
        $savePos = strpos($content, 'id="submit_customized_order"');
        $this->assertTrue($closePos !== false && $resetPos !== false && $savePos !== false);
        $this->assertTrue($closePos < $resetPos, 'Close button must come before Reset button');
        $this->assertTrue($resetPos < $savePos, 'Reset button must come before Save button');

        // 2. temporary_order_id hidden input must be empty, not 9999
        $this->assertStringContainsString('<input type="hidden" name="temporary_order_id" id="temporary_order_id" value="">', $content);
        $this->assertStringNotContainsString('value="9999"', $content);

        // 3. Customer inputs inside modal form must be empty
        $this->assertStringContainsString('name="cust_phone" value=""', $content);
        $this->assertStringContainsString('name="cust_email" value=""', $content);
        $this->assertStringContainsString('name="cust_name" value=""', $content);
        $this->assertStringNotContainsString('value="99887766"', $content);
        $this->assertStringNotContainsString('value="Old Customer"', $content);
    }

    /**
     * @test
     * Audit: Verify Preview Order button visibility based on session temporary order existence
     */
    public function test_preview_order_button_visibility_and_functionality()
    {
        $this->withoutExceptionHandling();
        $storeUser = $this->createTestUser();
        $storeUser->user_type = 'physical_store_user';
        $storeUser->save();
        $this->actingAs($storeUser);

        // Case A: NO temporary order in session -> Preview Order button is hidden
        session()->forget('customized_order_details');
        $cartController = new \App\Http\Controllers\Web\Cart\CartController();
        $viewWithout = $cartController->index();
        $contentWithout = $viewWithout->render();

        $this->assertStringContainsString('id="customized_pdf_preview_action" style="display:none;"', $contentWithout);

        // Case B: Temporary order exists in session -> Preview Order button is visible
        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'customer' => [
                    'phone' => '55512345',
                    'name' => 'Preview Customer',
                ],
                'items' => [
                    [
                        'product_title' => 'Customized Item For Preview',
                        'price' => 35.000,
                        'quantity' => 1,
                        'total' => 35.000,
                    ]
                ],
            ]
        ]);

        $viewWith = $cartController->index();
        $contentWith = $viewWith->render();

        $this->assertStringContainsString('id="customized_pdf_preview_action" style=""', $contentWith);
        $this->assertStringContainsString(route('web.cart.customized_preview', ['source' => 'cart']), $contentWith);

        // Case C: Test Preview Order endpoint directly
        $req = Request::create(route('web.cart.customized_preview'), 'GET', ['source' => 'cart']);
        $previewResp = $cartController->customizedPreview($req);
        $previewContent = $previewResp->render();

        $this->assertStringContainsString('CUSTOMIZED ITEM FOR PREVIEW', $previewContent);
        $this->assertStringContainsString('35.000', $previewContent);
    }

    /**
     * @test
     * Audit: Verify Cart Review inline buttons below Select Payment Method are ordered: Delete -> Preview -> Buy Now
     */
    public function test_cart_review_payment_store_actions_button_ordering()
    {
        $this->withoutExceptionHandling();
        $storeUser = $this->createTestUser();
        $storeUser->user_type = 'physical_store_user';
        $storeUser->save();
        $this->actingAs($storeUser);

        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'customer' => [
                    'phone' => '99887766',
                    'name' => 'Review Customer',
                ],
                'items' => [
                    [
                        'product_title' => 'Custom Item',
                        'price' => 25.000,
                        'quantity' => 1,
                        'total' => 25.000,
                    ]
                ],
            ]
        ]);

        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $checkoutController = new \App\Http\Controllers\Web\Checkout\CheckoutController();
        $view = $checkoutController->cartReview();
        $content = $view->render();

        // Check payment-method-store-actions-row exists
        $this->assertStringContainsString('payment-method-store-actions-row', $content);

        // Find positions within payment-method-store-actions-row
        $rowPos = strpos($content, '<div class="payment-method-store-actions-row">');
        $this->assertNotFalse($rowPos);

        $rowSubstr = substr($content, $rowPos, 2500);
        $deletePos = strpos($rowSubstr, 'delete-customized-invoice');
        $previewPos = strpos($rowSubstr, 'customized_pdf_preview_action');
        $buyNowButtonPos = strpos($rowSubstr, 'continue-action-button payment-store-action-btn');

        $this->assertNotFalse($deletePos, 'Delete Invoice must be in store actions row');
        $this->assertNotFalse($previewPos, 'Preview Invoice must be in store actions row');
        $this->assertNotFalse($buyNowButtonPos, 'Buy Now must be in store actions row');

        $this->assertTrue($deletePos < $previewPos, 'Delete Invoice must come before Preview Invoice');
        $this->assertTrue($previewPos < $buyNowButtonPos, 'Preview Invoice must come before Buy Now');
    }

    /** @test */
    public function test_44_advance_payment_validation_behavior()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();

        // 1. Advance (10.000) > Total (7.998) -> Submit MUST fail
        $invalidReq = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_phone' => '96590000000',
            'cust_name' => 'Advance Test User',
            'custom_product_title' => ['Custom Dress'],
            'custom_desc' => ['Custom Dress Description'],
            'custom_price' => [7.998],
            'custom_qty' => [1],
            'custom_shipping' => 0,
            'advance_payment' => 10.000,
        ]);

        $invalidResp = $cartController->submitCustomizedOrder($invalidReq);
        $invalidData = json_decode($invalidResp->getContent(), true);

        $this->assertFalse($invalidData['status'], 'Saving must be blocked when advance payment > total order value');
        $this->assertEquals(422, $invalidResp->getStatusCode());
        $this->assertEquals('advance_payment', $invalidData['field']);

        // 2. User corrects Advance (7.000) <= Total (7.998) -> Submit MUST succeed
        $validReq = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_phone' => '96590000000',
            'cust_name' => 'Advance Test User',
            'custom_product_title' => ['Custom Dress'],
            'custom_desc' => ['Custom Dress Description'],
            'custom_price' => [7.998],
            'custom_qty' => [1],
            'custom_shipping' => 0,
            'advance_payment' => 7.000,
        ]);

        $validResp = $cartController->submitCustomizedOrder($validReq);
        $validData = json_decode($validResp->getContent(), true);

        $this->assertTrue($validData['status'], 'Saving must succeed when advance payment <= total order value');
        $this->assertNotEmpty($validData['data']['temporary_order_id']);

        // 3. Verify blade view contains persistent advance payment validation logic
        $indexContent = file_get_contents(resource_path('views/web/cart/index.blade.php'));
        $this->assertStringContainsString('advance-payment-exceeds-error', $indexContent);
        $this->assertStringContainsString('window.validateAdvancePayment', $indexContent);
        $this->assertStringNotContainsString('setTimeout(function() { $field.trigger(\'focus\'); }, 350); input.next(\'.advance-payment-exceeds-error\').remove()', $indexContent);
    }

    /** @test */
    public function test_45_temporary_orders_admin_module_complete_flow()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Create Temporary Order without actual order
        $tempOrder1 = \App\UserModel\TemporaryOrder::create([
            'user_id' => $user->id,
            'type' => 'purchase_order',
            'custom_order_number' => '9901',
            'status' => 'pending',
            'customer_name' => 'Admin Temp Customer',
            'customer_phone' => '96512345678',
            'customer_email' => 'temp_admin@example.com',
            'subtotal' => 50.000,
            'shipping_price' => 5.000,
            'grand_total' => 55.000,
            'advance_price' => 20.000,
            'pending_amount' => 35.000,
        ]);

        \App\UserModel\TemporaryOrderDetail::create([
            'temporary_order_id' => $tempOrder1->id,
            'product_name' => 'Custom Silk Abaya',
            'item_code' => 'AB-001',
            'description' => 'Black silk custom abaya with embroidery',
            'color' => 'Black',
            'size' => 'M',
            'price' => 50.000,
            'quantity' => 1,
            'total_price' => 50.000,
        ]);

        // Create Temporary Order WITH linked actual order
        $tempOrder2 = \App\UserModel\TemporaryOrder::create([
            'user_id' => $user->id,
            'type' => 'quotation',
            'custom_order_number' => '9902',
            'status' => 'processing',
            'customer_name' => 'Converted Customer',
            'customer_phone' => '96587654321',
            'customer_email' => 'converted@example.com',
            'subtotal' => 100.000,
            'shipping_price' => 0.000,
            'grand_total' => 100.000,
            'advance_price' => 50.000,
            'pending_amount' => 50.000,
        ]);

        $actualOrder = Order::create([
            'user_id' => $user->id,
            'order_total' => 100.000,
            'paid_amount' => 50.000,
            'pending_amount' => 50.000,
            'order_status' => 1, // Processing
            'custom_order_type' => 'quotation',
            'custom_order_details' => json_encode([
                'temporary_order_id' => $tempOrder2->id,
                'customer' => ['name' => 'Converted Customer'],
            ]),
        ]);

        $adminOrderController = new \App\Http\Controllers\Admin\Order\OrderController();

        // 1. Listing Page Test
        $listReq = new Request([]);
        $listResp = $adminOrderController->temporaryOrderData($listReq);
        $this->assertEquals('admin.order.order.temporary-orders-data', $listResp->name());
        $viewData = $listResp->getData();
        $this->assertNotEmpty($viewData['orders']);

        // Check rendered HTML contains columns and data
        $listHtml = $listResp->render();
        $this->assertStringContainsString('Admin Temp Customer', $listHtml);
        $this->assertStringContainsString('Converted Customer', $listHtml);
        $this->assertStringContainsString('temp_admin@example.com', $listHtml);
        $this->assertStringContainsString('PO #9901', $listHtml);
        $this->assertStringContainsString('QO #9902', $listHtml);
        $this->assertStringContainsString('#' . $actualOrder->id, $listHtml); // Actual Order ID column

        // 2. Sidebar Navigation Test
        $sidebarContent = file_get_contents(resource_path('views/layout/sidebar.blade.php'));
        $this->assertStringContainsString('admin.order.temporary-orders', $sidebarContent);
        $this->assertStringContainsString('admin.temporary-orders', $sidebarContent);
    }

    /** @test */
    public function test_46_temporary_orders_view_decision_and_deletion()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $tempOrderSolo = \App\UserModel\TemporaryOrder::create([
            'user_id' => $user->id,
            'type' => 'purchase_order',
            'custom_order_number' => '9903',
            'status' => 'pending',
            'customer_name' => 'Solo Temp Customer',
            'customer_phone' => '96511112222',
            'customer_email' => 'solo@example.com',
            'city' => 'Kuwait City',
            'block' => '3',
            'street' => 'Salem Street',
            'house_number' => '12',
            'subtotal' => 40.000,
            'shipping_price' => 2.000,
            'grand_total' => 42.000,
            'advance_price' => 10.000,
            'pending_amount' => 32.000,
        ]);

        \App\UserModel\TemporaryOrderDetail::create([
            'temporary_order_id' => $tempOrderSolo->id,
            'product_name' => 'Evening Dress',
            'item_code' => 'EV-9903',
            'description' => 'Navy Blue Silk Gown',
            'color' => 'Navy Blue',
            'size' => 'L',
            'price' => 40.000,
            'quantity' => 1,
            'total_price' => 40.000,
        ]);

        $tempOrderLinked = \App\UserModel\TemporaryOrder::create([
            'user_id' => $user->id,
            'type' => 'purchase_order',
            'custom_order_number' => '9904',
            'status' => 'processing',
            'customer_name' => 'Linked Temp Customer',
            'subtotal' => 80.000,
            'shipping_price' => 0.000,
            'grand_total' => 80.000,
            'advance_price' => 30.000,
            'pending_amount' => 50.000,
        ]);

        $linkedActualOrder = Order::create([
            'user_id' => $user->id,
            'order_total' => 80.000,
            'paid_amount' => 30.000,
            'pending_amount' => 50.000,
            'order_status' => 1,
            'custom_order_type' => 'purchase_order',
            'custom_order_details' => json_encode([
                'temporary_order_id' => $tempOrderLinked->id,
            ]),
        ]);

        $adminOrderController = new \App\Http\Controllers\Admin\Order\OrderController();

        // 1. View Decision Test - CASE 1 (Linked to Actual Order -> Redirect to actual order view)
        $respLinked = $adminOrderController->viewTemporaryOrderRoute($tempOrderLinked->id);
        $this->assertEquals(302, $respLinked->getStatusCode());
        $this->assertEquals(route('admin.order.customized-pending-order.vieworder', ['id' => $linkedActualOrder->id]), $respLinked->getTargetUrl());

        // 2. View Decision Test - CASE 2 (NOT linked -> Redirect to Temporary Order view page)
        $respSolo = $adminOrderController->viewTemporaryOrderRoute($tempOrderSolo->id);
        $this->assertEquals(302, $respSolo->getStatusCode());
        $this->assertEquals(route('admin.order.temporary-orders.view', ['id' => $tempOrderSolo->id]), $respSolo->getTargetUrl());

        // 3. Detail Page View Test (CASE 2 view render)
        $detailResp = $adminOrderController->temporaryOrderDetail($tempOrderSolo->id);
        $this->assertEquals('admin.order.order.temporary-detail', $detailResp->name());
        $detailHtml = $detailResp->render();
        $this->assertStringContainsString('Solo Temp Customer', $detailHtml);
        $this->assertStringContainsString('Kuwait City', $detailHtml);
        $this->assertStringContainsString('Salem Street', $detailHtml);
        $this->assertStringContainsString('EV-9903', $detailHtml);
        $this->assertStringContainsString(\App\Helpers\Helper::formatCustomizedDescription('Navy Blue Silk Gown'), $detailHtml);
        $this->assertStringContainsString('42.000', $detailHtml);

        // 4. Delete Action Test
        $deleteResp = $adminOrderController->deleteTemporaryOrder($tempOrderSolo->id);
        $this->assertEquals(302, $deleteResp->getStatusCode());
        $this->assertEquals(route('admin.order.temporary-orders'), $deleteResp->getTargetUrl());
        $this->assertSoftDeleted('temporary_orders', ['id' => $tempOrderSolo->id]);
        $this->assertSoftDeleted('temporary_order_details', ['temporary_order_id' => $tempOrderSolo->id]);

        // 5. Verify actual order is NOT deleted
        $this->assertDatabaseHas('orders', ['id' => $linkedActualOrder->id]);
    }

    /** @test */
    public function test_47_temporary_orders_edit_prefill_and_update_behavior()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $tempOrder = \App\UserModel\TemporaryOrder::create([
            'user_id' => $user->id,
            'type' => 'purchase_order',
            'custom_order_number' => '9905',
            'status' => 'pending',
            'customer_name' => 'Original Customer',
            'customer_phone' => '96512345678',
            'customer_email' => 'original@example.com',
            'city' => 'Kuwait City',
            'block' => '4',
            'street' => 'Hamad Street',
            'house_number' => '10',
            'subtotal' => 50.000,
            'shipping_price' => 2.000,
            'discount_type' => 'flat',
            'discount_val' => 5.000,
            'discount_amount' => 5.000,
            'grand_total' => 47.000,
            'advance_price' => 20.000,
            'pending_amount' => 27.000,
        ]);

        \App\UserModel\TemporaryOrderDetail::create([
            'temporary_order_id' => $tempOrder->id,
            'product_name' => 'Silk Abaya',
            'item_code' => 'AB-001',
            'description' => 'Black Silk Abaya with lace',
            'color' => 'Black',
            'size' => 'M',
            'price' => 50.000,
            'quantity' => 1,
            'total_price' => 50.000,
        ]);

        $adminOrderController = new \App\Http\Controllers\Admin\Order\OrderController();

        // 1. Verify View contains Status Tag beside Type badge and Edit Button
        $detailResp = $adminOrderController->temporaryOrderDetail($tempOrder->id);
        $detailHtml = $detailResp->render();

        $this->assertStringContainsString('T-PO #' . $tempOrder->id, $detailHtml);
        $this->assertStringContainsString('Purchase order', $detailHtml);
        $this->assertStringContainsString('Pending', $detailHtml);
        $this->assertStringContainsString('Edit Purchase Order', $detailHtml);
        $this->assertStringNotContainsString('Back to List', $detailHtml);
        $this->assertStringContainsString('id="customizedOrderModal"', $detailHtml);

        // 2. Perform Update on the Existing Temporary Order
        $updateRequest = new \Illuminate\Http\Request();
        $updateRequest->merge([
            'temporary_order_id' => $tempOrder->id,
            'cust_name' => 'Updated Customer Name',
            'cust_phone' => '96598765432',
            'cust_email' => 'updated@example.com',
            'cust_city' => 'Salmiya',
            'cust_block' => '7',
            'cust_street' => 'Salem Al Mubarak',
            'cust_house_number' => '25',
            'custom_shipping' => 3.000,
            'custom_discount_type' => 'flat',
            'custom_discount_val' => 10.000,
            'advance_payment' => 25.000,
            'custom_product_title' => ['Luxury Kaftan'],
            'custom_item_code' => ['KF-777'],
            'custom_desc' => ['Golden embroidered kaftan'],
            'custom_color' => ['Gold'],
            'custom_size' => ['XL'],
            'custom_price' => [100.000],
            'custom_qty' => [2],
        ]);

        $initialTempOrderCount = \App\UserModel\TemporaryOrder::count();

        $updateResp = $adminOrderController->updateTemporaryOrder($updateRequest, $tempOrder->id);
        $this->assertEquals(200, $updateResp->getStatusCode());
        $responseData = $updateResp->getData(true);
        $this->assertTrue($responseData['status']);

        // 3. Verify NO Duplicate Temporary Order is Created
        $this->assertEquals($initialTempOrderCount, \App\UserModel\TemporaryOrder::count());

        // 4. Verify Existing Temporary Order was updated accurately
        $refreshedTemp = \App\UserModel\TemporaryOrder::with('details')->find($tempOrder->id);
        $this->assertEquals('Updated Customer Name', $refreshedTemp->customer_name);
        $this->assertEquals('96598765432', $refreshedTemp->customer_phone);
        $this->assertEquals('Salmiya', $refreshedTemp->city);
        $this->assertEquals(200.000, (float)$refreshedTemp->subtotal);
        $this->assertEquals(3.000, (float)$refreshedTemp->shipping_price);
        $this->assertEquals(10.000, (float)$refreshedTemp->discount_amount);
        $this->assertEquals(193.000, (float)$refreshedTemp->grand_total);
        $this->assertEquals(25.000, (float)$refreshedTemp->advance_price);
        $this->assertEquals(168.000, (float)$refreshedTemp->pending_amount);

        // 5. Verify Item Details were replaced without duplicating rows
        $this->assertEquals(1, $refreshedTemp->details->count());
        $item = $refreshedTemp->details->first();
        $this->assertEquals('Luxury Kaftan', $item->product_name);
        $this->assertEquals('KF-777', $item->item_code);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(200.000, (float)$item->total_price);
    }

    /** @test */
    public function test_55_cart_review_payment_methods_display_with_pending_amount()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Create actual order with Total = 40.000, Advance Paid = 38.000
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_type = 'custom';
        $order->custom_order_type = 'purchase_order';
        $order->order_total = 40.000;
        $order->paid_amount = 38.000;
        $order->is_advance_payment_done = 1;
        $order->order_status = 7; // Pending
        $order->save();

        // Create advance payment link (paid)
        $advanceLink = new PaymentLink();
        $advanceLink->order_id = $order->id;
        $advanceLink->amount = 38.000;
        $advanceLink->payment_type = 'advance';
        $advanceLink->status = 'paid';
        $advanceLink->save();

        // Create pending payment link for 2.000 KWD (unpaid / pending)
        $pendingLink = new PaymentLink();
        $pendingLink->order_id = $order->id;
        $pendingLink->amount = 2.000;
        $pendingLink->payment_type = 'pending';
        $pendingLink->status = 'pending'; // NOT paid
        $pendingLink->save();

        // Create linked temporary order
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->grand_total = 40.000;
        $temp->advance_price = 38.000;
        $temp->pending_amount = 2.000;
        $temp->custom_order_details = json_encode(['order_id' => $order->id]);
        $temp->save();

        // Setup session customized_order_details as if customer visits Cart Review with generate_pending_link = 1 (ON)
        $customOrderSession = [
            'type' => 'purchase_order',
            'temporary_order_id' => $temp->id,
            'order_id' => $order->id,
            'calculation' => [
                'grand_total' => 40.000,
                'shipping_price' => 0.000,
                'discount' => 0.000,
                'subtotal' => 40.000,
                'generate_pending_link' => 1,
            ],
            'payment' => [
                'advance' => '38.000',
                'pending_amount' => '2.000',
                'generate_pending_link' => 1,
            ],
            'generate_pending_link' => 1,
            'customer' => [
                'name' => 'Test Customer',
                'phone' => '96512345678',
                'email' => 'test@example.com',
            ],
            'address' => [
                'area_id' => 1,
                'block' => '1',
                'street' => 'Street',
                'building' => '1',
            ],
            'items' => [
                [
                    'product_title' => 'Custom Item',
                    'quantity' => 1,
                    'price' => 40.000,
                    'total' => 40.000,
                ]
            ]
        ];
        session(['customized_order_details' => $customOrderSession]);

        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());
        $checkoutController = new CheckoutController();
        $request = new Request();
        $cartReviewResp = $checkoutController->cartReview($request);
        $cartReviewHtml = $cartReviewResp->render();

        // Case 1: Total = 40, Paid = 38, Pending = 2, Checkbox = ON -> Payment methods MUST be displayed!
        $this->assertStringContainsString('cart-products payment-gateway', $cartReviewHtml);
        $this->assertStringContainsString('name="paymentMethod"', $cartReviewHtml);

        // Case 2: EDIT: Checkbox = OFF (generate_pending_link = 0) -> Payment methods must NOT be displayed!
        $customOrderSession['payment']['generate_pending_link'] = 0;
        $customOrderSession['calculation']['generate_pending_link'] = 0;
        $customOrderSession['generate_pending_link'] = 0;
        session(['customized_order_details' => $customOrderSession]);

        $cartReviewRespOff = $checkoutController->cartReview($request);
        $cartReviewHtmlOff = $cartReviewRespOff->render();
        $this->assertStringNotContainsString('cart-products payment-gateway', $cartReviewHtmlOff);

        // Case 3: Rejected payment link for pending amount with Checkbox = ON -> Payment methods visible for retry
        $customOrderSession['payment']['generate_pending_link'] = 1;
        $customOrderSession['calculation']['generate_pending_link'] = 1;
        $customOrderSession['generate_pending_link'] = 1;
        session(['customized_order_details' => $customOrderSession]);
        $pendingLink->status = 'rejected';
        $pendingLink->save();

        $cartReviewResp2 = $checkoutController->cartReview($request);
        $cartReviewHtml2 = $cartReviewResp2->render();
        $this->assertStringContainsString('cart-products payment-gateway', $cartReviewHtml2);

        // Case 4: Fully paid (Total = 40, Paid = 40, Pending = 0) -> Payment methods hidden
        $pendingLink->status = 'paid';
        $pendingLink->save();
        $order->paid_amount = 40.000;
        $order->save();

        $cartReviewResp3 = $checkoutController->cartReview($request);
        $cartReviewHtml3 = $cartReviewResp3->render();
        $this->assertStringNotContainsString('cart-products payment-gateway', $cartReviewHtml3);
    }

    /** @test */
    public function test_56_create_mode_mandatory_checkbox_and_edit_mode_toggle()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();

        // 1. CREATE MODE: Submit new purchase order without generate_pending_payment_link -> Expect 200 success (Checkbox is NOT required in create mode)
        $createRequestWithoutCheckbox = new Request();
        $createRequestWithoutCheckbox->merge([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'New Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'newcust@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 2.000,
            'advance_payment' => 10.000,
            'custom_product_title' => ['Custom Dress'],
            'custom_desc' => ['Custom dress description'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'generate_pending_payment_link' => '0', // Not checked
        ]);

        $createResp = $cartController->submitCustomizedOrder($createRequestWithoutCheckbox);
        $this->assertEquals(200, $createResp->getStatusCode());
        $createData = $createResp->getData(true);
        $this->assertTrue($createData['status']);

        // 2. CREATE MODE: Submit with generate_pending_payment_link = 1 -> Expect 200 success
        $createRequestWithCheckbox = new Request();
        $createRequestWithCheckbox->merge([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'New Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'newcust@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 2.000,
            'advance_payment' => 10.000,
            'custom_product_title' => ['Custom Dress'],
            'custom_desc' => ['Custom dress description'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'generate_pending_payment_link' => '1', // Checked
        ]);

        $createSuccessResp = $cartController->submitCustomizedOrder($createRequestWithCheckbox);
        $this->assertEquals(200, $createSuccessResp->getStatusCode());
        $successData = $createSuccessResp->getData(true);
        $this->assertTrue($successData['status']);
        $createdTempId = $successData['data']['temporary_order_id'];
        $this->assertNotEmpty($createdTempId);

        // Verify session & TemporaryOrder has generate_pending_link = 0 (Checkbox is NOT applicable in create mode)
        $savedTemp = \App\UserModel\TemporaryOrder::find($createdTempId);
        $savedDetails = json_decode($savedTemp->custom_order_details, true);
        $this->assertEquals(0, $savedDetails['payment']['generate_pending_link']);

        // 3. EDIT MODE: Update existing temporary order with generate_pending_payment_link = 0 (Unchecked)
        $editRequestUnchecked = new Request();
        $editRequestUnchecked->merge([
            'temporary_order_id' => $createdTempId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'New Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'newcust@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 2.000,
            'advance_payment' => 10.000,
            'custom_product_title' => ['Custom Dress'],
            'custom_desc' => ['Custom dress description'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
            'generate_pending_payment_link' => '0', // Unchecked during edit
        ]);

        $editResp = $cartController->submitCustomizedOrder($editRequestUnchecked);
        $this->assertEquals(200, $editResp->getStatusCode());
        $editData = $editResp->getData(true);
        $this->assertTrue($editData['status']);

        // Verify that in edit mode, generate_pending_link is updated to 0 without 422 error
        $refreshedTemp = \App\UserModel\TemporaryOrder::find($createdTempId);
        $refreshedDetails = json_decode($refreshedTemp->custom_order_details, true);
        $this->assertEquals(0, $refreshedDetails['payment']['generate_pending_link']);
    }

    /** @test */
    public function test_50_pay_at_shop_creates_new_customized_order_and_payment_link()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Setup customized order session
        $customSession = [
            'type' => 'purchase_order',
            'temporary_order_id' => null,
            'customer' => [
                'name' => 'Pay At Shop Customer',
                'phone' => '96511223344',
                'email' => 'payatshop@example.com',
                'country_id' => 1,
                'city' => 'Kuwait City',
                'block' => '1',
                'street' => 'Street 1',
                'house_number' => '10'
            ],
            'items' => [
                [
                    'product_title' => 'Custom Blazer',
                    'price' => 100.000,
                    'quantity' => 1,
                    'total' => 100.000,
                    'color' => 'Navy',
                    'size' => 'L'
                ]
            ],
            'payment' => [
                'subtotal' => 100.000,
                'shipping' => 5.000,
                'discount' => 0.000,
                'grand_total' => 105.000,
                'advance' => 30.000,
                'pending' => 75.000,
                'generate_pending_link' => 1
            ],
            'calculation' => [
                'grand_total' => 105.000,
                'advance' => 30.000,
                'pending' => 75.000
            ]
        ];
        session(['customized_order_details' => $customSession]);
        session(['paymentMethod' => 6]); // Pay At Shop

        $checkoutController = new CheckoutController();
        $orderId = $checkoutController->createOrder('', config('custom_web.Pending', 7), false, 6);

        $this->assertNotEmpty($orderId);
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals('custom', $order->order_type);
        $this->assertEquals(6, $order->payment_method);
        $this->assertEquals(1, $order->is_advance_payment_done);
        $this->assertEquals(30.000, (float)$order->paid_amount);
        $this->assertEquals(75.000, (float)$order->pending_amount);
        $this->assertEquals(105.000, (float)$order->order_total);

        // Verify PaymentLink paid entry created
        $paymentLink = PaymentLink::where('order_id', $order->id)->where('status', 'paid')->first();
        $this->assertNotNull($paymentLink);
        $this->assertEquals(30.000, (float)$paymentLink->paid_amount);
        $this->assertEquals(1, $paymentLink->is_advance_payment);
    }

    /** @test */
    public function test_51_edited_customized_order_pay_at_shop_updates_existing_order_without_duplicate()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Create Initial Actual Order
        $existingOrder = new Order();
        $existingOrder->user_id = $user->id;
        $existingOrder->order_type = 'custom';
        $existingOrder->order_status = 7; // Pending
        $existingOrder->payment_method = 6;
        $existingOrder->order_price = 100.000;
        $existingOrder->delivery_price = 5.000;
        $existingOrder->order_total = 105.000;
        $existingOrder->paid_amount = 0.000;
        $existingOrder->pending_amount = 105.000;
        $existingOrder->is_advance_payment_done = 0;
        $existingOrder->created_at = now();
        $existingOrder->save();

        // Create linked TemporaryOrder
        $tempOrder = new TemporaryOrder();
        $tempOrder->user_id = $user->id;
        $tempOrder->type = 'purchase_order';
        $tempOrder->status = 'pending';
        $tempOrder->subtotal = 100.000;
        $tempOrder->shipping_price = 5.000;
        $tempOrder->grand_total = 105.000;
        $tempOrder->advance_price = 0.000;
        $tempOrder->pending_amount = 105.000;
        $tempOrder->custom_order_details = json_encode([
            'order_id' => $existingOrder->id,
            'temporary_order_id' => null,
            'payment' => [
                'advance' => 0.000,
                'pending' => 105.000,
                'generate_pending_link' => 1
            ]
        ]);
        $tempOrder->save();

        // Update temp order details with its own ID
        $tempDetails = json_decode($tempOrder->custom_order_details, true);
        $tempDetails['temporary_order_id'] = $tempOrder->id;
        $tempOrder->custom_order_details = json_encode($tempDetails);
        $tempOrder->save();

        // Link temporary order to actual order in order details
        $existingOrder->custom_order_details = json_encode([
            'order_id' => $existingOrder->id,
            'temporary_order_id' => $tempOrder->id
        ]);
        $existingOrder->save();

        $initialOrdersCount = Order::where('user_id', $user->id)->count();
        $this->assertEquals(1, $initialOrdersCount);

        // 2. Simulate User Editing Customized Order & Selecting Pay At Shop with Advance = 40
        $customSession = [
            'type' => 'purchase_order',
            'temporary_order_id' => $tempOrder->id,
            'customer' => [
                'name' => 'Updated Customer',
                'phone' => '96511223344',
                'email' => 'updated@example.com',
                'country_id' => 1,
                'city' => 'Kuwait City',
                'block' => '2',
                'street' => 'Street 2',
                'house_number' => '20'
            ],
            'items' => [
                [
                    'product_title' => 'Updated Custom Suit',
                    'price' => 150.000,
                    'quantity' => 1,
                    'total' => 150.000,
                    'color' => 'Black',
                    'size' => 'XL'
                ]
            ],
            'payment' => [
                'subtotal' => 150.000,
                'shipping' => 5.000,
                'discount' => 0.000,
                'grand_total' => 155.000,
                'advance' => 40.000,
                'pending' => 115.000,
                'generate_pending_link' => 1
            ],
            'calculation' => [
                'grand_total' => 155.000,
                'advance' => 40.000,
                'pending' => 115.000
            ]
        ];
        session(['customized_order_details' => $customSession]);
        session(['paymentMethod' => 6]); // Pay At Shop

        $checkoutController = new CheckoutController();
        $updatedOrderId = $checkoutController->createOrder('', config('custom_web.Pending', 7), false, 6);

        // Assert that the returned order ID is the same existing order
        $this->assertEquals($existingOrder->id, $updatedOrderId);

        // Verify NO duplicate orders were created
        $finalOrdersCount = Order::where('user_id', $user->id)->count();
        $this->assertEquals(1, $finalOrdersCount);

        // Verify order attributes were properly updated
        $refreshedOrder = Order::find($existingOrder->id);
        $this->assertEquals(155.000, (float)$refreshedOrder->order_total);
        $this->assertEquals(40.000, (float)$refreshedOrder->paid_amount);
        $this->assertEquals(115.000, (float)$refreshedOrder->pending_amount);
        $this->assertEquals(1, $refreshedOrder->is_advance_payment_done);
        $this->assertEquals(6, $refreshedOrder->payment_method);

        // Verify Pay At Shop ledger entry created
        $paymentLink = PaymentLink::where('order_id', $existingOrder->id)->where('status', 'paid')->first();
        $this->assertNotNull($paymentLink);
        $this->assertEquals(40.000, (float)$paymentLink->paid_amount);

        // Verify TemporaryOrder was synced
        $refreshedTemp = TemporaryOrder::find($tempOrder->id);
        $this->assertEquals(155.000, (float)$refreshedTemp->grand_total);
        $this->assertEquals(40.000, (float)$refreshedTemp->advance_price);
        $this->assertEquals(115.000, (float)$refreshedTemp->pending_amount);
    }

    /** @test */
    public function test_52_pay_at_shop_with_zero_pending_amount()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 100% advance payment (full payment at shop)
        $customSession = [
            'type' => 'purchase_order',
            'temporary_order_id' => null,
            'customer' => [
                'name' => 'Full Paid Customer',
                'phone' => '96511223344',
                'email' => 'fullpaid@example.com'
            ],
            'items' => [
                [
                    'product_title' => 'Custom Abaya',
                    'price' => 80.000,
                    'quantity' => 1,
                    'total' => 80.000
                ]
            ],
            'payment' => [
                'subtotal' => 80.000,
                'shipping' => 0.000,
                'discount' => 0.000,
                'grand_total' => 80.000,
                'advance' => 80.000,
                'pending' => 0.000,
                'generate_pending_link' => 0
            ],
            'calculation' => [
                'grand_total' => 80.000,
                'advance' => 80.000,
                'pending' => 0.000
            ]
        ];
        session(['customized_order_details' => $customSession]);
        session(['paymentMethod' => 6]);

        $checkoutController = new CheckoutController();
        $orderId = $checkoutController->createOrder('', config('custom_web.Pending', 7), false, 6);

        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals(80.000, (float)$order->paid_amount);
        $this->assertEquals(0.000, (float)$order->pending_amount);
        $this->assertEquals(1, $order->is_advance_payment_done);
    }

    /** @test */
    public function test_53_temp_order_without_actual_order_pay_at_shop_creates_order_and_links()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Temp order that does not have an actual order linked yet
        $tempOrder = new TemporaryOrder();
        $tempOrder->user_id = $user->id;
        $tempOrder->type = 'purchase_order';
        $tempOrder->status = 'pending';
        $tempOrder->subtotal = 50.000;
        $tempOrder->shipping_price = 3.000;
        $tempOrder->grand_total = 53.000;
        $tempOrder->advance_price = 20.000;
        $tempOrder->pending_amount = 33.000;
        $tempOrder->custom_order_details = json_encode([
            'order_id' => null,
            'temporary_order_id' => null,
            'payment' => [
                'advance' => 20.000,
                'pending' => 33.000,
                'generate_pending_link' => 1
            ]
        ]);
        $tempOrder->save();

        $customSession = [
            'type' => 'purchase_order',
            'temporary_order_id' => $tempOrder->id,
            'customer' => [
                'name' => 'Temp Only Cust',
                'phone' => '96511223344',
                'email' => 'temponly@example.com'
            ],
            'items' => [
                [
                    'product_title' => 'Custom Item',
                    'price' => 50.000,
                    'quantity' => 1,
                    'total' => 50.000
                ]
            ],
            'payment' => [
                'subtotal' => 50.000,
                'shipping' => 3.000,
                'discount' => 0.000,
                'grand_total' => 53.000,
                'advance' => 20.000,
                'pending' => 33.000,
                'generate_pending_link' => 1
            ]
        ];
        session(['customized_order_details' => $customSession]);
        session(['paymentMethod' => 4]); // Pay At Store

        $checkoutController = new CheckoutController();
        $orderId = $checkoutController->createOrder('', config('custom_web.Pending', 7), false, 4);

        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals(20.000, (float)$order->paid_amount);
        $this->assertEquals(33.000, (float)$order->pending_amount);
        $this->assertEquals(1, $order->is_advance_payment_done);
        $this->assertEquals(4, $order->payment_method);

        // Verify temp order is linked and synced
        $refreshedTemp = TemporaryOrder::find($tempOrder->id);
        $this->assertEquals(20.000, (float)$refreshedTemp->advance_price);
        $this->assertEquals(33.000, (float)$refreshedTemp->pending_amount);
        $details = json_decode($refreshedTemp->custom_order_details, true);
        $this->assertEquals($order->id, $details['order_id']);
    }

    /** @test */
    public function test_54_regular_order_order_price_is_correct_and_not_blank()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Create category
        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        // Create a test product
        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 147.200;
        $product->product_sale_price = null;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Regular Test Product';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        // Create ShoppingCartDetail
        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 2;
        $cartItem->is_active = 1;
        $cartItem->save();

        session()->forget('customized_order_details');

        $checkoutController = new CheckoutController();
        $orderId = $checkoutController->createOrder('', 7, false, 4);

        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertNotNull($order->order_price);
        $this->assertEquals(294.400, (float)$order->order_price);
    }

    /** @test */
    public function test_55_mixed_order_customized_order_edit_updates_existing_suborder_without_duplication()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Setup existing custom draft & linked suborder
        $tempOrder = new TemporaryOrder();
        $tempOrder->user_id = $user->id;
        $tempOrder->type = 'purchase_order';
        $tempOrder->status = 'pending';
        $tempOrder->subtotal = 100.000;
        $tempOrder->grand_total = 100.000;
        $tempOrder->advance_price = 10.000;
        $tempOrder->pending_amount = 90.000;
        $tempOrder->custom_order_details = json_encode([
            'payment' => [
                'advance' => 10.000,
                'paid' => 10.000,
                'pending' => 90.000
            ]
        ]);
        $tempOrder->save();

        $existingSubOrder = new Order();
        $existingSubOrder->user_id = $user->id;
        $existingSubOrder->order_type = 'custom';
        $existingSubOrder->order_total = 100.000;
        $existingSubOrder->paid_amount = 10.000;
        $existingSubOrder->pending_amount = 90.000;
        $existingSubOrder->order_status = 7;
        $existingSubOrder->custom_order_details = json_encode(['temporary_order_id' => $tempOrder->id]);
        $existingSubOrder->save();

        $tempOrder->custom_order_details = json_encode([
            'order_id' => $existingSubOrder->id,
            'payment' => [
                'advance' => 10.000,
                'paid' => 10.000,
                'pending' => 90.000
            ]
        ]);
        $tempOrder->save();

        // 2. Setup regular cart item
        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 50.000;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Regular Cart Item';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 3. Setup mixed session with updated custom order items (new total = 120.000)
        session(['customized_order_details' => [
            'type' => 'purchase_order',
            'temporary_order_id' => $tempOrder->id,
            'customer' => ['name' => 'Test User', 'phone' => $user->mobile],
            'items' => [
                [
                    'product_title' => 'Custom Item Updated',
                    'price' => 120.000,
                    'quantity' => 1,
                    'total' => 120.000
                ]
            ],
            'payment' => [
                'advance' => 10.000,
                'paid' => 10.000,
                'pending' => 110.000
            ]
        ]]);

        $initialCustomOrdersCount = Order::where('order_type', 'custom')->count();

        $checkoutController = new CheckoutController();
        $parentOrderId = $checkoutController->createOrder('', 7, false, 4);

        $parentOrder = Order::find($parentOrderId);
        $this->assertNotNull($parentOrder);
        $this->assertEquals(50.000, (float)$parentOrder->order_price);

        // Verify suborder count did NOT increase (no duplicate custom order created)
        $newCustomOrdersCount = Order::where('order_type', 'custom')->count();
        $this->assertEquals($initialCustomOrdersCount, $newCustomOrdersCount, 'Custom suborder must be UPDATED, not duplicated!');

        // Verify the existing suborder was updated
        $updatedSubOrder = Order::find($existingSubOrder->id);
        $this->assertEquals($parentOrder->id, $updatedSubOrder->parent_id);
        $this->assertEquals(120.000, (float)$updatedSubOrder->order_total);
        $this->assertEquals(120.000, (float)$updatedSubOrder->order_price);
    }

    /** @test */
    public function test_56_regular_order_never_receives_customized_order_details()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Create a regular order
        $regularOrder = new Order();
        $regularOrder->user_id = $user->id;
        $regularOrder->order_type = 'regular';
        $regularOrder->order_price = 45.000;
        $regularOrder->order_total = 45.000;
        $regularOrder->order_status = 7;
        $regularOrder->save();

        // 2. Add valid regular order detail
        $regularDetail = new OrderDetail();
        $regularDetail->order_id = $regularOrder->id;
        $regularDetail->product_id = 999;
        $regularDetail->product_name = 'Regular Dress';
        $regularDetail->product_price = 45.000;
        $regularDetail->quantity = 1;
        $regularDetail->total_price = 45.000;
        $savedRegular = $regularDetail->save();
        $this->assertTrue($savedRegular);

        // 3. Attempt to add a customized order detail (empty product_id or custom_attributes) to regular order
        $customDetailAttempt = new OrderDetail();
        $customDetailAttempt->order_id = $regularOrder->id;
        $customDetailAttempt->product_id = null;
        $customDetailAttempt->product_name = 'Customized Embroidery';
        $customDetailAttempt->product_price = 10.000;
        $customDetailAttempt->quantity = 1;
        $customDetailAttempt->total_price = 10.000;
        $customDetailAttempt->custom_attributes = json_encode(['color' => 'Red', 'size' => 'XL']);
        $savedCustom = $customDetailAttempt->save();

        // Must be rejected by model integrity guard
        $this->assertFalse($savedCustom, 'Customized OrderDetail must NEVER be saved to a regular order!');

        // 4. Verify in DB that regular order has ONLY regular items
        $details = OrderDetail::where('order_id', $regularOrder->id)->get();
        $this->assertCount(1, $details);
        $this->assertEquals(999, $details->first()->product_id);
        $this->assertNull($details->first()->custom_attributes);

        // 5. Create a custom order and verify custom detail IS allowed on custom order
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_price = 100.000;
        $customOrder->order_total = 100.000;
        $customOrder->order_status = 7;
        $customOrder->save();

        $customDetailValid = new OrderDetail();
        $customDetailValid->order_id = $customOrder->id;
        $customDetailValid->product_id = null;
        $customDetailValid->product_name = 'Customized Abaya';
        $customDetailValid->product_price = 100.000;
        $customDetailValid->quantity = 1;
        $customDetailValid->total_price = 100.000;
        $customDetailValid->custom_attributes = json_encode(['color' => 'Black', 'size' => 'M']);
        $savedValidCustom = $customDetailValid->save();

        $this->assertTrue($savedValidCustom, 'Customized OrderDetail should save successfully on custom order.');
        $this->assertEquals(1, OrderDetail::where('order_id', $customOrder->id)->count());
    }

    /** @test */
    public function test_57_checkout_controller_regular_order_leaves_zero_customized_details_on_parent()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // Simulate shopping cart with 1 regular item
        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 25.000;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Regular Test Item';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // Stale customized order session left over
        session(['customized_order_details' => [
            'type' => 'purchase_order',
            'items' => [
                [
                    'product_title' => 'Stale Custom Item',
                    'price' => 75.000,
                    'quantity' => 1,
                    'total' => 75.000
                ]
            ],
            'payment' => [
                'advance' => 20.000,
                'paid' => 20.000,
                'pending' => 55.000
            ]
        ]]);

        $checkoutController = new CheckoutController();
        $orderId = $checkoutController->createOrder('', 7, false, 4);

        $parentOrder = Order::find($orderId);
        $this->assertNotNull($parentOrder);
        $this->assertEquals(25.000, (float)$parentOrder->order_price);

        // Verify parent order details contain ONLY regular items (product_id != null and no custom_attributes)
        $parentDetails = OrderDetail::where('order_id', $parentOrder->id)->get();
        foreach ($parentDetails as $detail) {
            $this->assertNotNull($detail->product_id, 'Regular parent order must not have null product_id details!');
            $this->assertEmpty($detail->custom_attributes, 'Regular parent order must not have custom_attributes!');
        }

        // Verify suborder (custom) contains the custom details, strictly isolated
        $subOrder = Order::where('parent_id', $parentOrder->id)->where('order_type', 'custom')->first();
        if ($subOrder) {
            $subDetails = OrderDetail::where('order_id', $subOrder->id)->get();
            $this->assertCount(1, $subDetails);
            $this->assertEquals('Stale Custom Item', $subDetails->first()->product_name);
            $this->assertNull($subDetails->first()->product_id);
        }
    }

    /** @test */
    public function test_58_regular_order_created_after_customized_order_exists_never_reuses_or_mixes_details()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Create customized order
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_price = 100.000;
        $customOrder->order_total = 100.000;
        $customOrder->order_status = 7;
        $customOrder->save();

        $cDetail = new OrderDetail();
        $cDetail->order_id = $customOrder->id;
        $cDetail->product_id = null;
        $cDetail->product_name = 'Existing Custom Item';
        $cDetail->product_price = 100.000;
        $cDetail->quantity = 1;
        $cDetail->total_price = 100.000;
        $cDetail->custom_attributes = json_encode(['color' => 'Blue']);
        $cDetail->save();

        // 2. Clear custom session to simulate subsequent regular order checkout
        session()->forget('customized_order_details');

        // Setup regular product & cart
        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 30.000;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Subsequent Regular Product';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->delete();
        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        $checkoutController = new CheckoutController();
        $newRegularOrderId = $checkoutController->createOrder('', 7, false, 4);

        $newRegularOrder = Order::find($newRegularOrderId);
        $this->assertNotEquals($customOrder->id, $newRegularOrder->id);
        $this->assertNotEquals('custom', $newRegularOrder->order_type);

        // Verify regular order has only regular details
        $newOrderDetails = OrderDetail::where('order_id', $newRegularOrder->id)->get();
        $this->assertCount(1, $newOrderDetails);
        $this->assertEquals($product->id, $newOrderDetails->first()->product_id);
        $this->assertEmpty($newOrderDetails->first()->custom_attributes);

        // Verify existing customized order was not altered
        $customOrderDetails = OrderDetail::where('order_id', $customOrder->id)->get();
        $this->assertCount(1, $customOrderDetails);
        $this->assertEquals('Existing Custom Item', $customOrderDetails->first()->product_name);
    }

    /** @test */
    public function test_59_regular_order_with_existing_customized_order_unchecked_checkbox_still_generates_payment_link()
    {
        $user = $this->createTestUser('payment_link_user_');
        $user->user_type = 'physical_store_user'; // tests payment link generation flow (physical store user)
        $user->save();
        Auth::login($user);

        // 1. Create an existing customized order with advance payment already PAID
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_price = 200.000;
        $customOrder->order_total = 200.000;
        $customOrder->paid_amount = 10.000;
        $customOrder->pending_amount = 190.000;
        $customOrder->order_status = 7;
        $customOrder->save();

        $paidLink = new PaymentLink();
        $paidLink->order_id = $customOrder->id;
        $paidLink->payment_request_id = 'pay_custom_adv_' . uniqid();
        $paidLink->amount = 10.000;
        $paidLink->paid_amount = 10.000;
        $paidLink->status = 'paid';
        $paidLink->paid_at = now();
        $paidLink->save();

        // 2. Setup temporary draft for this customized order in EDIT mode with generate_pending_link UNCHECKED
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->custom_order_details = json_encode(['order_id' => $customOrder->id]);
        $temp->subtotal = 200.000;
        $temp->grand_total = 200.000;
        $temp->advance_price = 10.000;
        $temp->pending_amount = 190.000;
        $temp->save();

        // Set session with generate_pending_link = false (UNCHECKED)
        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'temporary_order_id' => $temp->id,
                'order_id' => $customOrder->id,
                'generate_pending_link' => false,
                'payment' => [
                    'advance' => '10.000',
                    'generate_pending_link' => false,
                ],
                'calculation' => [
                    'grand_total' => 200.000,
                ],
                'items' => [
                    [
                        'product_title' => 'Custom Gown',
                        'quantity' => 1,
                        'price' => 200.000,
                    ]
                ]
            ]
        ]);

        // 3. Add User Address & Regular Cart Item (50.000 KWD)
        $address = new \App\UserModel\UserAddress();
        $address->user_id = $user->id;
        $address->country_id = 1;
        $address->first_name = 'Test';
        $address->last_name = 'Customer';
        $address->email = 'test@example.com';
        $address->phone = '96512345678';
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 50.000;
        $product->product_quantity = 10;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Regular Dress';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 4. Execute Checkout Controller initiatePayment
        $controller = new CheckoutController();
        $request = new Request([
            'paymentMethod' => 1, // KNET / online payment link
        ]);

        $response = $controller->initiatePayment($request);

        // Verify Redirect to Order Summary
        $this->assertEquals(302, $response->getStatusCode());

        // Find newly generated regular order
        $regularOrder = Order::where('user_id', $user->id)
            ->where('id', '!=', $customOrder->id)
            ->whereNull('parent_id')
            ->latest('id')
            ->first();

        $this->assertNotNull($regularOrder, 'Regular Order should be created successfully.');
        $this->assertNotEquals('custom', $regularOrder->order_type);

        // Verify Payment Link is generated for the REGULAR ORDER
        $regularPaymentLink = PaymentLink::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($regularPaymentLink, 'Regular Order MUST have a generated payment link even when customized checkbox is unchecked.');
        $this->assertEquals(50.000, (float)$regularPaymentLink->amount, 'Regular order payment link amount must be 50.000 KWD.');

        // Verify NO NEW payment link was created for the existing CUSTOM ORDER
        $customPaymentLinks = PaymentLink::where('order_id', $customOrder->id)->get();
        $this->assertCount(1, $customPaymentLinks, 'Custom order must keep only its existing paid payment link.');
        $this->assertEquals($paidLink->id, $customPaymentLinks->first()->id);

        // Verify custom order integrity preserved
        $customOrder->refresh();
        $this->assertEquals(10.000, (float)$customOrder->paid_amount);
        $this->assertEquals(190.000, (float)$customOrder->pending_amount);
    }

    /** @test */
    public function test_60_regular_order_with_existing_customized_order_checked_checkbox_generates_payment_link_including_pending()
    {
        $user = $this->createTestUser('payment_link_chk_user_');
        $user->user_type = 'physical_store_user';
        $user->save();
        Auth::login($user);

        // 1. Create an existing customized order with advance payment already PAID
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_price = 200.000;
        $customOrder->order_total = 200.000;
        $customOrder->paid_amount = 10.000;
        $customOrder->pending_amount = 190.000;
        $customOrder->order_status = 7;
        $customOrder->save();

        $paidLink = new PaymentLink();
        $paidLink->order_id = $customOrder->id;
        $paidLink->payment_request_id = 'pay_custom_adv_' . uniqid();
        $paidLink->amount = 10.000;
        $paidLink->paid_amount = 10.000;
        $paidLink->status = 'paid';
        $paidLink->paid_at = now();
        $paidLink->save();

        // 2. Setup temporary draft for this customized order in EDIT mode with generate_pending_link CHECKED
        $temp = new TemporaryOrder();
        $temp->user_id = $user->id;
        $temp->type = 'purchase_order';
        $temp->status = 'pending';
        $temp->custom_order_details = json_encode(['order_id' => $customOrder->id]);
        $temp->subtotal = 200.000;
        $temp->grand_total = 200.000;
        $temp->advance_price = 10.000;
        $temp->pending_amount = 190.000;
        $temp->save();

        // Set session with generate_pending_link = true (CHECKED)
        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'temporary_order_id' => $temp->id,
                'order_id' => $customOrder->id,
                'generate_pending_link' => true,
                'payment' => [
                    'advance' => '10.000',
                    'generate_pending_link' => true,
                ],
                'calculation' => [
                    'grand_total' => 200.000,
                ],
                'items' => [
                    [
                        'product_title' => 'Custom Gown',
                        'quantity' => 1,
                        'price' => 200.000,
                        'total' => 200.000,
                    ]
                ]
            ]
        ]);

        // 3. Add User Address & Regular Cart Item (50.000 KWD)
        $address = new \App\UserModel\UserAddress();
        $address->user_id = $user->id;
        $address->country_id = 1;
        $address->first_name = 'Test';
        $address->last_name = 'Customer';
        $address->email = 'testchk@example.com';
        $address->phone = '96512345679';
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 50.000;
        $product->product_quantity = 10;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Regular Dress';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 4. Execute Checkout Controller initiatePayment
        $controller = new CheckoutController();
        $request = new Request([
            'paymentMethod' => 1, // KNET / online payment link
        ]);

        $response = $controller->initiatePayment($request);

        // Verify Redirect to Order Summary
        $this->assertEquals(302, $response->getStatusCode());

        // Find newly generated regular order
        $regularOrder = Order::where('user_id', $user->id)
            ->where('id', '!=', $customOrder->id)
            ->whereNull('parent_id')
            ->latest('id')
            ->first();

        $this->assertNotNull($regularOrder, 'Regular Order should be created successfully.');

        // Payment link for the combined payable total (50 regular + 190 pending customized = 240 KWD)
        $regularPaymentLink = PaymentLink::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($regularPaymentLink);
        $this->assertEquals(240.000, (float)$regularPaymentLink->amount);
    }

    /** @test */
    public function test_61_mixed_cart_with_customized_order_edit_checkbox_unchecked_payable_regular_only()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Existing Customized Order: Total = 100, Advance = 0, Edit mode, Checkbox = UNCHECKED
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_total = 100.000;
        $customOrder->order_price = 100.000;
        $customOrder->delivery_price = 0.000;
        $customOrder->paid_amount = 0.000;
        $customOrder->pending_amount = 100.000;
        $customOrder->order_status = 7;
        $customOrder->save();

        $customDetail = new OrderDetail();
        $customDetail->order_id = $customOrder->id;
        $customDetail->product_name = 'Customized Robe';
        $customDetail->product_price = 100.000;
        $customDetail->quantity = 1;
        $customDetail->total_price = 100.000;
        $customDetail->save();

        $tempOrder = new \App\UserModel\TemporaryOrder();
        $tempOrder->user_id = $user->id;
        $tempOrder->type = 'purchase_order';
        $tempOrder->subtotal = 100.000;
        $tempOrder->grand_total = 100.000;
        $tempOrder->advance_price = 0.000;
        $tempOrder->pending_amount = 100.000;
        $tempOrder->status = 'pending';
        $tempOrder->custom_order_details = json_encode([
            'type' => 'purchase_order',
            'order_id' => $customOrder->id,
            'temporary_order_id' => null,
            'generate_pending_link' => false,
            'payment' => [
                'advance' => '0.000',
                'generate_pending_link' => false,
            ],
            'calculation' => [
                'grand_total' => 100.000,
            ],
            'items' => [
                [
                    'product_title' => 'Customized Robe',
                    'quantity' => 1,
                    'price' => 100.000,
                    'total' => 100.000,
                ]
            ]
        ]);
        $tempOrder->save();

        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'order_id' => $customOrder->id,
                'temporary_order_id' => $tempOrder->id,
                'generate_pending_link' => false,
                'payment' => [
                    'advance' => '0.000',
                    'generate_pending_link' => false,
                ],
                'calculation' => [
                    'grand_total' => 100.000,
                ],
                'items' => [
                    [
                        'product_title' => 'Customized Robe',
                        'quantity' => 1,
                        'price' => 100.000,
                        'total' => 100.000,
                    ]
                ]
            ]
        ]);

        // 2. Regular Item = 20 KWD
        $address = new \App\UserModel\UserAddress();
        $address->user_id = $user->id;
        $address->country_id = 1;
        $address->first_name = 'Regular';
        $address->last_name = 'User';
        $address->email = 'reguser@example.com';
        $address->phone = '96512345678';
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 20.000;
        $product->product_quantity = 10;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Standard Item';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 3. Initiate Payment
        $controller = new CheckoutController();
        $request = new Request([
            'paymentMethod' => 1,
        ]);

        $response = $controller->initiatePayment($request);
        $this->assertEquals(302, $response->getStatusCode());

        // Regular Order created
        $regularOrder = Order::where('user_id', $user->id)
            ->where('id', '!=', $customOrder->id)
            ->whereNull('parent_id')
            ->latest('id')
            ->first();

        $this->assertNotNull($regularOrder);

        // Payment link MUST be exactly 20.000 KWD (excluding the 100 KWD non-payable customized order)
        $paymentLink = PaymentLink::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($paymentLink);
        $this->assertEquals(20.000, (float)$paymentLink->amount);

        // SubOrder created/linked to parent order
        $subOrder = Order::where('parent_id', $regularOrder->id)->where('order_type', 'custom')->first();
        $this->assertNotNull($subOrder);
        $this->assertEquals(100.000, (float)$subOrder->order_total);
    }

    /** @test */
    public function test_62_mixed_cart_create_mode_advance_and_regular_split_payment_callback_success()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Customized order in Create Mode with Advance = 30 KWD, Total = 100 KWD
        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'temporary_order_id' => null,
                'generate_pending_link' => false,
                'payment' => [
                    'advance' => '30.000',
                    'generate_pending_link' => false,
                ],
                'calculation' => [
                    'grand_total' => 100.000,
                ],
                'items' => [
                    [
                        'product_title' => 'Custom Blazer',
                        'quantity' => 1,
                        'price' => 100.000,
                        'total' => 100.000,
                    ]
                ]
            ]
        ]);

        // 2. Regular Item = 20 KWD
        $address = new \App\UserModel\UserAddress();
        $address->user_id = $user->id;
        $address->country_id = 1;
        $address->first_name = 'Split';
        $address->last_name = 'Tester';
        $address->email = 'splittester@example.com';
        $address->phone = '96512345677';
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 20.000;
        $product->product_quantity = 10;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Split Product';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 3. Initiate Payment
        $controller = new CheckoutController();
        $request = new Request([
            'paymentMethod' => 1,
        ]);

        $response = $controller->initiatePayment($request);
        $this->assertEquals(302, $response->getStatusCode());

        // Regular parent order created
        $regularOrder = Order::where('user_id', $user->id)->whereNull('parent_id')->latest('id')->first();
        $this->assertNotNull($regularOrder);

        // Combined PaymentLink should be 20 (regular) + 30 (custom advance) = 50 KWD
        $parentPaymentLink = PaymentLink::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($parentPaymentLink);
        $this->assertEquals(50.000, (float)$parentPaymentLink->amount);

        // SubOrder created for custom order
        $subOrder = Order::where('parent_id', $regularOrder->id)->where('order_type', 'custom')->first();
        $this->assertNotNull($subOrder);
        $this->assertEquals(100.000, (float)$subOrder->order_total);

        // 4. Simulate Successful Payment Callback for the combined PaymentLink
        $callbackRequest = new Request([
            'requested_order_id' => $regularOrder->id,
            'payment_request_id' => $parentPaymentLink->payment_request_id,
            'PaymentID' => 'PAY_TXN_SPLIT_123',
            'Result' => 'CAPTURED',
            'status' => 'success',
        ]);

        $callbackResp = $controller->paymentSuccess($callbackRequest);
        $this->assertEquals(302, $callbackResp->getStatusCode());

        // Verify parent regular order is paid
        $regularOrder->refresh();
        $this->assertEquals(20.000, (float)$regularOrder->paid_amount);

        // Verify child customized order received 30.000 KWD advance payment
        $subOrder->refresh();
        $this->assertEquals(30.000, (float)$subOrder->paid_amount);
        $this->assertEquals(70.000, (float)$subOrder->pending_amount);
        $this->assertEquals(1, $subOrder->is_advance_payment_done);
    }

    /** @test */
    public function test_63_mixed_cart_edit_mode_generate_pending_link_checked_split_callback()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        // 1. Existing Custom Order: Total = 100, Paid = 30 (Advance already paid), Pending = 70.
        // In Edit Mode, user checks "Generate Pending Amount Payment Link" -> 70 KWD pending is payable.
        $customOrder = new Order();
        $customOrder->user_id = $user->id;
        $customOrder->order_type = 'custom';
        $customOrder->custom_order_type = 'purchase_order';
        $customOrder->order_total = 100.000;
        $customOrder->order_price = 100.000;
        $customOrder->delivery_price = 0.000;
        $customOrder->paid_amount = 30.000;
        $customOrder->pending_amount = 70.000;
        $customOrder->is_advance_payment_done = 1;
        $customOrder->order_status = 7;
        $customOrder->save();

        $customDetail = new OrderDetail();
        $customDetail->order_id = $customOrder->id;
        $customDetail->product_name = 'Custom Evening Gown';
        $customDetail->product_price = 100.000;
        $customDetail->quantity = 1;
        $customDetail->total_price = 100.000;
        $customDetail->save();

        $advancePaymentLink = PaymentLink::create([
            'order_id' => $customOrder->id,
            'payment_request_id' => 'pay_adv_' . $customOrder->id,
            'amount' => 30.000,
            'paid_amount' => 30.000,
            'status' => 'paid',
            'paid_at' => now()->subDay(),
            'is_advance_payment' => 1,
        ]);

        $tempOrder = new \App\UserModel\TemporaryOrder();
        $tempOrder->user_id = $user->id;
        $tempOrder->type = 'purchase_order';
        $tempOrder->subtotal = 100.000;
        $tempOrder->grand_total = 100.000;
        $tempOrder->advance_price = 30.000;
        $tempOrder->pending_amount = 70.000;
        $tempOrder->status = 'pending';
        $tempOrder->custom_order_details = json_encode([
            'type' => 'purchase_order',
            'order_id' => $customOrder->id,
            'temporary_order_id' => null,
            'generate_pending_link' => true,
            'is_advance_payment_done' => 1,
            'payment' => [
                'advance' => '30.000',
                'paid' => 30.000,
                'pending' => 70.000,
                'generate_pending_link' => true,
            ],
            'calculation' => [
                'grand_total' => 100.000,
                'advance' => 30.000,
                'paid' => 30.000,
                'pending' => 70.000,
                'generate_pending_link' => true,
            ],
            'items' => [
                [
                    'product_title' => 'Custom Evening Gown',
                    'quantity' => 1,
                    'price' => 100.000,
                    'total' => 100.000,
                ]
            ]
        ]);
        $tempOrder->save();

        session([
            'customized_order_details' => [
                'type' => 'purchase_order',
                'order_id' => $customOrder->id,
                'temporary_order_id' => $tempOrder->id,
                'generate_pending_link' => true,
                'is_advance_payment_done' => 1,
                'payment' => [
                    'advance' => '30.000',
                    'paid' => 30.000,
                    'pending' => 70.000,
                    'generate_pending_link' => true,
                ],
                'calculation' => [
                    'grand_total' => 100.000,
                    'advance' => 30.000,
                    'paid' => 30.000,
                    'pending' => 70.000,
                    'generate_pending_link' => true,
                ],
                'items' => [
                    [
                        'product_title' => 'Custom Evening Gown',
                        'quantity' => 1,
                        'price' => 100.000,
                        'total' => 100.000,
                    ]
                ]
            ]
        ]);

        // 2. Regular Item = 20 KWD
        $address = new \App\UserModel\UserAddress();
        $address->user_id = $user->id;
        $address->country_id = 1;
        $address->first_name = 'Pending';
        $address->last_name = 'Tester';
        $address->email = 'pendingtester@example.com';
        $address->phone = '96512345676';
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();

        $cat = new \App\AdminModel\Product\AdminCategory();
        $cat->parent_id = 1;
        $cat->is_active = 1;
        $cat->save();

        $product = new \App\AdminModel\Product\AdminProduct();
        $product->category_id = $cat->id;
        $product->product_regular_price = 20.000;
        $product->product_quantity = 10;
        $product->is_product_sale_price_null = 1;
        $product->product_weight = 1.0;
        $product->is_active = 1;
        $product->is_active_category = 1;
        $product->save();

        $prodDesc = new \App\AdminModel\Product\AdminProductDescription();
        $prodDesc->products_id = $product->id;
        $prodDesc->language_id = 1;
        $prodDesc->products_name = 'Evening Accessory';
        $prodDesc->is_active = 1;
        $prodDesc->save();

        $prodCat = new \App\AdminModel\Product\AdminProductCategory();
        $prodCat->products_id = $product->id;
        $prodCat->categories_id = $cat->id;
        $prodCat->is_active = 1;
        $prodCat->save();

        $cartItem = new \App\UserModel\Cart\ShoppingCartDetail();
        $cartItem->user_id = $user->id;
        $cartItem->product_id = $product->id;
        $cartItem->quantity = 1;
        $cartItem->is_active = 1;
        $cartItem->save();

        // 3. Initiate Payment
        $controller = new CheckoutController();
        $request = new Request([
            'paymentMethod' => 1,
        ]);

        $response = $controller->initiatePayment($request);
        $this->assertEquals(302, $response->getStatusCode());

        // Regular parent order created
        $regularOrder = Order::where('user_id', $user->id)->whereNull('parent_id')->latest('id')->first();
        $this->assertNotNull($regularOrder);

        // Combined PaymentLink should be 20 (regular) + 70 (custom pending) = 90 KWD
        $parentPaymentLink = PaymentLink::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($parentPaymentLink);
        $this->assertEquals(90.000, (float)$parentPaymentLink->amount);

        // SubOrder linked
        $subOrder = Order::where('parent_id', $regularOrder->id)->where('order_type', 'custom')->first();
        $this->assertNotNull($subOrder);
        $this->assertEquals($customOrder->id, $subOrder->id);

        // 4. Simulate Payment Success Callback
        $callbackRequest = new Request([
            'requested_order_id' => $regularOrder->id,
            'payment_request_id' => $parentPaymentLink->payment_request_id,
            'PaymentID' => 'PAY_TXN_PENDING_SPLIT_456',
            'Result' => 'CAPTURED',
            'status' => 'success',
        ]);

        $callbackResp = $controller->paymentSuccess($callbackRequest);
        $this->assertEquals(302, $callbackResp->getStatusCode());

        // Verify parent regular order is paid
        $regularOrder->refresh();
        $this->assertEquals(20.000, (float)$regularOrder->paid_amount);

        // Verify child customized order is now fully paid: 30 advance + 70 pending = 100 total
        $subOrder->refresh();
        $this->assertEquals(100.000, (float)$subOrder->paid_amount);
        $this->assertEquals(0.000, (float)$subOrder->pending_amount);
    }

    /** @test */
    public function test_64_mixed_cart_multiple_edit_save_cycles_preserves_single_draft_and_order()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $cartController = new \App\Http\Controllers\Web\Cart\CartController();

        // Cycle 1: Create
        $createReq = new Request([
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Cycle Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'cycle@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 5.000,
            'advance_payment' => 20.000,
            'custom_product_title' => ['Cycle Item 1'],
            'custom_desc' => ['Description 1'],
            'custom_price' => [50.000],
            'custom_qty' => [1],
        ]);
        $resp1 = $cartController->submitCustomizedOrder($createReq);
        $data1 = $resp1->getData(true);
        $tempId = $data1['data']['temporary_order_id'];

        // Cycle 2: Edit 1
        $editReq1 = new Request([
            'temporary_order_id' => $tempId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Cycle Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'cycle@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 5.000,
            'advance_payment' => 25.000,
            'custom_product_title' => ['Cycle Item 1 Modified'],
            'custom_desc' => ['Description 1 Modified'],
            'custom_price' => [60.000],
            'custom_qty' => [1],
        ]);
        $resp2 = $cartController->submitCustomizedOrder($editReq1);
        $this->assertEquals(200, $resp2->getStatusCode());

        // Cycle 3: Edit 2
        $editReq2 = new Request([
            'temporary_order_id' => $tempId,
            'custom_req_type' => 'purchase_order',
            'cust_name' => 'Cycle Customer',
            'cust_phone' => '96511223344',
            'cust_email' => 'cycle@example.com',
            'cust_city' => 'Kuwait City',
            'cust_block' => '1',
            'cust_street' => 'Street 1',
            'cust_house_number' => '10',
            'custom_shipping' => 5.000,
            'advance_payment' => 30.000,
            'custom_product_title' => ['Cycle Item 1 Final'],
            'custom_desc' => ['Description 1 Final'],
            'custom_price' => [70.000],
            'custom_qty' => [1],
        ]);
        $resp3 = $cartController->submitCustomizedOrder($editReq2);
        $this->assertEquals(200, $resp3->getStatusCode());

        // Assert exactly 1 TemporaryOrder exists for user
        $tempCount = \App\UserModel\TemporaryOrder::where('user_id', $user->id)->count();
        $this->assertEquals(1, $tempCount);

        $savedTemp = \App\UserModel\TemporaryOrder::find($tempId);
        $this->assertEquals(75.000, (float)$savedTemp->grand_total);
        $this->assertEquals(30.000, (float)$savedTemp->advance_price);
        $this->assertEquals(45.000, (float)$savedTemp->pending_amount);
    }

    /** @test */
    public function test_65_pending_amount_matrix_advance_zero_deduction()
    {
        // Custom total 12, Advance 0 -> Pending 12
        $session = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 0.000],
        ];
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(12.000, $summary['grand_total']);
        $this->assertEquals(0.000, $summary['advance_price']);
        $this->assertEquals(12.000, $summary['pending_amount']);
    }

    /** @test */
    public function test_66_pending_amount_matrix_advance_five_deduction()
    {
        // Custom total 12, Advance 5 -> Pending 7
        $session = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 5.000],
        ];
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(12.000, $summary['grand_total']);
        $this->assertEquals(5.000, $summary['advance_price']);
        $this->assertEquals(7.000, $summary['pending_amount']);
    }

    /** @test */
    public function test_67_pending_amount_matrix_advance_equals_total()
    {
        // Custom total 12, Advance 12 -> Pending 0
        $session = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 12.000],
        ];
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(12.000, $summary['grand_total']);
        $this->assertEquals(12.000, $summary['advance_price']);
        $this->assertEquals(0.000, $summary['pending_amount']);
    }

    /** @test */
    public function test_68_pending_amount_matrix_regular_amount_does_not_affect_custom_pending()
    {
        // Regular 50, Custom 12, Advance 5 -> Custom Pending = 7
        $session1 = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 5.000],
        ];
        $summary1 = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session1);
        $this->assertEquals(7.000, $summary1['pending_amount']);

        // Regular 2, Custom 12, Advance 5 -> Custom Pending = 7
        $session2 = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 5.000],
        ];
        $summary2 = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session2);
        $this->assertEquals(7.000, $summary2['pending_amount']);
    }

    /** @test */
    public function test_69_pending_amount_matrix_subtotal_shipping_discount_advance()
    {
        // Subtotal = 100, Shipping = 10, Discount = 20, Advance = 30 -> Total = 90, Pending = 60
        $session = [
            'type' => 'purchase_order',
            'items' => [['total' => 100.000]],
            'shipping' => 10.000,
            'discount_type' => 'flat',
            'discount_val' => 20.000,
            'payment' => ['advance' => 30.000],
        ];
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(90.000, $summary['grand_total']);
        $this->assertEquals(30.000, $summary['advance_price']);
        $this->assertEquals(60.000, $summary['pending_amount']);
    }

    /** @test */
    public function test_70_pending_amount_matrix_edit_advance_transitions()
    {
        $session = [
            'type' => 'purchase_order',
            'items' => [['total' => 12.000]],
            'shipping' => 0.000,
            'payment' => ['advance' => 5.000],
        ];
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(7.000, $summary['pending_amount']);

        // Edit Advance 5 -> 3
        $session['payment']['advance'] = 3.000;
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(9.000, $summary['pending_amount']);

        // Edit Advance 3 -> 8
        $session['payment']['advance'] = 8.000;
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(4.000, $summary['pending_amount']);

        // Edit Advance 8 -> 12
        $session['payment']['advance'] = 12.000;
        $summary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($session);
        $this->assertEquals(0.000, $summary['pending_amount']);
    }

    /** @test */
    public function test_71_mixed_regular_create_and_custom_create_pending_amount_and_payment()
    {
        $user = $this->createTestUser();
        Auth::login($user);

        $customSession = [
            'type' => 'purchase_order',
            'customer' => [
                'name' => 'Custom User',
                'phone' => '96512345678',
                'email' => 'custom@test.com',
                'city' => 'Kuwait City',
            ],
            'items' => [
                ['item_code' => 'CUST-01', 'product_title' => 'Custom Item', 'total' => 12.000, 'price' => 12.000, 'quantity' => 1]
            ],
            'shipping' => 0.000,
            'payment' => ['advance' => 5.000],
        ];
        session(['customized_order_details' => $customSession]);

        $financials = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($customSession);
        $this->assertEquals(12.000, $financials['grand_total']);
        $this->assertEquals(5.000, $financials['advance_price']);
        $this->assertEquals(7.000, $financials['pending_amount']);
        $this->assertEquals(5.000, $financials['payable_amount']);
    }
}





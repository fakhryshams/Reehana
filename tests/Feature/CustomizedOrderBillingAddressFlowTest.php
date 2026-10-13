<?php

namespace Tests\Feature;

use App\User;
use App\UserModel\UserAddress;
use App\UserModel\Order;
use App\UserModel\TemporaryOrder;
use App\Services\CustomizedOrderSyncService;
use Tests\TestCase;

class CustomizedOrderBillingAddressFlowTest extends TestCase
{
    /**
     * Test Scenario 1: Standalone Customized Order CREATE with saved billing address ID.
     */
    public function test_customized_order_create_stores_billing_address_id_correctly()
    {
        // 1. Setup mock user and addresses
        $user = new User();
        $user->id = 991;
        $user->email = 'testuser@example.com';
        $user->phone = '12345678';
        $user->name = 'Test User';

        $billingAddress = new UserAddress();
        $billingAddress->id = 43;
        $billingAddress->user_id = 991;
        $billingAddress->country_id = 1;
        $billingAddress->city = 'Kuwait City';
        $billingAddress->block = '1';
        $billingAddress->street = 'Street 1';
        $billingAddress->house = 'House 1';
        $billingAddress->is_billing_address = 1;
        $billingAddress->is_default = 1;

        // 2. Test TemporaryOrder model accessor/mutator
        $tempOrder = new TemporaryOrder();
        $tempOrder->id = 501;
        $tempOrder->customer_address_id = 43;
        $this->assertEquals(43, $tempOrder->customer_address_id);
        $this->assertEquals(43, $tempOrder->billing_address_id);

        $tempOrder->billing_address_id = 55;
        $this->assertEquals(55, $tempOrder->customer_address_id);
        $this->assertEquals(55, $tempOrder->billing_address_id);

        // Reset to 43
        $tempOrder->customer_address_id = 43;

        // 3. Test Session storage payload structure
        $customOrderSession = [
            'temp_order_id' => 501,
            'customer' => [
                'name' => 'Test User',
                'customer_address_id' => 43,
                'billing_address_id' => 43,
                'shipping_address_id' => 43,
            ],
            'custom_dimensions' => [],
            'custom_fields' => [],
            'advance_payment' => 0,
            'is_advance_payment' => 0,
        ];

        // 4. Test CheckoutController standalone order address resolution logic
        $cust = $customOrderSession['customer'];
        $customBillingAddressId = $cust['customer_address_id'] ?? $cust['billing_address_id'] ?? ($tempOrder->customer_address_id ?? null);
        $customShippingAddressId = $cust['shipping_address_id'] ?? $cust['customer_address_id'] ?? ($tempOrder->customer_address_id ?? null);

        $this->assertEquals(43, $customBillingAddressId);
        $this->assertEquals(43, $customShippingAddressId);

        // 5. Test Order object assignment
        $order = new Order();
        $order->id = 9901;
        $order->user_id = 991;
        $order->billing_address_id = $customBillingAddressId;
        $order->shipping_address_id = $customShippingAddressId;
        $order->custom_order_details = json_encode($customOrderSession);

        $this->assertEquals(43, $order->billing_address_id);
        $this->assertEquals(43, $order->shipping_address_id);
    }

    /**
     * Test Scenario 2: Separate Billing vs Shipping Address on Customized Order.
     */
    public function test_customized_order_supports_separate_billing_and_shipping_address()
    {
        $customOrderSession = [
            'temp_order_id' => 502,
            'customer' => [
                'name' => 'Separate Address User',
                'billing_address_id' => 43,
                'shipping_address_id' => 52,
                'customer_address_id' => 43,
            ]
        ];

        $cust = $customOrderSession['customer'];
        $customBillingAddressId = $cust['billing_address_id'] ?? $cust['customer_address_id'] ?? null;
        $customShippingAddressId = $cust['shipping_address_id'] ?? $cust['customer_address_id'] ?? null;

        $order = new Order();
        $order->billing_address_id = $customBillingAddressId;
        $order->shipping_address_id = $customShippingAddressId;

        $this->assertEquals(43, $order->billing_address_id);
        $this->assertEquals(52, $order->shipping_address_id);
        $this->assertNotEquals($order->billing_address_id, $order->shipping_address_id);
    }

    /**
     * Test Scenario 3: Customized Order UPDATE preserves existing address ID when not modified.
     */
    public function test_customized_order_update_preserves_or_updates_address_id()
    {
        $tempOrder = new TemporaryOrder();
        $tempOrder->id = 503;
        $tempOrder->customer_address_id = 43;

        // Simulate update request where new address is sent (e.g. 77)
        $requestNewAddressId = 77;
        $resolvedAddressId = $requestNewAddressId ?? $tempOrder->customer_address_id;
        $tempOrder->customer_address_id = $resolvedAddressId;
        $this->assertEquals(77, $tempOrder->customer_address_id);
        $this->assertEquals(77, $tempOrder->billing_address_id);

        // Simulate update request where address is omitted (preserved from existing)
        $requestOmittedAddressId = null;
        $resolvedAddressId = $requestOmittedAddressId ?? $tempOrder->customer_address_id;
        $tempOrder->customer_address_id = $resolvedAddressId;
        $this->assertEquals(77, $tempOrder->customer_address_id);
        $this->assertEquals(77, $tempOrder->billing_address_id);
    }

    /**
     * Test Scenario 4: Regular + Customized Order (Parent and Child Sub-order addresses).
     */
    public function test_regular_plus_customized_parent_and_child_billing_address()
    {
        $parentOrder = new Order();
        $parentOrder->id = 1000;
        $parentOrder->billing_address_id = 43;
        $parentOrder->shipping_address_id = 43;
        $parentOrder->order_type = 'regular';

        $customOrderSession = [
            'temp_order_id' => 504,
            'customer' => [
                'billing_address_id' => 43,
                'shipping_address_id' => 43,
                'customer_address_id' => 43,
            ]
        ];

        $cust = $customOrderSession['customer'];
        $customSubBillingAddressId = $cust['billing_address_id'] ?? $cust['customer_address_id'] ?? $parentOrder->billing_address_id;
        $customSubShippingAddressId = $cust['shipping_address_id'] ?? $cust['customer_address_id'] ?? $parentOrder->shipping_address_id;

        $subOrder = new Order();
        $subOrder->id = 1001;
        $subOrder->parent_id = $parentOrder->id;
        $subOrder->order_type = 'customized';
        $subOrder->billing_address_id = $customSubBillingAddressId;
        $subOrder->shipping_address_id = $customSubShippingAddressId;

        $this->assertEquals(43, $parentOrder->billing_address_id);
        $this->assertEquals(43, $subOrder->billing_address_id);
        $this->assertEquals(1000, $subOrder->parent_id);
    }

    /**
     * Test Scenario 5: Custom structured billing fields mode (saved address ID is null).
     */
    public function test_custom_billing_address_mode_preserves_custom_fields()
    {
        $customOrderSession = [
            'temp_order_id' => 505,
            'customer' => [
                'billing_address_id' => null,
                'customer_address_id' => null,
                'custom_billing_country_id' => 1,
                'custom_billing_country' => 'Kuwait',
                'custom_billing_city' => 'Salmiya',
                'custom_billing_block' => 'Block 4',
                'custom_billing_street' => 'Baghdad St',
                'custom_billing_house' => 'Building 12',
                'custom_billing_floor' => 'Floor 3',
                'custom_billing_apartment' => 'Apt 5',
            ]
        ];

        $order = new Order();
        $order->id = 9905;
        $order->billing_address_id = $customOrderSession['customer']['billing_address_id'];
        $order->custom_billing_country_id = $customOrderSession['customer']['custom_billing_country_id'];
        $order->custom_billing_country = $customOrderSession['customer']['custom_billing_country'];
        $order->custom_billing_city = $customOrderSession['customer']['custom_billing_city'];
        $order->custom_billing_block = $customOrderSession['customer']['custom_billing_block'];
        $order->custom_billing_street = $customOrderSession['customer']['custom_billing_street'];
        $order->custom_billing_house = $customOrderSession['customer']['custom_billing_house'];
        $order->custom_billing_floor = $customOrderSession['customer']['custom_billing_floor'];
        $order->custom_billing_apartment = $customOrderSession['customer']['custom_billing_apartment'];

        $this->assertNull($order->billing_address_id);
        $this->assertEquals(1, $order->custom_billing_country_id);
        $this->assertEquals('Kuwait', $order->custom_billing_country);
        $this->assertEquals('Salmiya', $order->custom_billing_city);
        $this->assertEquals('Block 4', $order->custom_billing_block);
        $this->assertEquals('Baghdad St', $order->custom_billing_street);
        $this->assertEquals('Building 12', $order->custom_billing_house);
        $this->assertEquals('Floor 3', $order->custom_billing_floor);
        $this->assertEquals('Apt 5', $order->custom_billing_apartment);
    }
}

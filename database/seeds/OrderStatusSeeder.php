<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * NOTE :: If you change or add new record then also chnage in -- config/order.php -- 
         * KEEP This seed and order.php (Inside config/order.php) SYNCED
         * 
         * 
         */
        DB::table('admin_orders_status')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_orders_status AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'sorting'               => '1',
                'order_status'          => 'Process',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 0,
                'is_default'            => 1,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '2',
                'order_status'          => 'Ready to Ship',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 0,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '3',
                'order_status'          => 'Shipped',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 0,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '4',
                'order_status'          => 'Delivered',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 0,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '5',
                'order_status'          => 'Cancel',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 1,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '6',
                'order_status'          => 'Return',
                'is_active'             => 1,
                'show_in_list'          => 1,
                'show_on_cancel_return' => 1,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'               => '7',
                'order_status'          => 'Pending',
                'is_active'             => 1,
                'show_in_list'          => 0,
                'show_on_cancel_return' => 0,
                'is_default'            => 0,
                'created_at'            => date("Y-m-d H:i:s"),
                'updated_at'            => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_orders_status')->insert($data);
    }

}

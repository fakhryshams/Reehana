<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ShippingMethodSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_shipping_methods')->delete();
        DB::table('admin_shipping_methods_description')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_shipping_methods AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE admin_shipping_methods_description AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'id'         => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'id'         => '2',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );

        DB::table('admin_shipping_methods')->insert($data);

        /*
         * Add Description
         */
        $data = array(
            array(
                'shipping_methods_id' => '1',
                'language_id'         => '1',
                'shipping_name'       => 'Flat Rate',
                'created_at'          => date("Y-m-d H:i:s"),
                'updated_at'          => date("Y-m-d H:i:s")
            ),
            array(
                'shipping_methods_id' => '2',
                'language_id'         => '1',
                'shipping_name'       => 'Free Shipping',
                'created_at'          => date("Y-m-d H:i:s"),
                'updated_at'          => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_shipping_methods_description')->insert($data);
    }

}

<?php

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_method')->delete();
        DB::table('payment_method_description')->delete();
        DB::statement(DB::raw('ALTER TABLE payment_method AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE payment_method_description AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'id'         => '1',
                'sorting'    => '1',
                'is_default' => '1',
                'image1'     => 'public/payment/image/cc-knet.png',
                'image2'     => null,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'id'         => '2',
                'sorting'    => '2',
                'is_default' => '0',
                'image1'     => 'public/payment/image/cc-visa.png',
                'image2'     => 'public/payment/image/cc-master-card.png',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'id'         => '3',
                'sorting'    => '3',
                'is_default' => '0',
                'image1'     => null,
                'image2'     => null,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );

        DB::table('payment_method')->insert($data);

        $data = array(
            array(
                'id'                  => '1',
                'payment_method_id'   => '1',
                'language_id'         => '1',
                'payment_method_name' => 'Pay with Knet',
            ),
            array(
                'id'                  => '2',
                'payment_method_id'   => '2',
                'language_id'         => '1',
                'payment_method_name' => 'Pay with VISA / Master',
            ),
            array(
                'id'                  => '3',
                'payment_method_id'   => '3',
                'language_id'         => '1',
                'payment_method_name' => 'Cash On Delivery',
            ),
        );

        DB::table('payment_method_description')->insert($data);
    }

}

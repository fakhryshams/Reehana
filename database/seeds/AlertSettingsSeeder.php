<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertSettingsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_alert_settings')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_alert_settings AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'setting_key' => 'Order Email',
                'setting_val' => '1',
                'description' => 'Sent an email to Customer on New Order',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'setting_key' => 'Order Status Email',
                'setting_val' => '1',
                'description' => 'Send an email to customer on change of Order Status',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'setting_key' => 'Forgot Password (Email)',
                'setting_val' => '1',
                'description' => 'Send an email to customer to recover Forgotton Password',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'setting_key' => 'Contact Us (Email)',
                'setting_val' => '1',
                'description' => 'Send an email to customer for Contact Us',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'setting_key' => 'Wishlist (Email)',
                'setting_val' => '1',
                'description' => 'Send an email to customer for remind about wishlist',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'setting_key' => 'Custom Form (Email)',
                'setting_val' => '1',
                'description' => 'Customized Form Email',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_alert_settings')->insert($data);
    }

}

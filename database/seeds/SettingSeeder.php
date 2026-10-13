<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_settings')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_settings AUTO_INCREMENT = 1'));
//
//        DB::table('admin_settings_description')->delete();
//        DB::statement(DB::raw('ALTER TABLE admin_settings_description AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'sorting'       => 1,
                'setting_key'   => 'Website Name',
                'setting_val'   => 'Reehana',
                'description'   => 'Website name',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 2,
                'setting_key'   => 'Website Logo',
                'setting_val'   => 'logo/logo.png',
                'description'   => 'Website Logo (Size ' . config('custom.app_logo_width') . 'x' . config('custom.app_logo_height') . ' pixels)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'file',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 3,
                'setting_key'   => 'FavIcon',
                'setting_val'   => 'favicon/logo.ico',
                'description'   => 'Website FavIcon (Size ' . config('custom.favicon_width') . 'x' . config('custom.favicon_height') . ' pixels)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'file',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 4,
                'setting_key'   => 'Facebook URL',
                'setting_val'   => '',
                'description'   => 'Website Facebook URL',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 5,
                'setting_key'   => 'Outlook URL',
                'setting_val'   => '',
                'description'   => 'Website Outlook URL',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 6,
                'setting_key'   => 'Instagram URL',
                'setting_val'   => '',
                'description'   => 'Website Instagram URL',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 7,
                'setting_key'   => 'Whatsapp URL',
                'setting_val'   => '',
                'description'   => 'Website Whatsapp URL',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 9,
                'setting_key'   => 'Top Name',
                'setting_val'   => 'Stay Home and Be Safe! We Ship Worldwide! Shop now!',
                'description'   => 'Text which will display at top of site',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 10,
                'setting_key'   => 'Tax(%)',
                'setting_val'   => '0',
                'description'   => 'Tax in percentage(%)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 20,
                'setting_key'   => 'Footer Text',
                'setting_val'   => '&copy; Reehana / <span>Website by <a href="http://www.uno-digital.com/" target="_blank">UNO Digital</a></span>',
                'description'   => 'Footer Text (It will display on Footer of your Site, Example  : &copy; 2020 Your Site Name)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 19,
                'setting_key'   => 'Google Analytics',
                'setting_val'   => '',
                'description'   => "Google Analytics Script <a target='_blank' href='https://developers.google.com/analytics/devguides/collection/analyticsjs'>Click here for Refence</a></script>
 )",
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 11,
                'setting_key'   => 'Contact Us : Phone 1',
                'setting_val'   => '+965 0007-0007',
                'description'   => 'Contact Us : Phone 1, Ex : +965 0007-0007',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 12,
                'setting_key'   => 'Contact Us : Phone 2',
                'setting_val'   => '+965 0008-0008',
                'description'   => 'Contact Us : Phone 2, Ex : +965 0008-0008',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 13,
                'setting_key'   => 'Contact Us : Email',
                'setting_val'   => 'info@reehana.com',
                'description'   => 'Contact Us : Email , Ex : info@reehana.com',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 14,
                'setting_key'   => 'Contact Us : Working Day & Hour',
                'setting_val'   => 'Saturday - Friday 10:00 a.m. - 10:00 p.m.',
                'description'   => 'Contact Us : Working Day & Hour , Ex : Saturday - Friday 10:00 a.m. - 10:00 p.m.',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 15,
                'setting_key'   => 'Contact Us : Venue',
                'setting_val'   => 'ABC Luxury Center, Ground Floor, Kuwait City, Kuwait',
                'description'   => 'Contact Us : Venue , Ex : ABC Luxury Center, Ground Floor, Kuwait City, Kuwait',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 16,
                'setting_key'   => 'COD Allow ?',
                'setting_val'   => 0,
                'description'   => 'Want to allow Cash on Delivery?',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'radio',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 17,
                'setting_key'   => 'Admin Email',
                'setting_val'   => 'info@reehana.com',
                'description'   => 'All orders will be received on this Email ID',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 18,
                'setting_key'   => 'Mail From (Sender Email)',
                'setting_val'   => 'info@reehana.com',
                'description'   => 'Communication Email for order, All Email will sent from this Email ID',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 8,
                'setting_key'   => 'Twitter URL',
                'setting_val'   => '',
                'description'   => 'Website Twitter URL',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 21,
                'setting_key'   => 'Footer Text',
                'setting_val'   => '&copy; Reehana',
                'description'   => 'Footer Text (It will display on footer of your website, Example  : &copy; 2020 Your Site Name)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 22,
                'setting_key'   => 'Footer Company Name',
                'setting_val'   => 'Designed by <a href="http://www.uno-digital.com/" target="_blank">UNO Digital</a>',
                'description'   => 'Footer Company Name (It will display on footer of your website, Example  : &copy; 2020 Your Site Name)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 23,
                'setting_key'   => 'Web Page Description',
                'setting_val'   => 'Reehana | The WOMENS Fashion Store!',
                'description'   => 'Web Page Description (Default page description of your website, It will set in content of meta description)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 24,
                'setting_key'   => 'Contact Us Page - Image',
                'setting_val'   => '',
                'description'   => 'Contact Us Page - Image (Image to set on Contact Us Page), (Size ' . config('custom.contact_us_width') . 'x' . config('custom.contact_us_height') . ' pixels)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'file',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 3,
                'setting_key'   => 'Website Logo (For Email and Print Page in PNG Format)',
                'setting_val'   => 'logo/logo.png',
                'description'   => 'Logo to show in Mail and Print (Size ' . config('custom.app_logo_png_width') . 'x' . config('custom.app_logo_png_height') . ' pixels, Allowed Format : PNG)',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'file',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 25,
                'setting_key'   => 'Payment Gateway Environment Test Mode?',
                'setting_val'   => 1,
                'description'   => 'Test or Live <b>(Yes = Test, No = Live)</b>',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'radio',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 26,
                'setting_key'   => 'Payment Gateway URL',
                'setting_val'   => "https://apitest.myfatoorah.com",
                'description'   => 'Test : https://apitest.myfatoorah.com, Live : https://api.myfatoorah.com',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 27,
                'setting_key'   => 'Payment Gateway Token',
                'setting_val'   => "rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL",
                'description'   => 'PAYMENT_TOKEN',
                'is_multi_lang' => '0',
                'is_require'    => '0',
                'can_edit'      => '0',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 28,
                'setting_key'   => 'Customer Care Email',
                'setting_val'   => 'info@reehana.com',
                'description'   => 'Customer Care Email ID, It will pass in all Emails (Orders, Wishlist etc.)',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '1',
                'type'          => 'text',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 29,
                'setting_key'   => 'Return & Exchange Policy',
                'setting_val'   => '',
                'description'   => 'Web site Return & Exchange Policy',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '1',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
            array(
                'sorting'       => 31,
                'setting_key'   => 'Coupon Code - Template',
                'setting_val'   => '',
                'description'   => 'Coupon code Template (Please do not change #variable)',
                'is_multi_lang' => '0',
                'is_require'    => '1',
                'can_edit'      => '1',
                'type'          => 'textarea',
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ),
        );

        //Current Total 30

        DB::table('admin_settings')->insert($data);
    }

}

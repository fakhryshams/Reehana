<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuLevelSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu_level_1')->delete();
        DB::table('menu_level_2')->delete();
        DB::table('menu_level_3')->delete();
        DB::table('permissions')->delete();

        DB::statement(DB::raw('ALTER TABLE menu_level_1 AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE menu_level_2 AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE menu_level_3 AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE permissions AUTO_INCREMENT = 1'));

        $menu = [
            '1:Super Admin' =>
            [
                '1:Role'              => [],
                '2:Permission'        => [],
                '3:Role - Permission' => [],
                '4:User - Role'       => [],
            ],
            '2:Admin'       =>
            [
                '5:Dashboard'        => [],
                '6:Profile'          => [],
                '7:Order'            => [
                    '1:Order',
                    //'2:Order Status',
                    '3:Wish List',
                ],
                '8:Product'          => [
                    '4:Product',
                    '5:Category',
                    '6:Sub Category',
                ],
                '9:Master'           => [
                    '7:Color',
                    '8:Unit',
                    '9:Countries',
                    '10:City-Area',
                    '40:Zone',
                    '25:Size',
                    '26:Courier Company',
                    '30:Coupon',
                ],
                '10:General Setting' => [
                    '12:Settings',
                    //'15:Alert Settings',
                    //'22:Home Categories Section',
                    //'27:Media',
                    '29:Payment Method',
                ],
                '11:Customers'       => [],
                '12:Reports'         => [
                    '16:Sales - Customerwise',
                    '17:Product Liked',
                    '18:Sales - Datewise',
                    '19:Sales - Monthwise',
                    '20:Sales - Yearwise',
                    '21:Top Selling Products',
                    '31:Coupon Code Report',
                ],
                '13:Contact Us'      => [],
                '14:Pages'           => [
                    '11:Home Slider',
                    '39:Our Collection',
                    '13:About Us',
                    '14:Shipping Policy',
                    '23:Return Policy',
                    '24:Privacy Policy',
                    '28:Terms & Condition',
                    '32:Contact Us',
                ],
                '16:Monogram'           => [
                    '33:Font Style',
                    '34:Letter Count',
                    '35:Monogram Color',
                    '36:Monogram Placement',
                    '37:Monogram Master',
                    '38:Monogram Image',
                ],
            //'14:Customized Form' => [],
            ]
        ];

        $permission_tag = [
            'View',
            'Create',
            'Edit',
            'Delete'
        ];
        /*
         * MENU LEVEL 1
         */

        $role        = \App\SuperAdmin\Role::find(1); //Super Admin
        $roleAdmin   = \App\SuperAdmin\Role::find(2); //Admin
        $roleManager = \App\SuperAdmin\Role::find(3); //Manager

        foreach ($menu as $key1 => $value1)
        {
            $key1 = explode(":", $key1);

            $level1_ID = $key1[0];
            $key1      = $key1[1];

            $level1 = App\SuperAdmin\MenuLevel1::create(['id' => $level1_ID, 'name' => $key1]);
            //echo "Level 1 : ID  : $i :: $key1<br>";
            foreach ($permission_tag as $tag)
            {
                //echo $tag . "_" . $level1->id."\n";
                $permission = App\SuperAdmin\Permission::create(['guard_name' => 'admin', 'name' => $tag . "_" . $level1->id, 'description' => $tag . "-" . $key1, 'menu_level_1_id' => $level1->id]);

                $role->givePermissionTo($permission);
                $permission->assignRole($role);
                if ($level1_ID != 1)
                {
                    /*
                     * SuperAdmin Role-Permission not assign to Admin
                     */
                    $roleAdmin->givePermissionTo($permission);
                    $permission->assignRole($roleAdmin);
                    $roleManager->givePermissionTo($permission);
                    $permission->assignRole($roleManager);
                }
            }

            /*
             * MENU LEVEL 2
             */
            foreach ($value1 as $key2 => $value2)
            {
                $key2 = explode(":", $key2);

                $level2_ID = $key2[0];
                $key2      = $key2[1];

                $level2 = App\SuperAdmin\MenuLevel2::create(['id' => $level2_ID, 'name' => $key2, 'menu_level_1_id' => $level1->id]);
                //echo "Level 2 : ID  : $j ::  $key2<br>";
                foreach ($permission_tag as $tag)
                {
                    $permission = App\SuperAdmin\Permission::create(['guard_name' => 'admin', 'name' => $tag . "_" . $level1->id . "_" . $level2->id, 'description' => $tag . "-" . $key1 . "-" . $key2, 'menu_level_1_id' => $level1->id, 'menu_level_2_id' => $level2->id]);
                    $role->givePermissionTo($permission);
                    $permission->assignRole($role);
                    $roleAdmin->givePermissionTo($permission);
                    $permission->assignRole($roleAdmin);
                    $roleManager->givePermissionTo($permission);
                    $permission->assignRole($roleManager);
                }

                /*
                 * MENU LEVEL 3
                 */
                foreach ($value2 as $key3 => $value3)
                {
                    $value3 = explode(":", $value3);

                    $level3_ID = $value3[0];
                    $value3    = $value3[1];

                    $level3 = App\SuperAdmin\MenuLevel3::create(['id' => $level3_ID, 'name' => $value3, 'menu_level_2_id' => $level2->id]);
                    //echo "Level 3 : ID : $k :: $value3<br>";
                    foreach ($permission_tag as $tag)
                    {
                        $permission = App\SuperAdmin\Permission::create(['guard_name' => 'admin', 'name' => $tag . "_" . $level1->id . "_" . $level2->id . "_" . $level3->id, 'description' => $tag . "-" . $key1 . "-" . $key2 . "-" . $value3, 'menu_level_1_id' => $level1->id, 'menu_level_2_id' => $level2->id, 'menu_level_3_id' => $level2->id]);
                        $role->givePermissionTo($permission);
                        $permission->assignRole($role);
                        $roleAdmin->givePermissionTo($permission);
                        $permission->assignRole($roleAdmin);
                        $roleManager->givePermissionTo($permission);
                        $permission->assignRole($roleManager);
                    }
                }
                /*
                 * END MENU LEVEL 3
                 */
            }
            /*
             * END MENU LEVEL 2
             */
        }
        /*
         * END MENU LEVEL 1
         */
    }

}

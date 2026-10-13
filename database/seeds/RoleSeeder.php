<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();
        DB::statement(DB::raw('ALTER TABLE roles AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'name'       => 'super-admin',
                'guard_name' => 'admin',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'name'       => 'admin',
                'guard_name' => 'admin',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'name'       => 'manager',
                'guard_name' => 'admin',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'name'       => 'user',
                'guard_name' => 'web',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );

        DB::table('roles')->insert($data);

        /*
         * Assign Role
         * 
         * 1 = Super Admin -> Role : super-admin
         * 2 = Admin -> Role : admin
         */
        \App\Admin::find(1)->assignRole('super-admin');
        \App\Admin::find(2)->assignRole('admin');
        \App\Admin::find(3)->assignRole('manager');
    }

}

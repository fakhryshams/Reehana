<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->delete();
        DB::statement(DB::raw('ALTER TABLE admins AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'name'       => 'Super Admin',
                'username'   => 'superadmin',
                'email'      => 'superadmin@salmamusab.com',
                'password'   => Hash::make('@@123456'),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'name'       => 'Admin',
                'username'   => 'admin',
                'email'      => 'admin@salmamusab.com',
                'password'   => Hash::make('123456'),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'name'       => 'Manager',
                'username'   => 'manager',
                'email'      => 'info@salmamusab.com',
                'password'   => Hash::make('12345678'),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            )
        );
        DB::table('admins')->insert($data);
    }

}

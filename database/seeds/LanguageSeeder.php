<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_languages')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_languages AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'name'       => 'English',
                'code'       => 'en',
                'direction'  => 'LTR',
                'is_active'  => 1,
                'is_default' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
//            array(
//                'name'       => 'Arabic',
//                'code'       => 'ar',
//                'direction'  => 'RTL',
//                'is_active'  => 1,
//                'is_default' => 0,
//                'created_at' => date("Y-m-d H:i:s"),
//                'updated_at' => date("Y-m-d H:i:s")
//            )
        );

        DB::table('admin_languages')->insert($data);
    }

}

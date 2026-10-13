<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_color')->delete();
        DB::table('admin_color_description')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_color AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE admin_color_description AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'id'         => '1',
                'sorting'    => '1',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
            array(
                'id'         => '2',
                'sorting'    => '2',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_color')->insert($data);

        $data = array(
            array(
                'id'          => '1',
                'color_id'    => '1',
                'language_id' => '1',
                'color_title' => 'Black',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '2',
                'color_id'    => '1',
                'language_id' => '2',
                'color_title' => 'أسود',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '3',
                'color_id'    => '2',
                'language_id' => '1',
                'color_title' => 'White',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '4',
                'color_id'    => '2',
                'language_id' => '2',
                'color_title' => 'أبيض',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_color_description')->insert($data);
    }

}

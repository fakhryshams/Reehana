<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_units')->delete();
        DB::table('admin_units_description')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_units AUTO_INCREMENT = 1'));
        DB::statement(DB::raw('ALTER TABLE admin_units_description AUTO_INCREMENT = 1'));

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
            ),
        );

        DB::table('admin_units')->insert($data);

        $data = array(
            array(
                'id'          => '1',
                'units_id'    => '1',
                'language_id' => '1',
                'unit_name'   => 'Gram',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '2',
                'units_id'    => '1',
                'language_id' => '2',
                'unit_name'   => 'غرام',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '3',
                'units_id'    => '2',
                'language_id' => '1',
                'unit_name'   => 'Kilo Gram',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
            array(
                'id'          => '4',
                'units_id'    => '2',
                'language_id' => '2',
                'unit_name'   => 'كيلو جرام',
                'created_at'  => date("Y-m-d H:i:s"),
                'updated_at'  => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_units_description')->insert($data);
    }

}

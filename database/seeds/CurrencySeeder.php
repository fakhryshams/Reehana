<?php

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        DB::table('admin_currencies')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_currencies AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'id'                  => '1',
                'title'               => 'KD',
                'code'                => 'KD',
                'symbol_left'         => null,
                'symbol_right'        => 'KD',
                'country_id'          => null,
                'position'            => 'Right',
                'decimal_point'       => '1',
                'thousands_seperator' => null,
                'decimal_seperator'   => '1',
                'value'               => null,
                'is_default'          => '1',
                'is_current'          => '1',
                'is_active'           => '1',
                'created_at'          => date("Y-m-d H:i:s"),
                'updated_at'          => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_currencies')->insert($data);
    }

}

<?php

use Illuminate\Database\Seeder;

class MonogramLetterCount extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_monogram_letter_count')->delete();
        DB::statement(DB::raw('ALTER TABLE admin_monogram_letter_count AUTO_INCREMENT = 1'));

        $data = array(
            array(
                'letter_count_name' => 'Single Letter',
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s")
            ),
            array(
                'letter_count_name' => 'Two Letter',
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s")
            ),
            array(
                'letter_count_name' => 'Three Letter',
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s")
            ),
            array(
                'letter_count_name' => 'More than three',
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s")
            ),
        );

        DB::table('admin_monogram_letter_count')->insert($data);
    }

}

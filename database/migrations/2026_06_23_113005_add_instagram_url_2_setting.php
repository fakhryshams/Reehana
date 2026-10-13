<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInstagramUrl2Setting extends Migration
{
    public function up()
    {
        DB::table('admin_settings')->insert([
            'sorting'      => 34,
            'setting_key'  => 'Instagram URL 2',
            'setting_val'  => '',
            'description'  => 'Website Instagram URL 2',
            'is_multi_lang'=> 0,
            'is_require'   => 0,
            'can_edit'     => 1,
            'type'         => 'text',
            'is_active'    => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function down()
    {
        DB::table('admin_settings')->where('setting_key', 'Instagram URL 2')->delete();
    }
}

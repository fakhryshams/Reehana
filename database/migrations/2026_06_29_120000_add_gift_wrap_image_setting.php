<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddGiftWrapImageSetting extends Migration
{
    public function up()
    {
        DB::table('admin_settings')->insert([
            'sorting'      => 32,
            'setting_key'  => 'Gift Wrap Image',
            'setting_val'  => '',
            'description'  => 'Gift Wrap Image',
            'is_multi_lang'=> 0,
            'is_require'   => 0,
            'can_edit'     => 1,
            'type'         => 'file',
            'is_active'    => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function down()
    {
        DB::table('admin_settings')->where('setting_key', 'Gift Wrap Image')->delete();
    }
}

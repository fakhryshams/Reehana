<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGiftBoxSettings extends Migration
{
    public function up()
    {
        DB::table('admin_settings')->insert([
            [
                'sorting'      => 32,
                'setting_key'  => 'Gift Box',
                'setting_val'  => '0',
                'description'  => 'Enable/Disable Gift Box',
                'is_multi_lang'=> 0,
                'is_require'   => 0,
                'can_edit'     => 1,
                'type'         => 'radio',
                'is_active'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'sorting'      => 33,
                'setting_key'  => 'Gift Box Amount',
                'setting_val'  => '0',
                'description'  => 'Gift Box Price',
                'is_multi_lang'=> 0,
                'is_require'   => 1,
                'can_edit'     => 1,
                'type'         => 'text',
                'is_active'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'sorting'      => 32,
                'setting_key'  => 'Gift Box Image',
                'setting_val'  => '',
                'description'  => 'Gift Box Image',
                'is_multi_lang'=> 0,
                'is_require'   => 0,
                'can_edit'     => 1,
                'type'         => 'file',
                'is_active'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'sorting'      => 32,
                'setting_key'  => 'Gift Box Message',
                'setting_val'  => '',
                'description'  => 'Gift Box Description/Message',
                'is_multi_lang'=> 0,
                'is_require'   => 1,
                'can_edit'     => 1,
                'type'         => 'textarea',
                'is_active'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'sorting'      => 33,
                'setting_key'  => 'Gift Box Min Amount',
                'setting_val'  => '0',
                'description'  => 'Minimum cart total for free Gift Box',
                'is_multi_lang'=> 0,
                'is_require'   => 1,
                'can_edit'     => 1,
                'type'         => 'text',
                'is_active'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('admin_settings')->where('setting_key', 'like', 'Gift Box%')->delete();
    }
}

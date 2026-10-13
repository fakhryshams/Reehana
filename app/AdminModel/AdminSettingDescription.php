<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminSettingDescription extends Model
{

    protected $table   = 'admin_settings_description';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public function getLangTitle()
    {
        return $this->hasOne('App\AdminModel\AdminLanguage', 'id', 'language_id')->first();
    }

}

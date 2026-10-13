<?php

namespace App\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class MenuLevel2 extends Model
{

    protected $table   = "menu_level_2";
    protected $guarded = ['created_at,updated_at'];

    public function getMenuLevel3()
    {
        return $this->belongsTo('App\SuperAdmin\MenuLevel3', 'id', 'menu_level_2_id')->get();
    }

}

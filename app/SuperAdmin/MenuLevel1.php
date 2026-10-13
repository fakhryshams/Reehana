<?php

namespace App\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class MenuLevel1 extends Model
{

    protected $table   = "menu_level_1";
    protected $guarded = ['created_at,updated_at'];

    public function getMenuLevel1()
    {
        return $this->orderBy('id', 'asc')->get();
    }

    public function getMenuLevel2()
    {
        return $this->belongsTo('App\SuperAdmin\MenuLevel2', 'id', 'menu_level_1_id')->get();
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{

    protected $table   = "filter";
    protected $guarded = ['created_at,updated_at,deleted_at'];

}

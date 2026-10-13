<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;

class Customized extends Model
{

    protected $table   = 'customized_form';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */
}

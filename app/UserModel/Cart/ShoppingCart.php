<?php

namespace App\UserModel\Cart;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{

    protected $table   = 'shopping_cart';
    protected $guarded = ['created_at,updated_at,deleted_at'];

}

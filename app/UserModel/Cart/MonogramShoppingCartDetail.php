<?php

namespace App\UserModel\Cart;

use Illuminate\Database\Eloquent\Model;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Size\AdminSizeDescription;

class MonogramShoppingCartDetail extends Model
{

    protected $table   = 'cart_monogram_detail';
    protected $guarded = ['created_at,updated_at,deleted_at'];

}

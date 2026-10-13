<?php

namespace App\UserModel\Cart;

use Illuminate\Database\Eloquent\Model;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Size\AdminSizeDescription;

class ShoppingCartDetail extends Model
{

    protected $table   = 'shopping_cart_detail';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public function getProduct()
    {
        return $this->hasOne(AdminProduct::class, 'id', 'product_id')->first();
    }

    public function getSize()
    {
        return $this->hasOne(AdminSizeDescription::class, 'size_id', 'size_id')->first();
    }

    public function updateShoppingCartDetailStatus($status)
    {
        
        $this->is_active = $status;
        $this->save();
    }

}

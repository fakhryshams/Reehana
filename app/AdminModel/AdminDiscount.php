<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminDiscount extends Model
{

    protected $table = 'discount';



    public function gerCategoryDiscount()
    {
        return $this->hasMany('App\AdminModel\CategoryDiscount', 'discount_id', 'id')->pluck('category_id')->toArray();
    }

    public function gerBrandDiscount()
    {

        return $this->hasMany('App\AdminModel\CategoryDiscount', 'discount_id', 'id')->pluck('brand_id')->toArray();
    }
    public function categoryDiscounts()
    {
        return $this->hasMany(CategoryDiscount::class, 'discount_id');
    }

   
}

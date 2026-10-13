<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use App\AdminModel\Size\AdminSizeDescription;
use App\AdminModel\Brand\AdminBrandDescription;
use App\AdminModel\Color\AdminColorDescription;
use App\AdminModel\Product\AdminProduct;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{

    use SoftDeletes;

    protected $table   = 'orders_detail';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($data)
        {
            $data->created_by = auth()->user()->id;
        });
        static::updating(function ($data)
        {
            $data->updated_by = auth()->user()->id;
        });
        static::deleting(function ($data)
        {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

//
    /*
     * get product
     */

    public function getProduct($languageId = 1)
    {
        return $this->hasOne('App\AdminModel\Product\AdminProductDescription', 'products_id', 'product_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getProductDetail()
    {
        return $this->hasOne(AdminProduct::class, 'id', 'product_id')->withTrashed()->first();
    }

    public function getProductImage()
    {
        return $this->hasOne('App\AdminModel\Product\AdminProductImage', 'products_id', 'product_id')->withTrashed()->first();
    }

    public function getSizeDetail($languageId = 1)
    {
        return $this->hasOne(AdminSizeDescription::class, 'size_id', 'size_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getColorDetail($languageId = 1)
    {
        return $this->hasOne(AdminColorDescription::class, 'color_id', 'color_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getMonogramColorDetail($languageId = 1)
    {
        return $this->hasOne(\App\AdminModel\Monogram\MonogramColor::class, 'id', 'monogram_color_id')->withTrashed()->first();
    }

}

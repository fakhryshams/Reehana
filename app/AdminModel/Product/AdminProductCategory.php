<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminProductCategory extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_products_categories';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

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

    /*
     * Blade File - Product Index Page
     */

    public function getCategory()
    {
        return $this->hasOne('\App\AdminModel\Product\AdminCategory', 'id', 'categories_id')->first();
    }

    public function getProductDescription($languageId = "", $getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\Product\AdminProductDescription', 'products_id', 'products_id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductImages($isDefault = "", $getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\Product\AdminProductImage', 'products_id', 'products_id');
        if ($isDefault)
        {
            $query->where('is_default', 1);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProduct($getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\Product\AdminProduct', 'id', 'products_id')->where('is_active',1);

        return $getAll ? $query->get() : $query->first();
    }

}

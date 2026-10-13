<?php

namespace App\AdminModel\GeneralSetting\HomeLatestCollection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminHomeLatestCollection extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_home_latest_collection';
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

    public function getAllData()
    {
        return $this->orderBy('id', 'asc')->get();
    }

    /*
     * code added by mayur
     */

    public function getCategoryDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Product\AdminCategoryDescription', 'categories_id', 'category_id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getSubCategoryDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Product\AdminCategoryDescription', 'categories_id', 'sub_category_id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductDescription($languageId = "", $getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\Product\AdminProductDescription', 'products_id', 'product_id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * Used on Homepage - Front
     */

    public function getProduct()
    {
        return $this->hasOne('App\AdminModel\Product\AdminProduct', 'id', 'product_id')->first();
    }

}

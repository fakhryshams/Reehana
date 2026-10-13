<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCategoryDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_categories_description';
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
     * Get data by Category ID and Language ID
     */

    public static function getDataByLanguageID($categoryId = "", $languageId = "")
    {
        $query = AdminCategoryDescription::where('id', '!=', null);
        if ($categoryId)
        {
            $query->where('categories_id', $categoryId);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

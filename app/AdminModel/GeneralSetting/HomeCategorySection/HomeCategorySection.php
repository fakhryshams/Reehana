<?php

namespace App\AdminModel\GeneralSetting\HomeCategorySection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeCategorySection extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_home_category';
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

    public static function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query = HomeCategorySection::where('can_edit', 1)->orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('setting_key', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('setting_val', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('description', 'LIKE', '%' . $searchText . '%');
        }
        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function getCategoryData()
    {
        $query = $this->hasOne('App\AdminModel\GeneralSetting\HomeCategorySection\HomeCategorySectionDescription', 'home_categories_section_id', 'id')->first();
        return $query;
    }

    public function getHomeCategoryDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\GeneralSetting\HomeCategorySection\HomeCategorySectionDescription', 'home_categories_section_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

}

<?php

namespace App\AdminModel\GeneralSetting\HomeCategorySection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeCategorySectionDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_home_categories_section_description';
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
     * Get data by Banner ID and Language ID
     */

    public static function getDataByLanguageID($homeCategoryID = "", $languageId = "")
    {
//        dd($homeCategoryID);
        $query = HomeCategorySectionDescription::where('id', '!=', null);
        if ($homeCategoryID)
        {
            $query->where('home_categories_section_id', $homeCategoryID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        
        return $query->first();
    }

}

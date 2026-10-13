<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCountryDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_country_description';
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

    /*
     * Get data by Banner ID and Language ID
     */

    public static function getDataByLanguageID($countryID = "", $languageId = "")
    {
        $query = AdminCountryDescription::where('id', '!=', null);
        if ($countryID)
        {
            $query->where('country_id', $countryID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function getCountryData()
    {
        return AdminCountryDescription::where('is_active', 1)->where('language_id', 1)->get();
    }

}

<?php

namespace App\AdminModel\GeneralSetting\AboutUs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutUsDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_about_us_page_description';
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

    public static function getDataByLanguageID($sboutUsID = "", $languageId = "")
    {
        $query = AboutUsDescription::where('id', '!=', null);
        if ($sboutUsID)
        {
            $query->where('admin_about_us_id', $sboutUsID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

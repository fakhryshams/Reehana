<?php

namespace App\AdminModel\GeneralSetting\HomeSlider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminHomeSliderDescription extends Model
{

    //
    use SoftDeletes;

    protected $table   = 'admin_banner_management_description';
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
     * get all the data of admin home slider description
     */
    public function getAllData()
    {
        return $this->orderBy('updated_at', 'desc')->paginate(config('custom.per_page', 10));
    }

    /*
     * Get data by Banner ID and Language ID
     */

    public static function getDataByLanguageID($bannerID = "", $languageId = "")
    {
        $query = AdminHomeSliderDescription::where('id', '!=', null);
        if ($bannerID)
        {
            $query->where('banner_management_id', $bannerID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

//   
}

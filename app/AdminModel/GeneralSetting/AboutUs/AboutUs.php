<?php

namespace App\AdminModel\GeneralSetting\AboutUs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\GeneralSetting\AboutUs\AboutUs;

class AboutUs extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_about_us_page';
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
     * get About us Data from about us description
     * 
     */

    public function getAboutUsDescriptionData()
    {
        $query = $this->hasOne('App\AdminModel\GeneralSetting\AboutUs\AboutUsDescription', 'admin_about_us_id', 'id')->first();
        return $query;
    }

    /*
     * during edit get description data
     *
     */

    public function getAboutUsEditDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\GeneralSetting\AboutUs\AboutUsDescription', 'admin_about_us_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

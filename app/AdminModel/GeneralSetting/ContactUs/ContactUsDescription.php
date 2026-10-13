<?php

namespace App\AdminModel\GeneralSetting\ContactUs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUsDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_contact_us_page_description';
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
        $query = ContactUsDescription::where('id', '!=', null);
        if ($sboutUsID)
        {
            $query->where('admin_contact_us_id', $sboutUsID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

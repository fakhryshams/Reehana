<?php

namespace App\AdminModel\GeneralSetting\ContactUs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\GeneralSetting\ContactUs\ContactUs;

class ContactUs extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_contact_us_page';
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
     * get Contact us Data from contact us description
     * 
     */

    public function getContactUsDescriptionData()
    {
        $query = $this->hasOne('App\AdminModel\GeneralSetting\ContactUs\ContactUsDescription', 'admin_contact_us_id', 'id')->first();
        return $query;
    }

    /*
     * during edit get description data
     *
     */

    public function getContactUsEditDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\GeneralSetting\ContactUs\ContactUsDescription', 'admin_contact_us_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

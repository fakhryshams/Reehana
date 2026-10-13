<?php

namespace App\AdminModel\Size;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminSizeDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_size_description';
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

    public static function getDataByLanguageID($sizeID = "", $languageId = "")
    {
        $query = AdminSizeDescription::where('id', '!=', null);
        if ($sizeID)
        {
            $query->where('size_id', $sizeID);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

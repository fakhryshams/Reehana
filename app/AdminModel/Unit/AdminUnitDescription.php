<?php

namespace App\AdminModel\Unit;

use Illuminate\Database\Eloquent\Model;

class AdminUnitDescription extends Model
{

    protected $table   = 'admin_units_description';
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

    public static function getDataByLanguageID($unitId = "", $languageId = "")
    {
        $query = AdminUnitDescription::where('id', '!=', null);
        if ($unitId)
        {
            $query->where('units_id', $unitId);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

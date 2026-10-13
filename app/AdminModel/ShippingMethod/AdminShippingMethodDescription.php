<?php

namespace App\AdminModel\ShippingMethod;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminShippingMethodDescription extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_shipping_methods_description';
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
     * Get data by Category ID and Language ID
     */

    public static function getDataByLanguageID($shipping_methods_id = "", $languageId = "")
    {
        $query = AdminShippingMethodDescription::where('id', '!=', null);
        if ($shipping_methods_id)
        {
            $query->where('shipping_methods_id', $shipping_methods_id);
        }
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

}

<?php

namespace App\AdminModel\GeneralSetting\ShippingPolicy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\GeneralSetting\ShippingPolicy\ShippingPolicyDescription;

class ShippingPolicy extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_shipping_policy';
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

    public function getShippingPolicyDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany(ShippingPolicyDescription::class, 'shipping_policy_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

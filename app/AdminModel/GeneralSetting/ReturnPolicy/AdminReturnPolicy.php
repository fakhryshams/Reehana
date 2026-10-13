<?php

namespace App\AdminModel\GeneralSetting\ReturnPolicy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\GeneralSetting\ReturnPolicy\AdminReturnPolicyDescription;

class AdminReturnPolicy extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_return_policy';
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

    public function getReturnPolicyDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany(AdminReturnPolicyDescription::class, 'return_policy_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

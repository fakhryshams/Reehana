<?php

namespace App\AdminModel\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\AdminCountry;
use App\AdminModel\City\AdminCity;

class WarehouseAddress extends Model
{
    use SoftDeletes;

    protected $table = 'warehouse_addresses';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            if (auth()->check()) {
                $data->created_by = auth()->user()->id;
            }
        });

        static::updating(function ($data) {
            if (auth()->check()) {
                $data->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($data) {
            if (auth()->check()) {
                $data->deleted_by = auth()->user()->id;
            }
            $data->save();
        });
    }

    public static function getAddress()
    {
        return self::where('is_active', 1)->first();
    }

    public function countryDetail()
    {
        return $this->belongsTo(AdminCountry::class, 'country_id', 'id');
    }

    public function areaDetail()
    {
        return $this->belongsTo(AdminCity::class, 'area', 'id');
    }
}

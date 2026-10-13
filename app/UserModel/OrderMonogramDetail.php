<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use App\AdminModel\Size\AdminSizeDescription;
use App\AdminModel\Brand\AdminBrandDescription;
use App\AdminModel\Color\AdminColorDescription;
use App\AdminModel\Product\AdminProduct;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMonogramDetail extends Model
{

    use SoftDeletes;

    protected $table   = 'order_monogram_detail';
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

    public function getMonogramColorDetail($languageId = 1)
    {
        return $this->hasOne(\App\AdminModel\Monogram\MonogramColor::class, 'id', 'monogram_color_id')->withTrashed()->first();
    }

}

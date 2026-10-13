<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminProductImage extends Model
{

    use SoftDeletes;

    protected $table = 'admin_products_images';
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

    public function getColor()
    {
        return $this->hasOne(\App\AdminModel\Color\AdminColorDescription::class, 'color_id', 'id')->withTrashed()->first();
    }

}

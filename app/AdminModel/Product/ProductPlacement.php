<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;

class ProductPlacement extends Model
{

    protected $table   = 'product_placement';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();

        // static::creating(function ($data) {
        //     $data->created_by = auth()->user()->id;
        // });

        // static::updating(function ($data) {
        //     $data->updated_by = auth()->user()->id;
        // });

        // static::deleting(function ($data) {
        //     $data->deleted_by = auth()->user()->id;
        //     $data->save();
        // });
    }

    
}

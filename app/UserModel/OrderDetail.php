<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use App\AdminModel\Size\AdminSizeDescription;
use App\AdminModel\Brand\AdminBrandDescription;
use App\AdminModel\Color\AdminColorDescription;
use App\AdminModel\Product\AdminProduct;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{

    use SoftDeletes;

    protected $table   = 'orders_detail';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($data)
        {
            if (auth()->check()) {
                $data->created_by = auth()->user()->id;
            }

            // CRITICAL DATA INTEGRITY GUARD:
            // Customized order details (product_id is null/empty or custom_attributes is not empty)
            // MUST NEVER be attached to a Regular Order.
            $isCustomItem = (empty($data->product_id) || !empty($data->custom_attributes));
            if ($isCustomItem && !empty($data->order_id)) {
                $parentOrder = \App\UserModel\Order::find($data->order_id);
                if ($parentOrder && $parentOrder->order_type !== 'custom') {
                    \Illuminate\Support\Facades\Log::error("[CRITICAL_DATA_INTEGRITY] Blocked attempt to insert customized OrderDetail into regular Order #{$data->order_id}");
                    return false;
                }
            }
        });
        static::updating(function ($data)
        {
            if (auth()->check()) {
                $data->updated_by = auth()->user()->id;
            }

            // CRITICAL DATA INTEGRITY GUARD:
            $isCustomItem = (empty($data->product_id) || !empty($data->custom_attributes));
            if ($isCustomItem && !empty($data->order_id)) {
                $parentOrder = \App\UserModel\Order::find($data->order_id);
                if ($parentOrder && $parentOrder->order_type !== 'custom') {
                    \Illuminate\Support\Facades\Log::error("[CRITICAL_DATA_INTEGRITY] Blocked attempt to update customized OrderDetail on regular Order #{$data->order_id}");
                    return false;
                }
            }
        });
        static::deleting(function ($data)
        {
            if (auth()->check()) {
                $data->deleted_by = auth()->user()->id;
                $data->save();
            }
        });
    }

//
    /*
     * get product
     */

    public function getProduct($languageId = 1)
    {
        return $this->hasOne('App\AdminModel\Product\AdminProductDescription', 'products_id', 'product_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getProductDetail()
    {
        return $this->hasOne(AdminProduct::class, 'id', 'product_id')->withTrashed()->first();
    }

    public function getProductImage()
    {
        return $this->hasOne('App\AdminModel\Product\AdminProductImage', 'products_id', 'product_id')->withTrashed()->first();
    }

    public function getSizeDetail($languageId = 1)
    {
        return $this->hasOne(AdminSizeDescription::class, 'size_id', 'size_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getColorDetail($languageId = 1)
    {
        return $this->hasOne(AdminColorDescription::class, 'color_id', 'color_id')->where('language_id', $languageId)->withTrashed()->first();
    }

    public function getMonogramColorDetail($languageId = 1)
    {
        return $this->hasOne(\App\AdminModel\Monogram\MonogramColor::class, 'id', 'monogram_color_id')->withTrashed()->first();
    }

}

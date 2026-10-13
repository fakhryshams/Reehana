<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Size\AdminSize;
use App\AdminModel\Color\AdminColorDescription;
use App\Helpers\Helper;

class AdminProductAttribute extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_products_attributes';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data) {
            if(isset(auth()->user()->id)) {
                $data->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($data) {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }


    public function getProductSalePriceAttribute()
    {

        $currency = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $product_sale_price = $this->attributes['product_sale_price'];
        if ($currency) {
            $product_sale_price = $product_sale_price * $currency->value;
        }

        $storeDiscount = Helper::getCategoryDiscount($this->attributes['products_id']);

        if ($storeDiscount > 0 && $product_sale_price > 0) {

            $catdiscountApplied = ($product_sale_price * $storeDiscount) / 100;
            return $product_sale_price - $catdiscountApplied;
        } else {

            return $product_sale_price;
        }
    }

    public function getProductRegularPriceAttribute()
    {

        $currency = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $product_regular_price = $this->attributes['product_regular_price'];
        if ($currency) {
            $product_regular_price = $product_regular_price * $currency->value;
        }

        $storeDiscount = Helper::getCategoryDiscount($this->attributes['products_id']);

        if ($storeDiscount > 0 && $product_regular_price > 0 && $this->attributes['is_product_sale_price_null']) {

            if ($this->attributes['product_sale_price'] != $this->attributes['product_regular_price']) {
                $catdiscountApplied = ($product_regular_price * $storeDiscount) / 100;

                return $product_regular_price - $catdiscountApplied;
            } else {
                return $product_regular_price;
            }
        } else {
            return $product_regular_price;
        }
    }

    public function getOriginalDiscountAttribute()
    {
        return $this->attributes['product_sale_price'];
    }

    public function getOriginaPriceAttribute()
    {
        return $this->attributes['product_regular_price'];
    }

    public function getIsProductSalePriceNullAttribute()
    {
        $storeDiscount = Helper::getCategoryDiscount($this->attributes['products_id']);

        if ($storeDiscount > 0 && $this->attributes['product_regular_price'] > 0) {
            return 0;
        } else {
            return $this->attributes['is_product_sale_price_null'];
        }
    }

    public function getSize()
    {
        return $this->hasOne(AdminSize::class, 'id', 'size_id')->first();
    }
}

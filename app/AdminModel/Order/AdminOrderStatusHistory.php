<?php

namespace App\AdminModel\Order;

use Illuminate\Database\Eloquent\Model;

class AdminOrderStatusHistory extends Model
{

    protected $table   = 'admin_order_status_history';
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

    public function getOrderStatus()
    {
        return $this->hasOne('App\AdminModel\Order\AdminOrderStatus', 'id', 'order_status_id')->first();
    }

}

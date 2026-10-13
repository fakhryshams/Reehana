<?php

namespace App\AdminModel\Order;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentLog extends Model
{
    protected $table = 'order_payment_logs';

    protected $fillable = [
        'order_id',
        'coupon_code_id',
        'coupon_discount_amount',
        'total_amount',
        'due_amount',
        'final_amount',
        'user_id',
        'meta',
        'discount_type',
        'discount_value',
        'discount_per',
    ];
}

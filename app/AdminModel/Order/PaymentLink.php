<?php

namespace App\AdminModel\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentLink extends Model
{
    use SoftDeletes;
    protected $table   = 'payment_links';
    protected $guarded = ['created_at,updated_at,deleted_at'];
    
    protected $fillable = [
        'order_id',
        'payment_link',
        'amount',
        'generated_from',
        'generated_by',
        'status',
        'paid_amount',
        'paid_at',
        'gateway_response',
        'payment_type',
        'payment_request_id',
        'is_advance_payment',
        'customer_email',
        'email_sent_count',
        'last_email_sent_at'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($data)
        {
            if (auth()->check()) {
                $data->created_by = auth()->id();
            }
        });
        static::updating(function ($data)
        {
            if (auth()->check()) {
                $data->updated_by = auth()->id();
            }
        });
        static::deleting(function ($data)
        {
            if (auth()->check()) {
                $data->deleted_by = auth()->id();
                $data->save();
            }
        });
    }
}

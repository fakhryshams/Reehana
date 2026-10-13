<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemporaryOrderDetail extends Model
{
    use SoftDeletes;

    protected $table = 'temporary_order_details';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'total_price' => 'float',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->user()->id;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->user()->id;
                $model->save();
            }
        });
    }

    /**
     * Parent temporary order
     */
    public function temporaryOrder()
    {
        return $this->belongsTo(TemporaryOrder::class, 'temporary_order_id', 'id');
    }
}

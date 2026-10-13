<?php

namespace App\AdminModel\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    protected $table = 'user_login_logs';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'email',
        'login_type',
        'user_type',
        'physical_store_id',
        'status',
        'ip_address',
        'user_agent',
        'message',
        'created_at',
    ];
}

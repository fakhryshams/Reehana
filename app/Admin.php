<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Admin extends Authenticatable
{

    use Notifiable,
        HasRoles,
        SoftDeletes;

    protected $guard = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'midname', 'lastname', 'email', 'address', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getUserData()
    {
        return Admin::orderBy('name', 'asc')->get();
    }

    /*
     * SuperAdmin - User Role
     */

    public static function getUserDataForUserRoles($searchText = '', $sortKey = 'username', $sortVal = 'desc', $paginate = true, $pageLength = false)
    {
        //return Admin::with('roles')->orderBy('updated_at', 'asc')->paginate(config('custom.per_page'));

        $query = Admin::with('roles')->orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('username', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('email', 'LIKE', '%' . $searchText . '%');
        }
        if ($paginate)
        {
            if ($pageLength)
            {
                $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
            }
            else
            {
                $paginate = config('custom.per_page', 10);
            }
            $query = $query->paginate($paginate);
        }
        else
        {
            return $query->get();
        }
        return $query;
    }

}

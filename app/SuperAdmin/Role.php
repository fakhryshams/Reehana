<?php

namespace App\SuperAdmin;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as Roles; //Extend existing model

class Role extends Roles
{
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

    /*
     * Listing Function
     */

    public static function getAllData($searchText, $sortKey, $sortVal, $paginate = true, $pageLength = false)
    {
        $query = Role::orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('name', 'LIKE', '%' . $searchText . '%');
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
    
    public static function getRoles()
    {
        return Role::orderBy('name', 'asc')->get();
    }

}

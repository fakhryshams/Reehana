<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminContactUs extends Model
{

    protected $table   = 'admin_contact_us';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

//        static::creating(function ($data)
//        {
//            $data->created_by = auth()->user()->id;
//        });
//
//        static::updating(function ($data)
//        {
//            $data->updated_by = auth()->user()->id;
//        });

        static::deleting(function ($data)
        {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        switch ($sortKey)
        {
            default:
                $query = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('first_name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('last_name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('email', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('mobile', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('subject', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('message', 'LIKE', '%' . $searchText . '%');
        }
        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

}

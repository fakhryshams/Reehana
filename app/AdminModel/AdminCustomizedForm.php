<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\AdminModel\Product\AdminCategory;

class AdminCustomizedForm extends Model
{

    protected $table   = 'customized_form';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

        if (Auth::user())
        {
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
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength, $requestTypeFilter)
    {
        switch ($sortKey)
        {
            default:
                $query = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('gift_type', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('sender_name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('receiver_name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('email', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('mobile', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('gift_occasion', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('gift_occasion', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('number_of_guests', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('gift_options', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('name_on_card', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('gift_budget', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('wrapping_budget', 'LIKE', '%' . $searchText . '%');
            $query->orWhereDate('preferred_delivery_date', date("Y-m-d", strtotime($searchText)));
            $query->orWhere('message', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('photo_reference', 'LIKE', '%' . $searchText . '%');
        }
        if ($requestTypeFilter)
        {
            $query->orWhereIn('gift_type', $requestTypeFilter);
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

    public function getCategory($categoryId)
    {
        return AdminCategory::find($categoryId);
    }

}

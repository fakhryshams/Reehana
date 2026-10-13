<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminCurrency extends Model
{

    protected $table   = 'admin_currencies';
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

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query = AdminCurrency::orderBy($sortKey, $sortVal);


        switch ($sortKey)
        {
            case 'country':
                $courseID  = $this->sortByCountryName($sortKey, $sortVal);
                $orderbyID = implode(",", $courseID);
                $query     = $this->orderByRaw("FIELD(country_id, $orderbyID)");
                break;
            case 'currency_code':
                $originID  = $this->sortByCountryCode($sortKey, $sortVal);
                $orderbyID = implode(",", $originID);
                $query     = $this->orderByRaw("FIELD(country_id, $orderbyID)");
                break;
            default:
                $query     = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {

            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('position', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('value', 'LIKE', '%' . $searchText . '%');

            $countryID = $this->searchbByCountryName($searchText);
            if ($countryID)
            {
                $query->orWhereIn('country_id', $countryID);
            }

            $countryID = $this->searchbByCountryCode($searchText);
            if ($countryID)
            {
                $query->orWhereIn('country_id', $countryID);
            }
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

    //------sort functions

    public function sortByCountryName($sortKey, $sortVal)
    {
        return \App\AdminModel\SadminCountries::orderBy('country_name', $sortVal)->pluck('id')->toArray();
    }

    public function sortByCountryCode($sortKey, $sortVal)
    {
        return \App\AdminModel\SadminCountries::orderBy('country_code', $sortVal)->pluck('id')->toArray();
    }

    //-----search functions

    public function searchbByCountryName($search_text)
    {
        return \App\AdminModel\SadminCountries::where('country_name', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public function searchbByCountryCode($search_text)
    {
        return \App\AdminModel\SadminCountries::where('country_code', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    //---- modal function ----
    public function getCountry()
    {
        return $this->hasOne('App\AdminModel\SadminCountries', 'id', 'country_id')->first();
    }

}

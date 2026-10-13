<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{

    use SoftDeletes;

    protected $table   = 'user_address';
    protected $guarded = ['created_at,updated_at,deleted_at'];
    protected $appends = ['is_country_active'];

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

    public function getcountry($getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\AdminCountry', 'id', 'country_id');
        return $getAll ? $query->get() : $query->first();
    }


     public function getIsCountryActiveAttribute()
   {
        if ($this->attributes['country_id'])
        {
            $country = \App\AdminModel\AdminCountry::find($this->attributes['country_id']);
            if ($country && $country->is_active == 1)
            {
                return true;
            }
        }
        return false;
    }

    /*
     * get user country name for order page 25/11/2020
     */

    public function getUserCountry($languageId = 1)
    {
        $query = $this->hasOne('App\AdminModel\AdminCountryDescription', 'country_id', 'country_id')->withTrashed();

        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public function getUserBillingCountry($languageId)
    {
        $query = $this->hasOne('App\AdminModel\AdminCountryDescription', 'country_id', 'billing_country_id')->withTrashed();

        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public function getUserCity($languageId)
    {
        $query = $this->hasOne('App\AdminModel\City\AdminCityDescription', 'city_id', 'city_id')->withTrashed();

        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public function getUserBillingCity($languageId)
    {
        $query = $this->hasOne('App\AdminModel\City\AdminCityDescription', 'city_id', 'billing_city_id')->withTrashed();

        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $userID, $pageLength)
    {
        switch ($sortKey)
        {
            default:
                $query = $this->orderBy($sortKey, $sortVal);
        }
        /*
         * To Show Parent Category Only
         */
        if ($userID && $userID == 'all')
        {
            $query->where('user_id', '!=', 0);
        }
        else
        {
            $query->where('user_id', $userID);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->orWhere('id', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('address_title', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('first_name', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('last_name', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('email', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('phone', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('is_default', 'LIKE', '%' . $searchText . '%');
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active')
                {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive')
                {
                    $query->orWhere('is_active', 0);
                }
                /*
                 * Search by Is Default :: Yes/No Keyword
                 */
                if (strtolower($searchText) == 'yes')
                {
                    $query->orWhere('is_default', 1);
                }
                if (strtolower($searchText) == 'no')
                {
                    $query->orWhere('is_default', 0);
                }
            });
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

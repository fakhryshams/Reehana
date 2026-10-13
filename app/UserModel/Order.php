<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{

    use SoftDeletes;

    protected $table   = 'orders';
    protected $guarded = ['created_at,updated_at,deleted_at'];

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
//        return Order::orderBy('updated_at', 'desc')->get();
        switch ($sortKey)
        {
            case 'user_name':
                $record = $this->sortByUsername($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD('user_id', $record)");
                break;
            case 'user_email':
                $record = $this->sortByUseremail($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD('user_id', $record)");
                break;
            case 'country_id':
                $record = $this->sortByUserCountry($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD('user_id', $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('order_total', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('order_status', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $searchText . '%')
                    ->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));

            /*
             * Search by User 
             */
            $record = $this->searchByUserFirstName($searchText);
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUserLastName($searchText);
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUserEmail($searchText);
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            /*
             * Search by country
             */
            $record = $this->searchByCountry($searchText);
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
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

    /*
     * Sorting Functions
     */

    public function sortByUsername($sortKey, $sortVal)
    {
        return \App\User::orderBy('first_name', $sortVal)->pluck('id')->toArray();
    }

    public function sortByUseremail($sortKey, $sortVal)
    {
        return \App\User::orderBy('email', $sortVal)->pluck('id')->toArray();
    }

    public function sortByUserCountry($sortKey, $sortVal)
    {
        $userCountry = \App\AdminModel\AdminCountryDescription::orderBy('country_name', $sortVal)->pluck('country_id')->toArray();

        if ($userCountry)
        {
            $userCountry = implode(",", $userCountry);

            $sortKey = 'country_id';
            $country = UserAddress::orderByRaw("FIELD($sortKey, $userCountry)")->pluck('user_id')->toArray();
            return $country;
        }
        return false;
    }

    /*
     * Searching function
     */

    //Search in User foreign table
    public static function searchByUserFirstName($search_text)
    {
        return \App\User::where('first_name', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public static function searchByUserLastName($search_text)
    {
        return \App\User::where('last_name', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public static function searchByCountry($search_text)
    {
        $country = \App\AdminModel\AdminCountryDescription::where('country_name', 'LIKE', '%' . $search_text . '%')->pluck('country_id');
        if ($country)
        {
            $userCountry = UserAddress::whereIn('country_id', $country)->pluck('user_id');
            return $userCountry;
        }
    }

    /*
     * search by country
     */

    public static function searchByUserEmail($search_text)
    {
        return \App\User::where('email', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    /*
     * get user 25-11-2020
     */



    /*
     * get user address 25-11-2020
     */

    public function getUserAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'shipping_address_id')->withTrashed()->first();;
    }

    public function getOrderDetail($getAll = false)
    {
        $query = $this->hasMany('App\UserModel\OrderDetail', 'order_id', 'id')->withTrashed();
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * this function is used in dashboard 06-12-2020
     */

    public static function getOrderData()
    {
        return Order::orderBy('updated_at', 'desc')->skip(0)->take(10)->get();
    }

    public function getShippingAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'shipping_address_id')->first();
    }

    public function getBillingAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'billing_address_id')->first();
    }

    public function getOrderStatus()
    {
        return $this->hasOne('App\AdminModel\Order\AdminOrderStatus', 'id', 'order_status')->first();
    }

}

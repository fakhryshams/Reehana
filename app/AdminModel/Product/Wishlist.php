<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{

    protected $table   = 'wishlist';
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

    public static function getUserWishlist($userID = '', $productID = "", $wishlistID = '', $getAll = false)
    {
        $query = Wishlist::where('is_active', 1);

        if ($wishlistID)
        {
            $query->where('id', $wishlistID);
        }
        if ($userID)
        {
            $query->where('user_id', $userID);
        }
        if ($productID)
        {
            $query->where('product_id', $productID);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProduct($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Product\AdminProduct', 'id', 'product_id');
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * get all data for index page
     */

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        $activeProductId = AdminProduct::pluck('id');

        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */

            case 'user_first_name':
                $record = $this->sortByName($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'user_last_name':
                $record = $this->sortByLastName($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'user_email':
                $record = $this->sortByEmail($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($activeProductId)
        {
            $query->whereIn('product_id', $activeProductId);
        }
        else
        {
            $query->where('product_id', NULL);
        }

        $query->groupBy('user_id');

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {

                $query->orWhereDate('updated_at', '=', date('Y-m-d', strtotime($searchText)));

                /*
                 * Search by User first name
                 */
                $record = $this->searchbByUserFirstName($searchText);
                if ($record)
                {
                    $query->orWhereIn('user_id', $record);
                }
                /*
                 * Search by User last name
                 */
                $record = $this->searchbByUserLastName($searchText);
                if ($record)
                {
                    $query->orWhereIn('user_id', $record);
                }
                /*
                 * Search by User email
                 */
                $record = $this->searchbByUserEmail($searchText);
                if ($record)
                {
                    $query->orWhereIn('user_id', $record);
                }
            });
        }

        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }


        return $query->paginate($paginate);
    }

    public function getWislistData($searchText = '', $sortKey = '', $sortVal = '', $userID, $pageLength, $requestProductFilter, $wishlistID = '')
    {

        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */
            case 'product_id':
                $record = $this->sortByProduct($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($wishlistID)
        {
            $query->where('user_id', $userID)->where('id', $wishlistID);
        }
        else
        {
            $query->where('user_id', $userID);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {

                $query->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
                /*
                 * Search by User
                 */
                $record = $this->searchByProduct($searchText);
                if ($record)
                {
                    $query->orWhereIn('product_id', $record);
                }
            });
        }

        $activeProductIds = AdminProduct::pluck('id')->toArray();
        if ($activeProductIds)
        {
            $query->whereIn('product_id', $activeProductIds);
        }

        if ($requestProductFilter)
        {
            $query->whereIn('product_id', $requestProductFilter);
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

    public function sortByName($sortKey, $sortVal)
    {
        return \App\User::orderBy('first_name', $sortVal)->pluck('id')->toArray();
    }

    public function sortByLastName($sortKey, $sortVal)
    {
        return \App\User::orderBy('last_name', $sortVal)->pluck('id')->toArray();
    }

    public function sortByEmail($sortKey, $sortVal)
    {
        return \App\User::orderBy('email', $sortVal)->pluck('id')->toArray();
    }

    public function sortByProduct($sortKey, $sortVal)
    {
        return AdminProductDescription::orderBy('products_name', $sortVal)->pluck('products_id')->toArray();
    }

    /*
     * /.Sorting Functions
     */



    /*
     * search By Product
     */

    public function searchByProduct($searchText)
    {
        $record = AdminProductDescription::where('products_name', 'LIKE', '%' . $searchText . '%')->pluck('products_id');
        return $record ? $record : false;
    }

    /*
     * search By User First Name
     */

    public function searchbByUserFirstName($searchText)
    {
        return \App\User::where('first_name', 'LIKE', '%' . $searchText . '%')->pluck('id');
    }

    /*
     * search By User Last Name
     */

    public function searchbByUserLastName($searchText)
    {
        return \App\User::where('last_name', 'LIKE', '%' . $searchText . '%')->pluck('id');
    }

    /*
     * search By User email
     */

    public function searchbByUserEmail($searchText)
    {
        return \App\User::where('email', 'LIKE', '%' . $searchText . '%')->pluck('id');
    }

    /*
     * get user data
     */

    public function getUserData()
    {
        $query = $this->hasOne('App\User', 'id', 'user_id')->first();
        return $query;
    }

    /*
     * get user data
     */

    public function getProductData()
    {
        $query = $this->hasOne('App\AdminModel\Product\AdminProduct', 'id', 'product_id')->withTrashed()->first();
        return $query;
    }

    public static function getWishListedProducts($id = "")
    {
        $productIds = Wishlist::whereNotNull('id');
        if ($id)
        {
            $productIds->whereUserId($id);
        }
        $productIds = $productIds->pluck('product_id');
        return AdminProduct::whereIn('id', $productIds)->get();
    }

    /*
     * ---------------------------------------------------------------------
     * CUSTOMER REPORT
     * ---------------------------------------------------------------------
     */

    public function getProductLikedReport($searchText, $sortKey, $sortVal, $pageLength)
    {
        /* Working Query
         * 
          $query = $this->groupBy('product_id')
          ->selectRaw('count(product_id) as no_of_product, product_id')
          ->orderBy('no_of_product', 'desc')
          ->orderBy('updated_at', 'desc')
          ->get();
         *
         * 
         */
        $query = $query = $this->groupBy('product_id')
                ->selectRaw('count(product_id) as no_of_product, product_id');

        switch ($sortKey)
        {
            case 'product':
                $record = $this->sortByProduct('products_name', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            default:
                $query->orderBy('no_of_product', $sortVal)->orderBy('updated_at', $sortVal);
        }
        if ($searchText)
        {
            /*
             * Search by User 
             */
            $record = $this->searchByProduct($searchText);
            if ($record)
            {
                $query->orWhereIn('product_id', $record);
            }
        }
        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

}

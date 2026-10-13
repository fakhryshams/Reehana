<?php

namespace App\AdminModel\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductDescription;
use App\UserModel\UserAddress;
use App\UserModel\PaymentMethod;
use App\UserModel\PaymentMethodDescription;
use App\UserModel\OrderDetail;
use App\AdminModel\Coupon\AdminCoupon;

class WebOrder extends Model
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

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status = '', $getPending = false)
    {
        switch ($sortKey)
        {
            case 'name':
                $record = $this->sortByUser('first_name', $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'email':
                $record = $this->sortByUser('email', $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'country':
                $record = $this->sortByUserCountry($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
                if ($status == 1)
                {
                    $pendingData = AdminOrderStatus::where('order_status', 'LIKE', '%' . 'Pending' . '%')->pluck('id')->first();
                    $query->where('order_status', $pendingData);
                }
        }

        if ($getPending)
        {
            $query->where('order_status', '=', config('custom_web.Pending'));
        }
        else
        {
            $query->where('order_status', '!=', config('custom_web.Pending'));
        }

        if (isset($filter['orderStatus']))
        {
            if (!is_array($filter['orderStatus']))
            {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }
            $query->whereIn('order_status', $filter['orderStatus']);
        }
        if (isset($filter['country']))
        {
            if (!is_array($filter['country']))
            {
                $filter['country'] = [$filter['country']];
            }
            $record = $this->filterByCountry($filter['country']);
            $query->whereIn('id', $record);
        }
        if (isset($filter['coupon']))
        {
            $query->where('coupon_id', $filter['coupon']);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->where('id', 'LIKE', '%' . $searchText . '%')
                        ->orWhere('order_total', 'LIKE', '%' . $searchText . '%')
                        ->orWhere('order_status', 'LIKE', '%' . $searchText . '%')
                        ->orWhere('payment_method', 'LIKE', '%' . $searchText . '%')
                        ->orWhere('user_agent_name', 'LIKE', '%' . $searchText . '%')
                        ->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));

                /*
                 * Search by User 
                 */
                $record = $this->searchByUser($searchText, 'first_name');
                if ($record)
                {
                    $query->orWhereIn('user_id', $record);
                }
                $record = $this->searchByUser($searchText, 'last_name');
                if ($record)
                {
                    $query->orWhereIn('user_id', $record);
                }
                $record = $this->searchByUser($searchText, 'email');
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
                /*
                 * Search by payment method
                 */
                $record = $this->searchByPaymentMethod($searchText);
                if ($record)
                {
                    $query->orWhereIn('payment_method', $record);
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

    /*
     * Sorting Functions
     */

    public function sortByUser($sortKey, $sortVal)
    {
        return \App\User::orderBy($sortKey, $sortVal)->pluck('id')->toArray();
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

    public static function filterByCountry($countryId)
    {
        $addressId = UserAddress::whereIn('country_id', $countryId)->pluck('id');
        if ($addressId)
        {
            return WebOrder::whereIn('shipping_address_id', $addressId)->pluck('id');
        }
        return false;
    }

    public static function searchByUser($search_text, $column)
    {
        return \App\User::where($column, 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public static function searchByOrderStatus($search_text)
    {
        return AdminOrderStatus::where('order_status', 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public static function searchByCountry($search_text)
    {
        $country = \App\AdminModel\AdminCountryDescription::where('country_name', 'LIKE', '%' . $search_text . '%')->pluck('country_id');
        if ($country)
        {
            $userCountry = UserAddress::whereIn('country_id', $country)->pluck('user_id');
            return $userCountry ? $userCountry : false;
        }
        return false;
    }

    public static function searchByPaymentMethod($search_text)
    {
        return PaymentMethodDescription::where('payment_method_name', 'LIKE', '%' . $search_text . '%')->pluck('payment_method_id');
    }

    /*
     * search by country
     */

    public static function getNewOrders($status)
    {
        return WebOrder::orderBy('id', 'desc')->where('order_status', $status)->skip(0)->take(10)->get();
    }

    public function getUser()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->withTrashed()->first();
    }

    public function getOrderStatus()
    {
        return $this->hasOne('App\AdminModel\Order\AdminOrderStatus', 'id', 'order_status')->withTrashed()->first();
    }

    public function getOrderProductDetail()
    {
        $productName = [];
        $query       = $this->hasMany('App\UserModel\OrderDetail', 'order_id', 'id');
        $order       = $query->get();
        foreach ($order as $order)
        {
            $product = AdminProduct::find($order->product_id);
            if ($product)
            {
                $product = $product->getProductDescription();
                if ($product)
                {
                    $productName[] = $product->products_name;
                }
            }
        }
        return $productName;
    }

    public function getUserAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'shipping_address_id')->withTrashed()->first();
    }

    public function getOrderDetail($getAll = false)
    {
        $query = $this->hasMany('App\UserModel\OrderDetail', 'order_id', 'id')->withTrashed();
        return $getAll ? $query->get() : $query->first();
    }

    public static function getOrderData()
    {
        return Order::orderBy('updated_at', 'desc')->skip(0)->take(10)->get();
    }

    public function getShippingAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'shipping_address_id')->withTrashed()->first();
    }

    public function getBillingAddress()
    {
        return $this->hasOne('App\UserModel\UserAddress', 'id', 'billing_address_id')->withTrashed()->first();
    }

    public function getPaymentMethodDetail($languageId = 1)
    {
        return $this->hasOne(PaymentMethodDescription::class, 'id', 'payment_method')->where('language_id', $languageId)->first();
    }

    /*
     * ---------------------------------------------------------------------
     * CUSTOMER REPORT
     * ---------------------------------------------------------------------
     */

    public function getCustomerReport($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        /* Working Query
         * 
          $query = WebOrder::groupBy('user_id')
          ->selectRaw('count(user_id) as no_of_order, user_id, sum(order_total) as sum_order_total')
          ->orderBy('no_of_order', 'desc')
          ->orderBy('sum_order_total', 'desc')
          ->get();
         *
         */
        $query = $this->selectRaw('count(user_id) as no_of_order, user_id, sum(order_total) as sum_order_total')->groupBy('user_id');

        if (isset($filter['orderStatus']))
        {
            if (!is_array($filter['orderStatus']))
            {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }
            $query->whereIn('order_status', $filter['orderStatus']);
        }
        switch ($sortKey)
        {
            case 'f_name':
                $record = $this->sortByUser('first_name', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'l_name':
                $record = $this->sortByUser('last_name', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'email':
                $record = $this->sortByUser('email', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'mobile':
                $record = $this->sortByUser('mobile', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'total_sale':
                $query->orderBy('sum_order_total', $sortVal);
                break;
            case 'total_order':
                $query->orderBy('no_of_order', $sortVal);
                break;
            case 'coupon_mail_sent_date':
                $record = $this->sortByUser('coupon_mail_sent_date', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(user_id, $record)");
                break;
            default:
                $query->orderBy('sum_order_total', $sortVal)->orderBy('no_of_order', $sortVal);
        }

        if ($searchText)
        {
            /*
             * Search by User 
             */
            $record = $this->searchByUser($searchText, 'first_name');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUser($searchText, 'last_name');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUser($searchText, 'email');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUser($searchText, 'mobile');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
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
        return $query = $query->paginate($paginate);
    }

    /*
     * ---------------------------------------------------------------------
     * Sales Report
     * ---------------------------------------------------------------------
     */

     public function getSalesReportDatewise($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        //        $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->whereYear('created_at', '2020')->groupByRaw('Month(created_at)')->orderBy('created_at', 'desc')->get();
        //        $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->whereYear('created_at', '2020')->groupByRaw('Year(created_at)')->orderBy('created_at', 'desc')->get();
        //        dd($query);
        /* Working Query
         * 
          WebOrder::selectRaw('created_at,sum(order_total) as order_total')->orderBy('created_at', 'desc')->groupByRaw('Date(created_at)')->get();
         *
         */
        $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->groupByRaw('Date(created_at)');
        switch ($sortKey) {
            default:
                $query->orderBy($sortKey, $sortVal);
        }

        if (isset($filter['type'])) {
            if ($filter['type'] == 3) {
                $query->where('user_id', config('config.store_admin_id'));
            }
            if ($filter['type'] == 2) {
                $query->where('user_id', '!=', config('config.store_admin_id'));
            }
        }

        if (isset($filter['orderStatus'])) {
            if (!is_array($filter['orderStatus'])) {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }
            $query->whereIn('order_status', $filter['orderStatus']);
        }
        if (isset($filter['fromDate'])) {
            $query->whereDate('created_at', '>=', date("Y-m-d", strtotime($filter['fromDate'])));
        }
        if (isset($filter['toDate'])) {
            $query->whereDate('created_at', '<=', date("Y-m-d", strtotime($filter['toDate'])));
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function getSalesReportMonthwise($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        /* Working Query
         * 
          $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->whereYear('created_at', '2020')->groupByRaw('Month(created_at)')->orderBy('created_at', 'desc')->get();
         *
         */
        $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->groupByRaw('Month(created_at)');
        switch ($sortKey) {
            default:
                $query->orderBy($sortKey, $sortVal);
        }

        if (isset($filter['type'])) {
            if ($filter['type'] == 3) {
                $query->where('user_id', config('config.store_admin_id'));
            }
            if ($filter['type'] == 2) {
                $query->where('user_id', '!=', config('config.store_admin_id'));
            }
        }
        if (isset($filter['orderStatus'])) {
            if (!is_array($filter['orderStatus'])) {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }
            $query->whereIn('order_status', $filter['orderStatus']);
        }
        if (isset($filter['fromDate'])) {
            
            $query->whereDate('created_at', '>=', date("Y-m-01", strtotime($filter['fromDate'])));
        }
        if (isset($filter['toDate'])) {
            $query->whereDate('created_at', '<=', date("Y-m-31", strtotime($filter['toDate'])));
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function sortByProduct($sortKey, $sortVal)
    {
        return AdminProductDescription::orderBy('products_name', $sortVal)->pluck('products_id')->toArray();
    }

    public function getTopSellingProducts($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        $orderIdFilter = "";

        if (isset($filter['orderStatus'])) {
            if (!is_array($filter['orderStatus'])) {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }
            $orderIdFilter = WebOrder::whereIn('order_status', $filter['orderStatus']);
            if ($filter['type']) {

                if ($filter['type'] == 3) {
                    $orderIdFilter->where('user_id', config('config.store_admin_id'));
                }
                if ($filter['type'] == 2) {
                    $orderIdFilter->where('user_id', '!=', config('config.store_admin_id'));
                }
            }

            $orderIdFilter = $orderIdFilter->pluck('id');
        }

        /* Working Query
         * 
          $query = OrderDetail::selectRaw('product_id,sum(quantity) as total_quantity,order_id')->groupBy('product_id')->orderBy('created_at', 'desc')->get();
         *
         */

        $nonDeletedProduct = AdminProduct::pluck('id')->toArray();

        $query = OrderDetail::selectRaw('product_id,sum(quantity) as total_quantity,order_id')->groupBy('product_id');
        $query->whereIn('product_id', $nonDeletedProduct);

        if ($orderIdFilter) {
            $query->whereIn('order_id', $orderIdFilter);
        }

        switch ($sortKey) {
            case 'product_id':
                $record = $this->sortByProduct('products_name', $sortVal);
                $record = implode(",", $record);
                $query->orderByRaw("FIELD(product_id, $record)");
                break;
            default:
                $query->orderBy('total_quantity', $sortVal);
        }
        if (isset($filter['fromDate'])) {
            $query->whereDate('created_at', '>=', date("Y-m-01", strtotime($filter['fromDate'])));
        }
        if (isset($filter['toDate'])) {
            $query->whereDate('created_at', '<=', date("Y-m-31", strtotime($filter['toDate'])));
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function getSalesReportYearwise($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        $query = WebOrder::selectRaw('created_at,sum(order_total) as order_total')->whereYear('created_at', $filter['year'])->whereMonth('created_at', $filter['month'])->groupByRaw('Month(created_at)');
        if (isset($filter['orderStatus'])) {
            if (!is_array($filter['orderStatus'])) {
                $filter['orderStatus'] = [$filter['orderStatus']];
            }

            if (isset($filter['type'])) {
                if ($filter['type'] == 3) {
                    $query->where('user_id', config('config.store_admin_id'));
                }
                if ($filter['type'] == 2) {
                    $query->where('user_id', '!=', config('config.store_admin_id'));
                }
            }
            $query->whereIn('order_status', $filter['orderStatus']);
        }
        return $query->first();
    }

    /*
     * code added by mayur 
     */

    public function getInCompleteOrder($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        switch ($sortKey)
        {
            case 'name':
                $record = $this->sortByUser('first_name', $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'email':
                $record = $this->sortByUser('email', $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            case 'country':
                $record = $this->sortByUserCountry($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(user_id, $record)");
                break;
            default:

                $pendingData = AdminOrderStatus::where('order_status', 'LIKE', '%' . 'Pending' . '%')->pluck('id');
                $query       = $this->whereIn('order_status', $pendingData)->orderBy($sortKey, $sortVal);
        }


        if (isset($filter['country']))
        {
            if (!is_array($filter['country']))
            {
                $filter['country'] = [$filter['country']];
            }
            $record = $this->filterByCountry($filter['country']);
            $query->whereIn('id', $record);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('order_total', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('payment_method', 'LIKE', '%' . $searchText . '%')
                    ->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));

            /*
             * Search by User 
             */
            $record = $this->searchByUser($searchText, 'first_name');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUser($searchText, 'last_name');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            $record = $this->searchByUser($searchText, 'email');
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            /*
             * Search by order status
             */
            $record = $this->searchByOrderStatus($searchText);
            if ($record)
            {
                $query->orWhereIn('order_status', $record);
            }
            /*
             * Search by country
             */
            $record = $this->searchByCountry($searchText);
            if ($record)
            {
                $query->orWhereIn('user_id', $record);
            }
            /*
             * Search by payment method
             */
            $record = $this->searchByPaymentMethod($searchText);
            if ($record)
            {
                $query->orWhereIn('payment_method', $record);
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

    public static function getIncompleteOrderStatus()
    {
        return AdminOrderStatus::where('order_status', 'LIKE', '%' . Pending . '%')->pluck('id');
    }

    /*
     * ---------------------------------------------------------------------
     * Coupon REPORT
     * ---------------------------------------------------------------------
     */

    public function getUsedCoupon($searchText, $sortKey, $sortVal, $pageLength, $filter)
    {
        /* Working Query
         * 
          $query = WebOrder::groupBy('user_id')
          ->selectRaw('count(user_id) as no_of_order, user_id, sum(order_total) as sum_order_total')
          ->orderBy('no_of_order', 'desc')
          ->orderBy('sum_order_total', 'desc')
          ->get();
         *
         */
        $query = $this->selectRaw('count(coupon_id) as no_of_order, user_id,coupon_id , sum(order_total) as sum_order_total')->groupBy('coupon_id');

        switch ($sortKey)
        {
            case 'total_order':
                $query->orderBy('no_of_order', $sortVal);
                break;
            default:
                $query->orderBy('sum_order_total', $sortVal)->orderBy('no_of_order', $sortVal);
        }
        
        $query->whereIn('order_status', [$filter['orderStatus']]);
        
        if ($searchText)
        {
            $record = $this->searchByCoupon($searchText, 'coupon_code');
            if ($record)
            {
                $query->orWhereIn('coupon_id', $record);
            }
        }
        else
        {
            $query->where('coupon_id', '!=', 0);
        }

        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? count($query->get()) : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }

        return $query = $query->paginate($paginate);
    }

    public static function searchByCoupon($search_text, $column)
    {
        return AdminCoupon::where($column, 'LIKE', '%' . $search_text . '%')->pluck('id');
    }

    public function getCouponDetails()
    {
        return $this->hasOne(AdminCoupon::class, 'id', 'coupon_id')->withTrashed()->first();
    }

}

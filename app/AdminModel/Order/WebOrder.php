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
use App\AdminModel\Master\WarehouseAddress;
use App\AdminModel\AdminCountry;

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
            if (auth()->check() && empty($data->created_by)) {
                $data->created_by = auth()->user()->id;
            }
        });
        static::updating(function ($data)
        {
            if (auth()->check()) {
                $data->updated_by = auth()->user()->id;
            }
        });
        static::saved(function ($model)
        {
            \App\UserModel\TemporaryOrder::syncStatusFromOrder($model);
        });
        static::deleting(function ($data)
        {
            if (auth()->check()) {
                $data->deleted_by = auth()->user()->id;
                $data->save();
            }
        });
    }

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status = '', $getPending = false, $isAdvancePayment = false, $includeFailedCanceled = false, $getOnlyQuotationPO = false)
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

        if(!$getOnlyQuotationPO) {
            if ($getPending)
            {
                if(!$includeFailedCanceled) {
                    $query->where('order_status', '=', config('custom_web.Pending'));
                }
            }
            else
            {
                $query->where('order_status', '!=', config('custom_web.Pending'));
            }
        } else {
            $query->where('is_from_quotation', 1);
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
        if (isset($filter['orderType']))
        {
            if (!is_array($filter['orderType']))
            {
                $filter['orderType'] = [$filter['orderType']];
            }
            $query->whereIn('order_type', $filter['orderType']);
        }
        if (isset($filter['customOrderType']))
        {
            if (!is_array($filter['customOrderType']))
            {
                $filter['customOrderType'] = [$filter['customOrderType']];
            }
            $query->whereIn('custom_order_type', $filter['customOrderType']);
        }
        
        if (isset($filter['show_customized_only']) && $filter['show_customized_only'] && !$getOnlyQuotationPO) {
            $query->whereNotNull('custom_order_type');
            if ($isAdvancePayment) {
                $query->where(function ($q) {
                    $q->where('is_advance_payment_done', 1)
                      ->orWhereIn('id', function ($sub) {
                          $sub->select('order_id')
                              ->from('payment_links')
                              ->where('status', 'paid');
                      });
                });
            } else {
                if ($includeFailedCanceled) {
                    $cancelStatus = config('custom_web.cancel');
                    $query->where(function ($q) use ($cancelStatus) {
                        $q->where('order_status', $cancelStatus)
                        ->orWhere(function ($sub) use ($cancelStatus) {
                            $sub->where('order_status', '!=', $cancelStatus)
                                ->where('is_advance_payment_done', 0)
                                ->whereNotIn('id', function ($sub2) {
                                    $sub2->select('order_id')
                                        ->from('payment_links')
                                        ->where('status', 'paid');
                                });
                        });
                    });
                } else {
                    $query->where('is_advance_payment_done', 0)
                          ->whereNotIn('id', function ($sub) {
                              $sub->select('order_id')
                                  ->from('payment_links')
                                  ->where('status', 'paid');
                          });
                }
            }
        }

        if (!$includeFailedCanceled) {
            $cancelStatus = config('custom_web.cancel');
            $query->where(function($q) use ($cancelStatus) {
                // Allow non-custom orders
                $q->whereNull('custom_order_type')
                // OR allow custom orders that are NOT canceled
                  ->orWhere(function($sub) use ($cancelStatus) {
                      $sub->whereNotNull('custom_order_type')
                          ->where('order_status', '!=', $cancelStatus);
                  });
            });
        }

        if (isset($filter['exclude_customized']) && $filter['exclude_customized'])
        {
            $query->whereNull('custom_order_type');
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

        // dd($query->toSql(), $query->getBindings());
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

    public function canRequestPickup()
    {
        return $this->carrier === 'FedEx'
            && $this->shipment_status === 'CREATED'
            && $this->order_type === 'regular'
            && $this->order_status == 1 && $this->pickup_status == NULL;
    }

    public function canUploadTradeDocuments()
    {
        $shippingAddress = $this->getShippingAddress();

        $shippingCountry = $shippingAddress->country_id ?? null;

        $warehouse = WarehouseAddress::where('is_active', 1)->first();
        $warehouseCountry = $warehouse->country_id ?? null;

        $isInternational = $shippingCountry && $warehouseCountry 
                            && $shippingCountry != $warehouseCountry;

        return $this->carrier === 'FedEx'
            && $this->shipment_status === 'CREATED'
            && $this->order_type === 'regular'
            && $this->order_status == 1
            && $this->pickup_status == NULL
            && empty($this->trade_document)
            && $isInternational;
    }

    public function getCustomeShippingAddress()
    {
        $order = $this->find($this->id);
        $countryName = '';
        if (isset($order['custom_billing_country_id']) && $order['custom_billing_country_id']) {
            $cn = \App\AdminModel\AdminCountryDescription::where('country_id', $order['custom_billing_country_id'])->first();
            if ($cn) $countryName = $cn->country_name;
        }

        return [
            'block' => $order['custom_billing_block'],
            'street' => $order['custom_billing_street'],
            'floor' => $order['custom_billing_floor'],
            'house_number' => $order['custom_billing_house_number'],
            'city' => $order['custom_billing_city'],
            'country' => $countryName,
            'address_detail' => $order['custom_billing_address_detail'],
            'address_url' => $order['custom_billing_address_url'],
        ];
    }

    /**
     * Get linked TemporaryOrder model if available
     *
     * @return \App\UserModel\TemporaryOrder|null
     */
    public function temporaryOrder()
    {
        $tempId = $this->getTemporaryOrderId();
        if ($tempId) {
            return \App\UserModel\TemporaryOrder::where('id', $tempId)
                ->where('user_id', $this->user_id)
                ->first();
        }
        return null;
    }

    /**
     * Extract temporary_order_id from custom_order_details JSON
     *
     * @return int|string|null
     */
    public function getTemporaryOrderId()
    {
        if (!empty($this->custom_order_details)) {
            $details = is_array($this->custom_order_details)
                ? $this->custom_order_details
                : json_decode($this->custom_order_details, true);

            if (isset($details['temporary_order_id']) && !empty($details['temporary_order_id'])) {
                return $details['temporary_order_id'];
            }
        }
        return null;
    }
}

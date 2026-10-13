<?php

namespace App\AdminModel\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminOrderStatus extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_orders_status';
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

    public function getAllData($searchText, $sortKey, $sortVal)
    {
        switch ($sortKey)
        {
            case 'order_status':
                $record = $this->sortByOrderStatus($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            /*
             * Search by Active/Inactive Keyword
             */
            if (strtolower($searchText) == 'yes')
            {
                $query->orWhere('is_default', 1);
            }
            if (strtolower($searchText) == 'no')
            {
                $query->orWhere('is_default', 0);
            }
            /*
             * Search by category
             */
            $record = $this->searchByOrderStatus($searchText);
            if ($record)
            {
                $query->orWhereIn('id', $record);
            }
        }
        return $query->paginate(config('custom.per_page', 10));
    }

    public function getParent($parentId)
    {
        return $this->find($parentId);
    }

    public function getOrderStatusDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Order\AdminOrderStatusDescription', 'orders_status_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * Sorting Functions
     */

    public function sortByOrderStatus($sortKey, $sortVal)
    {
        return AdminOrderStatusDescription::orderBy('orders_status_name', $sortVal)->pluck('orders_status_id')->toArray();
    }

    /*
     * End of Sorting Functions
     */

    /*
     * Search Functions
     */

    public function searchByOrderStatus($searchText)
    {
        $record = AdminOrderStatusDescription::where('orders_status_name', 'LIKE', '%' . $searchText . '%')->pluck('orders_status_id');
        return $record ? $record : false;
    }

    /*
     * End of Search Functions
     */
    /*
     * sorting
     */

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    public static function getOrderStatus()
    {
        return AdminOrderStatus::where(['show_in_list' => 1, 'is_active' => 1])->orderBy('sorting', 'asc')->get();
    }

    public static function getAllOrderStatus()
    {
        return AdminOrderStatus::where(['is_active' => 1])->orderBy('sorting', 'asc')->get();
    }

}

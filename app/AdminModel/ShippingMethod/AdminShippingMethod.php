<?php

namespace App\AdminModel\ShippingMethod;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AdminShippingMethod extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_shipping_methods';
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

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal)
    {
        switch ($sortKey)
        {
            case 'shipping_name':
                $record = $this->sortByShippingName($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
            $query->orWhereDate('updated_at', '=', date('Y-m-d', strtotime($searchText)));
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
             * Search by category
             */
            $record = $this->searchbByShippingName($searchText);
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

    public function getShippingMethodDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\ShippingMethod\AdminShippingMethodDescription', 'shipping_methods_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * Sorting Functions
     */

    public function sortByShippingName($sortKey, $sortVal)
    {
        return AdminShippingMethodDescription::whereIn('language_id', [config('custom.sortLanguageID', [1])])->orderBy('shipping_name', $sortVal)->pluck('shipping_methods_id')->toArray();
    }

    /*
     * End of Sorting Functions
     */
    /*
     * Search Functions
     */

    public function searchbByShippingName($searchText)
    {
        $record = AdminShippingMethodDescription::where('shipping_name', 'LIKE', '%' . $searchText . '%')->pluck('shipping_methods_id');
        return $record ? $record : false;
    }

    /*
     * End of Search Functions
     */
}

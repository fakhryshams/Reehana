<?php

namespace App\AdminModel\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUnit extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_units';
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

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        switch ($sortKey)
        {
            case 'unit_name':
                $record = $this->sortByUnit($sortKey, $sortVal);
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
            $record = $this->searchbByUnit($searchText);
            if ($record)
            {
                $query->orWhereIn('id', $record);
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

    public function sortByUnit($sortKey, $sortVal)
    {
        return AdminUnitDescription::whereIn('language_id', [config('custom.sortLanguageID', [1])])->orderBy('unit_name', $sortVal)->pluck('units_id')->toArray();
    }

    /*
     * End of Sorting Functions
     */

    public function getParent($parentId)
    {
        return $this->find($parentId);
    }

    public function getUnitDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Unit\AdminUnitDescription', 'units_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * Search Functions
     */

    public function searchbByUnit($searchText)
    {
        $record = AdminUnitDescription::where('unit_name', 'LIKE', '%' . $searchText . '%')->pluck('units_id');
        return $record ? $record : false;
    }

    /*
     * End of Search Functions
     */

    public static function getUnits($status = "")
    {
        $query = AdminUnit::where('id', '!=', null);
        if ($status)
        {
            $query->where('is_active', $status);
        }
        return $query->get();
    }

}

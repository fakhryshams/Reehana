<?php

namespace App\AdminModel\Brand;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminBrand extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_brand';
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

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */
            case 'brand_name':
                $record = $this->sortByTitle($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                /*
                 * Search by category
                 */
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $record = $this->searchbByTitle($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
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

    public function sortByTitle($sortKey, $sortVal)
    {
        return AdminBrandDescription::orderBy('brand_name', $sortVal)->where('language_id', config('custom.admin_default_language_id'))->pluck('brand_id')->toArray();
    }

    /*
     * search By Title
     */

    public function searchbByTitle($searchText)
    {
        return AdminBrandDescription::where('brand_name', 'LIKE', '%' . $searchText . '%')->pluck('brand_id');
    }

    public static function getData($status = "")
    {
        $query = AdminBrand::where('id', '!=', null)->orderBy('sorting', 'asc');
        if ($status)
        {
            $query->where('is_active', $status);
        }
        return $query->get();
    }

    public function getDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Brand\AdminBrandDescription', 'brand_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    public function getBrandDescription($languageId = "", $getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\Brand\AdminBrandDescription', 'brand_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

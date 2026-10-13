<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\City\AdminCity;

class AdminCountry extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_country';
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
            if (auth()->user())
            {
                $data->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($data)
        {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    /*
     * sorting
     */

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */
            case 'country_name':
                $record = $this->sortByTitle($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            case 'delivery_time':
                $record = $this->sortByDelivery($sortKey, $sortVal);
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
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('country_code', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('currency_code', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('currency_rate', 'LIKE', '%' . $searchText . '%');
                /*
                 * Search by category
                 */
                $record = $this->searchbByName($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
                $record = $this->searchbByDelivery($searchText);
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
        return AdminCountryDescription::orderBy('country_name', $sortVal)->pluck('country_id')->toArray();
    }

    public function sortByDelivery($sortKey, $sortVal)
    {
        return AdminCountryDescription::orderBy('delivery_time', $sortVal)->pluck('country_id')->toArray();
    }

    /*
     * search By Title
     */

    public function searchbByName($searchText)
    {
        return AdminCountryDescription::where('country_name', 'LIKE', '%' . $searchText . '%')->pluck('country_id');
    }

    public function searchbByDelivery($searchText)
    {
        return AdminCountryDescription::where('delivery_time', 'LIKE', '%' . $searchText . '%')->pluck('country_id');
    }

    public function getCountryDescription($languageId = "1", $getAll = false)
    {

        $query = $this->hasMany('App\AdminModel\AdminCountryDescription', 'country_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public static function getCountry($status = "")
    {
        $query = AdminCountry::where('id', '!=', null)->orderBy('sorting', 'asc');
        if ($status)
        {
            $query->where('is_active', $status);
        }
        return $query->get();
    }

    public static function getCountryData()
    {
        return AdminCountry::where('is_active', 1)->get();
    }

    public static function getCountryCity($countryID)
    {
        return AdminCity::where('country_id', $countryID)->where('is_active', 1)->orderBy('sorting', 'asc')->get();
    }

}

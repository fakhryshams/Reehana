<?php

namespace App\AdminModel\City;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\AdminCountry;
use App\AdminModel\AdminCountryDescription;

class AdminCity extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_city';
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
            case 'city_name':
                $record = $this->sortByTitle($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            case 'country_name':
                $record = $this->sortByCountry($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(country_id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');

                /*
                 * Search by category
                 */
                $record = $this->searchbByName($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
                $record = $this->searchbByCountry($searchText);
                if ($record)
                {
                    $query->orWhereIn('country_id', $record);
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

    public static function getCity($countryId, $status = "")
    {
        $query = AdminCity::orderBy('sorting', 'asc');
        if ($countryId)
        {
            $query->where('country_id', $countryId);
        }
        if ($status)
        {
            $query->where('is_active', $status);
        }
        return $query->get();
    }

    /*
     * Sorting Functions
     */

    public function sortByTitle($sortKey, $sortVal)
    {
        return AdminCityDescription::orderBy('city_name', $sortVal)->pluck('city_id')->toArray();
    }

    public function sortByCountry($sortKey, $sortVal)
    {
        return AdminCountryDescription::orderBy('country_name', $sortVal)->pluck('country_id')->toArray();
    }

    /*
     * search By Title
     */

    public function searchbByName($searchText)
    {
        return AdminCityDescription::where('city_name', 'LIKE', '%' . $searchText . '%')->pluck('city_id');
    }

    public function searchbByCountry($searchText)
    {
        return AdminCountryDescription::where('country_name', 'LIKE', '%' . $searchText . '%')->pluck('country_id');
    }

    public function getCityDescription($languageId = "1", $getAll = false, $isActive = "")
    {
        $query = $this->hasMany('App\AdminModel\City\AdminCityDescription', 'city_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        if ($isActive)
        {
            $query->where('is_active', $isActive);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getCountry()
    {
        return $this->hasOne(AdminCountry::class, 'id', 'country_id')->first();
    }

}

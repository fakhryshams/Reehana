<?php

namespace App\AdminModel\Zone;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminZone extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_zone';
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

    public function getZoneArea()
    {
        $areaID = $this->hasMany(AdminZoneArea::class, 'zone_id', 'id')->pluck('area_id');
        if ($areaID)
        {
            return \App\AdminModel\City\AdminCity::whereIn('id', $areaID)->get();
        }
        return [];
    }

}

<?php

namespace App\AdminModel\GeneralSetting\HomeSlider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\GeneralSetting\HomeSlider\AdminHomeSliderDescription;

class AdminHomeSlider extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_banner_management';
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

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */
            case 'banner_header':
                $record = $this->sortByTitle($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            case 'banner_header2':
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
                $query->orWhere('id', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');

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
                 * Search by title
                 */
                $record = $this->searchbByTitle($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
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
        return AdminHomeSliderDescription::orderBy($sortKey, $sortVal)->pluck('banner_management_id')->toArray();
    }

    /*
     * End of Sorting Functions
     */
    /*
     * search By Title
     */

    public function searchbByTitle($searchText)
    {
        return AdminHomeSliderDescription::where('banner_header', 'LIKE', '%' . $searchText . '%')->pluck('banner_management_id');
    }

    public function getSliderData()
    {
        $query = $this->hasOne(AdminHomeSliderDescription::class, 'banner_management_id', 'id')->first();
        return $query;
    }

    public static function getHomeSlider($status = "")
    {
        $query = AdminHomeSlider::where('id', '!=', null);
        if ($status)
        {
            $query->where('is_active', $status);
        }
        return $query->get();
    }

    public function getHomeSliderDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany(AdminHomeSliderDescription::class, 'banner_management_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public static function getHomeSliderData()
    {
        return AdminHomeSlider::where('is_active', 1)->orderBy('sorting', 'asc')->get();
    }

    public function getSliderDataByLanguage($languageId)
    {
        $query = $this->hasOne(AdminHomeSliderDescription::class, 'banner_management_id', 'id')->where('language_id', $languageId)->first();
        return $query;
    }

    /*
     * sorting
     */

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    public function getDefaultImage()
    {
        return $this->hasOne('App\AdminModel\Product\AdminProductImage', 'products_id', 'id')->where('is_default', 1)->first();
    }

}

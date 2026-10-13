<?php

namespace App\AdminModel\PhysicalStore;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\AdminModel\City\AdminCity;
use App\AdminModel\AdminCountry;

class PhysicalStore extends Model
{
    use SoftDeletes;

    protected $table   = 'physical_store';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data) {
            if(isset(auth()->user()->id)) {
                $data->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($data) {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query  = $this->orderBy($sortKey, $sortVal);

        if ($searchText) {
            $query->where(function ($query) use ($searchText) {
                $query->orWhere('id', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('store_code', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('store_name', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('address_line_1', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('address_line_2', 'LIKE', '%' . $searchText . '%');
                $query->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
                $query->orWhereDate('updated_at', '=', date('Y-m-d', strtotime($searchText)));
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active') {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive') {
                    $query->orWhere('is_active', 0);
                }
            });
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function areaDetail()
    {
        return $this->belongsTo(AdminCity::class, 'area', 'id');
    }

    public function countryDetail()
    {
        return $this->belongsTo(AdminCountry::class, 'country_id', 'id');
    }
}

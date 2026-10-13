<?php

namespace App\AdminModel\Monogram;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonogramPlacement extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_monogram_placement';
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
            default:
                $query = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                /*
                 * Search by category
                 */
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('placement_name', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('placement_size', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('placement_desc', 'LIKE', '%' . $searchText . '%');

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

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

}

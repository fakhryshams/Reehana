<?php

namespace App\AdminModel\Coupon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCoupon extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_coupon';
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
        $query = $this->orderBy($sortKey, $sortVal);

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->orWhere('coupon_code', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('coupon_description', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('coupon_discount', 'LIKE', '%' . $searchText . '%');
                $query->orWhereDate('from_date', '=', date('Y-m-d', strtotime($searchText)));
                $query->orWhereDate('to_date', '=', date('Y-m-d', strtotime($searchText)));

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

    public static function checkCoupon($coupon)
    {
        $date        = date('Y-m-d');
        $adminCoupon = AdminCoupon::where('coupon_code', $coupon)->whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)->where('is_active', 1)->first();
        if ($adminCoupon)
        {
            return $adminCoupon->toArray();
        }
        else
        {
            return false;
        }
    }

}

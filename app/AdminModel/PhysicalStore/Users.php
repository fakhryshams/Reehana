<?php

namespace App\AdminModel\PhysicalStore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\AdminModel\PhysicalStore\PhysicalStore;
use App\AdminModel\AdminCountry;
use App\User;

class Users extends Model
{
    use SoftDeletes;

    protected $table = 'physical_store_users';

    protected $guarded = ['created_at', 'updated_at', 'deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | Boot Functions (Audit Fields)
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            if (auth()->check()) {
                $data->created_by = auth()->id();
            }
        });

        static::updating(function ($data) {
            if (auth()->check()) {
                $data->updated_by = auth()->id();
            }
        });

        static::deleting(function ($data) {
            if (auth()->check()) {
                $data->deleted_by = auth()->id();
                $data->save();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Listing / Search / Pagination
    |--------------------------------------------------------------------------
    */
    public function getAllData($searchText, $sortKey, $sortVal, $pageLength, $filterStore)
    {
        // 🔐 Allowed sorting columns (important)
        $allowedSort = [
            'id',
            'first_name',
            'last_name',
            'username',
            'email',
            'mobile',
            'is_active',
            'created_at',
        ];

        if (!in_array($sortKey, $allowedSort)) {
            $sortKey = 'id';
        }

        $query = $this->orderBy($sortKey, $sortVal);

        if ($filterStore) {
            $query->whereIn('physical_store_id', $filterStore);
        }

        if ($searchText) {
            $query->where(function ($q) use ($searchText) {
                $q->orWhere('id', 'LIKE', "%$searchText%");
                $q->orWhere('first_name', 'LIKE', "%$searchText%");
                $q->orWhere('last_name', 'LIKE', "%$searchText%");
                $q->orWhere('username', 'LIKE', "%$searchText%");
                $q->orWhere('email', 'LIKE', "%$searchText%");
                $q->orWhere('mobile', 'LIKE', "%$searchText%");

                if (strtolower($searchText) === 'active') {
                    $q->orWhere('is_active', 1);
                }

                if (strtolower($searchText) === 'inactive') {
                    $q->orWhere('is_active', 0);
                }
            });
        }

        $paginate = $pageLength
            ? (strtolower($pageLength) === 'all' ? $query->count() : $pageLength)
            : config('custom.per_page', 10);

        return $query->paginate($paginate);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Physical Store
    public function physicalStore()
    {
        return $this->belongsTo(PhysicalStore::class, 'physical_store_id', 'id');
    }

    // Main users table
    public function userDetail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Country
    public function countryDetail()
    {
        return $this->belongsTo(AdminCountry::class, 'country', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Optional but Useful)
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}

<?php

namespace App\AdminModel\Product;

use Illuminate\Database\Eloquent\Model;

class AdminManufacturer extends Model
{

    protected $table   = 'admin_manufacturers';
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

    public function getAllData($searchText, $sortKey, $sortVal)
    {
        $query = AdminManufacturer::orderBy($sortKey, $sortVal);

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('manufacturer_name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('manufacturer_url', 'LIKE', '%' . $searchText . '%');
        }

        return $query->paginate(config('custom.per_page'));
    }

    public static function getData()
    {
        return AdminManufacturer::where('is_active', 1)->orderBy('manufacturer_name', 'asc')->get();
    }

    public static function checkSlug($currentSlug)
    {
        return AdminManufacturer::where('manufacturer_slug', $currentSlug)->get();
    }

    public static function updateSlug($categoriesID, $slug)
    {
        return AdminManufacturer::where('id', $categoriesID)->update([
                    'manufacturer_slug' => $slug
        ]);
    }

}

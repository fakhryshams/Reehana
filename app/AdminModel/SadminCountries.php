<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class SadminCountries extends Model
{

    protected $table = 'sadmin_countries';

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

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '')
    {
        $query = SadminCountries::orderBy($sortKey, $sortVal);

        if ($searchText)
        {
            
        }

        return $query->paginate(config('custom.per_page'));
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

}

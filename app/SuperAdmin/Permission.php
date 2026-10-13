<?php

namespace App\SuperAdmin;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as Permissions; //Extend existing model

class Permission extends Permissions
{

    public static function getAllData($searchText = "", $sortKey = "", $sortVal = "", $pageLength)
    {
        $query = Permission::orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('description', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('name', 'LIKE', '%' . $searchText . '%');
        }
        if ($sortKey && $sortVal)
        {
            $query->orderBy($sortKey, $sortVal);
        }

        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        $query = $query->paginate($paginate);
        return $query;
    }

}

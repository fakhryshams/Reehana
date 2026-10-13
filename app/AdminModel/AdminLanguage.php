<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminLanguage extends Model
{

    protected $table   = 'admin_languages';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query = AdminLanguage::orderBy($sortKey, $sortVal);

        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('name', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('code', 'LIKE', '%' . $searchText . '%');
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
     * Used in Helper
     */

    public static function getLanguageList()
    {
        return AdminLanguage::orderBy('id', 'asc')->get();
    }

}

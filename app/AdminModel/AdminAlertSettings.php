<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminAlertSettings extends Model
{

    protected $table = 'admin_alert_settings';

    public static function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query = AdminAlertSettings::where('can_edit', 1)->orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('setting_key', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('setting_val', 'LIKE', '%' . $searchText . '%');
            $query->orWhere('description', 'LIKE', '%' . $searchText . '%');
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

}

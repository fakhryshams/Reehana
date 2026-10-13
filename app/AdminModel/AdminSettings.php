<?php

namespace App\AdminModel;

use Illuminate\Database\Eloquent\Model;

class AdminSettings extends Model
{

    public static function getAllData($searchText, $sortKey, $sortVal, $pageLength)
    {
        $query = AdminSettings::where('can_edit', 1)->orderBy($sortKey, $sortVal);
        if ($searchText)
        {
            $query->where('id', 'LIKE', '%' . $searchText . '%');
            $record = AdminSettings::searchDescription($searchText);
            if ($record)
            {
                $query->orWhereIn('id', $record);
            }
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

    public static function searchDescription($searchText)
    {
        return AdminSettingDescription::where('setting_val', 'LIKE', '%' . $searchText . '%')->pluck('settings_id');
    }

    public function getDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\AdminSettingDescription', 'settings_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

}

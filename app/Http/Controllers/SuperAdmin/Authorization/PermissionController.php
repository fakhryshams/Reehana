<?php

namespace App\Http\Controllers\SuperAdmin\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SuperAdmin\Permission;
use Illuminate\Support\Facades\Lang;
use DB;
use Helper;

class PermissionController extends Controller
{

    private $langFile = 'admin';

    /*
     * List 
     */

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting = Helper::getOrderColumn($request);
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];

        $searchText = $request->q;
        $pageLength = $request->pageLength;

        $permissions = Permission::getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view("super-admin.permission.index", compact('permissions', 'searchText', 'sortKey', 'sortVal', 'pageLength'));
    }

}

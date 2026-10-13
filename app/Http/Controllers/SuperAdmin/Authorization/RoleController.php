<?php

namespace App\Http\Controllers\SuperAdmin\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\SuperAdmin\Role;
use DB;
use Helper;

class RoleController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.role';

    /*
     * List 
     */

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;

        $roles = Role::getAllData($searchText, $sortKey, $sortVal, true, $pageLength);

        return view("super-admin.role.index", compact('roles', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        return view("super-admin.role.add");
    }

    /*
     * Add 
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:roles,name,NULL,id,deleted_at,NULL|min:3|max:100',
                ], [
            'name.required' => Lang::get($this->langFile . '.error-role-req-name'),
            'name.unique'   => Lang::get($this->langFile . '.error-role-unique-name'),
            'name.min'      => Lang::get($this->langFile . '.error-role-min-name'),
            'name.max'      => Lang::get($this->langFile . '.error-role-max-name'),
        ]);

        DB::beginTransaction();

        try
        {
            $role       = new Role();
            $role->name = strtolower($request->name);

            if ($role->save())
            {
                DB::commit();
                $status       = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('super-admin.role.index')->with($status, $message_text);
    }

    /*
     * Edit  
     */

    public function edit(Role $role, $id)
    {
        //Get Single Record
        $record = $role->find($id);
        if ($record)
        {
            return view("super-admin.role.edit", compact('record'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
            return redirect()->route('super-admin.role.index')->with($status, $message_text);
        }
    }

    /*
     * Update 
     */

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'id'   => 'required',
            'name' => "required|unique:roles,name,{$id}',id,deleted_at,NULL|min:3|max:100",
                ], [
            'id.required'   => Lang::get($this->langFile . '.error'),
            'name.required' => Lang::get($this->langFile . '.error-role-req-name'),
            'name.unique'   => Lang::get($this->langFile . '.error-role-unique-name'),
            'name.min'      => Lang::get($this->langFile . '.error-role-min-name'),
            'name.max'      => Lang::get($this->langFile . '.error-role-max-name'),
        ]);

        $role = Role::find($id);

        if ($role && $request->id == $id)
        {
            DB::beginTransaction();

            try
            {
                $role->name = strtolower($request->name);

                if ($role->save())
                {
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        return redirect()->route('super-admin.role.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete(Role $role, $id)
    {
        if ($id)
        {
            $role = Role::find($id);

            if ($role)
            {
                DB::beginTransaction();

                try
                {
                    $role = Role::find($id)->delete();
                    /*
                     * Delete Dependecy as well
                     */

                    if ($role)
                    {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('super-admin.role.index')->with($status, $message_text);
    }

}

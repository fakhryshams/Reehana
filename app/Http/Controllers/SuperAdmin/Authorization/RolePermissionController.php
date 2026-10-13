<?php

namespace App\Http\Controllers\SuperAdmin\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SuperAdmin\MenuLevel1;
use App\SuperAdmin\MenuLevel2;
use App\SuperAdmin\MenuLevel3;
use App\SuperAdmin\Role;
use App\SuperAdmin\Permission;
use Illuminate\Support\Facades\Lang;
use DB;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{

    private $langFile = 'admin';

    public function index(Request $request)
    {
        $menu_level1 = new MenuLevel1();
        $menu_level1 = $menu_level1->getMenuLevel1();

        $roles = Role::getAllData("", "name", "asc", false);
        return view("super-admin.role-permission.index", compact('menu_level1', 'roles'));
    }

    public function saveRolePermission(Request $request)
    {
        $validatedData = $request->validate([
            'role_id'     => 'required',
            'permissions' => 'required',
                ], [
            'role_id.required'     => Lang::get($this->langFile . '.error-role-permission-req-role-id'),
            'permissions.required' => Lang::get($this->langFile . '.error-role-permission-req-permission')
        ]);

        $roles       = $request->role_id;
        $permissions = $request->permissions;

        $permission_array = [];

        foreach ($permissions as $permission)
        {
            $permission_array[] = Permission::findByName($permission);
        }
        try
        {
            DB::beginTransaction();

            foreach ($roles as $role)
            {
                $role = Role::find($role);
                $role->syncPermissions(); //Flush Permission
                $role->givePermissionTo($permission_array); //Assign Permission
            }
            DB::commit();

            $status       = 'success';
            $message_text = 'assign-success';
        }
        catch (Throwable $e)
        {
            DB::rollback();

            $status       = 'error';
            $message_text = 'error';
        }
        return redirect()->route('super-admin.role-permission.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

    /*
     * View Permission of Specific Role
     */

    public function viewPermission(Request $request, $id)
    {
        $role = Role::find($id);
        if ($role)
        {
            $permission = $role->permissions()->pluck('name');
            if (count($permission) > 0)
            {
                $menu_level1   = new MenuLevel1();
                $menu_level1   = $menu_level1->getMenuLevel1();
                $roles         = Role::getAllData("", "name", "asc", false);
                $selectedRolId = $id;
                return view("super-admin.role-permission.view", compact('menu_level1', 'roles', 'permission', 'selectedRolId'));
            }
            else
            {
                $status       = 'error';
                $message_text = 'permission_not_found';

                $message_text = Lang::get($this->langFile . '.' . $message_text);
                $message_text = str_replace("#role#", ucfirst($role->name), $message_text); //Replace message 
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record-not-found');
        }
        return redirect()->route('super-admin.role.index')->with($status, $message_text);
    }

    /*
     * Update Role Permission
     */

    public function updateRolePermission(Request $request)
    {
        $validatedData = $request->validate([
            'role_id'     => 'required',
            'permissions' => 'required',
                ], [
            'role_id.required'     => Lang::get($this->langFile . '.error-req-role-id'),
            'permissions.required' => Lang::get($this->langFile . '.error-req-permission')
        ]);

        $role = $request->role_id;

        $permissions = $request->permissions;

        $permission_array = [];
        foreach ($permissions as $permission)
        {
            $permission_array[] = Permission::findByName($permission);
        }
        try
        {
            DB::beginTransaction();

            $role = Role::find($role);
            $role->syncPermissions(); //Flush Permission
            $role->givePermissionTo($permission_array); //Assign Permission

            DB::commit();

            $status       = 'success';
            $message_text = 'assign-success';
        }
        catch (Throwable $e)
        {
            DB::rollback();

            $status       = 'error';
            $message_text = 'error';
        }
        return redirect()->route('super-admin.role-permission.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

}

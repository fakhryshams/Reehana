<?php

namespace App\Http\Controllers\SuperAdmin\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Admin as User;
use App\SuperAdmin\Role;
use Illuminate\Support\Facades\Lang;
use DB;
use App\Helpers\Helper;

class UserRoleController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.user';

    public function index(Request $request)
    {
        $sorting = Helper::getOrderColumn($request);
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];

        $user       = new User;
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $user       = $user->getUserDataForUserRoles($searchText, $sortKey, $sortVal, true, $pageLength);

        return view("super-admin.user-role.index", compact('user', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        $roleList = Role::getRoles();
        return view("super-admin.user-role.add", compact('roleList'));
    }

    public function store(Request $request)
    {
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        $validationRule = [
            'role_id'               => "required",
            'name'                  => "required",
            'username'              => "required|unique:admins,username,NULL,id,deleted_at,NULL",
            'email'                 => "required|unique:admins,email,NULL,id,deleted_at,NULL",
            'password'              => "required|confirmed|min:6",
            'password_confirmation' => "required",
        ];

        $validationMessage = [
            'name.required'                  => Lang::get($this->langFile . '.error-req-name'),
            'username.required'              => Lang::get($this->langFile . '.error-req-user-name'),
            'username.unique'                => Lang::get($this->langFile . '.error-unique-user-name'),
            'email.required'                 => Lang::get($this->langFile . '.error-req-email'),
            'email.unique'                   => Lang::get($this->langFile . '.error-unique-email'),
            'password.required'              => Lang::get($this->langFile . '.error-req-password'),
            'password.min'                   => Lang::get($this->langFile . '.error-req-password-min'),
            'password_confirmation.required' => Lang::get($this->langFile . '.error-req-confirmed-password'),
            'passwords.confirmed'            => Lang::get($this->langFile . '.error-req-confirmed'),
        ];
        /*
         * language wise validation
         */

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model           = new User();
            $password        = $request->password;
            $model->name     = $request->name;
            $model->username = $request->username;
            $model->email    = $request->email;
            $model->password = bcrypt($request->password);

            foreach ($request->role_id as $roleId)
            {
                /*
                 * assign role
                 */
                $role = \App\SuperAdmin\Role::find($roleId); //App Admin 
                $model->assignRole($role);
            }

            if ($model->save())
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

        return redirect()->route('super-admin.user-role.index')->with($status, $message_text);
    }

    public function bulkAssign()
    {
        $userList = User::getUserData();
        $roleList = Role::getRoles();
        return view("super-admin.user-role.bulk_assign", compact('userList', 'roleList'));
    }

    public function saveUserRole(Request $request)
    {
        $langFile      = $this->langFile;
        $validatedData = $request->validate([
            'role_id' => 'required',
            'user_id' => 'required',
                ], [
            'role_id.required' => Lang::get($this->langFile . '.error-role-user-req-role-id'),
            'user_id.required' => Lang::get($this->langFile . '.error-role-user-req-user-id')
        ]);
        DB::beginTransaction();

        $users = $request->user_id;
        $roles = $request->role_id;
        try
        {
            $flag = true;
            foreach ($users as $user)
            {
                $user = User::find($user);
                if ($user)
                {
                    foreach ($roles as $role)
                    {
                        $role = Role::find($role);
                        if ($role)
                        {
                            $user->assignRole($role->name); //Assign Role to User
                        }
                        else
                        {
                            $flag = false;
                        }
                    }
                }
                else
                {
                    $flag = false;
                }
            }
            if ($flag)
            {
                DB::commit();
                $status       = 'success';
                $message_text = 'assign_role_user_success';
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = 'error';
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();

            $status       = 'error';
            $message_text = 'error';
        }
        return redirect()->route('super-admin.user-role.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

    public function updateRole($id)
    {
        $user     = User::find($id);
        $langFile = $this->langFile;
        if ($user)
        {
            $userList = User::getUserDataForUserRoles();
            $roleList = Role::getRoles();
            return view("super-admin.user-role.update_role", compact('user', 'userList', 'roleList'));
        }
        else
        {
            return redirect()->route("super-admin.user-role.index")->with('error', Lang::get($langFile . '.record-not-found'));
        }
    }

    public function editUserRole(Request $request, $id)
    {
        $langFile      = $this->langFile;
        $validatedData = $request->validate([
            'role_id' => 'required',
                ], [
            'role_id.required' => Lang::get($this->langFile . '.error-role-user-req-role-id'),
        ]);
        $updateData    = User::find($id);
        $updateData    = $updateData->syncRoles($request->role_id);
        if ($updateData && $request->id == $id)
        {
            $updateData->fill($request->except('_token', 'user_id'));
            try
            {
                DB::beginTransaction();
                if ($updateData->save())
                {
                    $status       = 'success';
                    $message_text = '.role-updated-successfully';
                    DB::commit();
                }
                else
                {
                    $status       = 'error';
                    $message_text = '.error';
                    DB::rollback();
                }
            }
            catch (Throwable $e)
            {
                $status       = 'error';
                $message_text = '.error';
                DB::rollback();
            }
        }
        else
        {
            $message_text = 'record-not-found';
        }

        return redirect()->route('super-admin.user-role.index')->with('success', Lang::get($langFile . $message_text));
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = User::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $singleRecord->is_active = $active;
                if ($singleRecord->save())
                {
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    if ($active == 1)
                    {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.active') . '"'), $message_text);
                    }
                    else
                    {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.in_active') . '"'), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('super-admin.user-role.index', [http_build_query($_GET)])->with($status, $message_text);
        }
    }

}

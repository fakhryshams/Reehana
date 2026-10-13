<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

/*
|--------------------------------------------------------------------------
| Models
|--------------------------------------------------------------------------
*/
use App\AdminModel\PhysicalStore\PhysicalStore;
use App\AdminModel\PhysicalStore\Users;
use App\AdminModel\AdminCountry;
use App\AdminModel\AdminLanguage;
use App\User;

/*
|--------------------------------------------------------------------------
| Others
|--------------------------------------------------------------------------
*/
use DB;
use Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    private $langFile = 'admin';
    private $module   = '.user-management';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderSortingColumnById($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $filterStore = $request->_c;
        $store   = new Users();
        $users = $store->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filterStore);
        $stores   = PhysicalStore::where('is_active', 1)->get();

        return view('admin.user-management.users.index', compact('users', 'sortKey', 'sortVal', 'searchText', 'pageLength', 'stores', 'filterStore'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $stores   = PhysicalStore::where('is_active', 1)->get();

        return view(
            'admin.user-management.users.add',
            compact('stores')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Physical Store User
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
        $rules = [
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'mobile'            => 'required|digits_between:8,15',

            // 🔐 Password rules
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // matches password_confirmation
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/'
            ],
        ];

        $messages = [
            'first_name.required'        => __('admin.error-req-first-name'),
            'email.required'             => __('admin.error-req-email'),
            'email.unique'               => __('admin.error-email-exists'),
            'mobile.required'            => __('admin.error-req-mobile'),
            'password.required'   => __('admin.error-req-password'),
            'password.min'        => __('admin.error-req-password-min'),
            'password.confirmed'  => __('admin.error-req-confirmed'),
            'password.regex'      => __('admin.error-password-strong'),
        ];

        $request->validate($rules, $messages);

        $latestStoreId = PhysicalStore::orderBy('id', 'desc')->value('id');
        if (!$latestStoreId) {
            return redirect()
                ->back()
                ->with('error', __('admin.error-req-store'));
        }

        DB::beginTransaction();
        try {

            /*
        |--------------------------------------------------------------------------
        | STEP 1: Create entry in USERS table
        |--------------------------------------------------------------------------
        */
            $plainPassword = $request->password;
            $user = new User();

            $user->physical_store_id = $latestStoreId;
            $user->user_type = 'physical_store_user';
            $user->first_name = $request->first_name;
            $user->last_name  = $request->last_name;
            $user->email      = $request->email;
            $user->username   = $request->email;
            $user->password   = bcrypt($plainPassword); // auto password
            $user->login_type = 'website';
            $user->mobile     = $request->mobile;
            $user->is_active  = 1;

            $userSelectedLanguage = Helper::getLanguage();
            if ($userSelectedLanguage) {
                $user->locale = $userSelectedLanguage->id;
            }

            $user->created_by = auth()->id();
            $user->save();

            /*
        |--------------------------------------------------------------------------
        | STEP 2: Create entry in PHYSICAL_STORE_USERS table
        |--------------------------------------------------------------------------
        */
            $psUser = new Users();

            $psUser->physical_store_id = $latestStoreId;
            $psUser->user_id           = $user->id; // 🔥 LINKED HERE
            $psUser->first_name        = $request->first_name;
            $psUser->last_name         = $request->last_name;
            $psUser->username          = $request->email;
            $psUser->email             = $request->email;
            $psUser->mobile            = $request->mobile;
            $psUser->login_type        = 'website';
            $psUser->is_active         = 1;
            $psUser->created_by        = auth()->id();

            $psUser->save();

            DB::commit();

            if ($psUser) {
                /*
                 * Send Registration mail to User
                 */
                $language = Helper::getLanguage();
                $contactEmail = Helper::getSettingById(19);
                $admin_mail = $contactEmail ? $contactEmail->setting_val : "";
                $app_name = Helper::getAppName();

                $from['email'] = $admin_mail;
                $from['name'] = $app_name;
                $to['email'] = $psUser->email;
                $to['name'] = ucfirst($psUser->first_name) . ' ' . ucfirst($psUser->last_name);
                $subject = $app_name . "-" . __('admin.sales_login_title');
                $message = \View::make(
                    'admin.emails.register.index',
                    [
                        'user' => $user,
                        'plainPassword' => $plainPassword
                    ]
                )->render();


                $phpMail = new \App\Mail\CommonMailSender();

                $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);
            }

            $status = 'success';
            $message_text = Lang::get($this->langFile . '.add-success');
            $message_text = str_replace(
                '#module#',
                Lang::get($this->langFile . $this->module),
                $message_text
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            // optional log
            // \Log::error($e);

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
        }

        return redirect()
            ->route('admin.user-management.index')
            ->with($status, $message_text);
    }

    public function edit($id)
    {
        $user = Users::with('physicalStore')->find($id);
        $stores = PhysicalStore::where('is_active', 1)->get();

        if (!$user) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', __('admin.record_not_found'));
        }

        return view(
            'admin.user-management.users.edit',
            compact('user', 'stores')
        );
    }

    public function update(Request $request, $id)
    {
        $psUser = Users::find($id);

        if (!$psUser) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', __('admin.update-error'));
        }

        $rules = [
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'email'             => 'required|email|unique:users,email,' . $psUser->user_id . ',id,deleted_at,NULL',
            'mobile'            => 'required|digits_between:8,15',
        ];

        $messages = [
            'first_name.required'        => __('admin.error-req-first-name'),
            'last_name.required'         => __('admin.error-req-last-name'),
            'email.required'             => __('admin.error-req-email'),
            'email.unique'               => __('admin.error-email-exists'),
            'mobile.required'            => __('admin.error-req-mobile'),
        ];

        $request->validate($rules, $messages);

        $latestStoreId = PhysicalStore::orderBy('id', 'desc')->value('id');
        if (!$latestStoreId) {
            return redirect()
                ->back()
                ->with('error', __('admin.error-req-store'));
        }

        DB::beginTransaction();
        try {

            /*
        |--------------------------------------------------------------------------
        | Update USERS table
        |--------------------------------------------------------------------------
        */
            $user = User::find($psUser->user_id);

            if ($user) {
                $user->physical_store_id = $latestStoreId;
                $user->user_type = 'physical_store_user';
                $user->first_name = $request->first_name;
                $user->last_name  = $request->last_name;
                $user->email      = $request->email;
                $user->username   = $request->email;
                $user->mobile     = $request->mobile;
                $user->updated_by = auth()->id();
                $user->save();
            }

            /*
        |--------------------------------------------------------------------------
        | Update PHYSICAL_STORE_USERS table
        |--------------------------------------------------------------------------
        */
            $psUser->physical_store_id = $latestStoreId;
            $psUser->first_name        = $request->first_name;
            $psUser->last_name         = $request->last_name;
            $psUser->email             = $request->email;
            $psUser->username          = $request->email;
            $psUser->mobile            = $request->mobile;
            $psUser->updated_by        = auth()->id();
            $psUser->save();

            DB::commit();

            $status = 'success';
            $message_text = Lang::get($this->langFile . '.update-success');
            $message_text = str_replace(
                '#module#',
                Lang::get($this->langFile . $this->module),
                $message_text
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
        }

        return redirect()
            ->route('admin.user-management.index')
            ->with($status, $message_text);
    }

    public function delete($id)
    {
        if (!$id) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        $record = Users::find($id);

        if (!$record) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        DB::beginTransaction();
        try {

            /*
        |--------------------------------------------------------------------------
        | Soft delete physical_store_users
        |--------------------------------------------------------------------------
        */
            $record->deleted_by = auth()->id();
            $record->save();
            $record->delete();

            /*
        |--------------------------------------------------------------------------
        | Also deactivate linked USERS table (important)
        |--------------------------------------------------------------------------
        */
            if ($record->user_id) {
                $recordUser = User::find($record->user_id);
                if ($recordUser) {
                    $recordUser->deleted_by = auth()->id();
                    $recordUser->save();
                    $recordUser->delete();
                }
            }

            DB::commit();

            $status = 'success';
            $message_text = Lang::get($this->langFile . '.delete-success');
            $message_text = str_replace(
                "#module#",
                Lang::get($this->langFile . $this->module),
                $message_text
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.delete-error');
            $message_text = str_replace(
                "#module#",
                strtolower(Lang::get($this->langFile . $this->module)),
                $message_text
            );
        }

        return redirect()
            ->route('admin.user-management.index')
            ->with($status, $message_text);
    }

    public function changeStatus($id, $active)
    {
        if (!in_array($active, [0, 1])) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', Lang::get($this->langFile . '.invalid_status'));
        }

        $record = Users::find($id);

        if (!$record) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        if ((int) $active === 1) {
            $store = PhysicalStore::find($record->physical_store_id);
            if (!$store || (int) $store->is_active !== 1) {
                return redirect()
                    ->route('admin.user-management.index')
                    ->with('error', 'The related physical store is inactive.');
            }
        }

        DB::beginTransaction();
        try {

            /*
        |--------------------------------------------------------------------------
        | Update physical_store_users
        |--------------------------------------------------------------------------
        */
            $record->is_active = $active;
            $record->updated_by = auth()->id();
            $record->save();

            /*
        |--------------------------------------------------------------------------
        | Sync USERS table
        |--------------------------------------------------------------------------
        */
            if ($record->user_id) {
                User::where('id', $record->user_id)->update([
                    'is_active'  => $active,
                    'updated_by' => auth()->id(),
                ]);
            }

            DB::commit();

            $status = 'success';
            $message_text = Lang::get($this->langFile . '.status-change-success');
            $message_text = str_replace(
                "#module#",
                Lang::get($this->langFile . $this->module),
                $message_text
            );

            $message_text = str_replace(
                "#status#",
                '"' . ($active
                    ? Lang::get($this->langFile . '.active')
                    : Lang::get($this->langFile . '.in_active')) . '"',
                $message_text
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.status-change-error');
            $message_text = str_replace(
                "#module#",
                strtolower(Lang::get($this->langFile . $this->module)),
                $message_text
            );
        }

        return redirect()
            ->route('admin.user-management.index')
            ->with($status, $message_text);
    }

    public function change_password($id)
    {
        $user = Users::with('physicalStore')->find($id);

        if (!$user) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', __('admin.record_not_found'));
        }

        return view(
            'admin.user-management.users.change-password',
            compact('user')
        );
    }

    public function update_password(Request $request, $id)
    {
        $psUser = Users::find($id);

        if (!$psUser) {
            return redirect()
                ->route('admin.user-management.index')
                ->with('error', __('admin.update-error'));
        }

        $rules = [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // matches password_confirmation
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/'
            ],
        ];

        $messages = [
            'password.required'   => __('admin.error-req-password'),
            'password.min'        => __('admin.error-req-password-min'),
            'password.confirmed'  => __('admin.error-req-confirmed'),
            'password.regex'      => __('admin.error-password-strong'),
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {

            /*
        |--------------------------------------------------------------------------
        | Update USERS table
        |--------------------------------------------------------------------------
        */
            $user = User::find($psUser->user_id);
            $plainPassword = $request->password;
            
            if ($user) {
                $user->password = bcrypt($plainPassword);
                $user->updated_by = auth()->id();
                $user->save();
            }

            DB::commit();

            $status = 'success';
            $message_text = Lang::get($this->langFile . '.password-update-success');
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
        }

        return redirect()
            ->route('admin.user-management.index')
            ->with($status, $message_text);
    }
}

<?php

namespace App\Http\Controllers\Admin\Customer;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
//Export Excel
use App\Exports\CustomerExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.customer';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($request, 'created_at', 'desc');
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $customer   = new User();
        $customer   = $customer->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.customer.index', compact('customer', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        $isActive = config('admin.product.isActive');
        return view("admin.customer.add", compact('isActive'));
    }

    public function store(Request $request)
    {
        /*
         * Validations
         */
        $validationRule = [
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:users,email,NULL,id,deleted_at,NULL|min:6',
            'password'   => 'required|min:6',
            'mobile'     => 'required|numeric',
            'is_active'  => 'required',
        ];

        $validationMessage = [
            'email.required'      => Lang::get($this->langFile . '.error-customer-req-email'),
            'email.min'           => Lang::get($this->langFile . '.error-customer-req-email-min'),
            'email.email'         => Lang::get($this->langFile . '.error-customer-email-email'),
            'email.unique'        => Lang::get($this->langFile . '.error-customer-req-email-unique'),
            'password.required'   => Lang::get($this->langFile . '.error-customer-req-password'),
            'password.min'        => Lang::get($this->langFile . '.error-customer-req-password-min'),
            'first_name.required' => Lang::get($this->langFile . '.error-customer-req-first-name'),
            'last_name.required'  => Lang::get($this->langFile . '.error-customer-req-last-name'),
            'mobile.required'     => Lang::get($this->langFile . '.error-customer-req-mobile'),
            'mobile.numeric'      => Lang::get($this->langFile . '.error-customer-numeric-mobile'),
            'is_active.required'  => Lang::get($this->langFile . '.error-customer-is-active'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);

        /*
         * All validation Passed
         */

        DB::beginTransaction();

        try
        {
            $user             = new User();
            $user->first_name = $request->first_name;
            $user->last_name  = $request->last_name;
            $user->email      = $request->email;
            $user->password   = bcrypt($request->password);
            $user->username   = $request->email;
            $user->mobile     = $request->mobile;
            $user->login_type = 'website';
            $user->is_active  = $request->is_active;
            if ($user->save())
            {
                $user->assignRole('user');
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

        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $singleRecord = User::find($id);
        if ($singleRecord)
        {
            $isActive = config('admin.product.isActive');
            return view("admin.customer.edit", compact('singleRecord', 'isActive'));
        }
        return redirect()->route('admin.customer.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        $languageList = Helper::getLanguageList();
        $model        = User::find($id);
        if ($model)
        {
            $validationRule    = [
                'first_name' => 'required',
                'last_name'  => 'required',
                'is_active'  => 'required',
            ];
            $validationMessage = [
                'first_name.required' => Lang::get($this->langFile . '.error-customer-req-first-name'),
                'last_name.required'  => Lang::get($this->langFile . '.error-customer-req-last-name'),
                'is_active.required'  => Lang::get($this->langFile . '.error-customer-is-active'),
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                $model->first_name = $request->first_name;
                $model->last_name  = $request->last_name;
                $model->is_active  = $request->is_active;

                if ($model->save())
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

        return redirect()->route('admin.customer.index')->with($status, $message_text);
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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = User::find($id);
            if ($record)
            {
                $userId = $record->id;
                DB::beginTransaction();
                try
                {
                    if ($record->delete())
                    {
                        \App\AdminModel\Product\Wishlist::where('user_id', $userId)->delete();
                        
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
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    public function exportCustomerData()
    {
        return Excel::download(new CustomerExport(), config('excel.customer_data'));
    }

}

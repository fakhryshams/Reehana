<?php

namespace App\Http\Controllers\Admin\Master\Coupon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\Coupon\AdminCoupon;

class CouponController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.coupon';

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

        $coupon = new AdminCoupon();
        $coupon = $coupon->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.master.coupon.index', compact('coupon', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        $isActive = config('admin.product.isActive');
        return view("admin.master.coupon.add", compact('isActive'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'coupon_code'     => 'required|unique:admin_coupon,coupon_code,NULL,id,deleted_at,NULL',
            'coupon_discount' => 'required',
            'from_date'       => 'required',
            'to_date'         => 'required',
                ], [
            'coupon_code.required'     => Lang::get($this->langFile . '.error-coupon-req-code'),
            'coupon_code.unique'       => Lang::get($this->langFile . '.error-coupon-unique-code'),
            'coupon_discount.required' => Lang::get($this->langFile . '.error-coupon-req-coupon_discount'),
            'from_date.required'       => Lang::get($this->langFile . '.error-coupon-req-start-date'),
            'to_date.required'         => Lang::get($this->langFile . '.error-coupon-req-end-date'),
        ]);
        DB::beginTransaction();

        try
        {
            $model                     = new AdminCoupon();
            $model->coupon_code        = $request->coupon_code;
            $model->coupon_discount    = $request->coupon_discount;
            $model->coupon_description = $request->coupon_description;
            $model->from_date          = date("Y-m-d", strtotime($request->from_date));
            $model->to_date            = date("Y-m-d", strtotime($request->to_date));
            $model->allow_multiple     = isset($request->allow_multiple) ? true : false;
            $model->allow_all          = isset($request->allow_all) ? true : false;
            $model->is_active          = $request->is_active;

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

        return redirect()->route('admin.master.coupon.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminCoupon::find($id);
        if ($singleRecord)
        {
            $isActive = config('admin.product.isActive');
            return view("admin.master.coupon.edit", compact('id', 'singleRecord', 'isActive'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . 'record_not_found');
        }
        return redirect()->route('admin.master.coupon.index')->with($status, $message_text);
    }

    public function update(Request $request, $id)
    {
        $model = AdminCoupon::find($id);
        if ($model)
        {
            $validatedData = $request->validate([
                'coupon_code'     => "required|unique:admin_coupon,coupon_code,$id,id,deleted_at,NULL",
                'coupon_discount' => 'required',
                'from_date'       => 'required',
                'to_date'         => 'required',
                    ], [
                'coupon_code.required'     => Lang::get($this->langFile . '.error-coupon-req-code'),
                'coupon_code.unique'       => Lang::get($this->langFile . '.error-coupon-unique-code'),
                'coupon_discount.required' => Lang::get($this->langFile . '.error-coupon-req-coupon_discount'),
                'from_date.required'       => Lang::get($this->langFile . '.error-coupon-req-start-date'),
                'to_date.required'         => Lang::get($this->langFile . '.error-coupon-req-end-date'),
            ]);
            DB::beginTransaction();

            try
            {
                $model->coupon_code        = $request->coupon_code;
                $model->coupon_discount    = $request->coupon_discount;
                $model->coupon_description = $request->coupon_description;
                $model->from_date          = date("Y-m-d", strtotime($request->from_date));
                $model->to_date            = date("Y-m-d", strtotime($request->to_date));
                $model->allow_multiple     = isset($request->allow_multiple) ? true : false;
                $model->allow_all          = isset($request->allow_all) ? true : false;
                $model->is_active          = $request->is_active;

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
        return redirect()->route('admin.master.coupon.index')->with($status, $message_text);
    }

    public function delete($id)
    {
        if ($id)
        {
            $record = AdminCoupon::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    if ($record->delete())
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

        return redirect()->route('admin.master.coupon.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminCoupon::find($id);
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

            return redirect()->route('admin.master.coupon.index')->with($status, $message_text);
        }
    }

    public function sendMail(Request $request, $customerId)
    {
        $user         = \App\User::find($customerId);
        $redirectPage = isset($request->page) ? $request->page : 'report';
        if ($redirectPage == 'customer')
        {
            $redirectPageLink = 'admin.customer.index';
        }
        else
        {
            $redirectPageLink = 'admin.report.sales-customer';
        }

        if ($user)
        {
            $couponDesc = Helper::getSettingById(31);
            if ($couponDesc)
            {
                $couponDesc = $couponDesc->setting_val;
            }
            else
            {
                $couponDesc = "";
            }
            $coupon = AdminCoupon::where(['is_active' => 1])->whereDate('to_date', '>=', date("Y-m-d"))->orderBy('coupon_code', 'asc')->get();
            return view("admin.master.coupon.send_email", compact('coupon', 'couponDesc', 'user', 'redirectPage'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route($redirectPageLink)->with($status, $message_text);
    }

    public function sendCoupon(Request $request)
    {
        $validatedData = $request->validate([
            'user_id'     => 'required',
            'username'    => 'required',
            'coupon_id'   => 'required',
            'coupon_desc' => 'required',
                ], [
            'user_id.required'     => Lang::get($this->langFile . '.error-coupon-mail-req-user_id'),
            'username.required'    => Lang::get($this->langFile . '.error-coupon-mail-req-usernmae'),
            'coupon_id.required'   => Lang::get($this->langFile . '.error-coupon-req-mail-coupon_id'),
            'coupon_desc.required' => Lang::get($this->langFile . '.error-coupon-req-mail-coupon_desc'),
        ]);

        /*
         * Have to send mail to Related User and Insert entry in admin_coupon_code
         */
        $couponDetail = AdminCoupon::find($request->coupon_id);

        $redirectPage = isset($request->page) ? $request->page : 'report';
        if ($redirectPage == 'customer')
        {
            $redirectPageLink = 'admin.customer.index';
        }
        else
        {
            $redirectPageLink = 'admin.report.sales-customer';
        }

        if ($couponDetail)
        {
            /*
             * Check if same coupon and details are exist or not
             */
            DB::beginTransaction();

            try
            {
                $model = \App\UserModel\CouponCode::where([
                            'user_id'    => $request->user_id,
                            'coupon_id'  => $request->coupon_id,
                            'is_applied' => 0,
                            'is_active'  => 1
                        ])->first();

                if (!$model)
                {
                    $model = new \App\UserModel\CouponCode();
                }

                $model->user_id            = $request->user_id;
                $model->email              = $request->username;
                $model->coupon_id          = $request->coupon_id;
                $model->coupon_code        = $couponDetail->coupon_code;
                $model->coupon_discount    = $couponDetail->coupon_discount;
                $model->coupon_discount_in = $couponDetail->coupon_discount_in;
                $model->coupon_description = $request->coupon_desc;
                $model->from_date          = $couponDetail->from_date;
                $model->to_date            = $couponDetail->to_date;
                $model->allow_multiple     = $couponDetail->allow_multiple;
                $model->allow_all          = $couponDetail->allow_all;
                $model->save();

                if ($model->save())
                {
                    DB::commit();
                    $user = \App\User::find($request->user_id);
                    if ($user)
                    {
                        /*
                         * Send Mail
                         */
                        $language     = Helper::getLanguage();
                        $contactEmail = Helper::getSettingById(19);
                        $adminMail    = $contactEmail ? $contactEmail->setting_val : "";
                        $appName      = Helper::getAppName();

                        $from['email'] = $adminMail;
                        $from['name']  = $appName;
                        $to['email']   = $user->email;
                        $to['name']    = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
                        $subject       = $appName . " - " . __('web.coupon_code_subject');

                        $couponDescription = $request->coupon_desc;

                        $message      = \View::make('admin.master.coupon.mail', compact('user', 'couponDescription'))->render();
                        $phpMail      = new \App\Mail\CommonMailSender();
                        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);
                        /*
                         * End :: Send Mail
                         */

                        $user->coupon_mail_sent_date = date("Y-m-d H:i:s");
                        $user->save();
                    }
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.coupon-mail-sent-success');
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.error');
            }
        }
        return redirect()->route($redirectPageLink)->with($status, $message_text);
    }

}

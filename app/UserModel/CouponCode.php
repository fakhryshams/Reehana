<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Coupon\AdminCoupon;

class CouponCode extends Model
{

    use SoftDeletes;

    protected $table = 'admin_coupon_code';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {

        parent::boot();
        if (auth()->user())
        {

            static::creating(function ($data)
            {
                $data->created_by = auth()->user()->id;
            });
            static::updating(function ($data)
            {
                $data->updated_by = auth()->user()->id;
            });
            static::deleting(function ($data)
            {
                $data->deleted_by = auth()->user()->id;
                $data->save();
            });
        }
    }

    public function validateCouponCode($code, $user)
    {
        $response = false;
        $checkCoupon = AdminCoupon::where(['coupon_code' => $code, 'is_active' => 1])->whereDate('to_date', '>=', date("Y-m-d"))->first();
        if ($checkCoupon)
        {
            if ($user)
            {
                $exist = $this->where(['coupon_code' => $code, 'user_id' => $user->id, 'email' => $user->email, 'is_active' => 1])->first();
                if ($exist)
                {
                    /*
                     * If entry exist in admin_coupon_code then update it with existing coupon code... if there is any change in coupon code by admin 
                     */

                    $stillExist = $this->where(['coupon_code' => $code, 'user_id' => $user->id, 'email' => $user->email, 'is_active' => 1, 'is_applied' => 0])->first();
                    if ($stillExist)
                    {
                        $model = $stillExist;
                    }
                    else
                    {
                        $model = $exist;
                    }

                    if ($model->allow_multiple == 1)
                    {
                        /*
                         * If admin changed coupon to allow_multiple then update is_applied=0
                         */
                        $model->is_applied = 0;
                    }
                    $model->coupon_discount = $checkCoupon->coupon_discount;
                    $model->coupon_discount_in = $checkCoupon->coupon_discount_in;
                    //$model->coupon_description = "";
                    $model->from_date = $checkCoupon->from_date;
                    $model->to_date = $checkCoupon->to_date;
                    $model->allow_multiple = $checkCoupon->allow_multiple;
                    $model->allow_all = $checkCoupon->allow_all;
                    $model->save();

                    $response = $model;
                }
                else
                {
                    if ($checkCoupon->allow_all)
                    {
                        /*
                         * If allow all=1 and entry missing then and then insert in admin_coupon_code
                         */
                        $model = new CouponCode();
                        $model->user_id = $user->id;
                        $model->email = $user->email;
                        $model->coupon_id = $checkCoupon->id;
                        $model->coupon_code = $checkCoupon->coupon_code;
                        $model->coupon_discount = $checkCoupon->coupon_discount;
                        $model->coupon_discount_in = $checkCoupon->coupon_discount_in;
                        $model->coupon_description = "Allow All";
                        $model->from_date = $checkCoupon->from_date;
                        $model->to_date = $checkCoupon->to_date;
                        $model->allow_multiple = $checkCoupon->allow_multiple;
                        $model->allow_all = $checkCoupon->allow_all;
                        $model->is_applied = 0;
                        $model->is_active = 1;
                        $model->save();

                        $response = $model;
                    }
                }
            }
            else
            {

                if ($checkCoupon->allow_all)
                {
                    /*
                     * If allow all=1 and entry missing then and then insert in admin_coupon_code
                     */
                    $model = new CouponCode();
                    // $model->user_id = $user->id;
                    // $model->email = $user->email;
                    $model->coupon_id = $checkCoupon->id;
                    $model->coupon_code = $checkCoupon->coupon_code;
                    $model->coupon_discount = $checkCoupon->coupon_discount;
                    $model->coupon_discount_in = $checkCoupon->coupon_discount_in;
                    $model->coupon_description = "Allow All";
                    $model->from_date = $checkCoupon->from_date;
                    $model->to_date = $checkCoupon->to_date;
                    $model->allow_multiple = $checkCoupon->allow_multiple;
                    $model->allow_all = $checkCoupon->allow_all;
                    $model->is_applied = 0;
                    $model->is_active = 1;
                    $model->save();

                    $response = $model;
                }
            }
        }

        return $response;
    }

}

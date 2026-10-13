<?php

namespace App\Http\Controllers\Admin\Order;

use App\AdminModel\AdminCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use App\User;
use App\AdminModel\AdminLanguage;
use App\AdminModel\Order\WebOrder;
use App\UserModel\OrderDetail;
use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\Order\AdminOrderStatusHistory;
use App\AdminModel\AdminCountry;
use App\Exports\OrderNotCompleteExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderDetailExport;
use App\AdminModel\CourierCompany\CourierCompany;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductAttribute;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;



class OrderController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.order';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $filter['coupon']      = $request->coupon;
        $orders                = new WebOrder();
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();
        if ($currency)
        {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::getOrderStatus();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        return view('admin.order.order.index', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country'));
    }

     public function vieworder($id)
    {
        $orderDetail = WebOrder::find($id);
        
        if ($orderDetail)
        {
            $tax                = \App\Helpers\Helper::getTax();
            $orderStatus        = AdminOrderStatus::getOrderStatus();
            $allOrderStatus     = AdminOrderStatus::getAllOrderStatus();
            $orderProductDetail = OrderDetail::where('order_id', $id)->get();
            $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
            $currency           = Helper::getDefaultCurrency();
            $badgeColor         = config('order.badge');
            $courier            = CourierCompany::getCourierCompany();

            $shippingAddress = $orderDetail->getShippingAddress();

    
            $addressQr = "";

            if ($shippingAddress->address_url || $orderDetail->address_url)
            {
                $writer = new PngWriter();

                // Create QR code
                
                $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                        ->setEncoding(new Encoding('UTF-8'))
                     //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                        ->setSize(300)
                        ->setMargin(10)
                    //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                        ->setForegroundColor(new Color(0, 0, 0))
                        ->setBackgroundColor(new Color(255, 255, 255));

                // Create generic logo

                $logo = Logo::create(public_path() . '/logo.png')
                        ->setResizeToWidth(100);

                // Create generic label
                $label = Label::create('Shipping Address')
                        ->setTextColor(new Color(255, 0, 0));

                $result = $writer->write($qrCode, $logo, $label);

                //header('Content-Type: ' . $result->getMimeType());
                $addressQr = $result->getDataUri();
            }
            


            return view('admin.order.order.detail', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'allOrderStatus', 'courier', 'shippingAddress', 'addressQr'));
        }

        $status       = 'error';
        $message_text = Lang::get($this->langFile . '.record_not_found');

        return redirect()->route('admin.order.index')->with($status, $message_text);
    }

    public function saveNote(Request $request)
    {
        if ($request->order_note && $request->order_id)
        {
            $orderDetail = WebOrder::find($request->order_id);

            $orderDetail->order_note = $request->order_note;
            if ($orderDetail->save())
            {
                return redirect()->back()->with('success', 'Note updated successfully');
            }
            else
            {
                return redirect()->back()->with('error', 'Somthing went wrong');
            }
        }
    }
    
    public function updateAddress(Request $request)
    {
    
        if ($request->order_id)
        {
            $orderDetail = WebOrder::find($request->order_id);

            $orderDetail->address_url = $request->address_url;
            if ($orderDetail->save())
            {
                return redirect()->back()->with('success', 'Address URL updated successfully');
            }
            else
            {
                return redirect()->back()->with('error', 'Somthing went wrong');
            }
        }

    }

    public function updateOrderStatus(Request $request)
    {
        $validatedData = $request->validate([
            'orders_status' => 'required',
                ], [
            'orders_status.required' => Lang::get($this->langFile . '.error-select-orders_status'),
        ]);

        DB::beginTransaction();
        try
        {
            $orderDetail = WebOrder::find($request->orders_id);

            if ($orderDetail)
            {
                $isPartialCancelReturn = false;

                $cancelReturn = isset($request->cancel_return) ? $request->cancel_return : false;

                $lastUpdatedStatus = AdminOrderStatusHistory::where('order_id', $orderDetail->id)->orderBy('id', 'desc')->first();

                if ($cancelReturn)
                {
                    $orderDetail->cancel_return_flag = 1;
                    $orderProductCount               = OrderDetail::where(['order_id' => $orderDetail->id, 'is_active' => 1])->count();
                    if (count($cancelReturn) == $orderProductCount)
                    {
                        $orderDetail->order_status = $request->orders_status;

                        $orderStatusHistory                  = new AdminOrderStatusHistory();
                        $orderStatusHistory->order_id        = $orderDetail->id;
                        $orderStatusHistory->order_status_id = $request->orders_status;
                        $orderStatusHistory->save();
                    }
                    else
                    {
                        $isPartialCancelReturn = true;
                    }
                }
                else
                {
                    $orderDetail->order_status    = $request->orders_status;
                    $orderDetail->tracking_number = $request->tracking_number;
                    $orderDetail->courier_id      = $request->courier_id;

                    $orderStatusHistory                  = new AdminOrderStatusHistory();
                    $orderStatusHistory->order_id        = $orderDetail->id;
                    $orderStatusHistory->order_status_id = $request->orders_status;
                    $orderStatusHistory->save();
                }

                if ($orderDetail->save())
                {
                    if ($cancelReturn)
                    {
                        $productPrice          = 0;
                        /*
                         * For Discount
                         */
                        $discountPer           = $orderDetail->discount_per;
                        $discountApplied       = 0;
                        /*
                         * For Coupon Code
                         */
                        $couponDiscountPer     = $orderDetail->coupon_discount;
                        $couponDiscountApplied = 0;

                        foreach ($cancelReturn as $orderDetailId)
                        {
                            $orderProductDetail = OrderDetail::where(['id' => $orderDetailId, 'order_id' => $orderDetail->id])->first();
                            if ($orderProductDetail)
                            {
                                $individualProductPrice = $orderProductDetail->total_price;
                                $productPrice           += $individualProductPrice;
                                $productQuantity        = $orderProductDetail->quantity;

                                if ($discountPer)
                                {
                                    $individualDiscount = ($individualProductPrice * $discountPer) / 100;
                                    $discountApplied    += $individualDiscount;
                                }

                                if ($couponDiscountPer)
                                {
                                    $individualDiscount    = ($individualProductPrice * $couponDiscountPer) / 100;
                                    $couponDiscountApplied += $individualDiscount;
                                }

                                $orderProductDetail->order_status = $request->orders_status;
                                $orderProductDetail->is_active    = 0;

                                if ($orderProductDetail->save())
                                {
                                    /*
                                     * Have to increase quantity of product
                                     */
                                    $orderProductId = $orderProductDetail->product_id;
                                    $orderSizeId    = $orderProductDetail->size_id;
                                    $orderColorId   = $orderProductDetail->color_id;
                                    $orderQuantity  = $orderProductDetail->quantity;
                                    $productDetail  = \App\AdminModel\Product\AdminProduct::withTrashed()->find($orderProductId);
                                    $productDetail->increment('product_quantity', $orderQuantity); // Increase quantity of specific product in order

                                    $productDetail->save();

                                    if ($orderSizeId || $orderColorId)
                                    {
                                        $attribute = AdminProductAttribute::where(['products_id' => $orderProductId, 'size_id' => $orderSizeId, 'color_id' => $orderColorId])->first();
                                        if ($attribute)
                                        {
                                            $attribute->increment('quantity', $orderQuantity); // Increase quantity of specific product in order
                                        }
                                        else
                                        {
                                            $attribute              = new AdminProductAttribute();
                                            $attribute->products_id = $orderProductId;
                                            $attribute->size_id     = $orderSizeId;
                                            $attribute->color_id    = $orderColorId;
                                            $attribute->quantity    = $orderQuantity;
                                            $attribute->save();
                                        }
                                    }
                                }
                            }
                        }
                        /*
                         * Have to get order Last Status, If Last order status is -- Shipped or Ready to Shipped
                         * ... then don't deduct Shipping Charge.... 
                         * ... else Deduct Shipping Charge
                         */

                        $deductShippingFlag = false;

                        if (!$isPartialCancelReturn)
                        {
                            if ($lastUpdatedStatus)
                            {
                                $lastUpdatedStatus = $lastUpdatedStatus->order_status_id;
                            }
                            else
                            {
                                $lastUpdatedStatus = Helper::getOrderDefaultStatus();
                            }

                            if (!in_array($lastUpdatedStatus, config('custom.deductShippingChargeExcept', [2, 3])))
                            {
                                $deductShippingFlag = true;
                            }

                            if ($orderDetail->gift_wrap)
                            {
                                $orderDetail->gift_wrap = 0;
                                $orderDetail->save();
                            }
                        }

                        $orderDetail->order_price            = $orderDetail->order_price - $productPrice;
                        $orderDetail->discount               = $orderDetail->discount - $discountApplied;
                        $orderDetail->coupon_discount_amount = $orderDetail->coupon_discount_amount - $couponDiscountApplied;

                        $updatedOrderTotal = $orderDetail->order_price + $orderDetail->delivery_price + $orderDetail->tax - $orderDetail->discount - $orderDetail->coupon_discount_amount;
                        if ($deductShippingFlag)
                        {
                            $updatedOrderTotal           = $updatedOrderTotal - $orderDetail->delivery_price;
                            $orderDetail->delivery_price = 0;
                        }
                        $orderDetail->order_total = $updatedOrderTotal;
                        $orderDetail->save();

                        $orderProductDetail  = OrderDetail::whereIn('id', $request->cancel_return)->get();
                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$orderProductDetail[0]->order_status];
                    }
                    else
                    {
                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$orderDetail->order_status];
                        $orderProductDetail  = OrderDetail::where('order_id', $orderDetail->id)->where('is_active', 1)->get();
                    }

                    /*
                     * Mail Sending Code
                     */


                    $currency = Helper::getDefaultCurrency();

                    $user = User::find($orderDetail->user_id);

                    $locale = "1";

                    if ($user && isset($user->locale))
                    {
                        $languagedata = AdminLanguage::find($user->locale);
                        if ($languagedata)
                        {
                            $locale = $languagedata->id;
                            $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                        }
                        else
                        {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }
                    }
                    else
                    {
                        $locale = 1;
                        $view   = 'admin.order.order.en.mail';
                    }

                    $mailFrom = Helper::sendMailFrom();
                    $appName  = Helper::getAppName();
                    $mailTo   = $user->email;
                    $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                    $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                    $courier  = ($orderDetail->courier_id) ? CourierCompany::find($orderDetail->courier_id) : "";
                    
                    	$reciveEmail       = Helper::getSettingById(18);
        		$reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";

                    $phpMail           = new \App\Mail\CommonMailSender();
                    $customerCareEmail = Helper::getCustomerCareEmail();
                    $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                    $mailTo            = ['email' => $mailTo, 'name' => $userName];
 		    $cc['email'] = $reciveAdminEmail;

                    $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                    //echo $message;die;
                    if ($mailFrom && $mailTo && $subject && $message)
                    {
                        $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message,false, $cc);
                    }

                    /*
                     * /.Mail Sending Code
                     */

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
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->back()->with($status, $message_text);
    }

    public function cancelOrderStatus(Request $request, $orderId)
    {
        $orderId     = base64_decode($orderId);
        $orderDetail = WebOrder::find($orderId);
        if ($orderDetail)
        {
            $currentOrderStatus        = $orderDetail->order_status;
            $orderDetail->order_status = config('custom_web.cancel', 5);
            if ($orderDetail->save())
            {
                if ($currentOrderStatus != config('custom_web.Pending') && $currentOrderStatus != config('custom_web.cancel') && $currentOrderStatus != config('custom_web.return'))
                {
                    $orderProductData = $orderDetail->getOrderDetail(1);
                    foreach ($orderProductData as $productData)
                    {
                        $orderProductId = $productData->product_id;
                        $orderSizeId    = $productData->size_id;
                        $orderColorId   = $productData->color_id;
                        $orderQuantity  = $productData->quantity;
                        $productDetail  = \App\AdminModel\Product\AdminProduct::withTrashed()->find($orderProductId);
                        $productDetail->increment('product_quantity', $orderQuantity); // Increase quantity of specific product in order

                        $productDetail->save();

                        if ($orderSizeId || $orderColorId)
                        {
                            $attribute = AdminProductAttribute::where(['products_id' => $orderProductId, 'size_id' => $orderSizeId, 'color_id' => $orderColorId])->first();
                            if ($attribute)
                            {
                                $attribute->increment('quantity', $orderQuantity); // Increase quantity of specific product in order
                            }
                            else
                            {
                                $attribute              = new AdminProductAttribute();
                                $attribute->products_id = $orderProductId;
                                $attribute->size_id     = $orderSizeId;
                                $attribute->color_id    = $orderColorId;
                                $attribute->quantity    = $orderQuantity;
                                $attribute->save();
                            }
                        }
                    }
                }

                $orderStatusHistory                  = new AdminOrderStatusHistory();
                $orderStatusHistory->order_id        = $orderDetail->id;
                $orderStatusHistory->order_status_id = config('custom_web.cancel', 5);
                if ($orderStatusHistory->save())
                {
                    /*
                     * Mail Sending Code
                     */

                    $orderStatusPastForm = config('order.pastForm');
                    $orderStatusText     = $orderStatusPastForm[$orderDetail->order_status];
                    $orderProductDetail  = OrderDetail::where('order_id', $orderDetail->id)->get();
                    $currency            = Helper::getDefaultCurrency();

                    $user = User::withTrashed()->find($orderDetail->user_id);

                    $locale = "1";

                    if ($user && isset($user->locale))
                    {
                        $languagedata = AdminLanguage::find($user->locale);
                        if ($languagedata)
                        {
                            $locale = $languagedata->id;
                            $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                        }
                        else
                        {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }
                    }
                    else
                    {
                        $locale = 1;
                        $view   = 'admin.order.order.en.mail';
                    }

                    $mailFrom = Helper::sendMailFrom();
                    $appName  = Helper::getAppName();
                    $mailTo   = $user->email;
                    $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                    $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                    $courier  = ($orderDetail->courier_id) ? CourierCompany::find($orderDetail->courier_id) : "";

                    $phpMail           = new \App\Mail\CommonMailSender();
                    $customerCareEmail = Helper::getCustomerCareEmail();
                    $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                    $mailTo            = ['email' => $mailTo, 'name' => $userName];

                    $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                    //echo $message;die;
                    if ($mailFrom && $mailTo && $subject && $message)
                    {
                        $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message);
                    }

                    /*
                     * /.Mail Sending Code
                     */

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
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->back()->with($status, $message_text);
    }

    /* /
     * get order in complete data
     */

    public function orderNotCompleteData(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'id', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $filter['orderStatus'] = $request->_os;
        $filter['country']     = $request->_c;
        $orders                = new WebOrder();
        $status                = 1;
        $orders                = $orders->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filter, $status, true);
        $currency              = Helper::getDefaultCurrency();
        if ($currency)
        {
            $currency->code = config('custom.currency_convert_default_country');
        }
        $orderStatus = AdminOrderStatus::all();
        $country     = AdminCountry::getCountry();
        $badgeColor  = config('order.badge');
        return view('admin.order.order.pending-data', compact('sortKey', 'sortVal', 'searchText', 'orders', 'pageLength', 'currency', 'badgeColor', 'orderStatus', 'filter', 'country'));
    }

    /*
     * export data 
     */

    public function orderIndexExport()
    {
        return Excel::download(new OrderDetailExport(), config('excel.order_data'));
    }

    public function orderNotProcessExport()
    {
        return Excel::download(new OrderNotCompleteExport(), config('excel.order_not_complete_data'));
    }

    /* /
     * change order status from pending to complete
     */

    public function processedOrder($id)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $order       = WebOrder::find($id);
        $orderStatus = AdminOrderStatus::where('order_status', 'Process')->first();
        $flag        = true;
        if ($order && $order->order_status == config('custom_web.Pending'))
        {
            /*
             * Check Coupon code if applied
             */
            if ($order->coupon_discount_amount)
            {
                $checkCoupon = \App\AdminModel\Coupon\AdminCoupon::where(['id' => $order->coupon_id, 'coupon_code' => $order->coupon_code])->first();

                if ($checkCoupon)
                {
                    $user        = User::find($order->user_id);
                    $checkCoupon = Helper::checkCouponStatus($order->coupon_code, $user);
                    if (!$checkCoupon['status'])
                    {
                        $flag         = false;
                        $status       = 'error';
                        $message_text = $checkCoupon['message'];
                    }
                    else
                    {
                        $checkCoupon = $checkCoupon['data'];
                    }
                }
                else
                {
                    $flag         = false;
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.coupon_code_not_exist');
                }
            }
            if ($flag)
            {
                DB::beginTransaction();
                try
                {
                    $orderProductDetail = OrderDetail::where('order_id', $order->id)->get();
                    foreach ($orderProductDetail as $record)
                    {
                        $availQuantityFlag = true;

                        $adminProduct = AdminProduct::find($record->product_id);
                        if ($adminProduct && ($adminProduct->product_quantity >= $record->quantity))
                        {
                            $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                            $adminProduct->save();

                            if ($adminProduct->have_size || $adminProduct->have_color)
                            {
                                if ($record->size_id || $record->color_id)
                                {
                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id, 'size_id' => $record->size_id, 'color_id' => $record->color_id])->first();
                                    if ($adminProductAttriutes)
                                    {
                                        if ($adminProductAttriutes->quantity >= $record->quantity)
                                        {
                                            $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                            $adminProductAttriutes->save();
                                        }
                                        else
                                        {
                                            $availQuantityFlag = false;
                                        }
                                    }
                                    else
                                    {
                                        $availQuantityFlag = false;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $availQuantityFlag = false;
                        }

                        if (!$availQuantityFlag)
                        {
                            DB::rollback();
                            $status       = 'error';
                            $productName  = $record->getProduct(1);
                            $productName  = $productName ? ucfirst($productName->products_name) : '';
                            $message_text = Lang::get($this->langFile . '.available_quantity_less');
                            $message_text = str_replace("#product#", $productName, $message_text);
                            return redirect()->back()->with($status, $message_text);
                        }
                    }

                    $order->order_status = $orderStatus->id;

                    if ($order->save())
                    {
                        if (isset($checkCoupon) && $checkCoupon)
                        {
                            if (isset($checkCoupon['allow_multiple']) && $checkCoupon['allow_multiple'] == 1)
                            {
                                /*
                                 * If allow multiple then don't update is_applied
                                 */
                            }
                            else
                            {
                                $checkCoupon->is_applied = 1;
                                $checkCoupon->save();
                            }
                        }
                        /*
                         * Mail Sending Code
                         */

                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatusText     = $orderStatusPastForm[$order->order_status];
                        $currency            = Helper::getDefaultCurrency();

                        $user = User::find($order->user_id);

                        $locale = "1";

                        if ($user && isset($user->locale))
                        {
                            $languagedata = AdminLanguage::find($user->locale);
                            if ($languagedata)
                            {
                                $locale = $languagedata->id;
                                $view   = 'admin.order.order.' . $languagedata->code . '.mail';
                            }
                            else
                            {
                                $locale = 1;
                                $view   = 'admin.order.order.en.mail';
                            }
                        }
                        else
                        {
                            $locale = 1;
                            $view   = 'admin.order.order.en.mail';
                        }

                        $mailFrom = Helper::sendMailFrom();
                        $appName  = Helper::getAppName();
                        $mailTo   = $user->email;
                        $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                        $subject  = $appName . " : " . Lang::get($this->langFile . '.order-status') . ' "' . $orderStatusText . '"';
                        $courier  = ($order->courier_id) ? CourierCompany::find($order->courier_id) : "";

                        $phpMail = new \App\Mail\CommonMailSender();

                        $customerCareEmail = Helper::getCustomerCareEmail();
                        $mailFrom          = ['email' => $mailFrom, 'name' => $appName, 'customerCareEmail' => $customerCareEmail];
                        $mailTo            = ['email' => $mailTo, 'name' => $userName];
                        $reciveEmail       = Helper::getSettingById(18);
        		$reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";
                     $cc['email'] = $reciveAdminEmail;


                        $orderDetail = $order;

                        $message = view($view, compact('user', 'orderDetail', 'locale', 'orderStatusText', 'orderProductDetail', 'mailFrom', 'currency', 'courier'));
                        //echo $message;die;

                        if ($mailFrom && $mailTo && $subject && $message)
                        {
                            $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message,false, $cc);
                        }

                        /*
                         * /.Mail Sending Code
                         */

                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.update-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('admin.order.order-not-complete')->with($status, $message_text);
    }

    /*
     * print orde
     */

    public function printOrder($id)
    {
        $orderDetail        = WebOrder::find($id);
        $tax                = Helper::getTax();
        $orderStatus        = AdminOrderStatus::getOrderStatus();
        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
        $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
        $currency           = Helper::getDefaultCurrency();
        $badgeColor         = config('order.badge');
        $courier            = CourierCompany::find($orderDetail->courier_id);

        $shippingAddress = $orderDetail->getShippingAddress();

        $addressQr = "";

        if ($shippingAddress->address_url || $orderDetail->address_url)
        {
            $writer = new PngWriter();

            // Create QR code
            $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                        ->setEncoding(new Encoding('UTF-8'))
                     //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                        ->setSize(300)
                        ->setMargin(10)
                    //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                        ->setForegroundColor(new Color(0, 0, 0))
                        ->setBackgroundColor(new Color(255, 255, 255));
            // Create generic logo

            $logo = Logo::create(public_path() . '/logo.png')
                    ->setResizeToWidth(100);

            // Create generic label
            $label = Label::create('Shipping Address')
                    ->setTextColor(new Color(255, 0, 0));

            $result = $writer->write($qrCode, $logo, $label);

            //header('Content-Type: ' . $result->getMimeType());
            $addressQr = $result->getDataUri();
        }

        return view('admin.order.order.print', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr','courier'));
    }
    
    
    public function deliveryprintOrder($id)
    {
        $orderDetail        = WebOrder::find($id);
        $tax                = Helper::getTax();
        $orderStatus        = AdminOrderStatus::getOrderStatus();
        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
        $orderStatusHistory = AdminOrderStatusHistory::where('order_id', $id)->orderBy('updated_at', 'desc')->get();
        $currency           = Helper::getDefaultCurrency();
        $badgeColor         = config('order.badge');
        $courier            = CourierCompany::find($orderDetail->courier_id);

        $shippingAddress = $orderDetail->getShippingAddress();

        $addressQr = "";

        if ($shippingAddress->address_url || $orderDetail->address_url)
        {
            $writer = new PngWriter();

            // Create QR code
           $qrCode = QrCode::create($orderDetail->address_url ?  $orderDetail->address_url : $shippingAddress->address_url)
                        ->setEncoding(new Encoding('UTF-8'))
                     //   ->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW))
                        ->setSize(300)
                        ->setMargin(10)
                    //   ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                        ->setForegroundColor(new Color(0, 0, 0))
                        ->setBackgroundColor(new Color(255, 255, 255));

            // Create generic logo

            $logo = Logo::create(public_path() . '/logo.png')
                    ->setResizeToWidth(100);

            // Create generic label
            $label = Label::create('Shipping Address')
                    ->setTextColor(new Color(255, 0, 0));

            $result = $writer->write($qrCode, $logo, $label);

            //header('Content-Type: ' . $result->getMimeType());
            $addressQr = $result->getDataUri();
        }

        return view('admin.order.order.delivery-print', compact('orderDetail', 'orderProductDetail', 'orderStatus', 'tax', 'orderStatusHistory', 'currency', 'badgeColor', 'shippingAddress', 'addressQr','courier'));
    }

    /*
     * Delete Order
     */

    public function deleteOrder($id, $page)
    {
        /*
         * Delete orde which has status either : Cancel or Return
         */
        $orderDetail = WebOrder::find($id);
        if ($orderDetail)
        {
            if (collect(config('custom_web.can_delete_status'))->contains($orderDetail->order_status))
            {
                DB::beginTransaction();
                /*
                 * Delete Order from 'order_detail' and 'order' table
                 */
                try
                {
                    $orderDetailDelete = OrderDetail::where('order_id', $orderDetail->id)->delete();
                    $orderDelete       = $orderDetail->delete();
                    if ($orderDetailDelete && $orderDelete)
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
                $message_text = Lang::get($this->langFile . '.cant_delete_order');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        if ($page == 'on')
        {
            return redirect()->route('admin.order.order-not-complete')->with($status, $message_text);
        }
        return redirect()->route('admin.order.index')->with($status, $message_text);
    }

}

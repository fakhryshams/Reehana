<?php

namespace App\Http\Controllers\Web\Checkout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserModel\UserAddress;
use App\AdminModel\City\AdminCity;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductDescription;
use App\AdminModel\Product\AdminProductAttribute;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use App\UserModel\OrderMonogramDetail;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\AdminModel\AdminCountry;
use App\AdminModel\Order\AdminOrderStatus;
use Redirect;
use DB;
use URL;
use Session;
use Illuminate\Support\Facades\Lang;
use App\Helpers\Helper;
use App\UserModel\PaymentMethod;

class CheckoutController extends Controller
{

    private $module   = '.checkout';
    private $langFile = 'web';

    public function __construct() {}

    /**
     * Show the checkout .
     */
    public function index()
    {
        $user = Auth::user();

        if ($user) {

            $cart = [];

            $language = Helper::getLanguage();
            if (session('cart')) {
                // get cart from session
                $cart = session('cart');
            }
            if ($user) {    // get cart from db
                $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
            }
            //if user not login get product from session

            if (count($cart) == 0) {
                // if cart is empty
                Session::forget('couponCode');
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.cart-error-message'));
            } else {
                // check product quantity 
                $flag = true;
                $text = '';
                foreach ($cart as $id => $record) {
                    if ($user) {
                        $id = $record->product_id;
                    }
                    $productQuery = AdminProduct::where('id', $id);
                    $product = AdminProduct::applyActiveCondition($productQuery, $user)->first();
                    if (!$product) {
                        continue;
                    }
                    $qty           = $record['quantity'];
                    $productdetail = $product->getProductDescription($language->id);
                    if ((int) $qty > (int) $product->product_quantity) {
                        $flag = false;
                        $text .= $productdetail->products_name . ', ';
                    } else if ($record->size_id || $record->color_id) {
                        /*
                         * Check, If given size have quantity available or not
                         */
                        $sizeHasQuantityCheck = AdminProductAttribute::where(['products_id' => $record->product_id])->where('quantity', '>=', (int) $qty);
                        if ($record->size_id) {
                            $sizeHasQuantityCheck->where('size_id', $record->size_id);
                        }
                        if ($record->color_id) {
                            $sizeHasQuantityCheck->where('color_id', $record->color_id);
                        }

                        $sizeHasQuantityCheck = $sizeHasQuantityCheck->first();

                        if (!$sizeHasQuantityCheck) {
                            $flag = false;
                            $text .= $productdetail->products_name . ', ';
                        }
                    }
                }

                if ($flag == false) {
                    $text = rtrim($text, ", ");
                    return redirect()->back()->with('error', $text . Lang::get($this->langFile . '.out_of_stock'));
                }
            }

            //        if ($user->login_type != 'guest')
            //if ($user)
            //        {
            //            dd($user);
            $country  = AdminCountry::getCountry(1);
            if (!session('default_address')) {
                session()->put('default_address', 43);
            }

            $pickupUser = \App\User::where('pickupstore', 1)->pluck('id');

            $address = UserAddress::where('user_id', $user->id)->where('is_active', 1);
            if ($pickupUser) {
                $address->orWhereIn('user_id', $pickupUser)->where('is_active', 1);
            }
            $address = $address->orderBy('pickupstore', 'desc')->orderBy('created_at', 'desc')->get();
            $language = Helper::getLanguage();
            //if user login get product from db
            return view('web.checkout.index', compact('country', 'address', 'cart', 'language'));
            //        }
            //        else
            //        {
            //            return redirect()->route('web.guest.guest_address');
            //        }
        } else {
            return redirect()->route('web.user.login')->with('redirect_url', url()->current());
        }
    }

    public function cartReview()
    {

        $user     = Auth::user();
        $country  = AdminCountry::getCountry(1);
        $cart     = [];
        $language = Helper::getLanguage();
        $currency = Helper::getDefaultCurrency();


        $pickupStoreAddress = session('default_address');

        $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
        if (count($cart) > 0) {

            $useraddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();


            if (!$useraddress) {



                if ($pickupStoreAddress) {
                    $useraddress = UserAddress::where('id', $pickupStoreAddress)->first();
                    if (!$useraddress) {
                        return redirect()->route('web.checkout.index')->with('error', 'please enter address');
                    } else {
                        if ($pickupStoreAddress == 43) {
                            if (request()->cphone) {
                                session()->put('cphone', request()->cphone);
                            } else {
                                return redirect()->route('web.checkout.index')->with('error', 'please enter customer phone');
                            }
                        }
                    }
                } else {
                    return redirect()->route('web.checkout.index')->with('error', 'please enter address');
                }
            }

            if (!$useraddress->is_country_active) {

                return Redirect::route('web.checkout.index')->with('error', 'Please Select Address');
            }


            $c = 0;

            foreach ($cart as $id => $record) {
                if ($user) {
                    $id = $record->product_id;
                }


                $productQuery = AdminProduct::buildStoreUserAwareQuery($user)->where('id', $record->product_id);
                $productQuery = AdminProduct::applyActiveCondition($productQuery, $user);
                $product = $productQuery->first();
                if (!$product) {
                    $c++;
                    continue;
                }
            }
            if (count($cart) == $c) {
                $user = Auth::user();
                if ($user) {
                    ShoppingCart::where('user_id', $user->id)->delete();
                    ShoppingCartDetail::where('user_id', $user->id)->delete();
                }

                return Redirect::route('web.checkout.cart_review')->with('error', 'Please add product in to cart');
            }


            $paymentMethod = PaymentMethod::getPaymentMethod();

            return view('web.checkout.cart_review', compact('cart', 'country', 'language', 'user', 'paymentMethod'));
        } else {



            Session::forget('couponCode');
            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.cart-error-message'));
        }
    }

    public function initiatePayment(Request $request)
    {


        if ($request->c_phone) {
            session()->put('cphone', $request->c_phone);
        }

        if ($request->add_gift == 1) {
            $giftdata = array('is_gift' => $request->add_gift, 'title' => $request->gift_title, 'message' => $request->gift_message);
        } else {
            $giftdata = null;
        }

        if ($request->add_gift_box == 1) {
            $giftboxdata = array('is_gift' => $request->add_gift_box, 'title' => $request->gift_box_title, 'message' => $request->gift_box_message);
        } else {
            $giftboxdata = null;
        }
        
        $catdiscountApplied =0;
        $apply_discount = 0;


        session()->put('gift_wrap', $giftdata);
        session()->put('gift_box', $giftboxdata);
        if (isset($request->paymentMethod)) {
            session()->put('paymentMethod', $request->paymentMethod);

            $cart     = [];
            $user     = Auth::user();
            $language = Helper::getLanguage();
            if (session('cart')) {
                $cart = session('cart');
            }
            //if user login get product from db
            if ($user) {
                $cart = ShoppingCartDetail::where('user_id', $user->id)->where('is_active', 1)->get();
            }

            $shipping_charge = 0;
            if ($user) {
                $useraddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                if ($useraddress) {
                    $countryData = AdminCountry::where('id', $useraddress->country_id)->first();
                } else {
                    $countryData = AdminCountry::where('is_default', 1)->first();
                }
            } else {
                $countryData = AdminCountry::where('is_default', 1)->first();
            }


            if ($countryData) {
                $shipping_charge = $countryData->shipping_charge;
            }

            $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();

            if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                $shipping_charge = 0;
            }


            $shippingcharges = 0;
            if (count($cart) > 0) {
                $address      = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                    $address = $hasPickupStoreAddressSelected;
                }

                $total        = 0;
                $total_weight = 0;
                $c = 0;
                foreach ($cart as $id => $record) {
                    if ($user) {
                        $id = $record->product_id;
                    }


                    $productQueryInner = AdminProduct::buildStoreUserAwareQuery($user)->where('id', $record->product_id);
                    $productQueryInner = AdminProduct::applyActiveCondition($productQueryInner, $user);
                    $product = $productQueryInner->first();
                    if (!$product) {
                        $c++;
                        continue;
                    }
                    $productdetail = $product->getProductDescription($language->id);

                    /*
                     * Check International Shipping  - Server Side Validation
                     */
                    $allowIntlShipping       = $product->intl_shipping;
                    $checkIntlShippingStatus = Helper::checkIntlShippingStatus($address->country_id, $allowIntlShipping);
                    if (!$checkIntlShippingStatus) {
                        return redirect()->back()->with('error', Lang::get($this->langFile . '.intl_ship_text_validate'));
                    }
                    /*
                     * /.Check International Shipping  - Server Side Validation
                     */

                    $qty = $record['quantity'];
                    if ((int) $qty > (int) $product->product_quantity) {
                        return redirect()->back()->with('error', $productdetail->products_name . Lang::get($this->langFile . '.out_of_stock'));
                    }

                    $product_weight = $product->product_weight;
                    if ($record->size_id) {
                        /*
                         * Check, If given size have quantity available or not
                         */
                        $sizeHasQuantityCheck = AdminProductAttribute::where(['products_id' => $record->product_id])->where('quantity', '>=', (int) $qty);
                        if ($record->size_id) {
                            $sizeHasQuantityCheck->where('size_id', $record->size_id);
                        }
                        if ($record->color_id) {
                            $sizeHasQuantityCheck->where('color_id', $record->color_id);
                        }

                        $sizeHasQuantityCheck = $sizeHasQuantityCheck->first();
                        if (!$sizeHasQuantityCheck) {
                            return redirect()->back()->with('error', $productdetail->products_name . Lang::get($this->langFile . '.out_of_stock'));
                        }
                    }

                   
					$product_regular_price = $product->origina_price;
					$product_sale_price = $product->original_discount;
					$is_product_sale_price_null = $product->is_product_sale_price_null;
					
				
                    if ($record->color_id || $record->size_id) {
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                        if ($attributePrice) {
                         
                     		$product_regular_price = $attributePrice->origina_price;
							$product_sale_price = $attributePrice->original_discount;
							$is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                            $product_weight                      = $attributePrice->weight;
                        
                              
                            
                        }
                    }

                    $total_weight += $product_weight * $qty;

                    $price = $is_product_sale_price_null == 0  ? $product_sale_price : $product_regular_price;
                    $price2= $is_product_sale_price_null == 0  ? $product_sale_price : $product_regular_price;
                    if ($record->is_monogram) {
                        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                        $price         = $price + $monogramPrice;
                    }
                    
                    $categoryDiscount = Helper::getCategoryDiscount($record['product_id']);
					if ($categoryDiscount > 0 && $apply_discount == 0) {

						$cat_discount = $categoryDiscount;
					} else {
						$cat_discount = 0;
					}
                    
                    
                    $sub_total = $price * $record['quantity'];
                    $sub_total2 = $price2 * $record['quantity'];
                    
                    
                    
                    
                    if ($cat_discount && $apply_discount == 0) {
						$catdiscountApplied = $catdiscountApplied + ($sub_total2 * $cat_discount) / 100;
						
						//                                    $subTotal           = $subTotal - $catdiscountApplied;
					}
					
					

                    $total      += $sub_total;

                    $products[] = [
                        "name" => $productdetail->products_name,
                        "description" => $productdetail->products_name,
                        "quantity" => $record['quantity'],
                        "price" => Helper::formatNumber($price),
                    ];
                }
                
                
                
                
               
                
                
                $roundweight = ceil($total_weight);
                $countryName = 'kuwait';
                if (isset($address)) {
                    $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                    $countryName = $countryName ? $countryName->country_name : 'kuwait';
                }
                if ($roundweight < 1 ||  strtolower($countryName) == 'kuwait') {
                    $roundweight = 1;
                }
                
                
                
					
                $user = Auth::user();
                if ($user) {



                    if ($address && $address->id != 43) {
                        if ($address->id != '7') {

                            $zoneprice = Helper::getZonePrice($address->city_id);
                            if ($zoneprice > 0) {
                                $shipping_charge = $zoneprice;
                            }
                        } else {
                            $shipping_charge = 0;
                        }
                    }
                }

                $shippingcharges = $shipping_charge * $roundweight;

                $status = Helper::getOrderDefaultStatus();



                /*
                 * Coupon Code
                 */
                $couponDiscountFinal = 0;

                if (config('custom_web.show_coupon_card')) {
                    $appliedCoupon = [];

                    if ($user) {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                            if ($appliedCoupon['user_id'] != $user->id) {
                                $appliedCoupon = [];
                            } else {
                                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);
                                if (!$couponStatus['status']) {
                                    $appliedCoupon = [];
                                    return redirect()->back()->with('error', $couponStatus['message']);
                                }
                            }
                        }

                        if ($appliedCoupon) {
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                            if ($couponDiscountIn && $couponDiscount) {
                                if ($couponDiscountIn == 'P') {
                                    $couponDiscountFinal = ($total * $couponDiscount) / 100;
                                } else if ($couponDiscountIn == 'F') {
                                    $couponDiscountFinal = $total - $couponDiscount;
                                }
                            }
                        }
                    }
                }
                /*
                 * End :: Coupon Code
                 */


                if ($total == 0 && count($cart) == $c) {
                    $user = Auth::user();
                    if ($user) {
                        ShoppingCart::where('user_id', $user->id)->delete();
                        ShoppingCartDetail::where('user_id', $user->id)->delete();
                    }

                    return Redirect::route('web.checkout.cart_review')->with('error', 'Please add product in to cart');
                }

                if ($request->paymentMethod == config('custom_web.cod', 3)) {
                    $order = $this->createOrder('', $status);
                    if ($order) {
                        if ($user) {
                            ShoppingCart::where('user_id', $user->id)->delete();
                            ShoppingCartDetail::where('user_id', $user->id)->delete();
                        }
                        return redirect()->route('web.checkout.order_summary', ['id' => $order]);
                    }
                } else if (Auth::user()->id == config('config.store_admin_id')) {
                    $order = $this->createOrder('', $status, true, $request->paymentMethod);
                    if ($order) {
                        if ($user) {
                            ShoppingCart::where('user_id', $user->id)->delete();
                            ShoppingCartDetail::where('user_id', $user->id)->delete();
                        }
                        return redirect()->route('web.checkout.order_summary', ['id' => $order]);
                    }
                } else {
                    $tax = Helper::getTax();
                    if (!$tax) {
                        $tax = 0;
                    }
                    $taxval = ($total * $tax) / 100;



                    

                    if (config('custom_web.apply_discount')) {
                        $discount        = config('custom_web.discount', 20);
                        $discountApplied = ($total * $discount) / 100;
                    }
                    else
                    {
                       $discountApplied = $catdiscountApplied;
                    }
                    
                  
                    
                    $shippingcharges =  Helper::getShippingAmount($total, $shippingcharges);



                    $final_total = $total + $shippingcharges - $discountApplied - $couponDiscountFinal;
                    
                


                    // gift wrap
                    $setting    = \App\Helpers\Helper::getSetting();
                    $gift_price = 0;
                    $gift_wrap  = ['is_gift' => 0, 'title' => '', 'message' => ''];
                    if ($setting['31']->setting_val == 1) {
                        if (session('gift_wrap')) {
                            $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();

                            $gift_price = $setting['32']->setting_val > 0 ? $setting['32']->setting_val : 0;
                            if ($total >= $giftMinPrice) {
                                $gift_price = 0;
                            }
                            $gift_wrap = session('gift_wrap');
                        }
                    }

                    // gift box
                    $giftbox_price = 0;
                    $gift_box      = ['is_gift' => 0, 'title' => '', 'message' => ''];
                    $giftBoxSetting = $setting->firstWhere('setting_key', 'Gift Box');
                    if ($giftBoxSetting && $giftBoxSetting->setting_val == 1) {
                        if (session('gift_box')) {
                            $giftBoxAmtSetting  = $setting->firstWhere('setting_key', 'Gift Box Amount');
                            $giftbox_price      = $giftBoxAmtSetting && $giftBoxAmtSetting->setting_val > 0 ? $giftBoxAmtSetting->setting_val : 0;
                            $giftBoxMinSetting  = $setting->firstWhere('setting_key', 'Gift Box Min Amount');
                            $giftBoxMinPrice    = $giftBoxMinSetting ? $giftBoxMinSetting->setting_val : 0;
                            if ($total >= $giftBoxMinPrice) {
                                $giftbox_price = 0;
                            }
                            $gift_box = session('gift_box');
                        }
                    }

                    $final_total = $final_total + $gift_price + $giftbox_price;



                    /*
                     * Create Default Order with Pending Status
                     */
                    $status = $this->createOrder("", config('custom_web.Pending', 7), false, $request->paymentMethod);

                    if ($status) {
                        session()->put('customer_order_id', $status);

                        $merchant   = env('PAYMENT_MERCHANT', ''); #Merchant code
                        $basURL     = env('PAYMENT_URL', '');
                        $username   = env('PAYMENT_USERNAME', '');
                        $password   = env('PAYMENT_PASSWORD', '');
                        $apiKey     = env('PAYMENT_API_KEY', '');
                        $websiteURL = URL::to('/');

                        $successurl = route('web.checkout.payment_success');
                        $failururl  = route('web.checkout.payment_error');

                        //                        $post = [
                        //                            "email" => $user->email,
                        //                            "name" => $user->first_name . ' ' . $user->last_name,
                        //                            "paymentType" => $request->paymentMethod,
                        //                            'MerchantCode' => $merchant,
                        //                            'Amount' => Helper::formatNumber($final_total),
                        //                            'SuccessUrl' => $successurl,
                        //                            'FailureUrl' => $failururl,
                        //                            'Variable1' => $user->mobile,
                        //                            'Variable2' => $address->email,
                        //                            'Variable3' => $user->first_name . ' ' . $user->last_name,
                        //                            'Variable4' => $final_total,
                        //                            'Variable5' => $status, //Order ID
                        //                            'orderReferenceNumber' => $status, //Order ID
                        //                        ];

                        $payment_type = config('custom_web.payment_type');

                        $payment_type = $payment_type[$request->paymentMethod];

                        $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
                        $address = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                        if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                            $address = $hasPickupStoreAddressSelected;
                        }

                        //dd($final_total);
                        $post = array(
                            'merchant_id'     => $merchant,
                            'username'        => $username,
                            'password'        => stripslashes($password),
                            // 'api_key'         => $apiKey, // in sandbox request
                            'api_key' => password_hash($apiKey, PASSWORD_BCRYPT), //In production mode, please pass API_KEY withBCRYPT function
                            'order_id'        => $status, // MIN 30 characters with strong unique function (like hashing function with time)
                            'total_price'     => $final_total,
                            'CurrencyCode'    => 'KWD', //only works in production mode
                            'CstFName'        => $address->first_name . '' . $address->last_name,
                            'CstEmail'        => $address->email,
                            'CstMobile'       => $address->phone ? str_replace(' ', '', $address->phone) : '',
                            'success_url'     => $successurl,
                            'error_url'       => $failururl,
                            'test_mode'       => 0, // test mode enabled
                            'whitelabled'     => false, // only accept in live credentials (it will not work in test)
                            'payment_gateway' => $payment_type, // only works in production mode
                            'ProductName'     => '', // json_encode(['computer', 'television']),
                            'ProductQty'      => '', //json_encode([2, 1]),
                            'ProductPrice'    => '', //json_encode([150, 1500]),
                            'reference'       => $status
                        );


                        $post_data = array(
                            "products" => $products,
                            "order" => array(
                                "id" => strval($status),
                                "reference" => "",
                                "description" => "",
                                "currency" => "KWD",
                                "amount" => (float) $final_total
                            ),
                            "language" => "en",
                            "reference" => array(
                                "id" => strval($status)
                            ),
                            "customer" => array(
                                'uniqueId' => '',
                                'name' => $address->first_name . ' ' . $address->last_name,
                                'email' => $address->email,
                                'mobile' => ''
                            ),
                            'returnUrl' => $successurl,
                            'cancelUrl' => $failururl,
                            "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
                            "customerExtraData" => ""
                        );

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://uapi.upayments.com/api/v1/charge',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => json_encode($post_data),
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Authorization: Bearer NJo4MGY9db49ID9MvaWf543OV2L4BgQc1v0H0Ix2'
                            ),
                        ));

                        $response = curl_exec($curl);
                        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Get the HTTP status code
                        curl_close($curl);

                        $res = json_decode($response);
                        //dd($res);

                        if ($res && isset($res->status) && $res->status) {
                            $red_url = $res->data->link;
                            return Redirect::to($red_url);
                        } else {
                            return Redirect::route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.payment-error'));
                        }
                    } else {
                        return Redirect::route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.payment-error'));
                    }
                }
            } else {
                $this->emptyCart($user->id);
                return redirect()->route('web.index');
            }
        } else {
            return Redirect::route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.select-payment-error'));
        }
    }

    public function emptyCart($userId)
    {
        ShoppingCart::where('user_id', $userId)->delete();
        ShoppingCartDetail::where('user_id', $userId)->delete();
    }

    public function paymentSuccess(Request $request)
    {

        $orderId = session()->get('customer_order_id');
        if (isset($request->requested_order_id) && $request->requested_order_id == $orderId) {
            $response = $request->all();
            $paymentId = isset($request->payment_id) ? $request->payment_id : '';
            $status    = Helper::getOrderDefaultStatus();
            $status    = $this->updatedCreatedOrder($paymentId, $status, true, $orderId, $response);
            if ($status) {
                $user = Auth::user();
                if ($user) {
                    ShoppingCart::where('user_id', $user->id)->delete();
                    ShoppingCartDetail::where('user_id', $user->id)->delete();
                }
                //return redirect()->route('web.checkout.order_summary', ['id' => $status])->with('success', Lang::get($this->langFile . '.profile-update-message'));
                return redirect()->route('web.checkout.order_summary', ['id' => $status]);
            } else {
                return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
            }
        } else {
            return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
        }
    }

    public function paymentError(Request $request)
    {

        $orderId = session()->get('customer_order_id');
        if (isset($request->OrderID) && $request->OrderID == $orderId) {
            $response = $request->all();
            $paymentId = isset($request->PaymentID) ? $request->PaymentID : '';
            $user      = Auth::user();
            $status    = config('custom_web.fail', 7);
            $status    = $this->updatedCreatedOrder($paymentId, $status, false, $orderId, $response);
            /*
              if ($status)
              {
              if ($status)
              {
              ShoppingCart::where('user_id', $user->id)->delete();
              ShoppingCartDetail::where('user_id', $user->id)->delete();
              }
              return redirect()->route('web.checkout.order_summary', ['id' => $order]);
              }
             * 
             */
        }
        return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
    }

    public function createOrder($payment_id, $order_status, $isSuccess = true, $paymentMethod = false)
    {
        $couponId = 0;
        if ($paymentMethod) {
            $hesabePaymentMethod = config('custom_web.hesabe_payment_method');
        }
        
        $cat_discount = 0;
		$catdiscountApplied = 0;
		$discountApplied = 0;
		$apply_discount =0;

        $status  = false;
        $user    = Auth::user();
        $cart    = ShoppingCartDetail::where('user_id', $user->id)->where('is_active', 1)->get();
        $address = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
        if (!$address) {
            $address = Helper::getPickupStoreAddress();
        }
        if ($cart && $address) {
            DB::beginTransaction();

            $currency        = Helper::getDefaultCurrency(); //To manage user selected currency and current conversion rate
            $shipping_charge = 0;
            if ($user) {
                $useraddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                if ($useraddress) {
                    $countryData = AdminCountry::where('id', $useraddress->country_id)->first();
                } else {
                    $countryData = AdminCountry::where('is_default', 1)->first();
                }
            } else {
                $countryData = AdminCountry::where('is_default', 1)->first();
            }
            if ($countryData) {
                $shipping_charge = $countryData->shipping_charge;
            }

            if ($user) {

                $address = UserAddress::where('is_default', 1)->where('user_id', $user->id)->first();
                if ($address) {
                    if ($address->id != '7') {
                        $zoneprice = Helper::getZonePrice($address->city_id);
                        if ($zoneprice > 0) {
                            $shipping_charge = $zoneprice;
                        }
                    } else {
                        $shipping_charge = 0;
                    }
                }
            }

            $order_price     = 0;
            $shippingcharges = 0;
            $total_weight    = 0;
            $cat_discount_per = 0;

            foreach ($cart as $record) {
                $productQuery = AdminProduct::buildStoreUserAwareQuery($user)->where('id', $record->product_id);
                $productQuery = AdminProduct::applyActiveCondition($productQuery, $user);
                $product = $productQuery->first();
                if (!$product) {
                    continue;
                }
                $product_weight = $product->product_weight;

              	$product_regular_price = $product->origina_price;
				$product_sale_price = $product->original_discount;
				$is_product_sale_price_null = $product->is_product_sale_price_null;
                if ($record->color_id || $record->size_id) {
                    $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                    if ($attributePrice) {
                        $product_regular_price = $attributePrice->origina_price;
						$product_sale_price = $attributePrice->original_discount;
						$is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                        $product_weight                      = $attributePrice->weight;
                    }
                }

                $total_weight += $product_weight * $record->quantity;

                $product_price = $product_sale_price ? $product_sale_price : $product_regular_price;

                $sum = $record->quantity * $product_price;
                
                 $sum2 = $record->quantity * $product_price;
                if ($record->is_monogram) {
                    $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                    if ($record->is_same == 0) {

                        $monogrcount = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->count();
                        if ($monogrcount > 0) {
                            $monogramPrice = $monogramPrice * $monogrcount;
                            $sum           = $sum + $monogramPrice;
                        }
                    } else {
                        $monogramPrice = $record->quantity * $monogramPrice;

                        $sum = $sum + $monogramPrice;
                    }
                }
                
                $categoryDiscount = Helper::getCategoryDiscount($record['product_id']);
				if ($categoryDiscount > 0 && $apply_discount == 0) {

					$cat_discount = $categoryDiscount;
				} else {
					$cat_discount = 0;
				}

				if ($cat_discount && $apply_discount == 0) {

					$cat_discount_per = $cat_discount_per + $cat_discount;
					$productDiscount = ($sum2  * $cat_discount) / 100;

					$catdiscountApplied = $catdiscountApplied + $productDiscount;

					//
				}

                $order_price = $order_price + $sum;
            }
            //            dd($order_price);

            $roundweight = ceil($total_weight);

            $countryName = 'kuwait';
            if (isset($address)) {
                $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                $countryName = $countryName ? $countryName->country_name : 'kuwait';
            }
            if ($roundweight < 1 ||  strtolower($countryName) == 'kuwait') {
                $roundweight = 1;
            }
            $shippingcharges = $shipping_charge * $roundweight;
            $shippingcharges =  Helper::getShippingAmount($order_price, $shippingcharges);

            $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
            if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                $shipping_charge = 0;
                $shippingcharges = 0;
            }

            $tax = Helper::getTax();
            if (!$tax) {
                $tax = 0;
            }
            $taxval = ($order_price * $tax) / 100;


            $address                    = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();

            if (!$address) {
                $address = Helper::getPickupStoreAddress();
            }
            $order                      = new Order();
            $order->user_id             = $user->id;
            $order->shipping_address_id = $address->id;
            $order->cphone              = session('cphone');
            $order->billing_address_id  = $address->id;
            $order->transaction_id      = $payment_id;
            $order->payment_method      = $paymentMethod ? $hesabePaymentMethod[$paymentMethod] : session()->get('paymentMethod');
            $order->order_status        = $order_status;
            $order->order_price         = $order_price;
            $order->delivery_price      = $shippingcharges;
            $order->delivery_rate       = $shipping_charge;
            $order->tax                 = $taxval;
            $order->user_agent_name     = Helper::getBrowserName();
            $order->created_at          = date("Y-m-d H:i:s");
            /*
             *  Convert Currency Rate
             */
            if ($currency) {
                $order->currency_code = $currency->code;
                $order->currency_rate = $currency->value;
            }

            $discountApplied = 0;

            if (config('custom_web.apply_discount')) {
                $discount            = config('custom_web.discount', 20);
                $discountApplied     = ($order_price * $discount) / 100;
                $order->discount_per = $discount;
                $order->discount     = $discountApplied;
            }
            else
            {
               $discountApplied = $catdiscountApplied;
               $order->discount     = $catdiscountApplied;       
            }

            /*
             * Coupon Code
             */
            $couponDiscountFinal = 0;

            if (config('custom_web.show_coupon_card')) {
                $appliedCoupon = [];

                if ($user) {
                    $appliedCoupon = session('couponCode');
                    if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                        if ($appliedCoupon['user_id'] != $user->id) {
                            $appliedCoupon = [];
                        }
                    }

                    if ($appliedCoupon) {
                        $couponId         = isset($appliedCoupon['id']) ? $appliedCoupon['id'] : '';
                        $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                        $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';
                        $couponCode       = isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';

                        if ($couponDiscountIn && $couponDiscount) {
                            if ($couponDiscountIn == 'P') {
                                $couponDiscountFinal = ($order_price * $couponDiscount) / 100;
                            } else if ($couponDiscountIn == 'F') {
                                $couponDiscountFinal = $order_price - $couponDiscount;
                            }
                        }

                        $order->coupon_id              = $couponId;
                        $order->coupon_code            = $couponCode;
                        $order->coupon_discount_in     = $couponDiscountIn;
                        $order->coupon_discount        = $couponDiscount;
                        $order->coupon_discount_amount = $couponDiscountFinal;
                    }
                }
            }
            /*
             * End :: Coupon Code
             */




            $finalTotal = $order_price + $shippingcharges + $taxval - $discountApplied - $couponDiscountFinal;


            $setting = \App\Helpers\Helper::getSetting();

            $gift_price = null;
            $giftbox_price = null;

            $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
            if ($setting['31']->setting_val == 1) {
                if (session('gift_wrap')) {
                    $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
                    $gift_price   = $setting['32']->setting_val > 0 ? $setting['32']->setting_val : 0;

                    if ($order_price >= $giftMinPrice) {
                        $gift_price = 0;
                    }
                    $gift_wrap = session('gift_wrap');
                }
            }

            $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
            $giftBoxSetting = $setting->firstWhere('setting_key', 'Gift Box');
            if ($giftBoxSetting && $giftBoxSetting->setting_val == 1) {
                if (session('gift_box')) {
                    $giftBoxAmtSetting = $setting->firstWhere('setting_key', 'Gift Box Amount');
                    $giftbox_price     = $giftBoxAmtSetting && $giftBoxAmtSetting->setting_val > 0 ? $giftBoxAmtSetting->setting_val : 0;
                    $giftBoxMinSetting = $setting->firstWhere('setting_key', 'Gift Box Min Amount');
                    $giftBoxMinPrice   = $giftBoxMinSetting ? $giftBoxMinSetting->setting_val : 0;

                    if ($order_price >= $giftBoxMinPrice) {
                        $giftbox_price = 0;
                    }
                    $gift_box = session('gift_box');
                }
            }

            $order->is_gift = false;
            if (session('gift_wrap')) {
                $order->is_gift = true;
            }

            $order->is_box = false;
            if (session('gift_box')) {
                $order->is_box = true;
            }

            $order->order_total      = $finalTotal + $gift_price + $giftbox_price;
            $order->gift_wrap        = $gift_price;
            $order->gift_title       = $gift_wrap['title'];
            $order->gift_message     = $gift_wrap['message'];
            $order->gift_box         = $giftbox_price;
            $order->gift_box_title   = $gift_box['title'];
            $order->gift_box_message = $gift_box['message'];

            if ($order->save()) {

                foreach ($cart as $record) {
                    $productQueryInner = AdminProduct::buildStoreUserAwareQuery($user)->where('id', $record->product_id);
                    $productQueryInner = AdminProduct::applyActiveCondition($productQueryInner, $user);
                    $product = $productQueryInner->first();
                    if (!$product) {
                        continue;
                    }
                    $product_regular_price = $product->product_regular_price ;
                    $product_sale_price  = $product->product_sale_price;
                    $is_product_sale_price_null = $product->is_product_sale_price_null;

                    if ($record->color_id || $record->size_id) {
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                        if ($attributePrice) {
                            $product_regular_price      = $attributePrice->product_regular_price;
                            $product_sale_price         = $attributePrice->product_sale_price;
                            $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                        }
                    }
                    $product_price = $product_sale_price ? $product_sale_price : $product_regular_price;
                    //                    if ($record->is_monogram)
                    //                    {
                    //                        
                    //                        //OrderMonogramDetail
                    //                        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                    //                        $product_price = $product_price + $monogramPrice;
                    //                    }


                    $orderdetail                = new OrderDetail();
                    $orderdetail->order_id      = $order->id;
                    $orderdetail->product_id    = $record->product_id;
                    $orderdetail->product_price = $product_price;
                    $orderdetail->quantity      = $record->quantity;
                    $orderdetail->size_id       = $record->size_id;
                    $orderdetail->color_id      = $record->color_id;
                    $orderdetail->is_same       = $record->is_same;
                    $orderdetail->total_price   = $record->quantity * $product_price;
                    if ($record->note) {
                        $orderdetail->note = $record->note;
                    }
                    // monogram add

                    if ($record->is_monogram) {
                        $orderdetail->is_monogram = $record->is_monogram;
                    }
                    if ($orderdetail->save()) {
                        if ($record->is_monogram) {
                            $monogramList = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get();

                            foreach ($monogramList as $mvalue) {


                                $orderMonogramDetail                      = new OrderMonogramDetail();
                                $monogramPrice                            = \App\Helpers\Helper::getMonogramPrice();
                                $font                                     = Helper::getMonogramStyle($mvalue->font);
                                $placement                                = Helper::getPlacement($mvalue->placement);
                                $orderMonogramDetail->order_id            = $order->id;
                                $orderMonogramDetail->order_detail_id     = $orderdetail->id;
                                $orderMonogramDetail->font                = $mvalue->font;
                                $orderMonogramDetail->initials_count      = $mvalue->initials_count;
                                $orderMonogramDetail->left_text           = $mvalue->left_text;
                                $orderMonogramDetail->center_text         = $mvalue->center_text;
                                $orderMonogramDetail->right_text          = $mvalue->right_text;
                                $orderMonogramDetail->text4               = $mvalue->text4;
                                $orderMonogramDetail->monogram_color      = $mvalue->monogram_color;
                                $orderMonogramDetail->monogram_color_hash = $mvalue->monogram_color_hash;
                                $orderMonogramDetail->monogram_color_id   = $mvalue->monogram_color_id;
                                $orderMonogramDetail->placement           = $mvalue->placement;
                                $orderMonogramDetail->placement           = $mvalue->placement;
                                $orderMonogramDetail->placement_name      = $placement->placement_name;
                                $orderMonogramDetail->placement_desc      = $placement->placement_desc;
                                $orderMonogramDetail->placement_price     = $placement->placement_price;
                                $orderMonogramDetail->placement_price     = $placement->placement_price;
                                $orderMonogramDetail->placement_image     = $placement->placement_image;
                                $orderMonogramDetail->font_style_name     = $font->style_name;
                                $orderMonogramDetail->font_style_image    = $font->style_image;
                                $orderMonogramDetail->monogram_price      = $monogramPrice;
                                $orderMonogramDetail->monogram_note       = $mvalue->monogram_note ? $mvalue->monogram_note : '';
                                $orderMonogramDetail->save();
                            }
                        }
                        if ($order_status != config('custom_web.fail', 7)) {
                            /*
                             * Update Main quantity, Only if order is not failed
                             */
                            $adminProduct                   = AdminProduct::find($record->product_id);
                            $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                            $adminProduct->save();
                            if ($adminProduct->have_size || $adminProduct->have_color) {
                                if ($record->size_id || $record->color_id) {
                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id]);
                                    if ($record->size_id) {
                                        $adminProductAttriutes->where('size_id', $record->size_id);
                                    }
                                    if ($record->color_id) {
                                        $adminProductAttriutes->where('color_id', $record->color_id);
                                    }
                                    $adminProductAttriutes           = $adminProductAttriutes->first();
                                    $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                    $adminProductAttriutes->save();
                                }
                            }
                        }
                        $status = $order->id;
                    } else {
                        $status = false;
                    }
                }

                if ($isSuccess && $couponId) {
                    $couponData = \App\UserModel\CouponCode::where(['coupon_id' => $couponId])->first();
                    if ($couponData) {
                        if (isset($appliedCoupon['allow_multiple']) && $appliedCoupon['allow_multiple'] == 1) {
                            /*
                             * If allow multiple then don't update is_applied
                             */
                        } else {
                            $couponData->is_applied = 1;
                            $couponData->save();
                        }
                    }
                }

                DB::commit();
            } else {
                $status = false;
                DB::rollback();
            }
        }
        if ($status) {

            if ($isSuccess) {

                $this->sendMail($order);
                Session::forget('paymentMethod');
                Session::forget('couponCode');
                session()->put('gift_wrap', null);
                session()->put('gift_box', null);
            }
            //Session::forget('paymentMethod');
        }



        return $status;
    }

    public function updatedCreatedOrder($paymentId, $order_status, $isSuccess = true, $orderId = NULL, $paymentResponse = NULL)
    {


        //dd($paymentResponse);
        $status  = false;
        $user    = Auth::user();
        $orderId = $orderId ? $orderId : session()->get('customer_order_id');
        $order   = Order::where(['id' => $orderId, 'order_status' => config('custom_web.Pending')])->first();
        if ($order) {
            if ($paymentResponse) {
                if (isset($paymentResponse['Method'])) {
                    if ($paymentResponse['Method'] != 0) //0=Invalid Response :: https://developer.hesabe.com/docs/2.0/process-gateway-response
                    {
                        $hesabePaymentMethod = config('custom_web.hesabe_payment_method');
                        $paymentMethod       = isset($hesabePaymentMethod[$paymentResponse['Method']]) ? $hesabePaymentMethod[$paymentResponse['Method']] : '';
                        if ($paymentMethod) {
                            $order->payment_method = $paymentMethod;
                        }
                    }
                }
            }

            $order->transaction_id   = $paymentId;
            $order->order_status     = $order_status;
            $order->payment_response = json_encode($paymentResponse);
            $order->created_at       = date("Y-m-d H:i:s");

            if ($order->save()) {
                if ($isSuccess) {
                    /*
                     * Update Main quantity, Only if order is not failed
                     */

                    $orderDetail = OrderDetail::where('order_id', $order->id)->get();
                    foreach ($orderDetail as $record) {
                        $adminProduct = AdminProduct::find($record->product_id);
                        if ($adminProduct) {
                            $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                            $adminProduct->save();


                            if ($adminProduct->have_size || $adminProduct->have_color) {
                                if ($record->size_id && !$record->color_id) {
                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id, 'size_id' => $record->size_id])->first();
                                    if ($adminProductAttriutes) {
                                        $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                        $adminProductAttriutes->save();
                                    }
                                }
                                if ($record->color_id && !$record->size_id) {

                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id, 'color_id' => $record->color_id])->first();
                                    if ($adminProductAttriutes) {
                                        $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                        $adminProductAttriutes->save();
                                    }
                                }
                                if ($record->size_id && $record->color_id) {

                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id, 'size_id' => $record->size_id, 'color_id' => $record->color_id])->first();
                                    if ($adminProductAttriutes) {

                                        $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                        $adminProductAttriutes->save();
                                    }
                                }
                            }
                        }
                    }

                    $this->sendMail($order);

                    /*
                     * Update coupon as applied
                     */
                    $appliedCoupon = session('couponCode');
                    if ($appliedCoupon) {
                        $couponId = isset($appliedCoupon['id']) ? $appliedCoupon['id'] : '';
                        if ($couponId) {
                            if (isset($appliedCoupon['allow_multiple']) && $appliedCoupon['allow_multiple'] == 1) {
                                /*
                                 * If allow multiple then don't update is_applied
                                 */
                            } else {
                                $couponData = \App\UserModel\CouponCode::where(['coupon_id' => $couponId])->first();
                                if ($couponData) {
                                    $couponData->is_applied = 1;
                                    $couponData->save();
                                }
                            }
                        }
                    }

                    /*
                     * If Order successfully placed then and then clear coupon session
                     */
                    Session::forget('couponCode');
                    session()->put('gift_wrap', null);
                    session()->put('gift_box', null);
                }
                $status = $order->id;
            }
        }


        Session::forget('paymentMethod');
        Session::forget('customer_order_id');



        return $status;
    }

    public function orderSummary($id)
    {
        $user = Auth::user();
        if ($id && $user) {
            $order = Order::where('id', $id)->where('user_id', $user->id)->withTrashed()->first();
            if ($order) {
                $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
                return view('web.checkout.order_summary', compact('order', 'address'));
            } else {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
            }
        } else {

            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function printOrder($id, $page)
    {
        $user = Auth::user();
        if ($id && $user) {
            $order = Order::where('id', $id)->where('user_id', $user->id)->withTrashed()->first();
            if ($order) {
                $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
                return view('web.checkout.order_summary_print', compact('order', 'address', 'page'));
            } else {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
            }
        } else {

            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function ChangeDefaultAddress($id)
    {

        $user = Auth::user();

        if ($user && $id) {

            $address = UserAddress::where('id', $id)->where('user_id', $user->id)->first();

            if ($address) {
                if ($address->is_country_active) {
                    UserAddress::where('user_id', $user->id)->where('id', '!=', $id)->update(['is_default' => 0]);

                    if ($user->pickupstore == 0) {
                        $address->is_default = 1;
                    }

                    if ($address->save()) {
                        session()->put('default_address', $id);
                        Session::forget('cphone');
                        //return Redirect::back()->with('success', Lang::get($this->langFile . '.default-address-message'));

                        return Redirect::back();
                    } else {

                        return Redirect::back()->with('error', Lang::get($this->langFile . '.error-message'));
                    }
                } else {
                    return Redirect::back()->with('error', Lang::get($this->langFile . '.country-not-active'));
                }
            } else {
                $address = UserAddress::where('id', $id)->where('pickupstore', 1)->first();
                if ($address) {
                    UserAddress::where('user_id', $user->id)->update(['is_default' => 0]);
                    session()->put('default_address', $id);
                    return Redirect::back();
                } else {
                    Session::forget('default_address');
                    Session::forget('cphone');
                    return Redirect::back()->with('error', Lang::get($this->langFile . '.no-record-message'));
                }
            }
        } else {
            return Redirect::back()->with('error', Lang::get($this->langFile . '.no-record-message'));
        }
    }

    public function sendMail($order)
    {
        $shipping_address = UserAddress::find($order->shipping_address_id);
        $adminOrderStatus = AdminOrderStatus::find($order->order_status);
        $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
        $language         = Helper::getLanguage();
        $contactEmail     = Helper::getSettingById(19);
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";

        $reciveEmail       = Helper::getSettingById(18);
        $reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";

        if ($shipping_address->pickupstore == 0) {
            $mailTo            = $shipping_address->email;
        } else {
            $user = Auth::user();
            $mailTo            = $user->email;
        }
        $app_name          = Helper::getAppName();
        $customerCareEmail = Helper::getCustomerCareEmail();

        $from['email'] = $admin_mail;
        $from['name']  = $app_name;
        $to['email']   = $mailTo;
        $to['name']    = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

        $cc['email'] = $reciveAdminEmail;

        $subject      = $app_name . "-New Order #" . $order->id;
        $message      = \View::make('web.emails.thank_you.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail', 'customerCareEmail'))->render();
        //        echo $message;die;
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return true;
        } else {
            return false;
        }
    }

    public function test_order($id)
    {
        $order   = Order::where(['id' => $id])->first();
        $shipping_address = UserAddress::find($order->shipping_address_id);
        $adminOrderStatus = AdminOrderStatus::find($order->order_status);
        $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
        $language         = Helper::getLanguage();
        $contactEmail     = Helper::getSettingById(19);
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";

        $reciveEmail       = Helper::getSettingById(18);
        $reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";

        $mailTo            = $shipping_address->email;
        $app_name          = Helper::getAppName();
        $customerCareEmail = Helper::getCustomerCareEmail();

        $from['email'] = $admin_mail;

        $from['name']  = $app_name;
        $to['email']   = 'mp.miteshpancha@gmail.com';
        $to['name']    = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

        $cc['email'] = 'uditprajapati9791@gmail.com,nigamshah21@gmail.com';

        $subject      = $app_name . "-New Order #" . $order->id;
        $message      = \View::make('web.emails.thank_you.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail', 'customerCareEmail'))->render();
        //        echo $message;die;
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return 'mail sent';
        } else {
            return false;
        }
    }

    public function payment_success_mail($order)
    {
        $shipping_address = UserAddress::find($order->shipping_address_id);
        $adminOrderStatus = AdminOrderStatus::find($order->order_status);
        $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
        $language         = Helper::getLanguage();
        $contactEmail     = Helper::getSettingById(19);
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";


        $reciveEmail      = Helper::getSettingById(18);
        $reciveAdminEmail = $reciveEmail ? $reciveEmail->setting_val : "";
        $mailTo           = $shipping_address->email;
        $app_name         = Helper::getAppName();
        $from['email']    = $admin_mail;
        $from['name']     = $app_name;
        $to['email']      = $mailTo;
        $to['name']       = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

        $cc['email'] = $reciveAdminEmail;

        $subject      = $app_name . "-New Order";
        $message      = \View::make('web.emails.payment_success.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail'))->render();
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return true;
        } else {
            return false;
        }
    }
}

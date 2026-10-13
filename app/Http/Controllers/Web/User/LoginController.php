<?php

namespace App\Http\Controllers\Web\User;

use App\User;
use App\AdminModel\AdminCountry;
use App\AdminModel\City\AdminCity;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\Cart\MonogramShoppingCartDetail;
use App\AdminModel\Product\AdminProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Socialite;
use App\UserModel\UserAddress;
use Redirect;
use Session;
use App\Helpers\Helper;

class LoginController extends Controller
{

    private $module = '.login';
    private $langFile = 'web';

    public function __construct()
    {
//        if (Auth::viaRemember())
//        {
//            dd(Auth::viaRemember());
//        }
        $this->middleware('guest:web')->except('logout');
    }

    /**
     * Show the login .
     */
    public function showLoginForm()
    {

        $isExistRedirectUrl = session()->get('redirect_url');
        if ($isExistRedirectUrl)
        {
            $url = $isExistRedirectUrl;
        }
        else
        {
            $url = url()->previous();
        }

        session()->put('previous_url', $url);
        return view('web.user.login', compact('url'));
    }

    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required',
            'password' => 'required',
                ], [
            'email.required' => \Lang::get($this->langFile . '.error-req-email'),
            'password.required' => \Lang::get($this->langFile . '.error-req-password'),
        ]);
        $credentials = $request->only('email', 'password');
        $remember = isset($request->remember) ? true : false; //Remember Me
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_active' => 1], $remember))
        {
            // Authentication passed...
            $user = Auth::user();
            if ($user)
            {
                $userSelectedLanguage = Helper::getLanguage();

                if ($userSelectedLanguage)
                {
                    $user->locale = $userSelectedLanguage->id;
                }
                $user->login_type = 'website';
                $user->last_login = date("Y-m-d H:i:s");
                $user->save();

                $isCartUpdated = $this->addToCart();

                $appliedCoupon = session('couponCode');
                if ($appliedCoupon)
                {
                    $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);
                    if ($couponStatus['status'])
                    {
                        $coupon_data = \App\UserModel\CouponCode::where('id', $couponStatus['data']['id'])->first();
                        if ($coupon_data)
                        {
                            $coupon_data->user_id = $user->id;
                            $coupon_data->email = $user->email;
                            $coupon_data->save();

                            $response = [
                                'status' => true,
                                'message' => __('web.coupon_applied'),
                                'data' => [
                                    'id' => $coupon_data->coupon_id,
                                    'coupon_code' => $coupon_data->coupon_code,
                                    'user_id' => $coupon_data->user_id,
                                    'coupon_discount_in' => $coupon_data->coupon_discount_in,
                                    'coupon_discount' => $coupon_data->coupon_discount,
                                    'allow_multiple' => $coupon_data->allow_multiple,
                                ]
                            ];
                            session()->put('couponCode', $response['data']);
                        }
                    }
                }
                if (session()->get('previous_url'))
                {
                    $prev_url = session()->get('previous_url');

                    return redirect()->to($prev_url);
                }
                elseif ($isCartUpdated)
                {
                    return redirect()->route('web.cart.index');
                }
            }
            if (isset($request->url))
            {
                return redirect()->to($request->url);
            }
            return redirect()->route('web.index');
        }
        else
        {
            return redirect()->route('web.user.login')->with('login_error', \Lang::get($this->langFile . '.invalid-credentials-message'));
        }
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        session()->put('couponCode', []);
        return redirect()->route('web.user.login');
    }

    /**
     * Show the register page .
     */
    public function showRgeisterForm()
    {
        $url = '';
        if (session()->get('previous_url'))
        {
            $url = session()->get('previous_url');
        }
        // $url = url()->previous();
        return view('web.user.register', compact('url'));
    }

    public function register(Request $request)
    {
        $emailValidationRule = "";
        $checkGuestEmail = User::where(['email' => $request->email, 'login_type' => 'guest'])->first();
        if (!$checkGuestEmail)
        {
            $emailValidationRule = "|unique:users,email,NULL,id,deleted_at,NULL";
        }

        $validate = $request->validate([
            'email' => 'required|min:6' . $emailValidationRule,
            'password' => 'required|confirmed|min:6',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            't_and_c' => 'required',
                //     'captcha' => 'required|captcha'
                ], [
            'email.required' => Lang::get($this->langFile . '.error-req-email'),
            'email.min' => Lang::get($this->langFile . '.error-req-email-min'),
            'email.unique' => Lang::get($this->langFile . '.error-req-email-unique'),
            'password.required' => Lang::get($this->langFile . '.error-req-password'),
            'password.min' => Lang::get($this->langFile . '.error-req-password-min'),
            'password.password' => Lang::get($this->langFile . '.error-req-password-min'),
            'password.confirmed' => Lang::get($this->langFile . '.error-req-confirmed'),
            'first_name.required' => Lang::get($this->langFile . '.error-req-first-name'),
            'last_name.required' => Lang::get($this->langFile . '.error-req-last-name'),
            'mobile.required' => Lang::get($this->langFile . '.error-req-mobile'),
            't_and_c.required' => Lang::get($this->langFile . '.error-req-t_and_c'),
            'captcha.required' => Lang::get($this->langFile . '.error-req-captcha'),
            'captcha.captcha' => Lang::get($this->langFile . '.error-invalid-captcha'),
        ]);

        if (!$checkGuestEmail)
        {
            $user = new User();
        }
        else
        {
            $user = $checkGuestEmail;
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->username = $request->email;
        $user->login_type = 'website';
        $user->is_active = 1;
        $user->mobile = $request->mobile;
        $userSelectedLanguage = Helper::getLanguage();
        if ($userSelectedLanguage)
        {
            $user->locale = $userSelectedLanguage->id;
        }
        if ($user->save())
        {
            $user->assignRole('user');
            Auth::login($user);
            $user = Auth::user();
            if ($user)
            {
                $isCartUpdated = $this->addToCart();

                /*
                 * Send Registration mail to User
                 */
                $language = Helper::getLanguage();
                $contactEmail = Helper::getSettingById(19);
                $admin_mail = $contactEmail ? $contactEmail->setting_val : "";
                $app_name = Helper::getAppName();

                $from['email'] = $admin_mail;
                $from['name'] = $app_name;
                $to['email'] = $user->email;
                $to['name'] = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
                $subject = $app_name . "-" . __('web.thank_you_for_registering');
                $message = \View::make('web.emails.register.index', compact('user'))->render();


                $phpMail = new \App\Mail\CommonMailSender();

                $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);

                if ($isCartUpdated)
                {
                    return redirect()->route('web.cart.index');
                }
            }

            if (isset($request->url))
            {
                $url = session()->get('previous_url');
                unset($url);
                return redirect()->to($request->url);
            }
            return redirect()->route('web.index')->with('success', Lang::get($this->langFile . '.register-message'));
        }
        else
        {
            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function google_login()
    {
        if (!Auth::user())
        {
            return Socialite::driver('google')->redirect();
        }
        else
        {
            return redirect()->route('web.index');
        }
    }

    public function google_login_callback()
    {
        $social_user = Socialite::driver('google')->stateless()->user();
        $user = User::where(['social_id' => $social_user->id, 'login_type' => 'google'])->first();
        if ($user)
        {
            if ($user->is_active == 1)
            {
                Auth::login($user);
                $user = Auth::user();
                if ($user)
                {
                    $userSelectedLanguage = Helper::getLanguage();
                    if ($userSelectedLanguage)
                    {
                        $user->locale = $userSelectedLanguage->id;
                    }
                    $user->login_type = 'google';
                    $user->last_login = date("Y-m-d H:i:s");
                    $user->save();

                    $isCartUpdated = $this->addToCart();

                    if ($isCartUpdated)
                    {
                        return redirect()->route('web.cart.index');
                    }

                    if (session()->get('previous_url'))
                    {
                        $prev_url = session()->get('previous_url');
                        $url = session()->get('previous_url');
                        unset($url);
                        return redirect()->to($prev_url);
                    }

                    return redirect()->route('web.index');
                }
            }
            else
            {
                return redirect()->route('web.user.login')->with('error', \Lang::get($this->langFile . '.account-disable'));
            }
        }
        else
        {
            $user = User::where('email', $social_user->email)->first();

            if (!$user)
            {
                $user = new User();
                $user->password = '';
            }
            $user->social_id = $social_user->id;
            $user->first_name = $social_user->name;
            $user->last_name = '';
            $user->mobile = '';
            $user->email = $social_user->email;
            $user->username = $social_user->email;
            $user->login_type = 'google';
            $userSelectedLanguage = Helper::getLanguage();
            if ($userSelectedLanguage)
            {
                $user->locale = $userSelectedLanguage->id;
            }
            $user->last_login = date("Y-m-d H:i:s");
            $user->save();

            $user->assignRole('user');



            Auth::login($user);
            $user = Auth::user();
            if ($user)
            {
                /*
                 * Send Registration mail to User
                 */

                $contactEmail = Helper::getSettingById(19);
                $admin_mail = $contactEmail ? $contactEmail->setting_val : "";
                $app_name = Helper::getAppName();

                $from['email'] = $admin_mail;
                $from['name'] = $app_name;
                $to['email'] = $user->email;
                $to['name'] = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
                $subject = $app_name . "-" . __('web.thank_you_for_registering');

                $message = \View::make('web.emails.register.index', compact('user'))->render();

                $phpMail = new \App\Mail\CommonMailSender();
                $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);

                $isCartUpdated = $this->addToCart();

                if ($isCartUpdated)
                {
                    return redirect()->route('web.cart.index');
                }

                if (session()->get('previous_url'))
                {
                    $prev_url = session()->get('previous_url');
                    $url = session()->get('previous_url');
                    unset($url);
                    return redirect()->to($prev_url);
                }
            }
            return redirect()->route('web.index');
        }
    }

    public function facebook_login()
    {
        if (!Auth::user())
        {
            return Socialite::driver('facebook')->redirect();
        }
        else
        {
            return redirect()->route('web.user');
        }
    }

    public function facebook_login_callback()
    {
        $social_user = Socialite::driver('facebook')->user();
        if ($social_user->email)
        {
            $user = User::where(['email' => $social_user->email])->first();
        }
        else
        {
            $user = User::where(['social_id' => $social_user->id, 'login_type' => 'facebook'])->first();
        }
        if ($user)
        {
            Auth::login($user);
            $user = Auth::user();
            if ($user->is_active == 1)
            {
                Auth::login($user);
                $user = Auth::user();
                if ($user)
                {
                    $userSelectedLanguage = Helper::getLanguage();
                    if ($userSelectedLanguage)
                    {
                        $user->locale = $userSelectedLanguage->id;
                    }
                    $user->login_type = 'facebook';
                    $user->last_login = date("Y-m-d H:i:s");
                    $user->save();
                    $isCartUpdated = $this->addToCart();

                    if ($isCartUpdated)
                    {
                        return redirect()->route('web.cart.index');
                    }

                    if (session()->get('previous_url'))
                    {
                        $prev_url = session()->get('previous_url');
                        $url = session()->get('previous_url');
                        unset($url);
                        return redirect()->to($prev_url);
                    }
                    return redirect()->route('web.index');
                }
            }
            else
            {
                return redirect()->route('web.user.login')->with('error', \Lang::get($this->langFile . '.account-disable'));
            }
        }
        else
        {
            if (!$user)
            {
                $user = new User();
            }
            $user->social_id = $social_user->id;
            $user->first_name = $social_user->name;
            $user->last_name = '';
            $user->password = '';
            $user->mobile = '';
            $user->email = $social_user->email;
            $user->username = $social_user->email;
            $user->login_type = 'facebook';
            $userSelectedLanguage = Helper::getLanguage();
            if ($userSelectedLanguage)
            {
                $user->locale = $userSelectedLanguage->id;
            }
            $user->last_login = date("Y-m-d H:i:s");
            $user->save();
            $user->assignRole('user');
            Auth::login($user);
            $user = Auth::user();
            if ($user)
            {
                /*
                 * Send Registration mail to User
                 */

                $contactEmail = Helper::getSettingById(19);
                $admin_mail = $contactEmail ? $contactEmail->setting_val : "";
                $app_name = Helper::getAppName();

                $from['email'] = $admin_mail;
                $from['name'] = $app_name;
                $to['email'] = $user->email;
                $to['name'] = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
                $subject = $app_name . "-" . __('web.thank_you_for_registering');

                $message = \View::make('web.emails.register.index', compact('user'))->render();

                $phpMail = new \App\Mail\CommonMailSender();
                $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);

                $isCartUpdated = $this->addToCart();

                if ($isCartUpdated)
                {
                    return redirect()->route('web.cart.index');
                }

                if (session()->get('previous_url'))
                {
                    $prev_url = session()->get('previous_url');
                    $url = session()->get('previous_url');
                    unset($url);
                    return redirect()->to($prev_url);
                }
            }
            return redirect()->route('web.user');
        }
    }

    /*
      public function addToCart()
      {
      $user        = Auth::user();
      $sessioncart = session()->get('cart');
      if ($sessioncart && count($sessioncart) > 0)
      {
      dd($sessioncart);
      $cart                  = ShoppingCart::where('user_id', $user->id)->delete();
      $cartdetail            = ShoppingCartDetail::where('user_id', $user->id)->delete();
      $Shoppingcart          = new ShoppingCart();
      $Shoppingcart->user_id = $user->id;
      $Shoppingcart->save();
      foreach ($sessioncart as $key => $value)
      {
      $product = AdminProduct::find($key);
      if ($product)
      {
      $Shoppingcartdetail             = new ShoppingCartDetail();
      $Shoppingcartdetail->user_id    = $user->id;
      $Shoppingcartdetail->product_id = $key;
      $Shoppingcartdetail->quantity   = $value['quantity'];
      $Shoppingcartdetail->note       = $value['note'];
      $Shoppingcartdetail->save();
      }
      }
      Session::forget('cart');
      return true;
      }
      return false;
      }
     * 
     */

    public function addToCart()
    {
        $user = Auth::user();
        $sessioncart = [];
        $sessioncartid = '';
        if (session()->get('cart'))
        {

            $sessioncartid = session()->get('cart');
        }


        if ($sessioncartid)
        {
            $cart = ShoppingCart::where('user_id', $user->id)->delete();
            $cartdetail = ShoppingCartDetail::where('user_id', $user->id)->delete();
            $sessioncart = ShoppingCartDetail::where('shopping_cart_id', $sessioncartid)->get()->toArray();

            $Shoppingcart = new ShoppingCart();
            $Shoppingcart->user_id = $user->id;
            $Shoppingcart->save();


            foreach ($sessioncart as $key => $value)
            {
                $product = AdminProduct::find($value['product_id']);
                if ($product)
                {
                    $Shoppingcartdetail = new ShoppingCartDetail();
                    $Shoppingcartdetail->shopping_cart_id = $Shoppingcart->id;
                    $Shoppingcartdetail->user_id = $user->id;
                    $Shoppingcartdetail->product_id = $value['product_id'];
                    $Shoppingcartdetail->quantity = $value['quantity'];
                    $Shoppingcartdetail->size_id = $value['size_id'];
                    $Shoppingcartdetail->color_id = $value['color_id'];
                    $Shoppingcartdetail->note = $value['note'];
                    $Shoppingcartdetail->is_monogram = 0;
                    $Shoppingcartdetail->is_same = $value['is_same'];
                    $sessioncartmonogram = MonogramShoppingCartDetail::where('shopping_cart_detail_id', $value['id'])->get()->toArray();
                    if (count($sessioncartmonogram) > 0)
                    {
                        $Shoppingcartdetail->is_monogram = $value['is_monogram'];
                    }
                    if ($Shoppingcartdetail->save())
                    {

                        foreach ($sessioncartmonogram as $record)
                        {
                            $monogrmShoppingcartdetail = new MonogramShoppingCartDetail();
//                            $monogrmShoppingcartdetail->is_monogram         = $record['is_monogram'];
                            $monogrmShoppingcartdetail->shopping_cart_detail_id = $Shoppingcartdetail->id;
                            $monogrmShoppingcartdetail->font = $record['font'];
                            $monogrmShoppingcartdetail->initials_count = $record['initials_count'];
                            $monogrmShoppingcartdetail->left_text = $record['left_text'];
                            $monogrmShoppingcartdetail->center_text = $record['center_text'];
                            $monogrmShoppingcartdetail->right_text = $record['right_text'];
                            $monogrmShoppingcartdetail->text4 = $record['text4'];
                            $monogrmShoppingcartdetail->monogram_color = $record['monogram_color'];
                            $monogrmShoppingcartdetail->monogram_color_hash = $record['monogram_color_hash'];
                            $monogrmShoppingcartdetail->monogram_color_id = $record['monogram_color_id'];
                            $monogrmShoppingcartdetail->placement = $record['placement'];
                            $monogrmShoppingcartdetail->monogram_note = $record['monogram_note'];

                            $monogrmShoppingcartdetail->save();
                        }
                    }
                }
            }

            Session::forget('cart');
            return true;
        }
        return false;
    }

    public function guestLogin(Request $request)
    {
        $validate = $request->validate([
            'gEmail' => 'required|min:6',
                ], [
            'gEmail.required' => Lang::get($this->langFile . '.error-req-email'),
            'gEmail.min' => Lang::get($this->langFile . '.error-req-email-min'),
            'gEmail.unique' => Lang::get($this->langFile . '.error-req-email-unique'),
        ]);
        $user = User::Where('email', $request->gEmail)->first();
        if ($user)
        {
        
             if($user->id == config('config.store_admin_id'))
             {
              return redirect()->route('web.user.login')->with('error', 'You cannot login as guest using this email');
             }
            if ($user->is_active == 1)
            {
                $userSelectedLanguage = Helper::getLanguage();
                if ($userSelectedLanguage)
                {
                    $user->locale = $userSelectedLanguage->id;
                }
                $user->last_login = date("Y-m-d H:i:s");
                $user->login_type = 'guest';
                $user->save();
                Auth::login($user);
            }
            else
            {
                return redirect()->route('web.user.login')->with('error', \Lang::get($this->langFile . '.account-disable'));
            }
        }
        elseif (!$user)
        {
            $user = new User();
            $user->first_name = 'Guest';
            $user->last_name = 'User';
            $user->password = Hash::make('123456');
            $user->mobile = '';
            $user->email = $request->gEmail;
            $user->username = $request->gEmail;
            $user->login_type = 'guest';
            $userSelectedLanguage = Helper::getLanguage();
            if ($userSelectedLanguage)
            {
                $user->locale = $userSelectedLanguage->id;
            }
            $user->last_login = date("Y-m-d H:i:s");
            $user->save();
            $user->assignRole('user');
            Auth::login($user);
        }
        else
        {
            $user = null;
        }


        if ($user)
        {

            $this->addToCart();

            if (session()->get('previous_url'))
            {
                $prev_url = session()->get('previous_url');

                return redirect()->to($prev_url);
            }
            return redirect()->route('web.cart.index');
        }
        else
        {
            return redirect()->route('web.user.login')->with('error', \Lang::get($this->langFile . '.email-exist'));
        }
    }

    public function forotPassword(Request $request)
    {
        $validate = $request->validate([
            'femail' => 'required|min:6',
                ], [
            'femail.required' => Lang::get($this->langFile . '.error-req-email'),
            'femail.min' => Lang::get($this->langFile . '.error-req-email-min'),
        ]);
        $user = user::where('email', $request->femail)->first();
       
        if ($user)
        {
            if (false)
            {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.fp_guest_user'));
            }
            else
            {
                $rand = rand(10000, 99999);
                $user->password_reset_token = $rand;
                if ($user->save())
                {
                    $this->sendMail($user, $rand);
                    return redirect()->route('web.user.login')->with('email_suceess', Lang::get($this->langFile . '.reset-password-link-message'));
                }
            }
        }
        else
        {
            return redirect()->route('web.user.login')->with('email_error', Lang::get($this->langFile . '.email_not_exist'));
        }
    }

    public function resetPasswordPage(Request $request)
    {
        if ($request->id && $request->token)
        {
            $user_id = $request->id;

            $token = $request->token;
            $user = user::where('id', $user_id)->where('password_reset_token', $token)->first();
            if ($user)
            {

                return view('web.user.reset_password', compact('user_id', 'token'));
            }
            else
            {
                return redirect()->route('web.user.login')->with('error', Lang::get($this->langFile . '.token_not_exist'));
            }
        }
        else
        {
            return redirect()->route('web.user.login')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function resetPassword(Request $request)
    {
        $validate = $request->validate([
            'password' => 'required|confirmed|min:6',
            'id' => 'required',
            'token' => 'required',
                ], [
            'password.required' => Lang::get($this->langFile . '.error-req-password'),
            'password.min' => Lang::get($this->langFile . '.error-req-password-min'),
            'password.password' => Lang::get($this->langFile . '.error-req-password-min'),
            'password.confirmed' => Lang::get($this->langFile . '.error-req-confirmed'),
        ]);
        $user = user::where('id', $request->id)->where('password_reset_token', $request->token)->first();
        if ($user)
        {
            $user->password_reset_token = '';
            $user->password = bcrypt($request->password);
            if ($user->save())
            {

                return redirect()->route('web.user.login')->with('success', Lang::get($this->langFile . '.password-reset-success'));
            }
            else
            {
                return redirect()->route('web.user.login')->with('error', Lang::get($this->langFile . '.error-message'));
            }
        }
    }

    public function sendMail($user, $token)
    {

        $language = \App\Helpers\Helper::getLanguage();
        $contactEmail = \App\Helpers\Helper::getSettingById(19);
        $admin_mail = $contactEmail ? $contactEmail->setting_val : "";
        $app_name = \App\Helpers\Helper::getAppName();

        $link = route('web.user.reset_password', ['id' => $user->id, 'token' => $token]);

        $from['email'] = $admin_mail;
        $from['name'] = $app_name;
        $to['email'] = $user->email;
        $to['name'] = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
        $subject = $app_name . "-Forgot Password";
        $message = \View::make('web.emails.reset_password.index', compact('link', 'user'))->render();
        //echo $message;
        //die;
        
        $phpMail = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);
        if ($mailResponse['status'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}

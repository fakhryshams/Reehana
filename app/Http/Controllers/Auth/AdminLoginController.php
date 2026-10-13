<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Lang;
use Auth;
use App\Admin;
use App\Helpers\Helper;

class AdminLoginController extends Controller
{

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    private $langFile     = 'admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
        $this->username = $this->findUsername();
    }

    public function showLoginForm()
    {
        $url = url()->previous();
        return view('auth.login', compact('url'));
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email'    => 'required',
            'password' => 'required',
                ], [
            'email.required'    => Lang::get($this->langFile . '.error-req-email'),
            'password.required' => Lang::get($this->langFile . '.error-req-email'),
        ]);

//       dd($this->username);
//        if ($this->hasTooManyLoginAttempts($request))
//        {
//            $this->fireLockoutEvent($request);
//
//            return $this->sendLockoutResponse($request);
//        }

        $remember_me = $request->remember_me ? true : false;

        if (Auth::guard('admin')->attempt([$this->username => $request->email, 'password' => $request->password], $remember_me))
        {
            if (isset($request->url))
            {
                return redirect()->to($request->url)->with('success', Lang::get($this->langFile . '.login-success'));
            }
            return redirect()->route('admin.home.index')->with('success', Lang::get($this->langFile . '.login-success'));
        }
        else
        {
            $this->incrementLoginAttempts($request);
            return redirect()->route('admin.login')->with('error', Lang::get($this->langFile . '.login_fail'));
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->logout())
        {

            return redirect()->route('admin.login')->with('success', Lang::get($this->langFile . '.logout_success'));
        }
        else
        {
            return redirect()->route('admin.home.index');
        }
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername()
    {
        $login = request()->input('email');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required',
                ], [
            'email.required' => Lang::get($this->langFile . '.error-req-email-reset'),
        ]);
        $user          = Admin::where('email', $request->email)->first();
        if ($user)
        {
            $rand = rand(10000, 99999);
            $rand = $rand . strtotime(date("Y-m-d H:i:s A"));

            $user->password_reset_token = $rand;

            if ($user->save())
            {
                $this->sendMail($user, $rand);
                return redirect()->route('admin.login')->with('success', Lang::get($this->langFile . '.reset-password-link-message'));
            }
        }
        else
        {
            return redirect()->route('admin.login')->with('error', Lang::get($this->langFile . '.email_not_exist'));
        }
    }

    public function sendMail($user, $token)
    {
        $language   = Helper::getLanguage();
        $senderMail = Helper::getSettingById(19);
        $senderMail = $senderMail ? $senderMail->setting_val : "";
        $appName    = Helper::getAppName();
        if ($senderMail)
        {
            $link = route('admin.reset-password', ['id' => $user->id, 'token' => $token]);

            $from['email'] = $senderMail;
            $from['name']  = $appName;
            $to['email']   = $user->email;
            $to['name']    = ucfirst($user->name);
            $subject       = $appName . "-" . Lang::get($this->langFile . '.forgot-password-title');
            $message       = view('auth.reset-password.index', compact('link', 'user'))->render();

            $phpMail = new \App\Mail\CommonMailSender();

            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false);
            if ($mailResponse['status'])
            {
                return true;
            }
        }
        return false;
    }

    public function resetPasswordPage(Request $request)
    {
        if ($request->id && $request->token)
        {
            $userId = $request->id;

            $token = $request->token;
            $user  = Admin::where('id', $userId)->where('password_reset_token', $token)->first();
            if ($user)
            {
                return view('auth.reset-password', compact('userId', 'token'));
            }
            else
            {
                return redirect()->route('admin.login')->with('error', Lang::get($this->langFile . '.token_not_exist'));
            }
        }
        else
        {
            return redirect()->route('admin.login')->with('error', Lang::get($this->langFile . '.error'));
        }
    }

    public function resetPassword(Request $request)
    {
        $validate = $request->validate([
            'password' => 'required|confirmed|min:6',
            'id'       => 'required',
            'token'    => 'required',
                ], [
            'password.required'  => Lang::get($this->langFile . '.error-req-password'),
            'password.min'       => Lang::get($this->langFile . '.error-req-password-min'),
            'password.password'  => Lang::get($this->langFile . '.error-req-password-min'),
            'password.confirmed' => Lang::get($this->langFile . '.error-req-confirmed'),
        ]);

        $user = Admin::where('id', $request->id)->where('password_reset_token', $request->token)->first();
        if ($user)
        {
            $user->password_reset_token = '';
            $user->password             = bcrypt($request->password);
            if ($user->save())
            {
                return redirect()->route('admin.login')->with('success', Lang::get($this->langFile . '.password-reset-success'));
            }
            else
            {
                return redirect()->route('admin.login')->with('error', Lang::get($this->langFile . '.error'));
            }
        }
    }

}

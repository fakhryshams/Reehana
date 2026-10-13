<?php

namespace App\Http\Controllers\Admin\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use App\UserModel\Order; // Will Remove
use App\AdminModel\Order\WebOrder;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\AdminCustomizedForm;
use App\AdminModel\AdminContactUs;
use App\AdminModel\Product\Wishlist;
use App\User;
use App\Admin;
use App\AdminModel\Product\AdminProductAttribute;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.profile';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $dashboard     = [];
        $defaultStatus = Helper::getOrderDefaultStatus();
        $orderStatus   = config('order.status');
        $currency      = Helper::getDefaultCurrency();
        $badgeColor    = config('order.badge');

        /*
         * Bind Dashboard Array
         * 
         */
        $poutoutofstock = 0;
        $prod =  AdminProductAttribute::where('quantity', 0)->orWhere('quantity', NULL)->where('deleted_at', null)->pluck('products_id');

        if (count($prod) > 0) {
            $poutoutofstock = AdminProduct::whereIn('id',$prod)->count();
        }
        $dashboard['newOrder']            = WebOrder::where('order_status', $defaultStatus)->count();
        $dashboard['completedOrder']      = WebOrder::where('order_status', $orderStatus['delivered'])->count();
        $dashboard['totalMoneyEarned']    = WebOrder::where('order_status', $orderStatus['delivered'])->sum('order_total');
        $dashboard['totalCustomer']       = User::all()->count();
        $dashboard['outOfStock']          = AdminProduct::where('product_quantity', 0)->orWhere('product_quantity', NULL)->count();
        $dashboard['poutOfStock']          =  $poutoutofstock;

        $dashboard['totalProduct']        = AdminProduct::all()->count();
        $dashboard['newCustomers']        = User::getNewCustomers();
        $dashboard['newOrders']           = WebOrder::getNewOrders($defaultStatus);
        $dashboard['recentProduct']       = AdminProduct::getProductData();
        $dashboard['totalCustomizedForm'] = AdminCustomizedForm::all()->count();
        $dashboard['totalContactForm']    = AdminContactUs::all()->count();
        $dashboard['totalWishList']       = Wishlist::all()->count();

        return view('index', compact('dashboard', 'currency', 'badgeColor', 'defaultStatus', 'orderStatus'));
    }

    /*
     * Logged user profile
     */

    public function profile()
    {
        return view('admin.home.profile');
    }

    public function editProfile()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.home.edit_profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validate = $request->validate([
            'name'            => 'required|min:3|max:50',
            'profile_picture' => 'image|mimes:jpeg,png,jpg'
        ], [
            'name.required'         => Lang::get($this->langFile . '.error-profile-req-name'),
            'profile_picture.image' => Lang::get($this->langFile . '.error-profile-file-type'),
            'profile_picture.mimes' => Lang::get($this->langFile . '.error-profile-file-mimes'),
        ]);
        $user     = Auth::guard('admin')->user();

        if ($request->file('profile_picture')) {
            /*
             * Unlink Previous Image

              if (Auth::user()->profile_picture)
              {
              $usersImage = public_path("/images/profile/" . Auth::user()->profile_picture); // get previous image from folder
              if (File::exists($usersImage))
              { // unlink or remove previous image from folder
              unlink($usersImage);
              }
              }
             */

            $path = Storage::disk('local')->put('public/profile', $request->profile_picture);

            if ($path) {
                Storage::disk('local')->delete($user->profile_picture);
                $user->profile_picture = $path;
            }
        }
        $user->name = $request->name;

        if ($user->save()) {
            $status       = 'success';
            $message_text = Lang::get($this->langFile . '.update-success');
            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
            return redirect()->route('admin.home.profile')->with($status, $message_text);
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
            return redirect()->route('admin.home.edit-profile')->with($status, $message_text);
        }
    }

    /*
     *  Change password
     */

    public function changePassword()
    {
        $id   = Auth::guard('admin')->user()->id; //Login user id
        $user = Admin::find($id);

        if ($user) {
            return view('admin.home.change_password', ['data' => $user]);
        } else {
            return redirect()->route('admin.home.profile')->with('error', 'Record not found');
        }
    }

    /*
     * Update password
     */

    public function updatePassword(Request $request)
    {
        $validate = $request->validate([
            'password'              => 'required|confirmed|min:6',
            'current_password'      => 'required',
            'password_confirmation' => '',
        ], [
            'password.required'              => Lang::get($this->langFile . '.error-profile-req-new_password'),
            'current_password.required'      => Lang::get($this->langFile . '.error-profile-req-current_password'),
            'password.confirmed'             => Lang::get($this->langFile . '.error-profile-confirm-confirm_password'),
            'password_confirmation.required' => Lang::get($this->langFile . '.error-profile-req-confirm_password'),
            'password.min'                   => Lang::get($this->langFile . '.error-profile-min-password'),
        ]);

        //        dd($request->current_password);
        $user = Admin::find(Auth::guard('admin')->user()->id);
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('admin.home.change-password')
                ->withErrors(['current_password' => Lang::get($this->langFile . '.error-profile-wrong-current_password')]);
        }

        $user->password = bcrypt($request->password);

        $this->module = ".password";

        if ($user->save()) {
            $status       = 'success';
            $message_text = Lang::get($this->langFile . '.update-success');
            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.home.profile')->with($status, $message_text);
    }
}

<?php

namespace App\Http\Controllers\Web\User;

use App\AdminModel\AdminCountry;
use App\AdminModel\City\AdminCity;
use App\Http\Controllers\Controller;
use App\User;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use App\UserModel\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Redirect;
use Session;

class UserController extends Controller {

	private $module = '.checkout';
	private $langFile = 'web';

	public function __construct() {

	}

	/**
	 * Show the checkout .
	 */
	public function index() {
		return view('web.checkout.index');
	}

	/**
	 * Show the profile page .
	 */
	public function profile() {
		$country = AdminCountry::getCountry(1);
		return view('web.user.profile', compact('country'));
	}

	public function updateProfile(Request $request) {
		$user = Auth::user();
		$validate = $request->validate([
			'email' => "required|unique:users,email,$user->id,id,deleted_at,NULL|min:6",
			'first_name' => 'required',
			'last_name' => 'required',
			'mobile' => 'required',
			'birth_date' => 'required|date',
		], [
			'email.required' => Lang::get($this->langFile . '.error-req-email'),
			'email.min' => Lang::get($this->langFile . '.error-req-email-min'),
			'email.unique' => Lang::get($this->langFile . '.error-req-email-unique'),
			'first_name.required' => Lang::get($this->langFile . '.error-req-first-name'),
			'last_name.required' => Lang::get($this->langFile . '.error-req-last-name'),
			'mobile.required' => Lang::get($this->langFile . '.error-req-mobile'),
			'birth_date.required' => Lang::get($this->langFile . '.error-req-dob'),
			'birth_date.date' => Lang::get($this->langFile . '.error-date-dob'),
		]);

		$date = $request->birth_date;
		//$date             = str_replace('/', '-', $date);
		$date = date("Y-m-d", strtotime($date));
		$user->first_name = $request->first_name;
		$user->last_name = $request->last_name;
		$user->email = $request->email;
		$user->username = $request->email;
		$user->birth_date = $date;
		$user->country = $request->country;
		$user->is_active = 1;
		$user->mobile = $request->mobile;
		if ($user->save()) {
			$user->assignRole('user');
			return redirect()->route('web.user.profile')->with('profile_success', Lang::get($this->langFile . '.profile-update-message'));
		} else {
			return redirect()->route('web.user.profile')->with('profile_error', Lang::get($this->langFile . '.error-message'));
		}
	}

	/**
	 * Show the my order page .
	 */
	public function myOrder() {
		$user = Auth::user();
		if ($user) {
			$cancel_status = config('custom_web.cancel', 5);
			$exclude_staus = config('custom_web.exclude_staus');
			$orders = Order::where('user_id', $user->id)->whereNotIn('order_status', $exclude_staus)->orderBy('created_at', 'desc')->withTrashed()->get();
			$cancel = Order::where('user_id', $user->id)->where('order_status', $cancel_status)->orderBy('created_at', 'desc')->withTrashed()->get();
			return view('web.user.my_order', compact('orders', 'cancel'));
		} else {
			return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
		}
	}

	public function orderDetail($id) {
		$user = Auth::user();

		if ($id && $user) {
			$order = Order::where('id', $id)->where('user_id', $user->id)->withTrashed()->first();
			if ($order) {
				$address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
				return view('web.user.order_detail', compact('order', 'address'));
			} else {
				return redirect()->route('web.index')->with('error', \Lang::get($this->langFile . '.order-not-found'));
			}
		} else {
			return redirect()->route('web.index')->with('error', \Lang::get($this->langFile . '.error-message'));
		}
	}

	/**
	 * Show the wishlist page .
	 */
	public function wishlist() {
		return view('web.user.wishlist');
	}

	/**
	 * Show the my Address page .
	 */
	public function myAddress() {
		$user = Auth::user();
		$country = AdminCountry::getCountry(1);
		$address = UserAddress::where('user_id', $user->id)->get();
		return view('web.user.my_address', compact('country', 'address'));
	}

	public function saveAddress(Request $request) {

		$validationRule = [
			'email' => "required|min:6",
			'address_title' => 'required',
			'first_name' => 'required',
			'last_name' => 'required',
			'country_id' => 'required',
			'city_id' => 'required_if:is_city,0',
			'city' => 'required_if:is_city,1',
			'street' => 'required',
//           'area'                 => 'required',
			'block' => 'required',
			'house_number' => 'required',
			'apartment' => 'nullable',
			'phone' => 'required|numeric',
			// 'pin_code'             => 'required',
		];

		$validationMessage = [
			'email.required' => Lang::get($this->langFile . '.error-req-email'),
			'email.min' => Lang::get($this->langFile . '.error-req-email-min'),
			'first_name.required' => Lang::get($this->langFile . '.error-req-first-name'),
			'last_name.required' => Lang::get($this->langFile . '.error-req-last-name'),
			'phone.required' => Lang::get($this->langFile . '.error-req-phone'),
			'country_id.required' => Lang::get($this->langFile . '.error-req-country'),
			'city_id.required_if' => Lang::get($this->langFile . '.error-req-city'),
			'city.required_if' => Lang::get($this->langFile . '.error-req-city'),
			'street.required' => Lang::get($this->langFile . '.error-req-street'),
			'area.required' => Lang::get($this->langFile . '.error-req-area'),
			'house_number.required' => Lang::get($this->langFile . '.error-req-house-number'),
			'apartment.required' => Lang::get($this->langFile . '.error-req-apartment'),
			'phone.required' => Lang::get($this->langFile . '.error-req-phone'),
			'address_title.required' => Lang::get($this->langFile . '.error-req-address-title'),
			'pin_code.required' => Lang::get($this->langFile . '.error-req-address-pin-code'),
			// --------billing address ---------
			'billing_first_name.required_if' => Lang::get($this->langFile . '.error-req-first-name'),
			'billing_last_name.required_if' => Lang::get($this->langFile . '.error-req-last-name'),
			'billing_phone.required_if' => Lang::get($this->langFile . '.error-req-phone'),
			'billing_country_id.required_if' => Lang::get($this->langFile . '.error-req-country'),
			'billing_city_id.required_if' => Lang::get($this->langFile . '.error-req-city'),
			'billing_city.required_if' => Lang::get($this->langFile . '.error-req-city'),
			'billing_street.required_if' => Lang::get($this->langFile . '.error-req-street'),
			'billing_area.required_if' => Lang::get($this->langFile . '.error-req-area'),
			'billing_house_number.required_if' => Lang::get($this->langFile . '.error-req-house-number'),
			'billing_apartment.required_if' => Lang::get($this->langFile . '.error-req-apartment'),
		];
		if ($request->is_myaddress == '0') {

			//------- billing address ---------
			$validationRule['billing_first_name'] = 'required_if:check_billing_address,2';
			$validationRule['billing_last_name'] = 'required_if:check_billing_address,2';
			$validationRule['billing_country_id'] = 'required_if:check_billing_address,2';
			$validationRule['billing_city_id'] = 'required_if:is_billing_city,0';
			$validationRule['billing_city'] = 'required_if:is_billing_city,1';
			$validationRule['billing_street'] = 'required_if:check_billing_address,2';
//            'billing_area' = 'required_if:check_billing_address,2';
			$validationRule['billing_house_number'] = 'required_if:check_billing_address,2';
//            'billing_appartment'   => 'required_if:check_billing_address,2',
			$validationRule['billing_phone'] = 'required_if:check_billing_address,2';
			$validationRule['pEmail'] = 'required_if:check_billing_address,2';
		}
		$validatedData = $request->validate($validationRule, $validationMessage);

		$address = new UserAddress();
		$user = Auth::user();
		if ($request->address_id) {
			$address = $address->where('id', $request->address_id)->where('user_id', $user->id)->first();
		} else {
			$address->is_billing_address = 1;
			
			  UserAddress::where('user_id', $user->id)->update(['is_default' => 0]);
            		 $address->is_default = 1;
		}
		$city = $request->city;
		$city_id = $request->city_id;
//----- billing city

		$address->user_id = $user->id;
		$address->address_title = $request->address_title;
		$address->first_name = $request->first_name;
		$address->last_name = $request->last_name;
		$address->email = $request->email;
		$address->country_id = $request->country_id;
		if ($city_id) {
			$city = null;
		}
		if ($city) {
			$city_id = null;
		}
		$address->city_id = $city_id;
		$address->city = $city;
//        $address->area = $request->area;
		$address->block = $request->block;
		$address->street = $request->street;
		$address->house_number = $request->house_number;
		$address->floor = $request->floor;
		$address->appartment = $request->apartment;
		$address->address_detail = $request->address_detail;
		$address->address_url = $request->address_url;
// $address->pin_code = $request->pin_code;
		$address->phone = $request->phone;
		/*
			         * Billing Address
		*/
		if (isset($request->check_billing_address) && $request->check_billing_address != 3 && $request->is_myaddress == "0") {
			/*
				             * If $request->check_billing_address == 3, then it come from Profile Address
			*/
			$billing_city = $request->billing_city;
			$billing_city_id = $request->billing_city_id;
			$address->billing_first_name = $request->billing_first_name;
			$address->billing_last_name = $request->billing_last_name;
			$address->billing_country_id = $request->billing_country_id;
			if ($billing_city_id) {
				$billing_city = null;
			}
			if ($billing_city) {
				$billing_city_id = null;
			}
			$address->billing_city_id = $billing_city_id;
			$address->billing_city = $billing_city;
//            $address->billing_area = $request->billing_area;
			$address->billing_block = $request->billing_block;
			$address->billing_street = $request->billing_street;
			$address->billing_phone = $request->billing_phone;
			$address->billing_house_number = $request->billing_house_number;
			$address->billing_floor = $request->billing_floor;
			$address->billing_appartment = $request->billing_appartment;
			$address->billing_address_detail = $request->billing_address_detail;
			$address->billing_email = $request->pEmail;

			if ($request->check_billing_address == 2) {
				$address->is_billing_address = 0;
			} else if ($request->check_billing_address == 1) {
				$address->is_billing_address = 1;
			}
		}

		$count = UserAddress::where('user_id', $user->id)->count();
		if ($count == 0) {
			$address->is_default = 1;
		}

		if ($address->save()) {
			$message = Lang::get($this->langFile . '.save-address-success');
			if ($request->address_id) {
				$message = Lang::get($this->langFile . '.update-address-success');
			}
			Session::flash('message', $message);

			return array('status' => true, 'message' => $message);
		} else {
			return array('status' => false, 'message' => Lang::get($this->langFile . '.error-message'));
		}
	}

	/**
	 * Show the change Password page .
	 */
	public function showChangePasswordForm() {
		return view('web.user.change_password');
	}

	public function changePassword(Request $request) {
		$validate = $request->validate([
			'old_password' => 'required|min:6',
			'password' => 'required|confirmed|min:6',
			'password_confirmation' => 'required|min:6',
		], [
			'old_password.required' => Lang::get($this->langFile . '.error-req-old-password'),
			'old_password.min' => Lang::get($this->langFile . '.error-req-old-password-min'),
			'password.min' => Lang::get($this->langFile . '.error-req-password-min'),
			'password.required' => Lang::get($this->langFile . '.error-req-password'),
			'password.confirmed' => Lang::get($this->langFile . '.error-req-confirmed'),
			'password_confirmation.required' => Lang::get($this->langFile . '.error-req-password-confirmation'),
			'password_confirmation.min' => Lang::get($this->langFile . '.error-req-password-confirmation-min'),
		]);
		$user = User::find(Auth::user()->id);
		if (!Hash::check($request->old_password, $user->password)) {
			return redirect()->route('web.user.change_password')
				->withErrors(['old_password' => Lang::get($this->langFile . '.error-profile-wrong-current_password')]);
		}
		$user->password = bcrypt($request->password);
		$this->module = ".password";
		if ($user->save()) {
			$status = 'success';
			$message_text = Lang::get($this->langFile . '.password-update-success');
			$message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
		} else {
			$status = 'error';
			$message_text = Lang::get($this->langFile . '.update-error');
			$message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
		}
		return redirect()->route('web.user.change_password')->with($status, $message_text);
	}

	function GetCity(Request $request) {
		if ($request->country_id) {
			$city = AdminCity::getCity($request->country_id, 1);
			if (count($city) > 0) {
				return view('web.user.city', compact('city'));
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getAddressDetail(Request $request) {
		$user = Auth::user();
		if ($request->address_id && $user) {
			$address = UserAddress::where('id', $request->address_id)->where('user_id', $user->id)->first();
			if ($address) {
				return $address;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function billingAddress() {
		$country = AdminCountry::getCountry(1);
		return view('web.layout.billing_address', compact('country'));
	}

	public function removeAddress(Request $request) {
		$user = Auth::user();
		$id = $request->id;
		if ($id && $user) {
			$findAddress = Order::where('shipping_address_id', $id)->first();

			// if (!$findAddress)
			if (true) {
				$address = UserAddress::where('user_id', $user->id)->where('id', $id)->first();

				if ($address->delete()) {
					$messsage = \Lang::get($this->langFile . '.address-remove-message');
					return array('status' => true, 'message' => $messsage);
				} else {
					$message = \Lang::get($this->langFile . '.error-message');

					return array('status' => false, 'message' => $messsage);
				}
			} else {
				$message = \Lang::get($this->langFile . '.address-error-message');
				return array('status' => false, 'message' => $message);
			}
		} else {
			$message = \Lang::get($this->langFile . '.no-record-message');
			return array('status' => false, 'message' => $messsage);
		}
	}

}

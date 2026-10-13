<?php

namespace App\Http\Controllers\Web\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\UserModel\UserAddress;
use App\AdminModel\AdminCountry;
use Auth;

class GuestController extends Controller
{

    private $module = '.guest';
    private $langFile = 'web';

    public function __construct()
    {
        
    }

    public function index()
    {
        $user = Auth::user();
        $cart = [];

        $language = \App\Helpers\Helper::getLanguage();
        //if user login get product from db
        if ($user)
        {
            $cart = \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->get();
        }
        //if user not login get product from session
        if (isset($_COOKIE['cart']))
        {
            $cart = json_decode($_COOKIE['cart'], true);
        }

        if ($cart && $user && $user->login_type == 'guest')
        {
            $country = AdminCountry::get();
            $address = UserAddress::where('user_id', $user->id)->get();
            return view('web.guest.address', compact('address', 'country'));
        }
        else
        {
            return redirect()->route('web.index');
        }
    }

    public function orderReview()
    {
        $user = Auth::user();
        $country = AdminCountry::get();
        $cart = [];
        $language = \App\Helpers\Helper::getLanguage();
        //if user login get product from db
        if ($user)
        {
            $cart = \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->get();
        }
        //if user not login get product from session
        if (isset($_COOKIE['cart']))
        {
            $cart = json_decode($_COOKIE['cart'], true);
        }

        $address = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
        if (count($cart) > 0 && $user->login_type == 'guest')
        {

            $total = 0;
            foreach ($cart as $id => $record)
            {
                if ($user)
                {
                    $id = $record->product_id;
                }
                $product = \App\AdminModel\Product\AdminProduct::find($id);
                $price = $product->product_sale_price ? $product->product_sale_price : $product->product_regular_price;
                $sub_total = $price * $record['quantity'];
                $total += $sub_total;
            }
            $tax = \App\Helpers\Helper::getTax();
            $taxval = ($total * $tax) / 100;
            $shipping_charge = 0;

            if ($user)
            {
                $useraddress = \App\UserModel\UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                if ($useraddress)
                {
                    $countryData = \App\AdminModel\AdminCountry::where('id', $useraddress->country_id)->first();
                }
                else
                {
                    $countryData = \App\AdminModel\AdminCountry::where('is_default', 1)->first();
                }
            }
            else
            {
                $countryData = \App\AdminModel\AdminCountry::where('is_default', 1)->first();
            }
            if ($countryData)
            {
                $shipping_charge = $countryData->shipping_charge;
            }
            $final_total = $total + $shipping_charge + $taxval;

            return view('web.guest.order_review', compact('address', 'country', 'cart', 'language', 'shipping_charge'));
        }
        else
        {

            return redirect()->route('web.index')->with('error', \Lang::get($this->langFile . '.cart-error-message'));
        }
    }

}

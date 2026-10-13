<?php

namespace App\Http\Controllers\Web\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminModel\AdminLanguage;
use App\AdminModel\GeneralSetting\HomeSlider\AdminHomeSlider;
use App\AdminModel\GeneralSetting\HomeCategorySection\HomeCategorySection;
use App\AdminModel\Product\AdminProduct;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use Session;
use Config;
use App;
use Auth;
use Helper;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\AdminCountry;
use App\AdminModel\AdminOurCollection;

use App\AdminModel\AdminDiscount;

use App\AdminModel\CategoryDiscount;
//use Endroid\QrCode\Color\Color;
//use Endroid\QrCode\Encoding\Encoding;
//use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
//use Endroid\QrCode\QrCode;
//use Endroid\QrCode\Label\Label;
//use Endroid\QrCode\Logo\Logo;
//use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
//use Endroid\QrCode\Writer\PngWriter;

//use Cart;

class HomeController extends Controller
{

    private $module = '.home';
    private $langFile = 'web';

    /**
     * Show the website home.
     */
    public function index()
    {

    
    
   //$from['email'] = 'info@reehana.com';
         $from['name']  = 'Reehana';
//$to['email']   = 'uditprajapati9791@gmail.com';
       //  $to['name']    = 'udit prajapati';

     //    $cc['email'] = 'uditprajapati9791@gmail.com';

     //   $subject      = 'reehana';
     //    $message      = 'reehna  mail is working';
// //        echo $message;die;
    //  $phpMail      = new \App\Mail\CommonMailSender();
     //    $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        
//dd($mailResponse);
    
    

        $language = Helper::getLanguage();
        $adminHomeSlider = AdminHomeSlider::getHomeSliderData();
        $collections = AdminOurCollection::where('is_active', 1)->get();
        // $ourCategory      = HomeCategorySection::getAllData();

        $currency = Helper::getDefaultCurrency();
        $newArrival = AdminProduct::getNewArrivalsHomePage();
        //dd($newArrival);
        return view('web.home.index', compact('adminHomeSlider', 'language', 'newArrival', 'currency', 'collections'));
    }

    public function changeLanguage($code, Request $request)
    {
        $language = AdminLanguage::where('code', $code)->first();
        $locale = Config::get('app.locale');
        if ($language)
        {
            $request->session()->put('locale', $language->code);
            Session::put('locale', $language->code);
            $locale = $language->code;

            $loginUser = Auth::user();
            if ($loginUser)
            {
                $loginUser->locale = $language->id;
                $loginUser->save();
            }
        }
        App::setLocale($locale);
        return back();
    }

    public function changeCurrency($id, Request $request)
    {
        $country = AdminCountry::find($id);
        if ($country)
        {
            $request->session()->put('currency', $country->id);
            Session::put('currency', $country->id);
        }
        return back();
    }

    public function getLatestOrder(Request $request)
    {
        /*
         * Return
         */
        $productName = "";
        $productImage = "";
        $userName = "";

        if ($request->ajax())
        {
            $languageId = $request->language_id;
        }

        $activeProductId = AdminProduct::where('is_active', 1)->pluck('id')->toArray();
        $orderDetail = OrderDetail::inRandomOrder()->whereIn('product_id', $activeProductId)->first();

        if ($orderDetail)
        {
            $productId = AdminProduct::find($orderDetail->product_id);
            if ($productId)
            {
                $productDefaultImage = $productId->getDefaultImage();
                $productImage = asset(Storage::url($productDefaultImage->image_thumb_url));
                $productDescription = $productId->getProductDescription($languageId);
                if ($productDescription)
                {
                    $productName = ucfirst($productDescription->products_name);
                }
            }
            $order = Order::find($orderDetail->order_id);
            if ($order)
            {
                $user = App\User::find($order->user_id);
                if ($user)
                {
                    $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
                }
            }
        }

        if ($productName && $productImage && $userName)
        {
            $response = [
                'product' => $productName,
                'image' => $productImage,
                'user' => $userName,
                'url' => route('web.product.product_detail', ['slug' => $productId->products_slug])
            ];
            return \View('web.home.latest-order', compact('response'))->render();
        }
        return false;
    }
    
    
      public function checkExpiredDiscounts()
    {
        $currentDate = date('Y-m-d');

        $expiredDiscounts = AdminDiscount::where('end_date', '<', $currentDate)
            ->where('is_discount', 1) // Corrected column name
            ->get();

        foreach ($expiredDiscounts as $discount) {
            $productIds = [];

            if ($discount->type == 1) { // Brand discounts
                $brandIds = CategoryDiscount::where('discount_id', $discount->id)->pluck('brand_id')->toArray();
                $productIds = AdminProduct::whereIn('brand_id', $brandIds)->pluck('id')->toArray();
            } elseif ($discount->type == 2) { // Category discounts
                $categoryIds = CategoryDiscount::where('discount_id', $discount->id)->pluck('category_id')->toArray();
                $productIds = AdminProduct::whereIn('category_id', $categoryIds)->pluck('id')->toArray();
            } elseif ($discount->type == 3) { // Both brand and category discounts
                $categoryDiscounts = CategoryDiscount::where('discount_id', $discount->id)->get();
                foreach ($categoryDiscounts as $cd) {
                    $productIds = array_merge($productIds, AdminProduct::where('brand_id', $cd->brand_id)
                        ->where('category_id', $cd->category_id)
                        ->pluck('id')
                        ->toArray());
                }
            }

            // Update products to set is_category_discount to 0
            AdminProduct::whereIn('id', $productIds)->update(['is_category_discount' => 0]);

            // Mark discount as inactive
            $discount->is_discount = 0;
            $discount->save();
        }
    }

}

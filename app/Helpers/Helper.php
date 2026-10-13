<?php

namespace App\Helpers;

use App\AdminModel\AdminCountry;
use App\AdminModel\AdminCurrency;
use App\AdminModel\AdminLanguage;
use App\AdminModel\AdminSettingDescription;
use App\AdminModel\AdminSettings;
use App\AdminModel\Color\AdminColor;
use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Product\AdminProductMonogram;
use App\AdminModel\Product\Wishlist;
use App\AdminModel\Size\AdminSize;
use App\AdminModel\Size\AdminSizeDescription;
use App\AdminModel\Zone\AdminZone;
use App\AdminModel\Zone\AdminZoneArea;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\PaymentMethod;
use App\UserModel\UserAddress;
use Auth; //use IP2LocationLaravel class
use Illuminate\Support\Facades\Storage;
use IP2LocationLaravel;
use Session;
use App\AdminModel\AdminDiscount;
use App\AdminModel\CategoryDiscount;
use App\AdminModel\Product\AdminProductCategory;

class Helper
{

    public static function getMailLogo()
    {
        return asset("mail_logo/logo.png");
    }

    public static function getOrderColumn($request, $defaultKey = "", $defaultVal = "")
    {
        $sortKey      = $defaultKey ? $defaultKey : "id";
        $sortVal      = $defaultVal ? $defaultVal : "asc";
        $requestParam = $request->except('q');
        foreach ($requestParam as $key => $val) {
            if ($val == 'asc' || $val == 'desc') {
                $sortKey = $key;
                $sortVal = $val;
                break;
            }
        }

        return [
            'sortKey' => $sortKey,
            'sortVal' => $sortVal,
        ];
    }

    public static function getOrderSortingColumn($request)
    {
        $sortKey      = "sorting";
        $sortVal      = "asc";
        $requestParam = $request->except('q');
        foreach ($requestParam as $key => $val) {
            if ($val == 'asc' || $val == 'desc') {
                $sortKey = $key;
                $sortVal = $val;
                break;
            }
        }

        return [
            'sortKey' => $sortKey,
            'sortVal' => $sortVal,
        ];
    }

    public static function getOrderSortingColumn2($request)
    {
        $sortKey      = "new_arrival_soting";
        $sortVal      = "asc";
        $requestParam = $request->except('q');
        foreach ($requestParam as $key => $val) {
            if ($val == 'asc' || $val == 'desc') {
                if ($key == 'sorting') {
                    $sortKey = 'new_arrival_soting';
                } else {
                    $sortKey = $key;
                }
                $sortVal = $val;
                break;
            }
        }

        return [
            'sortKey' => $sortKey,
            'sortVal' => $sortVal,
        ];
    }

    public static function getSetting()
    {
        return AdminSettings::all();
    }

    public static function getBrand($user = null)
    {
        if (!isset($user)) {
            $user = auth()->user();
        }
        if (isset($user) && !empty($user->id) && $user->id == '7') {
            $brandIds = AdminProduct::where('product_quantity', '>', 0)->whereNotNull('brand_id')->pluck('brand_id');
        } else {
            $brandIds = AdminProduct::where('is_active', 1)->where('is_active_category', 1)->whereNotNull('brand_id')->pluck('brand_id');
        }


        if (count($brandIds) > 0) {
            $brandIds = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $brandIds)->orderBy('brand_name', 'ASC')->pluck('brand_id')->toArray();

            $record = implode(",", $brandIds);
            return \App\AdminModel\Brand\AdminBrand::where('is_active', 1)->whereIn('id', $brandIds)->orderByRaw("FIELD(id, $record)")->get();
        }
        return [];
    }

    public static function getSettingById($id, $languageID = false)
    {
        $record = AdminSettings::find($id);

        if ($record && $languageID && $record->is_multi_lang) {
            return AdminSettingDescription::where('settings_id', $id)->where('language_id', $languageID)->first();
        } else {
            return $record;
        }
    }

    public static function getAppName($languageID = 1)
    {
        $record = AdminSettings::find(1);

        if ($record && $languageID && $record->is_multi_lang) {
            $record = AdminSettingDescription::where('settings_id', $record->id)->where('language_id', $languageID)->first();
            return $record ? $record->setting_val : config('app.name');
        } else if ($record) {
            return $record ? $record->setting_val : config('app.name');
        }

        return config('app.name');
    }

    public static function getAppLogo($isEmailLogo = false)
    {
        return asset('images/logo.png');

        if ($isEmailLogo) {
            $record = AdminSettings::find(25);
        } else {
            $record = AdminSettings::find(2);
        }

        if ($record) {
            return $record = asset(Storage::url($record->setting_val));
        } else {
            return asset(Storage::url('default.png'));
        }
    }

    public static function getCustomerCareEmail()
    {
        $record = AdminSettings::find(29);

        if ($record) {
            return $record->setting_val;
        }
        return "";
    }

    public static function getContactUsImage()
    {
        $record = AdminSettings::find(24);

        if ($record) {
            return $record = asset(Storage::url($record->setting_val));
        } else {
            return asset(Storage::url('default.png'));
        }
    }

    public static function getUserProfilePicture()
    {
        return Auth::user()->profile_picture ? asset(Storage::url(Auth::user()->profile_picture)) : asset(Storage::url('profile/user.png'));
    }

    public static function getFavIcon()
    {
        $record = AdminSettings::find(3);

        if ($record) {
            return $record = asset(Storage::url($record->setting_val));
        }
        return '';
    }

    public static function getLanguageList()
    {
        return AdminLanguage::getLanguageList();
    }

    public static function slugify($slug)
    {
        // replace non letter or digits by -
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);

        // transliterate
        if (function_exists('iconv')) {
            $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        }

        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        // trim
        $slug = trim($slug, '-');

        // remove duplicate -
        $slug = preg_replace('~-+~', '-', $slug);

        // lowercase
        $slug = strtolower($slug);

        if (empty($slug)) {
            return 'n-a';
        }

        return $slug;
    }

    public static function getTopName()
    {
        /*
         * Get Default Lang if available
         */
        $languageID = Helper::getLanguage();
        $languageID = $languageID ? $languageID->id : '';

        $record = AdminSettings::find(8);
        if ($record && $languageID && $record->is_multi_lang) {
            $record = AdminSettingDescription::where('settings_id', $record->id)->where('language_id', $languageID)->first();
            return $record ? $record->setting_val : config('custom_web.top_name');
        } else if ($record) {
            return $record ? $record->setting_val : config('custom_web.top_name');
        }
        return config('custom_web.top_name');
    }

    public static function getWebFooterAppName()
    {
        /*
         * Get Default Lang if available
         */
        $languageID = Helper::getLanguage();
        $languageID = $languageID ? $languageID->id : '';

        $record = AdminSettings::find(21);
        if ($record && $languageID && $record->is_multi_lang) {
            $record = AdminSettingDescription::where('settings_id', $record->id)->where('language_id', $languageID)->first();
            return $record ? $record->setting_val : config('app.name');
        } else if ($record) {
            return $record ? $record->setting_val : config('app.name');
        }
        return config('app.name');
    }

    public static function getWebFooterCompanyName()
    {
        /*
         * Get Default Lang if available
         */
        $languageID = Helper::getLanguage();
        $languageID = $languageID ? $languageID->id : '';

        $record = AdminSettings::find(22);
        if ($record && $languageID && $record->is_multi_lang) {
            $record = AdminSettingDescription::where('settings_id', $record->id)->where('language_id', $languageID)->first();
            return $record ? $record->setting_val : config('custom_web.company_name');
        } else if ($record) {
            return $record ? $record->setting_val : config('custom_web.company_name');
        }
        return config('custom_web.company_name');
    }

    public static function getFrontHeader($filteredCategory = "", $showIn = "", $filterType = "", $price = "", $color = "", $search = "", $size = "", $brand = "", $mainCategoryId = false, $searchedProductCategoryIds = false, $user = null)
    {

        $categoryId = "";
        if ($filteredCategory) {
            $categoryId = AdminCategory::find($filteredCategory);

            if ($categoryId->parent_id) {
                $categoryId = $categoryId->parent_id;
            } else {
                $categoryId = $categoryId->id;
            }
        }


        $category = AdminCategory::getCategoriesFront($categoryId, [], $showIn, $filterType, $price, $color, $search, $size, $brand, $mainCategoryId, $searchedProductCategoryIds, $user);
        return $category;
    }

    public static function getCategoryDetails($parentId)
    {
        return AdminCategory::find($parentId);
    }

    /*
     * Front Side Filter
     */

    public static function getColorFilter($filterType, $categoryFilter, $price, $size, $search = "", $brand = "", $mainCategoryId = false, $user = null)
    {


        return $filter = AdminProduct::getColorFilter($filterType, $categoryFilter, $price, $size, $search, $brand, $mainCategoryId, $user);
    }

    public static function getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search = "", $brand = "", $mainCategoryId = false, $user = null)
    {
        $minMax = AdminProduct::getPriceMinMax($filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId, $user);

        if ($minMax && isset($minMax->min_price) && isset($minMax->max_price)) {
            $currency       = Helper::getDefaultCurrency();
            $conversionRate = 1;
            if ($currency) {
                $conversionRate = $currency->value;
            }
            $min = floor($minMax->min_price);
            $max = $minMax->max_price;

            $difference = substr($min, -1);
            $min        = $min - $difference;

            $interval = 10;
            $filter   = [];
            $loop     = 0;
            for ($i = $min; $i <= $max + $interval; $i = $i + $interval) {
                $lowerRange = $i;
                $upperRange = $i + $interval;

                if ($lowerRange <= $max) {
                    $minMaxcount = AdminProduct::getPriceRangeCount($lowerRange, (Helper::formatNumber($upperRange - 0.001)), $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId, $user);

                    if ($minMaxcount) {
                        if ($currencyCode != 'KWD') {
                            $filter[$loop]['title'] = $currencyCode . " " . (($lowerRange < 1 ? $minMax->min_price : $lowerRange) * $conversionRate) . "  to " . $currencyCode . " " . ($upperRange * $conversionRate);
                        } else {
                            $filter[$loop]['title'] = $currencyCode . " " . (($lowerRange < 1 ? $minMax->min_price : $lowerRange) * $conversionRate) . "  to " . $currencyCode . " " . $upperRange * $conversionRate;
                        }

                        $filter[$loop]['value'] = $lowerRange . "-" . $upperRange;
                        //                        $filter[$loop]['count'] = AdminProduct::getPriceRangeCount($lowerRange, ($upperRange - 1));
                        $filter[$loop]['count'] = $minMaxcount;
                        $loop++;
                    }
                }
            }
            return $filter;
        }
        return [];
    }

    public static function getSizeFilter($filterType, $categoryFilter, $price, $color, $search = "", $brand = "", $mainCategoryId = false)
    {
        return AdminSize::getSize($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
    }

    public static function getBrandFilter($filterType, $categoryFilter, $price, $color, $search = "", $size, $mainCategoryId = false, $user = null)
    {
        return $filter = AdminProduct::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId, $user);
    }

    /*
     * ./Front Side Filter
     */

    /*
     * Cart
     */

    public static function getShoppingCart($userID = '')
    {
        if ($userID) {
            $activeProduct = ShoppingCartDetail::where('user_id', $userID)->pluck('product_id')->toArray();
            if ($activeProduct) {
                $activeProduct = AdminProduct::whereIn('id', $activeProduct)->pluck('id')->toArray();
                if (!$activeProduct) {
                    return [];
                }
                return ShoppingCartDetail::where('user_id', $userID)->whereIn('product_id', $activeProduct)->get();
            }
            return [];
        } else {
            $cart = session()->get('cart');
            if (isset($cart)) {
                $activeProduct = ShoppingCartDetail::where('shopping_cart_id', $cart)->pluck('product_id')->toArray();

                if ($activeProduct) {
                    $activeProduct = AdminProduct::whereIn('id', $activeProduct)->pluck('id')->toArray();
                    if (!$activeProduct) {
                        return [];
                    }
                    return ShoppingCartDetail::where('shopping_cart_id', $cart)->whereIn('product_id', $activeProduct)->get();
                }
            }
            return [];
        }
    }

    public static function getProductDetail($productID)
    {
        $user = auth()->user();
        $query = AdminProduct::buildStoreUserAwareQuery($user)->where('id', $productID);
        $query = AdminProduct::applyActiveCondition($query, $user);
        return $query->first();
    }

    public static function getSize($sizeID, $languageID)
    {
        return AdminSizeDescription::where('size_id', $sizeID)->where('language_id', $languageID)->first();
    }

    public static function getColorName($colorID, $languageID)
    {
        return \App\AdminModel\Color\AdminColorDescription::where('color_id', $colorID)->where('language_id', $languageID)->first();
    }

    /*
     * /.Cart
     */

    public static function getActiveRoute()
    {
        $segments    = request()->segments();
        $lastSegment = is_array($segments) ? end($segments) : '';
        if ($lastSegment) {
            $categoryId = AdminCategory::where('categories_slug', $lastSegment)->first();
            return $categoryId;
        }
        return false;
    }

    public static function paginationDropdown()
    {
        return [10, 25, 50, 100, 'ALL'];
    }

    public static function changeOrder($model, $sorting, $order, $isNewArrival = false)
    {
        $currentSorting = [];
        foreach ($sorting as $k => $v) {
            $currentSorting[] = $v['sorting_id'];
        }

        if ($order == 'asc') {
            sort($currentSorting);
        } else {
            rsort($currentSorting);
        }

        $i = 0;

        foreach ($sorting as $v) {
            $updateOrder = $model->find($v['id']);
            if ($isNewArrival && $model instanceof \App\AdminModel\Product\AdminProduct) {
                $updateOrder->new_arrival_soting = $currentSorting[$i];
            } else {
                $updateOrder->sorting = $currentSorting[$i];
            }
            $updateOrder->save();
            $i++;
        }
        return true;
    }

    public static function getDefaultLanguage()
    {
        return $defaultLang = AdminLanguage::where(['is_default' => 1])->first();
    }

    public static function getLanguage()
    {
        if (Session::get('locale')) {
            $language = Session::get('locale');
        } else {
            $language = Config::get('app.locale');
        }

        $languagedata = AdminLanguage::where('code', $language)->first();

        if ($languagedata) {
            return $languagedata;
        }
        return false;
    }

    public static function getWishlistCount()
    {
        $count = 0;
        $user  = Auth::user();
        if ($user) {
            $count = Wishlist::where('user_id', $user->id)->count();
        }
        return $count;
    }

    public static function getCategory()
    {
        $categoryList = AdminCategory::where('parent_id', '!=', 0)->where('is_active', 1)->orderBy('sorting', 'asc')->get();
        return $categoryList;
    }

    public static function getColor()
    {
        $colorList = AdminColor::where('is_active', 1)->orderBy('sorting', 'asc')->get();
        return $colorList;
    }

    public static function getTax()
    {
        return 0;
        /*
          $record = AdminSettings::find(9);
          if ($record->setting_val)
          {
          return $record = $record->setting_val;
          }
          else
          {
          return 0;
          }
         *
         */
    }

    public static function getInstagramUrl()
    {
        $record = AdminSettings::find(6);

        if ($record) {
            return $record = $record->setting_val;
        } else {
            return false;
        }
    }

    public static function getInstagramUrl2()
    {
        $record = AdminSettings::where('setting_key', 'Instagram URL 2')->first();

        if ($record && $record->setting_val) {
            return $record->setting_val;
        }
        return false;
    }

    public static function getWhatsappUrl()
    {
        $record = AdminSettings::find(7);
        if ($record) {
            return $record = $record->setting_val;
        } else {
            return false;
        }
    }

    public static function getEmail()
    {
        $record = AdminSettings::find(5);
        if ($record) {
            return $record = $record->setting_val;
        } else {
            return false;
        }
    }

    public static function getOrderDefaultStatus()
    {
        $status = AdminOrderStatus::where('is_default', 1)->first();
        if ($status) {
            $status = $status->id;
        } else {
            $status = AdminOrderStatus::first()->id;
        }
        return $status;
    }

    public static function getDefaultCurrency()
    {
        /*
         * Call function to update user location/currency - On each and every requestt
         */
        Helper::updateCurrencyByCountry();

        $currency = AdminCurrency::where(['is_default' => 1, 'is_current' => 1])->first();
        if ($currency) {
            $selectedCurrency = Helper::getCurrency();
            if ($selectedCurrency) {
                /*
                 * Set Currency Code
                 */
                if ($selectedCurrency->currency_code) {
                    $currency->code = $selectedCurrency->currency_code;
                }
                /*
                 * Set Conversion Rate
                 */
                if ($selectedCurrency->currency_rate) {
                    $currency->value = $selectedCurrency->currency_rate;
                } else {
                    $currency->value = 1;
                }
            }
            return $currency;
        }
        return false;
    }

    public static function getPaymentMethod()
    {
        $paymentMethod = PaymentMethod::where('is_active', 1)->get();
        if ($paymentMethod) {
            return $paymentMethod;
        }
        return false;
    }

    public static function getPaymentMethodByID($id, $languageId = 1)
    {
        $language      = Helper::getLanguage();
        $paymentMethod = PaymentMethod::find($id);
        if ($paymentMethod) {
            $paymentMethod = $paymentMethod->getDescription($languageId);
            return $paymentMethod ? $paymentMethod->payment_method_name : '';
        }
        return false;
    }

    public static function isCod()
    {
        // 17 is for cod
        $cod = AdminSettings::find(17);
        if ($cod) {
            return $cod->setting_val;
        }
        return false;
    }

    public static function formatNumber($number, $decimalPlace = 3)
    {
        return number_format((float) $number, $decimalPlace, '.', '');
    }

    public static function sendMailFrom()
    {
        $record = AdminSettings::find(19);
        return $record ? $record->setting_val : false;
    }

    public static function getCartProductStatus($user, $productId, $sizeExist, $size, $colorExist, $color)
    {
        $status = true;

        if ($user) {
            $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $productId);
            if ($colorExist && $sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                }
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();
                if ($shoppingCartDetail) {
                    $status = false;
                }
            } elseif ($sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $status = false;
                    }
                }
            } elseif ($colorExist) {
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $status = false;
                    }
                }
            } else {
                $shoppingCartDetail = $shoppingCartDetail->first();
                if ($shoppingCartDetail) {
                    $status = false;
                }
            }
        } else {
            $cart = session()->get('cart');
            if (!isset($cart)) {
                $cart = 0;
            }
            $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $productId);
            if ($colorExist && $sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                }
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();
                if ($shoppingCartDetail) {
                    $status = false;
                }
            } elseif ($sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $status = false;
                    }
                }
            } elseif ($colorExist) {
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $status = false;
                    }
                }
            } else {
                $shoppingCartDetail = $shoppingCartDetail->first();
                if ($shoppingCartDetail) {
                    $status = false;
                }
            }
        }

        return $status;
    }

    public static function getProductQuantity($productId, $sizeExist, $size, $colorExist, $color)
    {
        $count = 0;

        //        dd($sizeExist , $colorExist);
        if ($colorExist && $sizeExist) {
            $attribute = AdminProductAttribute::where(['products_id' => $productId, 'is_active' => 1]);
            if ($size) {

                $attribute->where(['size_id' => $size]);
            }
            if ($color) {

                $attribute->where(['color_id' => $color]);
            }
            $attribute = $attribute->first();
            if ($attribute) {

                $count = $attribute->quantity;
            }
        } elseif ($sizeExist) {
            if ($size) {
                $attribute = AdminProductAttribute::where(['products_id' => $productId, 'size_id' => $size, 'is_active' => 1])->first();
                if ($attribute) {
                    $count = $attribute->quantity;
                }
            }
        } elseif ($colorExist) {
            if ($color) {
                $attribute = AdminProductAttribute::where(['products_id' => $productId, 'color_id' => $color, 'is_active' => 1])->first();
                if ($attribute) {
                    $count = $attribute->quantity;
                }
            }
        } else {
            $product = AdminProduct::find($productId);
            if ($product) {
                $count = $product->product_quantity;
            }
        }

        return $count;
    }

    public static function getDefaultCountry()
    {
        $user = Auth::user();
        if ($user) {
            $useraddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
            if ($useraddress) {

                return AdminCountry::where('id', $useraddress->country_id)->first();
            } else {
                return AdminCountry::where('is_default', 1)->first();
            }
        } else {
            return AdminCountry::where('is_default', 1)->first();
        }
    }

    public static function renderCartView()
    {
        return ['view' => \View::make('web.product.cart')->render()];
    }

    public static function getCategoryDataHome($id)
    {
        $record = \App\AdminModel\GeneralSetting\HomeCategorySection\HomeCategorySection::find($id);
        return $record ? $record->setting_val : '';
    }

    public static function showAddress($page, $record, $isBilling = false)
    {
        return \View::make('web.layout.show_address', compact('page', 'record', 'isBilling'))->render();
    }

    public static function checkIntlShippingStatus($countryId, $allowIntlShipping)
    {
        $status = true;
        if (!$allowIntlShipping) {
            $defaultCountry = \App\AdminModel\AdminCountry::where(['is_default' => 0, 'id' => $countryId])->first();
            if ($defaultCountry) {
                $status = false;
            }
        }
        return $status;
    }

    public static function getCommonReturnExchangePolicy($languageID = 1)
    {
        $record = AdminSettings::find(30);

        if ($record && $languageID) {
            if ($record->is_multi_lang) {
                $record = AdminSettingDescription::where('settings_id', $record->id)->where('language_id', $languageID)->first();
            }
            return $record ? $record->setting_val : "";
        }
        return "";
    }

    public static function checkProductExistCart($productId)
    {
        $status  = false;
        $product = AdminProduct::find($productId);
        if ($product) {
            $user = Auth::user();
            if ($user) {
                $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $product->id)->first();
                if ($shoppingCartDetail) {
                    $status = true;
                }
            } else {
                $cart    = session()->get('cart');
                $cartKey = $product->id . "_";
                if (isset($cart[$cartKey])) {
                    $status = true;
                }
            }
        }
        return $status;
    }

    public static function getBrowserName()
    {
        $u_agent  = $_SERVER['HTTP_USER_AGENT'];
        $bname    = 'Unknown';
        $platform = 'Unknown';
        $version  = "";
        $ub       = "Unknown";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub    = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub    = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub    = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub    = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub    = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub    = "Netscape";
        }

        // finally get the correct version number
        $known   = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        $systemInfo = Helper::systemInfo();
        return $systemInfo['device'] . " " . $systemInfo['os'] . " - " . $bname;
    }

    public static function systemInfo()
    {
        $user_agent  = $_SERVER['HTTP_USER_AGENT'];
        $os_platform = "Unknown OS Platform";
        $os_array    = array(
            '/windows phone 8/i'    => 'Windows Phone 8',
            '/windows phone os 7/i' => 'Windows Phone 7',
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile'
        );
        $found       = false;
        $device      = '';
        foreach ($os_array as $regex => $value) {
            if ($found) {
                break;
            } else if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
                $device      = !preg_match('/(windows|mac|linux|ubuntu)/i', $os_platform) ? 'Mobile' : (preg_match('/phone/i', $os_platform) ? 'Mobile' : 'Web');
            }
        }
        $device = !$device ? 'Web' : $device;
        return array('os' => $os_platform, 'device' => $device);
    }

    public static function isExistSaleProduct()
    {
        return AdminProduct::where('is_active', 1)
            ->where(function ($query) {
                $query->where('is_product_sale_price_null', 0)
                    ->orWhere('is_category_discount', 1);
            })
            ->count();
    }

    public static function isExistNewArrivalProduct()
    {
        return AdminProduct::where(['is_new_arrival' => 1, 'is_active' => 1])->count();
    }

    public static function printPageRecord($record)
    {
        return \View::make('common.table_number_record', compact('record'))->render();
    }

    public static function checkCouponStatus($couponCode, $user)
    {
        $response = [
            'status'  => false,
            'message' => __('web.coupon_invalid'),
        ];

        if ($couponCode) {
            $couponCodeModel = new \App\UserModel\CouponCode();

            $couponCode = $couponCodeModel->validateCouponCode($couponCode, $user);
            if ($couponCode) {
                if ($couponCode->is_applied == 0) {
                    $response = [
                        'status'  => true,
                        'message' => __('web.coupon_applied'),
                        'data'    => $couponCode,
                    ];
                } else {
                    $response = [
                        'status'  => false,
                        'message' => __('web.coupon_already_redeemed'),
                    ];
                }
            }
        }
        return $response;
    }

    public static function getCountryList()
    {
        return AdminCountry::orderBy('currency_code', 'asc')->get();
    }

    public static function getCurrency()
    {
        if (Session::get('currency')) {
            $currencyId = Session::get('currency');
            $currency   = AdminCountry::find($currencyId);
            if (!$currency) {
                $currency = AdminCountry::where('is_default', 1)->first();
            }
        } else {
            $currency = AdminCountry::where('is_default', 1)->first();
        }
        return $currency;
    }

    public static function updateCurrencyByCountry()
    {
        $existingSession = Session::get('currency');
        if (!$existingSession) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            //$ipAddress != '::1' || $ipAddress != '127.0.0.1'
            if (false) {
                $records = IP2LocationLaravel::get($ipAddress, 'bin');
                if (isset($records['countryCode']) && $records['countryCode'] != "-") {
                    $userContryCode   = strtoupper($records['countryCode']);
                    $userCurrencyCode = false;

                    /*
                     * Get Currency Code from Country Code via JSON File
                     */
                    $countryJson = file_get_contents(asset('json/currency.json'));
                    if ($countryJson) {
                        $countryJson = json_decode($countryJson, true);
                        if (isset($countryJson[$userContryCode])) {
                            $userCurrencyCode = $countryJson[$userContryCode];
                        }
                    }
                    $country = AdminCountry::where('currency_code', $userCurrencyCode)->first();
                    if ($country) {
                        Session::put('currency', $country->id);
                    } else {
                        $country = AdminCountry::where('is_default', 1)->first();
                        if ($country) {
                            Session::put('currency', $country->id);
                        }
                    }
                    return back();
                }
            }
        }
        return false;
    }

    public static function getCartProduct($user, $productId, $sizeExist, $size, $colorExist, $color, $monogramNo = '')
    {
        $status      = [];
        $cartproduct = [];

        if ($user) {
            $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $productId);
            if ($colorExist && $sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                }
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();

                if ($shoppingCartDetail) {
                    $cartproduct = $shoppingCartDetail->toArray();

                    $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                }
            } elseif ($sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $cartproduct = $shoppingCartDetail->toArray();

                        $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                    }
                }
            } elseif ($colorExist) {
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $cartproduct = $shoppingCartDetail->toArray();

                        $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                    }
                }
            } else {
                $shoppingCartDetail->whereNull('size_id');
                $shoppingCartDetail->whereNull('color_id');
                $shoppingCartDetail = $shoppingCartDetail->first();

                if ($shoppingCartDetail) {

                    $cartproduct = $shoppingCartDetail->toArray();

                    $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                }
            }

            if ($cartproduct) {
                return $cartproduct;
            } else {

                if ($size) {
                    $cartKey = $productId . '_' . $size . '_' . $color;
                }

                if (isset($_COOKIE['temp_monogram'])) {
                    $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);
                    if ($temp_monogram && isset($temp_monogram[$cartKey][$monogramNo])) {
                        return array('monogram' => $temp_monogram[$cartKey], 'is_monogram' => 1);
                    }
                }
            }
        } else {
            $cart = session()->get('cart');
            if (!isset($cart)) {
                $cart = 0;
            }
            $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $productId);

            if ($colorExist && $sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                }
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();

                if ($shoppingCartDetail) {
                    $cartproduct = $shoppingCartDetail->toArray();

                    $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                }
            } elseif ($sizeExist) {
                if ($size) {
                    $shoppingCartDetail->where('size_id', $size);
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $cartproduct = $shoppingCartDetail->toArray();

                        $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                    }
                }
            } elseif ($colorExist) {
                if ($color) {
                    $shoppingCartDetail->where('color_id', $color);
                    $shoppingCartDetail = $shoppingCartDetail->first();

                    if ($shoppingCartDetail) {
                        $cartproduct = $shoppingCartDetail->toArray();

                        $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                    }
                }
            } else {
                $shoppingCartDetail->whereNull('size_id');
                $shoppingCartDetail->whereNull('color_id');
                $shoppingCartDetail = $shoppingCartDetail->first();

                if ($shoppingCartDetail) {

                    $cartproduct = $shoppingCartDetail->toArray();

                    $cartproduct['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail['id'])->get()->toArray();
                }
            }

            if ($cartproduct) {
                return $cartproduct;
            } else {

                if ($size) {
                    $cartKey = $productId . '_' . $size . '_' . $color;
                }

                if (isset($_COOKIE['temp_monogram'])) {
                    $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);
                    if ($temp_monogram && isset($temp_monogram[$cartKey][$monogramNo])) {
                        return array('monogram' => $temp_monogram[$cartKey], 'is_monogram' => 1);
                    }
                }
            }
        }
        return $status;
    }

    public static function isMonogranm($productId)
    {

        $isMonogranm = AdminProductMonogram::where('product_id', $productId)->first();
        if ($isMonogranm) {
            return true;
        }
        return false;
    }

    public static function getMonogramStyle($style_id)
    {
        $style = \App\AdminModel\Monogram\MonogramFontStyle::where('id', $style_id)->first();
        if ($style) {
            return $style;
        }
    }

    public static function getMonogramColor($color_id)
    {
        $color = \App\AdminModel\Monogram\MonogramColor::where('id', $color_id)->first();
        if ($color) {
            return $color;
        }
    }

    public static function getPlacement($placemnet_id)
    {
        $placement = \App\AdminModel\Monogram\MonogramPlacement::where('id', $placemnet_id)->first();
        if ($placement) {
            return $placement;
        }
    }

    public static function getPlacementName($placemnet_id)
    {
        $placement = \App\AdminModel\Monogram\MonogramPlacement::where('id', $placemnet_id)->first();
        if ($placement) {
            return $placement->placement_name . ': ' . $placement->placement_size . '"';
        }
    }

    public static function getInitials($count, $left_text, $center_text, $right_text, $long_text)
    {
        $initials     = config('custom_web.initials_front');
        $count        = $initials[$count];
        $mongram_text = 'A-B-C';
        if ($count == 1) {
            $mongram_text = strtoupper($center_text);
        }
        if ($count == 2) {
            $mongram_text = strtoupper($left_text) . '-' . strtoupper($right_text);
        }
        if ($count == 3) {
            $mongram_text = strtoupper($left_text) . '-' . strtoupper($center_text) . '-' . strtoupper($right_text);
        }
        if ($count == 4) {
            $mongram_text = strtoupper($long_text);
        }
        return $mongram_text;
    }

    public static function getMonogramPrice()
    {
        $record = AdminSettings::find(34);
        if ($record) {
            return $record = $record->setting_val;
        } else {
            return 0;
        }
    }

    public static function getMonogram($product_id)
    {
        $hasMonogram = AdminProductMonogram::where('product_id', $product_id)->first();

        if ($hasMonogram) {
            return true;
        } else {
            return false;
        }
    }

    public static function getGiftMinPrice()
    {
        $record = AdminSettings::find(36);
        if ($record) {
            return $record = $record->setting_val;
        } else {
            return 0;
        }
    }

    public static function getZonePrice($area_id)
    {

        $zone = AdminZoneArea::where('area_id', $area_id)->first();
        if ($zone) {

            $zonePrice = AdminZone::where('id', $zone->zone_id)->first();
            return $zonePrice->delivery_charge;
        } else {
            return 0;
        }
    }

    public static function getShippingAmount($total, $shipping_charge)
    {

        $user          = Auth::user();
        $countryName = 'kuwait';

        if ($user) {

            $address = UserAddress::where('is_default', 1)->where('user_id', $user->id)->first();
            if (isset($address)) {
                $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                $countryName = $countryName ? $countryName->country_name : 'kuwait';
            }
        }
        if (isset($address)) {

            $freeShippingAmout = \App\AdminModel\AdminCountry::where('id', $address->country_id)->first();
            $freeShippingAmout = $freeShippingAmout ? $freeShippingAmout->free_shipping_amount : 0;
        } else {
            $freeShippingAmout = \App\AdminModel\AdminCountry::where('id', 2)->first();
            $freeShippingAmout = $freeShippingAmout ? $freeShippingAmout->free_shipping_amount : 0;
        }

        if (strtolower($countryName) == 'kuwait') {


            if ($total >= $freeShippingAmout && $freeShippingAmout != 0) {
                $shipping_charge = 0;
            }
        } else {
            if ($total >= $freeShippingAmout && $freeShippingAmout != 0) {
                $shipping_charge = 0;
            }
        }
        return $shipping_charge;
    }

    public static function getPickupStoreAddress()
    {
        $pickupStoreAddress = session('default_address');
        if ($pickupStoreAddress) {
            $address = \App\UserModel\UserAddress::where('id', $pickupStoreAddress)->first();
            if ($address) {
                return $address;
            }
        }
        return false;
    }


   public static function getCategoryDiscount($productID)
    {
        // Fetch all active discounts within the valid date range
        $activeDiscounts = AdminDiscount::where('is_active', 1)
            ->where('is_discount', 1)
            ->where('start_date', '<=', date('Y-m-d'))
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();

        // If no active discounts, return null
        if ($activeDiscounts->isEmpty()) {
            return null;
        }

        // Fetch the product details
        $product = AdminProduct::find($productID);

        // If the product is not found, return null
        if (!$product) {
            return null;
        }

        // Initialize the maximum discount to track the best discount
        $maxDiscount = null;

        foreach ($activeDiscounts as $discount) {
            // Get the associated type, brand, and category IDs from the CategoryDiscount table for this discount
            $discountType = $discount->type; // Type 1: Brand, Type 2: Category, Type 3: Brand and Category
            $discountBrands = CategoryDiscount::where('discount_id', $discount->id)
                ->pluck('brand_id')
                ->toArray();
            $discountCategories = CategoryDiscount::where('discount_id', $discount->id)
                ->pluck('category_id')
                ->toArray();

            // Handle the discount logic based on type
            if ($discountType == 1) {
                // Check with Brand only
                if (in_array($product->brand_id, $discountBrands)) {
                    $maxDiscount = max($maxDiscount ?? 0, $discount->discount);
                }
            } elseif ($discountType == 2) {
                // Check with Category only
                if (in_array($product->category_id, $discountCategories)) {
                    $maxDiscount = max($maxDiscount ?? 0, $discount->discount);
                }
            } elseif ($discountType == 3) {
                // Check with both Brand and Category (Brand is non-mandatory)
                $isBrandEligible = empty($discountBrands) || in_array($product->brand_id, $discountBrands);
                $isCategoryEligible = empty($discountCategories) || in_array($product->category_id, $discountCategories);

                if ($isCategoryEligible && $isBrandEligible) {
                    $maxDiscount = max($maxDiscount ?? 0, $discount->discount);
                }
            }
        }

        //dd($maxDiscount);
        // Return the maximum discount found, or null if none apply
        return $maxDiscount;
    }
    
    
    public static function isCategoryDiscount()
    {
        // Fetch all active discounts within the valid date range
        $activeDiscounts = AdminDiscount::where('is_active', 1)
            ->where('is_discount', 1)
            ->where('start_date', '<=', date('Y-m-d'))
            ->where('end_date', '>=', date('Y-m-d'))
            ->get();

        // If no active discounts, return null
        if ($activeDiscounts->isEmpty()) {
            return null;
        }

       
       
        foreach ($activeDiscounts as $discount) {
            // Get the associated type, brand, and category IDs from the CategoryDiscount table for this discount
            $discountType = $discount->type; // Type 1: Brand, Type 2: Category, Type 3: Brand and Category
            $discountBrands = CategoryDiscount::where('discount_id', $discount->id)
                ->pluck('brand_id')
                ->toArray();
            $discountCategories = CategoryDiscount::where('discount_id', $discount->id)
                ->pluck('category_id')
                ->toArray();

            // Handle the discount logic based on type
            if ($discountType == 1) {
                // Check with Brand only
                
                return $discountBrands;

            } elseif ($discountType == 2) {
                // Check with Category only
                return $discountCategories;

            } elseif ($discountType == 3) {
                // Check with both Brand and Category (Brand is non-mandatory)
                // $isBrandEligible = empty($discountBrands) || in_array($product->brand_id, $discountBrands);
                // $isCategoryEligible = empty($discountCategories) || in_array($product->category_id, $discountCategories);

                // if ($isCategoryEligible && $isBrandEligible) {
                //     $maxDiscount = max($maxDiscount ?? 0, $discount->discount);
                // }
            }
        }

       
    }

    public static function getGiftImage()
    {
        $record = AdminSettings::find(41);
        if ($record) {
            return asset(Storage::url($record->setting_val));
        } else {
            return asset('front/img/giftwrap.png');
        }
    }

    public static function getGiftBoxImage()
    {
        $record = AdminSettings::find(44);
        if ($record) {
            return asset(Storage::url($record->setting_val));
        } else {
            return asset('front/img/giftwrap.png');
        }
    }

    public static function getGiftBoxMinPrice()
    {
        $record = AdminSettings::find(46);
        if ($record) {
            return $record = $record->setting_val;
        } else {
            return 0;
        }
    }
}

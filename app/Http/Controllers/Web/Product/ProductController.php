<?php

namespace App\Http\Controllers\Web\Product;

use App\AdminModel\AdminCountry;
use App\AdminModel\Monogram\MonogramColor;
use App\AdminModel\Monogram\MonogramFontStyle;
use App\AdminModel\Monogram\MonogramLetterCount;
use App\AdminModel\Monogram\MonogramMaster;
use App\AdminModel\Monogram\MonogramPlacement;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductCategory;
use App\AdminModel\Product\AdminProductMonogram;
use App\AdminModel\Product\Wishlist;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\UserModel\Cart\MonogramShoppingCartDetail;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\Customized;
use App\UserModel\UserAddress;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Session;
use App\AdminModel\Product\ProductPlacement;;

class ProductController extends Controller
{

    private $module   = '.product';
    private $langFile = 'web';

    public function index(Request $request, $slug = '')
    {
        /*
         * Filter Page
         */
        $filterType         = "product";
        $color              = [];
        $size               = [];
        $brand              = [];
        $price              = [];
        $categoryFilter     = [];
        $category           = '';
        $filteredCategoryId = '';
        $slugClearFilter    = [];
        $search             = '';
        $showIn             = "";
        $mainCategoryId     = false;
        $isAll              = false;
        if (isset($request->isAll) && $request->isAll == 1) {
            $isAll          = true;
            $mainCategoryId = $request->sCId;
        }
        if ($slug) {
            $category = AdminCategory::where('categories_slug', $slug)->first();
            if ($category && $category->parent_id == 0) {
                $mainCategoryId     = $category->id;
                $showIn             = $category ? $category->show_in : '';
                $filteredCategoryId = $category->id;
                $subCategory        = $category->getChildFront($filteredCategoryId, $getIds             = true);
                if ($subCategory) {
                    $categoryFilter = $subCategory->toArray();
                }
                $categoryFilter[] = $filteredCategoryId;
            } else if ($category) {
                $mainCategoryId     = $category->parent_id;
                $showIn             = $category ? $category->show_in : '';
                $filteredCategoryId = $category ? $category->id : '';
                $categoryFilter[]   = $filteredCategoryId;
            }

            if ($category) {
                $parent   = $category->parent_id ? $category->parent_id : $category->id;
                $children = $category->getChildFront($parent, $getIds   = true);
                if ($children) {
                    $slugClearFilter = $children->toArray();
                }
                $slugClearFilter[] = $parent;
            }
        }

        if (isset($request->sCId) && !$mainCategoryId) {
            $mainCategoryId = $request->sCId;
        }
        //        dd($request->sCId);
        if ($request->search) {
            $search = $request->search;
        }

        
        if(isset($search))
        {
        
              $prId = '';
              $barcode ='';
            $barcodeSearch = AdminProduct::where('product_barcode',$search)->first();
          if(!$barcodeSearch)
          {
            $barcodeSearch = \App\AdminModel\Product\AdminProductAttribute::where('barcode', $search)->first();
             if($barcodeSearch)
             {
              $prId = $barcodeSearch->products_id;
                 $barcode = $barcodeSearch->barcode;
           
             }
          }
          else
          {
             $prId = $barcodeSearch->id;
              $barcode = $barcodeSearch->product_barcode;
          }
          if($barcodeSearch)
          {
              $barcodeproduct = AdminProduct::where('id',$prId)->first();
            return redirect()->route('web.product.product_detail',['slug' => $barcodeproduct->products_slug,'barcode'=> $barcode]);
		
          }
        
        }
        /*
         * Access get request and make filter as per URL
         */
        if ($isAll) {
            $categoryFilter = $slugClearFilter;
        } else if (isset($request->c) && $request->c) {
            if ($request->c != "clear") {
                $categoryFilter  = AdminCategory::whereIn('categories_slug', $request->c)->pluck('id')->toArray();
                $slugClearFilter = [];
            } else {
                $categoryFilter = $slugClearFilter;
            }
        } else {
            $slugClearFilter = [];
        }
        if (isset($request->s) && $request->s) {
            $size = \App\AdminModel\Size\AdminSizeDescription::whereIn('size_title', $request->s)->pluck('size_id')->toArray();
        }
        if (isset($request->cl) && $request->cl) {
            $color = \App\AdminModel\Color\AdminColorDescription::whereIn('color_title', $request->cl)->pluck('color_id')->toArray();
        }
        if (isset($request->b) && $request->b) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $request->b)->pluck('brand_id')->toArray();
        }
        if (isset($request->p) && $request->p) {
            $price = $request->p;
        }

        $sortBy = isset($request->sortby) ? $request->sortby : 'no';
 
        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');

        //        $mainCategoryId == false
        $categories  = Helper::getFrontHeader($filteredCategoryId, $showIn, $filterType, $price, $color, $search, $size, $brand, false);
   
        $filterColor = Helper::getColorFilter($filterType, $categoryFilter, $price, $size, $search, $brand, $mainCategoryId);
       
        
        $filterPrice = Helper::getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId);
         
        
         
        //        echo "<pre>";
        //        print_r($filterPrice);
        //        echo "</pre>";
        //        die;
        $filterSize  = Helper::getSizeFilter($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
        $filterBrand = Helper::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId);
        $redirectUrl = route('web.product.index', ['slug' => $slug]);

        $limit = config('custom_web.product_limit', 30);

        if ($request->page) {
            $limit = $limit * $request->page;
        }

        $appliedFilter = [
            'c'             => $request->c,
            'category'      => $categoryFilter,
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'search'        => $search,
            'isAll'         => $isAll,
            'subCategoryId' => $mainCategoryId,
            'brand'         => $brand,
            'sidebarSelectedCategoryId' => ($slug && isset($category) && $category) ? $mainCategoryId : null,
        ];

        /*
         * Product Listing
         */

      
 
        $products = AdminProduct::getProductByFilter($language, $appliedFilter, $limit);
     
        if (count($products) == 0 && $search) {
            return view('web.product.blank-search-result');
        }
        $productDatawithoutlimit    = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        $searchedProductCategoryIds = $productDatawithoutlimit->pluck('category_id');
        $searchedProductIds         = $productDatawithoutlimit->pluck('id');
        if ($searchedProductIds && $search) {
            $categories = Helper::getFrontHeader($filteredCategoryId, $showIn, $filterType, $price, $color, $search, $size, $brand, false, $searchedProductCategoryIds);
        } else {
            $searchedProductIds         = false;
            $searchedProductCategoryIds = false;
        }

        $productCount = count($productDatawithoutlimit);
        $pageLimit    = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) floor($productCount / $limit) + ($productCount % $limit);
        }
        
          $pageProductCount = count($products);
        
        
        //        dd($filterPrice);
        $filterPage = view('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'categoryFilter', 'appliedFilter', 'currency', 'filterBrand', 'searchedProductIds'));

        //      echo $filterPage;
        //        die;
        return view('web.product.index', compact('products', 'pageLimit', 'productDatawithoutlimit', 'filterPage', 'language', 'currencyCode', 'filteredCategoryId', 'categories', 'slug', 'filterType', 'redirectUrl', 'sortBy', 'category', 'search', 'currency', 'mainCategoryId', 'isAll', 'appliedFilter','productCount', 'pageProductCount'));
    }

    /*
     * Apply Filter-Ajax
     */

    public function applyFilter(Request $request)
    {
        $filterType      = isset($request->filterType) ? $request->filterType : '';
        $sortBy          = isset($request->sortby) ? (($filterType == 'sale' && $request->sortby == 'd') ? 'up' : $request->sortby) : ($filterType == 'sale' ? 'up' : 'd');
        $slug            = isset($request->slug) ? $request->slug : '';
        $category        = isset($request->category) ? $request->category : [];
        $color           = isset($request->color) ? $request->color : [];
        $size            = isset($request->size) ? $request->size : [];
        $brand           = isset($request->brand) ? $request->brand : [];
        $price           = isset($request->price) ? $request->price : [];
        $search          = isset($request->search) ? base64_decode($request->search) : "";
        $showIn          = isset($request->showIn) ? $request->showIn : "";
        $isAll           = false;
        $categoryId      = "";
        $categoryFilter  = [];
        $slugClearFilter = [];

        $mainCategoryId = false;

        if ($category && isset($category[0])) {
            $categoryAllData = explode("_", $category[0]);
            if (isset($categoryAllData[1])) {
                $category[0]             = 'all';
                $isAll                   = true;
                $request->category       = ['all'];
                $request->subCategoryId  = $categoryAllData[1];
                $request->mainCategoryId = $categoryAllData[1];
            }
        }

        if ($slug) {
            /*
             * Need to change here
             */
            if (isset($category[0]) && $category[0] != 'all') {
                $categoryId = $category[0];
            } else if (isset($request->mainCategoryId)) {
                $categoryId = $request->mainCategoryId;
            }
            if ($categoryId) {
                $categoryDetail = AdminCategory::find($categoryId);
                $showIn         = $categoryDetail ? $categoryDetail->show_in : "";
            }
            //            else
            //            {
            //                if (!$category)
            //                {
            //                    $categoryByTag = AdminCategory::whereIn('show_in', $slug)->pluck('id')->toArray();
            //                    if ($categoryByTag)
            //                    {
            //                        $category          = $request->category = $categoryByTag;
            //                    }
            //                }
            //                $showIn = $slug;
            //            }

            $categoryBySlug = AdminCategory::where('categories_slug', $slug)->first();

            if ($categoryBySlug) {
                $parent         = $categoryBySlug->parent_id ? $categoryBySlug->parent_id : $categoryBySlug->id;
                $mainCategoryId = $parent;
                $children       = $categoryBySlug->getChildFront($parent, $getIds         = true);
                if ($children) {
                    $slugClearFilter = $children->toArray();
                }
                $slugClearFilter[] = $parent;
            }
        } else {
            if (!$category) {
                $categoryByTag = AdminCategory::where('show_in', $showIn)->pluck('id')->toArray();
                if ($categoryByTag) {
                    $category = $categoryByTag;
                }
            }
        }
        if (isset($request->category) && $request->category[0] == 'all') {
            //$request->category = is_array($request->subCategoryId)?$request->subCategoryId:[];
        }

        $appliedFilter = [
            'c'             => $request->category ? $request->category : 'clear',
            'category'      => $request->category ? $request->category : [],
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'search'        => $search,
            'subCategoryId' => $request->mainCategoryId,
            'isAll'         => $isAll,
            'brand'         => $brand,
            'sidebarSelectedCategoryId' => ($slug && isset($categoryBySlug) && $categoryBySlug) ? $mainCategoryId : null,
        ];

          //     dd($appliedFilter);

        /*
         * Pagination
         */
        $limit = config('custom_web.product_limit', 30);
        $page = $request->page ?: 1;
        $offset = ($page - 1) * $limit;
        /*
         * /.Pagination
         */

        if ($slugClearFilter) {
            if (!$category) {
                $category = $slugClearFilter;
            }
        }

        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');

        //$mainCategoryId
		
	
        $categories = Helper::getFrontHeader($categoryId, $showIn, $filterType, $price, $color, $search, $size, $brand, false);

        $filterColor = Helper::getColorFilter($filterType, $category, $price, $size, $search, $brand, $request->mainCategoryId);
        
       	
		
        $filterPrice = Helper::getPriceFilter($currencyCode, $filterType, $category, $color, $size, $search, $brand, $request->mainCategoryId);
	
        $filterSize  = Helper::getSizeFilter($filterType, $category, $price, $color, $search, $brand, $request->mainCategoryId);
		
        $filterBrand = Helper::getBrandFilter($filterType, $category, $price, $color, $search, $size, $request->mainCategoryId);

        /*
         * Product Listing
         */
        $appliedFilter['category'] = $request->category ? $request->category : $category;

        $products                = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, false, $offset);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        /*
         * Filter Page HTML
         */

        $searchedProductCategoryIds = $productDatawithoutlimit->pluck('category_id');
        $searchedProductIds         = $productDatawithoutlimit->pluck('id');
        if ($searchedProductIds && $search) {
            $categories = Helper::getFrontHeader($categoryId, $showIn, $filterType, $price, $color, $search, $size, $brand, false, $searchedProductCategoryIds);
        } else {
            $searchedProductIds         = false;
            $searchedProductCategoryIds = false;
        }

        $filterPage   = \View::make('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'appliedFilter', 'currency', 'filterBrand', 'searchedProductIds'))->render();
        //echo $filterPage;die;
        $productCount = count($productDatawithoutlimit);
        $pageLimit    = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) ceil($productCount / $limit);
        }

 $pageProductCount = count($products);
        /*
         * Product Page HTML
         */
        $productPage        = \View::make('web.product.products', compact('products', 'language', 'currencyCode', 'currency'))->render();
        $productCountText   = $productCount > 1 ? ($productCount . " " . Lang::get($this->langFile . '.serach_items_found')) : ($productCount . " " . Lang::get($this->langFile . '.serach_item_found'));
        $newbreadcrumbs     = '';
        $catbreadcrumbs     = '';
        $main_category_slug = '';
        if ($category && $request->category) {

            if ($request->category[0] == 'all') {
                $main_category      = Helper::getCategoryDetails($categoryAllData[1]);
                $maincatroute       = route("web.product.index", ['slug' => $main_category->categories_slug]);
                $main_category_name = $main_category->getCategoryDescription($language->id);
                $catbreadcrumbs     = '<a href="' . $maincatroute . '">' . ucfirst($main_category_name->categories_name) . '</a>';
                $main_category_slug = $main_category->categories_slug;
            } else {

                $breadcrumbsCategory = AdminCategory::whereIn('id', $request->category)->first();
                if ($breadcrumbsCategory) {

                    $main_category = Helper::getCategoryDetails($breadcrumbsCategory->parent_id);

                    $main_category_name = $main_category->getCategoryDescription($language->id);
                    $cateDesc           = $breadcrumbsCategory->getCategoryDescription($language->id);

                    $catroute           = route("web.product.index", ['slug' => $breadcrumbsCategory->categories_slug]);
                    $maincatroute       = route("web.product.index", ['slug' => $main_category->categories_slug]);
                    $newbreadcrumbs     = '<a href="' . $catroute . '">' . ucfirst($cateDesc->categories_name) . '</a>';
                    $catbreadcrumbs     = '<a href="' . $maincatroute . '">' . ucfirst($main_category_name->categories_name) . '</a>';
                    $main_category_slug = $main_category->categories_slug;
                }
            }
        }
     

        return [
            'filterPage' => $filterPage,
            'productPage' => $productPage,
            'pageLimit' => $pageLimit,
            'productCountText' => $productCountText,
            'newbreadcrumbs' => $newbreadcrumbs,
            'catbreadcrumbs' => $catbreadcrumbs,
            'main_category_slug' => $main_category_slug,
            'pageProductCount' => $pageProductCount,
            'productCount' => $productCount,
            'colorCount' => $filterColor ? count($filterColor['data']) : 0,
            'priceCount' => $filterPrice ? count($filterPrice) : 0,
            'sizeCount' => $filterSize ? count($filterSize['data']) : 0,
            'brandCount' => $filterBrand ? count($filterBrand) : 0,
        ];
    }

    public function addToCart(Request $request)
    {
        $validationRule    = [
            'id'             => "required|numeric",
            'quantity'       => "required|numeric",
            'sizeExist'      => "required",
            'colorExist'     => "required",
            'color'          => "required_if:colorExist,1",
            'size'           => "required_if:sizeExist,1",
            'monogramExist'  => "required",
            'font'           => "required_if:monogramExist,1",
            'initials_count' => "required_if:monogramExist,1",
            //            'text1'          => "required_if:monogramExist,1",
            //            'text2'          => "required_if:monogramExist,1",
            //            'text3'          => "required_if:monogramExist,1",
            //            'text4'          => "required_if:monogramExist,1",
            //            'text4'          => "required_if:monogramExist,1",
            'monogram_color' => "required_if:monogramExist,1",
            'placement'      => "required_if:monogramExist,1",
            //            'monogram_note'  => "required_if:monogramExist,1",
        ];
        $validationMessage = [
            'id.required'        => Lang::get($this->langFile . '.error-cart-req-id'),
            'id.numeric'         => Lang::get($this->langFile . '.error-cart-numeric-id'),
            'quantity.required'  => Lang::get($this->langFile . '.error-cart-req-quantity'),
            'quantity.numeric'   => Lang::get($this->langFile . '.error-cart-numeric-quantity'),
            'sizeExist.required' => Lang::get($this->langFile . '.error-cart-req-sizeExist'),
            'sizeExist.numeric'  => Lang::get($this->langFile . '.error-cart-numeric-sizeExist'),
            'size.required_if'   => Lang::get($this->langFile . '.error-cart-req-size'),
            'size.numeric'       => Lang::get($this->langFile . '.error-cart-numeric-size'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();

        $product = AdminProduct::find($request->id);
        if ($product) {
            /*
             * Quantity Check
             */
            $productQuantityAvailable = AdminProduct::checkProductQuantity($request->id, $request->quantity, $request->sizeExist, $request->size, $request->colorExist, $request->color);
            if ($productQuantityAvailable['status']) {
                $user = Auth::user();
                if ($user) {
                    $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $product->id);
                    if ($request->sizeExist) {
                        $shoppingCartDetail->where('size_id', $request->size);
                    }
                    if ($request->colorExist) {
                        $shoppingCartDetail->where('color_id', $request->color);
                    }
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $shoppingCartDetail = ShoppingCartDetail::find($shoppingCartDetail->id);
                        $shoppingCartDetail->quantity = $shoppingCartDetail->quantity + $request->quantity;
                        if ($shoppingCartDetail->save()) {
                            DB::commit();
                            $cartView = Helper::renderCartView();
                            return $cartView;
                        } else {
                            DB::rollback();
                            return false;
                        }
                    } else {
                        $shoppingCart          = new ShoppingCart();
                        $shoppingCart->user_id = $user->id;
                        $shoppingCart->physical_store_id = $user->physical_store_id ?? '';
                        if ($shoppingCart->save()) {
                            $shoppingCartDetail                   = new ShoppingCartDetail();
                            $shoppingCartDetail->user_id          = $user->id;
                            $shoppingCartDetail->shopping_cart_id = $shoppingCart->id;
                            $shoppingCartDetail->product_id       = $product->id;
                            $shoppingCartDetail->size_id          = $request->sizeExist ? $request->size : null;
                            $shoppingCartDetail->quantity         = $request->quantity;
                            $shoppingCartDetail->color_id         = $request->colorExist ? $request->color : null;
                            $shoppingCartDetail->is_same          = $request->is_same;
                            $monogram                             = [];

                            if ($request->sizeExist && $request->colorExist) {
                                $cartKey = $product->id . '_' . $request->size . '_' . $request->color;
                            } elseif ($request->sizeExist) {
                                $cartKey = $product->id . '_' . $request->size . '_c';
                            } elseif ($request->colorExist) {
                                $cartKey = $product->id . '_s_' . $request->color;
                            } else {
                                $cartKey = $product->id . '_s_c';
                            }
                            //                            $temp_monogram = session()->get('temp_monogram');

                            if (isset($_COOKIE['temp_monogram'])) {
                                $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);

                                if (isset($temp_monogram[$cartKey])) {

                                    $monogram                        = $temp_monogram[$cartKey];
                                    $shoppingCartDetail->is_monogram = 1;
                                }
                            }
                            //

                            if ($shoppingCartDetail->save()) {
                                if ($request->monogramExist) {
                                    foreach ($monogram as $record) {
                                        $left                                               = '';
                                        $center                                             = '';
                                        $right                                              = '';
                                        $text                                               = '';
                                        $monogrmShoppingcartdetail                          = new MonogramShoppingCartDetail();
                                        //                                  $monogrmShoppingcartdetail->is_monogram         = $record['is_monogram'];
                                        $monogrmShoppingcartdetail->shopping_cart_detail_id = $shoppingCartDetail->id;
                                        $monogrmShoppingcartdetail->font                    = $record['font'];
                                        $monogrmShoppingcartdetail->initials_count          = $record['initials_count'];
                                        $monogrmShoppingcartdetail->left_text               = $record['left_text'];
                                        $monogrmShoppingcartdetail->center_text             = $record['center_text'];
                                        $monogrmShoppingcartdetail->right_text              = $record['right_text'];
                                        $monogrmShoppingcartdetail->text4                   = $record['text4'];
                                        $monogrmShoppingcartdetail->monogram_color          = $record['monogram_color'];
                                        $monogrmShoppingcartdetail->monogram_color_hash     = $record['monogram_color_hash'];
                                        $monogrmShoppingcartdetail->monogram_color_id       = $record['monogram_color_id'];
                                        $monogrmShoppingcartdetail->placement               = $record['placement'];
                                        $monogrmShoppingcartdetail->monogram_note           = $record['monogram_note'];

                                        $monogrmShoppingcartdetail->save();
                                    }
                                }

                                $wishlist = Wishlist::getUserWishlist($user->id, $product->id);
                                if ($wishlist) {
                                    $wishlist->delete();
                                }
                                //$count = ShoppingCartDetail::where('user_id', $user->id)->count();
                                DB::commit();
                                $cartView = Helper::renderCartView();
                                //                                return ['view' => \View::make('web.product.cart')->render()];
                                return $cartView;
                            } else {
                                DB::rollback();
                                return false;
                            }
                        } else {
                            DB::rollback();
                            return false;
                        }
                    }
                } else {
                    $cart = session()->get('cart');
                    if ($cart) {
                        $shoppingCart = ShoppingCart::where('id', $cart)->first();
                    } else {
                        $shoppingCart          = new ShoppingCart();
                        $shoppingCart->user_id = null;
                        $shoppingCart->save();
                    }
                    if ($shoppingCart) {
                        $existingDetail = ShoppingCartDetail::where('shopping_cart_id', $shoppingCart->id)->where('product_id', $product->id);
                        if ($request->sizeExist) {
                            $existingDetail->where('size_id', $request->size);
                        }
                        if ($request->colorExist) {
                            $existingDetail->where('color_id', $request->color);
                        }
                        $existingDetail = $existingDetail->first();
                        if ($existingDetail) {
                            $existingDetail->quantity = $request->quantity;
                            if ($existingDetail->save()) {
                                DB::commit();
                                session()->put('cart', $shoppingCart->id);
                                $cartView = Helper::renderCartView();
                                return $cartView;
                            } else {
                                DB::rollback();
                                return false;
                            }
                        }
                        $shoppingCartDetail                   = new ShoppingCartDetail();
                        //                        $shoppingCartDetail->user_id = $user->id;
                        $shoppingCartDetail->shopping_cart_id = $shoppingCart->id;
                        $shoppingCartDetail->product_id       = $product->id;
                        $shoppingCartDetail->size_id          = $request->sizeExist ? $request->size : null;
                        $shoppingCartDetail->quantity         = $request->quantity;
                        $shoppingCartDetail->color_id         = $request->colorExist ? $request->color : null;
                        $shoppingCartDetail->is_same          = $request->is_same;
                        $monogram                             = [];

                        if ($request->sizeExist && $request->colorExist) {
                            $cartKey = $product->id . '_' . $request->size . '_' . $request->color;
                        } elseif ($request->sizeExist) {
                            $cartKey = $product->id . '_' . $request->size . '_c';
                        } elseif ($request->colorExist) {
                            $cartKey = $product->id . '_s_' . $request->color;
                        } else {
                            $cartKey = $product->id . '_s_c';
                        }
                        //                            $temp_monogram = session()->get('temp_monogram');

                        if (isset($_COOKIE['temp_monogram'])) {
                            $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);

                            if (isset($temp_monogram[$cartKey])) {

                                $monogram                        = $temp_monogram[$cartKey];
                                $shoppingCartDetail->is_monogram = 1;
                            }
                        }
                        //

                        if ($shoppingCartDetail->save()) {
                            if ($request->monogramExist) {
                                foreach ($monogram as $record) {
                                    $left                                               = '';
                                    $center                                             = '';
                                    $right                                              = '';
                                    $text                                               = '';
                                    $monogrmShoppingcartdetail                          = new MonogramShoppingCartDetail();
                                    //                                  $monogrmShoppingcartdetail->is_monogram         = $record['is_monogram'];
                                    $monogrmShoppingcartdetail->shopping_cart_detail_id = $shoppingCartDetail->id;
                                    $monogrmShoppingcartdetail->font                    = $record['font'];
                                    $monogrmShoppingcartdetail->initials_count          = $record['initials_count'];
                                    $monogrmShoppingcartdetail->left_text               = $record['left_text'];
                                    $monogrmShoppingcartdetail->center_text             = $record['center_text'];
                                    $monogrmShoppingcartdetail->right_text              = $record['right_text'];
                                    $monogrmShoppingcartdetail->text4                   = $record['text4'];
                                    $monogrmShoppingcartdetail->monogram_color          = $record['monogram_color'];
                                    $monogrmShoppingcartdetail->monogram_color_hash     = $record['monogram_color_hash'];
                                    $monogrmShoppingcartdetail->monogram_color_id       = $record['monogram_color_id'];
                                    $monogrmShoppingcartdetail->placement               = $record['placement'];
                                    $monogrmShoppingcartdetail->monogram_note           = $record['monogram_note'];

                                    $monogrmShoppingcartdetail->save();
                                }
                            }
                            //$count = ShoppingCartDetail::where('user_id', $user->id)->count();
                            DB::commit();
                            session()->put('cart', $shoppingCart->id);
                            $cartView = Helper::renderCartView();
                            //                                return ['view' => \View::make('web.product.cart')->render()];
                            return $cartView;
                        } else {
                            DB::rollback();
                            return false;
                        }
                    } else {
                        DB::rollback();
                        return false;
                    }
                }
            } else {
                return $productQuantityAvailable;
            }
        } else {
            return false;
        }
    }

    public function checkCartProductExist(Request $request)
    {

        $validationRule    = [
            'product_id' => "required|numeric",
            'sizeExist'  => "required",
            'size'       => "required_if:sizeExist,1",
        ];
        $validationMessage = [
            'product_id.required' => Lang::get($this->langFile . '.error-cart-req-id'),
            'product_id.numeric'  => Lang::get($this->langFile . '.error-cart-numeric-id'),
            'sizeExist.required'  => Lang::get($this->langFile . '.error-cart-req-sizeExist'),
            'sizeExist.numeric'   => Lang::get($this->langFile . '.error-cart-numeric-sizeExist'),
            'size.required_if'    => Lang::get($this->langFile . '.error-cart-req-size'),
            'size.numeric'        => Lang::get($this->langFile . '.error-cart-numeric-size'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);
        $user          = Auth::user();
        $cartStatus    = Helper::getCartProductStatus($user, $request->product_id, $request->sizeExist, $request->size, $request->colorExist, $request->color);
        $cartproduct   = Helper::getCartProduct($user, $request->product_id, $request->sizeExist, $request->sizeExist ? $request->size : 's', $request->colorExist, $request->colorExist ? $request->color : 'c');

        $productQuantity    = Helper::getProductQuantity($request->product_id, $request->sizeExist, $request->size, $request->colorExist, $request->color);
        $quantityOptionView = \View::make('web.product.product-quantity', ['productQuantity' => $productQuantity, 'cartQuantity' => false])->render();
        $size               = \App\AdminModel\Product\AdminProductAttribute::where('color_id', $request->color)->where('size_id', '!=', null)->where('products_id', $request->product_id)->pluck('size_id');
        $productDetail      = AdminProduct::where('id', $request->product_id)->first();
        $productsize        = '';

        if (count($size) > 0 && $productDetail->have_size) {

            $is_size     = true;
            $productsize = \App\AdminModel\Size\AdminSize::whereIn('id', $size)->where('is_active', 1)->get();

            $product = \App\AdminModel\Product\AdminProductAttribute::where('size_id', $request->size)->where('products_id', $request->product_id);
            if ($request->colorExist && $productDetail->have_color) {
                $product->where('color_id', $request->color);
            } else {
                $product->where('color_id', null);
            }

            $product = $product->first();

            $sizeOptionView = \View::make('web.product.product-size', ['productSize' => $productsize])->render();
        } elseif ($productDetail->have_color && $request->colorExist) {
            $product = \App\AdminModel\Product\AdminProductAttribute::where('products_id', $request->product_id);
            if ($productDetail->have_color && $request->colorExist) {
                $product->where('color_id', $request->color);
            } else {
                $product->where('color_id', null);
            }
            $product        = $product->first();

            $is_size        = false;
            $sizeOptionView = '';
        } else {
            $product = \App\AdminModel\Product\AdminProduct::where('id', $request->product_id);
            $product        = $product->first();
            $is_size        = false;
            $sizeOptionView = '';
        }

        $currency = Helper::getDefaultCurrency();

        $regularPrice    = $product['product_regular_price'] ;
        $salePrice       = $product['product_sale_price'];
        $isSalePriceNull = $product['is_product_sale_price_null'];

        $productstyle = '';
        if ($product->style) {
            $productstyle = ' (' . $product->style . ')';
        }
        $currency = $currency ? $currency->code : config('custom.currency');

        if ($request->sizeExist && $request->colorExist) {
            $qty_key = $request->product_id . '_' . $request->size . '_' . $request->color;
        } elseif ($request->sizeExist) {
            $qty_key = $request->product_id . '_' . $request->size . '_c';
        } elseif ($request->colorExist) {
            $qty_key = $request->product_id . '_s_' . $request->color;
        } else {
            $qty_key = $request->product_id . '_s_c';
        }

        $price         = !$isSalePriceNull ? Helper::formatNumber($salePrice) : Helper::formatNumber($regularPrice);
        $price         = $price . " " . $currency;
        $priceStrike   = !$isSalePriceNull ? Helper::formatNumber($regularPrice) . ' ' . $currency : '';
        $monogramcount = 0;
        if (count($cartproduct) > 0) {
            if (count($cartproduct['monogram']) > 0) {
                $monogramcount = count($cartproduct['monogram']);
            }
        }
        $monogram_list        = \View::make('web.layout.monogram_list', ['cartProduct' => $cartproduct, 'id' => $qty_key])->render();
        $priceDetails         = '<span class = "prod-price">' . $price . $productstyle . '</span><span class = "striked">' . $priceStrike . '</span>';
        $priceDetailsWishlist = '<div class = "prod-price">' . $price . '<span class = "striked">' . $priceStrike . '</span></div>';
        return [
            'cartStatus'           => $cartStatus,
            'availableQuantity'    => $productQuantity,
            'quantityOptionView'   => $quantityOptionView,
            'product'              => $product,
            'priceDetails'         => $priceDetails,
            'priceDetailsWishlist' => $priceDetailsWishlist,
            'qtyKey'               => $qty_key,
            'monogramCount'        => $monogramcount,
            'cart'                 => $cartproduct,
            'monogram_list'        => $monogram_list,
        ];
    }

    /**
     * Show the new Arrivals .
     */
    public function newArrivals(Request $request)
    {
        $isExistNewArrivalProduct = Helper::isExistNewArrivalProduct();
        if (!$isExistNewArrivalProduct) {
            return redirect()->route('web.index');
        }
        /*
         * Filter Page
         */
        $filterType     = "new_arrivals";
        $color          = [];
        $size           = [];
        $brand          = [];
        $price          = [];
        $categoryFilter = [];
        $search         = '';
        $mainCategoryId = false;
        $isAll          = false;
        if (isset($request->isAll) && $request->isAll == 1) {
            $isAll          = true;
            $mainCategoryId = $request->sCId;
        }

        if (isset($request->sCId) && !$mainCategoryId) {
            $mainCategoryId = $request->sCId;
        }

        /*
         * Access get request and make filter as per URL
         */

        if (isset($request->c) && $request->c) {
            if ($request->c != "clear") {
                $categoryFilter = AdminCategory::whereIn('categories_slug', $request->c)->pluck('id')->toArray();
            }
        }
        if (isset($request->s) && $request->s) {
            $size = \App\AdminModel\Size\AdminSizeDescription::whereIn('size_title', $request->s)->pluck('size_id')->toArray();
        }
        if (isset($request->b) && $request->b) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $request->b)->pluck('brand_id')->toArray();
        }
        if (isset($request->cl) && $request->cl) {
            $color = \App\AdminModel\Color\AdminColorDescription::whereIn('color_title', $request->cl)->pluck('color_id')->toArray();
        }
        if (isset($request->p) && $request->p) {
            $price = $request->p;
        }

        $sortBy = isset($request->sortby) ? $request->sortby : 'no';

        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $categories   = Helper::getFrontHeader("", "", $filterType, $price, $color, $search, $size, $brand, $mainCategoryId);
        $filterColor  = Helper::getColorFilter($filterType, $categoryFilter, $price, $size, $brand, $mainCategoryId);
        $filterPrice  = Helper::getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId);
        $filterSize   = Helper::getSizeFilter($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
        $filterBrand  = Helper::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId);

        $filteredCategoryId = '';
        $slug               = '';
        $redirectUrl        = route('web.product.new_arrivals');

        $limit = config('custom_web.product_limit', 30);
        $page = $request->page ?: 1;
        $offset = ($page - 1) * $limit;

        $appliedFilter = [
            'c'             => $request->c,
            'category'      => $categoryFilter,
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'brand'         => $brand,
            'isAll'         => $isAll,
            'subCategoryId' => $mainCategoryId,
            'brand'         => $brand,
        ];

        /*
         * Product Listing
         */
         
        

        $products                = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, false, $offset);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        $productCount            = count($productDatawithoutlimit);
        $pageLimit               = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) ceil($productCount / $limit);
        }

        $pageProductCount = count($products);
        $filterPage = view('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'categoryFilter', 'appliedFilter', 'currency', 'filterBrand'));
        return view('web.product.index', compact('products', 'pageLimit', 'productDatawithoutlimit', 'filterPage', 'language', 'currencyCode', 'filteredCategoryId', 'categories', 'slug', 'filterType', 'redirectUrl', 'sortBy', 'search', 'currency', 'mainCategoryId','productCount', 'pageProductCount'));
    }

    public function shopByBrand(Request $request)
    {
        /*
         * Filter Page
         */
        $filterType     = "shop_by_brand";
        $color          = [];
        $size           = [];
        $brand          = [];
        $price          = [];
        $categoryFilter = [];
        $search         = '';
        $mainCategoryId = false;
        $isAll          = false;
        if (isset($request->isAll) && $request->isAll == 1) {
            $isAll          = true;
            $mainCategoryId = $request->sCId;
        }

        if (isset($request->sCId) && !$mainCategoryId) {
            $mainCategoryId = $request->sCId;
        }

        /*
         * Access get request and make filter as per URL
         */

        if (isset($request->c) && $request->c) {
            if ($request->c != "clear") {
                $categoryFilter = AdminCategory::whereIn('categories_slug', $request->c)->pluck('id')->toArray();
            }
        }
        if (isset($request->s) && $request->s) {
            $size = \App\AdminModel\Size\AdminSizeDescription::whereIn('size_title', $request->s)->pluck('size_id')->toArray();
        }
        if (isset($request->b) && $request->b) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $request->b)->pluck('brand_id')->toArray();
            
        }
        if (isset($request->cl) && $request->cl) {
            $color = \App\AdminModel\Color\AdminColorDescription::whereIn('color_title', $request->cl)->pluck('color_id')->toArray();
        }
        if (isset($request->p) && $request->p) {
            $price = $request->p;
        }

        $sortBy = isset($request->sortby) ? $request->sortby : 'no';

        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        
        
        $categories   = Helper::getFrontHeader("", "", $filterType, $price, $color, $search, $size, $brand, $mainCategoryId);
        
       
        $filterColor  = Helper::getColorFilter($filterType, $categoryFilter, $price, $size, $search , $brand , $mainCategoryId);
        
         
        $filterPrice  = Helper::getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId);
        $filterSize   = Helper::getSizeFilter($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
        $filterBrand  = Helper::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId);

        $filteredCategoryId = '';
        $slug               = '';
        $redirectUrl        = route('web.product.shop_by_brand');

        $limit = config('custom_web.product_limit', 30);

        if ($request->page) {
            $limit = $limit * $request->page;
        }

        $appliedFilter = [
            'c'             => $request->c,
            'category'      => $categoryFilter,
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'brand'         => $brand,
            'isAll'         => $isAll,
            'subCategoryId' => $mainCategoryId,
            'brand'         => $brand,
        ];

        /*
         * Product Listing
         */

        $products                = AdminProduct::getProductByFilter($language, $appliedFilter, $limit);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        $productCount            = count($productDatawithoutlimit);
        $pageLimit               = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) floor($productCount / $limit) + ($productCount % $limit);
        }
        $pageProductCount = count($products);

        $filterPage = view('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'categoryFilter', 'appliedFilter', 'currency', 'filterBrand'));
        return view('web.product.index', compact('products', 'pageLimit', 'productDatawithoutlimit', 'filterPage', 'language', 'currencyCode', 'filteredCategoryId', 'categories', 'slug', 'filterType', 'redirectUrl', 'sortBy', 'search', 'currency', 'mainCategoryId','productCount', 'pageProductCount'));
    }

    public function sale(Request $request)
    {
        $isExistSaleProduct = Helper::isExistSaleProduct();
        if (!$isExistSaleProduct) {
            return redirect()->route('web.index');
        }

        /*
         * Filter Page
         */
        $filterType     = "sale";
        $color          = [];
        $size           = [];
        $brand          = [];
        $price          = [];
        $categoryFilter = [];
        $search         = '';
        $mainCategoryId = false;
        $isAll          = false;
        if (isset($request->isAll) && $request->isAll == 1) {
            $isAll          = true;
            $mainCategoryId = $request->sCId;
        }

        if (isset($request->sCId) && !$mainCategoryId) {
            $mainCategoryId = $request->sCId;
        }

        /*
         * Access get request and make filter as per URL
         */
        if (isset($request->c) && $request->c) {
            if ($request->c != "clear") {
                $categoryFilter = AdminCategory::whereIn('categories_slug', $request->c)->pluck('id')->toArray();
            }
        }
        if (isset($request->s) && $request->s) {
            $size = \App\AdminModel\Size\AdminSizeDescription::whereIn('size_title', $request->s)->pluck('size_id')->toArray();
        }
        if (isset($request->b) && $request->b) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $request->b)->pluck('brand_id')->toArray();
        }
        if (isset($request->cl) && $request->cl) {
            $color = \App\AdminModel\Color\AdminColorDescription::whereIn('color_title', $request->cl)->pluck('color_id')->toArray();
        }
        if (isset($request->p) && $request->p) {
            $price = $request->p;
        }
        $sortBy = isset($request->sortby) ? ($request->sortby == 'd' ? 'up' : $request->sortby) : 'no';

        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $categories   = Helper::getFrontHeader("", "", $filterType, $price, $color, $search, $size, $brand, $mainCategoryId);
        $filterColor  = Helper::getColorFilter($filterType, $categoryFilter, $price, $size, $search, $brand, $mainCategoryId);
        $filterPrice  = Helper::getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId);
        $filterSize   = Helper::getSizeFilter($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
        $filterBrand  = Helper::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId);

        $filteredCategoryId = '';
        $slug               = '';
        $redirectUrl        = route('web.product.sale');

        $limit = config('custom_web.product_limit', 30);

        if ($request->page) {
            $limit = $limit * $request->page;
        }

        $appliedFilter = [
            'c'             => $request->c,
            'category'      => $categoryFilter,
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'brand'         => $brand,
            'isAll'         => $isAll,
            'subCategoryId' => $mainCategoryId,
        ];

        /*
         * Product Listing
         */

        $products = AdminProduct::getProductByFilter($language, $appliedFilter, $limit);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        $productCount            = count($productDatawithoutlimit);
        $pageLimit               = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) floor($productCount / $limit) + ($productCount % $limit);
        }
        $pageProductCount = count($products);

        $filterPage = view('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'categoryFilter', 'appliedFilter', 'currency', 'filterBrand'));
        return view('web.product.index', compact('products', 'pageLimit', 'productDatawithoutlimit', 'filterPage', 'language', 'currencyCode', 'filteredCategoryId', 'categories', 'slug', 'filterType', 'redirectUrl', 'sortBy', 'search', 'currency', 'mainCategoryId' ,'productCount', 'pageProductCount'));
    }
    
    
    
    
    public function sale_old(Request $request)
    {
        $isExistSaleProduct = Helper::isExistSaleProduct();
        if (!$isExistSaleProduct) {
            return redirect()->route('web.index');
        }

        /*
         * Filter Page
         */
        $filterType     = "sale";
        $color          = [];
        $size           = [];
        $brand          = [];
        $price          = [];
        $categoryFilter = [];
        $search         = '';
        $mainCategoryId = false;
        $isAll          = false;
        if (isset($request->isAll) && $request->isAll == 1) {
            $isAll          = true;
            $mainCategoryId = $request->sCId;
        }

        if (isset($request->sCId) && !$mainCategoryId) {
            $mainCategoryId = $request->sCId;
        }

        /*
         * Access get request and make filter as per URL
         */
        if (isset($request->c) && $request->c) {
            if ($request->c != "clear") {
                $categoryFilter = AdminCategory::whereIn('categories_slug', $request->c)->pluck('id')->toArray();
            }
        }
        if (isset($request->s) && $request->s) {
            $size = \App\AdminModel\Size\AdminSizeDescription::whereIn('size_title', $request->s)->pluck('size_id')->toArray();
        }
        if (isset($request->b) && $request->b) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $request->b)->pluck('brand_id')->toArray();
        }
        if (isset($request->cl) && $request->cl) {
            $color = \App\AdminModel\Color\AdminColorDescription::whereIn('color_title', $request->cl)->pluck('color_id')->toArray();
        }
        if (isset($request->p) && $request->p) {
            $price = $request->p;
        }
        $sortBy = isset($request->sortby) ? ($request->sortby == 'd' ? 'up' : $request->sortby) : 'no';

        $language     = Helper::getLanguage();
        $currency     = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $categories   = Helper::getFrontHeader("", "", $filterType, $price, $color, $search, $size, $brand, $mainCategoryId);
        $filterColor  = Helper::getColorFilter($filterType, $categoryFilter, $price, $size, $search, $brand, $mainCategoryId);
        $filterPrice  = Helper::getPriceFilter($currencyCode, $filterType, $categoryFilter, $color, $size, $search, $brand, $mainCategoryId);
        $filterSize   = Helper::getSizeFilter($filterType, $categoryFilter, $price, $color, $search, $brand, $mainCategoryId);
        $filterBrand  = Helper::getBrandFilter($filterType, $categoryFilter, $price, $color, $search, $size, $mainCategoryId);

        $filteredCategoryId = '';
        $slug               = '';
        $redirectUrl        = route('web.product.sale');

        $limit = config('custom_web.product_limit', 30);

        if ($request->page) {
            $limit = $limit * $request->page;
        }

        $appliedFilter = [
            'c'             => $request->c,
            'category'      => $categoryFilter,
            'color'         => $color,
            'size'          => $size,
            'price'         => $price,
            'type'          => $filterType,
            'sortBy'        => $sortBy,
            'brand'         => $brand,
            'isAll'         => $isAll,
            'subCategoryId' => $mainCategoryId,
        ];

        /*
         * Product Listing
         */

        $products = AdminProduct::getProductByFilter($language, $appliedFilter, $limit);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($language, $appliedFilter, $limit, true);
        $productCount            = count($productDatawithoutlimit);
        $pageLimit               = 0;

        if ($productCount >= $limit) {
            $pageLimit = (int) floor($productCount / $limit) + ($productCount % $limit);
        }
        $pageProductCount = count($products);

        $filterPage = view('web.product.filter', compact('language', 'currencyCode', 'categories', 'filterColor', 'filterPrice', 'filterSize', 'categoryFilter', 'appliedFilter', 'currency', 'filterBrand'));
        return view('web.product.index', compact('products', 'pageLimit', 'productDatawithoutlimit', 'filterPage', 'language', 'currencyCode', 'filteredCategoryId', 'categories', 'slug', 'filterType', 'redirectUrl', 'sortBy', 'search', 'currency', 'mainCategoryId' ,'productCount', 'pageProductCount'));
    }

    /**
     * Show the product detail .
     */
    public function productDetail($slug, $barcode='')
    {
        $productQuery = AdminProduct::where('products_slug', $slug);
        $productQuery = Helper::applyCategoryVisibility($productQuery);
        $productQuery = Helper::applyProductVisibility($productQuery);
        $product = $productQuery->first();
        if ($product) {
          
            if (isset($_COOKIE['temp_monogram'])) {
                unset($_COOKIE['temp_monogram']);
                setcookie('temp_monogram', null, -1, '/');
            }
            //            session()->put('temp_monogram', array());
            /*
             * Page detail
             */
            $language = Helper::getLanguage();

            $category = AdminProductCategory::where('products_id', $product->id)->first();
            $category = $category->getCategory();

            $parentCategory     = "";
            $parentCategoryName = "";
            $categoryName       = "";
            if ($category->parent_id) {
                $parentCategory     = $category->getParent($category->parent_id);
                $parentCategoryName = $parentCategory->getCategoryDescription($language->id);
                $categoryName       = $category->getCategoryDescription($language->id);
            } else {
                $parentCategory     = $category;
                $parentCategoryName = $category->getCategoryDescription($language->id);
            }

            $appName             = Helper::getAppName() ? Helper::getAppName() : '';
            $contactEmail        = Helper::getSettingById(19);
            $contactEmail        = $contactEmail ? $contactEmail->setting_val : "";
            $currency            = Helper::getDefaultCurrency();
            $currencyCode        = $currency ? $currency->code : config('custom.currency');
            $productDetail       = $product->getProductDescription($language->id);
            $productImage        = $product->getProductImages('', true);
            $productVideo        = $product->getProductVideos('', true);
            $productDefaultImage = $product->getDefaultImage();
            $productQuantity     = $product->getProductQuantity($product->id);
            $productAttributes   = $product->getProductAttributes();

            /*
             *  Convert Currency Rate
             */
            // if (
            //     $currency
            // ) {
            //     $product->product_sale_price    = $product->product_sale_price * $currency->value;
            //     $product->product_regular_price = $product->product_regular_price * $currency->value;
            // }
            

            

            $user     = Auth::user();
            $wishList = false;
            if ($user) {
                $wishList = Wishlist::getUserWishlist($user->id, $product->id);
            }

            return view('web.product.product_detail', compact('product', 'category', 'parentCategory', 'language', 'appName', 'contactEmail', 'currency', 'currencyCode', 'productDetail', 'productImage', 'productQuantity', 'productDefaultImage', 'parentCategoryName', 'categoryName', 'wishList', 'productAttributes', 'user', 'productVideo', 'barcode'));
        } else {
            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.no-product-message'));
        }
    }

    public function getProduct($slug = '', Request $request)
    {
        $search      = '';
        $page        = '';
        $color       = [];
        $category    = '';
        $cat_id      = '';
        $price       = '';
        $prices      = '';
        $filter_type = $request->filter_type;
        $limit       = config('custom.product_limit', 30);
        $filter      = '';
        if ($request->search) {
            $search = $request->search;
        }
        if ($request->category) {
            $category = $request->category;
            $cat_id   = $request->category;
        }
        if ($request->color) {
            $color = $request->color;
        }
        if ($request->price) {
            $price  = explode('-', $request->price);
            $prices = $request->price;
        }
        if ($request->page) {
            $limit = $limit * $request->page;
        }

        if ($request->filter) {
            $filter = $request->filter;
        }
        $products                = AdminProduct::getProductByFilter($slug, $search, $category, $color, $price, $limit, false, $filter);
        $productDatawithoutlimit = AdminProduct::getProductByFilter($slug, $search, $category, $color, $price, $limit, true, $filter);
        $productCount            = count($productDatawithoutlimit);
        $pageLimit               = (int) round($productCount / $limit) + ($productCount % $limit);
        $productID               = [];
        $productPrice            = [];

        return [
            'view'  => \View::make('web.product.products', compact('products'))->render(),
            'color' => \View::make('web.product.color', compact('productID', 'color', 'filter_type', 'productDatawithoutlimit', 'price', 'cat_id'))->render(),
            'price' => \View::make('web.product.price', compact('productPrice', 'prices', 'filter_type', 'productDatawithoutlimit', 'color', 'cat_id'))->render(),
            'page'  => $request->page,
        ];
    }

    public function removeFromCart($id)
    {
        $cart = session()->get('cart');

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('remove_cart_success', "Removed from Cart");
    }

    protected function sessionData($productId, $quantity, $size, $note = '', $color, $is_monogram, $monogram, $is_same)
    {
        return [
            'product_id'  => $productId,
            'quantity'    => $quantity,
            'size_id'     => $size,
            'color_id'    => $color,
            'note'        => $note,
            'is_monogram' => $is_monogram,
            'is_same'     => $is_same,
            'monogram'    => $monogram,
        ];
    }

    //    protected function setSessionAndReturnResponse($cart)
    //    {
    //
    //
    //        session()->put('cart', $cart);
    //        $carts = session()->get('cart');
    //        return ['view' => \View::make('web.product.cart')->render()];
    //    }

    public function addNote(Request $request)
    {
        $cart = session()->get('cart');
        $user = Auth::user();
        if ($user) {
            $productId = $request->id;
            $sizeId    = "";
            $colorId   = "";

            $productSize = $request->id;
            $productSize = explode("_", $productSize);
            if ($productSize) {
                if (isset($productSize[0])) {
                    $productId = $productSize[0];
                }
                if (isset($productSize[1]) && $productSize[1] != 's') {
                    $sizeId = $productSize[1];
                }
                if (isset($productSize[2]) && $productSize[2] != 'c') {
                    $colorId = $productSize[2];
                }
            }

            $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $productId);
            if ($sizeId) {
                $shoppingCartDetail->where('size_id', $sizeId);
            }
            if (
                $colorId
            ) {
                $shoppingCartDetail->where('color_id', $colorId);
            }
            $shoppingCartDetail = $shoppingCartDetail->first();

            if ($shoppingCartDetail) {
                DB::beginTransaction();
                $shoppingCartDetail->note = $request->note;
                if ($shoppingCartDetail->save()) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollback();
                    return false;
                }
            }
        } else {
            $productId = $request->id;
            $sizeId    = "";
            $colorId   = "";

            $productSize = $request->id;
            $productSize = explode("_", $productSize);
            if ($productSize) {
                if (isset($productSize[0])) {
                    $productId = $productSize[0];
                }
                if (isset($productSize[1]) && $productSize[1] != 's') {
                    $sizeId = $productSize[1];
                }
                if (isset($productSize[2]) && $productSize[2] != 'c') {
                    $colorId = $productSize[2];
                }
            }
            $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $productId);
            if ($sizeId) {
                $shoppingCartDetail->where('size_id', $sizeId);
            }
            if (
                $colorId
            ) {
                $shoppingCartDetail->where('color_id', $colorId);
            }
            $shoppingCartDetail = $shoppingCartDetail->first();

            if ($shoppingCartDetail) {
                DB::beginTransaction();
                $shoppingCartDetail->note = $request->note;
                if ($shoppingCartDetail->save()) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollback();
                    return false;
                }
            }
        }
    }

    public function updateQuantity(Request $request)
    {
        if ($request->id) {
            $user        = Auth::user();
            $productId   = $request->id;
            $sizeId      = $request->size_id;
            $colorId     = $request->color_id;
            $productSize = $request->id;
            $productSize = explode("_", $productSize);
            if ($productSize) {
                if (isset($productSize[0])) {
                    $productId = $productSize[0];
                }
                if (isset($productSize[1]) && $productSize[1] != 's') {
                    $sizeId = $productSize[1];
                }
                if (isset($productSize[2]) && $productSize[1] != 'c') {
                    $colorId = $productSize[2];
                }
            }

            $product = AdminProduct::find($productId);
            if ($product) {
                $user = Auth::user();
                if ($user) {
                    DB::beginTransaction();
                    $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $product->id);
                    if ($sizeId && $sizeId != 's') {
                        $shoppingCartDetail->where('size_id', $sizeId);
                    }
                    if ($colorId && $colorId != 'c') {
                        $shoppingCartDetail->where('color_id', $colorId);
                    }
                    $shoppingCartDetail = $shoppingCartDetail->first();
                    if ($shoppingCartDetail) {
                        $shoppingCartDetail = ShoppingCartDetail::find($shoppingCartDetail->id);
                        if ($request->quantity == 1) {
                            $shoppingCartDetail->is_same = 0;
                        }

                        $shoppingCartDetail->quantity = $request->quantity;
                        if ($shoppingCartDetail->save()) {
                            DB::commit();
                             $data = $this->getUpdatedProduct();
                            $shippingCharge = $this->getUpdatedShipping();
                          $count = count($data);
                                 $count = $count -1;

                            $shippingCharge =  Helper::getShippingAmount($data[$count]['total'],$shippingCharge);
                           
                          
                            return array('status' => true, 'data' => $data, 'page' => Helper::renderCartView(), 'shipping' =>  Helper::formatNumber($shippingCharge));
                        } else {
                            DB::rollback();
                            return false;
                        }
                    }
                    return false;
                } else {
                    $cart = session()->get('cart');
                    if (isset($cart)) {
                        DB::beginTransaction();
                        $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $product->id);
                        if ($sizeId && $sizeId != 's') {
                            $shoppingCartDetail->where('size_id', $sizeId);
                        }
                        if ($colorId && $colorId != 'c') {
                            $shoppingCartDetail->where('color_id', $colorId);
                        }
                        $shoppingCartDetail = $shoppingCartDetail->first();
                        if ($shoppingCartDetail) {
                            $shoppingCartDetail = ShoppingCartDetail::find($shoppingCartDetail->id);
                            if ($request->quantity == 1) {
                                $shoppingCartDetail->is_same = 0;
                            }

                            $monogrmCount = MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail->id)->count();
                            if ($monogrmCount == 0) {
                                $shoppingCartDetail->is_same     = 0;
                                $shoppingCartDetail->is_monogram = 0;
                            }
                            $shoppingCartDetail->quantity = $request->quantity;
                            if ($shoppingCartDetail->save()) {
                                DB::commit();
                                $data = $this->getUpdatedProduct();
                            $shippingCharge = $this->getUpdatedShipping();
                                 
                                 $count = count($data);
                                 $count = $count -1;
                            $shippingCharge =  Helper::getShippingAmount($data[$count]['total'],$shippingCharge);
                                return array('status' => true, 'data' => $data, 'page' => Helper::renderCartView(), 'shipping' => Helper::formatNumber($shippingCharge));
                            } else {
                                DB::rollback();
                                return false;
                            }
                        }
                        return false;
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function updateGiftWarp(Request $request)
    {
  	    if ($request->is_gift == 1) {
             $data = array('is_gift' => $request->is_gift, 'title' => $request->title, 'message' => $request->message);
         } else {
             $data = null;
         }
         session()->put('gift_wrap', $data);

         if ($request->has('is_gift_box')) {
             if ($request->is_gift_box == 1) {
                 session()->put('gift_box', array('is_gift' => $request->is_gift_box, 'title' => $request->gift_box_title, 'message' => $request->gift_box_message));
             } else {
                 session()->put('gift_box', null);
             }
         }

         $datas = $this->getUpdatedProduct();
         $shippingCharge = $this->getUpdatedShipping();
         $count = count($datas);
         $count = $count -1;

         $shippingCharge =  $count > 0 ? Helper::getShippingAmount($datas[$count]['total'],$shippingCharge) : $shippingCharge;

         return array('data' => $datas, 'shipping' => $shippingCharge);
    }

    public function updateGiftBox(Request $request)
    {
  	    if ($request->is_gift_box == 1) {
             $data = array('is_gift' => $request->is_gift_box, 'title' => $request->gift_box_title, 'message' => $request->gift_box_message);
         } else {
             $data = null;
         }
         session()->put('gift_box', $data);

         if ($request->has('is_gift')) {
             if ($request->is_gift == 1) {
                 session()->put('gift_wrap', array('is_gift' => $request->is_gift, 'title' => $request->title, 'message' => $request->message));
             } else {
                 session()->put('gift_wrap', null);
             }
         }

         $datas = $this->getUpdatedProduct();
         $shippingCharge = $this->getUpdatedShipping();
         $count = count($datas);
         $count = $count -1;

         $shippingCharge =  $count > 0 ? Helper::getShippingAmount($datas[$count]['total'],$shippingCharge) : $shippingCharge;

         return array('data' => $datas, 'shipping' => $shippingCharge);
    }

    public function getUpdatedProduct()
    {
        $cart          = [];
        $priceUpdate   = [];
        $total         = 0;
        $user          = Auth::user();
        $tax           = Helper::getTax();
        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
        $ismonogram    = false;

        if ($user) {
            $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
        } elseif (session('cart')) {

            $cart = ShoppingCartDetail::where('shopping_cart_id', session('cart'))->get();
        }

        $appliedCoupon = [];

        $appliedCoupon = session('couponCode');
        if ($user) {
            if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                if ($user && $appliedCoupon['user_id'] != $user->id) {
                    $appliedCoupon = [];
                }
            }
        }

        $shippingcharge = 0;

        foreach ($cart as $id => $record) {
            $intlShipFlag = true;

            $id = $record->product_id;
            if ($record->is_monogram == 1) {
                $ismonogram = true;

                $monogramCount = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->count();
            } else {
                $ismonogram = false;
            }

            $product = AdminProduct::find($id);

            $sizeId         = $record['size_id'];
            $colorId        = $record['color_id'];
            $product_weight = $product->product_weight;

            $product_regular_price      =  $product->product_regular_price;
            $product_sale_price         = $product->product_sale_price ;
            $is_product_sale_price_null = $product->is_product_sale_price_null;

            if ($sizeId || $colorId) {
                $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $colorId);
                if ($attributePrice) {
                    $product_regular_price     = $attributePrice->product_regular_price;
                    $product_sale_price          = $attributePrice->product_sale_price;
                    $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                    $product_weight                      = $attributePrice->weight;
                }
            }
            $price = $product_sale_price ? $product_sale_price : $product_regular_price;

            /*
             *  Convert Currency Rate
             */
            $currency = Helper::getDefaultCurrency();

            if ($currency) {
              //  $price = $price * $currency->value;
            }

            $sub_total = $price * $record['quantity'];

            if (isset($record->is_active) && $record->is_active == 0) {
                //Do not update total... as in this case product does not allow intl ship
                $intlShipFlag = false;
            } else {
                $total += $sub_total;
            }

            if ($ismonogram) {
                if ($record['is_same'] == 1) {
                    $total = $total + ($monogramPrice * $record['quantity']);
                } else {
                    $total = $total + ($monogramPrice * $monogramCount);
                }
            }
            $taxval = ($total * $tax) / 100;

            // Compute coupon only on main order subtotal, add customized advance afterwards
            $mainTotal = $total;
            $customOrderDetails = session('customized_order_details');
            $customAdvance = 0;
            if($customOrderDetails && isset($customOrderDetails['payment']['advance']) && is_numeric($customOrderDetails['payment']['advance'])) {
                $customAdvance = (float) str_replace(',', '', $customOrderDetails['payment']['advance']);
            }
            $total = $mainTotal + $customAdvance;

            $couponDiscountFinal = 0;

            if ($appliedCoupon) {
                $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                if ($couponDiscountIn && $couponDiscount) {
                    if ($couponDiscountIn == 'P') {
                        $couponDiscountFinal = ($mainTotal * $couponDiscount) / 100;
                    } else if ($couponDiscountIn == 'F') {
                        $couponDiscountFinal = $mainTotal - $couponDiscount;
                    }
                }
            }
            $giftprice = 0;

            $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
            if (session('gift_wrap')) {
                $setting      = \App\Helpers\Helper::getSetting();
                $gift_wrap    = session('gift_wrap');
                $giftWrapSetting = $setting->firstWhere('id', 33);
                $giftprice    = $giftWrapSetting ? $giftWrapSetting->setting_val : 0;
                $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice() * ($currency->value ?? 1);

                if ($total >= $giftMinPrice) {
                    $giftprice = 0;
                }
            }

            $giftboxprice = 0;
            $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
            if (session('gift_box')) {
                $setting         = \App\Helpers\Helper::getSetting();
                $gift_box        = session('gift_box');
                $giftBoxSetting  = $setting->firstWhere('id', 43);
                $giftboxprice    = $giftBoxSetting ? $giftBoxSetting->setting_val : 0;
                $giftboxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice() * ($currency->value ?? 1);

                if ($total >= $giftboxMinPrice) {
                    $giftboxprice = 0;
                }
            }

            $shippingcharge = $this->getUpdatedShipping();
            $giftprice      = $giftprice * $currency->value;
            $giftboxprice   = $giftboxprice * $currency->value;
            $shippingcharge =  Helper::getShippingAmount($total,$shippingcharge);
            
            $final_total    = $total + $shippingcharge + $giftprice + $giftboxprice - $couponDiscountFinal;

            $priceUpdate[] = array(
                'id'                  => $id,
                'size_id'             => $sizeId,
                'color_id'            => $colorId,
                'price'               => Helper::formatNumber($sub_total),
                'total'               => Helper::formatNumber($total),
                'shipping'            => Helper::formatNumber($shippingcharge),
                'tax'                 => Helper::formatNumber($taxval),
                'final_total'         => Helper::formatNumber($final_total),
                'couponDiscountFinal' => "- " . Helper::formatNumber($couponDiscountFinal),
                'is_active'           => $intlShipFlag,
            );
        }
        return $priceUpdate;
    }

    public function getUpdatedShipping()
    {
        $user = Auth::user();
        $cart = [];
        $address = null;

        if ($user) {
            $cart = \App\UserModel\Cart\ShoppingCartDetail::where('user_id', $user->id)->get();
            $address = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
        } elseif (session('cart')) {
            $cart = \App\UserModel\Cart\ShoppingCartDetail::where('shopping_cart_id', session('cart'))->get();
        }
        
        $shippingService = new \App\Services\ShippingService();
        $shippingData = $shippingService->calculateFedexShipping($address, $cart);

        return $shippingData['totalShipping'];
    }

    public function customizePage()
    {
        return view('web.customized.index');
    }

    public function customized(Request $request)
    {
        $validate = $request->validate([
            'gift_type'               => 'required',
            'sender_name'             => 'required',
            'receiver_name'           => 'required',
            'email'                   => 'required|min:6',
            'mobile'                  => 'required',
            'gift_occasion'           => 'required',
            'number_of_guests'        => 'required_if:gift_type,giveaways',
            'name_on_card'            => 'required_if:gift_type,giveaways',
            'gift_options'            => 'required',
            'gift_budget'             => 'required_if:gift_type,gift',
            'wrapping_budget'         => 'required_if:gift_type,gift',
            'preferred_delivery_date' => 'required',
        ], [
            'email.required'                   => Lang::get($this->langFile . '.error-req-email'),
            'email.min'                        => Lang::get($this->langFile . '.error-req-email-min'),
            'sender_name.required'             => Lang::get($this->langFile . '.error-req-sender-name'),
            'receiver_name.required'           => Lang::get($this->langFile . '.error-req-receiver-name'),
            'mobile.required'                  => Lang::get($this->langFile . '.error-req-mobile'),
            'gift_occasion.required'           => Lang::get($this->langFile . '.error-req-gift-occasion'),
            'number_of_guests.required_if'     => Lang::get($this->langFile . '.error-req-number-of-guests'),
            'name_on_card.required_if'         => Lang::get($this->langFile . '.error-req-name-on-card'),
            'gift_options.required'            => Lang::get($this->langFile . '.error-req-gift-options'),
            'gift_budget.required_if'          => Lang::get($this->langFile . '.error-req-gift-budget'),
            'wrapping_budget.required_if'      => Lang::get($this->langFile . '.error-req-wrapping-budget'),
            'preferred_delivery_date.required' => Lang::get($this->langFile . '.error-req-preferred-delivery-date'),
        ]);

        $date                         = $request->preferred_delivery_date;
        $date                         = str_replace('/', '-', $date);
        $date                         = date("Y-m-d", strtotime($date));
        $customized                   = new Customized();
        $customized->gift_type        = $request->gift_type;
        $customized->sender_name      = $request->sender_name;
        $customized->receiver_name    = $request->receiver_name;
        $customized->email            = $request->email;
        $customized->mobile           = $request->mobile;
        $customized->gift_occasion    = $request->gift_occasion;
        $customized->number_of_guests = $request->number_of_guests;
        $customized->name_on_card     = $request->name_on_card;
        if ($request->gift_options) {
            $customized->gift_options = implode(",", $request->gift_options);
        }
        $customized->gift_budget = $request->gift_budget;

        $customized->wrapping_budget         = $request->wrapping_budget;
        $customized->preferred_delivery_date = $date;
        $customized->message                 = $request->message;
        /*
         * Default Image Upload
         */
        if ($request->file('photo_reference')) {
            $file = $request->file('photo_reference');
            $path = $this->uploadImage($file);

            if ($path) {
                $customized->photo_reference = $path['path'];
            }
        }
        if ($customized->save()) {
            $this->sendMail($customized);
            return redirect()->route('web.product.customize_page')->with('success', Lang::get($this->langFile . '.customized-message-success'));
        } else {
            return redirect()->route(
                'web.product   .customize_page'
            )->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function uploadImage($file)
    {
        // Get filename with extension
        $filenameWithExt = $file->getClientOriginalName();
        // Get file path
        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Remove unwanted characters
        $filename        = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        $filename        = preg_replace("/\s+/", '-', $filename);
        // Get the original image extension
        $extension       = $file->getClientOriginalExtension();
        // Create unique file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;
        $path            = $file->storeAs(config('filepath.customized_image'), $fileNameToStore);
        if ($path) {
            return ['path' => $path];
        }
        return false;
    }

    public function sendMail($customized)
    {
        $contactEmail     = \App\Helpers\Helper::getSettingById(19);
        $reciverEmail     = \App\Helpers\Helper::getSettingById(18);
        $app_name         = \App\Helpers\Helper::getAppName();
        $app_name         = \App\Helpers\Helper::getAppName();
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
        $reciveEmail      = \App\Helpers\Helper::getSettingById(18);
        $reciveAdminEmail = $reciveEmail ? $reciveEmail->setting_val : "";

        $from['email']                 = $admin_mail;
        $from['name'] = $app_name;
        $to['email']                   = $customized->email;
        $to['name']                    = ucfirst($customized->sender_name);
        $cc['email']                   = $reciveAdminEmail;

        $subject = $app_name . "-Customized Form";
        $message = \View::make('web.emails.customized.index', compact('customized'))->render();

        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return true;
        } else {
            return false;
        }
    }

    public function search(Request $request)
    {
        if (strlen($request->search) >= 2) {
            $language  = Helper::getLanguage();
            $words = array_filter(explode(' ', $request->search));

            if (count($words) === 0) {
                return response()->json([]);
            }

            // 1. Matching Brands
            $activeBrands = \App\AdminModel\Brand\AdminBrand::where('is_active', 1)->pluck('id')->toArray();
            $matchingBrands = [];
            if (count($activeBrands) > 0) {
                $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrands)
                    ->where('language_id', $language->id);
                foreach ($words as $word) {
                    $brandSearch->where('brand_name', 'like', '%' . $word . '%');
                }
                $matchingBrands = $brandSearch->get();
            }

            // 2. Matching Categories
            $matchingCategories = \App\AdminModel\Product\AdminCategoryDescription::join('admin_categories', 'admin_categories.id', '=', 'admin_categories_description.categories_id')
                ->where('admin_categories.is_active', 1)
                ->where('admin_categories.parent_id', 0)
                ->where('admin_categories_description.language_id', $language->id);
            foreach ($words as $word) {
                $matchingCategories->where('admin_categories_description.categories_name', 'like', '%' . $word . '%');
            }
            $matchingCategories = $matchingCategories->select('admin_categories_description.*', 'admin_categories.categories_slug')->get();

            // 3. Matching Subcategories
            $matchingSubcategories = \App\AdminModel\Product\AdminCategoryDescription::join('admin_categories', 'admin_categories.id', '=', 'admin_categories_description.categories_id')
                ->where('admin_categories.is_active', 1)
                ->where('admin_categories.parent_id', '!=', 0)
                ->where('admin_categories_description.language_id', $language->id);
            foreach ($words as $word) {
                $matchingSubcategories->where('admin_categories_description.categories_name', 'like', '%' . $word . '%');
            }
            $matchingSubcategories = $matchingSubcategories->select('admin_categories_description.*', 'admin_categories.categories_slug')->get();

            // 4. Matching Products
            $productQuery = AdminProduct::query();
            $productQuery = Helper::applyCategoryVisibility($productQuery);
            $productQuery = Helper::applyProductVisibility($productQuery);

            foreach ($words as $word) {
                $productQuery->where(function($q) use ($word, $language) {
                    $q->where('product_barcode', 'like', '%' . $word . '%')
                      ->orWhere('product_sku', 'like', '%' . $word . '%')
                      ->orWhereExists(function($sub) use ($word, $language) {
                          $sub->select(DB::raw(1))
                              ->from('admin_products_description')
                              ->whereRaw('admin_products_description.products_id = admin_products.id')
                              ->where('language_id', $language->id)
                              ->where('products_name', 'like', '%' . $word . '%');
                      })
                      ->orWhereExists(function($sub) use ($word, $language) {
                          $sub->select(DB::raw(1))
                              ->from('admin_products_attributes')
                              ->leftJoin('admin_size_description', 'admin_size_description.size_id', '=', 'admin_products_attributes.size_id')
                              ->leftJoin('admin_color_description', 'admin_color_description.color_id', '=', 'admin_products_attributes.color_id')
                              ->whereRaw('admin_products_attributes.products_id = admin_products.id')
                              ->whereNull('admin_products_attributes.deleted_at')
                              ->where('admin_products_attributes.quantity', '>', 0)
                              ->where(function($q2) use ($word, $language) {
                                  $q2->where(function($q3) use ($word, $language) {
                                      $q3->where('admin_size_description.language_id', $language->id)
                                         ->where('admin_size_description.size_title', 'like', '%' . $word . '%');
                                  })->orWhere(function($q3) use ($word, $language) {
                                      $q3->where('admin_color_description.language_id', $language->id)
                                         ->where('admin_color_description.color_title', 'like', '%' . $word . '%');
                                  });
                              });
                      })
                      ->orWhereExists(function($sub) use ($word, $language) {
                          $sub->select(DB::raw(1))
                              ->from('admin_products_categories')
                              ->join('admin_categories', 'admin_categories.id', '=', 'admin_products_categories.categories_id')
                              ->join('admin_categories_description', 'admin_categories_description.categories_id', '=', 'admin_categories.id')
                              ->whereRaw('admin_products_categories.products_id = admin_products.id')
                              ->where('admin_categories_description.language_id', $language->id)
                              ->where('admin_categories_description.categories_name', 'like', '%' . $word . '%');
                      })
                      ->orWhereExists(function($sub) use ($word, $language) {
                          $sub->select(DB::raw(1))
                              ->from('admin_categories')
                              ->join('admin_categories_description', 'admin_categories_description.categories_id', '=', 'admin_categories.id')
                              ->whereRaw('admin_categories.id = admin_products.category_id')
                              ->where('admin_categories_description.language_id', $language->id)
                              ->where('admin_categories_description.categories_name', 'like', '%' . $word . '%');
                      })
                      ->orWhereExists(function($sub) use ($word, $language) {
                          $sub->select(DB::raw(1))
                              ->from('admin_brand')
                              ->join('admin_brand_description', 'admin_brand_description.brand_id', '=', 'admin_brand.id')
                              ->whereRaw('admin_brand.id = admin_products.brand_id')
                              ->where('admin_brand_description.language_id', $language->id)
                              ->where('admin_brand_description.brand_name', 'like', '%' . $word . '%');
                      });
                });
            }

            $matchedProductIds = $productQuery->pluck('id')->toArray();

            $results = [];

            // 1. Brands
            foreach ($matchingBrands as $brand) {
                $results[] = [
                    'label' => $brand->brand_name,
                    'category' => 'Matching Brands',
                    'url' => route('web.product.shop_by_brand', ['b[]' => $brand->brand_name])
                ];
            }

            // 2. Categories
            foreach ($matchingCategories as $cat) {
                $results[] = [
                    'label' => $cat->categories_name,
                    'category' => 'Matching Collection',
                    'url' => route('web.product.index', ['slug' => $cat->categories_slug, 'isAll' => 1, 'sCId' => $cat->id,])
                ];
            }

            // 3. Subcategories
            foreach ($matchingSubcategories as $subcat) {
                $results[] = [
                    'label' => $subcat->categories_name,
                    'category' => 'Matching Collection',
                    'url' => route('web.product.index', ['slug' => $subcat->categories_slug])
                ];
            }

            // 4. Products
            if (count($matchedProductIds) > 0) {
                $productNames = \App\AdminModel\Product\AdminProductDescription::whereIn('products_id', $matchedProductIds)
                    ->where('language_id', $language->id)
                    ->groupby('products_name')
                    ->pluck('products_name')
                    ->toArray();

                foreach ($productNames as $name) {
                    $results[] = [
                        'label' => $name,
                        'value' => $name,
                        'category' => 'Matching Products'
                    ];
                }
            }

            return response()->json($results);
        }
        return response()->json([]);
    }

    /*
     * Check Coupon Code
     */

    public function checkCouponCode(Request $request)
    {
        $user = Auth::user();

        if ($request->coupon_code == '') {

            $response = [
                'status'  => false,
                'message' => __('web.entert_coupon'),
                'data'    => [],
            ];
        } else {

            $response = [
                'status'  => false,
                'message' => __('web.coupon_invalid'),
                'data'    => [],
            ];
        }
        if (isset($request->coupon_code) && $request->coupon_code) {
            $couponCode = new \App\UserModel\CouponCode();
            $couponCode = $couponCode->validateCouponCode($request->coupon_code, $user);
            if ($couponCode) {
                if ($couponCode->is_applied == 0) {
                    $response = [
                        'status'  => true,
                        'message' => __('web.coupon_applied'),
                        'data'    => [
                            'id'                 => $couponCode->coupon_id,
                            'coupon_code'        => $couponCode->coupon_code,
                            'user_id'            => $couponCode->user_id,
                            'coupon_discount_in' => $couponCode->coupon_discount_in,
                            'coupon_discount'    => $couponCode->coupon_discount,
                            'allow_multiple'     => $couponCode->allow_multiple,
                        ],
                    ];
                } else {
                    $response = [
                        'status'  => false,
                        'message' => __('web.coupon_already_redeemed'),
                        'data'    => [],
                    ];
                }
            }
        } else if (isset($request->remove_coupon) && $request->remove_coupon) {
            $response = [
                'status'    => true,
                'message'   => __('web.coupon_removed'),
                'data'      => [],
                'cart_data' => $this->getUpdatedProduct(),
            ];
        }

        session()->put('couponCode', $response['data']);

        $response['cart_data'] = $this->getUpdatedProduct();

        return $response;
    }

    public function getSize(Request $request)
    {
        $product           = '';
        $validationRule    = [
            'product_id' => "required|numeric",
            'color'      => "required|numeric",
        ];
        $validationMessage = [
            'product_id.required' => Lang::get($this->langFile . '.error-cart-req-id'),
            'product_id.numeric'  => Lang::get($this->langFile . '.error-cart-numeric-id'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);
        $user          = Auth::user();
        $productdata   = AdminProduct::where('id', $request->product_id)->first();

        $cartStatus = Helper::getCartProductStatus($user, $request->product_id, $request->sizeExist, $request->size, $request->colorExist, $request->color);
        if ($productdata->have_size) {

            $size = \App\AdminModel\Product\AdminProductAttribute::where('color_id', $request->color)->where('size_id', '!=', null)->where('products_id', $request->product_id)->where('quantity', '>', 0)->orderBy('sorting')->pluck('size_id')->toArray();;
        } else {
            $size = [];
        }
        $productsize = '';

        if (count($size) > 0) {

            $is_size     = true;
			
			if ($size) {

                $sz = implode(",", $size);
            }
            $productsize = \App\AdminModel\Size\AdminSize::whereIn('id', $size)->where('is_active', 1)->orderByRaw("FIELD(id, $sz)")->get();

            if (count($productsize) > 0) {
                $product = \App\AdminModel\Product\AdminProductAttribute::where('color_id', $request->color)->where('size_id', $productsize[0]->id)->where('products_id', $request->product_id)->where(
                    'quantity',
                    '>',
                    0
                )->first();
            }

            $sizeOptionView = \View::make('web.product.product-size', ['productSize' => $productsize])->render();
        } else {
            $product = \App\AdminModel\Product\AdminProductAttribute::where('color_id', $request->color)->where('size_id', null)->where('products_id', $request->product_id)->where('quantity', '>', 0)->first();
            $is_size = false;

            $sizeOptionView = '';
        }

        if ($product) {

            $currency                       = Helper::getDefaultCurrency();
    //        $product->product_regular_price = Helper::formatNumber($product->product_regular_price );
     //       $product->product_sale_price    = Helper::formatNumber($product->product_sale_price * $currency->value);
        }

        return ['sizeOptionView' => $sizeOptionView, 'is_size' => $is_size, 'product' => $product];
    }

    public function getProductImaage(Request $request)
    {
        $productImage = [];
        $productQuery = AdminProduct::where('id', $request->product_id);
        $productQuery = Helper::applyProductVisibility($productQuery);
        $product = $productQuery->first();
        if ($product) {
            $productImage = $product->getVariantImagesFallback($request->color ? $request->color : null, $request->size ? $request->size : null, true);
        }
        //        dd($productImage);

        return \View::make('web.product.product-image', ['productImage' => $productImage, 'product' => $product, 'color' => $request->color ? $request->color : ''])->render();
    }

    public function monogram(Request $request)
    {
        $cart         = [];
        $editmonogram = [];
        $user         = Auth::user();
        $language     = Helper::getLanguage();
        if ($user) {
            // get cart from db
            $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
        }
        //if user not login get product from session
        if (session('cart')) {
            // get cart from session
            $cart = session('cart');
        }
        if ($request->product_id) {
            $cartStatus  = Helper::getCartProductStatus($user, $request->product_id, $request->sizeExist, $request->size, $request->colorExist, $request->color);
            $cartproduct = Helper::getCartProduct($user, $request->product_id, $request->sizeExist, $request->sizeExist ? $request->size : 's', $request->colorExist, $request->colorExist ? $request->color : 'c', $request->monogramNo);
            //            dd($cartproduct);
            if (count($cartproduct) > 0) {
                //                if ($user)
                //                {
                //                    if (count($cartproduct) > 0 && $cartproduct['is_monogram'] == 1)
                //                    {
                //                        $cartproduct['monogram'] = $cartproduct;
                //                    }
                //                    else
                //                    {
                //                        $cartproduct['monogram'] = [];
                //                    }
                //                }

                $mongram_text = '';
                $monogramNo   = $request->monogramNo;

                //                dd($cartproduct);
                if (count($cartproduct['monogram']) > 0 && $cartproduct['is_monogram'] == 1 && $monogramNo != '') {

                    $initials = config('custom_web.initials_front');
                    $count    = $initials[$cartproduct['monogram'][$monogramNo]['initials_count']];

                    $mongram_text = 'A-B-C';
                    if ($count == 1) {
                        $mongram_text = strtoupper($cartproduct['monogram'][$monogramNo]['center_text']);
                    }
                    if ($count == 2) {
                        $mongram_text = strtoupper($cartproduct['monogram'][$monogramNo]['left_text']) . '-' . strtoupper($cartproduct['monogram'][$monogramNo]['right_text']);
                    }
                    if ($count == 3) {
                        $mongram_text = strtoupper($cartproduct['monogram'][$monogramNo]['left_text']) . '-' . strtoupper($cartproduct['monogram'][$monogramNo]['center_text']) . '-' . strtoupper($cartproduct['monogram'][$monogramNo]['right_text']);
                    }
                    if ($count == 4) {
                        $mongram_text = strtoupper($cartproduct['monogram'][$monogramNo]['text4']);
                    }
                    $cartproduct['monogram'][$monogramNo]['mongram_text'] = $mongram_text;
                    $cartproduct['monogram'][$monogramNo]['size_id']      = $request->sizeExist ? $request->size : '';
                    $cartproduct['monogram'][$monogramNo]['color_id']     = $request->colorExist ? $request->color : '';
                    $cartproduct['monogram'][$monogramNo]['sizeExist']    = $request->sizeExist;
                    $cartproduct['monogram'][$monogramNo]['colorExist']   = $request->colorExist;
                    $cartproduct['monogram'][$monogramNo]['product_id']   = $request->product_id;

                    $editmonogram = $cartproduct['monogram'][$monogramNo];
                }
            }
            $productQuantity = Helper::getProductQuantity($request->product_id, $request->sizeExist, $request->size, $request->colorExist, $request->color);
        }
        $font_style_id = AdminProductMonogram::where('product_id', $request->product_id)->pluck('font_style_id');
        if (count($font_style_id) > 0) {
            $style     = MonogramFontStyle::selectRaw('id as fontId,style_slug as folderName,style_image_svg as defaultImage, has_varient as variants, has_border as hasBorder ,has_preview as preview')->whereIn('id', $font_style_id)->get()->toArray();
            $monogram  = array();
            $color     = array();
            $placement = array();
            $mongramId = '';
            if (count($style) > 0) {
                foreach ($style as $key => $value) {
                    $style[$key]['categories']   = ["all"];
                    $style[$key]['variants']     = $style[$key]['variants'] == 1 ? 'yes' : 'no';
                    $style[$key]['hasBorder']    = $style[$key]['hasBorder'] == 1 ? 'yes' : 'no';
                    $style[$key]['preview']      = $style[$key]['preview'] == 1 ? 'yes' : 'no';
                    $style[$key]['defaultImage'] = asset(Storage::url($style[$key]['defaultImage']));
                    $style[$key]['size_id']      = $request->sizeExist ? $request->size : '';
                    $style[$key]['color_id']     = $request->colorExist ? $request->color : '';
                    $style[$key]['sizeExist']    = $request->sizeExist;
                    $style[$key]['colorExist']   = $request->colorExist;
                    $style[$key]['product_id']   = $request->product_id;
                }
                $mongramId = $style[0]['fontId'];
            }
            if ($mongramId) {
                $color     = MonogramMaster::selectRaw('value1 as color_id')->where('font_style_id', $mongramId)->where('type', 'color')->get()->toArray();
                // $placement = MonogramMaster::where('font_style_id', $mongramId)->where('type', 'placement')->pluck('value1')->toArray();
                $placement  = ProductPlacement::where('product_id', $request->product_id)->where('monogram_id', $mongramId)->pluck('placement_id')->toArray();
            }
            if (count($color) > 0) {
                foreach ($color as $key => $value) {
                    $colorName                = MonogramColor::where('id', $value['color_id'])->first();
                    $color[$key]['colorName'] = $colorName->color_name;
                    $color[$key]['hexCode']   = $colorName->color_hash;

                    $color[$key]['id'] = $value['color_id'];
                }
            }
            if (count($placement) > 0) {

                $placements = MonogramPlacement::whereIn('id', $placement)->where('is_active', 1)->get()->toArray();
                foreach ($placements as $key => $value) {
                    $placements[$key]['placementId']    = $value['id'];
                    $placements[$key]['placementImage'] = asset(Storage::url($value['placement_image']));
                    $placements[$key]['categories']     = ['all'];
                    $placements[$key]['placementSize']  = Helper::getPlacementName($value['id']);
                    $placements[$key]['placementDesc']  = $value['placement_desc'];
                }
            }

            return $monogram = [array('fonts' => $style), array('colors' => $color), array('placement' => $placements), array('editmonogram' => $editmonogram)];
        } else {
            return $monogram = [array('fonts' => []), array('colors' => []), array('placement' => []), array('editmonogram' => [])];
        }
    }

    public function getInitials(Request $request)
    {

        $user = Auth::user();

        if ($request->id) {
            $monogramtId = $request->id;
            $initialsId  = MonogramMaster::where('font_style_id', $monogramtId)->where('type', 'initials')->get()->pluck('value1');

            if (count($initialsId) > 0) {
                $cartproduct = Helper::getCartProduct($user, $request->product_id, $request->sizeExist, $request->sizeExist ? $request->size : 's', $request->colorExist, $request->colorExist ? $request->color : 'c', $request->monogrammNo);
                if (count($cartproduct) > 0) {
                    //                    if ($user)
                    //                    {
                    //
                    //                        if (count($cartproduct) > 0 && $cartproduct['is_monogram'] == 1)
                    //                        {
                    //                            $cartproduct['monogram'] = $cartproduct;
                    //                        }
                    //                        else
                    //                        {
                    //                            $cartproduct['monogram'] = [];
                    //                        }
                    //                    }
                    if (count($cartproduct['monogram']) > 0 && $cartproduct['is_monogram'] == 1) {
                        $initials = config('custom_web.initials_front');
                        if (isset($request->monogrammNo)) {
                            //                            dd($cartproduct['monogram']);
                            $count = $initials[$cartproduct['monogram'][$request->monogrammNo]['initials_count']];

                            $selected_id = MonogramMaster::where('font_style_id', $monogramtId)->where('type', 'initials')->where('value1', $count)->orderBy('value1', 'desc')->limit(1)->get();
                        } else {
                            $selected_id = MonogramMaster::where(
                                'font_style_id',
                                $monogramtId
                            )->where('type', 'initials')->where('value1', '<=', 3)->orderBy('value1', 'desc')->limit(1)->get();
                        }
                    } else {
                        $selected_id = MonogramMaster::where(
                            'font_style_id',
                            $monogramtId
                        )->where('type', 'initials')->where('value1', '<=', 3)->orderBy('value1', 'desc')->limit(1)->get();
                    }
                } else {

                    $selected_id = MonogramMaster::where('font_style_id', $monogramtId)->where('type', 'initials')->where('value1', '<=', 3)->orderBy('value1', 'desc')->limit(1)->get();
                }

                //                dd($selected_id);
                $Initials     = MonogramLetterCount::whereIn('id', $initialsId)->get();
                $InitialsHtml = \View::make('web.layout.initials', ['initials' => $Initials, 'selected' => $selected_id])->render();

                $selectedArray = config('custom_web.initials');
                return ['view' => $InitialsHtml, 'selected' => $selectedArray[$selected_id[0]->value1], 'selected_id' => $selected_id[0]->value1];
            }
            return '';
        }
        return '';
    }

    public function removeMonogram($product_id, $size_id = '', $color_id = '', $monogramNo = '')
    {
        $user = Auth::user();
        if ($user) {
            DB::beginTransaction();
            $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $product_id);
            if ($size_id && $size_id != 's') {
                $shoppingCartDetail->where('size_id', $size_id);
            }
            if ($color_id && $color_id != 'c') {
                $shoppingCartDetail->where('color_id', $color_id);
            }
            $shoppingCartDetail = $shoppingCartDetail->first();

            if ($shoppingCartDetail) {
                $monogramShoppingCartDetail = MonogramShoppingCartDetail::where('id', $monogramNo);
                if ($monogramShoppingCartDetail->delete()) {
                    $monogrmCount = MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail->id)->count();
                    if ($monogrmCount == 0) {
                        $shoppingCartDetail->is_same     = 0;
                        $shoppingCartDetail->is_monogram = 0;
                        $shoppingCartDetail->save();
                    }
                    DB::commit();
                    return true;
                } else {
                    DB::rollback();
                    return false;
                }
            } else {
                return false;
            }
        }

        $cart = session()->get('cart');
        if ($cart) {

            DB::beginTransaction();
            $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $product_id);
            if ($size_id && $size_id != 's') {
                $shoppingCartDetail->where('size_id', $size_id);
            }
            if ($color_id && $color_id != 'c') {
                $shoppingCartDetail->where('color_id', $color_id);
            }
            $shoppingCartDetail = $shoppingCartDetail->first();

            if ($shoppingCartDetail) {

                $monogramShoppingCartDetail = MonogramShoppingCartDetail::where('id', $monogramNo);
                if ($monogramShoppingCartDetail->delete()) {
                    $monogrmCount = MonogramShoppingCartDetail::where('shopping_cart_detail_id', $shoppingCartDetail->id)->count();
                    if ($monogrmCount == 0) {
                        $shoppingCartDetail->is_same     = 0;
                        $shoppingCartDetail->is_monogram = 0;
                        $shoppingCartDetail->save();
                    }

                    DB::commit();
                    return true;
                } else {
                    DB::rollback();
                    return false;
                }
            } else {
                return false;
            }

            return true;
        } else {
            if (isset($_COOKIE['temp_monogram'])) {
                $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);
                if ($temp_monogram && isset($temp_monogram[$id][$monogramNo])) {
                    unset($_COOKIE['temp_monogram']);
                    setcookie('temp_monogram', null, -1, '/');
                    unset($temp_monogram[$id][$monogramNo]);
                    setcookie('temp_monogram', json_encode($temp_monogram), time() + (86400 * 30), "/");
                } else {
                    return false;
                }
            }
        }
    }

    public function updateMonogram(Request $request)
    {
        $product       = AdminProduct::find($request->id);
        $temp_monogram = [];
        if ($product) {
            if ($request->sizeExist && $request->colorExist) {
                $returnKey = $product->id . '_' . $request->size . '_' . $request->color;
            } elseif ($request->sizeExist) {
                $returnKey = $product->id . '_' . $request->size . '_c';
            } elseif ($request->colorExist) {
                $returnKey = $product->id . '_s_' . $request->color;
            } else {
                $returnKey = $product->id . '_s_c';
            }
            $user = Auth::user();
            if ($user) {
                $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $product->id);
                if ($request->sizeExist) {
                    $shoppingCartDetail->where('size_id', $request->size);
                }
                if ($request->colorExist) {
                    $shoppingCartDetail->where('color_id', $request->color);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();
                if ($shoppingCartDetail) {
                    if ($request->monogramExist) {
                        $shoppingCartDetail->is_monogram = 1;
                        $shoppingCartDetail->save();
                        $monogramShoppingCartDetail      = new MonogramShoppingCartDetail();

                        if ($request->product_cart_id) {
                            $monogramShoppingCartDetail = MonogramShoppingCartDetail::where('id', $request->product_cart_id)->first();
                        }
                        $initials = config('custom_web.initials_front');
                        $count    = $initials[$request->initials_count];
                        $left     = '';
                        $center   = '';
                        $right    = '';
                        $text     = '';
                        if ($count == 1) {
                            $center = $request->text2;
                        }
                        if ($count == 2) {
                            $left  = $request->text1;
                            $right = $request->text3;
                        }
                        if ($count == 3) {
                            $left   = $request->text1;
                            $center = $request->text2;
                            $right  = $request->text3;
                        }
                        if ($count == 4) {
                            $text = $request->text4;
                        }
                        $monogramShoppingCartDetail->shopping_cart_detail_id = $shoppingCartDetail->id;
                        $monogramShoppingCartDetail->font                    = $request->font;
                        $monogramShoppingCartDetail->initials_count          = $request->initials_count;
                        $monogramShoppingCartDetail->left_text               = strtoupper($left);
                        $monogramShoppingCartDetail->center_text             = strtoupper($center);
                        $monogramShoppingCartDetail->right_text              = strtoupper($right);
                        $monogramShoppingCartDetail->text4                   = strtoupper($text);
                        $monogramShoppingCartDetail->monogram_color          = $request->monogram_color;
                        $monogramShoppingCartDetail->monogram_color_hash     = $request->monogram_color_hash;
                        $monogramShoppingCartDetail->monogram_color_id       = $request->monogram_color_id;
                        $monogramShoppingCartDetail->placement               = $request->placement;
                        $monogramShoppingCartDetail->monogram_note           = $request->monogram_note;
                    }
                    $monogramShoppingCartDetail->save();
                    return array('key' => $returnKey, 'id' => $monogramShoppingCartDetail->id, 'is_login' => $user ? true : false);
                }

                if ($request->monogramExist) {
                    $initials = config('custom_web.initials_front');
                    $count    = $initials[$request->initials_count];
                    $left     = '';
                    $center   = '';
                    $right    = '';
                    $text     = '';
                    if ($count == 1) {
                        $center = $request->text2;
                    }
                    if ($count == 2) {
                        $left  = $request->text1;
                        $right = $request->text3;
                    }
                    if ($count == 3) {
                        $left   = $request->text1;
                        $center = $request->text2;
                        $right  = $request->text3;
                    }
                    if ($count == 4) {
                        $text = $request->text4;
                    }

                    $monogram = array(
                        'is_monogram'         => 1,
                        'font'                => $request->font,
                        'initials_count'      => $request->initials_count,
                        'left_text'           => strtoupper($left),
                        'center_text'         => strtoupper($center),
                        'right_text'          => strtoupper($right),
                        'text4'               => strtoupper($text),
                        'monogram_color'      => $request->monogram_color,
                        'monogram_color_hash' => $request->monogram_color_hash,
                        'monogram_color_id'   => $request->monogram_color_id,
                        'placement'           => $request->placement,
                        'monogram_note'       => $request->monogram_note,
                    );
                } else {
                    $monogram = array();
                }

                $mid = $request->monogram_id;
                if (isset($_COOKIE['temp_monogram'])) {
                    $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);

                    //                    if (isset($temp_monogram [$returnKey]) && $request->monogramExist)
                    //                    {
                    unset($_COOKIE['temp_monogram']);
                    setcookie('temp_monogram', null, -1, '/');
                    //                    }
                }
                $temp_monogram[$returnKey][$mid] = $monogram;

                setcookie('temp_monogram', json_encode($temp_monogram), time() + (86400 * 30), "/");
            } else {
                $cart = session()->get('cart');
                if (isset($cart)) {
                    $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $product->id);
                    if ($request->sizeExist) {
                        $shoppingCartDetail->where('size_id', $request->size);
                    }
                    if ($request->colorExist) {
                        $shoppingCartDetail->where('color_id', $request->color);
                    }
                    $shoppingCartDetail = $shoppingCartDetail->first();

                    if ($shoppingCartDetail) {
                        if ($request->monogramExist) {
                            $shoppingCartDetail->is_monogram = 1;
                            $shoppingCartDetail->save();
                            $monogramShoppingCartDetail      = new MonogramShoppingCartDetail();

                            if ($request->product_cart_id) {
                                $monogramShoppingCartDetail = MonogramShoppingCartDetail::where('id', $request->product_cart_id)->first();
                            }
                            $initials = config('custom_web.initials_front');
                            $count    = $initials[$request->initials_count];
                            $left     = '';
                            $center   = '';
                            $right    = '';
                            $text     = '';
                            if ($count == 1) {
                                $center = $request->text2;
                            }
                            if ($count == 2) {
                                $left  = $request->text1;
                                $right = $request->text3;
                            }
                            if ($count == 3) {
                                $left   = $request->text1;
                                $center = $request->text2;
                                $right  = $request->text3;
                            }
                            if ($count == 4) {
                                $text = $request->text4;
                            }
                            $monogramShoppingCartDetail->shopping_cart_detail_id = $shoppingCartDetail->id;
                            $monogramShoppingCartDetail->font                    = $request->font;
                            $monogramShoppingCartDetail->initials_count          = $request->initials_count;
                            $monogramShoppingCartDetail->left_text               = strtoupper($left);
                            $monogramShoppingCartDetail->center_text             = strtoupper($center);
                            $monogramShoppingCartDetail->right_text              = strtoupper($right);
                            $monogramShoppingCartDetail->text4                   = strtoupper($text);
                            $monogramShoppingCartDetail->monogram_color          = $request->monogram_color;
                            $monogramShoppingCartDetail->monogram_color_hash     = $request->monogram_color_hash;
                            $monogramShoppingCartDetail->monogram_color_id       = $request->monogram_color_id;
                            $monogramShoppingCartDetail->placement               = $request->placement;
                            $monogramShoppingCartDetail->monogram_note           = $request->monogram_note;
                        }
                        $monogramShoppingCartDetail->save();
                        return array('key' => $returnKey, 'id' => $monogramShoppingCartDetail->id, 'is_login' => $user ? true : false);
                    }
                }

                if ($request->monogramExist) {
                    $initials = config('custom_web.initials_front');
                    $count    = $initials[$request->initials_count];
                    $left     = '';
                    $center   = '';
                    $right    = '';
                    $text     = '';
                    if ($count == 1) {
                        $center = $request->text2;
                    }
                    if ($count == 2) {
                        $left  = $request->text1;
                        $right = $request->text3;
                    }
                    if ($count == 3) {
                        $left   = $request->text1;
                        $center = $request->text2;
                        $right  = $request->text3;
                    }
                    if ($count == 4) {
                        $text = $request->text4;
                    }

                    $monogram = array(
                        'is_monogram'         => 1,
                        'font'                => $request->font,
                        'initials_count'      => $request->initials_count,
                        'left_text'           => strtoupper($left),
                        'center_text'         => strtoupper($center),
                        'right_text'          => strtoupper($right),
                        'text4'               => strtoupper($text),
                        'monogram_color'      => $request->monogram_color,
                        'monogram_color_hash' => $request->monogram_color_hash,
                        'monogram_color_id'   => $request->monogram_color_id,
                        'placement'           => $request->placement,
                        'monogram_note'       => $request->monogram_note,
                    );
                } else {
                    $monogram = array();
                }

                $mid = $request->monogram_id;
                if (isset($_COOKIE['temp_monogram'])) {
                    $temp_monogram = json_decode($_COOKIE['temp_monogram'], true);

                    //                    if (isset($temp_monogram [$returnKey]) && $request->monogramExist)
                    //                    {
                    unset($_COOKIE['temp_monogram']);
                    setcookie('temp_monogram', null, -1, '/');
                    //                    }
                }
                $temp_monogram[$returnKey][$mid] = $monogram;

                setcookie('temp_monogram', json_encode($temp_monogram), time() + (86400 * 30), "/");
            }
        } else {
            return false;
        }
    }

    public function placement($id, $pid)
    {
        if ($id && $pid) {
            $color     = MonogramMaster::selectRaw('value1 as color_id')->where('font_style_id', $id)->where('type', 'color')->get()->toArray();
            // $placement = MonogramMaster::where('font_style_id', $id)->where('type', 'placement')->pluck('value1')->toArray();
            $placement  = ProductPlacement::where('product_id', $pid)->where('monogram_id', $id)->pluck('placement_id')->toArray();

            if (count($color) > 0) {
                foreach ($color as $key => $value) {
                    $colorName                = MonogramColor::where('id', $value['color_id'])->first();
                    $color[$key]['colorName'] = $colorName->color_name;
                    $color[$key]['hexCode']   = $colorName->color_hash;
                    $color[$key]['id']        = $value['color_id'];
                }
            }
            if (count($placement) > 0) {
                $placement = MonogramPlacement::whereIn('id', $placement)->where('is_active', 1)->get();

                foreach ($placement as $key => $value) {
                    $placement[$key]['placementId']    = $value->id;
                    $placement[$key]['placementImage'] = asset(Storage::url($value->placement_image));
                    $placement[$key]['categories']     = ['all'];
                    $placement[$key]['placementSize']  = Helper::getPlacementName($value->id);
                    $placement[$key]['placementDesc']  = $value->placement_desc;
                }
            }

            return [array('placement' => $placement), array('colors' => $color)];
        }
    }

    public function apply_same($cart_id)
    {
        $cart = session()->get('cart');
        $user = Auth::user();
        if ($user) {
            $productId = $cart_id;
            $sizeId    = "";
            $colorId   = "";

            $productSize = $cart_id;
            $productSize = explode("_", $productSize);
            if ($productSize) {
                if (isset($productSize[0])) {
                    $productId = $productSize[0];
                }
                if (isset($productSize[1]) && $productSize[1] != 's') {
                    $sizeId = $productSize[1];
                }
                if (isset($productSize[2]) && $productSize[2] != 'c') {
                    $colorId = $productSize[2];
                }
            }

            $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $productId);
            if ($sizeId) {
                $shoppingCartDetail->where('size_id', $sizeId);
            }
            if ($colorId) {
                $shoppingCartDetail->where('color_id', $colorId);
            }
            $shoppingCartDetail = $shoppingCartDetail->first();

            if ($shoppingCartDetail) {
                DB::beginTransaction();

                $is_same = 0;
                if ($shoppingCartDetail->is_same == 0) {
                    $is_same = 1;
                }
                $shoppingCartDetail->is_same = $is_same;
                if ($shoppingCartDetail->save()) {
                    DB::commit();
                    return array('status' => true, 'data' => $this->getUpdatedProduct());
                } else {
                    DB::rollback();
                    return false;
                }
            }
        } else {
            if (isset($cart)) {
                $productId = $cart_id;
                $sizeId    = "";
                $colorId   = "";

                $productSize = $cart_id;
                $productSize = explode("_", $productSize);
                if ($productSize) {
                    if (isset($productSize[0])) {
                        $productId = $productSize[0];
                    }
                    if (isset($productSize[1]) && $productSize[1] != 's') {
                        $sizeId = $productSize[1];
                    }
                    if (isset($productSize[2]) && $productSize[2] != 'c') {
                        $colorId = $productSize[2];
                    }
                }

                $shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $productId);
                if ($sizeId) {
                    $shoppingCartDetail->where('size_id', $sizeId);
                }
                if ($colorId) {
                    $shoppingCartDetail->where('color_id', $colorId);
                }
                $shoppingCartDetail = $shoppingCartDetail->first();

                if ($shoppingCartDetail) {
                    DB::beginTransaction();

                    $is_same = 0;
                    if ($shoppingCartDetail->is_same == 0) {
                        $is_same = 1;
                    }
                    $shoppingCartDetail->is_same = $is_same;
                    if ($shoppingCartDetail->save()) {
                        DB::commit();
                        return array('status' => true, 'data' => $this->getUpdatedProduct());
                    } else {
                        DB::rollback();
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public function updatePlacement()
    {
        $monogram =  AdminProductMonogram::get();
        foreach ($monogram as $value) {

            $placement = MonogramMaster::where('font_style_id', $value->font_style_id)->where('type', 'placement')->pluck('value1')->toArray();
            foreach ($placement  as  $record) {
                $ProductPlacement   = new ProductPlacement();
                $ProductPlacement->monogram_id = $value->font_style_id;
                $ProductPlacement->product_id = $value->product_id;
                $ProductPlacement->placement_id = $record;
                $ProductPlacement->save();
            }
        }
    }
}

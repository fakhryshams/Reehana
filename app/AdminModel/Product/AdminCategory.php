<?php

namespace App\AdminModel\Product;

use App\AdminModel\Brand\AdminBrand;
use App\AdminModel\Brand\AdminBrandDescription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AdminCategory extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_categories';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data) {
            $data->updated_by = auth()->user()->id;
        });

        static::deleting(function ($data) {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $parentID, $pageLength)
    {
        switch ($sortKey) {
            case 'category_name':
                $record = $this->sortByCategory($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }
        /*
         * To Show Parent Category Only
         */
        if ($parentID && $parentID == 'all') {
            $query->where('parent_id', '!=', 0);
        } else {
            $query->where('parent_id', $parentID);
        }

        if ($searchText) {
            $query->where(function ($query) use ($searchText) {
                $query->orWhere('id', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('categories_slug', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('show_in', 'LIKE', '%' . $searchText . '%');
                //$query->orWhere('min_order_quantity', 'LIKE', '%' . $searchText . '%');
                //$query->orWhere('max_order_quantity', 'LIKE', '%' . $searchText . '%');
                $query->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
                $query->orWhereDate('updated_at', '=', date('Y-m-d', strtotime($searchText)));
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active') {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive') {
                    $query->orWhere('is_active', 0);
                }
                /*
                 * Search by category
                 */
                $record = $this->searchbByCategory($searchText);
                if ($record) {
                    $query->orWhereIn('id', $record);
                }
            });
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    /*
     * Search Functions
     */

    public function searchbByCategory($searchText)
    {
        $record = AdminCategoryDescription::where('categories_name', 'LIKE', '%' . $searchText . '%')->pluck('categories_id');
        return $record ? $record : false;
    }

    /*
     * End of Search Functions
     */

    /*
     * Sorting Functions
     */

    public function sortByCategory($sortKey, $sortVal)
    {
        return AdminCategoryDescription::orderBy('categories_name', $sortVal)->pluck('id')->toArray();
    }

    /*
     * End of Sorting Functions
     */

    /*
     * Add - Edit - List Page
     */

    public function getParent($parentId)
    {
        return $this->find($parentId);
    }

    public function getCategoryDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany('App\AdminModel\Product\AdminCategoryDescription', 'categories_id', 'id');
        if ($languageId) {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public static function getCategories($status = "", $getChildOnly = false)
    {
        $query = AdminCategory::where('id', '!=', null)->orderBy('sorting', 'asc');
        if ($status) {
            $query->where('is_active', $status);
        }
        if ($getChildOnly) {
            $query->where('parent_id', '!=', 0);
        }
        return $query->get();
    }

    public function getChildCount($id)
    {
        $count = $this->where('parent_id', $id)->count();
        if ($count == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkSlug($currentSlug, $excludedId = '')
    {
        $query = AdminCategory::where('categories_slug', $currentSlug);
        if ($excludedId) {
            $query->where('id', '!=', $excludedId);
        }
        return $query->get();
    }

    public static function updateSlug($categoriesID, $slug)
    {
        $record = AdminCategory::find($categoriesID);
        if ($record) {
            $record->categories_slug = $slug;
            return $record->save();
        }
        return false;
    }

    public function getTotalPhoto($id)
    {
        return \App\AdminModel\Catalogue\AdminPhotoCategory::where('categories_id', $id)->count();
    }

    public function getSortingNumber($isParent = false)
    {
        $query = $this->where('id', '!=', null);
        if ($isParent) {
            $query->where('parent_id', '!=', '0');
        } else {
            $query->where('parent_id', '=', '0');
        }
        return $query->max('sorting');
    }

    /*
     * Front side functoions
     */

    public static function getSizeCategories()
    {
        $query = AdminCategory::where(['is_active' => 1])->orderBy('sorting', 'asc');
        return $query->get();
    }

    public static function getCategoriesFront($categoryId = "", $categoryIdByType = [], $showIn = "", $filterType = "", $price = "", $color = "", $search = "", $size = "", $brand = "", $mainCategoryId = false, $searchedProductCategoryIds = false, $user = null)
    {
        /*
         * Get Products Id and then get category id of those products
         */
        $productCategories = [];

        if ($price || $size || $color || $filterType) {
            $query = AdminProduct::where('is_active', 1)->where('is_active_category', 1);

            if ($filterType == 'latest_collection') {
                $query->where('is_latest_collection', 1);
            } else if ($filterType == 'new_arrivals') {
                $query->where('is_new_arrival', 1);
                $query->where('product_quantity', '>', 0);
            } else if ($filterType == 'sale') {
                $query->where(function ($q) {
                    $q->where('is_product_sale_price_null', 0)
                        ->orWhere('is_category_discount', 1);
                });
            } else if ($filterType == 'shop_by_brand') {
                $query->whereNotNull('brand_id');
            } else if ($filterType == 'product') {
                if ($search) {
                    $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);
                    $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();


                    if ($categoryProduct) {
                        $query->whereIn('id', $categoryProduct);
                    }
                    $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                    if (count($productSearch)) {
                        $query->whereIn('id', $productSearch);
                    }

                    $brandSearch = [];
                    $activeBrand = AdminBrand::where('is_active', 1)->pluck('id');
                    if (count($activeBrand)) {
                        $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand);
                        if ($search) {
                            $brandSearch->Where('brand_name', 'LIKE', '%' . $search . '%');
                        }
                        $brandSearch = $brandSearch->pluck('brand_id');

                        if (count($brandSearch)) {
                            $query->whereIn('brand_id', $brandSearch);
                        }
                    }
                }
            }

            if ($price) {
                $price = explode("-", $price);
                if ($price && isset($price[0]) && isset($price[1])) {
                    $min              = $price[0];
                    $max              = $price[1];
                    //$query->where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max);
                    $priceProductsIds = AdminProductAttribute::where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max)->pluck('products_id');
                    $query->whereIn('id', $priceProductsIds);
                }
            }
            if ($color) {
                $record = AdminProductAttribute::whereIn('color_id', $color)->pluck('products_id');
                if ($record) {
                    $query->whereIn('id', $record);
                }
            }
            if ($size) {
                $record = AdminProductAttribute::whereIn('size_id', $size)->pluck('products_id');
                if ($record) {
                    $query->whereIn('id', $record);
                }
            }

            $productIds = $query->pluck('id');

            if ($productIds) {
                $productCategories = AdminProductCategory::whereIn('products_id', $productIds)->pluck('categories_id');
            }
            /*
             * End
             */
        }

        if (isset($user) && !empty($user->id) && $user->id == '7') {
            $query = AdminCategory::where('parent_id', 0)->orderBy('sorting', 'asc');
        } else {
            $query = AdminCategory::where(['is_active' => 1, 'parent_id' => 0])->orderBy('sorting', 'asc');
        }
        if ($searchedProductCategoryIds) {
            
            $query->orWhereIn('id', $searchedProductCategoryIds);
        }
        if ($search) {
            $categorySearch = AdminCategoryDescription::orWhere('categories_name', 'LIKE', '%' . $search . '%')->pluck('categories_id')->toArray();
             $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                    if (count($productSearch)) {
                       $productsearchCategories = AdminProductCategory::whereIn('products_id', $productSearch)->pluck('categories_id')->toArray();
                       
                     
                        if(count($productsearchCategories) > 0)
                        {
                         $categorySearch = array_merge($categorySearch, $productsearchCategories);
                        }
                        
                      
                    }
            
            if (count($categorySearch)) {
                
                
                $query->where(function ($query) use ($categorySearch) {
                    $query->whereIn('id', $categorySearch);
                    $parentId = AdminCategory::whereIn('id', $categorySearch)->pluck('parent_id');
                    if ($parentId) {
                        $query->orWhereIn('id', $parentId);
                    }
                     
                });
            }
            
            
            
        }

        if ($mainCategoryId) {
            $query->where('id', $mainCategoryId);
        }
        if (count($productCategories)) {
            //$query->whereIn('id', $productCategories);
        }
        if ($categoryId && !$showIn) {
            //$query->where('id', $categoryId);
        }
        if ($categoryIdByType) {
            //$query->whereIn('id', $categoryIdByType);
        }
        if ($showIn) {
            //            $query->where('show_in', $showIn);
        }
        // dd($query->	toSql());
        return $query->get();
    }

    public function getChildFront($parentId, $getIds = false, $filterType = "", $search = "", $appliedFilter = [], $searchedProductIds = false)
    {
        $categoryIdByType = [];

        if ($filterType == 'latest_collection') {
            $categoryIdByType = AdminCategory::getLatestCollectionCategory();
        } else if ($filterType == 'new_arrivals') {
            $categoryIdByType = AdminCategory::getNewArrivalCategory();
        } else if ($filterType == 'sale') {
            $categoryIdByType = AdminCategory::getSaleCategory();
        } else if ($filterType == 'product') {
            $categoryIdByType = AdminCategory::getProductTypeCategory($search);
        } else if ($filterType == 'shop_by_brand') {
            $filteredBrand    = isset($_GET['b']) ? $_GET['b'] : false;
            $filteredBrandIds = (isset($appliedFilter['brand']) && $appliedFilter['brand']) ? $appliedFilter['brand'] : false;
            $categoryIdByType = AdminCategory::getShopByBrandCategory($search, $filteredBrand, $filteredBrandIds);
        }

        if (is_array($parentId) && $parentId) {
            $query = $this->where(['is_active' => 1])->whereIn('parent_id', $parentId);
        } else {
            $query = $this->where(['is_active' => 1, 'parent_id' => $parentId]);
        }
        if ($filterType) {
            $query->whereIn('id', $categoryIdByType);
        }

        if ($searchedProductIds) {
            //dd($searchedProductIds);
            $searchedProductCategoryIds = AdminProductCategory::whereIn('products_id', $searchedProductIds)->pluck('categories_id');

            if ($searchedProductCategoryIds) {
                $query->whereIn('id', $searchedProductCategoryIds);
            }
        }

        //        dd( $query->pluck('id') );
        $query->orderBy('sorting', 'asc');
        return $getIds ? $query->pluck('id') : $query->get();
    }

    public function getChildFront2($parentId, $getIds = false, $filterType = "", $search = "", $appliedFilter = [], $searchedProductIds = false, $user = null)
    {
        $categoryIdByType = [];

        if ($filterType == 'latest_collection') {
            $categoryIdByType = AdminCategory::getLatestCollectionCategory();
        } else if ($filterType == 'new_arrivals') {
            $categoryIdByType = AdminCategory::getNewArrivalCategory();
        } else if ($filterType == 'sale') {
            $categoryIdByType = AdminCategory::getSaleCategory();
        } else if ($filterType == 'product') {
            $categoryIdByType = AdminCategory::getProductTypeCategory($search, $user);
        } else if ($filterType == 'shop_by_brand') {
            $filteredBrand    = isset($_GET['b']) ? $_GET['b'] : false;
            $filteredBrandIds = (isset($appliedFilter['brand']) && $appliedFilter['brand']) ? $appliedFilter['brand'] : false;
            $categoryIdByType = AdminCategory::getShopByBrandCategory($search, $filteredBrand, $filteredBrandIds);
        }
        $query = DB::table('admin_categories');
        $query = $query->select('admin_categories.*');
        $query = $query->leftJoin('admin_categories_description', 'admin_categories_description.categories_id', '=', 'admin_categories.id');
        if (isset($user) && !empty($user->id) && $user->id == '7') {
            $query = $query->where('admin_categories.parent_id', $parentId);
        } else {
            $query = $query->where(['admin_categories.is_active' => 1, 'admin_categories.parent_id' => $parentId]);
        }

        if ($filterType) {
            $query->whereIn('admin_categories.id', $categoryIdByType);
        }

        if ($searchedProductIds) {
          //  dd($searchedProductIds);
            $searchedProductCategoryIds = AdminProductCategory::whereIn('products_id', $searchedProductIds)->pluck('categories_id');

            if ($searchedProductCategoryIds) {
                $query->whereIn('admin_categories.id', $searchedProductCategoryIds);
            }
        }

        //        dd( $query->pluck('id') );
        $query->orderBy('admin_categories_description.categories_name', 'asc');
        return $getIds ? $query->pluck('admin_categories.id') : $query->get();
    }

    public function getSubCategory($category)
    {
        return AdminCategory::where(['is_active' => 1, 'show_in' => $category])->orderBy('sorting', 'asc')->get();
    }

    public static function getChildCategories()
    {
        $query = AdminCategory::where('is_active', 1)->where('parent_id', '!=', 0)->where('show_on_homepage', 1)->orderBy('sorting', 'asc')->limit(8);
        return $query->get();
    }

    public static function getLatestCollectionCategory()
    {
        $result     = [];
        $productsId = AdminProduct::where(['is_latest_collection' => 1, 'is_active' => 1])->pluck('id')->toArray();
        if ($productsId) {
            $result = AdminProductCategory::whereIn('products_id', $productsId)->pluck('categories_id')->toArray();
        }
        return $result;
    }

    public static function getNewArrivalCategory()
    {
        $result     = [];
        $productsId = AdminProduct::where(['is_new_arrival' => 1, 'is_active' => 1])->pluck('id')->toArray();
        if ($productsId) {
            $result = AdminProductCategory::whereIn('products_id', $productsId)->pluck('categories_id')->toArray();
        }
        return $result;
    }

    public static function getShopByBrandCategory($search = '', $filteredBrand = '', $filteredBrandIds = [])
    {
        $brand = false;
        if ($filteredBrand) {
            $brand = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_name', $filteredBrand)->pluck('brand_id');
        }

        if ($filteredBrandIds) {
            $brand = $filteredBrandIds;
        }
        $result     = [];
        $productsId = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1)->whereNotNull('brand_id');
        if ($brand) {
            $productsId->whereIn('brand_id', $brand);
            //dd($productsId->pluck('id'));
        }
        $productsId = $productsId->pluck('id')->toArray();
        if ($productsId) {
            $result = AdminProductCategory::whereIn('products_id', $productsId)->pluck('categories_id')->toArray();
        }
        return $result;
    }

    public static function getSaleCategory()
    {
        $result     = [];
        $productsId = AdminProduct::where(['is_product_sale_price_null' => 0, 'is_active' => 1])->where('is_active_category', 1)->pluck('id')->toArray();
        if ($productsId) {
            $result = AdminProductCategory::whereIn('products_id', $productsId)->pluck('categories_id')->toArray();
        }
        return $result;
    }

    public static function getProductTypeCategory($search = "", $user = null)
    {
        if (!isset($user)) {
            $user = auth()->user();
        }
        $result     = [];
        if (isset($user) && !empty($user->id) && $user->id == '7') {
            $productsId = AdminProduct::where('product_quantity', '>', 0)->pluck('id')->toArray();
        } else {
            $productsId = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1)->pluck('id')->toArray();
        }
        if ($productsId) {
            $result = AdminProductCategory::whereIn('products_id', $productsId);
            if ($search) {
                $productSearch  = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id')->toArray();
                $categorySearch = AdminCategoryDescription::orWhere('categories_name', 'LIKE', '%' . $search . '%')->pluck('categories_id');
                if (count($productSearch)) {
                    if (isset($user) && !empty($user->id) && $user->id == '7') {
                        $productSearch = AdminProduct::whereIn('id', $productSearch)->where('product_quantity', '>', 0)->pluck('id')->toArray();
                    } else {
                        $productSearch = AdminProduct::whereIn('id', $productSearch)->where('is_active', 1)->pluck('id')->toArray();
                    }
                    if (count($productSearch) > 0) {

                        $result->whereIn('products_id', $productSearch);
                    }
                }

                if (count($categorySearch) > 0) {
                    $productsId = AdminProductCategory::whereIn('categories_id', $categorySearch)->pluck('products_id');
                    if ($productsId) {
                        $result->whereIn('products_id', $productsId);
                    }
                }

                if (count($productSearch) == 0 && count($categorySearch) == 0) {
                    return [];
                }
            }

            $result = $result->pluck('categories_id')->toArray();
        }

        return $result;
    }

    public static function getProductByCategory($slug = '', $search = '')
    {
        $language = \App\Helpers\Helper::getLanguage();
        $query    = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1)->orderBy('sorting', 'asc');
        if ($slug) {
            $categoryId = AdminCategory::where('categories_slug', $slug)->pluck('id');
            if ($categoryId) {
                $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryId)->pluck('products_id');
                if ($productIdByCategory) {
                    $query->whereIn('id', $productIdByCategory);
                    return $query->get();
                }
                return false;
            }
            return false;
        } elseif ($search) {
            $productIdBysearch  = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->where('language_id', $language->id)->pluck('products_id');
            $CategoryIdBysearch = AdminCategoryDescription::orWhere('categories_name', 'LIKE', '%' . $search . '%')->where('language_id', $language->id)->pluck('categories_id');

            if (count($productIdBysearch) > 0) {

                $query->WhereIn('id', $productIdBysearch);
            }
            if (count($CategoryIdBysearch) > 0) {
                $products_id = AdminProductCategory::whereIn('categories_id', $CategoryIdBysearch)->pluck('products_id');

                if ($products_id) {
                    $query->WhereIn('id', $products_id);
                }
            }

            return $query->get();
        } else {
            return $query->where('is_new_arrival', 1)->get();
        }
    }

    public static function showHomePageCount()
    {
        return AdminCategory::where(['show_on_homepage' => 1])->where('parent_id', '!=', 0)->count();
    }

    public static function getChildCategory()
    {
        $query = AdminCategory::where('is_active', 1)->where('parent_id', '!=', 0)->where('show_on_homepage', 1)->orderBy('sorting', 'asc')->limit(7);
        return $query->get()->toArray();
    }

    public function getDeactivatedProductCount()
    {
        $subCategory = AdminCategory::where('parent_id', '=', $this->id)->pluck('id');

        if (count($subCategory)) {
            $productIds = \App\AdminModel\Product\AdminProductCategory::whereIn('categories_id', $subCategory)->pluck('products_id');
            if (count($productIds)) {
                $products = AdminProduct::whereIn('id', $productIds)->where('is_active', 0)->count();
                if ($products) {
                    return $products;
                }
            }
        }
        return false;
    }

    public function getDeactivatedProductCountSubCategory()
    {

        $productIds = \App\AdminModel\Product\AdminProductCategory::where('categories_id', $this->id)->pluck('products_id');
        if (count($productIds)) {
            $products = AdminProduct::whereIn('id', $productIds)->where('is_active', 0)->count();
            if ($products) {
                return $products;
            }
        }
        return false;
    }
}

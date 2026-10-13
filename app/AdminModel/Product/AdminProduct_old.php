<?php

namespace App\AdminModel\Product;

use App\AdminModel\Brand\AdminBrand;
use App\AdminModel\Brand\AdminBrandDescription;
use App\AdminModel\Color\AdminColor;
use App\AdminModel\Color\AdminColorDescription;
use App\AdminModel\GeneralSetting\HomeLatestCollection\AdminHomeLatestCollection;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Product\AdminProductCategory;
use App\AdminModel\Product\AdminProductDescription;
use App\AdminModel\Product\AdminProductImage;
use App\AdminModel\Product\Wishlist;
use App\UserModel\OrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;

class AdminProduct extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_products';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    /*
     * Boot Functions
     */

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data)
        {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data)
        {
            $data->updated_by = auth()->user()->id;
        });

        static::deleting(function ($data)
        {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    /*
     * Bind Table Data
     */

    public function getAllData($searchText, $sortKey, $sortVal, $pageLength, $productId = false, $outOfStockFilter, $filterCategory, $newArrivalFilter, $intlFilter, $filterBrand)
    {
        switch ($sortKey)
        {
            case 'name':
                $record = $this->sortByProduct($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            case 'category':
                $record = $this->sortByCategory($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($productId)
        {
            $query->where('id', '=', $productId);
        }
        if ($outOfStockFilter)
        {
            $query->where('product_quantity', 0);
            $query->orWhere('product_quantity', NULL);
        }
        if ($newArrivalFilter)
        {
            $query->where('is_new_arrival', 1);
        }
        if ($intlFilter)
        {
            $query->where('intl_shipping', 1);
        }
        if ($filterCategory)
        {
            $record = $this->filterByCategory($filterCategory);
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($filterBrand)
        {
            $query->whereIn('brand_id', $filterBrand);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('product_sku', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('product_barcode', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('product_regular_price', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('product_sale_price', 'LIKE', '%' . $searchText . '%');
                $query->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
                $query->orWhereDate('updated_at', '=', date('Y-m-d', strtotime($searchText)));
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active')
                {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive')
                {
                    $query->orWhere('is_active', 0);
                }
                /*
                 * Search by product
                 */
                $record = $this->searchbByProduct($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
                /*
                 * Search by category
                 */
                $record = $this->searchByCategory($searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
            });
        }

        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public static function checkSlug($currentSlug, $excludedId = '')
    {
        $query = AdminProduct::where('products_slug', $currentSlug);
        if ($excludedId)
        {
            $query->where('id', '!=', $excludedId);
        }
        return $query->get();
    }

    public static function updateSlug($productID, $slug)
    {
        $record = AdminProduct::find($productID);
        if ($record)
        {
            $record->products_slug = $slug;
            return $record->save();
        }
        return false;
    }

    /*
     * Blade - Table Binding
     */

    /*
     * Sorting Functions
     */

    public function sortByProduct($sortKey, $sortVal)
    {
        return AdminProductDescription::orderBy('products_name', $sortVal)->pluck('products_id')->toArray();
    }

    public function sortByCategory($sortKey, $sortVal)
    {
        $record = AdminCategoryDescription::orderBy('categories_name', $sortVal)->pluck('categories_id')->toArray();
        if ($record)
        {
            $record = implode(",", $record);
        }
        return AdminProductCategory::orderByRaw("FIELD(categories_id, $record)")->pluck('products_id')->toArray();
    }

    /*
     * /.Sorting Functions
     */

    /*
     * Search Functions
     */

    public function filterByCategory($filter)
    {
        $record = AdminProductCategory::whereIn("categories_id", $filter)->pluck('products_id');
        return $record ? $record : false;
    }

    public function searchByCategory($searchText)
    {
        $record = AdminCategoryDescription::where('categories_name', 'LIKE', '%' . $searchText . '%')->pluck('categories_id');
        if ($record)
        {
            $record = AdminProductCategory::whereIn("categories_id", $record)->pluck('products_id');
            return $record ? $record : false;
        }
        return false;
    }

    public function searchbByProduct($searchText)
    {
        $record = AdminProductDescription::where('products_name', 'LIKE', '%' . $searchText . '%')->pluck('products_id');
        return $record ? $record : false;
    }

    /*
     * End of Search Functions
     */

    public function getProductDescription($languageId = "", $getAll = false)
    {
        $query = $this->hasMany(AdminProductDescription::class, 'products_id', 'id')->withTrashed();
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductCategories($pluckID = false)
    {
        $query = $this->hasMany(AdminProductCategory::class, 'products_id', 'id');
        if ($pluckID)
        {
            return $query->pluck('categories_id');
        }
        return $query->get();
    }

    public function getAllImage()
    {
        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->get();
    }

    public function getDefaultImage()
    {
        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_default', 1)->withTrashed()->first();
    }

    public function getColorImage($color)
    {
        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('color_id', $color)->withTrashed()->first();
    }

    public function getSizeImage($size)
    {
        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('size_id', $size)->withTrashed()->first();
    }

    public function getColorSizeImage($color, $size)
    {

        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('color_id', $color)->where('size_id', $size)->withTrashed()->first();
    }

    public function getVideo()
    {
        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_video', 1)->first();
    }

//    public function getGalleryImage()
    //    {
    //        return $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_default', 0)->get();
    //    }

    public function getGalleryImage($withoutColorImage = false, $colorId = false)
    {
        $query = $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_default', 0);
        if ($withoutColorImage)
        {
            $query->whereNull('color_id');
        }
        if ($colorId)
        {
            $query->where('color_id', $colorId);
        }
        $query->whereNull('size_id');
        return $query = $query->get();
    }

    public function getGallerySizeImage_old($withoutSizeImage = false, $sizeId = false, $colorId = false)
    {
        $query = $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_default', 0);
        if ($withoutSizeImage)
        {
            $query->whereNull('size_id');
        }
        if ($sizeId)
        {
            $query->where('size_id', $sizeId);
        }

        if ($colorId)
        {
            $query->where('color_id', $colorId);
        }
        else
        {
            $query->whereNull('color_id');
        }
        return $query = $query->get();
    }

    public static function getNewArrivals($count = false)
    {
        $limit = config('custom.product_limit', 30);
        if ($count)
        {
            return AdminProduct::where('is_new_arrival', 1)->get();
        }
        else
        {
            return AdminProduct::where('is_new_arrival', 1)->limit($limit)->get();
        }
    }

    public static function getNewArrivalsHomePage($count = false)
    {
        $limit = 8;
        if ($count)
        {
            return AdminProduct::where('is_new_arrival', 1)->where('is_active', 1)->where('is_active_category', 1)->where('product_quantity', '>', 0)->orderBy('sorting', 'asc')->get();
        }
        else
        {
            return AdminProduct::where('is_new_arrival', 1)->where('is_active', 1)->where('is_active_category', 1)->where('product_quantity', '>', 0)->orderBy('sorting', 'asc')->limit($limit)->get();
        }
    }

    public static function getLatestCollection()
    {
        /*
         * If Latest collection count is 5 AND is_center exist then return
         * Else
         * return false
         */
        $query = AdminHomeLatestCollection::where('is_active', 1)->orderBy('is_center', 'desc')->get();
        if (count($query) == 5 && isset($query[0]) && $query[0]->is_center == 1)
        {
            return $query;
        }
        return false;
    }

    public static function getSaleProducts($count = false)
    {
        $limit = config('custom.product_limit', 30);
        if ($count)
        {
            return AdminProduct::where('is_product_sale_price_null', 0)->get();
        }
        else
        {
            return AdminProduct::where('is_product_sale_price_null', 0)->limit($limit)->get();
        }
    }

    public function getProductImages($isDefault = "", $getAll = false)
    {
        $query = $this->hasMany(AdminProductImage::class, 'products_id', 'id')->where('is_video', 0);
        if ($isDefault)
        {
            $query->where('is_default', 1);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductImagesByColor($isDefault = "", $getAll = false, $color = '', $size = '')
    {

        $query = $this->hasMany(AdminProductImage::class, 'products_id', 'id')->where('is_video', 0);
        if ($isDefault)
        {
            $query->where('is_default', 1);
        }
        if (!empty($color))
        {

            if (!$size)
            {
                $query->where('size_id', null);
            }
            $query->where(function ($q) use ($color)
            {
                $q->where('color_id', $color)->orWhere('color_id', null);
            });

            $query->orderBy('color_id', 'desc');
        }
        if (!empty($size))
        {

            if (!$color)
            {
                $query->where('color_id', null);
            }
            $query->where(function ($q) use ($size)
            {
                $q->where('size_id', $size)->orWhere('size_id', null);
            });
            $query->orderBy('size_id', 'desc');
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductAttributeProductPage($productId, $sizeFilter, $colorFilter)
    {
        $query = $this->hasMany(AdminProductAttribute::class, 'products_id', 'id');
        $query->where('products_id', $productId);
        $query->where('product_regular_price', '!=', NULL);
        if ($sizeFilter)
        {
            $query->where('size_id', $sizeFilter);
            if (!$colorFilter)
            {
                $query->where('color_id', null);
            }
        }
        if ($colorFilter)
        {
            $query->where('color_id', $colorFilter);
            if (!$sizeFilter)
            {
                $query->where('size_id', null);
            }
        }

        return $query->first();
    }

    public function getProductVideos($isDefault = "", $getAll = false)
    {
        $query = $this->hasMany(AdminProductImage::class, 'products_id', 'id')->where('is_video', 1);
        if ($isDefault)
        {
            $query->where('is_default', 1);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductColor($languageId = "", $getAll = false)
    {
        $query = $this->hasMany(AdminColorDescription::class, 'color_id', 'color_id')->withTrashed();
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductColors($languageId = "", $getAll = false, $have_color = '', $have_size = '')
    {
        $colors = $this->hasMany(AdminProductAttribute::class, 'products_id', 'id');
        if ($have_color)
        {
            $colors->where('color_id', '!=', null);
        }
        else
        {
            $colors->where('color_id', null);
        }
        if ($have_size)
        {
            $colors->where('size_id', '!=', null);
        }
        else
        {
            $colors->where('size_id', null);
        }

        $colors = $colors->where('quantity', '>', 0)->withTrashed()->pluck('color_id');

        if ($colors)
        {

            $colors = AdminColor::whereIn('id', $colors)->where('is_active', 1)->orderBy('sorting')->pluck('id')->toArray();
        }

        if ($colors)
        {
            if ($colors)
            {

                $cs = implode(",", $colors);
            }
            return AdminColorDescription::whereIn('color_id', $colors)->where('language_id', $languageId)->orderByRaw("FIELD(color_id, $cs)")->get();
        }
        return [];
    }

    public function getProductSize($languageId = "", $getAll = false, $have_color = '', $have_size = '')
    {
        $colors = $this->hasMany(AdminProductAttribute::class, 'products_id', 'id');

        if ($have_color)
        {
            $colors->where('color_id', '!=', null);
        }
        else
        {
            $colors->where('color_id', null);
        }
        if ($have_size)
        {
            $colors->where('size_id', '!=', null);
        }
        else
        {
            $colors->where('size_id', null);
        }

        $colors = $colors->where('quantity', '>', 0)->withTrashed()->pluck('size_id');

        if ($colors)
        {
            $colors = \App\AdminModel\Size\AdminSize::whereIn('id', $colors)->where('is_active', 1)->orderBy('sorting')->pluck('id')->toArray();
        }

        if ($colors)
        {
            if ($colors)
            {

                $cs = implode(",", $colors);
            }
            return \App\AdminModel\Size\AdminSizeDescription::whereIn('size_id', $colors)->where('language_id', $languageId)->orderByRaw("FIELD(size_id, $cs)")->get();
        }
        return [];
    }

    public function getProductColorsName($languageId = "", $getAll = false)
    {
        $colors = $this->hasMany(AdminProductAttribute::class, 'products_id', 'id')->where('quantity', '>', 0)->withTrashed()->pluck('color_id');
        if ($colors)
        {
            return AdminColorDescription::whereIn('color_id', $colors)->where('language_id', $languageId)->pluck('color_id')->toArray();
        }
        return null;
    }

       public static function getProductByFilter($language, $appliedFilter, $page = '30', $count = false)
    {
        $query = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1);

        /*
         * Search
         */
        //        $activeBrand = AdminBrand::where('is_active', 1)->pluck('id');
        //        if (count($activeBrand) == 0)
        //        {
        //            $query->whereNull('brand_id');
        //        }

        if (isset($appliedFilter['search']) && $appliedFilter['search']) {
            $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $appliedFilter['search'] . '%')->where('language_id', $language->id)->pluck('products_id')->toArray();

            if (count($productSearch) > 0) {
                $productSearch = AdminProduct::whereIn('id', $productSearch)->where('is_active', 1)->pluck('id')->toArray();
            }

            $categorySearch = AdminCategoryDescription::orWhere('categories_name', 'LIKE', '%' . $appliedFilter['search'] . '%')->where('language_id', $language->id)->pluck('categories_id');




            $barcodeSearch  = AdminProduct::orWhere('product_barcode', 'LIKE', '%' . $appliedFilter['search'] . '%')->pluck('id')->toArray();
            $barcodeSearch2 = AdminProductAttribute::orWhere('barcode', 'LIKE', '%' . $appliedFilter['search'] . '%')->pluck('products_id')->toArray();
            /*
             * Check for Sub Category
             */
            $productSearch = array_merge($productSearch, $barcodeSearch, $barcodeSearch2);
            //dd($productSearch);

            if (count($categorySearch) > 0) {

                $productsId = AdminProductCategory::whereIn('categories_id', $categorySearch)->pluck('products_id')->toArray();
                $cproductsId = AdminProduct::whereIn('category_id', $categorySearch)->pluck('id')->toArray();
                $productsId = array_merge($productsId, $cproductsId);
                if ($productsId) {
                    $query->where(function ($query) use ($productsId) {
                        $query->orWhereIn('id', $productsId); #change on 01-June-2022
                    });

                    $subCategorySearch = AdminCategory::whereIn('parent_id', $categorySearch)->pluck('id');

                    if (count($subCategorySearch) > 0) {
                        $productsId = AdminProductCategory::whereIn('categories_id', $subCategorySearch)->pluck('products_id');
                        if ($productsId) {
                            $query->where(function ($query) use ($productsId) {
                                $query->orWhereIn('id', $productsId); #change on 01-June-2022
                            });
                            //$query->whereIn('id', $productsId);
                        }
                    }
                }

                $brandSearch = [];
                $activeBrand = \App\AdminModel\Brand\AdminBrand::where('is_active', 1)->pluck('id');
                if (count($activeBrand)) {

                    //                $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand)->orWhere('brand_name', 'LIKE', '%' . $appliedFilter['search'] . '%')->pluck('brand_id');
                    $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand)->Where('brand_name', 'LIKE', '%' . $appliedFilter['search'] . '%')->pluck('brand_id')->toArray();
                    if (count($brandSearch)) {

                        $query->whereIn('brand_id', $brandSearch);
                    }
                }

                if (count($productSearch) == 0 && count($categorySearch) == 0 && count($brandSearch) == 0) {
                    return [];
                }
            } else {

                if (count($productSearch) > 0) {
                    $query->whereIn('id', $productSearch);
                } else {
                    return [];
                }
            }
        }

        /*
         * Type wise filter
         */
        if ($appliedFilter['type'] == 'latest_collection') {
            $query->where('is_latest_collection', 1);
        } else if ($appliedFilter['type'] == 'new_arrivals') {
            $query->where('is_new_arrival', 1);
            $query->where('product_quantity', '>', 0);
        } else if ($appliedFilter['type'] == 'sale') {
            $query->where('is_product_sale_price_null', 0);
        } else if ($appliedFilter['type'] == 'shop_by_brand') {
            $query->whereNotNull('brand_id');
        }
        /*
         * Left Side Filter
         */
        if ($appliedFilter['category']) {

            if (isset($appliedFilter['category'][0]) && $appliedFilter['category'][0] != 'all') {

                $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $appliedFilter['category'])->where('is_sub_category', 1)->pluck('size_id')->toArray();
                
                if (count($sizeAttr) > 0) {
                    $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                    if (count($sizeAttr) > 0) {
                        $record = AdminProductCategory::whereIn('categories_id', $appliedFilter['category'])->pluck('products_id');
                        $record   = AdminProductAttribute::whereIn('products_id', $record)->whereIn('size_id', $sizeAttr)->pluck('products_id');
                    } else {
                        $record = AdminProductCategory::whereIn('categories_id', $appliedFilter['category'])->pluck('products_id');
                    }
                } else {
                    $record = AdminProductCategory::whereIn('categories_id', $appliedFilter['category'])->pluck('products_id');
                }
                if ($record) {
                    $query->whereIn('id', $record);
                }
            } else if (isset($appliedFilter['subCategoryId'])) {
                $record=[];
                $subCategories = AdminCategory::where('parent_id', $appliedFilter['subCategoryId'])->pluck('id')->toArray();
                $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $subCategories)->where('is_sub_category', 1)->pluck('size_id')->toArray();
                if (count($sizeAttr) > 0) {
                    $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                    if (count($sizeAttr) > 0) {
                        $record = AdminProductCategory::whereIn('categories_id',$subCategories)->pluck('products_id');
                        
                        $record   = AdminProductAttribute::whereIn('products_id', $record)->whereIn('size_id', $sizeAttr)->pluck('products_id');
                   
                    } else {
                        $record = AdminProductCategory::whereIn('categories_id', $subCategories)->pluck('products_id');

                    }
                }
                if(count($record) > 0)
                {
                    $query->whereIn('id', $record);
                }
                else
                {
                    if ($subCategories) {
                        $record = AdminProductCategory::whereIn('categories_id', $subCategories)->pluck('products_id');
                        if ($record) {
                            $query->whereIn('id', $record);
                        }
                    }
                }
    
            }
            //dd($query->pluck('id'));
        } else if (isset($appliedFilter['isAll']) && $appliedFilter['isAll'] == 1 && isset($appliedFilter['subCategoryId'])) {

            $query->where('category_id', $appliedFilter['subCategoryId']);
        }
        if ($appliedFilter['color']) {
            $record = AdminProductAttribute::whereIn('color_id', $appliedFilter['color'])->pluck('products_id');
            if ($record) {
                $query->whereIn('id', $record);
            }
        }
        if ($appliedFilter['size']) {
            $record = AdminProductAttribute::whereIn('size_id', $appliedFilter['size'])->pluck('products_id');
            if ($record) {
                $query->whereIn('id', $record);
            }
        }

        if ($appliedFilter['brand']) {
            $query->whereIn('brand_id', $appliedFilter['brand']);
        }

        if ($appliedFilter['price']) {
            $price = explode("-", $appliedFilter['price']);
            if ($price && isset($price[0]) && isset($price[1])) {
                $min = $price[0];
                $max = $price[1];

                $query->where(function ($query) use ($min, $max) {
                    $normalProduct = $query->where('have_price_variant', 0)->count();
                    //$attrProduct   = $query->where('have_price_variant', 1)->count();
                    if ($normalProduct) {
                        $query->orWhere('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max);
                    }
                    if ($query->where('have_price_variant', 1)->count()) {
                        $priceProductsIds = AdminProductAttribute::where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max)->pluck('products_id');
                        if ($priceProductsIds) {
                            $query->orWhereIn('id', $priceProductsIds);
                        }
                    }
                });
            }
        }
        if ($appliedFilter['sortBy']) {
            if ($appliedFilter['sortBy'] == 't') {
                $sortTrending = OrderDetail::selectRaw('SUM(quantity) as qty, product_id')->orderByRaw('qty')->groupBy('product_id')->pluck('product_id')->toArray();
                if (count($sortTrending) > 0) {
                    $sortTrending = implode(",", $sortTrending);
                    $query->orderByRaw("FIELD(id, $sortTrending)");
                } else {
                    $query->orderBy('sorting', 'asc');
                }
            } else if ($appliedFilter['sortBy'] == 'no') {
                $query->orderBy('created_at', 'desc');
            } else if ($appliedFilter['sortBy'] == 'on') {
                $query->orderBy('created_at', 'asc');
            } else if ($appliedFilter['sortBy'] == 'lh') {
                $query->orderBy('product_sale_price', 'asc');
            } else if ($appliedFilter['sortBy'] == 'hl') {
                $query->orderBy('product_sale_price', 'desc');
            } else if ($appliedFilter['sortBy'] == 'up') {
                $query->orderBy('updated_at', 'desc');
            } else {
                $query->orderBy('sorting', 'asc');
            }
        }

        // dd($query->toSql());
        if ($count) {
            return $query->get();
        } else {
            return $query->limit($page)->get();
        }
    }

    public function getProductCategory($products_id)
    {
        $prod_id  = [];
        $category = AdminProductCategory::where(['is_active' => 1])->where(['products_id' => $products_id])->pluck('categories_id');

        $prodStyle = AdminProductAttribute::where(['products_id' => $products_id])->where('style', '!=', null)->pluck('style')->toArray();

        $product = AdminProduct::where(['id' => $products_id])->first();

        if ($product)
        {

            $brandprod_id = AdminProduct::where('brand_id', $product->brand_id)->limit(4)->pluck('id')->toArray();
            if (count($brandprod_id) > 0)
            {

                $prod_id = array_merge($prod_id, $brandprod_id);
            }
        }

        if (count($prodStyle) > 0)
        {

            $styleprod_id = AdminProductAttribute::whereIn('style', $prodStyle)
                            ->where('products_id', '!=', $products_id)->limit(2)
                            ->pluck('products_id')->toArray();

            if (count($styleprod_id) > 0)
            {
                $prod_id = array_merge($prod_id, $styleprod_id);
            }
        }

        if ($category)
        {
            $catprod_id = AdminProductCategory::where('products_id', '!=', $products_id)->whereIn('categories_id', $category)->groupBy('products_id')->limit(5)->pluck('products_id')->toArray();
            if (count($catprod_id) > 0)
            {
                $prod_id = array_merge($prod_id, $catprod_id);
            }
        }

        if (count($prod_id) > 0)
        {
            shuffle($prod_id);

            $orderby = implode(",", $prod_id);
            return AdminProductCategory::where('products_id', '!=', $products_id)->whereIn('products_id', $prod_id)->groupBy('products_id')->orderByRaw("FIELD(products_id, $orderby)")->get();
        }

        return [];
    }

    public function getRelatedProductCount($products_id)
    {

        $prod_id  = [];
        $category = AdminProductCategory::where(['is_active' => 1])->where(['products_id' => $products_id])->pluck('categories_id');

        $prodStyle = AdminProductAttribute::where(['products_id' => $products_id])->where('style', '!=', null)->pluck('style')->toArray();

        $product = AdminProduct::where(['id' => $products_id])->first();

        if ($product)
        {

            $brandprod_id = AdminProduct::where('brand_id', $product->brand_id)->limit(4)->pluck('id')->toArray();
            if (count($brandprod_id) > 0)
            {

                $prod_id = array_merge($prod_id, $brandprod_id);
            }
        }

        if (count($prodStyle) > 0)
        {

            $styleprod_id = AdminProductAttribute::whereIn('style', $prodStyle)
                            ->where('products_id', '!=', $products_id)->limit(2)
                            ->pluck('products_id')->toArray();

            if (count($styleprod_id) > 0)
            {
                $prod_id = array_merge($prod_id, $styleprod_id);
            }
        }

        if ($category)
        {
            $catprod_id = AdminProductCategory::where('products_id', '!=', $products_id)->whereIn('categories_id', $category)->groupBy('products_id')->limit(5)->pluck('products_id')->toArray();
            if (count($catprod_id) > 0)
            {
                $prod_id = array_merge($prod_id, $catprod_id);
            }
        }

        if (count($prod_id) > 0)
        {

            shuffle($prod_id);
            return AdminProduct::whereIn('id', $prod_id)->count();
        }
        else
        {
            return 0;
        }
        return 0;
    }

    public function getProductQuantity($products_id)
    {
        $product = $this->find($products_id);
        if ($product)
        {
            return $product->product_quantity;
        }
        return false;
    }

    public function getWishlist($user_id = "", $product_id = "", $getAll = false)
    {
        $query = $this->hasMany(Wishlist::class, 'product_id', 'id');
        if ($user_id)
        {
            $query->where('user_id', $user_id);
        }
        if ($product_id)
        {
            $query->where('product_id', $product_id);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * used in dashboard
     */

    public static function getProductData()
    {
        return AdminProduct::orderBy('id', 'desc')->skip(0)->take(10)->get();
    }

    public function getProductAttributes($sizeId = "", $colorId = "", $withZero = true)
    {
        $query = $this->hasMany(AdminProductAttribute::class, 'products_id', 'id');
        if ($withZero)
        {
            $query->where('quantity', '!=', 0);
        }
        if ($query)
        {
            $sizeIdSorting = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->pluck('id')->toArray();
            if ($sizeIdSorting)
            {
                $sizeIdSorting = implode(",", $sizeIdSorting);
                $query         = $query->orderByRaw("FIELD(size_id, $sizeIdSorting)");
            }

            if ($colorId)
            {
                $query->where('color_id', $colorId);
            }
            else
            {
                $query->whereNull('color_id');
            }

            if ($sizeId)
            {
                $query->where('size_id', $sizeId);
                return $query->first();
            }
            else
            {
                $query->whereNull('size_id');
            }

            $query = $query->get();
            return count($query) ? $query : false;
        }
        return false;
    }

    /*
     *  Front side Filter
     */

    public static function getColorFilter($filterType, $categoryFilter = [], $price = [], $size = [], $search = "", $brand = "", $mainCategoryId = false)
    {
        /*
         * Get color sorting sequence
         */

//            $query = AdminProduct::groupBy('color_id')
//                    ->selectRaw('count(color_id) as filter_count, color_id');
        $query = AdminProduct::where('is_active', 1)->where('have_color', 1)->where('is_active_category', 1);
        if ($filterType == 'latest_collection')
        {
            $query->where('is_latest_collection', 1);
        }
        else if ($filterType == 'new_arrivals')
        {
            $query->where('is_new_arrival', 1);
            $query->where('product_quantity', '>', 0);
        }
        else if ($filterType == 'sale')
        {
            $query->where('is_product_sale_price_null', 0);
        }
        else if ($filterType == 'product')
        {
            if ($search)
            {
                $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);

                $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                if (count($productSearch))
                {
                    $query->whereIn('id', $productSearch);
                }
            }
        }

        if ($categoryFilter)
        {
            if (!is_array($categoryFilter))
            {
                $categoryFilter = [$categoryFilter];
            }

            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all')
            {
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);
            }
            else
            {
                if ($mainCategoryId)
                {
                    $categoryFilter = AdminCategory::where('parent_id', $mainCategoryId)->pluck('id')->toArray();
                    if ($categoryFilter)
                    {
                        $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                        $query->whereIn('id', $categoryProduct);
                    }
                }
            }
        }
        if ($price)
        {
            $price = explode("-", $price);
            if ($price && isset($price[0]) && isset($price[1]))
            {
                $min              = $price[0];
                $max              = $price[1];
                //$query->where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max);
                $priceProductsIds = AdminProductAttribute::where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max)->pluck('products_id');
                $query->whereIn('id', $priceProductsIds);
            }
        }
        if ($size)
        {
            $record = AdminProductAttribute::whereIn('size_id', $size)->pluck('products_id');
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($brand)
        {
            if (!is_array($brand))
            {
                $brand = [$brand];
            }
            $query->whereIn('brand_id', $brand);
        }

        $productIds = $query->pluck('id');
        if ($productIds)
        {
//            echo "<pre>";
            //            print_r($productIds);
            //            echo "</pre>";
            //            die;
            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all') {

                $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $categoryFilter)->where('is_sub_category', 1)->pluck('size_id')->toArray();
                if (count($sizeAttr) > 0) {
                    $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                    if (count($sizeAttr) > 0) {
                        $colorAttr = AdminProductAttribute::whereIn('products_id', $productIds)->whereIn('size_id', $sizeAttr)->whereNotNull('color_id')->pluck('color_id');
                    } else {
                        $colorAttr = AdminProductAttribute::whereIn('products_id', $productIds)->whereNotNull('color_id')->pluck('color_id');
                    }
                } else {
                    $colorAttr = AdminProductAttribute::whereIn('products_id', $productIds)->whereNotNull('color_id')->pluck('color_id');
                }
            } else {
                $colorAttr = AdminProductAttribute::whereIn('products_id', $productIds)->whereNotNull('color_id')->pluck('color_id');
            }
            $orderBy   = AdminColor::orderBy('sorting', 'asc')->whereIn('id', $colorAttr)->where('is_active', 1)->get();
//            dd($orderBy);
            return (['data' => $orderBy, 'productIds' => $productIds]);
        }

        return [];
    }

    public static function getPriceRangeCount($lowerRange, $upperRange, $filterType, $categoryFilter = [], $color = [], $size = [], $search = "", $brand = [], $mainCategoryId = false)
    {
//        $query = AdminProduct::where(['is_active' => 1])->where('product_sale_price', '>=', $lowerRange)->where('product_sale_price', '<=', $upperRange);
        $query = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1);
        if ($filterType == 'latest_collection')
        {
            $query->where('is_latest_collection', 1);
        }
        else if ($filterType == 'new_arrivals')
        {
            $query->where('is_new_arrival', 1);
            $query->where('product_quantity', '>', 0);
        }
        else if ($filterType == 'sale')
        {
            $query->where('is_product_sale_price_null', 0);
        }
        else if ($filterType == 'product')
        {
            if ($search)
            {
                $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();
                if ($categoryProduct)
                {
                    $query->whereIn('id', $categoryProduct);
                }
                $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                if (count($productSearch))
                {

                    $productSearch = AdminProduct::where('id', $productSearch)->where('is_active', 1)->pluck('id')->toArray();
                    if (count($productSearch) > 0)
                    {

                        $query->whereIn('id', $productSearch);
                    }
                }

                $brandSearch = [];
                $activeBrand = AdminBrand::where('is_active', 1)->pluck('id');
                if (count($activeBrand))
                {
                    $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand);
                    if ($search)
                    {
                        $brandSearch->Where('brand_name', 'LIKE', '%' . $search . '%');
                    }
                    $brandSearch = $brandSearch->pluck('brand_id');

                    if (count($brandSearch))
                    {
                        $query->whereIn('brand_id', $brandSearch);
                    }
                }
            }
        }

        if ($categoryFilter)
        {
            if (!is_array($categoryFilter))
            {
                $categoryFilter = [$categoryFilter];
            }

            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all')
            {
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);
            }
            else
            {
                if ($mainCategoryId)
                {
                    $categoryFilter = AdminCategory::where('parent_id', $mainCategoryId)->pluck('id');
                    if ($categoryFilter)
                    {
                        $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                        $query->whereIn('id', $categoryProduct);
                    }
                }
            }
        }
        if ($color)
        {
            $record = AdminProductAttribute::whereIn('color_id', $color)->pluck('products_id');
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($size)
        {
            $record = AdminProductAttribute::whereIn('size_id', $size)->pluck('products_id');
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($brand)
        {
            if (!is_array($brand))
            {
                $brand = [$brand];
            }
            $query->whereIn('brand_id', $brand);
        }
        
        
         $barcoderecord = AdminProductAttribute::where('barcode', $search)->pluck('products_id');
        if(count($barcoderecord) > 0)
        {
        $query->whereIn('id', $barcoderecord);
        }

        $productsIds = $query->pluck('id');
  $sizeAttr2 = [];
        if (!is_array($categoryFilter)) {
            $categoryFilter = [$categoryFilter];


            $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $categoryFilter)->where('is_sub_category', 1)->pluck('size_id')->toArray();
            if (count($sizeAttr) > 0) {
                $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                if (count($sizeAttr) > 0) {
                    $sizeAttr2 = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                }
            }
        }
        if (count($sizeAttr2) == 0) {
            $query = AdminProductAttribute::whereIn('products_id', $productsIds)->where('product_sale_price', '>=', $lowerRange)->where('product_sale_price', '<=', $upperRange)->where('is_active', 1)->where('quantity', '>', 0)->count();
        } else {
            $query = AdminProductAttribute::whereIn('products_id', $productsIds)->whereIn('size_id', $sizeAttr2)->where('product_sale_price', '>=', $lowerRange)->where('product_sale_price', '<=', $upperRange)->where('is_active', 1)->where('quantity', '>', 0)->count();
        }

        $queryProductTable = AdminProduct::whereIn('id', $productsIds)->where('product_sale_price', '>=', $lowerRange)->where('product_sale_price', '<=', $upperRange)->where('is_active', 1)->where('have_price_variant', 0)->where('product_quantity', '>', 0)->count();

        return $query + $queryProductTable;
    }

    public static function getPriceMinMax($filterType, $categoryFilter = [], $color = [], $size = [], $search = "", $brand = [], $mainCategoryId = false)
    {
//        $query = AdminProduct::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
//                ->where(['is_active' => 1]);
        $query = AdminProduct::where(['is_active' => 1])->where('is_active_category', 1);
        if ($filterType == 'latest_collection')
        {
            $query->where('is_latest_collection', 1);
        }
        else if ($filterType == 'new_arrivals')
        {
            $query->where('is_new_arrival', 1);
            $query->where('product_quantity', '>', 0);
        }
        else if ($filterType == 'sale')
        {
            $query->where('is_product_sale_price_null', 0);
        }
        else if ($filterType == 'product')
        {
            if ($search)
            {
                $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();
                if ($categoryProduct)
                {
                    $query->whereIn('id', $categoryProduct);
                }
                $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                if (count($productSearch))
                {

                    $productSearch = AdminProduct::where('id', $productSearch)->where('is_active', 1)->pluck('id')->toArray();
                    if (count($productSearch) > 0)
                    {

                        $query->whereIn('id', $productSearch);
                    }
                }

                $brandSearch = [];
                $activeBrand = AdminBrand::where('is_active', 1)->pluck('id');
                if (count($activeBrand))
                {
                    $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand);
                    if ($search)
                    {
                        $brandSearch->Where('brand_name', 'LIKE', '%' . $search . '%');
                    }
                    $brandSearch = $brandSearch->pluck('brand_id');

                    if (count($brandSearch))
                    {
                        $query->whereIn('brand_id', $brandSearch);
                    }
                }
            }
        }

        if ($categoryFilter)
        {
            if (!is_array($categoryFilter))
            {
                $categoryFilter = [$categoryFilter];
            }

            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all')
            {
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);
            }
            else
            {
                if ($mainCategoryId)
                {
                    $categoryFilter = AdminCategory::where('parent_id', $mainCategoryId)->pluck('id')->toArray();
                    if ($categoryFilter)
                    {
                        $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                        $query->whereIn('id', $categoryProduct);
                    }
                }
            }
        }
        if ($color)
        {
            $record = AdminProductAttribute::whereIn('color_id', $color)->pluck('products_id');
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($size)
        {
            $record = AdminProductAttribute::whereIn('size_id', $size)->pluck('products_id');
            if ($record)
            {
                $query->whereIn('id', $record);
            }
        }
        if ($brand)
        {
            if (!is_array($brand))
            {
                $brand = [$brand];
            }
            $query->whereIn('brand_id', $brand);
        }
       
        $barcoderecord = AdminProductAttribute::where('barcode', $search)->pluck('products_id');
        if(count($barcoderecord) > 0)
        {
        $query->whereIn('id', $barcoderecord);
        }

        $productsIds = $query->pluck('id');
          if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all') {

            $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $categoryFilter)->where('is_sub_category', 1)->pluck('size_id')->toArray();
            if (count($sizeAttr) > 0) {
                $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                if (count($sizeAttr) > 0) {
                    $query             = AdminProductAttribute::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                        ->where(['is_active' => 1])->whereIn('products_id', $productsIds)->whereIn('size_id', $sizeAttr)->first();
                } else {
                    $query             = AdminProductAttribute::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                        ->where(['is_active' => 1])->whereIn('products_id', $productsIds)->first();
                }
            } else {
                $query             = AdminProductAttribute::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                    ->where(['is_active' => 1])->whereIn('products_id', $productsIds)->first();
            }
        } else {

            $query             = AdminProductAttribute::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                ->where(['is_active' => 1])->whereIn('products_id', $productsIds)->first();
        }
      
        $query             = AdminProductAttribute::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                        ->where(['is_active' => 1])->whereIn('products_id', $productsIds)->first();
        $queryProductTable = AdminProduct::selectRaw('min(product_sale_price) as min_price, max(product_sale_price) as max_price')
                        ->where(['is_active' => 1])->where('product_quantity', '>', 0)->where('have_price_variant', 0)->whereIn('id', $productsIds)->first();

         if ($queryProductTable->min_price != null  && $queryProductTable->max_price != null && $query->min_price && $query->max_price) {
            if ($queryProductTable->min_price && ($queryProductTable->min_price < $query->min_price)) {
                $query->min_price = $queryProductTable->min_price;
            }
            if ($queryProductTable->max_price && ($queryProductTable->max_price > $query->max_price)) {
                $query->max_price = $queryProductTable->max_price;
            }
        } elseif ($queryProductTable->min_price == null && $queryProductTable->max_price == null && $query->min_price >= 0 && $query->max_price >= 0) {
            return $query;
        } else {
          

            $query = $queryProductTable;
        }

        return $query;
    }

    /*
     * Check Product Quantity - While add to cart
     */

    public static function checkProductQuantity($productId, $quantity, $sizeExist, $size, $colorExist, $color)
    {
        $exist = AdminProduct::where('id', $productId)->where('product_quantity', '>=', $quantity)->first();
        if ($exist)
        {

            if ($sizeExist)
            {
                $existSize = AdminProductAttribute::where('products_id', $productId)->where('quantity', '>=', $quantity)->where('size_id', $size);
                if ($colorExist)
                {
                    $existSize->where('color_id', $color);
                }
                $existSize->first();

                if ($existSize)
                {
                    $status  = true;
                    $message = Lang::get('web.size-available');
                }
                else
                {
                    $status  = false;
                    $message = Lang::get('web.size-not-available');
                }
            }
            else
            {
                $status  = true;
                $message = Lang::get('web.stock-available');
            }
        }
        else
        {
            $status  = false;
            $message = Lang::get('web.product-out-of-stock');
        }
        return $response = ['status' => $status, 'message' => $message];
    }

    public static function getBrandFilter($filterType, $categoryFilter = [], $price = [], $color = [], $search = "", $size = [], $mainCategoryId = false)
    {
        /*
         * Get brand sorting sequence
         */
        $orderBy = AdminBrand::orderBy('sorting', 'asc')->where('is_active', 1)->pluck('id')->toArray();
        if ($orderBy)
        {
            $orderBy = implode(",", $orderBy);
            $query   = AdminProduct::groupBy('brand_id')
                    ->selectRaw('count(brand_id) as filter_count, brand_id');
            $query->where('is_active', 1)->where('is_active_category', 1);

            if ($filterType == 'latest_collection')
            {
                $query->where('is_latest_collection', 1);
            }
            else if ($filterType == 'new_arrivals')
            {
                $query->where('is_new_arrival', 1);
                $query->where('product_quantity', '>', 0);
            }
            else if ($filterType == 'sale')
            {
                $query->where('is_product_sale_price_null', 0);
            }
            else if ($filterType == 'product')
            {
                if ($search)
                {
                    $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);
                    $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();
                    if (count($categoryProduct))
                    {
                        $query->whereIn('id', $categoryProduct);
                    }

                    $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                    if (count($productSearch))
                    {

                        $productSearch = AdminProduct::where('id', $productSearch)->where('is_active', 1)->pluck('id')->toArray();
                        if (count($productSearch) > 0)
                        {

                            $query->whereIn('id', $productSearch);
                        }
                    }

                    $brandSearch = [];
                    $activeBrand = AdminBrand::where('is_active', 1)->pluck('id');
                    if (count($activeBrand))
                    {
                        $brandSearch = \App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $activeBrand);
                        if ($search)
                        {
                            $brandSearch->Where('brand_name', 'LIKE', '%' . $search . '%');
                        }
                        $brandSearch = $brandSearch->pluck('brand_id');

                        if (count($brandSearch))
                        {
                            $query->whereIn('brand_id', $brandSearch);
                        }
                    }
                }
            }

            if ($categoryFilter)
            {
                if (!is_array($categoryFilter))
                {
                    $categoryFilter = [$categoryFilter];
                }

                if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all')
                {
                     $sizeAttr =  \App\AdminModel\Size\AdminSizeCategory::whereIn('category_id', $categoryFilter)->where('is_sub_category', 1)->pluck('size_id')->toArray();
                    if (count($sizeAttr) > 0) {
                        $sizeAttr = \App\AdminModel\Size\AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                        if (count($sizeAttr) > 0) {

                            $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                            $categoryProduct             = AdminProductAttribute::where(['is_active' => 1])->whereIn('products_id', $categoryProduct)->whereIn('size_id', $sizeAttr)->pluck('products_id')->toArray();
                        } else {
                            $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                        }
                    } else {
                        $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                    }
                    $query->whereIn('id', $categoryProduct);
                }
                else
                {
                    if ($mainCategoryId)
                    {
                        $categoryFilter = AdminCategory::where('parent_id', $mainCategoryId)->pluck('id');
                        if ($categoryFilter)
                        {
                            $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                            $query->whereIn('id', $categoryProduct);
                        }
                    }
                }
            }

            if ($price)
            {
                $price = explode("-", $price);
                if ($price && isset($price[0]) && isset($price[1]))
                {
                    $min              = $price[0];
                    $max              = $price[1];
                    //$query->where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max);
                    $priceProductsIds = AdminProductAttribute::where('product_sale_price', '>=', $min)->where('product_sale_price', '<', $max)->pluck('products_id');
                    $query->whereIn('id', $priceProductsIds);
                }
            }
            if ($color)
            {
                $record = AdminProductAttribute::whereIn('color_id', $color)->pluck('products_id');
                if ($record)
                {
                    $query->whereIn('id', $record);
                }
            }
            if ($size)
            {
                $record = AdminProductAttribute::whereIn('size_id', $size)->pluck('products_id');
                if ($record)
                {
                    $query->whereIn('id', $record);
                }
            }

            $query->orderByRaw("FIELD(brand_id, $orderBy)")
                    ->having('filter_count', '!=', 0);

            return $query->get();
        }
        return [];
    }

    public function getProductBrand($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany(AdminBrandDescription::class, 'brand_id', 'brand_id')->withTrashed();
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function getProductMonogram()
    {
        return $this->hasMany(AdminProductMonogram::class, 'product_id', 'id')->get();
    }
    
    public function getProductLength($languageId = "1")
    {
        $length = $this->hasMany(AdminProductLength::class, 'products_id', 'id')->pluck('length_id');
        if (count($length) > 0)
        {
            return \App\AdminModel\Length\AdminLengthDescription::whereIn('length_id', $length)->where('language_id', $languageId)->get();
        }
        return false;
    }

    public function getGallerySizeImage($withoutSizeImage = false, $sizeId = false, $colorId = false)
    {
        $query = $this->hasOne(AdminProductImage::class, 'products_id', 'id')->where('is_default', 0);
        if ($withoutSizeImage)
        {
            $query->whereNull('size_id');
        }
        if ($sizeId)
        {
            $query->where('size_id', $sizeId);
        }

        if ($colorId)
        {
            $query->where('color_id', $colorId);
        }
        else
        {
            $query->whereNull('color_id');
        }
        return $query = $query->get();
    }

}

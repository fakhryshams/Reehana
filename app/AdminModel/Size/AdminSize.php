<?php

namespace App\AdminModel\Size;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Size\AdminSizeDescription;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Product\AdminProductCategory;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Product\AdminProductDescription;

class AdminSize extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_size';
    protected $guarded = ['created_at,updated_at,deleted_at'];

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

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength, $filterCategory = false)
    {
        switch ($sortKey) {
                /*
             * if sortkey = title
             */
            case 'size_title':
                $record = $this->sortByTitle($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($filterCategory) {
            $record = $this->filterByCategory($filterCategory);
            if ($record) {
                $query->whereIn('id', $record);
            }
        }

        if ($searchText) {
            $query->where(function ($query) use ($searchText) {
                /*
                 * Search by category
                 */
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $record = $this->searchbByTitle($searchText);
                if ($record) {
                    $query->orWhereIn('id', $record);
                }
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active') {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive') {
                    $query->orWhere('is_active', 0);
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
     * Sorting Functions
     */

    public function sortByTitle($sortKey, $sortVal)
    {
        return AdminSizeDescription::orderBy('size_title', $sortVal)->where('language_id', config('custom.admin_default_language_id'))->pluck('size_id')->toArray();
    }

    /*
     * search By Title
     */

    public function searchbByTitle($searchText)
    {
        return AdminSizeDescription::where('size_title', 'LIKE', '%' . $searchText . '%')->pluck('size_id');
    }

    public function getSizeDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany(AdminSizeDescription::class, 'size_id', 'id');
        if ($languageId) {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    public function filterByCategory($filter)
    {
        $record = AdminSizeCategory::whereIn("category_id", $filter)->pluck('size_id');
        return $record ? $record : false;
    }

    public function getSizeCategory($getIds = false)
    {
        $query = $this->hasMany(AdminSizeCategory::class, 'size_id', 'id');
        return $getIds ? $query->where('is_sub_category', 0)->pluck('category_id') : $query->get();
    }

    public function getSizeSubCategory($sizeId)
    {
        $query = AdminSizeCategory::where('size_id', $sizeId)->where('is_sub_category', 1);
        return  $query->pluck('category_id');
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    /*
     * Used in Product Add/Edit page for List
     */

    public static function getSize($filterType, $categoryFilter = [], $price = [], $color = [], $search = "", $brand = "", $mainCategoryId = false)
    {
        /*
         * Get Product Id
         */

        $query = AdminProduct::where(['is_active' => 1])->where('have_size', 1)->where('is_active_category', 1);
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
        } else if ($filterType == 'product') {

            if ($search) {
                $categoryTypeFilter  = AdminCategory::getProductTypeCategory($search);

                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryTypeFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);

                $productSearch = AdminProductDescription::orWhere('products_name', 'LIKE', '%' . $search . '%')->pluck('products_id');
                if (count($productSearch)) {
                    $query->whereIn('id', $productSearch);
                }
            }
        }

        if ($categoryFilter) {
            if (!is_array($categoryFilter)) {
                $categoryFilter = [$categoryFilter];
            }

            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all') {
                $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                $query->whereIn('id', $categoryProduct);
            } else {
                if ($mainCategoryId) {
                    $categoryFilter = AdminCategory::where('parent_id', $mainCategoryId)->pluck('id')->toArray();
                    if ($categoryFilter) {
                        $categoryProduct     = $productIdByCategory = AdminProductCategory::whereIn('categories_id', $categoryFilter)->pluck('products_id')->toArray();
                        $query->whereIn('id', $categoryProduct);
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
        if ($brand) {
            if (!is_array($brand)) {
                $brand = [$brand];
            }
            $query->whereIn('brand_id', $brand);
        }
        $productIds = $query->pluck('id')->toArray();

        if ($productIds) {


            // if ($search) {
            //     $categoryFilter  = AdminCategory::getProductTypeCategory($search);               
            // }

            if (!is_array($categoryFilter)) {
                $categoryFilter = [$categoryFilter];
            }

            $sizeAttr = [];
            if (isset($categoryFilter[0]) && $categoryFilter[0] != 'all') {
                $sizeAttr =  AdminSizeCategory::whereIn('category_id', $categoryFilter)->where('is_sub_category', 1)->pluck('size_id')->toArray();
                if (count($sizeAttr) > 0) {
                    $sizeAttr = AdminSize::orderBy('sorting', 'asc')->whereIn('id',  $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                }
            }
            if (count($sizeAttr) == 0 || !empty($productIds)) {




                $sizeAttr = AdminProductAttribute::whereIn('products_id', $productIds)->whereNotNull('size_id')->where('quantity', '>', 0);

                if ($color) {

                    $sizeAttr =  $sizeAttr->whereIn('color_id', $color);
                }
                $sizeAttr =  $sizeAttr->pluck('size_id')->toArray();


                if (count($sizeAttr) > 0) {
                    $sizeAttr = AdminSize::orderBy('sorting', 'asc')->whereIn('id', $sizeAttr)->where('is_active', 1)->pluck('id')->toArray();
                }
            }


            //if(count($sizeAttr1) >0)
            // {
            //  $arrfinal = array_diff($sizeAttr, $sizeAttr1);

            //  $sizeAttr = array_merge($sizeAttr1,$arrfinal);
            // }

            //dd( $sizeAttrstring);
            if (count($sizeAttr) == 0) {
                // $sizeAttrstring = implode(",", $sizeAttr);

                // $orderBy  = AdminSize::whereIn('id', $sizeAttr)->where('is_active', 1)->orderByRaw("FIELD(id, $sizeAttrstring)")->get();
                $orderBy  = AdminSize::whereIn('id', $sizeAttr)->where('is_active', 1)->orderBy('sorting', 'asc')->get();
            } else {
                $orderBy  = AdminSize::whereIn('id', $sizeAttr)->where('is_active', 1)->orderBy('sorting', 'asc')->get();
            }
            return (['data' => $orderBy, 'productIds' => $productIds]);
        }

        return [];
        //
        //        /*
        //         * /.Get Product Id
        //         */
        //        $productAttributes = AdminProductAttribute::whereIn('products_id', $productId)->pluck('size_id')->toArray();
        //
        //        $query = AdminSize::where(['is_active' => 1])->orderBy('sorting', 'asc');
        //        $query->whereIn('id', $productAttributes);
        //        return $query->get();
    }

    public static function getSizeAdmin($category = false, $selectedSizes)
    {
        $query = AdminSize::where(['is_active' => 1]);
        if ($category) {

            if (is_array($category)) {
                $sizeId = AdminSizeCategory::whereIn('category_id', $category)->where(['is_sub_category' => '1', 'is_active' => 1])->pluck('size_id');
            } else {

                $sizeId = AdminSizeCategory::where(['category_id' => $category, 'is_active' => 1, 'is_sub_category' => '0'])->pluck('size_id');
            }
            if ($sizeId) {
                $query->whereIn('id', $sizeId);
                if ($selectedSizes) {
                    $query->orWhereIn('id', $selectedSizes);
                }
            } else {
                return [];
            }
        }
        $query->orderBy('sorting', 'asc');
        return $query->get();
    }

    public function getSizeCount($productIds)
    {
        $query = AdminProductAttribute::whereIn('products_id', $productIds)->where('size_id', $this->id)->pluck('products_id')->toArray();
        return count(array_unique($query));
    }
}

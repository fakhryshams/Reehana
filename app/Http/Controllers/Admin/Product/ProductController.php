<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
/*
 * Model
 */
//use App\AdminModel\AdminCurrency;
//use App\AdminModel\Product\AdminManufacturer;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Unit\AdminUnit;
/*
 * Product 
 */
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductDescription;
use App\AdminModel\Product\AdminProductCategory;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Product\AdminProductImage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductExport;
/*
 * Color
 */
use App\AdminModel\Color\AdminColor;
use App\AdminModel\Brand\AdminBrand;
/*
 * Size
 */
use App\AdminModel\Size\AdminSize;
/*
 * For Image 
 */
use Image;
use App\AdminModel\Product\AdminProductMonogram;

use App\AdminModel\Product\ProductPlacement;

class ProductController extends Controller
{

    private $langFile = 'admin';
    private $module = '.product';

    public function index(Request $request, $pageLength = '25')
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        if ($request->na) {
            $sorting = Helper::getOrderSortingColumn2($request);
        } else {
            $sorting = Helper::getOrderSortingColumn($request);
        }
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength ? $request->pageLength : $pageLength;
        $productId = $request->id;
        $outOfStockFilter = $request->OOSFilter;
        $newArrivalFilter = $request->na;
        $latestCollectionFilter = $request->lc;
        $filterCategory = $request->_c;
        $filterBrand = $request->_b;
        $intlFilter = $request->ish;
        $category = AdminCategory::getCategories(1, true);
        $brand = AdminBrand::getData(1);
        $product = new AdminProduct();
        $product = $product->getAllData(
            $searchText,
            $sortKey,
            $sortVal,
            $pageLength,
            $productId,
            $outOfStockFilter,
            $filterCategory,
            $newArrivalFilter,
            $intlFilter,
            $filterBrand
        );
        return view('admin.product.product.index', compact('sortKey', 'sortVal', 'searchText', 'product', 'pageLength', 'productId', 'outOfStockFilter', 'category', 'filterCategory', 'newArrivalFilter', 'latestCollectionFilter', 'intlFilter', 'brand', 'filterBrand'));
    }


    public function outOfStock(Request $request, $pageLength = 'ALL')
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        if ($request->na) {
            $sorting = Helper::getOrderSortingColumn2($request);
        } else {
            $sorting = Helper::getOrderSortingColumn($request);
        }
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength ? $request->pageLength : $pageLength;
        $productId = $request->id;
        $outOfStockFilter = $request->OOSFilter;
        $newArrivalFilter = $request->na;
        $latestCollectionFilter = $request->lc;
        $filterCategory = $request->_c;
        $filterBrand = $request->_b;
        $intlFilter = $request->ish;
        $category = AdminCategory::getCategories(1, true);
        $brand = AdminBrand::getData(1);
        $product = new AdminProduct();
        $product = $product->outofStock(
            $searchText,
            $sortKey,
            $sortVal,
            $pageLength,
            $productId,
            $outOfStockFilter,
            $filterCategory,
            $newArrivalFilter,
            $intlFilter,
            $filterBrand
        );
        
        return view('admin.product.product.outofstock', compact('sortKey', 'sortVal', 'searchText', 'product', 'pageLength', 'productId', 'outOfStockFilter', 'category', 'filterCategory', 'newArrivalFilter', 'latestCollectionFilter', 'intlFilter', 'brand', 'filterBrand'));
    }

    function getSortingNumber()
    {
        $model = new AdminProduct();
        $sortingNumber = $model->getSortingNumber();
        if (!$sortingNumber) {
            $sortingNumber = 0;
        }
        $sortingNumber++;
        return $sortingNumber;
    }
	
	function getNewArrivalSortingNumber()
    {
        $model = new AdminProduct();
        $sortingNumber = $model->getNewArrivalSortingNumber();
        if (!$sortingNumber) {
            $sortingNumber = 0;
        }
        $sortingNumber++;
        return $sortingNumber;
    }

    /*
     * Update Sorting Order
     */

    function updateSortingOrder($currentOrder, $newOrder)
{
    // Retrieve all records having the same current sorting order
    $affectedRecords = AdminProduct::where('sorting', $currentOrder)->get();

    // If the current order is greater than the new order, shift the affected records down
    if ($currentOrder > $newOrder) {
        $recordsToUpdate = AdminProduct::where('sorting', '>=', $newOrder)
            ->where('sorting', '<', $currentOrder)
            ->orderBy('sorting', 'asc')
            ->get();

        foreach ($recordsToUpdate as $record) {
            $record->sorting++;
            $record->save();
        }
    } 
    // If the current order is less than the new order, shift the affected records up
    elseif ($currentOrder < $newOrder) {
        $recordsToUpdate = AdminProduct::where('sorting', '>', $currentOrder)
            ->where('sorting', '<=', $newOrder)
            ->orderBy('sorting', 'asc')
            ->get();

        foreach ($recordsToUpdate as $record) {
            $record->sorting--;
            $record->save();
        }
    }

    // Update the sorting order of the record being moved
    foreach ($affectedRecords as $record) {
        $record->sorting = $newOrder;
        $record->save();
    }
}


    public function create()
    {




        $languageList = Helper::getLanguageList();
        if ($languageList) {
            /*
             * Dropdown Data
             */
            $monogram = \App\AdminModel\Monogram\MonogramFontStyle::getMonogramStyleList();
            $category = AdminCategory::getCategoriesFront();
            $brand = AdminBrand::getData(1);
            //$size               = AdminSize::getSizeAdmin();
            $isNewArrival = config('admin.product.isNewArrival');
            $isActive = config('admin.product.isActive');
            $sortingNumber = $this->getSortingNumber();
			$newArrivalSortingNumber = $this->getNewArrivalSortingNumber();
			
            /*
             * 
              $manufacturers      = AdminManufacturer::getData();
              $flashSale       = config('admin.product.flashSale');
              $isSpecial       = config('admin.product.isSpecial');
              $flashSaleStatus = config('admin.product.flashSaleStatus');
              $specialStatus = config('admin.product.specialStatus');
             *
             */

            return view("admin.product.product.add", compact('languageList', 'category', 'isNewArrival', 'isActive', 'sortingNumber', 'brand', 'monogram','newArrivalSortingNumber'));
        } else {
            return redirect()->route('admin.product.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {





        $languageList = Helper::getLanguageList();
        /*
         * For image uplod count - 
         * Aim - for erorr message manage and old value
         */
        $count = isset($request->product_images) ? count($request->product_images) : 0;
        session()->flash("form_count", $count);

        /*
         * Product Price Validation
         */
        $ruleProductPrice = "required|numeric";
        if (isset($request->is_price_required) && $request->is_price_required == 0) {
            //$ruleProductPrice = "nullable|numeric";
        }

        /*
         * Validations
         */
        $sortingNumber = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
            'category_id' => 'required',
            'product_regular_price' => $ruleProductPrice,
            'product_weight' => $ruleProductPrice,
            'product_sale_price' => 'nullable|numeric',
            'color_id' => 'nullable',
            //            'product_sku'             => 'required|unique:admin_products,product_sku,NULL,id,deleted_at,NULL',
            'product_barcode' => 'required',
            'product_sku' => 'nullable',
            'is_active' => 'required',
            'is_new_arrival' => 'required',
            'product_seo' => 'nullable',
            'product_default_image' => 'required|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.product_image_dimensions_width') . ',height=' . config('custom.product_image_dimensions_height'),
            'product_images.*' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.product_image_dimensions_width') . ',height=' . config('custom.product_image_dimensions_height'),
            'product_length' => 'nullable|numeric',
            'product_width' => 'nullable|numeric',
            'product_height' => 'nullable|numeric',
            'hs_code' => 'nullable|string',
            'country_of_origin' => 'nullable|string|min:2|max:3|alpha',
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric' => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte' => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min' => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max' => $message_text_sorting,
            'category_id.required' => Lang::get($this->langFile . '.error-product-req-category'),
            'product_regular_price.required' => Lang::get($this->langFile . '.error-product-req-reg-price'),
            'product_regular_price.numeric' => Lang::get($this->langFile . '.error-product-numeric-reg-price'),
            'product_weight.required' => Lang::get($this->langFile . '.error-product-req-product_weight'),
            'product_weight.numeric' => Lang::get($this->langFile . '.error-product-numeric-product_weight'),
            'product_sale_price.numeric' => Lang::get($this->langFile . '.error-product-numeric-sale-price'),
            'product_sale_price.lte' => Lang::get($this->langFile . '.error-product-lte-sale-price'),
            'product_quantity.required' => Lang::get($this->langFile . '.error-product-req-product_quantity'),
            'product_quantity.numeric' => Lang::get($this->langFile . '.error-product-numeric-product_quantity'),
            'product_quantity.lte' => Lang::get($this->langFile . '.error-product-lte-product_quantity'),
            'size.*.required' => Lang::get($this->langFile . '.error-product-req-size'),
            'product_sku.required' => Lang::get($this->langFile . '.error-product-req-sku'),
            'product_sku.unique' => Lang::get($this->langFile . '.error-product-unique-sku'),
            'is_active.required' => Lang::get($this->langFile . '.error-product-req-is_active'),
            'is_new_arrival.required' => Lang::get($this->langFile . '.error-product-req-is_new_arrival'),
            'product_seo_title.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_title'),
            'product_seo_description.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_description'),
            'product_seo_keyword.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_keyword'),
            'product_seo_url.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_url'),
            'product_default_image.required' => Lang::get($this->langFile . '.error-product-req-product_default_image'),
            'product_default_image.image' => Lang::get($this->langFile . '.error-product-image-product_default_image'),
            'product_default_image.dimensions' => Lang::get($this->langFile . '.error-product-dimensions-product_default_image'),
            'product_default_image.mimes' => Lang::get($this->langFile . '.error-product-mimes-product_default_image'),
            'product_images.*.image' => Lang::get($this->langFile . '.error-product-image-product_images'),
            'product_images.*.mimes' => Lang::get($this->langFile . '.error-product-mimes-product_images'),
            'product_images.*.dimensions' => Lang::get($this->langFile . '.error-product-mimes-product_images'),
            'product_images.*.dimensions' => Lang::get($this->langFile . '.error-product-dimensions-product_images'),
        ];

        /*
         * Language wise Field Validation
         */
        foreach ($languageList as $language) {
            $message_text = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-product-req-product-name'));
            $validationRule['product_name_' . $language->id] = 'required';
            $validationMessage['product_name_' . $language->id . '.required'] = $message_text;
            //$message_text                                                            = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-product-req-product-desc'));
            //$validationRule['product_description_' . $language->id]                  = 'required';
            //$validationMessage['product_description_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        /*
         * All validation Passed
         */
        DB::beginTransaction();

        try {
            /*
             * Insert status Flag
             */
            $flag = true;
            $model = new AdminProduct();
            /*
             * code for sorting
             */
            $model->sorting = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            $model->brand_id = $request->brand_id;
            $model->product_regular_price = $request->product_regular_price;
            $model->product_sale_price = $request->product_sale_price ? $request->product_sale_price : $request->product_regular_price;
            $model->is_product_sale_price_null = $request->product_sale_price ? 0 : 1;
            $model->discount_in = isset($request->main_discount_in) ? $request->main_discount_in : null;
            $model->discount = isset($request->main_discount) ? $request->main_discount : null;
            $model->product_weight = isset($request->product_weight) ? $request->product_weight : null;
            $model->product_sku = $request->product_sku;
            $model->product_barcode = $request->product_barcode;
            $model->product_quantity = $request->product_quantity;
            $model->product_size = (isset($request->size) && $request->size) ? 1 : 0;
            $model->is_new_arrival = $request->is_new_arrival;
            $model->is_active = $request->is_active;

            $model->product_seo = $request->product_seo ? 1 : 0;
            $model->product_seo_title = $request->product_seo_title;
            $model->product_seo_description = $request->product_seo_description;
            $model->product_seo_url = $request->product_seo_url;
            $model->product_seo_keyword = $request->product_seo_keyword;
            $model->intl_shipping = $request->intl_shipping ? 1 : 0;
            // FedEx Fields
            $model->product_length = $request->product_length;
            $model->product_width = $request->product_width;
            $model->product_height = $request->product_height;
            $model->dimension_unit = $request->dimension_unit ?? 'cm';
            $model->weight_unit = $request->weight_unit ?? 'kg';
            $model->country_of_origin = $request->country_of_origin;
            $model->hs_code = $request->hs_code;
			if($request->is_new_arrival == 1)
			{
                AdminProduct::where('is_new_arrival', 1)
                    ->update(['new_arrival_soting' => DB::raw('new_arrival_soting + 1')]);
                $model->new_arrival_soting = 1;
			} else {
                $model->new_arrival_soting = null;
            }
			
            //$model->have_price_variant      = isset($request->have_price_variant) ? ($request->have_price_variant) : 0;
            /*
             * Save Main Record
             */
            if ($model->save()) {
                AdminProduct::reorderNewArrivals();
                #Product Monogram
                if (isset($request->monogram) && $request->monogram) {
                    foreach ($request->monogram as $monogram) {
                        $productMonogram = new AdminProductMonogram();
                        $productMonogram->product_id = $model->id;
                        $productMonogram->font_style_id = $monogram;

                        if (!$productMonogram->save()) {
                            $flag = false;
                        }
                    }



                    foreach ($request->monogram as $monogram) {

                        if ($request->placement[$monogram]) {

                            foreach ($request->placement[$monogram] as $placement) {
                                $productMonogram = new ProductPlacement();
                                $productMonogram->monogram_id =  $monogram;
                                $productMonogram->product_id =  $model->id;
                                $productMonogram->placement_id =  $placement;

                                if (!$productMonogram->save()) {
                                    $flag = false;
                                }
                            }
                        }
                    }
                }
                /*
                 * Product Category
                 */
                $productCategory = null;
                foreach ($request->category_id as $categoryId) {
                    /*
                      $productCategory                = new AdminProductCategory();
                      $productCategory->products_id   = $model->id;
                      $productCategory->categories_id = $categoryId;

                      if (!$productCategory->save())
                      {
                      $flag = false;
                      }
                     * 
                     */
                    $productCategory = $categoryId;
                }

                if ($productCategory) {
                    $model->category_id = $productCategory;
                    $model->save();
                }

                foreach ($request->sub_category_id as $categoryId) {
                    $productCategory = new AdminProductCategory();
                    $productCategory->products_id = $model->id;
                    $productCategory->categories_id = $categoryId;

                    if (!$productCategory->save()) {
                        $flag = false;
                    }
                }

                /*
                 * Default Image Upload
                 */

                if ($request->file('product_default_image')) {
					$file = $request->file('product_default_image');

					// Generate unique WebP file names
					$fileName = uniqid() . '.webp';
					$thumbFileName = uniqid() . '_thumb.webp';

					// Define storage paths
					$filePath = config('filepath.product_image') . '/' . $fileName;
					$thumbFilePath = config('filepath.product_image_thumb') . '/' . $thumbFileName;

					// Convert the main image to WebP
					$webpImage = Image::make($file)->encode('webp', 90);
					Storage::disk('local')->put($filePath, $webpImage->stream());

					// Create and save a resized thumbnail version
					$webpThumb = Image::make($file)->crop(config('custom.product_resize_image_dimensions_width'), config('custom.product_resize_image_dimensions_height'))->encode('webp', 80);
					Storage::disk('local')->put($thumbFilePath, $webpThumb->stream());

					/*
					 * Main Image Store
					 */
					$productImages = new AdminProductImage();
					$productImages->products_id = $model->id;
					$productImages->image_url = $filePath;  // Main WebP image
					$productImages->image_thumb_url = $thumbFilePath; // WebP thumbnail
					//$productImages->image_cart_url = $cartImagePath; // (If needed, add cart image)
					$productImages->is_default = true;

					if (!$productImages->save()) {
						$flag = false;
					}
				}

                /*
                 * Other Image Upload
                 */

              

				if ($request->product_images && count($request->product_images) > 0) {
					foreach ($request->product_images as $image) {
						// Generate unique WebP file names
						$fileName = uniqid() . '.webp';
						$thumbFileName = uniqid() . '_thumb.webp';

						// Define storage paths
						$filePath = config('filepath.product_image') . '/' . $fileName;
						$thumbFilePath = config('filepath.product_image_thumb') . '/' . $thumbFileName;

						// Convert the main image to WebP
						$webpImage = Image::make($image)->encode('webp', 90);
						Storage::disk('local')->put($filePath, $webpImage->stream());

						// Create and save a resized thumbnail version
						$webpThumb = Image::make($image)->crop(config('custom.product_resize_image_dimensions_width'), config('custom.product_resize_image_dimensions_height'))->encode('webp', 80);
						Storage::disk('local')->put($thumbFilePath, $webpThumb->stream());

						// Store image details in the database
						$productImages = new AdminProductImage();
						$productImages->products_id = $model->id;
						$productImages->image_url = $filePath; // Main WebP image
						$productImages->image_thumb_url = $thumbFilePath; // WebP thumbnail
						//$productImages->image_cart_url = $cartImagePath; // (If needed, add cart image)
						$productImages->is_default = false;

						if (!$productImages->save()) {
							$flag = false;
						}
					}
				}


                /*
                 * Slug Creation
                 */
                $slugFlag = false;

                foreach ($languageList as $language) {
                    $subModel = new AdminProductDescription();

                    $productName = 'product_name_' . $language->id;
                    $productDescription = 'product_description_' . $language->id;
                    $productOtherDescription = 'product_return_exchange_policy_' . $language->id;

                    $subModel->products_id = $model->id;
                    $subModel->language_id = $language->id;
                    $subModel->products_name = $request->$productName;
                    $subModel->products_description = $request->$productDescription;
                    $subModel->product_return_exchange_policy = $request->$productOtherDescription;

                    if ($slugFlag == false) {
                        $slugFlag = true;
                        $slug = $request->$productName;
                        $oldSlug = $request->$productName;

                        $slugCount = 0;
                        do {
                            if ($slugCount == 0) {
                                $currentSlug = Helper::slugify($oldSlug);
                            } else {
                                $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                            }

                            $slug = $currentSlug;
                            $checkSlug = AdminProduct::checkSlug($currentSlug);
                            $slugCount++;
                        } while (count($checkSlug) > 0);

                        $updateSlug = AdminProduct::updateSlug($model->id, $slug);
                        if (!$updateSlug) {
                            $flag = false;
                        }
                    }

                    /*
                     * Save Description
                     */
                    if (!$subModel->save()) {
                        $flag = false;
                    }
                }
            } else {
                $flag = false;
            }

            if ($flag) {
                DB::commit();
                
                $sortingcount  =AdminProduct::where('sorting',$model->sorting)->count();
                
                if($sortingcount  >1)
                {
                    $up = AdminProduct::where('id',$model->id)->first();
                    $up->sorting = $this->getSortingNumber();
                    $up->save();
                }
                
                 
                
                $status = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
            } else {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } catch (Throwable $e) {
            DB::rollback();
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        if (isset($request->have_price_variant) && $request->have_price_variant == 1 && $status == 'success') {
            return redirect()->route('admin.product.stock', ['id' => $model->id])->with($status, $message_text);
        } else {
            return redirect()->route('admin.product.index')->with($status, $message_text);
        }
    }

    public function edit($id)
    {
        $languageList = Helper::getLanguageList();
        $record = AdminProduct::find($id);

        if ($languageList && $record) {
            $checkProductHasOrders = \App\UserModel\OrderDetail::where('product_id', $id)->get();

            $canEditVarient = false;

            if ((int) $record->have_price_variant == 0) {
                #Check this Product order status
                if (count($checkProductHasOrders) == 0) {
                    $canEditVarient = true;
                } else {
                    $productOrderID = $checkProductHasOrders->pluck('order_id');
                    if (count($productOrderID)) {
                        $productOrderID = \App\AdminModel\Order\WebOrder::whereIn('id', $productOrderID)->whereIn('order_status', [1, 2, 3, 7])->count();
                        if ($productOrderID == 0) {
                            $canEditVarient = true;
                        }
                    }
                }
            }

            if (count($checkProductHasOrders)) {
                $checkProductHasOrders = $record->product_size;
            }
            /*
             * Dropdown Data
             */
            $monogram = \App\AdminModel\Monogram\MonogramFontStyle::getMonogramStyleList();
            $productMonogram = $record->getProductMonogram();
            if ($productMonogram) {
                $productMonogram = $productMonogram->pluck('font_style_id')->toArray();
            }
            $category = AdminCategory::getCategoriesFront();
            $brand = AdminBrand::getData(1);
            $isNewArrival = config('admin.product.isNewArrival');
            $isActive = config('admin.product.isActive');
            $sortingNumber = $this->getSortingNumber();
            $currentOrder = $record->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
			$newArrivalSortingNumber = $this->getNewArrivalSortingNumber();
			
            return view("admin.product.product.edit", compact('languageList', 'category', 'isNewArrival', 'isActive', 'sortingNumber', 'record', 'checkProductHasOrders', 'brand', 'monogram', 'productMonogram', 'canEditVarient','newArrivalSortingNumber'));
        } else {
            return redirect()->route('admin.product.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
        }
    }

    public function update(Request $request, $id)
    {

        $languageList = Helper::getLanguageList();
        $model = AdminProduct::find($id);
        $wasNewArrival = $model ? ($model->is_new_arrival == 1) : false;
        $oldOrder = $model ? $model->new_arrival_soting : null;

        $message_text = Lang::get($this->langFile . '.update-error');
        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);

        $status = 'error';

        if ($model) {
            /*
             * For image uplod count  
             * Aim - for erorr message manage and old value
             */
            $count = isset($request->product_images) ? count($request->product_images) : 0;
            session()->flash("form_count", $count);

            /*
             * Product Price Validation
             */
            $ruleProductPrice = "required|numeric";
            if (isset($model->have_price_variant) && $model->have_price_variant == 1) {
                $ruleProductPrice = "nullable|numeric";
            }

            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;

            /*
             * Validations
             */
            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
                'category_id' => 'required',
                'product_regular_price' => $ruleProductPrice,
                'product_weight' => $ruleProductPrice,
                //                'product_sale_price'      => 'nullable|numeric|lte:product_regular_price',
                'product_sale_price' => 'nullable|numeric',
                //'product_quantity'        => $ruleProductQuantity,
                //'product_sku'             => "required|unique:admin_products,product_sku,$id,id,deleted_at,NULL",
                'product_barcode' => "required",
                'is_active' => 'required',
                'is_new_arrival' => 'required',
                'product_seo' => 'nullable',
                'product_default_image' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.product_image_dimensions_width') . ',height=' . config('custom.product_image_dimensions_height'),
                'product_images.*' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.product_image_dimensions_width') . ',height=' . config('custom.product_image_dimensions_height'),
                'product_length' => 'nullable|numeric',
                'product_width' => 'nullable|numeric',
                'product_height' => 'nullable|numeric',
                'hs_code' => 'nullable|string',
                'country_of_origin' => 'nullable|string|min:2|max:3|alpha',
            ];


            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric' => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte' => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min' => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max' => $message_text_sorting,
                'category_id.required' => Lang::get($this->langFile . '.error-product-req-category'),
                'product_regular_price.required' => Lang::get($this->langFile . '.error-product-req-reg-price'),
                'product_regular_price.numeric' => Lang::get($this->langFile . '.error-product-numeric-reg-price'),
                'product_sale_price.numeric' => Lang::get($this->langFile . '.error-product-numeric-sale-price'),
                'product_sale_price.lte' => Lang::get($this->langFile . '.error-product-lte-sale-price'),
                'product_quantity.required' => Lang::get($this->langFile . '.error-product-req-product_quantity'),
                'product_quantity.numeric' => Lang::get($this->langFile . '.error-product-numeric-product_quantity'),
                'product_quantity.lte' => Lang::get($this->langFile . '.error-product-lte-product_quantity'),
                'product_weight.required' => Lang::get($this->langFile . '.error-product-req-product_weight'),
                'product_weight.numeric' => Lang::get($this->langFile . '.error-product-numeric-product_weight'),
                'product_sku.required' => Lang::get($this->langFile . '.error-product-req-sku'),
                'product_sku.unique' => Lang::get($this->langFile . '.error-product-unique-sku'),
                'is_active.required' => Lang::get($this->langFile . '.error-product-req-is_active'),
                'is_new_arrival.required' => Lang::get($this->langFile . '.error-product-req-is_new_arrival'),
                'product_seo_title.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_title'),
                'product_seo_description.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_description'),
                'product_seo_url.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_url'),
                'product_seo_keyword.required_if' => Lang::get($this->langFile . '.error-product-req-product_seo_keyword'),
                'product_default_image.required' => Lang::get($this->langFile . '.error-product-req-product_default_image'),
                'product_default_image.image' => Lang::get($this->langFile . '.error-product-image-product_default_image'),
                'product_default_image.dimensions' => Lang::get($this->langFile . '.error-product-dimensions-product_default_image'),
                'product_default_image.mimes' => Lang::get($this->langFile . '.error-product-mimes-product_default_image'),
                'product_images.*.image' => Lang::get($this->langFile . '.error-product-image-product_images'),
                'product_images.*.mimes' => Lang::get($this->langFile . '.error-product-mimes-product_images'),
                'product_images.*.dimensions' => Lang::get($this->langFile . '.error-product-mimes-product_images'),
                'product_images.*.dimensions' => Lang::get($this->langFile . '.error-product-dimensions-product_images'),
            ];

            /*
             * Language wise Field Validation
             */
            foreach ($languageList as $language) {
                $message_text = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-product-req-product-name'));
                $validationRule['product_name_' . $language->id] = 'required';
                $validationMessage['product_name_' . $language->id . '.required'] = $message_text;
                //$message_text                                                            = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-product-req-product-desc'));
                //$validationRule['product_description_' . $language->id]                  = 'required';
                //$validationMessage['product_description_' . $language->id . '.required'] = $message_text;
            }

            $validatedData = $request->validate($validationRule, $validationMessage);

            /*
             * All validation Passed
             */
            DB::beginTransaction();

            try {
                $flag = true;

                if (isset($request->have_price_variant) && $request->have_price_variant == 1) {
                    $checkProductHasOrders = \App\UserModel\OrderDetail::where('product_id', $id)->get();

                    $canEditVarient = false;

                    if ((int) $model->have_price_variant == 0) {
                        #Check this Product order status
                        if (count($checkProductHasOrders) == 0) {
                            $canEditVarient = true;
                        } else {
                            $productOrderID = $checkProductHasOrders->pluck('order_id');
                            if (count($productOrderID)) {
                                $productOrderID = \App\AdminModel\Order\WebOrder::whereIn('id', $productOrderID)->whereIn('order_status', [1, 2, 3, 7])->count();
                                if ($productOrderID == 0) {
                                    $canEditVarient = true;
                                }
                            }
                        }
                    }


                    if ($canEditVarient) {
                        $modelCopy = new AdminProduct();
                        $modelCopy = $model->replicate();
                        //$modelCopy->have_price_variant = 1;

                        if ($modelCopy->save()) {
                            $currentProductMonogram = AdminProductMonogram::where('product_id', $id)->get();
                            foreach ($currentProductMonogram as $monogram) {
                                $productMonogram = new AdminProductMonogram();
                                $productMonogram = $monogram->replicate();
                                $productMonogram->product_id = $modelCopy->id;
                                if (!$productMonogram->save()) {
                                    $flag = false;
                                }
                            }
                            $currentPlacementMonogram = ProductPlacement::where('product_id', $id)->get();
                            foreach ($currentPlacementMonogram as $placement) {

                                $productMonogram = new ProductPlacement();
                                $productMonogram = $placement->replicate();
                                $productMonogram->product_id =  $modelCopy->id;
                                if (!$productMonogram->save()) {
                                    $flag = false;
                                }
                            }


                            $currentProductCategory = AdminProductCategory::where('products_id', $id)->get();
                            foreach ($currentProductCategory as $category) {
                                $productCategory = new AdminProductCategory();
                                $productCategory = $category->replicate();
                                $productCategory->products_id = $modelCopy->id;
                                if (!$productCategory->save()) {
                                    $flag = false;
                                }
                            }

                            $currentProductImage = AdminProductImage::where('products_id', $id)->get();
                            foreach ($currentProductImage as $image) {
                                $productImages = new AdminProductImage();
                                $productImages = $image->replicate();
                                $productImages->products_id = $modelCopy->id;
                                if (!$productImages->save()) {
                                    $flag = false;
                                }
                            }

                            $currentProductDesc = AdminProductDescription::where('products_id', $id)->get();
                            foreach ($currentProductDesc as $desc) {
                                $subModel = new AdminProductDescription();
                                $subModel = $desc->replicate();
                                $subModel->products_id = $modelCopy->id;
                                if (!$subModel->save()) {
                                    $flag = false;
                                }
                            }
                        } else {
                            $flag = false;
                        }


                        if ($flag) {
                            $model->sorting = 0;
                            $model->save();
                            $model->delete();
                        }
                    } else {
                        $flag = false;
                        $message_text = __($this->langFile . '.cannot_edit_varient');
                    }
                } else {


                    /*
                     * Insert status Flag
                     */

                    /*
                     * code for sorting
                     */
                    $model->sorting = $request->sorting;
                    $this->updateSortingOrder($currentOrder, $request->sorting);
                    /*
                     * end of code for sorting
                     */
					$model->brand_id = $request->brand_id;
                    if (!$model->have_price_variant) {
                        $model->product_regular_price = $request->product_regular_price;
                        $model->product_sale_price = $request->product_sale_price ? $request->product_sale_price : $request->product_regular_price;
                        $model->is_product_sale_price_null = $request->product_sale_price ? 0 : 1;
                        $model->discount_in = isset($request->main_discount_in) ? $request->main_discount_in : null;
                        $model->discount = isset($request->main_discount) ? $request->main_discount : null;
                        $model->product_weight = isset($request->product_weight) ? $request->product_weight : null;
                    }
                    //                dd($request->all());

                    $model->product_sku = $request->product_sku;
                    $model->product_barcode = $request->product_barcode;
                    if (isset($request->product_quantity)) {
                        $model->product_quantity = $request->product_quantity;
                    }
                    $model->is_new_arrival = $request->is_new_arrival;
                    $model->is_active = $request->is_active;
                    $model->product_seo = $request->product_seo ? 1 : 0;
                    $model->product_seo_title = $request->product_seo_title;
                    $model->product_seo_description = $request->product_seo_description;
                    $model->product_seo_url = $request->product_seo_url;
                    $model->product_seo_keyword = $request->product_seo_keyword;
                    $model->intl_shipping = $request->intl_shipping ? 1 : 0;
                     $model->intl_shipping = $request->intl_shipping ? 1 : 0;
                     // FedEx Fields
                    $model->product_length = $request->product_length;
                    $model->product_width = $request->product_width;
                    $model->product_height = $request->product_height;
                    $model->dimension_unit = $request->dimension_unit ?? 'cm';
                    $model->weight_unit = $request->weight_unit ?? 'kg';
                    $model->country_of_origin = $request->country_of_origin;
                    $model->hs_code = $request->hs_code;
                      $model->updated_at = date('Y-m-d H:i:s');
					 $isNewArrival = $request->is_new_arrival == 1;

                    if (!$isNewArrival) {
                        $model->new_arrival_soting = null;
                        if ($wasNewArrival) {
                            // Shift subsequent records down by 1
                            AdminProduct::where('is_new_arrival', 1)
                                ->where('new_arrival_soting', '>', $oldOrder)
                                ->update(['new_arrival_soting' => DB::raw('new_arrival_soting - 1')]);
                        }
                    } else {
                        if (!$wasNewArrival) {
                            AdminProduct::where('is_new_arrival', 1)
                                ->update(['new_arrival_soting' => DB::raw('new_arrival_soting + 1')]);
                            $model->new_arrival_soting = 1;
                        }
                    }
                    /*
                     * Save Main Record
                     */
                    if ($model->save()) {
                        AdminProduct::reorderNewArrivals();
						
                        #Product Monogram

                            #Remove existing record and insert new

                            AdminProductMonogram::where('product_id', $id)->delete();
                            ProductPlacement::where('product_id', $id)->delete();
                        
                        if (isset($request->monogram) && $request->monogram) {

                            foreach ($request->monogram as $monogram) {
                                $productMonogram = new AdminProductMonogram();
                                $productMonogram->product_id = $model->id;
                                $productMonogram->font_style_id = $monogram;

                                if (!$productMonogram->save()) {
                                    $flag = false;
                                }
                            }


                            foreach ($request->monogram as $monogram) {

                                $placementValue = $request->placement[$monogram] ?? null;
                                if ($placementValue) {
                                    foreach ($placementValue as $placement) {


                                        $productMonogram = new ProductPlacement();
                                        $productMonogram->monogram_id =  $monogram;
                                        $productMonogram->product_id =   $model->id;;
                                        $productMonogram->placement_id =  $placement;

                                        if (!$productMonogram->save()) {
                                            $flag = false;
                                        }
                                    }
                                }
                            }
                        }

                        $productCategory = null;
                        foreach ($request->category_id as $categoryId) {
                            /*
                              $productCategory                = new AdminProductCategory();
                              $productCategory->products_id   = $model->id;
                              $productCategory->categories_id = $categoryId;

                              if (!$productCategory->save())
                              {
                              $flag = false;
                              }
                             * 
                             */
                            $productCategory = $categoryId;
                        }

                        if ($productCategory) {
                            $model->category_id = $productCategory;
                            $model->save();
                        }

                        /*
                         * Product Category
                         */

                        /*
                         * Remove existing record and insert new
                         */
                        AdminProductCategory::where('products_id', $id)->delete();

                        foreach ($request->sub_category_id as $categoryId) {
                            $productCategory = new AdminProductCategory();
                            $productCategory->products_id = $model->id;
                            $productCategory->categories_id = $categoryId;

                            if (!$productCategory->save()) {
                                $flag = false;
                            }
                        }

                        /*
                         * Default Image Upload
                         */

                        if ($request->file('product_default_image')) {
                            $file = $request->file('product_default_image');
                            $path = $this->uploadImage($file);

                            if ($path) {
                                /*
                                 * Main Image Store
                                 * 
                                 * Get exising image -- if not found then insert new
                                 */
                                $productImages = $model->getDefaultImage();
                                if ($productImages) {
                                    $existingDefaultImages = $productImages;
                                } else {
                                    $productImages = new AdminProductImage();
                                }
                                $productImages->products_id = $model->id;
                                $productImages->image_url = $path['path'];
                                $productImages->image_thumb_url = $path['resize'];
                                //$productImages->image_cart_url  = $path['cartImage'];
                                $productImages->is_default = true;
                                if (!$productImages->save()) {
                                    $flag = false;
                                }
                            }
                        }

                        /*
                         * Other Image Upload
                         */

                        if ($request->product_images && count($request->product_images) > 0) {
                            foreach ($request->product_images as $image) {
                                $file = $image;
                                $path = $this->uploadImage($file, false);

                                if ($path) {
                                    $productImages = new AdminProductImage();
                                    $productImages->products_id = $model->id;
                                    $productImages->image_url = $path['path'];
                                    $productImages->image_thumb_url = $path['path'];
                                    //$productImages->image_cart_url  = $path['cartImage'];
                                    $productImages->is_default = false;
                                    if (!$productImages->save()) {
                                        $flag = false;
                                    }
                                }
                            }
                        }

                        /*
                         * Slug Creation
                         */
                        $slugFlag = false;

                        foreach ($languageList as $language) {
                            $subModel = AdminProductDescription::getDataByLanguageID($id, $language->id);
                            if ($subModel) {
                                $productName = 'product_name_' . $language->id;
                                $productDescription = 'product_description_' . $language->id;
                                $productOtherDescription = 'product_return_exchange_policy_' . $language->id;
                                $subModel->products_id = $model->id;
                                $subModel->language_id = $language->id;
                                $subModel->products_name = $request->$productName;
                                $subModel->products_description = $request->$productDescription;
                                $subModel->product_return_exchange_policy = $request->$productOtherDescription;
                                //                            dd($request->$productOtherDescription);
                                if ($slugFlag == false) {
                                    $slugFlag = true;
                                    $slug = $request->$productName;
                                    $oldSlug = $request->$productName;

                                    $slugCount = 0;
                                    do {
                                        if ($slugCount == 0) {
                                            $currentSlug = Helper::slugify($oldSlug);
                                        } else {
                                            $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                                        }

                                        $slug = $currentSlug;
                                        $checkSlug = AdminProduct::checkSlug($currentSlug, $model->id);
                                        $slugCount++;
                                    } while (count($checkSlug) > 0);

                                    $updateSlug = AdminProduct::updateSlug($model->id, $slug);
                                    if (!$updateSlug) {
                                        $flag = false;
                                    }
                                }

                                /*
                                 * Save Description
                                 */
                                if (!$subModel->save()) {
                                    $flag = false;
                                }
                            }
                        }
                    } else {
                        $flag = false;
                    }
                }

                if ($flag) {
                    DB::commit();
                    $status = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                } else {
                    DB::rollback();
                    $status = 'error';
                }
            } catch (Throwable $e) {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } else {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        if (isset($request->have_price_variant) && $request->have_price_variant == 1 && $status == 'success' && isset($modelCopy)) {
            return redirect()->route('admin.product.stock', ['id' => $modelCopy->id])->with($status, $message_text);
        } else {
            return redirect()->route('admin.product.index')->with($status, $message_text);
        }

        //return redirect()->route('admin.product.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id) {
            $record = AdminProduct::find($id);
            $deleteFlag = true;
            /*
             * Check foreign record before delete
             */
            $productExistMessage = Lang::get($this->langFile . '.record_not_found');
            $wishlist = \App\AdminModel\Product\Wishlist::where('product_id', $id)->count();
            $latestCollection = \App\AdminModel\GeneralSetting\HomeLatestCollection\AdminHomeLatestCollection::where('product_id', $id)->count();

            if ($latestCollection) {
                $productExistMessage = Lang::get($this->langFile . '.product_latest_collection_exist');
                $deleteFlag = false;
            } else if ($wishlist) {
                $productExistMessage = Lang::get($this->langFile . '.product_wishlist_exist');
                $deleteFlag = false;
            }

            if ($record && $deleteFlag) {
                DB::beginTransaction();

                try {
                    $subDeleteFlag = true;
                    /*
                     * Delete Description
                     */
                    $subRecord = $record->getProductDescription("", true);
                    foreach ($subRecord as $sub) {
                        if (!$sub->delete()) {
                            $subDeleteFlag = false;
                        }
                    }
                    /*
                     * Delete Category
                     */
                    $subRecord = $record->getProductCategories();
                    foreach ($subRecord as $sub) {
                        if (!$sub->delete()) {
                            $subDeleteFlag = false;
                        }
                    }
                    /*
                     * Delete Images
                     */
                    $subRecord = $record->getAllImage();
                    foreach ($subRecord as $sub) {
                        if (!$sub->delete()) {
                            $subDeleteFlag = false;
                        }
                    }

                    /*
                     * While Delete Change Sorting
                     */
                    $sortingNumber = $this->getSortingNumber();
                    $sortingNumber = $sortingNumber - 1;
                    $currentOrder = $record->sorting;
                    $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($record->is_new_arrival == 1) {
                        $oldOrder = $record->new_arrival_soting;
                        AdminProduct::where('is_new_arrival', 1)
                            ->where('new_arrival_soting', '>', $oldOrder)
                            ->decrement('new_arrival_soting');
                    }

                    if ($subDeleteFlag && $record->delete()) {
                        DB::commit();
                        AdminProduct::reorderNewArrivals();
                        $status = 'success';
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    } else {
                        DB::rollback();
                        $status = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status = 'error';
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } else {
                $status = 'error';
                $message_text = $productExistMessage;
            }
        } else {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('admin.product.index', request()->query())->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function removeGalleryImage($id)
    {
        if ($id) {
            $record = AdminProductImage::find($id);

            if ($record) {
                DB::beginTransaction();

                try {
                    $product = AdminProduct::find($record->products_id);
                    if ($record->delete()) {
                        session()->flash("form_count", count($product->getGalleryImage()));

                        DB::commit();
                        $status = true;
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . '.gallery_image'), $message_text);
                    } else {
                        DB::rollback();
                        $status = false;
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . '.gallery_image')), $message_text);
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status = false;
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . '.gallery_image')), $message_text);
                }
            } else {
                $status = false;
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        } else {
            $status = false;
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return ['status' => $status, 'message' => $message_text];
    }
    
    
      public function removeAttrImage($id)
    {
        if ($id)
        {
            $record = AdminProductImage::find($id);

            if ($record)
            {
                DB::beginTransaction();

                try
                {
                    $product = AdminProduct::find($record->products_id);
                    if ($record->delete())
                    {
                        //session()->flash("form_count", count($product->getGalleryImage()));
                        DB::commit();
                        $status       = true;
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . '.image'), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status       = false;
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . '.image')), $message_text);
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    $status       = false;
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . '.image')), $message_text);
                }
            }
            else
            {
                $status       = false;
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = false;
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return ['status' => $status, 'message' => $message_text];
    }


    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminProduct::find($id);
        if ($singleRecord && $active == 0 || $active == 1) {
            DB::beginTransaction();

            try {
                $singleRecord->is_active = $active;
                $singleRecord->is_active_brand = $active;

                if ($singleRecord->save()) {
                    DB::commit();
                    $status = 'success';
                    $message_text = Lang::get($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    if ($active == 1) {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.active') . '"'), $message_text);
                    } else {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.in_active') . '"'), $message_text);
                    }
                } else {
                    DB::rollback();
                    $status = 'error';
                    $message_text = Lang::get($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e) {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } else {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }

        return redirect()->route('admin.product.index', request()->query())->with($status, $message_text);
    }

    public function changeNewArrival($id, $active)
    {
        $singleRecord = AdminProduct::find($id);
        if ($singleRecord && $active == 0 || $active == 1) {
            DB::beginTransaction();

            try {
                $singleRecord->is_new_arrival = $active;
               
                if($active == 1)
                {
                    
                    $singleRecord->new_arrival_soting = $this->getNewArrivalSortingNumber();
                     
                }
                else
                {
                    $oldOrder = $singleRecord->new_arrival_soting;
                    $singleRecord->new_arrival_soting = null;
                    if ($oldOrder) {
                        AdminProduct::where('is_new_arrival', 1)
                            ->where('new_arrival_soting', '>', $oldOrder)
                            ->decrement('new_arrival_soting');
                    }
                }
                if ($singleRecord->save()) {
                    DB::commit();
                    AdminProduct::reorderNewArrivals();
                    $status = 'success';
                    $message_text = Lang::get($this->langFile . '.na-status-change-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    if ($active == 1) {
                        $message_text = str_replace("#status#", Lang::get($this->langFile . '.added_in_new_arrival'), $message_text);
                    } else {
                        $message_text = str_replace("#status#", Lang::get($this->langFile . '.removed_from_new_arrival'), $message_text);
                    }
                } else {
                    DB::rollback();
                    $status = 'error';
                    $message_text = Lang::get($this->langFile . '.na-status-change-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e) {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.na-status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } else {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.product.index', request()->query())->with($status, $message_text);
    }

    /*
     * Bind add more image HTML
     */

    public function bindImageHtml(Request $request)
    {
        if (isset($request->count) && $request->count) {
            $count = $request->count;
            return view("admin.product.product.add_more_html", compact('count'));
        } else {
            return response()->json('error', 401);
        }
    }

    /*
     * Bind Size Quantity HTML
     */

    public function bindSizeQuantity(Request $request)
    {
        if (isset($request->category) && $request->category) {
            $record = [];
            if (isset($request->productId) && $request->productId) {
                $record = AdminProduct::find($request->productId);
            }
            $category = $request->category;
            $haveSizePriceVariant = AdminCategory::find($category);
            $haveSizePriceVariant = $haveSizePriceVariant ? $haveSizePriceVariant->have_price_variant : 0;
            $size = AdminSize::getSizeAdmin($category);
            $view = \View::make("admin.product.product.size_quantity", compact('category', 'size', 'haveSizePriceVariant', 'record'))->render();
            return ['view' => $view, 'haveSizePriceVariant' => $haveSizePriceVariant];
        } else {
            return response()->json('error', 401);
        }
    }

    /*
     * Image Upload with Resize
     */

    public function uploadImage($file, $generateThumb = true)
   {
        ini_set('max_execution_time', 300);
		set_time_limit(300);

		// Get filename without extension & clean it
		$filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
		$filename = preg_replace("/\s+/", '-', $filename);

		// Unique WebP filename
		$fileNameToStore = $filename . '_' . time() . '.webp';
		$filePath = config('filepath.product_image') . '/' . $fileNameToStore;

		// Convert image to WebP & Save
		$webpImage = Image::make($file)->encode('webp', 90);
		Storage::put($filePath, $webpImage->stream());

		$thumbFilePath = null;

		// Generate thumbnail if required
		if ($generateThumb) {
			$thumbFileName = $filename . '_thumb_' . time() . '.webp';
			$thumbFilePath = config('filepath.product_image_thumb') . '/' . $thumbFileName;

			$webpThumb = Image::make($file)
				->crop(config('custom.product_resize_image_dimensions_width'), config('custom.product_resize_image_dimensions_height'))
				->encode('webp', 80);

			Storage::put($thumbFilePath, $webpThumb->stream());
		}

		return [
			'path' => $filePath, // WebP Image Path
			'resize' => $thumbFilePath, // WebP Thumbnail Path
		];
	}

    /*
     * export data 
     */

    public function productDataExport(Request $request)
    {
        return Excel::download(new ProductExport($request), config('excel.product_data'));
    }

    /*
     * get Sub Category 
     */

    public function getSubCategory(Request $request)
    {
        $category = $request->categoryID;
      
        $subCategoryID = $request->oldSubCategory;
        $subCategoryID = json_decode($subCategoryID);
        $subCategory = new AdminCategory();
        $subCategory = $subCategory->getChildFront($category);
        return view("admin.product.product.sub-category", compact('subCategory', 'subCategoryID'));
    }

    public function stock(Request $request, $id)
    {
        $product = AdminProduct::find($id);
        $message_text = __('admin.record_not_found');
        if ($product) {
            $checkProductHasOrders = \App\UserModel\OrderDetail::where('product_id', $id)->get();
            

            $canEditVarient = false;

            if ((int) $product->have_price_variant == 0) {
                #Check this Product order status
                if (count($checkProductHasOrders) == 0) {
                    $canEditVarient = true;
                } else {
                    $productOrderID = $checkProductHasOrders->pluck('order_id');
                    if (count($productOrderID)) {
                        $productOrderID = \App\AdminModel\Order\WebOrder::whereIn('id', $productOrderID)->whereIn('order_status', [1, 2, 3, 7])->count();
                        if ($productOrderID == 0) {
                            $canEditVarient = true;
                        }
                    }
                }
            } else {
                $canEditVarient = true;
            }

            if ($canEditVarient) {
                $selectedColor = false;
                $selectedSizes = false;

                $currentAttributes = AdminProductAttribute::where('products_id', $product->id)->where('attr_remove_by_admin', 0)->get();
                //dd($product);
                if ($currentAttributes) {
                    if ($product->have_color) {
                        $selectedColor = $currentAttributes->pluck('color_id');
                    }
                    if ($product->have_size) {
                        $selectedSizes = $currentAttributes->pluck('size_id');
                    }
                }

                //dd($selectedSizes);

                $color = AdminColor::getData(1, $selectedColor);
                
                 //$subCategory = AdminProductCategory::where('products_id', $id)->pluck('categories_id')->toArray();
                //$size = AdminSize::getSizeAdmin($subCategory, $selectedSizes);
                $size = AdminSize::getSizeAdmin($product->category_id, $selectedSizes);
                if (!count($currentAttributes)) {
                    $selectedSizes = $size->pluck('id');
                }
                return view("admin.product.product.stock", compact('product', 'color', 'size', 'selectedColor', 'selectedSizes'));
            } else {
                $flag = false;
                $message_text = __($this->langFile . '.cannot_edit_varient');
            }
        }
        return redirect()->route('admin.product.index')->with('error', $message_text);
    }

    public function getSizeColorHTML(Request $request)
    {
        $sizeId = $request->sizeId;
        $colorId = $request->colorId;
        $productId = $request->productId;
        $onlyColorId = $request->onlyColorId;
        $onlySizeId = $request->onlySizeId;
        $sizes = false;
        $colors = false;
        $product = false;
        $selectedColor = false;
        $selectedSizes = false;

        if ($productId) {
            $product = AdminProduct::find($productId);

            if ($product) {
                $currentAttributes = AdminProductAttribute::where('products_id', $product->id)->where('attr_remove_by_admin', 0)->get();
                if ($currentAttributes) {
                    if ($product->have_color) {
                        $selectedColor = $currentAttributes->pluck('color_id');
                    }
                    if ($product->have_size) {
                        $selectedSizes = $currentAttributes->pluck('size_id');
                    }
                }
            }
        }
        if ($sizeId) {
            $sizes = AdminSize::whereIn('id', $sizeId)->orderBy('sorting', 'asc');
            if ($selectedSizes) {
                $sizes->orWhereIn('id', $selectedSizes);
                //$sizes->orWhereIn('id', $selectedSizes);#Commented on 02-Aug-2022 as per discussion with fakhry
            }
            $sizes = $sizes->get();
           
        }
        if ($colorId) {
            $colors = AdminColor::whereIn('id', $colorId)->orderBy('sorting', 'asc');
            if ($selectedColor) {
                $colors->orWhereIn('id', $selectedColor);
            }
            $colors = $colors->get();
        }
        $view = \View::make("admin.product.product.color_size_html", compact('sizeId', 'colorId', 'sizes', 'colors', 'product', 'onlyColorId', 'onlySizeId'))->render();
        return $view;
    }
public function saveSizeColor(Request $request)
    {
        $status = 'error';
        DB::beginTransaction();
        try {
            $product = AdminProduct::find($request->id);

            $checkProductHasOrders = \App\UserModel\OrderDetail::where('product_id', $request->id)->get();

            $canEditVarient = false;

            if ((int) $product->have_price_variant == 0) {
                #Check this Product order status
                if (count($checkProductHasOrders) == 0) {
                    $canEditVarient = true;
                } else {
                    $productOrderID = $checkProductHasOrders->pluck('order_id');
                    if (count($productOrderID)) {
                        $productOrderID = \App\AdminModel\Order\WebOrder::whereIn('id', $productOrderID)->whereIn('order_status', [1, 2, 3, 7])->count();
                        if ($productOrderID == 0) {
                            $canEditVarient = true;
                        }
                    }
                }
            } else {
                $canEditVarient = true;
            }

            if ($canEditVarient) {
                $flag = true;
                $minPrice = "";
                $minPriceNormal = 0;
                $minSizePrice = "";
                $minSizeSalePrice = "";
                $minSizeSalePriceNull = 0;
                $sizeLoop = 0;
                $sizeLoopNormal = 0;
                $productId = $request->id;
                $productQuantity = 0;

                $allColorIds = $request->input('all_color_ids', []);
                $allSizeIds = $request->input('all_size_ids', []);

                $haveColor = !empty($allColorIds);
                $haveSize = !empty($allSizeIds);

                // Global cleanup: Deactivate attributes that are no longer in the overall selected lists
                if ($haveColor) {
                    AdminProductAttribute::where('products_id', $productId)
                        ->whereNotIn('color_id', $allColorIds)
                        ->update(['is_active' => 0, 'attr_remove_by_admin' => 1]);
                }
                if ($haveSize) {
                    AdminProductAttribute::where('products_id', $productId)
                        ->whereNotIn('size_id', $allSizeIds)
                        ->update(['is_active' => 0, 'attr_remove_by_admin' => 1]);
                }

                if (isset($request->color_id) && $request->color_id) {
                    $targetColorId = $request->color_id[0];

                    if (isset($request->is_color_only) && $request->is_color_only == 1) {
                        AdminProductAttribute::where([
                            'products_id' => $productId,
                            'color_id' => $targetColorId,
                        ])->whereNull('size_id')->update(['is_active' => 0, 'attr_remove_by_admin' => 1]);
                    } else {
                        AdminProductAttribute::where([
                            'products_id' => $productId,
                            'color_id' => $targetColorId,
                        ])->whereNotNull('size_id')->update(['is_active' => 0, 'attr_remove_by_admin' => 1]);
                    }

                    foreach ($request->color_id as $selectedColor) {
                        if (isset($request->is_color_only) && $request->is_color_only == 1) {
                            #Only Color Available
                            $styleName = "style_" . $selectedColor;
                            $skuName = "sku_" . $selectedColor;
                            $barcode = "barcode_" . $selectedColor;
                            $weightName = "weight_" . $selectedColor;
                            $lengthName = "length_" . $selectedColor;
                            $widthName = "width_" . $selectedColor;
                            $heightName = "height_" . $selectedColor;
                            $dimensionUnitName = "dimension_unit_" . $selectedColor;
                            $weightUnitName = "weight_unit_" . $selectedColor;
                            $hsCodeName = "hs_code_" . $selectedColor;
							$sortingName = "sorting_" . $selectedColor;
							$sortingName = "sorting_" . $selectedColor;
                            $sizeName = "size_" . $selectedColor;
                            $sizePrice = "price_" . $selectedColor;
                            $sizeDiscountIn = "discount_in";
                            $sizeDiscount = "discount_" . $selectedColor;
                            $sizeDiscountPrice = "discount_price_" . $selectedColor;
                            if (!isset($request->$sizeName) || !$request->$sizeName) {
                                $request->$sizeName = 0;
                            }
                            if (isset($request->$sizeName)) {
                                $productAttribute = AdminProductAttribute::where([
                                    'products_id' => $productId,
                                    'color_id' => $selectedColor,
                                    'size_id' => null
                                ])->first();

                                if (!$productAttribute) {
                                    $productAttribute = new AdminProductAttribute();
                                }

                                $productAttribute->products_id = $productId;
                                $productAttribute->color_id = $selectedColor;
                                $productAttribute->size_id = null;
                                $productAttribute->quantity = $request->$sizeName;
                                $productAttribute->weight = $request->$weightName;
                                $productAttribute->product_length = $request->$lengthName;
                                $productAttribute->product_width = $request->$widthName;
                                $productAttribute->product_height = $request->$heightName;
                                $productAttribute->dimension_unit = $request->$dimensionUnitName ?? 'cm';
                                $productAttribute->weight_unit = $request->$weightUnitName ?? 'kg';
								$productAttribute->sorting = $request->$sortingName;
                                $productAttribute->style = $request->$styleName;
                                $productAttribute->sku = $request->$skuName;
                                $productAttribute->barcode = $request->$barcode;
                                $productAttribute->hs_code = $request->$hsCodeName;
                                $productAttribute->product_regular_price = isset($request->$sizePrice) ? $request->$sizePrice : 0;
                                $productAttribute->product_sale_price = isset($request->$sizeDiscountPrice) ? $request->$sizeDiscountPrice : (isset($request->$sizePrice) ? $request->$sizePrice : null);
                                $productAttribute->is_product_sale_price_null = isset($request->$sizeDiscountPrice) ? 0 : 1;
                                $productAttribute->discount_in = isset($request->$sizeDiscountIn) ? $request->$sizeDiscountIn : null;
                                $productAttribute->discount = isset($request->$sizeDiscount) ? $request->$sizeDiscount : null;
                                $productAttribute->is_active = 1;
                                $productAttribute->attr_remove_by_admin       = 0;



                                $productQuantity += $request->$sizeName;

                                if ($productAttribute->product_regular_price > 0 && isset($request->$sizeDiscountPrice) && $request->$sizeDiscountPrice) {
                                    if ($sizeLoop == 0) {
                                        $minPrice = $productAttribute->product_regular_price;
                                        if ($productAttribute->product_sale_price) {
                                            $minPrice = $productAttribute->product_sale_price;
                                        }

                                        $minSizePrice = $productAttribute->product_regular_price;
                                        $minSizeSalePrice = $productAttribute->product_sale_price;
                                        $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                    } else {
                                        if ($productAttribute->product_regular_price < $minPrice || $productAttribute->product_sale_price < $minPrice) {
                                            $minPrice = $productAttribute->product_regular_price;
                                            if ($productAttribute->product_sale_price) {
                                                $minPrice = $productAttribute->product_sale_price;
                                            }

                                            $minSizePrice = $productAttribute->product_regular_price;
                                            $minSizeSalePrice = $productAttribute->product_sale_price;
                                            $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                        }
                                    }
                                    $sizeLoop++;
                                } else {
                                    if ($sizeLoopNormal == 0 && $productAttribute->product_regular_price > 0) {
                                        $minPriceNormal = $productAttribute->product_regular_price;
                                    } else {
                                        //                                        if ($sizeLoopNormal == 1)
                                        //                                        {
                                        //                                            dd($minPriceNormal, $productAttribute->product_regular_price);
                                        //                                        }
                                        if (!$minPriceNormal && $productAttribute->product_regular_price > 0) {
                                            $minPriceNormal = $productAttribute->product_regular_price;
                                        } else if (($productAttribute->product_regular_price < $minPriceNormal || $productAttribute->product_sale_price < $minPriceNormal) && $productAttribute->product_regular_price > 0) {
                                            $minPriceNormal = $productAttribute->product_regular_price;
                                            if ($productAttribute->product_sale_price) {
                                                $minPriceNormal = $productAttribute->product_sale_price;
                                            }
                                        }
                                    }
                                    $sizeLoopNormal++;
                                }

                                if (!$productAttribute->save()) {
                                    $flag = false;
                                }
                            }
                        } else {
                            #Color-Size both available
                            $haveSize = true;
                            foreach ($request->size_id as $inputSize) {
                                $styleName = "style_" . $inputSize . "_" . $selectedColor;
                                $skuName = "sku_" . $inputSize . "_" . $selectedColor;
                                $barcodeName = "barcode_" . $inputSize . "_" . $selectedColor;
                                $weightName = "weight_" . $inputSize . "_" . $selectedColor;
								$sortingName = "sorting_" . $inputSize . "_" . $selectedColor;
                                $lengthName = "length_" . $inputSize . "_" . $selectedColor;
                                $widthName = "width_" . $inputSize . "_" . $selectedColor;
                                $heightName = "height_" . $inputSize . "_" . $selectedColor;
                                $dimensionUnitName = "dimension_unit_" . $inputSize . "_" . $selectedColor;
                                $weightUnitName = "weight_unit_" . $inputSize . "_" . $selectedColor;
                                $hsCodeName = "hs_code_" . $inputSize . "_" . $selectedColor;
							   $sizeName = "size_" . $inputSize . "_" . $selectedColor;
                                $sizePrice = "price_" . $inputSize . "_" . $selectedColor;
                                $sizeDiscountIn = "discount_in";
                                $sizeDiscount = "discount_" . $inputSize . "_" . $selectedColor;
                                $sizeDiscountPrice = "discount_price_" . $inputSize . "_" . $selectedColor;
                                if (!isset($request->$sizeName) || !$request->$sizeName) {
                                    $request->$sizeName = 0;
                                }

                                if (isset($request->$sizeName)) {
                                    #Size Images with Color
                                    for ($fileCount = 1; $fileCount <= 3; $fileCount++) {
                                        $fileName = "product_size_image_" . $fileCount;


                                        if (isset($request->$fileName)) {
                                            foreach ($request->$fileName as $fileKey1 => $fileVal1) {
                                                foreach ($fileVal1 as $fileKey => $fileVal) {
                                                    //if ($fileVal && $fileKey == $inputSize)
                                                    {
                                                        $file = $fileVal;
                                                        $path = $this->uploadImage($file, false);
                                                        if ($path) {
                                                            /*
                                                             * Main Image Store
                                                             */
															 
															 
															 AdminProductImage::where('products_id', $productId)->where('number', $fileCount)->where('color_id', $fileKey1)->where('size_id', $fileKey)->delete();

                                                            $productImages = AdminProductImage::where('products_id', $productId)->where('number', $fileCount)->where('color_id', $fileKey1)->where('size_id', $fileKey)->get();
                                                            if (isset($productImages[$fileCount - 1])) {
                                                                $productImages = $productImages[$fileCount - 1];
                                                            } else {
                                                                $productImages = new AdminProductImage();
                                                            }

                                                            $productImages->number          = $fileCount;

                                                            $productImages->products_id = $productId;
                                                            $productImages->color_id = $fileKey1;
                                                            $productImages->size_id = $fileKey;
                                                            $productImages->image_url = $path['path'];
                                                            $productImages->image_thumb_url = $path['path'];
                                                            //$productImages->image_cart_url  = $path['cartImage'];
                                                            $productImages->is_default = false;


                                                            if (!$productImages->save()) {
                                                                $flag = false;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $productAttribute = AdminProductAttribute::where([
                                        'products_id' => $productId,
                                        'size_id' => $inputSize,
                                        'color_id' => $selectedColor
                                    ])->first();

                                    if (!$productAttribute) {
                                        $productAttribute = new AdminProductAttribute();
                                    }

                                    $productAttribute->products_id = $productId;
                                    $productAttribute->color_id = $selectedColor;
                                    $productAttribute->size_id = $inputSize;
                                    $productAttribute->quantity = $request->$sizeName;
                                    $productAttribute->weight = $request->$weightName;
                                    $productAttribute->product_length = $request->$lengthName;
                                    $productAttribute->product_width = $request->$widthName;
                                    $productAttribute->product_height = $request->$heightName;
                                    $productAttribute->dimension_unit = $request->$dimensionUnitName ?? 'cm';
                                    $productAttribute->weight_unit = $request->$weightUnitName ?? 'kg';
									$productAttribute->sorting = $request->$sortingName;
                                    $productAttribute->style = $request->$styleName;
                                    $productAttribute->sku = $request->$skuName;
                                    $productAttribute->barcode = $request->$barcodeName;
                                    $productAttribute->hs_code = $request->$hsCodeName;
                                    $productAttribute->product_regular_price = isset($request->$sizePrice) ? $request->$sizePrice : 0;
                                    $productAttribute->product_sale_price = isset($request->$sizeDiscountPrice) ? $request->$sizeDiscountPrice : (isset($request->$sizePrice) ? $request->$sizePrice : null);
                                    $productAttribute->is_product_sale_price_null = isset($request->$sizeDiscountPrice) ? 0 : 1;
                                    $productAttribute->discount_in = isset($request->$sizeDiscountIn) ? $request->$sizeDiscountIn : null;
                                    $productAttribute->discount = isset($request->$sizeDiscount) ? $request->$sizeDiscount : null;
                                    $productAttribute->is_active = 1;
                                     $productAttribute->attr_remove_by_admin       = 0;

                                    $productQuantity += $request->$sizeName;

                                    if ($productAttribute->product_regular_price > 0 && isset($request->$sizeDiscountPrice) && $request->$sizeDiscountPrice) {
                                        if ($sizeLoop == 0) {
                                            $minPrice = $productAttribute->product_regular_price;
                                            if ($productAttribute->product_sale_price) {
                                                $minPrice = $productAttribute->product_sale_price;
                                            }

                                            $minSizePrice = $productAttribute->product_regular_price;
                                            $minSizeSalePrice = $productAttribute->product_sale_price;
                                            $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                        } else {

                                            if ($productAttribute->product_regular_price < $minPrice || $productAttribute->product_sale_price < $minPrice) {
                                                $minPrice = $productAttribute->product_regular_price;
                                                if ($productAttribute->product_sale_price) {
                                                    $minPrice = $productAttribute->product_sale_price;
                                                }

                                                $minSizePrice = $productAttribute->product_regular_price;
                                                $minSizeSalePrice = $productAttribute->product_sale_price;
                                                $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                            }
                                        }
                                        $sizeLoop++;
                                    } else {
                                        if (!$minPriceNormal && $productAttribute->product_regular_price > 0) {
                                            $minPriceNormal = $productAttribute->product_regular_price;
                                        }
                                        if ($sizeLoopNormal == 0 && $productAttribute->product_regular_price > 0) {
                                            $minPriceNormal = $productAttribute->product_regular_price;
                                        } else {
                                            if (($productAttribute->product_regular_price < $minPriceNormal || $productAttribute->product_sale_price < $minPriceNormal) && $productAttribute->product_regular_price > 0) {
                                                $minPriceNormal = $productAttribute->product_regular_price;
                                                if ($productAttribute->product_sale_price) {
                                                    $minPriceNormal = $productAttribute->product_sale_price;
                                                }
                                            }
                                        }
                                        $sizeLoopNormal++;
                                    }

                                    if (!$productAttribute->save()) {
                                        $flag = false;
                                    }
                                }
                            }
                        }
                    }

                    #Colorwise Images
                    for ($fileCount = 1; $fileCount <= 3; $fileCount++) {
                        $fileName = "product_image_" . $fileCount;

                        if (isset($request->$fileName)) {

                            foreach ($request->$fileName as $fileKey => $fileVal) {

                                if ($fileVal) {
                                    //                            $file = $request->file('product_default_image');
                                    $file = $fileVal;
                                    $path = $this->uploadImage($file, false);
                                    if ($path) {
                                        /*
                                         * Main Image Store
                                         */
										 
										 AdminProductImage::where('number', $fileCount)->where('products_id', $productId)->where('color_id', $fileKey)->whereNull('size_id')->delete();

                                        $productImages = AdminProductImage::where('number', $fileCount)->where('products_id', $productId)->where('color_id', $fileKey)->whereNull('size_id')->first();


                                        if (isset($productImages[$fileCount - 1])) {
                                            $productImages = $productImages[$fileCount - 1];
                                        } else {
                                            $productImages = new AdminProductImage();
                                        }
                                        $productImages->number          = $fileCount;
                                        $productImages->products_id = $productId;
                                        $productImages->color_id = $fileKey;
                                        $productImages->image_url = $path['path'];
                                        $productImages->image_thumb_url = $path['path'];
                                        //$productImages->image_cart_url  = $path['cartImage'];
                                        $productImages->is_default = false;
                                        if (!$productImages->save()) {
                                            $flag = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $targetSizeId = $request->size_id[0];
                    AdminProductAttribute::where([
                        'products_id' => $productId,
                        'size_id' => $targetSizeId,
                    ])->whereNull('color_id')->update(['is_active' => 0, 'attr_remove_by_admin' => 1]);

                    #Only Size Available
                    $haveSize = true;
                    foreach ($request->size_id as $inputSize) {
                        $styleName = "style_" . $inputSize;
                        $skuName = "sku_" . $inputSize;
                        $barcodeName = "barcode_" . $inputSize;
                        $weightName = "weight_" . $inputSize;
                        $sortingName = "sorting_" . $inputSize;
                        $lengthName = "length_" . $inputSize;
                        $widthName = "width_" . $inputSize;
                        $heightName = "height_" . $inputSize;
                        $dimensionUnitName = "dimension_unit_" . $inputSize;
                        $weightUnitName = "weight_unit_" . $inputSize;
                        $hsCodeName = "hs_code_" . $inputSize;
						$sizeName = "size_" . $inputSize;
                        $sizePrice = "price_" . $inputSize;
                        $sizeDiscountIn = "discount_in";
                        $sizeDiscount = "discount_" . $inputSize;
                        $sizeDiscountPrice = "discount_price_" . $inputSize;
                        if (!isset($request->$sizeName) || !$request->$sizeName) {
                            $request->$sizeName = 0;
                        }

                        if (isset($request->$sizeName)) {
                            #Size Images
                            for ($fileCount = 1; $fileCount <= 3; $fileCount++) {
                                $fileName = "product_size_image_" . $fileCount;

                                if (isset($request->$fileName)) {
                                    foreach ($request->$fileName as $fileKey => $fileVal) {
                                        if ($fileVal && $fileKey == $inputSize) {
                                            //                            $file = $request->file('product_default_image');
                                            $file = $fileVal;
                                            $path = $this->uploadImage($file, false);
                                            if ($path) {
                                                /*
                                                 * Main Image Store
                                                 */
                                               

											  
											  AdminProductImage::where('products_id', $productId)->where('number', $fileCount)->whereNull('color_id')->where('size_id', $fileKey)->delete();											  
											  $productImages = AdminProductImage::where('products_id', $productId)->where('number', $fileCount)->whereNull('color_id')->where('size_id', $fileKey)->get();
                                                if (isset($productImages[$fileCount - 1])) {
                                                    $productImages = $productImages[$fileCount - 1];
                                                } else {
                                                    $productImages = new AdminProductImage();
                                                }

                                                $productImages->number          = $fileCount;
                                                $productImages->products_id = $productId;
                                                $productImages->color_id = null;
                                                $productImages->size_id = $fileKey;
                                                $productImages->image_url = $path['path'];
                                                $productImages->image_thumb_url = $path['path'];
                                                //$productImages->image_cart_url  = $path['cartImage'];
                                                $productImages->is_default = false;
                                                if (!$productImages->save()) {
                                                    $flag = false;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $productAttribute = AdminProductAttribute::where([
                                'products_id' => $productId,
                                'size_id' => $inputSize,
                                'color_id' => null
                            ])->first();

                            if (!$productAttribute) {
                                $productAttribute = new AdminProductAttribute();
                            }

                            $productAttribute->products_id = $productId;
                            $productAttribute->color_id = null;
                            $productAttribute->size_id = $inputSize;
                            $productAttribute->quantity = $request->$sizeName;
                            $productAttribute->weight = $request->$weightName;
                            $productAttribute->product_length = $request->$lengthName;
                            $productAttribute->product_width = $request->$widthName;
                            $productAttribute->product_height = $request->$heightName;
                            $productAttribute->dimension_unit = $request->$dimensionUnitName ?? 'cm';
                            $productAttribute->weight_unit = $request->$weightUnitName ?? 'kg';
							$productAttribute->sorting = $request->$sortingName;
                            $productAttribute->style = $request->$styleName;
                            $productAttribute->sku = $request->$skuName;
                            $productAttribute->barcode = $request->$barcodeName;
                            $productAttribute->hs_code = $request->$hsCodeName;
                            $productAttribute->product_regular_price = isset($request->$sizePrice) ? $request->$sizePrice : 0;
                            $productAttribute->product_sale_price = isset($request->$sizeDiscountPrice) ? $request->$sizeDiscountPrice : (isset($request->$sizePrice) ? $request->$sizePrice : null);
                            $productAttribute->is_product_sale_price_null = isset($request->$sizeDiscountPrice) ? 0 : 1;
                            $productAttribute->discount_in = isset($request->$sizeDiscountIn) ? $request->$sizeDiscountIn : null;
                            $productAttribute->discount = isset($request->$sizeDiscount) ? $request->$sizeDiscount : null;
                            $productAttribute->is_active = 1;
                            $productAttribute->attr_remove_by_admin       = 0;
                            $productQuantity += $request->$sizeName;

                            if ($productAttribute->product_regular_price > 0 && isset($request->$sizeDiscountPrice) && $request->$sizeDiscountPrice) {
                                if ($sizeLoop == 0) {
                                    $minPrice = $productAttribute->product_regular_price;
                                    if ($productAttribute->product_sale_price) {
                                        $minPrice = $productAttribute->product_sale_price;
                                    }

                                    $minSizePrice = $productAttribute->product_regular_price;
                                    $minSizeSalePrice = $productAttribute->product_sale_price;
                                    $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                } else {
                                    if ($productAttribute->product_regular_price < $minPrice || $productAttribute->product_sale_price < $minPrice) {
                                        $minPrice = $productAttribute->product_regular_price;
                                        if ($productAttribute->product_sale_price) {
                                            $minPrice = $productAttribute->product_sale_price;
                                        }

                                        $minSizePrice = $productAttribute->product_regular_price;
                                        $minSizeSalePrice = $productAttribute->product_sale_price;
                                        $minSizeSalePriceNull = $productAttribute->is_product_sale_price_null;
                                    }
                                }
                                $sizeLoop++;
                            } else {
                                if ($sizeLoopNormal == 0 && $productAttribute->product_regular_price > 0) {
                                    $minPriceNormal = $productAttribute->product_regular_price;
                                } else {
                                    if (!$minPriceNormal && $productAttribute->product_regular_price > 0) {
                                        $minPriceNormal = $productAttribute->product_regular_price;
                                    }
                                    if (($productAttribute->product_regular_price < $minPriceNormal || $productAttribute->product_sale_price < $minPriceNormal) && $productAttribute->product_regular_price > 0) {
                                        $minPriceNormal = $productAttribute->product_regular_price;
                                        if ($productAttribute->product_sale_price) {
                                            $minPriceNormal = $productAttribute->product_sale_price;
                                        }
                                    }
                                }
                                $sizeLoopNormal++;
                            }

                            if (!$productAttribute->save()) {
                                $flag = false;
                            }
                        }
                    }
                }


                // Calculate global metrics from all active attributes of this product in database
                $activeAttributes = AdminProductAttribute::where('products_id', $productId)
                    ->where('is_active', 1)
                    ->where('attr_remove_by_admin', 0)
                    ->get();

                $productQuantity = $activeAttributes->sum('quantity');

                $minPrice = "";
                $minPriceNormal = 0;
                $minSizePrice = "";
                $minSizeSalePrice = "";
                $minSizeSalePriceNull = 0;
                $sizeLoop = 0;
                $sizeLoopNormal = 0;

                foreach ($activeAttributes as $attr) {
                    $regPrice = $attr->getRawOriginal('product_regular_price');
                    $salePrice = $attr->getRawOriginal('product_sale_price');
                    $isSalePriceNull = $attr->getRawOriginal('is_product_sale_price_null');

                    if ($regPrice > 0 && !$isSalePriceNull && $salePrice) {
                        $currentEffective = $salePrice;
                        if ($sizeLoop == 0) {
                            $minPrice = $currentEffective;
                            $minSizePrice = $regPrice;
                            $minSizeSalePrice = $salePrice;
                            $minSizeSalePriceNull = $isSalePriceNull;
                        } else {
                            if ($currentEffective < $minPrice) {
                                $minPrice = $currentEffective;
                                $minSizePrice = $regPrice;
                                $minSizeSalePrice = $salePrice;
                                $minSizeSalePriceNull = $isSalePriceNull;
                            }
                        }
                        $sizeLoop++;
                    } else {
                        if ($regPrice > 0) {
                            $currentEffective = $salePrice ?: $regPrice;
                            if ($sizeLoopNormal == 0) {
                                $minPriceNormal = $regPrice;
                            } else {
                                if (!$minPriceNormal) {
                                    $minPriceNormal = $regPrice;
                                } else if ($currentEffective < $minPriceNormal) {
                                    $minPriceNormal = $regPrice;
                                }
                            }
                            $sizeLoopNormal++;
                        }
                    }
                }

                if ($minSizePrice) {
                    $currentProduct = AdminProduct::find($productId);
                    $currentProduct->product_regular_price = $minSizePrice ? $minSizePrice : 0;
                    $currentProduct->product_sale_price = $minSizeSalePrice;
                    $currentProduct->is_product_sale_price_null = $minSizeSalePriceNull;
                    $currentProduct->have_price_variant = 1;
                    $currentProduct->product_quantity = $productQuantity;
                    $currentProduct->have_size = $haveSize;
                    $currentProduct->have_color = $haveColor;

                    if (!$currentProduct->save()) {
                        $flag = false;
                    }
                }
                //                else if ($minPriceNormal)
                else {


                    $currentProduct = AdminProduct::find($productId);
                    $currentProduct->product_regular_price = $minPriceNormal ? $minPriceNormal : 0;
                    $currentProduct->product_sale_price = $minPriceNormal;
                    $currentProduct->product_quantity = $productQuantity;
                    $currentProduct->have_size = $haveSize;
                    $currentProduct->have_color = $haveColor;
                    $currentProduct->have_price_variant = 1;

                    $currentProduct->is_product_sale_price_null = 1;

                    if (!$currentProduct->save()) {
                        $flag = false;
                    }
                }


                if ($flag) {
                    DB::commit();
                    $status = 'success';
                    $message_text = __($this->langFile . '.stock-add-success');
                } else {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.error');
                }
            } else {
                $flag = false;
                $message_text = __($this->langFile . '.cannot_edit_varient');
            }
        } catch (Throwable $e) {
            DB::rollback();
            $status = 'error';
            $message_text = __($this->langFile . '.error');
        }


        if ($request->ajax()) {
            return response()->json([
                'status' => $status,
                'message' => $message_text
            ]);
        }

        return redirect()->route('admin.product.stock', ['id' => $request->id])->with($status, $message_text);
    }
	
	public function changeSorting($currentOrder, $newOrder, $productId)
	{
		if ($currentOrder > $newOrder) {
			AdminProduct::where('is_new_arrival', 1)
				->where('new_arrival_soting', '>=', $newOrder)
				->where('new_arrival_soting', '<', $currentOrder)
				->update(['new_arrival_soting' => DB::raw('new_arrival_soting + 1')]);
        } else if ($currentOrder < $newOrder) {
			AdminProduct::where('is_new_arrival', 1)
				->where('new_arrival_soting', '>', $currentOrder)
				->where('new_arrival_soting', '<=', $newOrder)
				->update(['new_arrival_soting' => DB::raw('new_arrival_soting - 1')]);
        }
		AdminProduct::where('id', $productId)->where('is_new_arrival', 1)
            ->update(['new_arrival_soting' => $newOrder]);

		AdminProduct::reorderNewArrivals();
	}
	
}

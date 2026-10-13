<?php

namespace App\Http\Controllers\Admin\Product\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\AdminModel\AdminLanguage;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Product\AdminCategoryDescription;
/*
 * Other
 */
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoryExport;

class CategoryController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.category';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderSortingColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $category   = new AdminCategory();
        $parentID   = 0;
        $categories = $category->getAllData($searchText, $sortKey, $sortVal, $parentID, $pageLength);
        return view('admin.product.category.index', compact('categories', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminCategory();
        $sortingNumber = $model->getSortingNumber();
        if (!$sortingNumber)
        {
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
        if ($currentOrder > $newOrder)
        {
            $record = AdminCategory::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->where('parent_id', 0)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminCategory::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->where('parent_id', 0)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting - 1;
                $record->save();
            }
        }
    }

    public function create()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
//            $addLimit           = config('custom.add_category_limit');
            $currentRecordCount = AdminCategory::where('parent_id', 0)->count();
//            if ($addLimit > $currentRecordCount)
//            {
            $sortingNumber      = $this->getSortingNumber();
            $categoryList       = AdminCategory::getCategories(1);
            return view("admin.product.category.add", compact('languageList', 'categoryList', 'sortingNumber'));
//            }
//            else
//            {
//                return redirect()->route('admin.product.category.index')->with('error', Lang::get($this->langFile . '.category-add-limit'));
//            }
        }
        else
        {
            return redirect()->route('admin.product.category.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList = Helper::getLanguageList();

        /*
         * Validations
         */
        $sortingNumber     = $this->getSortingNumber();
        $validationRule    = [];
        $validationMessage = [];

        $categoryName = "";
        $languageID   = "";
        foreach ($languageList as $language)
        {
            $message_text                                                      = Lang::get($this->langFile . '.error-category-req-category');
            $validationRule['category_name_' . $language->id]                  = 'required';
            $validationMessage['category_name_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $categoryName                                                      = 'category_name_' . $language->id;
            $languageID                                                        = $language->id;
        }
        $validationRule['parent_id']             = 'required';
        $validationRule['sorting']               = "required|numeric|gte:1|max:$sortingNumber";
        /*
         * 

          $validationRule['min_order_quantity']   = "required|numeric|min:1|lte:max_order_quantity";
          $validationRule['max_order_quantity']   = "required|numeric|min:1|gte:min_order_quantity";
          $validationRule['category_image']       = "required|image|mimes:jpg,jpeg,png|dimensions:width=" . config('custom.category_image_dimensions_width') . ",height=" . config('custom.category_image_dimensions_height');
          $validationRule['category_icon']        = "required|image|mimes:jpg,jpeg,png|dimensions:width=" . config('custom.category_icon_dimensions_width') . ",height=" . config('custom.category_icon_dimensions_height');
          $validationRule['show_customized_form'] = "nullable";
         */
        $validationMessage['parent_id.required'] = Lang::get($this->langFile . '.error-category-req-parent-id');
        $message_text_sorting                    = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting                    = str_replace("#number#", $sortingNumber, $message_text_sorting);
        $validationMessage['sorting.required']   = Lang::get($this->langFile . '.error-req-sorting');
        $validationMessage['sorting.numeric']    = Lang::get($this->langFile . '.error-numeric-sorting');
        $validationMessage['sorting.gte']        = Lang::get($this->langFile . '.error-min-sorting');
        $validationMessage['sorting.min']        = Lang::get($this->langFile . '.error-min-sorting');
        $validationMessage['sorting.max']        = $message_text_sorting;
        /*
         * 
          $validationMessage['min_order_quantity.required'] = Lang::get($this->langFile . '.error-category-req-min_order_quantity');
          $validationMessage['min_order_quantity.numeric']  = Lang::get($this->langFile . '.error-category-numeric-min_order_quantity');
          $validationMessage['min_order_quantity.min']      = Lang::get($this->langFile . '.error-category-min-min_order_quantity');
          $validationMessage['min_order_quantity.lte']      = Lang::get($this->langFile . '.error-category-lte-min_order_quantity');
          $validationMessage['max_order_quantity.required'] = Lang::get($this->langFile . '.error-category-req-max_order_quantity');
          $validationMessage['max_order_quantity.numeric']  = Lang::get($this->langFile . '.error-category-numeric-max_order_quantity');
          $validationMessage['max_order_quantity.min']      = Lang::get($this->langFile . '.error-category-min-max_order_quantity');
          $validationMessage['max_order_quantity.gte']      = Lang::get($this->langFile . '.error-category-gte-max_order_quantity');
          $validationMessage['category_image.required']     = Lang::get($this->langFile . '.error-category-image-req-photo');
          $validationMessage['category_image.image']        = Lang::get($this->langFile . '.error-category-image-image-photo');
          $validationMessage['category_image.mimes']        = Lang::get($this->langFile . '.error-category-image-mimes-photo');
          $validationMessage['category_image.dimensions']   = Lang::get($this->langFile . '.error-category-image-dimensions-photo');
          $validationMessage['category_icon.required']      = Lang::get($this->langFile . '.error-category-icon-req-photo');
          $validationMessage['category_icon.image']         = Lang::get($this->langFile . '.error-category-icon-image-photo');
          $validationMessage['category_icon.mimes']         = Lang::get($this->langFile . '.error-category-icon-mimes-photo');
          $validationMessage['category_icon.dimensions']    = Lang::get($this->langFile . '.error-category-icon-dimensions-photo');
         */
        $parentID                                = AdminCategory::where('parent_id', '=', 0)->pluck('id');
        $checkUnique                             = AdminCategoryDescription::where(['categories_name' => $request->$categoryName, 'language_id' => $languageID])->whereIn('categories_id', $parentID)->count();
        if ($checkUnique > 0)
        {
            return redirect()->back()->withInput($request->input)->withErrors([$categoryName => Lang::get($this->langFile . '.error-category-unique-category')]);
        }

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            $flag                        = true;
            $model                       = new AdminCategory();
            $model->parent_id            = $request->parent_id; //Main Category
            $model->sorting              = $request->sorting;
            $model->show_customized_form = isset($request->show_customized_form) ? 1 : 0;
            $model->have_price_variant   = isset($request->have_price_variant) ? 1 : 0;
            //$model->min_order_quantity   = $request->min_order_quantity;
            //$model->max_order_quantity   = $request->max_order_quantity;

            /*
             * Category Image`

              if ($request->file('category_image'))
              {
              $path = Storage::disk('local')->put('public/product/category/image', $request->category_image);
              if ($path)
              {
              $model->categories_image = $path;
              }
              }
             *
             * Category Icon
             *
              if ($request->file('category_icon'))
              {
              $path = Storage::disk('local')->put('public/product/category/icon', $request->category_icon);
              if ($path)
              {
              $model->categories_icon = $path;
              }
              }
             * 
             */
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $changeCustomizedFormFlag = true;
                if ($request->show_customized_form == 1)
                {
                    /*
                     * Remove other address from default
                     */
                    $changeCustomizedFormFlag = AdminCategory::where(['show_customized_form' => 1])->where('id', '!=', $model->id);
                    if ($changeCustomizedFormFlag->count())
                    {
                        $changeCustomizedFormFlag = $changeCustomizedFormFlag->update(['show_customized_form' => 0]);
                    }
                }
                /*
                 * Slug Creation
                 */
                $slugFlag = false;

                foreach ($languageList as $language)
                {
                    $subModel = new AdminCategoryDescription();

                    $categoryName = 'category_name_' . $language->id;

                    $subModel->categories_id   = $model->id;
                    $subModel->language_id     = $language->id;
                    $subModel->categories_name = $request->$categoryName;

                    if ($slugFlag == false)
                    {
                        $slugFlag = true;
                        $slug     = "";
                        $oldSlug  = "";

                        if ($request->parent_id)
                        {
                            $parent = AdminCategory::find($request->parent_id);
                            if ($parent)
                            {
                                $oldSlug .= $parent->categories_slug . "-";
                                $slug    .= $parent->categories_slug . "-";
                            }
                        }

                        $slug    .= $request->$categoryName;
                        $oldSlug .= $request->$categoryName;

                        $slugCount = 0;
                        do
                        {
                            if ($slugCount == 0)
                            {
                                $currentSlug = Helper::slugify($oldSlug);
                            }
                            else
                            {
                                $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                            }

                            $slug      = $currentSlug;
                            $checkSlug = AdminCategory::checkSlug($currentSlug);
                            $slugCount++;
                        }
                        while (count($checkSlug) > 0);

                        $updateSlug = AdminCategory::updateSlug($model->id, $slug);
                        if (!$updateSlug)
                        {
                            $flag = false;
                        }
                    }

                    /*
                     * Save Description
                     */
                    if (!$subModel->save())
                    {
                        $flag = false;
                    }
                }
            }
            else
            {
                $flag = false;
            }

            if ($flag && $changeCustomizedFormFlag)
            {
                DB::commit();
                $status       = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.product.category.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $singleRecord = AdminCategory::find($id);
        if ($singleRecord)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {
                /*
                 * While Update
                 */
                $sortingNumber = $this->getSortingNumber();
                $currentOrder  = $singleRecord->sorting;
                $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;

                $categoryList = AdminCategory::getCategories(1);
                return view("admin.product.category.edit", compact('languageList', 'categoryList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.product.category.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.product.category.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        $languageList = Helper::getLanguageList();
        $model        = AdminCategory::find($id);
        if ($model)
        {
            
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;

            $categoryName = "";
            $languageID   = "";
            foreach ($languageList as $language)
            {
                $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-category-req-category'));
                $validationRule['category_name_' . $language->id]                  = 'required';
                $validationMessage['category_name_' . $language->id . '.required'] = $message_text;
                /*
                 * 

                  $validationRule['category_image']                                  = "image|mimes:jpg,jpeg,png|dimensions:width=" . config('custom.category_image_dimensions_width') . ",height=" . config('custom.category_image_dimensions_height');
                  $validationRule['category_icon']                                   = "image|mimes:jpg,jpeg,png|dimensions:width=" . config('custom.category_icon_dimensions_width') . ",height=" . config('custom.category_icon_dimensions_height');
                  $validationRule['min_order_quantity'] = "required|numeric|min:1|lte:max_order_quantity";
                  $validationRule['max_order_quantity'] = "required|numeric|min:1|gte:min_order_quantity";
                 */
                /* Description 
                  $message_text                                                             = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-category-req-description'));
                  $validationRule['category_description_' . $language->id]                  = 'required';
                  $validationMessage['category_description_' . $language->id . '.required'] = $message_text;
                 */
                $categoryName                                                      = 'category_name_' . $language->id;
                $languageID                                                        = $language->id;
            }
            $validationRule['parent_id']            = 'required';
            $validationRule['sorting']              = "required|numeric|gte:1|max:$sortingNumber";
            $validationRule['show_customized_form'] = "nullable";

            $validationMessage['parent_id.required'] = Lang::get($this->langFile . '.error-category-req-parent-id');
            $message_text_sorting                    = Lang::get($this->langFile . '.error-photo-max-sorting');
            $message_text_sorting                    = str_replace("#number#", $sortingNumber, $message_text_sorting);
            $validationMessage['sorting.required']   = Lang::get($this->langFile . '.error-photo-req-sorting');
            $validationMessage['sorting.numeric']    = Lang::get($this->langFile . '.error-photo-numeric-sorting');
            $validationMessage['sorting.gte']        = Lang::get($this->langFile . '.error-photo-min-sorting');
            $validationMessage['sorting.min']        = Lang::get($this->langFile . '.error-photo-min-sorting');
            $validationMessage['sorting.max']        = $message_text_sorting;
            /*
             * 

              $validationMessage['min_order_quantity.required'] = Lang::get($this->langFile . '.error-category-req-min_order_quantity');
              $validationMessage['min_order_quantity.numeric']  = Lang::get($this->langFile . '.error-category-numeric-min_order_quantity');
              $validationMessage['min_order_quantity.min']      = Lang::get($this->langFile . '.error-category-min-min_order_quantity');
              $validationMessage['min_order_quantity.lte']      = Lang::get($this->langFile . '.error-category-lte-min_order_quantity');
              $validationMessage['max_order_quantity.required'] = Lang::get($this->langFile . '.error-category-req-max_order_quantity');
              $validationMessage['max_order_quantity.numeric']  = Lang::get($this->langFile . '.error-category-numeric-max_order_quantity');
              $validationMessage['max_order_quantity.min']      = Lang::get($this->langFile . '.error-category-min-max_order_quantity');
              $validationMessage['max_order_quantity.gte']      = Lang::get($this->langFile . '.error-category-gte-max_order_quantity');
              $validationMessage['category_image.required']     = Lang::get($this->langFile . '.error-category-image-req-photo');
              $validationMessage['category_image.image']        = Lang::get($this->langFile . '.error-category-image-image-photo');
              $validationMessage['category_image.mimes']        = Lang::get($this->langFile . '.error-category-image-mimes-photo');
              $validationMessage['category_image.dimensions']   = Lang::get($this->langFile . '.error-category-image-dimensions-photo');
              $validationMessage['category_icon.required']      = Lang::get($this->langFile . '.error-category-icon-req-photo');
              $validationMessage['category_icon.image']         = Lang::get($this->langFile . '.error-category-icon-image-photo');
              $validationMessage['category_icon.mimes']         = Lang::get($this->langFile . '.error-category-icon-mimes-photo');
              $validationMessage['category_icon.dimensions']    = Lang::get($this->langFile . '.error-category-icon-dimensions-photo');
             */

            $parentID    = AdminCategory::where('parent_id', '=', 0)->pluck('id');
            $checkUnique = AdminCategoryDescription::where(['categories_name' => $request->$categoryName, 'language_id' => $languageID])->whereIn('categories_id', $parentID)->where('categories_id', '!=', $id)->count();
            if ($checkUnique > 0)
            {
                return redirect()->back()->withInput($request->input)->withErrors([$categoryName => Lang::get($this->langFile . '.error-category-unique-category')]);
            }
            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                $flag = true;

                $model->parent_id            = $request->parent_id; //Main Category
                $model->sorting              = $request->sorting;
                $model->show_customized_form = isset($request->show_customized_form) ? 1 : 0;
                $model->have_price_variant   = isset($request->have_price_variant) ? 1 : 0;

                //$model->min_order_quantity   = $request->min_order_quantity;
                //$model->max_order_quantity   = $request->max_order_quantity;
                $this->updateSortingOrder($currentOrder, $request->sorting);

                /*
                 * Category Image

                  if ($request->file('category_image'))
                  {
                  $path = Storage::disk('local')->put('public/product/category/image', $request->category_image);

                  if ($path)
                  {
                  Storage::disk('local')->delete($model->categories_image);
                  $model->categories_image = $path;
                  }
                  }
                 *
                 * Category Icon
                 *
                  if ($request->file('category_icon'))
                  {
                  $path = Storage::disk('local')->put('public/product/category/icon', $request->category_icon);

                  if ($path)
                  {
                  Storage::disk('local')->delete($model->categories_icon);
                  $model->categories_icon = $path;
                  }
                  }
                 * 
                 */

                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    $changeCustomizedFormFlag = true;
                    if ($request->show_customized_form == 1)
                    {
                        /*
                         * Remove other address from default
                         */
                        $changeCustomizedFormFlag = AdminCategory::where(['show_customized_form' => 1])->where('id', '!=', $model->id);
                        if ($changeCustomizedFormFlag->count())
                        {
                            $changeCustomizedFormFlag = $changeCustomizedFormFlag->update(['show_customized_form' => 0]);
                        }
                    }

                    /*
                     * Slug Creation
                     */
                    $slugFlag = false;

                    foreach ($languageList as $language)
                    {
                        $subModel = AdminCategoryDescription::getDataByLanguageID($id, $language->id);
                        if ($subModel)
                        {
                            $categoryName        = 'category_name_' . $language->id;
                            $categoryDescription = 'category_description_' . $language->id;

                            $subModel->categories_name        = $request->$categoryName;
                            $subModel->categories_description = $request->$categoryDescription;

                            if ($slugFlag == false)
                            {
                                $slugFlag = true;
                                $slug     = "";
                                $oldSlug  = "";

                                if ($request->parent_id)
                                {
                                    $parent = AdminCategory::find($request->parent_id);
                                    if ($parent)
                                    {
                                        $oldSlug .= $parent->categories_slug . "-";
                                        $slug    .= $parent->categories_slug . "-";
                                    }
                                }

                                $slug    .= $request->$categoryName;
                                $oldSlug .= $request->$categoryName;

                                $slugCount = 0;
                                do
                                {
                                    if ($slugCount == 0)
                                    {
                                        $currentSlug = Helper::slugify($oldSlug);
                                    }
                                    else
                                    {
                                        $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                                    }

                                    $slug      = $currentSlug;
                                    $checkSlug = AdminCategory::checkSlug($currentSlug, $model->id);
                                    $slugCount++;
                                }
                                while (count($checkSlug) > 0);

                                $updateSlug = AdminCategory::updateSlug($model->id, $slug);
                                if (!$updateSlug)
                                {
                                    $flag = false;
                                }
                            }
                        }

                        /*
                         * Save Description
                         */
                        if (!$subModel->save())
                        {
                            $flag = false;
                        }
                    }
                }
                else
                {
                    $flag = false;
                }

                if ($flag && $changeCustomizedFormFlag)
                {
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.product.category.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record               = AdminCategory::find($id);
            $deleteFlag           = true;
            /*
             * Check foreign record before delete
             */
            $categoryExistMessage = Lang::get($this->langFile . '.record_not_found');
            $productCategory      = \App\AdminModel\Product\AdminProductCategory::where('categories_id', $id)->count();
            $sizeCategory         = \App\AdminModel\Size\AdminSizeCategory::where('category_id', $id)->count();

            if ($productCategory)
            {
                $categoryExistMessage = Lang::get($this->langFile . '.category_product_exist');
                $deleteFlag           = false;
            }
            else if ($sizeCategory)
            {
                $categoryExistMessage = Lang::get($this->langFile . '.category_size_exist');
                $deleteFlag           = false;
            }

            $canChangeFlag = true;
            /*
             * Check if this category have orders or not
             * 
             * Step 1: Get this category's Product ids
             */
            $productIds    = \App\AdminModel\Product\AdminProductCategory::where('categories_id', $id)->pluck('products_id');
            if (count($productIds))
            {
                $checkProductHasOrders = \App\UserModel\OrderDetail::whereIn('product_id', $productIds)->count();
                if ($checkProductHasOrders)
                {
                    //Cannot change status
                    $canChangeFlag = false;
                }
            }
            /*             * 
             * Step 2: Get this category's - Sub Categories - Product ids
             */
            $subCategories = AdminCategory::where(['is_active' => 1, 'parent_id' => $id])->pluck('id');
            if (count($subCategories))
            {
                $productIds = \App\AdminModel\Product\AdminProductCategory::where('categories_id', $subCategories)->pluck('products_id');
                if (count($productIds))
                {
                    $checkProductHasOrders = \App\UserModel\OrderDetail::whereIn('product_id', $productIds)->count();
                    if ($checkProductHasOrders)
                    {
                        //Cannot change status
                        $canChangeFlag = false;
                    }
                }
            }

            if ($record && $deleteFlag)
            {
                if ($canChangeFlag)
                {
                    DB::beginTransaction();

                    try
                    {
                        $subDeleteFlag = true;
                        $subRecord     = $record->getCategoryDescription("", true);

                        foreach ($subRecord as $sub)
                        {
                            if (!$sub->delete())
                            {
                                $subDeleteFlag = false;
                            }
                        }

                        /*
                         * Delete Child category as well
                         */
                        $subCategory = AdminCategory::where('parent_id', $id)->get();

                        $subCategoryController = new SubCategoryController();

                        foreach ($subCategory as $subCategoryRecord)
                        {
                            $subRecord = $subCategoryRecord->getCategoryDescription("", true);

                            foreach ($subRecord as $sub)
                            {
                                if (!$sub->delete())
                                {
                                    $subDeleteFlag = false;
                                }
                            }

                            /*
                             * While Delete Change Sorting
                             */
                            $sortingNumber = $subCategoryController->getSortingNumber();
                            $sortingNumber = $sortingNumber - 1;
                            $currentOrder  = $subCategoryRecord->sorting;
                            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                            $subCategoryController->updateSortingOrder($currentOrder, $sortingNumber);

                            $subCategoryRecord->delete();
                        }


                        /*
                         * Delete Dependecy as well
                         */

                        /*
                         * While Delete Change Sorting
                         */
                        $sortingNumber = $this->getSortingNumber();
                        $sortingNumber = $sortingNumber - 1;
                        $currentOrder  = $record->sorting;
                        $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                        $this->updateSortingOrder($currentOrder, $sortingNumber);

                        if ($subDeleteFlag && $record->delete())
                        {
                            DB::commit();
                            $status       = 'success';
                            $message_text = Lang::get($this->langFile . '.delete-success');
                            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        }
                        else
                        {
                            DB::rollback();
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.delete-error');
                            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                        }
                    }
                    catch (Throwable $e)
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                else
                {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.delete-category-have-orders');
                }
            }
            else
            {
                $status       = 'error';
                $message_text = $categoryExistMessage;
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('admin.product.category.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active, $activateAll = false)
    {
        $singleRecord = AdminCategory::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $canChangeFlag = true;
                /*
                 * Check if this category have orders or not
                 * 
                 * Step 1: Get this category's Product ids
                 */
                $productIds    = \App\AdminModel\Product\AdminProductCategory::where('categories_id', $id)->pluck('products_id');
                if (count($productIds))
                {
                    $checkProductHasOrders = \App\UserModel\OrderDetail::whereIn('product_id', $productIds)->count();
                    if ($checkProductHasOrders)
                    {
                        //Cannot change status
                        $canChangeFlag = true;
                    }
                }
                /*                 * 
                 * Step 2: Get this category's - Sub Categories - Product ids
                 */
                $subCategories = AdminCategory::where(['is_active' => 1, 'parent_id' => $id])->pluck('id');
                if (count($subCategories))
                {
                    $productIds = \App\AdminModel\Product\AdminProductCategory::whereIn('categories_id', $subCategories)->pluck('products_id');
                    if (count($productIds))
                    {
                        $checkProductHasOrders = \App\UserModel\OrderDetail::whereIn('product_id', $productIds)->count();
                        if ($checkProductHasOrders)
                        {
                            //Cannot change status
                            $canChangeFlag = true;
                        }
                    }
                }

                if ($canChangeFlag)
                {
                    $subCategory = AdminCategory::where('parent_id', $id)->pluck('id');
                    if (count($subCategory))
                    {
                        $productIds = \App\AdminModel\Product\AdminProductCategory::whereIn('categories_id', $subCategory)->pluck('products_id');
                        if (count($productIds))
                        {
                            $updateProduct = \App\AdminModel\Product\AdminProduct::whereIn('id', $productIds)->update(['is_active_category' => $active]);
                            if ($activateAll)
                            {
                                $updateProduct = \App\AdminModel\Product\AdminProduct::whereIn('id', $productIds)->update(['is_active' => $active]);
                            }
                        }
                    }

                    $singleRecord->is_active = $active;
                    if ($singleRecord->save())
                    {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.status-change-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        if ($active == 1)
                        {
                            $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.active') . '"'), $message_text);
                        }
                        else
                        {
                            $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.in_active') . '"'), $message_text);
                        }
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.status-change-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.change-status-category-have-orders');
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.product.category.index')->with($status, $message_text);
    }

    /*
     * export data 
     */

    public function categoryDataExport()
    {
        return Excel::download(new CategoryExport(), config('excel.category_data'));
    }

}

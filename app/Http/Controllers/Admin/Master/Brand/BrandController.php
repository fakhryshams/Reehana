<?php

namespace App\Http\Controllers\Admin\Master\Brand;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\Brand\AdminBrand;
use App\AdminModel\Brand\AdminBrandDescription;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
//Export Excel
use App\Exports\AdminBrandExport;
use Maatwebsite\Excel\Facades\Excel;
use App\AdminModel\Brand\AdminBrandCategory;
use App\AdminModel\Product\AdminCategory;

class BrandController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.brand';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting        = Helper::getOrderSortingColumn($request);
        $sortKey        = $sorting['sortKey'];
        $sortVal        = $sorting['sortVal'];
        $searchText     = $request->q;
        $pageLength     = $request->pageLength;
        $filterCategory = $request->_c;
        if (empty($filterCategory))
        {
            $filterCategory = [];
        }
        $languageList = Helper::getLanguageList();
        $category     = AdminCategory::getCategories(1, false, 'brand');
        $brand        = new AdminBrand();
        $brand        = $brand->getAllData($searchText, $sortKey, $sortVal, $pageLength, $filterCategory);
        return view('admin.master.brand.index', compact('brand', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength', 'category', 'filterCategory'));
    }

    function getSortingNumber()
    {
        $model         = new AdminBrand();
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
            $record = AdminBrand::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminBrand::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            $isActive      = config('admin.product.isActive');
            $sortingNumber = $this->getSortingNumber();
            return view("admin.master.brand.add", compact('languageList', 'isActive', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.master.brand.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];
        $brandName         = "";
        $languageID        = "";

        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
//            'category_id' => "required",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required'     => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'      => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'          => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'          => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'          => $message_text_sorting,
            'category_id.required' => Lang::get($this->langFile . '.error-product-req-category'),
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                              = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-brand-req-title'));
            $validationRule['brand_' . $language->id]                  = 'required';
            $validationMessage['brand_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $brandName                                                 = 'brand_' . $language->id;
            $languageID                                                = $language->id;
            $checkUnique                                               = AdminbrandDescription::where(['brand_name' => $request->$brandName, 'language_id' => $languageID])->count();
            if ($checkUnique > 0)
            {
                return redirect()->back()->withInput($request->input)->withErrors([$brandName => Lang::get($this->langFile . '.error-brand-unique-name')]);
            }
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag  = true;
            $model = new Adminbrand();

            /*
             * code for sorting
             */
            $model->sorting = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            /*
             * save brand
             */
            if ($model->save())
            {


                foreach ($languageList as $language)
                {
                    $subModel              = new AdminbrandDescription();
                    $brand                 = 'brand_' . $language->id;
                    $subModel->brand_id    = $model->id;
                    $subModel->brand_name  = $request->$brand;
                    $subModel->language_id = $language->id;

                    if (!$subModel->save())
                    {
                        $flag = false;
                    }
                }

                if ($flag)
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

        return redirect()->route('admin.master.brand.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminBrand::find($id);
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

                return view("admin.master.brand.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.master.brand.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.master.brand.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = AdminBrand::find($id);
        if ($model)
        {
            $languageList      = Helper::getLanguageList();
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

            $brandName      = "";
            $languageID     = "";
            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
//                'category_id' => "required",
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required'     => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'      => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'          => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'          => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'          => $message_text_sorting,
                'category_id.required' => Lang::get($this->langFile . '.error-product-req-category'),
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                              = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-brand-req-title'));
                $validationRule['brand_' . $language->id]                  = 'required';
                $validationMessage['brand_' . $language->id . '.required'] = $message_text;
                /*
                 * For unique validation
                 */
                $brandName                                                 = 'brand_' . $language->id;
                $languageID                                                = $language->id;
                $checkUnique                                               = AdminBrandDescription::where(['brand_name' => $request->$brandName, 'language_id' => $languageID])->where('brand_id', '!=', $id)->count();
                if ($checkUnique > 0)
                {
                    return redirect()->back()->withInput($request->input)->withErrors([$brandName => Lang::get($this->langFile . '.error-brand-unique-name')]);
                }
            }
            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                /*
                 * save brand
                 */
                $model->sorting = $request->sorting;
                $this->updateSortingOrder($currentOrder, $request->sorting);

                if ($model->save())
                {
                    /*
                     * Insert status Flag
                     */
                    $flag = true;


                    foreach ($languageList as $language)
                    {
                        $subModel              = AdminBrandDescription::getDataByLanguageID($id, $language->id);
                        $brand                 = 'brand_' . $language->id;
                        $subModel->brand_id    = $model->id;
                        $subModel->brand_name  = $request->$brand;
                        $subModel->language_id = $language->id;

                        if (!$subModel->save())
                        {
                            $flag = false;
                        }
                    }

                    if ($flag)
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
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.master.brand.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        $hasBrand = \App\AdminModel\Product\AdminProduct::where('brand_id', $id)->first();
        if (!$hasBrand)
        {
            if ($id)
            {
                $record = AdminBrand::find($id);

                if ($record)
                {
                    DB::beginTransaction();
                    try
                    {
                        $subDeleteFlag = true;
                        $subRecord     = $record->getBrandDescription("", true);

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
                    $message_text = Lang::get($this->langFile . '.record_not_found');
                }
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete-brand');
        }
        return redirect()->route('admin.master.brand.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminBrand::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $singleRecord->is_active = $active;
                if ($singleRecord->save())
                {
                    if ($active == 0)
                    {
                        DB::statement("Update admin_products SET is_active_brand=is_active where brand_id = $id");
                        DB::statement("Update admin_products SET is_active=$active where brand_id = $id");
                    }
                    if ($active == 1)
                    {
                        DB::statement("Update admin_products SET is_active=is_active_brand where brand_id = $id");
                    }

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
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.master.brand.index')->with($status, $message_text);
        }
    }

    public function exportBrandData()
    {

        return Excel::download(new AdminBrandExport(), config('excel.brand_data'));
    }

}

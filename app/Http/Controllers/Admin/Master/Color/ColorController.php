<?php

namespace App\Http\Controllers\Admin\Master\Color;

use App\AdminModel\Product\AdminManufacturer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\Color\AdminColor;
use App\AdminModel\Color\AdminColorDescription;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
//Export Excel
use App\Exports\ColorExport;
use Maatwebsite\Excel\Facades\Excel;

class ColorController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.color';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting      = Helper::getOrderSortingColumn($request);
        $sortKey      = $sorting['sortKey'];
        $sortVal      = $sorting['sortVal'];
        $searchText   = $request->q;
        $pageLength   = $request->pageLength;
        $languageList = Helper::getLanguageList();
        $color        = new AdminColor();
        $color        = $color->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.master.color.index', compact('color', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminColor();
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
            $record = AdminColor::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminColor::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            return view("admin.master.color.add", compact('languageList', 'isActive', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.master.color.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
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
        $colorName         = "";
        $languageID        = "";

        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
                //'sorting' => "required|numeric|gte:1|max:$sortingNumber",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'  => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'      => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'      => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'      => $message_text_sorting,
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {

            $message_text                                              = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-color-req-title'));
            $validationRule['color_' . $language->id]                  = 'required';
            $validationMessage['color_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $colorName                                                 = 'color_' . $language->id;
            $languageID                                                = $language->id;
//            $checkUnique                                               = AdminColorDescription::where(['color_title' => $request->$colorName, 'language_id' => $languageID])->count();
//            if ($checkUnique > 0)
//            {
//                return redirect()->back()->withInput($request->input)->withErrors([$colorName => Lang::get($this->langFile . '.error-color-unique-name')]);
//            }
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag = true;

            $input              = $request->color_1; #English
            $input              = explode(",", $input);
            $errorUniqueMessage = "";
            foreach ($input as $inp)
            {
                $checkUnique = AdminColorDescription::where(['color_title' => $inp, 'language_id' => 1])->count();
                if ($checkUnique > 0)
                {
                    $errorUniqueMessage .= $inp . ", ";
                }
                else
                {
                    $model          = new AdminColor();
                    $model->sorting = $this->getSortingNumber();
                    if ($model->save())
                    {
                        foreach ($languageList as $language)
                        {
                            $subModel              = new AdminColorDescription();
                            $subModel->color_id    = $model->id;
                            $subModel->color_title = $inp;
                            $subModel->language_id = $language->id;

                            if (!$subModel->save())
                            {
                                $flag = false;
                            }
                        }
                    }
                }
            }

            if ($flag)
            {
                DB::commit();
                $status       = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                if ($errorUniqueMessage)
                {
                    $message_text = rtrim($errorUniqueMessage, ", ") . " already exist so they not added, Remaining color added successfully";
                }
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            /*
             * save color

              if ($model->save())
              {
              foreach ($languageList as $language)
              {
              $subModel              = new AdminColorDescription();
              $color                 = 'color_' . $language->id;
              $subModel->color_id    = $model->id;
              $subModel->color_title = $request->$color;
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
             * 
             */
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.master.color.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminColor::find($id);
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

                return view("admin.master.color.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.master.color.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.master.color.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = AdminColor::find($id);
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

            $colorName      = "";
            $languageID     = "";
            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'  => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'      => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'      => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'      => $message_text_sorting,
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {

                $message_text                                              = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-color-req-title'));
                $validationRule['color_' . $language->id]                  = 'required';
                $validationMessage['color_' . $language->id . '.required'] = $message_text;
                /*
                 * For unique validation
                 */
                $colorName                                                 = 'color_' . $language->id;
                $languageID                                                = $language->id;
                $checkUnique                                               = AdminColorDescription::where(['color_title' => $request->$colorName, 'language_id' => $languageID])->where('color_id', '!=', $id)->count();
                if ($checkUnique > 0)
                {
                    return redirect()->back()->withInput($request->input)->withErrors([$colorName => Lang::get($this->langFile . '.error-color-unique-name')]);
                }
            }
            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                /*
                 * save color
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
                        $subModel              = AdminColorDescription::getDataByLanguageID($id, $language->id);
                        $color                 = 'color_' . $language->id;
                        $subModel->color_id    = $model->id;
                        $subModel->color_title = $request->$color;
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
        return redirect()->route('admin.master.color.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        $titleCount = AdminColor::all()->count();
        if ($titleCount > 1)
        {
            $hasProductColor = \App\AdminModel\Product\AdminProductAttribute::where('color_id', $id)->pluck('products_id');
            $hasProducts     = false;
            if ($hasProductColor)
            {
                $hasProducts = \App\AdminModel\Product\AdminProduct::where('have_color', 1)->whereIn('id', $hasProductColor)->pluck('id');
                $hasProducts = count($hasProducts);
            }
            if (!$hasProducts)
            {
                if ($id)
                {
                    $record = AdminColor::find($id);

                    if ($record)
                    {
                        DB::beginTransaction();
                        try
                        {
                            $subDeleteFlag = true;
                            $subRecord     = $record->getColorDescription("", true);
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
                $message_text = Lang::get($this->langFile . '.cannot-delete-color');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.master.color.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminColor::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
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
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.master.color.index')->with($status, $message_text);
        }
    }

    public function exportColorData()
    {

        return Excel::download(new ColorExport(), config('excel.color_data'));
    }

}

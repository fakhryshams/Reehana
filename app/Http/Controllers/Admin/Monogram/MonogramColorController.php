<?php

namespace App\Http\Controllers\Admin\Monogram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\AdminModel\Monogram\MonogramColor;
use DB;

class MonogramColorController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.monogram_color';

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
        $model        = new MonogramColor();
        $model        = $model->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.monogram.color.index', compact('model', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new MonogramColor();
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
            $record = MonogramColor::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = MonogramColor::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            $sortingNumber = $this->getSortingNumber();
            return view("admin.monogram.color.add", compact('languageList', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.monogram.color.index')->with('error', __($this->langFile . 'language-not-found'));
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
            'sorting'    => "required|numeric|gte:1|max:$sortingNumber",
            'color_name' => "required",
            'color_hash' => "required",
        ];

        $message_text_sorting = __($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required'    => __($this->langFile . '.error-req-sorting'),
            'sorting.numeric'     => __($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'         => __($this->langFile . '.error-min-sorting'),
            'sorting.min'         => __($this->langFile . '.error-min-sorting'),
            'sorting.max'         => $message_text_sorting,
            'color_name.required' => __($this->langFile . '.error-req-color_name'),
            'color_hash.required' => __($this->langFile . '.error-req-color_hash'),
        ];
        $validatedData     = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag  = true;
            $model = new MonogramColor();

            /*
             * code for sorting
             */
            $model->sorting    = $request->sorting;
            $model->color_name = $request->color_name;
            $model->color_hash = $request->color_hash;

            $this->updateSortingOrder($sortingNumber, $request->sorting);


            /*
             * end of code for sorting
             */
            /*
             * save color
             */
            if ($model->save())
            {
                DB::commit();
                $status       = 'success';
                $message_text = __($this->langFile . '.add-success');
                $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = __($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = __($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.monogram.color.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = MonogramColor::find($id);
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

                return view("admin.monogram.color.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.monogram.color.index')->with('error', __($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.monogram.color.index')->with('error', __($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = MonogramColor::find($id);
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

            $languageID     = "";
            $validationRule = [
                'sorting'    => "required|numeric|gte:1|max:$sortingNumber",
                'color_name' => "required",
                'color_hash' => "required",
            ];

            $message_text_sorting = __($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required'    => __($this->langFile . '.error-req-sorting'),
                'sorting.numeric'     => __($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'         => __($this->langFile . '.error-min-sorting'),
                'sorting.min'         => __($this->langFile . '.error-min-sorting'),
                'sorting.max'         => $message_text_sorting,
                'color_name.required' => __($this->langFile . '.error-req-color_name'),
                'color_hash.required' => __($this->langFile . '.error-req-color_hash'),
            ];
            $validatedData     = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                /*
                 * save color
                 */
                $model->sorting     = $request->sorting;
                $model->color_name  = $request->color_name;
                $model->color_hash = $request->color_hash;
                
                $this->updateSortingOrder($currentOrder, $request->sorting);

                if ($model->save())
                {
                    DB::commit();
                   
                    $status       = 'success';
                    $message_text = __($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = __($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = __($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            DB::rollback();
            $status       = 'error';
            $message_text = __($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.monogram.color.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = MonogramColor::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    /*
                     * While Delete Change Sorting
                     */
                    $sortingNumber = $this->getSortingNumber();
                    $sortingNumber = $sortingNumber - 1;
                    $currentOrder  = $record->sorting;
                    $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($record->delete())
                    {
                        DB::commit();

                        $status       = 'success';
                        $message_text = __($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = __($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = __($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                $status       = 'error';
                $message_text = __($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = __($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.monogram.color.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = MonogramColor::find($id);
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
                    $message_text = __($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                    if ($active == 1)
                    {
                        $message_text = str_replace("#status#", '"' . (__($this->langFile . '.active') . '"'), $message_text);
                    }
                    else
                    {
                        $message_text = str_replace("#status#", '"' . (__($this->langFile . '.in_active') . '"'), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = __($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = __($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.monogram.color.index')->with($status, $message_text);
        }
    }

}

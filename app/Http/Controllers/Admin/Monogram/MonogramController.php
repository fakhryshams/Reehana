<?php

namespace App\Http\Controllers\Admin\Monogram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\AdminModel\Monogram\MonogramMaster;
use DB;
use Illuminate\Support\Facades\Storage;

class MonogramController extends Controller
{

    private $langFile = 'admin';
    private $module = '.monogram_master';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting = Helper::getOrderSortingColumn($request);
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $languageList = Helper::getLanguageList();
        $model = new MonogramMaster();
        $model = $model->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.monogram.monogram.index', compact('model', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model = new MonogramMaster();
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
            $record = MonogramMaster::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = MonogramMaster::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            return view("admin.monogram.monogram.add", compact('languageList', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.monogram.monogram.index')->with('error', __($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule = [];
        $validationMessage = [];
        $colorName = "";
        $languageID = "";

        $sortingNumber = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
            'placement_name' => "required",
            'placement_desc' => "required",
            'placement_image' => 'required|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.monogram_placement_image_dimensions_width') . ',height=' . config('custom.monogram_placement_image_dimensions_height'),
        ];

        $message_text_sorting = __($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required' => __($this->langFile . '.error-req-sorting'),
            'sorting.numeric' => __($this->langFile . '.error-numeric-sorting'),
            'sorting.gte' => __($this->langFile . '.error-min-sorting'),
            'sorting.min' => __($this->langFile . '.error-min-sorting'),
            'sorting.max' => $message_text_sorting,
            'placement_name.required' => __($this->langFile . '.error-req-placement_name'),
            'placement_desc.required' => __($this->langFile . '.error-req-placement_desc'),
            'placement_image.required' => __($this->langFile . '.error-req-placement_image'),
            'placement_image.image' => __($this->langFile . '.error-image-placement_image'),
            'placement_image.mimes' => __($this->langFile . '.error-mimes-placement_image'),
            'placement_image.dimensions' => __($this->langFile . '.error-dimensions-placement_image'),
        ];
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag = true;
            $model = new MonogramMaster();

            /*
             * code for sorting
             */
            $model->sorting = $request->sorting;
            $model->placement_name = $request->placement_name;
            $model->placement_desc = $request->placement_desc;
            $model->placement_price = 0;
            if ($request->placement_image)
            {
                $file = $request->placement_image;
                $path = $this->uploadImage($file);

                if ($path)
                {
                    $model->placement_image = $path;
                }
            }

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
                $status = 'success';
                $message_text = __($this->langFile . '.add-success');
                $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        } catch (Throwable $e)
        {
            DB::rollback();
            $status = 'error';
            $message_text = __($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.monogram.monogram.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = MonogramMaster::find($id);
        if ($singleRecord)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {
                /*
                 * While Update
                 */
                $sortingNumber = $this->getSortingNumber();
                $currentOrder = $singleRecord->sorting;
                $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;

                return view("admin.monogram.monogram.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.monogram.monogram.index')->with('error', __($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.monogram.monogram.index')->with('error', __($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = MonogramMaster::find($id);
        if ($model)
        {
            $languageList = Helper::getLanguageList();
            /*
             * Validations
             */
            $validationRule = [];
            $validationMessage = [];

            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;

            $colorName = "";
            $languageID = "";
            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
                'placement_name' => "required",
                'placement_desc' => "required",
                'placement_image' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.monogram_placement_image_dimensions_width') . ',height=' . config('custom.monogram_placement_image_dimensions_height'),
            ];

            $message_text_sorting = __($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required' => __($this->langFile . '.error-req-sorting'),
                'sorting.numeric' => __($this->langFile . '.error-numeric-sorting'),
                'sorting.gte' => __($this->langFile . '.error-min-sorting'),
                'sorting.min' => __($this->langFile . '.error-min-sorting'),
                'sorting.max' => $message_text_sorting,
                'placement_name.required' => __($this->langFile . '.error-req-placement_name'),
                'placement_desc.required' => __($this->langFile . '.error-req-placement_desc'),
                'placement_image.required' => __($this->langFile . '.error-req-placement_image'),
                'placement_image.image' => __($this->langFile . '.error-image-placement_image'),
                'placement_image.mimes' => __($this->langFile . '.error-mimes-placement_image'),
                'placement_image.dimensions' => __($this->langFile . '.error-dimensions-placement_image'),
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                /*
                 * save color
                 */
                $model->sorting = $request->sorting;
                $model->placement_name = $request->placement_name;
                $model->placement_desc = $request->placement_desc;

                $oldImage = false;
                if ($request->placement_image && $request->placement_image)
                {
                    $file = $request->placement_image;
                    $path = $this->uploadImage($file);

                    if ($path)
                    {
                        $oldImage = $model->placement_image;
                        $model->placement_image = $path;
                    }
                }
                $this->updateSortingOrder($currentOrder, $request->sorting);

                if ($model->save())
                {
                    DB::commit();
                    /*
                     * Unlink Previous Image
                     */
                    if ($oldImage)
                    {
                        if (Storage::has($oldImage))
                        {
                            Storage::delete($oldImage);
                        }
                    }


                    $status = 'success';
                    $message_text = __($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e)
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            DB::rollback();
            $status = 'error';
            $message_text = __($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.monogram.monogram.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = MonogramMaster::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    /*
                     * While Delete Change Sorting
                     */
                    $oldImage = $record->placement_image;

                    $sortingNumber = $this->getSortingNumber();
                    $sortingNumber = $sortingNumber - 1;
                    $currentOrder = $record->sorting;
                    $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($record->delete())
                    {
                        DB::commit();

                        if ($oldImage)
                        {
                            if (Storage::has($oldImage))
                            {
                                Storage::delete($oldImage);
                            }
                        }

                        $status = 'success';
                        $message_text = __($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status = 'error';
                        $message_text = __($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                    }
                } catch (Throwable $e)
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                $status = 'error';
                $message_text = __($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status = 'error';
            $message_text = __($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.monogram.monogram.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = MonogramMaster::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $singleRecord->is_active = $active;
                if ($singleRecord->save())
                {
                    DB::commit();
                    $status = 'success';
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
                    $status = 'error';
                    $message_text = __($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e)
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.monogram.monogram.index')->with($status, $message_text);
        }
    }

    public function uploadImage($file)
    {
        // Get filename with extension
        $filenameWithExt = $file->getClientOriginalName();

        // Get file path
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        // Remove unwanted characters
        $filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        $filename = preg_replace("/\s+/", '-', $filename);

        // Get the original image extension
        $extension = $file->getClientOriginalExtension();

        // Create unique file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $path = $file->storeAs(config('filepath.monogram_font_placement_image'), $fileNameToStore);

        return $path;
    }

}

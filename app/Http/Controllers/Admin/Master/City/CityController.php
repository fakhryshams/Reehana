<?php

namespace App\Http\Controllers\Admin\Master\City;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\City\AdminCity;
use App\AdminModel\City\AdminCityDescription;
use App\AdminModel\AdminCountry;
//Export Excel
use App\Exports\CityExport;
use Maatwebsite\Excel\Facades\Excel;

class CityController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.city';

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
        $city         = new AdminCity();
        $city         = $city->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.master.city.index', compact('city', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminCity();
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
            $record = AdminCity::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminCity::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            $country       = AdminCountry::getCountry(1);
            return view("admin.master.city.add", compact('languageList', 'sortingNumber', 'country'));
        }
        else
        {
            return redirect()->route('admin.master.city.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
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

        $cityName       = "";
        $languageID     = "";
        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
                //'country_id' => "required",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required'    => Lang::get($this->langFile . '.error-req-sorting'),
            'country_id.required' => Lang::get($this->langFile . '.error-req-country'),
            'sorting.numeric'     => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'         => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'         => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'         => $message_text_sorting,
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                             = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-city-req-name'));
            $validationRule['city_' . $language->id]                  = 'required';
            $validationMessage['city_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $cityName                                                 = 'city_' . $language->id;
            $languageID                                               = $language->id;
            $checkUnique                                              = AdminCityDescription::where(['city_name' => $request->$cityName, 'language_id' => $languageID])->count();
            if ($checkUnique > 0)
            {
                return redirect()->back()->withInput($request->input)->withErrors([$cityName => Lang::get($this->langFile . '.error-city-unique-name')]);
            }
        }

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model             = new AdminCity();
            /*
             * code for sorting
             */
            $model->sorting    = $request->sorting;
            $model->country_id = config('custom.CountryID_Kuwait', 2); #2=Kuwait
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            /*
             * main save color
             */
            if ($model->save())
            {
                /*
                 * Insert status Flag
                 */
                $flag = true;

                foreach ($languageList as $language)
                {
                    $subModel              = new AdminCityDescription();
                    $city                  = 'city_' . $language->id;
                    $subModel->city_id     = $model->id;
                    $subModel->city_name   = $request->$city;
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

        return redirect()->route('admin.master.city.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminCity::find($id);
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

                $country = AdminCountry::getCountry(1);
                return view("admin.master.city.edit", compact('country', 'id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.master.city.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.master.city.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = AdminCity::find($id);
        if ($model)
        {
            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;

            $languageList      = Helper::getLanguageList();
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];
            $cityName          = "";
            $languageID        = "";
            $validationRule    = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
                    //'country_id' => "required",
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required'    => Lang::get($this->langFile . '.error-req-sorting'),
                'country_id.required' => Lang::get($this->langFile . '.error-req-country'),
                'sorting.numeric'     => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'         => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'         => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'         => $message_text_sorting,
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                             = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-city-req-name'));
                $validationRule['city_' . $language->id]                  = 'required';
                $validationMessage['city_' . $language->id . '.required'] = $message_text;
                /*
                 * For unique validation
                 */
                $cityName                                                 = 'city_' . $language->id;
                $languageID                                               = $language->id;
                $checkUnique                                              = AdminCityDescription::where(['city_name' => $request->$cityName, 'language_id' => $languageID])->where('city_id', '!=', $id)->count();
                if ($checkUnique > 0)
                {
                    return redirect()->back()->withInput($request->input)->withErrors([$cityName => Lang::get($this->langFile . '.error-city-unique-name')]);
                }
            }

            $validatedData = $request->validate($validationRule, $validationMessage);

            try
            {
                DB::beginTransaction();

                $model->sorting    = $request->sorting;
                $model->country_id = config('custom.CountryID_Kuwait', 2);
                $this->updateSortingOrder($currentOrder, $request->sorting);
                if ($model->save())
                {
                    /*
                     * Insert status Flag
                     */
                    $flag = true;

                    foreach ($languageList as $language)
                    {
                        $subModel              = AdminCityDescription::getDataByLanguageID($id, $language->id);
                        $city                  = 'city_' . $language->id;
                        $subModel->city_id     = $model->id;
                        $subModel->city_name   = $request->$city;
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
        return redirect()->route('admin.master.city.index')->with($status, $message_text);
    }

    public function delete($id)
    {
        $titleCount = AdminCity::all()->count();
        if ($titleCount > 1)
        {
            if ($id)
            {
                $record = AdminCity::find($id);

                if ($record)
                {
                    DB::beginTransaction();
                    try
                    {
                        $subDeleteFlag = true;
                        $subRecord     = $record->getCityDescription("", true);
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
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.master.city.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminCity::find($id);
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

            return redirect()->route('admin.master.city.index')->with($status, $message_text);
        }
    }

    public function exportCityData()
    {
        return Excel::download(new CityExport(), config('excel.city_data'));
    }

}

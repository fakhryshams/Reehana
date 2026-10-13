<?php

namespace App\Http\Controllers\Admin\Master\Country;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\AdminCountry;
use App\AdminModel\AdminCountryDescription;
use App\AdminModel\Color\AdminColorDescription;
//Export Excel
use App\Exports\CountryExport;
use Maatwebsite\Excel\Facades\Excel;

class CountryController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.country';

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
        $country      = new AdminCountry();
        $country      = $country->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        $currency     = Helper::getDefaultCurrency();
        if ($currency)
        {
            $currency->code = config('custom.currency_convert_default_country');
        }
        return view('admin.master.country.index', compact('country', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength', 'currency'));
    }

    function getSortingNumber()
    {
        $model         = new AdminCountry();
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
            $record = AdminCountry::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminCountry::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            $isActive      = config('admin.product.isActive');
            return view("admin.master.country.add", compact('languageList', 'sortingNumber', 'isActive'));
        }
        else
        {
            return redirect()->route('admin.master.country.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
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

        $countryName = "";
        $languageID  = "";

        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
            'sorting'         => "required|numeric|gte:1|max:$sortingNumber",
            'shipping_charge' => "required|numeric",
            'country_code'    => "required|unique:admin_country,country_code,NULL,id,deleted_at,NULL",
            'currency_code'   => "required",
            'currency_rate'   => "required",
            'is_active'       => 'required',
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required'         => Lang::get($this->langFile . '.error-req-sorting'),
            'shipping_charge.required' => Lang::get($this->langFile . '.error-req-shipping'),
            'shipping_charge.numeric'  => Lang::get($this->langFile . '.error-numeric-shipping'),
            'sorting.numeric'          => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'              => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'              => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'              => $message_text_sorting,
            'is_active.required'       => Lang::get($this->langFile . '.error-slider-req-status'),
            'country_code.required'    => Lang::get($this->langFile . '.error-country-req-country_code'),
            'country_code.unique'      => Lang::get($this->langFile . '.error-country-unique-country_code'),
            'currency_code.required'   => Lang::get($this->langFile . '.error-country-req-currency_code'),
            'currency_rate.required'   => Lang::get($this->langFile . '.error-country-req-currency_rate'),
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-country-req-name'));
            $validationRule['country_' . $language->id]                        = 'required';
            $validationMessage['country_' . $language->id . '.required']       = $message_text;
            $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-delivery-req-name'));
            $validationRule['delivery_time_' . $language->id]                  = 'required';
            $validationMessage['delivery_time_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $countryName                                                       = 'country_' . $language->id;
            $languageID                                                        = $language->id;
            $checkUnique                                                       = AdminCountryDescription::where(['country_name' => $request->$countryName, 'language_id' => $languageID])->count();
            if ($checkUnique > 0)
            {
                return redirect()->back()->withInput($request->input)->withErrors([$countryName => Lang::get($this->langFile . '.error-country-unique-name')]);
            }
        }

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model                  = new AdminCountry();
            /*
             * code for sorting
             */
            $model->sorting         = $request->sorting;
            $model->is_active       = $request->is_active;
            $model->shipping_charge = $request->shipping_charge;
            $model->free_shipping_amount = $request->free_shipping_amount;
            $model->country_code    = strtoupper($request->country_code);
            $model->currency_code   = strtoupper($request->currency_code);
            $model->currency_rate   = $request->currency_rate;
            $model->is_default      = 0;
            if (isset($request->is_default) && $request->is_default)
            {
                $this->removeDefaultCountry();
                $model->is_default = 1;
            }
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
                    $subModel                = new AdminCountryDescription();
                    $country                 = 'country_' . $language->id;
                    $deliveryTime            = 'delivery_time_' . $language->id;
                    $subModel->country_id    = $model->id;
                    $subModel->country_name  = $request->$country;
                    $subModel->delivery_time = $request->$deliveryTime;
                    $subModel->language_id   = $language->id;

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

        return redirect()->route('admin.master.country.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminCountry::find($id);
        if ($singleRecord)
        {
            if ($singleRecord->can_edit)
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

                    $isActive = config('admin.product.isActive');
                    return view("admin.master.country.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber', 'isActive'));
                }
                else
                {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . 'language-not-found');
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
        return redirect()->route('admin.master.country.index')->with($status, $message_text);
    }

    public function update(Request $request, $id)
    {
        $model = AdminCountry::find($id);
        if ($model)
        {
            if ($model->can_edit)
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
                $sortingNumber     = $this->getSortingNumber();
                $currentOrder      = $model->sorting;
                
                $sortingNumber     = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
                $currentOrder      = $currentOrder ? $currentOrder : $sortingNumber;

                $countryName    = "";
                $languageID     = "";
                $validationRule = [
                    'sorting'         => "required|numeric|gte:1|max:$sortingNumber",
                    'shipping_charge' => "required|numeric",
                    'is_active'       => 'required',
                    'country_code'    => "required|unique:admin_country,country_code,$id,id,deleted_at,NULL",
                    'currency_code'   => 'required',
                    'currency_rate'   => 'required',
                ];

                $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
                $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);
                $validationMessage    = [
                    'sorting.required'         => Lang::get($this->langFile . '.error-req-sorting'),
                    'sorting.numeric'          => Lang::get($this->langFile . '.error-numeric-sorting'),
                    'sorting.gte'              => Lang::get($this->langFile . '.error-min-sorting'),
                    'sorting.min'              => Lang::get($this->langFile . '.error-min-sorting'),
                    'sorting.max'              => $message_text_sorting,
                    'shipping_charge.required' => Lang::get($this->langFile . '.error-req-shipping'),
                    'shipping_charge.numeric'  => Lang::get($this->langFile . '.error-numeric-shipping'),
                    'is_active.required'       => Lang::get($this->langFile . '.error-slider-req-status'),
                    'country_code.required'    => Lang::get($this->langFile . '.error-country-req-country_code'),
                    'country_code.unique'      => Lang::get($this->langFile . '.error-country-unique-country_code'),
                    'currency_code.required'   => Lang::get($this->langFile . '.error-country-req-currency_code'),
                    'currency_rate.required'   => Lang::get($this->langFile . '.error-country-req-currency_rate'),
                ];
                /*
                 * language wise validation
                 */
                foreach ($languageList as $language)
                {
                    $message_text                                                = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-country-req-name'));
                    $validationRule['country_' . $language->id]                  = 'required';
                    $validationMessage['country_' . $language->id . '.required'] = $message_text;

                    $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-delivery-req-name'));
                    $validationRule['delivery_time_' . $language->id]                  = 'required';
                    $validationMessage['delivery_time_' . $language->id . '.required'] = $message_text;
                    /*
                     * For unique validation
                     */
                    $countryName                                                       = 'country_' . $language->id;
                    $languageID                                                        = $language->id;
                    $checkUnique                                                       = AdminCountryDescription::where(['country_name' => $request->$countryName, 'language_id' => $languageID])->where('country_id', '!=', $id)->count();
                    if ($checkUnique > 0)
                    {
                        return redirect()->back()->withInput($request->input)->withErrors([$countryName => Lang::get($this->langFile . '.error-country-unique-name')]);
                    }
                }

                $validatedData = $request->validate($validationRule, $validationMessage);

                DB::beginTransaction();

                try
                {
                    $model->sorting         = $request->sorting;
                    $this->updateSortingOrder($currentOrder, $request->sorting);
                    $model->is_active       = $request->is_active;
                    $model->shipping_charge = $request->shipping_charge;
                    $model->free_shipping_amount = $request->free_shipping_amount;
                    $model->country_code    = strtoupper($request->country_code);
                    $model->currency_code   = strtoupper($request->currency_code);
                    $model->currency_rate   = $request->currency_rate;

                    /*
                      if (isset($request->is_default) && $request->is_default)
                      {
                      $this->removeDefaultCountry($model->id);
                      $model->is_default = 1;
                      }
                      else
                      {
                      if ($model->is_default)
                      {
                      $defaultCountryCount = $this->getDefaultCountryCount();
                      if ($defaultCountryCount == 1)
                      {
                      return redirect()->back()->withInput($request->input)->withErrors(['is_default' => Lang::get($this->langFile . '.error-country-is_default-atleast-one')]);
                      }
                      }
                      $model->is_default = 0;
                      }
                     * 
                     */

                    if ($model->save())
                    {

                        /*
                         * Insert status Flag
                         */
                        $flag = true;
                        foreach ($languageList as $language)
                        {
                            $subModel     = AdminCountryDescription::getDataByLanguageID($id, $language->id);
                            $country      = 'country_' . $language->id;
                            $deliveryTime = 'delivery_time_' . $language->id;

                            $subModel->country_id    = $model->id;
                            $subModel->country_name  = $request->$country;
                            $subModel->delivery_time = $request->$deliveryTime;
                            $subModel->language_id   = $language->id;
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
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.master.country.index')->with($status, $message_text);
    }

    public function getDefaultCountryCount()
    {
        return AdminCountry::where('is_default', 1)->count();
    }

    public function removeDefaultCountry($countryId = false)
    {
        $query = AdminCountry::where('is_default', 1);
        if ($countryId)
        {
            $query->where('id', '!=', $countryId);
        }
        return $query = $query->update(['is_default' => 0]);
    }

    public function delete($id)
    {
        $titleCount = AdminCountry::all()->count();
        if ($titleCount > 1)
        {
            if ($id)
            {
                $record = AdminCountry::find($id);

                if ($record)
                {
                    if ($record->can_edit)
                    {
                        if ($record->is_default == 1)
                        {
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.cannot-delete-default-country');
                        }
                        else
                        {
                            DB::beginTransaction();
                            try
                            {
                                $subDeleteFlag = true;
                                $subRecord     = $record->getCountryDescription("", true);
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
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.master.country.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminCountry::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                if ($singleRecord->is_default == 1 && $active == 0)
                {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.cannot-modify-default-country');
                }
                else
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
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.master.country.index')->with($status, $message_text);
        }
    }

    public function exportCountryData()
    {
        return Excel::download(new CountryExport(), config('excel.country_data'));
    }

    public function getCurrencyRate(Request $request)
    {
        if (isset($request->currencyCode) && $request->currencyCode)
        {
            $convertTo = strtoupper($request->currencyCode);

            $fromTo = config('custom.currency_convert_default_country') . '_' . $convertTo;

            $currData = file_get_contents('https://free.currconv.com/api/v7/convert?q=' . $fromTo . '&compact=ultra&apiKey=' . config('custom.currency_convert_api_key'));
            $output   = json_decode($currData);

            if ($output && isset($output->$fromTo))
            {
                return Helper::formatNumber($output->$fromTo, 2);
            }
        }
        return false;
    }

    public function updateCurrencyCron()
    {
        $allCountry = AdminCountry::get();
        foreach ($allCountry as $country)
        {
            if ($country->currency_code)
            {
                $convertTo = strtoupper($country->currency_code);

                $fromTo = config('custom.currency_convert_default_country') . '_' . $convertTo;

                $currData = file_get_contents('https://free.currconv.com/api/v7/convert?q=' . $fromTo . '&compact=ultra&apiKey=' . config('custom.currency_convert_api_key'));
                $output   = json_decode($currData);

                if ($output && isset($output->$fromTo))
                {
                    $country->currency_rate = Helper::formatNumber($output->$fromTo, 2);
                    $country->save();
                }
            }
        }
    }

}

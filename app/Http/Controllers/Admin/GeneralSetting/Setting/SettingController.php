<?php

namespace App\Http\Controllers\Admin\GeneralSetting\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminModel\AdminSettings;
use App\AdminModel\AdminSettingDescription;
use Helper;
use DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\AdminDiscount;
use App\AdminModel\Brand\AdminBrand;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\CategoryDiscount;
use App\AdminModel\Product\AdminProductCategory;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Brand\AdminBrandDescription;
use App\AdminModel\Product\AdminCategoryDescription;

class SettingController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.setting';

    public function index(Request $request, $pageLength = 'ALL')
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderSortingColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength ? $request->pageLength : $pageLength;
        $radioValue = config('admin.setting.cod');
        $settings   = AdminSettings::getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.general-setting.setting.index', compact('settings', 'sortKey', 'sortVal', 'searchText', 'pageLength', 'radioValue'));
    }

    public function edit(AdminSettings $setting, $id)
    {
        $record = $setting->find($id);
        if ($record && $record->can_edit) {
            $radioValue = [];
            if ($record->type == 'radio') {
                $radioValue = config('admin.setting.cod');
            }
            $languageList = Helper::getLanguageList();
            return view("admin.general-setting.setting.edit", compact('record', 'languageList', 'radioValue'));
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
            return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
        }
    }

    public function update(Request $request, $id)
    {
        $record = AdminSettings::find($id);
        if ($record && $record->can_edit) {
            if ($record->is_multi_lang) {
                $languageList      = Helper::getLanguageList();
                $validationRules   = [];
                $validationMessage = [];
                foreach ($languageList as $language) {
                    $validationRules['setting_' . $record->id . '_' . $language->id]                 = ($record->is_require == 1) ? 'required' : 'nullable';
                    $message_text                                                                    = str_replace("#field#", ucfirst($record->setting_key) . ' (' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-setting-req-field'));
                    $validationMessage['setting_' . $record->id . '_' . $language->id . ".required"] = ($record->is_require == 1) ? $message_text : '';
                }
                $validatedData = $request->validate($validationRules, $validationMessage);
            } else {
                $validationString  = ($record->is_require == 1) ? 'required' : 'nullable';
                $validationMessage = [
                    'id.required'          => Lang::get($this->langFile . '.error'),
                    'setting_val.required' => Lang::get($this->langFile . '.error-setting-req-name')
                ];
                if ($record->type == 'file' && $record->id != 25) {
                    /*
                     * Image file validation common
                     */
                    $validationString                       .= "|image|mimes:jpeg,png,jpg,svg";
                    $validationMessage['setting_val.image'] = Lang::get($this->langFile . '.error-setting-file-type');
                    $validationMessage['setting_val.mimes'] = Lang::get($this->langFile . '.error-setting-file-mimes');
                } else if ($record->type == 'file' && $record->id == 25) {
                    /*
                     * Image file validation common
                     */

                    /*
                     * Logo - Dimention 174x148
                     */

                    $height                                      = config('custom.app_logo_png_height', 150);
                    $width                                       = config('custom.app_logo_png_width', 150);
                    $validationString                            .= "|dimensions:width=$width,height=$height|image|mimes:png";
                    //                    dd($validationString);
                    $validationMessage['setting_val.image']      = Lang::get($this->langFile . '.error-setting-file-type');
                    $validationMessage['setting_val.mimes']      = Lang::get($this->langFile . '.error-setting-file-mimes-png');
                    $validationMessage['setting_val.dimensions'] = Lang::get($this->langFile . '.error-setting-file-dimention-png');
                }
                if ($record->id == 2) {
                    /*
                     * Logo - Dimention 174x148
                     */
                    $height = config('custom.app_logo_height');
                    $width  = config('custom.app_logo_width');

                    //$validationString                            .= '|dimensions:width=' . $width . ',height=' . $height;
                    $validationMessage['setting_val.size']       = Lang::get($this->langFile . '.error-setting-size-favicon');
                    $validationMessage['setting_val.dimensions'] = Lang::get($this->langFile . '.error-setting-dimention-favicon');
                }
                if ($record->id == 3) {
                    /*
                     * Favicon validation - Size 1KB, Dimention 16x16
                     */

                    $height = config('custom.favicon_height');
                    $width  = config('custom.favicon_width');

                    $validationString                            .= '|dimensions:width=' . $width . ',height=' . $height;
                    $validationMessage['setting_val.size']       = Lang::get($this->langFile . '.error-setting-size-favicon');
                    $validationMessage['setting_val.dimensions'] = Lang::get($this->langFile . '.error-setting-dimention-favicon');
                }

                $validatedData = $request->validate([
                    'id'          => 'required',
                    'setting_val' => $validationString,
                ], $validationMessage);
            }

            if ($request->id == $id) {
                DB::beginTransaction();
                try {
                    if ($record->is_multi_lang) {
                        $updateFlag = true;

                        foreach ($languageList as $language) {
                            $settingLang = AdminSettingDescription::where(['settings_id' => $id, 'language_id' => $language->id])->first();
                            if (!$settingLang) {
                                $settingLang = new AdminSettingDescription;
                            }

                            $settingValLang           = "setting_" . $id . "_" . $language->id;
                            $settingLang->settings_id = $id;
                            $settingLang->language_id = $language->id;
                            $settingLang->setting_key = $record->setting_key;
                            $settingLang->setting_val = $request->$settingValLang;
                            if (!$settingLang->save()) {
                                $updateFlag = false;
                            }
                        }

                        if ($updateFlag) {
                            DB::commit();
                            $status       = 'success';
                            $message_text = Lang::get($this->langFile . '.update-success');
                            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        } else {
                            DB::rollback();
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.update-error');
                            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
                        }
                    } else {
                        if ($record->id == 2) {
                            /*
                             * Logo Upload
                             */
                            $path = Storage::disk('local')->put(config('filepath.setting_app_logo'), $request->setting_val);

                            if ($path) {
                                Storage::disk('local')->delete($record->setting_val);
                                $record->setting_val = $path;
                            } else {
                                $status       = 'error';
                                $message_text = Lang::get($this->langFile . '.file-upload-error');
                                return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
                            }
                        } else if ($record->id == 3) {
                            /*
                             * Favicon Upload
                             */
                            $path = Storage::disk('local')->put(config('filepath.setting_favicon'), $request->setting_val);

                            if ($path) {
                                Storage::disk('local')->delete($record->setting_val);
                                $record->setting_val = $path;
                            } else {
                                $status       = 'error';
                                $message_text = Lang::get($this->langFile . '.file-upload-error');
                                return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
                            }
                        } else if ($record->type == 'file') {
                            /*
                             * Other File Upload
                             */
                            $path = Storage::disk('local')->put(config('filepath.setting_other'), $request->setting_val);

                            if ($path) {
                                Storage::disk('local')->delete($record->setting_val);
                                $record->setting_val = $path;
                            } else {
                                $status       = 'error';
                                $message_text = Lang::get($this->langFile . '.file-upload-error');
                                return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
                            }
                        } else {
                            $record->setting_val = $request->setting_val;
                        }

                        if ($record->save()) {
                            DB::commit();
                            $status       = 'success';
                            $message_text = Lang::get($this->langFile . '.update-success');
                            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        } else {
                            DB::rollback();
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.update-error');
                            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
                        }
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
                }
            } else {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.error');
            }
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
    }

    public function editSetting()
    {
        $languageList = Helper::getLanguageList();
        $radioValue   = config('admin.setting.cod');
        $settings     = AdminSettings::getAllData('', 'sorting', 'asc', 'ALL');
        return view('admin.general-setting.setting.edit_setting', compact('settings', 'languageList', 'radioValue'));
    }

    public function updateSetting(Request $request)
    {
        $settings = AdminSettings::getAllData('', 'sorting', 'asc', 'ALL');

        $validationRules   = [];
        $validationMessage = [];

        /*
         * Dynamic validation
         */
        foreach ($settings as $record) {
            if (!$record->is_multi_lang) {
                $validationRules['setting_' . $record->id]                 = ($record->is_require == 1) ? 'required' : 'nullable';
                $validationMessage['setting_' . $record->id . ".required"] = ($record->is_require == 1) ? Lang::get($this->langFile . '.error-setting-req-name') : '';

                if ($record->type == 'file' && $record->id != 25) {
                    /*
                     * Image file validation common
                     */
                    $validationRules['setting_' . $record->id]              .= "|image|mimes:jpeg,png,jpg,svg";
                    $validationMessage['setting_' . $record->id . '.image'] = Lang::get($this->langFile . '.error-setting-file-type');
                    $validationMessage['setting_' . $record->id . '.mimes'] = Lang::get($this->langFile . '.error-setting-file-mimes');
                } else if ($record->type == 'file' && $record->id == 25) {
                    /*
                     * Image file validation common
                     */
                    $validationRules['setting_' . $record->id]              .= "|image|mimes:png";
                    $validationMessage['setting_' . $record->id . '.image'] = Lang::get($this->langFile . '.error-setting-file-type');
                    $validationMessage['setting_' . $record->id . '.mimes'] = Lang::get($this->langFile . '.error-setting-file-mimes-png');
                }
                if ($record->id == 2) {
                    /*
                     * Logo, Dimention 174x148
                     */
                    $height = config('custom.app_logo_height');
                    $width  = config('custom.app_logo_width');

                    //$validationRules['setting_' . $record->id]                   .= '|dimensions:width=' . $width . ',height=' . $height;
                    $validationMessage['setting_' . $record->id . '.size']       = Lang::get($this->langFile . '.error-setting-size-favicon');
                    $validationMessage['setting_' . $record->id . '.dimensions'] = Lang::get($this->langFile . '.error-setting-dimention-favicon');
                }
                if ($record->id == 3) {
                    /*
                     * Favicon validation - Size 1KB, Dimention 16x16
                     */
                    $height = config('custom.favicon_height');
                    $width  = config('custom.favicon_width');

                    $validationRules['setting_' . $record->id]                   .= '|dimensions:width=' . $width . ',height=' . $height;
                    $validationMessage['setting_' . $record->id . '.size']       = Lang::get($this->langFile . '.error-setting-size-favicon');
                    $validationMessage['setting_' . $record->id . '.dimensions'] = Lang::get($this->langFile . '.error-setting-dimention-favicon');
                }
            }
        }

        /*
         * Language wise Field Validation
         */
        $languageList = Helper::getLanguageList();

        foreach ($languageList as $language) {
            foreach ($settings as $record) {
                if ($record->is_multi_lang) {
                    $validationRules['setting_' . $record->id . '_' . $language->id]                 = ($record->is_require == 1) ? 'required' : 'nullable';
                    $message_text                                                                    = str_replace("#field#", ucfirst($record->setting_key) . ' (' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-setting-req-field'));
                    $validationMessage['setting_' . $record->id . '_' . $language->id . ".required"] = ($record->is_require == 1) ? $message_text : '';
                }
            }
        }

        /*
         * Validattion
         */
        $validatedData = $request->validate($validationRules, $validationMessage);
        $formData      = $request->except("_token");
        $updateFlag    = true;
        DB::beginTransaction();
        try {
            foreach ($formData as $key => $val) {
                $key = explode("_", $key);
                if (isset($key[1])) {
                    $id      = $key[1];
                    $setting = AdminSettings::find($id);
                    if ($setting && $setting->can_edit) {
                        if ($setting->is_multi_lang) {
                            foreach ($languageList as $language) {
                                $settingLang = AdminSettingDescription::where(['settings_id' => $id, 'language_id' => $language->id])->first();
                                if (!$settingLang) {
                                    $settingLang = new AdminSettingDescription;
                                }

                                $settingValLang           = "setting_" . $id . "_" . $language->id;
                                $settingLang->settings_id = $id;
                                $settingLang->language_id = $language->id;
                                $settingLang->setting_key = $setting->setting_key;
                                $settingLang->setting_val = $request->$settingValLang;
                                if (!$settingLang->save()) {
                                    $updateFlag = false;
                                }
                            }
                        } else {
                            if ($setting->type == 'file') {
                                if (isset($key[1]) && ($key[1] == 2)) {
                                    $id = $key[1];
                                    /*
                                     * Logo Upload
                                     */

                                    $path = Storage::disk('local')->put(config('filepath.setting_app_logo'), $val);
                                } else if (isset($key[1]) && ($key[1] == 3)) {
                                    $id = $key[1];
                                    /*
                                     * FavIcon Upload
                                     */

                                    $path = Storage::disk('local')->put(config('filepath.setting_favicon'), $val);
                                } else {
                                    $path = Storage::disk('local')->put(config('filepath.setting_other'), $val);
                                }

                                if ($path) {
                                    Storage::disk('local')->delete($setting->setting_val);
                                    $setting->setting_val = $path;
                                    if (!$setting->save()) {
                                        $updateFlag = false;
                                    }
                                } else {
                                    $status       = 'error';
                                    $message_text = Lang::get($this->langFile . '.file-upload-error');
                                    return redirect()->route('admin')->with($status, $message_text);
                                }
                            } else {
                                $setting->setting_val = $val;
                                if (!$setting->save()) {
                                    $updateFlag = false;
                                }
                            }
                        }
                    } else {
                        $updateFlag = false;
                    }
                }
            }
        } catch (Throwable $e) {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
        }

        if ($updateFlag) {
            DB::commit();
            $status       = 'success';
            $message_text = Lang::get($this->langFile . '.update-success');
            $message_text = str_replace("#module#", (Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.general-setting.setting.index')->with($status, $message_text);
    }


    public function discount($type = false, $id = false)
    {

        if ($type == false) {
            $activeDiscount = AdminDiscount::where('is_active', 1)->first();
            if ($activeDiscount) {
                $type =  $activeDiscount->type;
            } else {
                $type =  1;
            }
        } else {
            if (empty($id)) {
                // Set all rows' is_active to 0
                AdminDiscount::query()->update(['is_active' => 0]);

                // Set is_active to 1 for the specified $type
                AdminDiscount::where('type', $type)->update(['is_active' => 1]);
            }
        }
        $discounts = AdminDiscount::where('type', $type)->get();
        if ($id) {
            $singleRecord = AdminDiscount::where('type', $type)->where('id', $id)->first();


            $catDiscCat = CategoryDiscount::where('discount_id', $id)->where('type', $type)->pluck('category_id')->toArray();
            $catDiscBrand = CategoryDiscount::where('discount_id', $id)->where('type', $type)->pluck('brand_id')->toArray();
        } else {
            $singleRecord = new \stdClass();
            $singleRecord->id  = 0;
            $singleRecord->is_discount  = 0;
            $singleRecord->type = '';
            $singleRecord->discount = '';
            $singleRecord->start_date = date('Y-m-d');
            $singleRecord->end_date = date('Y-m-d');

            $catDiscCat = [];
            $catDiscBrand = [];
        }
        $category        = AdminCategory::getCategoriesFront();
        $brand        = AdminBrand::getData(1);
        $url = route('admin.general-setting.setting.discount');

        return view("admin.general-setting.discount", compact('discounts', 'singleRecord', 'type', 'brand', 'category', 'url', 'catDiscCat', 'catDiscBrand', 'id'));
    }

    public function updateDiscount(Request $request)
    {
        $validationRule = [];
        $validationMessage = [];

        // Validation rules and messages
        if ($request->is_discount == 1) {
            $validationRule['discount'] = 'required|integer|between:1,100';
            $validationMessage['discount.required'] = 'This field is required';
            $validationMessage['discount.between'] = 'Discount must be between 1% to 100%';
            $validationRule['from_date'] = 'required|date';
            $validationMessage['from_date.required'] = 'This field is required';
            $validationRule['to_date'] = 'required|date|after_or_equal:from_date';
            $validationMessage['to_date.required'] = 'This field is required';
            $validationMessage['to_date.after_or_equal'] = 'End date must be after or equal to start date.';
        }

        if ($request->discount_on == 1 || $request->discount_on == 3) {
            $validationRule['brand_id'] = 'required|array';
            $validationMessage['brand_id.required'] = 'This field is required';
        }

        if ($request->discount_on == 2 || $request->discount_on == 3) {
            $validationRule['category_id'] = 'required|array';
            $validationMessage['category_id.required'] = 'This field is required';
        }

        $validatedData = $request->validate($validationRule, $validationMessage);

        $fromDate = date("Y-m-d", strtotime($request->from_date));
        $toDate = date("Y-m-d", strtotime($request->to_date));

        // Check for duplicate brands or categories
        $duplicateBrands = [];
        $duplicateCategories = [];

        if (in_array($request->discount_on, [1, 3])) {
            foreach ($request->brand_id as $brandId) {
                $exists = AdminDiscount::where('start_date', '<=', $toDate)
                    ->where('end_date', '>=', $fromDate)
                    ->where('id', '!=', $request->discount_id)
                    ->whereHas('categoryDiscounts', function ($query) use ($brandId) {
                        $query->where('brand_id', $brandId);
                    })->exists();

                if ($exists) {
                    $duplicateBrands[] = AdminBrandDescription::where('brand_id', $brandId)->value('brand_name');
                }
            }
        }

        if (in_array($request->discount_on, [2, 3])) {
            foreach ($request->category_id as $categoryId) {
                $exists = AdminDiscount::where('start_date', '<=', $toDate)
                    ->where('end_date', '>=', $fromDate)
                    ->where('id', '!=', $request->discount_id)
                    ->whereHas('categoryDiscounts', function ($query) use ($categoryId) {
                        $query->where('category_id', $categoryId);
                    })->exists();

                if ($exists) {
                    $duplicateCategories[] = AdminCategoryDescription::where('categories_id', $categoryId)->value('categories_name');
                }
            }
        }

        if (!empty($duplicateBrands) || !empty($duplicateCategories)) {
            $errorMessages = '';
            if (!empty($duplicateBrands)) {
                $errorMessages = 'The following brands already have discounts: ' . implode(', ', $duplicateBrands);
            }
            if (!empty($duplicateCategories)) {
                $errorMessages = 'The following categories already have discounts: ' . implode(', ', $duplicateCategories);
            }

            return redirect()->back()->with('error', $errorMessages)->withInput();
        }

        $model = AdminDiscount::find($request->discount_id);

        if (empty($model)) {
            $model = new AdminDiscount();
        }

        $model->is_discount = $request->is_discount;
        $model->discount = $request->discount;
        $model->is_active = 1;
        $model->type = $request->discount_on;
        $model->start_date = $fromDate;
        $model->end_date = $toDate;

        if ($model->save()) {
            $discount_id = $model->id;

            CategoryDiscount::where('discount_id', $request->discount_id)->delete();

            $productIds = [];

            if ($request->discount_on == 1) {
                foreach ($request->brand_id as $brandId) {
                    $categoryDiscount = new CategoryDiscount();
                    $categoryDiscount->discount_id = $discount_id;
                    $categoryDiscount->brand_id = $brandId;
                    $categoryDiscount->type = 1;
                    $categoryDiscount->save();

                    $productIds = array_merge($productIds, AdminProduct::where('brand_id', $brandId)->pluck('id')->toArray());
                }
            } elseif ($request->discount_on == 2) {
                foreach ($request->category_id as $categoryId) {
                    $categoryDiscount = new CategoryDiscount();
                    $categoryDiscount->discount_id = $discount_id;
                    $categoryDiscount->category_id = $categoryId;
                    $categoryDiscount->type = 2;
                    $categoryDiscount->save();

                    $productIds = array_merge($productIds, AdminProduct::where('category_id', $categoryId)->pluck('id')->toArray());
                }
            } elseif ($request->discount_on == 3) {
                foreach ($request->category_id as $categoryId) {
                    foreach ($request->brand_id as $brandId) {
                        $categoryDiscount = new CategoryDiscount();
                        $categoryDiscount->discount_id = $discount_id;
                        $categoryDiscount->category_id = $categoryId;
                        $categoryDiscount->brand_id = $brandId;
                        $categoryDiscount->type = 3;
                        $categoryDiscount->save();

                        $productIds = array_merge($productIds, AdminProduct::where('brand_id', $brandId)
                            ->where('category_id', $categoryId)
                            ->pluck('id')
                            ->toArray());
                    }
                }
            }

            AdminProduct::whereIn('id', $productIds)->update(['is_category_discount' => 1]);
        }

        return redirect()->route('admin.general-setting.setting.discount')
            ->with('success', 'Record Updated Successfully');
    }




    public function changeStatus($id, $active)
    {
        $singleRecord = AdminDiscount::find($id);

        if ($singleRecord && in_array($active, [0, 1])) {
            DB::beginTransaction();

            try {
                $singleRecord->is_discount = $active;

                if ($singleRecord->save()) {
                    // Handle product updates
                    $productIds = [];

                    if ($singleRecord->type == 1) { // Brand discount
                        $brandIds = CategoryDiscount::where('discount_id', $id)->pluck('brand_id')->toArray();
                        $productIds = AdminProduct::whereIn('brand_id', $brandIds)->pluck('id')->toArray();
                    } elseif ($singleRecord->type == 2) { // Category discount
                        $categoryIds = CategoryDiscount::where('discount_id', $id)->pluck('category_id')->toArray();
                        $productIds = AdminProduct::whereIn('category_id', $categoryIds)->pluck('id')->toArray();
                    } elseif ($singleRecord->type == 3) { // Brand + Category discount
                        $categoryDiscounts = CategoryDiscount::where('discount_id', $id)->get();
                        foreach ($categoryDiscounts as $cd) {
                            $productIds = array_merge($productIds, AdminProduct::where('brand_id', $cd->brand_id)
                                ->where('category_id', $cd->category_id)
                                ->pluck('id')
                                ->toArray());
                        }
                    }

                    // Update is_category_discount for related products
                    AdminProduct::whereIn('id', $productIds)->update(['is_category_discount' => $active]);

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

            return redirect()->back()->with($status, $message_text);
        }
    }




    public function delete($id)
    {

        if ($id) {
            $record = AdminDiscount::find($id);

            if ($record) {
                DB::beginTransaction();
                try {
                    $subDeleteFlag = true;
                    $subRecord     = $record->categoryDiscounts();
                    foreach ($subRecord as $sub) {
                        if (!$sub->delete()) {
                            $subDeleteFlag = false;
                        }
                    }
                    /*
                             * While Delete Change Sorting
                             */
                    if ($subDeleteFlag && $record->delete()) {
                        DB::commit();
                        $status       = 'success';
                        $message_text = 'Record Deleted Successfully';
                    } else {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = 'Something went wrong';
                    }
                } catch (Throwable $e) {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = 'Something went wrong';
                }
            } else {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->back()->with($status, $message_text);
    }
}

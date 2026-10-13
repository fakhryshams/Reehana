<?php

namespace App\Http\Controllers\Admin\ShippingMethod;

use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;
use App\AdminModel\AdminLanguage;
use Illuminate\Http\Request;
use App\AdminModel\ShippingMethod\AdminShippingMethod;
use App\AdminModel\ShippingMethod\AdminShippingMethodDescription;
/*
 * Other
 */
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

class ShippingMethodController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.shipping_method';

    public function index(Request $request)
    {

        /*
         * Get SortKey and SortOrder from helper
         */


        if (isset($request->is_default))
        {
            $shippingMethod = AdminShippingMethod::where('id', $request->is_default)->where('is_default', 0)->first();

            if ($shippingMethod)
            {
                DB::beginTransaction();
                AdminShippingMethod::where('is_default', 1)
                        ->update(['is_default' => 0]);
                $shippingMethod->is_default = 1;
                if ($shippingMethod->save())
                {
                    DB::commit();
                    return redirect()->route('admin.shipping.index');
                }
                else
                {
                    DB::rollback();
                }
            }
        }



        $sorting    = Helper::getOrderColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;

        $shippingMethod = new AdminShippingMethod();
        $shippingMethod = $shippingMethod->getAllData($searchText, $sortKey, $sortVal);

        return view('admin.shipping-method.index', compact('shippingMethod', 'sortKey', 'sortVal', 'searchText'));
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $shippingMethod = AdminShippingMethod::find($id);
        if ($shippingMethod)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {

                return view("admin.shipping-method.edit", compact('languageList', 'shippingMethod'));
            }
            else
            {
                return redirect()->route('admin.shipping.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.shipping.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        $languageList = Helper::getLanguageList();

        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        foreach ($languageList as $language)
        {
            $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-shipping-req-shipping-name'));
            $validationRule['shipping_name_' . $language->id]                  = 'required';
            $validationMessage['shipping_name_' . $language->id . '.required'] = $message_text;
        }


        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            $flag  = true;
            $model = AdminShippingMethod::find($id);

            if ($request->price)
            {
                $model->price = $request->price; //Main 
            }


            /*
             * Save Main Record
             */
            if ($model->save())
            {

                foreach ($languageList as $language)
                {
                    $subModel = AdminShippingMethodDescription::getDataByLanguageID($id, $language->id);
                    if ($subModel)
                    {
                        $shippingName = 'shipping_name_' . $language->id;

                        $subModel->shipping_name = $request->$shippingName;
                    }
                    else
                    {
                        $subModel                      = new AdminShippingMethodDescription();
                        $shippingName                  = 'shipping_name_' . $language->id;
                        $subModel->shipping_methods_id = $model->id;
                        $subModel->shipping_name       = $request->$shippingName;
                        $subModel->language_id         = $language->id;
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
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.shipping.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminShippingMethod::find($id);
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

            return redirect()->route('admin.shipping.index')->with($status, $message_text);
        }
    }

}

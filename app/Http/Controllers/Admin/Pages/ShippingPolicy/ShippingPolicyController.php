<?php

namespace App\Http\Controllers\Admin\Pages\ShippingPolicy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\GeneralSetting\ShippingPolicy\ShippingPolicy;
use App\AdminModel\GeneralSetting\ShippingPolicy\ShippingPolicyDescription;

class ShippingPolicyController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.shipping-policy';

    /*
     * open insert page
     */

    public function create()
    {
        $languageList   = Helper::getLanguageList();
        $shippingPolicy = ShippingPolicy::all()->first();
        if ($languageList)
        {
            return view("admin.page.shipping-policy.add", compact('languageList', 'shippingPolicy'));
        }
        else
        {
            return redirect()->route('admin.page.shipping-policy.add')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function store(Request $request)
    {
        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-shipping-policy'));
            $validationRule['shipping_policy_' . $language->id]                  = 'required';
            $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try
        {
            $model = new ShippingPolicy();

            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $flag = true;
                foreach ($languageList as $language)
                {
                    $subModel                     = new ShippingPolicyDescription();
                    $description                  = 'shipping_policy_' . $language->id;
                    $subModel->shipping_policy_id = $model->id;
                    $subModel->language_id        = $language->id;
                    $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.shipping-policy.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function update(Request $request)
    {
        $id           = $request->ID;
        $model        = ShippingPolicy::find($id);
        $languageList = Helper::getLanguageList();
        if ($model)
        {
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-shipping-policy'));
                $validationRule['shipping_policy_' . $language->id]                  = 'required';
                $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
            }
            $validatedData = $request->validate($validationRule, $validationMessage);
            DB::beginTransaction();
            try
            { /*
             * Save Main Record
             */
                if ($model->save())
                {
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel = ShippingPolicyDescription::getDataByLanguageID($id, $language->id);

                        $description                  = 'shipping_policy_' . $language->id;
                        $subModel->shipping_policy_id = $model->id;
                        $subModel->language_id        = $language->id;
                        $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.shipping-policy.add')->with($status, $message_text);
    }

    /*
     * Privacy Policy
     */

    public function createPrivacyPolicy()
    {
        $this->module = '.privacy-policy';

        $languageList   = Helper::getLanguageList();
        $shippingPolicy = ShippingPolicy::find(2);
        if ($languageList)
        {
            return view("admin.page.privacy-policy.add", compact('languageList', 'shippingPolicy'));
        }
        else
        {
            return redirect()->route('admin.page.privacy-policy.add')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function storePrivacyPolicy(Request $request)
    {
        $this->module = '.privacy-policy';

        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-privacy-policy'));
            $validationRule['shipping_policy_' . $language->id]                  = 'required';
            $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try
        {
            $model     = new ShippingPolicy();
            $model->id = 2;
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $flag = true;
                foreach ($languageList as $language)
                {
                    $subModel                     = new ShippingPolicyDescription();
                    $description                  = 'shipping_policy_' . $language->id;
                    $subModel->shipping_policy_id = $model->id;
                    $subModel->language_id        = $language->id;
                    $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.privacy-policy.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function updatePrivacyPolicy(Request $request)
    {
        $this->module = '.privacy-policy';

        $id           = $request->ID;
        $model        = ShippingPolicy::find($id);
        $languageList = Helper::getLanguageList();
        if ($model)
        {
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-shipping-policy'));
                $validationRule['shipping_policy_' . $language->id]                  = 'required';
                $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
            }
            $validatedData = $request->validate($validationRule, $validationMessage);
            DB::beginTransaction();
            try
            { /*
             * Save Main Record
             */
                if ($model->save())
                {
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel = ShippingPolicyDescription::getDataByLanguageID($id, $language->id);

                        $description                  = 'shipping_policy_' . $language->id;
                        $subModel->shipping_policy_id = $model->id;
                        $subModel->language_id        = $language->id;
                        $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.privacy-policy.add')->with($status, $message_text);
    }

    /*
     * Terms
     */

    public function createTerms()
    {
        $this->module = '.terms';

        $languageList   = Helper::getLanguageList();
        $shippingPolicy = ShippingPolicy::find(3);
        if ($languageList)
        {
            return view("admin.page.terms.add", compact('languageList', 'shippingPolicy'));
        }
        else
        {
            return redirect()->route('admin.page.terms.add')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function storeTerms(Request $request)
    {
        $this->module = '.terms';

        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-terms'));
            $validationRule['shipping_policy_' . $language->id]                  = 'required';
            $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try
        {
            $model     = new ShippingPolicy();
            $model->id = 3;
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $flag = true;
                foreach ($languageList as $language)
                {
                    $subModel                     = new ShippingPolicyDescription();
                    $description                  = 'shipping_policy_' . $language->id;
                    $subModel->shipping_policy_id = $model->id;
                    $subModel->language_id        = $language->id;
                    $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.terms.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function updateTerms(Request $request)
    {
        $this->module = '.terms';

        $id           = $request->ID;
        $model        = ShippingPolicy::find($id);
        $languageList = Helper::getLanguageList();
        if ($model)
        {
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                                        = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-shipping-policy'));
                $validationRule['shipping_policy_' . $language->id]                  = 'required';
                $validationMessage['shipping_policy_' . $language->id . '.required'] = $message_text;
            }
            $validatedData = $request->validate($validationRule, $validationMessage);
            DB::beginTransaction();
            try
            { /*
             * Save Main Record
             */
                if ($model->save())
                {
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel = ShippingPolicyDescription::getDataByLanguageID($id, $language->id);

                        $description                  = 'shipping_policy_' . $language->id;
                        $subModel->shipping_policy_id = $model->id;
                        $subModel->language_id        = $language->id;
                        $subModel->shipping_policy    = $request->$description;
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
        return redirect()->route('admin.page.terms.add')->with($status, $message_text);
    }

}

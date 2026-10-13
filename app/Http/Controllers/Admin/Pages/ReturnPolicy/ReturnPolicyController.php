<?php

namespace App\Http\Controllers\Admin\Pages\ReturnPolicy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use App\AdminModel\GeneralSetting\ReturnPolicy\AdminReturnPolicy;
use App\AdminModel\GeneralSetting\ReturnPolicy\AdminReturnPolicyDescription;

class ReturnPolicyController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.return-policy';
    
    /*
     * open insert page
     */

    public function create()
    {
        $languageList = Helper::getLanguageList();
        $returnPolicy = AdminReturnPolicy::all()->first();
        if ($languageList)
        {
            return view("admin.page.return-policy.add", compact('languageList', 'returnPolicy'));
        }
        else
        {
            return redirect()->route('admin.page.return-policy.add')->with('error', Lang::get($this->langFile . 'language-not-found'));
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
            $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-return-policy'));
            $validationRule['return_policy_' . $language->id]                  = 'required';
            $validationMessage['return_policy_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try
        {
            $model = new AdminReturnPolicy();
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $flag = true;
                foreach ($languageList as $language)
                {
                    $subModel                   = new AdminReturnPolicyDescription();
                    $description                = 'return_policy_' . $language->id;
                    $subModel->return_policy_id = $model->id;
                    $subModel->language_id      = $language->id;
                    $subModel->return_policy    = $request->$description;
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
        return redirect()->route('admin.page.return-policy.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function update(Request $request)
    {
        $id           = $request->ID;
        $model        = AdminReturnPolicy::find($id);
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
                $message_text                                                      = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-req-return-policy'));
                $validationRule['return_policy_' . $language->id]                  = 'required';
                $validationMessage['return_policy_' . $language->id . '.required'] = $message_text;
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
                        $subModel = AdminReturnPolicyDescription::getDataByLanguageID($id, $language->id);

                        $description                = 'return_policy_' . $language->id;
                        $subModel->return_policy_id = $model->id;
                        $subModel->language_id      = $language->id;
                        $subModel->return_policy    = $request->$description;
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
        return redirect()->route('admin.page.return-policy.add')->with($status, $message_text);
    }

}

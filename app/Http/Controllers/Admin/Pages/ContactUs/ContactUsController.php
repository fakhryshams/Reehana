<?php

namespace App\Http\Controllers\Admin\Pages\ContactUs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\GeneralSetting\ContactUs\ContactUs;
use App\AdminModel\GeneralSetting\ContactUs\ContactUsDescription;

class ContactUsController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.contact_us';

    /*
     * open insert page
     */

    public function create()
    {
        $languageList = Helper::getLanguageList();
        $contactUs    = ContactUs::all()->first();

        if ($languageList)
        {
            $isActive = config('admin.product.isActive');
            return view("admin.page.contact-us.add", compact('languageList', 'isActive', 'contactUs'));
        }
        else
        {
            return redirect()->route('admin.page.contact-us.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
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

        DB::beginTransaction();
        try
        {
            $model = new ContactUs();

            /*
             * Save Main Record
             */

            $model->instagram_url = $request->instagram_url;
            $model->whatsapp_url = $request->whatsapp_url;
            $model->email         = $request->email;
            $model->address       = $request->address;
            $model->phone         = $request->phone;
            
            if ($model->save())
            {
                $flag = true;
                
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

        return redirect()->route('admin.page.contact-us.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function update(Request $request)
    {
        $id           = $request->ID;
        $model        = ContactUs::find($id);
        $languageList = Helper::getLanguageList();
        if ($model)
        {

            $model->whatsapp_url = $request->whatsapp_url;
            $model->instagram_url = $request->instagram_url;
            $model->email         = $request->email;
            $model->address       = $request->address;
            $model->phone         = $request->phone;
            DB::beginTransaction();
            try
            {
                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    $flag = true;
                    
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
        return redirect()->route('admin.page.contact-us.add')->with($status, $message_text);
    }

}

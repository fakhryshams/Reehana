<?php

namespace App\Http\Controllers\Admin\Language;

use App\AdminModel\AdminLanguage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

class LanguageController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.language';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting      = Helper::getOrderColumn($request);
        $sortKey      = $sorting['sortKey'];
        $sortVal      = $sorting['sortVal'];
        $searchText   = $request->q;
        $pageLength   = $request->pageLength;
        $status       = "";
        $message_text = "";
        if ($request->languages_id)
        {
            $languageDefault = AdminLanguage::where('id', $request->languages_id)->where('is_default', 0)->first();
            DB::beginTransaction();
            try
            {
                if ($languageDefault)
                {
                    AdminLanguage::where('is_default', 1)->update(['is_default' => 0]);
                    $languageDefault->is_default = 1;
                    if ($languageDefault->save())
                    {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.default-language-changed-message');
                        //  $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
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
        }

        $languages = AdminLanguage::getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.language.index', compact('languages', 'sortKey', 'sortVal', 'searchText', 'status', 'message_text', 'pageLength'));
    }

    public function create()
    {
        return view("admin.language.add");
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:admin_languages,name,NULL,id,deleted_at,NULL|min:3|max:100',
            'code' => 'required|unique:admin_languages,code,NULL,id,deleted_at,NULL|size:2',
                ], [
            'name.required' => Lang::get($this->langFile . '.error-language-req-name'),
            'name.unique'   => Lang::get($this->langFile . '.error-language-unique-name'),
            'name.min'      => Lang::get($this->langFile . '.error-language-min-name'),
            'name.max'      => Lang::get($this->langFile . '.error-language-max-name'),
            'code.required' => Lang::get($this->langFile . '.error-language-req-code'),
            'code.unique'   => Lang::get($this->langFile . '.error-language-unique-code'),
            'code.size'     => Lang::get($this->langFile . '.error-language-size-code'),
        ]);
        DB::beginTransaction();

        try
        {
            $language            = new AdminLanguage();
            $language->name      = strtolower($request->name);
            $language->code      = strtolower($request->code);
            $language->direction = $request->direction;

            //   dd($language);
            if ($request->image)
            {
                $path = Storage::disk('local')->put('public/language', $request->image);
                if ($path)
                {
                    Storage::disk('local')->delete($request->image);
                    $language->image = $path;
                }
                else
                {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.file-upload-error');
                    return redirect()->route('admin.language.index')->with($status, $message_text);
                }
            }


            if ($language->save())
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
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.language.index')->with($status, $message_text);
        dd($request->all());
    }

    public function edit(AdminLanguage $language, $id)
    {
        $language = $language->find($id);

        if ($language)
        {
            return view("admin.language.edit", compact('language'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
            return redirect()->route('admin.language.index')->with($status, $message_text);
        }
    }

    public function update(Request $request, $id)
    {
//        dd($request->all());
        $validatedData = $request->validate([
            'name' => "required|unique:admin_languages,name,$id,id,deleted_at,NULL|min:3|max:100",
            'code' => "required|unique:admin_languages,code,$id,id,deleted_at,NULL|size:2",
                ], [
            'name.required' => Lang::get($this->langFile . '.error-language-req-name'),
            'name.unique'   => Lang::get($this->langFile . '.error-language-unique-name'),
            'name.min'      => Lang::get($this->langFile . '.error-language-min-name'),
            'name.max'      => Lang::get($this->langFile . '.error-language-max-name'),
            'code.required' => Lang::get($this->langFile . '.error-language-req-code'),
            'code.unique'   => Lang::get($this->langFile . '.error-language-unique-code'),
            'code.size'     => Lang::get($this->langFile . '.error-language-size-code'),
        ]);

        $language = AdminLanguage::find($id);
        if ($language && $request->id == $id)
        {
            DB::beginTransaction();

            try
            {
                $language->name      = strtolower($request->name);
                $language->code      = strtolower($request->code);
                $language->direction = $request->direction;
                if ($request->has('image'))
                {
                    $path = Storage::disk('local')->put('public/language', $request->image);
                    if ($path)
                    {
                        Storage::disk('local')->delete($language->image);
                        $language->image = $path;
                    }
                    else
                    {
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.file-upload-error');
                        return redirect()->route('admin.language.index')->with($status, $message_text);
                    }
                }

                if ($language->save())
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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('admin.language.index')->with($status, $message_text);
    }

    public function setDefault(Request $request)
    {
        AdminLanguage::where('is_default', 1)->update(['is_default' => 0]);
        AdminLanguage::where('id', '=', $request->languages_id)->update(['is_default' => 1]);
    }

}

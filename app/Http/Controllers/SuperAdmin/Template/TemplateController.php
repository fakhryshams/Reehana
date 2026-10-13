<?php

namespace App\Http\Controllers\SuperAdmin\Template;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\SuperAdmin\SadminTemplate;
use App\SuperAdmin\SadminTemplateDescription;
use App\AdminModel\AdminLanguage;
use DB;
use Helper;

class TemplateController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.template';

    public function index(Request $request)
    {
        $sorting    = Helper::getOrderColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;

        $sadminTemplate = new SadminTemplate();
        $template       = $sadminTemplate->getAllData($searchText, $sortKey, $sortVal);

        return view("super-admin.template.index", compact('template', 'sortKey', 'sortVal', 'searchText'));
    }

    public function addTemplate()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            $templateData = new SadminTemplate();
            $moduleName   = config('template.module');
            $keywords     = [];
            foreach ($moduleName as $module)
            {
                $keywords[$module] = config('template.' . $module);
            }
            return view("super-admin.template.add", compact('templateData', 'moduleName', 'keywords', 'languageList'));
        }
        else
        {
            return redirect()->route('super-admin.template.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function editTemplate(Request $request, $id)
    {

        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            $templateData = SadminTemplate::find($id);
            $moduleName   = config('template.module');
            $keywords     = [];
            foreach ($moduleName as $module)
            {
                $keywords[$module] = config('template.' . $module);
            }
            if ($templateData)
            {
                return view("super-admin.template.add", compact('templateData', 'moduleName', 'keywords', 'languageList'));
            }
            else
            {
                $status       = 'error';
                $message_text = 'error';
            }

            return redirect()->route('super-admin.template.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
        }
        else
        {
            return redirect()->route('super-admin.template.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function previewTemplate($id)
    {

        $templateData = SadminTemplateDescription::where('template_id', $id)->first();
        if ($templateData)
        {
            return view("super-admin.template.preview", compact('templateData'));
        }
        else
        {
            $status       = 'error';
            $message_text = 'error';
        }
        return redirect()->route('super-admin.template.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

    public function saveTemplate(Request $request)
    {



        $successMessage = 'template-add-success';

        $languageList = Helper::getLanguageList();

        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        foreach ($languageList as $language)
        {



            $languageID = $language->id;
            if (isset($request->id) && $request->id)
            {
                $validationTemplateName = 'required|unique:sadmin_template_description,template_name,' . $request->id . ',template_id,deleted_at,NULL';
                $successMessage         = 'template-update-success';
            }
            else
            {
                $validationTemplateName = "required|unique:sadmin_template_description,template_name,NULL,id,deleted_at,NULL";
            }


            $template_text                                                     = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-template-req-template-name'));
            $template_data                                                     = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-template-req-template-data'));
            $validationRule['template_name_' . $language->id]                  = $validationTemplateName;
            $validationRule['template_data_' . $language->id]                  = 'required';
            $validationMessage['template_name_' . $language->id . '.required'] = $template_text;
            $validationMessage['template_name_' . $language->id . '.unique']   = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-template-unique-template-name'));
            $validationMessage['template_data_' . $language->id . '.required'] = $template_data;
        }


        $validationRule['module']                       = 'required';
        $validationRule['meta_description']             = 'required';
        $validationRule['meta_keyword']                 = 'required';
        $validationRule['meta_title']                   = 'required';
        $validationMessage['meta_description.required'] = Lang::get($this->langFile . '.error-template-req-meta-descriptione');
        $validationMessage['meta_keyword.required']     = Lang::get($this->langFile . '.error-template-req-meta-keyword');
        $validationMessage['meta_title.required']       = Lang::get($this->langFile . '.error-template-req-meta-title');

        $validatedData = $request->validate($validationRule, $validationMessage);


        try
        {

            DB::beginTransaction();
            $flag                        = true;
            $tableData                   = SadminTemplate::firstOrNew(['id' => $request->id]);
            //  $tableData->fill($request->except('_token'));
            $tableData->is_active        = isset($request->is_active) ? 1 : 0;
            $tableData->module           = $request->module;
            $tableData->meta_description = $request->meta_description;
            $tableData->meta_keyword     = $request->meta_keyword;
            $tableData->meta_title       = $request->meta_title;


            if ($tableData->save())
            {


                foreach ($languageList as $language)
                {
                    $subModel = new SadminTemplateDescription();
                    if ($request->id)
                    {
                        $subModel = $subModel->getDataByLanguageID($request->id, $language->id);
                    }

                    $templateName = 'template_name_' . $language->id;
                    $templateData = 'template_data_' . $language->id;

                    $subModel->template_id   = $tableData->id;
                    $subModel->language_id   = $language->id;
                    $subModel->template_name = $request->$templateName;
                    $subModel->template_data = $request->$templateData;

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

        return redirect()->route('super-admin.template.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

    public function deleteTemplate(Request $request)
    {

        $this->langFile = $this->langFile;
        $deleteData     = SadminTemplate::find($request->delete_id);
        if ($deleteData)
        {
            try
            {
                DB::beginTransaction();

                $deleteData = SadminTemplate::find($request->delete_id)->delete();

                if ($deleteData)
                {
                    $status       = 'success';
                    DB::commit();
                    $message_text = 'template-info-delete-success';
                }
                else
                {
                    $status       = 'error';
                    $message_text = 'error';
                    DB::rollback();
                }
            }
            catch (Throwable $e)
            {

                $status       = 'error';
                $message_text = 'error';
                DB::rollback();
            }
        }
        else
        {
            $status       = 'error';
            $message_text = 'error';
        }
        return redirect()->route('super-admin.template.index')->with($status, Lang::get($this->langFile . '.' . $message_text));
    }

}

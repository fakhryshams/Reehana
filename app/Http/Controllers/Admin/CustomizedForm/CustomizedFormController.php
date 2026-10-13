<?php

namespace App\Http\Controllers\Admin\CustomizedForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\AdminModel\AdminCustomizedForm;
/*
 * Other
 */
use Helper;
use DB;
//Export Excel
use App\Exports\CustomizedFormExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomizedFormController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.contact_us';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting           = Helper::getOrderColumn($request);
        $sortKey           = $sorting['sortKey'];
        $sortVal           = $sorting['sortVal'];
        $searchText        = $request->q;
        $pageLength        = $request->pageLength;
        $requestTypeFilter = $request->request_type;

        $data = new AdminCustomizedForm();
        $data = $data->getAllData($searchText, $sortKey, $sortVal, $pageLength, $requestTypeFilter);

        $requestType = config('custom.customized_form_request_type');
        return view('admin.customized-form.index', compact('data', 'sortKey', 'sortVal', 'searchText', 'pageLength', 'requestType', 'requestTypeFilter'));
    }

    public function exportToExcel()
    {
        return Excel::download(new CustomizedFormExport(), 'Customized-Form-Data-' . date("Y-m-d-H-i-s") . '.xlsx');
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = AdminCustomizedForm::find($id);

            if ($record)
            {
                DB::beginTransaction();

                try
                {
                    if ($record->delete())
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
        return redirect()->route('admin.customized-form.index')->with($status, $message_text);
    }

    public function view($id)
    {
        if ($id)
        {
            $record = AdminCustomizedForm::find($id);

            if ($record)
            {
                return view('admin.customized-form.view', compact('record'));
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
        return redirect()->route('admin.customized-form.index')->with($status, $message_text);
    }

}

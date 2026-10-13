<?php

namespace App\Http\Controllers\Admin\ContactUs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\AdminModel\AdminContactUs;
/*
 * Other
 */
use Helper;
use DB;
//Export Excel
use App\Exports\ContactUsExport;
use Maatwebsite\Excel\Facades\Excel;

class ContactUsController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.contact_us';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;

        $data = new AdminContactUs();
        $data = $data->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.contact-us.index', compact('data', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function exportToExcel()
    {
        return Excel::download(new ContactUsExport(), 'Contact-Us-Data-' . date("Y-m-d-H-i-s") . '.xlsx');
    }
    
    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = AdminContactUs::find($id);

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
        return redirect()->route('admin.contact-us.index')->with($status, $message_text);
    }

}

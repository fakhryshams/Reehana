<?php

namespace App\Http\Controllers\Admin\Master\CourierCompany;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\CourierCompany\CourierCompany;

class CourierCompanyController extends Controller
{

    //
    private $langFile = 'admin';
    private $module   = '.courier-company';

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

        $courier = new CourierCompany();
        $courier = $courier->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.master.courier.index', compact('courier', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        return view("admin.master.courier.add");
    }

    public function store(Request $request)
    {

        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        $validationRule = [
            'company_name' => "required",
            'link'         => 'required|url',
        ];


        $validationMessage = [
            'company_name.required' => Lang::get($this->langFile . '.error-req-company'),
            'link.required'         => Lang::get($this->langFile . '.error-slider-req-link'),
            'link.url'              => Lang::get($this->langFile . '.error-url-link'),
        ];


        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model               = new CourierCompany();
            $model->company_name = $request->company_name;
            $model->link         = $request->link;

            /*
             * main save color
             */
            if ($model->save())
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

        return redirect()->route('admin.master.courier-company.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = CourierCompany::find($id);
        if ($singleRecord)
        {
            return view("admin.master.courier.edit", compact('singleRecord'));
        }
        return redirect()->route('admin.master.courier-company.index')->with($status, $message_text);
    }

    public function update(Request $request, $id)
    {
        $model = CourierCompany::find($id);
        if ($model)
        {
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];
            $validationRule    = [
                'company_name' => "required",
                'link'         => 'required|url',
            ];

            $validationMessage = [
                'company_name.required' => Lang::get($this->langFile . '.error-req-company'),
                'link.required'         => Lang::get($this->langFile . '.error-slider-req-link'),
                'link.url'              => Lang::get($this->langFile . '.error-url-link'),
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            try
            {
                DB::beginTransaction();

                $model->company_name = $request->company_name;
                $model->link         = $request->link;

                if ($model->save())
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
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.master.courier-company.index')->with($status, $message_text);
    }

    public function delete($id)
    {
        $titleCount = CourierCompany::all()->count();
        if ($titleCount > 1)
        {
            if ($id)
            {
                $record = CourierCompany::find($id);

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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.master.courier-company.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = CourierCompany::find($id);
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

            return redirect()->route('admin.master.courier-company.index')->with($status, $message_text);
        }
    }

}

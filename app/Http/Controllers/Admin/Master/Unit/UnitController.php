<?php

namespace App\Http\Controllers\Admin\Master\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\AdminModel\AdminLanguage;
use App\AdminModel\Unit\AdminUnit;
use App\AdminModel\Unit\AdminUnitDescription;
/*
 * Other
 */
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

class UnitController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.unit';

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
        $unit  = new AdminUnit();
        $units = $unit->getAllData($searchText, $sortKey, $sortVal,$pageLength);
        return view('admin.master.unit.index', compact('units', 'sortKey', 'sortVal', 'searchText','pageLength'));
    }

    public function create()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            return view("admin.master.unit.add", compact('languageList'));
        }
        else
        {
            return redirect()->route('admin.master.unit.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList = Helper::getLanguageList();

        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        foreach ($languageList as $language)
        {
            $message_text                                                   = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-unit-req-unit'));
            $validationRule['units_name_' . $language->id]                  = 'required';
            $validationMessage['units_name_' . $language->id . '.required'] = $message_text;
        }

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            $flag  = true;
            $model = new AdminUnit();
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                foreach ($languageList as $language)
                {
                    $subModel = new AdminUnitDescription();

                    $unitName = 'units_name_' . $language->id;

                    $subModel->units_id    = $model->id;
                    $subModel->language_id = $language->id;
                    $subModel->unit_name   = $request->$unitName;
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

        return redirect()->route('admin.master.unit.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $unit = AdminUnit::find($id);
        if ($unit)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {

                return view("admin.master.unit.edit", compact('languageList', 'unit'));
            }
            else
            {
                return redirect()->route('admin.master.unit.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.master.unit.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
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
            $message_text                                                   = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-unit-req-unit'));
            $validationRule['units_name_' . $language->id]                  = 'required';
            $validationMessage['units_name_' . $language->id . '.required'] = $message_text;
        }

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            $flag  = true;
            $model = AdminUnit::find($id);

            if ($model->save())
            {
                foreach ($languageList as $language)
                {
                    $subModel = AdminUnitDescription::getDataByLanguageID($id, $language->id);
                    if ($subModel)
                    {
                        $unitName = 'units_name_' . $language->id;
                        $subModel->unit_name = $request->$unitName;
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

        return redirect()->route('admin.master.unit.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = AdminUnit::find($id);

            if ($record)
            {
                DB::beginTransaction();

                try
                {
                    $subDeleteFlag = true;
                    $record        = AdminUnit::find($id);
                    $subRecord     = $record->getUnitDescription("", true);

                    foreach ($subRecord as $sub)
                    {
                        if (!$sub->delete())
                        {
                            $subDeleteFlag = false;
                        }
                    }

                    /*
                     * Delete Dependecy as well
                     */

                    if ($subDeleteFlag && $record->delete())
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
        return redirect()->route('admin.master.unit.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminUnit::find($id);
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

            return redirect()->route('admin.master.unit.index')->with($status, $message_text);
        }
    }

}

<?php

namespace App\Http\Controllers\Admin\Master\Zone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use App\AdminModel\Zone\AdminZone;
use App\AdminModel\Zone\AdminZoneArea;
use App\AdminModel\AdminCountry;
//Export Excel
use App\Exports\CityExport;
use Maatwebsite\Excel\Facades\Excel;

class ZoneController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.zone';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting      = Helper::getOrderSortingColumn($request);
        $sortKey      = $sorting['sortKey'];
        $sortVal      = $sorting['sortVal'];
        $searchText   = $request->q;
        $pageLength   = $request->pageLength;
        $languageList = Helper::getLanguageList();
        $zone         = new AdminZone();
        $zone         = $zone->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.master.zone.index', compact('zone', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminZone();
        $sortingNumber = $model->getSortingNumber();
        if (!$sortingNumber)
        {
            $sortingNumber = 0;
        }
        $sortingNumber++;
        return $sortingNumber;
    }

    /*
     * Update Sorting Order
     */

    function updateSortingOrder($currentOrder, $newOrder)
    {
        if ($currentOrder > $newOrder)
        {
            $record = AdminZone::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminZone::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting - 1;
                $record->save();
            }
        }
    }

    public function create()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            $sortingNumber = $this->getSortingNumber();
            $zoneArea      = AdminZoneArea::pluck('area_id');
            $areaList      = \App\AdminModel\City\AdminCity::orderBy('sorting', 'asc');
            if ($zoneArea)
            {
                $areaList->whereNotIn('id', $zoneArea);
            }
            $areaList = $areaList->get();

            return view("admin.master.zone.add", compact('languageList', 'sortingNumber', 'areaList'));
        }
        else
        {
            return redirect()->route('admin.master.zone.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
            'sorting'         => "required|numeric|gte:1|max:$sortingNumber",
            'title'           => "required",
            'delivery_charge' => "required",
            'to'              => "required",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'title.required'           => Lang::get($this->langFile . '.error-req-title'),
            'delivery_charge.required' => Lang::get($this->langFile . '.error-req-delivery_charge'),
            'to.required'              => Lang::get($this->langFile . '.error-req-to'),
            'sorting.required'         => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'          => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'              => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'              => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'              => $message_text_sorting,
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try
        {
            $model                  = new AdminZone();
            /*
             * code for sorting
             */
            $model->sorting         = $request->sorting;
            $model->title           = $request->title;
            $model->delivery_charge = $request->delivery_charge;

            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            /*
             * main save color
             */
            if ($model->save())
            {
                $flag = true;

                foreach ($request->to as $to)
                {
                    $zoneArea          = new AdminZoneArea;
                    $zoneArea->zone_id = $model->id;
                    $zoneArea->area_id = $to;
                    if (!$zoneArea->save())
                    {
                        $flag = false;
                    }
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

        return redirect()->route('admin.master.zone.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = AdminZone::find($id);
        if ($singleRecord)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {
                /*
                 * While Update
                 */
                $sortingNumber = $this->getSortingNumber();
                $currentOrder  = $singleRecord->sorting;
                $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;

                $selectedZoneArea = AdminZoneArea::where('zone_id', '=', $id)->pluck('area_id');

                $zoneArea = AdminZoneArea::where('zone_id', '!=', $id)->pluck('area_id')->toArray();
                $areaList = \App\AdminModel\City\AdminCity::orderBy('sorting', 'asc');
                if ($zoneArea)
                {
                    $areaList->whereNotIn('id', $zoneArea);
                }

                $areaList = $areaList->get();

                return view("admin.master.zone.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber', 'areaList', 'selectedZoneArea'));
            }
            else
            {
                return redirect()->route('admin.master.zone.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.master.zone.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = AdminZone::find($id);
        if ($model)
        {
            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;

            $languageList      = Helper::getLanguageList();
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            $sortingNumber  = $this->getSortingNumber();
            $validationRule = [
                'sorting'         => "required|numeric|gte:1|max:$sortingNumber",
                'title'           => "required",
                'delivery_charge' => "required",
                'to'              => "required",
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'title.required'           => Lang::get($this->langFile . '.error-req-title'),
                'delivery_charge.required' => Lang::get($this->langFile . '.error-req-delivery_charge'),
                'to.required'              => Lang::get($this->langFile . '.error-req-to'),
                'sorting.required'         => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'          => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'              => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'              => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'              => $message_text_sorting,
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            try
            {
                DB::beginTransaction();

                $model->sorting         = $request->sorting;
                $model->title           = $request->title;
                $model->delivery_charge = $request->delivery_charge;

                $this->updateSortingOrder($currentOrder, $request->sorting);

                if ($model->save())
                {
                    $flag = true;

                    AdminZoneArea::where('zone_id', $model->id)->delete();

                    foreach ($request->to as $to)
                    {
                        $zoneArea          = new AdminZoneArea;
                        $zoneArea->zone_id = $model->id;
                        $zoneArea->area_id = $to;
                        if (!$zoneArea->save())
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
        return redirect()->route('admin.master.zone.index')->with($status, $message_text);
    }

    public function delete($id)
    {
        if ($id)
        {
            $record = AdminZone::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    $areaZoneDelete = AdminZoneArea::where('zone_id', $id)->delete();
                    /*
                     * While Delete Change Sorting
                     */
                    $sortingNumber  = $this->getSortingNumber();
                    $sortingNumber  = $sortingNumber - 1;
                    $currentOrder   = $record->sorting;
                    $currentOrder   = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($areaZoneDelete && $record->delete())
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

        return redirect()->route('admin.master.zone.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminZone::find($id);
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

            return redirect()->route('admin.master.zone.index')->with($status, $message_text);
        }
    }

    public function exportCityData()
    {
        return Excel::download(new CityExport(), config('excel.city_data'));
    }

}

<?php

namespace App\Http\Controllers\Admin\Master\PhysicalStore;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\AdminModel\AdminLanguage;
use App\AdminModel\PhysicalStore\PhysicalStore as PhysicalStoreModel;
use App\AdminModel\AdminCountry;
use App\AdminModel\City\AdminCity;
use App\AdminModel\PhysicalStore\Users as PhysicalStoreUser;
use App\User;
/*
 * Other
 */
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PhysicalStoreController extends Controller
{
    //
    private $langFile = 'admin';
    private $module   = '.physical-store';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderSortingColumnById($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $store   = new PhysicalStoreModel();
        $stores = $store->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.master.physical-store.index', compact('stores', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        $country = AdminCountry::getCountry(1);
        return view("admin.master.physical-store.add", compact('country'));
    }

    public function store(Request $request)
    {
        /*
     * Validations
     */
        $validationRule = [
            'store_name'       => 'required|string|max:255',
            'store_code'       => 'required|string|max:100|unique:physical_store,store_code',
            'address_line_1'   => 'required|string',
            'map' => [
                'nullable',
                'string'
            ],
            // 'country_id'       => 'required|integer',
            // 'area'             => 'required',
            // 'block'            => 'required|string|max:255',
            // 'street'           => 'required|string|max:255',
            // 'pin_code'         => 'required|digits:6',
        ];

        $validationMessage = [
            'store_name.required'     => Lang::get($this->langFile . '.error-req-store-name'),
            'store_code.required'     => Lang::get($this->langFile . '.error-req-store-code'),
            'store_code.unique'       => Lang::get($this->langFile . '.error-store-code-exists'),
            'address_line_1.required' => Lang::get($this->langFile . '.error-req-address1'),
            // 'country_id.required'     => Lang::get($this->langFile . '.error-req-country'),
            // 'area.required'           => Lang::get($this->langFile . '.error-req-area'),
            // 'block.required'          => Lang::get($this->langFile . '.error-req-block'),
            // 'street.required'         => Lang::get($this->langFile . '.error-req-street'),
            // 'pin_code.required'       => Lang::get($this->langFile . '.error-req-pin'),
            // 'pin_code.digits'         => Lang::get($this->langFile . '.error-pin-invalid'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try {
            $model = new PhysicalStoreModel();

            $model->store_name     = $request->store_name;
            $model->store_code     = $request->store_code;
            $model->address_line_1 = $request->address_line_1;
            $model->address_line_2 = $request->address_line_2;
            $model->country_id     = $request->country_id;

            // City handling (dropdown OR manual)
            if ($request->is_city == 1) {
                $model->city    = $request->city;
                $model->city_id = null;
                $model->area    = null;
            } else {
                $model->city_id = $request->area;
                $model->area    = $request->area;
                $model->city    = null;
            }

            $model->block     = $request->block;
            $model->street    = $request->street;
            $model->pin_code  = $request->pin_code;
            $model->map       = $request->map;
            $model->is_active = 1;

            // Audit fields
            $model->created_by = auth()->id();

            if ($model->save()) {
                DB::commit();

                $status = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace(
                    "#module#",
                    Lang::get($this->langFile . '.physical-store'),
                    $message_text
                );
            } else {
                DB::rollBack();

                $status = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace(
                    "#module#",
                    Lang::get($this->langFile . '.physical-store'),
                    $message_text
                );
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace(
                "#module#",
                Lang::get($this->langFile . '.physical-store'),
                $message_text
            );
        }

        return redirect()
            ->route('admin.master.physical-store.index')
            ->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = PhysicalStoreModel::find($id);
        $country      = AdminCountry::getCountry(1);

        if (!$singleRecord) {
            return redirect()
                ->route('admin.master.physical-store.index')
                ->with('error', __('admin.update-error'));
        }

        return view(
            'admin.master.physical-store.edit',
            compact('singleRecord', 'country')
        );
    }

    public function update(Request $request, $id)
    {
        $model = PhysicalStoreModel::find($id);

        if (!$model) {
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace(
                "#module#",
                strtolower(Lang::get($this->langFile . '.physical-store')),
                $message_text
            );

            return redirect()
                ->route('admin.master.physical-store.index')
                ->with($status, $message_text);
        }

        /*
        * Validations
        */
        $validationRule = [
            'store_name'       => 'required|string|max:255',
            'store_code'       => 'required|string|max:100|unique:physical_store,store_code,' . $id,
            'address_line_1'   => 'required|string',
            'map' => [
                'nullable',
                'string'
            ],
            // 'country_id'       => 'required|integer',
            // 'area'             => 'required',
            // 'block'            => 'required|string|max:255',
            // 'street'           => 'required|string|max:255',
            // 'pin_code'         => 'required|digits:6',
        ];

        $validationMessage = [
            'store_name.required'     => Lang::get($this->langFile . '.error-req-store-name'),
            'store_code.required'     => Lang::get($this->langFile . '.error-req-store-code'),
            'store_code.unique'       => Lang::get($this->langFile . '.error-store-code-exists'),
            'address_line_1.required' => Lang::get($this->langFile . '.error-req-address1'),
            // 'country_id.required'     => Lang::get($this->langFile . '.error-req-country'),
            // 'area.required'           => Lang::get($this->langFile . '.error-req-area'),
            // 'block.required'          => Lang::get($this->langFile . '.error-req-block'),
            // 'street.required'         => Lang::get($this->langFile . '.error-req-street'),
            // 'pin_code.required'       => Lang::get($this->langFile . '.error-req-pin'),
            // 'pin_code.digits'         => Lang::get($this->langFile . '.error-pin-invalid'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();
        try {

            $model->store_name     = $request->store_name;
            $model->store_code     = $request->store_code;
            $model->address_line_1 = $request->address_line_1;
            $model->address_line_2 = $request->address_line_2;
            $model->country_id     = $request->country_id;

            /*
            * City handling (dropdown OR manual)
            */
            if ($request->is_city == 1) {
                $model->city    = $request->city;
                $model->city_id = null;
                $model->area    = null;
            } else {
                $model->city_id = $request->area;
                $model->area    = $request->area;
                $model->city    = null;
            }

            $model->block    = $request->block;
            $model->street   = $request->street;
            $model->pin_code = $request->pin_code;
            $model->map      = $request->map;

            if ($model->save()) {
                DB::commit();

                $status = 'success';
                $message_text = Lang::get($this->langFile . '.update-success');
                $message_text = str_replace(
                    "#module#",
                    Lang::get($this->langFile . '.physical-store'),
                    $message_text
                );
            } else {
                DB::rollBack();

                $status = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace(
                    "#module#",
                    strtolower(Lang::get($this->langFile . '.physical-store')),
                    $message_text
                );
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            $status = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace(
                "#module#",
                strtolower(Lang::get($this->langFile . '.physical-store')),
                $message_text
            );
        }

        return redirect()
            ->route('admin.master.physical-store.index')
            ->with($status, $message_text);
    }

    public function delete($id)
    {
        $titleCount = PhysicalStoreModel::all()->count();
        if ($titleCount > 1) {
            if ($id) {
                $record = PhysicalStoreModel::find($id);

                if ($record) {
                    DB::beginTransaction();
                    try {
                        $this->deactivateStoreUsers($record->id);
                        if ($record->delete()) {
                            DB::commit();
                            $status       = 'success';
                            $message_text = Lang::get($this->langFile . '.delete-success');
                            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        } else {
                            DB::rollback();
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.delete-error');
                            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                        }
                    } catch (Throwable $e) {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                } else {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.record_not_found');
                }
            } else {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        } else {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.master.physical-store.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = PhysicalStoreModel::find($id);
        if ($singleRecord && $active == 0 || $active == 1) {
            DB::beginTransaction();

            try {
                $singleRecord->is_active = $active;
                if ($singleRecord->save()) {
                    if ((int) $active === 0) {
                        $this->deactivateStoreUsers($singleRecord->id);
                    }
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    if ($active == 1) {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.active') . '"'), $message_text);
                    } else {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.in_active') . '"'), $message_text);
                    }
                } else {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e) {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.master.physical-store.index')->with($status, $message_text);
        }
    }

    private function deactivateStoreUsers(int $storeId): void
    {
        PhysicalStoreUser::where('physical_store_id', $storeId)
            ->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

        User::where('physical_store_id', $storeId)
            ->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);
    }

    function GetCity(Request $request) {
		if ($request->country_id) {
			$city = AdminCity::getCity($request->country_id, 1);
			if (count($city) > 0) {
				return view('admin.master.physical-store.city', compact('city'));
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

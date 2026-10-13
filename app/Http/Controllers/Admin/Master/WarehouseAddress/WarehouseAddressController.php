<?php

namespace App\Http\Controllers\Admin\Master\WarehouseAddress;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminModel\Master\WarehouseAddress;
use App\AdminModel\AdminCountry;
use App\AdminModel\City\AdminCity;
use Illuminate\Support\Facades\Lang;
use Helper;

class WarehouseAddressController extends Controller
{
    private $langFile = 'admin';

    public function index()
    {
        $address = WarehouseAddress::first();
        return view('admin.master.warehouse_address.index', compact('address'));
    }

    public function edit($id)
    {
        $singleRecord = WarehouseAddress::find($id);
        if (!$singleRecord) {
            return redirect()->route('admin.master.warehouse-address.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        $country = AdminCountry::getCountry(1);
        $city = collect();

        if (!empty($singleRecord->country_id)) {
            $city = AdminCity::getCity($singleRecord->country_id, 1);
        }

        return view('admin.master.warehouse_address.edit', compact('singleRecord', 'country', 'city'));
    }

    public function update(Request $request, $id)
    {
        $country = null;
        $isKuwait = false;

        if ($request->filled('country_id')) {
            $country = AdminCountry::find($request->country_id);
            $isKuwait = $country && (
                strtoupper($country->country_code) === 'KW' ||
                (int) $country->id === (int) config('custom.CountryID_Kuwait', 2)
            );
        }

        $request->validate([
            'country_id' => 'required|integer',
            'area' => $isKuwait ? 'required' : 'nullable',
            'block' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'floor' => 'required|string|max:255',
            'address_detail' => 'required|string',
            'phone' => 'required|string|max:50',
            'pin_code' => 'required|digits:6',
        ], [
            'country_id.required' => Lang::get($this->langFile . '.error-req-country'),
            'area.required' => Lang::get($this->langFile . '.error-req-area'),
            'block.required' => Lang::get($this->langFile . '.error-req-block'),
            'street.required' => Lang::get($this->langFile . '.error-req-street'),
            'number.required' => Lang::get($this->langFile . '.error-req-number'),
            'floor.required' => Lang::get($this->langFile . '.error-req-floor'),
            'address_detail.required' => Lang::get($this->langFile . '.error-req-address-detail'),
            'phone.required' => Lang::get($this->langFile . '.error-req-phone'),
            'pin_code.required' => Lang::get($this->langFile . '.error-req-pin'),
            'pin_code.digits' => Lang::get($this->langFile . '.error-pin-invalid'),
        ]);

        $address = WarehouseAddress::find($id);
        if (!$address) {
            return redirect()->back()->with('error', Lang::get($this->langFile . '.record_not_found'));
        }

        $address->country_id = $request->country_id;
        $address->area = $isKuwait ? $request->area : null;

        $address->block = $request->block;
        $address->street = $request->street;
        $address->number = $request->number;
        $address->floor = $request->floor;
        $address->address_detail = $request->address_detail;
        $address->phone = $request->phone;
        $address->pin_code = $request->pin_code;

        if ($address->save()) {
            return redirect()->route('admin.master.warehouse-address.index')->with('success', Lang::get($this->langFile . '.update-success'));
        }

        return redirect()->back()->with('error', Lang::get($this->langFile . '.update-error'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Master\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\PhysicalStore\PhysicalStore as PhysicalStoreModel;
use Helper;

class PhysicalStoreController extends Controller
{
    private $langFile = 'admin';
    private $module   = '.physical-store';

    public function index(Request $request)
    {
        $sorting    = Helper::getOrderSortingColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $store   = new PhysicalStoreModel();
        $stores = $store->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.physical-store.index', compact('stores', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        return view('admin.physical-store.add');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'active' => 'nullable|in:0,1',
        ]);
        $data['active'] = $request->has('active') ? 1 : 0;
        $model = PhysicalStoreModel::create($data);
        return redirect()->route('admin.physical-store.index')->with('success', 'Physical store created.');
    }

    public function edit($id)
    {
        $store = PhysicalStoreModel::findOrFail($id);
        return view('admin.physical-store.edit', compact('store'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'active' => 'nullable|in:0,1',
        ]);
        $data['active'] = $request->has('active') ? 1 : 0;
        $store = PhysicalStoreModel::findOrFail($id);
        $store->update($data);
        return redirect()->route('admin.physical-store.index')->with('success', 'Physical store updated.');
    }

    public function delete($id)
    {
        $store = PhysicalStoreModel::find($id);
        if ($store) {
            $store->delete();
        }
        return redirect()->route('admin.physical-store.index')->with('success', 'Physical store deleted.');
    }

    public function changeStatus($id, $active)
    {
        $store = PhysicalStoreModel::find($id);
        if ($store) {
            $store->active = $active ? 1 : 0;
            $store->save();
        }
        return redirect()->route('admin.physical-store.index')->with('success', 'Status updated.');
    }
}

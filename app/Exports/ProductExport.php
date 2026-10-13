<?php

namespace App\Exports;

use App\AdminModel\Product\AdminProduct;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Helpers\Helper;

class ProductExport implements FromView, ShouldAutoSize
{

    protected $request;

     public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {

        $sorting = Helper::getOrderSortingColumn($this->request);
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $this->request->q;
        $pageLength = 'all';
        $productId = $this->request->id;
        $outOfStockFilter = $this->request->OOSFilter;
        $newArrivalFilter = $this->request->na;
        $latestCollectionFilter = $this->request->lc;
        $filterCategory = $this->request->_c;
        $filterBrand = $this->request->_b;
        $intlFilter = $this->request->ish;
    
        $product = new AdminProduct();
        $product = $product->getAllData(
                $searchText, $sortKey, $sortVal, $pageLength, $productId, $outOfStockFilter, $filterCategory, $newArrivalFilter, $intlFilter, $filterBrand
        );
       
        return view('admin.product.product.product_detail_export', [
            'data' => $product
        ]);
    }

}

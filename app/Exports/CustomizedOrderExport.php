<?php

namespace App\Exports;

use App\AdminModel\Order\WebOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\AdminModel\Order\AdminOrderStatus;

class CustomizedOrderExport implements FromView, ShouldAutoSize
{
    private $searchText;
    private $sortKey;
    private $sortVal;
    private $filter;

    public function __construct($searchText = '', $sortKey = 'id', $sortVal = 'desc', array $filter = [])
    {
        $this->searchText = $searchText;
        $this->sortKey = $sortKey;
        $this->sortVal = $sortVal;
        $this->filter = $filter;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $data = (new WebOrder())->getAllData(
            $this->searchText,
            $this->sortKey,
            $this->sortVal,
            'all',
            $this->filter,
            1,
            true,
            true,
            true,
            false
        );

        return view('admin.order.order.order_detail_export', [
            'data' => $data
        ]);
    }

}

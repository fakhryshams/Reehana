<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\AdminModel\Order\WebOrder;
use App\Helpers\Helper;
use Carbon\Carbon;

class SalesReportCustomerWiseExport implements FromView, ShouldAutoSize
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $sorting               = Helper::getOrderColumn($this->request, 'no_of_order', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $this->request->pageLength;
        $searchText            = $this->request->q;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getCustomerReport($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();
        return view('admin.report.sales-customerwise-export', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'currency'));
    }

}

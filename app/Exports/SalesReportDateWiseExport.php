<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\AdminModel\Order\WebOrder;
use App\Helpers\Helper;
use Carbon\Carbon;

class SalesReportDateWiseExport implements FromView, ShouldAutoSize
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $sorting               = Helper::getOrderColumn($this->request, 'created_at', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = 'all';
        $searchText            = "";
        $fistDayOfMonth        = new Carbon('first day of this month');
        $lastDayOfMonth        = new Carbon('last day of this month');
        $fromDate              = $this->request->from_date ? $this->request->from_date : $fistDayOfMonth->format("d-m-Y");
        $toDate                = $this->request->to_date ? $this->request->to_date : $lastDayOfMonth->format("d-m-Y");
        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getSalesReportDatewise($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();

        return view('admin.report.sales-datewise-export', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'currency', 'filter'));
    }

}

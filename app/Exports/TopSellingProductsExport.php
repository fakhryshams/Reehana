<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\AdminModel\Order\WebOrder;
use App\Helpers\Helper;
use Carbon\Carbon;

class TopSellingProductsExport implements FromView, ShouldAutoSize
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
        $pageLength            = "all";
        $searchText            = "";
        $fistDayOfYear         = Carbon::parse('2020')->startOfYear()->format("M-Y");
        $lastDayOfYear         = Carbon::parse('2020')->endOfYear()->format("M-Y");
        $fromDate              = $this->request->from_date ? $this->request->from_date : $fistDayOfYear;
        $toDate                = $this->request->to_date ? $this->request->to_date : $lastDayOfYear;
        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $filter['year']        = date("Y");
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getTopSellingProducts($searchText, $sortKey, $sortVal, $pageLength, $filter);

        return view('admin.report.top-selling-product-export', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'filter'));
    }

}

<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\AdminModel\Order\WebOrder;
use App\Helpers\Helper;

class CouponReportExport implements FromView, ShouldAutoSize
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
        $pageLength            = "all";
        $searchText            = "";
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getUsedCoupon($searchText, $sortKey, $sortVal, $pageLength, $filter);

        return view('admin.report.coupon_code_report_export', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData'));
    }

}

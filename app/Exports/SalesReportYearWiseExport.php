<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\AdminModel\Order\WebOrder;
use App\Helpers\Helper;
use Carbon\Carbon;

class SalesReportYearWiseExport implements FromView, ShouldAutoSize
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($this->request, 'created_at', 'desc');
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $pageLength = "all";
        $searchText = "";

        $yearLimit = config('admin.report.yearLimit', 2);
        $yearGap   = config('admin.report.yearGap', 1);

        $currentYear  = date("Y");
        $previousYear = date("Y", strtotime($currentYear . " - $yearGap year"));

        $fromDate = $this->request->from_date ? $this->request->from_date : $previousYear;

        $toDateStart = date("$fromDate-01-01");
        $toDateEnd   = $fromDate + $yearLimit;
        $toDateEnd   = date("$toDateEnd-12-31");

        $toDate = $this->request->to_date ? $this->request->to_date : $currentYear;

        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $filter['toDateEnd']   = $toDateEnd;
        $filter['toDateStart'] = $toDateStart;
        $filter['yearLimit']   = $yearLimit;
        $filter['yearGap']     = $yearGap;

        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */

        $currency = Helper::getDefaultCurrency();
        $response = [];
        $sum[]    = "Total : ";
        for ($i = 0; $i <= 12; $i++)
        {
            $innerResponse = [];

            $month           = $i + 1;
            $filter['month'] = $month;

            $innerResponse[] = date("M", strtotime(date("Y-" . $month . "-d"))); //Month
            for ($j = $filter['fromDate']; $j <= $filter['toDate']; $j++)
            {
                $filter['year'] = $j;
                $reportData     = $reportModel->getSalesReportYearwise($searchText, $sortKey, $sortVal, $pageLength, $filter);

                $innerSum = $reportData ? $reportData['order_total'] : 0;

                $innerResponse[] = $innerSum;

                $sum[$j] = (isset($sum[$j]) ? $sum[$j] : 0) + $innerSum;
            }
            $response[] = $innerResponse;
        }
        $response[] = $sum;

        $view = view('admin.report.sales-yearwise-export', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'response', 'currency', 'filter'));
       return $view;
    }

}

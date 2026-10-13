<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helper;
use App\AdminModel\Order\WebOrder;
use App\AdminModel\Product\Wishlist;
use Carbon\Carbon;
#Export to Excel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductLikeExport;
use App\Exports\SalesReportDateWiseExport;
use App\Exports\SalesReportMonthWiseExport;
use App\Exports\SalesReportYearWiseExport;
use App\Exports\SalesReportCustomerWiseExport;
use App\Exports\TopSellingProductsExport;
use App\Exports\CouponReportExport;

class ReportController extends Controller
{

    public function salesReportCustomerWise(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'no_of_order', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getCustomerReport($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();
        return view('admin.report.sales-customerwise', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'currency'));
    }

    public function salesReportCustomerWiseExport(Request $request)
    {
        return Excel::download(new SalesReportCustomerWiseExport($request), config('excel.SalesReportCustomerWiseExport'));
    }

    public function productLikedReport(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'no_of_order', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new Wishlist();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getProductLikedReport($searchText, $sortKey, $sortVal, $pageLength, $filter);
        return view('admin.report.product_like_report', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData'));
    }

    public function productLikedReportExport(Request $request)
    {
        return Excel::download(new ProductLikeExport(), config('excel.product_like_report'));
    }

    public function salesReportDateWise(Request $request)
    {

        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'created_at', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $fistDayOfMonth        = new Carbon('first day of this month');
        $lastDayOfMonth        = new Carbon('last day of this month');
        $fromDate              = $request->from_date ? $request->from_date : $fistDayOfMonth->format("d-m-Y");
        $toDate                = $request->to_date ? $request->to_date : $lastDayOfMonth->format("d-m-Y");
        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $filter['type'] = $request->type ? $request->type : 1;
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getSalesReportDatewise($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();

        return view('admin.report.sales-datewise', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'currency', 'filter'));
    }

    public function salesReportDateWiseExport(Request $request)
    {
        return Excel::download(new SalesReportDateWiseExport($request), config('excel.SalesReportDateWiseExport'));
    }

    public function salesReportMonthWise(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'created_at', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $fistDayOfYear         = Carbon::parse('2020')->startOfYear()->format("M-Y");
        $lastDayOfYear         = Carbon::parse('2020')->endOfYear()->format("M-Y");
        $fromDate              = $request->from_date ? $request->from_date : $fistDayOfYear;
        $toDate                = $request->to_date ? $request->to_date : $lastDayOfYear;
        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $filter['year']        = date("Y");
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $filter['type'] = $request->type ? $request->type : 1;
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getSalesReportMonthwise($searchText, $sortKey, $sortVal, $pageLength, $filter);
        $currency              = Helper::getDefaultCurrency();

        return view('admin.report.sales-monthwise', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'currency', 'filter'));
    }

    public function salesReportMonthWiseExport(Request $request)
    {
        return Excel::download(new SalesReportMonthWiseExport($request), config('excel.SalesReportMonthWiseExport'));
    }

    public function topSellingProducts(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'created_at', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $fistDayOfYear         = Carbon::parse('2020')->startOfYear()->format("M-Y");
        $lastDayOfYear         = Carbon::parse('2020')->endOfYear()->format("M-Y");
        $fromDate              = $request->from_date ? $request->from_date : $fistDayOfYear;
        $toDate                = $request->to_date ? $request->to_date : $lastDayOfYear;
        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $filter['year']        = date("Y");
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $filter['type'] = $request->type ? $request->type : 1;
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getTopSellingProducts($searchText, $sortKey, $sortVal, $pageLength, $filter);

        return view('admin.report.top-selling-product', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData', 'filter'));
    }

    public function topSellingProductsExport(Request $request)
    {
        return Excel::download(new TopSellingProductsExport($request), config('excel.TopSellingProductsExport'));
    }

    public function salesReportYearWise(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($request, 'created_at', 'desc');
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $pageLength = $request->pageLength;
        $searchText = $request->q;

        $yearLimit = config('admin.report.yearLimit', 2);
        $yearGap   = config('admin.report.yearGap', 1);

        $currentYear  = date("Y");
        $previousYear = date("Y", strtotime($currentYear . " - $yearGap year"));

        $fromDate = $request->from_date ? $request->from_date : $previousYear;

        $toDateStart = date("$fromDate-01-01");
        $toDateEnd   = $fromDate + $yearLimit;
        $toDateEnd   = date("$toDateEnd-12-31");

        $toDate = $request->to_date ? $request->to_date : $currentYear;

        $filter['fromDate']    = $fromDate;
        $filter['toDate']      = $toDate;
        $filter['toDateEnd']   = $toDateEnd;
        $filter['toDateStart'] = $toDateStart;
        $filter['yearLimit']   = $yearLimit;
        $filter['yearGap']     = $yearGap;
        $filter['type'] = $request->type ? $request->type : 1;

        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */

        $currency = Helper::getDefaultCurrency();
        $response = [];
        $sum[]    = "Total : ";
        for ($i = 0; $i <= 12; $i++) {
            $innerResponse = [];

            $month           = $i + 1;
            $filter['month'] = $month;

            $innerResponse[] = date("M", strtotime(date("Y-" . $month . "-d"))); //Month
            for ($j = $filter['fromDate']; $j <= $filter['toDate']; $j++) {
                $filter['year'] = $j;
                $reportData     = $reportModel->getSalesReportYearwise($searchText, $sortKey, $sortVal, $pageLength, $filter);

                $innerSum = $reportData ? $reportData['order_total'] : 0;

                $innerResponse[] = $innerSum;

                $sum[$j] = (isset($sum[$j]) ? $sum[$j] : 0) + $innerSum;
            }
            $response[] = $innerResponse;
        }
        $response[] = $sum;

        $currency = Helper::getDefaultCurrency();
        return view('admin.report.sales-yearwise', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'response', 'currency', 'filter'));
    }

    public function salesReportYearWiseExport(Request $request)
    {
        return Excel::download(new SalesReportYearWiseExport($request), config('excel.SalesReportYearWiseExport'));
    }

    public function couponReport(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting               = Helper::getOrderColumn($request, 'no_of_order', 'desc');
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $pageLength            = $request->pageLength;
        $searchText            = $request->q;
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new WebOrder();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getUsedCoupon($searchText, $sortKey, $sortVal, $pageLength, $filter);

        return view('admin.report.coupon_code_report', compact('pageLength', 'sortKey', 'sortVal', 'searchText', 'reportData'));
    }

    public function couponReportExport(Request $request)
    {
        return Excel::download(new CouponReportExport($request), config('excel.CouponReportExport'));
    }
}

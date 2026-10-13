<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\AdminModel\Product\Wishlist;
class ProductLikeExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        $orderStatus           = config('order.status');
        $filter['orderStatus'] = $orderStatus['delivered'];
        $reportModel           = new Wishlist();
        /*
         * Report of all customer-order with order status == 'Delivered'
         */
        $reportData            = $reportModel->getProductLikedReport("", "", "desc", "all", $filter);
        return view('admin.report.product_like_report_export', compact('reportData'));
    }

}

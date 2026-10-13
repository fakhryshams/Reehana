<?php

namespace App\Exports;

use App\AdminModel\Order\WebOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderDetailExport implements FromView, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $cancelStatus = config('custom_web.cancel');
        $pendingStatus = config('custom_web.Pending');
        $data = WebOrder::orderBy('id', 'desc')->where('order_status', '!=', $pendingStatus)->where(function($q) use ($cancelStatus, $pendingStatus) {
             // Allow non-custom orders
             $q->whereNull('custom_order_type')
             // OR allow custom orders that are NOT canceled
               ->orWhere(function($sub) use ($cancelStatus, $pendingStatus) {
                   $sub->whereNotNull('custom_order_type')
                       ->whereNotIn('order_status', [$cancelStatus, $pendingStatus]);
               });
        })->get();

        return view('admin.order.order.order_detail_export', [
            'data' => $data
        ]);
    }

}

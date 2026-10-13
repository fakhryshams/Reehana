<?php

namespace App\Exports;

use App\AdminModel\Order\WebOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\AdminModel\Order\AdminOrderStatus;

class OrderNotCompleteExport implements FromView, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $pendingData = AdminOrderStatus::where('order_status', 'LIKE', '%' . 'Pending' . '%')->pluck('id')->first();

        return view('admin.order.order.order_detail_export', [
            'data' => WebOrder::where('order_status', $pendingData)
                        ->whereNull('custom_order_type')
                        ->get()
        ]);
    }

}

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
        return view('admin.order.order.order_detail_export', [
            'data' => WebOrder::all()
        ]);
    }

}

<?php

namespace App\Exports;

use App\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomerExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.customer.customer_export_detail', [
            'data' => User::all()
        ]);
    }

}

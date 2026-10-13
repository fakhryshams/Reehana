<?php

namespace App\Exports;

use App\AdminModel\Size\AdminSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class AdminSizeExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.master.size.size_export_detail', [
            'data' => AdminSize::all()
        ]);
    }

}

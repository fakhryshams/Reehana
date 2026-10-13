<?php

namespace App\Exports;

use App\AdminModel\Color\AdminColor;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class ColorExport implements FromView, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('admin.master.color.color_export_detail', [
            'data' => AdminColor::all()
        ]);
    }

}

<?php

namespace App\Exports;

use App\AdminModel\City\AdminCity;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class CityExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.master.city.city_export_detail', [
            'data' => AdminCity::all()
        ]);
    }

}

<?php

namespace App\Exports;

use App\AdminModel\AdminCountry;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class CountryExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.master.country.country_export_detail', [
            'data' => AdminCountry::all()
        ]);
    }

}

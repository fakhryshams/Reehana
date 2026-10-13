<?php

namespace App\Exports;

use App\AdminModel\AdminContactUs;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class ContactUsExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.contact-us.contact_us_export', [
            'data' => AdminContactUs::all()
        ]);
    }

}

<?php

namespace App\Exports;

use App\AdminModel\AdminCustomizedForm;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomizedFormExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.customized-form.customized_form_export', [
            'data' => AdminCustomizedForm::all()
        ]);
    }

}

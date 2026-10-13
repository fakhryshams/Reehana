<?php

namespace App\Exports;

use App\AdminModel\Product\AdminCategory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoryExport implements FromView, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('admin.product.category.category_detail_export', [
            'data' => AdminCategory::where('parent_id', 0)->get()
        ]);
    }

}

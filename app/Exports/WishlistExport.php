<?php

namespace App\Exports;

use App\AdminModel\Product\Wishlist;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class WishlistExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {
        return view('admin.order.wishlist.wishlist_export_detail', [
            'data' => Wishlist::all()
        ]);
    }

}

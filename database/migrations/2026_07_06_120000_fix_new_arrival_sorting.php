<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixNewArrivalSorting extends Migration
{
    public function up()
    {
        $products = DB::table('admin_products')
            ->where('is_new_arrival', 1)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $order = 1;
        foreach ($products as $product) {
            DB::table('admin_products')
                ->where('id', $product->id)
                ->update(['new_arrival_soting' => $order]);
            $order++;
        }
    }

    public function down()
    {
    }
}

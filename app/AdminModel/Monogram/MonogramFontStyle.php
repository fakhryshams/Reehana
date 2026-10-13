<?php

namespace App\AdminModel\Monogram;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Monogram\MonogramPlacement;
use App\AdminModel\Monogram\MonogramMaster;
use App\AdminModel\Product\ProductPlacement;


class MonogramFontStyle extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_monogram_font_style';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data) {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data) {
            $data->updated_by = auth()->user()->id;
        });

        static::deleting(function ($data) {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        switch ($sortKey) {
            default:
                $query = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText) {
            $query->where(function ($query) use ($searchText) {
                /*
                 * Search by category
                 */
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $query->orWhere('style_name', 'LIKE', '%' . $searchText . '%');

                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active') {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive') {
                    $query->orWhere('is_active', 0);
                }
            });
        }
        if ($pageLength) {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        } else {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public function getSortingNumber()
    {
        return $this->max('sorting');
    }

    public static function getMonogramStyleList()
    {
        return MonogramFontStyle::where('is_active', 1)->orderBy('sorting', 'asc')->get();
    }


    public static function getMonogramPlacement($id)
    {
        $placement = MonogramMaster::where('font_style_id', $id)->where('type', 'placement')->pluck('value1')->toArray();
        if (count($placement) > 0) {

            return MonogramPlacement::whereIn('id', $placement)->orderBy('sorting', 'asc')->get();
        }
    }


    public static function getProductPlacement($monogram_id, $id, $productID)
    {


        return ProductPlacement::where('monogram_id', $monogram_id)->where('placement_id', $id)->where('product_id', $productID)->first();
    }


    public static function getProductFont($monogram_id, $productID)
    {

        return ProductPlacement::where('monogram_id', $monogram_id)->where('product_id', $productID)->first();
    }

    public static function checkSlug($currentSlug, $excludedId = '')
    {
        $query = MonogramFontStyle::where('style_slug', $currentSlug);
        if ($excludedId) {
            $query->where('id', '!=', $excludedId);
        }
        return $query->get();
    }

    public static function updateSlug($productID, $slug)
    {
        $record = MonogramFontStyle::find($productID);
        if ($record) {
            $record->style_slug = $slug;
            return $record->save();
        }
        return false;
    }
}

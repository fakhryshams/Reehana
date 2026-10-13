<?php

namespace App\Http\Controllers\Admin\Product\Brand;

use App\AdminModel\Brand\AdminBrand;
use App\AdminModel\Brand\AdminBrandDescription;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.brand';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;

        $manufacturer = new AdminManufacturer();
        $manufacturer = $manufacturer->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.product.brand.index', compact('manufacturer', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        return view("admin.product.brand.add");
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'manufacturer_name' => 'required',
                ], [
            'manufacturer_name.required' => Lang::get($this->langFile . '.error-language-req-manufacturer-name'),
        ]);
        DB::beginTransaction();

        try
        {
            $manufacturer = new AdminManufacturer();

            $manufacturer = $manufacturer->fill($request->except('_token'));
            /*
             *  Image
             */
            if ($request->file('manufacturer_image'))
            {
                $path = Storage::disk('local')->put('public/product/brand/image', $request->manufacturer_image);

                if ($path)
                {
                    $manufacturer->manufacturer_image = $path;
                }
            }


            if ($manufacturer->save())
            {
                $slug_count = 0;

                $slug    = $request->manufacturer_name;
                $oldSlug = $request->manufacturer_name;

                $slugCount = 0;
                do
                {
                    if ($slugCount == 0)
                    {
                        $currentSlug = Helper::slugify($oldSlug);
                    }
                    else
                    {
                        $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                    }

                    $slug      = $currentSlug;
                    $checkSlug = AdminManufacturer::checkSlug($currentSlug);
                    $slugCount++;
                }
                while (count($checkSlug) > 0);

                $updateSlug = AdminManufacturer::updateSlug($manufacturer->id, $slug);

                DB::commit();

                $status       = 'success';
                $message_text = Lang::get($this->langFile . '.add-success');
                $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.product.brand.index')->with($status, $message_text);
        //  dd($request->all());
    }

    public function edit($id)
    {


        $manufacturer = AdminManufacturer::find($id);
        if ($manufacturer)
        {

            return view("admin.product.brand.edit", compact('manufacturer'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.edit-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            return redirect()->route('admin.order.status.index')->with($status, $message_text);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'manufacturer_name' => 'required',
                ], [
            'manufacturer_name.required' => Lang::get($this->langFile . '.error-language-req-manufacturer-name'),
        ]);
        DB::beginTransaction();
        try
        {
            $manufacturer = AdminManufacturer::find($id);
            if ($manufacturer)
            {
                $manufacturer = $manufacturer->fill($request->except('_token'));

                /*
                 *  Image
                 */
                if ($request->file('manufacturer_image'))
                {
                    $path = Storage::disk('local')->put('public/product/brand/image', $request->manufacturer_image);

                    if ($path)
                    {
                        Storage::disk('local')->delete($request->manufacturer_image);
                        $manufacturer->manufacturer_image = $path;
                    }
                }
                if ($manufacturer->save())
                {
                    $slug_count = 0;

                    $slug    = $request->manufacturer_name;
                    $oldSlug = $request->manufacturer_name;

                    $slugCount = 0;
                    do
                    {
                        if ($slugCount == 0)
                        {
                            $currentSlug = Helper::slugify($oldSlug);
                        }
                        else
                        {
                            $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                        }

                        $slug      = $currentSlug;
                        $checkSlug = AdminManufacturer::checkSlug($currentSlug);
                        $slugCount++;
                    }
                    while (count($checkSlug) > 0);

                    $updateSlug = AdminManufacturer::updateSlug($manufacturer->id, $slug);

                    DB::commit();

                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.add-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.add-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }



        return redirect()->route('admin.product.brand.index')->with($status, $message_text);
        //  dd($request->all());
    }

}

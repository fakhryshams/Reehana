<?php

namespace App\Http\Controllers\Admin\Currency;

use App\AdminModel\AdminCurrency;
use App\AdminModel\SadminCountries;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;

class CurrencyController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.currency';

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
        $currency   = new AdminCurrency();
        $currency   = $currency->getAllData($searchText, $sortKey, $sortVal, $pageLength);


        return view('admin.currency.index', compact('currency', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
    }

    public function create()
    {
        $currency = new AdminCurrency();
        $currency = $currency->get()->pluck('country_id');
        $country  = SadminCountries::whereNotIn('id', $currency)->where('symbol', '!=', '')->get();
        return view("admin.currency.add", compact('country'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'country_id'          => 'required',
            'position'            => 'required',
            'thousands_seperator' => 'required',
            'decimal_seperator'   => 'required',
            'decimal_point'       => 'required',
            'value'               => 'required',
                ], [
            'country_id.required'          => Lang::get($this->langFile . '.error-language-req-currency'),
            'position.required'            => Lang::get($this->langFile . '.error-language-req-currency-position'),
            'thousands_seperator.required' => Lang::get($this->langFile . '.error-language-req-thousand-separator'),
            'decimal_seperator.required'   => Lang::get($this->langFile . '.error-language-req-decimal-separator'),
            'decimal_point.required'       => Lang::get($this->langFile . '.error-language-req-number-of-decimal'),
            'value.required'               => Lang::get($this->langFile . '.error-language-req-currency-value'),
        ]);
        DB::beginTransaction();

        try
        {
            $currency = new AdminCurrency();

            $currency = $currency->fill($request->except('_token'));

            if ($currency->save())
            {

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

        return redirect()->route('admin.currency.index')->with($status, $message_text);
        //  dd($request->all());
    }

    public function edit($id)
    {


        $currency = AdminCurrency::find($id);
        if ($currency)
        {

            $currency_country = AdminCurrency::where('id', '!=', $currency->country_id)->get()->pluck('country_id');
            $country          = SadminCountries::whereNotIn('id', $currency_country)->where('symbol', '!=', '')->get();
            return view("admin.currency.edit", compact('country', 'currency'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.edit-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            return redirect()->route('admin.currency.index')->with($status, $message_text);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'country_id'          => 'required',
            'position'            => 'required',
            'thousands_seperator' => 'required',
            'decimal_seperator'   => 'required',
            'decimal_point'       => 'required',
            'value'               => 'required',
                ], [
            'country_id.required'          => Lang::get($this->langFile . '.error-language-req-currency'),
            'position.required'            => Lang::get($this->langFile . '.error-language-req-currency-position'),
            'thousands_seperator.required' => Lang::get($this->langFile . '.error-language-req-thousand-separator'),
            'decimal_seperator.required'   => Lang::get($this->langFile . '.error-language-req-decimal-separator'),
            'decimal_point.required'       => Lang::get($this->langFile . '.error-language-req-number-of-decimal'),
            'value.required'               => Lang::get($this->langFile . '.error-language-req-currency-value'),
        ]);
        DB::beginTransaction();
        $currency      = AdminCurrency::find($id);
        if ($currency)
        {
            try
            {
                $currency = $currency->fill($request->except('_token'));
                if ($currency->save())
                {
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.currency.index')->with($status, $message_text);
        dd($request->all());
    }

}

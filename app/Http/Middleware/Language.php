<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Config;
use App;
use DB;

class Language
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::get('locale'))
        {
            $locale = Session::get('locale', Config::get('app.locale'));
        }
        else
        {
            $languages = DB::table('admin_languages')->where('is_default', '=', '1')->first();
            $request->session()->put('locale', $languages->code);
            Session::put('locale', $languages->code);
            Session::put('lanfuage_id', $languages->id);

            $locale = $languages->code;
        }
        App::setLocale($locale);
        return $next($request);
    }

}

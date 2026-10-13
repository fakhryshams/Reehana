<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\Product\Wishlist;
use App\AdminModel\AdminLanguage;
use App\User;
use Helper;
use DB;
use App\Exports\WishlistExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderWishlistController extends Controller
{

    private $langFile = 'admin';

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
        $wishlist   = new Wishlist();
        $wishlist   = $wishlist->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.order.wishlist.index', compact('wishlist', 'sortKey', 'sortVal', 'searchText', 'sorting', 'pageLength'));
    }

    public function view(Request $request, $id)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting              = Helper::getOrderColumn($request);
        $sortKey              = $sorting['sortKey'];
        $sortVal              = $sorting['sortVal'];
        $searchText           = $request->q;
        $pageLength           = $request->pageLength;
        $user                 = User::find($id);
        $wishlist             = new Wishlist();
        $productFilter        = Wishlist::getWishListedProducts($id);
        $requestProductFilter = $request->products;
        $wishlist             = $wishlist->getWislistData($searchText, $sortKey, $sortVal, $id, $pageLength, $requestProductFilter);
        return view('admin.order.wishlist.view', compact('wishlist', 'user', 'sortKey', 'sortVal', 'searchText', 'sorting', 'id', 'pageLength', 'productFilter', 'requestProductFilter'));
    }

    public function sendEmail(Request $request, $userID, $wishlistId = '')
    {
        $wishlist = new Wishlist();
        $wishlist = $wishlist->getWislistData('', 'updated_at', 'desc', $userID, '', '', $wishlistId);

        if ($wishlist)
        {
            $user   = User::find($userID);
            $locale = "1";
            if ($user && isset($user->locale))
            {
                $languagedata = AdminLanguage::find($user->locale);
                if ($languagedata)
                {
                    $locale = $languagedata->id;
                    $view   = 'admin.order.wishlist.' . $languagedata->code . '.mail';
                }
                else
                {
                    $locale = 1;
                    $view   = 'admin.order.wishlist.en.mail';
                }
            }
            else
            {
                $locale = 1;
                $view   = 'admin.order.wishlist.en.mail';
            }

            $mailFrom = Helper::sendMailFrom();
            $appName  = Helper::getAppName();
            $mailTo   = $user->email;
            $userName = ucfirst($user->first_name) . " " . ucfirst($user->last_name);
            $subject  = $appName . " - " . Lang::get($this->langFile . '.wish-list');

            $phpMail = new \App\Mail\CommonMailSender();

            $mailFrom = ['email' => $mailFrom, 'name' => $appName];
            $mailTo   = ['email' => $mailTo, 'name' => $userName];

            foreach ($wishlist as $record)
            {
                $message = view($view, compact('user', 'record', 'locale'));
                if ($mailFrom && $mailTo && $subject && $message)
                {
                    $mailResponse = $phpMail->sendMail($mailFrom, $mailTo, $subject, $message);
                    if ($mailResponse['status'])
                    {
                        $record->mail_sent_date = date("Y-m-d H:i:s");
                        $record->save();

                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.wishlist-mail-success');
                    }
                    else
                    {
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.wishlist-mail-error');
                    }
                }
                else
                {
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.mail-param-missing');
                }
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }

        return redirect()->route('admin.order.wishlist.view', ['id' => $userID])->with($status, $message_text);
    }

    public function exportWishlistData()
    {
        return Excel::download(new WishlistExport(), config('excel.wishlist_data'));
    }

}

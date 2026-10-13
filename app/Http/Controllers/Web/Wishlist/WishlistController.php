<?php

namespace App\Http\Controllers\Web\Wishlist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AdminModel\Product\AdminProduct;
use App\UserModel\Cart\ShoppingCartDetail;
use App\AdminModel\Product\Wishlist;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Helpers\Helper;

class WishlistController extends Controller
{

    private $langFile = 'web';

    public function index()
    {
        $user = Auth::user();
        if ($user)
        {
            $wishlist = Wishlist::getUserWishlist($user->id, '', '', true);
            return view('web.user.wishlist', compact('wishlist', 'user'));
        }
        else
        {
            return redirect()->route('web.user.login')->with('redirect_url', url()->current());
        }
    }

    public function addToWishlist(Request $request)
    {
        $user = Auth::user();
        $page = '';

        $product = AdminProduct::where('id', $request->id)->first();

        if ($request->id && $user)
        {
            DB::beginTransaction();
            $wishlist = Wishlist::getUserWishlist($user->id, $request->id);


            $status = "";
            if ($wishlist)
            {
                if ($wishlist->delete())
                {
                    DB::commit();
                    $status = 'remove';
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                }
                $page = Helper::renderCartView();
            }
            else
            {
                $wishlist = new Wishlist();
                $wishlist->product_id = $request->id;
                $wishlist->user_id = $user->id;
                if ($wishlist->save())
                {
                    $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $request->id)->get();
                    if ($shoppingCartDetail)
                    {
                        foreach ($shoppingCartDetail as $cartDetails)
                        {
                            $cartDetails->delete();
                        }
                    }
                    DB::commit();
                    $page = Helper::renderCartView();
                    $status = 'add';
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                }
            }
        }
        else
        {
            $status = 'error';
            /*
             * This is not used in case of not logged user
             */
            //return redirect()->route('web.user.login');
        }
        $wishlistcount = Wishlist::where('user_id', $user->id)->count();
        return ['status' => $status, 'view' => $page ? $page['view'] : '', 'count' => $wishlistcount, 'product_qty' => $product->product_quantity];
//        return ['status' => $status, 'page' => ''];
    }

    public function removeWishlist($id)
    {
        $user = Auth::user();
        if ($id && $user)
        {
            $wishlist = Wishlist::getUserWishlist($user->id, '', $id);

            if ($wishlist->delete())
            {
                return redirect()->route('web.wishlist.index')->with('success', \Lang::get($this->langFile . '.product-remove-wishlist-message'));
            }
            else
            {
                return redirect()->route('web.wishlist.index')->with('error', \Lang::get($this->langFile . '.error-message'));
            }
        }
        else
        {
            return redirect()->route('web.index')->with('error', \Lang::get($this->langFile . '.no-record-message'));
        }
    }

    public function moveToWishlist($id)
    {
        $user = Auth::user();
        $product = AdminProduct::find($id);
        if ($product && $user)
        {
            DB::beginTransaction();

            $wishlist = Wishlist::getUserWishlist($user->id, $product->id);

            if ($wishlist)
            {
                $wishlist->delete();
            }
            $wishlistsave = new Wishlist();
            $wishlistsave->product_id = $product->id;
            $wishlistsave->user_id = $user->id;

            if ($wishlistsave->save())
            {
                $shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $id)->get();
                if ($shoppingCartDetail)
                {
                    foreach ($shoppingCartDetail as $cartDetails)
                    {
                        $cartDetails->delete();
                    }
                }
                DB::commit();
                return redirect()->back();
            }
            else
            {
                DB::rollback();
                return redirect()->back();
            }
        }
        else
        {
            return redirect()->route('web.user.login');
        }
    }

    public function moveToCart()
    {
        
    }

    public function addProductWishlist($id)
    {
        $user = Auth::user();
        if ($id && $user)
        {
            DB::beginTransaction();
            $wishlist = Wishlist::getUserWishlist($user->id, $id);
            if (!$wishlist)
            {
                $wishlistsave = new Wishlist();
                $wishlistsave->product_id = $id;
                $wishlistsave->user_id = $user->id;
                if ($wishlistsave->save())
                {
                    DB::commit();
                    return redirect()->back()->with('success', \Lang::get($this->langFile . '.product-add-wishlist-message'));
                }
                else
                {
                    DB::rollback();
                    return redirect()->back()->with('error', \Lang::get($this->langFile . '.error-message'));
                }
            }
            else
            {
                DB::commit();
                return redirect()->back()->with('success', \Lang::get($this->langFile . '.product-allredy-add-wishlist-message'));
            }
        }
        else
        {

            return redirect()->route('web.user.login');
        }
    }

}

<?php

namespace App\Http\Controllers\Web\Cart;

use App\AdminModel\AdminCountry;
use App\AdminModel\Product\AdminProduct;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\UserAddress;
use Auth;
use DB;

class CartController extends Controller {

	private $module = '.cart';
	private $langFile = 'web';

	/**
	 * Show the cart .
	 */
	public function index() {
		return view('web.cart.index');
	}

	public function removeCart($id, $sizeId = '', $colorId = '') {

		$user = Auth::user();
		if ($user) {
			DB::beginTransaction();
			$shoppingCartDetail = ShoppingCartDetail::where('user_id', $user->id)->where('product_id', $id);
			if ($sizeId && $sizeId != 's') {
				$shoppingCartDetail->where('size_id', $sizeId);
			}
			if ($colorId && $colorId != 'c') {
				$shoppingCartDetail->where('color_id', $colorId);
			}
			$shoppingCartDetail = $shoppingCartDetail->first();

			if ($shoppingCartDetail) {
				if ($shoppingCartDetail->delete()) {
					$shoppingCartCount = ShoppingCartDetail::where('user_id', $user->id)->count();
					DB::commit();
					return array('status' => true, 'message' => \Lang::get($this->langFile . '.product-remove-cart-message'), 'count' => $shoppingCartCount, 'data' => $this->getUpdatedProduct(), 'view' => \View::make('web.product.cart')->render());
				} else {
					DB::rollback();
					return array('status' => false, 'message' => \Lang::get($this->langFile . '.error-message'));
				}
			}
		} else {
			DB::beginTransaction();
			$cart = session()->get('cart');
			$shoppingCartDetail = ShoppingCartDetail::where('shopping_cart_id', $cart)->where('product_id', $id);
			if ($sizeId && $sizeId != 's') {
				$shoppingCartDetail->where('size_id', $sizeId);
			}
			if ($colorId && $colorId != 'c') {
				$shoppingCartDetail->where('color_id', $colorId);
			}
			$shoppingCartDetail = $shoppingCartDetail->first();

			if ($shoppingCartDetail) {
				if ($shoppingCartDetail->delete()) {
					$shoppingCartCount = ShoppingCartDetail::where('shopping_cart_id', $cart)->count();
					if ($shoppingCartCount == 0) {
						$shoppingCart = ShoppingCart::find($cart);
						$shoppingCart->delete();
						session()->put('cart', null);
//                        session::forget('cart');
					}
					DB::commit();
					return array('status' => true, 'message' => \Lang::get($this->langFile . '.product-remove-cart-message'), 'count' => $shoppingCartCount, 'data' => $this->getUpdatedProduct(), 'view' => \View::make('web.product.cart')->render());
				} else {
					DB::rollback();
					return array('status' => false, 'message' => \Lang::get($this->langFile . '.error-message'));
				}
			}
		}
	}

	
	public function getUpdatedProduct() {
		$cart = [];
		$priceUpdate = [];
		$total = 0;
		$user = Auth::user();
		$tax = Helper::getTax();
		$monogramPrice = \App\Helpers\Helper::getMonogramPrice();
		$ismonogram = false;

		if ($user) {
			$cart = ShoppingCartDetail::where('user_id', $user->id)->get();
		} elseif (session('cart')) {

			$cart = ShoppingCartDetail::where('shopping_cart_id', session('cart'))->get();
		}

		$appliedCoupon = [];

		$appliedCoupon = session('couponCode');
		if ($user) {
			if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
				if ($user && $appliedCoupon['user_id'] != $user->id) {
					$appliedCoupon = [];
				}
			}
		}

		$shippingcharge = 0;

		foreach ($cart as $id => $record) {
			$intlShipFlag = true;

			$id = $record->product_id;
			if ($record->is_monogram == 1) {
				$ismonogram = true;

				$monogramCount = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->count();
			} else {
				$ismonogram = false;
			}

			$productQuery = AdminProduct::where('id', $id);
			$product = AdminProduct::applyActiveCondition($productQuery, $user)->first();
			if (!$product) {
				continue;
			}

			$sizeId = $record['size_id'];
			$colorId = $record['color_id'];
			$product_weight = $product->product_weight;

			
			$product_regular_price      =  $product->product_regular_price;
            $product_sale_price         = $product->product_sale_price ;
            $is_product_sale_price_null = $product->is_product_sale_price_null;

			if ($sizeId || $colorId) {
				$attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $colorId);
				if ($attributePrice) {
					$product_regular_price = $attributePrice->product_regular_price;
					$product_sale_price = $attributePrice->product_sale_price;
					$is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
					$product_weight = $attributePrice->weight;
				}
			}
			$price = $product_sale_price ? $product_sale_price : $product_regular_price;

			/*
				                             *  Convert Currency Rate
			*/
			$currency = Helper::getDefaultCurrency();

			if ($currency) {
			//	$price = $price * $currency->value;
			}

			$sub_total = $price * $record['quantity'];

			if (isset($record->is_active) && $record->is_active == 0) {
				//Do not update total... as in this case product does not allow intl ship
				$intlShipFlag = false;
			} else {
				$total += $sub_total;
			}

			if ($ismonogram) {
				if ($record['is_same'] == 1) {
					$total = $total + ($monogramPrice * $record['quantity']);
				} else {
					$total = $total + ($monogramPrice * $monogramCount);
				}
			}
			$taxval = ($total * $tax) / 100;

			//$final_total   = $total + $shipping_charge + $taxval;

			$couponDiscountFinal = 0;

			if ($appliedCoupon) {
				$couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
				$couponDiscount = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

				if ($couponDiscountIn && $couponDiscount) {
					if ($couponDiscountIn == 'P') {
						$couponDiscountFinal = ($total * $couponDiscount) / 100;
					} else if ($couponDiscountIn == 'F') {
						$couponDiscountFinal = $total - $couponDiscount;
					}
				}
			}
			$giftprice = 0;

			$gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
			if (session('gift_wrap')) {
				$setting = \App\Helpers\Helper::getSetting();
				$gift_wrap = session('gift_wrap');
				$giftprice = $setting['32']->setting_val;
				$giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();

				if ($total >= $giftMinPrice) {
					$giftprice = 0;
				}
			}

			$giftboxprice = 0;
			$gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
			if (session('gift_box')) {
				$setting           = \App\Helpers\Helper::getSetting();
				$gift_box          = session('gift_box');
				$giftBoxSetting    = $setting->firstWhere('setting_key', 'Gift Box Amount');
				$giftboxprice      = $giftBoxSetting ? $giftBoxSetting->setting_val : 0;
				$giftBoxMinSetting = $setting->firstWhere('setting_key', 'Gift Box Min Amount');
				$giftBoxMinPrice   = $giftBoxMinSetting ? $giftBoxMinSetting->setting_val : 0;

				if ($total >= $giftBoxMinPrice) {
					$giftboxprice = 0;
				}
			}

			$shippingcharge = $this->getUpdatedShipping();
			$giftprice = $giftprice * $currency->value;
			$giftboxprice = $giftboxprice * $currency->value;
			
				$shippingcharge =  Helper::getShippingAmount($total, $shippingcharge);
				
			$final_total = $total + $shippingcharge + $giftprice + $giftboxprice - $couponDiscountFinal;

			$priceUpdate[] = array(
				'id' => $id,
				'size_id' => $sizeId,
				'color_id' => $colorId,
				'price' => Helper::formatNumber($sub_total),
				'total' => Helper::formatNumber($total),
				'shipping' => Helper::formatNumber($shippingcharge),
				'tax' => Helper::formatNumber($taxval),
				'final_total' => Helper::formatNumber($final_total),
				'couponDiscountFinal' => "- " . Helper::formatNumber($couponDiscountFinal),
				'is_active' => $intlShipFlag,
			);
		}
		return $priceUpdate;
	}

	public function getUpdatedShipping() {

		$cart = [];
		$priceUpdate = [];
		$total = 0;
		$user = Auth::user();
		$tax = Helper::getTax();
		if ($user) {
			$cart = ShoppingCartDetail::where('user_id', $user->id)->get();
		} elseif (session('cart')) {
			$cart = ShoppingCartDetail::where('shopping_cart_id', session('cart'))->get();
		}

		$shippingcharge = 0;
		$total_weight = 0;

		foreach ($cart as $id => $record) {
			$intlShipFlag = true;
			$id = $record->product_id;

			$productQuery = AdminProduct::where('id', $id);
			$product = AdminProduct::applyActiveCondition($productQuery, $user)->first();
			if (!$product) {
				continue;
			}
			$sizeId = $record['size_id'];
			$colorId = $record['color_id'];
			$product_weight = $product->product_weight;

			if ($sizeId || $colorId) {
				$attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $colorId);
				if ($attributePrice) {
					$product_weight = $attributePrice->weight;
				}
			}

			$total_weight += $product_weight * $record['quantity'];
		}

		$shipping_charge = 0;
		if ($user) {
			$useraddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
			if ($useraddress) {

				$countryData = AdminCountry::where('id', $useraddress->country_id)->first();
			} else {
				$countryData = AdminCountry::where('is_default', 1)->first();
			}
		} else {
			$countryData = AdminCountry::where('is_default', 1)->first();
		}
		if ($countryData) {
			$shipping_charge = $countryData->shipping_charge;

			if ($user) {

				$address = UserAddress::where('is_default', 1)->where('user_id', $user->id)->first();
				if ($address) {
					if ($address->id != '7') {
						$zoneprice = Helper::getZonePrice($address->city_id);
						if ($zoneprice > 0) {
							$shipping_charge = $zoneprice;
						}
					} else {
						$shipping_charge = 0;
					}
				}

			}
			$currency = Helper::getDefaultCurrency();
			/*
				             *  Convert Currency Rate
			*/
			if ($currency) {
				$shipping_charge = $shipping_charge * $currency->value;
			}
		}

		$roundweight = ceil($total_weight);
 $countryName = 'kuwait';
                           if(isset($address))
                           {
                            $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                           $countryName = $countryName ? $countryName->country_name : 'kuwait';
                           }
		if ($roundweight < 1 ||  strtolower($countryName) =='kuwait' ) {
			$roundweight = 1;
		}

		$shipping_charge = $shipping_charge * $roundweight;
		return Helper::formatNumber($shipping_charge);
	}

}

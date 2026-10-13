<?php

namespace App\Http\Controllers\Web\Cart;

use App\AdminModel\AdminCountry;
use App\AdminModel\Product\AdminProduct;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\UserAddress;
use App\UserModel\TemporaryOrder;
use App\UserModel\TemporaryOrderDetail;
use Auth;
use DB;
use App\Services\FedExService;
use App\Services\ShippingService;
use App\Services\CustomizedOrderSyncService;
use App\Services\PaymentCalculationService;
use App\AdminModel\City\AdminCityDescription;
use App\Exceptions\FedExAddressValidationException;
use App\Exceptions\FedExRateException;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller {

	private $module = '.cart';
	private $langFile = 'web';

	/**
	 * Show the cart .
	 */
	public function index()
	{
		$country  = AdminCountry::getCountry(1);
		$cart = [];
		$totalShipping = 0;
		$customizedOrder = session('customized_order_details');
		if (!empty($customizedOrder) && is_array($customizedOrder)) {
			$sanitizedCustomOrder = $this->sanitizeCustomizedOrderDetails($customizedOrder);

			if (!empty($sanitizedCustomOrder['temporary_order_id'])) {
				$tempDraft = TemporaryOrder::find($sanitizedCustomOrder['temporary_order_id']);
				if ($tempDraft) {
					$linkedOrder = $tempDraft->getLinkedOrder();
					$historicalPaid = $linkedOrder ? PaymentCalculationService::getSuccessfulPaymentTotal($linkedOrder->id) : 0.0;
					$isAdvanceDone = $linkedOrder ? PaymentCalculationService::isAdvancePaymentDone($linkedOrder) : false;
					$paymentState = PaymentCalculationService::getPaymentState($tempDraft, $linkedOrder);

					if ($historicalPaid > 0) {
						$sanitizedCustomOrder['payment']['advance'] = $historicalPaid;
						$sanitizedCustomOrder['payment']['paid'] = $historicalPaid;
						$sanitizedCustomOrder['calculation']['advance'] = $historicalPaid;
						$sanitizedCustomOrder['calculation']['paid'] = $historicalPaid;
					}
					if ($linkedOrder) {
						$sanitizedCustomOrder['order_id'] = $linkedOrder->id;
					}
					$sanitizedCustomOrder['is_advance_payment_done'] = $isAdvanceDone ? 1 : 0;
					$sanitizedCustomOrder['payment_state'] = $paymentState;
					$sanitizedCustomOrder['can_generate_pending_link'] = $paymentState['can_generate_pending_link'];
				}
			}

			if ($sanitizedCustomOrder !== $customizedOrder) {
				session(['customized_order_details' => $sanitizedCustomOrder]);
			}
		}

		$user = Auth::user();
		$address = null;
		$pendingDrafts = collect();
		$activeDraftId = session('customized_order_details.temporary_order_id', null);

		if ($user) {
			$pendingDrafts = TemporaryOrder::where('user_id', $user->id)
				->pending()
				->orderBy('id', 'desc')
				->get();

			$cart = Helper::getShoppingCart($user->id);
			$address = UserAddress::where('is_default', 1)
				->where('user_id', $user->id)
				->first();

			if ($address && count($cart) > 0) {
				$shippingService = new ShippingService();
				try {
					$shippingData = $shippingService->getShippingWithCache(
						$address,
						$cart,
						$user->id
					);
					session()->forget('checkout_error');
				} catch (FedExAddressValidationException | FedExRateException $e) {
					session()->flash('checkout_error', $e->getMessage());
					$shippingData = ['totalShipping' => 0, 'productShipping' => []];
				}
				
				$totalShipping = $shippingData['totalShipping'];
				$productShipping = $shippingData['productShipping'];

				foreach ($cart as $record) {
					$record->shipping_cost = $productShipping[$record->product_id] ?? 0;
				}
			}
		} elseif (session('cart')) {
			$cart = Helper::getShoppingCart();
		}

		return view('web.cart.index', compact('country', 'totalShipping', 'cart', 'pendingDrafts', 'activeDraftId'));
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
						// session::forget('cart');
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

			$product = AdminProduct::find($id);

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

			// Preserve main order total (exclude customized advance), compute coupon on mainTotal
			$mainTotal = $total;
			$customOrderDetails = session('customized_order_details');
			$customAdvance = 0;
			if($customOrderDetails && isset($customOrderDetails['payment']['advance']) && is_numeric($customOrderDetails['payment']['advance'])) {
				$customAdvance = (float) str_replace(',', '', $customOrderDetails['payment']['advance']);
			}
			// total now includes customized advance for final display
			$total = $mainTotal + $customAdvance;

			$couponDiscountFinal = 0;

			if ($appliedCoupon) {
				$couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
				$couponDiscount = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

				if ($couponDiscountIn && $couponDiscount) {
					if ($couponDiscountIn == 'P') {
						$couponDiscountFinal = ($mainTotal * $couponDiscount) / 100;
					} else if ($couponDiscountIn == 'F') {
						$couponDiscountFinal = $mainTotal - $couponDiscount;
					}
				}
			}
			$giftprice = 0;

			$gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
			if (session('gift_wrap')) {
				$setting = \App\Helpers\Helper::getSetting();
				$gift_wrap = session('gift_wrap');
				$giftWrapSetting = $setting->firstWhere('id', 33);
				$giftprice = $giftWrapSetting ? $giftWrapSetting->setting_val : 0;
				$giftMinPrice = \App\Helpers\Helper::getGiftMinPrice() * ($currency->value ?? 1);

				if ($total >= $giftMinPrice) {
					$giftprice = 0;
				}
			}

			$giftboxprice = 0;
			$gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
			if (session('gift_box')) {
				$setting = \App\Helpers\Helper::getSetting();
				$gift_box = session('gift_box');
				$giftBoxSetting = $setting->firstWhere('id', 43);
				$giftboxprice = $giftBoxSetting ? $giftBoxSetting->setting_val : 0;
				$giftboxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice() * ($currency->value ?? 1);

				if ($total >= $giftboxMinPrice) {
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

    public function getUpdatedShipping()
    {
        $user = Auth::user();
        $cart = [];
        $address = null;

        if ($user) {
            $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
            $address = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
        } elseif (session('cart')) {
            $cart = ShoppingCartDetail::where('shopping_cart_id', session('cart'))->get();
        }

        $shippingService = new ShippingService();
        try {
            $shippingData = $shippingService->getShippingWithCache(
        		$address,
    			$cart,
    			$user ? $user->id : null
    		);
        } catch (FedExAddressValidationException | FedExRateException $e) {
            session()->flash('checkout_error', $e->getMessage());
            return 0;
        }

        return $shippingData['totalShipping'];
    }

	public function getCustomizedItem() {
		return view('web.cart.custimized-order-item');
	}

	public function customizedPreview(\Illuminate\Http\Request $request)
	{
		$customizedOrder = session('customized_order_details', []);
		if ($request->filled('temporary_order_id')) {
			$query = TemporaryOrder::where('id', $request->get('temporary_order_id'));
			if (Auth::check()) {
				$query->where('user_id', Auth::id());
			}
			$tempDraft = $query->first();
			if ($tempDraft) {
				$rawDetails = is_array($tempDraft->custom_order_details)
					? $tempDraft->custom_order_details
					: (json_decode($tempDraft->custom_order_details, true) ?: []);
				if (!empty($rawDetails)) {
					$customizedOrder = $rawDetails;
				}
			}
		}

		if (!empty($customizedOrder) && is_array($customizedOrder)) {
			$customizedOrder = $this->sanitizeCustomizedOrderDetails($customizedOrder);
			session(['customized_order_details' => $customizedOrder]);
		}
		$items = $customizedOrder['items'] ?? [];

		if (empty($items)) {
			return redirect()->route('web.cart.index');
		}

		$language = Helper::getLanguage();
		$currency = Helper::getDefaultCurrency();
		$currencyCode = $currency ? $currency->code : config('custom.currency');

		$customer = $customizedOrder['customer'] ?? [];
		$payment = $customizedOrder['payment'] ?? [];
		$type = $customizedOrder['type'] ?? 'purchase_order';

		$subTotal = 0;
		$previewItems = [];
		foreach ($items as $item) {
			$quantity = (float) ($item['quantity'] ?? 0);
			$price = (float) ($item['price'] ?? 0);
			$total = (float) ($item['total'] ?? ($price * $quantity));
			$subTotal += $total;

			$previewItems[] = [
				'item_code' => $item['item_code'] ?? '',
				'product_title' => $item['product_title'] ?? '',
				'description' => $item['description'] ?? '',
				'color' => $item['color'] ?? '',
				'size' => $item['size'] ?? '',
				'price' => $price,
				'quantity' => $quantity,
				'total' => $total,
				'image' => $this->normalizeCustomizedImagePath($item['image'] ?? null),
			];
		}

		$shipping = (float) ($customizedOrder['shipping'] ?? 0);
		$discountType = $customizedOrder['discount_type'] ?? '';
		$discountVal = (float) ($customizedOrder['discount_val'] ?? 0);
		$discountAmount = 0;
		if ($discountType === 'percentage') {
			$discountAmount = ($subTotal * $discountVal) / 100;
		} elseif ($discountType === 'flat') {
			$discountAmount = $discountVal;
		}
		$discountAmount = min($discountAmount, $subTotal + $shipping);
		$grandTotal = max(0, $subTotal + $shipping - $discountAmount);
		$advance = (float) ($payment['advance'] ?? 0);
		$pending = max(0, $grandTotal - $advance);

		$customizedOrder['calculation'] = [
			'sub_total' => $subTotal,
			'shipping' => $shipping,
			'discount_type' => $discountType,
			'discount_value' => $discountVal,
			'discount_amount' => $discountAmount,
			'grand_total' => $grandTotal,
			'advance' => $advance,
			'pending' => $pending,
		];

		$isPurchaseOrder = $type === 'purchase_order';

		// Resolve Temporary Order ID from session, items, request or database
		$temporaryOrderId = $customizedOrder['temporary_order_id'] ?? null;
		if (empty($temporaryOrderId)) {
			foreach ($items as $item) {
				if (!empty($item['temporary_order_id'])) {
					$temporaryOrderId = $item['temporary_order_id'];
					break;
				}
			}
		}
		if (empty($temporaryOrderId) && session()->has('customized_items')) {
			$sessionCustomItems = session('customized_items', []);
			foreach ($sessionCustomItems as $sItem) {
				if (!empty($sItem['temporary_order_id'])) {
					$temporaryOrderId = $sItem['temporary_order_id'];
					break;
				}
			}
		}
		if (empty($temporaryOrderId) && $request->filled('temporary_order_id')) {
			$temporaryOrderId = $request->get('temporary_order_id');
		}

		if (!empty($temporaryOrderId)) {
			$tempRecord = \App\UserModel\TemporaryOrder::find($temporaryOrderId);
			if ($tempRecord) {
				$temporaryOrderId = $tempRecord->id;
				if (empty($customizedOrder['type']) && !empty($tempRecord->type)) {
					$type = $tempRecord->type;
					$isPurchaseOrder = ($type === 'purchase_order');
				}
			}
		}

		$previewSource = $request->get('source', session('customized_preview_source', 'cart'));
		session(['customized_preview_source' => $previewSource]);

		return view('web.cart.customized-preview', compact(
			'customizedOrder',
			'previewItems',
			'customer',
			'payment',
			'type',
			'isPurchaseOrder',
			'subTotal',
			'shipping',
			'discountType',
			'discountVal',
			'discountAmount',
			'grandTotal',
			'advance',
			'pending',
			'currencyCode',
			'language',
			'previewSource',
			'temporaryOrderId'
		));
	}

	public function submitCustomizedOrder(\Illuminate\Http\Request $request) {
		$validator = \Validator::make($request->all(), [
			'cust_phone' => 'required',
			'cust_email' => 'nullable|email',
			'cust_name' => 'nullable',
			'cust_address_title' => 'nullable',
			// structured address fields
			'cust_country_id' => 'nullable',
			'cust_city' => 'nullable',
			'cust_block' => 'nullable',
			'cust_street' => 'nullable',
			'cust_house_number' => 'nullable',
			'cust_note' => 'nullable|string',
			'custom_item_code' => 'nullable|array',
			'custom_item_code.*' => 'nullable',
			'custom_product_title' => 'nullable|array',
			'custom_product_title.*' => 'nullable',
			'custom_desc' => 'nullable|array',
			'custom_desc.*' => 'nullable',
			'custom_price' => 'nullable|array',
			'custom_price.*' => 'nullable|numeric',
			'custom_qty' => 'nullable|array',
			'custom_qty.*' => 'nullable|numeric|min:0',
			'custom_item_existing_image' => 'nullable|array',
			'custom_item_existing_image.*' => 'nullable|string',
			'custom_item_image' => 'nullable|array',
			'custom_item_image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
			'custom_shipping' => 'nullable|numeric',
			'custom_discount_type' => 'nullable|in:flat,percentage',
			'custom_discount_val' => 'nullable|numeric',
			'customer_address_id' => 'nullable',
			'billing_address_id' => 'nullable',
			'temporary_order_id' => 'nullable',
		]);

		if ($validator->fails()) {
			$errors = $validator->errors()->toArray();
			$firstField = array_key_first($errors);

			return response()->json([
				'status' => false,
				'message' => $validator->errors()->first(),
				'field' => $firstField,
				'errors' => $errors,
			], 422);
		}

		$uploadedItemImages = $request->file('custom_item_image', []);
		$existingItemImages = array_map([$this, 'normalizeCustomizedImagePath'], $request->input('custom_item_existing_image', []));
		$activeItemIndexes = $this->getMeaningfulCustomizedItemIndexes($request);

		if (!empty($activeItemIndexes)) {
			$itemValidator = \Validator::make($request->all(), $this->buildMeaningfulCustomizedItemValidationRules($activeItemIndexes));

			if ($itemValidator->fails()) {
				$errors = $itemValidator->errors()->toArray();
				$firstField = array_key_first($errors);

				return response()->json([
					'status' => false,
					'message' => $itemValidator->errors()->first(),
					'field' => $firstField,
					'errors' => $errors,
				], 422);
			}
		}

		$currentItems = session('customized_order_details.items', []);
		$previousItemImages = [];
		foreach ($currentItems as $currentItem) {
			if (!empty($currentItem['image'])) {
				$previousItemImages[] = $currentItem['image'];
			}
		}

		$subTotal = 0;

		foreach (($request->custom_price ?? []) as $key => $price) {
			$qty = floatval($request->input("custom_qty.$key", 0));
			$subTotal += (floatval($price) * $qty);
		}

		$shipping = floatval($request->custom_shipping ?? 0);

		$discountType = $request->custom_discount_type;
		$discountVal  = floatval($request->custom_discount_val ?? 0);

		$discountAmount = 0;

		// Percentage discount validation
		if ($discountType === 'percentage') {
			if ($discountVal > 100) {
				return response()->json([
					'status' => false,
					'message' => 'Percentage discount cannot be more than 100%'
				]);
			}
			$discountAmount = ($subTotal * $discountVal) / 100;
		}

		// Flat discount validation
		if ($discountType === 'flat') {
			if ($discountVal >= $subTotal) {
				return response()->json([
					'status' => false,
					'message' => 'Discount amount cannot be greater than order total'
				]);
			}
			$discountAmount = $discountVal;
		}

		// Safety cap (extra protection)
		$discountAmount = min($discountAmount, $subTotal);

		$grandTotal = $subTotal + $shipping - $discountAmount;

		// Never allow negative total
		$grandTotal = max($grandTotal, 0);

		$advance = floatval($request->advance_payment ?? 0);

		// Check for existing linked order paid advance
		$existingLinkedOrderEarly = null;
		$historicalPaidEarly = 0.0;
		$isAdvanceDoneEarly = false;

		if ($request->filled('temporary_order_id') && Auth::check()) {
			$tempEarly = TemporaryOrder::where('id', $request->temporary_order_id)
				->where('user_id', Auth::id())
				->first();
			if ($tempEarly) {
				$existingLinkedOrderEarly = $tempEarly->getLinkedOrder();
				if ($existingLinkedOrderEarly) {
					$historicalPaidEarly = PaymentCalculationService::getSuccessfulPaymentTotal($existingLinkedOrderEarly->id);
					$isAdvanceDoneEarly = PaymentCalculationService::isAdvancePaymentDone($existingLinkedOrderEarly);
				}
			}
		}

		if ($request->custom_req_type === 'purchase_order') {
			if ($existingLinkedOrderEarly && $isAdvanceDoneEarly && $historicalPaidEarly > 0) {
				if ($grandTotal < $historicalPaidEarly) {
					return response()->json([
						'status' => false,
						'message' => 'Order total cannot be less than the paid advance amount of ' . number_format($historicalPaidEarly, 3, '.', '') . ' KWD.',
						'field' => 'advance_payment'
					], 422);
				}
			} elseif ($advance > 0 && $advance > $grandTotal) {
				return response()->json([
					'status' => false,
					'message' => 'Advance payment must be less than or equal to total amount',
					'field' => 'advance_payment'
				], 422);
			}
		}

		$customerAddressId = $request->customer_address_id ?? $request->billing_address_id ?? null;

		// Store in session (structured address)
		$customizedOrder = [
			'type' => $request->custom_req_type ?: 'purchase_order',
			'customer' => [
				'phone' => $request->cust_phone,
				'email' => $request->cust_email,
				'name' => $request->cust_name,
				'country_id' => $request->cust_country_id,
				'city' => $request->cust_city,
				'city_id' => $request->cust_city_id ?? null,
				'block' => $request->cust_block,
				'street' => $request->cust_street,
				'house_number' => $request->cust_house_number,
				'floor' => $request->cust_floor ?? null,
				'address_detail' => $request->cust_address_detail ?? null,
				'address_url' => $request->cust_address_url ?? null,
				'note' => $request->cust_note ?? null,
				'address_title_text' => $request->cust_address_title ?? null,
				'customer_address_id' => $customerAddressId,
				'billing_address_id' => $customerAddressId,
			],
			'calculation' => [
				'sub_total'       => $subTotal,
				'shipping'        => $shipping,
				'discount_type'   => $discountType,
				'discount_value'  => $discountVal,
				'discount_amount' => $discountAmount,
				'grand_total'     => $grandTotal,
			],
            'items' => [],
            'shipping' => $request->custom_shipping ?? 0,
            'discount_type' => $request->custom_discount_type,
            'discount_val' => $request->custom_discount_val,
            'payment' => [
                 'type' => $request->payment_type,
                 'advance' => $request->advance_payment
            ]
        ];

		$existingReferenceImage = $this->normalizeCustomizedImagePath(session('customized_order_details.reference_image'));
		if (!empty($existingReferenceImage)) {
			$customizedOrder['reference_image'] = $existingReferenceImage;
		}

		$isNewAddress = false;

		if($customerAddressId) {
			// Validate customer_address_id belongs to the user if provided
			$customerAddress = UserAddress::where('id', $customerAddressId)->first();
			if (!$customerAddress) {
				$isNewAddress = true;
			} else {
				// Update existing address if any field is modified
				$hasChanges = false;
				$mappings = [
					'address_title'  => $request->cust_address_title ?? '',
					'first_name'     => $request->cust_name ?? null,
					'email'          => $request->cust_email ?? null,
					'phone'          => $request->cust_phone ?? null,
					'country_id'     => $request->cust_country_id ?? null,
					'city_id'        => $request->cust_city_id ?? null,
					'city'           => $request->cust_city ?? null,
					'block'          => $request->cust_block ?? null,
					'street'         => $request->cust_street ?? null,
					'house_number'   => $request->cust_house_number ?? null,
					'floor'          => $request->cust_floor ?? null,
					'appartment'     => $request->cust_appartment ?? null,
					'address_detail' => $request->cust_address_detail ?? null,
				];

				foreach ($mappings as $column => $value) {
					if ($value !== null && $customerAddress->$column != $value) {
						$customerAddress->$column = $value;
						$hasChanges = true;
					}
				}

				if ($hasChanges) {
					$customerAddress->save();
				}
			}
		}
		else {
			$isNewAddress = true;
		}

		if($isNewAddress) {
			if($request->cust_country_id || $request->cust_city || $request->cust_city_id || $request->cust_block || $request->cust_street || $request->cust_house_number || $request->cust_floor || $request->cust_address_detail || $request->cust_address_url) {
				$user = null;
				if($request->cust_phone) {
					$user = \App\User::where('mobile', $request->cust_phone)->first();
				}
				if(!$user && $request->cust_email) {
					$user = \App\User::where('email', $request->cust_email)->first();
				}

				if($user) {
					$model                 = new UserAddress();
					$model->user_id        = $user->id; //Main Category
					$model->address_title  = $request->cust_address_title ?? "";
					$model->first_name     = $request->cust_name ?? null;
					$model->email          = $request->cust_email ?? null;
					$model->phone          = $request->cust_phone ?? null;
					$model->country_id     = $request->cust_country_id ?? null;
					$model->city_id        = $request->cust_city_id ?? null;
					$model->city           = $request->cust_city ?? null;
					//$model->area           = $request->area;
					$model->block          = $request->cust_block ?? null;
					$model->street         = $request->cust_street ?? null;
					$model->house_number   = $request->cust_house_number ?? null;
					$model->floor          = $request->cust_floor ?? null;
					$model->appartment     = $request->cust_appartment ?? null;
					$model->address_detail = $request->cust_address_detail ?? null;
					$model->is_default     = 1;

					$model->save();
					$customerAddressId = $model->id;
					$customizedOrder['customer']['customer_address_id'] = $model->id;
					$customizedOrder['customer']['billing_address_id'] = $model->id;
				}
			}
		}

		$retainedItemImages = [];
		$customizedItems = [];

		foreach(($request->custom_desc ?? []) as $key => $desc) {
			if ($this->isBlankCustomizedItem($request, $key)) {
				continue;
			}

			$price = floatval($request->input("custom_price.$key", 0));
			$qty   = floatval($request->input("custom_qty.$key", 0));
			$total = round($price * $qty, 3);
			$itemImage = $existingItemImages[$key] ?? null;

			if (isset($uploadedItemImages[$key]) && $uploadedItemImages[$key]) {
				$itemImage = $this->storeCustomizedReferenceImage(
					$uploadedItemImages[$key],
					$itemImage
				);
			}

			if (!empty($itemImage)) {
				$retainedItemImages[] = $itemImage;
			}

			if (empty($customizedOrder['reference_image']) && !empty($itemImage)) {
				$customizedOrder['reference_image'] = $itemImage;
			}

            $customizedItems[] = [
				'item_code'     => $request->input("custom_item_code.$key", ''),
				'product_title' => $request->input("custom_product_title.$key", ''),
				'description'   => $desc,
				'color'         => $request->input("custom_color.$key", ''),
				'size'          => $request->input("custom_size.$key", ''),
				'price'         => $price,
				'quantity'      => $qty,
				'total'         => $total,
				'image'         => $itemImage,
			];
        }

		$customizedOrder['items'] = $customizedItems;

		$this->deleteObsoleteCustomizedImages($previousItemImages, $retainedItemImages);

		DB::beginTransaction();
		try {
			$authUser = Auth::user();
			$tempOrder = null;

			if ($authUser) {
				if ($request->filled('temporary_order_id')) {
					$tempOrder = TemporaryOrder::where('id', $request->temporary_order_id)
						->where('user_id', $authUser->id)
						->first();

					if (!$tempOrder) {
						DB::rollBack();
						return response()->json([
							'status' => false,
							'message' => 'Selected draft order not found.'
						], 404);
					}

					$linkedOrderEarlyCheck = $tempOrder->getLinkedOrder();
					$canEdit = $linkedOrderEarlyCheck ? CustomizedOrderSyncService::canCustomerEdit($linkedOrderEarlyCheck) : in_array($tempOrder->status, ['pending', null], true);
					if (!$canEdit) {
						DB::rollBack();
						return response()->json([
							'status' => false,
							'message' => 'This order is currently being processed and cannot be modified directly.'
						], 422);
					}
				} else {
					$tempOrder = new TemporaryOrder();
					$tempOrder->user_id = $authUser->id;
					$tempOrder->status = 'pending';
				}

				$tempOrder->type = $request->custom_req_type ?: 'purchase_order';
				$tempOrder->customer_name = $request->cust_name;
				$tempOrder->customer_phone = $request->cust_phone;
				$tempOrder->customer_email = $request->cust_email;
				$tempOrder->country_id = $request->cust_country_id ?: null;
				$tempOrder->city_id = $request->cust_city_id ?: null;
				$tempOrder->city = $request->cust_city;
				$tempOrder->block = $request->cust_block;
				$tempOrder->street = $request->cust_street;
				$tempOrder->house_number = $request->cust_house_number;
				$tempOrder->floor = $request->cust_floor;
				$tempOrder->address_detail = $request->cust_address_detail;
				$tempOrder->address_url = $request->cust_address_url;
				$tempOrder->address_title_text = $request->cust_address_title;
				$tempOrder->customer_address_id = $customerAddressId ?: null;
				$tempOrder->note = $request->cust_note;

				$tempOrder->subtotal = $subTotal;
				$tempOrder->shipping_price = $shipping;
				$tempOrder->discount_type = $discountType;
				$tempOrder->discount_val = $discountVal;
				$tempOrder->discount_amount = $discountAmount;
				$tempOrder->grand_total = $grandTotal;

				// Check if this draft is linked to an existing order with historical payment
				$existingLinkedOrder = $tempOrder->getLinkedOrder();
				$historicalPaidAmount = $existingLinkedOrder ? PaymentCalculationService::getSuccessfulPaymentTotal($existingLinkedOrder->id) : 0.0;
				$isAdvanceDone = false;
				if ($existingLinkedOrder) {
					$isAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($existingLinkedOrder);
				}

				// STAGE 2 Validation: If advance payment is done, customer cannot reduce grand total below paid advance
				if ($existingLinkedOrder && $isAdvanceDone && $historicalPaidAmount > 0 && $grandTotal < $historicalPaidAmount) {
					DB::rollBack();
					return response()->json([
						'status' => false,
						'message' => 'Order total cannot be less than the paid advance amount of ' . number_format($historicalPaidAmount, 3, '.', '') . ' KWD.',
						'field' => 'advance_payment'
					], 422);
				}

				$effectiveAdvance = ($historicalPaidAmount > 0) ? $historicalPaidAmount : $advance;
				$beforeAdvance = $tempOrder->advance_price;
				$beforePending = $tempOrder->pending_amount;

				$tempOrder->advance_price = $effectiveAdvance;
				$tempOrder->pending_amount = max(0.0, round($grandTotal - $effectiveAdvance, 3));
				$tempOrder->payment_type = $request->payment_type;

				$isCreateMode = !$request->filled('temporary_order_id');
				$generatePendingLinkReq = (bool) $request->input('generate_pending_payment_link', $request->input('generate_pending_link', 0));

				$isAdvanceDone = false;
				if ($existingLinkedOrder) {
					$isAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($existingLinkedOrder);
				}

				$isAdvanceRequired = ($effectiveAdvance > 0.0001);
				$canGeneratePendingLink = ($tempOrder->pending_amount > 0.0001) && (!$isAdvanceRequired || $isAdvanceDone);

				if ($isCreateMode) {
					$generatePendingLinkAllowed = 0;
				} else {
					$generatePendingLinkAllowed = ($generatePendingLinkReq && $canGeneratePendingLink) ? 1 : 0;
				}

				if ($existingLinkedOrder) {
					$customizedOrder['order_id'] = $existingLinkedOrder->id;
				}
				$customizedOrder['temporary_order_id'] = $tempOrder->id;
				$customizedOrder['is_advance_payment_done'] = $isAdvanceDone ? 1 : 0;
				$customizedOrder['can_generate_pending_link'] = $canGeneratePendingLink ? 1 : 0;
				if ($existingLinkedOrder) {
					$customizedOrder['payment_state'] = PaymentCalculationService::getPaymentState($tempOrder, $existingLinkedOrder);
				}
				$customizedOrder['payment']['advance'] = $effectiveAdvance;
				$customizedOrder['payment']['paid'] = $historicalPaidAmount;
				$customizedOrder['payment']['pending'] = $tempOrder->pending_amount;
				$customizedOrder['payment']['generate_pending_link'] = $generatePendingLinkAllowed;
				$customizedOrder['calculation']['advance'] = $effectiveAdvance;
				$customizedOrder['calculation']['paid'] = $historicalPaidAmount;
				$customizedOrder['calculation']['pending'] = $tempOrder->pending_amount;
				$customizedOrder['calculation']['generate_pending_link'] = $generatePendingLinkAllowed;
				$customizedOrder['generate_pending_link'] = $generatePendingLinkAllowed;

				$tempOrder->custom_order_details = json_encode($customizedOrder);
				$tempOrder->save();

				PaymentCalculationService::logPaymentDebug([
					'order_id' => $existingLinkedOrder ? $existingLinkedOrder->id : 'N/A',
					'temporary_order_id' => $tempOrder->id,
					'user_id' => $tempOrder->user_id,
					'field' => 'temporary_orders.advance_price',
					'before' => $beforeAdvance,
					'request' => $advance,
					'calculated' => $effectiveAdvance,
					'written' => $tempOrder->advance_price,
					'source' => 'CartController::submitCustomizedOrder',
				]);

				PaymentCalculationService::logPaymentDebug([
					'order_id' => $existingLinkedOrder ? $existingLinkedOrder->id : 'N/A',
					'temporary_order_id' => $tempOrder->id,
					'user_id' => $tempOrder->user_id,
					'field' => 'temporary_orders.pending_amount',
					'before' => $beforePending,
					'request' => $request->advance_payment,
					'calculated' => $tempOrder->pending_amount,
					'written' => $tempOrder->pending_amount,
					'source' => 'CartController::submitCustomizedOrder',
				]);

				if (empty($tempOrder->custom_order_number)) {
					$tempOrder->custom_order_number = (string) $tempOrder->id;
					$tempOrder->save();
				}

				// Synchronize details
				TemporaryOrderDetail::where('temporary_order_id', $tempOrder->id)->forceDelete();
				foreach ($customizedItems as $sortIdx => $item) {
					TemporaryOrderDetail::create([
						'temporary_order_id' => $tempOrder->id,
						'product_name' => $item['product_title'] ?? '',
						'item_code' => $item['item_code'] ?? '',
						'description' => $item['description'] ?? '',
						'color' => $item['color'] ?? '',
						'size' => $item['size'] ?? '',
						'price' => $item['price'] ?? 0,
						'quantity' => $item['quantity'] ?? 1,
						'total_price' => $item['total'] ?? 0,
						'image' => $item['image'] ?? '',
						'sort_order' => $sortIdx,
					]);
				}

				// If linked to an actual Order, synchronize the Actual Order and OrderDetails
				$linkedOrder = $tempOrder->getLinkedOrder();
				if ($linkedOrder) {
					if (!CustomizedOrderSyncService::canCustomerEdit($linkedOrder)) {
						DB::rollBack();
						return response()->json([
							'status' => false,
							'message' => 'This order is currently being processed and cannot be modified directly.'
						], 422);
					}

					$syncResult = CustomizedOrderSyncService::syncDraftToActualOrder(
						$tempOrder,
						$linkedOrder,
						$customizedItems,
						[
							'subtotal' => $subTotal,
							'shipping' => $shipping,
							'discount_amount' => $discountAmount,
							'discount_type' => $discountType,
							'discount_val' => $discountVal,
						],
						$request->input('expected_updated_at')
					);

					if (!$syncResult['status']) {
						DB::rollBack();
						return response()->json([
							'status' => false,
							'message' => $syncResult['message']
						], $syncResult['code'] ?? 500);
					}
				}

				$customizedOrder['temporary_order_id'] = $tempOrder->id;
			}

			// Append to existing or create new for session
			$currentCustomItems = array_values(array_filter(session('customized_items', []), function ($item) {
				return !$this->isBlankCustomizedItemRow((array) $item);
			}));
			foreach ($customizedOrder['items'] as $item) {
				$item['customer'] = $customizedOrder['customer'];
				$item['request_type'] = $customizedOrder['type'];
				$item['address_title'] = $customizedOrder['customer']['address_title'] ?? null;
				if ($tempOrder) {
					$item['temporary_order_id'] = $tempOrder->id;
				}
				$currentCustomItems[] = $item;
			}

			session(['customized_items' => $currentCustomItems]);
			$customizedOrder = $this->sanitizeCustomizedOrderDetails($customizedOrder);
			if (empty($customizedOrder['items'])) {
				session()->forget('customized_order_details');
				session()->forget('customized_items');
				DB::commit();
				return response()->json(['status' => true, 'message' => trans('web.customized-message-success')]);
			}

			session(['customized_order_details' => $customizedOrder]);
			if (Auth::check() && (Auth::user()->id == config('config.store_admin_id') || (isset(Auth::user()->user_type) && Auth::user()->user_type == "physical_store_user"))) {
				session()->forget('address_selected');
			}

			DB::commit();

			return response()->json([
				'status' => true,
				'message' => trans('web.customized-message-success'),
				'data' => [
					'temporary_order_id' => $tempOrder ? $tempOrder->id : null,
					'formatted_number' => $tempOrder ? $tempOrder->formatted_document_number : null,
				]
			]);
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json([
				'status' => false,
				'message' => 'Failed to save draft order: ' . $e->getMessage(),
			], 500);
		}
	}

	public function updateCustomizedOrderQuantity(\Illuminate\Http\Request $request)
	{
		$validator = \Validator::make($request->all(), [
			'index' => 'required|integer|min:0',
			'quantity' => 'required|integer|min:1',
		]);

		if ($validator->fails()) {
			return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
		}

		$customizedOrder = session('customized_order_details');
		if (empty($customizedOrder) || empty($customizedOrder['items'])) {
			return response()->json(['status' => false, 'message' => 'Customized order not found']);
		}

		$index = (int) $request->index;
		$quantity = (int) $request->quantity;
		$quantity = max(1, $quantity);

		$items = array_values($customizedOrder['items']);
		if (!isset($items[$index])) {
			return response()->json(['status' => false, 'message' => 'Customized order item not found']);
		}

		$items[$index]['quantity'] = $quantity;
		$price = floatval($items[$index]['price'] ?? 0);
		$items[$index]['total'] = round($price * $quantity, 3);
		$customizedOrder['items'] = $items;

		$subTotal = 0;
		foreach (($customizedOrder['items'] ?? []) as $item) {
			$subTotal += floatval($item['total'] ?? 0);
		}

		$shipping = floatval($customizedOrder['shipping'] ?? 0);
		$discountType = $customizedOrder['discount_type'] ?? '';
		$discountVal = floatval($customizedOrder['discount_val'] ?? 0);
		$discountAmount = 0;

		if ($discountType === 'percentage') {
			$discountAmount = ($subTotal * $discountVal) / 100;
		} elseif ($discountType === 'flat') {
			$discountAmount = $discountVal;
		}

		$discountAmount = min($discountAmount, $subTotal);
		$grandTotal = max(0, $subTotal + $shipping - $discountAmount);
		$advance = floatval($customizedOrder['payment']['advance'] ?? 0);
		$pending = max(0, $grandTotal - $advance);

		$customizedOrder['calculation'] = [
			'sub_total' => $subTotal,
			'shipping' => $shipping,
			'discount_type' => $discountType,
			'discount_value' => $discountVal,
			'discount_amount' => $discountAmount,
			'grand_total' => $grandTotal,
			'advance' => $advance,
			'pending' => $pending,
		];

		$customizedOrder = $this->sanitizeCustomizedOrderDetails($customizedOrder);
		if (empty($customizedOrder['items'])) {
			session()->forget('customized_order_details');
			session()->forget('customized_items');
			return response()->json([
				'status' => false,
				'message' => 'Customized order item not found'
			]);
		}

		session(['customized_order_details' => $customizedOrder]);

		if ($request->expectsJson() || $request->ajax()) {
			return response()->json([
				'status' => true,
				'message' => trans('web.customized-message-success'),
				'data' => [
					'quantity' => $quantity,
					'item_total' => round($price * $quantity, 3),
					'sub_total' => $subTotal,
					'grand_total' => $grandTotal,
					'pending' => $pending,
				]
			]);
		}

		return redirect()->back();
	}

	private function storeCustomizedReferenceImage($file, $existingPath = null)
	{
		$filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
		$filename = trim($filename, '_');
		$filename = $filename ?: 'reference_image';

		$fileNameToStore = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
		$directory = public_path('storage/customized');
		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}

		$file->move($directory, $fileNameToStore);
		$path = 'customized/' . $fileNameToStore;

		$existingPath = $this->normalizeCustomizedImagePath($existingPath);
		if ($path && $existingPath) {
			$this->deleteCustomizedImageFile($existingPath);
		}

		return $path;
	}

	private function sanitizeCustomizedOrderDetails(array $customizedOrder): array
	{
		$items = $customizedOrder['items'] ?? [];
		$customizedOrder['items'] = [];

		foreach ($items as $item) {
			if ($this->isBlankCustomizedItemRow($item)) {
				continue;
			}

			$customizedOrder['items'][] = $item;
		}

		$customizedOrder['items'] = array_values($customizedOrder['items']);

		return $customizedOrder;
	}

	private function isBlankCustomizedItem($request, $key): bool
	{
		$uploadedFiles = $request->file('custom_item_image', []);
		$existingImages = $request->custom_item_existing_image ?? [];

		if (!empty($uploadedFiles[$key]) || !empty($existingImages[$key])) {
			return false;
		}

		$textFields = [
			$request->input("custom_item_code.$key", ''),
			$request->input("custom_product_title.$key", ''),
			$request->input("custom_desc.$key", ''),
			$request->input("custom_color.$key", ''),
			$request->input("custom_size.$key", ''),
		];

		foreach ($textFields as $field) {
			if (trim((string) $field) !== '') {
				return false;
			}
		}

		$numericFields = [
			$request->input("custom_price.$key", ''),
			$request->input("custom_qty.$key", ''),
		];

		foreach ($numericFields as $field) {
			if (is_numeric($field) && (float) $field > 0) {
				return false;
			}
		}

		return true;
	}

	private function isBlankCustomizedItemRow(array $item): bool
	{
		$textFields = [
			$item['item_code'] ?? '',
			$item['product_title'] ?? '',
			$item['description'] ?? '',
			$item['color'] ?? '',
			$item['size'] ?? '',
			$item['image'] ?? '',
		];

		foreach ($textFields as $field) {
			if (trim((string) $field) !== '') {
				return false;
			}
		}

		$numericFields = [
			$item['price'] ?? '',
			$item['quantity'] ?? '',
		];

		foreach ($numericFields as $field) {
			if (is_numeric($field) && (float) $field > 0) {
				return false;
			}
		}

		return true;
	}

	private function getMeaningfulCustomizedItemIndexes(\Illuminate\Http\Request $request): array
	{
		$indexes = [];

		foreach ([
			$request->input('custom_item_code', []),
			$request->input('custom_product_title', []),
			$request->input('custom_desc', []),
			$request->input('custom_color', []),
			$request->input('custom_size', []),
			$request->input('custom_price', []),
			$request->input('custom_qty', []),
			$request->input('custom_item_existing_image', []),
			$request->file('custom_item_image', []),
		] as $group) {
			if (is_array($group)) {
				$indexes = array_merge($indexes, array_keys($group));
			}
		}

		$indexes = array_values(array_unique($indexes));
		sort($indexes);

		return array_values(array_filter($indexes, function ($index) use ($request) {
			return $this->hasMeaningfulCustomizedItemValue($request, $index);
		}));
	}

	private function hasMeaningfulCustomizedItemValue(\Illuminate\Http\Request $request, $index): bool
	{
		$uploadedFiles = $request->file('custom_item_image', []);
		if (!empty($uploadedFiles[$index])) {
			return true;
		}

		$existingImages = $request->input('custom_item_existing_image', []);
		if (!empty($existingImages[$index])) {
			return true;
		}

		$textFields = [
			$request->input("custom_item_code.$index", ''),
			$request->input("custom_product_title.$index", ''),
			$request->input("custom_desc.$index", ''),
			$request->input("custom_color.$index", ''),
			$request->input("custom_size.$index", ''),
		];

		foreach ($textFields as $field) {
			if (trim((string) $field) !== '') {
				return true;
			}
		}

		$numericFields = [
			$request->input("custom_price.$index", ''),
			$request->input("custom_qty.$index", ''),
		];

		foreach ($numericFields as $field) {
			if (is_numeric($field) && (float) $field > 0) {
				return true;
			}
		}

		return false;
	}

	private function buildMeaningfulCustomizedItemValidationRules(array $activeItemIndexes): array
	{
		$rules = [];

		foreach ($activeItemIndexes as $index) {
			$rules["custom_qty.$index"] = 'required|integer|min:1';
			$rules["custom_price.$index"] = 'nullable|numeric|min:0';
			$rules["custom_item_code.$index"] = 'nullable|string';
			$rules["custom_product_title.$index"] = 'nullable|string';
			$rules["custom_desc.$index"] = 'nullable|string';
			$rules["custom_color.$index"] = 'nullable|string';
			$rules["custom_size.$index"] = 'nullable|string';
			$rules["custom_item_existing_image.$index"] = 'nullable|string';
			$rules["custom_item_image.$index"] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';
		}

		return $rules;
	}

	private function deleteObsoleteCustomizedImages(array $previousPaths, array $retainedPaths)
	{
		$pathsToDelete = array_diff(
			array_filter($previousPaths),
			array_filter($retainedPaths)
		);

		foreach ($pathsToDelete as $path) {
			$this->deleteCustomizedImageFile($path);
		}
	}

	private function normalizeCustomizedImagePath($path)
	{
		if (!$path) {
			return null;
		}

		$path = str_replace('\\', '/', $path);
		return ltrim(preg_replace('/^public\//', '', $path), '/');
	}

	private function deleteCustomizedImageFile($path)
	{
		$path = $this->normalizeCustomizedImagePath($path);
		if (!$path) {
			return;
		}

		$publicFile = public_path('storage/' . $path);
		if (file_exists($publicFile)) {
			@unlink($publicFile);
		}

		$legacyStoragePath = 'public/' . $path;
		if (Storage::disk('local')->exists($legacyStoragePath)) {
			Storage::disk('local')->delete($legacyStoragePath);
		}
	}

    public function getCustomerDetails(\Illuminate\Http\Request $request) {
        $phone = $request->phone;
        $email = $request->email;

        $user = null;
        if($phone) {
             $user = \App\User::where('mobile', $phone)->first();
        }
        if(!$user && $email) {
             $user = \App\User::where('email', $email)->first();
        }

		if($user) {
			 // Get default address
			 $address = $user->getUserDefaultAddress();

			 return response()->json([
				 'status' => true,
				 'data' => [
					'name' => $user->first_name . ' ' . $user->last_name,
					'email' => $user->email,
					'phone' => $user->mobile,
					'address' => $address
				 ]
			 ]);
		}

        return response()->json(['status' => false]);
    }

    public function removeCustomizedOrder() {
        session()->forget('customized_items');
        session()->forget('customized_order_details');
        return response()->json(['status' => true]);
    }

	public function getCustomerAddressDetails(\Illuminate\Http\Request $request)
	{
		$phone = $request->phone;
		$email = $request->email;

		$user = null;

		if ($phone) {
			$user = \App\User::where('mobile', $phone)->first();
		}

		if (!$user && $email) {
			$user = \App\User::where('email', $email)->first();
		}

		if ($user) {

			$pickupUser = \App\User::where('pickupstore', 1)->pluck('id');

			$address = \App\UserModel\UserAddress::where('user_id', $user->id)->where('is_active', 1);
			if ($pickupUser) {
				$address->orWhereIn('user_id', $pickupUser)->where('is_active', 1);
			}
			$addresses = $address->orderBy('pickupstore', 'desc')->orderBy('created_at', 'desc')->get();

			$defaultAddress = $addresses->where('is_default', 1)->first();

			return response()->json([
				'status' => true,
				'data' => [
					'name' => $user->first_name . ' ' . $user->last_name,
					'email' => $user->email,
					'phone' => $user->mobile,
					'addresses' => $addresses,
					'default_address' => $defaultAddress,
				]
			]);
		}

		return response()->json(['status' => false]);
	}

	public function getPendingTemporaryOrders()
	{
		$user = Auth::user();
		if (!$user) {
			return response()->json(['status' => false, 'message' => 'Unauthenticated', 'data' => []], 401);
		}

		$drafts = TemporaryOrder::where('user_id', $user->id)
			->pending()
			->orderBy('id', 'desc')
			->get();

		$data = $drafts->map(function ($draft) {
			return [
				'id' => $draft->id,
				'type' => $draft->type,
				'formatted_number' => $draft->formatted_document_number,
				'customer_name' => $draft->customer_name,
				'created_at' => $draft->created_at ? $draft->created_at->format('d M Y, h:i A') : '',
				'grand_total' => $draft->grand_total,
			];
		});

		return response()->json(['status' => true, 'data' => $data]);
	}

	public function getTemporaryOrderDetail($id)
	{
		$user = Auth::user();
		if (!$user) {
			return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
		}

		$draft = TemporaryOrder::with('details')
			->where('id', $id)
			->where('user_id', $user->id)
			->where('status', 'pending')
			->first();

		if (!$draft) {
			return response()->json(['status' => false, 'message' => 'Draft order not found or unauthorized'], 404);
		}

		$items = [];
		foreach ($draft->details as $detail) {
			$normalizedImg = $this->normalizeCustomizedImagePath($detail->image);
			$items[] = [
				'id' => $detail->id,
				'product_id' => $detail->product_id,
				'item_code' => $detail->item_code ?? '',
				'product_title' => $detail->product_name ?? '',
				'description' => $detail->description ?? '',
				'color' => $detail->color ?? '',
				'size' => $detail->size ?? '',
				'price' => (float)$detail->price,
				'quantity' => (int)$detail->quantity,
				'total' => (float)$detail->total_price,
				'image' => $normalizedImg,
				'image_url' => !empty($normalizedImg) ? asset(\Illuminate\Support\Facades\Storage::url($normalizedImg)) : asset(\Illuminate\Support\Facades\Storage::url('default.png')),
			];
		}

		$customer = [
			'phone' => $draft->customer_phone,
			'email' => $draft->customer_email,
			'name' => $draft->customer_name,
			'country_id' => $draft->country_id,
			'city' => $draft->city,
			'city_id' => $draft->city_id,
			'block' => $draft->block,
			'street' => $draft->street,
			'house_number' => $draft->house_number,
			'floor' => $draft->floor,
			'address_detail' => $draft->address_detail,
			'address_url' => $draft->address_url,
			'note' => $draft->note,
			'address_title_text' => $draft->address_title_text,
			'customer_address_id' => $draft->customer_address_id,
		];

		$linkedOrder = $draft->getLinkedOrder();
		$isEditable = true;
		$orderId = null;
		$orderStatus = null;
		$totalPaid = 0.0;
		$pendingAmount = (float)$draft->pending_amount;
		$excessCredit = 0.0;

		if ($linkedOrder) {
			$orderId = $linkedOrder->id;
			$orderStatus = $linkedOrder->order_status;
			$isEditable = CustomizedOrderSyncService::canCustomerEdit($linkedOrder);
			$calc = PaymentCalculationService::calculateOrderPayments($linkedOrder);
			$totalPaid = $calc['total_paid'];
			$pendingAmount = $calc['pending_amount'];
			$excessCredit = max(0.0, round($totalPaid - $calc['total_amount'], 3));
		}

		$advanceVal = ($totalPaid > 0) ? $totalPaid : (float)$draft->advance_price;

		$calculation = [
			'sub_total' => (float)$draft->subtotal,
			'shipping' => (float)$draft->shipping_price,
			'discount_type' => $draft->discount_type,
			'discount_value' => (float)$draft->discount_val,
			'discount_amount' => (float)$draft->discount_amount,
			'grand_total' => (float)$draft->grand_total,
			'advance' => $advanceVal,
			'paid' => $totalPaid,
			'pending' => $pendingAmount,
			'excess_credit' => $excessCredit,
		];

		$payment = [
			'type' => $draft->payment_type,
			'advance' => $advanceVal,
			'paid' => $totalPaid,
			'pending' => $pendingAmount,
		];

		$paymentState = PaymentCalculationService::getPaymentState($draft, $linkedOrder);

		$data = [
			'id' => $draft->id,
			'type' => $draft->type,
			'formatted_number' => $draft->formatted_document_number,
			'customer' => $customer,
			'calculation' => $calculation,
			'items' => $items,
			'shipping' => (float)$draft->shipping_price,
			'discount_type' => $draft->discount_type,
			'discount_val' => (float)$draft->discount_val,
			'payment' => $payment,
			'payment_state' => $paymentState,
			'is_advance_payment_done' => $paymentState['advance_completed'] ? 1 : 0,
			'can_generate_pending_link' => $paymentState['can_generate_pending_link'],
			'temporary_order_id' => $draft->id,
			'order_id' => $orderId,
			'order_status' => $orderStatus,
			'is_editable' => $isEditable,
			'last_updated_at' => $draft->updated_at ? $draft->updated_at->toDateTimeString() : null,
		];

		return response()->json(['status' => true, 'data' => $data]);
	}
}



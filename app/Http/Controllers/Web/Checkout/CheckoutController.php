<?php

namespace App\Http\Controllers\Web\Checkout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserModel\UserAddress;
use App\AdminModel\City\AdminCity;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductDescription;
use App\AdminModel\Product\AdminProductAttribute;
use App\UserModel\Order;
use App\UserModel\OrderDetail;
use App\UserModel\OrderMonogramDetail;
use App\UserModel\Cart\ShoppingCart;
use App\UserModel\Cart\ShoppingCartDetail;
use App\UserModel\TemporaryOrder;
use App\AdminModel\AdminCountry;
use App\AdminModel\Order\AdminOrderStatus;
use Redirect;
use DB;
use URL;
use Session;
use Illuminate\Support\Facades\Lang;
use App\Helpers\Helper;
use App\UserModel\PaymentMethod;
use App\AdminModel\Order\PaymentLink;
use App\AdminModel\Order\OrderPaymentLog;
use App\Services\FedExService;
use App\AdminModel\City\AdminCityDescription;
use App\Services\PaymentCalculationService;
use App\Services\CustomizedOrderSyncService;

use App\Http\Controllers\Web\Cart\CartController;
use App\Services\ShippingService;
use App\AdminModel\CourierCompany\CourierCompany;
use Facade\FlareClient\View;

class CheckoutController extends Controller
{

    private $module   = '.checkout';
    private $langFile = 'web';

    public function __construct() {}

    private function getSuccessfulPaymentTotal($orderId)
    {
        return PaymentCalculationService::getSuccessfulPaymentTotal($orderId);
    }

    private function getCalculatedPendingAmount($order, $successfulPaidTotal = null)
    {
        if ($successfulPaidTotal !== null) {
            return round(max(0, (float) $order->order_total - (float) $successfulPaidTotal), 3);
        }
        $summary = PaymentCalculationService::calculateOrderPayments($order);
        return $summary['pending_amount'];
    }

    private function buildSplitPaymentMeta($order, $finalTotal, $customizedAdvance)
    {
        if (
            !$order ||
            $order->order_type === 'custom' ||
            (float) $customizedAdvance <= 0
        ) {
            return null;
        }

        $customOrder = Order::where('parent_id', $order->id)
            ->where('order_type', 'custom')
            ->orderBy('id', 'desc')
            ->first();

        if (!$customOrder) {
            return null;
        }

        $customAdvanceAmount = round(min((float) $customizedAdvance, (float) $finalTotal), 3);
        $regularAmount = round(max(0, (float) $finalTotal - (float) $customAdvanceAmount), 3);

        if ($regularAmount <= 0 && $customAdvanceAmount <= 0) {
            return null;
        }

        $isCustomAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($customOrder);

        return [
            'parent_order_id' => (int) $order->id,
            'regular_amount' => $regularAmount,
            'custom_order_id' => (int) $customOrder->id,
            'custom_advance_amount' => $customAdvanceAmount,
            'is_advance_payment' => $isCustomAdvanceDone ? 0 : 1,
        ];
    }

    private function applySplitPaymentToOrders($paymentLink, $parentOrder, array $splitPayment)
    {
        $regularAmount = round((float) ($splitPayment['regular_amount'] ?? 0), 3);
        $customAdvanceAmount = round((float) ($splitPayment['custom_advance_amount'] ?? 0), 3);
        $customOrderId = (int) ($splitPayment['custom_order_id'] ?? 0);
        $isAdvanceFlag = isset($splitPayment['is_advance_payment']) ? (int)$splitPayment['is_advance_payment'] : 1;

        if ($regularAmount > 0) {
            $parentOrder->pending_amount = max(0, round((float) $parentOrder->pending_amount - $regularAmount, 3));
            $parentOrder->paid_amount = round((float) $parentOrder->paid_amount + $regularAmount, 3);
            $parentOrder->save();
        }

        if ($customOrderId <= 0 || $customAdvanceAmount <= 0) {
            return;
        }

        $customOrder = Order::find($customOrderId);
        if (!$customOrder) {
            return;
        }

        $derivedPaymentRequestId = $paymentLink->payment_request_id . '_custom_' . $customOrder->id;

        PaymentLink::updateOrCreate(
            ['payment_request_id' => $derivedPaymentRequestId],
            [
                'order_id' => $customOrder->id,
                'payment_link' => $paymentLink->payment_link,
                'amount' => $customAdvanceAmount,
                'generated_from' => 'system',
                'generated_by' => $paymentLink->generated_by,
                'status' => 'paid',
                'paid_amount' => $customAdvanceAmount,
                'paid_at' => $paymentLink->paid_at ?: now(),
                'gateway_response' => [
                    'source' => 'split_from_parent_payment',
                    'parent_payment_link_id' => $paymentLink->id,
                    'parent_payment_request_id' => $paymentLink->payment_request_id,
                    'split_payment' => $splitPayment,
                ],
                'payment_type' => $paymentLink->payment_type,
                'is_advance_payment' => $isAdvanceFlag,
                'customer_email' => $paymentLink->customer_email,
                'email_sent_count' => 0,
                'last_email_sent_at' => null,
            ]
        );

        $successfulPaidTotal = PaymentCalculationService::getSuccessfulPaymentTotal($customOrder->id);
        $customOrder->paid_amount = $successfulPaidTotal;
        $customOrder->pending_amount = max(0.0, round((float)$customOrder->order_total - $successfulPaidTotal, 3));
        if ($successfulPaidTotal > 0) {
            $customOrder->is_advance_payment_done = 1;
        }
        $customOrder->save();

        // Sync linked TemporaryOrder
        CustomizedOrderSyncService::syncActualOrderToDraft($customOrder);
    }

    private function invalidateParentMixedPaymentLinks($order)
    {
        if (!$order || empty($order->parent_id)) {
            return;
        }

        $pendingParentLinks = PaymentLink::where('order_id', $order->parent_id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingParentLinks as $paymentLink) {
            $gatewayResponse = is_array($paymentLink->gateway_response)
                ? $paymentLink->gateway_response
                : (json_decode($paymentLink->gateway_response, true) ?: []);

            $splitPayment = $gatewayResponse['split_payment'] ?? null;
            if (
                is_array($splitPayment) &&
                (int) ($splitPayment['custom_order_id'] ?? 0) === (int) $order->id
            ) {
                $gatewayResponse['invalidated_reason'] = 'custom_order_paid_separately';
                $gatewayResponse['invalidated_custom_order_id'] = (int) $order->id;
                $gatewayResponse['invalidated_at'] = now()->toDateTimeString();

                $paymentLink->status = 'rejected';
                $paymentLink->gateway_response = $gatewayResponse;
                $paymentLink->save();
            }
        }
    }

    /**
     * Show the checkout .
     */
    public function index()
    {
        $user = Auth::user();

        if ($user) {
            Session::forget('checkout_error');

            $cart = [];
            $customCart = [];

            $language = Helper::getLanguage();
            if (session('cart')) {
                // get cart from session
                $cart = session('cart');
            }
            if (session('customized_order_details')) {
                // get cart from session
                $customCart = session('customized_order_details');
                $customCart = isset($customCart) && isset($customCart['items']) && count($customCart['items']) > 0 ? true : false;
            }
            if ($user) {    // get cart from db
                $cart = ShoppingCartDetail::where('user_id', $user->id)->get();
            }
            //if user not login get product from session

            if (count($cart) == 0 && $customCart == false) {
                // if cart is empty
                Session::forget('couponCode');
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.cart-error-message'));
            } elseif (count($cart) == 0 && $customCart) {
                return redirect()->route('web.checkout.cart_review');
            } else {
                // check product quantity 
                $flag = true;
                $text = '';
                foreach ($cart as $id => $record) {
                    if ($user) {
                        $id = $record->product_id;
                    }
                    $product = AdminProduct::find($id);
                    if (!$product) {
                        continue;
                    }
                    $qty           = $record['quantity'];
                    $productdetail = $product->getProductDescription($language->id);
                    if ((int) $qty > (int) $product->product_quantity) {
                        $flag = false;
                        $text .= $productdetail->products_name . ', ';
                    } else if ($record->size_id || $record->color_id) {
                        /*
                         * Check, If given size have quantity available or not
                         */
                        $sizeHasQuantityCheck = AdminProductAttribute::where(['products_id' => $record->product_id])->where('quantity', '>=', (int) $qty);
                        if ($record->size_id) {
                            $sizeHasQuantityCheck->where('size_id', $record->size_id);
                        }
                        if ($record->color_id) {
                            $sizeHasQuantityCheck->where('color_id', $record->color_id);
                        }

                        $sizeHasQuantityCheck = $sizeHasQuantityCheck->first();

                        if (!$sizeHasQuantityCheck) {
                            $flag = false;
                            $text .= $productdetail->products_name . ', ';
                        }
                    }
                }

                if ($flag == false) {
                    $text = rtrim($text, ", ");
                    return redirect()->back()->with('error', $text . Lang::get($this->langFile . '.out_of_stock'));
                }
            }

            $country  = AdminCountry::getCountry(1);
            $autoSelectDefault = !($this->isPhysicalStoreUser($user) && session()->has('customized_order_details'));
            if ($autoSelectDefault) {
                $selectedAddress = $this->resolveSelectedShippingAddress($user);
                if ($selectedAddress) {
                    session()->put('default_address', $selectedAddress->id);
                } else {
                    Session::forget('default_address');
                }
            }

            $pickupUser = \App\User::where('pickupstore', 1)->pluck('id');

            $address = UserAddress::where('user_id', $user->id)->where('is_active', 1);
            if ($pickupUser) {
                $address->orWhereIn('user_id', $pickupUser)->where('is_active', 1);
            }
            $address = $address->orderBy('pickupstore', 'desc')->orderBy('created_at', 'desc')->get();
            $language = Helper::getLanguage();



            //if user login get product from db
            return view('web.checkout.index', compact('country', 'address', 'cart', 'language'));
        } else {
            return redirect()->route('web.user.login')->with('redirect_url', url()->current());
        }
    }

    public function cartReview()
    {
        $user     = Auth::user();
        $country  = AdminCountry::getCountry(1);
        $cart     = [];
        $customCart = [];
        $language = Helper::getLanguage();
        $currency = Helper::getDefaultCurrency();
        $totalShipping = 0;

        if (request()->filled('cphone')) {
            session()->put('cphone', request()->cphone);
        }

        $pickupStoreAddress = session('default_address');

        if (session('customized_order_details')) {
            $customCart = session('customized_order_details');
            $customCart = isset($customCart['items']) && count($customCart['items']) > 0 ? true : false;
        }

        $cart = ShoppingCartDetail::where('user_id', $user->id)->get();

        if (count($cart) > 0 || $customCart) {

            // =========================
            // ADDRESS VALIDATION BLOCK
            // =========================
            if (count($cart) > 0) {

                $useraddress = $this->resolveSelectedShippingAddress($user);

                if (!$useraddress) {
                    return redirect()->route('web.checkout.index')
                        ->with('error', 'please enter address');
                }

                if (($pickupStoreAddress == 43 || ($useraddress->pickupstore ?? 0) == 1) && !request()->cphone && !session('cphone')) {
                    return redirect()->route('web.checkout.index')
                        ->with('error', 'please enter customer phone');
                }

                if (!$useraddress->is_country_active) {
                    return redirect()->route('web.checkout.index')
                        ->with('error', 'Please Select Address');
                }
            }

            // =========================
            // PRODUCT VALIDATION BLOCK
            // =========================
            if (count($cart) > 0) {

                $invalidCount = 0;

                foreach ($cart as $record) {

                    $productQuery = AdminProduct::where('id', $record->product_id);
                    $productQuery = Helper::applyCategoryVisibility($productQuery);
                    $productQuery = Helper::applyProductVisibility($productQuery);
                    $product = $productQuery->first();

                    if (!$product) {
                        $invalidCount++;
                    }
                }

                if (count($cart) == $invalidCount) {

                    ShoppingCart::where('user_id', $user->id)->delete();
                    ShoppingCartDetail::where('user_id', $user->id)->delete();

                    return redirect()->route('web.checkout.cart_review')
                        ->with('error', 'Please add product in to cart');
                }

                // =========================
                // 🔥 FEDEX SHIPPING HERE
                // =========================
                $shippingService = new ShippingService();
                try {
                    $shippingData = $shippingService->getShippingWithCache(
                        $useraddress,
                        $cart,
                        $user->id
                    );
                    session()->forget('checkout_error');
                    $totalShipping = $shippingData['totalShipping'];
                    $productShipping = $shippingData['productShipping'];
                } catch (\App\Exceptions\FedExAddressValidationException | \App\Exceptions\FedExRateException $e) {
                    session()->flash('checkout_error', $e->getMessage());
                    $totalShipping = 0;
                    $productShipping = [];
                }

                // Assign shipping to each cart record
                foreach ($cart as $record) {
                    $record->shipping_cost = $productShipping[$record->product_id] ?? 0;
                    $record->save();
                }
            }

            else if ($customCart) {

                $paymentMethod = PaymentMethod::getPaymentMethod();
                $totalShipping = 0;

                return view('web.checkout.cart_review', compact(
                    'cart',
                    'country',
                    'language',
                    'user',
                    'paymentMethod',
                    'totalShipping'
                ));
            }

            else {
                return redirect()->route('web.checkout.cart_review')
                    ->with('error', 'Please add product in to cart');
            }

            $paymentMethod = PaymentMethod::getPaymentMethod();

            return view('web.checkout.cart_review', compact(
                'cart',
                'country',
                'language',
                'user',
                'paymentMethod',
                'totalShipping'
            ));
        }

        Session::forget('couponCode');

        return redirect()->route('web.index')
            ->with('error', Lang::get($this->langFile . '.cart-error-message'));
    }

    public function initiatePayment(Request $request)
    {
        if ($request->c_phone) {
            session()->put('cphone', $request->c_phone);
        }

        if ($request->add_gift == 1) {
            $giftdata = array('is_gift' => $request->add_gift, 'title' => $request->gift_title, 'message' => $request->gift_message);
        } else {
            $giftdata = null;
        }
        session()->put('gift_wrap', $giftdata);

        if ($request->add_gift_box == 1) {
            $giftboxdata = array('is_gift' => $request->add_gift_box, 'title' => $request->gift_box_title, 'message' => $request->gift_box_message);
        } else {
            $giftboxdata = null;
        }
        session()->put('gift_box', $giftboxdata);

        $catdiscountApplied = 0;
        $apply_discount = 0;
        
        // Define cart variables to check customization status
        $cartCheck = [];
        $customCartCheck = session('customized_order_details');
        $customCartStatusCheck = isset($customCartCheck) && isset($customCartCheck['items']) && count($customCartCheck['items']) > 0 ? true : false;
        
        $userCheck = Auth::user();
        if (session('cart')) {
            $cartCheck = session('cart');
        }
        if ($userCheck) {
            $cartCheck = ShoppingCartDetail::where('user_id', $userCheck->id)->where('is_active', 1)->get();
        }

        $calcSubTotal = 0;
        if (!empty($customCartCheck['items']) && is_array($customCartCheck['items'])) {
            foreach ($customCartCheck['items'] as $cItem) {
                $calcSubTotal += (float) ($cItem['total'] ?? 0);
            }
        }
        $calcShipping = (float) ($customCartCheck['shipping'] ?? ($customCartCheck['payment']['shipping'] ?? 0));
        $calcDiscountType = $customCartCheck['discount_type'] ?? ($customCartCheck['payment']['discount_type'] ?? null);
        $calcDiscountVal = (float) ($customCartCheck['discount_val'] ?? 0);
        $calcDiscount = 0;
        if ($calcDiscountType === 'percentage') {
            $calcDiscount = ($calcSubTotal * $calcDiscountVal) / 100;
        } elseif ($calcDiscountType === 'flat') {
            $calcDiscount = $calcDiscountVal;
        }
        $calcTotalAmount = max(0, $calcSubTotal + $calcShipping - $calcDiscount);
        $calcAdvanceAmount = 0;
        if (!empty($customCartCheck['payment']['advance'])) {
            $calcAdvanceAmount = (float) str_replace(',', '', $customCartCheck['payment']['advance']);
        }
        $calcPendingAmount = max(0, $calcTotalAmount - $calcAdvanceAmount);

        \Log::info('[CUSTOM_ORDER_DEBUG] Buy Now API HIT', [
            'user_id' => $userCheck ? $userCheck->id : null,
            'temporary_order_id' => $customCartCheck['temporary_order_id'] ?? $request->temporary_order_id ?? null,
            'actual_order_id' => $customCartCheck['order_id'] ?? null,
            'payment_method' => $request->paymentMethod ?? null,
            'order_type' => $customCartCheck['type'] ?? (count($cartCheck) > 0 ? 'standard' : 'unknown'),
            'total_amount' => $calcTotalAmount,
            'advance_amount' => $calcAdvanceAmount,
            'pending_amount' => $calcPendingAmount,
        ]);
        
        $isCustomizedOnly = (count($cartCheck) == 0 && $customCartStatusCheck);
        $products = [];
        if (isset($request->paymentMethod) || $isCustomizedOnly) {
             if (!isset($request->paymentMethod)) {
                 $request->merge(['paymentMethod' => config('custom_web.cod', 3)]);
             }
            session()->put('paymentMethod', $request->paymentMethod);

            $cart     = [];
            $customCart = [];
            $customCartStatus = false;
            $user     = Auth::user();
            $language = Helper::getLanguage();
            if (session('cart')) {
                $cart = session('cart');
            }
            //if user login get product from db
            if ($user) {
                $cart = ShoppingCartDetail::where('user_id', $user->id)->where('is_active', 1)->get();
            }
            if (session('customized_order_details')) {
                // get cart from session
                $customCart = session('customized_order_details');
                $customCartStatus = isset($customCart) && isset($customCart['items']) && count($customCart['items']) > 0 ? true : false;
            }

            $shipping_charge = 0;
            if ($user) {
                $useraddress = $this->resolveSelectedShippingAddress($user);
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
            }

            $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();

            if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                $shipping_charge = 0;
            }


            $shippingcharges = 0;

            if (count($cart) > 0 || $customCartStatus) {
                $address      = $this->resolveSelectedShippingAddress($user);
                if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                    $address = $hasPickupStoreAddressSelected;
                }

                $total        = 0;
                $total_weight = 0;
                $c = 0;
                $fedexPackages = [];
                foreach ($cart as $id => $record) {
                    if ($user) {
                        $id = $record->product_id;
                    }


                    $productQuery = AdminProduct::where('id', $record->product_id);
                    $productQuery = Helper::applyCategoryVisibility($productQuery);
                    $productQuery = Helper::applyProductVisibility($productQuery);
                    $product = $productQuery->first();
                    if (!$product) {
                        $c++;
                        continue;
                    }
                    $productdetail = $product->getProductDescription($language->id);

                    /*
                     * Check International Shipping  - Server Side Validation
                     */
                    $allowIntlShipping       = $product->intl_shipping;
                    $checkIntlShippingStatus = Helper::checkIntlShippingStatus($address->country_id, $allowIntlShipping);
                    if (!$checkIntlShippingStatus) {
                        return redirect()->back()->with('error', Lang::get($this->langFile . '.intl_ship_text_validate'));
                    }
                    /*
                     * /.Check International Shipping  - Server Side Validation
                     */

                    $qty = $record['quantity'];
                    if ((int) $qty > (int) $product->product_quantity) {
                        return redirect()->back()->with('error', $productdetail->products_name . Lang::get($this->langFile . '.out_of_stock'));
                    }

                    $product_weight = $product->product_weight;
                    if ($record->size_id) {
                        /*
                         * Check, If given size have quantity available or not
                         */
                        $sizeHasQuantityCheck = AdminProductAttribute::where(['products_id' => $record->product_id])->where('quantity', '>=', (int) $qty);
                        if ($record->size_id) {
                            $sizeHasQuantityCheck->where('size_id', $record->size_id);
                        }
                        if ($record->color_id) {
                            $sizeHasQuantityCheck->where('color_id', $record->color_id);
                        }

                        $sizeHasQuantityCheck = $sizeHasQuantityCheck->first();
                        if (!$sizeHasQuantityCheck) {
                            return redirect()->back()->with('error', $productdetail->products_name . Lang::get($this->langFile . '.out_of_stock'));
                        }
                    }


                    $product_regular_price = $product->origina_price;
                    $product_sale_price = $product->original_discount;
                    $is_product_sale_price_null = $product->is_product_sale_price_null;


                    if ($record->color_id || $record->size_id) {
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                        if ($attributePrice) {

                            $product_regular_price = $attributePrice->origina_price;
                            $product_sale_price = $attributePrice->original_discount;
                            $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                            $product_weight                      = $attributePrice->weight;
                        }
                    }

                    $total_weight += $product_weight * $qty;
                    
                    $fedexPackages[] = [
                        'weight' => $product_weight > 0 ? $product_weight : 0.5,
                        'length' => (int)($product->product_length ?? 10),
                        'width' => (int)($product->product_width ?? 10),
                        'height' => (int)($product->product_height ?? 10),
                        'count' => (int)$qty
                    ];

                    $price = $is_product_sale_price_null == 0  ? $product_sale_price : $product_regular_price;
                    $price2 = $is_product_sale_price_null == 0  ? $product_sale_price : $product_regular_price;
                    if ($record->is_monogram) {
                        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                        $price         = $price + $monogramPrice;
                    }

                    $categoryDiscount = Helper::getCategoryDiscount($record['product_id']);
                    if ($categoryDiscount > 0 && $apply_discount == 0) {

                        $cat_discount = $categoryDiscount;
                    } else {
                        $cat_discount = 0;
                    }


                    $sub_total = $price * $record['quantity'];
                    $sub_total2 = $price2 * $record['quantity'];




                    if ($cat_discount && $apply_discount == 0) {
                        $catdiscountApplied = $catdiscountApplied + ($sub_total2 * $cat_discount) / 100;

                        //                                    $subTotal           = $subTotal - $catdiscountApplied;
                    }



                    $total      += $sub_total;

                    $products[] = [
                        "name" => $productdetail->products_name,
                        "description" => $productdetail->products_name,
                        "quantity" => $record['quantity'],
                        "price" => Helper::formatNumber($price),
                    ];
                }

                // Preserve main order total, then add customized order payable amount to total
                $mainTotal = $total; // main order amount (exclude customized)
                $customOrder = session('customized_order_details');
                $customizedAdvance = 0;
                $isQuotation = false;
                $isPendingPaymentLinkRequested = false;

                if ($customOrder && isset($customOrder['type'])) {
                    if ($customOrder['type'] == 'quotation') {
                        $isQuotation = true;
                        $customizedAdvance = 0;
                    } elseif ($customOrder['type'] == 'purchase_order') {
                        $customSummary = PaymentCalculationService::resolveCustomizedOrderPayableFromSession($customOrder);
                        $customizedAdvance = $customSummary['payable_amount'];
                        $isPendingPaymentLinkRequested = $customSummary['generate_pending_requested'] && ($customizedAdvance > 0.0001);
                    }
                }

                $total = $mainTotal + $customizedAdvance;

                $hasCustomPurchaseOrderNoAdvance = $customOrder
                    && ($customOrder['type'] ?? null) === 'purchase_order'
                    && $customizedAdvance == 0
                    && (empty($cart) || count($cart) == 0);
                    
                
                if (!empty($customOrder['items']) && is_array($customOrder['items'])) {
                    foreach ($customOrder['items'] as $item) {
                        $title =
                            !empty($item['product_title'])
                            ? $item['product_title']
                            : (
                                !empty($item['description'])
                                ? $item['description']
                                : (
                                    !empty($item['item_code'])
                                    ? $item['item_code']
                                    : 'Customized Order'
                                )
                            );

                        $products[] = [
                            'name'        => $title,
                            'description' => $title, // keep same fallback for description
                            'quantity'   => isset($item['quantity']) && $item['quantity'] > 0
                                ? (int) $item['quantity']
                                : 1,
                            // Price must be PER UNIT
                            'price'      => Helper::formatNumber(
                                isset($item['price']) ? $item['price'] : 0
                            ),
                        ];
                    }
                }

                $roundweight = ceil($total_weight);
                $countryName = 'kuwait';
                if (isset($address)) {
                    $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                    $countryName = $countryName ? $countryName->country_name : 'kuwait';
                }
                if ($roundweight < 1 ||  strtolower($countryName) == 'kuwait') {
                    $roundweight = 1;
                }

                $user = Auth::user();
                if ($user) {
                     $isPickup = false;
                     if (isset($address->pickupstore) && $address->pickupstore == 1) {
                         $isPickup = true;
                     }
                     
                     if ($isPickup) {
                         $shippingcharges = 0;
                     } elseif ($address) {
                        if(count($cart) > 0) {
                            // Use ShippingService for calculation (Handling FedEx + Fallback)
                            $shippingService = new \App\Services\ShippingService();
                            $shippingData = $shippingService->getShippingWithCache($address, $cart, $user->id);
                            session()->forget('checkout_error');
                            // Helper::formatNumber returns string, ensure float for addition
                            $shippingcharges = (float) $shippingData['totalShipping']; 
                        }
                     }
                }

                $status = Helper::getOrderDefaultStatus();



                /*
                 * Coupon Code
                 */
                $couponDiscountFinal = 0;

                if (config('custom_web.show_coupon_card')) {
                    $appliedCoupon = [];

                    if ($user) {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                            if ($appliedCoupon['user_id'] != $user->id) {
                                $appliedCoupon = [];
                            } else {
                                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);
                                if (!$couponStatus['status']) {
                                    $appliedCoupon = [];
                                    return redirect()->back()->with('error', $couponStatus['message']);
                                }
                            }
                        }

                        if ($appliedCoupon) {
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                            if ($couponDiscountIn && $couponDiscount) {
                                if ($couponDiscountIn == 'P') {
                                    $couponDiscountFinal = ($mainTotal * $couponDiscount) / 100;
                                } else if ($couponDiscountIn == 'F') {
                                    $couponDiscountFinal = $mainTotal - $couponDiscount;
                                }
                            }
                        }
                    }
                }
                /*
                 * End :: Coupon Code
                 */

                $hasCartItems = !empty($cart) && count($cart) == $c;
                $hasCustomizedOrder = session()->has('customized_order_details');
                
                if ($total == 0 && count($cart) == $c && !$isQuotation && !$hasCustomizedOrder) {
                    $user = Auth::user();
                    if ($user) {
                        ShoppingCart::where('user_id', $user->id)->delete();
                        ShoppingCartDetail::where('user_id', $user->id)->delete();
                    }

                    return Redirect::route('web.checkout.cart_review')->with('error', 'Please add product in to cart');
                }

                $effectivePaymentMethod = $request->paymentMethod ?? null;
                if ($hasCustomPurchaseOrderNoAdvance && !$effectivePaymentMethod) {
                    $effectivePaymentMethod = config('custom_web.cod', 3);
                }
                $payAtStoreMethods = [4, 6];

                if ($effectivePaymentMethod == config('custom_web.cod', 3) && $customizedAdvance == 0 && !$hasCustomPurchaseOrderNoAdvance) {
                    $order = $this->createOrder('', $status);
                    if ($order) {
                        if ($user) {
                            ShoppingCart::where('user_id', $user->id)->delete();
                            ShoppingCartDetail::where('user_id', $user->id)->delete();
                        }
                        return redirect()->route('web.checkout.order_summary', ['id' => $order]);
                    }
                } else if (in_array((int) $effectivePaymentMethod, $payAtStoreMethods, true)) {
                    $orderId = $this->createOrder('', config('custom_web.Pending', 7), false, $effectivePaymentMethod);

                    if ($orderId) {
                        $actualOrder = Order::find($orderId);
                        $latestPayment = PaymentLink::where('order_id', $orderId)->where('status', 'paid')->latest()->first();
                        
                        \Log::info('[CUSTOM_ORDER_DEBUG] Pay At Shop flow SUCCESS', [
                            'temporary_order_id' => $customOrder['temporary_order_id'] ?? null,
                            'actual_order_id' => $orderId,
                            'payment_id' => $latestPayment ? $latestPayment->id : null,
                            'payment_amount' => $latestPayment ? $latestPayment->paid_amount : 0.0,
                            'pending_amount' => $actualOrder ? (float)$actualOrder->pending_amount : 0.0,
                            'status' => $actualOrder ? $actualOrder->order_status : null,
                        ]);

                        if ($user) {
                            ShoppingCart::where('user_id', $user->id)->delete();
                            ShoppingCartDetail::where('user_id', $user->id)->delete();
                        }

                        if (session()->has('customized_order_details')) {
                            session()->forget('customized_order_details');
                        }

                        return redirect()
                            ->route('web.checkout.order_summary', ['id' => $orderId])
                            ->with('success', 'Order placed successfully.');
                    }

                    \Log::info('[CUSTOM_ORDER_DEBUG] Pay At Shop flow FAILED', [
                        'step' => 'createOrder',
                        'error' => 'createOrder returned false/null',
                        'message' => \Lang::get($this->langFile . '.payment-error')
                    ]);

                    return redirect()
                        ->route('web.checkout.cart_review')
                        ->with('error', \Lang::get($this->langFile . '.payment-error'));
                } else if ($hasCustomPurchaseOrderNoAdvance && (empty($cart) || count($cart) == 0)) {
                    // PO only (no regular items) with zero advance: create order without payment gateway, like quotation flow
                    $orderId = $this->createOrder('', config('custom_web.Pending', 7), false, $effectivePaymentMethod);

                    if ($orderId) {
                        if ($user) {
                            ShoppingCart::where('user_id', $user->id)->delete();
                            ShoppingCartDetail::where('user_id', $user->id)->delete();
                        }

                        if (session()->has('customized_order_details')) {
                            session()->forget('customized_order_details');
                        }

                        return redirect()
                            ->route('web.checkout.order_summary', ['id' => $orderId])
                            ->with('success', 'Order placed successfully.');
                    }

                    return redirect()
                        ->route('web.checkout.cart_review')
                        ->with('error', \Lang::get($this->langFile . '.payment-error'));
                } else if (
                    (Auth::user()->id == config('config.store_admin_id')) ||
                    (Auth::user() && isset(Auth::user()->user_type) && Auth::user()->user_type == "physical_store_user")
                ) {
                    // Create order with isSuccess=false (do not send email yet)
                    $orderId = $this->createOrder('', config('custom_web.Pending', 7), false, $effectivePaymentMethod);
                    
                    if ($orderId) {
                        session()->put('customer_order_id', $orderId);
                        $order = Order::find($orderId);
                        
                        // Generate Payment Link
                        $linkTotal = $order->order_total;
                        
                        // Recalculate based on order type composition
                        if ($order->order_type == 'custom') {
                             $calc = PaymentCalculationService::calculateOrderPayments($order);
                             $isAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($order);

                             if ($isPendingPaymentLinkRequested) {
                                 // Paying current pending balance directly from authoritative service
                                 $linkTotal = $calc['pending_amount'];
                             } elseif (!$isAdvanceDone) {
                                 // Paying configured advance amount
                                 $linkTotal = ($customizedAdvance > 0) ? min($customizedAdvance, $calc['total_amount']) : 0;
                             } else {
                                 $linkTotal = 0;
                             }
                        } elseif ($customizedAdvance > 0) {
                             $linkTotal = $order->order_total + $customizedAdvance;
                        }

                        if ($linkTotal <= 0.0001) {
                            if ($user) {
                                ShoppingCart::where('user_id', $user->id)->delete();
                                ShoppingCartDetail::where('user_id', $user->id)->delete();
                            }
                            if (session()->has('customized_order_details')) {
                                session()->forget('customized_order_details');
                            }
                            return redirect()->route('web.checkout.order_summary', ['id' => $orderId])->with('success', 'Order placed successfully.');
                        }

                        $paymentResult = $this->getUpaymentsLink($orderId, $linkTotal, $user, $address, $products, $customizedAdvance, $effectivePaymentMethod, $isPendingPaymentLinkRequested);
                        
                        if ($paymentResult['status']) {
                            $paymentLink = $paymentResult['link'];
                            
                            // Send Email with Link
                        $reviewLink = route('web.checkout.payment_review', ['token' => $paymentResult['payment_request_id']]);
                            $this->sendPaymentLinkEmail($order, $reviewLink, $paymentResult['payment_request_id']);

                            if ($user) {
                                ShoppingCart::where('user_id', $user->id)->delete();
                                ShoppingCartDetail::where('user_id', $user->id)->delete();
                            }

                            // Clear customized order session
                            if (session()->has('customized_order_details')) {
                                session()->forget('customized_order_details');
                            }

                            return redirect()->route('web.checkout.order_summary', ['id' => $orderId])->with('success', 'Order Placed and Payment Link Sent Successfully');
                        } else {
                            return redirect()->route('web.checkout.cart_review')->with('error', $paymentResult['message']);
                        }
                    } else {
                        return redirect()->route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.payment-error'));
                    }
                } else {
                    $tax = Helper::getTax();
                    if (!$tax) {
                        $tax = 0;
                    }
                    // FIX: Tax should only apply to Main Total (Regular Items), NOT on Customized Advance
                    $taxval = ($mainTotal * $tax) / 100;


                    if (config('custom_web.apply_discount')) {
                        $discount        = config('custom_web.discount', 20);
                        // Discount assumes it applies to main total or similar? 
                        // Existing logic used $total. For now keeping $total but likely should be $mainTotal if logic dictates. 
                        // Assuming discount applies to regular items primarily.
                        // Lets stick to $total for discount to minimize breakage unless requested, but Tax was explicitly requested.
                        $discountApplied = ($total * $discount) / 100;
                    } else {
                        $discountApplied = $catdiscountApplied;
                    }

                    $shippingcharges =  Helper::getShippingAmount($total, $shippingcharges);

                    $final_total = $total + $shippingcharges - $discountApplied - $couponDiscountFinal;


                    // gift wrap
                    $setting    = \App\Helpers\Helper::getSetting();
                    $gift_price = 0;
                    $currency   = Helper::getDefaultCurrency();
                    $currencyRate = $currency->value ?? 1;
                    $gift_wrap  = ['is_gift' => 0, 'title' => '', 'message' => ''];
                    $giftWrapEnabledSetting = $setting->firstWhere('id', 32);
                    if ($giftWrapEnabledSetting && $giftWrapEnabledSetting->setting_val == 1) {
                        if (session('gift_wrap')) {
                            $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice() * $currencyRate;
                            $giftAmountSetting = $setting->firstWhere('id', 33);
                            $gift_price = (($giftAmountSetting && $giftAmountSetting->setting_val > 0) ? $giftAmountSetting->setting_val : 0) * $currencyRate;
                            if ($total >= $giftMinPrice) {
                                $gift_price = 0;
                            }
                            $gift_wrap = session('gift_wrap');
                        }
                    }

                    // gift box
                    $gift_box_price = 0;
                    $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                    $giftBoxEnabledSetting = $setting->firstWhere('id', 42);
                    if ($giftBoxEnabledSetting && $giftBoxEnabledSetting->setting_val == 1) {
                        if (session('gift_box')) {
                            $giftBoxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice() * $currencyRate;
                            $giftBoxAmountSetting = $setting->firstWhere('id', 43);
                            $gift_box_price = (($giftBoxAmountSetting && $giftBoxAmountSetting->setting_val > 0) ? $giftBoxAmountSetting->setting_val : 0) * $currencyRate;
                            if ($total >= $giftBoxMinPrice) {
                                $gift_box_price = 0;
                            }
                            $gift_box = session('gift_box');
                        }
                    }

                    $final_total = $final_total + $gift_price + $gift_box_price;

                    /*
                     * Zero Value Order / Quotation Handling
                     */
                    if ($final_total <= 0) {
                        // Create order directly with generic success status (e.g., Pending)
                        $status = $this->createOrder("QUOTATION-" . time(), config('custom_web.Pending', 7), false, $effectivePaymentMethod);
                        if ($status) {
                            session()->put('customer_order_id', $status);
                            $this->paymentSuccess($request, $status); // Trigger success manually
                            return Redirect::route('web.checkout.payment_success');
                        }
                    }

                    /*
                     * Coupon Code
                     */



                    /*
                     * Create Default Order with Pending Status
                     */
                    /*
                     * Create Default Order with Pending Status
                     */
                    $status = $this->createOrder("", config('custom_web.Pending', 7), false, $effectivePaymentMethod);

                    if ($status) {
                        session()->put('customer_order_id', $status);
                        
                        $paymentResult = $this->getUpaymentsLink($status, $final_total, $user, $address, $products, $customizedAdvance, $effectivePaymentMethod, $isPendingPaymentLinkRequested);

                        if ($paymentResult['status']) {
                            return Redirect::to($paymentResult['link']);
                        } else {
                            // If payment link failed, cancel/delete orphaned pending order to maintain database integrity
                            try {
                                $failedOrder = Order::find($status);
                                if ($failedOrder && $failedOrder->order_status == config('custom_web.Pending', 7)) {
                                    OrderProductDetail::where('order_id', $status)->delete();
                                    Order::where('parent_id', $status)->delete();
                                    $failedOrder->delete();
                                }
                                session()->forget('customer_order_id');
                            } catch (\Exception $e) {
                                \Log::error('Error cleaning up failed order: ' . $e->getMessage());
                            }

                            return Redirect::route('web.checkout.cart_review')->with('error', $paymentResult['message']);
                        }
                    } else {
                        return Redirect::route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.payment-error'));
                    }
                }
            } else {
                $this->emptyCart($user->id);
                return redirect()->route('web.index');
            }
        } else {
            return Redirect::route('web.checkout.cart_review')->with('error', \Lang::get($this->langFile . '.select-payment-error'));
        }
    }

    public function emptyCart($userId)
    {
        ShoppingCart::where('user_id', $userId)->delete();
        ShoppingCartDetail::where('user_id', $userId)->delete();
    }



    /**
     * Resolve PaymentLink robustly from gateway callback request parameters.
     * Checks trn_udf, customerExtraData, reference, trackId, payment_id, or latest pending for order.
     */
    private function resolvePaymentLinkFromCallback(Request $request, $orderId = null): ?PaymentLink
    {
        $token = null;

        // 1. Try trn_udf
        if ($request->has('trn_udf') && !empty($request->trn_udf)) {
            $udf = is_string($request->trn_udf) ? json_decode($request->trn_udf, true) : $request->trn_udf;
            if (is_array($udf)) {
                $token = $udf['payment_request_id'] ?? $udf['token'] ?? $udf['payment_token'] ?? $udf['reference_id'] ?? null;
            } elseif (is_string($request->trn_udf)) {
                $token = $request->trn_udf;
            }
        }

        // 2. Try customerExtraData
        if (!$token && $request->has('customerExtraData') && !empty($request->customerExtraData)) {
            $extra = is_string($request->customerExtraData) ? json_decode($request->customerExtraData, true) : $request->customerExtraData;
            if (is_array($extra)) {
                $token = $extra['payment_request_id'] ?? $extra['token'] ?? $extra['payment_token'] ?? null;
            }
        }

        // 3. Try direct payment_token, token, payment_request_id query/post param
        if (!$token) {
            $token = $request->payment_token ?? $request->payment_request_id ?? $request->token ?? null;
        }

        // 4. Try matching PaymentLink by payment_request_id if token found
        if ($token) {
            $link = PaymentLink::where('payment_request_id', $token)->first();
            if ($link) {
                return $link;
            }
        }

        // 5. Try matching by reference/trackId/PaymentID in gateway_response
        $refId = $request->PaymentID ?? $request->payment_id ?? $request->trackId ?? $request->track_id ?? $request->reference ?? null;
        if ($refId) {
            $link = PaymentLink::where('payment_link', 'LIKE', '%' . $refId . '%')
                ->orWhere('gateway_response', 'LIKE', '%' . $refId . '%')
                ->latest()
                ->first();
            if ($link) {
                return $link;
            }
        }

        // 6. Fallback: match by orderId
        if ($orderId) {
            // First look for latest pending payment link
            $link = PaymentLink::where('order_id', $orderId)
                ->where('status', 'pending')
                ->latest()
                ->first();
            if ($link) {
                return $link;
            }

            // Otherwise latest payment link for this order
            return PaymentLink::where('order_id', $orderId)->latest()->first();
        }

        return null;
    }

    public function paymentSuccess(Request $request)
    {
        $reqOrderId = $request->requested_order_id ?? $request->OrderID ?? $request->order_id ?? null;
        $response = $request->all();
        $paymentId = $request->payment_id ?? $request->PaymentID ?? $request->PaymentId ?? $request->trackId ?? $request->track_id ?? '';

        $paymentLink = $this->resolvePaymentLinkFromCallback($request, $reqOrderId);

        if (!$reqOrderId && $paymentLink) {
            $reqOrderId = $paymentLink->order_id;
        }

        $paymentRequestId = $paymentLink ? $paymentLink->payment_request_id : ($request->payment_request_id ?? $request->token ?? '');

        // Check whether gateway response is actually successful
        $resultVal = strtolower(trim((string)($request->Result ?? $request->result ?? $request->status ?? $request->payment_status ?? '')));
        $isGatewaySuccess = in_array($resultVal, ['captured', 'success', 'successful', 'paid', 'approved', 'true', '1', ''], true);

        // Safe Diagnostic Logging (Without sensitive credentials)
        \Log::info('KNET/UPayments Callback Received', [
            'order_id' => $reqOrderId,
            'payment_request_id' => $paymentRequestId,
            'gateway_result' => $resultVal,
            'payment_id' => $paymentId,
            'payment_link_found' => $paymentLink ? true : false,
            'is_gateway_success' => $isGatewaySuccess
        ]);

        if (!$isGatewaySuccess) {
            return $this->paymentError($request);
        }

        if (isset($reqOrderId) && $reqOrderId != null) {
            if ($paymentLink) {
                $alreadyPaid = ($paymentLink->status === 'paid');
                if (!$alreadyPaid) {
                    $existingGatewayResponse = is_array($paymentLink->gateway_response) ? $paymentLink->gateway_response : [];
                    $paymentLink->status = 'paid';
                    $paymentLink->paid_at = now();
                    $paymentLink->paid_amount = (float) $paymentLink->amount;
                    $paymentLink->gateway_response = array_merge($existingGatewayResponse, [
                        'payment_callback_response' => $response,
                    ]);
                    $paymentLink->save();
                }

                $linkOrder = Order::find($paymentLink->order_id);

                if ($linkOrder && $linkOrder->order_type == 'custom') {
                    $successfulPaidTotal = PaymentCalculationService::getSuccessfulPaymentTotal($linkOrder->id);
                    $linkOrder->pending_amount = $this->getCalculatedPendingAmount($linkOrder, $successfulPaidTotal);
                    $linkOrder->paid_amount = $successfulPaidTotal;

                    if ($successfulPaidTotal > 0) {
                        $linkOrder->is_advance_payment_done = 1;
                    }
                    $linkOrder->save();
                    $this->invalidateParentMixedPaymentLinks($linkOrder);

                    // Sync linked TemporaryOrder
                    CustomizedOrderSyncService::syncActualOrderToDraft($linkOrder);
                } elseif ($linkOrder) {
                    $splitPayment = is_array($paymentLink->gateway_response)
                        ? ($paymentLink->gateway_response['split_payment'] ?? null)
                        : null;

                    if (is_array($splitPayment) && !empty($splitPayment)) {
                        $this->applySplitPaymentToOrders($paymentLink, $linkOrder, $splitPayment);
                    } elseif (!$alreadyPaid) {
                        // Regular order payment link should only affect the regular order
                        $linkOrder->pending_amount = max(0, (float) $linkOrder->pending_amount - (float) $paymentLink->amount);
                        $linkOrder->paid_amount = (float) $linkOrder->paid_amount + (float) $paymentLink->amount;
                        $linkOrder->save();
                    }
                }
            }

            // Determine success status (default)
            $status    = Helper::getOrderDefaultStatus();
            
            // Validate response status if coming from Upayments? 
            // Usually we trust the redirect unless we verify server-to-server.
            // Assumption: Redirect implies success or we check Result='Captured'
            if ((isset($response['Result']) && $response['Result'] != 'Captured' && $response['Result'] != 'Success') || (isset($response['result']) && $response['result'] != 'Captured' && $response['result'] != 'Success')) {
                // Payment failed logic should be in paymentError, but if redirected here, treat as error?
                // But manualTest might send success=true.
                // We rely on updatedCreatedOrder.
                $regularOrderQuery = Order::where(function ($query) use ($reqOrderId) {
                    $query->where('id', $reqOrderId)
                        ->orWhere('parent_id', $reqOrderId);
                })
                ->where('order_type', '!=', 'custom')->latest()->first();

                if($regularOrderQuery) {
                    $address = $regularOrderQuery->getUserAddress();
                    $city = $address->city;
                    if($address->city_id) {
                        $cityData = AdminCity::where('id', $address->city_id)->first();
                        if($cityData) {
                            $city = $cityData->city_name;
                        }
                    }

                    $orderDetails = $regularOrderQuery->getOrderDetail(true);

                    $products = [];
                    $commodities = [];

                    foreach($orderDetails as $detail) {
                        $product = $detail->getProduct();
                        $productDetail = $detail->getProductDetail();
                        
                        $products[] = [
                            "name" => $product->products_name,
                            "description" => $product->products_description,
                            "quantity" => $detail->quantity,
                            "price" => Helper::formatNumber($detail->total_price),
                            'weight' => [
                                'units' => strtoupper($productDetail->weight_unit) ?? 'KG',
                                'value' => max(0.1, (float)($productDetail->product_weight ?? 1))
                            ],
                            'dimensions' => [
                                'length' => $productDetail->product_length ?? 10,
                                'width'  => $productDetail->product_width ?? 10,
                                'height' => $productDetail->product_height ?? 10,
                                'units'  => strtoupper($productDetail->dimension_unit) ?? 'CM'
                            ]
                        ];

                        $hsCode = $productDetail->getHsCode($detail->size_id, $detail->color_id);

                        $commodities[] = [
                            'description' => strip_tags($product->products_name),
                            'quantity' => $detail->quantity,
                            'price' => Helper::formatNumber($detail->total_price),
                            'weight' => $productDetail->product_weight ?? 1, // Replace with actual product weight
                            'weight_unit' => $productDetail->weight_unit ?? 'KG', // Replace with actual product weight
                            'currency' => $address->getcountry()->currency_code ?? 'KWD', // or dynamic currency
                            'countryOfManufacture' => $productDetail->country_of_origin, // Example: Portugal
                            'harmonizedCode' => $hsCode
                        ];
                    }

                    $fedexService = new FedExService();

                    try{
                    $response = $fedexService->createShipment([
                        'requestedShipment' => [
                            'recipients' => [
                                'contact' => [
                                    'personName' => $address->first_name . ' ' . $address->last_name,
                                    'phoneNumber' => $address->phone,
                                ],
                                'address' => [
                                    'streetLines' => $address ? array_filter([
                                        // $address->address_title ?? '',
                                        $address->block ?? '',
                                        $address->street ?? '',
                                        $address->house_number ?? '',
                                        $address->address_detail ?? ''
                                    ]) : [],
                                    'city' => $city,
                                    'stateOrProvinceCode' => $address->state_or_province_code,
                                    'postalCode' => $address->pin_code ?? '00000',
                                    'countryCode' => $address->getcountry()->country_code ?? 'KW',
                                ]
                            ],
                            'requestedPackageLineItems' => $products,
                            'commodities' => $commodities
                        ]
                    ]);

                    
                    if (
                        $response &&
                        isset($response['output']['transactionShipments'][0]['pieceResponses'][0]['trackingNumber'])
                    ) {

                        $shipment = $response['output']['transactionShipments'][0];
                        $piece    = $shipment['pieceResponses'][0];

                        $trackingNumber = $piece['trackingNumber'];
                        $serviceType    = $shipment['serviceType'] ?? 'INTERNATIONAL_PRIORITY';
                        $labelUrl       = $piece['packageDocuments'][0]['url'] ?? null;

                        Order::where('id', $reqOrderId)->update([
                            'carrier' => 'FedEx',
                            'courier_id' => CourierCompany::where('company_name', 'FedEx')->first()->id ?? null,
                            'tracking_number' => $trackingNumber,
                            'shipping_service' => $serviceType,
                            'shipment_status' => 'CREATED',
                            'fedex_label_url' => $labelUrl,
                            'shipping_response' => json_encode($response),
                            'shipped_at' => now(),
                        ]);
                    }
                    }
                    catch(\Exception $e) {
                        // Log error but do not fail the order
                        \Log::error('FedEx Shipment Creation Failed: ' . $e->getMessage());
                    }

                }
            }

            $status    = $this->updatedCreatedOrder($paymentId, $status, true, $reqOrderId, $response);
            if ($status) {
                $user = Auth::user();
                if ($user) {
                    $this->emptyCart($user->id);
                }
                
                $redirectPaymentId = $paymentRequestId ?: $paymentId;

                // Check if user is logged in as physical store user or admin, or not logged in
                if (!Auth::check() || (Auth::check() && ($user->id == config('config.store_admin_id') || (isset($user->user_type) && $user->user_type == "physical_store_user")))) {
                    return redirect()->route('web.checkout.payment_summary', ['id' => $status, 'payment_id' => $redirectPaymentId]);
                }

                $orderForRedirect = Order::find($reqOrderId);
                if ($orderForRedirect && $orderForRedirect->order_type == 'custom') {
                    return redirect()->route('web.checkout.payment_summary', ['id' => $status, 'payment_id' => $redirectPaymentId]);
                }
                
                return redirect()->route('web.checkout.order_summary', ['id' => $status]);
            } else {
                 $redirectPaymentId = $paymentRequestId ?: $paymentId;
                 if (!Auth::check()) {
                     return redirect()->route('web.checkout.payment_summary', ['id' => $status ?: $reqOrderId, 'payment_id' => $redirectPaymentId]);
                 }
                return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
            }
        } else {
             $redirectPaymentId = $paymentRequestId ?: $paymentId;
             if (!Auth::check()) {
                 return redirect()->route('web.checkout.payment_summary', ['id' => $reqOrderId, 'payment_id' => $redirectPaymentId]);
             }
            return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
        }
    }

    public function paymentError(Request $request)
    {
        $reqOrderId = $request->requested_order_id ?? $request->OrderID ?? $request->order_id ?? null;
        $response = $request->all();
        $paymentId = $request->payment_id ?? $request->PaymentID ?? $request->PaymentId ?? $request->trackId ?? $request->track_id ?? '';

        $paymentLinkObj = $this->resolvePaymentLinkFromCallback($request, $reqOrderId);
        if (!$reqOrderId && $paymentLinkObj) {
            $reqOrderId = $paymentLinkObj->order_id;
        }

        $paymentRequestId = $paymentLinkObj ? $paymentLinkObj->payment_request_id : ($request->payment_request_id ?? $request->token ?? '');

        \Log::warning('KNET/UPayments Payment Error/Failure Callback', [
            'order_id' => $reqOrderId,
            'payment_request_id' => $paymentRequestId,
            'gateway_response' => $response,
        ]);

        if (isset($reqOrderId) && $reqOrderId != null) {
            $orderForFailureFlow = Order::withTrashed()->find($reqOrderId);
            $isCustomizedOrder = $orderForFailureFlow && $orderForFailureFlow->custom_order_type;
            
            if ($paymentLinkObj && $paymentLinkObj->status !== 'paid') {
                $paymentLinkObj->update([
                    'status' => 'failed',
                    'paid_at' => now(),
                    'gateway_response' => json_encode($response),
                ]);
            }

            $user      = Auth::user();
            $status    = config('custom_web.fail', 7);
            $status    = $this->updatedCreatedOrder($paymentId, $status, false, $reqOrderId, $response);

            // Send Payment Reject Email
            $orderObj = Order::find($reqOrderId);
            
            $user = Auth::user();
            if (!Auth::check() || (Auth::check() && ($user->id == config('config.store_admin_id') || (isset($user->user_type) && $user->user_type == "physical_store_user")))) {
                return redirect()->route('web.checkout.payment_summary', ['id' => $reqOrderId, 'payment_id' => $paymentRequestId]);
            }

            if ($isCustomizedOrder) {
                return redirect()->route('web.checkout.payment_summary', ['id' => $reqOrderId, 'payment_id' => $paymentRequestId]);
            }
        }
        return Redirect::route('web.checkout.cart_review')->with('error', Lang::get($this->langFile . '.payment-error'));
    }

    public function paymentSummary($id, $payment_id = null)
    {
        $orderProductDetail = [];
        $orderCustom = null;
        if ($id) {
            $order = Order::where('id', $id)->withTrashed()->first();
            $payment_link = null;
            if ($payment_id) {
                $payment_link = PaymentLink::where('payment_request_id', $payment_id)
                    ->orWhere('id', $payment_id)
                    ->withTrashed()
                    ->first();
            }
            if (!$payment_link && $order) {
                // Fallback to latest payment link for this order
                $payment_link = PaymentLink::where('order_id', $order->id)->latest()->withTrashed()->first();
            }

            if ($order) {
                $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
                if ($order->custom_order_type) {
                    if($order->custom_order_type == 'purchase_order') {
                        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
                        return view('web.user.payment_detail_customized', compact('order', 'address', 'orderProductDetail', 'payment_link'));
                    }
                }
                else {
                    $orderCustom = Order::where('parent_id', $id)->withTrashed()->first();
                    if($orderCustom) {
                        $orderProductDetail = OrderDetail::where('order_id', $orderCustom->id)->get();
                    }
                }
                return view('web.checkout.payment_summary', compact('order', 'address', 'orderProductDetail', 'orderCustom', 'payment_link'));
            } else {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
            }
        } else {

            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function createOrder($payment_id, $order_status, $isSuccess = true, $paymentMethod = false)
    {
        $couponId = 0;
        if ($paymentMethod) {
            $hesabePaymentMethod = config('custom_web.hesabe_payment_method');
        }

        $currency = Helper::getDefaultCurrency();

        $cat_discount = 0;
        $catdiscountApplied = 0;
        $discountApplied = 0;
        $apply_discount = 0;

        $status  = false;
        $user    = Auth::user();
        $cart    = ShoppingCartDetail::where('user_id', $user->id)->where('is_active', 1)->get();
        $isStrictCustomAddress = $this->shouldUseStrictAddressSelectionForCustomOrder($user);
        $address = $this->resolveSelectedShippingAddress($user);
        if (!$isStrictCustomAddress && !$address) {
            $address = Helper::getPickupStoreAddress();
        }
                
        // if ($cart && $address) {
            DB::beginTransaction();

            $currency        = Helper::getDefaultCurrency(); //To manage user selected currency and current conversion rate
 
            // Shipping logic moved to Post-Loop using ShippingService

            $order_price     = 0;
            $shippingcharges = 0;
            $shipping_charge = 0;
            $total_weight    = 0;
            $cat_discount_per = 0;

            foreach ($cart as $record) {
                $productQuery = AdminProduct::where('id', $record->product_id);
                $productQuery = Helper::applyCategoryVisibility($productQuery);
                $productQuery = Helper::applyProductVisibility($productQuery);
                $product = $productQuery->first();
                if (!$product) {
                    continue;
                }
                $product_weight = $product->product_weight;

                $product_regular_price = $product->origina_price;
                $product_sale_price = $product->original_discount;
                $is_product_sale_price_null = $product->is_product_sale_price_null;
                if ($record->color_id || $record->size_id) {
                    $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                    if ($attributePrice) {
                        $product_regular_price = $attributePrice->origina_price;
                        $product_sale_price = $attributePrice->original_discount;
                        $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                        $product_weight                      = $attributePrice->weight;
                    }
                }

                $total_weight += $product_weight * $record->quantity;

                $product_price = $product_sale_price ? $product_sale_price : $product_regular_price;

                $sum = $record->quantity * $product_price;

                $sum2 = $record->quantity * $product_price;
                if ($record->is_monogram) {
                    $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                    if ($record->is_same == 0) {

                        $monogrcount = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->count();
                        if ($monogrcount > 0) {
                            $monogramPrice = $monogramPrice * $monogrcount;
                            $sum           = $sum + $monogramPrice;
                        }
                    } else {
                        $monogramPrice = $record->quantity * $monogramPrice;

                        $sum = $sum + $monogramPrice;
                    }
                }

                $categoryDiscount = Helper::getCategoryDiscount($record['product_id']);
                if ($categoryDiscount > 0 && $apply_discount == 0) {

                    $cat_discount = $categoryDiscount;
                } else {
                    $cat_discount = 0;
                }

                if ($cat_discount && $apply_discount == 0) {

                    $cat_discount_per = $cat_discount_per + $cat_discount;
                    $productDiscount = ($sum2  * $cat_discount) / 100;

                    $catdiscountApplied = $catdiscountApplied + $productDiscount;

                    //
                }

                $order_price += $sum;
            }

            $order_price = (float) $order_price * ($currency->value ?? 1);

            // Customized Order Advance was previously added here. 
            // Now we handle customized order separately.
            // Keep $order_price for regular items only.

            if($cart && count($cart) > 0) {
                // Use ShippingService for correct FedEx/Fallback calculation
                $shippingService = new \App\Services\ShippingService();
                $shippingData = $shippingService->getShippingWithCache($address, $cart, $user->id);
                session()->forget('checkout_error');
                $shippingcharges = (float) $shippingData['totalShipping'];
                $shipping_charge = 0; // Base rate not relevant for FedEx/Dynamic
            }

            $tax = Helper::getTax();
            if (!$tax) {
                $tax = 0;
            }
            $taxval = ($order_price * $tax) / 100;


            if ($isStrictCustomAddress && $cart->count() == 0 && session('customized_order_details')) {
                $address = $this->resolveSelectedShippingAddressStrict($user);
            } else {
                $address = $this->resolveSelectedShippingAddress($user);
                if (!$address) {
                    $address = Helper::getPickupStoreAddress();
                }
            }

            $isExistingCustomOrderUpdate = false;
            $previousOrderTotal = 0.0;
            $previousPendingAmount = 0.0;
            $linkedTempOrder = null;

            \Log::info('[CUSTOM_ORDER_DEBUG] Transaction START');

            // Look up TemporaryOrder and any existing linked Actual Order if customized order is present
            if (session('customized_order_details')) {
                $customOrderSession = session('customized_order_details');
                $tempDraftId = $customOrderSession['temporary_order_id'] ?? $request->temporary_order_id ?? null;

                \Log::info('[CUSTOM_ORDER_DEBUG] Looking up temporary order', [
                    'temporary_order_id' => $tempDraftId,
                    'user_id' => $user ? $user->id : null,
                ]);

                if ($tempDraftId && $user) {
                    $linkedTempOrder = TemporaryOrder::where('id', $tempDraftId)
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();

                    if ($linkedTempOrder) {
                        \Log::info('[CUSTOM_ORDER_DEBUG] Temporary order found', [
                            'id' => $linkedTempOrder->id,
                            'type' => $linkedTempOrder->order_type,
                            'status' => $linkedTempOrder->status,
                            'total' => (float)$linkedTempOrder->grand_total,
                            'advance_payment' => (float)$linkedTempOrder->advance_price,
                            'pending_amount' => (float)$linkedTempOrder->pending_amount,
                            'actual_order_id' => $linkedTempOrder->order_id,
                        ]);

                        \Log::info('[CUSTOM_ORDER_DEBUG] Checking actual order mapping', [
                            'temporary_order_id' => $linkedTempOrder->id,
                        ]);

                        $existingLinkedOrder = $linkedTempOrder->getLinkedOrder();
                        if ($existingLinkedOrder) {
                            $existingLinkedOrder = Order::where('id', $existingLinkedOrder->id)
                                ->where('user_id', $user->id)
                                ->lockForUpdate()
                                ->first();

                            if ($existingLinkedOrder) {
                                \Log::info('[CUSTOM_ORDER_DEBUG] Actual order mapping:', [
                                    'temporary_order_id' => $linkedTempOrder->id,
                                    'actual_order_id' => $existingLinkedOrder->id,
                                    'found' => true,
                                ]);

                                if (!CustomizedOrderSyncService::canCustomerEdit($existingLinkedOrder)) {
                                    DB::rollBack();
                                    \Log::info('[CUSTOM_ORDER_DEBUG] Transaction ROLLBACK', [
                                        'error' => 'This order can no longer be modified because it has already entered processing.'
                                    ]);
                                    return redirect()->back()->with('error', 'This order can no longer be modified because it has already entered processing.');
                                }
                                if ($cart->count() == 0) {
                                    // For standalone custom order, the main order is the linked actual order
                                    $order = $existingLinkedOrder;
                                    $isExistingCustomOrderUpdate = true;
                                    $previousOrderTotal = round((float)($order->order_total ?? 0), 3);
                                    $previousPendingAmount = round((float)($order->pending_amount ?? 0), 3);
                                }
                            } else {
                                \Log::info('[CUSTOM_ORDER_DEBUG] Actual order mapping:', [
                                    'temporary_order_id' => $linkedTempOrder->id,
                                    'actual_order_id' => null,
                                    'found' => false,
                                ]);
                            }
                        } else {
                            \Log::info('[CUSTOM_ORDER_DEBUG] Actual order mapping:', [
                                'temporary_order_id' => $linkedTempOrder->id,
                                'actual_order_id' => null,
                                'found' => false,
                            ]);
                        }
                    } else {
                        \Log::info('[CUSTOM_ORDER_DEBUG] Temporary order NOT FOUND', [
                            'temporary_order_id' => $tempDraftId,
                        ]);
                    }
                }
            }

            if (!isset($order)) {
                $order                      = new Order();
                $order->user_id             = $user->id;
                $order->physical_store_id   = $user->physical_store_id;
                $order->created_at          = date("Y-m-d H:i:s");
            }

            $order->shipping_address_id = $address->id ?? null;
            $order->cphone              = session('cphone');
            $order->billing_address_id  = $address->id ?? null;
            $order->transaction_id      = $payment_id;
            $resolvedPaymentMethod = $paymentMethod ?? session()->get('paymentMethod');
            if ($paymentMethod && isset($hesabePaymentMethod[$paymentMethod])) {
                $resolvedPaymentMethod = $hesabePaymentMethod[$paymentMethod];
            }
            $order->payment_method      = $resolvedPaymentMethod;
            $order->order_status        = $order_status;

            if ($currency) {
                $order->currency_code = $currency->code;
                $order->currency_rate = $currency->value;
            }

            $shouldCreatePayAtShopEntry = false;
            $payAtStoreMethods = [4, 6];
            $resolvedPM = $resolvedPaymentMethod ?? $paymentMethod ?? session()->get('paymentMethod');

            // Handle Standalone Custom Order vs Standard Order
            if ($cart->count() == 0 && session('customized_order_details')) {
                $customOrderSession = session('customized_order_details');
                $order->order_type = 'custom';
                $order->order_status = 7;
                $order->custom_order_type = $customOrderSession['type'];

                $customTotal = 0;
                if (isset($customOrderSession['items']) && is_array($customOrderSession['items'])) {
                    foreach ($customOrderSession['items'] as $cItem) {
                        $customTotal += (float) $cItem['total'];
                    }
                }
                $customAdvance = 0;
                if ($customOrderSession['type'] == 'purchase_order' && !empty($customOrderSession['payment']['advance'])) {
                    $customAdvance = (float) str_replace(',', '', $customOrderSession['payment']['advance']);
                }

                // Retrieve financial fields from session
                $customShipping = isset($customOrderSession['shipping']) ? (float)$customOrderSession['shipping'] : (isset($customOrderSession['payment']['shipping']) ? (float)$customOrderSession['payment']['shipping'] : 0);
                $customDiscountType = isset($customOrderSession['discount_type']) ? $customOrderSession['discount_type'] : (isset($customOrderSession['payment']['discount_type']) ? $customOrderSession['payment']['discount_type'] : null);
                $customDiscountVal = isset($customOrderSession['discount_val']) ? (float)$customOrderSession['discount_val'] : 0;

                // Calculate Discount Amount
                $customDiscountAmount = 0;
                if ($customDiscountType == 'percentage') {
                    $customDiscountAmount = ($customTotal * $customDiscountVal) / 100;
                } elseif ($customDiscountType == 'flat') {
                    $customDiscountAmount = $customDiscountVal;
                }

                $orderGrandTotal = max(0, round($customTotal + $customShipping - $customDiscountAmount, 3));

                $order->order_price    = $customTotal;
                $order->delivery_price = $customShipping;
                $order->delivery_rate  = 0;
                    $order->tax            = 0;
                $order->order_total    = $orderGrandTotal;
                $order->discount_per   = ($customDiscountType == 'percentage') ? $customDiscountVal : 0;
                $order->discount       = $customDiscountAmount;
                $order->coupon_discount_amount = 0;
                $order->discount_type = $customDiscountType;
                $isPayAtShopSelected = in_array((int)$resolvedPM, $payAtStoreMethods, true);
                $existingPaidTotal = $isExistingCustomOrderUpdate ? PaymentCalculationService::getSuccessfulPaymentTotal($order->id) : 0.0;
                $pendingBeforeThisPayment = max(0.0, round($orderGrandTotal - $existingPaidTotal, 3));

                $isGeneratePendingRequested = false;
                if (isset($customOrderSession['payment']['generate_pending_link'])) {
                    $isGeneratePendingRequested = (bool)$customOrderSession['payment']['generate_pending_link'];
                } elseif (isset($customOrderSession['generate_pending_payment_link'])) {
                    $isGeneratePendingRequested = (bool)$customOrderSession['generate_pending_payment_link'];
                }

                $payAtShopPaymentAmount = 0.0;
                if ($isPayAtShopSelected) {
                    if ($isExistingCustomOrderUpdate && $existingPaidTotal > 0) {
                        // EDIT MODE (with existing paid advance):
                        // Pay At Shop only generates a pending amount payment if the user requested it (checkbox checked / Buy Now on pending amount)
                        if ($isGeneratePendingRequested) {
                            $payAtShopPaymentAmount = $pendingBeforeThisPayment;
                            $shouldCreatePayAtShopEntry = ($payAtShopPaymentAmount > 0.0001);
                        } else {
                            $payAtShopPaymentAmount = 0.0;
                            $shouldCreatePayAtShopEntry = false;
                        }
                        \Log::info('[CUSTOM_ORDER_EDIT_PAY_AT_SHOP]', [
                            'mode' => 'EDIT',
                            'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : null,
                            'actual_order_id' => $order->id,
                            'total_amount' => $orderGrandTotal,
                            'existing_paid_amount' => $existingPaidTotal,
                            'existing_pending_amount' => $pendingBeforeThisPayment,
                            'is_generate_pending_requested' => $isGeneratePendingRequested,
                            'calculated_payment_amount' => $payAtShopPaymentAmount,
                            'should_create' => $shouldCreatePayAtShopEntry,
                        ]);
                    } else {
                        // CREATE MODE / First payment: Pay advance amount (or total if full/no advance)
                        if ($customOrderSession['type'] == 'purchase_order' && $customAdvance > 0) {
                            $payAtShopPaymentAmount = min($customAdvance, $orderGrandTotal);
                        } else {
                            $payAtShopPaymentAmount = $pendingBeforeThisPayment;
                        }
                        if ($payAtShopPaymentAmount > 0.0001) {
                            $shouldCreatePayAtShopEntry = true;
                        }
                        \Log::info('[PAY_AT_SHOP_DEBUG] Calculated payment amount:', [
                            'mode' => $isExistingCustomOrderUpdate ? 'EDIT_FIRST_PAY' : 'CREATE',
                            'total_amount' => $orderGrandTotal,
                            'already_paid' => $existingPaidTotal,
                            'advance_amount' => $customAdvance,
                            'pending_amount' => $pendingBeforeThisPayment,
                            'payment_amount' => $payAtShopPaymentAmount,
                            'should_create' => $shouldCreatePayAtShopEntry,
                        ]);
                    }
                }

                if ($isExistingCustomOrderUpdate) {
                    $beforePaid = $order->paid_amount;
                    $beforePending = $order->pending_amount;
                    $effectiveAdvance = ($existingPaidTotal > 0) ? $existingPaidTotal : ($isPayAtShopSelected ? min($customAdvance, $orderGrandTotal) : $customAdvance);
                    $order->paid_amount = $effectiveAdvance;
                    $order->pending_amount = max(0.0, round($orderGrandTotal - $effectiveAdvance, 3));
                    $order->is_advance_payment_done = ($effectiveAdvance > 0) ? 1 : 0;

                    PaymentCalculationService::logPaymentDebug([
                        'order_id' => $order->id,
                        'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : 'N/A',
                        'user_id' => $order->user_id,
                        'field' => 'orders.paid_amount',
                        'before' => $beforePaid,
                        'request' => $customAdvance,
                        'calculated' => $effectiveAdvance,
                        'written' => $order->paid_amount,
                        'source' => 'CheckoutController::createOrder (Standalone Custom Order Update)',
                    ]);

                    PaymentCalculationService::logPaymentDebug([
                        'order_id' => $order->id,
                        'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : 'N/A',
                        'user_id' => $order->user_id,
                        'field' => 'orders.pending_amount',
                        'before' => $beforePending,
                        'request' => $customAdvance,
                        'calculated' => $order->pending_amount,
                        'written' => $order->pending_amount,
                        'source' => 'CheckoutController::createOrder (Standalone Custom Order Update)',
                    ]);
                } else {
                    $order->paid_amount    = $customAdvance;
                    $order->pending_amount = max(0.0, round($orderGrandTotal - $customAdvance, 3));

                    // For Pay at Shop, advance is considered paid immediately
                    if ($isPayAtShopSelected && $payAtShopPaymentAmount > 0) {
                        $order->is_advance_payment_done = 1;
                    }

                    PaymentCalculationService::logPaymentDebug([
                        'order_id' => $order->id ?? 'NEW',
                        'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : 'N/A',
                        'user_id' => $order->user_id,
                        'field' => 'orders.paid_amount',
                        'before' => 0,
                        'request' => $customAdvance,
                        'calculated' => $customAdvance,
                        'written' => $order->paid_amount,
                        'source' => 'CheckoutController::createOrder (New Standalone Custom Order)',
                    ]);
                }

                if ($linkedTempOrder) {
                    $customOrderSession['temporary_order_id'] = $linkedTempOrder->id;
                }
            }

            if ($order->order_type == 'custom') {
                $order->is_gift = false;
                $order->is_gift_box = false;
                $order->gift_wrap = 0;
                $order->gift_box = 0;
            } else {
                $discountApplied = 0;

                if (config('custom_web.apply_discount')) {
                    $discount            = config('custom_web.discount', 20);
                    $discountApplied     = ($order_price * $discount) / 100;
                    $order->discount_per = $discount;
                    $order->discount     = $discountApplied;
                } else {
                    $discountApplied = $catdiscountApplied;
                    $order->discount     = $catdiscountApplied;
                }

                /*
                 * Coupon Code
                 */
                $couponDiscountFinal = 0;

                if (config('custom_web.show_coupon_card')) {
                    $appliedCoupon = [];

                    if ($user) {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                            if ($appliedCoupon['user_id'] != $user->id) {
                                $appliedCoupon = [];
                            }
                        }

                        if ($appliedCoupon) {
                            $couponId         = isset($appliedCoupon['id']) ? $appliedCoupon['id'] : '';
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';
                            $couponCode       = isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';

                            if ($couponDiscountIn && $couponDiscount) {
                                if ($couponDiscountIn == 'P') {
                                    $couponDiscountFinal = ($order_price * $couponDiscount) / 100;
                                } else if ($couponDiscountIn == 'F') {
                                    $couponDiscountFinal = $order_price - $couponDiscount;
                                }
                            }

                            $order->coupon_id              = $couponId;
                            $order->coupon_code            = $couponCode;
                            $order->coupon_discount_in     = $couponDiscountIn;
                            $order->coupon_discount        = $couponDiscount;
                            $order->coupon_discount_amount = $couponDiscountFinal;
                        }
                    }
                }
                /*
                 * End :: Coupon Code
                 */

                $finalTotal = $order_price + $shippingcharges + $taxval - $discountApplied - $couponDiscountFinal;

                $setting = \App\Helpers\Helper::getSetting();
                $gift_price = 0;
                $currencyRate = $currency->value ?? 1;

                $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                $giftWrapEnabledSetting = $setting->firstWhere('id', 32);
                if ($giftWrapEnabledSetting && $giftWrapEnabledSetting->setting_val == 1) {
                    if (session('gift_wrap')) {
                        $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice() * $currencyRate;
                        $giftAmountSetting = $setting->firstWhere('id', 33);
                        $gift_price   = (($giftAmountSetting && $giftAmountSetting->setting_val > 0) ? $giftAmountSetting->setting_val : 0) * $currencyRate;

                        if ($order_price >= $giftMinPrice) {
                            $gift_price = 0;
                        }
                        $gift_wrap = session('gift_wrap');
                    }
                }

                $gift_box_price = 0;
                $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                $giftBoxEnabledSetting = $setting->firstWhere('id', 42);
                if ($giftBoxEnabledSetting && $giftBoxEnabledSetting->setting_val == 1) {
                    if (session('gift_box')) {
                        $giftBoxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice() * $currencyRate;
                        $giftBoxAmountSetting = $setting->firstWhere('id', 43);
                        $gift_box_price = (($giftBoxAmountSetting && $giftBoxAmountSetting->setting_val > 0) ? $giftBoxAmountSetting->setting_val : 0) * $currencyRate;

                        if ($order_price >= $giftBoxMinPrice) {
                            $gift_box_price = 0;
                        }
                        $gift_box = session('gift_box');
                    }
                }

                $order->is_gift = false;
                if (session('gift_wrap')) {
                    $order->is_gift = true;
                }

                $order->is_gift_box = false;
                if (session('gift_box')) {
                    $order->is_gift_box = true;
                }

                $order->order_total  = $finalTotal + $gift_price + $gift_box_price;
                $order->order_price  = $order_price;
                $order->gift_wrap    = $gift_price;
                $order->gift_title   = $gift_wrap['title'];
                $order->gift_message = $gift_wrap['message'];

                $order->gift_box         = $gift_box_price;
                $order->gift_box_title   = $gift_box['title'];
                $order->gift_box_message = $gift_box['message'];
            }

            if ($order->save()) {
                if (!$isExistingCustomOrderUpdate) {
                    \Log::info('[CUSTOM_ORDER_DEBUG] Actual order CREATED', [
                        'actual_order_id' => $order->id,
                    ]);
                }
                
                // Create PaymentLink entry for Pay at Shop payment history (after order is saved)
                if ($shouldCreatePayAtShopEntry && isset($payAtShopPaymentAmount) && $payAtShopPaymentAmount > 0.0001) {
                    // Idempotency check: prevent duplicate Pay At Shop payment records if clicked twice quickly
                    $isDuplicatePayment = PaymentLink::where('order_id', $order->id)
                        ->where('payment_type', $resolvedPM)
                        ->where('amount', $payAtShopPaymentAmount)
                        ->where('status', 'paid')
                        ->where('created_at', '>=', now()->subSeconds(30))
                        ->exists();

                    if (!$isDuplicatePayment) {
                        $paymentMethodLabel = Helper::getPaymentMethodByID($resolvedPM);
                        \Log::info('[PAY_AT_SHOP_DEBUG] Creating payment record', [
                            'order_id' => $order->id,
                            'payment_type' => $resolvedPM,
                            'payment_method' => $paymentMethodLabel,
                            'payment_amount' => $payAtShopPaymentAmount,
                            'status' => 'paid',
                        ]);

                        try {
                            $createdPayment = PaymentLink::create([
                                'order_id' => $order->id,
                                'payment_link' => null,
                                'payment_request_id' => 'pay_at_shop_' . $order->id . '_' . time() . '_' . rand(100, 999),
                                'amount' => $payAtShopPaymentAmount,
                                'generated_from' => 'frontend',
                                'generated_by' => $user ? $user->id : null,
                                'status' => 'paid',
                                'paid_at' => now(),
                                'paid_amount' => $payAtShopPaymentAmount,
                                'payment_type' => $resolvedPM,
                                'is_advance_payment' => ($existingPaidTotal == 0 ? 1 : 0),
                                'customer_email' => $customOrderSession['customer']['email'] ?? null,
                                'email_sent_count' => 0,
                                'last_email_sent_at' => null,
                                'gateway_response' => [
                                    'source' => 'frontend_pay_at_store',
                                    'payment_method_label' => $paymentMethodLabel,
                                    'created_at' => now()->toDateTimeString()
                                ]
                            ]);

                            if ($isExistingCustomOrderUpdate) {
                                \Log::info('[CUSTOM_ORDER_EDIT_PAY_AT_SHOP]', [
                                    'step' => 'PAYMENT_RECORD_CREATED',
                                    'payment_record_id' => $createdPayment->id,
                                    'payment_record_status' => $createdPayment->status,
                                    'amount' => $createdPayment->paid_amount,
                                    'order_id' => $order->id,
                                ]);
                            } else {
                                \Log::info('[PAY_AT_SHOP_DEBUG] Payment record CREATED', [
                                    'payment_id' => $createdPayment->id,
                                    'order_id' => $order->id,
                                    'payment_amount' => $createdPayment->paid_amount,
                                    'payment_method' => $paymentMethodLabel,
                                    'status' => $createdPayment->status,
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::error('[PAY_AT_SHOP_DEBUG] Payment record CREATE FAILED', [
                                'error' => $e->getMessage(),
                                'message' => 'Exception when creating PaymentLink for Pay At Shop'
                            ]);
                            throw $e;
                        }
                    }

                    // Authoritative DB calculation from successful payments
                    $authoritativePaidTotal = PaymentCalculationService::getSuccessfulPaymentTotal($order->id);
                    $order->paid_amount = $authoritativePaidTotal;
                    $order->pending_amount = max(0.0, round($orderGrandTotal - $authoritativePaidTotal, 3));
                    $order->is_advance_payment_done = ($authoritativePaidTotal > 0) ? 1 : 0;
                    // Keep order_status in pending / initial state (7) until processing
                    $order->save();

                    if ($isExistingCustomOrderUpdate) {
                        \Log::info('[CUSTOM_ORDER_EDIT_PAY_AT_SHOP]', [
                            'step' => 'POST_PAYMENT_LEDGER_RECALCULATED',
                            'order_id' => $order->id,
                            'final_paid_amount' => $order->paid_amount,
                            'final_pending_amount' => $order->pending_amount,
                            'is_advance_payment_done' => $order->is_advance_payment_done,
                            'order_status' => $order->order_status,
                        ]);
                    }
                }

                $customOrderSession['order_id'] = $order->id ?? null;
                $order->custom_order_details = json_encode($customOrderSession);

                // Persist structured billing fields
                if (isset($customOrderSession['customer']) && is_array($customOrderSession['customer'])) {
                    $cust = $customOrderSession['customer'];
                    if (isset($cust['phone'])) $order->cphone = $cust['phone'];
                    if (isset($cust['email'])) $order->email = $cust['email'];
                    
                    $customBillingAddressId = $cust['customer_address_id'] ?? $cust['billing_address_id'] ?? ($linkedTempOrder->customer_address_id ?? null);
                    if (!empty($customBillingAddressId)) {
                        $order->billing_address_id = $customBillingAddressId;
                        if (empty($order->shipping_address_id)) {
                            $order->shipping_address_id = $customBillingAddressId;
                        }
                    }

                    // if (isset($cust['name'])) $order->custom_billing_name = $cust['name'];
                    $order->custom_billing_country_id = isset($cust['country_id']) ? $cust['country_id'] : null;
                    $order->custom_billing_city = isset($cust['city']) ? $cust['city'] : null;
                    $order->custom_billing_block = isset($cust['block']) ? $cust['block'] : null;
                    $order->custom_billing_street = isset($cust['street']) ? $cust['street'] : null;
                    $order->custom_billing_house_number = isset($cust['house_number']) ? $cust['house_number'] : null;
                    $order->custom_billing_floor = isset($cust['floor']) ? $cust['floor'] : null;
                    $order->custom_billing_address_detail = isset($cust['address_detail']) ? $cust['address_detail'] : null;
                    $order->custom_billing_address_url = isset($cust['address_url']) ? $cust['address_url'] : null;
                }

                $order->save();

                if (isset($isExistingCustomOrderUpdate) && $isExistingCustomOrderUpdate) {
                    OrderDetail::where('order_id', $order->id)->forceDelete();
                    
                    // Invalidate old unpaid payment links ONLY if payable balance or order total changed
                    $totalChanged = (abs($previousOrderTotal - (float)$order->order_total) > 0.0001);
                    $pendingChanged = (abs($previousPendingAmount - (float)$order->pending_amount) > 0.0001);
                    if ($totalChanged || $pendingChanged) {
                        CustomizedOrderSyncService::invalidatePendingPaymentLinks($order->id, 'order_total_modified');
                    }
                }

                // Save Custom Order Details Items for Standalone Order
                if (isset($customOrderSession['items']) && is_array($customOrderSession['items'])) {
                    foreach ($customOrderSession['items'] as $cItem) {
                        $cDetail = new OrderDetail();
                        $cDetail->order_id = $order->id;
                        $cDetail->product_name = $cItem['product_title'] ?? $cItem['item_code'] ?? $cItem['description'] ?? 'Custom Item';
                        $cDetail->product_price = (float)($cItem['price'] ?? 0);
                        $cDetail->quantity = (int)($cItem['quantity'] ?? 1);
                        $cDetail->total_price = isset($cItem['total']) ? (float)$cItem['total'] : ((float)($cItem['price'] ?? 0) * (int)($cItem['quantity'] ?? 1));

                        $attribs = [
                            'color' => isset($cItem['color']) ? $cItem['color'] : '',
                            'size' => isset($cItem['size']) ? $cItem['size'] : '',
                            'item_code' => isset($cItem['item_code']) ? $cItem['item_code'] : '',
                            'description' => isset($cItem['description']) ? $cItem['description'] : '',
                            'image' => isset($cItem['image']) ? $cItem['image'] : '',
                        ];
                        $cDetail->custom_attributes = json_encode($attribs);
                        $cDetail->save();
                    }
                }
                $status = $order->id;
            } else {
                // Standard Order Fields
                $order->order_price         = $order_price;
                $order->delivery_price      = $shippingcharges;
                $order->delivery_rate       = $shipping_charge;
                $order->tax                 = $taxval;
                
                $discountApplied = 0;

                if (config('custom_web.apply_discount')) {
                    $discount            = config('custom_web.discount', 20);
                    $discountApplied     = ($order_price * $discount) / 100;
                    $order->discount_per = $discount;
                    $order->discount     = $discountApplied;
                } else {
                    $discountApplied = $catdiscountApplied;
                    $order->discount     = $catdiscountApplied;
                }

                /*
                 * Coupon Code
                 */
                $couponDiscountFinal = 0;

                if (config('custom_web.show_coupon_card')) {
                    $appliedCoupon = [];

                    if ($user) {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                            if ($appliedCoupon['user_id'] != $user->id) {
                                $appliedCoupon = [];
                            }
                        }

                        if ($appliedCoupon) {
                            $couponId         = isset($appliedCoupon['id']) ? $appliedCoupon['id'] : '';
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount   = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';
                            $couponCode       = isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';

                            if ($couponDiscountIn && $couponDiscount) {
                                if ($couponDiscountIn == 'P') {
                                    $couponDiscountFinal = ($order_price * $couponDiscount) / 100;
                                } else if ($couponDiscountIn == 'F') {
                                    $couponDiscountFinal = $order_price - $couponDiscount;
                                }
                            }

                            $order->coupon_id              = $couponId;
                            $order->coupon_code            = $couponCode;
                            $order->coupon_discount_in     = $couponDiscountIn;
                            $order->coupon_discount        = $couponDiscount;
                            $order->coupon_discount_amount = $couponDiscountFinal;
                        }
                    }
                }
                /*
                 * End :: Coupon Code
                 */

                $finalTotal = $order_price + $shippingcharges + $taxval - $discountApplied - $couponDiscountFinal;

                $setting = \App\Helpers\Helper::getSetting();
                $gift_price = 0;
                $currencyRate = $currency->value ?? 1;

                $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                $giftWrapEnabledSetting = $setting->firstWhere('id', 32);
                if ($giftWrapEnabledSetting && $giftWrapEnabledSetting->setting_val == 1) {
                    if (session('gift_wrap')) {
                        $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice() * $currencyRate;
                        $giftAmountSetting = $setting->firstWhere('id', 33);
                        $gift_price   = (($giftAmountSetting && $giftAmountSetting->setting_val > 0) ? $giftAmountSetting->setting_val : 0) * $currencyRate;

                        if ($order_price >= $giftMinPrice) {
                            $gift_price = 0;
                        }
                        $gift_wrap = session('gift_wrap');
                    }
                }

                $gift_box_price = 0;
                $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                $giftBoxEnabledSetting = $setting->firstWhere('id', 42);
                if ($giftBoxEnabledSetting && $giftBoxEnabledSetting->setting_val == 1) {
                    if (session('gift_box')) {
                        $giftBoxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice() * $currencyRate;
                        $giftBoxAmountSetting = $setting->firstWhere('id', 43);
                        $gift_box_price = (($giftBoxAmountSetting && $giftBoxAmountSetting->setting_val > 0) ? $giftBoxAmountSetting->setting_val : 0) * $currencyRate;

                        if ($order_price >= $giftBoxMinPrice) {
                            $gift_box_price = 0;
                        }
                        $gift_box = session('gift_box');
                    }
                }

                $order->is_gift = false;
                if (session('gift_wrap')) {
                    $order->is_gift = true;
                }

                $order->is_gift_box = false;
                if (session('gift_box')) {
                    $order->is_gift_box = true;
                }

                $order->order_total  = $finalTotal + $gift_price + $gift_box_price;
                $order->gift_wrap    = $gift_price;
                $order->gift_title   = $gift_wrap['title'];
                $order->gift_message = $gift_wrap['message'];

                $order->gift_box         = $gift_box_price;
                $order->gift_box_title   = $gift_box['title'];
                $order->gift_box_message = $gift_box['message'];
            }

            if ($order->save()) {
                if (!$isExistingCustomOrderUpdate) {
                    \Log::info('[CUSTOM_ORDER_DEBUG] Actual order CREATED', [
                        'actual_order_id' => $order->id,
                    ]);
                }

                foreach ($cart as $record) {
                    $productQuery = AdminProduct::where('id', $record->product_id);
                    $productQuery = Helper::applyCategoryVisibility($productQuery);
                    $productQuery = Helper::applyProductVisibility($productQuery);
                    $product = $productQuery->first();
                    if (!$product) {
                        continue;
                    }
                    $product_regular_price = $product->product_regular_price;
                    $product_sale_price  = $product->product_sale_price;
                    $is_product_sale_price_null = $product->is_product_sale_price_null;

                    if ($record->color_id || $record->size_id) {
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $record->size_id, $record->color_id);

                        if ($attributePrice) {
                            $product_regular_price      = $attributePrice->product_regular_price;
                            $product_sale_price         = $attributePrice->product_sale_price;
                            $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                        }
                    }
                    $product_price = $product_sale_price ? $product_sale_price : $product_regular_price;
                    //                    if ($record->is_monogram)
                    //                    {
                    //                        
                    //                        //OrderMonogramDetail
                    //                        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                    //                        $product_price = $product_price + $monogramPrice;
                    //                    }


                    $orderdetail                = new OrderDetail();
                    $orderdetail->order_id      = $order->id;
                    $orderdetail->product_id    = $record->product_id;
                    $orderdetail->product_price = $product_price;
                    $orderdetail->quantity      = $record->quantity;
                    $orderdetail->size_id       = $record->size_id;
                    $orderdetail->color_id      = $record->color_id;
                    $orderdetail->is_same       = $record->is_same;
                    $orderdetail->total_price   = $record->quantity * $product_price;
                    if ($record->note) {
                        $orderdetail->note = $record->note;
                    }
                    // monogram add

                    if ($record->is_monogram) {
                        $orderdetail->is_monogram = $record->is_monogram;
                    }
                    if ($orderdetail->save()) {
                        if ($record->is_monogram) {
                            $monogramList = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get();

                            foreach ($monogramList as $mvalue) {


                                $orderMonogramDetail                      = new OrderMonogramDetail();
                                $monogramPrice                            = \App\Helpers\Helper::getMonogramPrice();
                                $font                                     = Helper::getMonogramStyle($mvalue->font);
                                $placement                                = Helper::getPlacement($mvalue->placement);
                                $orderMonogramDetail->order_id            = $order->id;
                                $orderMonogramDetail->order_detail_id     = $orderdetail->id;
                                $orderMonogramDetail->font                = $mvalue->font;
                                $orderMonogramDetail->initials_count      = $mvalue->initials_count;
                                $orderMonogramDetail->left_text           = $mvalue->left_text;
                                $orderMonogramDetail->center_text         = $mvalue->center_text;
                                $orderMonogramDetail->right_text          = $mvalue->right_text;
                                $orderMonogramDetail->text4               = $mvalue->text4;
                                $orderMonogramDetail->monogram_color      = $mvalue->monogram_color;
                                $orderMonogramDetail->monogram_color_hash = $mvalue->monogram_color_hash;
                                $orderMonogramDetail->monogram_color_id   = $mvalue->monogram_color_id;
                                $orderMonogramDetail->placement           = $mvalue->placement;
                                $orderMonogramDetail->placement           = $mvalue->placement;
                                $orderMonogramDetail->placement_name      = $placement->placement_name;
                                $orderMonogramDetail->placement_desc      = $placement->placement_desc;
                                $orderMonogramDetail->placement_price     = $placement->placement_price;
                                $orderMonogramDetail->placement_price     = $placement->placement_price;
                                $orderMonogramDetail->placement_image     = $placement->placement_image;
                                $orderMonogramDetail->font_style_name     = $font->style_name;
                                $orderMonogramDetail->font_style_image    = $font->style_image;
                                $orderMonogramDetail->monogram_price      = $monogramPrice;
                                $orderMonogramDetail->monogram_note       = $mvalue->monogram_note ? $mvalue->monogram_note : '';
                                $orderMonogramDetail->save();
                            }
                        }
                        if ($order_status != config('custom_web.fail', 7)) {
                            /*
                             * Update Main quantity, Only if order is not failed
                             */
                            $adminProduct                   = AdminProduct::find($record->product_id);
                            $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                            $adminProduct->save();
                            if ($adminProduct->have_size || $adminProduct->have_color) {
                                if ($record->size_id || $record->color_id) {
                                    $adminProductAttriutes = AdminProductAttribute::where(['products_id' => $record->product_id]);
                                    if ($record->size_id) {
                                        $adminProductAttriutes->where('size_id', $record->size_id);
                                    }
                                    if ($record->color_id) {
                                        $adminProductAttriutes->where('color_id', $record->color_id);
                                    }
                                    $adminProductAttriutes           = $adminProductAttriutes->first();
                                    $adminProductAttriutes->quantity = $adminProductAttriutes->quantity - $record->quantity;
                                    $adminProductAttriutes->save();
                                }
                            }
                        }
                        $status = $order->id;
                    } else {
                        $status = false;
                    }
                }

                // Handle Customized Sub-Order
                // Only if NOT standalone custom order (Standalone has type 'custom')
                if (session('customized_order_details')) {
                    // Check if it's standalone (cart empty). 
                    // If cart is empty, we ALREADY saved the custom details in the MAIN $order object above.
                    // So we only create SUB-ORDER if cart was NOT empty.
                    if ($cart->count() > 0) {
                        $customOrderSession = session('customized_order_details');

                        // Allow both purchase_order and quotation
                        if (isset($customOrderSession['type'])) {

                            $customAdvance = 0;
                            // Only calculate advance if purchase_order
                            if ($customOrderSession['type'] == 'purchase_order' && !empty($customOrderSession['payment']['advance'])) {
                                $customAdvance = (float) str_replace(',', '', $customOrderSession['payment']['advance']);
                            }

                            // Calculate total custom price
                            $customTotal = 0;
                            if (isset($customOrderSession['items']) && is_array($customOrderSession['items'])) {
                                foreach ($customOrderSession['items'] as $cItem) {
                                    // $cItem['total'] might be string or float or calculated from price * qty
                                    $itemTotal = isset($cItem['total']) ? (float) $cItem['total'] : ((float)($cItem['price'] ?? 0) * (int)($cItem['quantity'] ?? 1));
                                    $customTotal += $itemTotal;
                                }
                            }


                            try {
                                $existingSubOrder = Order::where('parent_id', $order->id)->where('order_type', 'custom')->first();
                                if (!$existingSubOrder && $linkedTempOrder) {
                                    $existingSubOrder = $linkedTempOrder->getLinkedOrder();
                                }
                                $isExistingSubOrder = ($existingSubOrder && $existingSubOrder->id);
                                $subOrder = $isExistingSubOrder ? $existingSubOrder : new Order();

                                $subOrder->parent_id           = $order->id;
                                $subOrder->order_type          = 'custom';
                                $subOrder->custom_order_type   = $customOrderSession['type']; // 'purchase_order' or 'quotation'
                                $subOrder->user_id             = $user->id;
                                $subOrder->physical_store_id   = $user->physical_store_id;
                                $subOrder->shipping_address_id = $address->id ?? null;
                                $subOrder->cphone              = isset($customOrderSession['customer']['phone']) ? $customOrderSession['customer']['phone'] : session('cphone');
                                $subOrder->email               = isset($customOrderSession['customer']['email']) ? $customOrderSession['customer']['email'] : null;
                                $subOrder->billing_address_id  = $address->id ?? null;
                                $subOrder->transaction_id      = $payment_id;
                                $resolvedSubOrderPaymentMethod = $paymentMethod ?? session()->get('paymentMethod');
                                if ($paymentMethod && isset($hesabePaymentMethod[$paymentMethod])) {
                                    $resolvedSubOrderPaymentMethod = $hesabePaymentMethod[$paymentMethod];
                                }
                                $subOrder->payment_method      = $resolvedSubOrderPaymentMethod;
                                $subOrder->order_status        = 7;

                                // Retrieve financial fields from session
                                $customShipping = isset($customOrderSession['shipping']) ? (float)$customOrderSession['shipping'] : 0;
                                $customDiscountType = isset($customOrderSession['discount_type']) ? $customOrderSession['discount_type'] : null;
                                $customDiscountVal = isset($customOrderSession['discount_val']) ? (float)$customOrderSession['discount_val'] : 0;

                                // Calculate Discount Amount
                                $customDiscountAmount = 0;
                                if ($customDiscountType == 'percentage') {
                                    $customDiscountAmount = ($customTotal * $customDiscountVal) / 100;
                                } elseif ($customDiscountType == 'flat') {
                                    $customDiscountAmount = $customDiscountVal;
                                }

                                $subOrderGrandTotal = max(0.0, round($customTotal + $customShipping - $customDiscountAmount, 3));

                                 $subOrder->order_price         = $customTotal;
                                $subOrder->delivery_price      = $customShipping;
                                $subOrder->delivery_rate       = 0;
                                $subOrder->tax                 = 0;
                                $subOrder->order_total         = $subOrderGrandTotal;
                                $subOrder->discount_per        = ($customDiscountType == 'percentage') ? $customDiscountVal : 0;
                                $subOrder->discount            = $customDiscountAmount;
                                $subOrder->discount_type       = $customDiscountType;

                                $payAtStoreMethods = [4, 6];
                                $resolvedSubPM = $paymentMethod ?? session()->get('paymentMethod');
                                $isSubPayAtShopSelected = in_array((int)$resolvedSubPM, $payAtStoreMethods, true);
                                $existingSubPaidTotal = $isExistingSubOrder ? PaymentCalculationService::getSuccessfulPaymentTotal($subOrder->id) : 0.0;
                                $subPendingBeforeThisPayment = max(0.0, round($subOrderGrandTotal - $existingSubPaidTotal, 3));

                                $isSubGeneratePendingRequested = false;
                                if (isset($customOrderSession['payment']['generate_pending_link'])) {
                                    $isSubGeneratePendingRequested = (bool)$customOrderSession['payment']['generate_pending_link'];
                                } elseif (isset($customOrderSession['generate_pending_payment_link'])) {
                                    $isSubGeneratePendingRequested = (bool)$customOrderSession['generate_pending_payment_link'];
                                }

                                $subPayAtShopPaymentAmount = 0.0;
                                $createPayAtShopEntry = false;
                                if ($isSubPayAtShopSelected) {
                                    if ($isExistingSubOrder && $existingSubPaidTotal > 0) {
                                        if ($isSubGeneratePendingRequested) {
                                            $subPayAtShopPaymentAmount = $subPendingBeforeThisPayment;
                                            $createPayAtShopEntry = ($subPayAtShopPaymentAmount > 0.0001);
                                        } else {
                                            $subPayAtShopPaymentAmount = 0.0;
                                            $createPayAtShopEntry = false;
                                        }
                                    } elseif ($customAdvance > 0) {
                                        $subPayAtShopPaymentAmount = min($customAdvance, $subOrderGrandTotal);
                                        if ($subPayAtShopPaymentAmount > 0.0001) {
                                            $createPayAtShopEntry = true;
                                        }
                                    } else {
                                        $subPayAtShopPaymentAmount = $isSubGeneratePendingRequested ? $subOrderGrandTotal : 0.0;
                                        if ($subPayAtShopPaymentAmount > 0.0001) {
                                            $createPayAtShopEntry = true;
                                        }
                                    }
                                }

                                if ($isExistingSubOrder) {
                                    $beforePaid = $subOrder->paid_amount;
                                    $beforePending = $subOrder->pending_amount;
                                    $effectiveSubAdvance = ($existingSubPaidTotal > 0) ? $existingSubPaidTotal : ($isSubPayAtShopSelected ? min($customAdvance, $subOrderGrandTotal) : $customAdvance);
                                    $subOrder->paid_amount = $effectiveSubAdvance;
                                    $subOrder->pending_amount = max(0.0, round($subOrderGrandTotal - $effectiveSubAdvance, 3));
                                    $subOrder->is_advance_payment_done = ($effectiveSubAdvance > 0 || ($isSubPayAtShopSelected && $subPayAtShopPaymentAmount > 0)) ? 1 : 0;

                                    PaymentCalculationService::logPaymentDebug([
                                        'order_id' => $subOrder->id,
                                        'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : 'N/A',
                                        'user_id' => $subOrder->user_id,
                                        'field' => 'sub_orders.paid_amount',
                                        'before' => $beforePaid,
                                        'request' => $customAdvance,
                                        'calculated' => $effectiveSubAdvance,
                                        'written' => $subOrder->paid_amount,
                                        'source' => 'CheckoutController::createOrder (SubOrder Update)',
                                    ]);
                                } else {
                                    $initialPaidAmount = $isSubPayAtShopSelected ? $subPayAtShopPaymentAmount : $customAdvance;
                                    $subOrder->paid_amount         = $initialPaidAmount;
                                    $subOrder->pending_amount      = max(0.0, round($subOrderGrandTotal - $initialPaidAmount, 3));
                                    if ($isSubPayAtShopSelected && $subPayAtShopPaymentAmount > 0) {
                                        $subOrder->is_advance_payment_done = 1;
                                    }

                                    PaymentCalculationService::logPaymentDebug([
                                        'order_id' => $subOrder->id ?? 'NEW_SUB',
                                        'temporary_order_id' => $linkedTempOrder ? $linkedTempOrder->id : 'N/A',
                                        'user_id' => $subOrder->user_id,
                                        'field' => 'sub_orders.paid_amount',
                                        'before' => 0,
                                        'request' => $customAdvance,
                                        'calculated' => $initialPaidAmount,
                                        'written' => $subOrder->paid_amount,
                                        'source' => 'CheckoutController::createOrder (New SubOrder)',
                                    ]);
                                }
                                $subOrder->custom_order_details = json_encode($customOrderSession);

                                    // Persist structured billing fields into dedicated columns (migration added)
                                if (isset($customOrderSession['customer']) && is_array($customOrderSession['customer'])) {
                                    $cust = $customOrderSession['customer'];
                                    
                                    $customSubBillingAddressId = $cust['customer_address_id'] ?? $cust['billing_address_id'] ?? ($linkedTempOrder->customer_address_id ?? null);
                                    if (!empty($customSubBillingAddressId)) {
                                        $subOrder->billing_address_id = $customSubBillingAddressId;
                                        if (empty($subOrder->shipping_address_id)) {
                                            $subOrder->shipping_address_id = $customSubBillingAddressId;
                                        }
                                    }

                                    // if (isset($cust['name'])) $subOrder->custom_billing_name = $cust['name'];
                                    $subOrder->custom_billing_country_id = isset($cust['country_id']) ? $cust['country_id'] : null;
                                    $subOrder->custom_billing_city = isset($cust['city']) ? $cust['city'] : null;
                                    $subOrder->custom_billing_block = isset($cust['block']) ? $cust['block'] : null;
                                    $subOrder->custom_billing_street = isset($cust['street']) ? $cust['street'] : null;
                                    $subOrder->custom_billing_house_number = isset($cust['house_number']) ? $cust['house_number'] : null;
                                    $subOrder->custom_billing_floor = isset($cust['floor']) ? $cust['floor'] : null;
                                    $subOrder->custom_billing_address_detail = isset($cust['address_detail']) ? $cust['address_detail'] : null;
                                    $subOrder->custom_billing_address_url = isset($cust['address_url']) ? $cust['address_url'] : null;
                                    // email already handled above
                                }

                                $subOrder->user_agent_name     = Helper::getBrowserName();
                                $subOrder->created_at          = date("Y-m-d H:i:s");

                                if ($currency) {
                                    $subOrder->currency_code = $currency->code;
                                    $subOrder->currency_rate = $currency->value;
                                }
                            } catch (\Throwable $e) {
                                \Log::error('[CUSTOM_ORDER_DEBUG] Exception preparing subOrder: ' . $e->getMessage(), [
                                    'exception' => $e
                                ]);
                            }

                            if ($subOrder->save()) {
                                // Create PaymentLink entry for Pay at Shop payment history
                                if (isset($createPayAtShopEntry) && $createPayAtShopEntry && isset($subPayAtShopPaymentAmount) && $subPayAtShopPaymentAmount > 0.0001) {
                                    $isDuplicateSubPayment = PaymentLink::where('order_id', $subOrder->id)
                                        ->where('payment_type', $resolvedSubPM)
                                        ->where('amount', $subPayAtShopPaymentAmount)
                                        ->where('status', 'paid')
                                        ->where('created_at', '>=', now()->subSeconds(30))
                                        ->exists();

                                    if (!$isDuplicateSubPayment) {
                                        $paymentMethodLabel = Helper::getPaymentMethodByID($resolvedSubPM);
                                        PaymentLink::create([
                                            'order_id' => $subOrder->id,
                                            'payment_link' => null,
                                            'payment_request_id' => 'pay_at_shop_' . $subOrder->id . '_' . time() . '_' . rand(100, 999),
                                            'amount' => $subPayAtShopPaymentAmount,
                                            'generated_from' => 'frontend',
                                            'generated_by' => $user ? $user->id : null,
                                            'status' => 'paid',
                                            'paid_at' => now(),
                                            'paid_amount' => $subPayAtShopPaymentAmount,
                                            'payment_type' => $resolvedSubPM,
                                            'is_advance_payment' => ($existingSubPaidTotal == 0 ? 1 : 0),
                                            'customer_email' => $customOrderSession['customer']['email'] ?? null,
                                            'email_sent_count' => 0,
                                            'last_email_sent_at' => null,
                                            'gateway_response' => [
                                                'source' => 'frontend_pay_at_store',
                                                'payment_method_label' => $paymentMethodLabel,
                                                'created_at' => now()->toDateTimeString()
                                            ]
                                        ]);
                                    }

                                    // Authoritative DB calculation from successful payments
                                    $authoritativeSubPaidTotal = PaymentCalculationService::getSuccessfulPaymentTotal($subOrder->id);
                                    $subOrder->paid_amount = $authoritativeSubPaidTotal;
                                    $subOrder->pending_amount = max(0.0, round($subOrderGrandTotal - $authoritativeSubPaidTotal, 3));
                                    $subOrder->is_advance_payment_done = ($authoritativeSubPaidTotal > 0) ? 1 : 0;
                                    // Keep subOrder order_status in pending / initial state (7) until processing
                                    $subOrder->save();
                                }
                                
                                if ($isExistingSubOrder) {
                                    OrderDetail::where('order_id', $subOrder->id)->forceDelete();
                                }
                                
                                // Save Custom Order Details
                                if (isset($customOrderSession['items']) && is_array($customOrderSession['items'])) {
                                    foreach ($customOrderSession['items'] as $cItem) {
                                        $cDetail = new OrderDetail();
                                        $cDetail->order_id = $subOrder->id;
                                        // product_id is null for custom items
                                        $cDetail->product_name = $cItem['product_title'] ?? $cItem['item_code'] ?? $cItem['description'] ?? 'Custom Item';
                                        $cDetail->product_price = (float)($cItem['price'] ?? 0);
                                        $cDetail->quantity = (int)($cItem['quantity'] ?? 1);
                                        $cDetail->total_price = isset($cItem['total']) ? (float)$cItem['total'] : ((float)($cItem['price'] ?? 0) * (int)($cItem['quantity'] ?? 1));

                                        // Prepare custom attributes
                                        $attribs = [
                                            'color' => isset($cItem['color']) ? $cItem['color'] : '',
                                            'size' => isset($cItem['size']) ? $cItem['size'] : '',
                                            'item_code' => isset($cItem['item_code']) ? $cItem['item_code'] : '',
                                            'description' => isset($cItem['description']) ? $cItem['description'] : '',
                                            'image' => isset($cItem['image']) ? $cItem['image'] : '',
                                        ];
                                        $cDetail->custom_attributes = json_encode($attribs);
                                        $cDetail->save();
                                    }
                                }
                            }
                        }
                    }
                }

                if ($isSuccess && $couponId) {
                    $couponData = \App\UserModel\CouponCode::where(['coupon_id' => $couponId])->first();
                    if ($couponData) {
                        if (isset($appliedCoupon['allow_multiple']) && $appliedCoupon['allow_multiple'] == 1) {
                            /*
                             * If allow multiple then don't update is_applied
                             */
                        } else {
                            $couponData->is_applied = 1;
                            $couponData->save();
                        }
                    }
                }

                $tempDraftId = session('customized_order_details.temporary_order_id') ?? $request->temporary_order_id ?? null;
                if ($tempDraftId && Auth::check()) {
                    $tempOrder = TemporaryOrder::where('id', $tempDraftId)
                        ->where('user_id', Auth::id())
                        ->first();
                    if ($tempOrder) {
                        $targetOrder = ($order->order_type == 'custom') ? $order : (isset($subOrder) ? $subOrder : $order);
                        $totalPaid = PaymentCalculationService::getSuccessfulPaymentTotal($targetOrder->id);
                        $beforeTempAdvance = $tempOrder->advance_price;
                        $beforeTempPending = $tempOrder->pending_amount;

                        $tempDetails = json_decode($tempOrder->custom_order_details, true) ?: [];
                        $tempDetails['order_id'] = $targetOrder->id;
                        $tempDetails['paid_amount'] = $totalPaid;
                        $tempDetails['pending_amount'] = (float)$targetOrder->pending_amount;
                        if (isset($tempDetails['payment'])) {
                            $tempDetails['payment']['paid'] = $totalPaid;
                            $tempDetails['payment']['pending'] = (float)$targetOrder->pending_amount;
                            $tempDetails['payment']['advance'] = ($totalPaid > 0) ? $totalPaid : ($tempDetails['payment']['advance'] ?? 0);
                        }
                        if (isset($tempDetails['calculation'])) {
                            $tempDetails['calculation']['paid'] = $totalPaid;
                            $tempDetails['calculation']['pending'] = (float)$targetOrder->pending_amount;
                            $tempDetails['calculation']['advance'] = ($totalPaid > 0) ? $totalPaid : ($tempDetails['calculation']['advance'] ?? 0);
                        }
                        $tempOrder->custom_order_details = json_encode($tempDetails);
                        $tempOrder->subtotal = (float)$targetOrder->order_price;
                        $tempOrder->shipping_price = (float)$targetOrder->delivery_price;
                        $tempOrder->discount_type = $targetOrder->discount_type;
                        $tempOrder->discount_val = (float)$targetOrder->discount_per;
                        $tempOrder->discount_amount = (float)$targetOrder->discount;
                        $tempOrder->grand_total = (float)$targetOrder->order_total;
                        $tempOrder->advance_price = ($totalPaid > 0) ? $totalPaid : (float)$tempOrder->advance_price;
                        $tempOrder->pending_amount = (float)$targetOrder->pending_amount;
                        $oldTempStatus = $tempOrder->status;
                        $newTempStatus = TemporaryOrder::mapOrderStatusToTempStatus($targetOrder->order_status ?? 7);
                        // Do NOT mark as completed. Keep pending or sync with created order's status.
                        $tempOrder->status = $newTempStatus;
                        $tempOrder->save();

                        \Log::info('[CUSTOM_ORDER_DEBUG] Updating temporary order', [
                            'temporary_order_id' => $tempOrder->id,
                            'old_status' => $oldTempStatus,
                            'new_status' => $newTempStatus,
                            'old_pending_amount' => $beforeTempPending,
                            'new_pending_amount' => $tempOrder->pending_amount,
                        ]);

                        PaymentCalculationService::logPaymentDebug([
                            'order_id' => $targetOrder->id,
                            'temporary_order_id' => $tempOrder->id,
                            'user_id' => $tempOrder->user_id,
                            'field' => 'temporary_orders.advance_price',
                            'before' => $beforeTempAdvance,
                            'request' => $targetOrder->paid_amount,
                            'calculated' => $totalPaid,
                            'written' => $tempOrder->advance_price,
                            'source' => 'CheckoutController::createOrder (Post Order Save TemporaryOrder Sync)',
                        ]);

                        PaymentCalculationService::logPaymentDebug([
                            'order_id' => $targetOrder->id,
                            'temporary_order_id' => $tempOrder->id,
                            'user_id' => $tempOrder->user_id,
                            'field' => 'temporary_orders.pending_amount',
                            'before' => $beforeTempPending,
                            'request' => $targetOrder->pending_amount,
                            'calculated' => $targetOrder->pending_amount,
                            'written' => $tempOrder->pending_amount,
                            'source' => 'CheckoutController::createOrder (Post Order Save TemporaryOrder Sync)',
                        ]);
                    }
                }

                DB::commit();
                \Log::info('[CUSTOM_ORDER_DEBUG] Transaction COMMIT');
            } else {
                $status = false;
                DB::rollback();
                \Log::info('[CUSTOM_ORDER_DEBUG] Transaction ROLLBACK', [
                    'error' => 'Failed to save order record.'
                ]);
            }
        // }
        if ($status) {
            if ($isSuccess) {

                if ($order->order_type == 'custom') {
                    $this->sendCustomOrderMail($order, true);
                } else {
                    $this->sendMail($order);
                }

                Session::forget('paymentMethod');
                Session::forget('couponCode');
                Session::forget('customized_order_details');
                Session::forget('customized_items');
                session()->put('gift_wrap', null);
                session()->put('gift_box', null);
            }
            //Session::forget('paymentMethod');
        }



        return $status;
    }

    public function updatedCreatedOrder($paymentId, $order_status, $isSuccess = true, $orderId = NULL, $paymentResponse = NULL)
    {
        $status  = false;
        $orderId = $orderId ? $orderId : session()->get('customer_order_id');
        $order   = Order::where(['id' => $orderId])->first();

        if ($order->order_type !== "custom") {
            // 1. Update Payment Details (Main Order)
            $this->handlePaymentResponse($order, $paymentId, $order_status, $paymentResponse);

            if ($order->save()) {

                // 2. Update Sub-Orders (Custom Orders)
                $this->handleSubOrders($order, $paymentId, $order_status, $paymentResponse, $isSuccess);

                if ($isSuccess) {
                    // 3. Handle Success Logic (Inventory, Email, Session)
                    $this->handleSuccessLogic($order);
                }
                $status = $order->id;
            }
        }
        else {
            $this->handleSubOrders($order, $paymentId, 7, $paymentResponse, $isSuccess);

            if ($isSuccess) {
                // 3. Handle Success Logic (Inventory, Email, Session)
                $this->handleSuccessLogic($order);
            }
            $status = $order->id;
        }

        // Session::forget('paymentMethod');
        // Session::forget('customer_order_id');

        return $status;
    }

    private function handlePaymentResponse($order, $paymentId, $order_status, $paymentResponse)
    {
        if ($paymentResponse) {
            if (isset($paymentResponse['Method']) && $paymentResponse['Method'] != 0) {
                $hesabePaymentMethod = config('custom_web.hesabe_payment_method');
                $paymentMethod       = isset($hesabePaymentMethod[$paymentResponse['Method']]) ? $hesabePaymentMethod[$paymentResponse['Method']] : '';
                if ($paymentMethod) {
                    $order->payment_method = $paymentMethod;
                }
            }
        }
        $order->transaction_id   = $paymentId;
        $order->order_status     = $order_status;
        $order->payment_response = json_encode($paymentResponse);
        $order->created_at       = date("Y-m-d H:i:s");
    }

    private function handleSubOrders($mainOrder, $paymentId, $order_status, $paymentResponse, $isSuccess)
    {
        // If the main order itself is a custom order (Standalone)
        if ($mainOrder->order_type == 'custom') {
            $this->processCustomOrderUpdate($mainOrder, $paymentId, 7, $paymentResponse, $isSuccess);
        }

        // Handle Sub-orders (if any)
        $subOrders = Order::where('parent_id', $mainOrder->id)->get();
        foreach ($subOrders as $subOrder) {
            $this->processCustomOrderUpdate($subOrder, $paymentId, 7, $paymentResponse, $isSuccess);
        }
    }

    private function processCustomOrderUpdate($subOrder, $paymentId, $order_status, $paymentResponse, $isSuccess)
    {
        // Update basic payment info
        $this->handlePaymentResponse($subOrder, $paymentId, 7, $paymentResponse);

        // Update customer contact info if available in custom details
        $customDetails = json_decode($subOrder->custom_order_details, true);
        if ($customDetails && isset($customDetails['customer'])) {
            if (isset($customDetails['customer']['phone'])) {
                $subOrder->cphone = $customDetails['customer']['phone'];
            }
            if (isset($customDetails['customer']['email'])) {
                $subOrder->email = $customDetails['customer']['email'];
            }
        }

        $subOrder->order_status = 7; // Pending (as requested)

        $paid = $this->getSuccessfulPaymentTotal($subOrder->id);
        $total = (float) $subOrder->order_total;

        // Tolerance for float comparison
        if (abs($paid - $total) < 0.001) {
            // Send Customized Order Email
            $this->sendCustomOrderMail($subOrder, true);
        } else {
            // Send Customized Order Email
            $this->sendCustomOrderMail($subOrder, false);
        }

        $subOrder->save();
    }

    private function checkAndUpdateCustomOrderStatus($subOrder)
    {
        $paid = $this->getSuccessfulPaymentTotal($subOrder->id);
        $total = (float) $subOrder->order_total;

        // Tolerance for float comparison
        if (abs($paid - $total) < 0.001) {
            // Fully Paid
            $subOrder->order_status = Helper::getOrderDefaultStatus(); // Normal success status
        } else {
            // Partially Paid / Pending
            $subOrder->order_status = 7; // Pending (as requested)
        }
    }

    private function handleSuccessLogic($order)
    {
        /*
         * Update Main quantity
         */
        $orderDetail = OrderDetail::where('order_id', $order->id)->get();
        foreach ($orderDetail as $record) {
            $adminProduct = AdminProduct::find($record->product_id);
            if ($adminProduct) {
                $adminProduct->product_quantity = $adminProduct->product_quantity - $record->quantity;
                $adminProduct->save();

                if ($adminProduct->have_size || $adminProduct->have_color) {
                    $this->updateProductAttributesQty($record);
                }
            }
        }

        if ($order->order_type != 'custom') {
            $this->sendMail($order);
        }

        /*
         * Update coupon
         */
        $this->updateCouponStatus();

        /*
         * Cleanup Session
         */
        Session::forget('couponCode');
        session()->put('gift_wrap', null);
        session()->put('gift_box', null);
        Session::forget('customized_order_details');
        Session::forget('customized_items');
    }

    private function updateProductAttributesQty($record)
    {
        $query = AdminProductAttribute::where(['products_id' => $record->product_id]);
        if ($record->size_id) {
            $query->where('size_id', $record->size_id);
        }
        if ($record->color_id) {
            $query->where('color_id', $record->color_id);
        }
        $adminProductAttributes = $query->first();

        if ($adminProductAttributes) {
            $adminProductAttributes->quantity = $adminProductAttributes->quantity - $record->quantity;
            $adminProductAttributes->save();
        }
    }

    private function updateCouponStatus()
    {
        $appliedCoupon = session('couponCode');
        if ($appliedCoupon) {
            $couponId = isset($appliedCoupon['id']) ? $appliedCoupon['id'] : '';
            if ($couponId) {
                if (!isset($appliedCoupon['allow_multiple']) || $appliedCoupon['allow_multiple'] != 1) {
                    $couponData = \App\UserModel\CouponCode::where(['coupon_id' => $couponId])->first();
                    if ($couponData) {
                        $couponData->is_applied = 1;
                        $couponData->save();
                    }
                }
            }
        }
    }

    public function orderSummary($id)
    {
        $orderProductDetail = [];
        $orderCustom = null;
        $user = Auth::user();
        if ($id && $user) {
            $order = Order::where('id', $id)->where('user_id', $user->id)->withTrashed()->first();
            // $this->sendCustomOrderMail($order, false);
            if ($order) {
                $paymentLinks = PaymentLink::where('status', 'pending')
                    ->where(function ($query) use ($id, $order) {
                        $query->where('order_id', $id);

                        if (!empty($order->parent_id)) {
                            $query->orWhere('order_id', $order->parent_id);
                        }
                    })
                    ->latest()
                    ->get()
                    ->first(function ($item) {
                        $gatewayResponse = is_array($item->gateway_response) ? $item->gateway_response : (json_decode($item->gateway_response, true) ?: []);
                        return ($gatewayResponse['source'] ?? null) !== 'frontend_pay_at_store';
                    });
                $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
                if ($order->custom_order_type) {
                    if($order->custom_order_type == 'purchase_order') {
                        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
                        return view('web.user.order_detail_customized', compact('order', 'address', 'orderProductDetail', 'paymentLinks'));
                    }
                    else {
                        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
                        return view('web.user.quotation', compact('order', 'address', 'orderProductDetail'));
                    }
                }
                else {
                    $orderCustom = Order::where('parent_id', $id)->where('user_id', $user->id)->withTrashed()->first();
                    if($orderCustom) {
                        $orderProductDetail = OrderDetail::where('order_id', $orderCustom->id)->get();
                    }
                }
                return view('web.checkout.order_summary', compact('order', 'address', 'orderProductDetail', 'orderCustom', 'paymentLinks'));
            } else {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
            }
        } else {

            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function printOrder($id, $page)
    {
        $user = Auth::user();
        if ($id && $user) {
            $order = Order::where('id', $id)->where('user_id', $user->id)->withTrashed()->first();
            if ($order) {
                $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
                if($order->order_type == 'custom'){
                    if($order->custom_order_type == 'purchase_order') {
                        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
                        $paymentLink = PaymentLink::where('status', 'pending')
                            ->where(function ($query) use ($id, $order) {
                                $query->where('order_id', $id);
                                if (!empty($order->parent_id)) {
                                    $query->orWhere('order_id', $order->parent_id);
                                }
                            })
                            ->whereNotNull('payment_link')
                            ->where('payment_link', '!=', '')
                            ->latest()
                            ->first();
                        return view('web.checkout.customized_order_summary_print', compact('order', 'orderProductDetail', 'address', 'page', 'paymentLink'));
                    }
                    else {
                        $orderProductDetail = OrderDetail::where('order_id', $id)->get();
                        return view('web.checkout.customized_quotation_summary_print', compact('order', 'orderProductDetail', 'address', 'page'));
                    }
                }
                return view('web.checkout.order_summary_print', compact('order', 'address', 'page'));
            } else {
                return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
            }
        } else {

            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.error-message'));
        }
    }

    public function ChangeDefaultAddress($id)
    {

        $user = Auth::user();

        if ($user && $id) {

            $address = UserAddress::where('id', $id)->where('user_id', $user->id)->first();

            if ($address) {
                if ($address->is_country_active) {
                    UserAddress::where('user_id', $user->id)->where('id', '!=', $id)->update(['is_default' => 0]);

                    if ($user->pickupstore == 0) {
                        $address->is_default = 1;
                    }

                    if ($address->save()) {
                        session()->put('default_address', $id);
                        session()->put('address_selected', true);
                        session()->forget('checkout_error');
                        $this->syncCurrencyByAddress($address);
                        Session::forget('cphone');
                        $cart = ShoppingCartDetail::where('user_id', $user->id)->where('is_active', 1)->get();
                        if ($cart->count() > 0) {
                            Helper::removeShippingCache($address, $cart, $user->id);
                        }
                        //return Redirect::back()->with('success', Lang::get($this->langFile . '.default-address-message'));

                        return Redirect::back();
                    } else {

                        return Redirect::back()->with('error', Lang::get($this->langFile . '.error-message'));
                    }
                } else {
                    return Redirect::back()->with('error', Lang::get($this->langFile . '.country-not-active'));
                }
            } else {
                $address = UserAddress::where('id', $id)->where('pickupstore', 1)->first();
                if ($address) {
                    UserAddress::where('user_id', $user->id)->update(['is_default' => 0]);
                    session()->put('default_address', $id);
                    session()->put('address_selected', true);
                    $this->syncCurrencyByAddress($address);
                    return Redirect::back();
                } else {
                    Session::forget('default_address');
                    Session::forget('address_selected');
                    Session::forget('cphone');
                    return Redirect::back()->with('error', Lang::get($this->langFile . '.no-record-message'));
                }
            }
        } else {
            return Redirect::back()->with('error', Lang::get($this->langFile . '.no-record-message'));
        }
    }

    public function manualPaymentTest($status, $orderId = null)
    {
        // 1. Resolve Order ID
        $orderId = $orderId ? $orderId : session()->get('customer_order_id');

        if (!$orderId) {
            return redirect()->route('web.checkout.index')->with('error', 'No active order found to test.');
        }

        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->route('web.checkout.index')->with('error', 'Order not found.');
        }

        // 2. Simulate Payment Response Data
        $isSuccess = ($status === 'success');
        $transactionId = 'TEST-TXN-' . time() . '-' . mt_rand(1000, 9999);

        // Mock Hesabe/UPayments response structure
        $paymentResponse = [
            'status' => $isSuccess, // true/false
            'message' => $isSuccess ? 'Manual Test Success' : 'Manual Test Failed',
            'Code' => $isSuccess ? 0 : 999, // 0 usually success
            'Method' => 1, // 1 = KNET (example from config)
            'PaymentID' => $transactionId,
            'Result' => $isSuccess ? 'Captured' : 'Not Captured',
            'PostDate' => date('mdY'),
            'TranID' => $transactionId,
            'Ref' => 'TEST-REF-' . time(),
            'TrackID' => $orderId,
            'OrderID' => $orderId,
            'cust_ref' => 'TEST-CUST-' . time(),
            // Add custom fields if needed
        ];

        // 3. Determine Order Status
        // If success -> Default processing status (e.g. 1)
        // If fail -> Default failed status (e.g. 7 or config('custom_web.fail'))
        $order_status = $isSuccess ? Helper::getOrderDefaultStatus() : config('custom_web.fail', 7);

        // 4. Trigger Post-Payment Logic
        // This function handles:
        // - Updating main Order (status, payment details)
        // - Updating/Creating Sub-Orders (via handleSubOrders)
        // - Sending Emails (via handleSuccessLogic -> sendMail / sendCustomOrderMail)
        // - Deduction of Inventory

        $result = $this->updatedCreatedOrder($transactionId, $order_status, $isSuccess, $orderId, $paymentResponse);

        // 5. Redirect based on result
        if ($result && $isSuccess) {
            return redirect()->route('web.checkout.payment_success');
        } else {
            return redirect()->route('web.checkout.payment_error');
        }
    }

    public function sendCustomOrderMail($order, $isSuccess, $paymentLink = null)
    {
        try {
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
            $reciveEmail      = Helper::getSettingById(18);
            $reciveAdminEmail = "";

            $shipping_address = null;

            $paymentLinkInfo = PaymentLink::where(function ($query) use ($order) {
                $query->where('order_id', $order->id);

                if (!empty($order->parent_id)) {
                    $query->orWhere('order_id', $order->parent_id);
                }
            })
            ->latest()->first();

            // Prepare Data
            if($order->order_type == 'custom') {
                $customDetails = json_decode($order->custom_order_details, true);
                $customerInfo = isset($customDetails['customer']) ? $customDetails['customer'] : null;
                $cust = is_array($customerInfo) ? $customerInfo : [];
                $countryName = '';
                if($customerInfo) {
                    // Country name lookup
                    if (isset($cust['country_id']) && $cust['country_id']) {
                        $cn = \App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                        if ($cn) $countryName = $cn->country_name;
                    }
                }

                $shipping_address = (object) [
                    'first_name' => $customerInfo['name'] ?? 'Customer',
                    'last_name' => '',
                    'email' => $customerInfo['email'] ?? '',
                    'phone' => $customerInfo['phone'] ?? '',
                    'block'=> $cust['block'] ?? '',
                    'floor' => $cust['floor'] ?? '',
                    'house_number' => $cust['house_number'] ?? '',
                    'house' => $cust['house_number'] ?? '',
                    'city' => $cust['city'] ?? '',
                    'country_name' => $countryName,
                    'address_detail' => $cust['address_detail'] ?? '',
                    'street' => $cust['street'] ?? '',
                ];
            } else {
                $shipping_address = UserAddress::find($order->shipping_address_id);
            }

            $customerCareEmail = Helper::getCustomerCareEmail();
            $app_name          = Helper::getAppName();

            $from['email'] = $admin_mail;
            $from['name']  = $app_name;

            // Parse custom details once
            // $customDetails = json_decode($order->custom_order_details, true) ?: [];
            // $custName = $customDetails['customer']['name'] ?? 'Customer';
             $customDetails = json_decode($order->custom_order_details, true) ?: [];
             $custName = $customDetails['customer']['name'] ?? ($shipping_address ? $shipping_address->first_name . ' ' . $shipping_address->last_name : 'Customer');

            // If this is a quotation, use the quotation view and prefer the customer email from custom details
            $isQuotation = (isset($order->custom_order_type) && $order->custom_order_type == 'quotation');
            $mailTo = $customDetails['customer']['email'] ?? $order->email ?? null;

            if ($isQuotation) {
                $view   = 'web.emails.custom_order_quotation';
            } elseif ($paymentLink) {
                 $view   = 'web.emails.payment_due_link';
            } else {
                if($isSuccess) {
                    $view   = 'web.emails.custom_order_invoice';
                }
                else {
                    $view   = 'web.emails.custom_order';
                }
            }

            // Fallback if still no email: try current user, or parent order's shipping address
            if (empty($mailTo)) {
                $user = Auth::user();
                if ($user) {
                    $mailTo = $user->email ?? null;
                } else {
                    $mailTo = $shipping_address->email ?? null;
                    if (!$mailTo && $order->parent_id) {
                        $parent = Order::find($order->parent_id);
                        if ($parent) {
                            $parentAddr = UserAddress::find($parent->shipping_address_id);
                            $mailTo = $parentAddr->email ?? null;
                        }
                    }
                }
            }

            if (!$mailTo) return false; // Cannot send without email

            $to['email'] = $mailTo;
            $to['name']  = ucfirst($custName);

            $cc['email'] = $reciveAdminEmail;

            $typeLabel = $isQuotation ? 'Quotation' : 'Order';
            $subject   = $app_name . '-Customized ' . $typeLabel . ' #' . $order->id;

            $isPaymentPaid = $paymentLinkInfo && $paymentLinkInfo->status === "paid" ? true : false;
            $customOrder = $order; // For clarity in view
            
            if ($paymentLink) {
                $subject = $app_name . ' - Payment Due for Order #' . $order->id;
                
                 $message = \View::make($view, [
                    'order'            => $order,
                    'customOrder'      => $order,
                    'paymentLink'      => $paymentLink,
                    'customerName'     => $custName,
                    'currency'         => $order->currency_code ?? 'KWD',
                    'appName'          => $app_name,
                    'customerCareEmail'=> $customerCareEmail,
                    'language'         => $language, // Pass language if needed
                    'admin_mail'       => $admin_mail // Pass admin_mail if needed
                ])->render();
            } else {
                 // Pass customDetails so the view can render items/customer info
                $message = \View::make($view, compact('shipping_address', 'order', 'customOrder', 'language', 'admin_mail', 'customerCareEmail', 'customDetails', 'paymentLink', 'isPaymentPaid'))->render();
            }

            $phpMail      = new \App\Mail\CommonMailSender();
            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);

            if (isset($mailResponse['status']) && $mailResponse['status']) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            // swallow and return false to avoid breaking order flow
            return false;
        }
    }

    public function sendMail($order, $paymentLink = null)
    {
        try {
            $shipping_address = UserAddress::find($order->shipping_address_id);
            $adminOrderStatus = AdminOrderStatus::find($order->order_status);
            $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";

            $reciveEmail       = Helper::getSettingById(18);
            $reciveAdminEmail  = "";

            if ($shipping_address->pickupstore == 0) {
                $mailTo            = $shipping_address->email;
            } else {
                $user = Auth::user();
                $mailTo            = $user->email ?? null;
            }
            $app_name          = Helper::getAppName();
            $customerCareEmail = Helper::getCustomerCareEmail();

            $from['email'] = $admin_mail;
            $from['name']  = $app_name;
            $to['email']   = $mailTo;
            // $to['email']   = "bhoomiqf@gmail.com";
            $to['name']    = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

            $cc['email'] = $reciveAdminEmail;

            $subject      = $app_name . "-New Order #" . $order->id;
            $message      = \View::make('web.emails.thank_you.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail', 'customerCareEmail', 'paymentLink'))->render();
            //        echo $message;die;
            $phpMail      = new \App\Mail\CommonMailSender();

            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
            if ($mailResponse['status']) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function test_order($id)
    {
        $order   = Order::where(['id' => $id])->first();
        $shipping_address = UserAddress::find($order->shipping_address_id);
        $adminOrderStatus = AdminOrderStatus::find($order->order_status);
        $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
        $language         = Helper::getLanguage();
        $contactEmail     = Helper::getSettingById(19);
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";

        $reciveEmail       = Helper::getSettingById(18);
        $reciveAdminEmail  = $reciveEmail ? $reciveEmail->setting_val : "";

        $mailTo            = $shipping_address->email;
        $app_name          = Helper::getAppName();
        $customerCareEmail = Helper::getCustomerCareEmail();

        $from['email'] = $admin_mail;

        $from['name']  = $app_name;
        $to['email']   = 'mp.miteshpancha@gmail.com';
        $to['name']    = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

        $cc['email'] = '';

        $subject      = $app_name . "-New Order #" . $order->id;
        $message      = \View::make('web.emails.thank_you.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail', 'customerCareEmail'))->render();
        //        echo $message;die;
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return 'mail sent';
        } else {
            return false;
        }
    }

    public function payment_success_mail($order)
    {
        $shipping_address = UserAddress::find($order->shipping_address_id);
        $adminOrderStatus = AdminOrderStatus::find($order->order_status);
        $orderDetail      = OrderDetail::where('order_id', $order->id)->get();
        $language         = Helper::getLanguage();
        $contactEmail     = Helper::getSettingById(19);
        $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";


        $reciveEmail      = Helper::getSettingById(18);
        $reciveAdminEmail = "";
        $mailTo           = $shipping_address->email;
        $app_name         = Helper::getAppName();
        $from['email']    = $admin_mail;
        $from['name']     = $app_name;
        $to['email']      = $mailTo;
        // $to['email'] = "bhoomiqf@gmail.com";
        $to['name']       = ucfirst($shipping_address->first_name) . '' . ucfirst($shipping_address->last_name);

        $cc['email'] = $reciveAdminEmail;

        $subject      = $app_name . "-New Order";
        $message      = \View::make('web.emails.payment_success.index', compact('shipping_address', 'order', 'orderDetail', 'adminOrderStatus', 'language', 'admin_mail'))->render();
        $phpMail      = new \App\Mail\CommonMailSender();
        $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);
        if ($mailResponse['status']) {
            return true;
        } else {
            return false;
        }
    }

    public function sendPaymentLinkEmail($order, $paymentLink, $paymentRequestId = null)
    {
        try {
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
            $reciveEmail      = Helper::getSettingById(18);
            $reciveAdminEmail = "";

            $customerCareEmail = Helper::getCustomerCareEmail();
            $app_name          = Helper::getAppName();

            $from['email'] = $admin_mail;
            $from['name']  = $app_name;

            $paymentLinkModel = null;
            if ($paymentRequestId) {
                $paymentLinkModel = PaymentLink::where('payment_request_id', $paymentRequestId)->first();
            }

            if ($paymentLinkModel && empty($paymentLinkModel->customer_email)) {
                return false;
            }

            // Determine if we have sub-orders or if this is the only order
            $regularOrder = null;
            $customOrder  = null;
            $mailTo       = null;
            $custName     = 'Customer';

            // If parent order (regular/main)
            if ($order->order_type !== 'custom') {
                $regularOrder = $order;
                // Check for child custom order
                $child = Order::where('parent_id', $order->id)->where('order_type', 'custom')->first();
                if ($child) {
                    $customOrder = $child;
                }
            } else {
                // It is a custom order (Standalone)
                $customOrder = $order;
            }

            // Determine Recipient Email & Name
            
            // 1. Try Custom Order Customer Details
            if ($customOrder) {
                $customDetails = json_decode($customOrder->custom_order_details, true);
                if (isset($customDetails['customer']['email']) && !empty($customDetails['customer']['email'])) {
                    $mailTo = $customDetails['customer']['email'];
                    $custName = $customDetails['customer']['name'] ?? $custName;
                }
            }

            // 2. Try Regular Order Shipping Address
            if (!$mailTo && $regularOrder) {
                $address = UserAddress::find($regularOrder->shipping_address_id);
                if ($address) {
                    $mailTo = $address->email;
                    $custName = $address->first_name . ' ' . $address->last_name;
                }
            }
            
            // 3. Try User Account (only if payment link email is not tracked)
            if (!$mailTo && !$paymentLinkModel) {
                 $user = Auth::user();
                 if ($user) $mailTo = $user->email ?? null;
            }

            // 4. Try Parent Order Shipping (if pure custom sub-order passed, though unlikely in this flow)
            if (!$mailTo && $order->parent_id) {
                 $parent = Order::find($order->parent_id);
                 if ($parent) {
                      $address = UserAddress::find($parent->shipping_address_id);
                      if ($address) $mailTo = $address->email;
                 }
            }

            if (!$mailTo) return false;

            $to['email'] = $mailTo;
            // $to['email'] = "bhoomiqf@gmail.com";
            $to['name']  = ucfirst($custName);
            $cc['email'] = $reciveAdminEmail;

            $subject = $app_name . ' - Payment Due for Order #' . $order->id;
            
            $view = 'web.emails.payment_due_link';

            $message = \View::make($view, [
                'order'            => $order, 
                'regularOrder'     => $regularOrder,
                'customOrder'      => $customOrder,
                'paymentLink'      => $paymentLink,
                'customerName'     => $custName,
                'currency'         => $order->currency_code ?? 'KWD',
                'appName'          => $app_name,
                'customerCareEmail'=> $customerCareEmail,
            ])->render();

            $phpMail      = new \App\Mail\CommonMailSender();
            $mailResponse = $phpMail->sendMail($from, $to, $subject, $message, false, $cc);

            $sent = isset($mailResponse['status']) && $mailResponse['status'];
            if ($sent) {
                $token = $paymentRequestId;
                if (!$token && is_string($paymentLink)) {
                    if (preg_match('~/payment-review/([^/?#]+)~', $paymentLink, $m)) {
                        $token = $m[1];
                    }
                }

                if ($token) {
                    $paymentLinkModel = $paymentLinkModel ?: PaymentLink::where('payment_request_id', $token)->first();
                    if ($paymentLinkModel) {
                        $paymentLinkModel->email_sent_count = (int) $paymentLinkModel->email_sent_count + 1;
                        $paymentLinkModel->last_email_sent_at = now();
                        $paymentLinkModel->save();
                    }
                }
            }

            return $sent;

        } catch (Exception $e) {
            return false;
        }
    }
    
    public function sendPaymentRejectEmail($order, $paymentLink = null, $paymentResponse = null)
    {
        try {
            $language         = Helper::getLanguage();
            $contactEmail     = Helper::getSettingById(19);
            $admin_mail       = $contactEmail ? $contactEmail->setting_val : "";
            
            $customerCareEmail = Helper::getCustomerCareEmail();
            $app_name          = Helper::getAppName();

            $from['email'] = $admin_mail;
            $from['name']  = $app_name;

            // Recipients
            $to = [];
            
            // 1. Generated Link User (Staff/Admin who created the link)
            if ($paymentLink && $paymentLink->generated_by) {
                $generatedByUser = \App\User::find($paymentLink->generated_by);
                if ($generatedByUser && $generatedByUser->email) {
                    $to[] = ['email' => $generatedByUser->email, 'name' => $generatedByUser->name];
                }
            }
            
            // 2. Info Email
            // $to[] = ['email' => 'info@reehana.com', 'name' => 'Reehana Info'];
            // $to[] = ['email' => 'bhoomiqf@gmail.com', 'name' => 'Bhoomi'];

            $shipping_address = null;
            // Prepare Data
            if($order->order_type == 'custom') {
                $customDetails = json_decode($order->custom_order_details, true);
                $customerInfo = isset($customDetails['customer']) ? $customDetails['customer'] : null;
                if($customerInfo) {
                    $cust = $customerInfo;
                    // Country name lookup
                    $countryName = '';
                    if (isset($cust['country_id']) && $cust['country_id']) {
                        $cn = \App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                        if ($cn) $countryName = $cn->country_name;
                    }
                }

                $shipping_address = (object) [
                    'first_name' => $customerInfo['name'] ?? 'Customer',
                    'last_name' => '',
                    'email' => $customerInfo['email'] ?? '',
                    'phone' => $customerInfo['phone'] ?? '',
                    'block'=> $cust['block'] ?? '',
                    'floor' => $cust['floor'] ?? '',
                    'house_number' => $cust['house_number'] ?? '',
                    'city' => $cust['city'] ?? '',
                    'country_name' => $countryName,
                    'address_detail' => $cust['address_detail'] ?? ''
                ];
            } else {
                $shipping_address = UserAddress::find($order->shipping_address_id);
            }
            
            $orderDetail = OrderDetail::where('order_id', $order->id)->get();
            
            $subject = "Payment Rejected - Order #" . $order->id;
            
            $view = 'web.emails.payment_reject';
            
            $message = \View::make($view, compact(
                'order', 
                'paymentLink', 
                'paymentResponse', 
                'shipping_address', 
                'orderDetail', 
                'language', 
                'customerCareEmail'
            ))->render();
            
            $phpMail = new \App\Mail\CommonMailSender();
             
             // Send to recipients
             if (!empty($to)) {
                 $phpMail->sendMail($from, $to, $subject, $message, true);
             }
             
             return true;

        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function getUpaymentsLink($orderId, $final_total, $user, $address, $products, $customizedAdvance = 0, $paymentMethod = 1, $isPendingPaymentLinkRequested = false)
    {
        $successurl = route('web.checkout.payment_success');
        $failururl  = route('web.checkout.payment_error');

        $payment_type = config('custom_web.payment_type');

        $payment_type = $payment_type[$paymentMethod];

        $order = Order::find($orderId);

        // Ensure we have correct address object
        if (!$address && $user) {
             $address = $this->resolveSelectedShippingAddress($user);
             // Check pickup
             $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
             if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                $address = $hasPickupStoreAddressSelected;
             }
        }
        
        $hasPickupStoreAddressSelected = Helper::getPickupStoreAddress();
        $paymentRequestId = uniqid('pay_', true);

        if ($order && $order->order_type === 'custom') {
            $customOrder = [];
            if (!empty($order->custom_order_details)) {
                $customOrder = json_decode($order->custom_order_details, true);
            }
            $customCustomer = $customOrder['customer'] ?? null;

            if ($hasPickupStoreAddressSelected && $hasPickupStoreAddressSelected->pickupstore == 1) {
                $address = $hasPickupStoreAddressSelected;
            }

            // Customer Name fallback
            $fallbackName = 'Customer';
            if ($address && (!empty($address->first_name) || !empty($address->last_name))) {
                $fallbackName = trim($address->first_name . ' ' . $address->last_name);
            } elseif ($user && !empty($user->name)) {
                $fallbackName = $user->name;
            }

            $customerName = is_array($customCustomer) && !empty($customCustomer['name']) ? $customCustomer['name'] : $fallbackName;

            // Customer Email fallback
            $fallbackEmail = '';
            if ($address && !empty($address->email)) {
                $fallbackEmail = $address->email;
            } elseif ($user && !empty($user->email)) {
                $fallbackEmail = $user->email;
            }

            $customerEmailForLink = is_array($customCustomer) && !empty($customCustomer['email'])
                ? $customCustomer['email']
                : $fallbackEmail;

            // Customer Mobile fallback
            $customerMobile = is_array($customCustomer) && !empty($customCustomer['phone'])
                ? $customCustomer['phone']
                : ($address->phone ?? ($user->phone ?? ''));

            $isAdvanceDone = PaymentCalculationService::isAdvancePaymentDone($order);
            if (!$isAdvanceDone) {
                // STAGE 1: Advance payment not completed. Charge amount is strictly the advance amount.
                $chargeAmount = (float) $customizedAdvance;
                if ($chargeAmount <= 0 && (float) $final_total > 0) {
                    $chargeAmount = (float) $final_total;
                }
            } else {
                // STAGE 2: Advance completed. Charge amount is the final total (authoritative pending balance).
                $chargeAmount = (float) $final_total;
            }

            if ($chargeAmount <= 0 && (float) $order->order_total > 0 && !$isAdvanceDone) {
                $chargeAmount = (float) $order->paid_amount > 0 ? (float) $order->paid_amount : (float) $order->order_total;
            }

            $post_data = array(
                "products" => $products,
                "order" => array(
                    "id" => strval($orderId),
                    "reference" => strval($paymentRequestId),
                    "description" => "",
                    "currency" => $order->currency_code ?? "KWD",
                    "amount" => $chargeAmount
                ),
                "paymentGateway" => array(
                    "src" => $payment_type ?? "knet"
                ),
                "language" => "en",
                "reference" => array(
                    "id" => strval($paymentRequestId)
                ),
                "customer" => array(
                    'uniqueId' => '',
                    'name' => $customerName ?: 'Customer',
                    'email' => $customerEmailForLink ?? '',
                    'mobile' => $customerMobile
                ),
                'returnUrl' => $successurl,
                'cancelUrl' => $failururl,
                "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
                "customerExtraData" => json_encode([
                    "payment_request_id" => $paymentRequestId
                ]),
                "total_price" => $chargeAmount
            );

        } else {
            $customerName = 'Customer';
            $customerEmail = '';
            $customerMobile = '';
            if ($address) {
                $customerName = trim($address->first_name . ' ' . $address->last_name) ?: 'Customer';
                $customerEmail = $address->email;
                $customerMobile = $address->phone ?? '';
            } elseif ($user) {
                $customerName = $user->name ?: 'Customer';
                $customerEmail = $user->email;
                $customerMobile = $user->phone ?? '';
            }

            $chargeAmount = (float) $final_total;
            if ($chargeAmount <= 0 && $order && (float) $order->paid_amount > 0) {
                $chargeAmount = (float) $order->paid_amount;
            } elseif ($chargeAmount <= 0 && $order && (float) $order->order_total > 0) {
                $chargeAmount = (float) $order->order_total;
            }

            $post_data = array(
                "products" => $products,
                "order" => array(
                    "id" => strval($orderId),
                    "reference" => strval($paymentRequestId),
                    "description" => "",
                    "currency" => $order->currency_code ?? "KWD",
                    "amount" => $chargeAmount
                ),
                "paymentGateway" => array(
                    "src" => $payment_type ?? "knet"
                ),
                "language" => "en",
                "reference" => array(
                    "id" => strval($paymentRequestId)
                ),
                "customer" => array(
                    'uniqueId' => '',
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'mobile' => $customerMobile
                ),
                'returnUrl' => $successurl,
                'cancelUrl' => $failururl,
                "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
                "customerExtraData" => json_encode([
                    "payment_request_id" => $paymentRequestId
                ]),
                "total_price" => $chargeAmount,
                "advance_price" => (float) $customizedAdvance
            );
        }

        $calcSummary = $order ? PaymentCalculationService::calculateOrderPayments($order) : null;
        $totalFormatted = number_format((float) ($order->order_total ?? $chargeAmount), 3, '.', '');
        $totalPaidFormatted = number_format((float) ($calcSummary['total_paid'] ?? 0), 3, '.', '');
        $pendingFormatted = number_format((float) ($calcSummary['pending_amount'] ?? $chargeAmount), 3, '.', '');
        $chargeAmountFormatted = number_format((float) $chargeAmount, 3, '.', '');
        $isPendingLinkStage = ($order && PaymentCalculationService::isAdvancePaymentDone($order)) || $isPendingPaymentLinkRequested;
        $paymentTypeLabel = $isPendingLinkStage ? 'pending' : 'advance';

        \Log::info("[KNET REQUEST] order_id={$orderId} | payment_link_id={$paymentRequestId} | total={$totalFormatted} | total_paid={$totalPaidFormatted} | pending={$pendingFormatted} | payment_type={$paymentTypeLabel} | payment_link_amount={$chargeAmountFormatted} | knet_request_amount={$chargeAmountFormatted}");

        $basURL     = env('PAYMENT_URL', '');
        $apiKey     = env('PAYMENT_API_KEY', '');
        $response = false;
        $httpCode = 0;
        $curlError = '';
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $basURL . 'charge',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($post_data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $apiKey
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            $shouldRetry = in_array($httpCode, [502, 503, 504], true) || $httpCode === 0;
            if (!$shouldRetry || $attempt === $maxAttempts) {
                break;
            }

            \Log::warning('UPayments charge transient failure, retrying', [
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'http_code' => $httpCode,
                'curl_error' => $curlError,
            ]);

            usleep($attempt * 500000); // 0.5s, 1.0s
        }

        $res = json_decode($response);

        if ($httpCode >= 400 || $response === false) {
            $fallbackMessage = $curlError ?: 'Payment service is temporarily unavailable. Please try again.';

            return [
                'status' => false,
                'message' => (is_object($res) && isset($res->errors)) ? (Helper::getErrorFromUpayments($res) ?: $fallbackMessage) : $fallbackMessage,
            ];
        }
        if ($res && isset($res->status) && $res->status) {
            $gatewayResponse = [];
            $splitPayment = $this->buildSplitPaymentMeta($order, $chargeAmount, $customizedAdvance);
            if ($splitPayment) {
                $gatewayResponse['split_payment'] = $splitPayment;
            }

            $isAdvancePaymentFlag = ($isPendingLinkStage) ? 0 : ((float) $customizedAdvance > 0 ? 1 : 0);

            PaymentLink::create([
                'order_id'       => $orderId,
                'payment_link'   => $res->data->link,
                'payment_request_id'=> $paymentRequestId,
                'amount'         => (float) $chargeAmount,
                'generated_from' => 'system',
                'generated_by'   => auth()->id(),
                'status'         => 'pending',
                'gateway_response' => $gatewayResponse,
                'payment_type'   => $paymentMethod,
                'is_advance_payment' => $isAdvancePaymentFlag,
                'customer_email' => $customerEmailForLink ?? '',
                'email_sent_count' => 0,
                'last_email_sent_at' => null
            ]);
            return ['status' => true, 'link' => $res->data->link, 'payment_request_id' => $paymentRequestId];
        }
        
        $errorMessage = isset($res->message) ? $res->message : \Lang::get($this->langFile . '.payment-error');
        if (isset($res->error)) {
             $errorMessage = $res->error;
        }

        return ['status' => false, 'message' => $errorMessage];
    }

    private function resolveSelectedShippingAddress($user)
    {
        if (!$user) {
            return null;
        }

        $selectedAddressId = session('default_address');

        if ($selectedAddressId) {
            $selectedAddress = UserAddress::where('id', $selectedAddressId)
                ->where('is_active', 1)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('pickupstore', 1);
                })
                ->first();

            if ($selectedAddress) {
                return $selectedAddress;
            }
        }

        return UserAddress::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('is_default', 1)
            ->first();
    }

    private function resolveSelectedShippingAddressStrict($user)
    {
        if (!$user) {
            return null;
        }

        if (!session('address_selected')) {
            return null;
        }

        $selectedAddressId = session('default_address');
        if (!$selectedAddressId) {
            return null;
        }

        return UserAddress::where('id', $selectedAddressId)
            ->where('is_active', 1)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('pickupstore', 1);
            })
            ->first();
    }

    private function isPhysicalStoreUser($user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->id == config('config.store_admin_id')
            || (isset($user->user_type) && $user->user_type == "physical_store_user");
    }

    private function shouldUseStrictAddressSelectionForCustomOrder($user): bool
    {
        return $this->isPhysicalStoreUser($user) && session()->has('customized_order_details');
    }

    private function syncCurrencyByAddress($address): void
    {
        if (!$address || empty($address->country_id)) {
            return;
        }

        $country = AdminCountry::where('id', $address->country_id)->first();
        if ($country) {
            Session::put('currency', $country->id);
        }
    }
    private function buildWebUrl(string $path): string
    {
        $baseUrl = rtrim(config('custom.web_url', config('app.url')), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function regeneratePaymentLink($oldPaymentLink, $order)
    {
        \Log::info('regeneratePaymentLink called for order: ' . $order->id);
        
        $merchant   = env('PAYMENT_MERCHANT', '');
        $basURL     = env('PAYMENT_URL', '');
        $apiKey     = env('PAYMENT_API_KEY', '');
        $websiteURL = URL::to('/');

        $successurl = $this->buildWebUrl(route('web.checkout.payment_success', [], false));
        $failururl  = $this->buildWebUrl(route('web.checkout.payment_error', [], false));

        $customOrder = null;
        $products = [];

        if (!empty($order->custom_order_details)) {
            $customOrder = json_decode($order->custom_order_details, true);
        }
        
        $customCustomer = is_array($customOrder['customer'] ?? null) ? $customOrder['customer'] : null;
        
        $customerName = !empty($customCustomer['name'])
            ? $customCustomer['name']
            : 'Customer';

        $customerEmail = !empty($customCustomer['email'])
            ? $customCustomer['email']
            : null;

        if (!empty($customOrder['items']) && is_array($customOrder['items'])) {
            foreach ($customOrder['items'] as $item) {
                $title = !empty($item['product_title'])
                    ? $item['product_title']
                    : (!empty($item['description'])
                        ? $item['description']
                        : (!empty($item['item_code'])
                            ? $item['item_code']
                            : 'Customized Order'));

                $products[] = [
                    'name'        => $title,
                    'description' => $title,
                    'quantity'   => isset($item['quantity']) && $item['quantity'] > 0
                        ? (int) $item['quantity']
                        : 1,
                    'price'      => Helper::formatNumber(
                        isset($item['price']) ? $item['price'] : 0
                    ),
                ];
            }
        }

        // Generate new unique reference for payment gateway (but keep same payment_request_id for our system)
        $gatewayReference = uniqid('pay_gw_', true);
        $linkAmount = $oldPaymentLink->amount;
        
        \Log::info('Generated new gateway reference: ' . $gatewayReference);

        $post_data = array(
            "products" => $products,
            "order" => array(
                "id" => strval($order->id),
                "reference" => strval($gatewayReference), // New unique reference for gateway
                "description" => "",
                "currency" => $order->currency_code ?? config('custom.currency'),
                "amount" => $linkAmount
            ),
            "paymentGateway" => array(
                "src" => "knet"
            ),
            "language" => "en",
            "reference" => array(
                "id" => strval($gatewayReference) // New unique reference for gateway
            ),
            "customer" => array(
                'uniqueId' => '',
                'name' => $customerName,
                'email' => $customerEmail,
                'mobile' => ''
            ),
            'returnUrl' => $successurl,
            'cancelUrl' => $failururl,
            "notificationUrl" => "https://webhook.site/d7c6e1c8-b98b-4f77-8b51-b487540df336",
            "customerExtraData" => json_encode([
                "payment_request_id" => $oldPaymentLink->payment_request_id // Keep our original token
            ]),
            "total_price" => $linkAmount
        );
        
        \Log::info('Calling payment gateway with data: ' . json_encode($post_data));
                
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $basURL . 'charge',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        \Log::info('Payment gateway response: ' . $response);

        $res = json_decode($response);

        if ($res && isset($res->status) && $res->status) {
            $red_url = $res->data->link;

            // Update only the payment_link URL, keep the same payment_request_id
            $oldPaymentLink->payment_link = $red_url;
            $oldPaymentLink->save();
            
            \Log::info('Payment link updated successfully in database');

            return $oldPaymentLink->fresh();
        }
        
        \Log::error('Payment gateway returned error or invalid response');
        return null;
    }

    public function paymentReview($token)
    {
        $orderProductDetail = [];
        $orderCustom = null;
        $paymentLink = PaymentLink::where('payment_request_id', $token)->first();
        $linkedOrder = $paymentLink ? Order::withTrashed()->find($paymentLink->order_id) : null;
        $isCustomizedOrder = $linkedOrder && $linkedOrder->custom_order_type;

        if (!$paymentLink || $paymentLink->status != 'pending') {
            $message = 'This payment link has expired or is no longer valid.';
            if ($paymentLink && $paymentLink->status === 'rejected') {
                $message = 'This payment link was rejected and is no longer available.';
            } elseif ($paymentLink && $paymentLink->status === 'failed') {
                $message = 'This payment link failed and cannot be used again.';
            }

            if ($isCustomizedOrder) {
                return redirect()->route('web.index')->with('checkout_error', $message);
            }

            return redirect()->route('web.index')->with('error', $message);
        }

        $order = $linkedOrder ?: Order::findOrFail($paymentLink->order_id);
        
        // Always regenerate payment link to avoid duplicate merchant track ID errors
        try {
            \Log::info('Attempting to regenerate payment link for token: ' . $token);
            $newPaymentLink = $this->regeneratePaymentLink($paymentLink, $order);
            if ($newPaymentLink) {
                $paymentLink = $newPaymentLink;
                \Log::info('Payment link regenerated successfully. New link: ' . $paymentLink->payment_link);
            } else {
                \Log::warning('Payment link regeneration returned null');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate payment link: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }

        if ($order) {
            $address = UserAddress::where('id', $order->shipping_address_id)->withTrashed()->first();
            if ($order->custom_order_type) {
                if($order->custom_order_type == 'purchase_order') {
                    $orderProductDetail = OrderDetail::where('order_id', $order->id)->get();
                    return view('web.checkout.payment_review_customized', compact('order', 'address', 'orderProductDetail', 'paymentLink'));
                }
            }
            else {
                $orderCustom = Order::where('parent_id', $order->id)->withTrashed()->first();
                if($orderCustom) {
                    $orderProductDetail = OrderDetail::where('order_id', $orderCustom->id)->get();
                }
            }
            return view('web.checkout.payment_review_page', compact('order', 'address', 'orderProductDetail', 'orderCustom', 'paymentLink'));
        } else {
            if ($isCustomizedOrder) {
                return redirect()->route('web.index')->with('checkout_error', 'We could not find your customized order. Please try again or contact support.');
            }
            return redirect()->route('web.index')->with('error', Lang::get($this->langFile . '.order-not-found'));
        }
    }

    public function paymentReject($token)
    {
        $paymentLink = PaymentLink::where('payment_request_id', $token)->first();
        $linkedOrder = $paymentLink ? Order::withTrashed()->find($paymentLink->order_id) : null;
        $isCustomizedOrder = $linkedOrder && $linkedOrder->custom_order_type;

        if (!$paymentLink || $paymentLink->status != 'pending') {
            $message = 'This payment link is no longer valid.';
            if ($isCustomizedOrder) {
                return redirect()->route('web.index')->with('checkout_error', $message);
            }
            return redirect()->route('web.index')->with('error', $message);
        }

        PaymentLink::where('payment_request_id', $token)->update([
            'status' => 'rejected',
            'updated_by' => auth()->check() ? auth()->id() : null
        ]);
        
        $order = Order::find($paymentLink->order_id);
        if($order) {
            $order->update([
                'order_status' => config('custom_web.cancel', 5) 
            ]);
        }


        if ($order) {
            $this->sendPaymentRejectEmail($order, $paymentLink, null);
        }

        if ($isCustomizedOrder) {
            return redirect()->route('web.index')->with('checkout_error', 'You rejected the customized order payment.');
        }

        return redirect()->route('web.index')->with('success', 'You have rejected the order.');
    }
}

@extends('web/layout/main')
@section('page_title', trans('web.cart'))
@section('body_class', 'configMonogram')
@section('content')
<style>
    .main-info .product-actions a {
        width: auto;
        margin: 0 15px 0 0;
        font-size: 14px;
        text-transform: uppercase;
    }

    .icon-btn {
        background: transparent;
        border: none;
        padding: 4px 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .icon-btn i {
        font-size: 16px;
    }
</style>
<?php
//session()->put('cart', []);
$shippingCharge = 0;
$cart = [];
$size = '';
$total = 0;
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$user = Auth::user();
if ($user) {
    $cart = Helper::getShoppingCart($user->id);
} elseif (session('cart')) {
    $cart = Helper::getShoppingCart();
}

$tax = Helper::getTax();
$tax = $tax ? $tax : 0;
$taxValue = ($total * $tax) / 100;

$discount = config('custom_web.discount', 20);
$countryData = Helper::getDefaultCountry();



if ($countryData) {

    $shippingCharges = $countryData->shipping_charge;

    /*
     *  Convert Currency Rate
     */
    if ($currency) {
        $shippingCharges = $shippingCharges * $currency->value;
    }
}

if ($user) {

    $address = App\UserModel\UserAddress::where('is_default', 1)->where('user_id', $user->id)->first();


    if ($address) {

        if ($address->id != '7') {

            $zoneprice = Helper::getZonePrice($address->city_id);
            if ($zoneprice > 0) {


                $shippingCharges = $zoneprice * $currency->value;
            }
        } else {
            $shippingCharges = 0;
        }
    }
}
$existingCust = session('customized_order_details');
?>
@section('additional_css')
<style>
    .cart .col-md-8,
    .cart-checkout .col-md-8,
    .cart-review .col-md-8 {
        @if(count($cart)==0) border-right:0px;
        @endif
    }
</style>
@endsection
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.cart.index')}}">{{__('web.my_cart')}}</a></span>
    </div>
</section>
<section class="cart">
    <div class="container">
        <div class="checkout-workflow">
            <ul>
                <li class="active">
                    <span>1</span>
                    <label>{{__('web.cart')}}</label>
                </li>
                <li class="">
                    <span>2</span>
                    <label>{{__('web.shipping_address')}}</label>
                </li>
                <li class="">
                    <span>3</span>
                    <label>{{__('web.review_pay')}}</label>
                </li>
            </ul>
        </div>
        <div class="h-flex flex-nowrap">
            <div class="cart-items-summary">
                <div class="cart-products">
                    <div class="cart-list">
                        <?php
                        $total_weight = 0;
                        $i = 0;
                        ?>
                        @foreach($cart as $id =>$record)
                        <?php
                        $product_weight = '';


                        $sizeid = 's';
                        if ($record['size_id']) {
                            $sizeid = $record['size_id'];
                        }
                        $colorid = 'c';
                        if ($record['color_id']) {
                            $colorid = $record['color_id'];
                        }

                        $record['monogram'] = $record;


                        $id = $record['product_id'] . "_" . $sizeid . "_" . $colorid;

                        $cartQuantity = $record['quantity'];
                        $product = Helper::getProductDetail($record['product_id']);
                        if (!$product) {
                            continue;
                        }
                        $has_monogram = Helper::isMonogranm($product->id);


                        $product_weight = $product->product_weight;
                        $productdetail = $product->getProductDescription($language->id);
                        $productimage = $product->getDefaultImage();


                        $sizeId = $record['size_id'];
                        $color_id = $record['color_id'];

                        $size = Helper::getSize($sizeId, $language->id);
                        $color = Helper::getColorName($color_id, $language->id);

                        $productSize = false;
                        $colorExist = false;

                        if ($color) {
                            $colorExist = $color->color_id;
                        }


                        if ($size) {
                            $productSize = $size->size_id;
                        }
                        //$productQuantity = $product->getProductQuantity($product->id);
                        $productQuantity = Helper::getProductQuantity($product->id, $productSize ? 1 : 0, $sizeId, $colorExist ? 1 : 0, $color_id);

                        $product_sale_price =  $product->product_sale_price;

                        $product_regular_price = $product->product_regular_price;

                        $is_product_sale_price_null  = $product->is_product_sale_price_null;


                        $price = $is_product_sale_price_null == 0 ? $product_sale_price  : $product_regular_price;


                        $style = '';
                        if ($color || $size) {
                            $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);
                            if ($attributePrice) {
                                $style = $attributePrice->style;
                                $product_weight = $attributePrice->weight;
                                $product_regular_price = $attributePrice->product_regular_price;
                                $product_sale_price = $attributePrice->product_sale_price;
                                $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                            }
                        }


                        $price = $is_product_sale_price_null == 0 ? $product_sale_price : $product_regular_price;


                        //monogram
                        $cartproduct = Helper::getCartProduct($user, $product->id, $sizeId ? 1 : 0, $sizeId ? $sizeId : 's', $color_id ? 1 : 0, $color_id ? $color_id : 'c');
                        //                      dd($cartproduct);
                        $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                        $font = [];
                        $initials = [];
                        $colors = [];
                        $placement = [];
                        $is_monogram = false;

                        if (count($cartproduct) > 0) {
                            if ($user) {
                                if ($cartproduct['is_monogram'] == 1) {
                                    $cartproduct['monogram'] = $cartproduct;
                                } else {
                                    $cartproduct['monogram'] = [];
                                }
                            }


                            if (count($cartproduct['monogram']) > 0 && $cartproduct['is_monogram'] == 1) {
                                $is_monogram = true;
                                //                                $font        = \App\Helpers\Helper::getMonogramStyle($cartproduct['monogram']['font']);
                                //                                $initials    = \App\Helpers\Helper::getInitials($cartproduct['monogram']['initials_count'], $cartproduct['monogram']['left_text'], $cartproduct['monogram']['center_text'], $cartproduct['monogram']['right_text'], $cartproduct['monogram']['text4']);
                                //                                $colors      = \App\Helpers\Helper::getMonogramColor($cartproduct['monogram']['monogram_color_id']);
                                //                                $placement   = \App\Helpers\Helper::getPlacement($cartproduct['monogram']['placement']);
                                //                                $placement   = \App\Helpers\Helper::getPlacement($cartproduct['monogram']['placement']);
                            }
                        }
                        //                        if ($is_monogram)
                        //                        {
                        //                            $price = $price + $monogramPrice;
                        //                        }
                        // monogram end
                        $subTotal = $price * $cartQuantity;
                        /*
                         *  Convert Currency Rate
                         */
                        if ($currency) {
                            //   $subTotal = $subTotal * $currency->value;
                        }
                        $total += $subTotal;
                        ?>
                        @if($i ==0)
                        <!--<input type="hidden" class="addMonogramButton" data-productId="{{$product->id}}">-->
                        @endif
                        <div class="cart-item main-info cart_{{$id}}">
                            <div class="h-flex">
                                <div class="prod-image">

                                    <a href="{{route('web.product.product_detail',['slug'=> $product->products_slug])}}">

                                        <?php
                                        $productImage2 = '';

                                        if ($record['color_id'] && !$record['size_id']) {
                                            $productImage2 = $product->getColorImage($record['color_id']);
                                        } else if (!$record['color_id'] && $record['size_id']) {
                                            $productImage2 = $product->getSizeImage($record['size_id']);
                                        } else if ($record['color_id'] && $record['size_id']) {
                                            $productImage2 = $product->getColorSizeImage($record['color_id'], $record['size_id']);
                                        }

                                        if (!$productImage2) {
                                            $productImage2 = $productimage->image_url;
                                        } else {
                                            $productImage2 = $productImage2->image_url;
                                        }
                                        ?>
                                        <img class="image_{{$id}}" src="{{asset(Storage::url($productImage2))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">

                                    </a>
                                    <div class="product-actions sm-hidden">
                                        <button class="btn btn-white favorite move_to_wishlist" data-href="{{route('web.wishlist.move_to_wishlist',['id'=> $product->id])}}"><i class="icon-wishlist"></i>{{__('web.move_to_wishlist')}}</button>
                                        <button class="btn btn-white delete_trash" data-page="cart" data-id="{{$id}}" data-href="{{route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])}}"><i class="icon-trash"></i></button>
                                    </div>
                                    <div class="v-flex sm-visible">
                                        <div class="prod-name">
                                            <h3>{{ucfirst($productdetail->products_name)}} {{ $style ? '('.$style.')': ''}} </h3>
                                        </div>
                                        <div class="product-pricing-details">
                                            <span class="prod-price product_price_{{$id}}">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="prod-details pd">
                                    <div class="h-flex sm-hidden">
                                        <div class="prod-name">
                                            <h3>{{ucfirst($productdetail->products_name)}} {{ $style ? '('.$style.')': ''}}</h3>
                                        </div>
                                        <div class="product-pricing-details">
                                            <span class="prod-price product_price_{{$id}}">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                        </div>
                                    </div>
                                    <div class="product-options">
                                        @if($color)
                                        <div class="form-item">
                                            <label>{{__('web.color')}}</label>
                                            <span>{{ucfirst($color->color_title)}}</span>
                                        </div>
                                        @endif
                                        @if($size)
                                        <div class="form-item">
                                            <label>{{__('web.size')}}</label>
                                            <span>{{ucfirst($size->size_title)}}</span>
                                        </div>
                                        @endif
                                        <?php
                                        $min = $cartQuantity;
                                        $max = $productQuantity;
                                        $data_show = 5;
                                        if ($productQuantity < 5) {
                                            $data_show = $productQuantity;
                                        }
                                        $lastkey = 1;

                                        $record['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->toArray();
                                        if (count($record['monogram']) > 0) {
                                            $lastkey = count($record['monogram']);
                                        }
                                        ?>
                                        <div class="form-item">
                                            <label>{{__('web.qty')}}</label>
                                            <div class="counter counter2" data-maximum="{{$max}}" data-show="{{$data_show}}">
                                                <div class="h-flex">
                                                    <button class="decrease decrease_cart" data-maximum="{{$max}}" data-id="{{$id}}" data-product="{{$id}}"><i class="icon-minus"></i></button>
                                                    <input type="text" name="quantity_{{$id}}" data-product="{{$id}}" data-id="{{$id}}" class="qty_add prod-count quantity number-input quantity_{{$id}}" value="{{$min}}">
                                                    <button class="increase increase_cart " data-maximum="{{$max}}" data-id="{{$id}}" data-product="{{$id}}"><i class="icon-plus"></i></button>
                                                </div>
                                                <!--<span class="remains"></span>-->
                                                <!-- <span class="remains show" data-show="10">(Only 5 left)</span> -->

                                            </div>
                                        </div>
                                    </div>
                                    @if($has_monogram)

                                    <div id="mogogram_list" class="monogramSummary monogramSummary-{{$id}}" data-total="{{$lastkey}}">

                                        @foreach($record['monogram'] as $key => $monogramVal)
                                        <?php
                                        $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                        $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                        $colors = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                        $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                        $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                        if ($record['is_same'] == 1) {

                                            $total = $total + ($monogramPrice * $min);
                                        } else {
                                            $total = $total + $monogramPrice;
                                        }
                                        ?>

                                        <div class="monogram-information  monogram-information-{{$id}}  {{count($cartproduct['monogram']) > 0 ? 'show' : 'hide' }} " id="monogramSummary-{{$id}}">
                                            <div class="h-flex">
                                                <div class="h-flex">
                                                    <label>Selected Monogram</label>
                                                    <span class="monogram-price nowrap"> {{$monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''}}</span>
                                                </div>
                                                <span class="link-button edit_monogram2" data-productId="{{$product->id}}" data-id="{{$id}}" data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-color="{{$color_id ? $color_id : 'c'}}" data-size="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{$key}}">Edit</span>
                                                <span class="link-button removeMonogram2" data-productid="{{$product->id}}" data-id="{{$id}}" data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-colorId="{{$color_id ? $color_id : 'c'}}" data-sizeId="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{ $monogramVal['id']}}">Remove</span>
                                            </div>

                                            <input type="hidden" name="product_cart_id_{{$id}}_{{$key}}" value="{{$monogramVal['id']}}">
                                            <div class="border-panel">
                                                <div class="h-flex">
                                                    <div class="monogram-options">
                                                        <label>Style</label>
                                                        <div class="info " id="styleInfo_{{$id}}_{{$key}}"><img src="{{asset(Storage::url($font ? $font->style_image_svg : ''))}}"></div>
                                                    </div>
                                                    <div class="monogram-options">
                                                        <label>Initials</label>
                                                        <div class="info" id="alphabetsInfo_{{$id}}_{{$key}}">{{$initials? $initials : ''}}</div>
                                                    </div>
                                                    <div class="monogram-options">
                                                        <label>Color</label>
                                                        <div class="info" id="colorInfo_{{$id}}_{{$key}}">
                                                            <span class="color-plate" style="background: {{$colors? $colors->color_hash : ''}};"></span>
                                                            {{$colors?$colors->color_name : ''}}
                                                        </div>
                                                    </div>
                                                    <div class="monogram-options">
                                                        <label>Placement</label>
                                                        <div class="info" id="placementInfo_{{$id}}_{{$key}}">{{$placement ?$placement->placement_desc:''}}</div>
                                                    </div>
                                                </div>
                                                <div class="separator underline underline_{{$id}}_{{$key}}  {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}"></div>
                                                <div class="comment monogramComment_{{$id}}_{{$key}} {{ $record['monogram'] ? $monogramVal['monogram_note'] ? 'show' : 'hide' : ''  }}">
                                                    <p>{{ $record['monogram'] ?  $monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:'': '' }} </p>
                                                </div>

                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <?php
                                    $hasmonogram = \App\Helpers\Helper::getMonogram($product->id);
                                    ?>
                                    @if($hasmonogram)
                                    <div class="addMonogramLink  addMonogramLink_{{$id}}" data-page="cart" data-productId="{{$product->id}}" data-id="{{$id}}" data-colorexist="{{$color_id?1:0}}" data-sizeexist="{{$sizeId?1:0}}" data-color="{{$color_id? $color_id : ''}}" data-size="{{$sizeId? $sizeId:''}}">
                                        <button <?php echo (int) $min == count($record['monogram']) || $record['is_same'] == 1 ? 'disabled="disabled"' : '' ?> class="link-button">
                                            <i class="icon-plus"></i>Add a Monogram
                                        </button>
                                    </div>
                                    <div class="applyMonogramStyles applyMonogramStyles_{{$id}} {{count($record['monogram']) ==1 && $min > 1 ? 'show' : 'hide' }}">
                                        <div class="checkbox">
                                            <input type="checkbox" name="applyAll_{{$id}}" data-id="{{$id}}" class="apply_same2" {{ $record['is_same'] == 1 ? 'checked' : ''}}>
                                            <label>Apply same Monogram Styles to all quantities.</label>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                    <div class="addNote-actions">
                                        <span class="link-button addNote" id='addNote-{{$id}}'><i class="icon-plus"></i>{{__('web.add_note')}}</span>
                                        <i class="error"></i>
                                        <div class="note-error form-item text-green custom-error2 note-success-{{$id}}"></div>
                                        <div class="addNoteInput  textarea_{{$id}}">
                                            <textarea name="note_{{$id}}" class="note_{{$id}}" maxlength="{{config('custom.order_note_length')}}">{{$record['note']}}</textarea>
                                            <i class="error"></i>
                                            <div style="width: 100%;" class="note-error form-item text-red custom-error2 note-error-{{$id}}"></div>

                                            <div class="actions">
                                                <span class="btn btn-primary save_note" data-id="{{$id}}">{{__('web.save')}}</span>
                                                <span class="btn btn-secondary close-note" data-id="{{$id}}">{{__('web.Close')}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-actions sm-visible">
                                    <button class="btn btn-white favorite move_to_wishlist" data-href="{{route('web.wishlist.move_to_wishlist',['id'=> $product->id])}}"><i class="icon-wishlist"></i>{{__('web.move_to_wishlist')}}</button>
                                    <button class="btn btn-white delete_trash" data-page="cart" data-id="{{$id}}" data-href="{{route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])}}"><i class="icon-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <?php
                        $total_weight += $product_weight * $min;
                        $i++;
                        ?>

                        @endforeach

                        {{-- Preserve main order total, then add Customized Items to Total --}}
                        <?php
                        $mainTotal = $total; // main order amount (exclude customized)
                        $customOrderDetails = session('customized_order_details');
                        $customAdvance = 0;
                        if ($customOrderDetails && isset($customOrderDetails['payment']['advance']) && is_numeric($customOrderDetails['payment']['advance'])) {
                            $customAdvance = (float) str_replace(',', '', $customOrderDetails['payment']['advance']);
                        }
                        $total = $mainTotal + $customAdvance;
                        ?>
                        @if(count($cart) == 0) <p> {{__('web.shopping_cart_is_empty')}}</p> @endif


                        <?php
                        $roundweight = ceil($total_weight);
                        $countryName = 'kuwait';
                        if (isset($address)) {
                            $countryName = App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                            $countryName = $countryName ? $countryName->country_name : 'kuwait';
                        }

                        if ($roundweight < 1  ||  strtolower($countryName) == 'kuwait') {
                            $roundweight = 1;
                        }

                        $shippingCharge = $shippingCharges * $roundweight;
                        $setting = \App\Helpers\Helper::getSetting();


                        $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                        if (session('gift_wrap')) {
                            $gift_wrap = session('gift_wrap');
                        }
                        ?>
                        @if($setting['31']->setting_val ==1 && count($cart) > 0)

                        <?php
                        $giftAmount = $setting['32']->setting_val;
                        $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
                        if ($giftMinPrice) {
                            $giftMinPrice = $giftMinPrice * $currency->value;
                        }

                        if ($total >= $giftMinPrice) {
                            $giftAmount = 0;
                        }
                        ?>



                        <div class="cart-item main-info gift-wrap-item">
                            <div class="v-flex">
                                <div class="gift-wrap-item-container">
                                    <div class="prod-image">
                                        <a href="#"><img src="{{asset('front/img/giftwrap.png')}}"></a>
                                    </div>
                                    <div class="options v-flex">
                                        <div class="h-flex">
                                            <div class="checkbox">
                                                <input type="checkbox" name="add-monogram" id="gift_wrap" {{$gift_wrap['is_gift'] == 1? 'checked': ''}} value="1">
                                                <label>{{__('web.add_gift_wrap')}}</label>
                                            </div>
                                            <span class="tt">
                                                <i class="fa fa-info-circle" id="tt"></i>
                                                <span class="tt-text" title="{!! $setting['34']->setting_val !!}">{!! $setting['34']->setting_val !!}</span>
                                            </span>
                                        </div>
                                        <div class="prod-price gift-amount">{{$giftAmount > 0 ? Helper::formatNumber(($giftAmount * $currency->value) )  : '0.000'  }} {{$currencyCode}}</div>

                                    </div>
                                </div>
                                <div class="prod-details">
                                    <div class="gift-wrap-information">
                                        <div class="form-item">
                                            <label>{{__('web.title')}}</label>
                                            <input type="text" name="gift_title" maxlength="20" class="gift_title" value="{{$gift_wrap['title']}}">
                                        </div>
                                        <div class="form-item">
                                            <label>{{__('web.message')}}</label>
                                            <textarea name="gift_message" class="gift_message" maxlength="40">{{$gift_wrap['message']}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endif
                    </div>



                </div>
                <div class="cart-actions sm-hidden">
                    <a href="{{route('web.product.index')}}" class="link-button"><i class="fa fa-arrow-left"></i>{{__('web.continue_shopping')}}</a>
                </div>
            </div>
            @if(count($cart)>0)
            <?php
            // Apply site discount on main order only
            $discountApplied = ($mainTotal * $discount) / 100;
            /*
             * Coupon Code
             */
            $appliedCoupon = [];
            $couponDiscountFinal = 0;
            $appliedCoupon = session('couponCode');
            if ($appliedCoupon) {
                if (isset($appliedCoupon['user_id'])) {
                    if ($user) {
                        if ($appliedCoupon['user_id'] != $user->id) {
                            $appliedCoupon = [];
                        }
                    }
                }
                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);

                if (!$couponStatus['status']) {
                    $appliedCoupon = [];
                }
            }

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


            /*
             * End :: Coupon Code
             */


            /*
             *  Convert Currency Rate
             */

            //            dd($couponDiscountFinal);
            if ($currency) {
                $discountApplied = $discountApplied * $currency->value;
                $couponDiscountFinal = $couponDiscountFinal;
            }

            $shippingCharge =  Helper::getShippingAmount($total, $shippingCharge);
            //            dd($couponDiscountFinal);

            // Final total = main order - discounts + customized advance + shipping + tax
            $finalTotal = $mainTotal + $shippingCharge + $taxValue - $discountApplied - $couponDiscountFinal + $customAdvance;
            ?>

            <div class="cart-summary">
                @if(config('custom_web.show_coupon_card'))
                @include('web.checkout.coupon_card')
                @endif
                <div class="cart-details">
                    <h4>{{__('web.price_detail')}}</h4>
                    <div class="v-flex">
                        <div class="total">
                            <div class="item">{{__('web.sub_total')}}</div>
                            <div class="item-price" data-subtotal="{{$mainTotal}}">{{Helper::formatNumber($mainTotal)}} {{$currencyCode}}</div>
                        </div>
                        @if(!empty($customOrderDetails['payment']['advance']))
                            <div class="total">
                                <div class="item">{{__('web.custom_advance')}}</div>
                                <div class="item-price custom-advance">{{ Helper::formatNumber($customOrderDetails['payment']['advance']) }} {{$currencyCode}}</div>
                            </div>
                        @endif
                        <div class="total">
                            <div class="item">{{__('web.delivery_price')}}</div>
                            <div class="item-price shipping-price">{{Helper::formatNumber($shippingCharge)}} {{$currencyCode}}</div>
                        </div>
                        @if($setting['31']->setting_val ==1)

                        <div class="total gift_wrap_item {{$gift_wrap['is_gift'] == 1? 'show': 'hide'}}">
                            <div class="item">{{__('web.gift_wrap')}}</div>
                            <div class="item-price gift_wrap_price">{{$giftAmount > 0 ? Helper::formatNumber(($giftAmount * $currency->value)) : '0.000' }} {{$currencyCode}}</div>
                        </div>
                        @endif
                        @if(config('custom_web.apply_discount') )
                        <?php
                        $coup = 'hide';

                        if (isset($appliedCoupon) && count($appliedCoupon) > 0) {
                            $coup = 'show';
                        }
                        ?>
                        <div class="total coupon-total {{$coup}}">
                            <div class="item "><span class="coupon_name">{{isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '-'}}</span> <span class="link-button remove-coupon-code">Remove</span></div>
                            <div class="item-price coupon-final-discount">- {{Helper::formatNumber($couponDiscountFinal)}} {{$currencyCode}}</div>
                        </div>
                        @endif
                        <?php
                        $finalTotalWithoutgift = $finalTotal;
                        if ($setting['31']->setting_val == 1) {
                            if ($gift_wrap['is_gift'] == 1) {
                                $finalTotal = $finalTotal + ($giftAmount * $currency->value);
                            }
                        }
                        ?>
                        <div class="total grandtotal">
                            <div class="item">{{__('web.grand_total')}} </div>
                            <div class="item-price grand-total" data-grand-total='{{Helper::formatNumber($finalTotalWithoutgift)}}'>{{Helper::formatNumber($finalTotal)}} {{$currencyCode}}</div>
                        </div>
                        <div class="page-actions">
                            <button class="btn btn-primary place_order2">{{__('web.place_order')}}</button>
                        </div>
                        <div class="page-actions" style="margin-top:15px;">
                            <div class="d-flex align-items-center justify-content-between">

                                <!-- LEFT SIDE -->
                                <div class="d-flex align-items-center checkbox">
                                    <input type="checkbox"
                                        id="trigger_customized_order_modal"
                                        {{ session('customized_order_details') ? 'checked' : '' }}>

                                    <label for="trigger_customized_order_modal"
                                        style="font-weight:600; margin:0 0 0 8px;">
                                        {{ __('web.add_customized_order') }}
                                    </label>
                                </div>

                                <!-- RIGHT SIDE -->
                                @if(session('customized_order_details'))
                                    <a href="javascript:void(0)"
                                    onclick="$('#customizedOrderModal').modal('show')"
                                    title="{{ __('web.edit') }}"
                                    style="text-decoration:none;">
                                        <i class="fas fa-edit" style="color:#555;"></i>
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customized Order Details Box --}}
                <?php $customOrder = session('customized_order_details'); ?>
                @if($customOrder)
                <div class="cart-details" style="margin-top: 20px;">
                    <h4>{{__('web.customized_order_details')}}</h4>
                    <div style="font-size: 13px; margin-bottom: 10px;">
                        <span style="font-weight: bold; color: #555;">{{__('web.request_type')}}:</span>
                        <span style="margin-left: 5px; text-transform: uppercase;">{{ str_replace('_', ' ', $customOrder['type']) }}</span>
                    </div>

                    {{-- Customer Info --}}
                    <div style="font-size: 13px;">
                        <span style="font-weight: bold; color: #555;">{{__('web.customer_details')}}:</span>
                    </div>
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <div style="font-size: 13px;">
                            <div><strong>{{__('web.name')}}:</strong> {{ $customOrder['customer']['name'] }}</div>
                            <div><strong>{{__('web.phone')}}:</strong> {{ $customOrder['customer']['phone'] }}</div>
                            <div><strong>{{__('web.email')}}:</strong> {{ $customOrder['customer']['email'] }}</div>
                            <div style="margin-top:6px;">
                                <?php
                                $cust = $customOrder['customer'];
                                if (isset($cust['block']) && $cust['block']) echo '<div>Apartment : ' . e($cust['block']) . '</div>';
                                if (isset($cust['floor']) && $cust['floor']) echo '<div>Floor : ' . e($cust['floor']) . '</div>';
                                if (isset($cust['house_number']) && $cust['house_number']) echo '<div>House Number : ' . e($cust['house_number']) . '</div>';
                                if (isset($cust['city']) && $cust['city']) echo '<div>City : ' . e($cust['city']) . '</div>';
                                if (isset($cust['country_id']) && $cust['country_id']) {
                                    $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                                    if ($cn) echo '<div>Country : ' . e($cn->country_name) . '</div>';
                                }
                                if (isset($cust['address_detail']) && $cust['address_detail']) echo '<div>Address Detail : ' . e($cust['address_detail']) . '</div>';
                                ?>
                            </div>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <h6 style="font-weight: bold; margin-bottom: 10px; font-size: 13px; color: #555; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px;">{{__('web.items')}}</h6>
                    <div class="v-flex" style="margin-bottom: 15px;">
                        @foreach($customOrder['items'] as $item)
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px dashed #e1e1e1; padding-bottom: 10px; margin-bottom: 10px;">
                            <div style="flex: 1; margin-right: 15px;">
                                <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px; line-height: 1.3;">{{ $item['description'] }}</div>
                                <div style="font-size: 12px; color: #777; line-height: 1.4;">
                                    @if(!empty($item['size']))
                                    <span style="margin-right: 8px;"><strong>{{__('web.size')}}:</strong> {{ $item['size'] }}</span>
                                    @endif
                                    @if(!empty($item['color']))
                                    <span style="margin-right: 8px;"><strong>{{__('web.color')}}:</strong> {{ $item['color'] }}</span>
                                    @endif
                                    <span><strong>{{__('web.qty')}}:</strong> {{ $item['quantity'] }}</span>
                                </div>
                            </div>
                            <div style="font-weight: bold; white-space: nowrap; font-size: 14px; color: #333;">
                                {{ Helper::formatNumber($item['total']) }} {{$currencyCode}}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Payment Details if exists --}}
                    @if(!empty($customOrder['payment']['advance']))
                    <div style="margin-top: 10px; background: #eef7ee; padding: 10px; border-radius: 5px;">
                        <div class="h-flex" style="justify-content: space-between;">
                            <span><strong>{{__('web.advance_payment')}}:</strong></span>
                            <div style="font-weight: bold; white-space: nowrap; font-size: 14px; color: #333;">
                                {{ Helper::formatNumber($customOrder['payment']['advance']) }} {{$currencyCode}}
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
                @endif

                @endif
            </div>
            <div class="cart-actions sm-visible">
                <a href="{{route('web.product.index')}}" class="link-button"><i class="fa fa-arrow-left"></i>{{__('web.continue_shopping')}}</a>
            </div>
        </div>
    </div>
</section>
@include('web.cart.save-note')

<!-- Customized Order Modal -->
<div id="customizedOrderModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h1 class="address_title">{{__('web.add_customized_order')}}</h1>
                <form id="customized_order_payload_form">

                    <!-- Request Type -->
                    <div class="form-group">
                        <div class="form-item h-flex">
                            <div class="radio w-50">
                                <input type="radio" id="req_type_po" name="custom_req_type" value="purchase_order" {{ (!session('customized_order_details') || session('customized_order_details.type') == 'purchase_order') ? 'checked' : '' }}>
                                <label for="req_type_po">{{__('web.purchase_order')}}</label>
                            </div>
                            <div class="radio w-50">
                                <input type="radio" id="req_type_qt" name="custom_req_type" value="quotation" {{ (session('customized_order_details.type') == 'quotation') ? 'checked' : '' }}>
                                <label for="req_type_qt">{{__('web.quotation')}}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;">{{__('web.customer_details')}}</h5>
                    <div class="row">
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_phone" value="{{ isset($existingCust['customer']['phone']) ? $existingCust['customer']['phone'] : '' }}" class="form-control mandatory" placeholder="{{__('web.enter_phone_number')}}*" data-req-msg="{{__('web.error-req-phone-number')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_email" value="{{ isset($existingCust['customer']['email']) ? $existingCust['customer']['email'] : '' }}" class="form-control mandatory" placeholder="{{__('web.enter_email')}}*" data-req-msg="{{__('web.error-req-email')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_name" value="{{ isset($existingCust['customer']['name']) ? $existingCust['customer']['name'] : '' }}" class="form-control mandatory" placeholder="{{__('web.enter_name')}}*" data-req-msg="{{__('web.error-req-name')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <select name="cust_country_id" class="form-control mandatory" data-req-msg="{{__('web.error-req-country')}}">
                                <option value="">{{__('web.select_country')}}</option>
                                <?php $countries = \App\Helpers\Helper::getCountryList();
                                $language = Helper::getLanguage();
                                ?>
                                @foreach($countries as $c)
                                <?php $cDesc = $c->getCountryDescription($language->id);
                                $sel = (isset($existingCust['customer']['country_id']) && $existingCust['customer']['country_id'] == $c->id) ? 'selected' : ''; ?>
                                <option value="{{$c->id}}" {{$sel}}>{{ $cDesc ? $cDesc->country_name : $c->currency_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-item">
                            <div class="cust_city_list">
                                <select name="cust_city_id" class="form-control cust_city_id select-component untouched">
                                    <option value="">{{__('web.select_area')}}</option>
                                </select>
                            </div>
                            <div class="cust_city_text" style="display:none; margin-top:5px;">
                                <input type="text" name="cust_city" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}" class="form-control mandatory" placeholder="{{__('web.enter_city')}}*" data-req-msg="{{__('web.error-req-city')}}">
                            </div>
                            <input type="hidden" id="existing_custom_city_id" value="{{ isset($existingCust['customer']['city_id']) ? $existingCust['customer']['city_id'] : '' }}">
                            <input type="hidden" id="existing_custom_city_name" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_block" value="{{ isset($existingCust['customer']['block']) ? $existingCust['customer']['block'] : '' }}" class="form-control mandatory" placeholder="{{__('web.block')}}*" data-req-msg="{{__('web.error-req-block')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_street" value="{{ isset($existingCust['customer']['street']) ? $existingCust['customer']['street'] : '' }}" class="form-control mandatory" placeholder="{{__('web.street')}}*" data-req-msg="{{__('web.error-req-street')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_house_number" value="{{ isset($existingCust['customer']['house_number']) ? $existingCust['customer']['house_number'] : '' }}" class="form-control mandatory" placeholder="{{__('web.house_number')}}*" data-req-msg="{{__('web.error-req-house')}}">
                        </div>
                        <div class="col-md-6 form-item">
                            <input type="text" name="cust_floor" value="{{ isset($existingCust['customer']['floor']) ? $existingCust['customer']['floor'] : '' }}" class="form-control" placeholder="{{__('web.floor')}}">
                        </div>
                        <div class="col-md-12 form-item">
                            <input type="text" name="cust_address_detail" value="{{ isset($existingCust['customer']['address_detail']) ? $existingCust['customer']['address_detail'] : '' }}" class="form-control" placeholder="{{__('web.address_detail')}}">
                        </div>
                        <div class="col-md-12 form-item">
                            <input type="text" name="cust_address_url" value="{{ isset($existingCust['customer']['address_url']) ? $existingCust['customer']['address_url'] : '' }}" class="form-control" placeholder="{{__('web.address_url')}}">
                        </div>
                    </div>

                    <!-- Order Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;">{{__('web.order_details')}}</h5>
                    <div class="custom-items-wrapper">

                        <div id="modal_custom_items_list">
                            @if(isset($existingCust['items']) && count($existingCust['items']) > 0)
                                @foreach($existingCust['items'] as $index => $item)
                                <div class="row modal-custom-item-row">
                                    <div class="col-md-3 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.description') }}</label> @endif
                                        <textarea name="custom_desc[]" class="form-control mandatory" placeholder="{{__('web.enter_description')}}*" data-req-msg="{{__('web.error-req-description')}}" rows="1" style="resize: none;">{{ $item['description'] }}</textarea>
                                    </div>
                                     <div class="col-md-2 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.color') }}</label> @endif
                                        <input type="text" name="custom_color[]" value="{{ $item['color'] ?? '' }}" class="form-control mandatory" placeholder="{{__('web.enter_color')}}*" data-req-msg="{{__('web.error-req-color')}}">
                                    </div>
                                     <div class="col-md-2 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.size') }}</label> @endif
                                        <input type="text" name="custom_size[]" value="{{ $item['size'] ?? '' }}" class="form-control mandatory" placeholder="{{__('web.enter_size')}}*" data-req-msg="{{__('web.error-req-size')}}">
                                    </div>
                                     <div class="col-md-2 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.price') }}</label> @endif
                                        <input type="number" name="custom_price[]" value="{{ $item['price'] ?? '' }}" class="form-control custom-price mandatory" placeholder="{{__('web.enter_price')}}*" step="0.001" data-req-msg="{{__('web.error-req-price')}}">
                                    </div>
                                    <div class="col-md-1 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.quantity') }}</label> @endif
                                        <input type="number" name="custom_qty[]" value="{{ $item['quantity'] ?? '' }}" class="form-control custom-qty mandatory" placeholder="{{__('web.enter_quantity')}}*" min="1" data-req-msg="{{__('web.error-req-quantity')}}">
                                    </div>
                                     <div class="col-md-1 form-item pr-0">
                                        @if($index === 0) <label>{{ __('web.total_price') }}</label> @endif
                                        <input type="text" name="custom_total[]" value="{{ $item['total'] ?? '0.000' }}" class="form-control custom-total" readonly>
                                    </div>
                                    <div class="col-md-1 form-item pr-0 icon-btn @if($index === 0) mt-4 @endif">
                                        <span class="add-modal-custom-item mr-2" style="cursor:pointer; font-size: 16px; display:block;" title="{{__('web.add_item')}}"><i class="fa fa-plus"></i></span>
                                        @if($index > 0)
                                        <span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{__('web.remove_item')}}"><i class="fa fa-trash"></i></span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <!-- <div style="margin-top: 10px;">
                             <button type="button" id="modal_add_item_btn" class="link-button"><i class="icon-plus"></i> {{__('web.add_item')}}</button>
                         </div> -->
                        <div style="text-align: right; font-weight: bold; margin-top: 10px;">
                            <?php 
                                $modalTotal = 0;
                                if(isset($existingCust['items'])) {
                                    foreach($existingCust['items'] as $it) {
                                        $modalTotal += floatval($it['total'] ?? 0);
                                    }
                                }
                            ?>
                            {{__('web.total')}}: <span id="modal_grand_total">{{ number_format($modalTotal, 3) }}</span> {{$currencyCode}}
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div id="payment_section">
                        <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{__('web.payment_details')}}</h5>
                        <div class="row">
                            <div class="col-md-12 form-item">
                                <label>{{__('web.advance_payment')}}</label>
                                <input type="number" name="advance_payment" value="{{ isset($existingCust['payment']['advance']) ? $existingCust['payment']['advance'] : '' }}" class="form-control mandatory" placeholder="{{__('web.enter_advance_payment')}}*" step="0.001" data-req-msg="{{__('web.error-req-advance-payment')}}">
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submit_customized_order">{{__('web.submit')}}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('web.close')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('page_specific_js')
<script>
    $(function() {
        // Customized Order JS
        // Toggle form logic
        // --- Customized Order Modal Logic --- //

        // Trigger Modal or Remove
        $('#trigger_customized_order_modal').change(function() {
            if ($(this).is(':checked')) {
                $('#customizedOrderModal').modal('show');
                // Initialize if empty
                if ($('#modal_custom_items_list .modal-custom-item-row').length === 0) {
                    addModalCustomItemRow();
                }
            } else {
                // Determine if we should show confirmation or simply just revert (case: user unchecked, but we want to confirm)
                // Show Bootstrap Modal
                $('#removeCustomizedOrderModal').modal('show');
            }
        });

        // Uncheck trigger when modal hidden ID user didn't submit?
        // No, if user closes modal without submit, we might want to uncheck if no session exists.
        // But simpler: just keep it consistent with session.
        $('#customizedOrderModal').on('hidden.bs.modal', function() {
            // If we have session, keep checked. If not, uncheck.
            @if(!session('customized_order_details'))
            $('#trigger_customized_order_modal').prop('checked', false);
            @endif
        });

        // When modal shown, if country already set (e.g., autofill), refresh city list
        $('#customizedOrderModal').on('shown.bs.modal', function() {
            var country = $('select[name="cust_country_id"]').val();
            if (country) {
                // Prefer existing stored city id (hidden), fallback to select DOM value
                var existingCityId = $('#existing_custom_city_id').val() || $('.cust_city_id').val();
                var existingCityName = $('#existing_custom_city_name').val() || $('input[name="cust_city"]').val();
                // If we have a city name but no id, pass undefined so getCustomCity will try matching by text
                getCustomCity(country, existingCityId ? existingCityId : null);
            }
        });

        // Auto Fetch Customer
        $('input[name="cust_phone"], input[name="cust_email"]').on('blur', function() {
            var phone = $('input[name="cust_phone"]').val();
            var email = $('input[name="cust_email"]').val();

            if (phone || email) {
                $.ajax({
                    url: "{{route('web.cart.get_customer')}}",
                    method: "POST",
                    data: {
                        phone: phone,
                        email: email,
                        _token: "{{csrf_token()}}"
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            $('input[name="cust_name"]').val(response.data.name).trigger('input');
                            if (response.data.email && !email) $('input[name="cust_email"]').val(response.data.email).trigger('input');
                            if (response.data.phone && !phone) $('input[name="cust_phone"]').val(response.data.phone).trigger('input');
                            // Fill structured address if available (supports both `structured` and object `address` responses)
                            if (response.data.structured && Object.keys(response.data.structured).length) {
                                var s = response.data.structured;
                                if (s.country_id) {
                                    $('select[name="cust_country_id"]').val(s.country_id).trigger('change');
                                    if (s.city_id) {
                                        getCustomCity(s.country_id, s.city_id);
                                    } else if (s.city) {
                                        getCustomCity(s.country_id);
                                        setTimeout(function() {
                                            var sel = $('.cust_city_id option').filter(function() {
                                                return $(this).text().trim() == s.city;
                                            }).first();
                                            if (sel.length) {
                                                $('.cust_city_id').val(sel.val());
                                                $('.cust_city_list').show();
                                                $('.cust_city_text').hide();
                                            } else {
                                                $('input[name="cust_city"]').val(s.city).trigger('input');
                                                $('.cust_city_text').show();
                                                $('.cust_city_list').hide();
                                            }
                                        }, 400);
                                    }
                                }
                                if (s.block) $('input[name="cust_block"]').val(s.block).trigger('input');
                                if (s.street) $('input[name="cust_street"]').val(s.street).trigger('input');
                                if (s.house_number) $('input[name="cust_house_number"]').val(s.house_number).trigger('input');
                                if (s.floor) $('input[name="cust_floor"]').val(s.floor).trigger('input');
                                if (s.address_detail) $('input[name="cust_address_detail"]').val(s.address_detail).trigger('input');
                                if (s.address_url) $('input[name="cust_address_url"]').val(s.address_url).trigger('input');
                            } else if (response.data.address) {
                                // If the API returned an address object, map its fields to our inputs
                                var addr = response.data.address;
                                if (typeof addr === 'object' && addr !== null) {
                                    if (addr.country_id) {
                                        $('select[name="cust_country_id"]').val(addr.country_id).trigger('change');
                                        if (addr.city_id) {
                                            getCustomCity(addr.country_id, addr.city_id);
                                        } else if (addr.city) {
                                            getCustomCity(addr.country_id);
                                            setTimeout(function() {
                                                var sel = $('.cust_city_id option').filter(function() {
                                                    return $(this).text().trim() == addr.city;
                                                }).first();
                                                if (sel.length) {
                                                    $('.cust_city_id').val(sel.val());
                                                    $('.cust_city_list').show();
                                                    $('.cust_city_text').hide();
                                                } else {
                                                    $('input[name="cust_city"]').val(addr.city).trigger('input');
                                                    $('.cust_city_text').show();
                                                    $('.cust_city_list').hide();
                                                }
                                            }, 400);
                                        }
                                    }
                                    // prefer explicit city/area name if present
                                    if (addr.city && !addr.city_id) {
                                        // if area exists and select not available, set input
                                        if (addr.area) {
                                            $('input[name="cust_city"]').val(addr.area).trigger('input');
                                        }
                                    }
                                    if (addr.block) $('input[name="cust_block"]').val(addr.block).trigger('input');
                                    if (addr.street) $('input[name="cust_street"]').val(addr.street).trigger('input');
                                    if (addr.house_number) $('input[name="cust_house_number"]').val(addr.house_number).trigger('input');
                                    if (addr.floor) $('input[name="cust_floor"]').val(addr.floor).trigger('input');
                                    if (addr.address_detail) $('input[name="cust_address_detail"]').val(addr.address_detail).trigger('input');
                                    if (addr.address_url) $('input[name="cust_address_url"]').val(addr.address_url).trigger('input');
                                } else {
                                    // fallback to legacy address string
                                    $('input[name="cust_address_detail"]').val(response.data.address).trigger('input');
                                }
                            }
                        }
                    }
                });
            }
        });

        // Helper to fetch city list for customized modal and optionally select a city by id
        function getCustomCity(countryId, cityId) {
            if (!countryId) return;
            $.ajax({
                url: "{{route('web.user.get_city')}}",
                method: "POST",
                data: {
                    country_id: countryId,
                    _token: "{{csrf_token()}}"
                },
                success: function(data) {
                    if (!data) {
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                        return;
                    }

                    // Fill select with returned options
                    $('.cust_city_id').html(data);
                    // Create temp select to inspect options
                    var tmp = $('<select>').html(data);
                    var opts = tmp.find('option');
                    if (opts.length > 1) {
                        // Show select and hide text input
                        $('.cust_city_list').show();
                        $('.cust_city_text').hide();

                        // Determine which option to select
                        var didSelect = false;
                        // 1) If explicit cityId provided and matching option exists, select it
                        if (cityId) {
                            var optByVal = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (optByVal.length) {
                                optByVal.prop('selected', true);
                                didSelect = true;
                            }
                        }

                        // 2) If not selected and cust_city text input has a value, try to match by option text (case-insensitive)
                        if (!didSelect) {
                            var desiredText = $('input[name="cust_city"]').val();
                            if (desiredText && desiredText.trim() !== '') {
                                desiredText = desiredText.trim().toLowerCase();
                                var matched = null;
                                $('.cust_city_id option').each(function() {
                                    var t = $(this).text().trim().toLowerCase();
                                    if (t === desiredText) {
                                        matched = $(this);
                                        return false;
                                    }
                                });
                                if (matched && matched.length) {
                                    matched.prop('selected', true);
                                    didSelect = true;
                                }
                            }
                        }

                        // 3) Fallback: choose first real option
                        if (!didSelect) {
                            var firstVal = opts.eq(1).attr('value');
                            if (firstVal) $('.cust_city_id').val(firstVal);
                        }

                        // Ensure cust_city text input contains the selected option text for server-side
                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) $('input[name="cust_city"]').val(selectedText).trigger('input');

                        // trigger change so any listeners update
                        $('.cust_city_id').trigger('change');
                    } else {
                        // No city list available: show text input
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                    }
                }
            });
        }

        // Bind country change to fetch cities
        $('select[name="cust_country_id"]').change(function() {
            var countryId = $(this).val();
            getCustomCity(countryId);
        });
        // When city select changes, copy its text into cust_city input so server always receives a city name
        $(document).on('change', '.cust_city_id', function() {
            var txt = $(this).find('option:selected').text().trim();
            if (txt) {
                $('input[name="cust_city"]').val(txt);
            }
        });

        // Toggle Payment Section based on Request Type
        $('input[name="custom_req_type"]').change(function() {
            var val = $('input[name="custom_req_type"]:checked').val();
            if (val === 'quotation') {
                $('#payment_section').slideUp();
                $('#payment_section').find('.mandatory').removeClass('mandatory').next('.text-red').remove().css('border', '1px solid #e1e1e1');
            } else {
                $('#payment_section').slideDown();
                $('#payment_section').find('select, input').addClass('mandatory');
            }
        });

        // Add Item
        $(document).on('click', '.add-modal-custom-item', function() {
            addModalCustomItemRow();
        });

        // Remove Item
        $(document).on('click', '.remove-modal-custom-item', function() {
            $(this).closest('.modal-custom-item-row').remove();
            calculateModalTotal();
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-price, .custom-qty', function() {
            var row = $(this).closest('.modal-custom-item-row');
            var qty = parseFloat(row.find('.custom-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-price').val()) || 0;
            var total = qty * price;

            row.find('.custom-total').val(total.toFixed(3));
            calculateModalTotal();
        });

        // --- Validation JS --- //
        // Show validation message on click/focus
        $(document).on('click focus', '.mandatory', function() {
            var input = $(this);
            // Remove any existing error message and reset border
            input.next('.text-red').remove();
            input.css('border', '1px solid #e1e1e1');

            // Check if empty
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                    input.css('border', '1px solid red');
                }
            }
        });

        // Remove error when user starts typing
        $(document).on('input', '.mandatory', function() {
            $(this).next('.text-red').remove();
            $(this).css('border', '1px solid #e1e1e1');
        });

        // Show validation message on blur if empty
        $(document).on('blur', '.mandatory', function() {
            var input = $(this);
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    // Check if error already exists
                    if (input.next('.text-red').length === 0) {
                        input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                        input.css('border', '1px solid red');
                    }
                }
            }
        });

        // Submit Logic
        $('#submit_customized_order').click(function() {
            var isValid = true;
            $('#customized_order_payload_form .mandatory').each(function() {

                if ($(this).val().trim() === '' && $(this).is(':visible')) {
                    isValid = false;
                    var msg = $(this).attr('data-req-msg');
                    $(this).next('.text-red').remove();
                    if (msg) {
                        $(this).after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                        $(this).css('border', '1px solid red');
                    }
                }
            });

            if (isValid) {
                var formData = new FormData($('#customized_order_payload_form')[0]);
                formData.append('_token', "{{csrf_token()}}");

                $.ajax({
                    url: "{{route('web.cart.submit_customized_order')}}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            location.reload();
                        } else {
                            // alert(response.message);
                        }
                    },
                    error: function() {
                        // alert("{{__('web.error_something_went_wrong')}}");
                    }
                });
            }
        });


        function addModalCustomItemRow() {
            var $list = $('#modal_custom_items_list');
            var totalRows = $list.find('.modal-custom-item-row').length;
            var isFirst = totalRows === 0;
            // Hide add button from previous last row
            if (totalRows > 0) {
                $list.find('.modal-custom-item-row:last .add-modal-custom-item').remove();
            }

            var deleteBtn = isFirst ? '' : '<span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{__('web.remove_item')}}"><i class="fa fa-trash"></i></span>';
            var addBtn = '<span class="add-modal-custom-item mr-2" style="cursor:pointer; font-size: 16px; display:block;" title="{{__('web.add_item')}}"><i class="fa fa-plus"></i></span>';

            var html = `
                <div class="row modal-custom-item-row">
                    <div class="col-md-3 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.description') }}</label>` : ''}
                        <textarea name="custom_desc[]" class="form-control mandatory" placeholder="{{__('web.enter_description')}}*" data-req-msg="{{__('web.error-req-description')}}" rows="1" style="resize: none;"></textarea>
                    </div>
                     <div class="col-md-2 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.color') }}</label>` : ''}
                        <input type="text" name="custom_color[]" class="form-control mandatory" placeholder="{{__('web.enter_color')}}*" data-req-msg="{{__('web.error-req-color')}}">
                    </div>
                     <div class="col-md-2 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.size') }}</label>` : ''}
                        <input type="text" name="custom_size[]" class="form-control mandatory" placeholder="{{__('web.enter_size')}}*" data-req-msg="{{__('web.error-req-size')}}">
                    </div>
                     <div class="col-md-2 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.price') }}</label>` : ''}
                        <input type="number" name="custom_price[]" class="form-control custom-price mandatory" placeholder="{{__('web.enter_price')}}*" step="0.001" data-req-msg="{{__('web.error-req-price')}}">
                    </div>
                    <div class="col-md-1 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.quantity') }}</label>` : ''}
                        <input type="number" name="custom_qty[]" class="form-control custom-qty mandatory" placeholder="{{__('web.enter_quantity')}}*" min="1" data-req-msg="{{__('web.error-req-quantity')}}">
                    </div>
                     <div class="col-md-1 form-item pr-0">
                        ${isFirst ? `<label>{{ __('web.total_price') }}</label>` : ''}
                        <input type="text" name="custom_total[]" class="form-control custom-total" readonly value="0.000">
                    </div>
                    <div class="col-md-1 form-item pr-0 icon-btn ${isFirst ? 'mt-4' : ''}">
                        ${addBtn}
                        ${!isFirst ? `${deleteBtn}` : ''}
                    </div>
                </div>
            `;
            $('#modal_custom_items_list').append(html);
        }

        function calculateModalTotal() {
            var grandTotal = 0;
            $('.custom-total').each(function() {
                var val = parseFloat($(this).val()) || 0;
                grandTotal += val;
            });
            $('#modal_grand_total').text(grandTotal.toFixed(3));
        }

        // Remove Row
        $(document).on('click', '.remove-custom-item', function() {
            $(this).closest('.custom-item-row').remove();
            calculateCustomOrderTotal();
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-item-qty, .custom-item-price', function() {
            var row = $(this).closest('.custom-item-row');
            var qty = parseFloat(row.find('.custom-item-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-item-price').val()) || 0;
            var total = qty * price;

            // Update row total field
            row.find('.custom-item-total').val(total.toFixed(3));

            calculateCustomOrderTotal();
        });

        // Show validation message on click/focus
        $(document).on('click focus', '.mandatory', function() {
            var input = $(this);
            // Remove any existing error message and reset border
            input.next('.text-red').remove();
            input.css('border', '1px solid #e1e1e1');

            // Check if empty
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                    input.css('border', '1px solid red');
                }
            }
        });

        // Remove error when user starts typing
        $(document).on('input', '.mandatory', function() {
            $(this).next('.text-red').remove();
            $(this).css('border', '1px solid #e1e1e1');
        });
        // Remove Confirmation Modal Logic
        var removeConfirmed = false;
        $('#confirm_remove_customized_order').click(function() {
            removeConfirmed = true;
            $.ajax({
                url: "{{route('web.cart.remove_customized_order')}}",
                method: "POST",
                data: {
                    _token: "{{csrf_token()}}"
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                         // if fails, close modal
                         $('#removeCustomizedOrderModal').modal('hide');
                    }
                }
            });
        });

        $('#removeCustomizedOrderModal').on('hidden.bs.modal', function() {
            if (!removeConfirmed) {
                // User cancelled or closed modal without confirming
                $('#trigger_customized_order_modal').prop('checked', true);
            }
        });
    });
</script>

<!-- Remove Confirmation Modal -->
<div id="removeCustomizedOrderModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h1 class="address_title">{{__('web.confirmation')}}</h1>
                <p>{{__('web.remove_customized_order')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm_remove_customized_order">{{__('web.yes_remove')}}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('web.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

@include('web.cart.save-note')

@endsection
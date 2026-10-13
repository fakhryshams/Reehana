@extends('web/layout/main')
@section('page_title',  trans('web.cart_review'))
@section('body_class',  'menubg configMonogram')
@section('content')
<style>
    .main-info .product-actions a {
        width: auto;
        margin: 0 15px 0 0;
        font-size: 14px;
        text-transform: uppercase;
    }

    .intl-ship{
        color:  red;
    }
    .strike {
        text-decoration: line-through;
    }
</style>
<?php
$size = '';
$total = 0;
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$paymentMethod = Helper::getPaymentMethod();

$intlShippingStatus = true;
$intlShippingStatusCount = 0;

$address = "";

$countryData = Helper::getDefaultCountry();
$appliedCoupon = session('couponCode');

if ($user && session('default_address'))
{
    $address =  Helper::getPickupStoreAddress();
    
    if ($address)
    {
        $countryData = $address->getcountry();
    }



    $city = $address ? $address->city : '';
    if (!$city && $address->city_id)
    {
        $city = $address->getUserCity($language->id) ? $address->getUserCity($language->id)->city_name : '';
    }
    $address->city = $city;
}
else
{
    $appliedCoupon = [];
}

$tax = Helper::getTax();
$tax = $tax ? $tax : 0;
$taxValue = ($total * $tax) / 100;
$shippingCharge = 0;
$discount = config('custom_web.discount', 20);

if ($countryData)
{
    $shippingCharges = $countryData->shipping_charge;
    /*
     *  Convert Currency Rate
     */
    if ($currency)
    {
        $shippingCharges = $shippingCharges * $currency->value;
    }
}

if ($user)
{
  
       
        if ($address)
        {

                if($address->id != '7')
                {
                    $zoneprice = Helper::getZonePrice($address->city_id);
                    if ($zoneprice > 0)
                    {
                        $shippingCharges = $zoneprice * $currency->value;
                    }

                }
              else
              {
                    $shippingCharges =0;
              }
        }
  


}

$total_weight = 0;
?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.checkout')}}</a></span>
    </div>
</section>
<form name="cart_review_form" id="cart_review_form" action="{{route('web.checkout.initiatePayment')}}" method="post">
    @csrf
    <section class="cart-review">
        <div class="container">
            <div class="checkout-workflow">
                <ul>
                    <li class="active">
                        <span>1</span>
                        <label>{{__('web.cart')}}</label>
                    </li>
                    <li class="active">
                        <span>2</span>
                        <label>{{__('web.shipping_address')}}</label>
                    </li>
                    <li class="active">
                        <span>3</span>
                        <label>{{__('web.review_pay')}}</label>
                    </li>
                </ul>
            </div>
            <div class="h-flex flex-nowrap">
                <div class="cart-items-summary">
                    <div class="cart-products">
                        <div class="cart-list">
                            @foreach($cart as  $id =>$record)
                            <?php
                            $isDefault = true;
                            if ($user)
                            {
                                $sizeid = 's';
                                if ($record['size_id'])
                                {
                                    $sizeid = $record['size_id'];
                                }
                                $colorid = 'c';
                                if ($record['color_id'])
                                {
                                    $colorid = $record['color_id'];
                                }
                                $id = $record['product_id'] . "_" . $sizeid . "_" . $colorid;

                                // $record['monogram'] = $record;
                            }
                            $cartQuantity = $record['quantity'];

                            $product = Helper::getProductDetail($record['product_id']);
                           
                            if (!$product)
                            {
                                continue;
                            }
                             $has_monogram = Helper::isMonogranm($product->id);
                            $productimage = $product->getDefaultImage();
                            $productDetail = $product->getProductDescription($language->id);
                            $productImage = $product->getDefaultImage();
                            $sizeId = $record['size_id'];
                            $color_id = $record['color_id'];
                            $product_weight = $product->product_weight;
                            $size = Helper::getSize($sizeId, $language->id);
                            $color = Helper::getColorName($color_id, $language->id);

                            $productSize = false;
                            $colorExist = false;

                            if ($color)
                            {
                                $colorExist = $color->color_id;
                            }
                            if ($size)
                            {
                                $productSize = $size->size_id;
                            }

                            //$productQuantity = $product->getProductQuantity($product->id);
                            $productQuantity = Helper::getProductQuantity($product->id, $productSize ? 1 : 0, $sizeId, $colorExist ? 1 : 0, $color_id);
                            $product_sale_price =  $product->product_sale_price;

                            $product_regular_price = $product->product_regular_price;
    
                            $is_product_sale_price_null  = $product->is_product_sale_price_null;

                            $style = '';
                            if ($color || $size)
                            {
                                $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);

                                if ($attributePrice)
                                {
                                    $style = $attributePrice->style;
                                    $product_weight = $attributePrice->weight;
                                    $product_regular_price = $attributePrice->product_regular_price;
                                    $product_sale_price = $attributePrice->product_sale_price;
                                    $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                                }
                            }
                            $price = $is_product_sale_price_null == 0 ? $product_sale_price : $product_regular_price;

                            //monogram
                            $cartproduct = Helper::getCartProduct($user, $product->id, $productSize ? 1 : 0, $productSize ? $sizeId : 's', $colorExist ? 1 : 0, $color_id ? $color_id : 'c');
                            $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                            $font = [];
                            $initials = [];
                            $colors = [];
                            $placement = [];
                            $is_monogram = false;

                            if (count($cartproduct) > 0)
                            {
                                if ($user)
                                {
                                    if ($cartproduct['is_monogram'] == 1)
                                    {
                                        $cartproduct['monogram'] = $cartproduct;
                                    }
                                    else
                                    {
                                        $cartproduct['monogram'] = [];
                                    }
                                }


                                if (count($cartproduct['monogram']) > 0 && $cartproduct['is_monogram'] == 1)
                                {
                                    $is_monogram = true;
                                }
                            }

                            if ($is_monogram)
                            {
                    //                                $price = $price + $monogramPrice;
                            }
                    // monogram end

                            /*
                             *  Convert Currency Rate
                             */
                            if ($currency)
                            {
                              //  $price = $price * $currency->value;
                            }

                            $subTotal = $price * $cartQuantity;

                            $allowIntlShipping = $product->intl_shipping;

                            $cnid = !empty($address->country_id) ? $address->country_id :2;
                            $checkIntlShippingStatus = Helper::checkIntlShippingStatus($cnid, $allowIntlShipping);

                            if (!$checkIntlShippingStatus)
                            {
                                $isDefault = false;
                                $record->updateShoppingCartDetailStatus(false);
                            }
                            else
                            {
                                $total += $subTotal;
                                $record->updateShoppingCartDetailStatus(true);
                            }

                            if ($user)
                            {
                                $record['monogram'] = $record;
                            }


//$productQuantity = $product->getProductQuantity($product->id);
                            $sizeId = $record['size_id'];

                            $colorId = $record['color_id'];
                            $size = Helper::getSize($sizeId, $language->id);
                            $color = Helper::getColorName($colorId, $language->id);

                            $productSize = false;
                            $productColor = false;

                            if ($size)
                            {
                                $productSize = $size->size_id;
                            }
                            if ($color)
                            {
                                $productColor = $color->color_id;
                            }

//$productQuantity = $product->getProductQuantity($product->id);
                            $productQuantity = Helper::getProductQuantity($product->id, $productSize ? 1 : 0, $sizeId, $productColor ? 1 : 0, $colorId);
                            ?>  
                            <div class="cart-item main-info cart_{{$id}}">
                                <div class="h-flex">
                                    <div class="prod-image">
                                        <a href="{{route('web.product.product_detail',['slug'=> $product->products_slug])}}">

                                            <?php
                                        $productImage2 = $product->getVariantThumbnailUrl($record['color_id'], $record['size_id']);
                                        ?>
                                            <img class="image_{{$id}}" src="{{asset(Storage::url($productImage2))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">

                                        </a>
                                        <div class="product-actions sm-hidden">
                                            <button type="button" class="btn btn-white favorite move_to_wishlist" data-href="{{route('web.wishlist.move_to_wishlist',['id'=> $product->id])}}" ><i class="icon-wishlist"></i>{{__('web.move_to_wishlist')}}</button>
                                            <button type="button" class="btn btn-white delete_trash"  data-page="cart_review" data-id="{{$id}}" data-intshipping="{{$checkIntlShippingStatus ==false ? 1 : 0}}" data-href="{{route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])}}"><i class="icon-trash"></i></button>
                                        </div>
                                        <div class="v-flex sm-visible">
                                            <div class="prod-name">
                                                @if(!$isDefault)
                                                <h3 class="strike">{{$productDetail->products_name}} {{ $style ? '('.$style.')': ''}} </h3>
                                                @else
                                                <h3>{{$productDetail->products_name}} {{ $style ? '('.$style.')': ''}} </h3>
                                                @endif
                                            </div>
                                            <div class="product-pricing-details">
                                                @if(!$isDefault)
                                                <span class="prod-price product_price_{{$id}} strike">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                                @else
                                                <span class="prod-price product_price_{{$id}}">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prod-details pd">
                                        <div class="h-flex sm-hidden">
                                            <div class="prod-name">
                                                @if(!$isDefault)
                                                <h3 class="strike">{{$productDetail->products_name}} {{ $style ? '('.$style.')': ''}}</h3>
                                                @else
                                                <h3>{{$productDetail->products_name}} {{ $style ? '('.$style.')': ''}}</h3>
                                                @endif
                                            </div>
                                            <div class="product-pricing-details">
                                                @if(!$isDefault)
                                                <span class="prod-price product_price_{{$id}} strike">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                                @else
                                                <span class="prod-price product_price_{{$id}}">{{Helper::formatNumber($subTotal)}} {{$currencyCode}}</span>
                                                @endif
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
                                            if ($user)
                                            {
//                                            dd($record->id);
                                                $record['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->toArray();
//                                            dd($record['monogram']);
                                            }

                                            if (count($record['monogram']) > 0)
                                            {
                                                $monocount = $record['monogram'];
                                                end($monocount);         // move the internal pointer to the end of the array
                                                $lastkey = key($monocount);
                                            }
                                            else
                                            {
                                                $lastkey = 1;
                                            }

                                            $min = $cartQuantity;
                                            $max = $productQuantity;
                                            $data_show = 5;
                                            if ($productQuantity < 5)
                                            {
                                                $data_show = $productQuantity;
                                            }
                                            ?>
                                            <div class="form-item">
                                                <label>{{__('web.qty')}}</label>
                                                <div class="counter counter2" data-maximum="{{$max}}" data-show="{{$data_show}}">
                                                    <div class="h-flex">
                                                        <button type="button" class="decrease decrease_cart" data-maximum="{{$max}}"  data-id="{{$id}}"  data-product="{{$id}}"><i class="icon-minus"></i></button>
                                                        <input type="text" name="quantity_{{$id}}" data-product="{{$id}}"  data-id="{{$id}}"  class="qty_add prod-count quantity number-input quantity_{{$id}}" value="{{$min}}">
                                                        <button type="button" class="increase increase_cart " data-maximum="{{$max}}"  data-id="{{$id}}"  data-product="{{$id}}"><i class="icon-plus"></i></button>
                                                    </div>
                                                    <!--<span class="remains"></span>-->
                                                   <!-- <span class="remains show" data-show="10">(Only 5 left)</span> -->

                                                </div>
                                            </div>
                                        </div>
                                        @if($has_monogram)

                                        <div id="mogogram_list" class="monogramSummary monogramSummary-{{$id}}" data-total="{{$lastkey}}">

                                            @foreach($record['monogram'] as $key =>  $monogramVal)
                                            <?php
                                            $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                            $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                            $colors = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                            $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                            $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                            if ($record['is_same'] == 1)
                                            {

                                                $total = $total + ($monogramPrice * $min);
                                            }
                                            else
                                            {
                                                $total = $total + $monogramPrice;
                                            }
//                                        
                                            ?>

                                            <div class="monogram-information  monogram-information-{{$id}}  {{count($cartproduct['monogram']) > 0 ? 'show' : 'hide' }} " id="monogramSummary-{{$id}}">
                                                <div class="h-flex">
                                                    <div class="h-flex">
                                                        <label>Selected Monogram</label>
                                                        <span class="monogram-price nowrap"> {{$monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''}}</span>
                                                    </div>
                                                    <span class="link-button edit_monogram2" data-productId="{{$product->id}}" data-id="{{$id}}"  data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-color="{{$color_id ? $color_id : 'c'}}"  data-size="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{$key}}">Edit</span>
                                                    <span class="link-button removeMonogram2" data-productid="{{$product->id}}" data-id="{{$id}}"  data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-colorId="{{$color_id ? $color_id : 'c'}}"  data-sizeId="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{$user ? $monogramVal['id'] : $key}}">Remove</span>
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
                                                            <div class="info"  id="placementInfo_{{$id}}_{{$key}}">{{$placement ?$placement->placement_desc:''}}</div>
                                                        </div>
                                                    </div>
                                                    <div class="separator underline underline_{{$id}}_{{$key}} {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}"></div>
                                                    <div class="comment comment_{{$id}}_{{$key}} {{ $record['monogram'] ? $monogramVal['monogram_note'] ? 'show' : 'hide' : ''  }}" >
                                                        <p>{{ $record['monogram'] ?  $monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:'': '' }} </p>
                                                    </div>

                                                </div>
                                            </div>
                                            @endforeach

                                            

                                        </div>
                                        <?php $hasmonogram = \App\Helpers\Helper::getMonogram($product->id); ?>
                                        @if($hasmonogram)
                                        <div class="addMonogramLink addMonogramLink_{{$id}}"  data-page="cart" data-productId="{{$product->id}}"  data-id="{{$id}}" data-colorexist="{{$color_id?1:0}}" data-sizeexist="{{$sizeId?1:0}}"  data-color="{{$color_id? $color_id : ''}}" data-size="{{$sizeId? $sizeId:''}}">
                                            <button type="button" <?php echo (int) $min == count($record['monogram']) || $record['is_same'] == 1 ? 'disabled="disabled"' : '' ?>  class="link-button" >
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
                                            <span class="link-button  addNote" id='addNote-{{$id}}'><i class="icon-plus"></i>{{__('web.add_note')}}</span>
                                            <i class="error"></i><div class="note-error form-item text-green custom-error2 note-success-{{$id}}"></div> 
                                            <div class="textarea_{{$id}} addNoteInput  ">
                                                <textarea name="note_{{$id}}" class="note_{{$id}}" maxlength="{{config('custom.order_note_length')}}">{{$record->note}}</textarea>
                                                <i class="error"></i><div style="width: 100%;" class="note-error form-item text-red custom-error2 note-error-{{$id}}"></div> 
                                                <div class="actions">
                                                    <span class="btn btn-primary save_note"  data-id="{{$id}}" >{{__('web.save')}}</span>
                                                    <span class="btn btn-secondary close-note" data-id="{{$id}}">{{__('web.Close')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product-actions sm-visible">
                                        <button type="button" class="btn btn-white favorite move_to_wishlist" data-href="{{route('web.wishlist.move_to_wishlist',['id'=> $product->id])}}" ><i class="icon-wishlist"></i>{{__('web.move_to_wishlist')}}</button>
                                        <button type="button" class="btn btn-white delete_trash" data-id="{{$id}}" data-page="cart_review" data-href="{{route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])}}"><i class="icon-trash"></i></button>
                                    </div>
                                </div>
                                @if(!$isDefault)
                                <?php
                                $intlShippingStatus = false;
                                ?>

                                <div class="alert-danger not_deliver">
                                    <i class="fa fa-times-circle"></i>
                                    <span>{{__('web.intl_ship_text')}}</span>
                                </div>

                                @endif
                                 @if($checkIntlShippingStatus == false)
                                <?php
                                 $intlShippingStatusCount ++;

                                ?>

                                @endif

                            </div>

                            <?php
                            $total_weight += $product_weight * $min;
                            ?>
                            @endforeach
                            
                            <input type="hidden" name="intlShippingStatusCount" value="{{$intlShippingStatusCount}}" id="intlShippingStatusCount">
                            <?php

                            $countryName = 'kuwait';
                           if(isset($address))
                           {
                               
                               $cnid =  !empty($address->country_id) ?$address->country_id :2;
                            $countryName = App\AdminModel\AdminCountryDescription::where('country_id', $cnid)->first();
                           $countryName = $countryName ? $countryName->country_name : 'kuwait';
                           }
                            $roundweight = ceil($total_weight);
                            if ($roundweight < 1 ||  strtolower($countryName) =='kuwait' )
                            {
                                $roundweight = 1;
                            }
                            $shippingCharge = $shippingCharges * $roundweight;


                            $setting = \App\Helpers\Helper::getSetting();
                            $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                            if (session('gift_wrap'))
                            {
                                $gift_wrap = session('gift_wrap');
                            }
                            $giftBoxSetting = $setting->firstWhere('setting_key', 'Gift Box');
                            $giftBoxAmount = $setting->firstWhere('setting_key', 'Gift Box Amount')->setting_val;
                            $giftBoxMinPriceVal = $setting->firstWhere('setting_key', 'Gift Box Min Amount')->setting_val;
                            $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                            if (session('gift_box'))
                            {
                                $gift_box = session('gift_box');
                            }
                            ?>
                            @if($setting['31']->setting_val ==1)

                            <?php
                            $giftAmount = $setting['32']->setting_val;
                            $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
                            if ($giftMinPrice)
                            {
                                $giftMinPrice = $giftMinPrice * $currency->value;
                            }


                            if ($total >= $giftMinPrice)
                            {
                                $giftAmount = 0;
                            }
                            ?>
                            <div class="cart-item main-info gift-wrap-item">
                                <div class="v-flex">
                                    <div class="gift-wrap-item-container">
                                        <div class="prod-image">
                                            <a href="#"><img src="{{ Helper::getGiftImage() }}"></a>
                                        </div>
                                        <div class="options v-flex">
                                            <div class="h-flex">
                                                <div class="checkbox">
                                                    <input type="checkbox" name="add_gift" id="gift_wrap"  {{$gift_wrap['is_gift'] == 1? 'checked': ''}} value="1">
                                                    <label>{{__('web.add_gift_wrap')}}</label>
                                                </div>
                                                @php
                                                    $giftDesc = trim(str_replace("\xc2\xa0", '', html_entity_decode(strip_tags($setting['34']->setting_val ?? ''))));
                                                @endphp
                                                @if ($giftDesc !== '')
                                                <span class="tt">
                                                    <i class="fa fa-info-circle"></i>
                                                    <span class="tt-text">{!! $setting['34']->setting_val !!}</span>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="prod-price gift-amount">{{$giftAmount > 0 ? Helper::formatNumber(($giftAmount * $currency->value) )  : '0.000'  }}  {{$currencyCode}}</div>

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
                            @if($giftBoxSetting->setting_val == 1)
                            <?php
                            $giftBoxMinPrice = $giftBoxMinPriceVal * $currency->value;
                            if ($total >= $giftBoxMinPrice)
                            {
                                $giftBoxAmount = 0;
                            }
                            ?>
                            <div class="cart-item main-info gift-wrap-item">
                                <div class="v-flex">
                                    <div class="gift-wrap-item-container">
                                        <div class="prod-image">
                                            @php
                                            @endphp
                                            <a href="#"><img src="{{ Helper::getGiftBoxImage() }}"></a>
                                        </div>
                                        <div class="options v-flex">
                                            <div class="h-flex">
                                                <div class="checkbox">
                                                    <input type="checkbox" name="add_gift_box" id="gift_box"  {{$gift_box['is_gift'] == 1? 'checked': ''}} value="1">
                                                    <label>{{__('web.add_gift_box')}}</label>
                                                </div>
                                                @php
                                                    $giftBoxDesc = trim(str_replace("\xc2\xa0", '', html_entity_decode(strip_tags($setting->firstWhere('setting_key', 'Gift Box Message')->setting_val ?? ''))));
                                                @endphp
                                                @if ($giftBoxDesc !== '')
                                                <span class="tt">
                                                    <i class="fa fa-info-circle"></i>
                                                    <span class="tt-text">{!! $setting->firstWhere('setting_key', 'Gift Box Message')->setting_val !!}</span>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="prod-price gift-box-amount">{{$giftBoxAmount > 0 ? Helper::formatNumber(($giftBoxAmount * $currency->value) )  : '0.000'  }}  {{$currencyCode}}</div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <?php
                    $discountApplied = ($total * $discount) / 100;

                    /*
                     * Coupon Code
                     */
                    $appliedCoupon = [];
                    $couponDiscountFinal = 0;

                    if ($user)
                    {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id']))
                        {
                            if ($appliedCoupon['user_id'] != $user->id)
                            {
                                $appliedCoupon = [];
                            }

                            if ($appliedCoupon)
                            {
                                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);
                                if (!$couponStatus['status'])
                                {
                                    $appliedCoupon = [];
                                }
                            }
                        }

                        if ($appliedCoupon)
                        {
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                            if ($couponDiscountIn && $couponDiscount)
                            {
                                if ($couponDiscountIn == 'P')
                                {
                                    $couponDiscountFinal = ($total * $couponDiscount) / 100;
                                }
                                else if ($couponDiscountIn == 'F')
                                {
                                    $couponDiscountFinal = $total - $couponDiscount;
                                }
                            }
                        }
                    }

                    /*
                     * End :: Coupon Code
                     */


                    /*
                     *  Convert Currency Rate
                     */
                    if ($currency)
                    {
                        $discountApplied = $discountApplied * $currency->value;
                        $couponDiscountFinal = $couponDiscountFinal;
                    }
                    $shippingCharge =  Helper::getShippingAmount($total,$shippingCharge);
					if($address->id == 43)
					{
					$shippingCharge =0;
					}
                    $finalTotal = $total + $shippingCharge + $taxValue - $discountApplied - $couponDiscountFinal;
                    ?>
                    <div class="shipping-address">
                        <div class="address-item selected">
                            <label class="contact">
                                <div class="contact-name">
                                    <span>{{$address->first_name}} {{$address->last_name}}</span>
                                    <span class="address-title">{{$address->address_title}}</span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address) !!}
                                </div>
                            </label>
                            <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="{{$address->id}}">{{__('web.change_address')}}</span>
                            <!--<a href="{{route('web.checkout.index')}}" ><span class="link-button ">{{__('web.change_address')}}</span></a>-->
                        </div>
                    </div>
                    <div class="billing-address">
                        <h3>{{__('web.billing_address')}}</h3>
                        <div class="clearfix">
                            @if($address->is_billing_address ==0)
                            <div class="address-item ">
                                <label class="contact">
                                    <div class="contact-address">
                                        {!! Helper::showAddress('my_address',$address,true) !!}
                                    </div>
                                </label>
                                <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="{{$address->id}}">{{__('web.edit_address')}}</span>
                                <!--<a href="{{route('web.checkout.index')}}" > <span class="link-button ">{{__('web.edit_address')}}</span></a>-->
                            </div>
                            @else

                            <p id="same-address">{{__('web.same_as_delivery_cart')}} <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="{{$address->id}}">{{__('web.change_address')}}</span></p>
                                                        <!--                            <p id="same-address">{{__('web.same_as_delivery_cart')}} 
                                                                                        <a href="{{route('web.checkout.index')}}" > <span class="link-button " >{{__('web.change_address')}}</span></a>
                                                                                    </p>-->
                            @endif
                        </div>
                    </div>
                    <div class="cart-summary sm-visible">
                        @if(config('custom_web.show_coupon_card'))
                        @include('web.checkout.coupon_card')
                        @endif
                        <div class="cart-details">
                            <h4>{{__('web.price_details')}}</h4>
                            <div class="v-flex">
                                <div class="total">
                                    <div class="item">{{__('web.sub_total')}}</div>
                                    <div class="item-price sub_total sub_total_price_detail"  data-subtotal="{{$total}}"> {{Helper::formatNumber($total)}} {{$currencyCode}}</div>
                                </div>
                                <div class="total">
                                    <div class="item">{{__('web.delivery_price')}}</div>
                                    <div class="item-price shipping-price">{{Helper::formatNumber($shippingCharge)}} {{$currencyCode}}</div>
                                </div>
                                @if($setting['31']->setting_val ==1)

                                <div class="total gift_wrap_item {{$gift_wrap['is_gift'] == 1? 'show': 'hide'}}" >
                                    <div class="item">{{__('web.gift_wrap')}}</div>
                                    <div class="item-price gift_wrap_price">{{$giftAmount > 0 ? Helper::formatNumber(($giftAmount  * $currency->value)) : '0.000' }} {{$currencyCode}}</div>
                                </div>
                                @endif
                                @if($giftBoxSetting->setting_val == 1)
                                <div class="total gift_box_item {{$gift_box['is_gift'] == 1? 'show': 'hide'}}" >
                                    <div class="item">{{__('web.gift_box')}}</div>
                                    <div class="item-price gift_box_price">{{$giftBoxAmount > 0 ? Helper::formatNumber(($giftBoxAmount  * $currency->value)) : '0.000' }} {{$currencyCode}}</div>
                                </div>
                                @endif
                                @if(config('custom_web.apply_discount') )
                                <?php
                                $coup = 'hide';
                                if (isset($appliedCoupon) && isset($appliedCoupon['user_id']))
                                {
                                    $coup = 'show';
                                }
                                ?>
                                <div class="total coupon-total {{$coup}}">
                                    <div class="item"><span class="coupon_name">{{isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '-'}}</span> <span class="link-button remove-coupon-code">Remove</span></div>
                                    <div class="item-price coupon-final-discount">{{Helper::formatNumber($couponDiscountFinal)}} {{$currencyCode}}</div>
                                </div>
                                @endif
                                <?php
                                $finalTotalWithoutgift = $finalTotal;
                                if ($setting['31']->setting_val == 1)
                                {
                                    if ($gift_wrap['is_gift'] == 1)
                                    {
                                        $finalTotal = $finalTotal + ($giftAmount * $currency->value);
                                    }
                                }
                                if ($giftBoxSetting->setting_val == 1)
                                {
                                    if ($gift_box['is_gift'] == 1)
                                    {
                                        $finalTotal = $finalTotal + ($giftBoxAmount * $currency->value);
                                    }
                                }
                                ?>
                                <div class="total grandtotal">
                                    <div class="item">{{__('web.grand_total')}}</div>
                                    <div class="item-price grand-total"  data-grand-total='{{Helper::formatNumber($finalTotalWithoutgift)}}'>{{Helper::formatNumber($finalTotal)}} {{$currencyCode}}</div>
                                </div>
                                <div class="page-actions sm-hidden">
                                    @if($intlShippingStatus)
                                    <button class="btn btn-primary buy_now">{{__('web.Place Order')}}</button>
                                    @else
                                    <button type="button" class="btn btn-primary" disabled>{{__('web.purchase_now')}}</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cart-products payment-gateway">
                        <div class="title">
                            <h3>{{__('web.select_payment_method')}}</h3>
                        </div>
                        <div class="clearfix">
                            <div class="options">
                                @foreach($paymentMethod as $record)
                                @if($record->id==4 && Auth::user()->id!=config('config.store_admin_id'))
                                @php
                                continue;
                                @endphp
                                @endif
                                <div class="radio ">
                                    <input type="radio" name="paymentMethod" class='paymentMethod' {{$record->is_default? 'checked' : ''}}  value="{{$record->id}}">
                                    <label class="contact">
                                        <span>{{$record->getDescription($language->id)?$record->getDescription($language->id)->payment_method_name:''}}</span>
                                        @if($record->id == 6)
                                        <img Style="height: 15px" src="{{asset('front/img/cc-mada.png')}}">
                                        @endif
                                        @if($record->id ==2)
                                        <img Style="height: 15px" src="{{asset('front/img/cc-master-card.png')}}">
                                        @endif
                                        @if($record->id ==1)
                                        <img Style="height: 15px" src="{{asset('front/img/cc-knet.png')}}">
                                        @endif
										@if($record->id ==5)
                                        <img Style="height: 33px" src="{{asset('front/img/digital.png')}}">
									   
                                        @endif
                                    </label>
                                </div>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
                <div class="cart-summary sm-hidden">
                    @if(config('custom_web.show_coupon_card'))
                    @include('web.checkout.coupon_card')
                    @endif
                    <div class="cart-details">
                        <h4>{{__('web.price_details')}}</h4>
                        <div class="v-flex">
                            <div class="total">
                                <div class="item">{{__('web.sub_total')}}</div>
                                <div class="item-price sub_total sub_total_price_detail" data-subtotal="{{$total}}">{{Helper::formatNumber($total)}} {{$currencyCode}}</div>
                            </div>

                            <div class="total">
                                <div class="item">{{__('web.delivery_price')}}</div>
                                <div class="item-price shipping-price">{{Helper::formatNumber($shippingCharge)}} {{$currencyCode}}</div>
                            </div>
                            @if($setting['31']->setting_val ==1)

                            <div class="total gift_wrap_item {{$gift_wrap['is_gift'] == 1? 'show': 'hide'}}" >
                                <div class="item">{{__('web.gift_wrap')}}</div>
                                <div class="item-price gift_wrap_price">{{$giftAmount > 0 ? Helper::formatNumber(($giftAmount * $currency->value)) : '0.000' }} {{$currencyCode}}</div>
                            </div>
                            @endif
                            @if($giftBoxSetting->setting_val == 1)
                            <div class="total gift_box_item {{$gift_box['is_gift'] == 1? 'show': 'hide'}}" >
                                <div class="item">{{__('web.gift_box')}}</div>
                                <div class="item-price gift_box_price">{{$giftBoxAmount > 0 ? Helper::formatNumber(($giftBoxAmount * $currency->value)) : '0.000' }} {{$currencyCode}}</div>
                            </div>
                            @endif

                            @if(config('custom_web.apply_discount') )
                            <?php
                            $coup = 'hide';
                            if (isset($appliedCoupon) && isset($appliedCoupon['user_id']))
                            {
                                $coup = 'show';
                            }
                            ?>
                            <div class="total coupon-total {{$coup}}">
                                <div class="item "><span class="coupon_name">{{isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '-'}}</span>    <span class="link-button remove-coupon-code">Remove</span></div>
                                <div class="item-price coupon-final-discount">{{Helper::formatNumber($couponDiscountFinal)}} {{$currencyCode}}</div>
                            </div>
                            @endif
                            <div class="total grandtotal">
                                <div class="item">{{__('web.grand_total')}}</div>
                                <div class="item-price grand-total" data-grand-total='{{Helper::formatNumber($finalTotalWithoutgift)}}' >{{Helper::formatNumber($finalTotal)}} {{$currencyCode}}</div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                @if($intlShippingStatus)
                <button type="submit" class="btn btn-primary buy_now">{{__('web.purchase_now')}}</button>
                @else
                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data">{{__('web.purchase_now')}}</button>
                @endif
            </div>
        </div>
    </section>
</form>
@section('page_specific_html')
@include('web.checkout.checkout_address')
<div class="psd" style="top: -19px; left: 0; display: none;">
    <img src="{{asset('front/img/checkout.jpg')}}">
</div>
@endsection

 @section('page_specific_js')

<script>

$(document).ready(function ()
{

	
 });	
</script>
    @endsection
@endsection





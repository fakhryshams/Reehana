@extends('web/layout/main')
@section('page_title',  trans('web.order_summary'))
@section('body_class',  'menubg')
@section('content')
<?php
$address = $order->getUserAddress();

$language = Helper::getLanguage();
$paymentMethod = Helper::getPaymentMethodByID($order->payment_method, $language->id);
$total = 0;
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
if ($order->currency_code)
{
    $currencyCode = $order->currency_code;
}
if ($order->currency_rate)
{
    $order->order_price = $order->order_price * $order->currency_rate;
    $order->coupon_discount_amount = $order->coupon_discount_amount * $order->currency_rate;
    $order->delivery_price = $order->delivery_price * $order->currency_rate;
    $order->discount = $order->discount * $order->currency_rate;
    $order->order_total = $order->order_total * $order->currency_rate;
}
$orderDetail = $order->getOrderDetail(true);
$deliveryTime = config('custom.default_delivery_time');
$country = $address->getUserCountry($language->id);
if ($country)
{
    $deliveryTime = $country->delivery_time;
}
?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">Order Confirmation</a></span>
    </div>
</section>
<section class="order">
    <div class="confirmation">
        <div class="container text-center">
            <p><img src="{{asset('front/img/icon-cart.svg')}}" width="40"></p>
            <p class="line1">{{__('web.thank_you_order_message')}}</p>
            <p class="line2">{{__('web.order_has_succ_placed')}}.</p>
            <?php $address = $order->getUserAddress(); ?>
            <p class="line3">{{__('web.copy_of_order_mail')}} {{$address->email}}. {{__('web.if_not_get_mail')}}.</p>
        </div>
        <div class="container">
            <div class="payment-info">
                <h4>{{__('web.payment_info')}}</h4>
                <p class="mont"><span>Order no.: #{{ $order->id}}</span> <span>Date: {{date("d M, Y", strtotime($order->created_at))}}</span></p>
                <div class="instruction">
                    <?php
                    $paymentResponse = $order->payment_response;
                    $paymentId = "";
                    if ($paymentResponse)
                    {
                        $paymentResponse = json_decode($paymentResponse);

                        $paymentId = isset($paymentResponse->PaymentID) ? $paymentResponse->PaymentID : '';
                    }
                    ?>
                    <div class="row">
                        <div class="{{$order->transaction_id? 'col-md-3' : ' col-md-6'}}">
                            <span>{{__('web.payment_method')}}</span>
                            <label>{{$paymentMethod}}</label>
                        </div>
                        @if($order->transaction_id)
                        <div class="col-md-3">
                            <span>{{__('web.payment_id')}}</span>
                            <label>{{$paymentId}}</label>
                        </div>

                        <div class="col-md-3">
                            <span>{{__('web.transaction_id')}}</span>
                            <label>{{$order->transaction_id ? $order->transaction_id : '-'}}</label>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <span>{{__('web.transaction_date_time')}}</span>
                            <label>{{date("d M Y h:i A", strtotime($order->created_at))}}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="prod-summary">
                <h4>Product Summary</h4>
                <div class="cart-table-container">
                    <table class="cart-list-table">
                        <tr class="sm-hidden">
                            <th>{{__('web.item')}}</th>
                            <th class="text-right">{{__('web.price')}}</th>
                            <th class="text-center">{{__('web.quantity')}}</th>
                            <th class="text-right">{{__('web.total')}}</th>
                        </tr>
                        <tr>
                            @foreach ($orderDetail as $record)
                            <?php
                            $product = $record->getProductDetail();
                            $productDetail = $product->getProductDescription($language->id);
                            $productDefaultImage = $product->getDefaultImage();
                            $sizeId = $record['size_id'];
                            $color_id = $record['color_id'];
                            $size = Helper::getSize($sizeId, $language->id);
                            $color = Helper::getColorName($color_id, $language->id);
                            $style = '';
                            $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);

                            if ($attributePrice)
                            {
                                $style = $attributePrice->style;
                            }

                            if ($order->currency_rate)
                            {
                                $record->product_price = $record->product_price * $order->currency_rate;
                                $record->total_price = $record->total_price * $order->currency_rate;
//                                $record->monogram_price = $record->monogram_price * $order->currency_rate;
                                $order->gift_wrap = $order->gift_wrap * $order->currency_rate;
                                $order->gift_box = $order->gift_box * $order->currency_rate;
                            }
                            ?>
                            <td>
                                <table>
                                    <tr>
                                        <td><div class="prod-img">
                                                <?php
                                            $productImage2 = $product->getVariantThumbnailUrl($record['color_id'], $record['size_id']);
                                            ?>
                                                <img src="{{asset(Storage::url($productImage2))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                            </div></td>
                                        <td>
                                            <div class="prod-name">{{$productDetail->products_name}} {{ $style ? '('.$style.')': ''}} </div>
                                            <div class="price mont sm-visible">  @if($record->is_active)
                                                {{Helper::formatNumber($record->total_price)}} {{$currencyCode}} 
                                                @else
                                                <s>{{Helper::formatNumber($record->total_price)}} {{$currencyCode}} </s>
                                                @endif</div>
                                            <div class="h-flex">
                                                <div class="sm-visible form-item">
                                                    <label>{{__('web.quantity')}}</label>
                                                    <span>{{$record->quantity}}</span>
                                                </div>
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
                                            </div>
                                            @if($record->is_monogram)
                                            <?php
                                            $monogramVal = App\UserModel\OrderMonogramDetail::where('order_detail_id', $record->id)->get()->toArray();
                                            ?>
                                            @foreach($monogramVal as $key =>  $monogramVal)
                                            <?php
                                            $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                            $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                            $color = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                            $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);

                                            if ($order->currency_rate)
                                            {

                                                $monogramVal['monogram_price'] = $monogramVal['monogram_price'] * $order->currency_rate;
                                            }
                                            ?>
                                            <div class="monogram-information">
                                                <div class="h-flex">
                                                    <div class="h-flex">
                                                        <label>Selected Monogram</label>
                                                        <span class="monogram-price nowrap">(+ {{Helper::formatNumber($monogramVal['monogram_price'])}}  {{$currencyCode}})</span>
                                                    </div>
                                                </div>
                                                <div class="border-panel">
                                                    <div class="h-flex">
                                                        <div class="monogram-options">
                                                            <label>Style</label>
                                                            <div class="info" id="styleInfo"><img src="{{asset(Storage::url($font->style_image_svg))}}"></div>
                                                        </div>
                                                        <div class="monogram-options">
                                                            <label>Initials</label>
                                                            <div class="info" id="alphabetsInfo">{{$initials}}</div>
                                                        </div>
                                                        <div class="monogram-options">
                                                            <label>Color</label>
                                                            <div class="info" id="colorInfo">
                                                                <span class="color-plate" style="background:  {{$color->color_hash}};"></span>
                                                                {{$color->color_name}}
                                                            </div>
                                                        </div>
                                                        <div class="monogram-options">
                                                            <label>Placement</label>
                                                            <div class="info" id="placementInfo">{{$placement->placement_desc}}</div>
                                                        </div>
                                                    </div>
                                                    <div class="separator underline {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}"></div>
                                                    @if($monogramVal['monogram_note'])
                                                    <div class="comment" id="monogramComment">
                                                        <p>{{$monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:''}} </p>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                            @if($record->is_same == 1)
                                            <div class="applyMonogramStyles" >
                                                <div class="checkbox">
                                                    <input type="checkbox" name="applyAll" checked="checked" disabled="disabled" >
                                                    <label>Applied same Monogram Styles to all quantities.</label>
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                            @if($record->note)

                                            <div class="note">Delivery Note: {{$record->note}}</div>
                                            @endif

                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="text-right font16 sm-hidden">@if($record->is_active)
                                {{Helper::formatNumber($record->product_price)}} {{$currencyCode}}
                                @else
                                <s>{{Helper::formatNumber($record->product_price)}} {{$currencyCode}}</s>
                                @endif</td>
                            <td class="text-center font16 sm-hidden">{{$record->quantity}}</td>
                            <td class="text-right font16 sm-hidden">   @if($record->is_active)
                                {{Helper::formatNumber($record->total_price)}} {{$currencyCode}} 
                                @else
                                <s>{{Helper::formatNumber($record->total_price)}} {{$currencyCode}} </s>
                                @endif</td>
                        </tr>
                        @endforeach

                        @if($order->is_gift == 1)
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td><div class="prod-img"><img src="{{Helper::getGiftImage()}}"></div></td>
                                        <td class="gift-wrap">
                                            <div class="prod-name">{{__('web.gift_wrap')}}</div>
                                            <div class="price mont sm-visible">{{Helper::formatNumber($order->gift_wrap)}} {{$currencyCode}}</div>
                                            @if($order->gift_message  ||$order->gift_title )
                                            <div class="v-flex">
                                                @if($order->gift_title)
                                                <div class="form-item">
                                                    <label>{{__('web.title')}}</label>
                                                    <span>{{$order->gift_title}}</span>
                                                </div>
                                                @endif
                                                @if($order->gift_message)
                                                <div class="form-item">
                                                    <label>{{__('web.message')}}</label>
                                                    <span>{{$order->gift_message}}</span>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_wrap)}} {{$currencyCode}}</td>
                            <td class="text-center font16 sm-hidden">1</td>
                            <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_wrap)}} {{$currencyCode}}</td>
                        </tr>
                        @endif

                        @if($order->is_box == 1)
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td><div class="prod-img"><img src="{{Helper::getGiftBoxImage()}}"></div></td>
                                        <td class="gift-wrap">
                                            <div class="prod-name">{{__('web.gift_box')}}</div>
                                            <div class="price mont sm-visible">{{Helper::formatNumber($order->gift_box)}} {{$currencyCode}}</div>
                                            @if($order->gift_box_message  ||$order->gift_box_title )
                                            <div class="v-flex">
                                                @if($order->gift_box_title)
                                                <div class="form-item">
                                                    <label>{{__('web.title')}}</label>
                                                    <span>{{$order->gift_box_title}}</span>
                                                </div>
                                                @endif
                                                @if($order->gift_box_message)
                                                <div class="form-item">
                                                    <label>{{__('web.message')}}</label>
                                                    <span>{{$order->gift_box_message}}</span>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_box)}} {{$currencyCode}}</td>
                            <td class="text-center font16 sm-hidden">1</td>
                            <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_box)}} {{$currencyCode}}</td>
                        </tr>
                        @endif

                    </table>
                </div>
            </div>
            <div class="order-details clearfix">
                <div class="w-33">
                    <div class="shipping-summary">
                        <h4>{{__('web.shipping_details')}}</h4>
                        <div class="address-item">
                            <label class="contact">
                                <div class="contact-name">
                                    <span>{{ucfirst($address->first_name)}} {{ucfirst($address->last_name)}}</span>
                                    <span class="address-title">{{ucfirst($address->address_title)}}</span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address) !!}
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="w-33">
                    <div class="billing-summary">
                        <h4>{{__('web.billing_details')}}</h4>
                        <div class="address-item selected">
                            <label class="contact">
                                @if($address->is_billing_address == 0)
                                <div class="contact-name">
                                    <span>
                                        {{ucfirst($address->billing_first_name)}} {{ucfirst($address->billing_last_name)}}
                                    </span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address,true) !!}
                                </div>
                                @else
                                <div class="contact-name">
                                    <span>
                                        {{$address->first_name}} {{$address->last_name}}
                                    </span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address) !!}
                                </div>
                                @endif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="w-33">
                    <h4>{{__('web.order_summary')}} <span>({{count($orderDetail)}}  {{__('web.items')}})</span></h4>
                    <div class="order-summary">
                        <div class="cart-details">
                            <div class="v-flex">
                                <div class="total">
                                    <div class="item">{{__('web.sub_total')}}</div>
                                    <?php
                                    $gift_wrap = $order->gift_wrap ? $order->gift_wrap : 0;
                                    $gift_box = $order->gift_box ? $order->gift_box : 0;
                                    $subtotal = $gift_wrap + $gift_box + $order->order_price;
                                    ?>
                                    <div class="item-price">{{Helper::formatNumber($subtotal)    }} {{$currencyCode}}</div>
                                </div>
                                <div class="total">
                                    <div class="item">{{__('web.shipping')}}</div>
                                    <div class="item-price">{{Helper::formatNumber($order->delivery_price)}} {{$currencyCode}}</div>
                                </div>
                                @if($order->coupon_discount_amount)
                                <div class="total">
                                    <div class="item">{{$order->coupon_code}} </div>
                                    <div class="item-price">- {{Helper::formatNumber($order->coupon_discount_amount)}} {{$currencyCode}}</div>
                                </div>
                                @endif
                                <div class="total grandtotal">
                                    <div class="item">{{__('web.total_amount')}}</div>
                                    <div class="item-price">{{Helper::formatNumber($order->order_total)}} {{$currencyCode}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary"> <a  href="{{route('web.user.my_order')}}" style="color: #000000 !important">     {{__('web.go_to_myorders')}}</a></button>
                <button class="btn btn-primary print_summary" data-href="{{route('web.checkout.print-order',['id'=>$order->id,'page'=>'summary'])}}" id="print">{{__('web.print_summary')}}</button>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_html')
@endsection

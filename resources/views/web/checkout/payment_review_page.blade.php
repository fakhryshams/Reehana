<html lang="{{Helper::getLanguage() ? Helper::getLanguage()->code : 'en'}}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="generator" content="{{Helper::getAppName() ? Helper::getAppName() : ''}}">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="{{asset('front/img/favicon.png')}}" type="image/x-icon">
    <meta name="description" content="{{Helper::getAppName() ? Helper::getAppName() : ''}}">
    @yield('page_meta')
    <link rel="manifest" href="{{asset('front/css/manifest.json')}}">

    <title>{{Helper::getAppName() ? Helper::getAppName() . " | " : ''}} @yield('page_title')</title>
    <link rel="stylesheet" href="{{asset('front/css/bootstrap-reboot.min.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/fontawesome.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/slick.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/cloudzoom.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/media.css')}}">
    <style>
        .order-details {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
            gap: 30px !important;
            margin-top: 24px !important;
            margin-bottom: 24px !important;
        }
        .order-details > .w-33,
        .order-details > div {
            flex: 1 1 calc(50% - 15px) !important;
            max-width: calc(50% - 15px) !important;
            min-width: 280px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            align-self: stretch !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        .order-details .billing-summary,
        .order-details .shipping-summary,
        .order-details .summary-details {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }
        .order-details .billing-summary > h4,
        .order-details .billing-summary h4,
        .order-details .shipping-summary > h4,
        .order-details .shipping-summary h4,
        .order-details .summary-details > h4,
        .order-details .summary-details h4,
        .order-details > .w-33 > h4,
        .order-details > div > h4 {
            margin-top: 0 !important;
            margin-bottom: 14px !important;
            flex: 0 0 auto !important;
            line-height: 1.2 !important;
            font-size: 20px !important;
        }
        .order-details .billing-summary .address-item,
        .order-details .shipping-summary .address-item {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #d7d7d7 !important;
            /* background: #f0f0f0 !important; */
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .billing-summary .address-item .contact,
        .order-details .shipping-summary .address-item .contact {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 15px 20px !important;
            box-sizing: border-box !important;
            cursor: default !important;
        }
        .order-details .billing-summary .address-item .contact .contact-name,
        .order-details .shipping-summary .address-item .contact .contact-name {
            flex: 0 0 auto !important;
            margin-bottom: 8px !important;
            padding: 0 !important;
        }
        .order-details .billing-summary .address-item .contact .contact-address,
        .order-details .shipping-summary .address-item .contact .contact-address {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        .order-details .order-summary {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #d7d7d7 !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .order-summary .cart-details {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 15px 20px !important;
            border: none !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .order-summary .cart-details .v-flex {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        @media screen and (max-width: 767px) {
            .order-details > .w-33,
            .order-details > div {
                flex: 1 1 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
            }
        }
    </style>
</head>
<?php
$address = $order->getUserAddress();
$language = Helper::getLanguage();
$paymentMethod = Helper::getPaymentMethodByID($order->payment_method, $language->id);
$total = 0;
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
if ($order->currency_code) {
    $currencyCode = $order->currency_code;
}
// if ($order->currency_rate) {
//     $order->order_price = $order->order_price * $order->currency_rate;
//     $order->coupon_discount_amount = $order->coupon_discount_amount * $order->currency_rate;
//     $order->delivery_price = $order->delivery_price * $order->currency_rate;
//     $order->discount = $order->discount * $order->currency_rate;
//     $order->order_total = $order->order_total * $order->currency_rate;
// }
$orderDetail = $order->getOrderDetail(true);
$deliveryTime = config('custom.default_delivery_time');
$country = $address ? $address->getUserCountry($language->id) : null;
if ($country) {
    $deliveryTime = $country->delivery_time;
}
?>

<body class="menubg">
    <img style=" display: block; margin-top: 10px; margin-bottom: 40px; margin-left: auto;margin-right: auto;  width: 245px; height: 56px;"
        src="{{asset('front/img/logo.png')}}">
    <div class="">
        <section class="order">
            <div class="confirmation">
                <div class="container text-center">
                    <p><img src="{{asset('front/img/icon-cart.svg')}}" width="40"></p>
                    <p class="line1">{{__('web.payment_review')}}</p>
                    <p class="line2">{{__('web.please_review_your_order_details_below_before_proceeding_with_payment')}}</p>
                </div>
                <div class="container">
                    <div class="payment-info">
                        <h4>{{__('web.payment_info')}}</h4>
                        <p class="mont"><span>Order no.: #{{ $order->id}}</span> <span>Date:
                                {{date("d M, Y", strtotime($order->created_at))}}</span></p>
                        <div class="instruction">
                            <?php
$paymentResponse = $order->payment_response;
$paymentId = "";
if ($paymentResponse) {
    $paymentResponse = json_decode($paymentResponse);

    $paymentId = isset($paymentResponse->PaymentID) ? $paymentResponse->PaymentID : (isset($paymentResponse->payment_id) ? $paymentResponse->payment_id : '');
}
                    ?>
                            <div class="row">
                                <div class="{{$order->transaction_id ? 'col-md-3' : ' col-md-6'}}">
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
                                    <span>{{__('web.date_time')}}</span>
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

                                        if ($attributePrice) {
                                            $style = $attributePrice->style;
                                        }

                                        if ($order->currency_rate) {
                                            $record->product_price = $record->product_price;
                                            $record->total_price = $record->total_price;
                                            //                                $record->monogram_price = $record->monogram_price * $order->currency_rate;
                                            $order->gift_wrap = $order->gift_wrap;
                                        }
                                                                ?>
                                                                        <td>
                                                                            <table>
                                                                                <tr>
                                                                                    <td>
                                                                                        <div class="prod-img">
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
                                            $productImage2 = $productDefaultImage->image_url;
                                        } else {
                                            $productImage2 = $productImage2->image_url;
                                        }
                                                                                    ?>
                                                                                            <img src="{{asset(Storage::url($productImage2))}}"
                                                                                                onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <div class="prod-name">{{$productDetail->products_name}}
                                                                                            {{ $style ? '(' . $style . ')' : ''}} </div>
                                                                                        <div class="price mont sm-visible"> @if($record->is_active)
                                                                                            {{Helper::formatNumber($record->total_price)}} {{$currencyCode}}
                                                                                        @else
                                                                                                <s>{{Helper::formatNumber($record->total_price)}}
                                                                                                    {{$currencyCode}} </s>
                                                                                            @endif
                                                                                        </div>
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
                                                                                                                                        @foreach($monogramVal as $key => $monogramVal)
                                                                                                                                                                                    <?php
                                                                                                                                            $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                                                                                                                            $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                                                                                                                            $color = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                                                                                                                            $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);

                                                                                                                                            if ($order->currency_rate) {

                                                                                                                                                $monogramVal['monogram_price'] = $monogramVal['monogram_price'] * $order->currency_rate;
                                                                                                                                            }
                                                                                                                                                                            ?>
                                                                                                                                                                                    <div class="monogram-information">
                                                                                                                                                                                        <div class="h-flex">
                                                                                                                                                                                            <div class="h-flex">
                                                                                                                                                                                                <label>Selected Monogram</label>
                                                                                                                                                                                                <span class="monogram-price nowrap">(+
                                                                                                                                                                                                    {{Helper::formatNumber($monogramVal['monogram_price'])}}
                                                                                                                                                                                                    {{$currencyCode}})</span>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        </div>
                                                                                                                                                                                        <div class="border-panel">
                                                                                                                                                                                            <div class="h-flex">
                                                                                                                                                                                                <div class="monogram-options">
                                                                                                                                                                                                    <label>Style</label>
                                                                                                                                                                                                    <div class="info" id="styleInfo"><img
                                                                                                                                                                                                            src="{{asset(Storage::url($font->style_image_svg))}}">
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                </div>
                                                                                                                                                                                                <div class="monogram-options">
                                                                                                                                                                                                    <label>Initials</label>
                                                                                                                                                                                                    <div class="info" id="alphabetsInfo">{{$initials}}
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                </div>
                                                                                                                                                                                                <div class="monogram-options">
                                                                                                                                                                                                    <label>Color</label>
                                                                                                                                                                                                    <div class="info" id="colorInfo">
                                                                                                                                                                                                        <span class="color-plate"
                                                                                                                                                                                                            style="background:  {{$color->color_hash}};"></span>
                                                                                                                                                                                                        {{$color->color_name}}
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                </div>
                                                                                                                                                                                                <div class="monogram-options">
                                                                                                                                                                                                    <label>Placement</label>
                                                                                                                                                                                                    <div class="info" id="placementInfo">
                                                                                                                                                                                                        {{$placement->placement_desc}}</div>
                                                                                                                                                                                                </div>
                                                                                                                                                                                            </div>
                                                                                                                                                                                            <div
                                                                                                                                                                                                class="separator underline {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}">
                                                                                                                                                                                            </div>
                                                                                                                                                                                            @if($monogramVal['monogram_note'])
                                                                                                                                                                                                <div class="comment" id="monogramComment">
                                                                                                                                                                                                    <p>{{$monogramVal['monogram_note'] ? "Comment: " . $monogramVal['monogram_note'] : ''}}
                                                                                                                                                                                                    </p>
                                                                                                                                                                                                </div>
                                                                                                                                                                                            @endif
                                                                                                                                                                                        </div>
                                                                                                                                                                                    </div>
                                                                                                                                        @endforeach
                                                                                                                                        @if($record->is_same == 1)
                                                                                                                                            <div class="applyMonogramStyles">
                                                                                                                                                <div class="checkbox">
                                                                                                                                                    <input type="checkbox" name="applyAll" checked="checked"
                                                                                                                                                        disabled="disabled">
                                                                                                                                                    <label>Applied same Monogram Styles to all
                                                                                                                                                        quantities.</label>
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
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center font16 sm-hidden">{{$record->quantity}}</td>
                                                                        <td class="text-right font16 sm-hidden"> @if($record->is_active)
                                                                            {{Helper::formatNumber($record->total_price)}} {{$currencyCode}}
                                                                        @else
                                                                                <s>{{Helper::formatNumber($record->total_price)}} {{$currencyCode}} </s>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                    @endforeach

                                @if($order->is_gift == 1)
                                    <?php
                                    $setting = \App\Helpers\Helper::getSetting();
                                    $giftImageSetting = $setting->firstWhere('setting_key', 'Gift Wrap Image');
                                    $giftImageUrl = $giftImageSetting && $giftImageSetting->setting_val
                                        ? asset(Storage::url($giftImageSetting->setting_val))
                                        : asset('front/img/giftwrap.png');
                                    ?>
                                    <tr>
                                        <td>
                                            <table>
                                                <tr>
                                                    <td>
                                                        <div class="prod-img"><img
                                                                src="{{$giftImageUrl}}"></div>
                                                    </td>
                                                    <td class="gift-wrap">
                                                        <div class="prod-name">{{__('web.gift_wrap')}}</div>
                                                        <div class="price mont sm-visible">
                                                            {{Helper::formatNumber($order->gift_wrap)}} {{$currencyCode}}
                                                        </div>
                                                        @if($order->gift_message || $order->gift_title)
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
                                        <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_wrap)}}
                                            {{$currencyCode}}</td>
                                        <td class="text-center font16 sm-hidden">1</td>
                                        <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_wrap)}}
                                            {{$currencyCode}}</td>
                                    </tr>
                                @endif

                                @if($order->is_gift_box == 1)
                                    <?php
                                    $setting = \App\Helpers\Helper::getSetting();
                                    $giftBoxImageSetting = $setting->firstWhere('setting_key', 'Gift Box Image');
                                    $giftBoxImageUrl = $giftBoxImageSetting && $giftBoxImageSetting->setting_val
                                        ? asset(Storage::url($giftBoxImageSetting->setting_val))
                                        : asset('front/img/giftwrap.png');
                                    ?>
                                    <tr>
                                        <td>
                                            <table>
                                                <tr>
                                                    <td>
                                                        <div class="prod-img"><img
                                                                src="{{ $giftBoxImageUrl }}"></div>
                                                    </td>
                                                    <td class="gift-wrap">
                                                        <div class="prod-name">{{__('web.gift_box')}}</div>
                                                        <div class="price mont sm-visible">
                                                            {{Helper::formatNumber($order->gift_box)}} {{$currencyCode}}
                                                        </div>
                                                        @if($order->gift_box_message || $order->gift_box_title)
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
                                        <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_box)}}
                                            {{$currencyCode}}</td>
                                        <td class="text-center font16 sm-hidden">1</td>
                                        <td class="text-right font16 sm-hidden">{{Helper::formatNumber($order->gift_box)}}
                                            {{$currencyCode}}</td>
                                    </tr>
                                @endif


                            </table>
                        </div>
                    </div>
                    <?php
$childOrders = \App\UserModel\Order::where('parent_id', $order->id)->get();
            ?>
                    <div class="order-details clearfix">
                        <div class="w-33">
                            <div class="shipping-summary">
                                <h4>{{__('web.shipping_details')}}</h4>
                                <div class="address-item">
                                    <label class="contact">
                                        <div class="contact-name">
                                            <span>{{ucfirst($address->first_name)}}
                                                {{ucfirst($address->last_name)}}</span>
                                            <span class="address-title">{{ucfirst($address->address_title)}}</span>
                                        </div>
                                        <div class="contact-address">
                                            {!! Helper::showAddress('my_address', $address) !!}
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
                                                    {{ucfirst($address->billing_first_name)}}
                                                    {{ucfirst($address->billing_last_name)}}
                                                </span>
                                            </div>
                                            <div class="contact-address">
                                                {!! Helper::showAddress('my_address', $address, true) !!}
                                            </div>
                                        @else
                                            <div class="contact-name">
                                                <span>
                                                    {{$address->first_name}} {{$address->last_name}}
                                                </span>
                                            </div>
                                            <div class="contact-address">
                                                {!! Helper::showAddress('my_address', $address) !!}
                                            </div>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="w-33">
                            <h4>{{__('web.order_summary')}} <span>({{count($orderDetail)}} {{__('web.items')}})</span>
                            </h4>
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
                                            <div class="item-price">{{Helper::formatNumber($subtotal) }}
                                                {{$currencyCode}}</div>
                                        </div>
                                        <div class="total">
                                            <div class="item">{{__('web.shipping')}}</div>
                                            <div class="item-price">{{Helper::formatNumber($order->delivery_price)}}
                                                {{$currencyCode}}</div>
                                        </div>
                                        @if($order->coupon_discount_amount)
                                            <div class="total">
                                                <div class="item">{{$order->coupon_code}} </div>
                                                <div class="item-price">-
                                                    {{Helper::formatNumber($order->coupon_discount_amount)}}
                                                    {{$currencyCode}}</div>
                                            </div>
                                        @endif
                                        <div class="total grandtotal">
                                            <div class="item">{{__('web.total_amount')}}</div>
                                            <div class="item-price">{{Helper::formatNumber($order->order_total)}}
                                                {{$currencyCode}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($orderCustom) && !empty($orderCustom))
                        @if($orderCustom->custom_order_type === "purchase_order")
                                        <div class="page-actions mt-4"></div>
                                        <div class="container">
                                            <div class="payment-info">
                                                <h4>{{__('web.payment_info')}}</h4>
                                                <p class="mont">
                                                    <span>{{__('web.po_no')}}.: #{{ $orderCustom->id}}</span>
                                                    <span>Date: {{date("d M, Y", strtotime($orderCustom->created_at))}}</span>
                                                </p>
                                                <div class="instruction">
                                                    <?php
                            $paymentResponse = $orderCustom->payment_response;
                            $paymentId = "";
                            if ($paymentResponse) {
                                $paymentResponse = json_decode($paymentResponse);

                                $paymentId = isset($paymentResponse->PaymentID) ? $paymentResponse->PaymentID : (isset($paymentResponse->payment_id) ? $paymentResponse->payment_id : '');
                            }
                                            ?>
                                                    <div class="row">
                                                        <div class="{{$orderCustom->transaction_id ? 'col-md-3' : ' col-md-6'}}">
                                                            <span>{{__('web.payment_method')}}</span>
                                                            <label>{{$paymentMethod}}</label>
                                                        </div>
                                                        @if($orderCustom->transaction_id)
                                                            <div class="col-md-3">
                                                                <span>{{__('web.payment_id')}}</span>
                                                                <label>{{$paymentId}}</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <span>{{__('web.transaction_id')}}</span>
                                                                <label>{{$orderCustom->transaction_id ? $orderCustom->transaction_id : '-'}}</label>
                                                            </div>
                                                        @endif
                                                        <div class="col-md-3">
                                                            <span>{{__('web.date_time')}}</span>
                                                            <label>{{date("d M Y h:i A", strtotime($orderCustom->created_at))}}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                            $conversationRate = 1;
                            if ($currency && $orderCustom->currency_code) {
                                $currency->code = $orderCustom->currency_code;
                                $conversationRate = $orderCustom->currency_rate;
                            } else {
                                $orderCustom->currency_code = config('custom.currency_convert_default_country');
                                $currency->code = config('custom.currency_convert_default_country');
                            }
                                    ?>
                                            <div class="prod-summary">
                                                <h4>{{ __('web.customized_items') }}</h4>
                                                <div class="cart-table-container">
                                                    <table class="cart-list-table">
                                                        <tr class="sm-hidden">
                                                            <th class="text-center" style="width: 90px;">{{ __('admin.image') }}</th>
                                                            <th>{{__('web.item')}}</th>
                                                            <th class="text-right">{{__('web.price')}}</th>
                                                            <th class="text-center">{{__('web.quantity')}}</th>
                                                            <th class="text-right">{{__('web.total')}}</th>
                                                        </tr>
                                                        <?php        $productPrice = []; ?>
                                                        @forelse($orderProductDetail as $recordIndex => $record)
                                                                                    <?php
                                                            $pricetotal = $record->quantity * $record->product_price;
                                                            $customAttributes = [];
                                                            if (!empty($record->custom_attributes)) {
                                                                $customAttributes = json_decode($record->custom_attributes, true);
                                                                if (!is_array($customAttributes))
                                                                    $customAttributes = [];
                                                            }
                                                            $productName = Helper::formatCustomizedTitle(
                                                                $record->product_name
                                                                ?: ($customAttributes['item_code'] ?? null)
                                                                ?: ($customAttributes['description'] ?? null)
                                                                ?: 'Custom Item'
                                                            );
                                                            $customOrderDetails = json_decode($orderCustom->custom_order_details, true);
                                                            $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                                                            $itemImage = $itemImage ? preg_replace('/^public\//', '', $itemImage) : null;
                                                            
                                                                            ?>
                                                                                    <tr>
                                                                                        <td class="text-center align-middle" style="width: 90px; vertical-align: middle;">
                                                                                            <a href="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" target="_blank" style="display: inline-flex; justify-content: center; align-items: center;">
                                                                                                <img src="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" alt="Reference Image" style="width:56px; height:56px; object-fit:cover; border:1px solid #ddd; border-radius:6px; padding:2px;" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                                                                            </a>
                                                                                        </td>
                                                                                        <td>
                                                                                            <div class="prod-name">{{$productName}}</div>
                                                                                            <div class="price mont sm-visible"> @if($record->is_active)
                                                                                                {{Helper::formatNumber($record->total_price)}} {{$currency->code}}
                                                                                            @else
                                                                                                    <s>{{Helper::formatNumber($record->total_price)}} {{$currency->code}}
                                                                                                    </s>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="h-flex">
                                                                                                <div class="sm-visible form-item">
                                                                                                    <label>{{__('web.quantity')}}</label>
                                                                                                    <span>{{$record->quantity}}</span>
                                                                                                </div>
                                                                                                @if(count($customAttributes) > 0)
                                                                                                    @foreach($customAttributes as $key => $value)
                                                                                                        @if($key === 'image')
                                                                                                        @continue
                                                                                                        @endif
                                                                                                        @if(isset($value) && !empty($value))
                                                                                                            <div class="form-item">
                                                                                                                <label>{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                                                                                                <span>{{ $key === 'description' ? Helper::formatCustomizedDescription($value) : $value }}</span>
                                                                                                            </div>
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                @endif
                                                                                            </div>
                                                                                        </td>
                                                                                        <td class="text-right">
                                                                                            <span>{{Helper::formatNumber($record->product_price)}}
                                                                                                {{$currency->code}}</span>
                                                                                        </td>
                                                                                        <td class="text-center">
                                                                                            <span>{{$record->quantity}}</span>
                                                                                        </td>
                                                                                        <td class="text-right">
                                                                                            <span>{{Helper::formatNumber($record->total_price)}}
                                                                                                {{$currency->code}}</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <?php            if ($record->is_active) {
                                                                $productPrice[] = $pricetotal;
                                                            } ?>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No items found.</td>
                                                            </tr>
                                                        @endforelse
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="order-details clearfix">
                                                <!-- <div class="w-33">
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
                                        </div> -->
                                                <?php
                            $customOrderDetails = json_decode($orderCustom->custom_order_details, true);
                            $customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
                                        ?>
                                                <div class="w-33">
                                                    <div class="billing-summary">
                                                        <h4>{{__('web.billing_details')}}</h4>
                                                        <div class="address-item selected">
                                                            <label class="contact">
                                                                @if($customerInfo)
                                                                                                    <div class="contact-name">
                                                                                                        <span>
                                                                                                            {{ isset($customerInfo['name']) ? ucfirst($customerInfo['name']) : '' }}
                                                                                                        </span>
                                                                                                    </div>
                                                                                                    <div class="contact-address">
                                                                                                        <address>
                                                                                                            <?php
                                                                    $cust = $customerInfo;
                                                                    // Country name lookup
                                                                    $countryName = '';
                                                                    if (isset($cust['country_id']) && $cust['country_id']) {
                                                                        $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                                                                        if ($cn)
                                                                            $countryName = $cn->country_name;
                                                                    }
                                                                                                    ?>
                                                                                                            @if(isset($cust['block']) && $cust['block'])
                                                                                                                <div><strong>Appartment :</strong> {{ e($cust['block']) }}</div>
                                                                                                            @endif
                                                                                                            @if(isset($cust['floor']) && $cust['floor'])
                                                                                                            <div><strong>Floor :</strong> {{ e($cust['floor']) }}</div>@endif
                                                                                                            @if(isset($cust['house_number']) && $cust['house_number'])
                                                                                                                <div><strong>House Number :</strong> {{ e($cust['house_number']) }}
                                                                                                            </div>@endif
                                                                                                            @if(isset($cust['city']) && $cust['city'])
                                                                                                                <div><strong>City :</strong> {{ e(ucfirst($cust['city'])) }}</div>
                                                                                                            @endif
                                                                                                            @if($countryName)
                                                                                                            <div><strong>Country :</strong> {{ e($countryName) }}</div>@endif
                                                                                                            @if(isset($cust['address_detail']) && $cust['address_detail'])
                                                                                                                <div><strong>Address Detail :</strong>
                                                                                                            {{ e($cust['address_detail']) }}</div>@endif
                                                                                                            <strong>{{__('admin.email')}} :
                                                                                                            </strong>{{ $customerInfo['email'] ?? '' }}<br>
                                                                                                            <strong>{{__('admin.phone')}} :
                                                                                                            </strong>{{ $customerInfo['phone'] ?? '' }}<br>
                                                                                                        </address>
                                                                                                    </div>
                                                                @elseif($address->is_billing_address == 0)
                                                                    <div class="contact-name">
                                                                        <span>
                                                                            {{ucfirst($address->billing_first_name)}}
                                                                            {{ucfirst($address->billing_last_name)}}
                                                                        </span>
                                                                    </div>
                                                                    <div class="contact-address">
                                                                        {!! Helper::showAddress('my_address', $address, true) !!}
                                                                    </div>
                                                                @else
                                                                    <div class="contact-name">
                                                                        <span>
                                                                            {{$address->first_name}} {{$address->last_name}}
                                                                        </span>
                                                                    </div>
                                                                    <div class="contact-address">
                                                                        {!! Helper::showAddress('my_address', $address) !!}
                                                                    </div>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="w-33">
                                                    <h4>{{__('web.po_summary')}} <span>({{count($orderProductDetail)}}
                                                            {{__('web.items')}})</span></h4>
                                                    <div class="order-summary">
                                                        <div class="cart-details">
                                                            <div class="v-flex">
                                                                <div class="total">
                                                                    <div class="item">{{__('web.sub_total')}}</div>
                                                                    <?php
                            $gift_wrap = $orderCustom->gift_wrap ? $orderCustom->gift_wrap : 0;
                            $gift_box = $orderCustom->gift_box ? $orderCustom->gift_box : 0;
                            $subtotal = $gift_wrap + $gift_box + $orderCustom->order_price;
                                                            ?>
                                                                    <div class="item-price">{{Helper::formatNumber($subtotal) }}
                                                                        {{$currencyCode}}</div>
                                                                </div>
                                                                @if($orderCustom->delivery_price > 0)
                                                                    <div class="total">
                                                                        <div class="item">{{__('web.shipping_charges')}}</div>
                                                                        <div class="item-price">
                                                                            {{Helper::formatNumber($orderCustom->delivery_price)}}
                                                                            {{$currencyCode}}</div>
                                                                    </div>
                                                                @endif
                                                                @if($orderCustom->discount > 0)
                                                                    <div class="total">
                                                                        <div class="item">
                                                                            {{ __('web.discount') }}
                                                                            ({{ ucfirst($orderCustom->discount_type) }}@if($orderCustom->discount_type === 'percentage' && $orderCustom->discount > 0){{" - " . $orderCustom->discount_per}}%@endif)
                                                                        </div>
                                                                        <div class="item-price">
                                                                            - {{ Helper::formatNumber($orderCustom->discount) }}
                                                                            {{ $currencyCode }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                                <div class="total grandtotal">
                                                                    <div class="item">{{__('web.total_amount')}}</div>
                                                                    <div class="item-price">
                                                                        {{Helper::formatNumber($orderCustom->order_total)}}
                                                                        {{$currencyCode}}</div>
                                                                </div>
                                                                @php
                                                                    $customCalc = \App\Services\PaymentCalculationService::calculateOrderPayments($orderCustom);
                                                                    $customConfirmedPaid = $customCalc['paid_amount'];
                                                                    $customAdvanceDisplay = ($customConfirmedPaid > 0) ? $customConfirmedPaid : (float)($orderCustom->paid_amount ?? 0);
                                                                    $customPendingDisplay = max(0.0, round((float)$orderCustom->order_total - $customAdvanceDisplay, 3));
                                                                @endphp
                                                                @if($orderCustom->custom_order_type === "purchase_order" && $customAdvanceDisplay > 0)
                                                                    <div class="total">
                                                                        <div class="item">{{__('admin.advance_payment')}}</div>
                                                                        <div class="item-price">-
                                                                            {{Helper::formatNumber($customAdvanceDisplay)}}
                                                                            {{$currencyCode}}</div>
                                                                    </div>
                                                                @endif
                                                                @if($orderCustom->custom_order_type === "purchase_order")
                                                                    <div class="total">
                                                                        <div class="item">{{__('admin.pending_amount')}}</div>
                                                                        <div class="item-price">
                                                                            {{Helper::formatNumber($customPendingDisplay)}}
                                                                            {{$currencyCode}}</div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                        @endif
                    @endif

                    <div class="payment-info">
                        <h4>{{__('web.having_trouble_with_the_link_pay_via_bank_transfer')}}</h4>
                        <div class="instruction">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span>{{__('web.bank_name')}}:&nbsp;</span>
                                        <label class="mb-0">{{__('web.demo_bank')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span>{{__('web.account_name')}}:&nbsp;</span>
                                        <label class="mb-0">{{__('web.reehana_general_trading')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span>{{__('web.account_number')}}:&nbsp;</span>
                                        <label class="mb-0">{{__('web.1460171196')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span>{{__('web.iban')}}:&nbsp;</span>
                                        <label class="mb-0">{{__('web.kw00_0000_0000')}}</label>
                                    </div>
                                </div>
                            </div>
                            <h4 class="mt-3">{{__('web.please_share_the_transfer_receipt_via_WhatsApp_or_Email_once_completed')}}</h4>
                        </div>
                    </div>

                    <div class="page-actions">
                        <a href="{{ route('web.checkout.payment_reject', ['token' => $paymentLink->payment_request_id]) }}" 
                        class="btn btn-danger" 
                        onclick="return confirm('{{__('web.are_you_sure_you_want_to_reject_this_order')}}');"
                        style="padding: 12px 40px; font-size: 16px;">
                            {{__('web.reject_order')}}
                        </a>

                        <a href="{{ $paymentLink->payment_link }}" 
                        class="btn btn-success" 
                        style="padding: 12px 40px; font-size: 16px;"
                        onclick="markPaymentAttempt()">
                            {{__('web.pay_now')}}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!--Modal Ex: Forgot Password-->
    <!--Header-->
    <!--Footer Scripts-->
    @include('web/layout/footer_scripts')
    
    <script>
    // Mark that user clicked Pay Now
    function markPaymentAttempt() {
        sessionStorage.setItem('paymentAttempted', 'true');
        sessionStorage.setItem('paymentAttemptTime', Date.now());
    }
    
    // Auto-refresh when user returns from payment gateway
    window.addEventListener('focus', function() {
        // Check if payment was attempted
        if (sessionStorage.getItem('paymentAttempted') === 'true') {
            var attemptTime = parseInt(sessionStorage.getItem('paymentAttemptTime'));
            var currentTime = Date.now();
            
            // If more than 30 milliseconds have passed since clicking Pay Now, refresh the page
            if (currentTime - attemptTime > 30) {
                sessionStorage.removeItem('paymentAttempted');
                sessionStorage.removeItem('paymentAttemptTime');
                window.location.reload();
            }
        }
    });
    
    // Clear flags when page loads
    window.addEventListener('load', function() {
        // Clear old flags after page loads successfully
        setTimeout(function() {
            sessionStorage.removeItem('paymentAttempted');
            sessionStorage.removeItem('paymentAttemptTime');
        }, 1000);
    });
    </script>
</body>

</html>

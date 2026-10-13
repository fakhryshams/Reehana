@extends('web/layout/main')
@section('page_title', trans('web.order_summary'))
@section('body_class', 'menubg')
@section('content')
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
$country = null;
if ($address) {
    $country = $address->getUserCountry($language->id);
    if ($country) {
        $deliveryTime = $country->delivery_time;
    }
}
?>
<?php
    $customOrderDetails = json_decode($order->custom_order_details, true);
    $customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.my_order')}}">{{__('web.my_orders')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">Order Confirmation</a></span>
    </div>
</section>
<section class="order">
    <div class="confirmation">
        <div class="container text-center">
            <p><img src="{{asset('front/img/icon-cart.svg')}}" width="40"></p>
            <p class="line1">{{__('web.quotation_generated_successfully')}}</p>
            <p class="line2">{{__('web.your_quotation_has_been_successfully_generated')}}.</p>
            <?php $address = $order->getUserAddress(); ?>
            @if(isset($customerInfo['email']))<p class="line3">{{__('web.quotation_copy_of_order_mail')}} {{ $customerInfo['email'] ?? '' }}. {{__('web.if_not_get_mail')}}.</p>@endif
        </div>
        <div class="container">
            <div class="payment-info">
                <h4>{{__('web.item_info')}}</h4>
                <p class="mont">
                    <span>{{__('web.qo_no')}}.: #{{ $order->id}}</span> 
                    <span>Date: {{date("d M, Y", strtotime($order->created_at))}}</span></p>
                {{-- <div class="instruction">
                    <?php
                    $paymentResponse = $order->payment_response;
                    $paymentId = "";
                    if ($paymentResponse) {
                        $paymentResponse = json_decode($paymentResponse);

                        $paymentId = isset($paymentResponse->PaymentID) ? $paymentResponse->PaymentID : (isset($paymentResponse->payment_id) ? $paymentResponse->payment_id : '');
                    }
                    ?>
                    <div class="row">
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
                </div> --}}
            </div>
            <?php
            $conversationRate = 1;
            if ($currency && $order->currency_code) {
                $currency->code = $order->currency_code;
                $conversationRate = $order->currency_rate;
            } else {
                $order->currency_code = config('custom.currency_convert_default_country');
                $currency->code = config('custom.currency_convert_default_country');
            }
            ?>
            <div class="prod-summary">
                <h4>{{ __('web.customized_items') }}</h4>
                <div class="cart-table-container">
                    <style>
                        .summary-table-head th {
                            text-transform: uppercase;
                            font-size: 11px;
                            line-height: 1.1;
                            letter-spacing: 0;
                        }
                    </style>
                    <?php
                    // Pre-scan all rows to determine which optional columns have data
                    $showColor = false;
                    $showSize  = false;
                    $showItemCode = false;
                    foreach ($orderProductDetail as $r) {
                        $attrs = [];
                        if (!empty($r->custom_attributes)) {
                            $attrs = json_decode($r->custom_attributes, true);
                            if (!is_array($attrs)) $attrs = [];
                        }
                        if (!empty($attrs['color'])) $showColor = true;
                        if (!empty($attrs['size']))  $showSize  = true;
                        if (!empty($attrs['item_code'])) $showItemCode = true;
                    }
                    $totalCols   = 4 + ($showColor ? 1 : 0) + ($showSize ? 1 : 0) + ($showItemCode ? 1 : 0);
                    $imageColPct = 12;
                    $itemColPct = 25;
                    $rightColCount = 3 + ($showItemCode ? 1 : 0) + ($showColor ? 1 : 0) + ($showSize ? 1 : 0);
                    $rightColPct = round((100 - $imageColPct - $itemColPct) / $rightColCount, 2);
                    $visibleOrderProductDetail = collect($orderProductDetail)->map(function ($record, $recordIndex) use ($customOrderDetails) {
                        $customAttributes = [];
                        if (!empty($record->custom_attributes)) {
                            $customAttributes = json_decode($record->custom_attributes, true);
                            if (!is_array($customAttributes)) {
                                $customAttributes = [];
                            }
                        }

                        $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                        $isPlaceholderName = trim(strtolower((string) $record->product_name)) === 'custom item';
                        $hasData = (!empty($record->product_name) && !$isPlaceholderName)
                            || !empty($customAttributes['item_code'])
                            || !empty($customAttributes['description'])
                            || !empty($itemImage)
                            || !empty($customAttributes['color'])
                            || !empty($customAttributes['size'])
                            || !empty($record->quantity)
                            || !empty($record->product_price)
                            || !empty($record->total_price);

                        return [
                            'record' => $record,
                            'recordIndex' => $recordIndex,
                            'hasData' => $hasData ? 1 : 0,
                        ];
                    })->sort(function ($a, $b) {
                        if ($a['hasData'] === $b['hasData']) {
                            return $a['recordIndex'] <=> $b['recordIndex'];
                        }

                        return $a['hasData'] <=> $b['hasData'];
                    })->values()->all();
                    ?>
                    <table class="cart-list-table" style="table-layout:fixed; width:100%;">
                        <colgroup>
                            <col style="width:{{ $imageColPct }}%;">{{-- Image --}}
                            <col style="width:{{ $itemColPct }}%;">{{-- Item --}}
                            @if($showItemCode)<col style="width:{{ $rightColPct }}%;">@endif
                            @if($showColor)<col style="width:{{ $rightColPct }}%;">@endif
                            @if($showSize)<col style="width:{{ $rightColPct }}%;">@endif
                            <col style="width:{{ $rightColPct }}%;">{{-- Price --}}
                            <col style="width:{{ $rightColPct }}%;">{{-- Qty --}}
                            <col style="width:{{ $rightColPct }}%;">{{-- Total --}}
                        </colgroup>
                        <thead style="display:table-header-group;">
                        <tr class="sm-hidden summary-table-head">
                            <th class="text-center">{{ __('admin.image') }}</th>
                            <th>Item &amp; Description</th>
                            @if($showItemCode)<th>{{__('web.item_code')}}</th>@endif
                            @if($showColor)<th>{{__('web.color')}}</th>@endif
                            @if($showSize)<th>{{__('web.size')}}</th>@endif
                            <th>{{__('web.price')}}</th>
                            <th class="text-center">{{ __('web.qty') }}</th>
                            <th>{{__('web.total_price')}}</th>
                        </tr>
                        </thead>
                        <?php $productPrice = []; ?>
                        @forelse($visibleOrderProductDetail as $row)
                        <?php
                        $record = $row['record'];
                        $recordIndex = $row['recordIndex'];
                        $pricetotal = $record->quantity * $record->product_price;
                        $customAttributes = [];
                        if (!empty($record->custom_attributes)) {
                            $customAttributes = json_decode($record->custom_attributes, true);
                            if (!is_array($customAttributes)) $customAttributes = [];
                        }
                        $productName = Helper::formatCustomizedTitle(
                            $record->product_name
                            ?: ($customAttributes['item_code'] ?? null)
                            ?: ($customAttributes['description'] ?? null)
                            ?: 'Custom Item'
                        );
                        $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                        $itemImage = $itemImage ? preg_replace('/^public\//', '', $itemImage) : null;
                        ?>
                        <tr>
                            <td class="text-center" >
                                <a href="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" target="_blank" style="display:inline-flex;justify-content:center;align-items:center;">
                                    <img src="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" alt="Reference Image" style="width:56px;height:56px;object-fit:cover;border:1px solid #ddd;border-radius:6px;padding:2px;" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                </a>
                            </td>
                            <td >
                                <div class="prod-name">{{$productName}}</div>
                                @if(!empty($customAttributes['description']))
                                    <div class="item-description">{{ Helper::formatCustomizedDescription($customAttributes['description']) }}</div>
                                @endif
                                <div class="price mont sm-visible">
                                    @if($record->is_active)
                                        <span class="price-amount">{{Helper::formatNumber($record->total_price)}} {{$currency->code}}</span>
                                    @else
                                        <s><span class="price-amount">{{Helper::formatNumber($record->total_price)}} {{$currency->code}}</span></s>
                                    @endif
                                </div>
                                <div class="sm-visible form-item">
                                    <label>{{__('web.quantity')}}</label>
                                    <span>{{$record->quantity}}</span>
                                </div>
                            </td>
                            @if($showItemCode)
                            <td class="barcode-cell">{{ $customAttributes['item_code'] ?? '' }}</td>
                            @endif
                            @if($showColor)
                            <td >{{ $customAttributes['color'] ?? '' }}</td>
                            @endif
                            @if($showSize)
                            <td >{{ $customAttributes['size'] ?? '' }}</td>
                            @endif
                            <td >
                                <span class="price-amount">{{Helper::formatNumber($record->product_price)}} {{$currency->code}}</span>
                            </td>
                            <td class="text-center qty-cell">
                                <span>{{$record->quantity}}</span>
                            </td>
                            <td >
                                <span class="price-amount">{{Helper::formatNumber($record->total_price)}} {{$currency->code}}</span>
                            </td>
                        </tr>
                        <?php if ($record->is_active) { $productPrice[] = $pricetotal; } ?>
                        @empty
                        <tr>
                            <td colspan="{{ $totalCols }}" class="text-center">No items found.</td>
                        </tr>
                        @endforelse
                    </table>
                </div>
            </div>
            <div class="order-details clearfix">
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
                                            if(isset($cust['country_id']) && $cust['country_id']){
                                                $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                                                if($cn) $countryName = $cn->country_name;
                                            }
                                        ?>
                                        @if(isset($cust['block']) && $cust['block'])<div><strong>Appartment :</strong> {{ e($cust['block']) }}</div>@endif
                                        @if(isset($cust['floor']) && $cust['floor'])<div><strong>Floor :</strong> {{ e($cust['floor']) }}</div>@endif
                                        @if(isset($cust['house_number']) && $cust['house_number'])<div><strong>House Number :</strong> {{ e($cust['house_number']) }}</div>@endif
                                        @if(isset($cust['city']) && $cust['city'])<div><strong>City :</strong> {{ e(ucfirst($cust['city'])) }}</div>@endif
                                        @if($countryName)<div><strong>Country :</strong> {{ e($countryName) }}</div>@endif
                                        @if(isset($cust['address_detail']) && $cust['address_detail'])<div><strong>Address Detail :</strong> {{ e($cust['address_detail']) }}</div>@endif
                                        <strong>{{__('admin.email')}} : </strong>{{ $customerInfo['email'] ?? '' }}<br>
                                        <strong>{{__('admin.phone')}} : </strong>{{ $customerInfo['phone'] ?? '' }}<br>
                                    </address>
                                </div>
                                @elseif($address && $address->is_billing_address == 0)
                                <div class="contact-name">
                                    <span>
                                        {{ucfirst($address->billing_first_name)}} {{ucfirst($address->billing_last_name)}}
                                    </span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address,true) !!}
                                </div>
                                @elseif($address)
                                <div class="contact-name">
                                    <span>
                                        {{$address->first_name}} {{$address->last_name}}
                                    </span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$address) !!}
                                </div>
                                @else
                                <div class="contact-name">
                                    <span>{{ __('web.not_available') }}</span>
                                </div>
                                @endif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="w-33">
                    <h4>{{__('web.qo_summary')}} <span>({{count($orderDetail)}} {{__('web.items')}})</span></h4>
                    <div class="order-summary" style="border:none;">
                        <div class="cart-details">
                            <div class="v-flex">
                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">{{__('web.sub_total')}}</div>
                                    <?php
                                    $gift_wrap = $order->gift_wrap ? $order->gift_wrap : 0;
                                    $gift_box = $order->gift_box ? $order->gift_box : 0;
                                    $subtotal = $gift_wrap + $gift_box + $order->order_price;
                                    ?>
                                    <div class="item-price">{{Helper::formatNumber($subtotal) }} {{$currencyCode}}</div>
                                </div>
                                @if($order->delivery_price > 0)
                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">{{__('web.shipping_charges')}}</div>
                                    <div class="item-price">{{Helper::formatNumber($order->delivery_price)}} {{$currencyCode}}</div>
                                </div>
                                @endif
                                @if($order->discount > 0)
                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">
                                        {{ __('web.discount') }}
                                        ({{ ucfirst($order->discount_type) }}@if($order->discount_type === 'percentage' && $order->discount > 0){{" - ".$order->discount_per}}%@endif)
                                    </div>
                                    <div class="item-price">
                                        - {{ Helper::formatNumber($order->discount) }} {{ $currencyCode }}
                                    </div>
                                </div>
                                @endif
                                <div class="total grandtotal" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">{{__('web.total_amount')}}</div>
                                    <div class="item-price">{{Helper::formatNumber($order->order_total)}} {{$currencyCode}}</div>
                                </div>
                                @if($order->custom_order_type === "purchase_order")
                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">{{__('admin.advance_payment')}}</div>
                                    <div class="item-price">- {{Helper::formatNumber($order->paid_amount * $conversationRate)}} {{$currencyCode}}</div>
                                </div>
                                @endif
                                @if($order->custom_order_type === "purchase_order")
                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                    <div class="item">{{__('admin.pending_amount')}}</div>
                                    <div class="item-price">{{Helper::formatNumber($order->pending_amount)}} {{$currencyCode}}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary"> <a href="{{route('web.cart.index')}}" style="color: #000000 !important"> {{__('web.go_to_cart')}}</a></button>
                <button class="btn btn-primary print_summary" data-href="{{route('web.checkout.print-order',['id'=>$order->id,'page'=>'summary'])}}" id="print">{{__('web.print_summary')}}</button>


            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_html')
@endsection

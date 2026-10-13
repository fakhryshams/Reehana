<!DOCTYPE html>
<html>
@extends('layout/head')
@section('page_title', (($orderDetail->custom_order_type ?? 'purchase_order') === 'quotation' ? 'QO-' : 'PO-') . $orderDetail->id)
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<style>
    .main-footer {
        margin-left: 0px !important;
        padding: 10px !important;
    }

    .subtotal-table td,
    .subtotal-table th {
        padding: .6rem !important;
    }
    
    .table td, .table th {
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    
    .barcode-cell {
        word-break: break-all !important;
        overflow-wrap: anywhere !important;
        white-space: normal !important;
        line-height: 1.3 !important;
    }
</style>
@endsection

<body>
    <div class="wrapper">
        <section class="invoice">
            <div class="col-12 p-3">
                <div class="row">
                    <div class="col-12 text-center">
                        <img src="{{Helper::getAppLogo(1)?Helper::getAppLogo(1): ''}}" alt="{{__('admin.logo')}}" height="50" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                    </div>
                    <div class="col-12 mt-2">
                        <h4>
                            <i class="fa fa-cart-plus no-print"></i> {{__('admin.po_no')}} {{$orderDetail->id}}
                            <small class="">({{__('admin.po-date')}} : {{\Carbon\Carbon::parse($orderDetail->created_at)->format(config('custom.php_datetime_format'))}})</small>
                        </h4>
                    </div>
                </div>

                <?php
                $conversationRate = 1;
                if ($currency && $orderDetail->currency_code) {
                    $currency->code = $orderDetail->currency_code;
                    $conversationRate = $orderDetail->currency_rate;
                } else {
                    $orderDetail->currency_code = config('custom.currency_convert_default_country');
                    $currency->code = config('custom.currency_convert_default_country');
                }
                $user = $orderDetail->getUser();
                $userAddress = $orderDetail->getUserAddress();
                $billingAddress = $orderDetail->getBillingAddress();
                $shippingAddress = $orderDetail->getShippingAddress();
                $shippingAddressCountry = $shippingAddress ? $shippingAddress->getUserCountry() : '';
                $shippingCity = $shippingAddress->city ?? "";
                if (isset($shippingAddress->city_id) && $shippingAddress && $shippingAddress->city_id) {
                    $shippingCity = $shippingAddress->getUserCity(1) ? $shippingAddress->getUserCity(1)->city_name : '';
                }
                ?>

                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col" style="font-size:20px;">
                        <b>{{__('admin.customer-info')}}</b>
                        <address>
                            {{$user?ucfirst($user->first_name):''}} {{$user?ucfirst($user->last_name):''}}<br>
                            {{__('admin.email')}} : {{$user->email}}<br>
                            <strong>{{__('admin.phone')}} : </strong>{{$userAddress->phone ?? ""}}
                        </address>
                    </div>

                    {{-- <div class="col-sm-4">
                        <b><u>{{$userAddress->pickupstore==0?__('admin.shipping-info'):'Pickup From Store'}}</u></b>
                        <address style="font-size: 20px;">
                            <strong>{{__('admin.email')}} </strong>: {{$userAddress->email}}<br>
                            {{$userAddress?ucfirst($userAddress->first_name):''}} {{$userAddress?ucfirst($userAddress->last_name):''}}<br>
                            {!!$shippingAddress->appartment? '<strong>Appartment :</strong> '. $shippingAddress->appartment.'<br>':''!!}
                            {!!$shippingAddress->floor? '<strong>Floor :</strong> '.$shippingAddress->floor."<br> ":''!!}
                            {!!$shippingAddress->area? '<strong>Area :</strong> '.$shippingAddress->area."<br>":''!!}
                            {!!$shippingAddress->block? '<strong>Block :</strong> '.$shippingAddress->block."":''!!} <br>
                            {!! $shippingAddress->street? '<strong>Street :</strong> '.$shippingAddress->street."<br>":'' !!}
                            {!!$shippingAddress->house_number? '<strong>House Number : </strong> '. $shippingAddress->house_number:''!!}<br>
                            {!! $shippingCity ? '<strong>City :</strong> '. ucfirst($shippingCity).'<br>' : ''!!}
                            {!!$shippingAddressCountry->country_name? '<strong>Country :</strong> '.$shippingAddressCountry->country_name." ":''!!}
                            {!!$shippingAddress->pin_code?'<strong> Pin Code :</strong> '. $shippingAddress->pin_code:""!!}<br>
                            {!!$shippingAddress->address_detail?'<strong>Address Detail : </strong>'.$shippingAddress->address_detail ."<br>":''!!}
                            @if($userAddress->pickupstore==0)
                            <strong>{{__('admin.phone')}} : </strong>{{$shippingAddress->phone}}<br>
                            @endif
                            @if($orderDetail->cphone && $userAddress->pickupstore==1)
                            <strong>Customer Phone : </strong>{{$orderDetail->cphone}}<br>
                            @endif
                            <strong> {{__('admin.shipping-cost')}} : </strong>{{Helper::formatNumber($orderDetail->delivery_price*$conversationRate)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small><br>
                        </address>
                    </div> --}}
                    <div class="col-sm-4">
                        <b><u>{{__('admin.billing-info')}}</u></b>
                        <?php
                                $customOrderDetails = json_decode($orderDetail->custom_order_details, true);
                                $customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
                                ?>
                                @if($customerInfo)
                                <address>
                                {{ isset($customerInfo['name']) ? ucfirst($customerInfo['name']) : '' }}<br>
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
                                @if(isset($cust['note']) && $cust['note'])<div><strong>{{ __('admin.note') }} :</strong> {!! nl2br(e($cust['note'])) !!}</div>@endif
                                <strong>{{__('admin.email')}} : </strong>{{ $customerInfo['email'] ?? '' }}<br>
                                <strong>{{__('admin.phone')}} : </strong>{{ $customerInfo['phone'] ?? '' }}<br>
                            </address>
                            @elseif($shippingAddress && $shippingAddress->is_billing_address==0)
                            <address>
                                {{$userAddress?ucfirst($userAddress->billing_first_name):''}} {{$userAddress?ucfirst($userAddress->billing_last_name):''}}<br>
                                {!! !empty($billingAddress->billing_appartment) ? '<strong>Appartment :</strong> '.$billingAddress->billing_appartment."<br>" : ''!!}
                                {!! !empty($billingAddress->billing_floor)?'<strong>Floor :</strong> '.$billingAddress->billing_floor."<br> ":''!!}
                                {!! !empty($billingAddress->billing_area)? '<strong>Area :</strong> '. $billingAddress->billing_area."<br>":''!!}
                                {!! !empty($billingAddress->billing_block)? '<strong>Block :</strong> '. $billingAddress->billing_block."<br>":''!!}
                                {!! !empty($billingAddress->billing_street)? '<strong>Street : </strong>'. $billingAddress->billing_street."<br>":''!!}
                                {!! !empty($billingAddress->billing_house_number)? '<strong>House Number : </strong>'.$billingAddress->billing_house_number."<br> ":''!!}
                                
                                {!!$billingCity ? '<strong>City : </strong>'. ucfirst($billingCity).'<br>' : ''!!}
                                {!!isset($billingAddressCountry->country_name)? '<strong>Country :</strong> '.ucfirst($billingAddressCountry->country_name)." ":''!!}<br>
                                {!! !empty($billingAddress->pin_code)? '<strong>Pin Code :</strong> '.$billingAddress->pin_code:""!!}
                                {!! !empty($shippingAddress->billing_address_detail)?'<strong>Address Detail : </strong>'.$shippingAddress->billing_address_detail ."<br>":''!!}
                                <strong>{{__('admin.phone')}} : </strong>{{$billingAddress->billing_phone ?? ''}}<br>
                            </address>
                            @elseif($shippingAddress)
                            <address>
                                
                                {!!$shippingAddress->appartment? '<strong>Appartment :</strong> '. $shippingAddress->appartment.'<br>':''!!}
                                {!!$shippingAddress->floor? '<strong>Floor :</strong> '.$shippingAddress->floor."<br> ":''!!}
                                {!!$shippingAddress->area? '<strong>Area :</strong> '.$shippingAddress->area."<br>":''!!}
                                {!!$shippingAddress->block? '<strong>Block :</strong> '.$shippingAddress->block."<br>":''!!}
                                {!! $shippingAddress->street? '<strong>Street :</strong> '.$shippingAddress->street." <br>":'' !!}
                                
                                {!!$shippingAddress->house_number? '<strong>House Number : </strong> '. $shippingAddress->house_number:''!!}<br>
                                {!! $shippingCity ? '<strong>City :</strong> '. ucfirst($shippingCity).'<br>' : ''!!}
                                {!!$shippingAddressCountry->country_name? '<strong>Country :</strong> '.$shippingAddressCountry->country_name." ":''!!}
                                {!!$shippingAddress->pin_code?'<strong> Pin Code :</strong> '. $shippingAddress->pin_code:""!!}<br>
                                {!!$shippingAddress->address_detail?'<strong>Address Detail : </strong>'.$shippingAddress->address_detail ."<br>":''!!}
                                <strong>{{__('admin.phone')}} : </strong>{{$shippingAddress->phone}}<br>
                            </address>
                            @endif
                    </div>

                    <div class="col-sm-4">
                        @if($addressQr)
                        <img class="text-center p-0" width="300" src="{{$addressQr}}">
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 table-responsive">
                        <table class="table table-striped normal-table">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 100px;">{{ __('admin.image') }}</th>
                                    <th>{{__('admin.product-name')}}</th>
                                    <th>Custom Attributes</th>
                                    <th>{{__('admin.price')}}</th>
                                    <th class="text-center">{{__('admin.qty')}}</th>
                                    <th>{{__('admin.total')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $productPrice = []; ?>
                                @forelse($orderProductDetail as $recordIndex => $record)
                                <?php
                                $pricetotal = $record->quantity * $record->product_price;
                                $customAttributes = [];
                                if (!empty($record->custom_attributes)) {
                                    $customAttributes = json_decode($record->custom_attributes, true);
                                    if (!is_array($customAttributes)) $customAttributes = [];
                                }
                                $productName = $productName =
                                    $record->product_name
                                    ?: ($customAttributes['item_code'] ?? null)
                                    ?: ($customAttributes['description'] ?? null)
                                    ?: 'Custom Item';
                                $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                                $itemImage = $itemImage ? preg_replace('/^public\//', '', $itemImage) : null;
                                ?>
                                <tr>
                                    <td class="text-center align-middle" style="width: 100px; vertical-align: middle;">
                                        <a href="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" target="_blank" style="display: inline-flex; justify-content: center; align-items: center;">
                                            <img src="{{ $itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png')) }}" alt="Reference Image" style="width:64px; height:64px; object-fit:cover; border:1px solid #ddd; border-radius:6px; padding:2px;" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                        </a>
                                    </td>
                                    <td><b>{{ Helper::formatCustomizedTitle($productName ?: 'Custom Item') }}</b>
                                        @if($record->note)
                                        <br />
                                        {{__('admin.note')}} : {{ Helper::formatCustomizedDescription($record->note) }}<br>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($customAttributes) > 0)
                                        @foreach($customAttributes as $key => $value)
                                        @if($key === 'image')
                                        @continue
                                        @endif
                                        <strong>{{ ucfirst(str_replace('_',' ',$key)) }}:</strong> {{ $key === 'description' ? Helper::formatCustomizedDescription($value) : $value }}<br>
                                        @endforeach
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>{{Helper::formatNumber($record->product_price*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    <td class="text-center">{{$record->quantity}}</td>
                                    <td>{{Helper::formatNumber($pricetotal*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                </tr>
                                <?php if ($record->is_active) {
                                    $productPrice[] = $pricetotal;
                                } ?>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                $sumProductPrice = array_sum($productPrice);
                if ($orderDetail->gift_wrap) $sumProductPrice += $orderDetail->gift_wrap;
                if ($orderDetail->gift_box) $sumProductPrice += $orderDetail->gift_box;
                $shippingTax = $sumProductPrice * $tax / 100;
                $deliveryPrice = $orderDetail->delivery_price;
                $discount = $orderDetail->discount;
                $couponCode = $orderDetail->coupon_code;
                $couponDiscountAmount = $orderDetail->coupon_discount_amount ? $orderDetail->coupon_discount_amount : 0;
                $total = $sumProductPrice + $shippingTax + $deliveryPrice - $discount - $couponDiscountAmount;
                $paymentResponse = $orderDetail->payment_response;
                $paymentId = "";
                if ($paymentResponse) {
                    $paymentResponse = json_decode($paymentResponse);
                    
                    
                    $postDate =  !empty($paymentResponse->post_date) ? $paymentResponse->post_date : '';
                    
                    $paymentId = isset($paymentResponse->invoice_id) ? date('Y') . $postDate . $paymentResponse->invoice_id : '';
                }
                ?>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{__('admin.payment-method')}}</label>
                                <?php
                                $paymentMethod = $orderDetail->getPaymentMethodDetail();
                                ?>
                                <input type="text" class="form-control" readonly="" value="{{$paymentMethod?$paymentMethod->payment_method_name:''}}" disabled="" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6 float-left">
                            <div class="form-group">
                                <label>{{__('admin.ref-number')}}</label>
                                <input type="text" class="form-control" readonly="" value="{{$orderDetail->transaction_id}}" disabled="" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6 float-left">
                            <div class="form-group">
                                <label>{{__('admin.payment-id')}}</label>
                                <input type="text" class="form-control" readonly="" value="{{$paymentId}}" disabled="" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @if($orderDetail->custom_order_type === "purchase_order")
                                        <tr>
                                            <th class="th-td-boarder-none">{{__('admin.subtotal')}}</th>
                                            <td class="th-td-boarder-none">{{Helper::formatNumber($sumProductPrice*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <th class="th-td-boarder-none">{{__('admin.subtotal')}}</th>
                                            <td class="th-td-boarder-none">{{Helper::formatNumber($sumProductPrice*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{__('admin.shipping')}}</th>
                                        <td>{{Helper::formatNumber($orderDetail->delivery_price*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small> </td>
                                    </tr>
                                    @if($discount && $discount > 0)
                                    <tr>
                                        <th>{{__('web.discount_type')}}</th>
                                        <td>{{ ucfirst($orderDetail->discount_type) }}</td>
                                    </tr>
                                    @if($orderDetail->discount_type == 'percentage')
                                    <tr>
                                        <th>{{__('web.discount_value')}}</th>
                                        <td>{{ $orderDetail->discount_per }}%</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>{{__('admin.discount')}}</th>
                                        <td>{{Helper::formatNumber($discount*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small> </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>{{__('admin.total')}}</th>
                                        <td>{{Helper::formatNumber($total*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
                                    @if($orderDetail->custom_order_type === "purchase_order")
                                        <tr>
                                            <th class="">{{__('admin.advance_payment')}}</th>
                                            <td class="">{{Helper::formatNumber($orderDetail->paid_amount)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.pending_amount')}}</th>
                                            <td class="text-danger font-weight-bold">{{Helper::formatNumber($orderDetail->pending_amount)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        </tr>
                                    @endif
                                    @if($currency && $currency->code!=config('custom.currency_convert_default_country'))
                                    <tr>
                                        <th>{{__('admin.total')}} in {{config('custom.currency_convert_default_country')}}</th>
                                        <td>{{Helper::formatNumber($total)}}<small> {{config('custom.currency_convert_default_country')}}</small></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        
        @include('layout/footer')
    </div>
    
    <!-- ./wrapper -->
    <script type="text/javascript">
        window.addEventListener("load", window.print());
    </script>
</body>

</html>

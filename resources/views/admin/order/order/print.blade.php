<!DOCTYPE html>
<html>
@extends('layout/head')
@section('page_title', trans('admin.print_order'))
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
    .custom-font-monogram-text {
        line-height: 3em;
    }

</style>
@endsection

<body>
    <div class="wrapper">
        <!-- Main content -->
        <section class="invoice">
            <!-- title row -->
            <div class="col-12 p-3">
                <!-- Main content -->
                <!-- title row -->
                <div class="row">
                    <div class="col-12 text-center">
                        <img src="{{Helper::getAppLogo(1)?Helper::getAppLogo(1): ''}}" alt="{{__('admin.logo')}}" height="50" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                    </div>
                    <div class="col-12">
                        <!--<span class="brand-text font-weight-light text-center">{{Helper::getAppName()?Helper::getAppName():config('app.name',__('admin.admin'))}}</span>-->
                    </div>
                    <div class="col-12 mt-2">
                        <h4>
                            <i class="fa fa-cart-plus no-print"></i> {{__('admin.order_no')}} {{$orderDetail->id}}
                            <small class="">({{__('admin.order-date')}} : {{\Carbon\Carbon::parse($orderDetail->created_at)->format(config('custom.php_datetime_format'))}})</small>
                        </h4>
                    </div>
                    <!-- /.col -->
                </div>
                <?php
                $orderActionCount = count($orderProductDetail);
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
                //$shippingAddress = $orderDetail->getShippingAddress();
                $billingAddress = $orderDetail->getBillingAddress();
                $billingCity = $billingAddress->billing_city;
                if ($billingAddress->billing_city_id) {
                    $billingCity = $billingAddress->getUserBillingCity(1) ? $billingAddress->getUserBillingCity(1)->city_name : '';
                }
                $userAddressCountry = $userAddress ? $userAddress->getUserCountry() : '';
                $shippingAddressCountry = $shippingAddress ? $shippingAddress->getUserCountry() : '';
                $shippingCity = $shippingAddress->city;
                if ($shippingAddress->city_id) {
                    $shippingCity = $shippingAddress->getUserCity(1) ? $shippingAddress->getUserCity(1)->city_name : '';
                }
                $billingAddressCountry = $billingAddress ? $billingAddress->getUserBillingCountry(1) : '';
                $orderStatus = $orderDetail->getOrderStatus();
                $orderStatus = $orderStatus ? $orderStatus->order_status : '';
                ?>
                <!-- info row -->
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col"  style="font-size: 20px;">
                        <b>{{__('admin.customer-info')}}</b>
                        <address>
                            {{$user?ucfirst($user->first_name):''}} {{$user?ucfirst($user->last_name):''}}<br>
                            {{__('admin.email')}} : {{$user->email}}<br>
                            <strong>{{__('admin.phone')}} : </strong>{{$userAddress->phone}}
                        </address>
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-4">
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
                    </div>
                    <div class="col-sm-4">
                        @if($addressQr)
                        <img class="text-center p-0" width="300" src="{{$addressQr}}">
                        @endif
                    </div>
                    <!-- /.col -->
                    @if(false)
                    <div class="col-sm-4 invoice-col">
                        <b>{{__('admin.billing-info')}}</b>
                        @if($shippingAddress->is_billing_address==0)
                        <address>
                            {{$userAddress?ucfirst($userAddress->billing_first_name):''}} {{$userAddress?ucfirst($userAddress->billing_last_name):''}}<br>
                            {!!$billingAddress->billing_house_number? '<strong>House Number : </strong>'.$billingAddress->billing_house_number."<br> ":''!!}
                            {!! $billingAddress->billing_appartment ? '<strong>Appartment :</strong> '.$billingAddress->billing_appartment."<br>" : ''!!}
                            {!!$billingAddress->billing_floor?'<strong>Floor :</strong> '.$billingAddress->billing_floor."<br> ":''!!}
                            {!! $billingAddress->billing_street? '<strong>Street : </strong>'. $billingAddress->billing_street."<br>":''!!}
                            {!! $billingAddress->billing_area? '<strong>Area :</strong> '. $billingAddress->billing_area."<br>":''!!}
                            {!!$billingAddress->billing_block? '<strong>Block :</strong> '. $billingAddress->billing_block."<br>":''!!}
                            {!!$billingCity ? '<strong>City : </strong>'. ucfirst($billingCity).'<br>' : ''!!}
                            {!!isset($billingAddressCountry->country_name)? '<strong>Country :</strong> '.ucfirst($billingAddressCountry->country_name)." ":''!!}<br>
                            {!!$billingAddress->pin_code? '<strong>Pin Code :</strong> '.$billingAddress->pin_code:""!!}
                            {!!$shippingAddress->billing_address_detail?'<strong>Address Detail : </strong>'.$shippingAddress->billing_address_detail ."<br>":''!!}
                            <strong>{{__('admin.phone')}} : </strong>{{$billingAddress->billing_phone}}<br>
                        </address>
                        @else
                        <address>
                            {!!$shippingAddress->house_number? '<strong>House Number : </strong> '. $shippingAddress->house_number:''!!}<br>
                            {!!$shippingAddress->appartment? '<strong>Appartment :</strong> '. $shippingAddress->appartment.'<br>':''!!}
                            {!!$shippingAddress->floor? '<strong>Floor :</strong> '.$shippingAddress->floor."<br> ":''!!}
                            {!! $shippingAddress->street? '<strong>Street :</strong> '.$shippingAddress->street." <br>":'' !!}
                            {!!$shippingAddress->area? '<strong>Area :</strong> '.$shippingAddress->area."<br>":''!!}
                            {!!$shippingAddress->block? '<strong>Block :</strong> '.$shippingAddress->block."<br>":''!!}
                            {!! $shippingCity ? '<strong>City :</strong> '. ucfirst($shippingCity).'<br>' : ''!!}
                            {!!$shippingAddressCountry->country_name? '<strong>Country :</strong> '.$shippingAddressCountry->country_name." ":''!!}
                            {!!$shippingAddress->pin_code?'<strong> Pin Code :</strong> '. $shippingAddress->pin_code:""!!}<br>
                            {!!$shippingAddress->address_detail?'<strong>Address Detail : </strong>'.$shippingAddress->address_detail ."<br>":''!!}
                            <strong>{{__('admin.phone')}} : </strong>{{$shippingAddress->phone}}<br>
                        </address>
                        @endif
                    </div>
                    @endif
                    <!-- /.col -->
                </div>
                <div class="row">
                    <div class="col-xs-12 table-responsive">
                        <table class="table table-striped normal-table">
                            <thead>
                                <tr>
                                    <th class="text-center">{{__('admin.image')}}</th>
                                    <th>{{__('admin.product-name')}}</th>
                                    <th>{{__('admin.price')}}</th>
                                    <th class="text-center">{{__('admin.qty')}}</th>
                                    <th>{{__('admin.total')}}</th>
                                    <?php $issku = 0;

                                                $isbarcode = 0;

                                                ?>
                                                @foreach($orderProductDetail as $record)
                                                <?php
                                                $product = $record->getProduct();
                                                $size = $record->getSizeDetail();

                                                $color = $record->getColorDetail();
    
                                                $products = \App\AdminModel\Product\AdminProduct::find($product->products_id);
                                                $attributePrice = $products->getProductAttributeProductPage($product->products_id, $size ? $size->size_id : '', $color ? $color->color_id : '');
                                                if ($attributePrice) {
                                                    if ($attributePrice->sku) {
                                                        $issku = $issku + 1;
                                                    } else {
                                                        if ($product->product_sku) {
                                                            $issku = $issku + 1;
                                                        }
                                                    }

                                                    if ($attributePrice->barcode) {
                                                        $isbarcode = $isbarcode + 1;
                                                    } else {
                                                        if ($product->product_sku) {
                                                            $isbarcode = $isbarcode + 1;
                                                        }
                                                    }
                                                } else {
                                                    if ($products->product_sku) {
                                                        $issku = $issku + 1;
                                                    }

                                                    if ($products->product_barcode) {
                                                        $isbarcode = $isbarcode + 1;
                                                    }
                                                }
                                                
                                                ?>
                                                @endforeach

                                                @if($issku > 0)
                                                <th>SKU</th>
                                                @endif
                                                @if($isbarcode > 0)
                                                <th>Barcode</th>
                                                @endif

                                </tr>
                            </thead>
                            <?php
                            $productPrice = [];
                            ?>
                            <tbody>
                                @forelse($orderProductDetail as $record)
                                <?php
                                $product = $record->getProduct();
                                $productImage = $record->getProductImage();

                                $style = '';


                                $pricetotal = $record->quantity * $record->product_price;

                                $size = $record->getSizeDetail();

                                $color = $record->getColorDetail();

                                $productDetail = $record->getProductDetail();
                                $style = '';


                                $products = \App\AdminModel\Product\AdminProduct::find($product->products_id);
                                $attributePrice = $products->getProductAttributeProductPage($product->products_id, $size ? $size->size_id : '', $color ? $color->color_id : '');
                                
                                 $product_main = \App\AdminModel\Product\AdminProduct::find($product->products_id);
                                if ($attributePrice) {
                                    $style = $attributePrice->style;
                                }
                                $monogram_price = 0;
                                ?>
                                @if($product)
                                <tr>
                                    <td class="text-center">

                                        <?php
                                        $productImage2 = '';

                                        if ($record['color_id'] && !$record['size_id']) {
                                            $productImage2 = $products->getColorImage($record['color_id']);
                                        } else if (!$record['color_id'] && $record['size_id']) {
                                            $productImage2 = $products->getSizeImage($record['size_id']);
                                        } else if ($record['color_id'] && $record['size_id']) {
                                            $productImage2 = $products->getColorSizeImage($record['color_id'], $record['size_id']);
                                        }

                                        if (!$productImage2) {
                                            $productImage2 = $productImage->image_url;
                                        } else {
                                            $productImage2 = $productImage2->image_url;
                                        }
                                        ?>
                                        <img class="logo-table" src="{{asset(Storage::url($productImage2))}}" onclick="showDetails(this.src)" />
                                    </td>
                                    <td width="30%">{{$product?ucfirst($product->products_name):""}} {{ $style ? '('.$style.')': ''}}
                                        {!! $size? '<br>Size : '.ucfirst($size->size_title).'':''!!}
                                        {!! $color? '<br>Color : '.ucfirst($color->color_title).'':''!!}
                                        @if($record->note)
                                        <br />
                                        {{__('admin.note')}} : {{$record->note}}<br>
                                        @endif

                                        @if($record->is_monogram)
                                        <?php
                                        $monogramVal = App\UserModel\OrderMonogramDetail::where('order_detail_id', $record->id)->get()->toArray();
                                        ?>

                                        @foreach($monogramVal as $key => $monogramVal)
                                        <?php
                                        if ($record->is_same == 0) {
                                            $monogram_price = $monogram_price + $monogramVal['monogram_price'];
                                        }
                                        ?>
                                        <br>
                                        <br>
                                        <span class="text-sm custom-font-monogram-title"><b>{{__('admin.selected_monogram')}}</b></span> <small>(+{{Helper::formatNumber(Helper::getMonogramPrice()*$conversationRate)}} {{$currency?$currency->code:config('custom.currency')}})</small><br>
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-default text-center">
                                                <input type="radio" name="color_option" id="color_option_b1" autocomplete="off">
                                                <span class="text-sm custom-font-monogram">{{__('admin.monogram_style')}}</span>
                                                <br>
                                                <span class="custom-font-monogram-text">{{$monogramVal['font_style_name']}}</span>
                                                <img height="50px" width="60px" src="{{asset(Storage::url($monogramVal['font_style_image']))}}" class="text-center">
                                                @if(false)<img src="{{asset(Storage::url($monogramVal['font_style_image']))}}">@endif
                                            </label>
                                            <label class="btn btn-default text-center">
                                                <input type="radio" name="color_option" id="color_option_b2" autocomplete="off">
                                                <span class="text-sm custom-font-monogram">{{__('admin.monogram_initials')}}</span>
                                                <br>
                                                <span class="custom-font-monogram-text">{{Helper::getInitials($monogramVal['initials_count'],$monogramVal['left_text'],$monogramVal['center_text'],$monogramVal['right_text'],$monogramVal['text4'])}}</span>
                                            </label>
                                            <label class="btn btn-default text-center">
                                                <input type="radio" name="color_option" id="color_option_b3" autocomplete="off">
                                                <span class="text-sm custom-font-monogram">{{__('admin.monogram_color')}}</span>
                                                <?php
                                                //                                                            $monogramColor        = $record->getMonogramColorDetail();
                                                $monogramColor = $monogramVal['monogram_color'];
                                                ?>
                                                <br>
                                                <span class="custom-font-monogram-text">{{$monogramColor?ucfirst($monogramColor):'-'}}</span>
                                                <span class="color-plate text-center" style="background-color: {{$monogramVal['monogram_color_hash']}}"></span>
                                            </label>
                                            <label class="btn btn-default text-center">
                                                <input type="radio" name="color_option" id="color_option_b4" autocomplete="off">
                                                <span class="text-sm custom-font-monogram">{{__('admin.monogram_placement')}}</span>
                                                <br>
                                                <span class="custom-font-monogram-text">{{$monogramVal['placement_desc']}}</span>
                                                <img height="60px" width="60px" src="{{asset(Storage::url($monogramVal['placement_image']))}}" class="text-center">
                                            </label>
                                        </div>
                                        @if($monogramVal['monogram_note'])
                                        <br>
                                        <span class="text-sm custom-font-monogram-comment"><b>{{__('admin.monogram_comment')}} :</b> {{$monogramVal['monogram_note']}}</span><br>

                                        @endif
                                        @endforeach
                                        @if($record->is_same == 1)

                                        <div>
                                            <br />
                                            <div class="checkbox">
                                                <input type="checkbox" name="applyAll" checked="checked" disabled="disabled">
                                                <label>Applied same Monogram Styles to all quantities.</label>
                                            </div>
                                        </div>
                                        @endif
                                        @endif
                                    </td>
                                    @if(false)
                                    <td title="{{$record->note}}">{{$record->note?substr($record->note,0,10).'...':' - '}}</td>
                                    @endif
                                    <td>{{Helper::formatNumber($record->product_price*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    <td class="text-center">{{$record->quantity}}</td>
                                    <td>{{Helper::formatNumber($pricetotal*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    @if($issku > 0)
                                    <td>
                                        {{$attributePrice ?$attributePrice->sku :  $product_main->product_sku}}
                                    </td>
                                    @endif
                                    @if($isbarcode > 0)
                                    <td>{{$attributePrice ? $attributePrice->barcode :  $product_main->product_barcode}}</td>
                                    @endif
                                </tr>
                                <?php
                                if ($record->is_active) {

                                    if ($record->is_same == 1) {
                                        $monogram_price = $monogramVal['monogram_price'] * $record->quantity;
                                    }



                                    $productPrice[] = $pricetotal + $monogram_price;
                                }
                                ?>

                                @endif
                                <!--GiftWarp-->
                                @if($loop->last)
                                @if($orderDetail->is_gift ==1)
                                <tr>
                                    <td class="text-center">
                                        <img class="logo-table" src="{{ Helper::getGiftImage() }}" onclick="showDetails(this.src)" />
                                    </td>
                                    <td width="30%">
                                        {{__('admin.gift_wrap')}}
                                        @if($orderDetail->gift_title)
                                        <br><b>Title</b>: {{$orderDetail->gift_title}}
                                        @endif
                                        @if($orderDetail->gift_message)
                                        <br><b>Message</b>: {{$orderDetail->gift_message}}
                                        @endif

                                    </td>
                                    <td>{{Helper::formatNumber($orderDetail->gift_wrap*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    <td class="text-center">1</td>
                                    <td>{{Helper::formatNumber($orderDetail->gift_wrap*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>

                                    @if($orderDetail->order_status != config('custom_web.Pending'))

                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    @endif
                                </tr>
                                @endif
                                @endif
                                <!--/.GiftWrap-->

                                <!--GiftBox-->
                                @if($loop->last)
                                @if($orderDetail->is_box ==1)
                                <tr>
                                    <td class="text-center">
                                        <img class="logo-table" src="{{ Helper::getGiftBoxImage() }}" onclick="showDetails(this.src)" />
                                    </td>
                                    <td width="30%">
                                        {{__('admin.gift_box')}}
                                        @if($orderDetail->gift_box_title)
                                        <br><b>Title</b>: {{$orderDetail->gift_box_title}}
                                        @endif
                                        @if($orderDetail->gift_box_message)
                                        <br><b>Message</b>: {{$orderDetail->gift_box_message}}
                                        @endif

                                    </td>
                                    <td>{{Helper::formatNumber($orderDetail->gift_box*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    <td class="text-center">1</td>
                                    <td>{{Helper::formatNumber($orderDetail->gift_box*$conversationRate)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>

                                    @if($orderDetail->order_status != config('custom_web.Pending'))

                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    @endif
                                </tr>
                                @endif
                                @endif
                                <!--/.GiftBox-->

                                @empty
                                @endforelse
                            </tbody>
                            <?php
                            $sumProductPrice = array_sum($productPrice);
                            if ($orderDetail->gift_wrap) {
                                $sumProductPrice += $orderDetail->gift_wrap;
                            }
                            if ($orderDetail->gift_box) {
                                $sumProductPrice += $orderDetail->gift_box;
                            }
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
                        </table>
                    </div>

                    <!-- /.col -->
                </div>
                <hr>
                <div class="row">
                    <!-- accepted payments column -->
                    @if(false)
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
                    @endif
                    <!-- /.col -->
                    <!-- accepted payments column -->
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
                        <div class="table-responsive subtotal-table">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th class="th-td-boarder-none pt-0">{{__('admin.subtotal')}}</th>
                                        <td class="th-td-boarder-none pt-0">{{Helper::formatNumber($sumProductPrice*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
                                    @if($tax != 0)
                                    <tr>
                                        <th>{{__('admin.tax')." " .'('.$tax."%".')'}}</th>
                                        <td>{{Helper::formatNumber($shippingTax*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
                                    @endif
                                    @if($couponCode)
                                    <tr>
                                        <th>{{__('admin.discount_code')}} : {{$couponCode}}</th>
                                        <td>{{$couponDiscountAmount?"-":""}} {{Helper::formatNumber($couponDiscountAmount*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small> </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>{{__('admin.shipping')}}</th>
                                        <td>{{Helper::formatNumber($orderDetail->delivery_price*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small> </td>
                                    </tr>
                                    @if($discount)
                                    <tr>
                                        <th>{{__('admin.discount')}}</th>
                                        <td>{{Helper::formatNumber($discount*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>{{__('admin.total')}}</th>
                                        <td>{{Helper::formatNumber($total*$conversationRate)}}<small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
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
                    <!-- /.col -->
                </div>
                <div class="row">
                    <div class="col-md-12">

                        <input class="form-control" id="orders_id" name="orders_id" type="hidden" value="{{$orderDetail->id}}">

                        <!--<p class="lead">{{__('admin.change-status')}}</p>-->
                        <div class="row">
                            @if(false)
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.order-status')}}</label>
                                    <input type="text" value="{{$orderStatus}}" class="form-control" readonly="" disabled="">
                                </div>
                            </div>
                            @endif
                            @if($orderDetail->tracking_number)
                            <hr>
                            <div class="col-md-6 col-sm-12 hide-cancel-return">
                                <div class="form-group">
                                    <label>{{__('admin.courier-company')}}</label>
                                    <input readonly="" type="text" class="form-control" name='courier_company' value="{{$courier?$courier->company_name:''}}" placeholder="Courier">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.tracking_number')}}</label>
                                    <input readonly="" type="text" class="form-control" name='tracking_number' value="{{$orderDetail->tracking_number?$orderDetail->tracking_number:""}}" placeholder="">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- this row will not appear when printing -->
                </div>
                @if(false)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <p class="mb-3"><strong>{{__('admin.order-status-history')}}</strong></p>
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{__('admin.date')}}</th>
                                    <th>{{__('admin.time')}}</th>
                                    <th>{{__('admin.status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderStatusHistory as $record)
                                <?php
                                $orderStatus = $record->getOrderStatus();
                                $orderStatus = $orderStatus ? $orderStatus->order_status : '';
                                ?>
                                <tr>
                                    <td>
                                        {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_date_format'))}}
                                    </td>
                                    <td>
                                        {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_time_format'))}}
                                    </td>
                                    <td>
                                        {{$orderStatus}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <!-- /.row -->
                <!-- /.invoice -->
            </div><!-- /.col -->
        </section>
        <!-- /.content -->
        @include('layout/footer')
    </div>
    <!-- ./wrapper -->
    <script type="text/javascript">
        window.addEventListener("load", window.print());
    </script>
</body>

</html>
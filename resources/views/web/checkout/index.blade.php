@extends('web/layout/main')
@section('page_title',  trans('web.checkout'))
@section('body_class',  'menubg')
@section('content')
<?php
$total        = 0;
$language     = Helper::getLanguage();
$user         = Auth::user();
$currency     = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$def_address ='';
$selectedPickupAddress = '';
$hasCheckoutError = session()->has('checkout_error');

?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.checkout')}}</a></span>
    </div>
</section>
<section class="cart-checkout">
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
                <li>
                    <span>3</span>
                    <label>{{__('web.review_pay')}}</label>
                </li>
            </ul>
        </div>
        <div class="checkout-address">
            <div class="clearfix">
                <div class="address-list">
                    @if(count($address) > 0)
                    @foreach($address  as $record)
                    @if( $record->is_country_active)
						
					<?php
                                
								if (session('default_address') && $record->id == session('default_address') && $record->id == 43) {
                                    $record->is_default    = 1;
                                    $selectedPickupAddress = $record->id;
                                    $pickupFromStoreMessage = __('web.pickup_message', ['store' => $record->address_title]);
                                    // dd($selectedPickupAddress);

                                }
                                if (session('default_address')) {
                                    $def_address   = session('default_address');
                                }
                                
                      ?>


                    <div class="address-item options {{$record->is_default == 1 ? 'selected': ''}}">
                        <div class="radio">
                            <input type="radio" name="is_default" {{$def_address == $record->id  ? 'checked': ''}} class="change_default" data-default="{{route('web.checkout.change_default_address',['id'=>$record->id])}}" data-is-pickup="{{$record->pickupstore}}" data-addressid="{{$record->id}}">
                            <label class="contact">
                                <div class="contact-name">
                                    <span>{{$record->first_name}} {{$record->last_name}}</span>
                                    <span class="address-title">{{$record->address_title}}</span>
                                </div>
                                <div class="contact-address">
                                    {!! Helper::showAddress('my_address',$record) !!} 
                                </div>
                            </label>
                        </div>
						  @if($record->pickupstore==1)
                                                        <?php
                                                        $cPhone       = session('cphone');
                                                        $cPhone2  = ($cPhone && $selectedPickupAddress == $record->id) ? $cPhone : '';
                                                        ?>
						<input type="text" id="phone"  class="form-control number-input c_phone_{{$record->id}}" placeholder="Customer Phone Number" value="{{($cPhone && $selectedPickupAddress==$record->id)?$cPhone:''}}" maxlength="15">
						<div class="invalid-feedback">
                                                                    Please Customer Number.
                                                                </div>
						@endif
						@if($record->pickupstore==0)
                        <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="{{$record->id}}"><i class="fa fa-edit"></i></span>
					@endif
                    </div>
                    @endif
                    @endforeach
					<input type="hidden" name="def_address" id="def_address" value="{{$def_address}}">

                    @else
                    <div> {{--__('web.no-address')--}}</div> 
                    @endif

                    <div class="address-item create-new">
                        <a href="#" class="link-button add_new_address"><i class="icon-plus"></i>{{__('web.add_new_address')}}</a>
                    </div>
                </div>
            </div>
        </div>
        @if(count($address) > 0)
        <div class="page-actions">
            <button
                class="btn btn-primary continue-action-button"
                data-initial-disabled="{{ $hasCheckoutError ? '1' : '0' }}"
                {{ $hasCheckoutError ? 'disabled' : '' }}
            >Proceed to Payment</button>
        </div>
        @else
        <div class="page-actions">
            <button disabled="" class="disabled btn btn-primary continue-action-button">{{__('web.precess_to_payment')}}</button>
        </div>
        @endif
    </div>
</section>
@endsection
@section('page_specific_html')
@include('web.checkout.checkout_address')
<div class="psd" style="top: -19px; left: 0; display: none;">
    <img src="{{asset('front/img/checkout.jpg')}}">
</div>
@endsection

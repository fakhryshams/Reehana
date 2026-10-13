@extends('web/layout/main')
@section('page_title',  trans('web.my_address'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_account')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.my_address')}}">{{__('web.my_address')}}</a></span>
    </div>
</section>
<section class="profile">
    <div class="container-fluid">
        <div class="grey-background">
            <div class="mobile-white-background">
                @include('web/user/profile_menu')
                <div class="tab-content">
                    <div class="alert alert-success  alert-success-remove" style="display: none" >

                    </div>

                    <div class="alert alert-danger alert-danger-remove" style="display: none" >

                    </div>
                    <br/>

                    <div id="addresses" class="show">
                        <div class="title">
                            <h1>{{__('web.my_address')}}</h1>
                            <span class="btn btn-primary" data-toggle="modal"  data-target="#address">Add New Address</span>
                        </div>
                        <div class="address-list">
                            @if(count($address) > 0)
                            @foreach($address  as $record)
                            @if( $record->is_country_active)
                            <div class="address-item options my_address_{{$record->id}}">
                                <div class="radio">
                                    <input type="radio" name="radio">
                                    <label class="contact">
                                        <div class="contact-name">
                                            <span>{{$record->first_name}} {{$record->last_name}}</span>
                                            <span class="address-title">{{$record->address_title}}</span>
                                        </div>
                                        <?php
                                        $language = Helper::getLanguage();
                                        $city = $record->city;
                                        if ($record->city_id)
                                        {
                                            $city = $record->getUserCity($language->id) ? $record->getUserCity($language->id)->city_name : '';
                                        }
                                        ?>
                                        <div class="contact-address">
                                            {!! Helper::showAddress('my_address',$record) !!}
                                        </div>
                                    </label>
                                </div>
                                <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="{{$record->id}}"><i class="fa fa-edit"></i></span>
                             @if($record->id != '7')

                                <span class="link-button remove remove_address" data-delete="{{$record->id}}">Remove</span>
                            @endif

                            </div>
                             @endif
                            @endforeach

                            @endif
                            <div class="address-item create-new">
                                <a href="#" class="link-button  add_new_address"><i class="icon-plus"></i>{{__('web.add_new_address')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
?>
@endsection
@section('page_specific_html')
@include('web.checkout.checkout_address')
@endsection
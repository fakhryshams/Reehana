@extends('web/layout/main')
@section('page_title', trans('web.profile'))
@section('body_class', 'menubg')
@section('content')
<?php
$language = Helper::getLanguage();
$orderStatusPastForm = config('order.pastForm');
?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_account')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.my_order')}}">{{__('web.my_orders')}}</a></span>
    </div>
</section>
<section class="profile">
    <div class="container-fluid">
        <div class="grey-background">
            <div class="mobile-white-background">
                @include('web/user/profile_menu')
                <div class="tab-content">
                    <div id="orders" class="show">
                        <ul class="order-tabs">
                            <li class="active allOrders">Orders</li>
                            <li class="cancelled">Cancelled Orders</li>
                        </ul>
                        <div class="orders-content">
                            <div id="allOrders" class="show">
                                <div class="cart-table-container">
                                    <table class="cart-list-table sm-hidden">
                                        <thead>
                                            <tr>
                                                <th>{{__('web.order_date')}}</th>
                                                <th>{{__('web.type')}}</th>
                                                <th>{{__('web.order_no')}}.</th>
                                                <th>{{__('web.payment_mode')}}</th>
                                                <th>{{__('web.status')}}</th>
                                                <th>{{__('web.tracking_id')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($orders) > 0)
                                            @foreach($orders as $record)
                                            <tr>
                                                <td>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom_web.php_date_format'))}}</td>
                                                <td class="pr-2">
                                                    @if($record->custom_order_type)
                                                    <span class="badge {{$customizedBadgeColor[$record->custom_order_type]}}">{{ ucfirst(str_replace('_', ' ', $record->custom_order_type)) }}</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td><a href="{{route('web.user.order_detail',['id'=>$record->id])}}">{{$record->id}}</a></td>
                                                <?php
                                                $payment_method = Helper::getPaymentMethodByID($record->payment_method, $language->id);
                                                ?>
                                                <td>{{$payment_method}}</td>
                                                <td><span class="status {{($record->order_status == config('custom_web.delivered',4))?'delivered':'processing'}}">{{$orderStatusPastForm[$record->order_status]}}</span></td>
                                                <td>{{$record->tracking_number? $record->tracking_number: '-'}}</td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td> {{__('web.no-record-message')}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            <tr>
                                                @endif

                                        </tbody>
                                    </table>
                                </div>
                                <div class="sm-visible">
                                    @if(count($orders) > 0)
                                    @foreach($orders as $record)
                                    <div class="v-flex order-item">
                                        <div class="h-flex">
                                            <label>{{__('web.order_date')}}</label>
                                            <span>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom_web.php_date_format'))}}</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.type')}}</label>
                                            <span>
                                                @if($record->custom_order_type)
                                                <span class="badge {{$customizedBadgeColor[$record->custom_order_type]}}">{{ ucfirst(str_replace('_', ' ', $record->custom_order_type)) }}</span>
                                                @else
                                                -
                                                @endif
                                            </span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.order_no')}}</label>
                                            <span><a href="{{route('web.user.order_detail',['id'=>$record->id])}}">{{$record->id}}</a></span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.tracking_id')}}</label>
                                            <span>-</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.payment_mode')}}</label>
                                            <?php $payment_method = Helper::getPaymentMethodByID($record->payment_method, $language->id); ?>
                                            <span>{{$payment_method}}</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.status')}}</label>
                                            <span>{{$orderStatusPastForm[$record->order_status]}}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    @endif

                                </div>
                            </div>
                            <div id="cancelled">
                                <div class="cart-table-container">
                                    <table class="cart-list-table sm-hidden">
                                        <thead>
                                            <tr>
                                                <th>{{__('web.order_date')}}</th>
                                                <th>{{__('web.type')}}</th>
                                                <th>{{__('web.order_no')}}.</th>
                                                <th>{{__('web.payment_mode')}}</th>
                                                <th>{{__('web.status')}}</th>
                                                <th>{{__('web.tracking_id')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($cancel) > 0)
                                            @foreach($cancel as $record)
                                            <tr>
                                                <td>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom_web.php_date_format'))}}</td>
                                                <td class="pr-2">
                                                    @if($record->custom_order_type)
                                                    <span class="badge {{$customizedBadgeColor[$record->custom_order_type]}}">{{ ucfirst(str_replace('_', ' ', $record->custom_order_type)) }}</span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td><a href="{{route('web.user.order_detail',['id'=>$record->id])}}">{{$record->id}}</a></td>
                                                <?php $payment_method = Helper::getPaymentMethodByID($record->payment_method, $language->id); ?>
                                                <td>{{$payment_method}}</td>
                                                <td><span class="status cancelled">{{$orderStatusPastForm[$record->order_status]}}</span></td>
                                                <td><em>-</em></td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td> {{__('web.no-record-message')}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            <tr>
                                                @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="sm-visible">
                                    @if(count($cancel) > 0)
                                    @foreach($cancel as $record)
                                    <div class="v-flex order-item">
                                        <div class="h-flex">
                                            <label>{{__('web.order_date')}}</label>
                                            <span>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom_web.php_date_format'))}}</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.type')}}</label>
                                            <span>
                                                @if($record->custom_order_type)
                                                <span class="badge {{$customizedBadgeColor[$record->custom_order_type]}}">{{ ucfirst(str_replace('_', ' ', $record->custom_order_type)) }}</span>
                                                @else
                                                -
                                                @endif
                                            </span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.order_no')}}</label>
                                            <span><a href="{{route('web.user.order_detail',['id'=>$record->id])}}">{{$record->id}}</a></span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.tracking_id')}}</label>
                                            <span>-</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.payment_mode')}}</label>
                                            <?php $payment_method = Helper::getPaymentMethodByID($record->payment_method, $language->id); ?>
                                            <span>{{$payment_method}}</span>
                                        </div>
                                        <div class="h-flex">
                                            <label>{{__('web.status')}}</label>
                                            <span>{{$orderStatusPastForm[$record->order_status]}}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
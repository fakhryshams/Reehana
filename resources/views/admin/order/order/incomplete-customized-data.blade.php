@extends('layout/main')
@section('page_title',  'Customized Orders')
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
@endsection
@php
    $orderType = app('App\UserModel\Order')->getCustomOrderTypes();
@endphp
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.customized-incomplete-order')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.index')}}">{{__('admin.order')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.customized-incomplete-order')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- /.row -->
            <div class="row">
                <div class="col-12">
                    <form class="list-table" method="get" action="{{route('admin.order.customized-pending-order')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="row">
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <select name="_cty[]"  class="form-control select2 select-type {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                        @foreach($orderType as $key => $value)
                                                        <option value="{{$key}}" {{ (collect($filter['customOrderType'])->contains($key)) ? 'selected':'' }}>{{ucfirst($value)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>  
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-toolbar float-right">
                                                <div class="btn-group">
                                                    <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.search')}}">{{__('admin.search')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">   
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    {!! Helper::printPageRecord($orders) !!}
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.order.customized-incomplete-order-export', request()->except('page'))}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc" id="@if($sortKey=='id'){{$sortVal}}@endif">{{__('admin.po_no')}}/{{__('admin.quotation_no')}}<input @if($sortKey!='id')disabled=""@endif  type="hidden" name="id" value="@if($sortKey=='id'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.name')}}<input @if($sortKey!='name')disabled=""@endif  type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc">{{__('admin.type')}}</th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='paid_amount'){{$sortVal}}@endif">{{__('admin.advance_payment')}}<input @if($sortKey!='paid_amount')disabled=""@endif  type="hidden" name="paid_amount" value="@if($sortKey=='paid_amount'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='pending_amount'){{$sortVal}}@endif">{{__('admin.pending_amount')}}<input @if($sortKey!='pending_amount')disabled=""@endif  type="hidden" name="pending_amount" value="@if($sortKey=='pending_amount'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='order_total'){{$sortVal}}@endif">{{__('admin.total')}}<input @if($sortKey!='order_total')disabled=""@endif  type="hidden" name="order_total" value="@if($sortKey=='order_total'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='order_status'){{$sortVal}}@endif">{{__('admin.order_status')}}<input @if($sortKey!='order_status')disabled=""@endif  type="hidden" name="order_status" value="@if($sortKey=='order_status'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='country'){{$sortVal}}@endif">{{__('admin.country')}}<input @if($sortKey!='country')disabled=""@endif  type="hidden" name="country" value="@if($sortKey=='country'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='created_at'){{$sortVal}}@endif">{{__('admin.order-date')}}<input @if($sortKey!='created_at')disabled=""@endif  type="hidden" name="created_at" value="@if($sortKey=='created_at'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='payment_method'){{$sortVal}}@endif">{{__('admin.payment-method')}}<input @if($sortKey!='payment_method')disabled=""@endif  type="hidden" name="payment_method" value="@if($sortKey=='payment_method'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='user_agent_name'){{$sortVal}}@endif">{{__('admin.source')}}<input @if($sortKey!='user_agent_name')disabled=""@endif  type="hidden" name="user_agent_name" value="@if($sortKey=='user_agent_name'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='is_add_to_po_order'){{$sortVal}}@endif">{{__('admin.converted-to-order')}}<input @if($sortKey!='is_add_to_po_order')disabled=""@endif  type="hidden" name="is_add_to_po_order" value="@if($sortKey=='is_add_to_po_order'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.view_order')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $record)
                                    <tr>
                                        <td class="text-center">{{$record->id}}</td>
                                        <?php
                                        $user               = $record->getUser();
                                        $userAddress        = $record->getUserAddress();
                                        $userAddressCountry = $userAddress ? $userAddress->getUserCountry() : '';
                                        $orderStatus        = $record->getOrderStatus();
                                        $orderStatus        = $orderStatus ? $orderStatus->order_status : '';
                                        $paymentMethod      = $record->getPaymentMethodDetail();
                                        ?>
                                        <td>{{$user?ucfirst($user->first_name):''}} {{$user?ucfirst($user->last_name):''}}</td>
                                        <td>{{$user?$user->email:''}}</td>
                                        <td class="text-center"><span class="badge {{$customizedBadgeColor[$record->custom_order_type]}}">{{ ucfirst(str_replace('_', ' ', $record->custom_order_type)) }}</span></td>
                                        <td class="text-center">{{Helper::formatNumber($record->paid_amount)}} <small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                        <td class="text-center text-danger font-weight-bold">{{Helper::formatNumber($record->pending_amount)}} <small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                        <td class="text-center">{{Helper::formatNumber($record->order_total)}} <small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                        <td class="text-center"><span class="badge {{$badgeColor[$record->order_status]}}">{{$orderStatus}}</span></td>
                                        <td class="text-center">{{$userAddressCountry?$userAddressCountry->country_name:""}}</td>
                                        <td class="text-center">{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_datetime_format'))}}</td>
                                        <td class="text-center">{{ Helper::getPaymentMethodByID($record->payment_method) ?: ($paymentMethod?$paymentMethod->payment_method_name:'') }}</td>
                                        <td class="text-center">{{$record->user_agent_name ? $record->user_agent_name : '-'}}</td>
                                        @canany([config('custom_middleware.edit_order')])
                                        <td class="text-center">
                                            @if($record->is_add_to_po_order == 1)
                                                <span class="badge badge-secondary">{{ __('admin.yes') }}</span>
                                            @endif
                                        <td class="text-center">
                                            <a href="{{route('admin.order.customized-pending-order.vieworder',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.view_order')}}"><i class="fas fa-eye"></i></button></a>
                                            @can(config('custom_middleware.delete_order_order'))
                                            @if(collect(config('custom_web.can_delete_status'))->contains($record->order_status))
                                            <a onclick="showSweetAlert('{{route('admin.order.delete',['id'=>$record->id,'page'=>'customized'])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                            @endif
                                            @endcan
                                        </td>
                                        @endcanany
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- /.card-body -->
                        </div>
						
						
                      <div class="table-pagination pt20 float-right">
{{$orders->appends(request()->except('page'))->links('pagination::bootstrap-4')}}
                        </div>
                    </form>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('common/sorting')
@include('layout/datatable')
@include('layout/sweetalert')
@include('layout/select2') 
<script>
    $(document).ready(function ()
    {
    $(".select-status").select2({
    placeholder: "{{__('admin.filter_by_order_status')}}",
            allowClear: true
    });
    $(".select-type").select2({
    placeholder: "{{__('admin.filter_by_order_type')}}",
            allowClear: true
    });
    });
</script>
@endsection

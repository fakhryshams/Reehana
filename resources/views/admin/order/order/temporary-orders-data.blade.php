@extends('layout/main')
@section('page_title', __('admin.temporary-orders'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.temporary-orders')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.index')}}">{{__('admin.order')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.temporary-orders')}}</li>
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
                    <form class="list-table" method="get" action="{{route('admin.order.temporary-orders')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="row">
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <select name="_cty[]" class="form-control select2 select-type {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                        @foreach($orderType as $key => $value)
                                                        <option value="{{$key}}" {{ (collect($filter['customOrderType'] ?? [])->contains($key)) ? 'selected':'' }}>{{ucfirst($value)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <select name="_os[]" class="form-control select2 select-status {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                        @foreach($orderStatusList as $sKey => $sVal)
                                                        <option value="{{$sKey}}" {{ (collect($filter['orderStatus'] ?? [])->contains($sKey)) ? 'selected':'' }}>{{$sVal}}</option>
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="table-responsive">
                                <table class="table normal-table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="asc-desc text-center" id="@if($sortKey=='id'){{$sortVal}}@endif">{{__('admin.temp_order_id')}}<input @if($sortKey!='id')disabled=""@endif type="hidden" name="id" value="@if($sortKey=='id'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.name')}}<input @if($sortKey!='name')disabled=""@endif type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='email')disabled=""@endif type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='phone'){{$sortVal}}@endif">{{__('admin.phone')}}<input @if($sortKey!='phone')disabled=""@endif type="hidden" name="phone" value="@if($sortKey=='phone'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='type'){{$sortVal}}@endif">{{__('admin.type')}}<input @if($sortKey!='type')disabled=""@endif type="hidden" name="type" value="@if($sortKey=='type'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='custom_order_number'){{$sortVal}}@endif">{{__('admin.po_no')}}/{{__('admin.quotation_no')}}<input @if($sortKey!='custom_order_number')disabled=""@endif type="hidden" name="custom_order_number" value="@if($sortKey=='custom_order_number'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='status'){{$sortVal}}@endif">{{__('admin.order_status')}}<input @if($sortKey!='status')disabled=""@endif type="hidden" name="status" value="@if($sortKey=='status'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='total'){{$sortVal}}@endif">{{__('admin.total')}}<input @if($sortKey!='total')disabled=""@endif type="hidden" name="total" value="@if($sortKey=='total'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='advance'){{$sortVal}}@endif">{{__('admin.advance_payment')}}<input @if($sortKey!='advance')disabled=""@endif type="hidden" name="advance" value="@if($sortKey=='advance'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='pending'){{$sortVal}}@endif">{{__('admin.pending_amount')}}<input @if($sortKey!='pending')disabled=""@endif type="hidden" name="pending" value="@if($sortKey=='pending'){{$sortVal}}@endif"></th>
                                            <th class="text-center asc-desc" id="@if($sortKey=='created_at'){{$sortVal}}@endif">{{__('admin.order-date')}}<input @if($sortKey!='created_at')disabled=""@endif type="hidden" name="created_at" value="@if($sortKey=='created_at'){{$sortVal}}@endif"></th>
                                            <th class="text-center">{{__('admin.actual_order_id')}}</th>
                                            @canany([config('custom_middleware.view_order'), config('custom_middleware.view_order_customized_order_list')])
                                            <th class="text-center">{{__('admin.action')}}</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $record)
                                        @php
                                            $linkedOrder = $record->getLinkedOrder();
                                            $custName = $record->customer_name ?: ($record->user ? ($record->user->first_name . ' ' . $record->user->last_name) : '');
                                            $custEmail = $record->customer_email ?: ($record->user ? $record->user->email : '');
                                            $custPhone = $record->customer_phone ?: ($record->user ? $record->user->mobile : '');
                                            $currCode = $currency ? $currency->code : config('custom.currency', 'KWD');
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold">#{{$record->id}}</td>
                                            <td>{{$custName ?: '-'}}</td>
                                            <td>{{$custEmail ?: '-'}}</td>
                                            <td>{{$custPhone ?: '-'}}</td>
                                            <td class="text-center">
                                                <span class="badge {{$record->type_badge_class}}">{{ ucfirst(str_replace('_', ' ', $record->type)) }}</span>
                                            </td>
                                            <td class="text-center font-weight-bold">{{$record->formatted_document_number}}</td>
                                            <td class="text-center">
                                                <span class="badge {{$record->status_badge_class}}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                                            </td>
                                            <td class="text-center font-weight-bold">{{Helper::formatNumber($record->grand_total)}} <small>{{$currCode}}</small></td>
                                            <td class="text-center">{{Helper::formatNumber($record->advance_price)}} <small>{{$currCode}}</small></td>
                                            <td class="text-center text-danger font-weight-bold">{{Helper::formatNumber($record->pending_amount)}} <small>{{$currCode}}</small></td>
                                            <td class="text-center">{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_datetime_format'))}}</td>
                                            <td class="text-center">
                                                @if($linkedOrder)
                                                    <a href="{{route('admin.order.customized-pending-order.vieworder', ['id' => $linkedOrder->id])}}" title="{{__('admin.view_order')}}">
                                                        <span class="badge badge-success font-weight-bold">#{{$linkedOrder->id}}</span>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @canany([config('custom_middleware.view_order'), config('custom_middleware.view_order_customized_order_list')])
                                            <td class="text-center" style="white-space: nowrap;">
                                                <a href="{{route('admin.order.temporary-orders.vieworder', ['id' => $record->id])}}" title="{{__('admin.view_order')}}">
                                                    <button type="button" class="{{config('custom.btn-primary', 'btn btn-outline-primary btn-sm')}}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </a>
                                                @can(config('custom_middleware.delete_order_order'))
                                                <a onclick="showSweetAlert('{{route('admin.order.temporary-orders.delete', ['id' => $record->id])}}')" title="{{__('admin.delete')}}" style="cursor: pointer;">
                                                    <button type="button" class="{{config('custom.btn-danger', 'btn btn-outline-danger btn-sm')}}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </a>
                                                @endcan
                                            </td>
                                            @endcanany
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="13" class="text-center py-4 text-muted">{{__('admin.no_record_found')}}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
    $(document).ready(function () {
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

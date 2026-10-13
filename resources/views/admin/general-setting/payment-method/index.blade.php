@extends('layout/main')
@section('page_title',  trans('admin.payment-gateway'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
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
                    <h1>{{__('admin.payment-gateway')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">  
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.payment-gateway.index')}}">{{__('admin.payment-gateway')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.list')}}</li>
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
                    <form class="list-table" method="get" action="{{route('admin.general-setting.payment-gateway.index')}}">
                        <div class="card {{config('custom.card-primary')}}">

                            <!-- /.card-header -->
                            <table data-model="PaymentMethod"  class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc text-center" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.first_column')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='payment_method_name'){{$sortVal}}@endif">{{__('admin.payment-gateway')}}<input @if($sortKey!='payment_method_name')disabled=""@endif  type="hidden" name="payment_method_name" value="@if($sortKey=='payment_method_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_general_setting_faq')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentMethod as $record)
                                    <tr id="{{$record->id}}">
                                        <td class="text-center">{{$record->sorting}}</td>
                                        @php
                                        $description = $record->getDescription();
                                        @endphp
                                        <td>
                                            {!! $description?ucfirst($description->payment_method_name):'' !!}
                                        </td>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_general_setting_payment_gateway')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_general_setting_payment_gateway'))
                                            @if($record->can_edit)
                                            @if($record->is_active)
                                            <a href="{{route('admin.general-setting.payment-gateway.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.general-setting.payment-gateway.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
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
@include('layout/sweetalert')
@include('layout/datatable')
@endsection

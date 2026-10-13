@extends('layout/main')
@section('page_title',  trans('admin.coupon'))
@section('additional_css')
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
                    <h1>{{__('admin.coupon')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.coupon.index')}}">{{__('admin.coupon')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.master.coupon.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @canany([config('custom_middleware.create_master_coupon')])
                                        <div class="input-group-prepend pl-3">
                                            <a href="{{route('admin.master.coupon.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        </div>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->

                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='coupon_code'){{$sortVal}}@endif">{{__('admin.code')}}<input @if($sortKey!='coupon_code')disabled=""@endif  type="hidden" name="coupon_code" value="@if($sortKey=='coupon_code'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='coupon_discount'){{$sortVal}}@endif">{{__('admin.coupon-percentage')}}<input @if($sortKey!='coupon_discount')disabled=""@endif  type="hidden" name="coupon_discount" value="@if($sortKey=='coupon_discount'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='from_date'){{$sortVal}}@endif">{{__('admin.coupon-start')}}<input @if($sortKey!='from_date')disabled=""@endif  type="hidden" name="from_date" value="@if($sortKey=='from_date'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='to_date'){{$sortVal}}@endif">{{__('admin.coupon-end')}}<input @if($sortKey!='to_date')disabled=""@endif  type="hidden" name="to_date" value="@if($sortKey=='to_date'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='allow_multiple'){{$sortVal}}@endif">{{__('admin.allow-multiple')}}<input @if($sortKey!='allow_multiple')disabled=""@endif  type="hidden" name="allow_multiple" value="@if($sortKey=='allow_multiple'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='allow_all'){{$sortVal}}@endif">{{__('admin.all-allow')}}<input @if($sortKey!='allow_all')disabled=""@endif  type="hidden" name="allow_all" value="@if($sortKey=='allow_all'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='coupon_description'){{$sortVal}}@endif">{{__('admin.coupon-desc')}}<input @if($sortKey!='coupon_description')disabled=""@endif  type="hidden" name="coupon_description" value="@if($sortKey=='coupon_description'){{$sortVal}}@endif"></th>
                                        @endif
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_master_coupon'),config('custom_middleware.delete_master_coupon')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coupon as $record)
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td class="">{{$record->coupon_code}}</td>
                                        <td class="text-center">{{$record->coupon_discount}}</td>
                                        <td class="text-center">{{$record->from_date?\Carbon\Carbon::parse($record->from_date)->format(config('custom.date')):''}}</td>
                                        <td class="text-center">{{$record->to_date?\Carbon\Carbon::parse($record->to_date)->format(config('custom.date')):''}}</td>
                                        <td class="text-center">
                                            @if($record->allow_multiple)
                                            <i title="{{__('admin.yes')}}" class="fa fa-check text-success"></i>
                                            @else
                                            <i title="{{__('admin.no')}}" class="fa fa-times text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($record->allow_all)
                                            <i title="{{__('admin.yes')}}" class="fa fa-check text-success"></i>
                                            @else
                                            <i title="{{__('admin.no')}}" class="fa fa-times text-danger"></i>
                                            @endif
                                        </td>
                                        @if(false)
                                        <td class="">{{$record->coupon_description}}</td>
                                        @endif
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_master_coupon'),config('custom_middleware.delete_master_coupon')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_master_coupon'))
                                            <a href="{{route('admin.master.coupon.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @endcan
                                            @if($record->is_active)
                                            <a href="{{route('admin.master.coupon.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.master.coupon.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @can(config('custom_middleware.delete_master_coupon'))
                                            <a onclick="showSweetAlert('{{route('admin.master.coupon.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
                            {{$coupon->links('pagination::bootstrap-4')}}
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

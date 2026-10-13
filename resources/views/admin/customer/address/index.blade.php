@extends('layout/main')
@section('page_title',  trans('admin.customer_address'))
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
                    <h1>{{__('admin.customer_address')}} {{$user?'('.ucfirst($user->first_name).' '.ucfirst($user->last_name).')':''}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.index')}}">{{__('admin.customer')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.address.index',['id'=>$user->id])}}">{{__('admin.customer_address')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.customer.address.index',['id'=>$user->id])}}">
                        <input type="hidden" name="id" value="{{$user->id}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            @if(false)
                                            @canany([config('custom_middleware.create_category')])
                                            <a class="pl-2" href="{{route('admin.customer.address.add',['id'=>$user->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                            @endcanany
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center" >{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='address_title'){{$sortVal}}@endif">{{__('admin.customer_address_title')}}<input @if($sortKey!='address_title')disabled=""@endif  type="hidden" name="address_title" value="@if($sortKey=='address_title'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='first_name'){{$sortVal}}@endif">{{__('admin.customer_address_first_name')}}<input @if($sortKey!='first_name')disabled=""@endif  type="hidden" name="first_name" value="@if($sortKey=='first_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='last_name'){{$sortVal}}@endif">{{__('admin.customer_address_last_name')}}<input @if($sortKey!='last_name')disabled=""@endif  type="hidden" name="last_name" value="@if($sortKey=='last_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.customer_address_email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='phone'){{$sortVal}}@endif">{{__('admin.customer_address_phone')}}<input @if($sortKey!='phone')disabled=""@endif  type="hidden" name="phone" value="@if($sortKey=='phone'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='is_default'){{$sortVal}}@endif">{{__('admin.customer_address_default')}}<input @if($sortKey!='is_default')disabled=""@endif  type="hidden" name="is_default" value="@if($sortKey=='is_default'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="" >{{__('admin.customer_address_address')}}</th>
                                        @endif
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_category'),config('custom_middleware.delete_category')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($userAddress as $record)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ucfirst($record->address_title)}}</td>
                                        <td>{{ucfirst($record->first_name)}}</td>
                                        <td>{{ucfirst($record->last_name)}}</td>
                                        <td>{{($record->email)}}</td>
                                        <td>{{ucfirst($record->phone)}}</td>
                                        <td class="text-center">
                                            @if($record->is_default)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.yes')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.no')}}</span> 
                                            @endif
                                        </td>
                                        @if(false)
                                        <td>
                                            <?php
                                            echo '<b>Address Title</b>: ' . ucfirst($record->address_title);
                                            ?>
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_customer'),config('custom_middleware.delete_customer')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_customer'))
                                            <a href="{{route('admin.customer.address.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @endcan
                                            @if(false)
                                            @if($record->is_active)
                                            <a href="{{route('admin.customer.address.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.customer.address.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endif
                                            @can(config('custom_middleware.delete_customer'))
                                            <a onclick="showSweetAlert('{{route('admin.customer.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
                            {{$userAddress->appends(request()->input())->links()}}
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

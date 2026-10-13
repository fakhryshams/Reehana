@extends('layout/main')
@section('page_title',  trans('admin.shipping_method'))
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.shipping_method')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.shipping.index')}}">{{__('admin.shipping_method')}}</a></li>
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
                    <form class="list-table" id="shipping"  method="get" action="{{route('admin.shipping.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="card-header">
                                <h3 class="card-title">{{__('admin.list')}}</h3>
                                <div class="card-tools">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="q" class="form-control float-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->

                            <table  class="table normal-table table-striped">
                                <thead>
                                    <tr>

                                        <th >{{__('admin.default')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='shipping_name'){{$sortVal}}@endif">{{__('admin.shipping_name')}}<input @if($sortKey!='shipping_name')disabled=""@endif  type="hidden" name="shipping_name" value="@if($sortKey=='shipping_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='price'){{$sortVal}}@endif">{{__('admin.price')}}<input @if($sortKey!='price')disabled=""@endif  type="hidden" name="price" value="@if($sortKey=='price'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_shipping_method'),config('custom_middleware.delete_shipping_method')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shippingMethod as $record)
                                    <tr>
                                        <td>
                                            <label>
                                                <input type="radio" name="is_default"  {{$record->is_default? 'checked' : '' }}    value="{{$record->id}}" class="shipping">
                                            </label>
                                        </td>
                                        @php
                                        $shippingName = $record->getShippingMethodDescription();
                                        @endphp
                                        <td>{{$shippingName?ucfirst($shippingName->shipping_name):''}}
                                        </td>
                                        <td>{{$record->price? $record->price : '-'}}</td>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>

                                        @canany([config('custom_middleware.edit_shipping_method'),config('custom_middleware.delete_shipping_method')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_shipping_method'))
                                            <a href="{{route('admin.shipping.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.shipping.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.shipping.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
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
                            {{$shippingMethod->appends(request()->input())->links()}}
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
<script>
    $("input:radio[name=is_default]").click(function () {
        $("#shipping").submit();
    });
</script>
@include('common/sorting')
@include('layout/sweetalert')
@endsection

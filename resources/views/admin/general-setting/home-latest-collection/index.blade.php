@extends('layout/main')
@section('page_title',  trans('admin.home-latest-collection'))
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
                    <h1>{{__('admin.home-latest-collection')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">  
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.home-latest-collection.index')}}">{{__('admin.home-latest-collection')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.general-setting.home-latest-collection.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    <div class="float-right">
                                        @if($collectionCount < config('custom.max_data_display'))
                                        @canany([config('custom_middleware.create_general_setting_home_latest_collection')])
                                        <a class="float-right float-sm-right" href="{{route('admin.general-setting.home-latest-collection.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th>{{__('admin.category')}}</th>
                                        <th>{{__('admin.subcategory')}}</th>
                                        <th>{{__('admin.product')}}</th>
                                        <th class="text-center">{{__('admin.image')}}</th>
                                        <th class="text-center">{{__('admin.is_center')}}</th>
                                        <th class="text-center">{{__('admin.status')}}</th>
                                        @canany([config('custom_middleware.edit_general_setting_home_latest_collection')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($homeLatestCollection as $record)
                                    <tr id="{{$record->id}}">
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        @php
                                        $categoryDescription = $record->getCategoryDescription();
                                        $subCategoryDescription = $record->getSubCategoryDescription();
                                        $product = $record->getProductDescription();
                                        @endphp
                                        <td>
                                            {{$categoryDescription?ucfirst($categoryDescription->categories_name):''}}
                                        </td>
                                        <td>
                                            {{$subCategoryDescription?ucfirst($subCategoryDescription->categories_name):''}}
                                        </td>
                                        <td>
                                            {{$product?ucfirst($product->products_name):''}}
                                        </td>

                                        <td class="reorder-exclude text-center">
                                            <img class="logo-table" src="{{asset(Storage::url($record->image))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)">
                                        </td>

                                        <td class="text-center">
                                            <div class="icheck-primary">
                                                <input disabled="" type="checkbox" {{$record->is_center == 1?'checked':''}}>
                                                <label for="is_center">
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>

                                        @canany([config('custom_middleware.edit_general_setting_home_latest_collection'),config('custom_middleware.delete_general_setting_home_latest_collection')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_general_setting_home_latest_collection'))
                                            <a href="{{route('admin.general-setting.home-latest-collection.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.general-setting.home-latest-collection.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.general-setting.home-latest-collection.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endcan
                                            @can(config('custom_middleware.delete_general_setting_home_latest_collection'))
                                            <a onclick="showSweetAlert('{{route('admin.general-setting.home-latest-collection.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
@include('layout/sweetalert')
@include('layout/datatable')
@include('layout/image_preview')
@endsection

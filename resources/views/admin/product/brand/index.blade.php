@extends('layout/main')
@section('page_title',  trans('admin.brand'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
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
                    <h1>{{__('admin.brand')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.brand.index')}}">{{__('admin.brand')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.product.brand.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <!--<h4 class="card-title ml-3">{{__('admin.list')}}</h4>-->
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            @canany([config('custom_middleware.add_brand')])
                                            <a class="pl-2" href="{{route('admin.product.brand.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                            @endcanany
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"> {{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='manufacturer_name'){{$sortVal}}@endif">{{__('admin.name')}}<input @if($sortKey!='manufacturer_name')disabled=""@endif  type="hidden" name="manufacturer_name" value="@if($sortKey=='manufacturer_name'){{$sortVal}}@endif"></th>
                                        <th class="" >{{__('admin.image')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='manufacturer_url'){{$sortVal}}@endif">{{__('admin.manufacturer_url')}}<input @if($sortKey!='manufacturer_url')disabled=""@endif  type="hidden" name="manufacturer_url" value="@if($sortKey=='manufacturer_url'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_product')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($manufacturer as $record)
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>{{$record->manufacturer_name}}</td>
                                        <td> <img class="logo-table text-center" src="{{ Storage::url($record->manufacturer_image)}}"/></td>

                                        <td>{{$record->manufacturer_url ? $record->manufacturer_url : '-'}}</td>


                                        @canany([config('custom_middleware.edit_brand')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_brand'))
                                            <a href="{{route('admin.product.brand.edit',['id' => $record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
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
                            {{$manufacturer->links('pagination::bootstrap-4')}}
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

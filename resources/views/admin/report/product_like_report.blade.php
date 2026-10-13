@extends('layout/main')
@section('page_title',  trans('admin.menu_report_product_liked'))
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
                    <h1>{{__('admin.state-product-like')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.state-product-like')}}</li>

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
                    <form class="list-table" method="get" action="{{route('admin.report.product-like')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.report.product-like-export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{__('admin.first_column')}}</th>
                                        <th class="text-center">{{__('admin.image')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='product'){{$sortVal}}@endif">{{__('admin.product')}}<input @if($sortKey!='product')disabled=""@endif  type="hidden" name="product" value="@if($sortKey=='product'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='liked_count'){{$sortVal}}@endif">{{__('admin.liked_count')}}<input @if($sortKey!='liked_count')disabled=""@endif  type="hidden" name="liked_count" value="@if($sortKey=='liked_count'){{$sortVal}}@endif"></th>
                                        <!--<th>{{__('admin.view')}}</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $report)
                                    <?php
                                    $product     = $report->getProductData();
                                    $productName = "";
                                    if ($product)
                                    {
                                        $productName = $product->getProductDescription();
                                    }
                                    $defaultImage = $product ? $product->getProductImages() : '';
                                    ?>
                                    <tr>
                                        <td class="">{{$loop->iteration}}</td>
                                        <td class="text-center"><img class="logo-table text-center" alt='' src="{{asset(Storage::url($defaultImage?$defaultImage->image_thumb_url:''))}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/>
                                        <td>{{$productName?ucfirst($productName->products_name):''}}</td>
                                        <td class="text-center">{{$report->no_of_product}}</td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$reportData->links('pagination::bootstrap-4')}}
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
@include('layout/image_preview')
@endsection

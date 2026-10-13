@extends('layout/main')
@section('page_title',  trans('admin.menu_report_coupon'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<style>
    .custom-search{
        width: auto !important;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.coupon-report')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.coupon-report')}}</li>
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
                    <form class="list-table" method="get" action="{{route('admin.report.coupon-report')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <div class="input-group-append">
                                            <a class="pl-2" href="{{route('admin.report.coupon-report-export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='coupon'){{$sortVal}}@endif">{{__('admin.coupon')}}<input @if($sortKey!='coupon')disabled=""@endif  type="hidden" name="coupon" value="@if($sortKey=='coupon'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='order_count'){{$sortVal}}@endif">{{__('admin.total-order')}}<input @if($sortKey!='order_count')disabled=""@endif  type="hidden" name="order_count" value="@if($sortKey=='order_count'){{$sortVal}}@endif"></th>
                                        <!--<th>{{__('admin.view')}}</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $report)
                                    <?php
                                    $coupon     = $report->getCouponDetails();
                                    $couponName = "";
                                    $couponID   = "";
                                    if ($coupon)
                                    {
                                        $couponID   = $coupon->id;
                                        $couponName = $coupon->coupon_code;
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td class="text-center"><a style="text-decoration:underline;" href="{{route('admin.order.index',['coupon'=> $couponID])}}" target="_blank" > {{$couponName}}</a></td>
                                        <td class="text-center">{{$report->no_of_order}}</td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$reportData->appends(request()->input())->links()}}
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

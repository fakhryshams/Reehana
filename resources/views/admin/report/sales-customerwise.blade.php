@extends('layout/main')
@section('page_title',  trans('admin.menu_report_sales_customer'))
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
                    <h1>{{__('admin.sales-report-customer')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.sales-report-customer')}}</li>
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
                    <form class="list-table" method="get" action="{{route('admin.report.sales-customer')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.report.sales-customer-export').'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='f_name'){{$sortVal}}@endif">{{__('admin.first_name')}}<input @if($sortKey!='f_name')disabled=""@endif  type="hidden" name="f_name" value="@if($sortKey=='f_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='l_name'){{$sortVal}}@endif">{{__('admin.last_name')}}<input @if($sortKey!='l_name')disabled=""@endif  type="hidden" name="l_name" value="@if($sortKey=='l_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='mobile'){{$sortVal}}@endif">{{__('admin.mobile')}}<input @if($sortKey!='mobile')disabled=""@endif  type="hidden" name="mobile" value="@if($sortKey=='mobile'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='total_order'){{$sortVal}}@endif">{{__('admin.total-order')}}<input @if($sortKey!='total_order')disabled=""@endif  type="hidden" name="total_order" value="@if($sortKey=='total_order'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='total_sale'){{$sortVal}}@endif">{{__('admin.total-sales')}}<input @if($sortKey!='total_sale')disabled=""@endif  type="hidden" name="total_sale" value="@if($sortKey=='total_sale'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='coupon_mail_sent_date'){{$sortVal}}@endif">{{__('admin.coupon_mail_sent_date')}}<input @if($sortKey!='coupon_mail_sent_date')disabled=""@endif  type="hidden" name="coupon_mail_sent_date" value="@if($sortKey=='coupon_mail_sent_date'){{$sortVal}}@endif"></th>
                                        <th class="text-center">{{__('admin.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $report)
                                    <?php
                                    $userDetail = $report->getUser();
                                    ?>
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$userDetail?ucfirst($userDetail->first_name):""}}</td>
                                        <td>{{$userDetail?ucfirst($userDetail->last_name):""}}</td>
                                        <td>{{$userDetail?$userDetail->email:""}}</td>
                                        <td>{{$userDetail?$userDetail->mobile:""}}</td>
                                        <td class="text-center">{{$report->no_of_order}}</td>
                                        <td class="text-center">{{Helper::formatNumber($report->sum_order_total)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        <td class="text-center">{{$userDetail?($userDetail->coupon_mail_sent_date?\Carbon\Carbon::parse($userDetail->coupon_mail_sent_date)->format(config('custom.date-time')):''):""}}</td>
                                        <td class="text-center">
                                            @if($userDetail)
                                            <a href="{{route('admin.master.coupon.send-mail',['id'=>$userDetail->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.send_mail')}}"><i class="fa fa-mail-bulk"></i> {{__('admin.send_mail')}}</button></a>
                                            @endif
                                        </td>
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
@endsection

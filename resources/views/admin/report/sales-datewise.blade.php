@extends('layout/main')
@section('page_title', trans('admin.menu_report_sales'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<style>
    .custom-search {
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
                    <h1>{{__('admin.sales-report')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.sales-report')}}</li>

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
                    <form class="list-table" method="get" action="{{route('admin.report.sales-date')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="row p-0">
                                                <div class="form-group col-md-4 ">
                                                    <input readonly="" data-toggle="datetimepicker" data-target=".start-date" class="start-date form-control datetimepicker-input" placeholder="{{__('admin.from_date')}}" name="from_date" type="text" data-value="{{$filter['fromDate']}}" data-start-date="{{date("Y-m-d",strtotime($filter['fromDate']))}}">
                                                </div>
                                                <div class="form-group col-md-4 ">
                                                    <input readonly="" data-toggle="datetimepicker" data-target=".end-date" class="end-date form-control datetimepicker-input" placeholder="{{__('admin.to_date')}}" name="to_date" type="text" data-value="{{$filter['toDate']}}">
                                                </div>
                                                <div class="col-sm-4">

                                                    <div class="form-group">
                                                        <select class="form-control" name="type">
                                                            <option {{$filter['type'] ==1 ? 'selected' : ''}}  value="1">All</option>
                                                            <option {{$filter['type'] ==2 ? 'selected' : ''}} value="2">Website</option>
                                                            <option {{$filter['type'] ==3 ? 'selected' : ''}} value="3">Store</option>
                                                        </select>
                                                    </div>
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
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <div class="input-group-append">
                                            <a class="pl-2" href="{{route('admin.report.sales-date-export').'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @if(false)
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.report.sales-date-export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{__('admin.first_column')}}</th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='created_at'){{$sortVal}}@endif">{{__('admin.order_date')}}<input @if($sortKey!='created_at' )disabled="" @endif type="hidden" name="created_at" value="@if($sortKey=='created_at'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='order_total'){{$sortVal}}@endif">{{__('admin.total_sales')}}<input @if($sortKey!='order_total' )disabled="" @endif type="hidden" name="order_total" value="@if($sortKey=='order_total'){{$sortVal}}@endif"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sum = 0;
                                    ?>
                                    @forelse($reportData as $report)
                                    <tr>
                                        <td class="">{{$loop->iteration}}</td>
                                        <td class="text-center">{{\Carbon\Carbon::parse($report->created_at)->format(config('custom.php_date_format'))}}</td>
                                        <td class="text-center">{{Helper::formatNumber($report->order_total)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
                                    </tr>
                                    <?php $sum += $report->order_total; ?>
                                    @if($loop->last)
                                    <tr>
                                        <td class=""></td>
                                        <td class="text-center"><strong>{{__('admin.total_sales')}} : </strong></td>
                                        <td class="text-center"><strong>{{Helper::formatNumber($sum)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small></strong></td>
                                    </tr>
                                    @endif
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">

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
<script src="{{asset('plugins/moment/moment.min.js')}}" crossorigin="anonymous"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-4.js')}}" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        var startDateVal = $(".start-date").attr('data-value');
        var endDateVal = $(".end-date").attr('data-value');

        startDate(startDateVal);
        endDate($(".start-date").attr("data-start-date"), endDateVal);

        $(".start-date").on('change.datetimepicker', function(e) {
            $(".end-date").datetimepicker("destroy");
            $(".end-date").val("");
            endDate(e.date, '');
            $(".end-date").datetimepicker('minDate', e.date);
        });
    });

    function startDate(startDateVal) {
        $(".start-date").datetimepicker({
            format: "{{config('custom.js_date_format')}}",
            ignoreReadonly: true,
            buttons: {
                showClear: false,
            },
            icons: {
                clear: 'fa fa-trash',
            },
        });
        $(".start-date").val(startDateVal);
    }

    function endDate(startDateVal, endDateVal = '') {
        $(".end-date").datetimepicker({
            format: "{{config('custom.js_date_format')}}",
            minDate: moment(startDateVal),
            ignoreReadonly: true,
            buttons: {
                showClear: false,
            },
            icons: {
                clear: 'fa fa-trash',
            }
        });

        if (endDateVal) {
            $(".end-date").val(endDateVal);
        }
    }
</script>

@endsection
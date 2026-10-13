@extends('layout/main')
@section('page_title',  trans('admin.menu_report_sales_year'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<style>
    .table tr:last-child
    {
        font-weight: bold;
    }
</style>
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
                    <h1>{{__('admin.sales-report-year')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.sales-report-year')}}</li>

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
                    <form class="list-table" method="get" action="{{route('admin.report.sales-year')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="row p-0">
                                                <div class="form-group col-md-4 ">
                                                    <input readonly="" data-toggle="datetimepicker" data-target=".start-date" class="start-date form-control datetimepicker-input" placeholder="{{__('admin.from_year')}}" name="from_date" type="text" data-value="{{$filter['fromDate']}}" data-end-date="{{date("Y-m-d",strtotime($filter['toDateEnd']))}}" data-start-date="{{date("Y-m-d",strtotime($filter['toDateStart']))}}" data-year-limit="{{$filter['yearLimit']}}" data-year-gap="{{$filter['yearGap']}}">
                                                </div>
                                                <div class="form-group col-md-4 ">
                                                    <input readonly="" data-toggle="datetimepicker" data-target=".end-date" class="end-date form-control datetimepicker-input" placeholder="{{__('admin.to_year')}}" name="to_date" type="text" data-value="{{$filter['toDate']}}">
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
                                <div class="input-group-append float-right">
                                    <a class="pl-2" href="{{route('admin.report.sales-year-export').'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                </div>
                                @if(false)
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">

                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.month')}}</th>
                                        <?php
                                        for ($i = $filter['fromDate']; $i <= $filter['toDate']; $i++)
                                        {
                                            ?>
                                            <th class="text-center">{{$i}}</th>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($response as $data)
                                    <tr>
                                        @foreach($data as $innerData)
                                        <td class="text-center">
                                            @if(!$loop->first)
                                            {{Helper::formatNumber($innerData)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small>
                                            @else
                                            {!! $innerData !!} 
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
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
$(document).ready(function ()
{
    var startDateLimit = $(".start-date").attr('data-start-date');
    var endDateLimit = $(".start-date").attr('data-end-date');

    var startDateVal = $(".start-date").attr('data-value');
    var endDateVal = $(".end-date").attr('data-value');
    var yearLimitVal = $(".start-date").attr('data-year-limit');
    var yearGap = $(".start-date").attr('data-year-gap');

    startDate(startDateVal);
    /*
     * 1. Call End Date Function
     */
    endDate(startDateLimit, endDateLimit, endDateVal);

    $(".start-date").on('change.datetimepicker', function (e)
    {
        $(".end-date").datetimepicker("destroy");
        $(".end-date").val("");

        var fromYear = $(".start-date").val();
        var startDateInput = parseInt(fromYear) + parseInt(yearLimitVal);
        var defaultValue = parseInt(fromYear) + parseInt(yearGap);

        startDateVal = fromYear + "-01-01";
        endDateVal = startDateInput + "-12-31";
        /*
         * 2. Call End Date Function
         */
        endDate(startDateVal, endDateVal, defaultValue);
    });
});
function startDate(startDateVal)
{
    $(".start-date").datetimepicker({
        format: "{{config('custom.js_date_format_year')}}",
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
function endDate(minDate, maxDate, defaultVal = '')
{
    $(".end-date").datetimepicker({
        format: "{{config('custom.js_date_format_year')}}",
        minDate: minDate.toString(),
        maxDate: maxDate.toString(),
        ignoreReadonly: true,
        buttons: {
            showClear: false,
        },
        icons: {
            clear: 'fa fa-trash',
        }
    });

    if (defaultVal)
    {
        $(".end-date").val(defaultVal);
}
}
</script>

@endsection

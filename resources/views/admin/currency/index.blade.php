@extends('layout/main')
@section('page_title',  trans('admin.currency'))
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
                    <h1>{{__('admin.currency')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.currency.index')}}">{{__('admin.currency')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.currency.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="card-header">
                                <div class="table-card card-header pl-0">
                                    <div class="card-tools w-100">
                                        @include('common/paginate')
                                        <div class="input-group input-group-sm float-right custom-search">
                                            <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            </div>
                                            @canany([config('custom_middleware.edit_currency')])
                                            <div class="input-group-prepend pl-3">
                                                <a href="{{route('admin.currency.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                            </div>
                                            @endcanany
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-header -->

                                <table class="table normal-table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center" >{{__('admin.first_column')}}</th>                                            <th class="asc-desc" id="@if($sortKey=='country'){{$sortVal}}@endif">{{__('admin.country')}}<input @if($sortKey!='country')disabled=""@endif  type="hidden" name="country" value="@if($sortKey=='country'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='currency_code'){{$sortVal}}@endif">{{__('admin.currency_code')}}<input @if($sortKey!='currency_code')disabled=""@endif  type="hidden" name="currency_code" value="@if($sortKey=='currency_code'){{$sortVal}}@endif"></th>
                                            <th class="" id="@if($sortKey=='symbole'){{$sortVal}}@endif">{{__('admin.symbol')}}<input @if($sortKey!='code')disabled=""@endif  type="hidden" name="code" value="@if($sortKey=='code'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='position'){{$sortVal}}@endif">{{__('admin.currency_position')}}<input @if($sortKey!='position')disabled=""@endif  type="hidden" name="position" value="@if($sortKey=='position'){{$sortVal}}@endif"></th>
    <!--                                            <th class="asc-desc" id="@if($sortKey=='decimal_point'){{$sortVal}}@endif">{{__('admin.number_of_decimal')}}<input @if($sortKey!='decimal_point')disabled=""@endif  type="hidden" name="decimal_point" value="@if($sortKey=='decimal_point'){{$sortVal}}@endif"></th>
                                            <th class="" id="@if($sortKey=='thousands_seperator'){{$sortVal}}@endif">{{__('admin.thousand_separator')}}<input @if($sortKey!='thousands_seperator')disabled=""@endif  type="hidden" name="thousands_seperator" value="@if($sortKey=='thousands_seperator'){{$sortVal}}@endif"></th>
                                            <th class="" id="@if($sortKey=='decimal_seperator'){{$sortVal}}@endif">{{__('admin.decimal_separator')}}<input @if($sortKey!='decimal_seperator')disabled=""@endif  type="hidden" name="decimal_seperator" value="@if($sortKey=='decimal_seperator'){{$sortVal}}@endif"></th>
                                            <th class="asc-desc" id="@if($sortKey=='value'){{$sortVal}}@endif">{{__('admin.currency_value')}}<input @if($sortKey!='value')disabled=""@endif  type="hidden" name="value" value="@if($sortKey=='value'){{$sortVal}}@endif"></th>-->
                                            @canany([config('custom_middleware.edit_currency')])
                                            <th class="text-center">{{__('admin.action')}}</th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($currency as $record)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <?php
                                            $country     = '-';
                                            $countrycode = '-';
                                            $symbol      = '-';
                                            if ($record->getCountry())
                                            {
                                                $countryData = $record->getCountry();
                                                $country     = $countryData->country_name;
                                                $countrycode = $countryData->country_code;
                                                $symbol      = $countryData->symbol;
                                                $symbol      = $countryData->symbol;
                                            }
                                            ?> 
                                            <td>{{ucwords($country )}}</td>
                                            <td>{{$countrycode}}</td>
                                            <td>{{$symbol}}</td>
                                            <td>{{$record->position}}</td>
    <!--                                            <td>{{$record->decimal_point}}</td>
                                            <td>{{$record->thousands_seperator}}</td>
                                            <td>{{$record->decimal_seperator}}</td>
                                            <td>{{$record->value}}</td>-->

                                            @canany([config('custom_middleware.edit_currency')])
                                            <td class="text-center">
                                                @can(config('custom_middleware.edit_currency'))
                                                <a href="{{route('admin.currency.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
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
                                {{$currency->appends(request()->input())->links()}}
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

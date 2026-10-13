@extends('layout/main')
@section('page_title',  trans('admin.alert-setting'))
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
                    <h1>{{__('admin.alert-setting')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.alert-setting.index')}}">{{__('admin.alert-setting')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.general-setting.alert-setting.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
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
                                        <th class="text-cener">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='setting_key'){{$sortVal}}@endif">{{__('admin.alert-setting_name')}}<input @if($sortKey!='setting_key')disabled=""@endif  type="hidden" name="setting_key" value="@if($sortKey=='setting_key'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='setting_val'){{$sortVal}}@endif">{{__('admin.alert-setting_value')}}<input @if($sortKey!='setting_val')disabled=""@endif  type="hidden" name="setting_val" value="@if($sortKey=='setting_val'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='description'){{$sortVal}}@endif">{{__('admin.alert-setting_desc')}}<input @if($sortKey!='description')disabled=""@endif  type="hidden" name="description" value="@if($sortKey=='description'){{$sortVal}}@endif"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($settings as $record)
                                    <tr>
                                        <td class="text-cener">{{$loop->iteration}}</td>
                                        <td>{{ucwords($record->setting_key)}}</td>
                                        <td><input class="alert-checkbox" data-id='{{$record->id}}' value="1" type="checkbox" {{$record->setting_val==1?'checked':''}} data-bootstrap-switch data-on-text="{{__('admin.alert-switch-yes')}}" data-off-text="{{__('admin.alert-switch-no')}}"></td>
                                        <td>{!! ucwords($record->description) !!}</td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$settings->appends(request()->input())->links()}}
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
@include('layout/bootstrap_switch')
@endsection

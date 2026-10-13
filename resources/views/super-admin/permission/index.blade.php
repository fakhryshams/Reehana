@extends('layout/main')
@section('page_title',  trans('admin.permission'))
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
                    <h1>{{__('admin.permission')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.permission.index')}}">{{__('admin.permission')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('super-admin.permission.index')}}">
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
                            <table data-model="" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc text-center" id="@if($sortKey=='id'){{$sortVal}}@endif">{{__('admin.permission_id')}}<input @if($sortKey!='id')disabled=""@endif  type="hidden" name="id" value="@if($sortKey=='id'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.permission_name')}}<input @if($sortKey!='name')disabled=""@endif  type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='description'){{$sortVal}}@endif">{{__('admin.permission_desc')}}<input @if($sortKey!='description')disabled=""@endif  type="hidden" name="description" value="@if($sortKey=='description'){{$sortVal}}@endif"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permissions as $record)
                                    <tr>
                                        <td class="text-center">{{$record->id}}</td>
                                        <td>{{ucwords($record->name)}}</td>
                                        <td>{{ucfirst($record->description)}}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-danger" colspan="3">{{__('admin.no_record_found')}}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$permissions->appends(request()->input())->links()}}
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
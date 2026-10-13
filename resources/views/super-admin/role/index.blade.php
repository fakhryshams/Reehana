@extends('layout/main')
@section('page_title',  trans('admin.role'))
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
                    <h1>{{__('admin.role')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.role.index')}}">{{__('admin.role')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('super-admin.role.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @can(config('custom_middleware.create_super_admin_role'))
                                        <a class="pl-2" href="{{route('super-admin.role.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="Role" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc" id="@if($sortKey=='id'){{$sortVal}}@endif">{{__('admin.role_id')}}<input @if($sortKey!='id')disabled=""@endif  type="hidden" name="id" value="@if($sortKey=='id'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.role_name')}}<input @if($sortKey!='name')disabled=""@endif  type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_super_admin_role'),config('custom_middleware.delete_super_admin_role'),config('custom_middleware.view_super_admin_role_permission')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $record)
                                    <tr>
                                        <td>{{$record->id}}</td>
                                        <td>{{ucwords($record->name)}}</td>
                                        @canany([config('custom_middleware.edit_super_admin_role'),config('custom_middleware.delete_super_admin_role'),config('custom_middleware.view_super_admin_role_permission')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_super_admin_role'))
                                            <a href="{{route('super-admin.role.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @endcan
                                            @can(config('custom_middleware.delete_super_admin_role'))
                                            <a onclick="showSweetAlert('{{route('super-admin.role.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                            @endcan
                                            @can(config('custom_middleware.view_super_admin_role_permission'))
                                            <a href='{{route('super-admin.role-permission.view',['id'=>$record->id])}}' class="{{config('custom.btn-info','btn btn-outline-info btn-sm')}}" title="{{__('admin.view_role_permission')}}"><i class="fas fa-eye"></i></a>
                                            @endcan
                                        </td>
                                        @endcanany
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
                            {{$roles->appends(request()->input())->links()}}
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

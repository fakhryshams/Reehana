@extends('layout/main')
@section('page_title',  trans('admin.user_role'))
@section('additional_css')
<!--select2 css-->
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<!--/ select2 css -->
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.user_role')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.user-role.index')}}">{{__('admin.user_role')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.list')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form class="list-table" method="get" action="{{route('super-admin.user-role.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @can(config('custom_middleware.create_super_admin_user_role'))
                                        <!--<div class="input-group-append">-->
                                        <a class="pl-2" href="{{route('super-admin.user-role.bulkAssign')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.user_role_bulk_assign')}}">{{__('admin.user_role_bulk_assign')}}</button></a>
                                        <a class="pl-2" href="{{route('super-admin.user-role.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add-user')}}">{{__('admin.add-user')}}</button></a>
                                        <!--</div>-->
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc text-center" id="@if($sortKey=='id'){{$sortVal}}@endif">{{__('admin.user_id')}}<input @if($sortKey!='id')disabled=""@endif  type="hidden" name="id" value="@if($sortKey=='id'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='username'){{$sortVal}}@endif">{{__('admin.user_name')}}<input @if($sortKey!='username')disabled=""@endif  type="hidden" name="username" value="@if($sortKey=='username'){{$sortVal}}@endif"></th>
                                        <th class="text-center">{{__('admin.user_role')}}</th>
                                        @canany([config('custom_middleware.edit_super_admin_user_role')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user as $record)
                                    <tr>
                                        <td class="text-center">{{$record ? $record->id :''}}</td>
                                        <td>{{$record ? ucfirst($record->name.' '.$record->last_name) :''}} ({{__('admin.email_username')}} : {{$record->email}} / {{$record->username}})</td>
                                        @php
                                        $role = $record->getRoleNames();
                                        $userRoleList = "";
                                        foreach($role as $role)
                                        {
                                        $userRoleList.= ucfirst($role).", ";
                                        }
                                        $userRoleList = rtrim($userRoleList,", ");
                                        @endphp
                                        <td class="text-center">{{$userRoleList ? $userRoleList :''}}</td>
                                        @can(config('custom_middleware.edit_super_admin_user_role'))
                                        <td class="text-center">
                                            <a href="{{route('super-admin.user-role.update-role',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('super-admin.user-role.change-status',['id'=>$record->id,'active'=>0]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('super-admin.user-role.change-status',['id'=>$record->id,'active'=>1]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif                                           
                                        </td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$user->appends(request()->input())->links()}}
                        </div>
                    </form>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('common/sorting')
@include('layout/sweetalert')
@include('layout/select2')
@include('layout/datatable')
@endsection


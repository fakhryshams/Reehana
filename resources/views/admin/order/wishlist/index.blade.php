@extends('layout/main')
@section('page_title',  trans('admin.wish-list'))
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
                    <h1>{{__('admin.wish-list')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.wish-list')}}</li>
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
                    <form class="list-table" method="get" action="{{route('admin.order.wishlist.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.order.wishlist.export-wishlist')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->

                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"> {{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='user_first_name'){{$sortVal}}@endif">{{__('admin.first-name')}}<input @if($sortKey!='user_first_name')disabled=""@endif  type="hidden" name="user_first_name" value="@if($sortKey=='user_first_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='user_last_name'){{$sortVal}}@endif">{{__('admin.last-name')}}<input @if($sortKey!='user_last_name')disabled=""@endif  type="hidden" name="user_last_name" value="@if($sortKey=='user_last_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='user_email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='user_email')disabled=""@endif  type="hidden" name="user_email" value="@if($sortKey=='user_email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='updated_at'){{$sortVal}}@endif">{{__('admin.update_date')}}<input @if($sortKey!='updated_at')disabled=""@endif  type="hidden" name="updated_at" value="@if($sortKey=='updated_at'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='mail_sent_date'){{$sortVal}}@endif">{{__('admin.email_send_date')}}<input @if($sortKey!='mail_sent_date')disabled=""@endif  type="hidden" name="mail_sent_date" value="@if($sortKey=='mail_sent_date'){{$sortVal}}@endif"></th>
                                        @endif
                                        @canany([config('custom_middleware.view_order_wish_list')])
                                        <th class="">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($wishlist as $record)
                                    @php
                                    $user = $record->getUserData();
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>{{$user?$user->first_name:''}}</td>
                                        <td>{{$user?$user->last_name:''}}</td>
                                        <td>{{$user?$user->email:''}}</td>
                                        <td>{{$record->updated_at?\Carbon\Carbon::parse($record->updated_at)->format(config('custom.php_date_format')):''}}</td>
                                        @if(false)
                                        <td>{{$record->mail_sent_date?\Carbon\Carbon::parse($record->mail_sent_date)->format(config('custom.php_datetime_format')):''}}</td>
                                        @endif
                                        @canany([config('custom_middleware.view_order_wish_list')])
                                        <td>
                                            @can(config('custom_middleware.view_order_wish_list'))
                                            <a href="{{route('admin.order.wishlist.view',['id'=>$record->user_id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.view')}}"><i class="fas fa-eye"></i></button></a>
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
                            {{$wishlist->appends(request()->input())->links()}}
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

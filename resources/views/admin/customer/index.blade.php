@extends('layout/main')
@section('page_title',  trans('admin.customer'))
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
                    <h1>{{__('admin.customer')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.index')}}">{{__('admin.customer')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.customer.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    {!! Helper::printPageRecord($customer) !!}
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.customer.export-customer')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @if(false)
                                        @canany([config('custom_middleware.add_customer')])
                                        <a class="pl-2" href="{{route('admin.customer.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='first_name'){{$sortVal}}@endif">{{__('admin.first_name')}}<input @if($sortKey!='first_name')disabled=""@endif  type="hidden" name="first_name" value="@if($sortKey=='first_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='last_name'){{$sortVal}}@endif">{{__('admin.last_name')}}<input @if($sortKey!='last_name')disabled=""@endif  type="hidden" name="last_name" value="@if($sortKey=='last_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='mobile'){{$sortVal}}@endif">{{__('admin.mobile')}}<input @if($sortKey!='mobile')disabled=""@endif  type="hidden" name="mobile" value="@if($sortKey=='mobile'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='login_type'){{$sortVal}}@endif">{{__('admin.login_type')}}<input @if($sortKey!='login_type')disabled=""@endif  type="hidden" name="login_type" value="@if($sortKey=='login_type'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='user_type'){{$sortVal}}@endif">{{__('admin.type')}}<input @if($sortKey!='user_type')disabled=""@endif  type="hidden" name="user_type" value="@if($sortKey=='user_type'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='last_login'){{$sortVal}}@endif">{{__('admin.last_login')}}<input @if($sortKey!='last_login')disabled=""@endif  type="hidden" name="last_login" value="@if($sortKey=='last_login'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='created_at'){{$sortVal}}@endif">{{__('admin.register_date')}}<input @if($sortKey!='created_at')disabled=""@endif  type="hidden" name="created_at" value="@if($sortKey=='created_at'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_customer')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer as $record)
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>{{ucfirst($record->first_name)}}</td>
                                        <td>{{ucfirst($record->last_name)}}</td>
                                        <td>{{$record->email}}</td>
                                        <td>{{$record->mobile}}</td>
                                        <td>{{ucwords($record->login_type)}}</td>
                                        <td>
                                            @if(isset($record->user_type) && $record->user_type === 'physical_store_user')
                                                {{ __('admin.physical_store_user') }}
                                            @else
                                                {{ __('admin.website_user') }}
                                            @endif
                                        </td>
                                        <td>{{$record->last_login?\Carbon\Carbon::parse($record->last_login)->format(config('custom.date-time')):''}}</td>
                                        <td>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.date-time'))}}</td>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_customer'),config('custom_middleware.delete_customer')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_customer'))
                                            <a href="{{route('admin.customer.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @endcan
                                            @if($record->is_active)
                                            <a href="{{route('admin.customer.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.customer.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @can(config('custom_middleware.delete_customer'))
                                            <a onclick="showSweetAlert('{{route('admin.customer.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                            @endcan
                                            <a href="{{route('admin.customer.address.index',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.manage_address')}}"><i class="fas fa-address-book"></i></button></a>
                                            @if($record)
                                            <a href="{{route('admin.master.coupon.send-mail',['id'=>$record->id,'page'=>'customer'])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.send_mail')}}"><i class="fa fa-mail-bulk"></i> {{__('admin.send_mail')}}</button></a>
                                            @endif
                                        </td>
                                        @endcanany
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                     
						<div class="table-pagination pt20 float-right">
                            {{$customer->links('pagination::bootstrap-4')}}
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

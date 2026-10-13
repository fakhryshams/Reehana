@extends('layout/main')
@section('page_title', trans('admin.user-management'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
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
                    <h1>{{__('admin.user-management')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.user-management.index')}}">{{__('admin.user-management')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.user-management.index')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <select name="_c[]"  class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                    @foreach($stores as $store)
                                                    <option value="{{ $store->id }}"
                                                        {{ !empty($filterStore) && collect($filterStore)->contains($store->id) ? 'selected' : '' }}>
                                                        {{ $store->store_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
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
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <!--<a class="pl-2" href="{{route('admin.master.city.export-city')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>-->
                                        </div>
                                        @canany([config('custom_middleware.create_master_courier_company')])
                                        <a class="pl-2" href="{{route('admin.user-management.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="CourierCompany" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.first_column') }}</th>

                                        <th>{{ __('admin.physical-store') }}</th>

                                        <th class="asc-desc" id="@if($sortKey=='first_name'){{ $sortVal }}@endif">
                                            {{ __('admin.first_name') }}
                                            <input type="hidden"
                                                name="first_name"
                                                value="@if($sortKey=='first_name'){{ $sortVal }}@endif"
                                                @if($sortKey!='first_name' ) disabled @endif>
                                        </th>

                                        <th class="asc-desc" id="@if($sortKey=='last_name'){{ $sortVal }}@endif">
                                            {{ __('admin.last_name') }}
                                            <input type="hidden"
                                                name="last_name"
                                                value="@if($sortKey=='last_name'){{ $sortVal }}@endif"
                                                @if($sortKey!='last_name' ) disabled @endif>
                                        </th>

                                        <th class="asc-desc" id="@if($sortKey=='email'){{ $sortVal }}@endif">
                                            {{ __('admin.email') }}
                                            <input type="hidden"
                                                name="email"
                                                value="@if($sortKey=='email'){{ $sortVal }}@endif"
                                                @if($sortKey!='email' ) disabled @endif>
                                        </th>

                                        <th class="asc-desc" id="@if($sortKey=='mobile'){{ $sortVal }}@endif">
                                            {{ __('admin.mobile') }}
                                            <input type="hidden"
                                                name="mobile"
                                                value="@if($sortKey=='mobile'){{ $sortVal }}@endif"
                                                @if($sortKey!='mobile' ) disabled @endif>
                                        </th>

                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{ $sortVal }}@endif">
                                            {{ __('admin.status') }}
                                            <input type="hidden"
                                                name="is_active"
                                                value="@if($sortKey=='is_active'){{ $sortVal }}@endif"
                                                @if($sortKey!='is_active' ) disabled @endif>
                                        </th>

                                        @canany([
                                        config('custom_middleware.edit_master_physical_store'),
                                        config('custom_middleware.delete_master_physical_store')
                                        ])
                                        <th class="text-center">{{ __('admin.action') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $record)
                                    <tr id="{{ $record->id }}">
                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $record->physicalStore->store_name . "(". $record->physicalStore->store_code .")" }}</td>

                                        <td>{{ $record->first_name }}</td>

                                        <td>{{ $record->last_name }}</td>

                                        <td>{{ $record->email }}</td>

                                        <td>{{ $record->mobile }}</td>

                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{ config('custom.badge-success','badge bg-success') }}">
                                                {{ __('admin.active') }}
                                            </span>
                                            @else
                                            <span class="{{ config('custom.badge-danger','badge bg-danger') }}">
                                                {{ __('admin.in_active') }}
                                            </span>
                                            @endif
                                        </td>

                                        @canany([
                                        config('custom_middleware.edit_master_physical_store'),
                                        config('custom_middleware.delete_master_physical_store')
                                        ])
                                        <td class="text-center">

                                            @can(config('custom_middleware.edit_master_physical_store'))
                                            <a href="{{ route('admin.user-management.edit', ['id' => $record->id]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-primary','btn btn-outline-primary btn-sm') }}"
                                                    title="{{ __('admin.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </a>

                                            <a href="{{ route('admin.user-management.change-password', ['id' => $record->id]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-info','btn btn-outline-info btn-sm') }}"
                                                    title="{{ __('admin.change_password') }}">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </a>

                                            @if($record->is_active)
                                            <a href="{{ route('admin.user-management.change-status', ['id'=>$record->id,'active'=>0]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-danger','btn btn-outline-danger btn-sm') }}"
                                                    title="{{ __('admin.click_in_active') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </a>
                                            @else
                                            <a href="{{ route('admin.user-management.change-status', ['id'=>$record->id,'active'=>1]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-success','btn btn-outline-success btn-sm') }}"
                                                    title="{{ __('admin.click_active') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </a>
                                            @endif
                                            @endcan

                                            @can(config('custom_middleware.delete_master_physical_store'))
                                            <a onclick="showSweetAlert('{{ route('admin.user-management.delete',['id'=>$record->id]) }}')">
                                                <button type="button"
                                                    class="{{ config('custom.btn-danger','btn btn-outline-danger btn-sm') }}"
                                                    title="{{ __('admin.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </a>
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
                            {{$users->links('pagination::bootstrap-4')}}
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
@include('layout/select2')
<script>
    $(document).ready(function ()
    {
    $(".select-category").select2({
    placeholder: "{{__('admin.select_store')}}",
            allowClear: true
    });
    });
</script>
@endsection
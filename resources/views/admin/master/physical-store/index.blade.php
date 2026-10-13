@extends('layout/main')
@section('page_title', trans('admin.physical-store'))
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
                    <h1>{{__('admin.physical-store')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.physical-store.index')}}">{{__('admin.physical-store')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.master.physical-store.index')}}">
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
                                        {{--
                                        @canany([config('custom_middleware.create_master_courier_company')])
                                        <a class="pl-2" href="{{route('admin.master.physical-store.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                        --}}
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="CourierCompany" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.first_column') }}</th>

                                        <th class="asc-desc" id="@if($sortKey=='store_code'){{ $sortVal }}@endif">
                                            {{ __('admin.store_code') }}
                                            <input type="hidden"
                                                name="store_code"
                                                value="@if($sortKey=='store_code'){{ $sortVal }}@endif"
                                                @if($sortKey!='store_code' ) disabled @endif>
                                        </th>

                                        <th class="asc-desc" id="@if($sortKey=='store_name'){{ $sortVal }}@endif">
                                            {{ __('admin.store_name') }}
                                            <input type="hidden"
                                                name="store_name"
                                                value="@if($sortKey=='store_name'){{ $sortVal }}@endif"
                                                @if($sortKey!='store_name' ) disabled @endif>
                                        </th>

                                        <th>{{ __('admin.address_line_1') }}</th>
                                        <th>{{ __('admin.address_line_2') }}</th>
                                        <th>{{ __('admin.country') }}</th>
                                        <th>{{ __('admin.area') }}</th>
                                        <th>{{ __('admin.block') }}</th>
                                        <th>{{ __('admin.street') }}</th>
                                        <th>{{ __('admin.pin_code') }}</th>
                                        <th>{{ __('admin.map_location') }}</th>

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
                                    @forelse($stores as $record)
                                    <tr id="{{ $record->id }}">
                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $record->store_code }}</td>

                                        <td>{{ $record->store_name }}</td>

                                        <td>{{ $record->address_line_1 }}</td>

                                        <td>{{ $record->address_line_2 ?? '-' }}</td>

                                        @php
                                        $language = Helper::getDefaultLanguage();
                                        $countryDesc = $record->countryDetail
                                        ? $record->countryDetail->getCountryDescription($language->id)
                                        : null;
                                        @endphp

                                        <td>{{ $countryDesc->country_name ?? '-' }}</td>

                                        @php
                                        $language = Helper::getDefaultLanguage();
                                        $cityDesc = $record->areaDetail
                                        ? $record->areaDetail->getCityDescription($language->id)
                                        : null;
                                        @endphp

                                        <td>{{ $cityDesc->city_name ?? '-' }}</td>

                                        <td>{{ $record->block ?? '-' }}</td>

                                        <td>{{ $record->street ?? '-' }}</td>

                                        <td>{{ $record->pin_code ?? '-' }}</td>

                                        <td>
                                            @if(!empty($record->map) && filter_var($record->map, FILTER_VALIDATE_URL))
                                            <a href="{{ $record->map }}" target="_blank" rel="noopener noreferrer">
                                                {{ __('admin.view_map') }}
                                            </a>
                                            @else
                                            {{ $record->map ?? '-' }}
                                            @endif
                                        </td>

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
                                            <a href="{{ route('admin.master.physical-store.edit', ['id' => $record->id]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-primary','btn btn-outline-primary btn-sm') }}"
                                                    title="{{ __('admin.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </a>

                                            @if($record->is_active)
                                            <a href="javascript:void(0)" onclick="confirmPhysicalStoreInactivate('{{ route('admin.master.physical-store.change-status', ['id'=>$record->id,'active'=>0]) }}')">
                                                <button type="button"
                                                    class="{{ config('custom.btn-danger','btn btn-outline-danger btn-sm') }}"
                                                    title="{{ __('admin.click_in_active') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.master.physical-store.change-status', ['id'=>$record->id,'active'=>1]) }}">
                                                <button type="button"
                                                    class="{{ config('custom.btn-success','btn btn-outline-success btn-sm') }}"
                                                    title="{{ __('admin.click_active') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </a>
                                        @endif
                                            @endcan

                                            {{--
                                            @can(config('custom_middleware.delete_master_physical_store'))
                                            <a onclick="showSweetAlert('{{ route('admin.master.physical-store.delete',['id'=>$record->id]) }}')">
                                                <button type="button"
                                                    class="{{ config('custom.btn-danger','btn btn-outline-danger btn-sm') }}"
                                                    title="{{ __('admin.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </a>
                                            @endcan
                                            --}}

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
                            {{$stores->links('pagination::bootstrap-4')}}
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
<script>
    function confirmPhysicalStoreInactivate(url)
    {
        if (typeof Swal === 'undefined') {
            if (confirm('If you mark this store as inactive, all users related to this store will also be set to inactive.')) {
                window.location.href = url;
            }
            return;
        }
        Swal.fire({
            title: '',
            text: 'If you mark this store as inactive, all users related to this store will also be set to inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: '{{ __("admin.no_keep_it") }}'
        }).then((result) => {
            if (result.value) {
                window.location.href = url;
            }
        });
    }
</script>
@endsection

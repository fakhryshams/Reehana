@extends('layout/main')
@section('page_title',  trans('admin.country'))
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
                    <h1>{{__('admin.country')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.country.index')}}">{{__('admin.country')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.master.country.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.master.country.export-country')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @canany([config('custom_middleware.create_country')])
                                        <a class="pl-2" href="{{route('admin.master.country.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="AdminCountry" class="table sorting-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='country_name'){{$sortVal}}@endif">{{__('admin.country')}}<input @if($sortKey!='country_name')disabled=""@endif  type="hidden" name="country_name" value="@if($sortKey=='country_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='currency_code'){{$sortVal}}@endif">{{__('admin.currency_code')}}<input @if($sortKey!='currency_code')disabled=""@endif  type="hidden" name="currency_code" value="@if($sortKey=='currency_code'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='currency_rate'){{$sortVal}}@endif">{{__('admin.currency_rate')}}<input @if($sortKey!='currency_rate')disabled=""@endif  type="hidden" name="currency_rate" value="@if($sortKey=='currency_rate'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='shipping_charge'){{$sortVal}}@endif">{{__('admin.shipping-charge')}}<input @if($sortKey!='shipping_charge')disabled=""@endif  type="hidden" name="shipping_charge" value="@if($sortKey=='shipping_charge'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='delivery_time'){{$sortVal}}@endif">{{__('admin.delivery_time')}}<input @if($sortKey!='delivery_time')disabled=""@endif  type="hidden" name="delivery_time" value="@if($sortKey=='delivery_time'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='free_shipping_amount'){{$sortVal}}@endif">Free Shipping Amount<input @if($sortKey!='free_shipping_amount')disabled=""@endif  type="hidden" name="free_shipping_amount" value="@if($sortKey=='free_shipping_amount'){{$sortVal}}@endif"></th>
                                      
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_default'){{$sortVal}}@endif">{{__('admin.is_default_country')}}<input @if($sortKey!='is_default')disabled=""@endif  type="hidden" name="is_default" value="@if($sortKey=='is_default'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                      
                                        @canany([config('custom_middleware.edit_country'),config('custom_middleware.delete_country')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($country as $record)
                                    <tr id="{{$record->id}}">
                                        <td class="">{{$record->sorting}}</td>
                                        @php
                                        $description = $record->getCountryDescription();
                                        @endphp
                                        <td class=''>{{$description?ucfirst($description->country_name):''}} 
                                        @if($record->country_code)
                                        ({{$record->country_code}})
                                        @endif
                                        </td>
                                        <td class='text-center'>{{$record->currency_code}}</td>
                                        <td class='text-center'>{{$record->currency_rate}}</td>
                                        <td class='text-center'>{{Helper::formatNumber($record->shipping_charge)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        <td class='text-center'>{{$description?ucfirst($description->delivery_time):''}}</td>
                                        <td class='text-center'>{{$record->free_shipping_amount ? $record->free_shipping_amount : '0'}}</td>
                                        <td class="text-center reorder-exclude">
                                            @if($record->is_default)
                                            <i title="{{__('admin.yes')}}" class="fa fa-check text-success"></i>
                                            @else
                                            <i title="{{__('admin.no')}}" class="fa fa-times text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-center reorder-exclude">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_country'),config('custom_middleware.delete_country')])
                                        <td class="text-center">
                                            @if($record->can_edit)
                                            @can(config('custom_middleware.edit_country'))
                                            <a href="{{route('admin.master.country.edit',['id' => $record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.master.country.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.master.country.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endcan
                                            @if($record->id != 2)
                                            @can(config('custom_middleware.delete_country'))
                                            <a onclick="showSweetAlert('{{route('admin.master.country.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                            @endcan
                                            @endif
                                            @endif
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
                            {{$country->links('pagination::bootstrap-4')}}
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

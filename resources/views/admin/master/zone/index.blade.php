@extends('layout/main')
@section('page_title',  trans('admin.zone'))
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
                    <h1>{{__('admin.zone')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.zone.index')}}">{{__('admin.zone')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.master.zone.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @canany([config('custom_middleware.create_city')])
                                        <a class="pl-2" href="{{route('admin.master.zone.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="AdminZone" class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        @endif
                                        <th class="asc-desc" id="@if($sortKey=='title'){{$sortVal}}@endif">{{__('admin.zone_title')}}<input @if($sortKey!='title')disabled=""@endif  type="hidden" name="title" value="@if($sortKey=='title'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='delivery_charge'){{$sortVal}}@endif">{{__('admin.zone_delivery_charge')}}<input @if($sortKey!='delivery_charge')disabled=""@endif  type="hidden" name="delivery_charge" value="@if($sortKey=='delivery_charge'){{$sortVal}}@endif"></th>
                                        <th class="">{{__('admin.city')}}</th>
                                        @if(false)
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @endif
                                        @canany([config('custom_middleware.edit_city'),config('custom_middleware.delete_city')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($zone as $record)
                                    <tr id="{{$record->id}}">
                                        @if(false)
                                        <td class="">{{$record->sorting}}</td>
                                        @endif
                                        <td class="">{{$record->title}}</td>
                                        <td class="">{{Helper::formatNumber($record->delivery_charge,2)}}</td>
                                        <?php
                                        $zoneArea    = $record->getZoneArea();
                                        ?>
                                        <td class="">
                                            @foreach($zoneArea as $za)
                                            <?php
                                            $description = $za->getCityDescription();
                                            ?>
                                            {{$description?ucfirst($description->city_name):''}}@if(!$loop->last),<br> @endif
                                            @endforeach                                            
                                        </td>
                                        @if(false)
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @endif
                                        @canany([config('custom_middleware.edit_city'),config('custom_middleware.delete_city')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_city'))
                                            <a href="{{route('admin.master.zone.edit',['id' => $record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if(false)
                                            @if($record->is_active)
                                            <a href="{{route('admin.master.zone.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.master.zone.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endif
                                            @endcan
                                            @can(config('custom_middleware.delete_city'))
                                            <a onclick="showSweetAlert('{{route('admin.master.zone.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
                            {{$zone->links('pagination::bootstrap-4')}}
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

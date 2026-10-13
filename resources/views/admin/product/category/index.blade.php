@extends('layout/main')
@section('page_title',  trans('admin.category'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.category')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.category.index')}}">{{__('admin.category')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.product.category.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <!--<h4 class="card-title ml-3">{{__('admin.list')}}</h4>-->
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.product.category.category-export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                            @canany([config('custom_middleware.create_category')])
                                            <a class="pl-2" href="{{route('admin.product.category.add')}}"><button type="button" class="add-new-btn {{config('custom.btn-primary','btn btn-outline-primary')}}" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                            @endcanany
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="AdminCategory" class="table sorting-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc text-center" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='category_name'){{$sortVal}}@endif">{{__('admin.category_name')}}<input @if($sortKey!='category_name')disabled=""@endif  type="hidden" name="category_name" value="@if($sortKey=='category_name'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="text-center">{{__('admin.category_image')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='min_order_quantity'){{$sortVal}}@endif">{{__('admin.min_order_quantity')}}<input @if($sortKey!='min_order_quantity')disabled=""@endif  type="hidden" name="min_order_quantity" value="@if($sortKey=='min_order_quantity'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='max_order_quantity'){{$sortVal}}@endif">{{__('admin.max_order_quantity')}}<input @if($sortKey!='max_order_quantity')disabled=""@endif  type="hidden" name="max_order_quantity" value="@if($sortKey=='max_order_quantity'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='show_customized_form'){{$sortVal}}@endif">{{__('admin.show_customized_form')}}<input @if($sortKey!='show_customized_form')disabled=""@endif  type="hidden" name="show_customized_form" value="@if($sortKey=='show_customized_form'){{$sortVal}}@endif"></th>
                                       
                                        <th class="asc-desc text-center" id="@if($sortKey=='have_price_variant'){{$sortVal}}@endif">{{__('admin.have_price_variant_index')}}<input @if($sortKey!='have_price_variant')disabled=""@endif  type="hidden" name="have_price_variant" value="@if($sortKey=='have_price_variant'){{$sortVal}}@endif"></th>
                                       @endif
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc text-center" id="@if($sortKey=='updated_at'){{$sortVal}}@endif">{{__('admin.table_date')}}<input @if($sortKey!='updated_at')disabled=""@endif  type="hidden" name="updated_at" value="@if($sortKey=='updated_at'){{$sortVal}}@endif"></th>
                                        @endif
                                        @canany([config('custom_middleware.edit_category'),config('custom_middleware.delete_category')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $record)
                                    <tr id="{{$record->id}}">
                                        <td class="text-center">{{$record->sorting}}</td>
                                        @php
                                        $categoryName = $record->getCategoryDescription();
                                        @endphp
                                        <td>
                                            {{$categoryName?ucfirst($categoryName->categories_name):''}}
                                        </td>
                                        @if(false)
                                        <td class="text-center reorder-exclude">
                                            <img class="text-center logo-table" src="{{asset(Storage::url($record->categories_image))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)">
                                        </td>
                                        <td class="">{{$record->min_order_quantity}}</td>
                                        <td class="">{{$record->max_order_quantity}}</td>
                                        <td class="text-center">
                                            <div class="icheck-primary">
                                                <input disabled="" type="checkbox" id="show_customized_form" {{$record->show_customized_form==1?'checked':''}}>
                                                <label for="show_customized_form">
                                                </label>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="icheck-primary">
                                                <input disabled="" type="checkbox" id="have_price_variant" {{$record->have_price_variant==1?'checked':''}}>
                                                <label for="have_price_variant">
                                                </label>
                                            </div>
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @if(false)
                                        <td class="text-center">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span class="text-primary">{{__('admin.added_on')}} : {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.date-time'))}}</span>
                                                </div>
                                                @if($record->updated_by)
                                                <div class="col-md-12">
                                                    <span class="text-success">{{__('admin.modified_on')}} : {{\Carbon\Carbon::parse($record->updated_at)->format(config('custom.date-time'))}}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                        @canany([config('custom_middleware.edit_category'),config('custom_middleware.delete_category')])
                                        <td class="text-center">
                                            <a href="{{route('admin.product.subcategory.index',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-primary btn-sm')}}" title="{{__('admin.show_sub_category')}}"><i class="fas fa-arrow-alt-circle-right"></i></button></a>
                                            @can(config('custom_middleware.edit_category'))
                                            <a href="{{route('admin.product.category.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.product.category.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <?php
                                            $deactivatedProductCount = $record->getDeactivatedProductCount();
                                            //echo $deactivatedProductCount."TEST";
                                            ?>
                                            <a onclick="categorySweetAlert('{{route('admin.product.category.change-status',['id'=>$record->id,'active'=>1])}}',{{$deactivatedProductCount}})"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @if(false)
                                            <a href="{{route('admin.product.category.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endif
                                            @endcan
                                            @can(config('custom_middleware.delete_category'))
                                            <a onclick="showSweetAlert('{{route('admin.product.category.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
                            {{$categories->links('pagination::bootstrap-4')}}
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
@include('layout/image_preview')
@include('layout/datatable')
@endsection

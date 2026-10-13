@extends('layout/main')
@section('page_title',  trans('admin.product'))
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
                    <h1>{{__('admin.product')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.index')}}">{{__('admin.product')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.product.index')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <select name="_c[]"  class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                    @foreach($category as $category)
                                                    @php
                                                    $categoryName = $category->getCategoryDescription();
                                                    @endphp
                                                    <option value="{{$category->id}}" {{ (collect($filterCategory)->contains($category->id)) ? 'selected':'' }}>
                                                        @if($category->parent_id)
                                                        @php
                                                        $parentCategory = $category->getParent($category->parent_id);
                                                        $parentCategoryName = $parentCategory->getCategoryDescription();
                                                        @endphp
                                                        {{$parentCategoryName?ucfirst($parentCategoryName->categories_name).' > ':''}}
                                                        @endif
                                                        {{$categoryName?ucfirst($categoryName->categories_name):''}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <select name="_b[]"  class="form-control select2 select-brand {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                @foreach($brand as $data)
                                                @php
                                                $brandName = $data->getBrandDescription();
                                                @endphp
                                                <option value="{{$data->id}}" {{ (collect($filterBrand)->contains($data->id)) ? 'selected':'' }}>{{$brandName->brand_name?ucfirst($brandName->brand_name):''}}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <div class="icheck-primary">
                                                    <input value="1" type="checkbox" name="OOSFilter" id="OOSFilter" {{$outOfStockFilter?'checked':''}}>
                                                    <label for="OOSFilter">{{__('admin.out_of_stock_filter')}}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="icheck-primary">
                                                    <input value="1" type="checkbox" name="na" id="naFilter" {{$newArrivalFilter?'checked':''}}>
                                                    <label for="naFilter">{{__('admin.new_arrival_filter')}}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="icheck-primary">
                                                    <input value="1" type="checkbox" name="ish" id="ishFilter" {{$intlFilter?'checked':''}}>
                                                    <label for="ishFilter">{{__('admin.international_shipping_filter')}}
                                                    </label>
                                                </div>
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
                        @if(false)
                        <input type="hidden" name="id" value="{{$productId}}">
                        @endif
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    {!! Helper::printPageRecord($product) !!}
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.product.product-export').'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @canany([config('custom_middleware.add_product')])
                                        <a class="pl-2"  href="{{route('admin.product.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table data-model="AdminProduct" data-isnewarrival="{{$newArrivalFilter?1:0}}" class="table sorting-table table-striped">
                                <thead>
                                    <tr>
                                        @if($newArrivalFilter)
                                        <th class="asc-desc text-center" id="@if($sortKey=='new_arrival_soting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='new_arrival_soting')disabled=""@endif  type="hidden" name="new_arrival_soting" value="@if($sortKey=='new_arrival_soting'){{$sortVal}}@endif"></th>
                                        @else
                                        <th class="asc-desc text-center" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        @endif
                                        <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.name')}}<input @if($sortKey!='name')disabled=""@endif  type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='product_sku'){{$sortVal}}@endif">{{__('admin.sku')}}<input @if($sortKey!='product_sku')disabled=""@endif  type="hidden" name="product_sku" value="@if($sortKey=='product_sku'){{$sortVal}}@endif"></th>
                                        @endif
										@if($newArrivalFilter == 0)
                                        <th class="text-center asc-desc" id="@if($sortKey=='product_regular_price'){{$sortVal}}@endif">{{__('admin.regular_price')}}<input @if($sortKey!='product_regular_price')disabled=""@endif  type="hidden" name="product_regular_price" value="@if($sortKey=='product_regular_price'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='product_sale_price'){{$sortVal}}@endif">{{__('admin.selling_price')}}<input @if($sortKey!='product_sale_price')disabled=""@endif  type="hidden" name="product_sale_price" value="@if($sortKey=='product_sale_price'){{$sortVal}}@endif"></th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='product_quantity'){{$sortVal}}@endif">{{__('admin.product_quantity_index')}}<input @if($sortKey!='product_quantity')disabled=""@endif  type="hidden" name="product_quantity" value="@if($sortKey=='product_quantity'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='category'){{$sortVal}}@endif">{{__('admin.category')}}<input @if($sortKey!='category')disabled=""@endif  type="hidden" name="category" value="@if($sortKey=='category'){{$sortVal}}@endif"></th>
                                        <th class="text-center">{{__('admin.image')}}</th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}/{{__('admin.category_product_status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @endif
										<th class="asc-desc text-center" id="@if($sortKey=='is_new_arrival'){{$sortVal}}@endif">{{__('admin.new_arrival')}}<input @if($sortKey!='is_new_arrival')disabled=""@endif  type="hidden" name="is_new_arrival" value="@if($sortKey=='is_new_arrival'){{$sortVal}}@endif"></th>
										@if($newArrivalFilter == 0)
                                        <th class="asc-desc text-center" id="@if($sortKey=='intl_shipping'){{$sortVal}}@endif"><input @if($sortKey!='intl_shipping')disabled=""@endif  type="hidden" name="intl_shipping" value="@if($sortKey=='intl_shipping'){{$sortVal}}@endif"><i class="fa fa-plane" title="{{__('admin.product_intl_shipping')}}"></i></th>
										@endif
                                        @canany([config('custom_middleware.edit_product_product'),config('custom_middleware.delete_product_product')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($product as $record)
                                    <tr id="{{$record->id}}">
									@if($newArrivalFilter)
                                        <td class="text-center"><input type='number' name="new_arrival_soting[]"  value="{{ $record->new_arrival_soting}}" style="width:20%" class='new_arrival_soting' data-current-order="{{$record->new_arrival_soting}}" data-product-id="{{$record->id}}">   
									  </td>
										@else
										<td class="text-center">{{ $record->sorting }}</td>
										@endif	
									   <?php
                                        $productName     = $record->getProductDescription();
                                        $productCategory = $record->getProductCategories();
                                        $defaultImage    = $record->getDefaultImage();
                                        ?>
                                        <td>{{$productName?ucfirst($productName->products_name):''}}</td>
                                        @if(false)
                                        <td>{{$record->product_sku}}</td>
                                        @endif
										 @if($newArrivalFilter == 0)
                                        <td class="text-center">{{Helper::formatNumber($record->product_regular_price)}}</td>
                                        <td class="text-center">{{$record->is_product_sale_price_null?" - ":Helper::formatNumber($record->product_sale_price)}}</td>
                                        <td class="text-center">
                                            @if($record->product_quantity)
                                            {{$record->product_quantity}}
                                            @else
                                            <span class='badge badge-danger'>{{__('admin.out_of_stock')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($productCategory as $category)
                                            <?php
                                            $categoryDetail  = $category->getCategory();
                                            if ($categoryDetail)
                                            {
                                                $categoryDescription = $categoryDetail->getCategoryDescription();
                                                if ($categoryDescription)
                                                {
                                                    echo ucfirst($categoryDescription->categories_name) . "<br/>";
                                                }
                                            }
                                            ?>
                                            @endforeach
                                        </td>
                                        <td class="text-center reorder-exclude"><img class="logo-table text-center" src="{{asset(Storage::url($defaultImage ? $defaultImage->image_thumb_url : 'default.png'))}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                            
                                            @if($record->is_active_category)
                                            {{--<span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>--}} 
                                            @else
                                            / <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span>
                                            @endif
                                        </td>
										@endif
                                        <td class="text-center reorder-exclude">
                                            @if(!$record->is_new_arrival)
                                            <a href="{{route('admin.product.change-new-arrival',['id'=>$record->id,'active'=>1]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_add_new_arrival')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.product.change-new-arrival',['id'=>$record->id,'active'=>0]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.click_remove_new_arrival')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @if(false)
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="is_new_arrival_{{$record->id}}" {{$record->is_new_arrival==1?'checked':''}}>
                                                <label for="is_new_arrival_{{$record->id}}">
                                                </label>
                                            </div>
                                            @endif
                                        </td>
										@if($newArrivalFilter == 0)
                                        <td class="text-center reorder-exclude">
                                            @if($record->intl_shipping)
                                            <i title="{{__('admin.yes')}}" class="fa fa-check text-success"></i>
                                            @else
                                            <i title="{{__('admin.no')}}" class="fa fa-times text-danger"></i>
                                            @endif
                                        </td>
										 @endif
                                        @canany([config('custom_middleware.edit_product_product'),config('custom_middleware.delete_product_product')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_product_product'))
                                            <a target="_blank"  href="{{route('admin.product.edit',['id'=>$record->id])}}"><button type="button" class="mt-1 {{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.product.change-status',['id'=>$record->id,'active'=>0]).'?'.http_build_query($_GET)}}"><button type="button" class="mt-1 {{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.product.change-status',['id'=>$record->id,'active'=>1]).'?'.http_build_query($_GET)}}"><button type="button" class="mt-1 {{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endcan
                                            @can(config('custom_middleware.delete_product_product'))
                                            <a onclick="showSweetAlert('{{route('admin.product.delete',['id'=>$record->id] + request()->query())}}')"><button type="button" class="mt-1 {{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                            @endcan
                                            @can(config('custom_middleware.edit_product_product'))
                                            @if($record->have_price_variant)
                                            <a href="{{route('admin.product.stock',['id'=>$record->id])}}"><button type="button" class="mt-1 {{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.manage_stock')}}"><i class="fas fa-database"></i></button></a>
                                            @endif
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
                            {{$product->appends(request()->query())->links('pagination::bootstrap-4')}}
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
@include('layout/select2') 
<script>
    $(document).ready(function ()
    {
		  $(".new_arrival_soting").on("change", function (e)
                {
                  
				       var currentOrder = $(this).attr('data-current-order');
					   var newOrder = $(this).val();
					   var productId = $(this).attr('data-product-id');
					   if(currentOrder != newOrder)
					   {
						   $.ajax({
								type: "GET",
								url: "{{route('admin.product.change-sorting')}}/"+currentOrder+'/'+newOrder+'/'+productId,
								success: function (res)
								{
								location.reload();

								}
						});
					   }
                });
		
		
		
		
		
    $(".select-category").select2({
    placeholder: "{{__('admin.filter_by_category')}}",
            allowClear: true
    });
    $(".select-brand").select2({
    placeholder: "{{__('admin.filter_by_brand')}}",
            allowClear: true
    });
    });
</script>
@endsection

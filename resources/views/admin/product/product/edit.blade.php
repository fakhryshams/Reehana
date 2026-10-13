@extends('layout/main')
@section('page_title', trans('admin.product'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.product')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.index')}}">{{__('admin.product')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form name="product" method="post" action="{{route('admin.product.update',['id'=>$record->id])}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $recordDescription = $record->getProductDescription($language->id);
                            @endphp
                            <div class="col-md-12 p-0">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_name')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                        <input name="product_name_{{$language->id}}" type="text" class="form-control {{ $errors->has('product_name_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_name')}} ({{ucfirst($language->name)}})" value="{{old('product_name_'.$language->id,$recordDescription?$recordDescription->products_name:'')}}">
                                        @error('product_name_'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_description')}} ({{ucfirst($language->name)}})</label><span class=""></span>
                                        <textarea class="textarea form-control {{ $errors->has('product_description_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_description')}} ({{ucfirst($language->name)}})" name="product_description_{{$language->id}}" rows="4" cols="300">{{old('product_description_'.$language->id,$recordDescription?$recordDescription->products_description:'')}}</textarea>
                                        @error('product_description_'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if(false)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})</label>
                                        <textarea class="textarea form-control {{ $errors->has('product_return_exchange_policy'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})" name="product_return_exchange_policy_{{$language->id}}" rows="4" cols="300">{{old('product_return_exchange_policy_'.$language->id,$recordDescription?$recordDescription->product_return_exchange_policy:'')}}</textarea>
                                        @error('product_return_exchange_policy'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_short_description')}} ({{ucfirst($language->name)}})</label>
                                        <textarea class="textarea form-control {{ $errors->has('product_short_description_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_short_description')}} ({{ucfirst($language->name)}})" name="product_short_description_{{$language->id}}" rows="4" cols="300">{{old('product_short_description_'.$language->id)}}</textarea>
                                        @error('product_short_description_'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.product_barcode')}}</label><span class="required"></span>
                                    <input class="form-control length-100" placeholder="{{__('admin.product_barcode')}}" name="product_barcode" type="text" value="{{old('product_barcode',$record->product_barcode)}}">
                                    @error('product_barcode')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.product_sku')}}</label><span class=""></span>
                                    <input class="form-control length-100" placeholder="{{__('admin.product_sku')}}" name="product_sku" type="text" value="{{old('product_sku',$record->product_sku)}}">
                                    @error('product_sku')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.brand')}}</label><span class=""></span>
                                    <select name="brand_id" class="form-control select2 select-brand {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        <option selected="" disabled=""></option>
                                        @foreach($brand as $data)
                                        @php
                                        $brandName = $data->getBrandDescription();
                                        @endphp
                                        <option value="{{$data->id}}" {{ (collect(old('brand_id',$record->brand_id))->contains($data->id)) ? 'selected':'' }}>{{$brandName->brand_name?ucfirst($brandName->brand_name):''}}</option>
                                        @endforeach
                                    </select>
                                    <small class="{{config('custom.text-note-css')}}">
                                    </small>
                                    @error('brand_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                            $productCategory   = $record->getProductCategories(true);
                            //                            dd($productCategory);
                            ?>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.category')}}</label><span class="required"></span>
                                    <select id="category_id" name="category_id[]" class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($category as $category)
                                        <?php
                                        $categoryName      = $category->getCategoryDescription();
                                        //$productCategory          = $record->getProductCategories(true);
                                        ?>
                                        <option value="{{$category->id}}" {{ (collect(old('category_id',$record->category_id))->contains($category->id)) ? 'selected':'' }}>
                                            {{$categoryName?ucfirst($categoryName->categories_name):''}}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="{{config('custom.text-note-css')}}">
                                    </small>
                                    @error('category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.sub_category')}}</label><span class=""></span>
                                    <select id="subcategory_id" name="sub_category_id[]" class="form-control select2 select-sub-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" required>
                                    </select>
                                    <small class="{{config('custom.text-note-css')}}">
                                    </small>
                                    @error('sub_category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
							@if($record->have_price_variant == 0)
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_regular_price')}}</label><span class="product_regular_price required"></span>
                                    <input class="form-control float-input length-10 main-price" placeholder="{{__('admin.product_regular_price')}}" name="product_regular_price" type="text" value="{{old('product_regular_price',$record->product_regular_price)}}">
                                    @error('product_regular_price')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <input type="hidden" name="main_discount_in" value="{{config('custom.discount_in')}}">
                                    <label>({{config('custom.discount_title')}}) {{__('admin.discount')}}</label>
                                    <input class="form-control float-input length-2 main-price" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}}" name="main_discount" type="text" value="{{old('main_discount',$record->discount)}}">
                                    @error('main_discount')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.discount_price')}}</label>
                                    <input readonly="" class="form-control float-input length-10" placeholder="{{__('admin.discount_price')}}" name="product_sale_price" type="text" value="{{old('product_sale_price',$record->is_product_sale_price_null?"":Helper::formatNumber($record->product_sale_price))}}">
                                    @error('product_sale_price')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.product_quantity')}}</label><span class="size-quantity-box-req"></span>
                                    <input class="form-control float-input length-10 product_quantity" placeholder="{{__('admin.product_quantity')}}" name="product_quantity" type="text" value="{{old('product_quantity',$record->product_size?'':$record->product_quantity)}}">
                                    @error('product_quantity')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        	@endif
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_weight')}}</label><span class="required"></span><small class="{{config('custom.text-note-css')}}">{{__('admin.weightkg')}}</small>
                                    <input class="form-control float-input length-10 product_quantity" placeholder="{{__('admin.product_weight')}}" name="product_weight" type="text" value="{{old('product_weight',$record->product_weight)}}">
                                    @error('product_weight')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
						<div class="row">
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Length</label>
                                    <input class="form-control float-input" placeholder="Length" name="product_length" type="text" value="{{old('product_length',$record->product_length)}}">
                                    @error('product_length')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Width</label>
                                    <input class="form-control float-input" placeholder="Width" name="product_width" type="text" value="{{old('product_width',$record->product_width)}}">
                                    @error('product_width')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input class="form-control float-input" placeholder="Height" name="product_height" type="text" value="{{old('product_height',$record->product_height)}}">
                                    @error('product_height')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Dim Unit</label>
                                    <select class="form-control select2" name="dimension_unit">
                                        <option value="cm" {{old('dimension_unit',$record->dimension_unit) == 'cm' ? 'selected' : ''}}>cm</option>
                                        <option value="in" {{old('dimension_unit',$record->dimension_unit) == 'in' ? 'selected' : ''}}>in</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Weight Unit</label>
                                    <select class="form-control select2" name="weight_unit">
                                        <option value="kg" {{old('weight_unit',$record->weight_unit) == 'kg' ? 'selected' : ''}}>kg</option>
                                        <option value="lb" {{old('weight_unit',$record->weight_unit) == 'lb' ? 'selected' : ''}}>lb</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                     <label>Country of Origin</label>
                                    <input class="form-control" placeholder="ISO Country Code (e.g. SA or USA)" name="country_of_origin" type="text" value="{{ old('country_of_origin', $record->country_of_origin ?: 'KW') }}">
                                     @error('country_of_origin')
                                     <div class="text-red">{{ $message }}</div>
                                     @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>HS Code</label>
                                    <input class="form-control" placeholder="HS Code" name="hs_code" type="text" value="{{old('hs_code',$record->hs_code)}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.new_arrival')}}</label> <small class="{{config('custom.text-note-css')}}">{{__("admin.new_arrival_note")}}</small>
                                    <select class="form-control select2 {{config('custom.select2-css')}} newarr" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_new_arrival">
                                        @foreach($isNewArrival as $key=>$value)
                                        <option value="{{$key}}" {{ (collect(old('is_new_arrival',[$record->is_new_arrival]))->contains($key)) ? 'selected':'' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_feature')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.status')}} </label>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active',[$record->is_active]))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_active')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.sorting_order')}}</label><span class="required"></span>
                                    <input type="text" readonly="" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$record->sorting!=0?$record->sorting:$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
							
							<div class="col-xs-12 col-md-2 newArrivalSorting" style="{{ $record->is_new_arrival ==1 ? '' : 'display:none' }}">
                                <div class="form-group">
                                    <label>New Arrival Sorting</label><span class="required"></span>
                                    <input type="text" readonly name="new_arrival_soting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('new_arrival_soting',$record->new_arrival_soting!=null?$record->new_arrival_soting:$newArrivalSortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">


                                <div class="box">
                                    <div class="box-header with-border">
                                        <label>{{__('admin.select_monogram')}}</label><span class=""> &nbsp;
                                            @if(count($monogram) > 2)
                                            <button type="button" class="view_all hide btn btn-outline-success btn-sm form-submit-btn" title="Submit">View</button> </span>
                                        @endif
                                    </div>

                                    <div class="box-body">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 10px"><input type="checkbox" checked name="select_all" id="select_all"></th>
                                                    <th>Monogram</th>
                                                    <th>Placement</th>

                                                </tr>
                                                <?php $emcount = 0;
                                                $mcount = count($monogram);
                                                ?>
                                                @foreach($monogram as $k => $data)

                                                <?php
                                                $style = '';
                                                if ($k > 1) {
                                                    $style = 'display:none';
                                                }
                                               
                                                $font = $data->getProductFont($data->id, $record->id);
                                                $sfont = '';
                                                $req = '';
                                                if ($font) {
                                                    $emcount++;
                                                    $sfont = 'checked';
                                                    $req = 'required';
                                                }
                                                ?>
                                                <tr class='{{$k > 1 ? "mm" : ""}}' style="{{$style}}">
                                                    <td><input type="checkbox" name="monogram[]" class="monogram" {{$sfont}} value="{{$data->id}}"></td>
                                                    <td>{{$data->style_name}}</td>
                                                    <td>
                                                        <?php
                                                        $placements = $data->getMonogramPlacement($data->id);

                                                        ?>

                                                        <div class="form-group">
                                                            <select class="form-control select2 {{config('custom.select2-css')}} mono-{{$data->id}} m-{{$k}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="placement[{{$data->id}}][]" multiple="" {{ $req}}>
                                                                 <option value="select_all">{{__('admin.select_all')}}</option>
                                                                 @foreach($placements as $v)


                                                                 <?php
                                                                 $sel = '';
                                                                 $editplacement = $data->getProductPlacement($data->id, $v->id, $record->id);

                                                                 if ($editplacement) {
                                                                     $sel = 'selected';
                                                                 }
                                                                 ?>
                                                                 <option value="{{$v->id}}" {{$sel}}>{{$v->placement_name}} {{$v->placement_desc}} {{$v->placement_size}}"</option>
                                                                 @endforeach
                                                             </select>
                                                        </div>

                                                    </td>
                                                </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <?php
                        $defaultImage      = $record->getDefaultImage();
                        $galleryImage      = $record->getGalleryImage($withoutColorImage = true);
                        ?>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group">
                                    <label>{{__('admin.product_default_image')}}</label> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}, {{__('admin.imageformat1')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_default_image" class="custom-file-input default-image" onchange="readURL(this, 'default-image','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_default_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('product_default_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <div class="form-group">
                                    <label class="profile_preview">&nbsp;</label>
                                    <div class="text-center">
                                        <img class="logo-table default-image text-center" src="{{asset(Storage::url($defaultImage->image_thumb_url))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="">
                                    <label>{{__('admin.product_images')}} <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}, {{__('admin.imageformat1')}}</small></label>
                                </div>
                            </div>
                        </div>
                        @if($galleryImage)
                        <div class="row">
                            <div class="col-md-12">
                                @foreach($galleryImage as $gImage)
                                <div class="float-left product-item" id="gallery_image_{{$gImage->id}}">
                                    <a title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertImage('{{route('admin.product.remove-gallery-image',['id'=>$gImage->id])}}','{{'gallery_image_'.$gImage->id}}')"><span class="notify-badge"><i class="fa fa-times"></i></span></a>
                                    <img class="gallery-image mt-1 mr-20" src="{{asset(Storage::url($gImage->image_thumb_url))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)" />
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="add-more-append mt-2">
                            @for($i = 1;$i<= session()->get('form_count');$i++)
                                <div class="row">
                                    <div class="col-md-11">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="product_images[]" class="custom-file-input other-image-<?php echo $i; ?>" onchange="readURL(this, 'other-image-<?php echo $i; ?>','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                                    <label class="custom-file-label">{{__('admin.product_images')}}</label>
                                                </div>
                                                <div class="input-group-append">
                                                    <!--<span class="input-group-text">Upload</span>-->
                                                </div>
                                            </div>
                                            @error('product_images.*')
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <div class="btn-toolbar float-right">
                                            <div class="btn-group mr-2">
                                                <div class="">
                                                    <img class="logo-table text-center other-image-<?php echo $i; ?>" src="" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'" />
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <button type="button" class="remove-btn {{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.remove')}}"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <button type="button" title="{{__('admin.add_more')}}" class="add_button text-center {{config('custom.btn-success-form')}}">{{__('admin.add_more')}} (<span class="add-more-limit">{{config('admin.product.addMoreLimit')}}</span>)</button>
                            </div>
                        </div>
                        @if(false)
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="add_return_exchange" id="add_return_exchange" value="1" {{ (collect(old('add_return_exchange'))->contains("1")) ? 'checked':'' }}>
                                        <label for="add_return_exchange">
                                            {{__('admin.want_to_add_return_exchange')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $recordDescription = $record->getProductDescription($language->id);
                            @endphp
                            <div class="col-md-12 return_exchange">
                                <div class="form-group">
                                    <label>{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})</label>
                                    <textarea class="return_exchange_field textarea form-control {{ $errors->has('product_return_exchange_policy'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})" name="product_return_exchange_policy_{{$language->id}}" rows="4" cols="300">{{old('product_return_exchange_policy_'.$language->id,$recordDescription?$recordDescription->product_return_exchange_policy:'')}}</textarea>
                                    @error('product_return_exchange_policy'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input class="intl_shipping_hidden" type="hidden" name="intl_shipping" value="0">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="intl_shipping" id="intl_shipping" value="1" {{ (collect(old('intl_shipping',$record->intl_shipping))->contains("1")) ? 'checked':'' }}>
                                        <label for="intl_shipping">
                                            {{__('admin.product_intl_shipping')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input class="product_seo_hidden" type="hidden" name="product_seo" value="0">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="product_seo" id="product_seo" value="1" {{ (collect(old('product_seo',$record->product_seo))->contains("1")) ? 'checked':'' }}>
                                        <label for="product_seo">
                                            {{__('admin.product_seo')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row seo-fields-box">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.product_seo_title')}}<span class=""></span></label>
                                    <input disabled="" class="form-control length-150 seo-fields" placeholder="{{__('admin.product_seo_title')}}" name="product_seo_title" type="text" value="{{old('product_seo_title',$record->product_seo_title)}}">
                                    @error('product_seo_title')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <div class="product_seo_description hidden"></div>
                                    <label>{{__('admin.product_seo_description')}}<span class=""></span></label>
                                    <input disabled="" class="form-control length-150 seo-fields" placeholder="{{__('admin.product_seo_description')}}" name="product_seo_description" type="text" value="{{old('product_seo_description',$record->product_seo_description)}}">
                                    @error('product_seo_description')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.product_seo_keyword')}}<span class=""></span></label>
                                    <input disabled="" class="form-control length-300 seo-fields" placeholder="{{__('admin.product_seo_keyword')}}" name="product_seo_keyword" type="text" value="{{old('product_seo_keyword',$record->product_seo_keyword)}}">
                                    @error('product_seo_keyword')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($canEditVarient)
                        <hr class="stock-hr">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="have_price_variant">
                                        {{__('admin.have_price_variant_index')}}
                                    </label>
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="have_price_variant" id="have_price_variant" value="1" {{ (collect(old('have_price_variant',$record->have_price_variant))->contains("1")) ? 'checked':'' }}>
                                        <label for="have_price_variant">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="stock-hr">
                        @endif
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.product.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                            </div>
                            @if($record->have_price_variant)
                            <div class="btn-group">
                                <a href="{{route('admin.product.stock',['id'=>$record->id])}}"><button type="button" class="ml-2 {{config('custom.btn-primary-form')}}" title="{{__('admin.manage_stock')}}"><i class="fas fa-database"></i></button></a>
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
@include('layout/ckeditor')
<script>
$(function() {
    CKEDITOR.on('instanceReady', function (evt) {

        if (!evt.editor.name.startsWith('product_description_')) {
            return;
        }

        evt.editor.on('paste', function (e) {

            const parser = new DOMParser();
            const doc = parser.parseFromString(e.data.dataValue, 'text/html');

            let output = '';

            function walk(node) {

                if (node.nodeType === Node.TEXT_NODE) {
                    return node.textContent;
                }

                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return '';
                }

                const tag = node.tagName.toLowerCase();

                // Every block becomes <p>
                if ([
                    'p','div','h1','h2','h3','h4','h5','h6',
                    'section','article','blockquote',
                    'li','td','th'
                ].includes(tag)) {

                    let text = '';

                    node.childNodes.forEach(child => {
                        text += walk(child);
                    });

                    text = text.trim();

                    return text ? '<p>' + text + '</p>' : '';
                }

                if (tag === 'br') {
                    return '<br>';
                }

                let html = '';

                node.childNodes.forEach(child => {
                    html += walk(child);
                });

                return html;
            }

            doc.body.childNodes.forEach(node => {
                output += walk(node);
            });

            e.data.dataValue = output;

        });

    });
});
</script>
@include('layout/fileupload')
@include('layout/sweetalert')
@include('layout/image_preview')
<script src="{{asset('plugins/moment/moment.min.js')}}" crossorigin="anonymous"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-4.js')}}" crossorigin="anonymous"></script>
@include('admin/product/product/product_edit_js')
@endsection

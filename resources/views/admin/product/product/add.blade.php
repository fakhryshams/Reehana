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
                        <li class="breadcrumb-item active">{{__('admin.add')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.add')}}</h3>
                </div>
                <form name="product" method="post" action="{{route('admin.product.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            <div class="col-md-12 p-0">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_name')}} ({{ucfirst($language->name)}})</label> <span class="required"></span>
                                        <input name="product_name_{{$language->id}}" type="text" class="form-control {{ $errors->has('product_name_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_name')}} ({{ucfirst($language->name)}})" value="{{old('product_name_'.$language->id)}}">
                                        @error('product_name_'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_description')}} ({{ucfirst($language->name)}})</label> <span class=""></span>
                                        <textarea class="textarea form-control {{ $errors->has('product_description_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_description')}} ({{ucfirst($language->name)}})" name="product_description_{{$language->id}}" rows="4" cols="300">{{old('product_description_'.$language->id)}}</textarea>
                                        @error('product_description_'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.product_barcode')}}</label><span class="required"></span>
                                    <input class="form-control length-100" placeholder="{{__('admin.product_barcode')}}" name="product_barcode" type="text" value="{{old('product_barcode')}}">
                                    @error('product_barcode')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.product_sku')}}</label><span class=""></span>
                                    <input class="form-control length-100" placeholder="{{__('admin.product_sku')}}" name="product_sku" type="text" value="{{old('product_sku')}}">
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
                                        <option value="{{$data->id}}" {{ (collect(old('brand_id'))->contains($data->id)) ? 'selected':'' }}>{{$brandName->brand_name?ucfirst($brandName->brand_name):''}}</option>
                                        @endforeach
                                    </select>
                                    <small class="{{config('custom.text-note-css')}}">
                                    </small>
                                    @error('brand_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.category')}}</label><span class="required"></span>
                                    <select id="category_id" title="" name="category_id[]" class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        <option selected="" value="0" disabled="">{{__('admin.select_category')}}</option>
                                        @foreach($category as $category)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option data-parent-id="{{$category->parent_id}}" value="{{$category->id}}" {{ (collect(old('category_id'))->contains($category->id)) ? 'selected':'' }}>
                                            @if($category->parent_id)
                                            @php
                                            $parentCategory = $category->getParent($category->parent_id);
                                            $parentCategoryName = $parentCategory->getCategoryDescription();
                                            @endphp
                                            {{$parentCategoryName?ucfirst($parentCategoryName->categories_name).' > ':''}}
                                            @endif
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
                                    <label>{{__('admin.sub_category')}}</label><span class="required"></span>
                                    <select id="subcategory_id" title="" name="sub_category_id[]" class="form-control select2 select-sub-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="">
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
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_regular_price')}}</label><span class="product_regular_price required"></span>
                                    <input class="form-control float-input length-10 main-price" placeholder="{{__('admin.product_regular_price')}}" name="product_regular_price" type="text" value="{{old('product_regular_price')}}">
                                    @error('product_regular_price')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <input type="hidden" name="main_discount_in" value="{{config('custom.discount_in')}}">
                                    <label>({{config('custom.discount_title')}}) {{__('admin.discount')}}</label>
                                    <input class="form-control float-input length-2 main-price" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}}" name="main_discount" type="text" value="{{old('main_discount')}}">
                                    @error('main_discount')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>{{__('admin.discount_price')}}</label>
                                    <input readonly="" class="form-control float-input" placeholder="{{__('admin.discount_price')}}" name="product_sale_price" type="text" value="{{old('product_sale_price')}}">
                                    @error('product_sale_price')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3 size-quantity-box-req">
                                <div class="form-group">
                                    <label>{{__('admin.product_quantity')}}</label><span class="size-quantity-box-req"></span>
                                    <input class="form-control float-input length-10 product_quantity" placeholder="{{__('admin.product_quantity')}}" name="product_quantity" type="text" value="{{old('product_quantity')}}">
                                    @error('product_quantity')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_weight')}}</label><span class="required"></span><small class="{{config('custom.text-note-css')}}">{{__('admin.weightkg')}}</small>
                                    <input class="form-control float-input length-10 product_quantity" placeholder="{{__('admin.product_weight')}}" name="product_weight" type="text" value="{{old('product_weight',0)}}">
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
                                    <input class="form-control float-input" placeholder="Length" name="product_length" type="text" value="{{old('product_length')}}">
                                    @error('product_length')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Width</label>
                                    <input class="form-control float-input" placeholder="Width" name="product_width" type="text" value="{{old('product_width')}}">
                                    @error('product_width')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input class="form-control float-input" placeholder="Height" name="product_height" type="text" value="{{old('product_height')}}">
                                    @error('product_height')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Dim Unit</label>
                                    <select class="form-control select2" name="dimension_unit">
                                        <option value="cm" {{old('dimension_unit') == 'cm' ? 'selected' : ''}}>cm</option>
                                        <option value="in" {{old('dimension_unit') == 'in' ? 'selected' : ''}}>in</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Weight Unit</label>
                                    <select class="form-control select2" name="weight_unit">
                                        <option value="kg" {{old('weight_unit') == 'kg' ? 'selected' : ''}}>kg</option>
                                        <option value="lb" {{old('weight_unit') == 'lb' ? 'selected' : ''}}>lb</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                     <label>Country of Origin</label>
                                    <input class="form-control" placeholder="ISO Country Code (e.g. SA or USA)" name="country_of_origin" type="text" value="{{ old('country_of_origin', 'KW') }}">
                                     @error('country_of_origin')
                                     <div class="text-red">{{ $message }}</div>
                                     @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>HS Code</label>
                                    <input class="form-control" placeholder="HS Code" name="hs_code" type="text" value="{{old('hs_code')}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.new_arrival')}} <small class="{{config('custom.text-note-css')}}">{{__("admin.new_arrival_note")}}</small></label>
                                    <select class="form-control select2 newarr {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_new_arrival">
                                        @foreach($isNewArrival as $key=>$value)
                                        <option value="{{$key}}" {{ (collect(old('is_new_arrival'))->contains($key)) ? 'selected':'' }}>{{$value}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_new_arrival')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.status')}}</label><span class="required"></span>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active'))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
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
                                    <input type="text" readonly="" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
							<div class="col-xs-12 col-md-2 newArrivalSorting" style="display:none">
                                <div class="form-group">
                                    <label>New Arrival Sorting</label><span class="required"></span>
                                    <input type="text" readonly name="new_arrival_soting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="1">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-7">



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
                                                @foreach($monogram as $k => $data)
                                                <?php
                                                $style = '';
                                                if ($k > 1) {
                                                    $style = 'display:none';
                                                }


                                                ?>
                                                <tr class='{{$k > 1 ? "mm" : ""}}' style="{{$style}}">
                                                    <td><input type="checkbox" name="monogram[]" class="monogram " checked value="{{$data->id}}"></td>
                                                    <td>{{$data->style_name}}</td>
                                                    <td>
                                                        <?php $placement = $data->getMonogramPlacement($data->id);
                                                        ?>

                                                        <div class="form-group">
                                                            <select class="form-control select2 {{config('custom.select2-css')}} mono-{{$data->id}} m-{{$k}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="placement[{{$data->id}}][]" multiple="" required>
                                                                <option value="select_all">{{__('admin.select_all')}}</option>
                                                                @foreach($placement as $record)
                                                                <option value="{{$record->id}}" selected>{{$record->placement_name}} {{$record->placement_desc}} {{$record->placement_size}}"</option>
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
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group">
                                    <label>{{__('admin.product_default_image')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}, {{__('admin.imageformat1')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input required="" type="file" name="product_default_image" class="custom-file-input default-image" onchange="readURL(this, 'default-image','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_default_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="text-red default-image-msg"></div>
                                    @error('product_default_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image text-center mt-1" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)" />
                                </div>
                            </div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="">
                                    <label>{{__('admin.product_images')}}</label> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}, {{__('admin.imageformat1')}}</small>
                                </div>
                            </div>
                        </div>
                        <div class="add-more-append">
                            @for($i = 1;$i<= session()->get('form_count');$i++)
                                <div class="row">
                                    <div class="col-11">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="product_images[]" class="custom-file-input" onchange="readURL(this, 'other-image-<?php echo $i; ?>','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
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
                                    <div class="col-1 text-center">
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
                            <div class="col-md-12 p-0 return_exchange">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})</label>
                                        <textarea class="return_exchange_field textarea form-control {{ $errors->has('product_return_exchange_policy'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.product_return_exchange_policy')}} ({{ucfirst($language->name)}})" name="product_return_exchange_policy_{{$language->id}}" rows="4" cols="300">{{old('product_return_exchange_policy_'.$language->id)}}</textarea>
                                        @error('product_return_exchange_policy'.$language->id)
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="intl_shipping" id="intl_shipping" value="1" {{ (collect(old('intl_shipping'))->contains("1")) ? 'checked':'' }}>
                                        <label for="intl_shipping">
                                            {{__('admin.product_intl_shipping')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="product_seo" id="product_seo" value="1" {{ (collect(old('product_seo'))->contains("1")) ? 'checked':'' }}>
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
                                    <input disabled="" class="form-control length-150 seo-fields" placeholder="{{__('admin.product_seo_title')}}" name="product_seo_title" type="text" value="{{old('product_seo_title')}}">
                                    @error('product_seo_title')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.product_seo_description')}}<span class=""></span></label>
                                    <div class="product_seo_description hidden"></div>
                                    <input disabled="" class="form-control length-150 seo-fields" placeholder="{{__('admin.product_seo_description')}}" name="product_seo_description" type="text" value="{{old('product_seo_description')}}">
                                    @error('product_seo_description')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.product_seo_keyword')}}<span class=""></span></label>
                                    <input disabled="" class="form-control length-300 seo-fields" placeholder="{{__('admin.product_seo_keyword')}}" name="product_seo_keyword" type="text" value="{{old('product_seo_keyword')}}">
                                    @error('product_seo_keyword')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @if(false)
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.product_seo_url')}}<span class=""></span></label>
                                    <input disabled="" class="form-control length-150 seo-fields" placeholder="{{__('admin.product_seo_url')}}" name="product_seo_url" type="text" value="{{old('product_seo_url')}}">
                                    @error('product_seo_url')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <hr class="stock-hr">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="have_price_variant">
                                        {{__('admin.have_price_variant_index')}}
                                    </label>
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="have_price_variant" id="have_price_variant" value="1" {{ (collect(old('have_price_variant',1))->contains("1")) ? 'checked':'' }}>
                                        <label for="have_price_variant">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="stock-hr">
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.product.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}} form-submit-btn" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
                            </div>
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
@include('layout/image_preview')
@include('layout/sweetalert')
<script src="{{asset('plugins/moment/moment.min.js')}}" crossorigin="anonymous"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-4.js')}}" crossorigin="anonymous"></script>
<script>
    var addMoreLimit = "{{config('admin.product.addMoreLimit')}}";
    var addMoreCount = '<?php echo session()->get('form_count'); ?>';
    var dynamicClass = 0;
    $(document).ready(function() {
        changeCount();
        setMainDiscount();
        /*
         * For delete old input image 
         */

$('.newarr').select2();
		  $('.newarr').on('change', function () {
			 
			 if($(this).val() == 1)
			 {
				 $('.newArrivalSorting').show();
				 
			 }
			 else
			 {
				 $('.newArrivalSorting').hide();
				 
				 
			 }
			 
			 
		});	 

        $(".view_all").click(function() {
       
            if($(".view_all").hasClass( "hide" ))
            {  
                $('.view_all').text('Hide');
                $('.mm').show();
                $(".view_all").removeClass('hide');
            }
            else
            {

                $('.view_all').text('View');
               
                $('.mm').hide();

                $(".view_all").addClass('hide');
                
            }

       
            
        });

      
        $("#select_all").click(function() {
            if ($('#select_all').is(':checked')) {
                $('input[name="monogram[]"]').each(function(index, elem) {
                    var $elem = $(elem);
                    this.checked = true;
                    $('.m-' + index).prop('required', true);
                });
            } else {
                $(".monogram").prop('required', false);
                $('input[name="monogram[]"]').each(function(index, elem) {
                    var $elem = $(elem);
                    this.checked = false;
                    $('.m-' + index).prop('required', false);
                    if ($(this).val()) {
                        $('.mono-' + $(this).val()).val([]).trigger('change');
                    }
                });
            }
        });

        $(".monogram").click(function() {
            if ($(this).is(':checked')) {
                $(this)
                if ($(this).val()) {
                    $('.mono-' + $(this).val()).prop('required', true);
                }
                $(this).attr('checked', 'checked');
            } else {
                $(this).prop('required', false);
                if ($(this).val()) {
                    $('.mono-' + $(this).val()).prop('required', false);
                    $('.mono-' + $(this).val()).val([]).trigger('change');
                }
                $(this).removeAttr('checked');

            }

        });




        removeElement();
        $(".add_button").click(function() {
            addMoreCount++;
            dynamicClass++;
            if (addMoreCount <= addMoreLimit) {
                bindHtml(dynamicClass);
                if (addMoreCount == addMoreLimit) {
                    $(this).attr("disabled", "disabled");
                    $(this).hide();
                }
            } else {
                $(this).attr("disabled", "disabled");
                $(this).hide();
            }
        });
        $(".select-category").select2({
            placeholder: "{{__('admin.select_category')}}",
            //                                                            allowClear: true,
            selectionTitleAttribute: true

        });
        $(".select-sub-category").select2({
            placeholder: "{{__('admin.select_subcategory')}}",
            allowClear: true
        });
        $(".select-category").change(function() {
            getSubCategory();
        });
        $(".select-brand").select2({
            placeholder: "{{__('admin.select_brand')}}",
            allowClear: true
        });
        $(".select-monogram").select2({
            placeholder: "{{__('admin.select_monogram')}}",
            allowClear: true
        });
        /*
         *OnLoad 
         */
        getSubCategory();
        seoFields($("input[name=product_seo]"));
        returnExchangeFields($("input[name=add_return_exchange]"));
        //sizePriceVarient($("#have_price_variant"));

        /*
         * OnChange
         */
        $("input[name=product_seo]").change(function() {
            seoFields($(this));
        });
        $("input[name=add_return_exchange]").change(function() {
            returnExchangeFields($(this));
        });
        $("#have_price_variant").change(function() {
            //sizePriceVarient($("#have_price_variant"));
        });
        $(document).on('change', 'select[name^="placement"]', function() {
            var $select = $(this);

            var $selectAll = $select.find('option[value="select_all"]');

            if ($selectAll.length && $selectAll.is(':selected')) {
                $selectAll.prop('selected', false);
                $select.find('option').each(function() {
                    if ($(this).val() !== 'select_all') {
                        $(this).prop('selected', true);
                    }
                });
                $select.trigger('change.select2');
            }

            var monogramId = (this.className.match(/mono-(\d+)/) || [])[1];
            if (monogramId) {
                var hasSelection = $select.find('option:selected').length > 0;
                $('input.monogram[value="' + monogramId + '"]').prop('checked', hasSelection);
                $select.prop('required', hasSelection);
            }
        });
    });

    function removeElement() {
        $(".remove-btn").click(function() {
            $(this).closest('div.row').remove(); //Remove field html
            addMoreCount--; //Decrease added count
            $(".add-more-limit").text(addMoreLimit - addMoreCount) //Change count text
            $(".add_button").removeAttr("disabled"); //enable button
            $(".add_button").show();
        });
    }

    function returnExchangeFields(flag) {
        if (flag.prop('checked')) {
            //                                                            $(".return_exchange_field").removeAttr('disabled');
            $(".return_exchange").show();
        } else {
            //                                                            $(".return_exchange_field").attr('disabled', 'disabled');
            $(".return_exchange_field").val("");
            $(".return_exchange").hide();
        }
    }

    function sizePriceVarient(flag) {
        if (flag.prop('checked')) {
            $("input[name=product_quantity]").val("");
            $("input[name=product_quantity]").attr("disabled", true);
        } else {
            $("input[name=product_quantity]").attr("disabled", false);
        }
    }

    function seoFields(seo) {
        if (seo.prop('checked')) {
            $(".seo-fields").removeAttr('disabled');
            $(".seo-fields-box").show();
            $("input[name=product_seo_title]").val($("input[name=product_name_1]").val());
            $(".product_seo_description").html($("[name=product_description_1]").val());
            $("input[name=product_seo_description]").val($(".product_seo_description").text());
        } else {
            $(".seo-fields").attr('disabled', 'disabled');
            $(".seo-fields").val("");
            $(".seo-fields-box").hide();
        }
    }

    function readURL(input, imageClass, requiredWidth, requiredHeight) {
        if (input.files && input.files[0]) {
            var inputFile = input.files[0];
            var file = $('.' + imageClass);
            if (isImage(file.val())) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var image = new Image();
                    image.src = e.target.result;
                    image.onload = function() {
                        var height = this.height;
                        var width = this.width;
                        //alert(width + "," + height);

                        if (width == requiredWidth && height == requiredHeight) {
                            $('.' + imageClass).attr('data-res', 'true');
                            $('.' + imageClass).removeClass('is-invalid');
                            $('.' + imageClass + '-msg').html('');
                            $('.' + imageClass).attr('src', e.target.result);
                            validateFiles();
                        } else {
                            $('.' + imageClass).addClass('is-invalid');
                            $('.' + imageClass).attr('src', "{{ asset(Storage::url('default.png')) }}");
                            $('.' + imageClass).attr('data-res', 'false');
                            show_error("Required Dimension is " + requiredWidth + "x" + requiredHeight);
                            $('.' + imageClass + '-msg').html("{{__('admin.media_invalid')}}");
                            $('.form-submit-btn').attr('disabled', 'disabled');
                            validateFiles();
                        }
                    };
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                $('.' + imageClass).attr('data-res', 'false');
                show_error("{{__('admin.error-mime')}}");
                $('.form-submit-btn').attr('disabled', 'disabled')
                validateFiles();
            }
        }
    }

    function validateFiles() {
        var filesData = $("input[type=file]");
        $.each(filesData, function() {
            var att = $(this).attr('data-res');
            if (att == "false") {
                $('.form-submit-btn').attr('disabled', 'disabled');
                return false; // breaks
            } else {
                $('.form-submit-btn').removeAttr('disabled');
            }
        });
    }

    function getExtension(filename) {
        var parts = filename.split('.');
        return parts[parts.length - 1];
    }

    function isImage(filename) {
        var ext = getExtension(filename);
        switch (ext.toLowerCase()) {
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'bmp':
            case 'png':
                //case 'svg':
                //etc
                return true;
        }
        return false;
    }
    /*
     * Bind Image Div
     */
    function bindHtml(countHtml) {
        $.ajax({
            type: "POST",
            url: "{{route('admin.product.bind-html')}}",
            data: {
                count: countHtml,
                '_token': "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res) {
                    $(".add-more-append").append(res);
                    changeCount();
                    /*
                     * Bind file input for newly added element
                     */
                    bsCustomFileInput.init();
                    /*
                     * Unbind previously binded event and bind it again
                     */
                    $(".remove-btn").unbind('click');
                    removeElement();
                }
            }
        });
    }

    /*
     * Change Addmore Count
     */
    function changeCount() {
        $(".add-more-limit").text(addMoreLimit - addMoreCount)
    }

    /*
     * For Size-Quantity Data, Disply only if category is selected
     */
    function checkCategoryForSizeQuantity() {
        var categoryId = $(".select-category").find(':selected').attr('data-parent-id');
        if (categoryId) {
            //$(".size-quantity-data").show();
            //$(".size-quantity").removeAttr('disabled', 'disabled');

            $.ajax({
                type: "POST",
                url: "{{route('admin.product.bind-size-quantity')}}",
                data: {
                    category: categoryId,
                    '_token': "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res) {
                        $(".size-quantity-data").html(res.view);
                        commonHelper();
                        setDiscount();
                        setPriceTextBox(res.haveSizePriceVariant);
                    } else {
                        $(".size-quantity-data").html("");
                    }
                }
            });
        } else {
            //$(".size-quantity-data").hide();
            //$(".size-quantity").attr('disabled', 'disabled');
        }
    }

    function setDiscount() {
        $(".size-discount").unbind('change');
        $(".size-discount").change(function() {
            var selectedSizeId = $(this).attr('data-size');
            var price = $("input[name=price_" + selectedSizeId + "]").val();
            var discount = $("input[name=discount_" + selectedSizeId + "]").val();
            var calculatedDiscount = "";
            var discountIn = "{{config('custom.discount_in')}}";
            var numberFixed = "{{config('custom.number_fixed')}}";
            if (discountIn == "P") {
                if (discount && discount > 0) {
                    calculatedDiscount = (price * discount) / 100;
                    calculatedDiscount = price - calculatedDiscount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            } else if (discountIn == "F") {
                if (discount && discount > 0) {
                    calculatedDiscount = price - discount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }

            if (calculatedDiscount) {
                $("input[name=discount_price_" + selectedSizeId + "]").val(calculatedDiscount);
            } else {
                $("input[name=discount_price_" + selectedSizeId + "]").val("");
            }
        });
    }

    function setMainDiscount() {
        $(".main-price").unbind('change');
        $(".main-price").change(function() {
            var price = $("input[name=product_regular_price]").val();
            var discount = $("input[name=main_discount]").val();
            var calculatedDiscount = "";
            var discountIn = "{{config('custom.discount_in')}}";
            var numberFixed = "{{config('custom.number_fixed')}}";
            if (discountIn == "P") {
                if (discount && discount > 0) {
                    calculatedDiscount = (price * discount) / 100;
                    calculatedDiscount = price - calculatedDiscount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            } else if (discountIn == "F") {
                if (discount && discount > 0) {
                    calculatedDiscount = price - discount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }

            if (calculatedDiscount) {
                $("input[name=product_sale_price").val(calculatedDiscount);
            } else {
                $("input[name=product_sale_price]").val("");
            }
        });
    }

    function setPriceTextBox(haveSizePriceVariant) {
        if (haveSizePriceVariant) {
            $(".product_regular_price").removeClass("required");
            $("input[name=product_regular_price]").val("");
            $("input[name=product_regular_price]").attr("readonly", true);
            $("input[name=product_sale_price]").val("");
            $("input[name=product_sale_price]").attr("readonly", true);
            $("input[name=main_discount]").val("");
            $("input[name=main_discount]").attr("readonly", true);
        } else {
            $(".product_regular_price").removeClass("required");
            $(".product_regular_price").addClass("required");
            $("input[name=product_regular_price]").val("");
            $("input[name=product_regular_price]").removeAttr("readonly");
            $("input[name=product_sale_price]").val("");
            //$("input[name=product_sale_price]").removeAttr("readonly");
            $("input[name=main_discount]").val("");
            $("input[name=main_discount]").removeAttr("readonly");
        }
    }

    function getSubCategory() {
        var categoryID = $("#category_id").find("option:selected").val();
        var oldSubCategory = '<?php echo json_encode(old("sub_category_id")); ?>';
        if (categoryID > 0) {
            $.ajax({
                type: "post",
                url: "{{route('admin.product.get-subcategory')}}",
                data: {
                    'categoryID': categoryID,
                    oldSubCategory: oldSubCategory,
                    '_token': "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res) {
                        $("#subcategory_id").empty();
                        $("#subcategory_id").append(res);
                    } else {
                        $("#subcategory_id").empty();
                    }
                },
                beforeSend: function() {
                    $("#pageloader").fadeIn();
                },
                complete: function() {
                    $("#pageloader").fadeOut();
                }
            });
        }
    }
</script>
@endsection

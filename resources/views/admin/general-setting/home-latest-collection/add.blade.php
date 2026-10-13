@extends('layout/main')
@section('page_title',  trans('admin.home-latest-collection'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.home-latest-collection')}}</h1>  
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.home-latest-collection.index')}}">{{__('admin.home-latest-collection')}}</a></li>
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
                <form method="post" action="{{route('admin.general-setting.home-latest-collection.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.menu_category')}}<span class="required"></span></label>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}} select-category" data-dropdown-css-class="{{config('custom.select2-css')}}" name="category_id" id="category_id">
                                        @foreach($categoryList as $record)
                                        @php
                                        $categoryName = $record->getCategoryDescription();
                                        @endphp
                                        <option value="{{$record->id}}" {{ (collect(old('category_id'))->contains($record->id)) ? 'selected':'' }}>{{$categoryName?$categoryName->categories_name:''}}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.menu_subcategory')}}<span class="required"></span></label>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}} select-sub-category" data-dropdown-css-class="{{config('custom.select2-css')}}" name="subcategory_id" id="subcategory_id">
                                        <option value="" selected="" disabled="">{{__('admin.main_sub_category')}}</option>
                                    </select>
                                    @error('subcategory_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.product')}}<span class="required"></span></label>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}} select-product" data-dropdown-css-class="{{config('custom.select2-css')}}" name="product_id" id="product_id">
                                        <option value="" selected="" disabled="">{{__('admin.main_product')}}</option>
                                    </select>
                                    @error('product_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.is_center')}}<span class="required"></span></label>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_center" id="is_center">
                                        @foreach($isCenter as $key=>$val)
                                        <option value="{{$key}}" {{ (collect(old('is_center'))->contains($key)) ? 'selected':'' }}>{{$val}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_center')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.image')}}<span class="required"></span><small class="{{config('custom.text-note-css')}}" id="dimesion">  </small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="photo" class="custom-file-input {{ $errors->has('photo') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image4')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                    </div>
                                    @error('photo')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image4 text-center mt-1" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.general-setting.home-latest-collection.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
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
@include('layout/fileupload')
@include('layout/select2')
@include('layout/image_preview')
<script>
    function readURL(input, imageClass)
    {
        if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e)
            {
                $('.' + imageClass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $(document).ready(function ()
    {
        /*
         * get sub category on category change event.
         */
        $("#category_id").change(function ()
        {
            getSubCategory();
        });
        getSubCategory();
        function getSubCategory()
        {
            var categoryID = $("#category_id").find("option:selected").val();
            var oldSubCategory = "{{old('subcategory_id')}}";
            if (categoryID)
            {
                $.ajax({
                    type: "post",
                    url: "{{route('admin.general-setting.home-latest-collection.get-subCategory')}}",
                    data: {'categoryID': categoryID, oldSubCategory: oldSubCategory, '_token': "{{ csrf_token() }}"},
                    success: function (res)
                    {
                        if (res)
                        {

                            $("#subcategory_id").empty();
                            $("#product_id").empty();
                            $("#subcategory_id").append(res);
                            getProduct();
                        }
                        else
                        {
                            $("#subcategory_id").empty();
                            $("#product_id").empty();
                            $("#subcategory_id").append("<option value='' selected='' disabled=''>{{__('admin.main_sub_category')}}</option>");
                            $("#product_id").append("<option value='' selected='' disabled=''>{{__('admin.main_product')}}</option>");
                        }

                    }
                });
            }
        }
        /*
         * get product on based on category and subcategory.
         */
        $("#subcategory_id").change(function ()
        {
            getProduct();
        });
        getProduct();
        function getProduct()
        {
            var categoryID = $("#category_id").find("option:selected").val();
            var subCategoryID = $("#subcategory_id").find("option:selected").val();
            var oldProduct = "{{old('product_id')}}";
            if (categoryID && subCategoryID)
            {
                $.ajax({
                    type: "post",
                    url: "{{route('admin.general-setting.home-latest-collection.get-product')}}",
                    data: {'categoryID': categoryID, subCategoryID: subCategoryID, oldProduct: oldProduct, '_token': "{{ csrf_token() }}"},
                    success: function (res)
                    {
                        if (res)
                        {
                            $("#product_id").empty();
                            $("#product_id").append(res);
                        }
                        else
                        {
                            $("#product_id").empty();
                            $("#product_id").append("<option value='' selected='' disabled=''>{{__('admin.main_product')}}</option>");
                        }

                    }
                });
            }
        }
        $("#is_center").change(function ()
        {
            setDimension();
        });
        setDimension();
        function setDimension()
        {
            var Value = $("#is_center").find("option:selected").val();
            var dimension = "{{__('admin.error-latest-dimensions-no-photo')}}";
            if (Value == 1)
            {
                dimension = "{{__('admin.error-latest-dimensions-yes-photo')}}";
            }
            $("#dimesion").html(dimension);
        }
        /*
         * set selected value for dropdown
         */
        $(".select-product").select2({
            placeholder: "{{__('admin.main_product')}}",

        });
        $(".select-sub-category").select2({
            placeholder: "{{__('admin.main_sub_category')}}",
        });
        $(".select-category").select2({
            placeholder: "{{__('admin.main_category')}}",
        });
    });
</script>
@endsection
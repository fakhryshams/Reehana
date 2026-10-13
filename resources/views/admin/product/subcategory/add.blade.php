@extends('layout/main')
@section('page_title',  trans('admin.sub_category'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.sub_category')}} {{$subCategoryOf?'('.$subCategoryOf.')':''}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.category.index')}}">{{__('admin.category')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.subcategory.index',['id'=>''])}}">{{__('admin.sub_category')}}</a></li>
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
                <form method="post" action="{{route('admin.product.subcategory.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.main_category')}}</label><span class="required"></span>
                                    <select name="parent_id" class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @if(!$subCategoryID)
                                        <option value="" selected="" disabled="">{{__('admin.main_category')}}</option>
                                        @endif
                                        @foreach($categoryList as $category)
                                        @if($category->parent_id == 0)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option value="{{$category->id}}" {{ (collect(old('parent_id',$subCategoryID))->contains($category->id)) ? 'selected':'' }}>
                                            {{$categoryName?ucfirst($categoryName->categories_name):''}}</option>
                                        @endif
                                        @endforeach
                                    </select>

                                    @error('parent_id')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            @foreach($languageList as $language)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.sub_category_name')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="category_name_{{$language->id}}" type="text" class="form-control length-50 {{ $errors->has('category_name_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.sub_category_name')}} ({{ucfirst($language->name)}})" value="{{old('category_name_'.$language->id)}}">
                                    @error('category_name_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(false)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.min_order_quantity')}}<span class="required"></span></label>
                                    <input type="text" name="min_order_quantity" placeholder="{{__('admin.min_order_quantity')}}" class="form-control number-input {{ $errors->has('min_order_quantity') ?'is-invalid':'' }}" value="{{old('min_order_quantity')}}">
                                    @error('min_order_quantity')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.max_order_quantity')}}<span class="required"></span></label>
                                    <input type="text" name="max_order_quantity" placeholder="{{__('admin.max_order_quantity')}}" class="form-control number-input {{ $errors->has('max_order_quantity') ?'is-invalid':'' }}" value="{{old('max_order_quantity')}}">
                                    @error('max_order_quantity')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.sub_category_image')}} <span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.sub_category_image_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_image" class="custom-file-input {{ $errors->has('category_image') ?'is-invalid':'' }}" onchange="readURL(this, 'category-image')">
                                            <label class="custom-file-label">{{__('admin.sub_category_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('category_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="profile_preview">{{__('admin.sub_category_image')}}</label>
                                <div class="text-center">
                                    <img class="logo-table category-image text-center" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            @if(false)
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.sub_category_icon')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.sub_category_icon_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_icon" class="custom-file-input {{ $errors->has('category_icon') ?'is-invalid':'' }}"  onchange="readURL(this, 'category-icon')">
                                            <label class="custom-file-label">{{__('admin.sub_category_icon')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('category_icon')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="profile_preview">{{__('admin.sub_category_icon')}}</label>
                                <div class="text-center">
                                    <img class="logo-table category-icon text-center" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/></span>
                                </div>
                            </div>
                             @endif
                        </div>
                        <!--@if(config('custom.category_show_on_homepage_limit',8)>$homePageCount)-->
                        <!--<div class="row">-->
                        <!--    <div class="col-md-12">-->
                        <!--        <div class="form-group"> -->
                        <!--            <label>{{__('admin.show_on_homepage')}} <small class="{{config('custom.text-note-css')}}">({{__('admin.category_show_on_homepage_total_limit')}} {{$homePageCount}})</small></label>-->
                        <!--            <div class="icheck-primary ">-->
                        <!--                <input name="show_on_homepage" type="checkbox" value="1" id="homepage" {{old('show_on_homepage')?'checked':''}}>-->
                        <!--                <input name="show_on_homepage" type="hidden" value="" {{old('show_on_homepage')?'disabled':''}}>-->
                        <!--                <label for="homepage">-->
                        <!--                </label>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--@endif-->
                       
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.product.subcategory.index',['id'=>''])}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
@include('layout/select2')
@include('layout/fileupload')
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
</script>
<script>
    $(function ()
    {
        $('input[name=show_on_homepage]').click(function ()
        {
            if ($(this).prop('checked'))
            {
                $('input[name=show_on_homepage][type=hidden]').attr('disabled', 'disabled');
            }
            else
            {
                $('input[name=show_on_homepage][type=hidden]').removeAttr('disabled');
            }
        });
    });
</script>
@endsection
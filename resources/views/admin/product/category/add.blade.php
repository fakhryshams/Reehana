@extends('layout/main')
@section('page_title',  trans('admin.category'))
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
                    <h1>{{__('admin.category')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.category.index')}}">{{__('admin.category')}}</a></li>
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
                <form method="post" action="{{route('admin.product.category.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row hidden">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.main_category')}}</label><span class="required"></span><small class="{{config('custom.text-note-css')}}"> {{__('admin.select_main_category_note')}} </small>
                                    <select name="parent_id"  class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        <option selected=""  value="0" {{ collect(old('parent_id','0'))->contains('0') ? 'selected' : ''  }}>{{__('admin.leave_as_parent')}}</option>
                                        @if(false)
                                        @foreach($categoryList as $category)
                                        @if($category->parent_id == 0)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option value="{{$category->id}}" {{ (collect(old('parent_id'))->contains($category->id)) ? 'selected':'' }}>@if($category->parent_id)
                                            @php
                                            $parentCategory = $category->getParent($category->parent_id);
                                            $parentCategoryName = $parentCategory->getCategoryDescription();
                                            @endphp
                                            {{$parentCategoryName?ucfirst($parentCategoryName->categories_name).' > ':''}}
                                            @endif
                                            {{$categoryName?ucfirst($categoryName->categories_name):''}}</option>
                                        @endif
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('parent_id')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($languageList as $language)
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.category_name')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="category_name_{{$language->id}}" type="text" class="form-control length-27 {{ $errors->has('category_name_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.category_name')}} ({{ucfirst($language->name)}})" value="{{old('category_name_'.$language->id)}}">
                                    @error('category_name_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}</label> <span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if(false)
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
                            @endif

                            @if(false)
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.show_customized_form')}}</label>
                                    <div class="icheck-primary ">
                                        <input name="show_customized_form" type="checkbox" value="1" id="homepage" {{old('show_customized_form')?'checked':''}}>
                                        <input name="show_customized_form" type="hidden" value="" {{old('show_customized_form')?'disabled':''}}>
                                        <label for="homepage">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if(false)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.category_image')}} <span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.category_image_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_image" class="custom-file-input {{ $errors->has('category_image') ?'is-invalid':'' }}" onchange="readURL(this, 'category-image')">
                                            <label class="custom-file-label">{{__('admin.category_image')}}</label>
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
                                <label class="profile_preview">{{__('admin.category_image')}}</label>
                                <div class="text-center">
                                    <img class="logo-table category-image text-center" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.category_icon')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.category_icon_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_icon" class="custom-file-input {{ $errors->has('category_icon') ?'is-invalid':'' }}"  onchange="readURL(this, 'category-icon')">
                                            <label class="custom-file-label">{{__('admin.category_icon')}}</label>
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
                                <label class="profile_preview">{{__('admin.category_icon')}}</label>
                                <div class="text-center">
                                    <img class="logo-table bg-dark category-icon text-center" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/></span>
                                </div>
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="have_price_variant" id="have_price_variant" {{ (collect(old('have_price_variant'))->contains("1")) ? 'checked':'' }} value="1">
                                        <label for="have_price_variant">
                                            {{__('admin.have_price_variant')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                         @endif
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.product.category.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
        $('input[name=show_customized_form]').click(function ()
        {
            if ($(this).prop('checked'))
            {
                $('input[name=show_customized_form][type=hidden]').attr('disabled', 'disabled');
            } else
            {
                $('input[name=show_customized_form][type=hidden]').removeAttr('disabled');
            }
        });
    });
</script>
@endsection
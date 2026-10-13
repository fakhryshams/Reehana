@extends('layout/main')
@section('page_title',  trans('admin.size'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/tags/bootstrap-tagsinput.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<style>
    .bootstrap-tagsinput .tag{
        margin-right: 2px;
        color: #fff;
        background-color: #CAA42F !important;
        padding: 3px;
        border-radius: 6px;
    }
    .bootstrap-tagsinput{
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
</style>
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.size')}}</h1>
                </div>  
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.size.index')}}">{{__('admin.size')}}</a></li>
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
                    <h3 class="card-title">{{__('admin.add_new')}}</h3>
                </div>
                <form method="post" action="{{route('admin.master.size.add')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_category')}}</label><span class="required"></span>
                                    <select class="w-100 form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" id='category_id' name="category_id[]" multiple="">
                                        @foreach($categories as $category)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option value="{{$category->id}}" {{ (collect(old('category_id'))->contains($category->id)) ? 'selected':'selected' }}>{{ucfirst($categoryName->categories_name)}}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.sub_category')}}</label><span class="required"></span>
                                    <select id="subcategory_id" title="" name="sub_category_id[]" required class="form-control select2 select-sub-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="">
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
                            @foreach($languageList as $language)
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group ">
                                    <label>{{__('admin.size')}} ({{ucfirst($language->name)}})</label><span class="required"></span> <span class="{{config('custom.text-note-css')}}">{{__('admin.add_multiple_help_text')}}</span>
                                    <input data-role="tagsinput" name="size_{{$language->id}}" type="text" class="form-control {{ $errors->has('size_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.size')}} ({{ucfirst($language->name)}})" value="{{old('size_'.$language->id)}}">
                                    @error('size_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @if(false)
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}} <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label><span class="required"></span>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.size.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script src="{{asset('plugins/tags/bootstrap-tagsinput.min.js')}}"></script>
<script>
$(function () {
    $(".select-category").select2({
        placeholder: "{{__('admin.select_category')}}",
        allowClear: true,
        selectionTitleAttribute: false

    }); 
    
    getSubCategory();

      $(".select-category").change(function() {
        
            getSubCategory();
        });
});

function getSubCategory() {
        var categoryID = $("#category_id").find("option:selected").val();
        
           var categoryID = $("#category_id :selected").map(function(i, el) {
                                                    return $(el).val();
                                                    }).get();
                                                    
                                                   
                                                    
        var oldSubCategory = '<?php echo json_encode(old("sub_category_id")); ?>';
        
        if (categoryID) {
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


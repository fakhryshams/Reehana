@extends('layout/main')
@section('page_title',  trans('admin.size'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
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
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <form method="post" action="{{route('admin.master.size.update',['id' => $singleRecord->id])}}">
                <div class="card {{config('custom.card-primary')}}">
                    <div class="card-header">
                        <h3 class="card-title">{{__('admin.edit')}}</h3>
                    </div>
                    @csrf
                    <div class="card-body">
                        <?php
                        $sizeCatogory = $singleRecord->getSizeCategory(1);
                      $sizeSubCatogory = $singleRecord->getSizeSubCategory($singleRecord->id);
                        
                        
                        
                        ?>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_category')}}</label><span class="required"></span>
                                    <select id="category_id" class="w-100 form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="category_id[]" multiple="">
                                        @foreach($categories as $category)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option value="{{$category->id}}" {{ (collect(old('category_id',$sizeCatogory))->contains($category->id)) ? 'selected':'' }}>{{ucfirst($categoryName->categories_name)}}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        
                         <div class="row">
                        <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.sub_category')}}</label>
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
                            @foreach($languageList as $language)
                            @php
                            $record = $singleRecord->getSizeDescription($language->id);
                            @endphp
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.size')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="size_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('size_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.size')}} ({{ucfirst($language->name)}})" value="{{old('size_'.$language->id,$record?$record->size_title:'')}}">
                                    @error('size_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}} <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label><span class="required"></span>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$singleRecord->sorting!=0?$singleRecord->sorting:$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.size.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
<script>
    $(function () {
        $(".select-category").select2({
            placeholder: "{{__('admin.select_category')}}",
            allowClear: true,
            selectionTitleAttribute: false
        });
        
          $(".select-sub-category").select2({
            placeholder: "Select Sub Category",
            allowClear: true,
            selectionTitleAttribute: false
        });
        
        
       
        $("#category_id").change(function() {
            getSubCategory();
        });
        getSubCategory();
    });
    
    function getSubCategory() {
        var categoryID = $("#category_id").find("option:selected").val();
        
           var categoryID = $("#category_id :selected").map(function(i, el) {
                                                    return $(el).val();
                                                    }).get();
        var oldSubCategory = '<?php echo json_encode(old("sub_category_id", $sizeSubCatogory)); ?>';
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
                }
            });
        }
    }
</script>
@endsection

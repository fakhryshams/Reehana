@extends('web/layout/main')
@section('page_title', trans('web.product'))
@section('body_class', 'listing')
@section('content')


<?php 
 $language = Helper::getLanguage();
                   
$categoryName = $category->getCategoryDescription($language->id);

?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">Category</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{$categoryName->categories_name?ucfirst($categoryName->categories_name):''}}</a></span>
        
      
    </div>
</section>

<section class="products-and-filters">
    <div class="container-fluid">
        <div class="h-flex">
            <div class="col-md-12 products">
                <div class="products-list">
                    <div class="row">
                    @foreach($subCategories as $record)
                     <?php 
                    $cat = \App\AdminModel\Product\AdminCategory::find($record->id); 
                    $subCategoryName = $cat->getCategoryDescription($language->id);
                     ?>
                    <div class="col-md-4 col-xs-6">
                        <div class="product ">
                            <div class="product-image">
                                <a href="{{route("web.product.index",['slug'=>$record->categories_slug])}}">
                                    <img src="{{asset(Storage::url($record->categories_image))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                    <img src="{{asset('front/img/prod-transparent.png')}}" class="transparent" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                </a>
                                
                            </div>
                            <div class="prod-title">
                                <span>{{$subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''}}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach


                    </div>
                </div>
                
              
            </div>
        </div>
    </div>
</section>
@endsection

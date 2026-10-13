@extends('web/layout/main')

<?php
$setting = Helper::getSetting();
$metaTitle = Helper::getSettingById(37)->setting_val;
$metaDescription = Helper::getSettingById(38)->setting_val;
$metaKeyword = Helper::getSettingById(39)->setting_val;
$pageTitle = Helper::getSettingById(23);
$pageTitle = $pageTitle ? $pageTitle->setting_val : "";
?>  
@section('page_title',  $pageTitle)
@section('body_class',  'home')
@section('content')

@section('page_meta')
<meta name="title" content="{{$metaTitle}}">
<meta name="description" content="{{$metaDescription}}">
<meta name="keyword" content="{{$metaKeyword}}">
@endsection


<!--Preloader -- Only for Homepage-->
@if($adminHomeSlider)
<section class="homepage-slider">
    <div class="container"></div>
    <div id="slider" class="horizontal">
        @foreach($adminHomeSlider as $record)
        <?php $data = $record->getSliderDataByLanguage($language->id); ?>
        <div class="panel">
            <div class="slider-content">
                <div class="container">
                    <div class="content">
                        <h1>{{ucfirst($data->banner_header)}}</h1>
                        <a href="{{ucfirst($record->button_link)}}" class="btn btn-secondary">{{ucfirst($data->button_title)}}</a>
                    </div>
                </div>
            </div>
            <div class="slider-image"><img src="{{asset(Storage::url($record->banner_image))}}"></div>
        </div>
        @endforeach
    </div>
</section>
@endif

@if(count($collections) > 0)
<section class="collections">
    <div class="container">
        <div class="collections-frame">
            <h1>{!!__('web.our_category')!!}</h1>
            @foreach($collections as $record)
            <div class="collections-holder">
                <a href="{{$record->link}}" class="collection">
                    <div class="collection-image">
                        <img src="{{asset(Storage::url($record->photo))}}">
                    </div>
                    <div class="collection-content">
                        <h3>{{$record->title}}</h3>
                        <h5>{{$record->description}}</h5>
                    </div>
                </a>
            </div>
            @endforeach

        </div>
    </div>
</section>
@endif
@if(Helper::isExistNewArrivalProduct())
<section class="trending">
    <div class="container">
        <h1>{!! __('web.home_new_arrival') !!}</h1>
        <div class="products-list row" id="trendingProducts">
            @foreach($newArrival as $key => $val)
            <?php
            $i = $key + 1;

            $round_value = 0;
            $value = 0;
            if ($key > 0)
            {
                $value = $i / 2;
                $round_value = (int) round($value);
            }

            $productdetail = $val->getProductDescription($language->id);
            $productDefaultImage = $val->getDefaultImage();
            $getWishlist = '';
            if (Auth::user())
            {

                $getWishlist = \App\AdminModel\Product\Wishlist::where('user_id', Auth::user()->id)->where('product_id', $val->id)->first();
            }
            if ($val->product_quantity <= 0)
            {
                $class = 'sold';
            }
            elseif (!$val->is_product_sale_price_null)
            {
                $class = 'sale';
            }
            else if ($val->is_new_arrival == 1)
            {
                $class = 'new';
            }
            else
            {
                $class = '';
            }
            ?>


            @if($key ==0  ||  $value !=   $round_value) 

            <div class="v-flex">
                @endif
                <div class="product {{$class}}">
                    <div class="product-image">
                        <a href="{{route('web.product.product_detail',['slug'=> $val->products_slug])}}">
                            <img class="image_{{$val->id}}" src="{{asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                        </a>
                        <a href="{{route('web.product.product_detail',['slug'=> $val->products_slug])}}" class="mask"></a>
                        <span class="icon"><i class="icon-wishlist  add-wishlist2 {{$getWishlist ? 'selected':''}}" data-page="product" data-id='{{$val->id}}'></i></span>
                    </div>
                    <div class="prod-title">
                        <span><a href="{{route('web.product.product_detail',['slug'=> $val->products_slug])}}">{{ucfirst($productdetail->products_name)}}</a></span>
                    </div>
                </div>

                @if( $key !=0 &&  $value == $round_value) 

            </div>
            @endif
            @endforeach



        </div>
    </div>
</section>
@else
<section class="trending">
</section>    
@endif  
@endsection       
@section('page_specific_js')
<script src="{{asset('front/js/scroll.js')}}"></script>
@endsection



<div id="preloader"><img src="{{asset('front/img/loader.gif')}}"></div>
<div id="productSlider-container">
    <div id="arrowsContainer"></div>
    <div id="productSlider">




        <!-- color size image  -->
        <?php
        $language = Helper::getLanguage();
        $colorName = $product->getProductColorsName($language->id);
        ?>
        @if($productImage)
        <input type="hidden" class="image_{{$productImage[0]->products_id}}" value="{{asset(Storage::url($productImage ? $productImage[0]->image_thumb_url : ''))}}"> 
        @endif
        @foreach($productImage as $record)

        <div class="panel general {{$record->color_id}} ">
            <div class="product-image cloud-zoom" data-zoom-url="{{asset(Storage::url($record->image_url))}}">
                <img src="{{asset(Storage::url($record->image_url))}}">
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="prod-thumbs-wrapper">
    <div id="prodThumbs">



        @foreach($productImage as $record)

        <div class="thumb-item general {{$record->color_id}} "><a href="javascript:;"><img src="{{asset(Storage::url($record->image_url))}}"></a></div>
        @endforeach

    </div>
</div>
@extends('web/layout/main')
@section('page_title',  trans('web.my_wishlist'))
@section('body_class',  '')
@section('profile_class',  'profile-page')  
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_account')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.wishlist.index')}}">{{__('web.my_wishlist')}}</a></span>
    </div>
</section>
<section class="profile">
    <div class="container-fluid">
        <div class="grey-background">
            <div class="mobile-white-background">
                @include('web/user/profile_menu')
                <div class="tab-content">
                    <div id="wishlist" class="show">
                        <div class="title">
                            <h1>{{__('web.my_wishlist')}}</h1>
                        </div>
                        <div class="row products-list">
                            <input type="hidden" name="monogram_data" id="monogram_data" value="0"/>
                            <?php
                            $wishlistCountProfileMenu = 0;
                            $language = Helper::getLanguage();
                            $currency = Helper::getDefaultCurrency();
                            $currencycode = $currency ? $currency->code : config('custom.currency');
                            $conversationRate = $currency ? $currency->value : 1;
                            ?>
                            @if(count($wishlist)> 0 )   
                            @foreach($wishlist as $record)
                            <?php
                            $product = $record->getProduct();
                             if (!$product)
                            {
                                continue;
                            }
							$productRedirectUrl = route('web.product.product_detail', ['slug' => $product->products_slug]);
                           
                            if ($product && !Helper::isProductVisibleForCurrentUser($product))
                            {
                                continue;
                            }

                            $wishlistCountProfileMenu++;

                            $productdetail = $product->getProductDescription($language->id);
                            $productDefaultImage = $product->getDefaultImage();
                            //$productQuantity     = $product->getProductQuantity($product->id);
                            $productAttributes = $product->getProductAttributes();

                            $sizeId = $record['size_id'];
                            $colorId = $record['color_id'];
                            $color = $product->getProductColors($language->id ,'', $product->have_color, $product->have_size);
                            $sizes = $product->getProductSize($language->id,'', $product->have_color, $product->have_size);
                           


                            $isExistInCart = Helper::getCartProductStatus($user, $record->id, count($sizes) > 0 ? true : false, '', count($color) > 0 ? true : false, '');

                            $productQuantity = Helper::getProductQuantity($product->id, count($sizes) > 0 ? 1 : 0, $sizeId, count($color) > 0 ? 1 : 0, $colorId);

                            $cartQuantity = 1;
                            if ($product->product_quantity <= 0)
                            {
                                $class = 'sold';
                            }
                            elseif (!$product->is_product_sale_price_null)
                            {
                                $class = 'sale';
                            }
                            else if ($product->is_new_arrival == 1)
                            {
                                $class = 'new';
                            }
                            else
                            {
                                $class = '';
                            }
                            ?>
                            <div class="col-md-4 col-xs-6 cart-item-{{$product->id}}">
                                <div class="product {{$class}}">
                                    <div class="product-image">
                                        <a href="{{route('web.product.product_detail',['slug'=> $product->products_slug])}}">
                                            <img class="image_{{$product->id}}" src="{{asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                        </a>
                                        <input type="hidden" name="quantity_{{$product->id}}" value="1">
                                    </div>
                                    <div class="prod-title">
                                        <span>{{ucfirst($productdetail->products_name)}}</span>
                                    </div>
                                    <div class="prod-price">{{$product->is_product_sale_price_null == 0 ? Helper::formatNumber($product->product_sale_price*$conversationRate) : Helper::formatNumber($product->product_regular_price*$conversationRate)}} {{$currencycode}} <span class="striked">{{ $product->is_product_sale_price_null == 0  ? Helper::formatNumber($product->product_regular_price*$conversationRate).' '.$currencycode: '' }}</span></div>
                                    <span class="remove remove_wishlist" data-id="{{$record->product_id}}"><i class="icon-close"></i></span>
                                    <div class="actions">
                                        @if($product->product_quantity == 0)

                                        <button class="btn btn-primary ">{{__('web.sold_out')}}</button>
                                       
                                        @else
                                        @if(Count($color) > 0 || count($sizes)>0 )
                                        <button class="btn btn-primary product_detail" data-url="{{$productRedirectUrl}}">{{__('web.move_to_cart')}}</button>
                                        @elseif($isExistInCart)
                                        <button class="btn btn-primary add_to_cart add-cart-{{$product->id}}" data-page="wishlist" data-size="{{count($sizes)>0?1:0}}"    data-color="{{count($color)>0?1:0}}" data-id="{{$product->id}}">{{__('web.move_to_cart')}} </button>

                                        @else
                                        <button class="btn btn-primary  add_to_cart add-cart-{{$product->id}}" disabled="">{{__('web.added_to_cart')}}</button>

                                        @endif
                                         @endif

                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div >
                                {{__('web.no-record-message')}}  
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_js')
<script>
    $("span.wishlist em").text("{{__('web.my_wishlist')}} ({{$wishlistCountProfileMenu}})");
</script>
@include('web.product.page-ajax')
@endsection
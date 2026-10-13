<?php
$cart = [];
$total = 0;
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$currencycode = $currency ? $currency->code : config('custom.currency');
$user = Auth::user();
?>
<!--if  user login  get product from db-->
<?php
if ($user) {
    $cart = Helper::getShoppingCart($user->id);
} elseif (session('cart')) {


    $cart = Helper::getShoppingCart();
}
?>



<a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
    <i class="icon-cart"></i>
    <em class="cart_count">{{count($cart) > 0 ? count($cart) : 0 }}</em>
</a>
<div class="cart dropdown-menu" id="cartd">
    <div class="cart-bag">
        <div class="title">
            <h4>{{__('web.cart_summary')}}</h4>
        </div>
        <div class="fancy">
            <ul>
                @foreach($cart as $id =>$record)
                <?php
                if ($user) {
                    $id = $record->product_id;
                } else {
                    $id = $record['product_id'];
                }
                $product = Helper::getProductDetail($id);
                if (!$product) {
                    continue;
                }
                $productDetail = $product->getProductDescription($language->id);
                $productImage = $product->getDefaultImage();
                $sizeId = $record['size_id'];
                $colorId = $record['color_id'];
                $size = Helper::getSize($sizeId, $language->id);
                $color = Helper::getColorName($colorId, $language->id);

                $product_regular_price      =  $product->product_regular_price;
                $product_sale_price         = $product->product_sale_price;
                $is_product_sale_price_null = $product->is_product_sale_price_null;

                if ($color || $size) {

                    $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $colorId);

                    if ($attributePrice) {
                        $product_weight = $attributePrice->weight;
                        $product_regular_price = $attributePrice->product_regular_price;
                        $product_sale_price  = $attributePrice->product_sale_price;
                        $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                    }
                }
                $price = $is_product_sale_price_null == 0 ? $product_sale_price : $product_regular_price;
            
                $subTotal = $price * $record['quantity'];

                /*
                 *  Convert Currency Rate
                 */
                if ($currency) {
                   // $subTotal = $subTotal * $currency->value;
                }
                ?>
                <li>
                    <div class="prod-img">
                        <a href="{{route('web.product.product_detail',['slug'=> $product->products_slug])}}">

                            <?php
                            $productImage2 = $product->getVariantThumbnailUrl($record['color_id'], $record['size_id']);
                            ?>
                            <img style="width: 189px; height: 118px;" src="{{asset(Storage::url($productImage2))}}">

                        </a>
                    </div>
                    <div class="prod-detail">
                        <div class="v-flex">
                            <span class="prod-title">{{ucfirst($productDetail->products_name)}} </span>
                            <span class="prod-price">{{Helper::formatNumber($subTotal)}} {{$currencycode}}</span>
                            <span class="prod-qty">{{__('web.quantity')}}: <span>{{$record['quantity']}}</span></span>
                        </div>
                    </div>
                </li>
                @endforeach
                @if(count($cart)==0)
                <p style="text-align: center">{{__('web.no_item')}}</p>
                @endif

            </ul>
        </div>
        @if(count($cart)>0)
        <div class="actions h-flex">
            <button class="btn btn-secondary view_cart">{{__('web.view_cart')}}</button>
            <button class="btn btn-primary place_order">{{__('web.place_order')}}</button>
        </div>
        @endif
    </div>
</div>
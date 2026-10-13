@if(count($products)>0)

<?php



 ?>
<input type="hidden" name="monogram_data" id="monogram_data" value="0"/>
@foreach($products as $record)
<?php
$productdetail = $record->getProductDescription($language->id);
$productDefaultImage = $record->getDefaultImage();
$getWishlist = '';
if (Auth::user())
{
    $getWishlist = $record->getWishlist(Auth::user()->id, $record->id);
}

$user = Auth::user();
$productRedirectUrl = route('web.product.product_detail', ['slug' => $record->products_slug]);
$productAttributes = $record->getProductAttributes();
$color = $record->getProductColors($language->id);
$sizes = $record->getProductSize($language->id);
$cartExistStatus = Helper::getCartProductStatus($user, $record->id, count($sizes) > 0 ? true : false, '', count($color) > 0 ? true : false, '');
$has_qty = true;

if ($record->have_color)
{
    $color = $record->getProductColors($language->id, '', $record->have_color, $record->have_size);
    if (count($color) == 0)
    {
        $has_qty = false;
    }
}

if ($record->have_size)
{

    $sizes = $record->getProductSize($language->id, '', $record->have_color, $record->have_size);
    if (count($sizes) == 0)
    {
        $has_qty = false;
    }
}

if ($record->product_quantity <= 0 || $has_qty == false)
{
    $class = 'sold';
}
elseif (!$record->is_product_sale_price_null)
{
    $class = 'sale';
}
else if ($record->is_new_arrival == 1)
{
    $class = 'new';
}
else
{
    $class = '';
}
/*
 * Convert Currency Rate
 */
// if (isset($currency))
// {
//     $record->product_sale_price = $record->product_sale_price * $currency->value;
//     $record->product_regular_price = $record->product_regular_price * $currency->value;
// }
?>
<div class="col-md-4 col-xs-6">
    <div class="product {{$class}}">
        <div class="product-image">
            <a href="{{$productRedirectUrl}}">
                <input type="hidden" class="image_{{$record->id}}" value="{{asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))}}"> 
                <img src="{{asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                <img src="{{asset('front/img/prod-transparent.png')}}" class="transparent" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
            </a>
            <span class="icon">
                <i class="icon-wishlist add-wishlist {{$getWishlist ? 'selected':''}}" data-id='{{$record->id}}' data-page='product'></i>
            </span>
        </div>
        <div class="prod-title">
            <span>{{$productdetail->products_name}}</span>
        </div>
        <div class="prod-price">{{$record->is_product_sale_price_null == 0? Helper::formatNumber($record->product_sale_price) : Helper::formatNumber($record->product_regular_price)}} {{$currencyCode}} <span class="striked">{{ !$record->is_product_sale_price_null ? Helper::formatNumber($record->product_regular_price).' '.$currencyCode : '' }} </span></div>
        <div class="prod-actions">
            @if($class == 'sold' || $has_qty ==false)
            <span class="btn btn-primary product_detail " data-url="{{$productRedirectUrl}}">{{__('web.sold_out')}} </span>
            @else
            @if(Count($color) > 0 || count($sizes)>0 )
            <span class="btn btn-primary product_detail" data-url="{{$productRedirectUrl}}">{{__('web.add_to_cart')}}</span>
            @else
            @if(!$cartExistStatus)
            <span disabled="" class="btn btn-primary product_detail" data-url="{{$productRedirectUrl}}">{{__('web.added_to_cart')}}</span>
            @else
            <input type="hidden" name="quantity_{{$record->id}}" value="1" class="quantity quantity-value">
            <span  class="btn btn-primary add_to_cart add-cart-{{$record->id}}" data-page="detail" data-id="{{$record->id}}" data-size="0"  data-color="0">{{__('web.add_to_cart')}}</span>
            @endif
            @endif
            @endif
        </div>
    </div>
</div>
@endforeach
@else
<div class="noresults">
    <span class="icon"></span>
    <h1>No Results Found</h1>
    <h2>Try using a more general search term</h2>
</div>
@endif

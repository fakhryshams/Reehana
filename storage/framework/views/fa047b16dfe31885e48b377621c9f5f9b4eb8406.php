<?php if(count($products)>0): ?>

<?php



 ?>
<input type="hidden" name="monogram_data" id="monogram_data" value="0"/>
<?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
    <div class="product <?php echo e($class); ?>">
        <div class="product-image">
            <a href="<?php echo e($productRedirectUrl); ?>">
                <input type="hidden" class="image_<?php echo e($record->id); ?>" value="<?php echo e(asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))); ?>"> 
                <img src="<?php echo e(asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                <img src="<?php echo e(asset('front/img/prod-transparent.png')); ?>" class="transparent" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
            </a>
            <span class="icon">
                <i class="icon-wishlist add-wishlist <?php echo e($getWishlist ? 'selected':''); ?>" data-id='<?php echo e($record->id); ?>' data-page='product'></i>
            </span>
        </div>
        <div class="prod-title">
            <span><?php echo e($productdetail->products_name); ?></span>
        </div>
        <div class="prod-price"><?php echo e($record->is_product_sale_price_null == 0? Helper::formatNumber($record->product_sale_price) : Helper::formatNumber($record->product_regular_price)); ?> <?php echo e($currencyCode); ?> <span class="striked"><?php echo e(!$record->is_product_sale_price_null ? Helper::formatNumber($record->product_regular_price).' '.$currencyCode : ''); ?> </span></div>
        <div class="prod-actions">
            <?php if($class == 'sold' || $has_qty ==false): ?>
            <span class="btn btn-primary product_detail " data-url="<?php echo e($productRedirectUrl); ?>"><?php echo e(__('web.sold_out')); ?> </span>
            <?php else: ?>
            <?php if(Count($color) > 0 || count($sizes)>0 ): ?>
            <span class="btn btn-primary product_detail" data-url="<?php echo e($productRedirectUrl); ?>"><?php echo e(__('web.add_to_cart')); ?></span>
            <?php else: ?>
            <?php if(!$cartExistStatus): ?>
            <span disabled="" class="btn btn-primary product_detail" data-url="<?php echo e($productRedirectUrl); ?>"><?php echo e(__('web.added_to_cart')); ?></span>
            <?php else: ?>
            <input type="hidden" name="quantity_<?php echo e($record->id); ?>" value="1" class="quantity quantity-value">
            <span  class="btn btn-primary add_to_cart add-cart-<?php echo e($record->id); ?>" data-page="detail" data-id="<?php echo e($record->id); ?>" data-size="0"  data-color="0"><?php echo e(__('web.add_to_cart')); ?></span>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
<div class="noresults">
    <span class="icon"></span>
    <h1>No Results Found</h1>
    <h2>Try using a more general search term</h2>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/products.blade.php ENDPATH**/ ?>
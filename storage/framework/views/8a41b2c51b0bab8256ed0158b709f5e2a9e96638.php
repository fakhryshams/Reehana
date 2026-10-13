<?php if(count($cartProduct) > 0): ?>

<?php
if (count($cartProduct['monogram']) > 0)
{
    $monocount = $cartProduct['monogram'];
    end($monocount);         // move the internal pointer to the end of the array
    $lastkey = key($monocount);
}
else
{
    $lastkey = 1;
}

$monogramPrice = \App\Helpers\Helper::getMonogramPrice();
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$color_id = $cartProduct['color_id'];
$sizeId = $cartProduct['size_id'];
$productId = $cartProduct['product_id'];
?>
<div class="monogramSummary monogramSummary-<?php echo e($id); ?>" data-total="<?php echo e($lastkey); ?>">
    <?php $__currentLoopData = $cartProduct['monogram']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>  $monogramVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
    $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
    $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
    $colors = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
    $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
    $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);

    $user = Auth::user();
    ?>                                
    <div class="monogram-information  monogram-information-<?php echo e($id); ?>   " id="monogramSummary-<?php echo e($id); ?>">
        <div class="h-flex">
            <div class="h-flex">
                <label>Selected Monogram</label>
                <span class="monogram-price nowrap"> <?php echo e($monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''); ?></span>
            </div>
            <span class="link-button edit_monogram2" data-productId="<?php echo e($productId); ?>" data-id="<?php echo e($id); ?>" data-edit-id="<?php echo e($user ? $monogramVal['id'] : ''); ?>"   data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-color="<?php echo e($color_id ? $color_id : 'c'); ?>"  data-size="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($key); ?>">Edit</span>
            <span class="link-button removeMonogram2" data-productid="<?php echo e($productId); ?>" data-id="<?php echo e($id); ?>"  data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-colorId="<?php echo e($color_id ? $color_id : 'c'); ?>"  data-sizeId="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($monogramVal['id']); ?>">Remove</span>
        </div>
        <div class="border-panel">


            <input type="hidden" name="product_cart_id_<?php echo e($id); ?>_<?php echo e($key); ?>" value="<?php echo e($monogramVal['id']); ?>">

            <div class="h-flex">
                <div class="monogram-options">
                    <label>Style</label>
                    <div class="info " id="styleInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><img src="<?php echo e(asset(Storage::url($font ? $font->style_image_svg : ''))); ?>"></div>
                </div>
                <div class="monogram-options">
                    <label>Initials</label>
                    <div class="info" id="alphabetsInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><?php echo e($initials? $initials : ''); ?></div>
                </div>
                <div class="monogram-options">
                    <label>Color</label>
                    <div class="info" id="colorInfo_<?php echo e($id); ?>_<?php echo e($key); ?>">
                        <span class="color-plate" style="background: <?php echo e($colors? $colors->color_hash : ''); ?>;"></span>
                        <?php echo e($colors?$colors->color_name : ''); ?>

                    </div>
                </div>
                <div class="monogram-options">
                    <label>Placement</label>
                    <div class="info"  id="placementInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><?php echo e($placement ?$placement->placement_desc:''); ?></div>
                </div>
            </div>

            <div class="separator underline underline_<?php echo e($id); ?>_<?php echo e($key); ?> <?php echo e($monogramVal['monogram_note'] ? 'show' : 'hide'); ?>"></div>
            <div class="comment monogramComment monogramComment_<?php echo e($id); ?>_<?php echo e($key); ?>  <?php echo e($monogramVal['monogram_note'] ? 'show' : 'hide'); ?>">
                <p>Comment:<?php echo e($monogramVal['monogram_note'] ?  $monogramVal['monogram_note'] : ''); ?></p>
            </div>
        </div>


    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/monogram_list.blade.php ENDPATH**/ ?>
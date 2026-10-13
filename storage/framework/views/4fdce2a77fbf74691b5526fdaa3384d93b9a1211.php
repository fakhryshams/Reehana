<?php if(!$productQuantity): ?>
<option value="0" selected="selected" disabled=""><?php echo e(__('web.select')); ?></option>
<?php endif; ?>
<?php for($quantityLoop=1;$quantityLoop<=$productQuantity;$quantityLoop++): ?>
<option value="<?php echo e($quantityLoop); ?>" <?php echo e($cartQuantity==$quantityLoop?'selected="selected"':''); ?>><?php echo e($quantityLoop); ?></option>
<?php endfor; ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/product-quantity.blade.php ENDPATH**/ ?>
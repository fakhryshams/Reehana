<?php if(!$productSize): ?>
<option value="0" selected="selected" disabled=""><?php echo e(__('web.select')); ?></option>
<?php endif; ?>
<?php $__currentLoopData = $productSize; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
$sizeDesc = $val->getSizeDescription();
?>
<option value="<?php echo e($val->id); ?>" <?php echo e($key == 0 ? 'selected':''); ?> ><?php echo e($sizeDesc->size_title); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/product-size.blade.php ENDPATH**/ ?>
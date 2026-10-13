<div id="preloader"><img src="<?php echo e(asset('front/img/loader.gif')); ?>"></div>
<div id="productSlider-container">
    <div id="arrowsContainer"></div>
    <div id="productSlider">




        <!-- color size image  -->
        <?php
        $language = Helper::getLanguage();
        $colorName = $product->getProductColorsName($language->id);
        ?>
        
        <?php $__currentLoopData = $productImage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="panel general <?php echo e($record->color_id); ?>">
            <?php if($loop->first): ?>
                <input type="hidden" class="image_<?php echo e($record?->products_id); ?>" value="<?php echo e(asset(Storage::url($record?->image_thumb_url))); ?>"> 
            <?php endif; ?>
            <div class="product-image cloud-zoom" data-zoom-url="<?php echo e(asset(Storage::url($record->image_url))); ?>">
                <img src="<?php echo e(asset(Storage::url($record->image_url))); ?>">
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div id="arrowsContainerMobile"></div>
	<div id="productSliderMobile">
	     <?php $__currentLoopData = $productImage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<div class="panel general">
							   
								<a data-fancybox="gallery" class="product-image" href="<?php echo e(asset(Storage::url($record->image_url))); ?>" >
								 
									<img src="<?php echo e(asset(Storage::url($record->image_url))); ?>">
								 
								</a>
							
		                    </div>
		  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							
	</div>
    
    
    
</div>
<div class="prod-thumbs-wrapper">
    <div id="prodThumbs">



        <?php $__currentLoopData = $productImage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <div class="thumb-item general <?php echo e($record->color_id); ?> "><a href="javascript:;"><img src="<?php echo e(asset(Storage::url($record->image_url))); ?>"></a></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/product-image.blade.php ENDPATH**/ ?>
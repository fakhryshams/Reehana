

<?php
$setting = Helper::getSetting();
$metaTitle = Helper::getSettingById(37)->setting_val;
$metaDescription = Helper::getSettingById(38)->setting_val;
$metaKeyword = Helper::getSettingById(39)->setting_val;
$pageTitle = Helper::getSettingById(23);
$pageTitle = $pageTitle ? $pageTitle->setting_val : "";
?>  
<?php $__env->startSection('page_title',  $pageTitle); ?>
<?php $__env->startSection('body_class',  'home'); ?>
<?php $__env->startSection('content'); ?>

<?php $__env->startSection('page_meta'); ?>
<meta name="title" content="<?php echo e($metaTitle); ?>">
<meta name="description" content="<?php echo e($metaDescription); ?>">
<meta name="keyword" content="<?php echo e($metaKeyword); ?>">
<?php $__env->stopSection(); ?>


<!--Preloader -- Only for Homepage-->
<?php if($adminHomeSlider): ?>
<section class="homepage-slider">
    <div class="container"></div>
    <div id="slider" class="horizontal">
        <?php $__currentLoopData = $adminHomeSlider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $data = $record->getSliderDataByLanguage($language->id); ?>
        <div class="panel">
            <div class="slider-content">
                <div class="container">
                    <div class="content">
                        <h1><?php echo e(ucfirst($data->banner_header)); ?></h1>
                        <a href="<?php echo e(ucfirst($record->button_link)); ?>" class="btn btn-secondary"><?php echo e(ucfirst($data->button_title)); ?></a>
                    </div>
                </div>
            </div>
            <div class="slider-image"><img src="<?php echo e(asset(Storage::url($record->banner_image))); ?>"></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php endif; ?>

<?php if(count($collections) > 0): ?>
<section class="collections">
    <div class="container">
        <div class="collections-frame">
            <h1><?php echo __('web.our_category'); ?></h1>
            <?php $__currentLoopData = $collections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="collections-holder">
                <a href="<?php echo e($record->link); ?>" class="collection">
                    <div class="collection-image">
                        <img src="<?php echo e(asset(Storage::url($record->photo))); ?>">
                    </div>
                    <div class="collection-content">
                        <h3><?php echo e($record->title); ?></h3>
                        <h5><?php echo e($record->description); ?></h5>
                    </div>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>
</section>
<?php endif; ?>
<?php if(Helper::isExistNewArrivalProduct()): ?>
<section class="trending">
    <div class="container">
        <h1><?php echo __('web.home_new_arrival'); ?></h1>
        <div class="products-list row" id="trendingProducts">
            <?php $__currentLoopData = $newArrival; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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


            <?php if($key ==0  ||  $value !=   $round_value): ?> 

            <div class="v-flex">
                <?php endif; ?>
                <div class="product <?php echo e($class); ?>">
                    <div class="product-image">
                        <a href="<?php echo e(route('web.product.product_detail',['slug'=> $val->products_slug])); ?>">
                            <img class="image_<?php echo e($val->id); ?>" src="<?php echo e(asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png'))); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                        </a>
                        <a href="<?php echo e(route('web.product.product_detail',['slug'=> $val->products_slug])); ?>" class="mask"></a>
                        <span class="icon"><i class="icon-wishlist  add-wishlist2 <?php echo e($getWishlist ? 'selected':''); ?>" data-page="product" data-id='<?php echo e($val->id); ?>'></i></span>
                    </div>
                    <div class="prod-title">
                        <span><a href="<?php echo e(route('web.product.product_detail',['slug'=> $val->products_slug])); ?>"><?php echo e(ucfirst($productdetail->products_name)); ?></a></span>
                    </div>
                </div>

                <?php if( $key !=0 &&  $value == $round_value): ?> 

            </div>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



        </div>
    </div>
</section>
<?php else: ?>
<section class="trending">
</section>    
<?php endif; ?>  
<?php $__env->stopSection(); ?>       
<?php $__env->startSection('page_specific_js'); ?>
<script src="<?php echo e(asset('front/js/scroll.js')); ?>"></script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/home/index.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html>
    <?php echo $__env->make('web/layout/head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <body class="<?php echo $__env->yieldContent('body_class'); ?>">

        <?php if(!request()->routeIs('web.product.product_detail')): ?>
        <div id="preloader"  >
            <img src="<?php echo e(asset('front/img/loader.gif')); ?>">Loading...
        </div>
        <?php endif; ?>
        <div class="wrapper <?php echo $__env->yieldContent('profile_class'); ?>">
            <!--Menu-->
            <?php echo $__env->make('web/layout/menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <!--Main Content-->
            <?php echo $__env->yieldContent('content'); ?>

            <!--Footer-->
            <?php echo $__env->make('web/layout/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!--Modal Ex: Forgot Password-->
        <?php echo $__env->yieldContent('page_specific_html'); ?>
        <!--Header-->
        <?php echo $__env->make('web/layout/responsive_menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('web/layout/monogram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!--Global Back to Top Button-->
        <?php echo $__env->make('web/layout/back_to_top', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!--Footer Scripts-->
        <?php echo $__env->make('web/layout/footer_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </body>
</html><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/main.blade.php ENDPATH**/ ?>
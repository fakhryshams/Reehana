
<?php $__env->startSection('page_title',  trans('web.404_title')); ?>
<?php $__env->startSection('body_class',  'menubg'); ?>
<?php $__env->startSection('404_class',  'p404'); ?>
<?php $__env->startSection('content'); ?>
<div class="pg404">
    <div class="container">
        <div class="grey-background">
            <div class="h-flex">
                <h1>404</h1>
                <h2>Error</h2>
            </div>
            <p>Oops! Page not found.</p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/errors/web/404.blade.php ENDPATH**/ ?>
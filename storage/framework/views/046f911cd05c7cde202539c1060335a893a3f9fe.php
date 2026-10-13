<?php if(session()->has('success')): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <!--<h4><i class="icon fa fa-check"></i> Alert!</h4>-->
    <i class="icon fa fa-check"></i> <?php echo e(session()->get('success')); ?>

</div>
<?php endif; ?>

<?php if(session()->has('error')): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <!--<h4><i class="icon fa fa-ban"></i> Alert!</h4>-->
    <i class="icon fa fa-ban"></i><?php echo e(session()->get('error')); ?>

</div>
<?php endif; ?>
<?php if(isset($status) && !empty($status) && $status=='success'): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <!--<h4><i class="icon fa fa-check"></i> Alert!</h4>-->
    <i class="icon fa fa-check"></i><?php echo e($message_text); ?>

</div>
<?php endif; ?>

<?php if(isset($status) && !empty($status) && $status=='error'): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <!--<h4><i class="icon fa fa-ban"></i> Alert!</h4>-->
    <i class="icon fa fa-ban"></i><?php echo e($message_text); ?>

</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/layout/toaster.blade.php ENDPATH**/ ?>
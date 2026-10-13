<div class="float-left float-sm-left">
    <div class="input-group input-group-sm">
        <select class="form-control float-right float-sm-right" name="pageLength">
            <?php $__currentLoopData = Helper::paginationDropdown(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pageNumber): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option <?php echo e($pageLength==$pageNumber?'selected':''); ?>><?php echo e($pageNumber); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/common/paginate.blade.php ENDPATH**/ ?>
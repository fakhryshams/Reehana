<style>
    p{
        margin-bottom: 0px;
    }
</style>
<?php
$setting       = Helper::getSettingById(21,1);
$footer   = isset($setting) ? trim($setting->setting_val) : "";
$company       = Helper::getSettingById(22,1);
$company   = isset($company) ? trim($company->setting_val) : "";
?>
<footer class="main-footer">
    <strong><span>&copy; <?php echo e(__('web.copyright')); ?> <?php echo date("Y") ?> <?php echo e(Helper::getAppName()); ?></span>
    </strong> <strong class="float-right"><span class="no-print"> <?php echo $company; ?></span></strong>
</footer><?php /**PATH C:\xampp\htdocs\reehana\resources\views/layout/footer.blade.php ENDPATH**/ ?>
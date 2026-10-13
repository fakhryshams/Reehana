<div class="footer">
    <div class="footer-container">
        <div class="line line1"></div>
        <div class="line line2"></div>
        <div class="line line3"></div>
        <div class="footer-logo logo">
                <!-- <span>R</span>
                <span class="E">
                        <span></span>
                        <span></span>
                        <span></span>
                </span>
                <span>H</span>
                <span>A</span>
                <span>N</span>
                <span>A</span> -->
            <span class="logoR"></span>
            <span class="E">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="logoH"></span>
            <span class="logoA"></span>
            <span class="logoN"></span>
            <span class="logoA"></span>
        </div>
        <div class="links">
            <ul class="footer-links">
                <li><a href="<?php echo e(route('web.privacy-policy')); ?>"><?php echo e(__('web.privacy-policy')); ?></a></li>
                <li><a href="<?php echo e(route('web.shipping')); ?>"><?php echo e(__('web.shipping')); ?></a></li>
                <li><a href="<?php echo e(route('web.about')); ?>"><?php echo e(__('web.about_us')); ?></a></li>
                <li><a href="<?php echo e(route('web.contact')); ?>"><?php echo e(__('web.contact_us')); ?></a></li>
            </ul>
        </div>
        <div class="social-links">
            <ul class="share">
                <?php if(Helper::getInstagramUrl()): ?>
                <li><a target="_blank" href="<?php echo e(Helper::getInstagramUrl()); ?>"><span class="instagram"></span></a></li>
                <?php endif; ?>
                <?php if(Helper::getInstagramUrl2()): ?>
                <li><a target="_blank" href="<?php echo e(Helper::getInstagramUrl2()); ?>"><span class="instagram"></span></a></li>
                <?php endif; ?>
                <?php if(Helper::getWhatsappUrl()): ?>
                <li><a target="_blank"  href="<?php echo e(Helper::getWhatsappUrl()); ?>"><span class="whatsapp"></span></a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="copyright">
            <span><?php echo Helper::getWebFooterCompanyName(); ?></span>
            <!-- <span class="gap"></span>
            <span>Designed &amp; Developed by <a href="http://www.uno-digital.com/" target="_blank">UNO Digital</a></span> -->
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/footer.blade.php ENDPATH**/ ?>
<div class="coupons">
    <h4><?php echo e(__('web.coupon_code')); ?></h4>
    <div class="coupon-input">
        <input class="coupon_code coupon_code_web" type="text" placeholder=""
               value="<?php
               if (isset($appliedCoupon))
                   echo isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';
               ?>">
        <button type="button" class="btn btn-primary apply coupon_apply coupon_apply_web"><?php echo e(__('web.coupon_apply')); ?></button>
    </div>
    <span class="danger cc-msg-fail" style="display: none"><i class="icon-close"></i><?php echo e(__('web.coupon_invalid')); ?></span>

    <?php if(isset($appliedCoupon)  && count($appliedCoupon) > 0): ?>
    <span class="cc-msg-success success"><i class="icon-tick"></i><?php echo e(__('web.coupon_applied')); ?></span>
    <?php else: ?>
    <span class="cc-msg-success success" style="display: none"><i class="icon-tick"></i><?php echo e(__('web.coupon_applied')); ?></span>
    <?php endif; ?>
    <?php if(false): ?>
    <span class="danger"><?php echo e(__('web.coupon_invalid')); ?></span>
    <span class="danger"><?php echo e(__('web.coupon_already_redeemed')); ?></span>
    <span class="success"><?php echo e(__('web.coupon_applied')); ?> <a href="#"><i class="fa fa-times"></i></a></span>
    <?php endif; ?>
   <!--<span class="success"><i class="icon-tick"></i>Gift Coupon applied</span>-->
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/checkout/coupon_card.blade.php ENDPATH**/ ?>
<div id="resetPwdPopup" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h4><?php echo e(__('web.forgot_passsword')); ?></h4>
                <p><?php echo e(__('web.type_in_your_email')); ?></p>
                <form id="reset-password2" action="<?php echo e(route('web.user.forgot_password')); ?>"  method="post">
                    <?php echo csrf_field(); ?>
                    <div class="form-item">
                        <input type="email" data-type="email" id="email" name="femail"  placeholder="<?php echo e(__('web.email')); ?>">
                        <i class="error"></i>
                        <span class="text-red email_error" style="display: block;width: 100%;text-align:left"></span>
                        <span class="text-green email_suceess" style="display: block;width: 100%;text-align:left"></span>
                     <!--<p class="success">A password reset link is successfully sent to your registered email ID.</p>-->
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-primary resetBtn2"><?php echo e(__('web.forgot_passsword')); ?></button>
                    </div>
                </form>
                <div class="separator"></div>
                <h5><?php echo e(__('web.dont_have_account')); ?>?</h5>
                <div class="actions">
                    <a href="<?php echo e(route('web.user.register')); ?>"><button class="btn btn-primary registerBtn"><?php echo e(__('web.register_now')); ?></button></a>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/user/forgot_password.blade.php ENDPATH**/ ?>
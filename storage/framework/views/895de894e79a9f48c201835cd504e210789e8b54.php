
<?php $__env->startSection('page_title',  trans('web.login')); ?>
<?php $__env->startSection('body_class',  'menubg'); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startSection('profile_class',  'login-page'); ?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="<?php echo e(route('web.index')); ?>"><?php echo e(__('web.home')); ?></a></span>
        <span class="sep"></span>
        <span><a href="<?php echo e(route('web.user.login')); ?>"><?php echo e(__('web.sign_in')); ?></a></span>
    </div>
</section>

<section class="login">
    <div class="container">
        <div class="h-flex grey-background">
            <div class="login-form user-login white-background">
                <div class="title"><h4><?php echo e(__('web.login_to_your_account')); ?></h4></div>
                
                    <div class="form-section row">
                   <form id="user-login" action="<?php echo e(route('web.user.login')); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="url" value="<?php echo e($url); ?>">
                        <div class="form-item col-sm-12">
                            <input type="email" data-type="email" id="uEmail" name="email" class="required" placeholder="<?php echo e(__('web.email')); ?>" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div> 
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-sm-12">
                            <input type="password" data-type="password" id="upassword" name="password" class="required" placeholder="<?php echo e(__('web.password')); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div> 
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <p class="error login_email_error">
                         

                        </p>
                        <div class="v-flex col-sm-12">
                            <div class="h-flex form-item">
                                <div class="checkbox flex1">
                                    <input type="checkbox" name="remember">
                                    <label><?php echo e(__('web.remember_me')); ?></label>
                                </div>
                                <div class="forgot">
                                    <span class="link-button" id="resetPassword" data-toggle="modal" data-target="#resetPwdPopup"><?php echo e(__('web.forgot_passsword')); ?>?</span>
                                </div>
                            </div>
                            <div class="action">
                                <button  type="submit" class="btn btn-primary"><?php echo e(__('web.login')); ?></button>
                            </div>
                            <div class="text-center mTop20">
                                OR
                            </div>
                        </div>
                        </form>
                        <div class="h-flex form-item actions col-sm-12 mTop20">
                            <button class="btn btn-secondary fb  fb-btn"><i class="facebook"></i> <?php echo e(__('web.login_with_facebook')); ?></button>
                            <button class="btn btn-secondary gl google-btn"><i class="google"></i> <?php echo e(__('web.login_with_google')); ?></button>
                        </div>
                    </div>
                
                <div class="separator"></div>
                <div class="text-center create-account">
                    <?php echo e(__('web.dont_have_account')); ?> <a href="<?php echo e(route('web.user.register')); ?>" class="link-button"><?php echo __('web.signup_now'); ?>!</a>
                </div>
            </div>
            <!--if(session('cart'))-->
            <div class="guest grey-background">
                <div class="login-form">
                    <div class="title"><h4><?php echo e(__('web.guest_checkout')); ?></h4></div>
                    <form id="guest-checkout" method="post"  action="<?php echo e(route('web.user.guest_login')); ?>">
                        <?php echo csrf_field(); ?>
                        <p><?php echo e(__('web.in_a_hurry')); ?>:</p>
                        <div class="form-section row">
                            <div class="form-item col-sm-12">
                                <input type="email" data-type="email" id="gEmail" name="gEmail" class="required" placeholder="<?php echo e(__('web.email')); ?>*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                                <i class="error"></i>
                                <?php $__errorArgs = ['gEmail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-red"> <?php echo e($message); ?></div> 
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="btn btn-primary"><?php echo e(__('web.continue')); ?></button>
                    </form>
                </div>
            </div>
            <!--endif-->
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_specific_html'); ?>
<?php echo $__env->make('web/user/forgot_password', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/user/login.blade.php ENDPATH**/ ?>
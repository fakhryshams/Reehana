
      
<section class="menu">
    <nav class="navbar navbar-dropdown navbar-fixed-top navbar-expand-lg navbar-toggleable-md scrolling-navbar double-nav">
        <div class="navbar-collapse container-fluid">
            <button class="navbar-toggler" type="button">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <div class="navbar-brand">
                <span class="navbar-caption-wrap logo">
                    <a href="<?php echo e(route('web.index')); ?>">
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
                    </a>
                </span>
            </div>
            <div class="gap"></div>
            <div class="right-menu">
                <ul class="quick-menu">
                    <?php if(isset(auth()->user()->user_type) && auth()->user()->user_type == "physical_store_user"): ?>
                    <li class="nav-icon sm-hidden"><a href="<?php echo e(route('web.cart.index')); ?>"><i class="icon-custom-order"></i></a></li>
                    <?php endif; ?>
                    <li class="nav-icon dropdown user_cart">
                        <?php echo $__env->make('web.product.cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        
                    </li>
                    <li class="nav-icon sm-hidden"><a href="<?php echo e(route('web.wishlist.index')); ?>"><i class="icon-wishlist"></i></a></li>
                    <li class="nav-icon <?php echo e(Auth::user() ? 'dropdown' : ''); ?>">
                        <?php if(Auth::user()): ?>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                            <i class="icon-user"></i>
                            <i class="icon-down"></i>
                        </a>
                        <div id="user-menu" class="user-menu dropdown-menu">
                            <ul>
                                <li><a href="<?php echo e(route('web.user.my_order')); ?>"><?php echo e(__('web.my_orders')); ?></a></li>
                                <li><a href="<?php echo e(route('web.wishlist.index')); ?>"><?php echo e(__('web.my_wishlist')); ?></a></li>
                                <li class="separator"></li>
                                <li><a href="<?php echo e(route('web.user.my_address')); ?>"><?php echo e(__('web.my_addresses')); ?></a></li>
                                <li><a href="<?php echo e(route('web.user.profile')); ?>"><?php echo e(__('web.my_profile')); ?></a></li>
                                <li><a href="<?php echo e(route('web.user.change_password')); ?>"><?php echo e(__('web.change_password')); ?></a></li>
                                <li><a href="<?php echo e(route('web.user.logout')); ?>"><?php echo e(__('web.logout')); ?></a></li>
                            </ul>
                        </div>
                        <?php else: ?>
                        <a href="<?php echo e(route('web.user.login')); ?>">
                            <i class="icon-user"></i>
                        </a>
                        <?php endif; ?>
                    </li>

                    <?php
                    $countryList = Helper::getCountryList();
                    $selectedCountry = Helper::getCurrency();
                    ?>
                    <?php if($countryList): ?>
                    <li class="nav-icon">
                        <select class="select-currency">
                            <?php $__currentLoopData = $countryList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($country->id); ?>" <?php echo e($country->currency_code == $selectedCountry->currency_code?'selected':''); ?>            data-href="<?php echo e(route('web.change_currency',['id'=>$country->id])); ?>"><?php echo e($country->currency_code); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="toast" id="itemAdd">
                    <div class="h-flex">
                        <img class="cart_image" src="">
                        <span>Added to Cart</span>
                    </div>
                </div>
                <div class="toast" id="itemRem">
                    <div class="h-flex">
                        <img class="cart_remove_image"  src="../img/item-01.png">
                        <span>Removed from Cart</span>
                    </div>
                </div>
                <div class="toast" id="favAdd">
                    <div class="h-flex">
                        <img class="wishlist_image" src="">
                        <span>Added to Wishlist</span>
                    </div>
                </div>
                <div class="toast" id="favRem">
                    <div class="h-flex">
                        <img class="remove_wishlist_image" src="">
                        <span>Removed from Wishlist</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</section><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/menu.blade.php ENDPATH**/ ?>
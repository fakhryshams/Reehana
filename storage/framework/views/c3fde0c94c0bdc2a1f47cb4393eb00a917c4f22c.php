<div id="menu-handheld" class="fancy">
    <div class="menu-container">
        <div class="search" id="search">
            <div class="searchContainer">
                <form method="get" action="<?php echo e(route('web.product.index')); ?>" id="search-form">
                    <input type="submit" value="">
                    <input type="text" name="search" placeholder="<?php echo e(__('web.search')); ?>" id="search_bind" required="" value="<?php echo e(Request::get('search')); ?>">
                    <span id="clear" class="<?php echo e(Request::get('search') ? '' : 'hide'); ?>"><i class="icon-close"></i></span>
                </form>
            </div>
        </div>
        <ul class="navbar-nav nav-dropdown nav-right quick-menu" data-app-modern-menu="true">
            <li class="nav-item">
                <a class="nav-link link" href="<?php echo e(route("web.about")); ?>"><span><?php echo e(__('web.about_us')); ?></span></a>
            </li>
            <?php if(Helper::isExistNewArrivalProduct()): ?>
            <li class="nav-item">
                <a class="nav-link link" href="<?php echo e(route("web.product.new_arrivals")); ?>"><span><?php echo e(__('web.new_arrivals')); ?></span></a>
            </li>
            <?php endif; ?>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link link dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                    <span><?php echo e(__('web.collections')); ?></span>
                    <i class="icon-up expanded"></i>
                    <i class="icon-down collapsed"></i>
                </a>
                <div class="submenu dropdown-menu">
                    <div class="v-flex">
                        <ul>
                            <?php echo $__env->make('web/layout/menu_list', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </ul>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link link dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                    <span><?php echo e(__('web.brands')); ?></span>
                    <i class="icon-up expanded"></i>
                    <i class="icon-down collapsed"></i>
                </a>
                <div class="submenu dropdown-menu">
                    <div class="v-flex">
                        <ul>
                            <?php
                            $language = Helper::getLanguage();
                            $brand = Helper::getBrand();
                            ?>
                            <?php $__currentLoopData = $brand; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                            $brand_desc = $record->getBrandDescription($language->id);
                            ?>
                            <li><a href="<?php echo e(route('web.product.shop_by_brand', (['b[]'=>$brand_desc ? $brand_desc->brand_name : '']))); ?>"><span><?php echo e($brand_desc ? $brand_desc->brand_name : ''); ?></span></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php if(Helper::isExistSaleProduct() ): ?>
            <li class="nav-item">
                <a class="nav-link link" href="<?php echo e(route("web.product.sale")); ?>"><span><?php echo e(__('web.sale')); ?></span></a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link link" href="<?php echo e(route("web.contact")); ?>"><span><?php echo e(__('web.contact_us')); ?></span></a>
            </li>
        </ul>
    </div>
</div>
<div id="sidenav-overlay"></div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/responsive_menu.blade.php ENDPATH**/ ?>
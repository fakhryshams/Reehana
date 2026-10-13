<aside class="main-sidebar sidebar-dark-orange elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route("admin.home.index")); ?>" class="brand-link navbar-orange">
        <img src="<?php echo e(Helper::getAppLogo()?Helper::getAppLogo(): ''); ?>" alt="<?php echo e(__('admin.logo')); ?>" class="brand-image elevation-3" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
        <span class="brand-text">&nbsp;</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!--Super Admin-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_super_admin'))): ?>
                <li class="nav-header"><?php echo e(__('admin.menu_super_admin')); ?></li>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('super-admin.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('super-admin.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p><?php echo e(__('admin.menu_authorization')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_super_admin_role'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('super-admin.role.index')); ?>" class="nav-link <?php echo e(request()->routeIs('super-admin.role.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_role')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_super_admin_permission'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('super-admin.permission.index')); ?>" class="nav-link <?php echo e(request()->routeIs('super-admin.permission*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_permission')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_super_admin_role_permission'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('super-admin.role-permission.index')); ?>" class="nav-link <?php echo e(request()->routeIs('super-admin.role-permission*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_role_permission')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_super_admin_user_role'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('super-admin.user-role.index')); ?>" class="nav-link <?php echo e(request()->routeIs('super-admin.user-role*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_user_role')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Super Admin-->
                <!--Admin-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_admin'))): ?>
                <!--<li class="nav-header"><?php echo e(__('admin.admin')); ?></li>-->
                <!--Dashboard-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_dashboard'))): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.home.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.home.index') ?'active':''); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p><?php echo e(__('admin.menu_dashboard')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <!--/.Dashboard-->
                <!--Customer-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_customer'))): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.customer.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.customer.*') ?'active':''); ?>">
                        <i class="fas fa-users nav-icon"></i>
                        <p><?php echo e(__('admin.menu_customer')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <!--/.Customer-->
                <!--User Management-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_user_management'))): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.user-management.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.user-management.*') ?'active':''); ?>">
                        <i class="fas fa-user nav-icon"></i>
                        <p><?php echo e(__('admin.user-management')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <!--/.User Management-->
                <!--Orders-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.order.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.order.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p><?php echo e(__('admin.menu_order')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_order'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.index') || request()->routeIs('admin.order.vieworder') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_order')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_order_status'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.status.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.status.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_order_status')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_wish_list'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.wishlist.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.wishlist.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.wish-list')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_order'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.order-not-complete')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.order-not-complete.*') || request()->routeIs('admin.order.order-not-complete') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.order-not-complete')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_customized_order_list'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.customized-order')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.customized-order') || request()->routeIs('admin.order.customized-order.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.customized-order')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_customized_order_list'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.customized-pending-order')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.customized-pending-order') || request()->routeIs('admin.order.customized-pending-order.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.customized-pending-order')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_customized_order_list'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.customized-quotation-to-po')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.customized-quotation-to-po') || request()->routeIs('admin.order.customized-quotation-to-po.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.customized-quotation-to-po')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_order_customized_order_list'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.order.temporary-orders')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.order.temporary-orders') || request()->routeIs('admin.order.temporary-orders.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.temporary-orders')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Orders-->
                <!--Report-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.report.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.report.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p><?php echo e(__('admin.menu_report')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_product_liked'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.product-like')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.product-like') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_product_liked')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_sales_date'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.sales-date')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.sales-date') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_sales')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_sales_month'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.sales-month')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.sales-month') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_sales_month')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_sales_year'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.sales-year')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.sales-year') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_sales_year')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_sales_customer'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.sales-customer')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.sales-customer') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_sales_customer')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_top_selling_product'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.top-selling')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.top-selling') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_top_selling')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_report_coupon'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.report.coupon-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.report.coupon-report') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_report_coupon')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Report end-->

                <!--Product-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_product'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.product.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.product.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-coins"></i>
                        <p><?php echo e(__('admin.menu_product')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_product_product'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.product.index')); ?>" class="nav-link <?php echo e((request()->routeIs('admin.product.index') || request()->routeIs('admin.product.stock') || request()->routeIs('admin.product.add')) || request()->routeIs('admin.product.edit*')?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_product')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_product_category'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.product.category.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.product.category.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_category')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_product_sub_category'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.product.subcategory.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.product.subcategory.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_subcategory')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Products-->

                <!--Monogram-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_monogram'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.monogram.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.monogram.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-font"></i>
                        <p><?php echo e(__('admin.menu_monogram')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_monogram_color'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.monogram.color.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.monogram.color.*')?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_monogram_color')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_monogram_placement'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.monogram.placement.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.monogram.placement.*')?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_monogram_placement')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_monogram_font_style'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.monogram.font-style.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.monogram.font-style.*')?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_monogram_font_style')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(false): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_monogram_master'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.monogram.monogram.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.monogram.monogram.*')?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_monogram_master')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Monogram-->

                <!--Master-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.master.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.master.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-globe-asia"></i>
                        <p><?php echo e(__('admin.menu_master')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.brand.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.brand.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Brand</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_color'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.color.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.color.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_color')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <!--UNITS-->
                        <?php if(false): ?>
                        <!--NOT USED IN KB-->
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_unit'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.unit.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.unit.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_unit')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <!--/.UNITS-->
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_country'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.country.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.country.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_country')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_city'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.city.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.city.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_city')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_zone'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.zone.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.zone.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_zone')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_size'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.size.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.size.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.size')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_courier_company'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.courier-company.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.courier-company.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.courier-company')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_master_coupon'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.coupon.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.coupon.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_coupon')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_physical_store'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.physical-store.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.physical-store.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.physical-store')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_warehouse_address'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.master.warehouse-address.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.master.warehouse-address.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.warehouse-address')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Master-->
                <!--Contact Us-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_contact_us'))): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.contact-us.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.contact-us*') ?'active':''); ?>">
                        <i class="fa fa-comment nav-icon"></i>
                        <p><?php echo e(__('admin.menu_contact_us')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                <!--/.Contact Us-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.page.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.page.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-file"></i>
                        <p><?php echo e(__('admin.menu_pages')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_home_slider'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.home-slider.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.home-slider.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_home_slider')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_our_collection'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.our-collection.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.our-collection.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_our_collection')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_about_us'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.about-us.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.about-us.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.about_us')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_shipping_policy'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.shipping-policy.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.shipping-policy.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.shipping-policy')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_privacy_policy'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.privacy-policy.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.privacy-policy.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.privacy-policy')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_t_and_c'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.terms.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.terms.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.terms')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_return_policy'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.return-policy.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.return-policy.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.return-policy')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_pages_contact_us'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.page.contact-us.add')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.page.contact-us.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.contact_us')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--Setting-->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_general_setting'))): ?>
                <li class="nav-item has-treeview <?php echo e(request()->routeIs('admin.general-setting.*') ?'menu-open':''); ?>">
                    <a href="#" class="nav-link <?php echo e(request()->routeIs('admin.general-setting.*') ?'active':''); ?>">
                        <i class="nav-icon fas fa-tools"></i>
                        <p><?php echo e(__('admin.menu_general_setting')); ?><i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_general_setting_setting'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.general-setting.setting.discount')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.general-setting.setting.discount') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Discount</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_general_setting_payment_gateway'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.general-setting.payment-gateway.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.general-setting.payment-gateway.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.payment-gateway')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.view_general_setting_setting'))): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.general-setting.setting.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.general-setting.setting.*') ?'active':''); ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p><?php echo e(__('admin.menu_setting')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <!--/.Setting-->

                <?php endif; ?>
                <!--/.Admin-->
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside><?php /**PATH C:\xampp\htdocs\reehana\resources\views/layout/sidebar.blade.php ENDPATH**/ ?>
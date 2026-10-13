<aside class="main-sidebar sidebar-dark-orange elevation-4">
    <!-- Brand Logo -->
    <a href="{{route("admin.home.index")}}" class="brand-link navbar-orange">
        <img src="{{Helper::getAppLogo()?Helper::getAppLogo(): ''}}" alt="{{__('admin.logo')}}" class="brand-image elevation-3" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
        <span class="brand-text">&nbsp;</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!--Super Admin-->
                @can(config('custom_middleware.view_super_admin'))
                <li class="nav-header">{{__('admin.menu_super_admin')}}</li>
                <li class="nav-item has-treeview {{ request()->routeIs('super-admin.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('super-admin.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>{{__('admin.menu_authorization')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_super_admin_role'))
                        <li class="nav-item">
                            <a href="{{route('super-admin.role.index')}}" class="nav-link {{ request()->routeIs('super-admin.role.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_role')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_super_admin_permission'))
                        <li class="nav-item">
                            <a href="{{route('super-admin.permission.index')}}" class="nav-link {{ request()->routeIs('super-admin.permission*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_permission')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_super_admin_role_permission'))
                        <li class="nav-item">
                            <a href="{{route('super-admin.role-permission.index')}}" class="nav-link {{ request()->routeIs('super-admin.role-permission*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_role_permission')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_super_admin_user_role'))
                        <li class="nav-item">
                            <a href="{{route('super-admin.user-role.index')}}" class="nav-link {{ request()->routeIs('super-admin.user-role*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_user_role')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Super Admin-->
                <!--Admin-->
                @can(config('custom_middleware.view_admin'))
                <!--<li class="nav-header">{{__('admin.admin')}}</li>-->
                <!--Dashboard-->
                @can(config('custom_middleware.view_dashboard'))
                <li class="nav-item">
                    <a href="{{route('admin.home.index')}}" class="nav-link {{ request()->routeIs('admin.home.index') ?'active':'' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{__('admin.menu_dashboard')}}</p>
                    </a>
                </li>
                @endcan
                <!--/.Dashboard-->
                <!--Customer-->
                @can(config('custom_middleware.view_customer'))
                <li class="nav-item">
                    <a href="{{route('admin.customer.index')}}" class="nav-link {{ request()->routeIs('admin.customer.*') ?'active':'' }}">
                        <i class="fas fa-users nav-icon"></i>
                        <p>{{__('admin.menu_customer')}}</p>
                    </a>
                </li>
                @endcan
                <!--/.Customer-->
                <!--Orders-->
                @can(config('custom_middleware.view_order'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.order.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.order.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{__('admin.menu_order')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_order_order'))
                        <li class="nav-item">
                            <a href="{{route('admin.order.index')}}" class="nav-link {{ request()->routeIs('admin.order.index') || request()->routeIs('admin.order.vieworder') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_order')}}</p>
                            </a>
                        </li>
                        @endcan
                        @if(false)
                        @can(config('custom_middleware.view_order_order_status'))
                        <li class="nav-item">
                            <a href="{{route('admin.order.status.index')}}" class="nav-link {{ request()->routeIs('admin.order.status.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_order_status')}}</p>
                            </a>
                        </li>
                        @endcan
                        @endif
                        @can(config('custom_middleware.view_order_wish_list'))
                        <li class="nav-item">
                            <a href="{{route('admin.order.wishlist.index')}}" class="nav-link {{ request()->routeIs('admin.order.wishlist.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.wish-list')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_order_order'))
                        <li class="nav-item">
                            <a href="{{route('admin.order.order-not-complete')}}" class="nav-link {{ request()->routeIs('admin.order.order-not-complete.*') || request()->routeIs('admin.order.order-not-complete') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.order-not-complete')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Orders-->
                <!--Report-->
                @can(config('custom_middleware.view_report'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.report.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.report.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>{{__('admin.menu_report')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_report_product_liked'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.product-like')}}" class="nav-link {{ request()->routeIs('admin.report.product-like') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_product_liked')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_sales_date'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.sales-date')}}" class="nav-link {{ request()->routeIs('admin.report.sales-date') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_sales')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_sales_month'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.sales-month')}}" class="nav-link {{ request()->routeIs('admin.report.sales-month') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_sales_month')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_sales_year'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.sales-year')}}" class="nav-link {{ request()->routeIs('admin.report.sales-year') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_sales_year')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_sales_customer'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.sales-customer')}}" class="nav-link {{ request()->routeIs('admin.report.sales-customer') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_sales_customer')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_top_selling_product'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.top-selling')}}" class="nav-link {{ request()->routeIs('admin.report.top-selling') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_top_selling')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_report_coupon'))
                        <li class="nav-item">
                            <a href="{{route('admin.report.coupon-report')}}" class="nav-link {{ request()->routeIs('admin.report.coupon-report') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_report_coupon')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Report end-->

                <!--Product-->
                @can(config('custom_middleware.view_product'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.product.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.product.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>{{__('admin.menu_product')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_product_product'))
                        <li class="nav-item">
                            <a href="{{route('admin.product.index')}}" class="nav-link {{ (request()->routeIs('admin.product.index') || request()->routeIs('admin.product.stock') || request()->routeIs('admin.product.add')) || request()->routeIs('admin.product.edit*')?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_product')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_product_category'))
                        <li class="nav-item">
                            <a href="{{route('admin.product.category.index')}}" class="nav-link {{ request()->routeIs('admin.product.category.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_category')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_product_sub_category'))
                        <li class="nav-item">
                            <a href="{{route('admin.product.subcategory.index')}}" class="nav-link {{ request()->routeIs('admin.product.subcategory.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_subcategory')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Products-->

                <!--Monogram-->
                @can(config('custom_middleware.view_monogram'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.monogram.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.monogram.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-font"></i>
                        <p>{{__('admin.menu_monogram')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_monogram_color'))
                        <li class="nav-item">
                            <a href="{{route('admin.monogram.color.index')}}" class="nav-link {{ request()->routeIs('admin.monogram.color.*')?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_monogram_color')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_monogram_placement'))
                        <li class="nav-item">
                            <a href="{{route('admin.monogram.placement.index')}}" class="nav-link {{ request()->routeIs('admin.monogram.placement.*')?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_monogram_placement')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_monogram_font_style'))
                        <li class="nav-item">
                            <a href="{{route('admin.monogram.font-style.index')}}" class="nav-link {{ request()->routeIs('admin.monogram.font-style.*')?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_monogram_font_style')}}</p>
                            </a>
                        </li>
                        @endcan
                        @if(false)
                        @can(config('custom_middleware.view_monogram_master'))
                        <li class="nav-item">
                            <a href="{{route('admin.monogram.monogram.index')}}" class="nav-link {{ request()->routeIs('admin.monogram.monogram.*')?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_monogram_master')}}</p>
                            </a>
                        </li>
                        @endcan
                        @endif
                    </ul>
                </li>
                @endcan
                <!--/.Monogram-->

                <!--Master-->
                @can(config('custom_middleware.view_master'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.master.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.master.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-globe-asia"></i>
                        <p>{{__('admin.menu_master')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">

                        @can(config('custom_middleware.view_master'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.brand.index')}}" class="nav-link {{ request()->routeIs('admin.master.brand.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Brand</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_color'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.color.index')}}" class="nav-link {{ request()->routeIs('admin.master.color.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_color')}}</p>
                            </a>
                        </li>
                        @endcan
                        <!--UNITS-->
                        @if(false)
                        <!--NOT USED IN KB-->
                        @can(config('custom_middleware.view_master_unit'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.unit.index')}}" class="nav-link {{ request()->routeIs('admin.master.unit.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_unit')}}</p>
                            </a>
                        </li>
                        @endcan
                        <!--/.UNITS-->
                        @endif
                        @can(config('custom_middleware.view_master_country'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.country.index')}}" class="nav-link {{ request()->routeIs('admin.master.country.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_country')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_city'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.city.index')}}" class="nav-link {{ request()->routeIs('admin.master.city.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_city')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_zone'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.zone.index')}}" class="nav-link {{ request()->routeIs('admin.master.zone.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_zone')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_size'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.size.index')}}" class="nav-link {{ request()->routeIs('admin.master.size.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.size')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_courier_company'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.courier-company.index')}}" class="nav-link {{ request()->routeIs('admin.master.courier-company.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.courier-company')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_master_coupon'))
                        <li class="nav-item">
                            <a href="{{route('admin.master.coupon.index')}}" class="nav-link {{ request()->routeIs('admin.master.coupon.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_coupon')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Master-->
                <!--Contact Us-->
                @can(config('custom_middleware.view_contact_us'))
                <li class="nav-item">
                    <a href="{{route('admin.contact-us.index')}}" class="nav-link {{ request()->routeIs('admin.contact-us*') ?'active':'' }}">
                        <i class="fa fa-comment nav-icon"></i>
                        <p>{{__('admin.menu_contact_us')}}</p>
                    </a>
                </li>
                @endcan
                <!--/.Contact Us-->
                @can(config('custom_middleware.view_pages'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.page.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.page.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-file"></i>
                        <p>{{__('admin.menu_pages')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can(config('custom_middleware.view_pages_home_slider'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.home-slider.index')}}" class="nav-link {{ request()->routeIs('admin.page.home-slider.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_home_slider')}}</p>
                            </a>
                        </li>
                        @endcan

                        @can(config('custom_middleware.view_pages_our_collection'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.our-collection.index')}}" class="nav-link {{ request()->routeIs('admin.page.our-collection.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_our_collection')}}</p>
                            </a>
                        </li>
                        @endcan

                        @can(config('custom_middleware.view_pages_about_us'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.about-us.add')}}" class="nav-link {{ request()->routeIs('admin.page.about-us.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.about_us')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_pages_shipping_policy'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.shipping-policy.add')}}" class="nav-link {{ request()->routeIs('admin.page.shipping-policy.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.shipping-policy')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_pages_privacy_policy'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.privacy-policy.add')}}" class="nav-link {{ request()->routeIs('admin.page.privacy-policy.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.privacy-policy')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_pages_t_and_c'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.terms.add')}}" class="nav-link {{ request()->routeIs('admin.page.terms.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.terms')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_pages_return_policy'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.return-policy.add')}}" class="nav-link {{ request()->routeIs('admin.page.return-policy.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.return-policy')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_pages_contact_us'))
                        <li class="nav-item">
                            <a href="{{route('admin.page.contact-us.add')}}" class="nav-link {{ request()->routeIs('admin.page.contact-us.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.contact_us')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--Setting-->
                @can(config('custom_middleware.view_general_setting'))
                <li class="nav-item has-treeview {{ request()->routeIs('admin.general-setting.*') ?'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.general-setting.*') ?'active':'' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>{{__('admin.menu_general_setting')}}<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">

                        @can(config('custom_middleware.view_general_setting_setting'))
                        <li class="nav-item">
                            <a href="{{route('admin.general-setting.setting.discount')}}" class="nav-link {{ request()->routeIs('admin.general-setting.setting.discount') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Discount</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_general_setting_payment_gateway'))
                        <li class="nav-item">
                            <a href="{{route('admin.general-setting.payment-gateway.index')}}" class="nav-link {{ request()->routeIs('admin.general-setting.payment-gateway.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.payment-gateway')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can(config('custom_middleware.view_general_setting_setting'))
                        <li class="nav-item">
                            <a href="{{route('admin.general-setting.setting.index')}}" class="nav-link {{ request()->routeIs('admin.general-setting.setting.*') ?'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('admin.menu_setting')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                <!--/.Setting-->

                @endcan
                <!--/.Admin-->
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
<div id="menu-handheld" class="fancy">
    <div class="menu-container">
        <div class="search" id="search">
            <div class="searchContainer">
                <form method="get" action="{{route('web.product.index')}}" id="search-form">
                    <input type="submit" value="">
                    <input type="text" name="search" placeholder="{{__('web.search')}}" id="search_bind" required="" value="{{Request::get('search')}}">
                    <span id="clear" class="{{ Request::get('search') ? '' : 'hide'}}"><i class="icon-close"></i></span>
                </form>
            </div>
        </div>
        <ul class="navbar-nav nav-dropdown nav-right quick-menu" data-app-modern-menu="true">
            <li class="nav-item">
                <a class="nav-link link" href="{{route("web.about")}}"><span>{{__('web.about_us')}}</span></a>
            </li>
            @if(Helper::isExistNewArrivalProduct())
            <li class="nav-item">
                <a class="nav-link link" href="{{route("web.product.new_arrivals")}}"><span>{{__('web.new_arrivals')}}</span></a>
            </li>
            @endif
            <li class="nav-item dropdown">
                <a href="#" class="nav-link link dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                    <span>{{__('web.collections')}}</span>
                    <i class="icon-up expanded"></i>
                    <i class="icon-down collapsed"></i>
                </a>
                <div class="submenu dropdown-menu">
                    <div class="v-flex">
                        <ul>
                            @include('web/layout/menu_list')
                        </ul>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link link dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                    <span>{{__('web.brands')}}</span>
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
                            @foreach($brand as $record)
                            <?php
                            $brand_desc = $record->getBrandDescription($language->id);
                            ?>
                            <li><a href="{{route('web.product.shop_by_brand', (['b[]'=>$brand_desc ? $brand_desc->brand_name : '']))}}"><span>{{$brand_desc ? $brand_desc->brand_name : ''}}</span></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </li>
            @if(Helper::isExistSaleProduct() )
            <li class="nav-item">
                <a class="nav-link link" href="{{route("web.product.sale")}}"><span>{{__('web.sale')}}</span></a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link link" href="{{route("web.contact")}}"><span>{{__('web.contact_us')}}</span></a>
            </li>
        </ul>
    </div>
</div>
<div id="sidenav-overlay"></div>
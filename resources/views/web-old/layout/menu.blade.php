
      
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
                    <a href="{{route('web.index')}}">
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
                    <li class="nav-icon dropdown user_cart">
                        @include('web.product.cart')
                        
                    </li>
                    <li class="nav-icon sm-hidden"><a href="{{route('web.wishlist.index')}}"><i class="icon-wishlist"></i></a></li>
                    <li class="nav-icon {{Auth::user() ? 'dropdown' : '' }}">
                        @if(Auth::user())
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
                            <i class="icon-user"></i>
                            <i class="icon-down"></i>
                        </a>
                        <div id="user-menu" class="user-menu dropdown-menu">
                            <ul>
                                <li><a href="{{route('web.user.my_order')}}">{{__('web.my_orders')}}</a></li>
                                <li><a href="{{route('web.wishlist.index')}}">{{__('web.my_wishlist')}}</a></li>
                                <li class="separator"></li>
                                <li><a href="{{route('web.user.my_address')}}">{{__('web.my_addresses')}}</a></li>
                                <li><a href="{{route('web.user.profile')}}">{{__('web.my_profile')}}</a></li>
                                <li><a href="{{route('web.user.change_password')}}">{{__('web.change_password')}}</a></li>
                                <li><a href="{{route('web.user.logout')}}">{{__('web.logout')}}</a></li>
                            </ul>
                        </div>
                        @else
                        <a href="{{route('web.user.login')}}">
                            <i class="icon-user"></i>
                        </a>
                        @endif
                    </li>

                    <?php
                    $countryList = Helper::getCountryList();
                    $selectedCountry = Helper::getCurrency();
                    ?>
                    @if($countryList)
                    <li class="nav-icon">
                        <select class="select-currency">
                            @foreach($countryList as $country)
                            <option value="{{$country->id}}" {{$country->currency_code == $selectedCountry->currency_code?'selected':''}}            data-href="{{route('web.change_currency',['id'=>$country->id])}}">{{$country->currency_code}}</option>
                            @endforeach
                        </select>
                    </li>
                    @endif
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
</section>
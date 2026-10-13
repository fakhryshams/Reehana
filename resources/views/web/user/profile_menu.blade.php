<?php
$wishlistCount = Helper::getWishlistCount();
?>
<ul class="tabs">
    <li class="{{request()->routeIs('web.user.profile') ? 'active': '' }}"><a href="{{route('web.user.profile')}}"><span class="profile"><i class="fas fa-user"></i> <em>{{__('web.profile')}}</em></span></a></li>
    <li class="{{request()->routeIs('web.user.my_order') || request()->routeIs('web.user.order_detail') ? 'active': '' }}"><a href="{{route('web.user.my_order')}}"><span class="orders"><i class="icon-cart"></i> <em>{{__('web.my_orders')}}</em></span></a></li>
    <li class="{{request()->routeIs('web.wishlist.index') ? 'active': '' }}"><a href="{{route('web.wishlist.index')}}"><span class="wishlist"><i class="icon-wishlist"></i> <em class="wish_count">{{__('web.my_wishlist')}} ({{$wishlistCount}})</em></span></a></li>
    <li class=" {{request()->routeIs('web.user.my_address') ? 'active': '' }}"><a href="{{route('web.user.my_address')}}"><span class="addresses"><i class="far fa-address-book"></i> <em>{{__('web.my_addresses')}}</em></span></a></li>
    <li class="{{request()->routeIs('web.user.change_password') ? 'active': '' }}"><a href="{{route('web.user.change_password')}}"><span class="password"><i class="fas fa-unlock-alt"></i> <em>{{__('web.change_password')}}</em></span></a></li>
</ul>


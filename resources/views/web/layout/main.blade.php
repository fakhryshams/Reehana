<!DOCTYPE html>
<html>
    @include('web/layout/head')

    <body class="@yield('body_class')">

        @if(!request()->routeIs('web.product.product_detail'))
        <div id="preloader"  >
            <img src="{{asset('front/img/loader.gif')}}">Loading...
        </div>
        @endif
        <div class="wrapper @yield('profile_class')">
            <!--Menu-->
            @include('web/layout/menu')
            <!--Main Content-->
            @yield('content')

            <!--Footer-->
            @include('web/layout/footer')
        </div>

        <!--Modal Ex: Forgot Password-->
        @yield('page_specific_html')
        <!--Header-->
        @include('web/layout/responsive_menu')
        @include('web/layout/monogram')
        <!--Global Back to Top Button-->
        @include('web/layout/back_to_top')
        <!--Footer Scripts-->
        @include('web/layout/footer_scripts')
    </body>
</html>
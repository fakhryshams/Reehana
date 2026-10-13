<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="generator" content="{{Helper::getAppName()?Helper::getAppName():''}}">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="{{asset('front/img/favicon.png')}}" type="image/x-icon">
    <meta name="description" content="{{Helper::getAppName()?Helper::getAppName():''}}">
    @yield('page_meta')
    <link rel="manifest" href="{{asset('front/css/manifest.json')}}">
    <link rel="android-touch-icon" sizes="76x76" href="{{asset('front/img/android-icon-192x192.png')}}">
    <link rel="android-touch-icon" sizes="76x76" href="{{asset('front/img/android-icon-196x196.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-57x57.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-60x60.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-72x72.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-76x76.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-114x114.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-120x120.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-144x144.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-152x152.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-180x180.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-192x192.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('front/img/apple-icon-196x196.png')}}">

    <title>{{Helper::getAppName()?Helper::getAppName()." | ":''}} @yield('page_title')</title>
    <link rel="stylesheet"  href="{{asset('front/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/bootstrap-reboot.min.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/fontawesome.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/slick.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/jquery-ui.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/cloudzoom.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('front/css/media.css')}}">
    <style>
        .text-red{
            color:rgb(189, 31, 46);
            font-size:13px;
            font-style:normal;
            font-family:'Roboto Condensed', sans-serif;
            float:left;
        }
        .hide{
            display: none !important;
        }

        .text-green {
            color: #5fb098!important;
        }
			.address-item input#phone {
		max-width: 71%;
		margin: 10px auto auto auto;
		border-radius: 0;
		font-family: 'Futura Bk BT';
		font-size: 14px;
		height: 45px;
	}
	
		@media screen and (max-width: 767px) {
    section.login .h-flex.grey-background {
        flex-direction: column-reverse;
    }
}

@media screen and (max-width: 767px) {
    .login .h-flex > .guest {
        padding-top: 0;
        padding-bottom: 0;
        border-top: none;
        border-bottom: 1px solid #d7d7d7;
        margin-bottom: 25px;
    }
}


    </style>


<?php
 $setting = \App\Helpers\Helper::getSetting();
  
 ?>
<script async src="https://www.googletagmanager.com/gtag/js?id={{$setting[10]->setting_val}}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){window.dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{{$setting[10]->setting_val}}');
</script>
</head>
<?php $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
 ?>
<input type="hidden" name="murl" id="murl" value="{{route('web.product.get-monogram')}}">
<input type="hidden" name="base_url" id="base_url" value="{{asset('storage/')}}">
<input type="hidden" name="initials" id="initials_url" value="{{route('web.product.get_initials')}}">
<input type="hidden" name="initials" id="remove_monogram_url" value="{{route('web.product.remove-monogram')}}">
<input type="hidden" name="initials" id="update_monogram_url" value="{{route('web.product.update-monogram')}}">
<input type="hidden"  id="placement_url" value="{{route('web.product.get-placement')}}">
<input type="hidden"  id="apply_same_url" value="{{route('web.product.apply_same')}}">
<input type="hidden"  id="monoPriceData" value="{{Helper::formatNumber($monogramPrice)}}">
<input type="hidden" name="token" id="token" value="{{ csrf_token()}}">

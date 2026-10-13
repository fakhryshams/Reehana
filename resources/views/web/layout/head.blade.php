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

        .order-details {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
            gap: 30px !important;
            margin-top: 24px !important;
            margin-bottom: 24px !important;
        }
        .order-details > .w-33,
        .order-details > div {
            flex: 1 1 calc(50% - 15px) !important;
            max-width: calc(50% - 15px) !important;
            min-width: 280px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: stretch !important;
            align-self: stretch !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        .order-details .billing-summary,
        .order-details .shipping-summary,
        .order-details .summary-details {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }
        .order-details .billing-summary > h4,
        .order-details .billing-summary h4,
        .order-details .shipping-summary > h4,
        .order-details .shipping-summary h4,
        .order-details .summary-details > h4,
        .order-details .summary-details h4,
        .order-details > .w-33 > h4,
        .order-details > div > h4 {
            margin-top: 0 !important;
            margin-bottom: 14px !important;
            flex: 0 0 auto !important;
            line-height: 1.2 !important;
            font-size: 20px !important;
        }
        .order-details .billing-summary .address-item,
        .order-details .shipping-summary .address-item {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #d7d7d7 !important;
            /* background: #f0f0f0 !important; */
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .billing-summary .address-item .contact,
        .order-details .shipping-summary .address-item .contact {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 15px 20px !important;
            box-sizing: border-box !important;
            cursor: default !important;
        }
        .order-details .billing-summary .address-item .contact .contact-name,
        .order-details .shipping-summary .address-item .contact .contact-name {
            flex: 0 0 auto !important;
            margin-bottom: 8px !important;
            padding: 0 !important;
        }
        .order-details .billing-summary .address-item .contact .contact-address,
        .order-details .shipping-summary .address-item .contact .contact-address {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        .order-details .order-summary {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 1px solid #d7d7d7 !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .order-summary .cart-details {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 15px 20px !important;
            border: none !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .order-details .order-summary .cart-details .v-flex {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        @media screen and (max-width: 767px) {
            .order-details > .w-33,
            .order-details > div {
                flex: 1 1 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
            }
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

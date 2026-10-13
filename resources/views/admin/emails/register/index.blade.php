<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
    <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
    <title>::: {{__('admin.sales_login_title')}} | {{Helper::getAppName()?Helper::getAppName():''}} :::</title>

    <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
</head>

<body style="background: #fff; font-family: 'Futura Bk BT'; font-size: 14px; color: #000000; line-height: 1.5em">
    <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box;">
        <div style="padding: 20px 0;">
            <img src="https://reehana.com/front/img/logo.png" alt="{{Helper::getAppName()?Helper::getAppName():''}}">
        </div>

        <!-- Body -->
        <div style="padding: 0 20px 20px 20px; border-top: 1px solid #d7d7d7;">
            <div style="margin-bottom: 30px;">
                <p>
                    {{ __('admin.dear') }}
                    {{ ucfirst($user->first_name) }} {{ ucfirst($user->last_name) }},
                </p>

                <h2 style="font-size:22px;font-weight:normal;margin:20px 0;">
                    {{ __('admin.sales_welcome', ['app' => Helper::getAppName()]) }}
                </h2>

                <p>{{ __('admin.sales_account_created') }}</p>

                <!-- Credentials Box -->
                <div style="background:#f9f9f9;border:1px solid #e0e0e0;padding:15px;margin:20px 0;">

                    <p style="margin:0;"><strong>{{ __('admin.login_url') }}:</strong></p>
                    <p style="margin:5px 0;">
                        <a href="{{ route('web.user.login') }}" style="color:#caa42f;">
                            {{ route('web.user.login') }}
                        </a>
                    </p>

                    <p style="margin:10px 0 0;"><strong>{{ __('admin.username_email') }}:</strong></p>
                    <p style="margin:5px 0;">{{ $user->email }}</p>

                    <p style="margin:10px 0 0;"><strong>{{ __('admin.temporary_password') }}:</strong></p>
                    <p style="margin:5px 0;">{{ $plainPassword }}</p>
                </div>

                <p>{!! __('admin.change_password_note') !!}</p>

                <p>{{ __('admin.contact_store_admin') }}</p>

                <p style="margin-top:30px;">
                    {{ __('admin.thank_you_part_of', ['app' => Helper::getAppName()]) }}
                </p>

                <p style="color:#caa42f;">
                    {{ __('admin.best_wishes') }},<br>
                    {{ Helper::getAppName() }} {{ __('admin.team') }}
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-image:url({{ asset('front/img/footer.png') }});background-repeat:no-repeat;background-position:center;background-size:auto 100%;height:120px;"></div>

        <div style="padding:20px;text-align:center;font-size:12px;color:#555;">
            <span>{{ __('admin.all_rights_reserved') }}</span>
        </div>

    </div>
</body>

</html>
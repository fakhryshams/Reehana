<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{__('admin.forgot-password-title')}} | {{Helper::getAppName()?\App\Helpers\Helper::getAppName():''}} :::</title>

        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    </head>
    <body style="background: #fff; font-family: 'Segoe UI', serif; font-size: 14px; color: #606060; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; border: 5px solid rgba(225, 225, 225, 0.3)">
            <div style="text-align: center; padding: 20px;">
                <img src="{{\App\Helpers\Helper::getAppLogo()?Helper::getAppLogo(): ''}}" >
            </div>
            <div style="padding: 20px; background: rgba(225, 225, 225, 0.3) url({{asset('images/mail/bg.svg')}}) top center; background-size: 950px auto;">
                <div style="margin-bottom: 30px;">
                    <p><b>{{__('admin.dear')}} {{ucfirst($user->name)}} ,</b></p>
                    <p>{{__('admin.user_with_detail')}}:</p>
                </div>
                <div style="border: 1px solid #f6cfca; margin-bottom: 30px;">
                    <table style="border-collapse: collapse; width: 100%; background: #fff;">

                        <tr style="border-bottom: 1px solid #f6cfca;">
                            <th style="padding: 10px; text-align: left; background: #f6cfca;">{{__('admin.registered_email')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;">{{$user->email}}</td>
                        </tr>
                        <tr>
                            <th style="padding: 10px; text-align: left; background: #f6cfca;">{{__('admin.login_here')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;"><a href="{{$link}}">{{__('admin.fp-link')}}</a></td>
                        </tr>
                    </table>
                </div>
                <div style="color: #e18a88;">
                    <p>{{__('admin.best_wishes')}},<br>{{Helper::getAppName()?Helper::getAppName():''}}</p>
                </div>
            </div>
            <div style="padding: 20px; text-align: center; font-size: 12px; background: #e18a88; color: #ffffff;">
                <div>
                    <?php
                    $setting       = \App\Helpers\Helper::getSetting();
                    $facebookLink  = isset($setting[3]) ? $setting[3]->setting_val : "";
                    $instagramLink = isset($setting[5]) ? $setting[5]->setting_val : "";
                    $twitterLink   = isset($setting[19]) ? $setting[19]->setting_val : "";
                    ?>
                    <div>
                        @if($facebookLink)
                        <span style="display: inline-block; margin: 0 5px;"><a href="{{$facebookLink}}" target="_blank"><img src="{{asset('images/mail/fb.png')}}"></a></span>
                        @endif
                        @if($twitterLink)
                        <span style="display: inline-block; margin: 0 5px;"><a href="{{$twitterLink}}" target="_blank"><img src="{{asset('images/mail/tw.png')}}"></a></span>
                        @endif
                        @if($instagramLink)
                        <span style="display: inline-block; margin: 0 5px;"><a href="{{$instagramLink}}" target="_blank"><img src="{{asset('images/mail/in.png')}}"></a></span>
                        @endif
                    </div>
                </div>
                <span style="display: block">{{__('admin.all_rights_reserved')}}.</span>
            </div>
        </div>
    </body>
</html>

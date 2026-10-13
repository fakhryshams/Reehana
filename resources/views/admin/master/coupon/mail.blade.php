<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{Helper::getAppName()?Helper::getAppName()." | ":''}} {{trans('admin.Order')}} :::</title>
        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    </head>
    <body style="background: #fff; font-family: 'Futura Bk BT'; font-size: 14px; color: #000000; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box;">
            <div style="padding: 20px 0;">
                <img src="{{Helper::getMailLogo()}}" alt="REEHANA">
            </div>
            <div style="padding: 0 10px; border-top: 1px solid #d7d7d7;">
                <div>
                    <p>{!! $couponDescription !!}</p>
                </div>
                <div style="color: #caa42f;">
                    <p>Best Wishes,<br>REEHANA Team</p>
                </div>
            </div>
            <div style="background-image: url({{asset('front/img/footer.png')}}); background-size: auto 100%; background-repeat: no-repeat; background-position: center; height: 137px; width: 100%; max-width: 100%;"></div>

            <div style="padding: 20px; text-align: center; font-size: 13px;">
                <?php
                $setting       = Helper::getSetting();
                $whatsappLink  = isset($setting[6]) ? $setting[6]->setting_val : "";
                $instagramLink = isset($setting[5]) ? $setting[5]->setting_val : "";
                ?>
                <span style="display: block;">ALL RIGHTS RESERVED.</span>
                <div style="margin: 20px auto; border-bottom: 1px solid #000000; max-width: 400px; height: 10px;">
                    @if($instagramLink)
                    <span style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 40px"><a href="{{$instagramLink}}" target="_blank"><img src="{{asset('images/mail/in.png')}}"></a></span>
                    @endif
                    @if($whatsappLink)
                    <span style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 40px"><a href="{{$whatsappLink}}" target="_blank"><img src="{{asset('images/mail/wa.png')}}"></a></span>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>

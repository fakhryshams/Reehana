<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{__('web.contact_us')}} | {{Helper::getAppName()?Helper::getAppName():''}}. :::</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    </head>
    <body style="background: #fff; font-family: 'Roboto Condensed', sans-serif; font-weight: 400; font-size: 14px; color: #000000; line-height: 1.5em;">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; border: 5px solid rgba(225, 225, 225, 0.3)">
            <div style="text-align: center; padding: 0; background: url({{asset('images/mail/bg-pattern.png')}}) center repeat; background-size: 20% auto; height: 100px; overflow: hidden;">
                <img src="{{Helper::getAppLogo(1)?Helper::getAppLogo(1): ''}}" style="width: 200px;">
            </div>        
            <div style="padding: 20px;">
                <div style="margin-bottom: 30px;">
                    <p><b>{{ucfirst($detail->first_name)}} {{ucfirst($detail->last_name)}},</b></p>
                </div>
                <div style="border: 1px solid #fef4f2; margin-bottom: 30px;">
                    <table style="border-collapse: collapse; width: 100%; background: #fff;">
                        <tr style="border-bottom: 1px solid #fef4f2;">
                            <th style="padding: 10px; text-align: left; background: #fef4f2;">{{__('web.email')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;">{{$detail->email}}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <th style="padding: 10px; text-align: left; background: #fef4f2;">{{__('web.phone')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;">{{$detail->mobile}}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <th style="padding: 10px; text-align: left; background: #fef4f2;">{{__('web.query')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;">{{$detail->subject}}</td>
                        </tr>
                        <tr>
                            <th style="padding: 10px; text-align: left; background: #fef4f2;">{{__('web.message')}}:</th>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c;">{{$detail->message}}</td>
                        </tr>
                    </table>
                </div>

                <div style="color: #f5907c;">
                    <p>Best Wishes,<br>Salma Musab Team</p>
                </div>
            </div>

            <div style="padding: 20px; text-align: center; font-size: 12px; background: #f5907c; color: #ffffff;">
                <?php
                $setting       = Helper::getSetting();
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
                <span style="display: block">All rights reserved.</span>
            </div>
        </div>
    </body>
</html>


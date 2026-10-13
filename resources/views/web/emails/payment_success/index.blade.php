<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{__('web.orders')}} | {{Helper::getAppName()?Helper::getAppName():''}}:::</title>
        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    </head>
    <body style="background: #fff; font-family: 'Roboto Condensed', sans-serif; font-weight: 400; font-size: 14px; color: #000000; line-height: 1.5em;">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; border: 5px solid rgba(225, 225, 225, 0.3)">
            <div style="text-align: center; padding: 0; background: url({{asset('images/mail/bg-pattern.png')}}) center repeat; background-size: 20% auto; height: 100px; overflow: hidden;">
                <img src="{{Helper::getAppLogo(1)?Helper::getAppLogo(1): ''}}" style="width: 200px;">
            </div>
            <div style="padding: 20px; background: #f5eded; background-size: 950px auto;">
                <div>
                    <p>{{__('web.dear')}} {{ucfirst($shipping_address->first_name)}},</p>
                    <h1 style="font-size: 24px; margin: 20px 0; font-weight: normal; line-height: 1.4em;">{{__('web.payment_success_made')}}!</h1>
                    <p>{{__('web.thank_you_for_order')}} {{$order->id}}.{{__('web.these_are_items')}}:</p>
                </div>
                <div style="border: 1px solid #eee8e0; margin-bottom: 30px;">
                    <table style="border-collapse: collapse; width: 100%; background: #fff;">
                        <tr>
                            <th style="padding: 10px; text-align: left; background: #eee8e0;">{{__('web.order')}} #</th>
                            <th style="padding: 10px; text-align: left; background: #eee8e0;">{{__('web.item')}}</th>
                            <th style="padding: 10px; text-align: left; background: #eee8e0;">{{__('web.qty')}}</th>
                            <th style="padding: 10px; text-align: left; background: #eee8e0;">{{__('web.price')}}</th>
                        </tr>
                        <?php
                        $currency            = Helper::getDefaultCurrency();
                        $currencycode        = $currency ? $currency->code : config('custom.currency');
                        ?>
                        @foreach($orderDetail as $record)
                        <?php
                        $product             = \App\AdminModel\Product\AdminProduct::find($record->product_id);
                        $productdetail       = $product->getProductDescription($language->id);
                        $productDefaultImage = $product->getProductImages(1);
                        ?>
                        <tr>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top;">#{{$record->order_id}}</td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top;">{{$productdetail->products_name }}</td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top;">{{$record->quantity}}</td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top;">{{$record->total_price}} {{$currencycode}}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                <div>
                    <p>{{__('web.you_will_recive_notification')}}</p>
                    <p>{{__('web.if_you_need_any_further_assistanc')}} <a href="mailto:{{$customerCareEmail}}">{{$customerCareEmail}}</a></p>
                    <p>{{__('web.thankyou_for_shopping')}}!</p>
                </div>
                <div style="color: #e18a88;">
                    <p>{{__('web.best_wishes')}},<br>{{Helper::getAppName()?Helper::getAppName():''}}</p>
                </div>
            </div>
            <div style="padding: 20px; text-align: center; font-size: 12px; background: #e18a88; color: #ffffff;">
                <div>
                    <?php
                    $setting             = Helper::getSetting();
                    $facebookLink        = isset($setting[3]) ? $setting[3]->setting_val : "";
                    $instagramLink       = isset($setting[5]) ? $setting[5]->setting_val : "";
                    $twitterLink         = isset($setting[19]) ? $setting[19]->setting_val : "";
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
                <span style="display: block">{{__('web.all_rights_reserved')}}.</span>
            </div>
        </div>
    </body>
</html>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{Helper::getAppName($locale)?Helper::getAppName($locale)." | ":''}} {{trans('admin.Order')}} :::</title>

        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    </head>
    <body style="background: #fff; font-family: 'Futura Bk BT'; font-size: 14px; color: #000000; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box;">
            <div style="padding: 20px 0;">
                <img src="{{Helper::getMailLogo()}}" alt="REEHANA">
            </div>

            <div style="padding: 0 10px; border-top: 1px solid #d7d7d7;">
                <div>
                    <p>Dear {{$user?ucfirst($user->first_name).' '.ucfirst($user->last_name)  :''}},</p>
                    <h1 style="font-size: 24px; margin: 20px 0; font-weight: normal; line-height: 1.4em;">Your Order Status</h1>
                    <p>Thank you for your order #{{$orderDetail->id}} It has been {{$orderStatusText}}.</p>
                    @if($courier && $orderDetail->tracking_number)
                    <p>Your order tracking #<a href="{{$courier->link}}">{{$orderDetail->tracking_number}}</a></p>
                    @endif
                    <p>These are the items in your order:</p>
                </div>
                <div style="border: 1px solid #dfdfdf; margin-bottom: 30px;">
                    <table style="border-collapse: collapse; width: 100%; background: #fff;">
                        <tr>
                            <th style="font-weight: normal; padding: 10px; text-align: left; background: #efefef;">Product</th>
                            <th style="font-weight: normal; padding: 10px; text-align: left; background: #efefef;">Description &amp; Status</th>
                        </tr>
                        <?php
                        $conversionRate = $orderDetail->currency_rate ? $orderDetail->currency_rate : 1;
                        ?>
                        @forelse($orderProductDetail as $record)
                        <?php
                        $product = $record->getProduct($locale);
                        $productImage = $record->getProductImage();
                        $pricetotal = $record->quantity * $record->product_price;
                        $sizeId = $record['size_id'];
                        $size = Helper::getSize($sizeId, $locale);
                        $color = Helper::getColorName($record['color_id'], $locale);
                        $orderStatusPastForm = config('order.pastForm');
                        $orderStatus = $record->order_status ? $record->order_status : $orderDetail->order_status;
                        $orderStatusText = $orderStatusPastForm[$orderStatus];
                        ?>
                        <tr>
                            <td style="padding: 10px; vertical-align: top; width: 50px;">
                                <img src="{{asset(Storage::url($productImage?$productImage->image_thumb_url:''))}}" width="90">
                            </td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top; line-height: 1.5em;">
                                <div>{{$product?ucfirst($product->products_name):""}}</div>
                                <div>Qty: {{$record->quantity}}</div>
                                @if($size)
                                <div>Size: {{ucfirst($size->size_title)}}</div>
                                @endif
                                @if($color)
                                <div>Color: {{ucfirst($color->color_title)}}</div>
                                @endif
                                <div>Price: {{Helper::formatNumber($pricetotal*$conversionRate)}} {{$orderDetail->currency_code?($orderDetail->currency_code):($currency?$currency->code:config('custom.currency'))}}</div>
                                <div>Status: {{$orderStatusText}}</div>
                            </td>
                        </tr>
                        @empty
                        @endforelse 
                    </table>
                </div>
                <div>
                    <p>The team at REEHANA is at your service if you need any further assistance regarding the above order. Please drop us an email at <a href="mailto:{{$mailFrom['customerCareEmail']}}" style="color: #caa42f;">{{$mailFrom['customerCareEmail']}}</a></p>
                </div>
                <p>Thank you for shopping with REEHANA</p>
                <div style="color: #caa42f;">
                    <p>Best Wishes,<br>REEHANA Team</p>
                </div>
            </div>
            <div style="background-image: url({{asset('front/img/footer.png')}}); background-size: auto 100%; background-repeat: no-repeat; background-position: center; height: 137px; width: 100%; max-width: 100%;"></div>

            <div style="padding: 20px; text-align: center; font-size: 13px;">
                <?php
                $setting = Helper::getSetting();
                $whatsappLink = isset($setting[6]) ? $setting[6]->setting_val : "";
                $instagramLink = isset($setting[5]) ? $setting[5]->setting_val : "";
                ?>
                <span style="display: block;">ALL RIGHTS RESERVED.</span>
                <div style="margin: 20px auto; border-bottom: 1px solid #000000; max-width: 400px; height: 10px;">
                    @if($instagramLink)
                    <span  style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="{{$instagramLink}}" target="_blank"><img src="{{asset('images/mail/in.png')}}"></a></span>
                    @endif
                    @if($whatsappLink)
                    <span  style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="{{$whatsappLink}}" target="_blank"><img src="{{asset('images/mail/wa.png')}}"></a></span>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>

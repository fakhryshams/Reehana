<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{__('web.orders')}} | {{Helper::getAppName()?Helper::getAppName():''}}  :::</title>

        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
    </head>
    <body style="background: #fff; font-family: 'Futura Bk BT'; font-size: 14px; color: #000000; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; background: #ffffff;">
            <div style="padding: 20px 0;">
                <img src="{{asset('front/img/logo.png')}}" alt="REEHANA">
            </div>
            <?php
            $language = Helper::getLanguage();
            $payment_method = Helper::getPaymentMethodByID($order->payment_method);
            $country = $shipping_address->getUserCountry($language->id);
            ?>
            <div style="padding: 0 10px; border-top: 1px solid #d7d7d7;">
                <div>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.dear')}} {{$shipping_address->first_name}},</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 22px; line-height: 30px;">{{__('web.thankyou_for_shopping').' '. strtoupper(Helper::getAppName())}}</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;">{{__('web.order').' #'.$order->id.' '. __('web.has_been_placed')}} {{date("d M Y", strtotime($order->created_at)).'. '. __('web.order_deatil_below')}} :</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;">{{__('web.payment_method')}} : <span>{{$payment_method}}</span></p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;">{{__('web.estimated_delivery')}} : <span>{{$country->delivery_time}} Days</span></p>
                </div>
                <div style="border: 1px solid #dfdfdf; margin: 30px 0; background: #fff;">
                    <div style="padding: 10px; background: #f0f0f0;"><span>{{__('web.delivery_address')}}</span></div>
                    <div style="padding: 10px;">
                        {!! Helper::showAddress('mail',$shipping_address) !!}
                    </div>
                </div>


                <div style="border: 1px solid #dfdfdf; margin-bottom: 30px;">
                    <table style="border-collapse: collapse; width: 100%; background: #fff;">
                        <tr>
                            <th style="padding: 10px; text-align: left; background: #f0f0f0; font-weight: normal;" colspan="2">Product &amp; Description</th>
                            <!-- <th style="padding: 10px; text-align: left; background: #f0f0f0; font-weight: normal; text-align: center;">Qty</th>
                            <th style="padding: 10px; text-align: left; background: #f0f0f0; font-weight: normal; text-align: center;">Price</th> -->
                        </tr>
                        <?php
                        $currency = Helper::getDefaultCurrency();
                        $currencyCode = $currency ? $currency->code : config('custom.currency');
                        $conversionRate = $currency ? $currency->value : 1;
                        ?>
                        @foreach($orderDetail as $record)
                        <?php
                        $product = $record->getProductDetail();
                        $productDetail = $product->getProductDescription($language->id);
                        $productDefaultImage = $product->getProductImages(1);
                        $sizeId = $record['size_id'];
                        $color_id = $record['color_id'];
                        $size = Helper::getSize($sizeId, $language->id);
                        $color = Helper::getColorName($color_id, $language->id);
                        $note = $record['note'];
                        $style = '';
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);

                        if ($attributePrice)
                        {
                            $style = $attributePrice->style;
                        }

                        if ($order->currency_rate)
                        {
                            $record->product_price = $record->product_price * $order->currency_rate;
                            $record->total_price = $record->total_price * $order->currency_rate;
                            $order->gift_wrap = $order->gift_wrap * $order->currency_rate;
                            $order->gift_box = $order->gift_box * $order->currency_rate;
                        }
                        ?>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px; vertical-align: top;" colspan="2">
                                <div style="display: flex; align-items: flex-start;">
                                    <?php
                                    $productImage2 = '';

                                    if ($record['color_id'] && !$record['size_id'])
                                    {
                                        $productImage2 = $product->getColorImage($record['color_id']);
                                    }
                                    else if (!$record['color_id'] && $record['size_id'])
                                    {
                                        $productImage2 = $product->getSizeImage($record['size_id']);
                                    }
                                    else if ($record['color_id'] && $record['size_id'])
                                    {
                                        $productImage2 = $product->getColorSizeImage($record['color_id'], $record['size_id']);
                                        if (!$productImage2)
                                        {
                                            $productImage2 = $product->getColorImage($record['color_id']);
                                        }
                                    }

                                    if (!$productImage2)
                                    {
                                        $productImage2 =  $productDefaultImage->image_thumb_url;
                                    }
                                    else
                                    {
                                        $productImage2 = $productImage2->image_thumb_url;
                                    }
                                    ?>

                                    <img src="{{asset(Storage::url($productImage2))}}" width="90" height="90" style="margin-right: 10px; display: inline-block;">

                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{$productDetail->products_name}}</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.quantity')}}: {{$record->quantity}}</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.price')}}: {{Helper::formatNumber($record->total_price)}} {{$currencyCode}} {{ $style ? '('.$style.')': ''}}</div>
                                        @if($color)  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.color')}}: {{ucfirst($color->color_title)}}</div> @endif
                                        @if($size)  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.size')}}: {{ucfirst($size->size_title)}}</div>@endif
                                    </div>
                                </div>
                                @if($record->is_monogram)
                                <?php
                                $monogramVal = App\UserModel\OrderMonogramDetail::where('order_detail_id', $record->id)->get()->toArray();
                                ?>
                                @foreach($monogramVal as $key =>  $monogramVal)
                                <?php
                                $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                $color = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                ?>
                                <div style="padding: 0 10px; margin-top: 15px; overflow: hidden;">
                                    @if($record->is_same == 1)
                                    <label style="font-family: 'Futura Bk BT'; font-size: 12px; font-weight: bold; color: #000; margin-bottom: 5px; display: block;">SELECTED MONOGRAM: Applied same Monogram Styles to all quantities.</label>
                                    @else
                                    <label style="font-family: 'Futura Bk BT'; font-size: 12px; font-weight: bold; color: #000; margin-bottom: 5px; display: block;">SELECTED MONOGRAM: {{$key + 1}} </label>      
                                    @endif
                                    <div style="padding: 20px 5px; border: 1px solid #d7d7d7; border-radius: 5px;">
                                        <table style="width: 100%; border: none;">
                                            <tr>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">STYLE</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">INITIALS</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">COLOR</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">PLACEMENT</td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"><img style="width: 41px" src="{{asset(Storage::url($font->style_image))}}"></td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;">{{$initials}}</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"> {{$color->color_name}}</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;">{{$placement->placement_desc}}</td>
                                            </tr>
                                        </table>
                                    </div>

                                    @if($monogramVal['monogram_note'])
                                    <div style="clear: both; margin: 10px 0;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; clear: both; margin-bottom: 5px;">
                                            {{$monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:''}}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                                @endif

                                @if($note)
                                <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; clear: both">{{__('web.note')}}: {{$note}}</div>
                                @endif
                            </td>
                            <!-- <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top; text-align: center;">1</td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top; text-align: right; white-space: nowrap;">300.00 KD</td> -->
                        </tr>
                        @endforeach
                        @if($order->is_gift == 1 )
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px; vertical-align: top;" colspan="2">
                                <div style="display: flex; align-items: flex-start;">
                                    <img  src="{{ Helper::getGiftImage() }}" width="90" style="margin-right: 10px; display: inline-block; width: 90px;
                                          height: 90px;">
                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.gift_wrap')}}</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.quantity')}}: 1</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; margin-bottom: 15px;">{{__('web.price')}}: {{$order->gift_wrap? $order->gift_wrap : 0 }}  {{$currencyCode}}</div>
                                        @if($order->gift_title)
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000">{{__('web.title')}}:<br>{{$order->gift_title}}</div>
                                        @endif
                                        @if($order->gift_message)
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000">{{__('web.message')}}:<br>{{$order->gift_message}}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <!-- <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top; text-align: center;">1</td>
                            <td style="padding: 10px; font-size: 14px; color: #4c4c4c; vertical-align: top; text-align: right; white-space: nowrap;">0.00 KD</td> -->
                        </tr>
                        @endif
                        @if($order->is_box == 1)
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px; vertical-align: top;" colspan="2">
                                <div style="display: flex; align-items: flex-start;">
                                    <img  src="{{ Helper::getGiftBoxImage() }}" width="90" style="margin-right: 10px; display: inline-block; width: 90px;
                                          height: 90px;">
                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.gift_box')}}</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.quantity')}}: 1</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; margin-bottom: 15px;">{{__('web.price')}}: {{$order->gift_box? $order->gift_box : 0 }}  {{$currencyCode}}</div>
                                        @if($order->gift_box_title)
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000">{{__('web.title')}}:<br>{{$order->gift_box_title}}</div>
                                        @endif
                                        @if($order->gift_box_message)
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000">{{__('web.message')}}:<br>{{$order->gift_box_message}}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline">{{__('web.subtotal')}}</label>
                                </div>
                            </td>
                            <?php
                            $gift_wrap = $order->gift_wrap ? $order->gift_wrap : 0;
                            $gift_box = $order->gift_box ? $order->gift_box : 0;
                            $subtotal = ($order->order_price * $conversionRate) + $gift_wrap + $gift_box;
                            ?>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;">{{ Helper::formatNumber($subtotal)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label  style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline">{{__('web.shipping_charges')}}</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;">{{Helper::formatNumber($order->delivery_price*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        @if($order->coupon_discount_amount)
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">{{__('web.discount_code')}}: {{$order->coupon_code}}</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;">- {{Helper::formatNumber($order->coupon_discount_amount*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">Total Amount</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;">{{Helper::formatNumber($order->order_total*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">You will receive a notification at your registered email address when the vendor ships your order.</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">The team at {{Helper::getAppName()}} is at your service if you need any further assistance regarding the above order. Please drop us an email at <a href="mailto:{{$customerCareEmail}}" style="color: #caa42f;">{{$customerCareEmail}}</a></p>
                </div>
                <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">Thank you for shopping with {{Helper::getAppName()}}!</p>
                <div style="color: #caa42f;">
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">Best Wishes,<br>{{Helper::getAppName()?Helper::getAppName():''}} Team</p>
                </div>
            </div>
            <div style="background-image: url({{asset('front/img/footer.png')}}); background-size: auto 100%; background-repeat: no-repeat; background-position: center; height: 137px; width: 100%; max-width: 100%;"></div>
            <?php
            $setting = Helper::getSetting();
            $facebookLink = isset($setting[3]) ? $setting[3]->setting_val : "";
            $instagramLink = isset($setting[5]) ? $setting[5]->setting_val : "";
            $twitterLink = isset($setting[19]) ? $setting[19]->setting_val : "";
            $whatsappLink = isset($setting[6]) ? $setting[6]->setting_val : "";
            ?>
            <div style="padding: 20px; text-align: center; font-size: 13px;">
                <span style="display: block; font-family: 'Futura Bk BT'; font-size: 14px; color: #000">ALL RIGHTS RESERVED.</span>
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

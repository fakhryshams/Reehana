<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Rejected</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
    </style>
</head>
<body style="background: #fff;">
    <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; background: #ffffff;">
        <div style="padding: 20px 0;">
            <img src="https://reehana.com/front/img/logo.png" alt="REEHANA">
        </div>
        <?php
        $language = Helper::getLanguage();
        $payment_method = Helper::getPaymentMethodByID($order->payment_method);
        ?>
        <div style="padding: 0 10px; border-top: 1px solid #d7d7d7;">
            <div>
                <h2 style="font-family: 'Futura Bk BT'; font-size: 24px; margin-bottom: 20px;">Payment Rejected</h2>
                <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">
                    We regret to inform you that the payment for Order #{{$order->id}} has been rejected.
                </p>

                <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;">{{__('web.order').' #'.$order->id}} Details:</p>
                <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;">{{__('web.payment_method')}} : <span>{{$payment_method}}</span></p>
                
                @if(isset($payment_response) && !empty($payment_response))
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px;">Reason: {{ $payment_response['result'] ?? $payment_response['message'] ?? 'Transaction Failed' }}</p>
                @endif
            </div>
            
            @if($shipping_address && $order->order_type != 'custom')
            <div style="border: 1px solid #dfdfdf; margin: 30px 0; background: #fff;">
                <div style="padding: 10px; background: #f0f0f0;"><span>{{__('web.delivery_address')}}</span></div>
                <div style="padding: 10px;">
                    {!! Helper::showAddress('mail',$shipping_address) !!}
                </div>
            </div>
            @elseif($shipping_address && $order->order_type == 'custom')
            <div style="border: 1px solid #dfdfdf; margin: 30px 0; background: #fff;">
                <div style="padding: 10px; background: #f0f0f0;"><span>{{__('web.delivery_address')}}</span></div>
                <div style="padding: 10px;">
                    <address>
                        @if(isset($shipping_address->block) && $shipping_address->block)<div><strong>Appartment :</strong> {{ $shipping_address->block }}</div>@endif
                        @if(isset($shipping_address->floor) && $shipping_address->floor)<div><strong>Floor :</strong> {{ $shipping_address->floor }}</div>@endif
                        @if(isset($shipping_address->house_number) && $shipping_address->house_number)<div><strong>House Number :</strong> {{ $shipping_address->house_number }}</div>@endif
                        @if(isset($shipping_address->city) && $shipping_address->city)<div><strong>City :</strong> {{ ucfirst($shipping_address->city) }}</div>@endif
                        @if($shipping_address->country_name)<div><strong>Country :</strong> {{ $shipping_address->country_name }}</div>@endif
                        @if(isset($shipping_address->address_detail) && $shipping_address->address_detail)<div><strong>Address Detail :</strong> {{ $shipping_address->address_detail }}</div>@endif
                        @if(isset($shipping_address->email) && $shipping_address->email)<strong>{{__('admin.email')}} : </strong>{{ $shipping_address->email ?? '' }}<br>@endif
                        @if(isset($shipping_address->phone) && $shipping_address->phone)<strong>{{__('admin.phone')}} : </strong>{{ $shipping_address->phone ?? '' }}<br>@endif
                    </address> 
                </div>
            </div>
            @endif


            <div style="border: 1px solid #dfdfdf; margin-bottom: 30px;">
                <table style="border-collapse: collapse; width: 100%; background: #fff;">
                    <tr>
                        <th style="padding: 10px; text-align: left; background: #f0f0f0; font-weight: normal;" colspan="2">Product &amp; Description</th>
                    </tr>
                    <?php
                    $currency = Helper::getDefaultCurrency();
                    $currencyCode = $currency ? $currency->code : config('custom.currency');
                    $conversionRate = $currency ? $currency->value : 1;
                    ?>
                    @if($orderDetail && $order->order_type != 'custom')
                    @foreach($orderDetail as $record)
                    <?php
                    $product = $record->product_id ? $record->getProductDetail() : null;
                    
                    // Handle Regular Product
                    if ($product) {
                        $productDetail = $product->getProductDescription($language->id);
                        $productDefaultImage = $product->getProductImages(1);
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
                        }

                        if (!$productImage2)
                        {
                            $productImage2 =  $productDefaultImage ? $productDefaultImage->image_thumb_url : 'default.png';
                        }
                        else
                        {
                            $productImage2 = is_object($productImage2) ? $productImage2->image_thumb_url : $productImage2;
                        }
                            $displayImage = asset(Storage::url($productImage2));
                            $displayName = $productDetail->products_name;
                    } 
                    // Handle Custom Item (No Product ID)
                    else {
                        $displayImage = asset('front/img/logo.png'); // Fallback or placeholder
                        $displayName = Helper::formatCustomizedTitle($record->product_name ?? 'Custom Item'); // Check if product_name saved in OrderDetail
                    }
                    
                    $sizeId = $record['size_id'];
                    $color_id = $record['color_id'];
                    $size = Helper::getSize($sizeId, $language->id);
                    $color = Helper::getColorName($color_id, $language->id);
                    $note = $record['note'];
                    $style = '';
                    
                    if($product) {
                        $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);
                        if ($attributePrice)
                        {
                            $style = $attributePrice->style;
                        }
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
                                <img src="{{$displayImage}}" width="90" height="90" style="margin-right: 10px; display: inline-block;">

                                <div style="display: inline-block; line-height: 1.5em;">
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{$displayName}}</div>
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.quantity')}}: {{$record->quantity}}</div>
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.price')}}: {{Helper::formatNumber($record->total_price)}} {{$currencyCode}} {{ $style ? '('.$style.')': ''}}</div>
                                    @if($color)  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.color')}}: {{ucfirst($color->color_title)}}</div> @endif
                                    @if($size)  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.size')}}: {{ucfirst($size->size_title)}}</div>@endif
                                    
                                    {{-- Custom Attributes if available --}}
                                    @if(!empty($record->custom_attributes))
                                        <?php $cAttribs = json_decode($record->custom_attributes, true); ?>
                                            @if(isset($cAttribs['item_code']) && $cAttribs['item_code']) <div>Code: {{$cAttribs['item_code']}}</div> @endif
                                            @if(isset($cAttribs['description']) && $cAttribs['description']) <div>{{Helper::formatCustomizedDescription($cAttribs['description'])}}</div> @endif
                                    @endif
                                </div>
                            </div>
                            
                            @if($record->is_monogram && $product) 
                            {{-- Monogram Logic Only for Products --}}
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
                    </tr>
                    @endforeach
                    @endif
                    
                    {{-- Logic for Sub-Order (Custom) in Email --}}
                    <?php
                        $subOrderEmail = \App\UserModel\Order::where('parent_id', $order->id)->where('order_type', 'custom')->first();
                        if(!$subOrderEmail && $order->order_type == 'custom') {
                            $subOrderEmail = $order; // If main order is custom, use it as sub order for details
                        }
                    ?>
                    @if($subOrderEmail)
                            <?php $customDetails = json_decode($subOrderEmail->custom_order_details, true); ?>
                            @if(isset($customDetails['items']) && is_array($customDetails['items']))
                            <tr>
                                <th style="padding: 10px; text-align: left; background: #e0e0e0; font-weight: bold;" colspan="2">Custom Order Details</th>
                            </tr>
                            @foreach($customDetails['items'] as $item)
                            <tr style="border-bottom: 1px solid #eee8e0;">
                                <td style="padding: 10px; vertical-align: top;" colspan="2">
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{ Helper::formatCustomizedTitle($item['product_title'] ?? $item['description'] ?? 'Custom Item') }}</div>
                                    @if(!empty($item['description']))
                                    <div style="font-family: 'Futura Bk BT'; font-size: 13px; color: #555">{{ Helper::formatCustomizedDescription($item['description']) }}</div>
                                    @endif
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.quantity')}}: {{ $item['quantity'] ?? 1 }}</div>
                                    <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">{{__('web.price')}}: {{ Helper::formatNumber($item['total'] ?? 0) }} {{$currencyCode}}</div>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                    @endif

                    @if($order->is_gift == 1 )
                    <tr style="border-bottom: 1px solid #eee8e0;">
                        <td style="padding: 10px; vertical-align: top;" colspan="2">
                            <div style="display: flex; align-items: flex-start;">
                                <img  src="{{asset('front/img/giftwrap.png')}}" width="90" style="margin-right: 10px; display: inline-block; width: 90px;
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
                    </tr>
                    @endif

                    @if($order->is_gift_box == 1)
                    <?php
                    $settingObj = \App\Helpers\Helper::getSetting();
                    $giftBoxImageSetting = $settingObj->firstWhere('setting_key', 'Gift Box Image');
                    $giftBoxImageUrl = $giftBoxImageSetting && $giftBoxImageSetting->setting_val
                        ? asset(Storage::url($giftBoxImageSetting->setting_val))
                        : asset('front/img/giftwrap.png');
                    ?>
                    <tr style="border-bottom: 1px solid #eee8e0;">
                        <td style="padding: 10px; vertical-align: top;" colspan="2">
                            <div style="display: flex; align-items: flex-start;">
                                <img src="{{$giftBoxImageUrl}}" width="90" style="margin-right: 10px; display: inline-block; width: 90px; height: 90px;">
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
                    
                    {{-- Custom Order (Sub-Order) Totals --}}
                    @if($subOrderEmail)
                            <?php
                                $customTotal = $subOrderEmail->order_total;
                                $customAdvance = $subOrderEmail->paid_amount;
                                $customPending = $subOrderEmail->pending_amount;
                            ?>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">Total Amount (Custom)</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;">{{Helper::formatNumber($customTotal*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">{{__('web.advance_payment')}}</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;">{{Helper::formatNumber($customAdvance*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        @if($customPending > 0)
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">{{__('web.due_amount')}}</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: red; display: inline-block; white-space: nowrap;">{{Helper::formatNumber($customPending*$conversionRate)}} {{$currencyCode}}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endif

                </table>
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
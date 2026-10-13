<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: <?php echo e(__('web.orders')); ?> | <?php echo e(Helper::getAppName()?Helper::getAppName():''); ?>  :::</title>

        <link rel="stylesheet" href="<?php echo e(asset('front/css/fonts.css')); ?>">
    </head>
    <body style="background: #fff; font-family: 'Futura Bk BT'; font-size: 14px; color: #000000; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; background: #ffffff;">
            <div style="padding: 20px 0;">
                <img src="https://reehana.com/front/img/logo.png" alt="REEHANA">
            </div>
            <?php
            $language = Helper::getLanguage();
            $payment_method = Helper::getPaymentMethodByID($order->payment_method);
            $country = $shipping_address->getUserCountry($language->id);
            ?>
            <div style="padding: 0 10px; border-top: 1px solid #d7d7d7;">
                <div>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.dear')); ?> <?php echo e($shipping_address->first_name); ?>,</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 22px; line-height: 30px;"><?php echo e(__('web.thankyou_for_shopping').' '. strtoupper(Helper::getAppName())); ?></p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;"><?php echo e(__('web.order').' #'.$order->id.' '. __('web.has_been_placed')); ?> <?php echo e(date("d M Y", strtotime($order->created_at)).'. '. __('web.order_deatil_below')); ?> :</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;"><?php echo e(__('web.payment_method')); ?> : <span><?php echo e($payment_method); ?></span></p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;"><?php echo e(__('web.estimated_delivery')); ?> : <span><?php echo e($country->delivery_time); ?> Days</span></p>
                </div>
                <div style="border: 1px solid #dfdfdf; margin: 30px 0; background: #fff;">
                    <div style="padding: 10px; background: #f0f0f0;"><span><?php echo e(__('web.delivery_address')); ?></span></div>
                    <div style="padding: 10px;">
                        <?php echo Helper::showAddress('mail',$shipping_address); ?>

                    </div>
                </div>


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
                        <?php $__currentLoopData = $orderDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                $productImage2 = $productDefaultImage ? $productDefaultImage->image_thumb_url : '';
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
                                    <img src="<?php echo e($displayImage); ?>" width="90" height="90" style="margin-right: 10px; display: inline-block;">

                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e($displayName); ?></div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.quantity')); ?>: <?php echo e($record->quantity); ?></div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.price')); ?>: <?php echo e(Helper::formatNumber($record->total_price)); ?> <?php echo e($currencyCode); ?> <?php echo e($style ? '('.$style.')': ''); ?></div>
                                        <?php if($color): ?>  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.color')); ?>: <?php echo e(ucfirst($color->color_title)); ?></div> <?php endif; ?>
                                        <?php if($size): ?>  <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.size')); ?>: <?php echo e(ucfirst($size->size_title)); ?></div><?php endif; ?>
                                        
                                        
                                        <?php if(!empty($record->custom_attributes)): ?>
                                            <?php $cAttribs = json_decode($record->custom_attributes, true); ?>
                                             <?php if(isset($cAttribs['item_code']) && $cAttribs['item_code']): ?> <div>Code: <?php echo e($cAttribs['item_code']); ?></div> <?php endif; ?>
                                             <?php if(isset($cAttribs['description']) && $cAttribs['description']): ?> <div><?php echo e(Helper::formatCustomizedDescription($cAttribs['description'])); ?></div> <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if($record->is_monogram && $product): ?> 
                                
                                <?php
                                $monogramVal = App\UserModel\OrderMonogramDetail::where('order_detail_id', $record->id)->get()->toArray();
                                ?>
                                <?php $__currentLoopData = $monogramVal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>  $monogramVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                $color = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                ?>
                                <div style="padding: 0 10px; margin-top: 15px; overflow: hidden;">
                                    <?php if($record->is_same == 1): ?>
                                    <label style="font-family: 'Futura Bk BT'; font-size: 12px; font-weight: bold; color: #000; margin-bottom: 5px; display: block;">SELECTED MONOGRAM: Applied same Monogram Styles to all quantities.</label>
                                    <?php else: ?>
                                    <label style="font-family: 'Futura Bk BT'; font-size: 12px; font-weight: bold; color: #000; margin-bottom: 5px; display: block;">SELECTED MONOGRAM: <?php echo e($key + 1); ?> </label>      
                                    <?php endif; ?>
                                    <div style="padding: 20px 5px; border: 1px solid #d7d7d7; border-radius: 5px;">
                                        <table style="width: 100%; border: none;">
                                            <tr>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">STYLE</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">INITIALS</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">COLOR</td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 12px; color: #aaa; text-align: center; width: 20%;">PLACEMENT</td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"><img style="width: 41px" src="<?php echo e(asset(Storage::url($font->style_image))); ?>"></td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"><?php echo e($initials); ?></td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"> <?php echo e($color->color_name); ?></td>
                                                <td style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; font-size: 14px; text-align: center;"><?php echo e($placement->placement_desc); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <?php if($monogramVal['monogram_note']): ?>
                                    <div style="clear: both; margin: 10px 0;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; clear: both; margin-bottom: 5px;">
                                            <?php echo e($monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:''); ?>

                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                                <?php if($note): ?>
                                <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; clear: both"><?php echo e(__('web.note')); ?>: <?php echo e($note); ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        
                        <?php
                            $subOrderEmail = \App\UserModel\Order::where('parent_id', $order->id)->where('order_type', 'custom')->first();
                        ?>
                        <?php if($subOrderEmail): ?>
                             <?php $customDetails = json_decode($subOrderEmail->custom_order_details, true); ?>
                             <?php if(isset($customDetails['items']) && is_array($customDetails['items'])): ?>
                                <tr>
                                    <th style="padding: 10px; text-align: left; background: #e0e0e0; font-weight: bold;" colspan="2">Custom Order Details</th>
                                </tr>
                                <?php $__currentLoopData = $customDetails['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr style="border-bottom: 1px solid #eee8e0;">
                                    <td style="padding: 10px; vertical-align: top;" colspan="2">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(Helper::formatCustomizedTitle($item['product_title'] ?? $item['description'] ?? 'Custom Item')); ?></div>
                                        <?php if(!empty($item['description'])): ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 13px; color: #555"><?php echo e(Helper::formatCustomizedDescription($item['description'])); ?></div>
                                        <?php endif; ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.quantity')); ?>: <?php echo e($item['quantity'] ?? 1); ?></div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.price')); ?>: <?php echo e(Helper::formatNumber($item['total'] ?? 0)); ?> <?php echo e($currencyCode); ?></div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                             <?php endif; ?>
                        <?php endif; ?>

                        <?php if($order->is_gift == 1 ): ?>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px; vertical-align: top;" colspan="2">
                                <div style="display: flex; align-items: flex-start;">
                                    <img  src="<?php echo e(asset('front/img/giftwrap.png')); ?>" width="90" style="margin-right: 10px; display: inline-block; width: 90px;
                                          height: 90px;">
                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.gift_wrap')); ?></div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.quantity')); ?>: 1</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; margin-bottom: 15px;"><?php echo e(__('web.price')); ?>: <?php echo e($order->gift_wrap? $order->gift_wrap : 0); ?>  <?php echo e($currencyCode); ?></div>
                                        <?php if($order->gift_title): ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000"><?php echo e(__('web.title')); ?>:<br><?php echo e($order->gift_title); ?></div>
                                        <?php endif; ?>
                                        <?php if($order->gift_message): ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000"><?php echo e(__('web.message')); ?>:<br><?php echo e($order->gift_message); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if($order->is_gift_box == 1): ?>
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
                                    <img src="<?php echo e($giftBoxImageUrl); ?>" width="90" style="margin-right: 10px; display: inline-block; width: 90px; height: 90px;">
                                    <div style="display: inline-block; line-height: 1.5em;">
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.gift_box')); ?></div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000"><?php echo e(__('web.quantity')); ?>: 1</div>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; margin-bottom: 15px;"><?php echo e(__('web.price')); ?>: <?php echo e($order->gift_box? $order->gift_box : 0); ?>  <?php echo e($currencyCode); ?></div>
                                        <?php if($order->gift_box_title): ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000"><?php echo e(__('web.title')); ?>:<br><?php echo e($order->gift_box_title); ?></div>
                                        <?php endif; ?>
                                        <?php if($order->gift_box_message): ?>
                                        <div style="font-family: 'Futura Bk BT'; font-size: 15px; color: #000"><?php echo e(__('web.message')); ?>:<br><?php echo e($order->gift_box_message); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline"><?php echo e(__('web.subtotal')); ?></label>
                                </div>
                            </td>
                            <?php
                            $gift_wrap = $order->gift_wrap ? $order->gift_wrap : 0;
                            $gift_box = $order->gift_box ? $order->gift_box : 0;
                            $subtotal = ($order->order_price * $conversionRate) + $gift_wrap + $gift_box;
                            ?>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($subtotal)); ?> <?php echo e($currencyCode); ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label  style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline"><?php echo e(__('web.shipping_charges')); ?></label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($order->delivery_price*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php if($order->coupon_discount_amount): ?>
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline"><?php echo e(__('web.discount_code')); ?>: <?php echo e($order->coupon_code); ?></label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000; display: inline-block; white-space: nowrap;">- <?php echo e(Helper::formatNumber($order->coupon_discount_amount*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr style="border-bottom: 1px solid #eee8e0;">
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline">Total Amount</label>
                                </div>
                            </td>
                            <td style="padding: 10px;">
                                <div style="text-align: right; font-size: 14px;">
                                    <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($order->order_total*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                </div>
                            </td>
                        </tr>
                        
                        
                        <?php if($subOrderEmail): ?>
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
                                        <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($customTotal*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee8e0;">
                                <td style="padding: 10px;">
                                    <div style="text-align: right; font-size: 14px;">
                                        <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline"><?php echo e(__('web.advance_payment')); ?></label>
                                    </div>
                                </td>
                                <td style="padding: 10px;">
                                    <div style="text-align: right; font-size: 14px;">
                                        <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($customAdvance*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php if($customPending > 0): ?>
                            <tr style="border-bottom: 1px solid #eee8e0;">
                                <td style="padding: 10px;">
                                    <div style="text-align: right; font-size: 14px;">
                                        <label style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000;display: inline"><?php echo e(__('web.due_amount')); ?></label>
                                    </div>
                                </td>
                                <td style="padding: 10px;">
                                    <div style="text-align: right; font-size: 14px;">
                                        <span style="font-family: 'Futura Bk BT'; font-size: 14px; color: red; display: inline-block; white-space: nowrap;"><?php echo e(Helper::formatNumber($customPending*$conversionRate)); ?> <?php echo e($currencyCode); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endif; ?>

                    </table>
                </div>
                <div>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">You will receive a notification at your registered email address when the vendor ships your order.</p>
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">The team at <?php echo e(Helper::getAppName()); ?> is at your service if you need any further assistance regarding the above order. Please drop us an email at <a href="mailto:<?php echo e($customerCareEmail); ?>" style="color: #caa42f;"><?php echo e($customerCareEmail); ?></a></p>
                </div>
                <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">Thank you for shopping with <?php echo e(Helper::getAppName()); ?>!</p>
                <div style="color: #caa42f;">
                    <p style="font-family: 'Futura Bk BT'; font-size: 14px; color: #000">Best Wishes,<br><?php echo e(Helper::getAppName()?Helper::getAppName():''); ?> Team</p>
                </div>
            </div>
            <div style="background-image: url(<?php echo e(asset('front/img/footer.png')); ?>); background-size: auto 100%; background-repeat: no-repeat; background-position: center; height: 137px; width: 100%; max-width: 100%;"></div>
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
                    <?php if($instagramLink): ?>
                    <span  style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="<?php echo e($instagramLink); ?>" target="_blank"><img src="<?php echo e(asset('images/mail/in.png')); ?>"></a></span>
                    <?php endif; ?>
                    <?php if($whatsappLink): ?>
                    <span  style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="<?php echo e($whatsappLink); ?>" target="_blank"><img src="<?php echo e(asset('images/mail/wa.png')); ?>"></a></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/emails/thank_you/index.blade.php ENDPATH**/ ?>
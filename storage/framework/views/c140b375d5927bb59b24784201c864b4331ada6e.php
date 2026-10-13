<html lang="<?php echo e(Helper::getLanguage() ? Helper::getLanguage()->code : 'en'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="generator" content="<?php echo e(Helper::getAppName() ? Helper::getAppName() : ''); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="<?php echo e(asset('front/img/favicon.png')); ?>" type="image/x-icon">
    <meta name="description" content="<?php echo e(Helper::getAppName() ? Helper::getAppName() : ''); ?>">
    <?php echo $__env->yieldContent('page_meta'); ?>
    <link rel="manifest" href="<?php echo e(asset('front/css/manifest.json')); ?>">

    <title><?php echo e(Helper::getAppName() ? Helper::getAppName() . " | " : ''); ?> <?php echo $__env->yieldContent('page_title'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('front/css/bootstrap-reboot.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/fontawesome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/slick.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/jquery-ui.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/cloudzoom.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/fonts.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/main.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('front/css/media.css')); ?>">
    <style>
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
        @media  screen and (max-width: 767px) {
            .order-details > .w-33,
            .order-details > div {
                flex: 1 1 100% !important;
                max-width: 100% !important;
                min-width: 100% !important;
            }
        }
    </style>
</head>
<?php
$address = $order->getUserAddress();
$language = Helper::getLanguage();
$paymentMethod = Helper::getPaymentMethodByID($order->payment_method, $language->id);
$total = 0;
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
if ($order->currency_code) {
    $currencyCode = $order->currency_code;
}
// if ($order->currency_rate) {
//     $order->order_price = $order->order_price * $order->currency_rate;
//     $order->coupon_discount_amount = $order->coupon_discount_amount * $order->currency_rate;
//     $order->delivery_price = $order->delivery_price * $order->currency_rate;
//     $order->discount = $order->discount * $order->currency_rate;
//     $order->order_total = $order->order_total * $order->currency_rate;
// }
$orderDetail = $order->getOrderDetail(true);
$deliveryTime = config('custom.default_delivery_time');
?>
<?php
$customOrderDetails = json_decode($order->custom_order_details, true);
$customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
?>
<body class="menubg">
    <img style=" display: block; margin-top: 10px; margin-bottom: 40px; margin-left: auto;margin-right: auto;  width: 245px; height: 56px;"
        src="<?php echo e(asset('front/img/logo.png')); ?>">
    <div class="">
        <section class="order">
            <div class="confirmation">
                <?php if(isset($payment_link) && $payment_link->status == 'paid'): ?>
                    <div class="container text-center">
                        <p><img src="<?php echo e(asset('front/img/icon-cart.svg')); ?>" width="40"></p>
                        <p class="line1"><?php echo e(__('web.thank_you_order_message')); ?></p>
                        <p class="line2"><?php echo e(__('web.order_has_succ_placed')); ?>.</p>
                        <?php    $address = $order->getUserAddress(); ?>
                        <?php if(isset($customerInfo['email'])): ?><p class="line3"><?php echo e(__('web.copy_of_order_mail')); ?> <?php echo e($customerInfo['email'] ?? ''); ?>.
                            <?php echo e(__('web.if_not_get_mail')); ?>.
                        </p><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="container text-center">
                        <p><img src="<?php echo e(asset('front/img/icon-cart.svg')); ?>" width="40"></p>
                        <p class="line1"><?php echo e(__('web.your_order_failed')); ?></p>
                        <p class="line2"><?php echo e(__('web.unfortunately_your_payment_could_not_be_processed_at_this_time')); ?>

                            <?php echo e(__('web.please_try_again_after_some_time_or_use_a_different_payment_method')); ?>

                        </p>
                        <p class="line3"><?php echo e(__('web.if_the_issue_continues_please_reach_out_to_us_at')); ?> <a
                                href="mailto:info@reehana.com" style="color: #caa42f;">info@reehana.com</a>.</p>
                    </div>
                <?php endif; ?>
                <div class="container">
                    <div class="payment-info">
                        <h4><?php echo e(__('web.payment_info')); ?></h4>
                        <p class="mont">
                            <span><?php echo e(__('web.po_no')); ?>.: #<?php echo e($order->id); ?></span>
                            <span>Date: <?php echo e(date("d M, Y", strtotime($order->created_at))); ?></span>
                        </p>
                        <div class="instruction">
                            <?php
$paymentResponse = $order->payment_response;
$paymentId = "";
if ($paymentResponse) {
    $paymentResponse = json_decode($paymentResponse);
    $paymentId = isset($paymentResponse->PaymentID) ? $paymentResponse->PaymentID : (isset($paymentResponse->payment_id) ? $paymentResponse->payment_id : '');
}
                            ?>
                            <div class="row">
                                <div class="<?php echo e($order->transaction_id? 'col-md-3' : ' col-md-6'); ?>">
                                    <span><?php echo e(__('web.payment_method')); ?></span>
                                    <label><?php echo e($paymentMethod); ?></label>
                                </div>
                                <?php if($order->transaction_id): ?>
                                    <div class="col-md-3">
                                        <span><?php echo e(__('web.payment_id')); ?></span>
                                        <label><?php echo e($paymentId); ?></label>
                                    </div>

                                    <div class="col-md-3">
                                        <span><?php echo e(__('web.transaction_id')); ?></span>
                                        <label><?php echo e($order->transaction_id ? $order->transaction_id : '-'); ?></label>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <span><?php echo e(__('web.date_time')); ?></span>
                                    <label><?php echo e(date("d M Y h:i A", strtotime($order->created_at))); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
$conversationRate = 1;
if ($currency && $order->currency_code) {
    $currency->code = $order->currency_code;
    $conversationRate = $order->currency_rate;
} else {
    $order->currency_code = config('custom.currency_convert_default_country');
    $currency->code = config('custom.currency_convert_default_country');
}
                    ?>
                    <div class="prod-summary">
                        <style>
                            .summary-table-head th {
                                text-transform: uppercase;
                                font-size: 11px;
                                line-height: 1.2;
                                letter-spacing: 0;
                                font-weight: 700;
                                white-space: nowrap;
                            }

                            .price-amount {
                                white-space: nowrap;
                                display: inline-block;
                            }

                            .item-description {
                                margin-top: 4px;
                                font-size: 12px;
                                line-height: 1.35;
                                color: #666;
                            }

                            .qty-cell {
                                text-align: center;
                            }
                        </style>
                        <h4><?php echo e(__('web.customized_items')); ?></h4>
                        <div class="cart-table-container">
                            <?php
                            $showItemCode = false;
                            $showColor = false;
                            $showSize = false;
                            foreach ($orderProductDetail as $r) {
                                $attrs = [];
                                if (!empty($r->custom_attributes)) {
                                    $attrs = json_decode($r->custom_attributes, true);
                                    if (!is_array($attrs)) {
                                        $attrs = [];
                                    }
                                }
                                if (!empty($attrs['item_code'])) {
                                    $showItemCode = true;
                                }
                                if (!empty($attrs['color'])) {
                                    $showColor = true;
                                }
                                if (!empty($attrs['size'])) {
                                    $showSize = true;
                                }
                            }
                            $totalCols = 4 + ($showColor ? 1 : 0) + ($showSize ? 1 : 0) + ($showItemCode ? 1 : 0);
                            $imageColPct = 12;
                            $itemColPct = 25;
                            $rightColCount = 3 + ($showItemCode ? 1 : 0) + ($showColor ? 1 : 0) + ($showSize ? 1 : 0);
                            $rightColPct = round((100 - $imageColPct - $itemColPct) / $rightColCount, 2);
                            $visibleOrderProductDetail = collect($orderProductDetail)->map(function ($record, $recordIndex) use ($customOrderDetails) {
                                $customAttributes = [];
                                if (!empty($record->custom_attributes)) {
                                    $customAttributes = json_decode($record->custom_attributes, true);
                                    if (!is_array($customAttributes)) {
                                        $customAttributes = [];
                                    }
                                }

                                $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                                $isPlaceholderName = trim(strtolower((string) $record->product_name)) === 'custom item';
                                $hasData = (!empty($record->product_name) && !$isPlaceholderName)
                                    || !empty($customAttributes['item_code'])
                                    || !empty($customAttributes['description'])
                                    || !empty($itemImage)
                                    || !empty($customAttributes['color'])
                                    || !empty($customAttributes['size'])
                                    || !empty($record->quantity)
                                    || !empty($record->product_price)
                                    || !empty($record->total_price);

                                return [
                                    'record' => $record,
                                    'recordIndex' => $recordIndex,
                                    'hasData' => $hasData ? 1 : 0,
                                ];
                            })->sort(function ($a, $b) {
                                if ($a['hasData'] === $b['hasData']) {
                                    return $a['recordIndex'] <=> $b['recordIndex'];
                                }

                                return $a['hasData'] <=> $b['hasData'];
                            })->values()->all();
                            ?>
                            <table class="cart-list-table" style="table-layout:fixed; width:100%;">
                                <colgroup>
                                    <col style="width:<?php echo e($imageColPct); ?>%;">
                                    <col style="width:<?php echo e($itemColPct); ?>%;">
                                    <?php if($showItemCode): ?><col style="width:<?php echo e($rightColPct); ?>%;"><?php endif; ?>
                                    <?php if($showColor): ?><col style="width:<?php echo e($rightColPct); ?>%;"><?php endif; ?>
                                    <?php if($showSize): ?><col style="width:<?php echo e($rightColPct); ?>%;"><?php endif; ?>
                                    <col style="width:<?php echo e($rightColPct); ?>%;">
                                    <col style="width:<?php echo e($rightColPct); ?>%;">
                                    <col style="width:<?php echo e($rightColPct); ?>%;">
                                </colgroup>
                                <thead style="display:table-header-group;">
                                <tr class="sm-hidden summary-table-head">
                                    <th class="text-center"><?php echo e(__('admin.image')); ?></th>
                                    <th>Item &amp; Description</th>
                                    <?php if($showItemCode): ?><th><?php echo e(__('web.item_code')); ?></th><?php endif; ?>
                                    <?php if($showColor): ?><th><?php echo e(__('web.color')); ?></th><?php endif; ?>
                                    <?php if($showSize): ?><th><?php echo e(__('web.size')); ?></th><?php endif; ?>
                                    <th><?php echo e(__('web.price')); ?></th>
                                    <th class="text-center"><?php echo e(__('web.quantity')); ?></th>
                                    <th><?php echo e(__('web.total_price')); ?></th>
                                </tr>
                                </thead>
                                <?php $productPrice = []; ?>
                                <?php $__empty_1 = true; $__currentLoopData = $visibleOrderProductDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                    $record = $row['record'];
                                    $recordIndex = $row['recordIndex'];
                                    $pricetotal = $record->quantity * $record->product_price;
                                    $customAttributes = [];
                                    if (!empty($record->custom_attributes)) {
                                        $customAttributes = json_decode($record->custom_attributes, true);
                                        if (!is_array($customAttributes)) {
                                            $customAttributes = [];
                                        }
                                    }
                                    $productName = Helper::formatCustomizedTitle(
                                        $record->product_name
                                        ?: ($customAttributes['item_code'] ?? null)
                                        ?: ($customAttributes['description'] ?? null)
                                        ?: 'Custom Item'
                                    );
                                    $itemImage = $customAttributes['image'] ?? ($customOrderDetails['items'][$recordIndex]['image'] ?? null);
                                    $itemImage = $itemImage ? preg_replace('/^public\//', '', $itemImage) : null;
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <a href="<?php echo e($itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png'))); ?>" target="_blank" style="display:inline-flex;justify-content:center;align-items:center;">
                                                <img src="<?php echo e($itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png'))); ?>" alt="Reference Image" style="width:56px;height:56px;object-fit:cover;border:1px solid #ddd;border-radius:6px;padding:2px;" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                            </a>
                                        </td>
                                        <td>
                                            <div class="prod-name"><?php echo e($productName); ?></div>
                                            <?php if(!empty($customAttributes['description'])): ?>
                                                <div class="item-description"><?php echo e(Helper::formatCustomizedDescription($customAttributes['description'])); ?></div>
                                            <?php endif; ?>
                                            <div class="price mont sm-visible">
                                                <?php if($record->is_active): ?>
                                                    <span class="price-amount"><?php echo e(Helper::formatNumber($record->total_price)); ?> <?php echo e($currency->code); ?></span>
                                                <?php else: ?>
                                                    <s><span class="price-amount"><?php echo e(Helper::formatNumber($record->total_price)); ?> <?php echo e($currency->code); ?></span></s>
                                                <?php endif; ?>
                                            </div>
                                            <div class="sm-visible form-item">
                                                <label><?php echo e(__('web.quantity')); ?></label>
                                                <span><?php echo e($record->quantity); ?></span>
                                            </div>
                                        </td>
                                        <?php if($showItemCode): ?>
                                            <td class="barcode-cell"><?php echo e($customAttributes['item_code'] ?? ''); ?></td>
                                        <?php endif; ?>
                                        <?php if($showColor): ?>
                                            <td><?php echo e($customAttributes['color'] ?? ''); ?></td>
                                        <?php endif; ?>
                                        <?php if($showSize): ?>
                                            <td><?php echo e($customAttributes['size'] ?? ''); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="price-amount"><?php echo e(Helper::formatNumber($record->product_price)); ?> <?php echo e($currency->code); ?></span>
                                        </td>
                                        <td class="text-center qty-cell">
                                            <span><?php echo e($record->quantity); ?></span>
                                        </td>
                                        <td>
                                            <span class="price-amount"><?php echo e(Helper::formatNumber($record->total_price)); ?> <?php echo e($currency->code); ?></span>
                                        </td>
                                    </tr>
                                    <?php if ($record->is_active) { $productPrice[] = $pricetotal; } ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e($totalCols); ?>" class="text-center">No items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <div class="order-details clearfix">
                        <?php
$customOrderDetails = json_decode($order->custom_order_details, true);
$customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
                        ?>
                        <div class="w-33">
                            <div class="billing-summary">
                                <h4><?php echo e(__('web.billing_details')); ?></h4>
                                <div class="address-item selected">
                                    <label class="contact">
                                        <?php if($customerInfo): ?>
                                                                                <div class="contact-name">
                                                                                    <span>
                                                                                        <?php echo e(isset($customerInfo['name']) ? ucfirst($customerInfo['name']) : ''); ?>

                                                                                    </span>
                                                                                </div>
                                                                                <div class="contact-address">
                                                                                    <address>
                                                                                        <?php
                                            $cust = $customerInfo;
                                            // Country name lookup
                                            $countryName = '';
                                            if (isset($cust['country_id']) && $cust['country_id']) {
                                                $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                                                if ($cn)
                                                    $countryName = $cn->country_name;
                                            }
                                                                                                                                ?>
                                                                                        <?php if(isset($cust['block']) && $cust['block']): ?>
                                                                                        <div><strong>Appartment :</strong> <?php echo e(e($cust['block'])); ?></div><?php endif; ?>
                                                                                        <?php if(isset($cust['floor']) && $cust['floor']): ?>
                                                                                        <div><strong>Floor :</strong> <?php echo e(e($cust['floor'])); ?></div><?php endif; ?>
                                                                                        <?php if(isset($cust['house_number']) && $cust['house_number']): ?>
                                                                                            <div><strong>House Number :</strong> <?php echo e(e($cust['house_number'])); ?>

                                                                                        </div><?php endif; ?>
                                                                                        <?php if(isset($cust['city']) && $cust['city']): ?>
                                                                                        <div><strong>City :</strong> <?php echo e(e(ucfirst($cust['city']))); ?></div><?php endif; ?>
                                                                                        <?php if($countryName): ?>
                                                                                        <div><strong>Country :</strong> <?php echo e(e($countryName)); ?></div><?php endif; ?>
                                                                                        <?php if(isset($cust['address_detail']) && $cust['address_detail']): ?>
                                                                                            <div><strong>Address Detail :</strong> <?php echo e(e($cust['address_detail'])); ?>

                                                                                        </div><?php endif; ?>
                                                                                        <?php if(isset($cust['note']) && $cust['note']): ?>
                                                                                        <div><strong><?php echo e(__('web.note')); ?> :</strong> <?php echo nl2br(e($cust['note'])); ?></div><?php endif; ?>
                                                                                        <strong><?php echo e(__('admin.email')); ?> :
                                                                                        </strong><?php echo e($customerInfo['email'] ?? ''); ?><br>
                                                                                        <strong><?php echo e(__('admin.phone')); ?> :
                                                                                        </strong><?php echo e($customerInfo['phone'] ?? ''); ?><br>
                                                                                    </address>
                                                                                </div>
                                        <?php elseif(isset($address) && $address && $address->is_billing_address == 0): ?>
                                            <div class="contact-name">
                                                <span>
                                                    <?php echo e(ucfirst($address->billing_first_name)); ?>

                                                    <?php echo e(ucfirst($address->billing_last_name)); ?>

                                                </span>
                                            </div>
                                            <div class="contact-address">
                                                <?php echo Helper::showAddress('my_address', $address, true); ?>

                                            </div>
                                        <?php elseif(isset($address) && $address): ?>
                                            <div class="contact-name">
                                                <span>
                                                    <?php echo e($address->first_name); ?> <?php echo e($address->last_name); ?>

                                                </span>
                                            </div>
                                            <div class="contact-address">
                                                <?php echo Helper::showAddress('my_address', $address); ?>

                                            </div>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="w-33">
                            <h4><?php echo e(__('web.po_summary')); ?> <span>(<?php echo e(count($orderDetail)); ?> <?php echo e(__('web.items')); ?>)</span></h4>
                            <div class="order-summary">
                                <div class="cart-details">
                                    <div class="v-flex">
                                        <div class="total">
                                            <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                            <?php
$gift_wrap = $order->gift_wrap ? $order->gift_wrap : 0;
$gift_box = $order->gift_box ? $order->gift_box : 0;
$subtotal = $gift_wrap + $gift_box + $order->order_price;
                                            ?>
                                            <div class="item-price"><?php echo e(Helper::formatNumber($subtotal)); ?>

                                                <?php echo e($currencyCode); ?>

                                            </div>
                                        </div>
                                        <?php if($order->delivery_price > 0): ?>
                                            <div class="total">
                                                <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                                <div class="item-price"><?php echo e(Helper::formatNumber($order->delivery_price)); ?>

                                                    <?php echo e($currencyCode); ?>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($order->discount > 0): ?>
                                            <div class="total">
                                                <div class="item">
                                                    <?php echo e(__('web.discount')); ?>

                                                    (<?php echo e(ucfirst($order->discount_type)); ?><?php if($order->discount_type === 'percentage' && $order->discount > 0): ?><?php echo e(" - " . $order->discount_per); ?>%<?php endif; ?>)
                                                </div>
                                                <div class="item-price">
                                                    - <?php echo e(Helper::formatNumber($order->discount)); ?> <?php echo e($currencyCode); ?>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="total grandtotal">
                                            <div class="item"><?php echo e(__('web.total_amount')); ?></div>
                                            <div class="item-price"><?php echo e(Helper::formatNumber($order->order_total)); ?>

                                                <?php echo e($currencyCode); ?>

                                            </div>
                                        </div>
                                        <?php if($order->custom_order_type === "purchase_order"): ?>
                                            <div class="total">
                                                <div class="item"><?php echo e(__('admin.advance_payment')); ?></div>
                                                <div class="item-price">-
                                                    <?php echo e(Helper::formatNumber($order->paid_amount)); ?>

                                                    <?php echo e($currencyCode); ?>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($order->custom_order_type === "purchase_order"): ?>
                                            <div class="total">
                                                <div class="item"><?php echo e(__('admin.pending_amount')); ?></div>
                                                <div class="item-price"><?php echo e(Helper::formatNumber($order->pending_amount)); ?>

                                                    <?php echo e($currencyCode); ?>

                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-info">
                        <h4><?php echo e(__('web.having_trouble_with_the_link_pay_via_bank_transfer')); ?></h4>
                        <div class="instruction">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span><?php echo e(__('web.bank_name')); ?>:&nbsp;</span>
                                        <label class="mb-0"><?php echo e(__('web.demo_bank')); ?></label>
                                    </div> 
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span><?php echo e(__('web.account_name')); ?>:&nbsp;</span>
                                        <label class="mb-0"><?php echo e(__('web.reehana_general_trading')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span><?php echo e(__('web.account_number')); ?>:&nbsp;</span>
                                        <label class="mb-0"><?php echo e(__('web.1460171196')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="total d-flex justify-start align-items-center">
                                        <span><?php echo e(__('web.iban')); ?>:&nbsp;</span>
                                        <label class="mb-0"><?php echo e(__('web.kw00_0000_0000')); ?></label>
                                    </div>
                                </div>
                            </div>
                            <h4 class="mt-3"><?php echo e(__('web.please_share_the_transfer_receipt_via_WhatsApp_or_Email_once_completed')); ?></h4>
                        </div>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-secondary"> <a href="<?php echo e(route('web.index')); ?>"
                                style="color: #000000 !important"> <?php echo e(__('web.go_to_home')); ?></a></button>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!--Modal Ex: Forgot Password-->
    <!--Header-->
    <!--Footer Scripts-->
    <?php echo $__env->make('web/layout/footer_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/user/payment_detail_customized.blade.php ENDPATH**/ ?>
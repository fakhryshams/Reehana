<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Request</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo-cell {
            width: 30%;
        }
        .center-info-cell {
            width: 40%;
            text-align: left;
            padding-left: 20px;
        }
        .right-info-cell {
            width: 30%;
            text-align: right;
        }

        .company-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .company-details {
            font-size: 12px;
            line-height: 1.6;
            color: #555;
        }

        .invoice-meta-label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .invoice-meta-value {
            font-size: 13px;
            font-weight: normal;
            margin-bottom: 15px;
        }
        .balance-due-large {
            font-weight: bold;
            font-size: 14px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .items-table th {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 8px 0;
            text-transform: uppercase;
            color: #555;
            font-weight: bold;
            font-size: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .col-desc { width: 45%; }
        .col-detail { width: 15%; font-size: 11px; color: #666; }
        .col-rate { width: 15%; text-align: right; }
        .col-qty { width: 10%; text-align: center; }
        .col-amount { width: 15%; text-align: right; }

        .item-main {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .item-sub {
            color: #666;
            font-size: 11px;
        }

        /* Totals */
        .totals-table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 5px;
            float: right;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 4px 0;
            font-size: 12px;
        }
        .totals-label {
            width: 60%;
            padding-right: 20px;
            color: #555;
            text-align: right;
            font-size: 11px;
        }
        .totals-value {
            width: 40%;
            text-align: right;
             font-weight: bold;
        }
        
        .clear { clear: both; }

        .pay-btn-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .pay-btn {
            background-color: #000000; 
            color: #ffffff !important; 
            padding: 12px 30px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 16px; 
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
            // Setup Currency
            $currencyCode = 'KWD';
            if (isset($currency) && is_string($currency)) {
                $currencyCode = $currency;
                $conversionRate = 1;
            } else {
                $cObj = Helper::getDefaultCurrency();
                $currencyCode = $cObj ? $cObj->code : config('custom.currency');
                $conversionRate = $cObj ? $cObj->value : 1;
            }
            
            // Calculate Total Pending Amount for Display
            // Calculate Total Pending Amount for Display
            $totalPending = 0;

            if(isset($regularOrder)) {
                 $totalPending += ($regularOrder->pending_amount > 0 ? $regularOrder->pending_amount : $regularOrder->order_total);
            }
            
            // For Custom Orders in this context (Pay Now Link), the user is paying the ADVANCE
            if(isset($customOrder)) {
                 $totalPending += (float)$customOrder->paid_amount; 
            }
        ?>

        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                     <img src="https://reehana.com/front/img/logo.png" alt="REEHANA" style="max-width: 150px;">
                </td>
                <td class="center-info-cell">
                    <div class="company-name">Reehana General Trading & Contracting Co.</div>
                    <div class="company-details">
                        Reehana General Trading & Contracting Co.<br>
                        Salmiya, Salem Al Mubarak Street<br>
                        Mariam Center<br>
                        Floor 07, office 37<br>
                        25711588
                    </div>
                </td>
                <td class="right-info-cell">
                    <div class="invoice-meta-label">DATE</div>
                    <div class="invoice-meta-value"><?php echo e(date('d-m-Y')); ?></div>
                    
                    <div class="invoice-meta-label">TOTAL DUE</div>
                    <div class="invoice-meta-value balance-due-large"><?php echo e($currencyCode); ?> <?php echo e(number_format($totalPending, 3)); ?></div>
                </td>
            </tr>
        </table>

        <!-- Message & Pay Link -->
        <div class="pay-btn-container">
            <p style="margin: 0 0 15px 0;">Dear <strong><?php echo e($customerName); ?></strong>,</p>
            <p style="margin: 0 0 20px 0; color: #555;">
                We have processed your order(s). Please review the details below. To finalize your purchase, kindly click the button below to proceed to payment.
            </p>
            <a href="<?php echo e($paymentLink ?? '#'); ?>" class="pay-btn">PAY <?php echo e($currencyCode); ?> <?php echo e(number_format($totalPending, 3)); ?></a>
        </div>

        <!-- Alternative Payment: IBAN -->
        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border: 1px dashed #ccc; border-radius: 4px; font-size: 13px;">
            <p style="margin: 0 0 10px 0; color: #333; font-weight: bold; text-uppercase">Having trouble with the link? Pay via Bank Transfer:</p>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 3px 0; color: #666; width: 120px;">Bank Name:</td>
                    <td style="padding: 3px 0; color: #333; font-weight: bold;">Burgan Bank</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0; color: #666;">Account Name:</td>
                    <td style="padding: 3px 0; color: #333; font-weight: bold;">Reehana General Trading and Contracting Co.</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0; color: #666;">Account Number:</td>
                    <td style="padding: 3px 0; color: #333; font-weight: bold;">1460171196</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0; color: #666;">IBAN:</td>
                    <td style="padding: 3px 0; color: #333; font-weight: bold;">KW28BRGN0000000000001460171196</td>
                </tr>
            </table>
            <p style="margin: 10px 0 0 0; color: #999; font-size: 11px;">* Please share the transfer receipt via WhatsApp or Email once completed.</p>
        </div>

        <!-- REGULAR ORDER SECTION -->
        <?php if(isset($regularOrder) && $regularOrder): ?>
        <?php 
            $regItems = \App\UserModel\OrderDetail::where('order_id', $regularOrder->id)->get(); 
        ?>
        <div class="section-title">Ref: #<?php echo e($regularOrder->id); ?></div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-desc">ITEM</th>
                    <th class="col-detail">DETAILS</th>
                    <th class="col-rate" style="text-align: right;">PRICE</th>
                    <th class="col-qty" style="text-align: center;">QTY</th>
                    <th class="col-amount" style="text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $regItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="item-main"><?php echo e($item->product_name ?? ($item->getProduct(1) ? $item->getProduct(1)->products_name : 'Item')); ?></div>
                    </td>
                    <td>
                         <?php if($item->size_id): ?> <div class="item-sub">Size: <?php echo e(\App\Helpers\Helper::getSize($item->size_id, 1)->size_title ?? ''); ?></div> <?php endif; ?>
                         <?php if($item->color_id): ?> <div class="item-sub">Color: <?php echo e(\App\Helpers\Helper::getColorName($item->color_id, 1)->color_title ?? ''); ?></div> <?php endif; ?>
                    </td>
                    <td class="col-rate"><?php echo e($currencyCode); ?> <?php echo e(number_format($item->product_price, 3)); ?></td>
                    <td class="col-qty"><?php echo e($item->quantity); ?></td>
                    <td class="col-amount"><?php echo e($currencyCode); ?> <?php echo e(number_format($item->total_price, 3)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal</td>
                <td class="totals-value"><?php echo e($currencyCode); ?> <?php echo e(number_format($regularOrder->order_price, 3)); ?></td>
            </tr>
            <?php if($regularOrder->delivery_price > 0): ?>
            <tr>
                <td class="totals-label">Delivery</td>
                <td class="totals-value"><?php echo e($currencyCode); ?> <?php echo e(number_format($regularOrder->delivery_price, 3)); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($regularOrder->tax > 0): ?>
            <tr>
                <td class="totals-label">Tax</td>
                <td class="totals-value"><?php echo e($currencyCode); ?> <?php echo e(number_format($regularOrder->tax, 3)); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($regularOrder->coupon_discount_amount > 0): ?>
            <tr>
                <td class="totals-label"><?php echo e(__('web.discount_code')); ?>: <?php echo e($order->coupon_code); ?></td>
                <td class="totals-value">-<?php echo e($currencyCode); ?> <?php echo e(Helper::formatNumber($order->coupon_discount_amount*$conversionRate)); ?></td>
            </tr>
            <?php endif; ?>
             <tr>
                <td class="totals-label" style="border-top: 1px solid #ddd; padding-top: 5px;"><strong>Total</strong></td>
                <td class="totals-value" style="border-top: 1px solid #ddd; padding-top: 5px;"><strong><?php echo e($currencyCode); ?> <?php echo e(number_format($regularOrder->order_total, 3)); ?></strong></td>
            </tr>
        </table>
        <div class="clear"></div>
        <?php endif; ?>

        <!-- CUSTOM ORDER SECTION -->
        <?php if(isset($customOrder) && $customOrder): ?>
        <?php 
            $customDetails = json_decode($customOrder->custom_order_details, true);
            $cItems = $customDetails['items'] ?? [];
        ?>
        <div class="section-title">CUSTOMIZED ORDER (Ref: #<?php echo e($customOrder->id); ?>)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-desc">ITEM DESCRIPTION</th>
                    <th class="col-detail">SPECS</th>
                    <th class="col-rate" style="text-align: right;">PRICE</th>
                    <th class="col-qty" style="text-align: center;">QTY</th>
                    <th class="col-amount" style="text-align: right;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $cItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                     $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                     // Avoid division by zero
                     $unit = isset($item['unit_price']) ? $item['unit_price'] : ($item['price'] ?? 0); 
                     $total = isset($item['total']) ? $item['total'] : ($unit * $qty);
                ?>
                <tr>
                    <td>
                        <div class="item-main"><?php echo e(Helper::formatCustomizedTitle($item['product_title'] ?? $item['description'] ?? 'Custom Item')); ?></div>
                        <div class="item-sub"><?php echo e(Helper::formatCustomizedDescription($item['description2'] ?? ($item['description'] ?? ''))); ?></div>
                    </td>
                    <td>
                        <?php if(!empty($item['color'])): ?> <div class="item-sub">Color: <?php echo e($item['color']); ?></div> <?php endif; ?>
                        <?php if(!empty($item['size'])): ?> <div class="item-sub">Size: <?php echo e($item['size']); ?></div> <?php endif; ?>
                        <?php if(!empty($item['material'])): ?> <div class="item-sub">Material: <?php echo e($item['material']); ?></div> <?php endif; ?>
                    </td>
                    <td class="col-rate"><?php echo e($currencyCode); ?> <?php echo e(number_format((float)$unit, 3)); ?></td>
                    <td class="col-qty"><?php echo e($qty); ?></td>
                    <td class="col-amount"><?php echo e($currencyCode); ?> <?php echo e(number_format((float)$total, 3)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal</td>
                <td class="totals-value"><?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->order_price, 3)); ?></td>
            </tr>
            <?php if($customOrder->delivery_price > 0): ?>
            <tr>
                <td class="totals-label">Delivery</td>
                <td class="totals-value"><?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->delivery_price, 3)); ?></td>
            </tr>
            <?php endif; ?>
             <?php if($customOrder->discount > 0): ?>
            <tr>
                <td class="totals-label">Discount</td>
                <td class="totals-value">-<?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->discount, 3)); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="totals-label" style="border-top: 1px solid #ddd;"><strong>Total</strong></td>
                <td class="totals-value" style="border-top: 1px solid #ddd;"><strong><?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->order_total, 3)); ?></strong></td>
            </tr>
            <?php if($customOrder->paid_amount > 0): ?>
            <tr>
                <td class="totals-label">Advance Paid</td>
                <td class="totals-value">-<?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->paid_amount, 3)); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="totals-label" style="border-top: 2px solid #ddd; padding-top: 5px; color: #000;"><strong>AMOUNT DUE</strong></td>
                <td class="totals-value" style="border-top: 2px solid #ddd; padding-top: 5px; color: #000;"><strong><?php echo e($currencyCode); ?> <?php echo e(number_format($customOrder->pending_amount, 3)); ?></strong></td>
            </tr>
        </table>
        <div class="clear"></div>
        <?php endif; ?>
        
        <!-- Final Footer -->
        <div style="text-align: center; font-size: 11px; color: #999; margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
            Thank you for shopping with Reehana.
        </div>

    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/emails/payment_due_link.blade.php ENDPATH**/ ?>
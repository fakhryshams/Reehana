
<?php $__env->startSection('page_title', trans('web.cart_review')); ?>
<?php $__env->startSection('body_class', 'menubg configMonogram'); ?>
<?php $__env->startSection('content'); ?>
<style>
    .main-info .product-actions a {
        width: auto;
        margin: 0 15px 0 0;
        font-size: 14px;
        text-transform: uppercase;
    }

    .custom-order-image-wrap {
        position: relative;
        display: inline-block;
        line-height: 0;
    }

    .custom-order-image-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        border-radius: 999px;
        background: #f3c24d;
        color: #111;
        font-size: 8px;
        font-weight: 700;
        line-height: 1;
        letter-spacing: .02em;
        text-transform: uppercase;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
    }

    .intl-ship {
        color: red;
    }

    .strike {
        text-decoration: line-through;
    }

    .custom-order-summary-divider {
        margin: 16px 0;
        border-top: 1px solid #d9d9d9;
    }

    .store-confirmation-modal .modal-body {
        font-family: 'Futura Md BT', Arial, sans-serif;
    }

    @media (min-width: 769px) {
        .physical-store-bottom-buy-now {
            display: none !important;
        }

        .physical-store-right-buy-now {
            display: block !important;
            margin-top: 15px;
        }
    }

    @media (max-width: 768px) {
        .physical-store-right-buy-now {
            display: none !important;
        }
    }

    .payment-gateway-buy-now {
        margin: 20px 0 10px 0;
        padding: 15px 30px 20px;
        border-top: 1px solid #ebebeb;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .payment-gateway-buy-now .btn {
        width: 298px;
        max-width: 100%;
    }

    .payment-method-store-actions-row {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        justify-content: space-between;
        gap: 14px;
        width: 100%;
    }

    .payment-method-store-actions-row .payment-store-action-wrapper {
        flex: 1 1 0;
        min-width: 0;
        display: flex;
        margin: 0 !important;
        padding: 0 !important;
    }

    .payment-method-store-actions-row > .payment-store-action-btn,
    .payment-method-store-actions-row .payment-store-action-wrapper > .payment-store-action-btn {
        flex: 1 1 0;
        min-width: 0;
        width: 100%;
        min-height: 46px;
        height: 46px;
        padding: 10px 16px;
        font-size: 13px;
        /* font-weight: 600; */
        letter-spacing: 0.5px;
        /* text-transform: uppercase; */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-radius: 4px;
        box-shadow: none !important;
        transition: all 0.25s ease-in-out;
        white-space: nowrap;
        text-decoration: none !important;
        cursor: pointer;
        line-height: 1;
        box-sizing: border-box;
        margin: 0 !important;
    }

    @media (max-width: 768px) {
        .payment-gateway-buy-now {
            padding: 15px 20px;
            justify-content: center;
        }

        .payment-gateway-buy-now .btn {
            width: 100%;
        }

        .payment-method-store-actions-row {
            flex-direction: column;
            gap: 10px;
        }
        .payment-method-store-actions-row > .payment-store-action-btn,
        .payment-method-store-actions-row .payment-store-action-wrapper,
        .payment-method-store-actions-row .payment-store-action-wrapper > .payment-store-action-btn {
            width: 100%;
        }
    }
</style>
<?php
$size = '';
$total = 0;
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$paymentMethod = Helper::getPaymentMethod();
$setting = \App\Helpers\Helper::getSetting();

$intlShippingStatus = true;
$intlShippingStatusCount = 0;

$address = "";

$countryData = Helper::getDefaultCountry();
$appliedCoupon = session('couponCode');
$hasCheckoutError = session()->has('checkout_error');

if ($user && session('default_address')) {
    $address =  Helper::getPickupStoreAddress();

    if ($address) {
        $countryData = $address->getcountry();
    }



    $city = $address ? $address->city : '';
    if (!$city && $address->city_id) {
        $city = $address->getUserCity($language->id) ? $address->getUserCity($language->id)->city_name : '';
    }
    $address->city = $city;
} else {
    $appliedCoupon = [];
}

$tax = Helper::getTax();
$tax = $tax ? $tax : 0;
$taxValue = ($total * $tax) / 100;
$shippingCharge = 0;
$discount = config('custom_web.discount', 20);

if ($countryData) {
    $shippingCharges = $countryData->shipping_charge;
    /*
     *  Convert Currency Rate
     */
    if ($currency) {
        $shippingCharges = $shippingCharges * $currency->value;
    }
}

if ($user) {


    if ($address) {

        if ($address->id != '7') {
            $zoneprice = Helper::getZonePrice($address->city_id);
            if ($zoneprice > 0) {
                $shippingCharges = $zoneprice * $currency->value;
            }
        } else {
            $shippingCharges = 0;
        }
    }
}

$total_weight = 0;
$customOrder = session('customized_order_details');
if (!empty($customOrder) && is_array($customOrder)) {
    $customOrderItems = [];
    foreach (($customOrder['items'] ?? []) as $customOrderItem) {
        $hasData = false;
        foreach (['item_code', 'product_title', 'description', 'color', 'size', 'price', 'quantity', 'image'] as $field) {
            if (!empty($customOrderItem[$field])) {
                $hasData = true;
                break;
            }
        }
        if ($hasData) {
            $customOrderItems[] = $customOrderItem;
        }
    }
    $customOrder['items'] = array_values($customOrderItems);
    session(['customized_order_details' => $customOrder]);
}
$hasCustomizedInvoice = !empty($customOrder) && !empty($customOrder['items']) && count($customOrder['items']) > 0;
$customAdvance = 0;
if ($customOrder && isset($customOrder['payment']['advance']) && is_numeric($customOrder['payment']['advance'])) {
    $customAdvance = (float) str_replace(',', '', $customOrder['payment']['advance']);
}
$isPhysicalStoreUser = auth()->user() && (
    auth()->user()->id == config('config.store_admin_id')
    || auth()->user()->user_type == "physical_store_user"
);
?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="<?php echo e(route('web.index')); ?>"><?php echo e(__('web.home')); ?></a></span>
        <span class="sep"></span>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__('web.checkout')); ?></a></span>
    </div>
</section>
<form name="cart_review_form" id="cart_review_form" action="<?php echo e(route('web.checkout.initiatePayment')); ?>" method="post">
    <?php echo csrf_field(); ?>
    <section class="cart-review">
        <div class="container">
            <div class="checkout-workflow">
                <ul>
                    <li class="active">
                        <span>1</span>
                        <label><?php echo e(__('web.cart')); ?></label>
                    </li>
                    <?php if(count($cart) > 0): ?>
                    <li class="active">
                        <span>2</span>
                        <label><?php echo e(__('web.shipping_address')); ?></label>
                    </li>
                    <?php endif; ?>
                    <li class="active">
                        <span><?php echo e(count($cart) > 0 ? 3 : 2); ?></span>
                        <label><?php echo e(count($cart) == 0 ? __('web.review_order') : __('web.review_pay')); ?></label>
                    </li>
                </ul>
            </div>
            <div class="h-flex flex-nowrap">
                <div class="cart-items-summary">
                    <?php if(count($cart) > 0): ?>
                        <div class="cart-products">
                            <div class="cart-list">
                                <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id =>$record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                $isDefault = true;
                                if ($user) {
                                    $sizeid = 's';
                                    if ($record['size_id']) {
                                        $sizeid = $record['size_id'];
                                    }
                                    $colorid = 'c';
                                    if ($record['color_id']) {
                                        $colorid = $record['color_id'];
                                    }
                                    $id = $record['product_id'] . "_" . $sizeid . "_" . $colorid;

                                    // $record['monogram'] = $record;
                                }
                                $cartQuantity = $record['quantity'];

                                $product = Helper::getProductDetail($record['product_id']);

                                if (!$product) {
                                    continue;
                                }
                                $has_monogram = Helper::isMonogranm($product->id);
                                $productimage = $product->getDefaultImage();
                                $productDetail = $product->getProductDescription($language->id);
                                $productImage = $product->getDefaultImage();
                                $sizeId = $record['size_id'];
                                $color_id = $record['color_id'];
                                $product_weight = $product->product_weight;
                                $size = Helper::getSize($sizeId, $language->id);
                                $color = Helper::getColorName($color_id, $language->id);

                                $productSize = false;
                                $colorExist = false;

                                if ($color) {
                                    $colorExist = $color->color_id;
                                }
                                if ($size) {
                                    $productSize = $size->size_id;
                                }

                                //$productQuantity = $product->getProductQuantity($product->id);
                                $productQuantity = Helper::getProductQuantity($product->id, $productSize ? 1 : 0, $sizeId, $colorExist ? 1 : 0, $color_id);
                                $product_sale_price =  $product->product_sale_price;

                                $product_regular_price = $product->product_regular_price;

                                $is_product_sale_price_null  = $product->is_product_sale_price_null;

                                $style = '';
                                if ($color || $size) {
                                    $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $color_id);

                                    if ($attributePrice) {
                                        $style = $attributePrice->style;
                                        $product_weight = $attributePrice->weight;
                                        $product_regular_price = $attributePrice->product_regular_price;
                                        $product_sale_price = $attributePrice->product_sale_price;
                                        $is_product_sale_price_null = $attributePrice->is_product_sale_price_null;
                                    }
                                }
                                $price = $is_product_sale_price_null == 0 ? $product_sale_price : $product_regular_price;
                                $hsCode = $product->getHsCode($sizeId, $color_id);

                                //monogram
                                $cartproduct = Helper::getCartProduct($user, $product->id, $productSize ? 1 : 0, $productSize ? $sizeId : 's', $colorExist ? 1 : 0, $color_id ? $color_id : 'c');
                                $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                                $font = [];
                                $initials = [];
                                $colors = [];
                                $placement = [];
                                $is_monogram = false;

                                if (count($cartproduct) > 0) {
                                    if ($user) {
                                        if ($cartproduct['is_monogram'] == 1) {
                                            $cartproduct['monogram'] = $cartproduct;
                                        } else {
                                            $cartproduct['monogram'] = [];
                                        }
                                    }


                                    if (count($cartproduct['monogram']) > 0 && $cartproduct['is_monogram'] == 1) {
                                        $is_monogram = true;
                                    }
                                }

                                if ($is_monogram) {
                                    //                                $price = $price + $monogramPrice;
                                }
                                // monogram end

                                /*
                                *  Convert Currency Rate
                                */
                                if ($currency) {
                                    //  $price = $price * $currency->value;
                                }

                                $subTotal = $price * $cartQuantity;

                                $allowIntlShipping = $product->intl_shipping;

                                $cnid = !empty($address->country_id) ? $address->country_id : 2;
                                $checkIntlShippingStatus = Helper::checkIntlShippingStatus($cnid, $allowIntlShipping);

                                if (!$checkIntlShippingStatus) {
                                    $isDefault = false;
                                    $record->updateShoppingCartDetailStatus(false);
                                } else {
                                    $total += $subTotal;
                                    $record->updateShoppingCartDetailStatus(true);
                                }

                                if ($user) {
                                    $record['monogram'] = $record;
                                }


                                //$productQuantity = $product->getProductQuantity($product->id);
                                $sizeId = $record['size_id'];

                                $colorId = $record['color_id'];
                                $size = Helper::getSize($sizeId, $language->id);
                                $color = Helper::getColorName($colorId, $language->id);

                                $productSize = false;
                                $productColor = false;

                                if ($size) {
                                    $productSize = $size->size_id;
                                }
                                if ($color) {
                                    $productColor = $color->color_id;
                                }

                                //$productQuantity = $product->getProductQuantity($product->id);
                                $productQuantity = Helper::getProductQuantity($product->id, $productSize ? 1 : 0, $sizeId, $productColor ? 1 : 0, $colorId);
                                ?>
                                <div class="cart-item main-info cart_<?php echo e($id); ?>">
                                    <div class="h-flex">
                                        <div class="prod-image">
                                            <a href="<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>">

                                                <?php
                                                $productImage2 = $product->getVariantThumbnailUrl($record['color_id'], $record['size_id']);
                                                ?>
                                                <img class="image_<?php echo e($id); ?>" src="<?php echo e(asset(Storage::url($productImage2))); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">

                                            </a>
                                            <div class="product-actions sm-hidden">
                                                <button type="button" class="btn btn-white favorite move_to_wishlist" data-href="<?php echo e(route('web.wishlist.move_to_wishlist',['id'=> $product->id])); ?>"><i class="icon-wishlist"></i><?php echo e(__('web.move_to_wishlist')); ?></button>
                                                <button type="button" class="btn btn-white delete_trash" data-page="cart_review" data-id="<?php echo e($id); ?>" data-intshipping="<?php echo e($checkIntlShippingStatus ==false ? 1 : 0); ?>" data-href="<?php echo e(route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])); ?>"><i class="icon-trash"></i></button>
                                            </div>
                                            <div class="v-flex sm-visible">
                                                <div class="prod-name">
                                                    <?php if(!$isDefault): ?>
                                                    <h3 class="strike"><?php echo e($productDetail->products_name); ?> <?php echo e($style ? '('.$style.')': ''); ?> </h3>
                                                    <?php else: ?>
                                                    <h3><?php echo e($productDetail->products_name); ?> <?php echo e($style ? '('.$style.')': ''); ?> </h3>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-pricing-details">
                                                    <?php if(!$isDefault): ?>
                                                    <span class="prod-price product_price_<?php echo e($id); ?> strike"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
                                                    <?php else: ?>
                                                    <span class="prod-price product_price_<?php echo e($id); ?>"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="prod-details pd">
                                            <div class="h-flex sm-hidden">
                                                <div class="prod-name">
                                                    <?php if(!$isDefault): ?>
                                                    <h3 class="strike"><?php echo e($productDetail->products_name); ?> <?php echo e($style ? '('.$style.')': ''); ?></h3>
                                                    <?php else: ?>
                                                    <h3><?php echo e($productDetail->products_name); ?> <?php echo e($style ? '('.$style.')': ''); ?></h3>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-pricing-details">
                                                    <?php if(!$isDefault): ?>
                                                    <span class="prod-price product_price_<?php echo e($id); ?> strike"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
                                                    <?php else: ?>
                                                    <span class="prod-price product_price_<?php echo e($id); ?>"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="product-options">

                                                <?php if($color): ?>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.color')); ?></label>
                                                    <span><?php echo e(ucfirst($color->color_title)); ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php if($size): ?>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.size')); ?></label>
                                                    <span><?php echo e(ucfirst($size->size_title)); ?></span>
                                                </div>
                                                <?php endif; ?>
                                                <?php
                                                if ($user) {
                                                    //                                            dd($record->id);
                                                    $record['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->toArray();
                                                    //                                            dd($record['monogram']);
                                                }

                                                if (count($record['monogram']) > 0) {
                                                    $monocount = $record['monogram'];
                                                    end($monocount);         // move the internal pointer to the end of the array
                                                    $lastkey = key($monocount);
                                                } else {
                                                    $lastkey = 1;
                                                }

                                                $min = $cartQuantity;
                                                $max = $productQuantity;
                                                $data_show = 5;
                                                if ($productQuantity < 5) {
                                                    $data_show = $productQuantity;
                                                }
                                                ?>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.qty')); ?></label>
                                                    <div class="counter counter2" data-maximum="<?php echo e($max); ?>" data-show="<?php echo e($data_show); ?>">
                                                        <div class="h-flex">
                                                            <button type="button" class="decrease decrease_cart" data-maximum="<?php echo e($max); ?>" data-id="<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>"><i class="icon-minus"></i></button>
                                                            <input type="text" name="quantity_<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>" data-id="<?php echo e($id); ?>" class="qty_add prod-count quantity number-input quantity_<?php echo e($record['product_id']); ?>" value="<?php echo e($min); ?>">
                                                            <button type="button" class="increase increase_cart " data-maximum="<?php echo e($max); ?>" data-id="<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>"><i class="icon-plus"></i></button>
                                                        </div>
                                                        <!--<span class="remains"></span>-->
                                                        <!-- <span class="remains show" data-show="10">(Only 5 left)</span> -->

                                                    </div>
                                                </div>
                                            </div>
                                            <?php if($has_monogram): ?>

                                            <div id="mogogram_list" class="monogramSummary monogramSummary-<?php echo e($id); ?>" data-total="<?php echo e($lastkey); ?>">

                                                <?php $__currentLoopData = $record['monogram']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $monogramVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
                                                $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
                                                $colors = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
                                                $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                                $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
                                                if ($record['is_same'] == 1) {

                                                    $total = $total + ($monogramPrice * $min);
                                                } else {
                                                    $total = $total + $monogramPrice;
                                                }
                                                //                                        
                                                ?>

                                                <div class="monogram-information  monogram-information-<?php echo e($id); ?>  <?php echo e(count($cartproduct['monogram']) > 0 ? 'show' : 'hide'); ?> " id="monogramSummary-<?php echo e($id); ?>">
                                                    <div class="h-flex">
                                                        <div class="h-flex">
                                                            <label>Selected Monogram</label>
                                                            <span class="monogram-price nowrap"> <?php echo e($monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''); ?></span>
                                                        </div>
                                                        <span class="link-button edit_monogram2" data-productId="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-color="<?php echo e($color_id ? $color_id : 'c'); ?>" data-size="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($key); ?>">Edit</span>
                                                        <span class="link-button removeMonogram2" data-productid="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-colorId="<?php echo e($color_id ? $color_id : 'c'); ?>" data-sizeId="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($user ? $monogramVal['id'] : $key); ?>">Remove</span>
                                                    </div>

                                                    <input type="hidden" name="product_cart_id_<?php echo e($id); ?>_<?php echo e($key); ?>" value="<?php echo e($monogramVal['id']); ?>">

                                                    <div class="border-panel">
                                                        <div class="h-flex">
                                                            <div class="monogram-options">
                                                                <label>Style</label>
                                                                <div class="info " id="styleInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><img src="<?php echo e(asset(Storage::url($font ? $font->style_image_svg : ''))); ?>"></div>
                                                            </div>
                                                            <div class="monogram-options">
                                                                <label>Initials</label>
                                                                <div class="info" id="alphabetsInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><?php echo e($initials? $initials : ''); ?></div>
                                                            </div>
                                                            <div class="monogram-options">
                                                                <label>Color</label>
                                                                <div class="info" id="colorInfo_<?php echo e($id); ?>_<?php echo e($key); ?>">
                                                                    <span class="color-plate" style="background: <?php echo e($colors? $colors->color_hash : ''); ?>;"></span>
                                                                    <?php echo e($colors?$colors->color_name : ''); ?>

                                                                </div>
                                                            </div>
                                                            <div class="monogram-options">
                                                                <label>Placement</label>
                                                                <div class="info" id="placementInfo_<?php echo e($id); ?>_<?php echo e($key); ?>"><?php echo e($placement ?$placement->placement_desc:''); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="separator underline underline_<?php echo e($id); ?>_<?php echo e($key); ?> <?php echo e($monogramVal['monogram_note'] ? 'show' : 'hide'); ?>"></div>
                                                        <div class="comment comment_<?php echo e($id); ?>_<?php echo e($key); ?> <?php echo e($record['monogram'] ? $monogramVal['monogram_note'] ? 'show' : 'hide' : ''); ?>">
                                                            <p><?php echo e($record['monogram'] ?  $monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:'': ''); ?> </p>
                                                        </div>

                                                    </div>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



                                            </div>
                                            <?php $hasmonogram = \App\Helpers\Helper::getMonogram($product->id); ?>
                                            <?php if($hasmonogram): ?>
                                            <div class="addMonogramLink addMonogramLink_<?php echo e($id); ?>" data-page="cart" data-productId="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id?1:0); ?>" data-sizeexist="<?php echo e($sizeId?1:0); ?>" data-color="<?php echo e($color_id? $color_id : ''); ?>" data-size="<?php echo e($sizeId? $sizeId:''); ?>">
                                                <button type="button" <?php echo (int) $min == count($record['monogram']) || $record['is_same'] == 1 ? 'disabled="disabled"' : '' ?> class="link-button">
                                                    <i class="icon-plus"></i>Add a Monogram
                                                </button>
                                            </div>
                                            <div class="applyMonogramStyles applyMonogramStyles_<?php echo e($id); ?> <?php echo e(count($record['monogram']) ==1 && $min > 1 ? 'show' : 'hide'); ?>">
                                                <div class="checkbox">
                                                    <input type="checkbox" name="applyAll_<?php echo e($id); ?>" data-id="<?php echo e($id); ?>" class="apply_same2" <?php echo e($record['is_same'] == 1 ? 'checked' : ''); ?>>
                                                    <label>Apply same Monogram Styles to all quantities.</label>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                            <div class="addNote-actions">
                                                <span class="link-button  addNote" id='addNote-<?php echo e($id); ?>'><i class="icon-plus"></i><?php echo e(__('web.add_note')); ?></span>
                                                <i class="error"></i>
                                                <div class="note-error form-item text-green custom-error2 note-success-<?php echo e($id); ?>"></div>
                                                <div class="textarea_<?php echo e($id); ?> addNoteInput  ">
                                                    <textarea name="note_<?php echo e($id); ?>" class="note_<?php echo e($id); ?>" maxlength="<?php echo e(config('custom.order_note_length')); ?>"><?php echo e($record->note); ?></textarea>
                                                    <i class="error"></i>
                                                    <div style="width: 100%;" class="note-error form-item text-red custom-error2 note-error-<?php echo e($id); ?>"></div>
                                                    <div class="actions">
                                                        <span class="btn btn-primary save_note" data-id="<?php echo e($id); ?>"><?php echo e(__('web.save')); ?></span>
                                                        <span class="btn btn-secondary close-note" data-id="<?php echo e($id); ?>"><?php echo e(__('web.Close')); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                                <?php if(isset($record['shipping_cost']) && $record['shipping_cost'] > 0): ?>
                                                <div class="delivery-information">
                                                    <label><?php echo e(__('web.shipping_charges')); ?></label>
                                                    <span><?php echo e(Helper::formatNumber($record['shipping_cost'])); ?> <?php echo e($currencyCode); ?></span>
                                                </div>
                                                <?php endif; ?>
                                        </div>
                                        <div class="product-actions sm-visible">
                                            <button type="button" class="btn btn-white favorite move_to_wishlist" data-href="<?php echo e(route('web.wishlist.move_to_wishlist',['id'=> $product->id])); ?>"><i class="icon-wishlist"></i><?php echo e(__('web.move_to_wishlist')); ?></button>
                                            <button type="button" class="btn btn-white delete_trash" data-id="<?php echo e($id); ?>" data-page="cart_review" data-href="<?php echo e(route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])); ?>"><i class="icon-trash"></i></button>
                                        </div>
                                    </div>
                                    <?php if(!$isDefault): ?>
                                    <?php
                                    $intlShippingStatus = false;
                                    ?>

                                    <div class="alert-danger not_deliver">
                                        <i class="fa fa-times-circle"></i>
                                        <span><?php echo e(__('web.intl_ship_text')); ?></span>
                                    </div>

                                    <?php endif; ?>
                                    <?php if($checkIntlShippingStatus == false): ?>
                                    <?php
                                    $intlShippingStatusCount++;

                                    ?>

                                    <?php endif; ?>

                                </div>

                                <?php
                                $total_weight += $product_weight * $min;
                                ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <input type="hidden" name="intlShippingStatusCount" value="<?php echo e($intlShippingStatusCount); ?>" id="intlShippingStatusCount">
                                <?php

                                $countryName = 'kuwait';
                                if (isset($address)) {

                                $cnid =  !empty($address->country_id) ? $address->country_id : 2;
                                $countryName = App\AdminModel\AdminCountryDescription::where('country_id', $cnid)->first();
                                $countryName = $countryName ? $countryName->country_name : 'kuwait';
                            }
                            $roundweight = ceil($total_weight);
                            if ($roundweight < 1 ||  strtolower($countryName) == 'kuwait') {
                                $roundweight = 1;
                            }
                            $shippingCharge = $shippingCharges * $roundweight;


                                $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                                if (session('gift_wrap')) {
                                    $gift_wrap = session('gift_wrap');
                                }
                                ?>
                                <?php if(!empty($setting['31']) && $setting['31']->setting_val == 1): ?>

                                <?php
                                $giftAmount = !empty($setting['32']) ? $setting['32']->setting_val : 0;
                                $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
                                if ($giftMinPrice) {
                                    $giftMinPrice = $giftMinPrice * $currency->value;
                                }


                                if ($total >= $giftMinPrice) {
                                    $giftAmount = 0;
                                }

                                $setting = \App\Helpers\Helper::getSetting();
                                $giftWrapImageSetting = $setting->firstWhere('setting_key', 'Gift Wrap Image');
                                $giftWrapImageUrl = $giftWrapImageSetting && $giftWrapImageSetting->setting_val
                                    ? asset(Storage::url($giftWrapImageSetting->setting_val))
                                    : asset('front/img/giftwrap.png');
                                ?>
                                <div class="cart-item main-info gift-wrap-item">
                                    <div class="v-flex">
                                        <div class="gift-wrap-item-container">
                                            <div class="prod-image">
                                                <a href="#"><img src="<?php echo e($giftWrapImageUrl); ?>"></a>
                                            </div>
                                            <div class="options v-flex">
                                                <div class="h-flex">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="add_gift" id="gift_wrap" <?php echo e($gift_wrap['is_gift'] == 1? 'checked': ''); ?> value="1">
                                                        <label><?php echo e(__('web.add_gift_wrap')); ?></label>
                                                    </div>
                                                    <?php
                                                        $giftDesc = trim(str_replace("\xc2\xa0", '', html_entity_decode(strip_tags($setting['34']->setting_val ?? ''))));
                                                    ?>
                                                    <?php if($giftDesc !== ''): ?>
                                                    <span class="tt">
                                                        <i class="fa fa-info-circle"></i>
                                                        <span class="tt-text"><?php echo !empty($setting['34']) ? $setting['34']->setting_val : ''; ?></span>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="prod-price gift-amount"><?php echo e($giftAmount > 0 ? Helper::formatNumber(($giftAmount * $currency->value) )  : '0.000'); ?> <?php echo e($currencyCode); ?></div>

                                            </div>
                                        </div>
                                        <div class="prod-details">
                                            <div class="gift-wrap-information">
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.title')); ?></label>
                                                    <input type="text" name="gift_title" maxlength="20" class="gift_title" value="<?php echo e($gift_wrap['title']); ?>">
                                                </div>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.message')); ?></label>
                                                    <textarea name="gift_message" class="gift_message" maxlength="40"><?php echo e($gift_wrap['message']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php
                                $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                                if (session('gift_box')) {
                                    $gift_box = session('gift_box');
                                }
                                ?>
                                <?php if(!empty($setting['41']) && $setting['41']->setting_val == 1): ?>
                                <?php
                                $giftBoxAmount = !empty($setting['42']) ? $setting['42']->setting_val : 0;
                                $giftBoxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice();
                                if ($giftBoxMinPrice) {
                                    $giftBoxMinPrice = $giftBoxMinPrice * $currency->value;
                                }

                                if ($total >= $giftBoxMinPrice) {
                                    $giftBoxAmount = 0;
                                }

                                $giftBoxImageSetting = $setting->firstWhere('setting_key', 'Gift Box Image');
                                $giftBoxImageUrl = $giftBoxImageSetting && $giftBoxImageSetting->setting_val
                                    ? asset(Storage::url($giftBoxImageSetting->setting_val))
                                    : asset('front/img/giftwrap.png');
                                ?>
                                <div class="cart-item main-info gift-wrap-item">
                                     <div class="v-flex">
                                         <div class="gift-wrap-item-container">
                                             <div class="prod-image">
                                                 <a href="#"><img src="<?php echo e($giftBoxImageUrl); ?>"></a>
                                             </div>
                                             <div class="options v-flex">
                                                 <div class="h-flex">
                                                     <div class="checkbox">
                                                         <input type="checkbox" name="add_gift_box" id="gift_box" <?php echo e($gift_box['is_gift'] == 1? 'checked': ''); ?> value="1">
                                                         <label><?php echo e(__('web.add_gift_box')); ?></label>
                                                     </div>
                                                    <?php
                                                        $giftBoxDesc = trim(str_replace("\xc2\xa0", '', html_entity_decode(strip_tags($setting['44']->setting_val ?? ''))));
                                                    ?>
                                                    <?php if($giftBoxDesc !== ''): ?>
                                                     <span class="tt">
                                                         <i class="fa fa-info-circle"></i>
                                                         <span class="tt-text"><?php echo $setting['44']->setting_val; ?></span>
                                                     </span>
                                                     <?php endif; ?>
                                                 </div>
                                                 <div class="prod-price gift-box-amount"><?php echo e($giftBoxAmount > 0 ? Helper::formatNumber(($giftBoxAmount * $currency->value) )  : '0.000'); ?> <?php echo e($currencyCode); ?></div>

                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php
                    $discountApplied = ($total * $discount) / 100;

                    /*
                     * Coupon Code
                     */
                    $appliedCoupon = [];
                    $couponDiscountFinal = 0;

                    if ($user) {
                        $appliedCoupon = session('couponCode');
                        if ($appliedCoupon && isset($appliedCoupon['user_id'])) {
                            if ($appliedCoupon['user_id'] != $user->id) {
                                $appliedCoupon = [];
                            }

                            if ($appliedCoupon) {
                                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);
                                if (!$couponStatus['status']) {
                                    $appliedCoupon = [];
                                }
                            }
                        }

                        if ($appliedCoupon) {
                            $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                            $couponDiscount = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                            if ($couponDiscountIn && $couponDiscount) {
                                if ($couponDiscountIn == 'P') {
                                    $couponDiscountFinal = ($total * $couponDiscount) / 100;
                                } else if ($couponDiscountIn == 'F') {
                                    $couponDiscountFinal = $total - $couponDiscount;
                                }
                            }
                        }
                    }

                    /*
                     * End :: Coupon Code
                     */
                    


                    /*
                     *  Convert Currency Rate
                     */
                    if ($currency) {
                        $discountApplied = $discountApplied * $currency->value;
                        $couponDiscountFinal = $couponDiscountFinal;
                    }
                    // $shippingCharge =  Helper::getShippingAmount($total, $shippingCharge);
                    // if (isset($address) && !empty($address) && $address->id == 43) {
                    //     $shippingCharge = 0;
                    // }
                    $customDisplaySubTotal = 0;
                    if ($customOrder && isset($customOrder['items'])) {
                        foreach ($customOrder['items'] as $cItem) {
                            $customDisplaySubTotal += (float) ($cItem['total'] ?? 0);
                        }
                    }
                    $customReviewSummary = \App\Services\PaymentCalculationService::resolveCustomizedOrderPayableFromSession($customOrder);
                    $customDisplaySubTotal = $customReviewSummary['subtotal'];
                    $customDisplayShipping = $customReviewSummary['shipping'];
                    $customDisplayDiscount = $customReviewSummary['discount_amount'];
                    $customDisplayDiscountType = $customReviewSummary['discount_type'] ?? '';
                    $customDisplayDiscountVal = $customReviewSummary['discount_val'];
                    $customDisplayOrderValue = $customReviewSummary['grand_total'];
                    $customDisplayAdvancePaid = $customReviewSummary['advance_price'];
                    $customDisplayPendingAmount = $customReviewSummary['pending_amount'];
                    $customDisplayAmountPayable = $customReviewSummary['payable_amount'];
                    $isAdvanceDoneForReview = $customReviewSummary['is_advance_done'];
                    $generatePendingRequestedForReview = $customReviewSummary['generate_pending_requested'];
                    $gift_box = ['is_gift' => 0, 'title' => '', 'message' => ''];
                    if (session('gift_box')) {
                        $gift_box = session('gift_box');
                    }
                    $giftBoxAmount = !empty($setting['42']) ? $setting['42']->setting_val : 0;
                    $giftBoxMinPrice = \App\Helpers\Helper::getGiftBoxMinPrice();
                    if ($giftBoxMinPrice) {
                        $giftBoxMinPrice = $giftBoxMinPrice * $currency->value;
                    }
                    if ($total >= $giftBoxMinPrice) {
                        $giftBoxAmount = 0;
                    }

                    $regularDisplaySubTotal = (float) $total;
                    $regularDisplayShipping = (float) $totalShipping;
                    $regularDisplayDiscount = (float) ($discountApplied + $couponDiscountFinal);
                    $regularDisplayGiftWrap = (!empty($setting['31']) && $setting['31']->setting_val == 1 && count($cart) > 0 && !empty($gift_wrap['is_gift']))
                        ? (float) ($giftAmount * $currency->value)
                        : 0;
                    $regularDisplayGiftBox = (!empty($setting['41']) && $setting['41']->setting_val == 1 && count($cart) > 0 && !empty($gift_box['is_gift']))
                        ? (float) ($giftBoxAmount * $currency->value)
                        : 0;
                    $regularDisplayPayable = max(0, $regularDisplaySubTotal + $regularDisplayShipping - $regularDisplayDiscount + $regularDisplayGiftWrap + $regularDisplayGiftBox);
                    $finalTotal = $regularDisplayPayable + $customDisplayAmountPayable;
                    ?>


                    <?php $customOrder = session('customized_order_details'); ?>
                    <?php if(!empty($customOrder) && !empty($customOrder['items'])): ?>
                    <?php
                        $cust = $customOrder['customer'] ?? [];
                        $leftCustomerDetails = [];
                        $rightCustomerDetails = [];
                        $rightCustomerDetails2 = [];
                        $bottomNote = [];

                        if (!empty($cust['name'])) {
                            $leftCustomerDetails[] = ['label' => __('web.name'), 'value' => $cust['name']];
                        }
                        if (!empty($cust['phone'])) {
                            $leftCustomerDetails[] = ['label' => __('web.phone'), 'value' => $cust['phone']];
                        }
                        if (!empty($cust['email'])) {
                            $leftCustomerDetails[] = ['label' => __('web.email'), 'value' => $cust['email']];
                        }
                        if (!empty($cust['note'])) {
                            $bottomNote[] = ['label' => __('web.note'), 'value' => $cust['note']];
                        }

                        if (!empty($cust['block'])) {
                            $rightCustomerDetails[] = ['label' => __('web.apartment'), 'value' => $cust['block']];
                        }
                        if (!empty($cust['floor'])) {
                            $rightCustomerDetails[] = ['label' => __('web.floor'), 'value' => $cust['floor']];
                        }
                        if (!empty($cust['house_number'])) {
                            $rightCustomerDetails[] = ['label' => __('web.house_number'), 'value' => $cust['house_number']];
                        }
                        if (!empty($cust['city'])) {
                            $rightCustomerDetails2[] = ['label' => __('web.city'), 'value' => $cust['city']];
                        }
                        if (!empty($cust['country_id'])) {
                            $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                            if ($cn) {
                                $rightCustomerDetails2[] = ['label' => __('web.country'), 'value' => $cn->country_name];
                            }
                        }
                        if (!empty($cust['address_detail'])) {
                            $rightCustomerDetails2[] = ['label' => __('web.address_detail'), 'value' => $cust['address_detail']];
                        }
                    ?>
                    <?php if(!empty($leftCustomerDetails) || !empty($rightCustomerDetails)): ?>
                    <div class="cart-details mb-4">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <h4 style="margin: 0;"><?php echo e(__('web.customized_order_details')); ?></h4>

                            <div style="font-size: 13px;">
                                <span style="font-weight: bold; color: #555;">
                                    <?php echo e(__('web.request_type')); ?>:
                                </span>
                                <span style="margin-left: 5px; text-transform: uppercase;">
                                    <?php echo e(in_array(($customOrder['type'] ?? ''), ['purchase_order', 'quotation'])
                                        ? __('web.' . $customOrder['type'])
                                        : str_replace('_', ' ', $customOrder['type'] ?? '')); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="row w-100">
                            <?php if(!empty($leftCustomerDetails)): ?>
                            <div class="col-md-5">
                                <div style="font-size: 13px;">
                                    <span style="font-weight: bold; color: #555;"><?php echo e(__('web.customer_details')); ?>:</span>
                                </div>
                                <?php $__currentLoopData = $leftCustomerDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><strong><?php echo e($detail['label']); ?>:</strong> <?php echo e($detail['value']); ?></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($rightCustomerDetails)): ?>
                            <div class="col-md-7">
                                <div style="font-size: 13px;">
                                    <span style="font-weight: bold; color: #555;"><?php echo e(__('web.address_details')); ?>:</span>
                                </div>
                                <div class="row w-100">
                                    <div class="col-md-6">
                                        <?php $__currentLoopData = $rightCustomerDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div><strong><?php echo e($detail['label']); ?>:</strong> <?php echo e($detail['value']); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $__currentLoopData = $rightCustomerDetails2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div><strong><?php echo e($detail['label']); ?>:</strong> <?php echo e($detail['value']); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if(!empty($bottomNote)): ?>
                            <div class="col-md-12 d-flex text-justify">
                                <?php $__currentLoopData = $bottomNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><strong><?php echo e($detail['label']); ?>:</strong> <?php echo e($detail['value']); ?></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($customOrder) && !empty($customOrder['items'])): ?>
                    <div class="cart-products custom-order-cart-section">
                        <div class="cart-list">
                            <?php
                            $total_weight = 0;
                            $i = 0;
                            ?>
                            <?php $__currentLoopData = $customOrder['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="cart-item main-info cart_1 custom-order-item" data-index="<?php echo e($loop->index); ?>">
                                <div class="h-flex">
                                    <div class="prod-image">
                                            <a href="#" class="custom-order-image-wrap">
                                                <?php $itemImage = !empty($item['image']) ? preg_replace('/^public\//', '', $item['image']) : null; ?>
                                                <img class="image_1" src="<?php echo e($itemImage ? asset(Storage::url($itemImage)) : asset(Storage::url('default.png'))); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                            <span class="custom-order-image-badge"><?php echo e(__('web.customized')); ?></span>
                                        </a>
                                        <div class="v-flex sm-visible">
                                            <?php if(isset($item['product_title'])): ?>
                                            <div class="prod-name">
                                                <h3><?php echo e(Helper::formatCustomizedTitle($item['product_title'])); ?></h3>
                                            </div>
                                            <?php endif; ?>
                                            <div class="product-pricing-details">
                                                <span class="prod-price custom-order-line-total" data-index="<?php echo e($loop->index); ?>"><?php echo e(Helper::formatNumber($item['total'])); ?> <?php echo e($currencyCode); ?></span>
                                            </div>
                                        </div>
                                </div>
                                <div class="prod-details pd">
                                    <div class="h-flex sm-hidden">
                                        <?php if(isset($item['product_title'])): ?>
                                        <div class="prod-name">
                                            <h3><?php echo e(Helper::formatCustomizedTitle($item['product_title'])); ?></h3>
                                        </div>
                                            <?php endif; ?>
                                            <div class="product-pricing-details">
                                                <span class="prod-price custom-order-line-total" data-index="<?php echo e($loop->index); ?>"><?php echo e(Helper::formatNumber($item['total'])); ?> <?php echo e($currencyCode); ?></span>
                                            </div>
                                        </div>
                                        <div class="product-options">
                                            <?php if(isset($item['item_code']) && !empty($item['size'])): ?>
                                            <div class="form-item barcode-cell">
                                                <label><?php echo e(__('web.item_code')); ?></label>
                                                <span><?php echo e(ucfirst($item['item_code'])); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($item['size']) && !empty($item['size'])): ?>
                                            <div class="form-item">
                                                <label><?php echo e(__('web.size')); ?></label>
                                                <span><?php echo e(ucfirst($item['size'])); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($item['color']) && !empty($item['color'])): ?>
                                            <div class="form-item">
                                                <label><?php echo e(__('web.color')); ?></label>
                                                <span><?php echo e(ucfirst($item['color'])); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($item['quantity']) && !empty($item['quantity'])): ?>
                                                <?php
                                                    $min = (int) $item['quantity'];
                                                    $max = 1000;
                                                    $data_show = 5;
                                                    if ($item['quantity'] < 5) {
                                                        $data_show = $item['quantity'];
                                                    }
                                                ?>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.qty')); ?></label>
                                                    <div class="counter counter2" data-maximum="<?php echo e($max); ?>" data-show="<?php echo e($data_show); ?>">
                                                        <div class="h-flex">
                                                            <button type="button" class="custom-order-decrease" data-index="<?php echo e($loop->index); ?>" data-maximum="<?php echo e($max); ?>"><i class="icon-minus"></i></button>
                                                            <input type="text" name="custom_quantity_<?php echo e($loop->index); ?>" data-index="<?php echo e($loop->index); ?>" data-price="<?php echo e((float)($item['price'] ?? 0)); ?>" class="qty_add prod-count quantity number-input custom-order-qty" value="<?php echo e($min); ?>" readonly>
                                                            <button type="button" class="custom-order-increase" data-index="<?php echo e($loop->index); ?>" data-maximum="<?php echo e($max); ?>"><i class="icon-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-options">
                                            <?php if(isset($item['description']) && !empty($item['description'])): ?>
                                                <div class="form-item">
                                                    <label><?php echo e(__('web.description')); ?></label>
                                                    <span><?php echo e(Helper::formatCustomizedDescription($item['description'])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="product-actions sm-visible">
                                        <button type="button" class="btn btn-white custom-order-delete" data-index="<?php echo e($loop->index); ?>"><i class="icon-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if(count($cart) > 0): ?>
                    <div class="shipping-address mt-0">
                        <div class="address-item selected">
                            <label class="contact">
                                <div class="contact-name">
                                    <span><?php echo e($address->first_name); ?> <?php echo e($address->last_name); ?></span>
                                    <span class="address-title"><?php echo e($address->address_title); ?></span>
                                </div>
                                <div class="contact-address">
                                    <?php echo Helper::showAddress('my_address',$address); ?>

                                </div>
                            </label>
                            <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="<?php echo e($address->id); ?>"><?php echo e(__('web.change_address')); ?></span>
                            <!--<a href="<?php echo e(route('web.checkout.index')); ?>" ><span class="link-button "><?php echo e(__('web.change_address')); ?></span></a>-->
                        </div>
                    </div>
                    <div class="billing-address">
                        <h3><?php echo e(__('web.billing_address')); ?></h3>
                        <div class="clearfix">
                            <?php if($address->is_billing_address ==0): ?>
                            <div class="address-item ">
                                <label class="contact">
                                    <div class="contact-address">
                                        <?php echo Helper::showAddress('my_address',$address,true); ?>

                                    </div>
                                </label>
                                <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="<?php echo e($address->id); ?>"><?php echo e(__('web.edit_address')); ?></span>
                                <!--<a href="<?php echo e(route('web.checkout.index')); ?>" > <span class="link-button "><?php echo e(__('web.edit_address')); ?></span></a>-->
                            </div>
                            <?php else: ?>

                            <p id="same-address"><?php echo e(__('web.same_as_delivery_cart')); ?> <span class="link-button edit-address edit_address" data-page="cart_review" data-edit="<?php echo e($address->id); ?>"><?php echo e(__('web.change_address')); ?></span></p>
                            <!--                            <p id="same-address"><?php echo e(__('web.same_as_delivery_cart')); ?> 
                                                                                        <a href="<?php echo e(route('web.checkout.index')); ?>" > <span class="link-button " ><?php echo e(__('web.change_address')); ?></span></a>
                                                                                    </p>-->
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="cart-summary sm-visible">
                        <?php if(count($cart) > 0): ?>
                        <?php if(config('custom_web.show_coupon_card')): ?>
                        <?php echo $__env->make('web.checkout.coupon_card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php endif; ?>
                        <?php endif; ?>
                        <div class="cart-details">
                        <h4><?php echo e(__('web.price_details')); ?></h4>
                        <div class="v-flex">
                                    <?php if(count($cart) > 0): ?>
                                    <h6><?php echo e(__('web.regular_order')); ?></h6>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                        <div class="item-price" data-subtotal="<?php echo e($total); ?>"> <?php echo e(Helper::formatNumber($total)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    
                                    <?php
                                        $customSubTotal = $customDisplaySubTotal;
                                        $customShipping = $customDisplayShipping;
                                        $customDiscount = $customDisplayDiscount;
                                        $customOrderValue = $customDisplayOrderValue;
                                        $customAdvancePaid = $customDisplayAdvancePaid;
                                        $customAmountPayable = $customDisplayAmountPayable;
                                    ?>

                                    <h4><?php echo e(__('web.custom_order_price_details')); ?></h4>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                        <div class="item-price"><?php echo e(Helper::formatNumber($customSubTotal)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                        <div class="item-price"><?php echo e(Helper::formatNumber($customShipping)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php if($customDiscount > 0): ?>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.discount')); ?></div>
                                        <div class="item-price">- <?php echo e(Helper::formatNumber($customDiscount)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                     <div class="custom-order-summary-divider"></div>
                                     <div class="total">
                                         <div class="item"><?php echo e(__('web.total_order_value')); ?></div>
                                         <div class="item-price"><?php echo e(Helper::formatNumber($customDisplayOrderValue)); ?> <?php echo e($currencyCode); ?></div>
                                     </div>
                                     <?php if($customDisplayAdvancePaid > 0): ?>
                                     <div class="total">
                                         <div class="item"><?php echo e(($isAdvanceDoneForReview) ? __('web.advance_paid') : __('web.advance_payment')); ?></div>
                                         <div class="item-price custom-advance">- <?php echo e(Helper::formatNumber($customDisplayAdvancePaid)); ?> <?php echo e($currencyCode); ?></div>
                                     </div>
                                     <?php endif; ?>
                                     <div class="total">
                                         <div class="item"><?php echo e(__('web.pending_amount')); ?></div>
                                         <div class="item-price amount-payable custom-order-pending"><?php echo e(Helper::formatNumber($customDisplayPendingAmount)); ?> <?php echo e($currencyCode); ?></div>
                                     </div>
                                     <div class="custom-order-summary-divider"></div>

                                    <?php if($taxValue > 0 && count($cart) > 0): ?>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.tax')); ?></div>
                                        <div class="item-price"><?php echo e(Helper::formatNumber($taxValue)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(count($cart) > 0): ?>
                                    <div class="total">
                                        <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                        <div class="item-price shipping-price"><?php echo e(Helper::formatNumber($totalShipping)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(count($cart) > 0): ?>
                                    <?php if(!empty($setting['31']) && $setting['31']->setting_val == 1): ?>

                                    <div class="total gift_wrap_item <?php echo e($gift_wrap['is_gift'] == 1? 'show': 'hide'); ?>">
                                        <div class="item"><?php echo e(__('web.gift_wrap')); ?></div>
                                        <div class="item-price gift_wrap_price"><?php echo e($giftAmount > 0 ? Helper::formatNumber(($giftAmount  * $currency->value)) : '0.000'); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(!empty($setting['41']) && $setting['41']->setting_val == 1): ?>

                                    <div class="total gift_box_item <?php echo e($gift_box['is_gift'] == 1? 'show': 'hide'); ?>">
                                        <div class="item"><?php echo e(__('web.gift_box')); ?></div>
                                        <div class="item-price gift_box_price"><?php echo e($giftBoxAmount > 0 ? Helper::formatNumber(($giftBoxAmount  * $currency->value)) : '0.000'); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if(config('custom_web.apply_discount') ): ?>
                                    <?php
                                    $coup = 'hide';
                                    if (isset($appliedCoupon) && isset($appliedCoupon['user_id'])) {
                                        $coup = 'show';
                                    }
                                    ?>
                                    <div class="total coupon-total <?php echo e($coup); ?>">
                                        <div class="item"><span class="coupon_name"><?php echo e(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '-'); ?></span> <span class="link-button remove-coupon-code">Remove</span></div>
                                        <div class="item-price coupon-final-discount"><?php echo e(Helper::formatNumber($couponDiscountFinal)); ?> <?php echo e($currencyCode); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                    // Removed redundant double addition of gift charges to finalTotal
                                    ?>
                            <div class="total grandtotal">
                                <div class="item"><?php echo e(__('web.total_payable')); ?></div> <!-- Changed label to Total Payable to be clear -->
                                <div class="item-price grand-total" data-grand-total='<?php echo e(number_format((float) $finalTotal, 3, '.', '')); ?>' data-cart-base-total="<?php echo e(number_format((float) ($finalTotal - $customDisplayAmountPayable), 3, '.', '')); ?>"><?php echo e(Helper::formatNumber($finalTotal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            
                            </div>
                            <?php if($isPhysicalStoreUser): ?>
                            <div class="page-actions physical-store-right-buy-now">
                                <?php if($intlShippingStatus): ?>
                                <button type="submit" class="btn btn-primary buy_now continue-action-button" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data continue-action-button" disabled><?php echo e(__('web.purchase_now')); ?></button>
                                <?php endif; ?>

                                <?php if($hasCustomizedInvoice): ?>
                                <div class="customized-preview-action" id="customized_pdf_preview_action">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'review'])); ?>" target="_blank" class="btn btn-secondary mt-3 no-loader preview-pdf-button"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                                <div class="customized-delete-action" style="margin-top: 12px;">
                                    <button type="button" class="btn btn-primary buy_now continue-action-button delete-customized-invoice"><?php echo e(__('web.delete_invoice')); ?></button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                        $isCustomizedOnly = count($cart) == 0 && !empty($customOrder) && ($customOrder['type'] ?? '') !== 'quotation';
                        $isPoNoPaymentRequired = false;
                        if ($isCustomizedOnly) {
                            if ($isAdvanceDoneForReview) {
                                $isPoNoPaymentRequired = !$generatePendingRequestedForReview || ($customDisplayPendingAmount <= 0.0001);
                            } else {
                                $isPoNoPaymentRequired = empty($customOrder['payment']['advance']) && ($customDisplayAmountPayable <= 0.0001);
                            }
                        }
                    ?>
                    <?php if(!$isPoNoPaymentRequired && (count($cart) > 0  || ($customOrder && $customOrder['type'] != 'quotation'))): ?>
                    <div class="cart-products payment-gateway">
                        <div class="title">
                            <h3><?php echo e(__('web.select_payment_method')); ?></h3>
                        </div>
                        <div class="clearfix">
                            <div class="options">
                                <?php $__currentLoopData = $paymentMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($record->id==4): ?>
                                <?php
                                continue;
                                ?>
                                <?php endif; ?>
                                <?php if(
                                    $record->id==6
                                    && auth()->user()
                                    && auth()->user()->id!=config('config.store_admin_id')
                                    && auth()->user()->user_type != "physical_store_user"
                                ): ?>
                                <?php
                                continue;
                                ?>
                                <?php endif; ?>
                                <div class="radio ">
                                    <input type="radio" name="paymentMethod" class='paymentMethod' <?php echo e($record->is_default? 'checked' : ''); ?> value="<?php echo e($record->id); ?>">
                                    <label class="contact">
                                        <span><?php echo e((int) $record->id === 6 ? 'Pay At Shop' : ($record->getDescription($language->id)?$record->getDescription($language->id)->payment_method_name:'')); ?></span>
                                        <?php if($record->id == 6): ?>
                                        <img Style="height: 15px" src="<?php echo e(asset('front/img/market.png')); ?>">
                                        <?php endif; ?>
                                        <?php if($record->id ==2): ?>
                                        <img Style="height: 15px" src="<?php echo e(asset('front/img/cc-master-card.png')); ?>">
                                        <?php endif; ?>
                                        <?php if($record->id ==1): ?>
                                        <img Style="height: 15px" src="<?php echo e(asset('front/img/cc-knet.png')); ?>">
                                        <?php endif; ?>
                                        <?php if($record->id ==5): ?>
                                        <img Style="height: 33px" src="<?php echo e(asset('front/img/digital.png')); ?>">

                                        <?php endif; ?>
                                    </label>
                                </div>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>
                        </div>
                        <?php if($isPhysicalStoreUser): ?>
                        <div class="page-actions payment-gateway-buy-now">
                            <div class="payment-method-store-actions-row">
                                <?php if($hasCustomizedInvoice): ?>
                                <div class="customized-delete-action payment-store-action-wrapper">
                                    <button type="button" class="btn btn-primary buy_now continue-action-button delete-customized-invoice payment-store-action-btn"><?php echo e(__('web.delete_invoice')); ?></button>
                                </div>
                                <div class="customized-preview-action payment-store-action-wrapper" id="customized_pdf_preview_action">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'review'])); ?>" target="_blank" class="btn btn-secondary no-loader preview-pdf-button payment-store-action-btn"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                                <?php endif; ?>

                                <?php if($intlShippingStatus): ?>
                                <button type="submit" class="btn btn-primary buy_now continue-action-button payment-store-action-btn" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data continue-action-button payment-store-action-btn" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if($isPoNoPaymentRequired): ?>
                        <input type="hidden" name="paymentMethod" value="<?php echo e(config('custom_web.cod', 3)); ?>">
                        <?php if($isPhysicalStoreUser): ?>
                        <div class="page-actions payment-gateway-buy-now" style="margin-top: 20px; border-top: none; padding: 0;">
                            <div class="payment-method-store-actions-row">
                                <?php if($hasCustomizedInvoice): ?>
                                <div class="customized-delete-action payment-store-action-wrapper">
                                    <button type="button" class="btn btn-primary buy_now continue-action-button delete-customized-invoice payment-store-action-btn"><?php echo e(__('web.delete_invoice')); ?></button>
                                </div>
                                <div class="customized-preview-action payment-store-action-wrapper" id="customized_pdf_preview_action">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'review'])); ?>" target="_blank" class="btn btn-secondary no-loader preview-pdf-button payment-store-action-btn"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                                <?php endif; ?>

                                <?php if($intlShippingStatus): ?>
                                <button type="submit" class="btn btn-primary buy_now continue-action-button payment-store-action-btn" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data continue-action-button payment-store-action-btn" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="cart-summary sm-hidden">
                    <?php if(count($cart) > 0): ?>
                    <?php if(config('custom_web.show_coupon_card')): ?>
                    <?php echo $__env->make('web.checkout.coupon_card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="cart-details">
                        <h4><?php echo e(__('web.price_details')); ?></h4>
                        <div class="v-flex">
                            <?php if(count($cart) == 0): ?>
                            <?php
                                $customSubTotal = 0;
                                if (!empty($customOrder['items']) && is_array($customOrder['items'])) {
                                    foreach ($customOrder['items'] as $customItem) {
                                        $customSubTotal += (float) ($customItem['total'] ?? 0);
                                    }
                                }

                                $customShipping = (float) ($customOrder['shipping'] ?? 0);
                                $customDiscount = 0;
                                $customDiscountType = $customOrder['discount_type'] ?? '';
                                $customDiscountVal = (float) ($customOrder['discount_val'] ?? 0);
                                if ($customDiscountType === 'percentage') {
                                    $customDiscount = ($customSubTotal * $customDiscountVal) / 100;
                                } elseif ($customDiscountType === 'flat') {
                                    $customDiscount = $customDiscountVal;
                                }
                                $customDiscount = min($customDiscount, $customSubTotal + $customShipping);
                                $customAdvanceAmount = (float) ($customAdvance ?? 0);
                                $customOrderValue = max(0, $customSubTotal + $customShipping - $customDiscount);
                                $customAmountDueNow = max(0, $customOrderValue - $customAdvanceAmount);
                            ?>
                            <h6 class="mt-0"><?php echo e(__('web.custom_order_price_details')); ?></h6>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                <div class="item-price custom-order-subtotal"><?php echo e(Helper::formatNumber($customSubTotal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                <div class="item-price custom-order-shipping"><?php echo e(Helper::formatNumber($customShipping)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php if($customDiscount > 0): ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.discount')); ?></div>
                                <div class="item-price custom-order-discount">- <?php echo e(Helper::formatNumber($customDiscount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.total_order_value')); ?></div>
                                <div class="item-price custom-order-grand-total"><?php echo e(Helper::formatNumber($customDisplayOrderValue)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php if($customDisplayAdvancePaid > 0): ?>
                            <div class="total">
                                <div class="item"><?php echo e(($isAdvanceDoneForReview) ? __('web.advance_paid') : __('web.advance_payment')); ?></div>
                                <div class="item-price custom-advance">- <?php echo e(Helper::formatNumber($customDisplayAdvancePaid)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.pending_amount')); ?></div>
                                <div class="item-price amount-payable custom-order-pending"><?php echo e(Helper::formatNumber($customDisplayPendingAmount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <div class="custom-order-summary-divider"></div>
                            <div class="total grandtotal" style="margin-top:10px;">
                                <div class="item"><?php echo e(__('web.grand_total')); ?> </div>
                                <div class="item-price grand-total" data-grand-total='<?php echo e(number_format((float) $customDisplayAmountPayable, 3, '.', '')); ?>' data-cart-base-total="<?php echo e(number_format((float) 0, 3, '.', '')); ?>"><?php echo e(Helper::formatNumber($customDisplayAmountPayable)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php else: ?>
                            <?php if(count($cart) > 0): ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                <div class="item-price sub_total sub_total_price_detail" data-subtotal="<?php echo e($regularDisplaySubTotal); ?>"><?php echo e(Helper::formatNumber($regularDisplaySubTotal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                <div class="item-price shipping-price"><?php echo e(Helper::formatNumber($regularDisplayShipping)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                             <?php if(!empty($setting['31']) && $setting['31']->setting_val == 1): ?>
                             <div class="total gift_wrap_item <?php echo e($gift_wrap['is_gift'] == 1? 'show': 'hide'); ?>">
                                 <div class="item"><?php echo e(__('web.gift_wrap')); ?></div>
                                 <div class="item-price gift_wrap_price"><?php echo e(Helper::formatNumber($regularDisplayGiftWrap)); ?> <?php echo e($currencyCode); ?></div>
                             </div>
                             <?php endif; ?>
                             <?php if(!empty($setting['41']) && $setting['41']->setting_val == 1): ?>
                             <div class="total gift_box_item <?php echo e($gift_box['is_gift'] == 1? 'show': 'hide'); ?>">
                                 <div class="item"><?php echo e(__('web.gift_box')); ?></div>
                                 <div class="item-price gift_box_price"><?php echo e(Helper::formatNumber($regularDisplayGiftBox)); ?> <?php echo e($currencyCode); ?></div>
                             </div>
                             <?php endif; ?>
                            <?php if(config('custom_web.apply_discount') && $regularDisplayDiscount > 0): ?>
                            <?php
                            $coup = 'hide';
                            if (isset($appliedCoupon) && count($appliedCoupon) > 0) {
                                $coup = 'show';
                            }
                            ?>
                            <div class="total coupon-total <?php echo e($coup); ?>">
                                <div class="item "><span class="coupon_name"><?php echo e(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '-'); ?></span> <span class="link-button remove-coupon-code">Remove</span></div>
                                <div class="item-price coupon-final-discount">- <?php echo e(Helper::formatNumber($couponDiscountFinal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if($customDisplaySubTotal > 0 || $customDisplayShipping > 0 || $customDisplayDiscount > 0 || $customDisplayAdvancePaid > 0): ?>
                            <?php if(count($cart) > 0): ?>
                            <div class="custom-order-summary-divider"></div>
                            <?php endif; ?>
                            <h6 class="<?php echo e(count($cart) > 0 ? 'mt-3' : 'mt-0'); ?>"><?php echo e(__('web.custom_order_price_details')); ?></h6>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                                <div class="item-price"><?php echo e(Helper::formatNumber($customDisplaySubTotal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                                <div class="item-price"><?php echo e(Helper::formatNumber($customDisplayShipping)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php if($customDisplayDiscount > 0): ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.discount')); ?></div>
                                <div class="item-price">- <?php echo e(Helper::formatNumber($customDisplayDiscount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.total_order_value')); ?></div>
                                <div class="item-price"><?php echo e(Helper::formatNumber($customDisplayOrderValue)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php if($customDisplayAdvancePaid > 0): ?>
                            <div class="total">
                                <div class="item"><?php echo e(($isAdvanceDoneForReview) ? __('web.advance_paid') : __('web.advance_payment')); ?></div>
                                <div class="item-price custom-advance">- <?php echo e(Helper::formatNumber($customDisplayAdvancePaid)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.pending_amount')); ?></div>
                                <div class="item-price amount-payable custom-order-pending"><?php echo e(Helper::formatNumber($customDisplayPendingAmount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if(count($cart) > 0 || $customDisplaySubTotal > 0 || $customDisplayShipping > 0 || $customDisplayDiscount > 0 || $customDisplayAdvancePaid > 0): ?>
                            <div class="custom-order-summary-divider"></div>
                            <div class="total grandtotal">
                                <div class="item"><?php echo e(__('web.grand_total')); ?></div>
                                <div class="item-price grand-total" data-grand-total='<?php echo e(number_format((float) $finalTotal, 3, '.', '')); ?>' data-cart-base-total="<?php echo e(number_format((float) $finalTotal - $customDisplayAmountPayable, 3, '.', '')); ?>"><?php echo e(Helper::formatNumber($finalTotal)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if($isPhysicalStoreUser): ?>
                            <div class="page-actions physical-store-right-buy-now">
                                <?php if($intlShippingStatus): ?>
                                <button type="submit" class="btn btn-primary buy_now continue-action-button" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.purchase_now')); ?></button>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data continue-action-button" disabled><?php echo e(__('web.purchase_now')); ?></button>
                                <?php endif; ?>

                                <?php if($hasCustomizedInvoice): ?>
                                <div class="customized-preview-action" id="customized_pdf_preview_action">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'review'])); ?>" target="_blank" class="btn btn-secondary mt-3 no-loader preview-pdf-button"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                                <div class="customized-delete-action" style="margin-top: 12px;">
                                    <button type="button" class="btn btn-primary buy_now continue-action-button delete-customized-invoice"><?php echo e(__('web.delete_invoice')); ?></button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-actions <?php echo e($isPhysicalStoreUser ? 'physical-store-bottom-buy-now' : ''); ?>">
                <?php if($intlShippingStatus): ?>
                <button type="submit" class="btn btn-primary buy_now continue-action-button" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e($isPhysicalStoreUser ? __('web.purchase_now') : __('web.purchase_now')); ?></button>
                <?php else: ?>
                <button type="button" class="btn btn-primary show_intl_box show_intl_box_data continue-action-button" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e($isPhysicalStoreUser ? __('web.purchase_now') : __('web.purchase_now')); ?></button>
                <?php endif; ?>

                <?php if($isPhysicalStoreUser): ?>
                                <?php if($hasCustomizedInvoice): ?>
                                <div class="customized-preview-action" id="customized_pdf_preview_action">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'review'])); ?>" target="_blank" class="btn btn-secondary mt-3 no-loader preview-pdf-button"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                                <div class="customized-delete-action" style="margin-top: 12px;">
                                    <button type="button" class="btn btn-primary buy_now continue-action-button delete-customized-invoice"><?php echo e(__('web.delete_invoice')); ?></button>
                                </div>
                                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
</section>
</form>
<script>
    window.customizedOrderState = <?php echo json_encode(session('customized_order_details'), 15, 512) ?>;
    window.customizedOrderCurrencyCode = <?php echo json_encode($currencyCode, 15, 512) ?>;
    window.customizedReviewBaseGrandTotal = null;
    window.customizedReviewInitialAdvance = <?php echo json_encode($customAdvance, 15, 512) ?>;
    window.customizedReviewCurrentAdvance = 0;
    window.customizedReviewBaseGrandTotal = parseFloat($('.grand-total').first().data('cart-base-total')) || null;
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_specific_js'); ?>
<script>
    $(function() {
        function formatCustomizedCartAmount(amount) {
            return (parseFloat(amount) || 0).toFixed(3) + ' ' + window.customizedOrderCurrencyCode;
        }

        window.customizedReviewSyncVisibleGrandTotal = function(advanceAmount) {
            var $grandTotal = $('.grand-total');
            if (!$grandTotal.length) {
                return;
            }

            var baseTotal = parseFloat($('.grand-total').first().data('cart-base-total')) || window.customizedReviewBaseGrandTotal || 0;
            var advance = parseFloat(advanceAmount) || 0;
            var updatedGrandTotal = Math.max(0, baseTotal + advance);

            $grandTotal.each(function() {
                $(this).text(formatCustomizedCartAmount(updatedGrandTotal));
                $(this).attr('data-grand-total', updatedGrandTotal.toFixed(3));
            });

            window.customizedReviewBaseGrandTotal = baseTotal;
            window.customizedReviewCurrentAdvance = advance;
        };

        function showCustomizedCartLoader() {
            $("#preloader").stop(true, true).removeAttr("style").addClass("show").show();
        }

        function hideCustomizedCartLoader() {
            $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
        }

        window.customizedStorageBaseUrl = "<?php echo e(asset(Storage::url('dummy'))); ?>".replace(/dummy$/, '');

        function getCustomizedItemImageUrl(path) {
            if (!path) {
                return '';
            }

            var normalized = String(path)
                .replace(/^https?:\/\/[^/]+\//, '')
                .replace(/^\/?storage\/?/, '')
                .replace(/^public\//, '')
                .replace(/^\/+/, '');
            if (!normalized) {
                return '';
            }

            return window.customizedStorageBaseUrl + normalized;
        }

        function clearCustomizedOrderFormState() {
            window.customizedOrderState = null;
            window.customizedReviewBaseGrandTotal = null;
            window.customizedReviewInitialAdvance = 0;
            window.customizedReviewCurrentAdvance = 0;

            var form = $('#customized_order_payload_form')[0];
            if (form) {
                form.reset();
            }

            $('#modal_custom_items_list').empty();
            $('#modal_grand_total').text('0.000');
            $('input[name="advance_payment"]').val('');
            $('input[name="custom_shipping"]').val('');
            $('input[name="custom_discount_val"]').val('');
            $('select[name="custom_discount_type"]').val('');
            $('input[name="cust_phone"]').val('');
            $('input[name="cust_email"]').val('');
            $('input[name="cust_name"]').val('');
            $('input[name="cust_address_title"]').val('');
            $('input[name="cust_country_id"]').val('');
            $('input[name="cust_city"]').val('');
            $('input[name="cust_block"]').val('');
            $('input[name="cust_street"]').val('');
            $('input[name="cust_house_number"]').val('');
            $('input[name="cust_floor"]').val('');
            $('input[name="cust_address_detail"]').val('');
            $('input[name="cust_address_url"]').val('');
            $('textarea[name="cust_note"]').val('');
            $('select[name="customer_address_id"]').empty().append('<option value=""><?php echo e(__('web.select_address')); ?></option>').prop('selectedIndex', 0).val('').trigger('change');
            $('select[name="cust_country_id"]').prop('selectedIndex', 0).val('').trigger('change');
            $('.cust_city_id').empty().append('<option value=""><?php echo e(__('web.select_area')); ?></option>').prop('selectedIndex', 0).val('').trigger('change');
            $('.cust_city_list').hide();
            $('.cust_city_text').show();
            $('.cust_address_title_wrapper').removeClass('d-none');
            $('#address_dropdown_wrapper').hide();
            $('#existing_custom_city_id').val('');
            $('#existing_custom_city_name').val('');
            window.customerAddresses = [];
            lastFetchedPhone = '';
            lastFetchedEmail = '';
        }

        function getCustomizedCartDiscountAmount(subTotal) {
            var discountType = window.customizedOrderState.discount_type || '';
            var discountVal = parseFloat(window.customizedOrderState.discount_val || 0) || 0;
            var discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subTotal * discountVal) / 100;
            } else if (discountType === 'flat') {
                discountAmount = discountVal;
            }

            return Math.min(discountAmount, subTotal);
        }

        function renderCustomizedCartSummary() {
            if (!window.customizedOrderState || !Array.isArray(window.customizedOrderState.items)) {
                return;
            }

            var subTotal = 0;
            (window.customizedOrderState.items || []).forEach(function(item) {
                subTotal += parseFloat(item.total || 0) || 0;
            });

            var shipping = parseFloat(window.customizedOrderState.shipping || 0) || 0;
            var advance = parseFloat((window.customizedOrderState.payment && window.customizedOrderState.payment.advance) || 0) || 0;
            var discountAmount = getCustomizedCartDiscountAmount(subTotal);
            var customGrandTotal = Math.max(0, subTotal + shipping - discountAmount);
            var pendingAmount = Math.max(0, customGrandTotal - advance);
            var isAdvanceDone = (window.customizedOrderState.is_advance_payment_done == 1) || !!(window.customizedOrderState.payment_state && window.customizedOrderState.payment_state.advance_completed);
            var isExistingOrder = !!(window.customizedOrderState.temporary_order_id || window.customizedOrderState.order_id);
            var isGeneratePendingRequested = (window.customizedOrderState.payment && typeof window.customizedOrderState.payment.generate_pending_link !== 'undefined')
                ? Boolean(window.customizedOrderState.payment.generate_pending_link)
                : (typeof window.customizedOrderState.generate_pending_link !== 'undefined' ? Boolean(window.customizedOrderState.generate_pending_link) : false);

            var payableCustomAmount = 0;
            if (isExistingOrder) {
                if (isGeneratePendingRequested) {
                    payableCustomAmount = pendingAmount;
                } else if (isAdvanceDone) {
                    payableCustomAmount = 0;
                } else {
                    payableCustomAmount = (advance > 0) ? advance : 0;
                }
            } else {
                payableCustomAmount = (advance > 0) ? advance : 0;
            }

            $('.custom-order-subtotal').text(formatCustomizedCartAmount(subTotal));
            $('.custom-order-shipping').text(formatCustomizedCartAmount(shipping));
            $('.custom-order-discount').text('- ' + formatCustomizedCartAmount(discountAmount));
            $('.custom-order-grand-total').text(formatCustomizedCartAmount(customGrandTotal));
            $('.custom-order-pending').text(formatCustomizedCartAmount(pendingAmount));
            window.customizedReviewSyncVisibleGrandTotal(payableCustomAmount);
        }

        function renderCustomizedCartModalFromState() {
            if (!window.customizedOrderState || !Array.isArray(window.customizedOrderState.items)) {
                return;
            }

            var items = window.customizedOrderState.items || [];
            var $list = $('#modal_custom_items_list');

            if (!items.length) {
                $list.empty();
                $('#modal_grand_total').text('0.000');
                $('input[name="advance_payment"]').val('');
                return;
            }

            var $rows = $list.find('.modal-custom-item-row');
            while ($rows.length > items.length) {
                $rows.last().remove();
                $rows = $list.find('.modal-custom-item-row');
            }

            if (!$rows.length) {
                return;
            }

            while ($rows.length < items.length) {
                addModalCustomItemRow();
                $rows = $list.find('.modal-custom-item-row');
            }

            if (!$rows.length) {
                return;
            }

            items.forEach(function(item, index) {
                var $row = $rows.eq(index);
                if (!$row.length) {
                    return;
                }

                $row.find('input[name="custom_item_code[]"]').val(item.item_code || '');
                $row.find('input[name="custom_product_title[]"]').val(item.product_title || '');
                $row.find('textarea[name="custom_desc[]"]').val(item.description || '');
                $row.find('input[name="custom_color[]"]').val(item.color || '');
                $row.find('input[name="custom_size[]"]').val(item.size || '');
                $row.find('input[name="custom_price[]"]').val(parseFloat(item.price || 0).toFixed(3));
                $row.find('input[name="custom_qty[]"]').val(parseInt(item.quantity, 10) || 1);
                $row.find('input[name="custom_total[]"]').val(parseFloat(item.total || 0).toFixed(3));
                $row.find('input[name="custom_item_existing_image[]"]').val(item.image || '');

                var $preview = $row.find('.custom-item-image-preview');
                if (item.image) {
                    var previewUrl = getCustomizedItemImageUrl(item.image);
                    $preview.html(
                        '<a href="' + previewUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                            '<img src="' + previewUrl + '" alt="<?php echo e(__('web.photo_reference')); ?>" style="width:10rem; height:10rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'<?php echo e(asset(Storage::url('default.png'))); ?>\'">' +
                        '</a>'
                    );
                } else {
                    $preview.empty();
                }
            });

            $list.find('.add-modal-custom-item').remove();
            var $lastActionCol = $list.find('.modal-custom-item-row:last .icon-btn');
            if ($lastActionCol.length && !$lastActionCol.find('.add-modal-custom-item').length) {
                $lastActionCol.prepend('<span class="add-modal-custom-item mr-2" style="cursor:pointer; font-size: 16px; display:block;" title="<?php echo e(__('web.add_item')); ?>"><i class="fa fa-plus"></i></span>');
            }

            $('input[name="custom_shipping"]').val(parseFloat(window.customizedOrderState.shipping || 0).toFixed(3));
            $('select[name="custom_discount_type"]').val(window.customizedOrderState.discount_type || '');
            $('input[name="custom_discount_val"]').val((function() {
                var discountType = window.customizedOrderState.discount_type || '';
                var discountVal = parseFloat(window.customizedOrderState.discount_val || 0) || 0;
                return discountType === 'percentage' ? String(discountVal) : discountVal.toFixed(3);
            })());
            $('input[name="advance_payment"]').val(parseFloat((window.customizedOrderState.payment && window.customizedOrderState.payment.advance) || 0).toFixed(3));
            $('textarea[name="cust_note"]').val((window.customizedOrderState.customer && window.customizedOrderState.customer.note) || '');

            var subTotal = 0;
            (window.customizedOrderState.items || []).forEach(function(item) {
                subTotal += parseFloat(item.total || 0) || 0;
            });

            var customGrandTotal = Math.max(
                0,
                subTotal + (parseFloat(window.customizedOrderState.shipping || 0) || 0) - getCustomizedCartDiscountAmount(subTotal)
            );

            $('#modal_grand_total').text(customGrandTotal.toFixed(3));
        }

        $(document).on('change', 'select[name="custom_discount_type"]', function() {
            var discountType = $(this).val();
            var discountInput = $('input[name="custom_discount_val"]');

            discountInput.attr('step', discountType === 'percentage' ? '0.1' : '0.001');

            if (!discountType) {
                discountInput.val('');
                return;
            }

            var discountVal = parseFloat(discountInput.val() || 0) || 0;
            discountInput.val(discountType === 'percentage' ? String(discountVal) : discountVal.toFixed(3));
        });

        function syncCustomizedCartOrder() {
            if (!window.customizedOrderState || !Array.isArray(window.customizedOrderState.items)) {
                return;
            }

            var formData = new FormData();
            formData.append('_token', "<?php echo e(csrf_token()); ?>");
            formData.append('custom_req_type', window.customizedOrderState.type || 'purchase_order');

            var customer = window.customizedOrderState.customer || {};
            var liveNote = $('textarea[name="cust_note"]').val();
            if (typeof liveNote === 'string') {
                customer.note = liveNote;
                window.customizedOrderState.customer = customer;
            }
            [
                ['cust_phone', customer.phone || ''],
                ['cust_email', customer.email || ''],
                ['cust_name', customer.name || ''],
                ['cust_country_id', customer.country_id || ''],
                ['cust_city', customer.city || ''],
                ['cust_city_id', customer.city_id || ''],
                ['cust_block', customer.block || ''],
                ['cust_street', customer.street || ''],
                ['cust_house_number', customer.house_number || ''],
                ['cust_floor', customer.floor || ''],
                ['cust_address_detail', customer.address_detail || ''],
                ['cust_address_url', customer.address_url || ''],
                ['cust_note', customer.note || ''],
                ['cust_address_title', customer.address_title_text || ''],
                ['customer_address_id', customer.customer_address_id || '']
            ].forEach(function(pair) {
                formData.append(pair[0], pair[1]);
            });

            (window.customizedOrderState.items || []).forEach(function(item) {
                formData.append('custom_item_code[]', item.item_code || '');
                formData.append('custom_product_title[]', item.product_title || '');
                formData.append('custom_desc[]', item.description || '');
                formData.append('custom_color[]', item.color || '');
                formData.append('custom_size[]', item.size || '');
                formData.append('custom_price[]', parseFloat(item.price || 0).toFixed(3));
                formData.append('custom_qty[]', parseInt(item.quantity, 10) || 1);
                formData.append('custom_item_existing_image[]', item.image || '');
            });

            formData.append('custom_shipping', parseFloat(window.customizedOrderState.shipping || 0).toFixed(3));
            formData.append('custom_discount_type', window.customizedOrderState.discount_type || '');
            formData.append('custom_discount_val', parseFloat(window.customizedOrderState.discount_val || 0).toFixed(3));
            formData.append('advance_payment', parseFloat((window.customizedOrderState.payment && window.customizedOrderState.payment.advance) || 0).toFixed(3));

            $.ajax({
                url: "<?php echo e(route('web.cart.submit_customized_order')); ?>",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    showCustomizedCartLoader();
                },
                complete: function() {
                    hideCustomizedCartLoader();
                }
            });
        }

        var customizedCartSyncTimer = null;

        function queueCustomizedCartSync() {
            clearTimeout(customizedCartSyncTimer);
            customizedCartSyncTimer = setTimeout(syncCustomizedCartOrder, 250);
        }

        function reindexCustomizedCartRows() {
            $('.custom-order-item').each(function(rowIndex) {
                $(this).attr('data-index', rowIndex);
                $(this).find('.custom-order-delete').attr('data-index', rowIndex);
                $(this).find('.custom-order-decrease').attr('data-index', rowIndex);
                $(this).find('.custom-order-increase').attr('data-index', rowIndex);
                $(this).find('.custom-order-qty')
                    .attr('data-index', rowIndex)
                    .attr('name', 'custom_quantity_' + rowIndex);
                $(this).find('.custom-order-line-total').attr('data-index', rowIndex);
            });
        }

        function removeCustomizedCartItem(index) {
            if (!window.customizedOrderState || !Array.isArray(window.customizedOrderState.items) || !window.customizedOrderState.items[index]) {
                return;
            }

            window.customizedOrderState.items.splice(index, 1);
            $('.custom-order-item[data-index="' + index + '"]').remove();
            reindexCustomizedCartRows();
            renderCustomizedCartModalFromState();

            if (!window.customizedOrderState.items.length) {
                $.ajax({
                    url: "<?php echo e(route('web.cart.remove_customized_order')); ?>",
                    method: "POST",
                    data: {
                        _token: "<?php echo e(csrf_token()); ?>"
                    },
                    beforeSend: function() {
                        showCustomizedCartLoader();
                    },
                    complete: function() {
                        hideCustomizedCartLoader();
                    },
                    success: function(response) {
                        if (response.status) {
                            $('.custom-order-cart-section').remove();
                            $('.custom-advance').closest('.total').remove();
                            clearCustomizedOrderFormState();
                            $('#trigger_customized_order_modal').prop('checked', false);
                            $('#customizedOrderModal').modal('hide');
                        }
                    }
                });
                return;
            }

            renderCustomizedCartSummary();
            syncCustomizedCartOrder();
        }

        function updateCustomizedCartQuantity(index, delta) {
            if (!window.customizedOrderState || !Array.isArray(window.customizedOrderState.items) || !window.customizedOrderState.items[index]) {
                return;
            }

            var item = window.customizedOrderState.items[index];
            var currentQty = parseInt(item.quantity, 10) || 1;
            var nextQty = Math.max(1, currentQty + delta);
            var unitPrice = parseFloat(item.price || 0) || 0;

            item.quantity = nextQty;
            item.total = parseFloat((nextQty * unitPrice).toFixed(3));

            $('.custom-order-qty[data-index="' + index + '"]').val(nextQty).attr('value', nextQty);
            $('.custom-order-line-total[data-index="' + index + '"]').text(formatCustomizedCartAmount(item.total));

            renderCustomizedCartSummary();
            queueCustomizedCartSync();
        }

        $(document).on('click', '.custom-order-decrease', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            updateCustomizedCartQuantity(parseInt($(this).data('index'), 10), -1);
        });

        $(document).on('click', '.custom-order-increase', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            updateCustomizedCartQuantity(parseInt($(this).data('index'), 10), 1);
        });

        $(document).on('click', '.custom-order-delete', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            removeCustomizedCartItem(parseInt($(this).data('index'), 10));
        });

        $(document).on('input change', 'textarea[name="cust_note"]', function() {
            if (!window.customizedOrderState) {
                return;
            }

            if (!window.customizedOrderState.customer) {
                window.customizedOrderState.customer = {};
            }

            window.customizedOrderState.customer.note = $(this).val();
        });

        $('#customizedOrderModal').on('show.bs.modal', function() {
            renderCustomizedCartModalFromState();
        });

        $(document).on('change', 'input[name="paymentMethod"]', function() {
            var selectedPaymentMethod = $(this).val();
            var isPayAtShop = (parseInt(selectedPaymentMethod, 10) === 6 || parseInt(selectedPaymentMethod, 10) === 4);
            var customState = window.customizedOrderState || {};
            var tempOrderId = customState.temporary_order_id || null;
            var actualOrderId = customState.order_id || null;
            var subTotal = 0;
            (customState.items || []).forEach(function(item) {
                subTotal += parseFloat(item.total || 0) || 0;
            });
            var shipping = parseFloat(customState.shipping || 0) || 0;
            var advance = parseFloat((customState.payment && customState.payment.advance) || 0) || 0;
            var discount = (typeof getCustomizedCartDiscountAmount === 'function') ? getCustomizedCartDiscountAmount(subTotal) : 0;
            var totalAmount = Math.max(0, subTotal + shipping - discount);
            var pendingAmount = Math.max(0, totalAmount - advance);

            console.log('[PAYMENT_DEBUG] Payment method selected:', {
                selectedPaymentMethod: selectedPaymentMethod,
                paymentMethodValue: selectedPaymentMethod,
                temporaryOrderId: tempOrderId,
                actualOrderId: actualOrderId,
                totalAmount: totalAmount,
                advanceAmount: advance,
                pendingAmount: pendingAmount
            });

            if (isPayAtShop) {
                console.log('[PAYMENT_DEBUG] Pay At Shop selected');
            }
        });

        renderCustomizedCartSummary();
        renderCustomizedCartModalFromState();
    });
</script>
<?php echo $__env->make('web.cart.save-note', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_specific_html'); ?>
<?php echo $__env->make('web.checkout.checkout_address', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="psd" style="top: -19px; left: 0; display: none;">
    <img src="<?php echo e(asset('front/img/checkout.jpg')); ?>">
</div>

<?php if(auth()->user() && ((auth()->user()->id == config('config.store_admin_id')) || (auth()->user()->user_type == "physical_store_user"))): ?>
<div class="modal fade store-confirmation-modal" id="storeUserConfirmModal" tabindex="-1" role="dialog" aria-labelledby="storeUserConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <span class="close" data-dismiss="modal">
            <i class="icon-close"></i>
        </span>
      <div class="modal-body fancy">
        <h1 class="address_title"><?php echo e(__('web.confirmation')); ?></h1>
        <span id="storeUserConfirmMessage"><?php echo e(__('web.place_order_confirm_message')); ?></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('web.cancel')); ?></button>
        <button type="button" class="btn btn-primary" id="confirmStoreOrderBtn"><?php echo e(__('web.confirmed')); ?></button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/checkout/cart_review.blade.php ENDPATH**/ ?>
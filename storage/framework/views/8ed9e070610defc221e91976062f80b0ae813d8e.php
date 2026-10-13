
<?php $__env->startSection('page_title', trans('web.cart')); ?>
<?php $__env->startSection('body_class', 'configMonogram'); ?>
<?php $__env->startSection('content'); ?>
<style>
    .main-info .product-actions a {
        width: auto;
        margin: 0 15px 0 0;
        font-size: 14px;
        text-transform: uppercase;
    }

    .icon-btn {
        background: transparent;
        border: none;
        padding: 4px 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .icon-btn i {
        font-size: 16px;
    }

    .custom-order-last {
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #d9d9d9;
    }

    .custom-order-summary-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .custom-order-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 5px 0;
        width: 100%;
        max-width: 295px;
        margin-left: auto;
    }

    .custom-order-summary-item {
        display: flex;
        align-items: baseline;
        justify-content: flex-start;
        gap: 12px;
        min-width: 0;
    }

    .custom-order-summary-item label {
        display: block;
        flex: 0 0 190px;
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        text-transform: none;
        color: #111;
        white-space: nowrap;
        text-align: left;
    }

    .custom-order-summary-item span {
        display: block;
        flex: 1 1 auto;
        font-size: 16px;
        color: #222;
        white-space: nowrap;
        text-align: right;
    }

    .custom-order-summary-divider {
        margin: 16px 0;
        border-top: 1px solid #d9d9d9;
    }

    .custom-order-summary-box {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #d9d9d9;
    }

    .customized-order-summary-lines {
        margin-top: 12px;
    }

    .customized-order-summary-line {
        display: flex;
        align-items: baseline;
        justify-content: flex-start;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 16px;
        line-height: 1.35;
    }

    .customized-order-summary-line label {
        margin: 0;
        font-weight: 400;
        color: #111;
        font-size: 16px;
    }

    .customized-order-summary-line span {
        white-space: nowrap;
        text-align: left;
    }

    .customized-order-summary-line small {
        display: inline-block;
        margin-left: 4px;
        color: #777;
        font-size: 12px;
    }

    #customizedOrderModal .modal-content {
        max-height: calc(100vh - 40px);
    }

    #customizedOrderModal .modal-header {
        justify-content: space-between;
        align-items: center;
    }

    #customizedOrderModal .modal-header .address_title {
        margin: 0 !important;
        line-height: 1.2;
    }

    #customizedOrderModal .modal-header .close {
        position: static;
        top: auto;
        right: auto;
        width: auto;
        height: auto;
        margin: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    #customizedOrderModal .modal-body.fancy {
        max-height: calc(100vh - 180px);
        overflow-y: auto;
    }

    .custom-item-image-preview {
        position: relative;
        display: inline-block;
    }

    .custom-item-image-preview .remove-image-btn {
        position: absolute;
        top: -6px;
        right: -6px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        z-index: 10;
        font-weight: bold;
        line-height: 1;
        border: 1px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        user-select: none;
    }

    .custom-item-image-preview .remove-image-btn:hover {
        background: #dc3545;
    }

    #generate_pending_link_wrapper {
        margin-top: 12px !important;
    }

    #generate_pending_link_wrapper label,
    .generate-pending-label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        user-select: none !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #222 !important;
        margin: 0 !important;
        padding: 0 !important;
        position: relative !important;
        line-height: 1.4 !important;
    }

    #generate_pending_link_wrapper label::before,
    #generate_pending_link_wrapper label::after,
    .generate-pending-label::before,
    .generate-pending-label::after,
    .generate-pending-checkbox + label::before,
    .generate-pending-checkbox + label::after,
    .generate-pending-checkbox:checked + label::before,
    .generate-pending-checkbox:checked + label::after {
        display: none !important;
        content: none !important;
        border: none !important;
        background: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
    }

    .generate-pending-checkbox {
        width: 18px !important;
        height: 18px !important;
        min-width: 18px !important;
        margin: 0 !important;
        cursor: pointer !important;
        opacity: 1 !important;
        position: static !important;
        display: inline-block !important;
        appearance: checkbox !important;
        -webkit-appearance: checkbox !important;
        accent-color: #111 !important;
        vertical-align: middle !important;
    }

    @media (max-width: 575px) {
        .custom-order-summary-box {
            padding-top: 14px;
        }

        .custom-order-summary-grid {
            max-width: 100%;
            margin-left: 0;
        }

        .custom-order-summary-item {
            gap: 8px;
        }

        .custom-order-summary-item label {
            flex: 1 1 auto;
            font-size: 14px;
            white-space: normal;
            line-height: 1.25;
        }

        .custom-order-summary-item span {
            flex: 0 0 auto;
            font-size: 14px;
        }
    }

    .custom-order-image-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 210px;
        overflow: hidden;
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

    .cart-empty-price-detail {
        margin-top: 0;
    }

    .desktop-empty-cart-left-action,
    .desktop-empty-cart-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 1.25rem;
    }

    .desktop-empty-cart-customized-order {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
    }

    /* Customized Order Info Card (Desktop & Mobile) */
    .customized-order-info-card {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .customized-order-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .customized-order-info-grid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    .customized-order-customer-col {
        flex: 0 0 41.666667%;
        max-width: 41.666667%;
        padding-left: 15px;
        padding-right: 15px;
        box-sizing: border-box;
    }

    .customized-order-address-col {
        flex: 0 0 58.333333%;
        max-width: 58.333333%;
        padding-left: 15px;
        padding-right: 15px;
        box-sizing: border-box;
    }

    .customized-order-address-subgrid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -10px;
        margin-right: -10px;
    }

    .customized-order-address-subgrid .customized-order-info-list {
        flex: 0 0 50%;
        max-width: 50%;
        padding-left: 10px;
        padding-right: 10px;
        box-sizing: border-box;
    }

    .customized-order-note-col {
        flex: 0 0 100%;
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
        margin-top: 10px;
        box-sizing: border-box;
    }

    .customized-order-info-title {
        font-size: 13px;
        font-weight: bold;
        color: #555;
        margin-bottom: 6px;
    }

    .customized-order-info-item {
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 4px;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .cart-bottom-primary-actions {
        margin-top: 20px;
        gap: 15px;
    }

    .cart-bottom-primary-actions .customized-preview-action {
        width: 50%;
    }

    .cart-bottom-primary-actions button.place_order2 {
        width: 50%;
    }

    /* Product card options column sizing and wrapping fix (Laptop / Desktop / All Viewports) */
    .cart-item.main-info .prod-details {
        min-width: 0;
        flex: 1 1 auto;
    }

    .cart-item.main-info .product-options {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 12px 24px;
        margin: 10px 0;
        width: 100%;
        max-width: 100%;
    }

    .cart-item.main-info .product-options .form-item {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 60px;
        margin-right: 0;
        flex: 0 0 auto;
    }

    .cart-item.main-info .product-options .form-item.barcode-cell {
        min-width: 120px;
    }

    .cart-item.main-info .product-options .form-item label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #111;
        margin-bottom: 4px;
        white-space: nowrap;
        letter-spacing: 0.5px;
    }

    .cart-item.main-info .product-options .form-item span {
        font-size: 14px;
        color: #333;
        white-space: normal;
        word-break: normal;
        overflow-wrap: break-word;
    }

    .cart-item.main-info .product-options.product-options-description {
        display: block;
        width: 100%;
        max-width: 100%;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .cart-item.main-info .product-options.product-options-description .form-item {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .cart-item.main-info .product-options.product-options-description span {
        display: block;
        width: 100%;
        max-width: 100%;
        font-size: 13px;
        line-height: 1.5;
        color: #444;
        white-space: normal;
        word-break: normal;
        overflow-wrap: break-word;
    }

    /* RESPONSIVE BREAKPOINTS (Mobile & Tablet <= 991px) */
    @media (max-width: 991px) {
        section.cart {
            padding: 10px 0 40px !important;
            overflow-x: hidden !important;
        }

        section.cart .container {
            padding-left: 15px !important;
            padding-right: 15px !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        .cart .h-flex,
        .cart .h-flex.flex-nowrap {
            flex-direction: column !important;
            flex-wrap: wrap !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            gap: 0 !important;
        }

        .cart-items-summary {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .cart-summary {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin: 20px 0 0 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        .cart-summary .cart-details {
            width: 100% !important;
            padding: 20px 16px !important;
            box-sizing: border-box !important;
            border-radius: 8px !important;
            margin: 0 !important;
        }

        /* Hide duplicate action buttons inside Price Detail on mobile */
        .cart-summary .page-actions {
            display: none !important;
        }

        /* Customized Order Info Card - Mobile */
        .customized-order-info-card {
            padding: 16px !important;
            margin-bottom: 20px !important;
            border-radius: 8px !important;
        }

        .customized-order-header-row {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
        }

        .customized-order-info-grid {
            flex-direction: column !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            gap: 15px !important;
        }

        .customized-order-customer-col,
        .customized-order-address-col,
        .customized-order-note-col {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .customized-order-address-subgrid {
            flex-direction: column !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            gap: 0 !important;
        }

        .customized-order-address-subgrid .customized-order-info-list {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Product Card Mobile Layout */
        .cart-item.main-info {
            width: 100% !important;
            margin-bottom: 20px !important;
            border-radius: 8px !important;
            padding: 0 !important;
            background: #f0f0f0 !important;
            border: 1px solid #e5e5e5 !important;
            box-sizing: border-box !important;
        }

        .cart-item.main-info > .h-flex {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            padding: 16px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .cart-item.main-info .prod-image {
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 0 12px 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .cart-item.main-info .prod-image a,
        .cart-item.main-info .custom-order-image-wrap {
            width: 100% !important;
            max-width: 110px !important;
            height: 110px !important;
            min-width: 0 !important;
            margin: 0 0 10px 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            border-radius: 6px !important;
        }

        .cart-item.main-info .prod-image img {
            max-height: 110px !important;
            max-width: 100% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
        }

        .cart-item.main-info .prod-details {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: none !important;
        }

        .cart-item.main-info .v-flex.sm-visible {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: baseline !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            margin-bottom: 12px !important;
            width: 100% !important;
        }

        .cart-item.main-info .v-flex.sm-visible .prod-name h3 {
            font-size: 16px !important;
            font-weight: 700 !important;
            margin: 0 !important;
            color: #111 !important;
        }

        .cart-item.main-info .v-flex.sm-visible .prod-price {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #111 !important;
            white-space: nowrap !important;
        }

        /* Responsive Product Options - prevent letter-by-letter breaking */
        .cart-item.main-info .product-options {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            align-items: flex-start !important;
            gap: 12px 20px !important;
            margin: 10px 0 !important;
            width: 100% !important;
        }

        .cart-item.main-info .product-options .form-item {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            min-width: 65px !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            flex: 0 0 auto !important;
        }

        .cart-item.main-info .product-options .form-item.barcode-cell {
            min-width: 110px !important;
        }

        .cart-item.main-info .product-options .form-item label {
            font-size: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            color: #111 !important;
            margin-bottom: 4px !important;
            white-space: nowrap !important;
            letter-spacing: 0.5px !important;
        }

        .cart-item.main-info .product-options .form-item span {
            font-size: 14px !important;
            color: #333 !important;
            white-space: normal !important;
            word-break: normal !important;
            overflow-wrap: break-word !important;
        }

        .cart-item.main-info .product-options .counter {
            height: 38px !important;
            width: auto !important;
            max-width: 130px !important;
            margin: 0 !important;
        }

        .cart-item.main-info .product-options .counter .h-flex {
            height: 38px !important;
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            background: #fff !important;
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
        }

        .cart-item.main-info .product-options .counter button {
            padding: 0 10px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .cart-item.main-info .product-options .counter input.qty_add {
            width: 40px !important;
            text-align: center !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
        }

        .cart-item.main-info .product-options-description {
            width: 100% !important;
            margin-top: 10px !important;
        }

        .cart-item.main-info .product-options-description .form-item {
            width: 100% !important;
            max-width: 100% !important;
        }

        .cart-item.main-info .product-options-description span {
            display: block !important;
            font-size: 13px !important;
            line-height: 1.45 !important;
            color: #444 !important;
            word-break: break-word !important;
        }

        /* Action Buttons on Mobile */
        .cart-item.main-info .product-actions.sm-visible {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
            margin-top: 14px !important;
            width: 100% !important;
            padding: 0 !important;
        }

        .cart-item.main-info .product-actions.sm-visible .btn {
            height: 38px !important;
            padding: 6px 14px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 4px !important;
            margin: 0 !important;
        }

        /* Bottom Primary Actions Mobile & Tablet (After Price Detail) */
        .cart-page-bottom-actions {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: stretch !important;
            gap: 12px !important;
            width: 100% !important;
            margin: 20px 0 10px 0 !important;
        }

        .cart-page-bottom-actions .customized-preview-action {
            flex: 1 1 0% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .cart-page-bottom-actions .customized-preview-action a.preview-pdf-button {
            width: 100% !important;
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }

        .cart-page-bottom-actions button.place_order2 {
            flex: 1 1 0% !important;
            width: 100% !important;
            height: 48px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }

        /* Back to Shop Mobile */
        .cart-actions.sm-visible {
            width: 100% !important;
            margin-top: 20px !important;
            margin-bottom: 20px !important;
            text-align: left !important;
            padding: 0 5px !important;
        }

        .cart-actions.sm-visible a.link-button {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #111 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-decoration: none !important;
        }

        /* Mobile Header Adjustments */
        .menu .navbar .navbar-collapse {
            padding: 0 12px !important;
            flex-wrap: nowrap !important;
        }

        .menu .navbar-brand {
            flex: 0 1 auto !important;
            min-width: 0 !important;
        }

        .menu .right-menu {
            flex: 0 0 auto !important;
        }

        .menu .quick-menu {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .select-currency {
            margin-left: 4px !important;
            width: 52px !important;
            padding: 4px 14px 4px 4px !important;
            font-size: 10px !important;
            background-position: calc(100% - 4px) center !important;
        }
    }

    @media (max-width: 480px) {
        .cart-page-bottom-actions {
            flex-direction: column !important;
            gap: 10px !important;
        }

        .cart-page-bottom-actions .customized-preview-action,
        .cart-page-bottom-actions button.place_order2 {
            width: 100% !important;
            flex: none !important;
        }
    }

    @media (max-width: 991px) {
        .custom-order-summary-grid {
            grid-template-columns: 1fr;
        }

        /* Tablet and Mobile responsive styles for customized order form */
        .modal-custom-item-row {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            flex-wrap: wrap;
        }
        
        .modal-custom-item-row:last-child {
            border-bottom: none;
        }
        
        /* Move icon column to the end on tablet and mobile */
        .icon-column {
            order: 999;
            padding-top: 0 !important;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center !important;
        }
        
        .custom-item-image-field {
            margin-bottom: 10px;
        }
        
        .modal-custom-item-row .form-item {
            margin-bottom: 10px;
        }
    }

    /* @media (min-width: 992px) {
        .cart-empty-price-detail {
            margin-top: 3.7rem;
        }
    }*/
</style>
<?php
//session()->put('cart', []);
$shippingCharge = $totalShipping;
$cart = $cart ?? [];
$size = '';
$total = 0;
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$user = Auth::user();
/* if ($user) {
    $cart = Helper::getShoppingCart($user->id);
} elseif (session('cart')) {
    $cart = Helper::getShoppingCart();
} */

$tax = Helper::getTax();
$tax = $tax ? $tax : 0;
$taxValue = ($total * $tax) / 100;

$discount = config('custom_web.discount', 20);
$countryData = Helper::getDefaultCountry();
$hasCheckoutError = session()->has('checkout_error');
$hasRegularOrderItems = count($cart) > 0;
$showDesktopEmptyCustomizedOrderToggle = isset(auth()->user()->user_type) && auth()->user()->user_type == "physical_store_user" && !$hasRegularOrderItems;



if ($countryData) {



    /*
     *  Convert Currency Rate
     */

}

if ($user) {

    $address = App\UserModel\UserAddress::where('is_default', 1)->where('user_id', $user->id)->first();


    if ($address) {


    }
}
$existingCust = session('customized_order_details');
if (!empty($existingCust) && is_array($existingCust)) {
    $existingCustItems = [];
    foreach (($existingCust['items'] ?? []) as $existingCustItem) {
        $hasData = false;
        foreach (['item_code', 'product_title', 'description', 'color', 'size', 'price', 'quantity', 'image'] as $field) {
            if (!empty($existingCustItem[$field])) {
                $hasData = true;
                break;
            }
        }
        if ($hasData) {
            $existingCustItems[] = $existingCustItem;
        }
    }
    $existingCust['items'] = array_values($existingCustItems);
    session(['customized_order_details' => $existingCust]);
}
$modalSubTotal = 0;
$modalShipping = 0;
$modalDiscountAmount = 0;
$modalTotalOrderValue = 0;
$modalAdvancePaid = 0;
$modalPendingAmount = 0;
$modalDiscountType = $existingCust['discount_type'] ?? '';
$modalDiscountLabel = '';
if (!empty($existingCust['items']) && is_array($existingCust['items'])) {
    foreach ($existingCust['items'] as $it) {
        $modalSubTotal += floatval($it['total'] ?? 0);
    }
}
$modalShipping = floatval($existingCust['shipping'] ?? 0);
$modalAdvancePaid = floatval($existingCust['payment']['advance'] ?? 0);
$modalDiscountVal = floatval($existingCust['discount_val'] ?? 0);
if ($modalDiscountType === 'percentage') {
    $modalDiscountAmount = ($modalSubTotal * $modalDiscountVal) / 100;
    $modalDiscountLabel = __('web.discount_percentage');
} elseif ($modalDiscountType === 'flat') {
    $modalDiscountAmount = $modalDiscountVal;
    $modalDiscountLabel = __('web.discount_flat');
}
$modalDiscountAmount = min($modalDiscountAmount, $modalSubTotal);
$modalTotalOrderValue = max(0, $modalSubTotal + $modalShipping - $modalDiscountAmount);
$modalPendingAmount = max(0, $modalTotalOrderValue - $modalAdvancePaid);
?>
<?php $__env->startSection('additional_css'); ?>
<style>
    .cart .col-md-8,
    .cart-checkout .col-md-8,
    .cart-review .col-md-8 {
        <?php if(count($cart)==0): ?> border-right:0px;
        <?php endif; ?>
    }

    .customized-order-financial-row {
        margin-top: 20px;
    }

    .customized-order-financial-col {
        display: flex;
        flex-direction: column;
    }

    .customized-order-section-title {
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
        margin-top: 0;
    }

    .customized-order-financial-block {
        height: 100%;
    }

    #customizedOrderModal .modal-body.fancy {
        padding-top: 18px;
        padding-bottom: 18px;
    }

    #customizedOrderModal .modal-body .address_title {
        margin-bottom: 6px !important;
        line-height: 1.1;
    }

    #customizedOrderModal .form-group {
        margin-bottom: 10px;
    }

    #customizedOrderModal .form-group:first-of-type {
        margin-top: -6px;
        margin-bottom: 6px;
    }

    .saved-drafts-form-group {
        margin-top: 0 !important;
        margin-bottom: 14px !important;
    }

    .customized-draft-dropdown-label {
        font-size: 13px;
        font-weight: 600;
        color: #444;
        margin-bottom: 6px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .saved-draft-select-container {
        position: relative;
        width: 100%;
    }

    .saved-draft-select-container select {
        width: 100%;
        height: 42px;
        font-size: 14px;
        border: 1px solid #dcdcdc;
        border-radius: 4px;
        padding-right: 35px;
        background-color: #fff;
    }

    .draft-loader-spinner {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    #customizedOrderModal .form-item.h-flex {
        margin-bottom: 0;
        margin-top: 0;
        align-items: center;
    }

    #customizedOrderModal .form-item.h-flex .radio {
        min-width: 0;
        margin-bottom: 0;
        padding-top: 0;
    }

    #customizedOrderModal .form-item.h-flex .radio label {
        margin-bottom: 0;
        min-width: 0;
    }

    #customizedOrderModal h5 {
        margin-top: 12px !important;
        margin-bottom: 8px !important;
        padding-bottom: 4px !important;
    }

    @media (max-width: 991px) {
        .customized-order-financial-col + .customized-order-financial-col {
            margin-top: 16px;
        }
    }
</style>
<?php $__env->stopSection(); ?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="<?php echo e(route('web.index')); ?>"><?php echo e(__('web.home')); ?></a></span>
        <span class="sep"></span>
        <span><a href="<?php echo e(route('web.cart.index')); ?>"><?php echo e(__('web.my_cart')); ?></a></span>
    </div>
</section>
<section class="cart">
    <div class="container">
        <div class="checkout-workflow">
            <ul>
                <li class="active">
                    <span>1</span>
                    <label><?php echo e(__('web.cart')); ?></label>
                </li>
                <?php if(!(count($cart) == 0 && session()->has('customized_order_details'))): ?>
                <li class="">
                    <span>2</span>
                    <label><?php echo e(__('web.shipping_address')); ?></label>
                </li>
                <?php endif; ?>
                <li class="">
                    <span><?php echo e((count($cart) == 0 && session()->has('customized_order_details')) ? 2 : 3); ?></span>
                    <label><?php echo e((count($cart) == 0 && session()->has('customized_order_details')) ? __('web.review_order') : __('web.review_pay')); ?></label>
                </li>
            </ul>
        </div>
        <div class="h-flex flex-nowrap">
            <div class="cart-items-summary">
                <?php if(isset(auth()->user()->user_type) && auth()->user()->user_type == "physical_store_user"): ?>
                    <div class="sm-visible" style="padding: 1.5rem 0">
                        <a href="javascript:void(0)" class="btn-add-customized-order d-inline-flex align-items-center" style="color: #111; text-decoration: none; font-size: inherit; gap: 6px; font-weight: 700; font-size: 16px !important; line-height: 1 !important;">
                            <span style="font-size: 18px; line-height: 1; font-weight: 700;">+</span>
                            <span><?php echo e(__('web.add_customized_order')); ?></span>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if($showDesktopEmptyCustomizedOrderToggle): ?>
                <div class="desktop-empty-cart-left-action sm-hidden">
                    <a href="<?php echo e(route('web.product.index')); ?>" class="link-button"><i class="fa fa-arrow-left"></i><?php echo e(__('web.continue_shopping')); ?></a>
                </div>
                <?php endif; ?>

                <div class="cart-products">
                    <div class="cart-list">
                        <?php
                        $total_weight = 0;
                        $i = 0;
                        ?>
                        <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id =>$record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                        $product_weight = '';


                        $sizeid = 's';
                        if ($record['size_id']) {
                            $sizeid = $record['size_id'];
                        }
                        $colorid = 'c';
                        if ($record['color_id']) {
                            $colorid = $record['color_id'];
                        }

                        $record['monogram'] = $record;


                        $id = $record['product_id'] . "_" . $sizeid . "_" . $colorid;

                        $cartQuantity = $record['quantity'];
                        $product = Helper::getProductDetail($record['product_id']);
                        if (!$product) {
                            continue;
                        }
                        $has_monogram = Helper::isMonogranm($product->id);


                        $product_weight = $product->product_weight;
                        $productdetail = $product->getProductDescription($language->id);
                        $productimage = $product->getDefaultImage();


                        $sizeId = $record['size_id'];
                        $color_id = $record['color_id'];

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


                        $price = $is_product_sale_price_null == 0 ? $product_sale_price  : $product_regular_price;


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
                        $cartproduct = Helper::getCartProduct($user, $product->id, $sizeId ? 1 : 0, $sizeId ? $sizeId : 's', $color_id ? 1 : 0, $color_id ? $color_id : 'c');
                        //                      dd($cartproduct);
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
                                //                                $font        = \App\Helpers\Helper::getMonogramStyle($cartproduct['monogram']['font']);
                                //                                $initials    = \App\Helpers\Helper::getInitials($cartproduct['monogram']['initials_count'], $cartproduct['monogram']['left_text'], $cartproduct['monogram']['center_text'], $cartproduct['monogram']['right_text'], $cartproduct['monogram']['text4']);
                                //                                $colors      = \App\Helpers\Helper::getMonogramColor($cartproduct['monogram']['monogram_color_id']);
                                //                                $placement   = \App\Helpers\Helper::getPlacement($cartproduct['monogram']['placement']);
                                //                                $placement   = \App\Helpers\Helper::getPlacement($cartproduct['monogram']['placement']);
                            }
                        }
                        //                        if ($is_monogram)
                        //                        {
                        //                            $price = $price + $monogramPrice;
                        //                        }
                        // monogram end
                        $subTotal = $price * $cartQuantity;
                        /*
                         *  Convert Currency Rate
                         */
                        if ($currency) {
                            //   $subTotal = $subTotal * $currency->value;
                            $currencyRate = $currency->value;
                        }
                        $total += $subTotal;
                        ?>
                        <?php if($i ==0): ?>
                        <!--<input type="hidden" class="addMonogramButton" data-productId="<?php echo e($product->id); ?>">-->
                        <?php endif; ?>
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
                                        <button class="btn btn-white favorite move_to_wishlist" data-href="<?php echo e(route('web.wishlist.move_to_wishlist',['id'=> $product->id])); ?>"><i class="icon-wishlist"></i><?php echo e(__('web.move_to_wishlist')); ?></button>
                                        <button class="btn btn-white delete_trash" data-page="cart" data-id="<?php echo e($id); ?>" data-href="<?php echo e(route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])); ?>"><i class="icon-trash"></i></button>
                                    </div>
                                    <div class="v-flex sm-visible">
                                        <div class="prod-name">
                                            <h3><?php echo e(ucfirst($productdetail->products_name)); ?> <?php echo e($style ? '('.$style.')': ''); ?> </h3>
                                        </div>
                                        <div class="product-pricing-details">
                                            <span class="prod-price product_price_<?php echo e($id); ?>"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="prod-details pd">
                                    <div class="h-flex sm-hidden">
                                        <div class="prod-name">
                                            <h3><?php echo e(ucfirst($productdetail->products_name)); ?> <?php echo e($style ? '('.$style.')': ''); ?></h3>
                                        </div>
                                        <div class="product-pricing-details">
                                            <span class="prod-price product_price_<?php echo e($id); ?>"><?php echo e(Helper::formatNumber($subTotal)); ?> <?php echo e($currencyCode); ?></span>
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
                                        $min = $cartQuantity;
                                        $max = $productQuantity;
                                        $data_show = 5;
                                        if ($productQuantity < 5) {
                                            $data_show = $productQuantity;
                                        }
                                        $lastkey = 1;

                                        $record['monogram'] = \App\UserModel\Cart\MonogramShoppingCartDetail::where('shopping_cart_detail_id', $record->id)->get()->toArray();
                                        if (count($record['monogram']) > 0) {
                                            $lastkey = count($record['monogram']);
                                        }
                                        ?>
                                        <div class="form-item">
                                            <label><?php echo e(__('web.qty')); ?></label>
                                            <div class="counter counter2" data-maximum="<?php echo e($max); ?>" data-show="<?php echo e($data_show); ?>">
                                                <div class="h-flex">
                                                    <button class="decrease decrease_cart" data-maximum="<?php echo e($max); ?>" data-id="<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>"><i class="icon-minus"></i></button>
                                                    <input type="text" name="quantity_<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>" data-id="<?php echo e($id); ?>" class="qty_add prod-count quantity number-input quantity_<?php echo e($record['product_id']); ?>" value="<?php echo e($min); ?>">
                                                    <button class="increase increase_cart " data-maximum="<?php echo e($max); ?>" data-id="<?php echo e($id); ?>" data-product="<?php echo e($record['product_id']); ?>"><i class="icon-plus"></i></button>
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
                                        ?>

                                        <div class="monogram-information  monogram-information-<?php echo e($id); ?>  <?php echo e(count($cartproduct['monogram']) > 0 ? 'show' : 'hide'); ?> " id="monogramSummary-<?php echo e($id); ?>">
                                            <div class="h-flex">
                                                <div class="h-flex">
                                                    <label>Selected Monogram</label>
                                                    <span class="monogram-price nowrap"> <?php echo e($monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''); ?></span>
                                                </div>
                                                <span class="link-button edit_monogram2" data-productId="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-color="<?php echo e($color_id ? $color_id : 'c'); ?>" data-size="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($key); ?>">Edit</span>
                                                <span class="link-button removeMonogram2" data-productid="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id ? 1 : 0); ?>" data-sizeexist="<?php echo e($sizeId ? 1 : 0); ?>" data-colorId="<?php echo e($color_id ? $color_id : 'c'); ?>" data-sizeId="<?php echo e($sizeId ? $sizeId : 's'); ?>" data-monogram="<?php echo e($monogramVal['id']); ?>">Remove</span>
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
                                                <div class="separator underline underline_<?php echo e($id); ?>_<?php echo e($key); ?>  <?php echo e($monogramVal['monogram_note'] ? 'show' : 'hide'); ?>"></div>
                                                <div class="comment monogramComment_<?php echo e($id); ?>_<?php echo e($key); ?> <?php echo e($record['monogram'] ? $monogramVal['monogram_note'] ? 'show' : 'hide' : ''); ?>">
                                                    <p><?php echo e($record['monogram'] ?  $monogramVal['monogram_note'] ? "Comment: ".$monogramVal['monogram_note']:'': ''); ?> </p>
                                                </div>

                                            </div>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php
                                    $hasmonogram = \App\Helpers\Helper::getMonogram($product->id);
                                    ?>
                                    <?php if($hasmonogram): ?>
                                    <div class="addMonogramLink  addMonogramLink_<?php echo e($id); ?>" data-page="cart" data-productId="<?php echo e($product->id); ?>" data-id="<?php echo e($id); ?>" data-colorexist="<?php echo e($color_id?1:0); ?>" data-sizeexist="<?php echo e($sizeId?1:0); ?>" data-color="<?php echo e($color_id? $color_id : ''); ?>" data-size="<?php echo e($sizeId? $sizeId:''); ?>">
                                        <button <?php echo (int) $min == count($record['monogram']) || $record['is_same'] == 1 ? 'disabled="disabled"' : '' ?> class="link-button">
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
                                        <span class="link-button addNote" id='addNote-<?php echo e($id); ?>'><i class="icon-plus"></i><?php echo e(__('web.add_note')); ?></span>
                                        <i class="error"></i>
                                        <div class="note-error form-item text-green custom-error2 note-success-<?php echo e($id); ?>"></div>
                                        <div class="addNoteInput  textarea_<?php echo e($id); ?>">
                                            <textarea name="note_<?php echo e($id); ?>" class="note_<?php echo e($id); ?>" maxlength="<?php echo e(config('custom.order_note_length')); ?>"><?php echo e($record['note']); ?></textarea>
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
                                    <button class="btn btn-white favorite move_to_wishlist" data-href="<?php echo e(route('web.wishlist.move_to_wishlist',['id'=> $product->id])); ?>"><i class="icon-wishlist"></i><?php echo e(__('web.move_to_wishlist')); ?></button>
                                    <button class="btn btn-white delete_trash" data-page="cart" data-id="<?php echo e($id); ?>" data-href="<?php echo e(route('web.cart.remove_cart',['id'=> $product->id,'sizeId'=>$size?$size->size_id:'s','colorId'=>$color?$color->color_id:'c'])); ?>"><i class="icon-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <?php
                        $total_weight += $product_weight * $min;
                        $i++;
                        ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        
                        <?php
                        $mainTotal = $total; // main order amount (exclude customized)
                        $customOrderDetails = session('customized_order_details');
                        $customAdvance = 0;
                        if ($customOrderDetails && isset($customOrderDetails['payment']['advance']) && is_numeric($customOrderDetails['payment']['advance'])) {
                            $customAdvance = (float) str_replace(',', '', $customOrderDetails['payment']['advance']);
                        }
                        $total = $mainTotal;
                        ?>
                        <?php if(count($cart) == 0 && empty($customOrderDetails['items'])): ?> <p> <?php echo e(__('web.shopping_cart_is_empty')); ?></p> <?php endif; ?>


                        <?php
                        $roundweight = ceil($total_weight);
                        $countryName = 'kuwait';
                        if (isset($address)) {
                            $countryName = App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                            $countryName = $countryName ? $countryName->country_name : 'kuwait';
                        }

                        if ($roundweight < 1  ||  strtolower($countryName) == 'kuwait') {
                            $roundweight = 1;
                        }


                        $setting = \App\Helpers\Helper::getSetting();


                        $gift_wrap = ['is_gift' => 0, 'title' => '', 'message' => ''];
                        if (session('gift_wrap')) {
                            $gift_wrap = session('gift_wrap');
                        }
                        ?>
                        <?php if($setting['31']->setting_val ==1 && count($cart) > 0): ?>

                        <?php
                        $giftAmount = $setting['32']->setting_val;
                        $giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
                        if ($giftMinPrice) {
                            $giftMinPrice = $giftMinPrice * $currency->value;
                        }

                        if ($total >= $giftMinPrice) {
                            $giftAmount = 0;
                        }
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
                                                <input type="checkbox" name="add-monogram" id="gift_wrap" <?php echo e($gift_wrap['is_gift'] == 1? 'checked': ''); ?> value="1">
                                                <label><?php echo e(__('web.add_gift_wrap')); ?></label>
                                            </div>
                                            <?php
                                                $giftDesc = trim(str_replace("\xc2\xa0", '', html_entity_decode(strip_tags($setting['34']->setting_val ?? ''))));
                                            ?>
                                            <?php if($giftDesc !== ''): ?>
                                            <span class="tt">
                                                <i class="fa fa-info-circle" id="tt"></i>
                                                <span class="tt-text" title="<?php echo $setting['34']->setting_val; ?>"><?php echo $setting['34']->setting_val; ?></span>
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
                        <?php if($setting['41']->setting_val == 1 && count($cart) > 0): ?>
                        <?php
                        $giftBoxAmount = $setting['42']->setting_val;
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
                                                <input type="checkbox" name="add-monogram-box" id="gift_box" <?php echo e($gift_box['is_gift'] == 1? 'checked': ''); ?> value="1">
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
                <?php if(!$showDesktopEmptyCustomizedOrderToggle): ?>
                <div class="cart-actions sm-hidden mb-3">
                    <a href="<?php echo e(route('web.product.index')); ?>" class="link-button"><i class="fa fa-arrow-left"></i><?php echo e(__('web.continue_shopping')); ?></a>
                </div>
                <?php endif; ?>

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
                <div class="cart-details mb-4 customized-order-info-card">
                    <div class="customized-order-header-row">
                        <h4 style="margin: 0;"><?php echo e(__('web.customized_order_details')); ?></h4>

                        <div class="customized-order-request-type" style="font-size: 13px;">
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

                    
                    <div class="customized-order-info-grid">
                        <?php if(!empty($leftCustomerDetails)): ?>
                        <div class="customized-order-customer-col">
                            <div class="customized-order-info-title">
                                <span><?php echo e(__('web.customer_details')); ?>:</span>
                            </div>
                            <div class="customized-order-info-list">
                                <?php $__currentLoopData = $leftCustomerDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="customized-order-info-item"><strong><?php echo e($detail['label']); ?>:</strong> <span><?php echo e($detail['value']); ?></span></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($rightCustomerDetails)): ?>
                        <div class="customized-order-address-col">
                            <div class="customized-order-info-title">
                                <span><?php echo e(__('web.address_details')); ?>:</span>
                            </div>
                            <div class="customized-order-address-subgrid">
                                <div class="customized-order-info-list">
                                    <?php $__currentLoopData = $rightCustomerDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="customized-order-info-item"><strong><?php echo e($detail['label']); ?>:</strong> <span><?php echo e($detail['value']); ?></span></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div class="customized-order-info-list">
                                    <?php $__currentLoopData = $rightCustomerDetails2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="customized-order-info-item"><strong><?php echo e($detail['label']); ?>:</strong> <span><?php echo e($detail['value']); ?></span></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($bottomNote)): ?>
                        <div class="customized-order-note-col">
                            <?php $__currentLoopData = $bottomNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="customized-order-info-item customized-order-note-item"><strong><?php echo e($detail['label']); ?>:</strong> <span><?php echo e($detail['value']); ?></span></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
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
                                            <span class="custom-order-image-badge">Custom</span>
                                        </a>
                                        <?php if($loop->first): ?>
                                        <?php
                                            $__editTempId = $customOrder['temporary_order_id'] ?? null;
                                            $__editLabel  = (($customOrder['type'] ?? '') === 'quotation') ? __('web.edit_qo_order') : __('web.edit_po_order');
                                        ?>
                                        <div class="product-actions sm-hidden">
                                            <button class="btn btn-white btn-edit-active-custom-order ml-2" data-draft-id="<?php echo e($__editTempId); ?>" type="button">
                                                <i class="fas fa-edit mr-2"></i><?php echo e($__editLabel); ?>

                                            </button>
                                        </div>
                                        <?php endif; ?>
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
                                        <?php
                                            if (!isset($__editTempId)) {
                                                $__editTempId = $customOrder['temporary_order_id'] ?? null;
                                                $__editLabel  = (($customOrder['type'] ?? '') === 'quotation') ? __('web.edit_qo_order') : __('web.edit_po_order');
                                            }
                                        ?>
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
                                    <div class="product-options product-options-description">
                                        <?php if(isset($item['description']) && !empty($item['description'])): ?>
                                            <div class="form-item">
                                                <label><?php echo e(__('web.description')); ?></label>
                                                <span><?php echo e(Helper::formatCustomizedDescription($item['description'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                     <?php if($loop->first): ?>
                                     <div class="product-actions sm-hidden" style="display:none;"></div>
                                     <?php endif; ?>
                                 </div>
                                 <?php if($loop->first): ?>
                                 <div class="product-actions sm-visible">
                                     <button class="btn btn-white btn-edit-active-custom-order" data-draft-id="<?php echo e($__editTempId); ?>" type="button">
                                         <i class="fas fa-edit mr-2"></i><?php echo e($__editLabel); ?>

                                     </button>
                                 </div>
                                 <?php endif; ?>
                             </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="cart-bottom-primary-actions d-flex justify-content-end align-items-center sm-hidden">
                            <div
                                class="customized-preview-action w-50"
                                id="customized_pdf_preview_action"
                                style="<?php echo e((!empty(session('customized_order_details.items')) || !empty(session('customized_order_details.temporary_order_id')) || !empty($customOrderDetails['items'])) ? '' : 'display:none;'); ?>"
                            >
                                <a
                                    href="<?php echo e(route('web.cart.customized_preview', ['source' => 'cart'])); ?>"
                                    target="_blank"
                                    class="btn btn-secondary no-loader preview-pdf-button"
                                    style="height: 48px; display: flex; align-items: center; justify-content: center;"
                                >
                                    <?php echo e(__('web.preview_pdf')); ?>

                                </a>
                            </div>
                            
                            <button
                                class="btn btn-primary place_order2 continue-action-button w-50"
                                <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>

                                style="height: 48px;"
                            >
                                <?php echo e(__('web.place_order')); ?>

                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php if(count($cart)>0): ?>
            <?php
            // Apply site discount on main order only
            $discountApplied = ($mainTotal * $discount) / 100;
            /*
             * Coupon Code
             */
            $appliedCoupon = [];
            $couponDiscountFinal = 0;
            $appliedCoupon = session('couponCode');
            if ($appliedCoupon) {
                if (isset($appliedCoupon['user_id'])) {
                    if ($user) {
                        if ($appliedCoupon['user_id'] != $user->id) {
                            $appliedCoupon = [];
                        }
                    }
                }
                $couponStatus = Helper::checkCouponStatus(isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : false, $user);

                if (!$couponStatus['status']) {
                    $appliedCoupon = [];
                }
            }

            if ($appliedCoupon) {

                $couponDiscountIn = isset($appliedCoupon['coupon_discount_in']) ? $appliedCoupon['coupon_discount_in'] : '';
                $couponDiscount = isset($appliedCoupon['coupon_discount']) ? $appliedCoupon['coupon_discount'] : '';

                if ($couponDiscountIn && $couponDiscount) {


                    if ($couponDiscountIn == 'P') {
                        $couponDiscountFinal = ($mainTotal * $couponDiscount) / 100;
                    } else if ($couponDiscountIn == 'F') {
                        $couponDiscountFinal = $mainTotal - $couponDiscount;
                    }
                }
            }


            /*
             * End :: Coupon Code
             */


            /*
             *  Convert Currency Rate
             */

            //            dd($couponDiscountFinal);
            if ($currency) {
                $discountApplied = $discountApplied * $currency->value;
                $couponDiscountFinal = $couponDiscountFinal;
            }


            //            dd($couponDiscountFinal);

            // Final total = main order - discounts + customized advance + shipping + tax
            $finalTotal = $mainTotal + $shippingCharge + $taxValue - $discountApplied - $couponDiscountFinal;
            ?>

            <div class="cart-summary <?php echo e(count($cart) == 0 ? 'cart-empty-price-detail' : ''); ?>">
                <?php if($showDesktopEmptyCustomizedOrderToggle): ?>
                <div class="desktop-empty-cart-actions sm-hidden">
                    <div class="desktop-empty-cart-customized-order">
                        <a href="javascript:void(0)" class="btn-add-customized-order d-inline-flex align-items-center" style="color: #111; text-decoration: none; font-size: inherit; gap: 6px; font-weight: 700; font-size: 16px !important; line-height: 1 !important;">
                            <span style="font-size: 18px; line-height: 1; font-weight: 700;">+</span>
                            <span><?php echo e(__('web.add_customized_order')); ?></span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php echo $__env->make('web.cart.customized-order', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php if(config('custom_web.show_coupon_card')): ?>
                <?php echo $__env->make('web.checkout.coupon_card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endif; ?>
                <div class="cart-details">
                    <h4><?php echo e(__('web.price_detail')); ?></h4>
                    <div class="v-flex">
                        <h6><?php echo e(__('web.regular_order')); ?></h6>
                        <?php
                            $regularSubTotal = $mainTotal;
                            $regularShipping = $totalShipping;
                            $regularDiscount = $discountApplied + $couponDiscountFinal;
                            $regularGiftWrap = ($setting['31']->setting_val == 1 && count($cart) > 0 && !empty($gift_wrap['is_gift']))
                                ? (float) ($giftAmount * $currency->value)
                                : 0;
                            $regularGiftBox = ($setting['41']->setting_val == 1 && count($cart) > 0 && !empty($gift_box['is_gift']))
                                ? (float) ($giftBoxAmount * $currency->value)
                                : 0;

                            $customSummary = \App\Services\PaymentCalculationService::resolveCustomizedOrderPayableFromSession($customOrderDetails);
                            $customSubTotal = $customSummary['subtotal'];
                            $customShipping = $customSummary['shipping'];
                            $customDiscount = $customSummary['discount_amount'];
                            $customDiscountType = $customSummary['discount_type'] ?? '';
                            $customDiscountVal = $customSummary['discount_val'];
                            $customOrderValue = $customSummary['grand_total'];
                            $customAdvanceAmount = $customSummary['advance_price'];
                            $customPendingAmount = $customSummary['pending_amount'];
                            $customAmountPayable = $customSummary['payable_amount'];
                            $isAdvanceDoneForCart = $customSummary['is_advance_done'];
                            $generatePendingRequestedForCart = $customSummary['generate_pending_requested'];

                            $regularOrderPayable = max(0, $regularSubTotal + $regularShipping - $regularDiscount + $regularGiftWrap + $regularGiftBox);
                            $combinedGrandTotal = $regularOrderPayable + $customAmountPayable;
                            $hasRegularOrderDetails = $regularSubTotal > 0 || $regularShipping > 0 || $regularGiftWrap > 0 || $regularGiftBox > 0 || $regularDiscount > 0;
                            $hasCustomOrderDetails = $customSubTotal > 0 || $customShipping > 0 || $customDiscount > 0 || $customAdvanceAmount > 0;
                        ?>
                        <?php if($hasRegularOrderDetails): ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                            <div class="item-price sub_total sub_total_price_detail" data-subtotal="<?php echo e($regularSubTotal); ?>"><?php echo e(Helper::formatNumber($regularSubTotal)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                            <div class="item-price shipping-price"><?php echo e(Helper::formatNumber($regularShipping)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php if($setting['31']->setting_val == 1): ?>
                        <div class="total gift_wrap_item <?php echo e($gift_wrap['is_gift'] == 1? 'show': 'hide'); ?>">
                            <div class="item"><?php echo e(__('web.gift_wrap')); ?></div>
                            <div class="item-price gift_wrap_price"><?php echo e(Helper::formatNumber($regularGiftWrap)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if($setting['41']->setting_val == 1): ?>
                        <div class="total gift_box_item <?php echo e($gift_box['is_gift'] == 1? 'show': 'hide'); ?>">
                            <div class="item"><?php echo e(__('web.gift_box')); ?></div>
                            <div class="item-price gift_box_price"><?php echo e(Helper::formatNumber($regularGiftBox)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if(config('custom_web.apply_discount') && $regularDiscount > 0): ?>
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

                        <?php if($hasCustomOrderDetails): ?>
                        <?php if($hasRegularOrderDetails): ?>
                        <div class="custom-order-summary-divider"></div>
                        <?php endif; ?>
                        <h6 class="<?php echo e($hasRegularOrderDetails ? 'mt-3' : 'mt-0'); ?>"><?php echo e(__('web.custom_order_price_details')); ?></h6>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.sub_total')); ?></div>
                            <div class="item-price custom-order-subtotal"><?php echo e(Helper::formatNumber($customSubTotal)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.shipping_charges')); ?></div>
                            <div class="item-price custom-order-shipping"><?php echo e(Helper::formatNumber($customShipping)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php if($customDiscount > 0): ?>
                        <?php
                            $custSubTotal = 0;
                            if (isset($customOrder['items'])) {
                                foreach ($customOrder['items'] as $it) {
                                    $custSubTotal += floatval($it['total'] ?? 0);
                                }
                            }
                            $custShipping = isset($customOrder['shipping']) ? floatval($customOrder['shipping']) : 0;
                            $custDiscType = isset($customOrder['discount_type']) ? $customOrder['discount_type'] : '';
                            $custDiscVal = isset($customOrder['discount_val']) ? floatval($customOrder['discount_val']) : 0;
                            $custDiscountAmount = 0;
                            if ($custDiscType == 'percentage') {
                                $custDiscountAmount = ($custSubTotal * $custDiscVal) / 100;
                            } elseif ($custDiscType == 'flat') {
                                $custDiscountAmount = $custDiscVal;
                            }
                        ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.discount')); ?> <small>&nbsp;(<?php echo e(ucfirst($custDiscType)); ?><?php echo e($custDiscType === 'percentage' ? ' - ' . $custDiscVal . '%' : ''); ?>)</small></div>
                            <div class="item-price custom-order-discount">- <?php echo e(Helper::formatNumber($customDiscount)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.total_order_value')); ?></div>
                            <div class="item-price custom-order-grand-total"><?php echo e(Helper::formatNumber($customOrderValue)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php if($customAdvanceAmount > 0): ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.advance_paid')); ?></div>
                            <div class="item-price custom-advance">- <?php echo e(Helper::formatNumber($customAdvanceAmount)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.pending_amount')); ?></div>
                                <div class="item-price amount-payable custom-order-pending"><?php echo e(Helper::formatNumber($customPendingAmount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>

                        <?php if($hasRegularOrderDetails || $hasCustomOrderDetails): ?>
                        <div class="custom-order-summary-divider"></div>
                        <div class="total grandtotal" style="margin-top:10px;">
                            <div class="item"><?php echo e(__('web.grand_total')); ?> </div>
                            <div class="item-price grand-total" data-grand-total='<?php echo e(number_format((float) $combinedGrandTotal, 3, '.', '')); ?>' data-cart-base-total="<?php echo e(number_format((float) $combinedGrandTotal - $customAmountPayable, 3, '.', '')); ?>"><?php echo e(Helper::formatNumber($combinedGrandTotal)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="page-actions">
                            <button class="btn btn-primary place_order2 continue-action-button" <?php echo e($hasCheckoutError ? 'disabled' : ''); ?>><?php echo e(__('web.place_order')); ?></button>
                            <div class="customized-preview-action" id="customized_pdf_preview_action" style="<?php echo e((!empty(session('customized_order_details.items')) || !empty(session('customized_order_details.temporary_order_id')) || !empty($customOrderDetails['items'])) ? '' : 'display:none;'); ?>">
                                <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'cart'])); ?>" target="_blank" class="btn btn-secondary mt-3 no-loader preview-pdf-button" style="width: 100%;"><?php echo e(__('web.preview_pdf')); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php if(isset(auth()->user()->user_type) && auth()->user()->user_type == "physical_store_user"): ?>
                <div class="cart-summary cart-empty-price-detail">
                    <?php if($showDesktopEmptyCustomizedOrderToggle): ?>
                    <div class="desktop-empty-cart-actions sm-hidden">
                        <div class="desktop-empty-cart-customized-order">
                            <a href="javascript:void(0)" class="btn-add-customized-order d-inline-flex align-items-center" style="color: #111; text-decoration: none; font-size: inherit; gap: 6px; font-weight: 700; font-size: 16px !important; line-height: 1 !important;">
                                <span style="font-size: 18px; line-height: 1; font-weight: 700;">+</span>
                                <span><?php echo e(__('web.add_customized_order')); ?></span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="cart-details">
                        <div class="v-flex">
                            <?php
                                $customSubTotal = 0;
                                if (!empty($customOrderDetails['items']) && is_array($customOrderDetails['items'])) {
                                    foreach ($customOrderDetails['items'] as $customItem) {
                                        $customSubTotal += (float) ($customItem['total'] ?? 0);
                                    }
                                }

                                $customSummaryEmpty = \App\Services\PaymentCalculationService::resolveCustomizedOrderPayableFromSession($customOrderDetails);
                                $customSubTotal = $customSummaryEmpty['subtotal'];
                                $customShipping = $customSummaryEmpty['shipping'];
                                $customDiscount = $customSummaryEmpty['discount_amount'];
                                $customDiscountType = $customSummaryEmpty['discount_type'] ?? '';
                                $customDiscountVal = $customSummaryEmpty['discount_val'];
                                $customOrderValue = $customSummaryEmpty['grand_total'];
                                $customAdvanceAmount = $customSummaryEmpty['advance_price'];
                                $customPendingAmount = $customSummaryEmpty['pending_amount'];
                                $customAmountDueNow = $customSummaryEmpty['payable_amount'];
                                $isAdvanceDoneForCartEmpty = $customSummaryEmpty['is_advance_done'];
                                $generatePendingRequestedForCartEmpty = $customSummaryEmpty['generate_pending_requested'];
                            ?>
                            <?php if(count($cart) == 0): ?>
                                <h4><?php echo e(__('web.price_detail')); ?></h4>
                            <?php endif; ?>
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
                            <?php
                                $custSubTotal = 0;
                                if (isset($customOrder['items'])) {
                                    foreach ($customOrder['items'] as $it) {
                                        $custSubTotal += floatval($it['total'] ?? 0);
                                    }
                                }
                                $custShipping = isset($customOrder['shipping']) ? floatval($customOrder['shipping']) : 0;
                                $custDiscType = isset($customOrder['discount_type']) ? $customOrder['discount_type'] : '';
                                $custDiscVal = isset($customOrder['discount_val']) ? floatval($customOrder['discount_val']) : 0;
                                $custDiscountAmount = 0;
                                if ($custDiscType == 'percentage') {
                                    $custDiscountAmount = ($custSubTotal * $custDiscVal) / 100;
                                } elseif ($custDiscType == 'flat') {
                                    $custDiscountAmount = $custDiscVal;
                                }
                            ?>
                            <div class="total">
                                <div class="item"><?php echo e(__('web.discount')); ?> <small>&nbsp;(<?php echo e(ucfirst($custDiscType)); ?><?php echo e($custDiscType === 'percentage' ? ' - ' . $custDiscVal . '%' : ''); ?>)</small></div>
                                <div class="item-price custom-order-discount">- <?php echo e(Helper::formatNumber($customDiscount)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="total">
                            <div class="item"><?php echo e(__('web.total_order_value')); ?></div>
                            <div class="item-price custom-order-grand-total"><?php echo e(Helper::formatNumber($customOrderValue)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php if($customAdvanceAmount > 0): ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.advance_paid')); ?></div>
                            <div class="item-price custom-advance">- <?php echo e(Helper::formatNumber($customAdvanceAmount)); ?> <?php echo e($currencyCode); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="total">
                            <div class="item"><?php echo e(__('web.pending_amount')); ?></div>
                                <div class="item-price amount-payable custom-order-pending"><?php echo e(Helper::formatNumber($customPendingAmount)); ?> <?php echo e($currencyCode); ?></div>
                        </div>

                        <div class="custom-order-summary-divider"></div>
                        <div class="total grandtotal" style="margin-top:10px;">
                            <div class="item"><?php echo e(__('web.grand_total')); ?> </div>
                                <div class="item-price grand-total" data-grand-total='<?php echo e(number_format((float) $customAmountDueNow, 3, '.', '')); ?>' data-cart-base-total="<?php echo e(number_format((float) 0, 3, '.', '')); ?>"><?php echo e(Helper::formatNumber($customAmountDueNow)); ?> <?php echo e($currencyCode); ?></div>
                            </div>
                            <div class="page-actions">
                                <?php
                                    $finalTotalAmount = $customAmountDueNow;
                                ?>

                                <button
                                    class="btn btn-primary place_order2 continue-action-button"
                                    <?php echo e(($finalTotalAmount <= 0 && ($customOrder['type'] ?? '') === 'quotation') || $hasCheckoutError ? 'disabled' : ''); ?>

                                >
                                    <?php echo e(__('web.place_order')); ?>

                                </button>
                                <div class="customized-preview-action" id="customized_pdf_preview_action" style="<?php echo e((!empty(session('customized_order_details.items')) || !empty(session('customized_order_details.temporary_order_id')) || !empty($customOrderDetails['items'])) ? '' : 'display:none;'); ?>">
                                    <a href="<?php echo e(route('web.cart.customized_preview', ['source' => 'cart'])); ?>" target="_blank" class="btn btn-secondary mt-3 no-loader preview-pdf-button" style="width: 100%;"><?php echo e(__('web.preview_pdf')); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="cart-page-bottom-actions sm-visible">
                <div
                    class="customized-preview-action"
                    id="customized_pdf_preview_action"
                    style="<?php echo e((!empty(session('customized_order_details.items')) || !empty(session('customized_order_details.temporary_order_id')) || !empty($customOrderDetails['items'])) ? '' : 'display:none;'); ?>"
                >
                    <a
                        href="<?php echo e(route('web.cart.customized_preview', ['source' => 'cart'])); ?>"
                        target="_blank"
                        class="btn btn-secondary no-loader preview-pdf-button"
                    >
                        <?php echo e(__('web.preview_pdf')); ?>

                    </a>
                </div>
                
                <button
                    class="btn btn-primary place_order2 continue-action-button"
                    <?php echo e(((isset($customAmountDueNow) && $customAmountDueNow <= 0 && ($customOrder['type'] ?? '') === 'quotation') || $hasCheckoutError) ? 'disabled' : ''); ?>

                >
                    <?php echo e(__('web.place_order')); ?>

                </button>
            </div>
            <div class="cart-actions sm-visible">
                <a href="<?php echo e(route('web.product.index')); ?>" class="link-button"><i class="fa fa-arrow-left"></i><?php echo e(__('web.continue_shopping')); ?></a>
            </div>
        </div>
    </div>
</section>
<?php
$hasCheckoutError = session()->has('checkout_error');
$checkoutRoute = route('web.checkout.index');
if (count($cart) == 0 && session()->has('customized_order_details')) {
    $checkoutRoute = route('web.checkout.cart_review');
}
?>
<script>
    var checkoutUrl = "<?php echo e($checkoutRoute); ?>";
    window.checkoutFlowBlocked = <?php echo e($hasCheckoutError ? 'true' : 'false'); ?>;
    window.checkoutErrorRedirectUrl = "<?php echo e(route('web.user.my_address')); ?>";
</script>

<!-- Customized Order Modal -->
<div id="customizedOrderModal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="address_title"><?php echo e(__('web.add_customized_order')); ?></h1>

                <span class="close" data-dismiss="modal">
                    <i class="icon-close"></i>
                </span>
            </div>
            <div class="modal-body fancy">
                <form id="customized_order_payload_form" enctype="multipart/form-data">
                    
                    
                    <input type="hidden" name="temporary_order_id" id="temporary_order_id" value="">
                    
                    <!-- Saved Temporary Drafts Dropdown -->
                    <div class="form-group form-item saved-drafts-form-group">
                        <label class="customized-draft-dropdown-label text-left" for="saved_draft_order_select">
                            <?php echo e(__('web.select_saved_draft_order')); ?>

                        </label>
                        <div class="saved-draft-select-container position-relative">
                            <select class="form-control select-component" id="saved_draft_order_select" name="saved_draft_order_select">
                                <option value=""><?php echo e(__('web.select_saved_order')); ?></option>
                                <?php if(isset($pendingDrafts) && count($pendingDrafts) > 0): ?>
                                    <?php $__currentLoopData = $pendingDrafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        
                                        
                                        <option value="<?php echo e($draft->id); ?>">
                                            <?php echo e($draft->formatted_document_number); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <option value="" disabled><?php echo e(__('web.no_pending_saved_orders')); ?></option>
                                <?php endif; ?>
                            </select>
                            <div class="draft-loader-spinner" id="draft_loader_spinner" style="display:none;">
                                <i class="fa fa-spinner fa-spin text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Request Type -->
                    <div class="form-group">
                        <div class="form-item h-flex">
                            <div class="radio w-50">
                                
                                <input type="radio" id="req_type_po" name="custom_req_type" value="purchase_order" checked>
                                <label for="req_type_po"><?php echo e(__('web.purchase_order')); ?></label>
                            </div>
                            <div class="radio w-50">
                                
                                <input type="radio" id="req_type_qt" name="custom_req_type" value="quotation">
                                <label for="req_type_qt"><?php echo e(__('web.quotation')); ?></label>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details -->
                    <h5 class="customized-order-section-title"><?php echo e(__('web.customer_details')); ?></h5>
                    <div class="row">
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_phone" value="" class="form-control mandatory" placeholder="<?php echo e(__('web.enter_phone_number')); ?>*" data-req-msg="<?php echo e(__('web.error-req-phone-number')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_email" value="" class="form-control" placeholder="<?php echo e(__('web.enter_email')); ?>" data-req-msg="<?php echo e(__('web.error-req-email')); ?>">
                        </div>
                        <div class="col-md-12 form-item" id="address_dropdown_wrapper" style="">
                            <select class="form-control select-component untouched" id="customer_address_select" name="customer_address_id">
                                <option value=""><?php echo e(__('web.select_address')); ?></option>
                            </select>
                        </div>
                        <div class="col-md-12 form-item cust_address_title_wrapper">
                            
                            <input type="text" name="cust_address_title" value="" class="form-control cust_address_title" placeholder="<?php echo e(__('web.enter_address_title')); ?>" data-req-msg="<?php echo e(__('web.error-req-address-title')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_name" value="" class="form-control" placeholder="<?php echo e(__('web.enter_name')); ?>" data-req-msg="<?php echo e(__('web.error-req-name')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            <select name="cust_country_id" class="form-control select-component untouched" data-req-msg="<?php echo e(__('web.error-req-country')); ?>">
                                <option value=""><?php echo e(__('web.select_country')); ?></option>
                                <?php 
                                $language = Helper::getLanguage();
                                ?>
                                <?php $__currentLoopData = $country; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $cDesc = $c->getCountryDescription($language->id);
                                /* $sel = (isset($existingCust['customer']['country_id']) && $existingCust['customer']['country_id'] == $c->id) ? 'selected' : ''; */
                                $sel = '';
                                ?>
                                <option value="<?php echo e($c->id); ?>" <?php echo e($sel); ?>><?php echo e($cDesc ? $cDesc->country_name : $c->currency_code); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-item">
                            <div class="cust_city_list">
                                <select name="cust_city_id" class="form-control cust_city_id select-component untouched">
                                    <option value=""><?php echo e(__('web.select_area')); ?></option>
                                </select>
                            </div>
                            <div class="cust_city_text" style="display:none; margin-top:5px;">
                                
                                <input type="text" name="cust_city" value="" class="form-control" placeholder="<?php echo e(__('web.enter_city')); ?>" data-req-msg="<?php echo e(__('web.error-req-city')); ?>">
                            </div>
                            
                            
                            <input type="hidden" id="existing_custom_city_id" value="">
                            <input type="hidden" id="existing_custom_city_name" value="">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_block" value="" class="form-control" placeholder="<?php echo e(__('web.block')); ?>" data-req-msg="<?php echo e(__('web.error-req-block')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_street" value="" class="form-control" placeholder="<?php echo e(__('web.street')); ?>" data-req-msg="<?php echo e(__('web.error-req-street')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_house_number" value="" class="form-control" placeholder="<?php echo e(__('web.house_number')); ?>" data-req-msg="<?php echo e(__('web.error-req-house')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_floor" value="" class="form-control" placeholder="<?php echo e(__('web.floor')); ?>">
                        </div>
                        <div class="col-md-6 form-item">
                            
                            <input type="text" name="cust_address_url" value="" class="form-control" placeholder="<?php echo e(__('web.address_url')); ?>">
                        </div>
                        <div class="col-md-12 form-item">
                            
                            <input type="text" name="cust_address_detail" value="" class="form-control" placeholder="<?php echo e(__('web.address_detail')); ?>">
                        </div>
                        <div class="col-md-12 form-item">
                            
                            <textarea name="cust_note" class="form-control" placeholder="<?php echo e(__('web.note')); ?>" rows="3" style="resize: vertical;"></textarea>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <h5 class="customized-order-section-title"><?php echo e(__('web.order_details')); ?></h5>
                    <div class="custom-items-wrapper">
                        <div id="modal_custom_items_list">
                            
                            
                        </div>
                        <!-- <div style="margin-top: 10px;">
                             <button type="button" id="modal_add_item_btn" class="link-button"><i class="icon-plus"></i> <?php echo e(__('web.add_item')); ?></button>
                         </div> -->
                    </div>

                    <div class="row customized-order-financial-row">
                        <div id="payment_section_col" class="col-12 col-lg-6 customized-order-financial-col">
                            <!-- Payment Details -->
                            <div id="payment_section" class="customized-order-financial-block">
                                <h5 class="customized-order-section-title"><?php echo e(__('web.payment_details')); ?></h5>
                                <div class="row">
                                    
                                    
                                    <div class="col-md-12 form-item">
                                        <label><?php echo e(__('web.advance_payment')); ?></label>
                                        
                                        <input type="number" name="advance_payment" value="0" class="form-control" placeholder="<?php echo e(__('web.enter_advance_payment')); ?>" step="0.001" min="0" data-req-msg="<?php echo e(__('web.error-req-advance-payment')); ?>" oninput="if(this.value.includes('.')){let parts=this.value.split('.');if(parts[1]&&parts[1].length>3)this.value=parts[0]+'.'+parts[1].slice(0,3);}if(this.value<0)this.value=Math.abs(this.value);">
                                    </div>
                                    <div class="col-md-12 form-item" id="generate_pending_link_wrapper" style="display: none; margin-top: 12px;">
                                        <label class="generate-pending-label" for="generate_pending_payment_link">
                                            <input type="checkbox" name="generate_pending_payment_link" id="generate_pending_payment_link" class="generate-pending-checkbox" value="1">
                                            <span><?php echo e(__('web.generate_pending_amount_payment_link')); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="shipping_section_col" class="col-12 col-lg-6 customized-order-financial-col">
                            <div id="shipping_section" class="customized-order-financial-block">
                                <h5 class="customized-order-section-title"><?php echo e(__('web.shipping_details')); ?></h5>
                                <div class="row">
                                    
                                    
                                    <div class="col-md-12 form-item"> <label><?php echo e(__('web.shipping_charges')); ?></label> <input type="number" name="custom_shipping" class="form-control" placeholder="0.000" step="0.001" min="0" value="0" oninput="if(this.value.includes('.')){let parts=this.value.split('.');if(parts[1]&&parts[1].length>3)this.value=parts[0]+'.'+parts[1].slice(0,3);}if(this.value<0)this.value=Math.abs(this.value);"> </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Discount Details -->

                    <div id="discount_section">
                        <h5 class="customized-order-section-title"><?php echo e(__('web.discount_details')); ?></h5>
                        <div class="row justify-content-end">
                            <div class="col-md-6 form-item"> <label><?php echo e(__('web.discount_type')); ?></label> <select name="custom_discount_type" class="form-control select-component untouched">
                                    <option value=""><?php echo e(__('web.select')); ?></option>
                                    
                                    
                                    <option value="flat"><?php echo e(__('web.discount_flat')); ?></option>
                                    <option value="percentage"><?php echo e(__('web.discount_percentage')); ?></option>
                                </select> </div>
                            
                            
                            <div class="col-md-6 form-item"> <label><?php echo e(__('web.discount_value')); ?></label> <input type="number" name="custom_discount_val" class="form-control" placeholder="0.000" step="0.001" min="0" value="" oninput="restrictDiscountInput(this);"> </div>
                        </div>
                    </div>

                    <div class="custom-order-summary-box" style="margin-top: 22px;">
                        <div class="custom-order-summary-grid">
                            <div class="custom-order-summary-item">
                                <label><?php echo e(__('web.sub_total')); ?></label>
                                <span id="modal_sub_total"><?php echo e(number_format($modalSubTotal, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                            <div class="custom-order-summary-item">
                                <label><?php echo e(__('web.shipping_charges')); ?></label>
                                <span id="modal_shipping_charge"><?php echo e(number_format($modalShipping, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                            <div class="custom-order-summary-item">
                                <label id="modal_discount_label">
                                    <?php echo e(__('web.discount')); ?>

                                    <?php if($modalDiscountLabel): ?>
                                        <small>(<?php echo e($modalDiscountLabel); ?>)</small>
                                    <?php endif; ?>
                                </label>
                                <span id="modal_discount_amount">- <?php echo e(number_format($modalDiscountAmount, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                            <div class="custom-order-summary-item">
                                <label><?php echo e(__('web.total_order_value')); ?></label>
                                <span id="modal_grand_total"><?php echo e(number_format($modalTotalOrderValue, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                            <div class="custom-order-summary-item" id="modal_advance_paid_row">
                                <label><?php echo e(__('web.advance_paid')); ?></label>
                                <span id="modal_advance_paid">- <?php echo e(number_format($modalAdvancePaid, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                            <div class="custom-order-summary-item">
                                <label><?php echo e(__('web.pending_amount')); ?></label>
                                <span id="modal_pending_amount"><?php echo e(number_format($modalPendingAmount, 3)); ?> <?php echo e($currencyCode); ?></span>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo e(__('web.close')); ?></button>
                <button type="button" class="btn btn-secondary" id="reset_customized_order" style="background:#6c757d; border-color:#6c757d; color:#fff; margin-left: 5px;"><?php echo e(__('web.reset')); ?></button>
                <button type="button" class="btn btn-primary" id="submit_customized_order" style="margin-left: 5px;"><?php echo e(__('web.save')); ?></button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_specific_js'); ?>
<script>
    window.savedCustomizedOrderState = <?php echo json_encode(session('customized_order_details'), 15, 512) ?>;
    window.customizedOrderState = window.savedCustomizedOrderState;
    window.customizedOrderCurrencyCode = <?php echo json_encode($currencyCode, 15, 512) ?>;
    window.customizedReviewBaseGrandTotal = null;
    window.customizedReviewInitialAdvance = <?php echo json_encode($customAdvance, 15, 512) ?>;
    window.customizedReviewCurrentAdvance = 0;
    window.customizedReviewBaseGrandTotal = parseFloat($('.grand-total').first().data('cart-base-total')) || null;

    let isResettingAddress = false;
    let lastFetchedPhone = '';
    let lastFetchedEmail = '';

    $(function() {
        // Customized Order JS
        // Toggle form logic
        // --- Customized Order Modal Logic --- //

        function rowHasCustomItemData($row) {
            var hasData = false;

            $row.find('input, textarea').each(function () {
                var $field = $(this);
                if ($field.attr('type') === 'file') {
                    if (this.files && this.files.length > 0) {
                        hasData = true;
                        return false;
                    }
                    return;
                }

                var value = ($field.val() || '').toString().trim();
                if (value !== '') {
                    hasData = true;
                    return false;
                }
            });

            return hasData;
        }

        function hasCustomizedOrderInSession() {
            var state = window.savedCustomizedOrderState || window.customizedOrderState;
            if (state) {
                if (Array.isArray(state.items) && state.items.length > 0) {
                    return true;
                }
                if (state.temporary_order_id) {
                    return true;
                }
            }
            return false;
        }

        function syncCustomizedPreviewButtonVisibility() {
            var hasOrder = hasCustomizedOrderInSession();
            $('.customized-preview-action, #customized_pdf_preview_action').toggle(hasOrder);
        }

        function resetCustomizedOrderModal() {
            window.customizedOrderState = null;
            clearCustomizedOrderFormState();
            $('#temporary_order_id').val('');
            $('#saved_draft_order_select').val('');
            $('#req_type_po').prop('checked', true);
            $('#req_type_qt').prop('checked', false);

            // Ensure items list is clean with 1 fresh empty row
            $('#modal_custom_items_list').empty();
            addModalCustomItemRow();

            // Clear any red validation error styling/messages
            $('#customized_order_payload_form .text-red').remove();
            $('#customized_order_payload_form .mandatory, #customized_order_payload_form input, #customized_order_payload_form select, #customized_order_payload_form textarea').css('border', '1px solid #e1e1e1');

            syncAdvancePaymentSection();
            calculateModalTotal();
            syncCustomizedPreviewButtonVisibility();
        }

        // Reset button in modal footer
        $(document).on('click', '#reset_customized_order', function(e) {
            e.preventDefault();
            resetCustomizedOrderModal();
        });

        // + Add Customized Order button — always opens a fresh modal
        $(document).on('click', '.btn-add-customized-order', function(e) {
            e.preventDefault();
            resetCustomizedOrderModal();
            $('#customizedOrderModal').modal('show');
        });

        // Edit Active Custom Order button (Edit PO / QO)
        $(document).on('click', '.btn-edit-active-custom-order', function(e) {
            e.preventDefault();
            var draftId = $(this).data('draft-id');
            if (!draftId && window.savedCustomizedOrderState && window.savedCustomizedOrderState.temporary_order_id) {
                draftId = window.savedCustomizedOrderState.temporary_order_id;
            }

            if (draftId && $('#saved_draft_order_select option[value="' + draftId + '"]').length) {
                $('#saved_draft_order_select').val(draftId).trigger('change');
            } else if (window.savedCustomizedOrderState) {
                window.customizedOrderState = window.savedCustomizedOrderState;
                if (window.savedCustomizedOrderState.temporary_order_id) {
                    $('#temporary_order_id').val(window.savedCustomizedOrderState.temporary_order_id);
                }
                renderCustomizedCartModalFromState();
                syncAdvancePaymentSection();
            }
            $('#customizedOrderModal').modal('show');
        });

        $(document).on('keydown', '.custom-qty', function(e) {
            if (e.key === '.' || e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });

        // When modal shown, if country already set (e.g., autofill), refresh city list
        $('#customizedOrderModal').on('shown.bs.modal', function() {
            
            
        });

        // Auto Fetch Customer
        $('input[name="cust_phone"], input[name="cust_email"]').on('change', function () {

            var phone = $('input[name="cust_phone"]').val().trim();
            var email = $('input[name="cust_email"]').val().trim();

            fetchAddresses(phone, email, false);
        });

        function fetchAddresses(phone, email, isAlreadyFetched, targetAddressId, callback) {
            // Do nothing if both empty
            if (!phone && !email) {
                if (typeof callback === 'function') callback();
                return;
            }

            // If phone/email haven't changed and we already have cached addresses, restore address if targetAddressId provided
            if (phone === lastFetchedPhone && email === lastFetchedEmail) {
                if (window.customerAddresses && window.customerAddresses.length > 0) {
                    $('#address_dropdown_wrapper').show();
                    let $select = $('#customer_address_select');
                    let selectedAddr = null;
                    if (targetAddressId) {
                        selectedAddr = window.customerAddresses.find(addr => parseInt(addr.id) === parseInt(targetAddressId));
                    }
                    if (selectedAddr) {
                        $select.val(selectedAddr.id);
                        fillAddress(selectedAddr);
                    } else if (targetAddressId) {
                        // Saved address ID not in list anymore
                        $select.val('');
                        enableManualAddress();
                    }
                }
                if (typeof callback === 'function') callback();
                return;
            }

            // Update cache BEFORE API call
            lastFetchedPhone = phone;
            lastFetchedEmail = email;

            var preservedNote = isAlreadyFetched ? $('textarea[name="cust_note"]').val() : '';

            // 🔥 Reset address ONLY when value changes
            resetAddressFields();
            if (isAlreadyFetched && typeof preservedNote === 'string') {
                $('textarea[name="cust_note"]').val(preservedNote);
            }

            $.ajax({
                url: "<?php echo e(route('web.cart.get_customer_address')); ?>",
                method: "POST",
                data: {
                    phone: phone,
                    email: email,
                    _token: "<?php echo e(csrf_token()); ?>"
                },
                success: function (response) {
                    if (response.status && response.data) {
                        $('input[name="cust_email"]').val(response.data.email || email);
                        $('input[name="cust_phone"]').val(response.data.phone || phone);

                        let addresses = response.data.addresses || [];
                        let defaultAddress = response.data.default_address;

                        if (targetAddressId) {
                            let matched = addresses.find(addr => parseInt(addr.id) === parseInt(targetAddressId));
                            if (matched) {
                                defaultAddress = matched;
                            } else {
                                defaultAddress = null;
                            }
                        } else if (isAlreadyFetched) {
                            let exisAddress = "<?php echo e($existingCust['customer']['customer_address_id'] ?? ''); ?>";
                            if (exisAddress) {
                                defaultAddress = addresses.find(addr => parseInt(addr.id) == parseInt(exisAddress));
                            }
                        }

                        if (addresses.length > 0) {
                            $('#address_dropdown_wrapper').show();
                            let $select = $('#customer_address_select');
                            $select.empty();

                            $select.append(`<option value="">Select Address</option>`);

                            addresses.forEach(function (addr) {
                                let isSel = (defaultAddress && parseInt(addr.id) === parseInt(defaultAddress.id));
                                $select.append(`<option value="${addr.id}" ${isSel ? 'selected' : ''}>${addr.address_title}</option>`);
                            });

                            window.customerAddresses = addresses;

                            if (defaultAddress) {
                                $select.val(defaultAddress.id);
                                fillAddress(defaultAddress);
                            } else {
                                $select.val('');
                                enableManualAddress();
                            }

                        } else {
                            $('#address_dropdown_wrapper').hide();
                            enableManualAddress();
                        }
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function () {
                    if (typeof callback === 'function') callback();
                }
            });
        }

        $('#customizedOrderModal').on('hidden.bs.modal', function () {
            lastFetchedPhone = '';
            lastFetchedEmail = '';
            window.customizedOrderState = null;
            clearCustomizedOrderFormState();
            $('#temporary_order_id').val('');
            $('#saved_draft_order_select').val('');
            $('#generate_pending_link_wrapper').hide();
            $('#generate_pending_payment_link').prop('checked', false);
        });

        $(document).on('change', '#customer_address_select', function () {
            let val = $(this).val();
            if (!val) {
                enableManualAddress();
                return;
            }

            if (window.customerAddresses?.length > 0) {
                let selectedAddress = window.customerAddresses.find(addr => parseInt(addr.id) == parseInt(val));
                if (selectedAddress) {
                    fillAddress(selectedAddress);
                } else {
                    enableManualAddress();
                }
                return;
            }

            enableManualAddress();
        });

        function fillAddress(addr) {
            if (!addr) {
                enableManualAddress();
                return;
            }

            const fullName = [addr.first_name, addr.last_name].filter(Boolean).join(' ').trim();
            $('.cust_address_title_wrapper').addClass('d-none');
            $('input[name="cust_address_title"]').val(addr?.address_title || '');
            $('input[name="cust_name"]').val(fullName);
            $('input[name="cust_block"]').val(addr?.block || '');
            $('input[name="cust_street"]').val(addr?.street || '');
            $('input[name="cust_house_number"]').val(addr?.house_number || '');
            $('input[name="cust_floor"]').val(addr?.floor || '');
            $('input[name="cust_address_detail"]').val(addr?.address_detail || '');
            $('input[name="cust_address_url"]').val(addr?.address_url || '');

            // Set country first
            $('select[name="cust_country_id"]')
                .val(addr?.country_id || '')
                .trigger('change');

            // Store city values temporarily
            $('#existing_custom_city_id').val(addr?.city_id || '');
            $('#existing_custom_city_name').val(addr?.city || '');
            $('input[name="cust_city"]').val(addr?.city || '');

            // Load city list and auto-select city
            setTimeout(function () {
                getCustomCity(addr?.country_id, addr?.city_id);
            }, 300);
        }

        function enableManualAddress() {
            $('.cust_address_title_wrapper').removeClass('d-none');
            $('#customer_address_select').val('');
            $('input[name="cust_address_title"]').val('');
            $('input[name="cust_name"]').val('');
            $('input[name="cust_block"]').val('');
            $('input[name="cust_street"]').val('');
            $('input[name="cust_house_number"]').val('');
            $('input[name="cust_floor"]').val('');
            $('input[name="cust_city"]').val('');
            $('input[name="cust_address_detail"]').val('');
            $('input[name="cust_address_url"]').val('');
            $('textarea[name="cust_note"]').val('');

            // Set country first
            $('select[name="cust_country_id"]')
                .val('')
                .trigger('change');
            $('.cust_city_id').empty().append('<option value="">Select Area</option>');
            $('.cust_city_list').hide();
            $('.cust_city_text').show();

            // Store city values temporarily
            $('#existing_custom_city_id').val('');
            $('#existing_custom_city_name').val('');
        }

        // Helper to fetch city list for customized modal and optionally select a city by id
        function getCustomCity(countryId, cityId) {
             if (isResettingAddress) return; // 🚫 STOP auto city fill during reset
            if (!countryId) return;
            $.ajax({
                url: "<?php echo e(route('web.user.get_city')); ?>",
                method: "POST",
                data: {
                    country_id: countryId,
                    _token: "<?php echo e(csrf_token()); ?>"
                },
                success: function(data) {
                    if (!data) {
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                        return;
                    }

                    // Fill select with returned options
                    $('.cust_city_id').html(data);
                    // Create temp select to inspect options
                    var tmp = $('<select>').html(data);
                    var opts = tmp.find('option');
                    if (opts.length > 1) {
                        // Show select and hide text input
                        $('.cust_city_list').show();
                        $('.cust_city_text').hide();

                        // Determine which option to select
                        var didSelect = false;
                        // 1) If explicit cityId provided and matching option exists, select it
                        if (cityId) {
                            var optByVal = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (optByVal.length) {
                                optByVal.prop('selected', true);
                                didSelect = true;
                            }
                        }

                        // 2) If not selected and cust_city text input has a value, try to match by option text (case-insensitive)
                        if (!didSelect) {
                            var desiredText = $('input[name="cust_city"]').val();
                            if (desiredText && desiredText.trim() !== '') {
                                desiredText = desiredText.trim().toLowerCase();
                                var matched = null;
                                $('.cust_city_id option').each(function() {
                                    var t = $(this).text().trim().toLowerCase();
                                    if (t === desiredText) {
                                        matched = $(this);
                                        return false;
                                    }
                                });
                                if (matched && matched.length) {
                                    matched.prop('selected', true);
                                    didSelect = true;
                                }
                            }
                        }

                        // 3) Fallback: choose first real option
                        if (!didSelect) {
                            var firstVal = opts.eq(1).attr('value');
                            if (firstVal) $('.cust_city_id').val(firstVal);
                        }

                        // Ensure cust_city text input contains the selected option text for server-side
                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) $('input[name="cust_city"]').val(selectedText).trigger('input');

                        // After city list loads
                        if (cityId) {
                            var opt = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (opt.length) {
                                opt.prop('selected', true);
                            }
                        }

                        // Sync text field
                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) {
                            $('input[name="cust_city"]').val(selectedText);
                        }

                        // trigger change so any listeners update
                        $('.cust_city_id').trigger('change');
                    } else {
                        // No city list available: show text input
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                    }
                }
            });
        }

        function resetAddressFields() {

            isResettingAddress = true;

            // Clear text fields
            $('select[name="customer_address_id"]').val('');
            $('input[name="cust_name"]').val('');
            $('input[name="cust_block"]').val('');
            $('input[name="cust_street"]').val('');
            $('input[name="cust_house_number"]').val('');
            $('input[name="cust_floor"]').val('');
            $('input[name="cust_city"]').val('');
            $('input[name="cust_address_detail"]').val('');
            $('input[name="cust_address_url"]').val('');
            $('textarea[name="cust_note"]').val('');

            // Clear country & city
            $('select[name="cust_country_id"]').val('');
            $('.cust_city_id').empty().append('<option value="">Select Area</option>');

            // Hide dropdowns
            $('.cust_city_list').hide();
            $('.cust_city_text').show();

            $('#address_dropdown_wrapper').hide();

            // Clear temp city memory
            $('#existing_custom_city_id').val('');
            $('#existing_custom_city_name').val('');

            // Clear JS cache
            window.customerAddresses = [];

            // Allow async handlers to finish, then unlock
            setTimeout(() => {
                isResettingAddress = false;
            }, 100);
        }

        // Bind country change to fetch cities
        $('select[name="cust_country_id"]').change(function() {
            var countryId = $(this).val();
            getCustomCity(countryId);
        });
        // When city select changes, copy its text into cust_city input so server always receives a city name
        $(document).on('change', '.cust_city_id', function() {
            if (isResettingAddress) return;
            var txt = $(this).find('option:selected').text().trim();
            if (txt) {
                $('input[name="cust_city"]').val(txt);
            }
        });

        function syncAdvancePaymentSection() {
            var val = $('input[name="custom_req_type"]:checked').val();
            var $paymentSection = $('#payment_section');
            var $paymentSectionCol = $('#payment_section_col');
            var $shippingSectionCol = $('#shipping_section_col');
            var $advanceInput = $('input[name="advance_payment"]');
            var $advanceField = $paymentSection.find('.form-item').first();
            var isQuotation = val === 'quotation';

            if (isQuotation) {
                $paymentSectionCol.addClass('d-none');
                $shippingSectionCol.removeClass('col-lg-6').addClass('col-lg-12');
                $advanceField.hide();
                $advanceInput.val('0');
                $advanceInput
                    .removeClass('mandatory')
                    .next('.text-red').remove()
                    .css('border', '1px solid #e1e1e1');
                $('#generate_pending_link_wrapper').hide();
                $('#generate_pending_link_wrapper').find('.text-red').remove();
            } else {
                $paymentSectionCol.removeClass('d-none');
                $shippingSectionCol.removeClass('col-lg-12').addClass('col-lg-6');
                $advanceField.show();
                $advanceInput.addClass('mandatory');
                var isEditMode = Boolean($('#temporary_order_id').val());
                if (isEditMode) {
                    $('#generate_pending_link_wrapper').show();
                } else {
                    $('#generate_pending_link_wrapper').hide();
                }
            }

            calculateModalTotal();
        }

        // Toggle Payment Section based on Request Type
        $('input[name="custom_req_type"]').change(function () {
            syncAdvancePaymentSection();
        });

        // Handle Draft Order Selection
        $('#saved_draft_order_select').change(function() {
            var draftId = $(this).val();

            // Reset checkbox state immediately on ANY selection change
            $('#generate_pending_payment_link').prop('checked', false);

            if (!draftId) {
                $('#temporary_order_id').val('');
                $('#generate_pending_link_wrapper').hide();
                $('input[name="advance_payment"]').prop('readonly', false).removeAttr('readonly');
                if (window.customizedOrderState) {
                    window.customizedOrderState.is_advance_payment_done = 0;
                    window.customizedOrderState.can_generate_pending_link = false;
                    if (window.customizedOrderState.payment) {
                        window.customizedOrderState.payment.generate_pending_link = 0;
                    }
                }
                calculateModalTotal();
                syncAdvancePaymentSection();
                return;
            }

            var $spinner = $('#draft_loader_spinner');
            var $select = $(this);

            $spinner.show();
            $select.prop('disabled', true);

            var detailUrl = "<?php echo e(route('web.cart.get_draft_detail', ['id' => ':id'])); ?>".replace(':id', draftId);

            $.ajax({
                url: detailUrl,
                method: "GET",
                success: function(response) {
                    if (response.status && response.data) {
                        var draft = response.data;
                        $('#temporary_order_id').val(draft.id);

                        // Set Type
                        if (draft.type === 'quotation') {
                            $('#req_type_qt').prop('checked', true).trigger('change');
                        } else {
                            $('#req_type_po').prop('checked', true).trigger('change');
                        }

                        // Set Customer Details
                        var cust = draft.customer || {};
                        $('input[name="cust_phone"]').val(cust.phone || '');
                        $('input[name="cust_email"]').val(cust.email || '');

                        // Fetch addresses and restore saved address selection
                        if (cust.phone || cust.email) {
                            fetchAddresses(cust.phone || '', cust.email || '', false, cust.customer_address_id, function() {
                                // If no saved address ID was selected, ensure manual address fields from draft are populated
                                if (!cust.customer_address_id) {
                                    enableManualAddress();
                                    $('input[name="cust_name"]').val(cust.name || '');
                                    $('input[name="cust_address_title"]').val(cust.address_title_text || '');
                                    $('input[name="cust_block"]').val(cust.block || '');
                                    $('input[name="cust_street"]').val(cust.street || '');
                                    $('input[name="cust_house_number"]').val(cust.house_number || '');
                                    $('input[name="cust_floor"]').val(cust.floor || '');
                                    $('input[name="cust_address_detail"]').val(cust.address_detail || '');
                                    $('input[name="cust_address_url"]').val(cust.address_url || '');
                                    if (cust.country_id) {
                                        $('select[name="cust_country_id"]').val(cust.country_id);
                                        getCustomCity(cust.country_id, cust.city_id);
                                    } else {
                                        $('select[name="cust_country_id"]').val('');
                                    }
                                    if (cust.city) {
                                        $('input[name="cust_city"]').val(cust.city);
                                    }
                                }
                                $('textarea[name="cust_note"]').val(cust.note || '');
                            });
                        } else {
                            resetAddressFields();
                            enableManualAddress();
                            $('input[name="cust_name"]').val(cust.name || '');
                            $('input[name="cust_address_title"]').val(cust.address_title_text || '');
                            $('input[name="cust_block"]').val(cust.block || '');
                            $('input[name="cust_street"]').val(cust.street || '');
                            $('input[name="cust_house_number"]').val(cust.house_number || '');
                            $('input[name="cust_floor"]').val(cust.floor || '');
                            $('input[name="cust_address_detail"]').val(cust.address_detail || '');
                            $('input[name="cust_address_url"]').val(cust.address_url || '');
                            if (cust.country_id) {
                                $('select[name="cust_country_id"]').val(cust.country_id);
                                getCustomCity(cust.country_id, cust.city_id);
                            }
                            if (cust.city) {
                                $('input[name="cust_city"]').val(cust.city);
                            }
                            $('textarea[name="cust_note"]').val(cust.note || '');
                        }

                        // Rebuild Items
                        var $list = $('#modal_custom_items_list');
                        $list.empty();
                        var items = draft.items || [];
                        if (items.length === 0) {
                            addModalCustomItemRow();
                        } else {
                            items.forEach(function(item) {
                                var imgUrl = item.image_url || '<?php echo e(asset(Storage::url("default.png"))); ?>';
                                var rawImage = item.image || '';
                                var priceVal = parseFloat(item.price || 0).toFixed(3);
                                var qtyVal = item.quantity || 1;
                                var totalVal = parseFloat(item.total || (priceVal * qtyVal)).toFixed(3);
                                var prodTitleEscaped = $('<div>').text(item.product_title || '').html();
                                var descEscaped = $('<div>').text(item.description || '').html();
                                var itemCodeEscaped = $('<div>').text(item.item_code || '').html();
                                var colorEscaped = $('<div>').text(item.color || '').html();
                                var sizeEscaped = $('<div>').text(item.size || '').html();

                                var rowHtml = `
                                    <div class="row modal-custom-item-row">
                                        <div class="col-12 col-lg-5 form-item pr-0">
                                            <input type="hidden" name="custom_item_existing_image[]" value="${rawImage}">
                                            <div class="custom-item-image-field d-flex align-items-center">
                                                <div class="custom-item-image-preview mr-2">
                                                    <a href="${imgUrl}" target="_blank" class="custom-item-image-link d-inline-block">
                                                        <img src="${imgUrl}" alt="<?php echo e(__('web.photo_reference')); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                                    </a>
                                                    ${rawImage ? '<span class="remove-image-btn" title="Remove image">&times;</span>' : ''}
                                                </div>
                                                <div class="flex-grow-1 photo-upload-section">
                                                    <label><?php echo e(__('web.photo_reference')); ?></label>
                                                    <input type="file" name="custom_item_image[]" class="form-control custom-item-image-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3 form-item pr-0">
                                            <label><?php echo e(__('web.product_title')); ?></label>
                                            <input type="text" name="custom_product_title[]" value="${prodTitleEscaped}" class="form-control"
                                                placeholder="<?php echo e(__('web.enter_product_title')); ?>"
                                                data-req-msg="<?php echo e(__('web.error-req-product-title')); ?>">
                                        </div>

                                        <div class="col-12 col-lg-3 form-item pr-0">
                                            <label><?php echo e(__('web.description')); ?></label>
                                            <textarea name="custom_desc[]" class="form-control" placeholder="<?php echo e(__('web.enter_description')); ?>" data-req-msg="<?php echo e(__('web.error-req-description')); ?>" rows="1" style="resize: none;">${descEscaped}</textarea>
                                        </div>

                                        <div class="col-12 col-lg-1 form-item pr-0 text-center icon-column d-flex justify-content-between p-1.5" style="padding-top: 28px !important;">
                                        </div>

                                        <div class="col-12 col-lg-3 form-item pr-0">
                                            <label><?php echo e(__('web.item_code')); ?></label>
                                            <input type="text" name="custom_item_code[]" value="${itemCodeEscaped}" class="form-control"
                                                placeholder="<?php echo e(__('web.enter_item_code')); ?>"
                                                data-req-msg="<?php echo e(__('web.error-req-item-code')); ?>">
                                        </div>
                                         <div class="col-6 col-lg-2 form-item pr-0">
                                            <label><?php echo e(__('web.color')); ?></label>
                                            <input type="text" name="custom_color[]" value="${colorEscaped}" class="form-control" placeholder="<?php echo e(__('web.enter_color')); ?>" data-req-msg="<?php echo e(__('web.error-req-color')); ?>">
                                        </div>
                                         <div class="col-6 col-lg-2 form-item pr-0">
                                            <label><?php echo e(__('web.size')); ?></label>
                                            <input type="text" name="custom_size[]" value="${sizeEscaped}" class="form-control" placeholder="<?php echo e(__('web.enter_size')); ?>" data-req-msg="<?php echo e(__('web.error-req-size')); ?>">
                                        </div>
                                         <div class="col-6 col-lg-2 form-item pr-0">
                                            <label><?php echo e(__('web.price')); ?></label>
                                            <input type="number" name="custom_price[]" value="${priceVal}" class="form-control custom-price" placeholder="<?php echo e(__('web.enter_price')); ?>" step="0.001" min="0" data-req-msg="<?php echo e(__('web.error-req-price')); ?>" oninput="if(this.value.includes('.') ){let parts=this.value.split('.');if(parts[1]&&parts[1].length>3)this.value=parts[0]+'.'+parts[1].slice(0,3);}if(this.value<0)this.value=Math.abs(this.value);">
                                        </div>
                                        <div class="col-6 col-lg-1 form-item pr-0">
                                            <label><?php echo e(__('web.quantity')); ?></label>
                                            <input type="number" name="custom_qty[]" value="${qtyVal}" class="form-control custom-qty" placeholder="<?php echo e(__('web.enter_quantity')); ?>" min="1" data-req-msg="<?php echo e(__('web.error-req-quantity')); ?>">
                                        </div>
                                         <div class="col-12 col-lg-2 form-item pr-0">
                                            <label><?php echo e(__('web.total_price')); ?></label>
                                            <input type="text" name="custom_total[]" class="form-control custom-total" readonly value="${totalVal}">
                                        </div>
                                    </div>
                                `;
                                $list.append(rowHtml);
                            });
                            updateModalItemIcons();
                        }

                        // Financials
                        $('input[name="custom_shipping"]').val(parseFloat(draft.shipping || 0).toFixed(3));
                        $('select[name="custom_discount_type"]').val(draft.discount_type || '');
                        var discountVal = parseFloat(draft.discount_val || 0);
                        $('input[name="custom_discount_val"]').val(draft.discount_type === 'percentage' ? String(discountVal) : discountVal.toFixed(3));
                        var advanceVal = parseFloat((draft.payment && draft.payment.advance) || 0);
                        var $advanceInput = $('input[name="advance_payment"]');
                        $advanceInput.val(advanceVal.toFixed(3));

                        var isAdvanceDone = (draft.is_advance_payment_done == 1) || !!(draft.payment_state && draft.payment_state.advance_completed);
                        var pendingAmount = parseFloat((draft.calculation && draft.calculation.pending) || (draft.payment && draft.payment.pending) || draft.pending_amount || 0);

                        if (isAdvanceDone) {
                            $advanceInput.prop('readonly', true).attr('readonly', 'readonly');
                        } else {
                            $advanceInput.prop('readonly', false).removeAttr('readonly');
                        }

                        var savedGenPending = (draft.payment && typeof draft.payment.generate_pending_link !== 'undefined') ? Boolean(draft.payment.generate_pending_link) : (typeof draft.generate_pending_link !== 'undefined' ? Boolean(draft.generate_pending_link) : false);
                        $('#generate_pending_payment_link').prop('checked', savedGenPending);

                        // Sync window.customizedOrderState BEFORE calling calculateModalTotal & syncAdvancePaymentSection
                        window.customizedOrderState = draft;

                        if (draft.type !== 'quotation') {
                            $('#generate_pending_link_wrapper').show();
                        } else {
                            $('#generate_pending_link_wrapper').hide();
                        }

                        calculateModalTotal();
                        syncAdvancePaymentSection();
                        syncCustomizedPreviewButtonVisibility();
                    }
                },
                error: function(xhr) {
                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error loading draft order';
                    alert(errorMsg);
                },
                complete: function() {
                    $spinner.hide();
                    $select.prop('disabled', false);
                }
            });
        });

        // Add Item
        $(document).on('click', '.add-modal-custom-item', function() {
            addModalCustomItemRow();
        });

        // Remove Item
        // $(document).on('click', '.remove-modal-custom-item', function() {
        //     $(this).closest('.modal-custom-item-row').remove();
        //     calculateModalTotal();
        // });

        // Remove Item (if last row → clear inputs)
        // Function to update icon visibility
        function updateModalItemIcons() {
            var $list = $('#modal_custom_items_list');
            var $rows = $list.find('.modal-custom-item-row');
            var totalRows = $rows.length;

            // Remove all add and delete icons first
            $rows.find('.add-modal-custom-item, .remove-modal-custom-item').remove();

            $rows.each(function(index) {
                var $iconContainer = $(this).find('.icon-column');
                var isLast = (index === totalRows - 1);
                var isFirst = (index === 0);

                // Add icon only on last row
                if (isLast) {
                    $iconContainer.append('<span class="add-modal-custom-item" style="cursor:pointer; font-size: 20px; display:inline-block;" title="<?php echo e(__('web.add_item')); ?>"><i class="fa fa-plus"></i></span>');
                }

                // Delete icon on all rows except when it's the only row
                if (totalRows > 1) {
                    $iconContainer.append('<span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 20px; display:inline-block;" title="<?php echo e(__('web.remove_item')); ?>"><i class="fa fa-trash"></i></span>');
                }
            });
        }

        // Remove Item (if last row → clear inputs)
        $(document).on('click', '.remove-modal-custom-item', function () {
            var $list = $('#modal_custom_items_list');
            var totalRows = $list.find('.modal-custom-item-row').length;
            var $row = $(this).closest('.modal-custom-item-row');

            if (totalRows > 1) {
                // More than one row → remove row
                $row.remove();
                updateModalItemIcons();
            } else {
                // Last row → just clear inputs
                $row.find('textarea, input').each(function () {
                    if ($(this).hasClass('custom-total')) {
                        $(this).val('0.000');
                    } else if (!$(this).is('[type="hidden"]')) {
                        $(this).val('');
                    }
                });
                // Clear image preview
                $row.find('.custom-item-image-preview').html(
                    '<a href="<?php echo e(asset(Storage::url("default.png"))); ?>" target="_blank" class="custom-item-image-link d-inline-block">' +
                        '<img src="<?php echo e(asset(Storage::url("default.png"))); ?>" alt="<?php echo e(__("web.photo_reference")); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'<?php echo e(asset(Storage::url("default.png"))); ?>\'">' +
                    '</a>'
                );
            }

            calculateModalTotal();           
            syncCustomizedPreviewButtonVisibility();
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-price, .custom-qty', function() {
            if (this.value < 0) this.value = Math.abs(this.value);
            var row = $(this).closest('.modal-custom-item-row');
            var qty = parseFloat(row.find('.custom-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-price').val()) || 0;
            var total = qty * price;

            row.find('.custom-total').val(total.toFixed(3));
            calculateModalTotal();
            syncCustomizedPreviewButtonVisibility();
        });

        // --- Validation JS --- //
        // Show validation message on click/focus
        window.validateAdvancePayment = function() {
             var $input = $('input[name="advance_payment"]');
             if (!$input.length) return { isValid: true };
             
             var val = $input.val().trim();
             var advance = val === '' ? 0 : (parseFloat(val) || 0);
             
             var summary = (typeof getCustomizedModalSummary === 'function') ? getCustomizedModalSummary() : null;
             var total = summary ? summary.totalOrderValue : 0;
             if (!summary) {
                 var subTotal = 0;
                 $('.custom-total').each(function() {
                     subTotal += parseFloat($(this).val()) || 0;
                 });
                 var shipping = parseFloat($('input[name="custom_shipping"]').val()) || 0;
                 var discountType = $('select[name="custom_discount_type"]').val();
                 var discountVal = parseFloat($('input[name="custom_discount_val"]').val()) || 0;
                 var discountAmount = 0;
                 if (discountType === 'percentage') {
                     discountAmount = (subTotal * discountVal) / 100;
                 } else if (discountType === 'flat') {
                     discountAmount = discountVal;
                 }
                 if (discountAmount > subTotal) {
                     discountAmount = subTotal;
                 }
                 total = Math.max(0, subTotal + shipping - discountAmount);
             }
             
             var currentState = window.customizedOrderState || {};
             var isAdvanceDone = (currentState.is_advance_payment_done == 1) || !!(currentState.payment_state && currentState.payment_state.advance_completed);
             var paidAmount = parseFloat((currentState.payment && currentState.payment.paid) || (currentState.payment_state && currentState.payment_state.paid_amount) || 0);

             if (isAdvanceDone && paidAmount > 0) {
                 if (total < paidAmount) {
                     var msg = "Order total cannot be less than the paid advance amount of " + paidAmount.toFixed(3) + " KWD.";
                     $input.next('.text-red').remove();
                     $input.after('<div class="text-red advance-payment-exceeds-error" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                     $input.css('border', '1px solid red');
                     return { isValid: false, message: msg };
                 } else {
                     $input.next('.advance-payment-exceeds-error').remove();
                     if ($input.next('.text-red').length === 0) {
                         $input.css('border', '1px solid #e1e1e1');
                     }
                     var isEditMode = Boolean($('#temporary_order_id').val());
                    if (isEditMode && $('input[name="custom_req_type"]:checked').val() !== 'quotation') {
                        $('#generate_pending_link_wrapper').show();
                    } else {
                        $('#generate_pending_link_wrapper').hide();
                    }
                     return { isValid: true };
                 }
             } else {
                 var isEditMode = Boolean($('#temporary_order_id').val());
                    if (isEditMode && $('input[name="custom_req_type"]:checked').val() !== 'quotation') {
                        $('#generate_pending_link_wrapper').show();
                    } else {
                        $('#generate_pending_link_wrapper').hide();
                    }
                 if (val !== '' && advance > total) {
                      var msg = "<?php echo e(__('web.error-advance-payment-exceeds-total')); ?>";
                      $input.next('.text-red').remove();
                      $input.after('<div class="text-red advance-payment-exceeds-error" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                      $input.css('border', '1px solid red');
                      return { isValid: false, message: msg };
                 } else {
                      $input.next('.advance-payment-exceeds-error').remove();
                      if ($input.next('.text-red').length === 0) {
                          $input.css('border', '1px solid #e1e1e1');
                      }
                      return { isValid: true };
                 }
             }
        };

        $(document).on('click focus', '.mandatory', function() {
            var input = $(this);
            if (input.attr('name') === 'advance_payment') {
                var advValidation = (typeof window.validateAdvancePayment === 'function') ? window.validateAdvancePayment() : { isValid: true };
                if (!advValidation || advValidation.isValid === false || input.next('.advance-payment-exceeds-error').length) {
                    return;
                }
            }
            // Remove any existing error message and reset border
            input.next('.text-red').remove();
            input.css('border', '1px solid #e1e1e1');

            // Check if empty
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                    input.css('border', '1px solid red');
                }
            }
        });

        // Remove error when user starts typing
        $(document).on('input', '.mandatory', function() {
            var input = $(this);
            if (input.attr('name') === 'advance_payment') {
                var advValidation = (typeof window.validateAdvancePayment === 'function') ? window.validateAdvancePayment() : { isValid: true };
                if (!advValidation || advValidation.isValid === false || input.next('.advance-payment-exceeds-error').length) {
                    return;
                }
            }
            input.next('.text-red').remove();
            input.css('border', '1px solid #e1e1e1');
        });

        // Show validation message on blur if empty
        $(document).on('blur', '.mandatory', function() {
            var input = $(this);
            if (input.attr('name') === 'advance_payment') {
                var advValidation = (typeof window.validateAdvancePayment === 'function') ? window.validateAdvancePayment() : { isValid: true };
                if (!advValidation || advValidation.isValid === false || input.next('.advance-payment-exceeds-error').length) {
                    return;
                }
            }
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    // Check if error already exists
                    if (input.next('.text-red').length === 0) {
                        input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                        input.css('border', '1px solid red');
                    }
                }
            }
        });

        // Validate Advance Payment on Input
        $(document).on('input', 'input[name="advance_payment"]', function() {
             window.validateAdvancePayment();
        });

        // Auto-reset to 0 if cleared on change/blur
        $(document).on('change blur', 'input[name="advance_payment"], input[name="custom_shipping"]', function() {
            var val = $(this).val().trim();
            if (val === '') {
                $(this).val('0');
                calculateModalTotal();
            }
        });

        // Submit Logic
        $('#submit_customized_order').click(function() {
            var isValid = true;
            var firstInvalidField = null;

            function markInvalidField($field, msg) {
                if (!$field || !$field.length) {
                    return;
                }

                var $visibleField = $field.filter(':visible').first();
                if (!$visibleField.length) {
                    $visibleField = $field.first();
                }

                if (!firstInvalidField && $visibleField.length) {
                    firstInvalidField = $visibleField;
                }

                $visibleField.next('.text-red').remove();
                $visibleField.find('.text-red').remove();
                if (msg) {
                    var extraClass = '';
                    if ($visibleField.attr('name') === 'advance_payment') {
                        extraClass = ' advance-payment-exceeds-error';
                    }
                    if ($visibleField.is('#generate_pending_link_wrapper') || $visibleField.attr('id') === 'generate_pending_link_wrapper') {
                        $visibleField.append('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                    } else {
                        $visibleField.after('<div class="text-red' + extraClass + '" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                        $visibleField.css('border', '1px solid red');
                    }
                }
            }

            function scrollToCustomizedOrderField($field) {
                if (!$field || !$field.length) {
                    return;
                }

                var $target = $field.closest('.form-item').length ? $field.closest('.form-item') : $field;
                var $scrollContainer = $('#customizedOrderModal .modal-body.fancy');

                if ($scrollContainer.length && $scrollContainer[0].scrollHeight > $scrollContainer[0].clientHeight) {
                    var containerOffset = $scrollContainer.offset().top;
                    var targetOffset = $target.offset().top;
                    var scrollTop = targetOffset - containerOffset + $scrollContainer.scrollTop() - 80;
                    $scrollContainer.stop().animate({ scrollTop: Math.max(scrollTop, 0) }, 300);
                } else {
                    var pageTop = $target.offset().top - 100;
                    $('html, body').stop().animate({ scrollTop: Math.max(pageTop, 0) }, 300);
                }

                if ($field.is('input, select, textarea')) {
                    setTimeout(function() {
                        $field.trigger('focus');
                    }, 350);
                }
            }

            $('#customized_order_payload_form .mandatory').each(function() {

                if ($(this).val().trim() === '' && $(this).is(':visible')) {
                    isValid = false;
                    var msg = $(this).attr('data-req-msg');
                    markInvalidField($(this), msg);
                }
            });

            // Validate Advance Payment Validity
            var advanceInput = $('input[name="advance_payment"]');
            if (advanceInput.is(':visible')) {
                 var advValidation = (typeof window.validateAdvancePayment === 'function') ? window.validateAdvancePayment() : { isValid: true };
                 if (!advValidation || advValidation.isValid === false) {
                      isValid = false;
                      markInvalidField(advanceInput, (advValidation && advValidation.message) ? advValidation.message : "<?php echo e(__('web.error-advance-payment-exceeds-total')); ?>");
                 }
            }

            // Checkbox validation removed: in CREATE mode it is hidden/not required, and in EDIT mode it is optional.
            $('#generate_pending_link_wrapper').find('.text-red').remove();

        // Validate percentage discount precision on submit
            var discountType = $('select[name="custom_discount_type"]').val();
            var discountInput = $('input[name="custom_discount_val"]');
            var discountRawVal = discountInput.val().trim();
            if (discountType === 'percentage' && !isValidPercentageDiscountValue(discountRawVal)) {
                isValid = false;
                markInvalidField(discountInput, "Percentage discount can have only one decimal place");
            }

            // Validate Image Sizes (max 10MB)
            $('#customized_order_payload_form .custom-item-image-input').each(function() {
                var $input = $(this);
                var file = $input[0].files && $input[0].files[0];
                if (file) {
                    var maxSize = 10 * 1024 * 1024; // 10 MB
                    if (file.size > maxSize) {
                        isValid = false;
                        markInvalidField($input, "File size must not exceed 10 MB");
                    }
                }
            });

            if (!isValid && firstInvalidField) {
                scrollToCustomizedOrderField(firstInvalidField);
                return;
            }

            if (isValid) {
                var formData = new FormData($('#customized_order_payload_form')[0]);
                formData.append('_token', "<?php echo e(csrf_token()); ?>");

                $.ajax({
                    url: "<?php echo e(route('web.cart.submit_customized_order')); ?>",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            location.reload();
                        } else {
                            if (response.field) {
                                var $field = getCustomizedOrderFieldFromError(response.field);
                                if ($field.length) {
                                    scrollToCustomizedOrderField($field);
                                }
                            }
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON || {};
                        if (response.field) {
                            var $field = getCustomizedOrderFieldFromError(response.field);
                            if ($field.length) {
                                scrollToCustomizedOrderField($field);
                            }
                        }
                    }
                });
            }
        });

        // Prevent typing negative sign
        $(document).on('keydown', 'input[type="number"]', function(e) {
            // Prevent minus sign and 'e' for exponent
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });

        // Enforce positive on paste/input for advance payment
        $(document).on('input', 'input[name="advance_payment"]', function() {
             if (this.value < 0) this.value = Math.abs(this.value);
        });

        function addModalCustomItemRow() {
            var $list = $('#modal_custom_items_list');
            var totalRows = $list.find('.modal-custom-item-row').length;
            var isFirst = totalRows === 0;

            var html = `
                <div class="row modal-custom-item-row">
                    <div class="col-12 col-lg-5 form-item pr-0">
                        <input type="hidden" name="custom_item_existing_image[]" value="">
                        <div class="custom-item-image-field d-flex align-items-center">
                            <div class="custom-item-image-preview mr-2">
                                <a href="<?php echo e(asset(Storage::url('default.png'))); ?>" target="_blank" class="custom-item-image-link d-inline-block">
                                    <img src="<?php echo e(asset(Storage::url('default.png'))); ?>" alt="<?php echo e(__('web.photo_reference')); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                </a>
                            </div>
                            <div class="flex-grow-1 photo-upload-section">
                                <label><?php echo e(__('web.photo_reference')); ?></label>
                                <input type="file" name="custom_item_image[]" class="form-control custom-item-image-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-3 form-item pr-0">
                        <label><?php echo e(__('web.product_title')); ?></label>
                        <input type="text" name="custom_product_title[]" class="form-control"
                            placeholder="<?php echo e(__('web.enter_product_title')); ?>"
                            data-req-msg="<?php echo e(__('web.error-req-product-title')); ?>">
                    </div>

                    <div class="col-12 col-lg-3 form-item pr-0">
                        <label><?php echo e(__('web.description')); ?></label>
                        <textarea name="custom_desc[]" class="form-control" placeholder="<?php echo e(__('web.enter_description')); ?>" data-req-msg="<?php echo e(__('web.error-req-description')); ?>" rows="1" style="resize: none;"></textarea>
                    </div>

                    <div class="col-12 col-lg-1 form-item pr-0 text-center icon-column d-flex justify-content-between p-1.5" style="padding-top: 28px !important;">
                    </div>

                    <div class="col-12 col-lg-3 form-item pr-0">
                        <label><?php echo e(__('web.item_code')); ?></label>
                        <input type="text" name="custom_item_code[]" class="form-control"
                            placeholder="<?php echo e(__('web.enter_item_code')); ?>"
                            data-req-msg="<?php echo e(__('web.error-req-item-code')); ?>">
                    </div>
                     <div class="col-6 col-lg-2 form-item pr-0">
                        <label><?php echo e(__('web.color')); ?></label>
                        <input type="text" name="custom_color[]" class="form-control" placeholder="<?php echo e(__('web.enter_color')); ?>" data-req-msg="<?php echo e(__('web.error-req-color')); ?>">
                    </div>
                     <div class="col-6 col-lg-2 form-item pr-0">
                        <label><?php echo e(__('web.size')); ?></label>
                        <input type="text" name="custom_size[]" class="form-control" placeholder="<?php echo e(__('web.enter_size')); ?>" data-req-msg="<?php echo e(__('web.error-req-size')); ?>">
                    </div>
                     <div class="col-6 col-lg-2 form-item pr-0">
                        <label><?php echo e(__('web.price')); ?></label>
                        <input type="number" name="custom_price[]" class="form-control custom-price" placeholder="<?php echo e(__('web.enter_price')); ?>" step="0.001" min="0" data-req-msg="<?php echo e(__('web.error-req-price')); ?>" oninput="if(this.value.includes('.') ){let parts=this.value.split('.');if(parts[1]&&parts[1].length>3)this.value=parts[0]+'.'+parts[1].slice(0,3);}if(this.value<0)this.value=Math.abs(this.value);">
                    </div>
                    <div class="col-6 col-lg-1 form-item pr-0">
                        <label><?php echo e(__('web.quantity')); ?></label>
                        <input type="number" name="custom_qty[]" class="form-control custom-qty" placeholder="<?php echo e(__('web.enter_quantity')); ?>" min="1" data-req-msg="<?php echo e(__('web.error-req-quantity')); ?>">
                    </div>
                     <div class="col-12 col-lg-2 form-item pr-0">
                        <label><?php echo e(__('web.total_price')); ?></label>
                        <input type="text" name="custom_total[]" class="form-control custom-total" readonly value="0.000">
                    </div>
                </div>
            `;
            $('#modal_custom_items_list').append(html);
            updateModalItemIcons();
            syncCustomizedPreviewButtonVisibility();
        }

        function getCustomizedOrderFieldFromError(fieldKey) {
            if (!fieldKey) {
                return $();
            }

            var fieldName = fieldKey;
            var fieldIndex = null;

            if (fieldKey.indexOf('.') !== -1) {
                var parts = fieldKey.split('.');
                fieldName = parts[0];
                fieldIndex = parseInt(parts[1], 10);
            }

            var $baseField = $('#customized_order_payload_form').find('[name="' + fieldName + '"], [name="' + fieldName + '[]"]');

            if (fieldIndex !== null && !isNaN(fieldIndex)) {
                var $indexedArrayField = $('#customized_order_payload_form').find('[name="' + fieldName + '[]"]').eq(fieldIndex);
                if ($indexedArrayField.length) {
                    return $indexedArrayField.filter(':visible').first();
                }

                var $indexedField = $baseField.eq(fieldIndex);
                if ($indexedField.length) {
                    return $indexedField.filter(':visible').first();
                }
            }

            var $visibleField = $baseField.filter(':visible').first();
            return $visibleField.length ? $visibleField : $baseField.first();
        }

        function renderCustomItemImagePreview($input) {
            var $field = $input.closest('.custom-item-image-field');
            var $preview = $field.find('.custom-item-image-preview');
            var file = $input[0].files && $input[0].files[0];

            if (!file) {
                // Reset to default image if no file selected
                var defaultImageUrl = '<?php echo e(asset(Storage::url("default.png"))); ?>';
                $preview.html(
                    '<a href="' + defaultImageUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                        '<img src="' + defaultImageUrl + '" alt="<?php echo e(__("web.photo_reference")); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'' + defaultImageUrl + '\'">' +
                    '</a>'
                );
                return;
            }

            if (!file.type || file.type.indexOf('image/') !== 0) {
                // Reset to default image if invalid file type
                var defaultImageUrl = '<?php echo e(asset(Storage::url("default.png"))); ?>';
                $preview.html(
                    '<a href="' + defaultImageUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                        '<img src="' + defaultImageUrl + '" alt="<?php echo e(__("web.photo_reference")); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'' + defaultImageUrl + '\'">' +
                    '</a>'
                );
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $preview.html(
                    '<a href="' + e.target.result + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                        '<img src="' + e.target.result + '" alt="Image Preview" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;">' +
                    '</a>' +
                    '<span class="remove-image-btn" title="Remove image">&times;</span>'
                );
            };
            reader.readAsDataURL(file);
        }

        $(document).on('change', '.custom-item-image-input', function() {
            var $input = $(this);
            var file = $input[0].files && $input[0].files[0];
            
            // Clear any existing error for this specific field first
            $input.next('.text-red').remove();
            $input.css('border', '1px solid #e1e1e1');
            
            if (file) {
                var maxSize = 10 * 1024 * 1024; // 10 MB
                if (file.size > maxSize) {
                    $input.css('border', '1px solid red');
                    $input.after('<div class="text-red img-size-error" style="font-size:12px; margin-top:5px;">File size must not exceed 10 MB</div>');
                }
            }
            
            renderCustomItemImagePreview($input);
            syncCustomizedPreviewButtonVisibility();
        });

        $(document).on('click', '.remove-image-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $preview = $(this).closest('.custom-item-image-preview');
            var $row = $preview.closest('.modal-custom-item-row');
            
            // Clear the file input
            var $fileInput = $row.find('.custom-item-image-input');
            $fileInput.val('');
            
            // Clear the hidden existing image input
            var $existingInput = $row.find('input[name="custom_item_existing_image[]"]');
            $existingInput.val('');
            
            // Clear validation state on the file input
            $fileInput.next('.text-red').remove();
            $fileInput.css('border', '1px solid #e1e1e1');
            
            // Reset preview to default placeholder image
            var defaultImageUrl = '<?php echo e(asset(Storage::url("default.png"))); ?>';
            $preview.html(
                '<a href="' + defaultImageUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                    '<img src="' + defaultImageUrl + '" alt="<?php echo e(__("web.photo_reference")); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'' + defaultImageUrl + '\'">' +
                '</a>'
            );
            
            syncCustomizedPreviewButtonVisibility();
        });

        $(document).on('input change', '#modal_custom_items_list .modal-custom-item-row input, #modal_custom_items_list .modal-custom-item-row textarea', function() {
            syncCustomizedPreviewButtonVisibility();
        });

        // Remove Row
        $(document).on('click', '.remove-custom-item', function() {
            $(this).closest('.custom-item-row').remove();
            calculateCustomOrderTotal();
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-item-qty, .custom-item-price', function() {
            var row = $(this).closest('.custom-item-row');
            var qty = parseFloat(row.find('.custom-item-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-item-price').val()) || 0;
            var total = qty * price;

            // Update row total field
            row.find('.custom-item-total').val(total.toFixed(3));

            calculateCustomOrderTotal();
        });

        window.restrictDiscountInput = function(input) {
            let discountType = $('select[name="custom_discount_type"]').val();
            let maxDecimals = discountType === 'percentage' ? 1 : 3;

            if (input.value < 0) {
                input.value = Math.abs(input.value);
            }

            if (input.value.includes('.')) {
                let parts = input.value.split('.');
                if (parts[1] && parts[1].length > maxDecimals) {
                    input.value = parts[0] + '.' + parts[1].slice(0, maxDecimals);
                }
                if (discountType === 'percentage' && /^0+$/.test(parts[1].slice(0, maxDecimals))) {
                    input.value = parts[0];
                }
            }
        };

        function isValidPercentageDiscountValue(value) {
            if (value === '') return true;
            return /^\d+(\.\d{1})?$/.test(value);
        }

        $(document).on('change', 'select[name="custom_discount_type"]', function() {
            let discountType = $(this).val();
            let discountInput = $('input[name="custom_discount_val"]');
            discountInput.attr('step', discountType === 'percentage' ? '0.1' : '0.001');

            if (!discountType) {
                discountInput.val('');
                discountInput.next('.text-red').remove();
                discountInput.css('border', '1px solid #e1e1e1');
                return;
            }

            if (discountInput.length) {
                restrictDiscountInput(discountInput[0]);
            }
        });
        $('select[name="custom_discount_type"]').trigger('change');

        $(document).on('input change', 'select[name="custom_discount_type"], input[name="custom_discount_val"]', function() {

            let discountType = $('select[name="custom_discount_type"]').val();
            let discountInput = $('input[name="custom_discount_val"]');
            let discountRawVal = discountInput.val().trim();
            let discountVal = parseFloat(discountRawVal) || 0;

            let subTotal = 0;
            $('.custom-total').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });

            // Clear old error
            discountInput.next('.text-red').remove();
            discountInput.css('border', '1px solid #e1e1e1');

            // Percentage precision validation
            if (discountType === 'percentage' && !isValidPercentageDiscountValue(discountRawVal)) {
                showDiscountError("Percentage discount can have only one decimal place");
                return;
            }

            // Percentage validation
            if (discountType === 'percentage' && discountVal > 100) {
                showDiscountError("Percentage discount cannot be more than 100%");
                return;
            }

            // Flat discount validation
            if (discountType === 'flat' && discountVal > subTotal) {
                showDiscountError("Discount amount cannot be greater than order total");
                return;
            }
        });

        syncCustomizedPreviewButtonVisibility();

        function showDiscountError(msg) {
            let input = $('input[name="custom_discount_val"]');
            input.after('<div class="text-red" style="font-size:12px;margin-top:5px;">' + msg + '</div>');
            input.css('border', '1px solid red');
        }

        function getCustomizedModalSummary() {
            let subTotal = 0;
            $('.custom-total').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });

            let shipping = parseFloat($('input[name="custom_shipping"]').val()) || 0;
            let advance = parseFloat($('input[name="advance_payment"]').val()) || 0;
            let discountType = $('select[name="custom_discount_type"]').val();
            let discountVal = parseFloat($('input[name="custom_discount_val"]').val()) || 0;
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subTotal * discountVal) / 100;
            } else if (discountType === 'flat') {
                discountAmount = discountVal;
            }

            if (discountAmount > subTotal) {
                discountAmount = subTotal;
            }

            let totalOrderValue = subTotal + shipping - discountAmount;
            if (totalOrderValue < 0) {
                totalOrderValue = 0;
            }

            let pendingAmount = totalOrderValue - advance;
            if (pendingAmount < 0) {
                pendingAmount = 0;
            }

            return {
                subTotal: subTotal,
                shipping: shipping,
                advance: advance,
                discountType: discountType,
                discountAmount: discountAmount,
                totalOrderValue: totalOrderValue,
                pendingAmount: pendingAmount
            };
        }

        function normalizeCustomizedQuantity(quantity) {
            if (quantity === null || typeof quantity === 'undefined' || quantity === '') {
                return '';
            }

            var parsed = parseInt(quantity, 10);
            return isNaN(parsed) ? '' : parsed;
        }

        function updateCustomizedModalSummary() {
            let summary = getCustomizedModalSummary();
            let discountLabel = '';
            let advanceValue = parseFloat(summary.advance) || 0;

            if (summary.discountType === 'percentage') {
                discountLabel = '(<?php echo e(__('web.discount_percentage')); ?>)';
            } else if (summary.discountType === 'flat') {
                discountLabel = '(<?php echo e(__('web.discount_flat')); ?>)';
            }

            $('#modal_sub_total').text(summary.subTotal.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);
            $('#modal_shipping_charge').text(summary.shipping.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);
            $('#modal_discount_amount').text('- ' + summary.discountAmount.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);
            $('#modal_grand_total').text(summary.totalOrderValue.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);
            $('#modal_advance_paid').text('- ' + advanceValue.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);
            $('#modal_pending_amount').text(summary.pendingAmount.toFixed(3) + ' ' + window.customizedOrderCurrencyCode);

            if (advanceValue > 0) {
                $('#modal_advance_paid_row').show();
            } else {
                $('#modal_advance_paid_row').hide();
            }

            var discountLabelText = '<?php echo e(__('web.discount')); ?>';
            if (discountLabel) {
                discountLabelText += ' <small>' + discountLabel + '</small>';
            }
            $('#modal_discount_label').html(discountLabelText);
        }

        function calculateModalTotal() {
            updateCustomizedModalSummary();
            window.validateAdvancePayment();
        }

        $(document).on('input change',
            '.custom-price, .custom-qty, input[name="advance_payment"], input[name="custom_shipping"], input[name="custom_discount_val"], select[name="custom_discount_type"]',
            calculateModalTotal
        );

        // Remove Confirmation Modal Logic
        var removeConfirmed = false;
        $('#confirm_remove_customized_order').click(function() {
            removeConfirmed = true;
            $.ajax({
                url: "<?php echo e(route('web.cart.remove_customized_order')); ?>",
                method: "POST",
                data: {
                    _token: "<?php echo e(csrf_token()); ?>"
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                         // if fails, close modal
                         $('#removeCustomizedOrderModal').modal('hide');
                    }
                }
            });
        });

        $('#removeCustomizedOrderModal').on('hidden.bs.modal', function() {
            // No checkbox to restore — button-based trigger, nothing to reset
        });

        // Restrict negative values
        $(document).on('input', 'input[name="custom_price[]"], input[name="custom_qty[]"], input[name="advance_payment"], input[name="custom_shipping"], input[name="custom_discount_val"]', function() {
             if (this.value < 0) {
                this.value = Math.abs(this.value);
            }
        });

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
            var form = $('#customized_order_payload_form')[0];
            if (form) {
                form.reset();
            }

            $('#modal_custom_items_list').empty();
            $('#modal_sub_total').text('0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_shipping_charge').text('0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_discount_amount').text('- 0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_grand_total').text('0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_advance_paid').text('- 0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_pending_amount').text('0.000 ' + window.customizedOrderCurrencyCode);
            $('#modal_discount_label').html('<?php echo e(__('web.discount')); ?>');
            $('#modal_advance_paid_row').hide();
            $('input[name="advance_payment"]').val('0').prop('readonly', false).removeAttr('readonly');
            $('#generate_pending_link_wrapper').hide();
            $('#generate_pending_payment_link').prop('checked', false);
            $('#generate_pending_link_wrapper').find('.text-red').remove();
            $('input[name="custom_shipping"]').val('0');
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
            var state = window.savedCustomizedOrderState || window.customizedOrderState || {};
            var discountType = state.discount_type || '';
            var discountVal = parseFloat(state.discount_val || 0) || 0;
            var discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subTotal * discountVal) / 100;
            } else if (discountType === 'flat') {
                discountAmount = discountVal;
            }

            return Math.min(discountAmount, subTotal);
        }

        function renderCustomizedCartSummary() {
            var state = window.savedCustomizedOrderState || window.customizedOrderState;
            if (!state || !Array.isArray(state.items)) {
                return;
            }

            var subTotal = 0;
            (state.items || []).forEach(function(item) {
                subTotal += parseFloat(item.total || 0) || 0;
            });

            var shipping = parseFloat(state.shipping || 0) || 0;
            var advance = parseFloat((state.payment && state.payment.advance) || 0) || 0;
            var discountAmount = getCustomizedCartDiscountAmount(subTotal);
            var customGrandTotal = Math.max(0, subTotal + shipping - discountAmount);
            var pendingAmount = Math.max(0, customGrandTotal - advance);

            var isAdvanceDone = (state.is_advance_payment_done == 1) || !!(state.payment_state && state.payment_state.advance_completed);
            var isGeneratePendingRequested = (state.payment && typeof state.payment.generate_pending_link !== 'undefined') 
                ? Boolean(state.payment.generate_pending_link) 
                : (typeof state.generate_pending_link !== 'undefined' ? Boolean(state.generate_pending_link) : false);

            var isCreateMode = !Boolean(state.temporary_order_id);
            var payableCustomAmount = 0;
            if (isCreateMode) {
                payableCustomAmount = advance > 0 ? advance : 0;
            } else {
                if (isAdvanceDone) {
                    payableCustomAmount = isGeneratePendingRequested ? pendingAmount : 0;
                } else {
                    payableCustomAmount = advance > 0 ? advance : (isGeneratePendingRequested ? pendingAmount : 0);
                }
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
                $('#modal_sub_total').text('0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_shipping_charge').text('0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_discount_amount').text('- 0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_grand_total').text('0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_advance_paid').text('- 0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_pending_amount').text('0.000 ' + window.customizedOrderCurrencyCode);
                $('#modal_discount_label').html('<?php echo e(__('web.discount')); ?>');
                $('#modal_advance_paid_row').hide();
                $('input[name="advance_payment"]').val('0');
                $('input[name="custom_shipping"]').val('0');
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

                var quantityValue = normalizeCustomizedQuantity(item.quantity);

                $row.find('input[name="custom_item_code[]"]').val(item.item_code || '');
                $row.find('input[name="custom_product_title[]"]').val(item.product_title || '');
                $row.find('textarea[name="custom_desc[]"]').val(item.description || '');
                $row.find('input[name="custom_color[]"]').val(item.color || '');
                $row.find('input[name="custom_size[]"]').val(item.size || '');
                $row.find('input[name="custom_price[]"]').val(parseFloat(item.price || 0).toFixed(3));
                $row.find('input[name="custom_qty[]"]').val(quantityValue);
                $row.find('input[name="custom_total[]"]').val(parseFloat(item.total || 0).toFixed(3));
                $row.find('input[name="custom_item_existing_image[]"]').val(item.image || '');

                var $preview = $row.find('.custom-item-image-preview');
                if (item.image) {
                    var previewUrl = getCustomizedItemImageUrl(item.image);
                    $preview.html(
                        '<a href="' + previewUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                            '<img src="' + previewUrl + '" alt="<?php echo e(__('web.photo_reference')); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'<?php echo e(asset(Storage::url('default.png'))); ?>\'">' +
                        '</a>' +
                        '<span class="remove-image-btn" title="Remove image">&times;</span>'
                    );
                } else {
                    var defaultImageUrl = '<?php echo e(asset(Storage::url("default.png"))); ?>';
                    $preview.html(
                        '<a href="' + defaultImageUrl + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                            '<img src="' + defaultImageUrl + '" alt="<?php echo e(__("web.photo_reference")); ?>" style="width:5rem; height:5rem; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;" onerror="this.onerror=null;this.src=\'' + defaultImageUrl + '\'">' +
                        '</a>'
                    );
                }
            });

            updateModalItemIcons();

            $('input[name="custom_shipping"]').val(parseFloat(window.customizedOrderState.shipping || 0).toFixed(3));
            $('select[name="custom_discount_type"]').val(window.customizedOrderState.discount_type || '');
            $('input[name="custom_discount_val"]').val((function() {
                var discountType = window.customizedOrderState.discount_type || '';
                var discountVal = parseFloat(window.customizedOrderState.discount_val || 0) || 0;
                return discountType === 'percentage' ? String(discountVal) : discountVal.toFixed(3);
            })());
            var $advanceInput = $('input[name="advance_payment"]');
            $advanceInput.val(parseFloat((window.customizedOrderState.payment && window.customizedOrderState.payment.advance) || 0).toFixed(3));

            var isAdvanceDone = (window.customizedOrderState.is_advance_payment_done == 1) || !!(window.customizedOrderState.payment_state && window.customizedOrderState.payment_state.advance_completed);
            var pendingAmount = parseFloat((window.customizedOrderState.calculation && window.customizedOrderState.calculation.pending) || (window.customizedOrderState.payment && window.customizedOrderState.payment.pending) || window.customizedOrderState.pending_amount || 0);

            if (isAdvanceDone) {
                $advanceInput.prop('readonly', true).attr('readonly', 'readonly');
            } else {
                $advanceInput.prop('readonly', false).removeAttr('readonly');
            }

            var savedGenPending = (window.customizedOrderState.payment && typeof window.customizedOrderState.payment.generate_pending_link !== 'undefined') ? Boolean(window.customizedOrderState.payment.generate_pending_link) : (typeof window.customizedOrderState.generate_pending_link !== 'undefined' ? Boolean(window.customizedOrderState.generate_pending_link) : false);
            $('#generate_pending_payment_link').prop('checked', savedGenPending);

            var isEditMode = Boolean($('#temporary_order_id').val());
            if (isEditMode && $('input[name="custom_req_type"]:checked').val() !== 'quotation') {
                $('#generate_pending_link_wrapper').show();
            } else {
                $('#generate_pending_link_wrapper').hide();
            }

            $('textarea[name="cust_note"]').val((window.customizedOrderState.customer && window.customizedOrderState.customer.note) || '');
            updateCustomizedModalSummary();
            syncAdvancePaymentSection();
        }

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
                var quantityValue = normalizeCustomizedQuantity(item.quantity);

                formData.append('custom_item_code[]', item.item_code || '');
                formData.append('custom_product_title[]', item.product_title || '');
                formData.append('custom_desc[]', item.description || '');
                formData.append('custom_color[]', item.color || '');
                formData.append('custom_size[]', item.size || '');
                formData.append('custom_price[]', parseFloat(item.price || 0).toFixed(3));
                formData.append('custom_qty[]', quantityValue === '' ? '' : String(quantityValue));
                formData.append('custom_item_existing_image[]', item.image || '');
            });

            formData.append('custom_shipping', parseFloat(window.customizedOrderState.shipping || 0).toFixed(3));
            formData.append('custom_discount_type', window.customizedOrderState.discount_type || '');
            formData.append('custom_discount_val', parseFloat(window.customizedOrderState.discount_val || 0).toFixed(3));
            formData.append('advance_payment', parseFloat((window.customizedOrderState.payment && window.customizedOrderState.payment.advance) || 0).toFixed(3));
            if ($('#generate_pending_payment_link').is(':checked')) {
                formData.append('generate_pending_payment_link', '1');
            } else {
                formData.append('generate_pending_payment_link', '0');
            }
            if (window.customizedOrderState.temporary_order_id) {
                formData.append('temporary_order_id', window.customizedOrderState.temporary_order_id);
            }

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
                },
                error: function(xhr) {
                    var response = xhr.responseJSON || {};
                    if (response.field) {
                        var $field = getCustomizedOrderFieldFromError(response.field);
                        if ($field.length) {
                            $('#customizedOrderModal').modal('show');
                            setTimeout(function() {
                                var $modalField = getCustomizedOrderFieldFromError(response.field);
                                if ($modalField.length) {
                                    var $target = $modalField.closest('.form-item').length ? $modalField.closest('.form-item') : $modalField;
                                    var $scrollContainer = $('#customizedOrderModal .modal-body.fancy');
                                    if ($scrollContainer.length && $scrollContainer[0].scrollHeight > $scrollContainer[0].clientHeight) {
                                        var containerOffset = $scrollContainer.offset().top;
                                        var targetOffset = $target.offset().top;
                                        var scrollTop = targetOffset - containerOffset + $scrollContainer.scrollTop() - 80;
                                        $scrollContainer.stop().animate({ scrollTop: Math.max(scrollTop, 0) }, 300);
                                    }
                                    $modalField.trigger('focus');
                                }
                            }, 250);
                        }
                    }
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
            var state = window.savedCustomizedOrderState || window.customizedOrderState;
            if (!state || !Array.isArray(state.items) || !state.items[index]) {
                return;
            }

            state.items.splice(index, 1);
            $('.custom-order-item[data-index="' + index + '"]').remove();
            reindexCustomizedCartRows();

            if (!state.items.length) {
                window.customizedReviewSyncVisibleGrandTotal(0);
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
                            window.savedCustomizedOrderState = null;
                            window.customizedOrderState = null;
                            clearCustomizedOrderFormState();
                            syncCustomizedPreviewButtonVisibility();
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
            var state = window.savedCustomizedOrderState || window.customizedOrderState;
            if (!state || !Array.isArray(state.items) || !state.items[index]) {
                return;
            }

            var item = state.items[index];
            var currentQty = parseInt(item.quantity, 10);
            if (isNaN(currentQty) || currentQty < 1) {
                currentQty = 0;
            }
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

        
        

        renderCustomizedCartSummary();
        
        syncAdvancePaymentSection();
        syncCustomizedPreviewButtonVisibility();
        
        // Initialize icons on page load if items exist
        if ($('#modal_custom_items_list .modal-custom-item-row').length > 0) {
            updateModalItemIcons();
        }
    });
</script>
<?php echo $__env->make('web.cart.save-note', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Remove Confirmation Modal -->
<div id="removeCustomizedOrderModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h1 class="address_title"><?php echo e(__('web.confirmation')); ?></h1>
                <p><?php echo e(__('web.remove_customized_order')); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirm_remove_customized_order"><?php echo e(__('web.yes_remove')); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo e(__('web.cancel')); ?></button>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('web.cart.save-note', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/cart/index.blade.php ENDPATH**/ ?>
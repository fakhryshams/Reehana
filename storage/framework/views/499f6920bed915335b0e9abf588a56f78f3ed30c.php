<html lang="<?php echo e(Helper::getLanguage()?Helper::getLanguage()->code :'en'); ?>">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="generator" content="<?php echo e(Helper::getAppName()?Helper::getAppName():''); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
        <link rel="shortcut icon" href="<?php echo e(asset('front/img/favicon.png')); ?>" type="image/x-icon">
        <meta name="description" content="<?php echo e(Helper::getAppName()?Helper::getAppName():''); ?>">
        <link rel="manifest" href="<?php echo e(asset('front/css/manifest.json')); ?>">

        <?php
            $headIsPO = (!empty($customizedOrder['type']) ? $customizedOrder['type'] : (!empty($type) ? $type : 'purchase_order')) === 'purchase_order';
            $headPrefix = $headIsPO ? 'T-PO' : 'T-QO';
            $headTempId = !empty($temporaryOrderId) ? $temporaryOrderId : (!empty($customizedOrder['temporary_order_id']) ? $customizedOrder['temporary_order_id'] : null);
        ?>
        <title><?php echo e(Helper::getAppName()?Helper::getAppName()." | ":''); ?><?php echo e($headPrefix); ?><?php echo e(!empty($headTempId) ? ' #'.$headTempId.' ' : ' '); ?>PREVIEW</title>
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
            #preloader, #pageloader, .preloader { display:none !important; visibility:hidden !important; opacity:0 !important; pointer-events:none !important; }
            .print-letterhead-footer {
                display: none;
            }
            .print-footer-spacer {
                display: none;
            }

            .preview-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding-top: 10px;
                padding-bottom: 20px;
                border-bottom: 1px solid #ddd;
                margin-bottom: 24px;
                width: 100%;
                box-sizing: border-box;
                overflow: visible;
            }

            .preview-header .preview-logo-container {
                flex: 0 0 auto;
                max-width: 50%;
            }

            .preview-header .preview-date {
                flex: 0 0 auto;
                margin-left: auto;
                text-align: right;
                font-size: 16px;
                line-height: 1.4;
                color: #111;
                margin-top: 0;
                margin-bottom: 0;
                overflow: visible;
            }

            .preview-header .preview-meta {
                margin: 0;
                padding: 0;
                text-align: right;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 6px;
            }

            .preview-header .preview-meta-row {
                margin: 0;
                padding: 0;
                text-align: right;
            }

            .preview-header .preview-meta-label,
            .preview-header-first-page-date .preview-meta-label,
            .preview-meta-label {
                display: inline !important;
                font-size: 15px !important;
                font-family: 'Futura Md BT', sans-serif !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px !important;
                margin-bottom: 0 !important;
                text-align: right !important;
                white-space: nowrap !important;
                line-height: 1.2 !important;
                color: #111 !important;
            }

            .preview-header .preview-meta-value,
            .preview-header-first-page-date .preview-meta-value,
            .preview-meta-value {
                display: inline !important;
                font-size: 14px !important;
                font-family: 'Futura Bk BT', sans-serif !important;
                color: #222 !important;
                text-align: right !important;
                white-space: nowrap !important;
                line-height: 1.3 !important;
                margin-left: 2px !important;
            }

            .preview-logo-container {
                width: 50%;
            }

            .preview-logo {
                width: 300px;
                height: 66px;
                object-fit: contain;
                margin-left: -58px;
            }

            .preview-header-first-page-date {
                display: none !important;
            }

            @media (max-width: 575px) {
                .preview-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .preview-header-first-page-date {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .preview-header-first-page-date .preview-date {
                    text-align: left;
                    margin-top: 0;
                }
            }

            .bank-transfer-box {
                margin-top: 20px;
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border: 3px dashed #ccc;
                border-radius: 4px;
                font-size: 16px;
                width: 100%;
                box-sizing: border-box;
                page-break-inside: avoid;
                break-inside: avoid;
                page-break-before: auto;
                break-before: auto;
            }

            .bank-transfer-box * {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .bank-details-wrapper {
                page-break-inside: avoid;
                break-inside: avoid;
                min-height: 0;
            }

            .bank-transfer-box .bank-transfer-title {
                margin: 0 0 10px 0;
                color: #333;
                font-weight: 700;
            }

            .bank-transfer-box .bank-info-row {
                display: flex;
                padding: 3px 0;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .bank-transfer-box table {
                width: 100%;
                border-collapse: collapse;
            }

            .bank-transfer-box td {
                padding: 3px 0;
                vertical-align: top;
            }

            .bank-transfer-box .bank-label {
                color: #666;
                width: 150px;
                flex-shrink: 0;
            }

            .bank-transfer-box .bank-value {
                color: #333;
                font-weight: 700;
                flex: 1;
            }

            .bank-transfer-box .bank-note {
                margin: 10px 0 0 0;
                color: #666;
                font-size: 16px;
                page-break-inside: avoid;
                break-inside: avoid;
                display: block;
                visibility: visible;
                opacity: 1;
                white-space: normal;
            }

            .item-summary-row {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 10px;
                width: 100%;
                min-width: 0;
            }

            .item-summary-title {
                flex: 1 1 auto;
                min-width: 0;
            }

            .item-summary-code {
                flex: 0 0 auto;
                text-align: right;
                white-space: nowrap;
                font-size: 16px;
                line-height: 1.2;
                color: #333;
            }

            .item-summary-code-label {
                display: block;
                font-weight: 700;
                text-transform: uppercase;
                margin-bottom: 1px;
            }

            .item-summary-description {
                margin-top: 4px;
                font-size: 12px;
                color: #555;
                word-break: break-word;
            }

            .item-description-row td {
                padding-top: 4px !important;
                border-top: none !important;
            }

            .item-product-row td {
                padding-top: 10px !important;
                font-size: 16px !important;
            }

            .barcode-cell {
                min-width: 0 !important;
                max-width: 100% !important;
                word-break: break-all !important;
                overflow-wrap: anywhere !important;
                white-space: normal !important;
                line-height: 1.3 !important;
            }

            .qty-cell {
                text-align: center !important;
            }

            .prod-name {
                text-transform: uppercase !important;
            }

            .item-description {
                text-transform: uppercase !important;
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
                /* text-align: justify; */
                text-justify: inter-word;
                color: #555;
                font-size: 16px;
                margin-top: 6px;
                line-height: 1.5 !important;
            }

            .item-product-row {
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
            }

            .item-product-row td {
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
            }

            .item-has-description td {
                border-bottom: none !important;
            }

            .item-description-cell {
                font-size: 16px;
                color: #555;
                word-break: break-word;
                white-space: normal;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .item-description-text {
                display: block;
                width: 100%;
                margin: 0;
                text-align: justify;
                text-justify: inter-word;
            }

            .item-code-th,
            .total-price-th {
                white-space: nowrap;
            }

            .summary-table-head th {
                text-transform: uppercase;
                font-size: 16px;
                line-height: 1.1;
                letter-spacing: 0;
            }

            .desktop-customized-items {
                display: table;
                width: 100%;
            }

            .mobile-customized-items {
                display: none !important;
            }

            @media  print {
                .desktop-customized-items {
                    display: table !important;
                }
                .mobile-customized-items {
                    display: none !important;
                }
            }

            .cart-table-container {
                page-break-inside: auto;
            }

            .cart-list-table th {
                padding: 10px 5px !important;
            }

            .cart-list-table tr td {
                padding: 10px 5px !important;
            }

            .cart-list-table tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .cart-list-table tbody tr:last-child td {
                border-bottom: none !important;
            }

            .cart-list-table tbody tr.item-product-row:last-child td {
                border-bottom: 1px solid #d7d7d7 !important;
            }

            .item-description-row {
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
                page-break-before: auto;
                break-before: auto;
            }

            .item-has-description td {
                border-bottom: none !important;
            }

            .item-description-cell {
                padding: 8px 12px !important;
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
                font-size: 16px;
                color: #555;
                background-color: transparent;
                border-top: none !important;
            }

            /* ============================================================
               BILLING DETAILS & SUMMARY 3-COLUMN CONTINUOUS DIVIDER ARCHITECTURE
               ============================================================ */
            .order-details,
            .billing-summary-grid,
            .billing-summary-section {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 32px minmax(0, 1fr) !important;
                column-gap: 0 !important;
                row-gap: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin-top: 24px !important;
                margin-bottom: 24px !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
                box-sizing: border-box !important;
                clear: both !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                align-items: stretch !important;
            }

            .billing-column,
            .summary-column {
                min-width: 0 !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }

            .billing-column {
                grid-column: 1 !important;
            }

            .divider-column {
                grid-column: 2 !important;
                width: 32px !important;
                align-self: stretch !important;
                display: flex !important;
                justify-content: center !important;
                align-items: stretch !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-sizing: border-box !important;
            }

            .divider-column .grid-divider-line {
                width: 1px !important;
                height: 100% !important;
                min-height: 100% !important;
                background-color: #eeeeee !important;
                margin: 0 auto !important;
                display: block !important;
            }

            .summary-column {
                grid-column: 3 !important;
            }

            .column-heading {
                min-width: 0 !important;
                box-sizing: border-box !important;
                margin: 0 !important;
                padding: 0 0 14px 0 !important;
                border: none !important;
                display: flex !important;
                align-items: flex-end !important;
            }

            .column-heading .section-title,
            .column-heading h4,
            .prod-summary > h4,
            .prod-summary h4,
            .prod-summary .section-title,
            .section-title {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 20px !important;
                font-weight: 600 !important;
                line-height: 1.2 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                color: #333 !important;
                white-space: nowrap !important;
                border: none !important;
                box-sizing: border-box !important;
            }

            .prod-summary > h4,
            .prod-summary h4,
            .prod-summary .section-title {
                margin-top: 0 !important;
                margin-bottom: 12px !important;
            }

            .column-heading .section-title span,
            .column-heading h4 span {
                font-size: 15px !important;
                font-weight: 400 !important;
                letter-spacing: normal !important;
                color: #666 !important;
                text-transform: uppercase !important;
            }

            .column-body {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }

            .billing-row-item {
                min-width: 0 !important;
                box-sizing: border-box !important;
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                padding: 7px 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                min-height: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                border: none !important;
                border-bottom: 1px solid #eeeeee !important;
                color: #555 !important;
                text-align: left !important;
            }

            .summary-row-item {
                min-width: 0 !important;
                box-sizing: border-box !important;
                display: flex !important;
                align-items: baseline !important;
                justify-content: space-between !important;
                padding: 7px 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                min-height: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                border: none !important;
                border-bottom: 1px solid #eeeeee !important;
            }

            .summary-row-item .summary-label {
                display: block !important;
                white-space: nowrap !important;
                flex: 0 0 auto !important;
                color: #555 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                font-weight: 500 !important;
                text-align: left !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .summary-row-item .summary-value {
                display: block !important;
                white-space: nowrap !important;
                text-align: right !important;
                flex: 0 0 auto !important;
                width: auto !important;
                margin: 0 0 0 auto !important;
                padding: 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                color: #333 !important;
                font-weight: 600 !important;
            }

            .summary-row-item .summary-value .price-amount {
                display: inline !important;
                line-height: 1.4 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .summary-row-item.grandtotal .summary-label,
            .summary-row-item.grandtotal .summary-value {
                font-weight: 700 !important;
                color: #111 !important;
            }

            .pay-now-row {
                border-bottom: none !important;
                padding-top: 14px !important;
                padding-bottom: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 100% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: stretch !important;
                box-sizing: border-box !important;
            }

            /* Billing row internal elements */
            .billing-row-inline {
                display: block !important;
                width: 100% !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                color: #555 !important;
                word-break: normal !important;
                overflow-wrap: normal !important;
                white-space: normal !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .billing-customer-name {
                font-size: 15px !important;
                font-weight: 600 !important;
                color: #333 !important;
                display: inline !important;
                line-height: 1.4 !important;
            }

            .billing-field {
                display: inline !important;
                white-space: nowrap !important;
            }

            .billing-label {
                font-weight: 700 !important;
                color: #333 !important;
                margin-right: 2px !important;
                display: inline !important;
                white-space: nowrap !important;
                line-height: 1.4 !important;
            }

            .billing-val {
                font-weight: 400 !important;
                color: #555 !important;
                display: inline !important;
                line-height: 1.4 !important;
            }

            .billing-comma {
                display: inline !important;
                color: #555 !important;
            }

            .billing-row-multiline {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                gap: 6px !important;
                width: 100% !important;
                min-width: 0 !important;
                line-height: 1.4 !important;
            }

            .billing-row-multiline .billing-label {
                flex: 0 0 auto !important;
                white-space: nowrap !important;
                margin-right: 0 !important;
                align-self: flex-start !important;
                line-height: 1.4 !important;
                display: inline-block !important;
            }

            .billing-row-multiline .billing-val {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                line-height: 1.4 !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
                display: inline-block !important;
            }

            .billing-row-multiline .billing-val.billing-val-url {
                word-break: break-all !important;
                overflow-wrap: anywhere !important;
            }

            .po-pay-now-btn {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 11px 16px !important;
                font-size: 15px !important;
                font-weight: 700 !important;
                text-align: center !important;
                background-color: #28a745 !important;
                border: 1px solid #28a745 !important;
                color: #ffffff !important;
                text-decoration: none !important;
                border-radius: 4px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                line-height: 1.4 !important;
                cursor: pointer !important;
                transition: background-color 0.2s ease, border-color 0.2s ease !important;
            }

            .po-pay-now-btn:hover {
                background-color: #218838 !important;
                border-color: #1e7e34 !important;
                color: #ffffff !important;
                text-decoration: none !important;
            }

            @media  print {
                * {
                    orphans: 3;
                    widows: 3;
                }

                tr, td, th {
                    orphans: 3;
                    widows: 3;
                }
            }

            @media  print {
                @page  {
                    size: auto;
                    margin: 8mm 8mm 8mm 8mm;
                }

                html, body {
                    width: 100% !important;
                    height: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow: visible !important;
                }

                body.menubg {
                    background: #ffffff !important;
                }

                body.menubg > img {
                    margin-top: 0 !important;
                }

                body.review-preview,
                body.cart-preview {
                    background: #ffffff !important;
                    padding-bottom: 0 !important;
                }

                body.review-preview .preview-content-shell,
                body.cart-preview .preview-content-shell {
                    padding-bottom: 0 !important;
                }

                /* Header in thead: Logo only on all printed pages (no border, no date) */
                .preview-header {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: flex-start !important;
                    align-items: flex-start !important;
                    padding-top: 10px !important;
                    padding-bottom: 22px !important;
                    border-bottom: none !important;
                    margin-bottom: 0 !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                }

                .print-layout-header > tr > td {
                    padding-bottom: 0 !important;
                    border: none !important;
                }

                .preview-header .preview-logo-container {
                    flex: 0 0 auto !important;
                    max-width: 50% !important;
                }

                /* Hide date section from thead on all printed pages */
                thead .preview-date {
                    display: none !important;
                }

                /* Page 1 ONLY: Overlay Right-side Info and Header Underline in tbody */
                .preview-header-first-page-date {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: flex-start !important;
                    position: relative !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    margin-top: -98px !important;
                    min-height: 98px !important;
                    height: auto !important;
                    border-bottom: 1px solid #ddd !important;
                    margin-bottom: 24px !important;
                    padding-top: 0 !important;
                    padding-bottom: 20px !important;
                    clear: both !important;
                }

                .preview-header-first-page-date .preview-header-spacer {
                    display: block !important;
                    flex: 0 0 300px !important;
                    width: 300px !important;
                    min-width: 300px !important;
                    height: 66px !important;
                }

                .preview-header-first-page-date .preview-date {
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: flex-end !important;
                    text-align: right !important;
                    flex: 1 1 auto !important;
                    position: static !important;
                    width: auto !important;
                    height: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .preview-header-first-page-date .preview-meta {
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: flex-end !important;
                    text-align: right !important;
                    gap: 6px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .preview-header-first-page-date .preview-meta-row {
                    display: block !important;
                    text-align: right !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .preview-header-first-page-date .preview-meta-label {
                    display: inline !important;
                    font-size: 15px !important;
                    font-family: 'Futura Md BT', sans-serif !important;
                    font-weight: 700 !important;
                    letter-spacing: 0.5px !important;
                    margin-bottom: 0 !important;
                    text-align: right !important;
                    white-space: nowrap !important;
                    line-height: 1.2 !important;
                    color: #111 !important;
                }

                .preview-header-first-page-date .preview-meta-value {
                    display: inline !important;
                    font-size: 14px !important;
                    font-family: 'Futura Bk BT', sans-serif !important;
                    color: #222 !important;
                    text-align: right !important;
                    white-space: nowrap !important;
                    line-height: 1.3 !important;
                    margin-left: 2px !important;
                }

                body.review-preview .print-footer-spacer,
                body.cart-preview .print-footer-spacer {
                    display: block !important;
                    height: 0mm !important;
                    width: 100% !important;
                    clear: both !important;
                }

                body.review-preview .print-letterhead-footer,
                body.cart-preview .print-letterhead-footer {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    position: fixed !important;
                    left: 8mm !important;
                    right: 8mm !important;
                    bottom: 8mm !important;
                    z-index: 9999 !important;
                    margin: 0 !important;
                    padding: 0 0 0 0 !important;
                    min-height: 25mm !important;
                    height: auto !important;
                    max-height: 25mm !important;
                    overflow: visible !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    background: #ffffff !important;
                }

                body.review-preview .print-letterhead-footer .footer-line,
                body.cart-preview .print-letterhead-footer .footer-line {
                    border-top: 2px solid #b88d16 !important;
                    margin: 0 0 8px 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }

            body.review-preview .print-letterhead-footer .footer-content,
            body.cart-preview .print-letterhead-footer .footer-content {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: space-between !important;
                gap: 20px !important;
                width: 100% !important;
            }

                body.review-preview .print-letterhead-footer .footer-left,
                body.review-preview .print-letterhead-footer .footer-right,
                body.cart-preview .print-letterhead-footer .footer-left,
                body.cart-preview .print-letterhead-footer .footer-right {
                    font-family: 'Futura Bk BT', Arial, sans-serif !important;
                    color: #222222 !important;
                    font-size: 14px !important;
                    line-height: 1.5 !important;
                    letter-spacing: 0.1px !important;
                    font-weight: 400 !important;
                }

            body.review-preview .print-letterhead-footer .footer-left,
            body.cart-preview .print-letterhead-footer .footer-left {
                flex: 0 0 58% !important;
            }

            body.review-preview .print-letterhead-footer .footer-right,
            body.cart-preview .print-letterhead-footer .footer-right {
                flex: 0 0 38% !important;
                text-align: right !important;
                direction: rtl !important;
            }

            body.review-preview .print-letterhead-footer .footer-left div,
            body.review-preview .print-letterhead-footer .footer-right div,
            body.cart-preview .print-letterhead-footer .footer-left div,
            body.cart-preview .print-letterhead-footer .footer-right div {
                margin-bottom: 2px !important;
            }

                .confirmation,
                .confirmation .container,
                .order,
                .prod-summary {
                    margin-bottom: 0 !important;
                    height: auto !important;
                    min-height: 0 !important;
                    overflow: visible !important;
                    margin-top: 30px !important;
                }

                .payment-info,
                .prod-summary {
                    margin-bottom: 16px !important;
                }

                .prod-summary {
                    margin-top: 30px !important;
                }

                .order-details,
                .billing-summary-grid {
                    break-before: auto !important;
                    page-break-before: auto !important;
                    display: grid !important;
                    grid-template-columns: minmax(0, 1fr) 32px minmax(0, 1fr) !important;
                    column-gap: 0 !important;
                    row-gap: 0 !important;
                    clear: both !important;
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                    margin-top: 24px !important;
                    margin-bottom: 24px !important;
                    width: 100% !important;
                    max-width: none !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    box-sizing: border-box !important;
                }

                .grid-heading-cell {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    margin: 0 !important;
                    padding: 0 0 14px 0 !important;
                    border: none !important;
                    display: flex !important;
                    align-items: flex-end !important;
                }

                .grid-heading-cell.grid-col-left {
                    grid-column: 1 !important;
                    padding-right: 0 !important;
                    border: none !important;
                }

                .grid-heading-cell.grid-col-divider {
                    grid-column: 2 !important;
                    display: flex !important;
                    justify-content: center !important;
                    align-items: stretch !important;
                    padding: 0 0 14px 0 !important;
                    border: none !important;
                }

                .grid-heading-cell.grid-col-right {
                    grid-column: 3 !important;
                    padding-left: 0 !important;
                    border: none !important;
                }

                .grid-heading-cell .section-title,
                .grid-heading-cell h4,
                .column-heading .section-title,
                .column-heading h4,
                .prod-summary > h4,
                .prod-summary h4,
                .prod-summary .section-title,
                .section-title {
                    margin: 0 !important;
                    padding: 0 !important;
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    line-height: 1.2 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #333 !important;
                    white-space: nowrap !important;
                    border: none !important;
                    box-sizing: border-box !important;
                }

                .prod-summary > h4,
                .prod-summary h4,
                .prod-summary .section-title {
                    margin-top: 0 !important;
                    margin-bottom: 12px !important;
                }

                .grid-heading-cell .section-title span,
                .grid-heading-cell h4 span {
                    font-size: 15px !important;
                    font-weight: 400 !important;
                    letter-spacing: normal !important;
                    color: #666 !important;
                    text-transform: uppercase !important;
                }

                .grid-row-cell {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    display: flex !important;
                    padding: 7px 0 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    margin: 0 !important;
                }

                .grid-row-cell.grid-col-left {
                    grid-column: 1 !important;
                    align-items: flex-start !important;
                    justify-content: flex-start !important;
                    color: #555 !important;
                    text-align: left !important;
                    padding-right: 0 !important;
                    border: none !important;
                }

                .grid-row-cell.grid-col-divider {
                    grid-column: 2 !important;
                    display: flex !important;
                    justify-content: center !important;
                    align-items: stretch !important;
                    padding: 0 !important;
                    border: none !important;
                }

                .grid-row-cell.grid-col-right {
                    grid-column: 3 !important;
                    align-items: baseline !important;
                    justify-content: space-between !important;
                    padding-left: 0 !important;
                    border: none !important;
                }

                .grid-row-cell.billing-row-cell {
                    border-bottom: 1px solid #eeeeee !important;
                }

                .grid-row-cell.summary-row-cell {
                    border-bottom: 1px solid #eeeeee !important;
                }

                .grid-row-cell.empty-cell {
                    border-bottom: none !important;
                    padding-top: 0 !important;
                    padding-bottom: 0 !important;
                    height: auto !important;
                    min-height: 0 !important;
                }

                .grid-row-cell.empty-cell.grid-col-left {
                    border: none !important;
                }

                .grid-row-cell.empty-cell.grid-col-right {
                    border: none !important;
                    visibility: hidden !important;
                }

                .grid-divider-line {
                    width: 1px !important;
                    height: 100% !important;
                    min-height: 100% !important;
                    background-color: #eeeeee !important;
                    margin: 0 auto !important;
                    display: block !important;
                }

                /* Billing cell contents */
                .grid-row-cell .billing-row-inline {
                    display: block !important;
                    width: 100% !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    color: #555 !important;
                    word-break: normal !important;
                    overflow-wrap: normal !important;
                    white-space: normal !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .grid-row-cell .billing-customer-name {
                    font-size: 15px !important;
                    font-weight: 600 !important;
                    color: #333 !important;
                    display: inline !important;
                    line-height: 1.4 !important;
                }

                .grid-row-cell .billing-field {
                    display: inline !important;
                    white-space: nowrap !important;
                }

                .grid-row-cell .billing-label {
                    font-weight: 700 !important;
                    color: #333 !important;
                    margin-right: 2px !important;
                    display: inline !important;
                    white-space: nowrap !important;
                    line-height: 1.4 !important;
                }

                .grid-row-cell .billing-val {
                    font-weight: 400 !important;
                    color: #555 !important;
                    display: inline !important;
                    line-height: 1.4 !important;
                }

                .grid-row-cell .billing-comma {
                    display: inline !important;
                    color: #555 !important;
                }

                .grid-row-cell .billing-row-multiline {
                    display: flex !important;
                    align-items: flex-start !important;
                    justify-content: flex-start !important;
                    gap: 6px !important;
                    width: 100% !important;
                    min-width: 0 !important;
                }

                .grid-row-cell .billing-row-multiline .billing-label {
                    flex: 0 0 auto !important;
                    white-space: nowrap !important;
                    margin-right: 0 !important;
                    align-self: flex-start !important;
                    line-height: 1.4 !important;
                    display: inline-block !important;
                }

                .grid-row-cell .billing-row-multiline .billing-val {
                    flex: 1 1 auto !important;
                    min-width: 0 !important;
                    align-self: flex-start !important;
                    line-height: 1.4 !important;
                    word-break: break-word !important;
                    overflow-wrap: anywhere !important;
                    display: inline-block !important;
                }

                .grid-row-cell .billing-row-multiline .billing-val.billing-val-url {
                    word-break: break-all !important;
                    overflow-wrap: anywhere !important;
                }

                /* Summary cell contents */
                .grid-row-cell .summary-label {
                    display: block !important;
                    white-space: nowrap !important;
                    flex: 0 0 auto !important;
                    color: #555 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    font-weight: 500 !important;
                    text-align: left !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .grid-row-cell .summary-value {
                    display: block !important;
                    white-space: nowrap !important;
                    text-align: right !important;
                    flex: 0 0 auto !important;
                    width: auto !important;
                    margin: 0 0 0 auto !important;
                    padding: 0 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    color: #333 !important;
                    font-weight: 600 !important;
                }

                .grid-row-cell .summary-value .price-amount {
                    display: inline !important;
                    line-height: 1.4 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .grid-row-cell.grandtotal .summary-label,
                .grid-row-cell.grandtotal .summary-value {
                    font-weight: 700 !important;
                    color: #111 !important;
                }

                .preview-header .preview-meta-label,
                .preview-header-first-page-date .preview-meta-label,
                .preview-meta-label {
                    display: inline !important;
                    font-size: 16px !important;
                    font-family: 'Futura Md BT', sans-serif !important;
                    font-weight: 600 !important;
                    margin-bottom: 0 !important;
                    text-align: right !important;
                }

                .prod-summary > h4,
                .prod-summary h4,
                .order-details > .w-33 > h4,
                .order-details .billing-summary > h4,
                .order-details .billing-summary h4,
                .order-details .summary-details > h4,
                .order-details .summary-details h4 {
                    margin: 0 0 14px 0 !important;
                    padding: 0 !important;
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    line-height: 1.2 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #333 !important;
                    white-space: nowrap !important;
                    flex: 0 0 auto !important;
                    border: none !important;
                    box-sizing: border-box !important;
                }

                .order-details .summary-details > h4 span,
                .order-details .summary-details h4 span {
                    font-size: 15px !important;
                    font-weight: 400 !important;
                    letter-spacing: normal !important;
                    color: #666 !important;
                }

                .order-details .billing-summary,
                .order-details .summary-details {
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: flex-start !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    box-sizing: border-box !important;
                }

                .order-details .order-summary,
                .order-details .billing-summary .order-summary {
                    display: flex !important;
                    flex-direction: column !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    box-shadow: none !important;
                    box-sizing: border-box !important;
                }

                .order-details .cart-details,
                .order-details .order-summary .cart-details {
                    display: flex !important;
                    flex-direction: column !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    box-sizing: border-box !important;
                }

                .order-details .cart-details .v-flex,
                .order-details .order-summary .cart-details .v-flex {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }

            .order-details .billing-summary .cart-details .total {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                min-height: 0 !important;
                height: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                border: none !important;
                border-bottom: 1px solid #eeeeee !important;
                margin: 0 !important;
                padding: 7px 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
            }

            .order-details .billing-summary .cart-details .total .item {
                display: block !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                color: #555 !important;
                text-align: left !important;
                flex: 1 1 auto !important;
                min-width: 0 !important;
                word-break: normal !important;
                overflow-wrap: normal !important;
                white-space: normal !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .order-details .billing-summary .cart-details .total .item .billing-customer-name {
                font-size: 15px !important;
                font-weight: 600 !important;
                color: #333 !important;
                display: inline !important;
                line-height: 1.4 !important;
            }

            .order-details .billing-summary .cart-details .total .item .billing-inline-group {
                display: inline !important;
                white-space: nowrap !important;
            }

            .order-details .billing-summary .cart-details .total .item .billing-label {
                font-weight: 700 !important;
                color: #333 !important;
                margin-right: 2px !important;
                display: inline !important;
                white-space: nowrap !important;
                line-height: 1.4 !important;
            }

            .order-details .billing-summary .cart-details .total .item .billing-val {
                font-weight: 400 !important;
                color: #555 !important;
                display: inline !important;
                line-height: 1.4 !important;
            }

            .order-details .billing-summary .cart-details .total .item .billing-separator {
                display: inline !important;
                color: #555 !important;
            }

            .order-details .billing-summary .cart-details .total .item.billing-row-multiline {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                width: 100% !important;
                min-width: 0 !important;
                gap: 6px !important;
            }

            .order-details .billing-summary .cart-details .total .item.billing-row-multiline .billing-label {
                flex: 0 0 auto !important;
                white-space: nowrap !important;
                margin-right: 0 !important;
                align-self: flex-start !important;
                line-height: 1.4 !important;
                display: inline-block !important;
            }

            .order-details .billing-summary .cart-details .total .item.billing-row-multiline .billing-val {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                align-self: flex-start !important;
                line-height: 1.4 !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
                display: inline-block !important;
            }

            .order-details .billing-summary .cart-details .total .item.billing-row-multiline .billing-val.billing-val-url {
                word-break: break-all !important;
                overflow-wrap: anywhere !important;
            }

            .order-details .summary-details .cart-details .total {
                display: flex !important;
                align-items: baseline !important;
                justify-content: space-between !important;
                min-height: 0 !important;
                height: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                border: none !important;
                border-bottom: 1px solid #eeeeee !important;
                margin: 0 !important;
                padding: 7px 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
            }

            .order-details .summary-details .cart-details .total .item {
                display: block !important;
                white-space: nowrap !important;
                flex: 0 0 auto !important;
                color: #555 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                font-weight: 500 !important;
                text-align: left !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: 0 !important;
                height: auto !important;
            }

            .order-details .summary-details .cart-details .total .item-price {
                display: block !important;
                white-space: nowrap !important;
                text-align: right !important;
                flex: 0 0 auto !important;
                width: auto !important;
                margin: 0 0 0 auto !important;
                padding: 0 !important;
                font-size: 15px !important;
                line-height: 1.4 !important;
                color: #333 !important;
                font-weight: 600 !important;
                min-height: 0 !important;
                height: auto !important;
            }

            .order-details .summary-details .cart-details .total .item-price .price-amount {
                display: inline !important;
                line-height: 1.4 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .order-details .summary-details .cart-details .total.grandtotal .item,
            .order-details .summary-details .cart-details .total.grandtotal .item-price {
                font-weight: 700 !important;
                color: #111 !important;
            }

                .preview-content-shell {
                    padding-bottom: 0 !important;
                    margin-bottom: 0 !important;
                }

                .bank-transfer-box {
                    page-break-before: auto !important;
                    break-before: auto !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                    margin-top: 0 !important;
                    margin-bottom: 20px !important;
                    overflow: visible !important;
                }

                .bank-transfer-box * {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .bank-transfer-box table,
                .bank-transfer-box tr,
                .bank-transfer-box td {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .bank-transfer-box .bank-info-row {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .bank-transfer-box .bank-note {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    color: #222 !important;
                    font-size: 16px !important;
                    line-height: 1.4 !important;
                    margin-top: 8px !important;
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    white-space: normal !important;
                }

                .bank-details-wrapper {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                    min-height: 0 !important;
                    display: block !important;
                    overflow: visible !important;
                }

                .bank-transfer-box table tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    page-break-after: auto !important;
                    page-break-before: auto !important;
                }

                .print-footer-spacer {
                    page-break-before: auto !important;
                }

                .page-actions {
                    display:none !important;
                }

                thead {
                    display: table-header-group !important;
                }

                thead tr th {
                    border-top: none !important;
                }

                tbody tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .cart-list-table {
                    page-break-inside: auto !important;
                    border-collapse: collapse !important;
                }

                .cart-list-table tbody tr {
                    page-break-inside: avoid !important;
                    page-break-after: auto !important;
                    page-break-before: auto !important;
                    break-inside: avoid-page !important;
                    break-after: auto !important;
                    break-before: auto !important;
                    min-height: 80px !important;
                    height: auto !important;
                }

                .cart-list-table tbody tr td {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                    vertical-align: top !important;
                }

                .cart-list-table tbody tr td > div {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .cart-list-table tbody tr td > * {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .cart-list-table tbody tr:last-child td {
                    border-bottom: none !important;
                }

                .prod-name,
                .contact-address address,
                .contact-address address > div {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .cart-table-container {
                    overflow: visible !important;
                }

                .prod-summary {
                    overflow: visible !important;
                }

                .item-description-row {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                    page-break-before: auto !important;
                    break-before: auto !important;
                }

                .item-description-row td {
                    padding-top: 4px !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .item-product-row td {
                    padding-top: 8px !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .item-product-row {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .item-description {
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
                }

                .item-description-cell {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .item-description-text {
                    display: block !important;
                    width: 100% !important;
                    margin: 0 !important;
                    text-align: justify !important;
                    text-justify: inter-word !important;
                }

                .item-code-th,
                .total-price-th {
                    white-space: nowrap !important;
                }

                .qty-cell {
                    text-align: center !important;
                }

                .cart-list-table tbody tr.item-description-row:last-child td {
                    border-bottom: 1px solid #d7d7d7 !important;
                }

                /* New layout for reserving footer space */
                .print-layout-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                }

                .print-layout-body {
                    display: table-row-group !important;
                }

                .print-layout-header {
                    display: table-header-group !important;
                }

                .print-layout-footer {
                    display: table-footer-group !important;
                }

                .print-footer-placeholder {
                    height: 35mm !important; /* Fully accommodates the 40mm footer + 10mm bottom offset + 5mm margin */
                    width: 100% !important;
                    visibility: hidden !important;
                }

                /* Ensure the content shell doesn't have extra page breaks */
                .preview-content-shell {
                    page-break-inside: auto !important;
                }
            }

            /* Screen styles for the layout table */
            @media  screen {
                .print-layout-table {
                    width: 100% !important;
                    max-width: 900px !important;
                    margin: 20px auto !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                    background: #fff !important;
                }
                .print-layout-footer {
                    display: none !important;
                }
                .print-layout-header {
                    display: table-header-group !important;
                }
                .print-layout-body {
                    display: table-row-group !important;
                }

                .preview-header {
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 16px;
                    margin-bottom: 18px;
                }

                .preview-header .preview-meta-label,
                .preview-header-first-page-date .preview-meta-label,
                .preview-header .preview-meta-value,
                .preview-header-first-page-date .preview-meta-value {
                    display: inline;
                    text-align: right;
                }

                .preview-header .preview-meta-label,
                .preview-header-first-page-date .preview-meta-label,
                .preview-meta-label {
                    display: inline !important;
                    font-size: 15px !important;
                    font-family: 'Futura Md BT', sans-serif !important;
                    font-weight: 700 !important;
                    letter-spacing: 0.5px !important;
                    margin-bottom: 0 !important;
                    text-align: right !important;
                }

                .prod-summary > h4,
                .prod-summary h4,
                .prod-summary .section-title,
                .column-heading .section-title,
                .column-heading h4,
                .section-title {
                    margin-top: 0 !important;
                    margin-bottom: 12px !important;
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    line-height: 1.2 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #333 !important;
                    white-space: nowrap !important;
                    border: none !important;
                    box-sizing: border-box !important;
                }

                .column-heading .section-title,
                .column-heading h4 {
                    margin: 0 !important;
                }

                .order-details,
                .billing-summary-grid,
                .billing-summary-section {
                    display: grid !important;
                    grid-template-columns: minmax(0, 1fr) 32px minmax(0, 1fr) !important;
                    column-gap: 0 !important;
                    row-gap: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin-top: 24px !important;
                    margin-bottom: 24px !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    box-sizing: border-box !important;
                    clear: both !important;
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                    align-items: stretch !important;
                }

                .billing-column,
                .summary-column {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: flex-start !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                }

                .billing-column {
                    grid-column: 1 !important;
                }

                .divider-column {
                    grid-column: 2 !important;
                    width: 32px !important;
                    align-self: stretch !important;
                    display: flex !important;
                    justify-content: center !important;
                    align-items: stretch !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    box-sizing: border-box !important;
                }

                .divider-column .grid-divider-line {
                    width: 1px !important;
                    height: 100% !important;
                    min-height: 100% !important;
                    background-color: #eeeeee !important;
                    margin: 0 auto !important;
                    display: block !important;
                }

                .summary-column {
                    grid-column: 3 !important;
                }

                .column-heading {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    margin: 0 !important;
                    padding: 0 0 14px 0 !important;
                    border: none !important;
                    display: flex !important;
                    align-items: flex-end !important;
                }

                .column-heading .section-title,
                .column-heading h4 {
                    margin: 0 !important;
                    padding: 0 !important;
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    line-height: 1.2 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #333 !important;
                    white-space: nowrap !important;
                    border: none !important;
                    box-sizing: border-box !important;
                }

                .column-heading .section-title span,
                .column-heading h4 span {
                    font-size: 15px !important;
                    font-weight: 400 !important;
                    letter-spacing: normal !important;
                    color: #666 !important;
                    text-transform: uppercase !important;
                }

                .column-body {
                    display: flex !important;
                    flex-direction: column !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }

                .billing-row-item {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    display: flex !important;
                    align-items: flex-start !important;
                    justify-content: flex-start !important;
                    padding: 7px 0 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    margin: 0 !important;
                    border: none !important;
                    border-bottom: 1px solid #eeeeee !important;
                    color: #555 !important;
                    text-align: left !important;
                }

                .summary-row-item {
                    min-width: 0 !important;
                    box-sizing: border-box !important;
                    display: flex !important;
                    align-items: baseline !important;
                    justify-content: space-between !important;
                    padding: 7px 0 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    min-height: 0 !important;
                    width: 100% !important;
                    margin: 0 !important;
                    border: none !important;
                    border-bottom: 1px solid #eeeeee !important;
                }

                .summary-row-item .summary-label {
                    display: block !important;
                    white-space: nowrap !important;
                    flex: 0 0 auto !important;
                    color: #555 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    font-weight: 500 !important;
                    text-align: left !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .summary-row-item .summary-value {
                    display: block !important;
                    white-space: nowrap !important;
                    text-align: right !important;
                    flex: 0 0 auto !important;
                    width: auto !important;
                    margin: 0 0 0 auto !important;
                    padding: 0 !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    color: #333 !important;
                    font-weight: 600 !important;
                }

                .summary-row-item .summary-value .price-amount {
                    display: inline !important;
                    line-height: 1.4 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .summary-row-item.grandtotal .summary-label,
                .summary-row-item.grandtotal .summary-value {
                    font-weight: 700 !important;
                    color: #111 !important;
                }

                .pay-now-row {
                    border-bottom: none !important;
                    padding-top: 14px !important;
                    padding-bottom: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    width: 100% !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: stretch !important;
                    box-sizing: border-box !important;
                }

                /* Billing row internal elements */
                .billing-row-inline {
                    display: block !important;
                    width: 100% !important;
                    font-size: 15px !important;
                    line-height: 1.4 !important;
                    color: #555 !important;
                    word-break: normal !important;
                    overflow-wrap: normal !important;
                    white-space: normal !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .billing-customer-name {
                    font-size: 15px !important;
                    font-weight: 600 !important;
                    color: #333 !important;
                    display: inline !important;
                    line-height: 1.4 !important;
                }

                .billing-field {
                    display: inline !important;
                    white-space: nowrap !important;
                }

                .billing-label {
                    font-weight: 700 !important;
                    color: #333 !important;
                    margin-right: 2px !important;
                    display: inline !important;
                    white-space: nowrap !important;
                    line-height: 1.4 !important;
                }

                .billing-val {
                    font-weight: 400 !important;
                    color: #555 !important;
                    display: inline !important;
                    line-height: 1.4 !important;
                }

                .billing-comma {
                    display: inline !important;
                    color: #555 !important;
                }

                .billing-row-multiline {
                    display: flex !important;
                    align-items: flex-start !important;
                    justify-content: flex-start !important;
                    gap: 6px !important;
                    width: 100% !important;
                    min-width: 0 !important;
                    line-height: 1.4 !important;
                }

                .billing-row-multiline .billing-label {
                    flex: 0 0 auto !important;
                    white-space: nowrap !important;
                    margin-right: 0 !important;
                    align-self: flex-start !important;
                    line-height: 1.4 !important;
                    display: inline-block !important;
                }

                .billing-row-multiline .billing-val {
                    flex: 1 1 auto !important;
                    min-width: 0 !important;
                    line-height: 1.4 !important;
                    word-break: break-word !important;
                    overflow-wrap: anywhere !important;
                    display: inline-block !important;
                }

                .billing-row-multiline .billing-val.billing-val-url {
                    word-break: break-all !important;
                    overflow-wrap: anywhere !important;
                }

                .bank-transfer-box {
                    margin-top: 0 !important;
                    margin-bottom: 20px !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                }
            }

            /* Responsive styles for Mobile Viewports */
            @media  screen and (max-width: 768px) {
                body.custom-print-page,
                body.cart-preview,
                body.review-preview {
                    width: 100% !important;
                    max-width: 100vw !important;
                    padding: 12px 10px !important;
                    margin: 0 !important;
                    box-sizing: border-box !important;
                }

                .print-layout-table {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 auto !important;
                    display: block !important;
                }

                .print-layout-header,
                .print-layout-body,
                .print-layout-table > thead > tr,
                .print-layout-table > tbody > tr,
                .print-layout-table > thead > tr > td,
                .print-layout-table > tbody > tr > td {
                    display: block !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }

                .preview-header-first-page-date {
                    display: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    height: 0 !important;
                    min-height: 0 !important;
                }

                .container,
                .confirmation .container,
                .preview-content-shell,
                .order,
                .confirmation {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    padding-top: 0 !important;
                    margin-top: 0 !important;
                    margin-bottom: 0 !important;
                    box-sizing: border-box !important;
                }

                .payment-info {
                    display: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    height: 0 !important;
                }

                .prod-summary {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin-top: 0 !important;
                    margin-bottom: 16px !important;
                    box-sizing: border-box !important;
                }

                .preview-header {
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    gap: 14px !important;
                    padding: 10px 0 16px 0 !important;
                    margin-bottom: 16px !important;
                    border-bottom: 1px solid #ddd !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                }

                .preview-header .preview-logo-container,
                .preview-logo-container {
                    width: 100% !important;
                    max-width: 100% !important;
                    flex: 0 0 auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .preview-logo {
                    max-width: 220px !important;
                    width: 100% !important;
                    height: auto !important;
                    object-fit: contain !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    display: block !important;
                }

                .preview-header .preview-date {
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    text-align: left !important;
                    display: block !important;
                }

                .preview-header .preview-meta {
                    width: 100% !important;
                    align-items: flex-start !important;
                    text-align: left !important;
                    gap: 5px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .preview-header .preview-meta-row {
                    width: 100% !important;
                    margin: 0 !important;
                    text-align: left !important;
                    display: flex !important;
                    flex-wrap: wrap !important;
                    align-items: baseline !important;
                    justify-content: flex-start !important;
                }

                .preview-header .preview-meta-label,
                .preview-header .preview-meta-value {
                    text-align: left !important;
                    display: inline !important;
                }

                .preview-header .preview-meta-label {
                    font-size: 14px !important;
                    font-weight: 700 !important;
                    color: #111 !important;
                    margin-right: 4px !important;
                    margin-bottom: 0 !important;
                }

                .preview-header .preview-meta-value {
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    color: #222 !important;
                    margin-left: 0 !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .prod-summary > h4,
                .prod-summary h4,
                .prod-summary .section-title,
                .column-heading .section-title,
                .column-heading h4,
                .section-title {
                    font-size: 18px !important;
                    font-weight: 600 !important;
                    line-height: 1.2 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #333 !important;
                    margin-top: 0 !important;
                    margin-bottom: 12px !important;
                    padding-top: 0 !important;
                    border: none !important;
                }

                .cart-table-container,
                .customized-items-table-wrapper {
                    width: 100% !important;
                    max-width: 100% !important;
                    overflow: visible !important;
                    display: block !important;
                    margin-bottom: 20px !important;
                    border: none !important;
                    background: transparent !important;
                    box-sizing: border-box !important;
                }

                .desktop-customized-items {
                    display: none !important;
                }

                .mobile-customized-items {
                    display: table !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    border-collapse: collapse !important;
                    margin-bottom: 20px !important;
                    border: none !important;
                    background: transparent !important;
                }

                .customized-mobile-table tbody {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    border: 1px solid #e2e8f0 !important;
                    border-radius: 8px !important;
                    padding: 12px 14px !important;
                    margin-bottom: 16px !important;
                    background: #ffffff !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
                }

                .customized-mobile-table tbody:last-child {
                    margin-bottom: 0 !important;
                }

                .customized-mobile-table tr {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    padding: 8px 0 !important;
                    border-bottom: 1px solid #f1f5f9 !important;
                }

                .customized-mobile-table tr:last-child {
                    border-bottom: none !important;
                    padding-bottom: 0 !important;
                }

                .customized-mobile-table th.mobile-th {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    font-size: 11px !important;
                    font-weight: 700 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #64748b !important;
                    margin-bottom: 4px !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    text-align: left !important;
                    line-height: 1.2 !important;
                }

                .customized-mobile-table td.mobile-td {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    font-size: 14px !important;
                    color: #0f172a !important;
                    line-height: 1.4 !important;
                    min-width: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    background: transparent !important;
                    text-align: left !important;
                    white-space: normal !important;
                    word-break: normal !important;
                    overflow-wrap: anywhere !important;
                }

                .product-mobile-image {
                    width: 60px !important;
                    height: 60px !important;
                    object-fit: cover !important;
                    border: 1px solid #e2e8f0 !important;
                    border-radius: 6px !important;
                    padding: 2px !important;
                    background: #fff !important;
                    display: block !important;
                }

                .order-details,
                .billing-summary-grid,
                .billing-summary-section {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 24px !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    margin-top: 20px !important;
                    margin-bottom: 20px !important;
                    box-sizing: border-box !important;
                }

                .billing-column,
                .summary-column {
                    width: 100% !important;
                    max-width: 100% !important;
                    grid-column: auto !important;
                    display: flex !important;
                    flex-direction: column !important;
                }

                .divider-column {
                    display: none !important;
                }

                .column-heading {
                    padding-bottom: 8px !important;
                    margin-bottom: 0 !important;
                    border: none !important;
                }

                .column-heading .section-title,
                .column-heading h4 {
                    font-size: 18px !important;
                    white-space: normal !important;
                    word-break: normal !important;
                }

                .billing-row-item {
                    width: 100% !important;
                    box-sizing: border-box !important;
                    padding: 8px 0 !important;
                    font-size: 14px !important;
                }

                .billing-row-inline {
                    font-size: 14px !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .billing-customer-name {
                    font-size: 15px !important;
                    font-weight: 600 !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .billing-field {
                    display: inline !important;
                    white-space: normal !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .billing-label {
                    font-size: 14px !important;
                    font-weight: 700 !important;
                }

                .billing-val {
                    font-size: 14px !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .billing-row-multiline {
                    display: flex !important;
                    align-items: flex-start !important;
                    justify-content: flex-start !important;
                    gap: 6px !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                }

                .billing-row-multiline .billing-label {
                    flex: 0 0 auto !important;
                    white-space: nowrap !important;
                    align-self: flex-start !important;
                    font-size: 14px !important;
                }

                .billing-row-multiline .billing-val {
                    flex: 1 1 auto !important;
                    min-width: 0 !important;
                    font-size: 14px !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                }

                .billing-row-multiline .billing-val.billing-val-url {
                    word-break: break-all !important;
                    overflow-wrap: anywhere !important;
                }

                .summary-row-item {
                    width: 100% !important;
                    box-sizing: border-box !important;
                    padding: 8px 0 !important;
                    font-size: 14px !important;
                    display: flex !important;
                    align-items: baseline !important;
                    justify-content: space-between !important;
                }

                .summary-row-item .summary-label {
                    flex: 1 1 auto !important;
                    font-size: 14px !important;
                    white-space: normal !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                    padding-right: 8px !important;
                }

                .summary-row-item .summary-value {
                    flex: 0 0 auto !important;
                    font-size: 14px !important;
                    white-space: nowrap !important;
                    text-align: right !important;
                    margin-left: auto !important;
                }

                .pay-now-row {
                    width: 100% !important;
                    padding-top: 14px !important;
                }

                .po-pay-now-btn {
                    width: 100% !important;
                    max-width: 100% !important;
                    font-size: 15px !important;
                    padding: 12px 16px !important;
                }

                .bank-transfer-box {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    padding: 14px 12px !important;
                    font-size: 14px !important;
                    margin-top: 18px !important;
                    margin-bottom: 18px !important;
                }

                .bank-transfer-box .bank-transfer-title {
                    font-size: 14px !important;
                    margin-bottom: 10px !important;
                    font-weight: 700 !important;
                    line-height: 1.3 !important;
                }

                .bank-transfer-box table,
                .bank-transfer-box tbody,
                .bank-transfer-box tr,
                .bank-transfer-box td {
                    display: block !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    border: none !important;
                    padding: 0 !important;
                }

                .bank-transfer-box tr {
                    margin-bottom: 8px !important;
                    display: block !important;
                }

                .bank-transfer-box tr:last-child {
                    margin-bottom: 0 !important;
                }

                .bank-transfer-box td.bank-label,
                .bank-transfer-box .bank-label {
                    display: block !important;
                    width: 100% !important;
                    font-size: 13px !important;
                    font-weight: 700 !important;
                    color: #666 !important;
                    margin-bottom: 2px !important;
                    text-align: left !important;
                    line-height: 1.3 !important;
                }

                .bank-transfer-box td.bank-value,
                .bank-transfer-box .bank-value {
                    display: block !important;
                    width: 100% !important;
                    font-size: 14px !important;
                    font-weight: 600 !important;
                    color: #222 !important;
                    word-break: break-word !important;
                    overflow-wrap: anywhere !important;
                    text-align: left !important;
                    line-height: 1.4 !important;
                }

                .bank-transfer-box .bank-note {
                    font-size: 12px !important;
                    margin-top: 10px !important;
                    word-break: normal !important;
                    overflow-wrap: break-word !important;
                    line-height: 1.4 !important;
                }

                .page-actions {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 10px !important;
                    width: 100% !important;
                    margin-top: 20px !important;
                }

                .page-actions .btn {
                    width: 100% !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    text-align: center !important;
                    display: block !important;
                }
            }
        </style>
    </head>

    <?php
        $language = Helper::getLanguage();
        $customizedOrder = !empty($customizedOrder) ? $customizedOrder : session('customized_order_details', []);
        $items = !empty($previewItems) ? $previewItems : ($customizedOrder['items'] ?? []);
        $isPurchaseOrder = ($customizedOrder['type'] ?? 'purchase_order') === 'purchase_order';
        $orderLabel = $isPurchaseOrder ? __('web.po_no') : __('web.qo_no');
        $summaryLabel = $isPurchaseOrder ? __('web.po_summary') : __('web.qo_summary');
        $tempDocPrefix = $isPurchaseOrder ? 'T-PO' : 'T-QO';
        $temporaryOrderId = $temporaryOrderId ?? ($customizedOrder['temporary_order_id'] ?? null);
        if (empty($temporaryOrderId)) {
            foreach ($items as $it) {
                if (!empty($it['temporary_order_id'])) {
                    $temporaryOrderId = $it['temporary_order_id'];
                    break;
                }
            }
        }
        if (empty($temporaryOrderId) && session()->has('customized_items')) {
            foreach (session('customized_items', []) as $sIt) {
                if (!empty($sIt['temporary_order_id'])) {
                    $temporaryOrderId = $sIt['temporary_order_id'];
                    break;
                }
            }
        }
        if (empty($temporaryOrderId) && request()->filled('temporary_order_id')) {
            $temporaryOrderId = request()->get('temporary_order_id');
        }
        if (!empty($temporaryOrderId)) {
            $tempRecord = \App\UserModel\TemporaryOrder::find($temporaryOrderId);
            if ($tempRecord) {
                $temporaryOrderId = $tempRecord->id;
                if (empty($customizedOrder['type']) && !empty($tempRecord->type)) {
                    $isPurchaseOrder = ($tempRecord->type === 'purchase_order');
                    $tempDocPrefix = $isPurchaseOrder ? 'T-PO' : 'T-QO';
                }
            }
        }
        $currency = Helper::getDefaultCurrency();
        $currencyCode = $currency ? $currency->code : config('custom.currency');
        $customer = $customizedOrder['customer'] ?? [];
        $shipping = (float) ($customizedOrder['shipping'] ?? 0);
        $discountType = $customizedOrder['discount_type'] ?? '';
        $discountVal = (float) ($customizedOrder['discount_val'] ?? 0);
        $advance = (float) ($customizedOrder['payment']['advance'] ?? 0);
        $subTotal = 0;
        foreach ($items as $it) {
            $subTotal += (float) ($it['total'] ?? 0);
        }
        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = ($subTotal * $discountVal) / 100;
        } elseif ($discountType === 'flat') {
            $discountAmount = $discountVal;
        }
        $discountAmount = min($discountAmount, $subTotal + $shipping);
        $grandTotal = max(0, $subTotal + $shipping - $discountAmount);
        $pendingAmount = $isPurchaseOrder ? max(0, $grandTotal - $advance) : $grandTotal;
        $customerCountry = '';
        if (!empty($customer['country_id'])) {
            $cn = App\AdminModel\AdminCountryDescription::where('country_id', $customer['country_id'])->first();
            $customerCountry = $cn ? $cn->country_name : '';
        }
        $previewImage = function ($path) {
            if (empty($path)) return asset(Storage::url('default.png'));
            return asset(Storage::url($path));
        };
        $documentDate = now()->format('d M, Y');
        $documentDateTime = now()->format('d M Y h:i A');
        $itemsCount = count($previewItems ?? $items);
        $previewSource = $previewSource ?? request('source', session('customized_preview_source', 'cart'));
        $previewPhone = session('cphone');
        if (empty($previewPhone) && !empty($customer['phone'])) {
            $previewPhone = $customer['phone'];
        }
        $backUrl = $previewSource === 'review'
            ? route('web.checkout.cart_review', array_filter(['cphone' => $previewPhone]))
            : route('web.cart.index');
        $backLabel = $previewSource === 'review' ? __('web.back_to_review') : __('web.back_to_cart');
    ?>

    <body class="menubg <?php echo e($previewSource === 'review' ? 'review-preview' : 'cart-preview'); ?>">
        <table class="print-layout-table">
            <thead class="print-layout-header">
                <tr>
                    <td style="padding: 0; border: none;">
                        <div class="container preview-header">
                            <div class="preview-logo-container">
                                <img class="preview-logo" style="display:block;" src="<?php echo e(asset('front/img/Rehana-Logo-HD-Left.png')); ?>">
                            </div>
                            <div class="preview-date">
                                <div class="preview-meta">
                                    <?php if(!empty($temporaryOrderId)): ?>
                                    <div class="preview-meta-row">
                                        <span class="preview-meta-label"><?php echo e($tempDocPrefix); ?></span>
                                        <span class="preview-meta-value">#<?php echo e($temporaryOrderId); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                        $previewMetaRows = [
                                            __('web.date') => $documentDate,
                                        ];
                                    ?>
                                    <?php $__currentLoopData = $previewMetaRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!empty($value)): ?>
                                        <div class="preview-meta-row">
                                            <span class="preview-meta-label"><?php echo e($label); ?>:</span>
                                            <span class="preview-meta-value"><?php echo e($value); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </thead>
            <tbody class="print-layout-body">
                <tr>
                    <td style="padding: 0; border: none;">
                        <!-- Date section that shows only on first page -->
                        <div class="container preview-header-first-page-date">
                            <div class="preview-header-spacer"></div>
                            <div class="preview-date">
                                <div class="preview-meta">
                                    <?php if(!empty($temporaryOrderId)): ?>
                                    <div class="preview-meta-row">
                                        <span class="preview-meta-label"><?php echo e($tempDocPrefix); ?></span>
                                        <span class="preview-meta-value">#<?php echo e($temporaryOrderId); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                        $previewMetaRows = [
                                            __('web.date') => $documentDate,
                                        ];
                                    ?>
                                    <?php $__currentLoopData = $previewMetaRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!empty($value)): ?>
                                        <div class="preview-meta-row">
                                            <span class="preview-meta-label"><?php echo e($label); ?>:</span>
                                            <span class="preview-meta-value"><?php echo e($value); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                        <div class="preview-content-shell">
                            <section class="order">
                                <div class="confirmation">
                                    <div class="container">
                                        <div class="payment-info"></div>

                                        <div class="prod-summary">
                                            <h4 class="section-title text-uppercase"><?php echo e(__('web.customized_items')); ?></h4>
                                            <div class="cart-table-container">
                                                <?php
                                                $showColor = false;
                                                $showSize  = false;
                                                $showItemCode = false;
                                                foreach ($items as $pi) {
                                                    if (!empty($pi['color'])) $showColor = true;
                                                    if (!empty($pi['size']))  $showSize  = true;
                                                    if (!empty($pi['item_code'])) $showItemCode = true;
                                                }
                                                $totalCols   = 5 + ($showColor ? 1 : 0) + ($showSize ? 1 : 0) + ($showItemCode ? 1 : 0);
                                                $imageColPct = 10;
                                                $itemColPct = 20;
                                                $rightColCount = 3 + ($showItemCode ? 1 : 0) + ($showColor ? 1 : 0) + ($showSize ? 1 : 0);
                                                $rightColPct = round((100 - $imageColPct - $itemColPct) / $rightColCount, 2);
                                                $visibleItems = collect($items)->values()->all();
                                                usort($visibleItems, function ($a, $b) {
                                                    $aHasData = !empty($a['product_title'])
                                                        || !empty($a['item_code'])
                                                        || !empty($a['description'])
                                                        || !empty($a['image'])
                                                        || !empty($a['color'])
                                                        || !empty($a['size'])
                                                        || !empty($a['quantity'])
                                                        || !empty($a['price'])
                                                        || !empty($a['total']);

                                                    $bHasData = !empty($b['product_title'])
                                                        || !empty($b['item_code'])
                                                        || !empty($b['description'])
                                                        || !empty($b['image'])
                                                        || !empty($b['color'])
                                                        || !empty($b['size'])
                                                        || !empty($b['quantity'])
                                                        || !empty($b['price'])
                                                        || !empty($b['total']);

                                                    if ($aHasData === $bHasData) {
                                                        return 0;
                                                    }

                                                    return $aHasData ? 1 : -1;
                                                });
                                                ?>
                                                
                                                <table class="cart-list-table desktop-customized-items" style="table-layout:fixed; width:100%;">
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
                                                        <tr class="summary-table-head">
                                                            <th class="text-center"><?php echo e(__('web.image')); ?></th>
                                                            <th>Item &amp; Description</th>
                                                            <?php if($showItemCode): ?><th class="item-code-th"><?php echo e(__('web.item_code')); ?></th><?php endif; ?>
                                                            <?php if($showColor): ?><th class="text-center"><?php echo e(__('web.color')); ?></th><?php endif; ?>
                                                            <?php if($showSize): ?><th class="text-center"><?php echo e(__('web.size')); ?></th><?php endif; ?>
                                                            <th class="text-center"><?php echo e(__('web.price')); ?></th>
                                                            <th class="text-center"><?php echo e(__('web.qty')); ?></th>
                                                            <th class="total-price-th text-center"><?php echo e(__('web.total_price')); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php $__empty_1 = true; $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr class="item-product-row">
                                                        <td class="text-center">
                                                            <a href="<?php echo e($previewImage($item['image'] ?? null)); ?>" target="_blank" style="display:inline-flex;justify-content:center;align-items:center;">
                                                                <img src="<?php echo e($previewImage($item['image'] ?? null)); ?>" alt="<?php echo e(__('web.image')); ?>" style="width:56px;height:56px;object-fit:cover;border:1px solid #ddd;border-radius:6px;padding:2px;" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="prod-name">
                                                                <?php echo e(Helper::formatCustomizedTitle(!empty($item['product_title']) ? $item['product_title'] : (!empty($item['item_code']) ? $item['item_code'] : __('web.custom_item')))); ?>

                                                            </div>
                                                            <?php if(!empty($item['description'])): ?>
                                                                <div class="item-description"><?php echo e(Helper::formatCustomizedDescription($item['description'])); ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <?php if($showItemCode): ?>
                                                        <td class="barcode-cell">
                                                            <?php if(!empty($item['item_code'])): ?>
                                                                <?php echo e($item['item_code']); ?>

                                                            <?php endif; ?>
                                                        </td>
                                                        <?php endif; ?>
                                                        <?php if($showColor): ?>
                                                        <td class="text-center">
                                                            <?php if(!empty($item['color'])): ?>
                                                                <?php echo e($item['color']); ?>

                                                            <?php endif; ?>
                                                        </td>
                                                        <?php endif; ?>
                                                        <?php if($showSize): ?>
                                                        <td class="text-center">
                                                            <?php if(!empty($item['size'])): ?>
                                                                <?php echo e($item['size']); ?>

                                                            <?php endif; ?>
                                                        </td>
                                                        <?php endif; ?>
                                                        <td class="text-center"><span class="price-amount"><?php echo e(Helper::formatNumber($item['price'] ?? 0)); ?> <?php echo e($currencyCode); ?></span></td>
                                                        <td class="text-center qty-cell"><?php echo e($item['quantity'] ?? 0); ?></td>
                                                        <td><span class="price-amount text-center"><?php echo e(Helper::formatNumber($item['total'] ?? 0)); ?> <?php echo e($currencyCode); ?></span></td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="<?php echo e($totalCols); ?>" class="text-center"><?php echo e(__('web.no_items_found')); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    </tbody>
                                                </table>

                                                
                                                <table class="cart-list-table customized-mobile-table mobile-customized-items" style="width:100%;">
                                                    <?php $__empty_1 = true; $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <?php
                                                    $productTitle = Helper::formatCustomizedTitle(!empty($item['product_title']) ? $item['product_title'] : (!empty($item['item_code']) ? $item['item_code'] : __('web.custom_item')));
                                                    $itemImgUrl = $previewImage($item['image'] ?? null);
                                                    ?>
                                                    <tbody class="mobile-product-tbody">
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.image')); ?></th>
                                                            <td class="mobile-td">
                                                                <a href="<?php echo e($itemImgUrl); ?>" target="_blank" style="display:inline-block;">
                                                                    <img class="product-mobile-image" src="<?php echo e($itemImgUrl); ?>" alt="<?php echo e(__('web.image')); ?>" onerror="this.onerror=null;this.src='<?php echo e(asset(Storage::url('default.png'))); ?>'">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="mobile-th">Item &amp; Description</th>
                                                            <td class="mobile-td">
                                                                <div class="prod-name" style="font-weight: 700; color: #111;"><?php echo e($productTitle); ?></div>
                                                                <?php if(!empty($item['description'])): ?>
                                                                    <div class="item-description" style="margin-top: 4px; font-size: 13px; color: #555; text-transform: uppercase;"><?php echo e(Helper::formatCustomizedDescription($item['description'])); ?></div>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php if($showItemCode && !empty($item['item_code'])): ?>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.item_code')); ?></th>
                                                            <td class="mobile-td"><?php echo e($item['item_code']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <?php if($showColor && !empty($item['color'])): ?>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.color')); ?></th>
                                                            <td class="mobile-td"><?php echo e($item['color']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <?php if($showSize && !empty($item['size'])): ?>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.size')); ?></th>
                                                            <td class="mobile-td"><?php echo e($item['size']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.price')); ?></th>
                                                            <td class="mobile-td"><?php echo e(Helper::formatNumber($item['price'] ?? 0)); ?> <?php echo e($currencyCode); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.qty')); ?></th>
                                                            <td class="mobile-td"><?php echo e($item['quantity'] ?? 0); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="mobile-th"><?php echo e(__('web.total_price')); ?></th>
                                                            <td class="mobile-td" style="font-weight: 700; color: #111;"><?php echo e(Helper::formatNumber($item['total'] ?? 0)); ?> <?php echo e($currencyCode); ?></td>
                                                        </tr>
                                                    </tbody>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tbody>
                                                        <tr>
                                                            <td class="mobile-td text-center" style="padding: 16px; color: #666;"><?php echo e(__('web.no_items_found')); ?></td>
                                                        </tr>
                                                    </tbody>
                                                    <?php endif; ?>
                                                </table>
                                            </div>
                                        </div>

                                        <?php
                                            $billingRows = [];

                                            // 1. Customer Name
                                            if (!empty($customer['name'])) {
                                                $billingRows[] = '<span class="billing-customer-name">' . e($customer['name']) . '</span>';
                                            }

                                            // 2. Phone + Email
                                            $contactParts = [];
                                            if (!empty($customer['phone'])) {
                                                $contactParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.phone') . ':</strong>&nbsp;<span class="billing-val">' . e($customer['phone']) . '</span></span>';
                                            }
                                            if (!empty($customer['email'])) {
                                                $contactParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.email') . ':</strong>&nbsp;<span class="billing-val">' . e($customer['email']) . '</span></span>';
                                            }
                                            if (!empty($contactParts)) {
                                                $billingRows[] = implode('<span class="billing-comma">,</span>&nbsp;', $contactParts);
                                            }

                                            // 3. Apartment + Floor + House Number
                                            $addressParts = [];
                                            $aptVal = !empty($customer['apartment']) ? $customer['apartment'] : (!empty($customer['block']) ? $customer['block'] : null);
                                            $floorVal = !empty($customer['floor']) ? $customer['floor'] : null;
                                            $houseVal = !empty($customer['house_number']) ? $customer['house_number'] : null;

                                            if (!empty($aptVal)) {
                                                $addressParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.apartment') . ':</strong>&nbsp;<span class="billing-val">' . e($aptVal) . '</span></span>';
                                            }
                                            if (!empty($floorVal)) {
                                                $addressParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.floor') . ':</strong>&nbsp;<span class="billing-val">' . e($floorVal) . '</span></span>';
                                            }
                                            if (!empty($houseVal)) {
                                                $addressParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.house_number') . ':</strong>&nbsp;<span class="billing-val">' . e($houseVal) . '</span></span>';
                                            }
                                            if (!empty($addressParts)) {
                                                $billingRows[] = implode('<span class="billing-comma">,</span>&nbsp;', $addressParts);
                                            }

                                            // 4. Country + City
                                            $locationParts = [];
                                            if (!empty($customerCountry)) {
                                                $locationParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.country') . ':</strong>&nbsp;<span class="billing-val">' . e($customerCountry) . '</span></span>';
                                            }
                                            if (!empty($customer['city'])) {
                                                $locationParts[] = '<span class="billing-field"><strong class="billing-label">' . __('web.city') . ':</strong>&nbsp;<span class="billing-val">' . e($customer['city']) . '</span></span>';
                                            }
                                            if (!empty($locationParts)) {
                                                $billingRows[] = implode('<span class="billing-comma">,</span>&nbsp;', $locationParts);
                                            }

                                            // 5. Address Detail
                                            if (!empty($customer['address_detail'])) {
                                                $billingRows[] = [
                                                    'multiline' => true,
                                                    'label' => __('web.address_detail') . ':',
                                                    'value' => $customer['address_detail'],
                                                    'is_url' => false,
                                                ];
                                            }

                                            // 6. Shipping Location
                                            if (!empty($customer['address_url'])) {
                                                $billingRows[] = [
                                                    'multiline' => true,
                                                    'label' => __('web.shipping_location') . ':',
                                                    'value' => $customer['address_url'],
                                                    'is_url' => true,
                                                ];
                                            }

                                            // 7. Note
                                            if (!empty($customer['note'])) {
                                                $billingRows[] = [
                                                    'multiline' => true,
                                                    'label' => __('web.note') . ':',
                                                    'value' => $customer['note'],
                                                    'is_url' => false,
                                                ];
                                            }

                                            // Prepare PO / Quotation Summary Rows
                                            $summaryRows = [];

                                            // 1. Sub Total
                                            $summaryRows[] = [
                                                'label' => __('web.sub_total'),
                                                'value' => Helper::formatNumber($subTotal) . ' ' . $currencyCode,
                                                'is_grandtotal' => false,
                                            ];

                                            if ($isPurchaseOrder) {
                                                if ($shipping > 0) {
                                                    $summaryRows[] = [
                                                        'label' => __('web.shipping_charges'),
                                                        'value' => Helper::formatNumber($shipping) . ' ' . $currencyCode,
                                                        'is_grandtotal' => false,
                                                    ];
                                                }
                                                if ($discountAmount > 0) {
                                                    $summaryRows[] = [
                                                        'label' => __('web.discount'),
                                                        'value' => '- ' . Helper::formatNumber($discountAmount) . ' ' . $currencyCode,
                                                        'is_grandtotal' => false,
                                                    ];
                                                }
                                                $summaryRows[] = [
                                                    'label' => __('web.total_amount'),
                                                    'value' => Helper::formatNumber($grandTotal) . ' ' . $currencyCode,
                                                    'is_grandtotal' => true,
                                                ];
                                                $summaryRows[] = [
                                                    'label' => __('web.advance_payment'),
                                                    'value' => '- ' . Helper::formatNumber($advance) . ' ' . $currencyCode,
                                                    'is_grandtotal' => false,
                                                ];
                                                $summaryRows[] = [
                                                    'label' => __('web.pending_amount'),
                                                    'value' => Helper::formatNumber($pendingAmount) . ' ' . $currencyCode,
                                                    'is_grandtotal' => false,
                                                ];
                                            } else {
                                                if ($shipping > 0) {
                                                    $summaryRows[] = [
                                                        'label' => __('web.shipping_charges'),
                                                        'value' => Helper::formatNumber($shipping) . ' ' . $currencyCode,
                                                        'is_grandtotal' => false,
                                                    ];
                                                }
                                                if ($discountAmount > 0) {
                                                    $summaryRows[] = [
                                                        'label' => __('web.discount'),
                                                        'value' => '- ' . Helper::formatNumber($discountAmount) . ' ' . $currencyCode,
                                                        'is_grandtotal' => false,
                                                    ];
                                                }
                                                $summaryRows[] = [
                                                    'label' => __('web.total_amount'),
                                                    'value' => Helper::formatNumber($grandTotal) . ' ' . $currencyCode,
                                                    'is_grandtotal' => true,
                                                ];
                                            }

                                            $maxSyncRows = max(count($billingRows), count($summaryRows));
                                        ?>

                                        <div class="billing-summary-section">
                                            
                                            <div class="billing-column">
                                                <div class="column-heading">
                                                    <h4 class="section-title"><?php echo e(__('web.billing_details')); ?></h4>
                                                </div>
                                                <div class="column-body">
                                                    <?php $__currentLoopData = $billingRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="billing-row-item">
                                                            <?php if(is_array($bRow) && !empty($bRow['multiline'])): ?>
                                                                <div class="billing-row-multiline">
                                                                    <strong class="billing-label"><?php echo e($bRow['label']); ?></strong>
                                                                    <span class="billing-val <?php echo e($bRow['is_url'] ? 'billing-val-url' : ''); ?>"><?php echo e($bRow['value']); ?></span>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="billing-row-inline"><?php echo $bRow; ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>

                                            
                                            <div class="divider-column">
                                                <div class="grid-divider-line"></div>
                                            </div>

                                            
                                            <div class="summary-column">
                                                <div class="column-heading">
                                                    <h4 class="section-title"><?php echo e($summaryLabel); ?> <span>(<?php echo e($itemsCount); ?> <?php echo e(__('web.items')); ?>)</span></h4>
                                                </div>
                                                <div class="column-body">
                                                    <?php $__currentLoopData = $summaryRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="summary-row-item <?php echo e($sRow['is_grandtotal'] ? 'grandtotal' : ''); ?>">
                                                            <div class="summary-label"><?php echo e($sRow['label']); ?></div>
                                                            <div class="summary-value"><span class="price-amount"><?php echo e($sRow['value']); ?></span></div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        </div>

                                    <?php if(in_array($previewSource, ['review', 'cart'])): ?>
                                    <div class="bank-details-wrapper">
                                        <div class="bank-transfer-box">
                                            <p class="bank-transfer-title text-uppercase">Having trouble with the link? Pay via Bank Transfer:</p>
                                            <table>
                                                <tr>
                                                    <td class="bank-label">Bank Name:</td>
                                                    <td class="bank-value">Burgan Bank</td>
                                                </tr>
                                                <tr>
                                                    <td class="bank-label">Account Name:</td>
                                                    <td class="bank-value">Reehana General Trading and Contracting Co.</td>
                                                </tr>
                                                <tr>
                                                    <td class="bank-label">Account Number:</td>
                                                    <td class="bank-value">1460171196</td>
                                                </tr>
                                                <tr>
                                                    <td class="bank-label">IBAN:</td>
                                                    <td class="bank-value">KW28BRGN0000000000001460171196</td>
                                                </tr>
                                            </table>
                                            <p class="bank-note">* Please share the transfer receipt via WhatsApp or Email once completed.</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="page-actions">
                                        <a class="btn btn-secondary" href="<?php echo e($backUrl); ?>"><?php echo e($backLabel); ?></a>
                                    </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot class="print-layout-footer">
                <tr>
                    <td style="padding: 0; border: none;">
                        <div class="print-footer-placeholder"></div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="print-footer-spacer" aria-hidden="true"></div>

        <?php echo $__env->make('web/layout/footer_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php if(in_array($previewSource, ['review', 'cart'])): ?>
            <div class="print-letterhead-footer" aria-hidden="true">
                <div class="footer-line"></div>
                <div class="footer-content">
                    <div class="footer-left">
                        <div>Reehana Home | T +965 2571 1588 / +965 6518 6816</div>
                        <div>P.O.Box: 2016 Salmiya / 22021 Salmiya - Kuwait</div>
                        <div>Salmiya | Salem Al-Mubarak St. | Mariam Complex | Floor 7 | Office 37</div>
                    </div>
                    <div class="footer-right">
                        <div>info@reehana.com | reehana.com</div>
                        <div>ص. ب: السالمية ٢٠١٦ / الرمز ٢٢٠٢١ السالمية - الكويت</div>
                        <div>السالمية | شارع سالم المبارك | مجمع مريم | الطابق السابع | مكتب ٣٧</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <script type="text/javascript">
            (function () {
                var printed = false;

                function hidePreloader() {
                    var preloader = document.getElementById('preloader');
                    if (preloader) {
                        preloader.style.display = 'none';
                    }
                }

                function waitForFonts() {
                    if (document.fonts && document.fonts.ready) {
                        return document.fonts.ready.catch(function () {
                            return null;
                        });
                    }

                    return Promise.resolve();
                }

                function waitForImages() {
                    var images = Array.prototype.slice.call(document.images || []);
                    var pending = images.filter(function (image) {
                        return !image.complete;
                    });

                    if (!pending.length) {
                        return Promise.resolve();
                    }

                    return Promise.all(pending.map(function (image) {
                        return new Promise(function (resolve) {
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        });
                    }));
                }

                function nextPaint() {
                    return new Promise(function (resolve) {
                        requestAnimationFrame(function () {
                            requestAnimationFrame(resolve);
                        });
                    });
                }

                function waitForPrintReady() {
                    return waitForFonts()
                        .then(waitForImages)
                        .then(nextPaint)
                        .then(function () {
                            return new Promise(function (resolve) {
                                setTimeout(resolve, 150);
                            });
                        });
                }

                function openPrintDialog() {
                    if (printed) {
                        return;
                    }
                    printed = true;
                    hidePreloader();
                    window.focus();
                    window.scrollTo(0, 0);
                    waitForPrintReady().then(function () {
                        window.print();
                    });
                }

                window.addEventListener("DOMContentLoaded", hidePreloader);
                window.addEventListener("load", function () {
                    hidePreloader();
                    openPrintDialog();
                });

                if (document.readyState === "complete") {
                    openPrintDialog();
                }
            })();
        </script>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/cart/customized-preview.blade.php ENDPATH**/ ?>
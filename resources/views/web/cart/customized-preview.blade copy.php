<html lang="{{Helper::getLanguage()?Helper::getLanguage()->code :'en'}}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="generator" content="{{Helper::getAppName()?Helper::getAppName():''}}">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
        <link rel="shortcut icon" href="{{asset('front/img/favicon.png')}}" type="image/x-icon">
        <meta name="description" content="{{Helper::getAppName()?Helper::getAppName():''}}">
        <link rel="manifest" href="{{asset('front/css/manifest.json')}}">

        <title>{{Helper::getAppName()?Helper::getAppName()." | ":''}}{{ $isPurchaseOrder ? 'PO-' : 'QO-' }}PREVIEW</title>
        <link rel="stylesheet" href="{{asset('front/css/bootstrap-reboot.min.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/fontawesome.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/slick.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/jquery-ui.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/cloudzoom.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/fonts.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/main.css')}}">
        <link rel="stylesheet" href="{{asset('front/css/media.css')}}">
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
                padding-bottom: 12px;
                margin-bottom: 18px;
                border-bottom: 1px solid #ddd;
            }

            .preview-header .preview-date {
                text-align: right;
                font-size: 14px;
                line-height: 1.4;
                color: #111;
                margin-top: 18px;
            }

            .preview-header .preview-meta {
                margin-top: 6px;
                font-size: 12px;
                line-height: 1.45;
                color: #111;
            }

            .preview-header .preview-meta .preview-meta-row {
                margin-top: 8px;
            }

            .preview-header .preview-meta .preview-meta-label {
                display: block;
                font-weight: 600;
                margin-bottom: 1px;
            }

            .preview-header .preview-meta .preview-meta-value {
                display: block;
            }

            .preview-logo {
                width: 245px;
                height: 56px;
                object-fit: contain;
            }

            @media (max-width: 575px) {
                .preview-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .preview-header .preview-date {
                    text-align: left;
                    margin-top: 0;
                }
            }

            .bank-transfer-box {
                margin-top: 22px;
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border: 1px dashed #ccc;
                border-radius: 4px;
                font-size: 13px;
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
                min-height: 200px;
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
                font-size: 11px;
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
                font-size: 11px;
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
            }

            .qty-cell {
                text-align: center !important;
                vertical-align: middle !important;
            }

            .item-description {
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
                text-align: justify;
                text-justify: inter-word;
                color: #555;
                font-size: 12px;
                margin-top: 6px;
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
                font-size: 12px;
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

            .cart-table-container {
                page-break-inside: auto;
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
            }
                font-size: 12px;
                color: #555;
                background-color: transparent;
                border-top: none !important;
            }

            @media print {
                * {
                    orphans: 3;
                    widows: 3;
                }

                tr, td, th {
                    orphans: 3;
                    widows: 3;
                }
            }

            @media print {
                @page {
                    size: auto;
                    margin: 8mm 8mm 28mm 8mm;
                }

                .force-page-break-before {
                    break-before: page !important;
                    page-break-before: always !important;
                }

                .force-page-break-after {
                    break-after: page !important;
                    page-break-after: always !important;
                }

                .print-page-break-row td {
                    padding: 0 !important;
                    margin: 0 !important;
                    border: 0 !important;
                    height: 0 !important;
                    line-height: 0 !important;
                    font-size: 0 !important;
                }

                .print-page-break-spacer {
                    display: block !important;
                    break-before: page !important;
                    page-break-before: always !important;
                    height: 0 !important;
                    overflow: hidden !important;
                }

                .print-section-break {
                    display: block !important;
                    break-before: page !important;
                    page-break-before: always !important;
                    height: 0 !important;
                    overflow: hidden !important;
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

                body.review-preview .print-footer-spacer,
                body.cart-preview .print-footer-spacer {
                    display: none !important;
                }

                body.review-preview .print-letterhead-footer,
                body.cart-preview .print-letterhead-footer {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    position: fixed !important;
                    left: 8mm !important;
                    right: 8mm !important;
                    bottom: 0 !important;
                    z-index: 9999 !important;
                    margin: 0 !important;
                    padding: 4px 0 0 0 !important;
                    min-height: 28mm !important;
                    height: auto !important;
                    max-height: 28mm !important;
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
                    font-size: 9px !important;
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
                .prod-summary,
                .order-details,
                .billing-summary,
                .order-summary,
                .cart-details {
                    margin-bottom: 0 !important;
                    height: auto !important;
                    min-height: 0 !important;
                    overflow: visible !important;
                    margin-top: 10px !important;
                }

                .summary-details {
                    margin-top: 10px !important;
                }

                .payment-info,
                .prod-summary {
                    margin-bottom: 16px !important;
                }

                .prod-summary {
                    margin-top: 16px !important;
                }

                .order-details {
                    break-before: auto !important;
                    page-break-before: auto !important;
                    display: grid !important;
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    gap: 18px !important;
                    align-items: start !important;
                    clear: both !important;
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                    margin-bottom: 12px !important;
                }

                .order-details > .w-33 {
                    float: none !important;
                    width: auto !important;
                    min-width: 0 !important;
                }

                .billing-summary,
                .summary-details,
                .order-summary,
                .cart-details {
                    break-inside: avoid-page !important;
                    page-break-inside: avoid !important;
                }

                .preview-content-shell {
                    padding-bottom: 28mm !important;
                    margin-bottom: 0 !important;
                    margin-top: 0 !important;
                }

                .bank-transfer-box {
                    page-break-before: auto !important;
                    break-before: auto !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid-page !important;
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
                    font-size: 12px !important;
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
                    page-break-inside: auto !important;
                    page-break-after: auto !important;
                    page-break-before: auto !important;
                    break-inside: auto !important;
                    break-after: auto !important;
                    break-before: auto !important;
                    min-height: 0 !important;
                    height: auto !important;
                }

                .cart-list-table tbody tr td {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
                    vertical-align: top !important;
                }

                .cart-list-table tbody tr td > div {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
                }

                .cart-list-table tbody tr td > * {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
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
                    break-inside: avoid-page !important;
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
                    vertical-align: middle !important;
                }

                .cart-list-table tbody tr.item-description-row:last-child td {
                    border-bottom: 1px solid #d7d7d7 !important;
                }
            }
        </style>
    </head>

    @php
        $language = Helper::getLanguage();
        $customizedOrder = session('customized_order_details', []);
        $items = $customizedOrder['items'] ?? [];
        $isPurchaseOrder = ($customizedOrder['type'] ?? 'purchase_order') === 'purchase_order';
        $orderLabel = $isPurchaseOrder ? __('web.po_no') : __('web.qo_no');
        $summaryLabel = $isPurchaseOrder ? __('web.po_summary') : __('web.qo_summary');
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
        $itemsCount = count($items);
        $previewSource = $previewSource ?? request('source', session('customized_preview_source', 'cart'));
        $previewPhone = session('cphone');
        if (empty($previewPhone) && !empty($customer['phone'])) {
            $previewPhone = $customer['phone'];
        }
        $backUrl = $previewSource === 'review'
            ? route('web.checkout.cart_review', array_filter(['cphone' => $previewPhone]))
            : route('web.cart.index');
        $backLabel = $previewSource === 'review' ? __('web.back_to_review') : __('web.back_to_cart');
    @endphp

    <body class="menubg {{ $previewSource === 'review' ? 'review-preview' : 'cart-preview' }}">
        <div class="container preview-header">
            <img class="preview-logo" style="display:block;" src="{{asset('front/img/reehana_logo_Resize.png')}}">
            <div class="preview-date">
                <div class="preview-meta">
                    @php
                        $previewMetaRows = [
                            __('web.date') => $documentDate,
                        ];
                    @endphp
                    @foreach($previewMetaRows as $label => $value)
                        @if(!empty($value))
                        <div class="preview-meta-row">
                            <span class="preview-meta-label">{{ $label }}</span>
                            <span class="preview-meta-value">{{ $value }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="preview-content-shell">
            <section class="order">
                <div class="confirmation">
                    <div class="container">
                        <div class="payment-info"></div>

                        <div class="prod-summary">
                            <h4>{{ __('web.customized_items') }}</h4>
                            <div class="cart-table-container">
                                <?php
                                $showColor = false;
                                $showSize  = false;
                                $showItemCode = false;
                                foreach ($previewItems as $pi) {
                                    if (!empty($pi['color'])) $showColor = true;
                                    if (!empty($pi['size']))  $showSize  = true;
                                    if (!empty($pi['item_code'])) $showItemCode = true;
                                }
                                $totalCols   = 5 + ($showColor ? 1 : 0) + ($showSize ? 1 : 0) + ($showItemCode ? 1 : 0);
                                $imageColPct = 12;
                                $itemColPct = 25;
                                $rightColCount = 3 + ($showItemCode ? 1 : 0) + ($showColor ? 1 : 0) + ($showSize ? 1 : 0);
                                $rightColPct = round((100 - $imageColPct - $itemColPct) / $rightColCount, 2);
                                ?>
                                <table class="cart-list-table" style="table-layout:fixed; width:100%;">
                                    <colgroup>
                                        <col style="width:{{ $imageColPct }}%;">
                                        <col style="width:{{ $itemColPct }}%;">
                                        @if($showItemCode)<col style="width:{{ $rightColPct }}%;">@endif
                                        @if($showColor)<col style="width:{{ $rightColPct }}%;">@endif
                                        @if($showSize)<col style="width:{{ $rightColPct }}%;">@endif
                                        <col style="width:{{ $rightColPct }}%;">
                                        <col style="width:{{ $rightColPct }}%;">
                                        <col style="width:{{ $rightColPct }}%;">
                                    </colgroup>
                                    <thead style="display:table-header-group;">
                                        <tr class="sm-hidden">
                                            <th class="text-center">{{ __('web.image') }}</th>
                                            <th>{{ __('web.item') }}</th>
                                            @if($showItemCode)<th class="item-code-th">{{ __('web.item_code') }}</th>@endif
                                            @if($showColor)<th>{{ __('web.color') }}</th>@endif
                                            @if($showSize)<th>{{ __('web.size') }}</th>@endif
                                            <th>{{ __('web.price') }}</th>
                                            <th class="text-center">{{ __('web.qty') }}</th>
                                            <th class="total-price-th">{{ __('web.total_price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($items as $item)
                                    <tr class="item-product-row">
                                        <td class="text-center">
                                            <a href="{{ $previewImage($item['image'] ?? null) }}" target="_blank" style="display:inline-flex;justify-content:center;align-items:center;">
                                                <img src="{{ $previewImage($item['image'] ?? null) }}" alt="{{ __('web.image') }}" style="width:56px;height:56px;object-fit:cover;border:1px solid #ddd;border-radius:6px;padding:2px;" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'">
                                            </a>
                                        </td>
                                        <td>
                                            <div class="prod-name">
                                                {{ !empty($item['product_title']) ? $item['product_title'] : (!empty($item['item_code']) ? $item['item_code'] : __('web.custom_item')) }}
                                            </div>
                                            @if(!empty($item['description']))
                                                <div class="item-description">{{ $item['description'] }}</div>
                                            @endif
                                        </td>
                                        @if($showItemCode)
                                        <td>
                                            @if(!empty($item['item_code']))
                                                {{ $item['item_code'] }}
                                            @endif
                                        </td>
                                        @endif
                                        @if($showColor)
                                        <td>
                                            @if(!empty($item['color']))
                                                {{ $item['color'] }}
                                            @endif
                                        </td>
                                        @endif
                                        @if($showSize)
                                        <td>
                                            @if(!empty($item['size']))
                                                {{ $item['size'] }}
                                            @endif
                                        </td>
                                        @endif
                                        <td>{{ Helper::formatNumber($item['price'] ?? 0) }} {{ $currencyCode }}</td>
                                        <td class="text-center qty-cell">{{ $item['quantity'] ?? 0 }}</td>
                                        <td>{{ Helper::formatNumber($item['total'] ?? 0) }} {{ $currencyCode }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ $totalCols }}" class="text-center">{{ __('web.no_items_found') }}</td>
                                    </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="order-details clearfix">
                            <div class="w-33">
                                <div class="billing-summary">
                                    <h4>{{ __('web.billing_details') }}</h4>
                                    <div class="address-item selected">
                                        <label class="contact">
                                            @if(!empty($customer['name']))
                                            <div class="contact-name">
                                                <span>{{ $customer['name'] }}</span>
                                            </div>
                                            @endif
                                            <div class="contact-address">
                                                <address>
                                                    @if(!empty($customer['phone']))<div><strong>{{ __('web.phone') }} :</strong> {{ $customer['phone'] }}</div>@endif
                                                    @if(!empty($customer['email']))<div><strong>{{ __('web.email') }} :</strong> {{ $customer['email'] }}</div>@endif
                                                    @if(!empty($customer['block']))<div><strong>{{ __('web.floor') }} :</strong> {{ $customer['block'] }}</div>@endif
                                                    @if(!empty($customer['house_number']))<div><strong>{{ __('web.house_number') }} :</strong> {{ $customer['house_number'] }}</div>@endif
                                                    @if(!empty($customer['city']))<div><strong>{{ __('web.city') }} :</strong> {{ $customer['city'] }}</div>@endif
                                                    @if(!empty($customerCountry))<div><strong>{{ __('web.country') }} :</strong> {{ $customerCountry }}</div>@endif
                                                    @if(!empty($customer['address_detail']))<div><strong>{{ __('web.address_detail') }} :</strong> {{ $customer['address_detail'] }}</div>@endif
                                                    @if(!empty($customer['address_url']))<div><strong>{{ __('web.shipping_location') }}:</strong> {{ $customer['address_url'] }}</div>@endif
                                                    @if(!empty($customer['note']))<div><strong>{{ __('web.note') }} :</strong> {{ $customer['note'] }}</div>@endif
                                                </address>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="w-33 summary-details">
                                <h4>{{ $summaryLabel }} <span>({{ $itemsCount }} {{ __('web.items') }})</span></h4>
                                <div class="order-summary" style="border:none;">
                                    <div class="cart-details">
                                        <div class="v-flex">
                                            <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                <div class="item">{{ __('web.sub_total') }}</div>
                                                <div class="item-price">{{ Helper::formatNumber($subTotal) }} {{ $currencyCode }}</div>
                                            </div>
                                            @if($isPurchaseOrder)
                                                @if($shipping > 0)
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.shipping_charges') }}</div>
                                                    <div class="item-price">{{ Helper::formatNumber($shipping) }} {{ $currencyCode }}</div>
                                                </div>
                                                @endif
                                                @if($discountAmount > 0)
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.discount') }}</div>
                                                    <div class="item-price">- {{ Helper::formatNumber($discountAmount) }} {{ $currencyCode }}</div>
                                                </div>
                                                @endif
                                                <div class="total grandtotal" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.total_amount') }}</div>
                                                    <div class="item-price">{{ Helper::formatNumber($grandTotal) }} {{ $currencyCode }}</div>
                                                </div>
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.advance_payment') }}</div>
                                                    <div class="item-price">- {{ Helper::formatNumber($advance) }} {{ $currencyCode }}</div>
                                                </div>
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.pending_amount') }}</div>
                                                    <div class="item-price">{{ Helper::formatNumber($pendingAmount) }} {{ $currencyCode }}</div>
                                                </div>
                                            @else
                                                @if($shipping > 0)
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.shipping_charges') }}</div>
                                                    <div class="item-price">{{ Helper::formatNumber($shipping) }} {{ $currencyCode }}</div>
                                                </div>
                                                @endif
                                                @if($discountAmount > 0)
                                                <div class="total" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.discount') }}</div>
                                                    <div class="item-price">- {{ Helper::formatNumber($discountAmount) }} {{ $currencyCode }}</div>
                                                </div>
                                                @endif
                                                <div class="total grandtotal" style="border-bottom: 1px solid #ddd; margin-bottom: 0.2rem; padding-bottom: 0.2rem;">
                                                    <div class="item">{{ __('web.total_amount') }}</div>
                                                    <div class="item-price">{{ Helper::formatNumber($grandTotal) }} {{ $currencyCode }}</div>
                                                </div>
                                            @endif
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    @if(in_array($previewSource, ['review', 'cart']))
                    <div class="bank-details-wrapper">
                        <div class="bank-transfer-box">
                            <p class="bank-transfer-title text-uppercase">Having trouble with the link? Pay via Bank Transfer:</p>
                            <div class="bank-info-row">
                                <span class="bank-label">Bank Name:</span>
                                <span class="bank-value">Burgan Bank</span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-label">Account Name:</span>
                                <span class="bank-value">Reehana General Trading and Contracting Co.</span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-label">Account Number:</span>
                                <span class="bank-value">1460171196</span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-label">IBAN:</span>
                                <span class="bank-value">KW28BRGN0000000000001460171196</span>
                            </div>
                            <p class="bank-note">* Please share the transfer receipt via WhatsApp or Email once completed.</p>
                        </div>
                    </div>
                    @endif

                    <div class="page-actions">
                        <a class="btn btn-secondary" href="{{ $backUrl }}">{{ $backLabel }}</a>
                    </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="print-footer-spacer" aria-hidden="true"></div>

        @include('web/layout/footer_scripts')
                @if(in_array($previewSource, ['review', 'cart']))
            <div class="print-letterhead-footer" aria-hidden="true">
                <div class="footer-line"></div>
                <div class="footer-content">
                    <div class="footer-left">
                        <div>info@reehana.com | reehana.com</div>
                        <div>T +965 2571 1588 / +965 6518 6816 | F +965 2571 1599</div>
                        <div>P.O.Box: 2016 Salmiya / 22021 Salmiya - Kuwait</div>
                        <div>Salmiya | Salem Al-Mubarak St. | Mariam Complex | Floor 7 | Office 37</div>
                    </div>
                    <div class="footer-right">
                        <div>رأس المال المصرح به والمدفوع 1000 دينار كويتي</div>
                        <div>ص.ب 2016 السالمية / الرمز 22021 السالمية - الكويت</div>
                        <div>الأنشطة لتجميع وصيانة الثلاجات | مكتب 37</div>
                    </div>
                </div>
            </div>
        @endif
        <script type="text/javascript">
            (function () {
                var printed = false;

                function hidePreloader() {
                    var preloader = document.getElementById('preloader');
                    if (preloader) {
                        preloader.style.display = 'none';
                    }
                }

                function mmToPx(mm) {
                    var probe = document.createElement('div');
                    probe.style.position = 'absolute';
                    probe.style.visibility = 'hidden';
                    probe.style.left = '-9999px';
                    probe.style.top = '0';
                    probe.style.height = mm + 'mm';
                    probe.style.width = '1px';
                    document.body.appendChild(probe);
                    var px = probe.getBoundingClientRect().height;
                    probe.parentNode.removeChild(probe);
                    return px;
                }

                function applyPrintRowBreaks() {
                    var rows = Array.prototype.slice.call(document.querySelectorAll('.cart-list-table tbody tr.item-product-row'));
                    var shell = document.querySelector('.preview-content-shell');
                    var table = document.querySelector('.cart-list-table');
                    var thead = table ? table.querySelector('thead') : null;
                    var footer = document.querySelector('.print-letterhead-footer');
                    var orderDetails = document.querySelector('.order-details');
                    var bankDetails = document.querySelector('.bank-details-wrapper');
                    if (!rows.length || !footer || !shell) {
                        return;
                    }

                    rows.forEach(function (row) {
                        row.classList.remove('force-page-break-before');
                        row.classList.remove('force-page-break-after');
                    });

                    Array.prototype.slice.call(document.querySelectorAll('.cart-list-table tbody tr.print-page-break-row')).forEach(function (marker) {
                        marker.parentNode.removeChild(marker);
                    });

                    Array.prototype.slice.call(document.querySelectorAll('.print-section-break')).forEach(function (marker) {
                        marker.parentNode.removeChild(marker);
                    });

                    var usablePageHeightPx = mmToPx(297 - 8 - 42);
                    var shellTopPx = shell.getBoundingClientRect().top;
                    var firstRowTopPx = rows[0].getBoundingClientRect().top;
                    var firstPageConsumedPx = firstRowTopPx - shellTopPx;
                    var rowHeaderPx = thead ? thead.getBoundingClientRect().height : 0;
                    var laterPageRemaining = Math.max(0, usablePageHeightPx - rowHeaderPx - mmToPx(2));
                    var remaining = Math.max(0, usablePageHeightPx - firstPageConsumedPx - mmToPx(2));

                    rows.forEach(function (row) {
                        var rowHeight = row.getBoundingClientRect().height;
                        if (rowHeight > remaining) {
                            var marker = document.createElement('tr');
                            marker.className = 'print-page-break-row';
                            var td = document.createElement('td');
                            td.colSpan = row.children.length || 1;
                            var spacer = document.createElement('div');
                            spacer.className = 'print-page-break-spacer';
                            td.appendChild(spacer);
                            marker.appendChild(td);
                            row.parentNode.insertBefore(marker, row);
                            remaining = laterPageRemaining - rowHeight;
                        } else {
                            remaining -= rowHeight;
                        }

                        if (remaining < 0) {
                            remaining = 0;
                        }
                    });

                    function insertSectionBreakBefore(element) {
                        var marker = document.createElement('div');
                        marker.className = 'print-section-break';
                        element.parentNode.insertBefore(marker, element);
                    }

                    if (orderDetails) {
                        var orderHeight = orderDetails.getBoundingClientRect().height;
                        if (orderHeight > remaining) {
                            insertSectionBreakBefore(orderDetails);
                            remaining = laterPageRemaining - orderHeight;
                        } else {
                            remaining -= orderHeight;
                        }
                    }

                    if (bankDetails) {
                        var bankHeight = bankDetails.getBoundingClientRect().height;
                        if (bankHeight > remaining) {
                            insertSectionBreakBefore(bankDetails);
                        }
                    }
                }

                function openPrintDialog() {
                    if (printed) {
                        return;
                    }
                    printed = true;
                    hidePreloader();
                    window.focus();
                    setTimeout(function () {
                        window.print();
                    }, 350);
                }

                window.addEventListener("DOMContentLoaded", hidePreloader);
                window.addEventListener("beforeprint", applyPrintRowBreaks);
                window.addEventListener("load", function () {
                    hidePreloader();
                    applyPrintRowBreaks();
                    openPrintDialog();
                });

                if (document.readyState === "complete") {
                    applyPrintRowBreaks();
                    openPrintDialog();
                }
            })();
        </script>
    </body>
</html>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice</title>
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
        .top-border {
            border-top: 5px solid #000000; /* Approximate color from image */
            margin-bottom: 30px;
        }
        
        /* Header Layout: Logo Left, Address Center, Info Right */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo-cell {
            width: 30%;
        }
        .center-info-cell {
            width: 40%;
            text-align: left; /* Image shows center text aligned left relative to itself but in the center area, let's look closer. Actually it looks like the company name is bold at top. */
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

        /* Bill To */
        .bill-to-label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .bill-to-name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
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
        }
        .items-table td {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        /* Alignments */
        .col-desc { width: 55%; text-align: left; }
        .col-rate { width: 15%; text-align: right; }
        .col-qty { width: 10%; text-align: right; }
        .col-amount { width: 20%; text-align: right; }

        .item-main {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .item-sub {
            color: #666;
            margin-bottom: 2px;
        }
        .item-sku {
            color: #888;
        }

        /* Totals */
        .totals-table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 10px;
            float: right;
        }
        .totals-table td {
            padding: 5px 0;
            font-size: 12px;
        }
        .totals-label {
            width: 50%;
            padding-right: 20px;
            color: #555;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 600;
        }
        .totals-value {
            width: 50%;
            text-align: right;
        }
        
        .total-line {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 10px 0 !important;
            font-weight: bold;
        }
        
        .balance-line td {
            padding-top: 20px !important;
            font-size: 14px;
            font-weight: bold;
        }
        
        /* Footer/Copyright if needed, but image doesn't show much footer other than whitespace */
    </style>
</head>
<body>
    <div class="container">

        <?php
            $language = Helper::getLanguage();
            $payment_method = Helper::getPaymentMethodByID($order->payment_method);
            $currency = Helper::getDefaultCurrency();
            $currencyCode = $currency ? $currency->code : config('custom.currency');
            $customDetails = json_decode($order->custom_order_details, true);
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
                    <div class="invoice-meta-label">INVOICE</div>
                    <div class="invoice-meta-value">INV{{ $order->id }}</div>
                    
                    <div class="invoice-meta-label">DATE</div>
                    <div class="invoice-meta-value">{{ date('d-m-Y') }}</div>
                    
                    <div class="invoice-meta-label">DUE</div>
                    <div class="invoice-meta-value">On Receipt</div>
                    
                    <div class="invoice-meta-label">BALANCE DUE</div>
                    <div class="invoice-meta-value balance-due-large">{{ isset($currencyCode) ? $currencyCode : 'KWD' }} {{ number_format($order->order_total ?? 0, 3) }}</div>
                </td>
            </tr>
        </table>

        <!-- Bill To -->
        <div style="margin-bottom: 40px;">
            <div class="bill-to-label">BILL TO</div>
            <div class="bill-to-name">
                {{ $customDetails['customer']['name'] ?? ($shipping_address->first_name . ' ' . $shipping_address->last_name ?? 'CUSTOMER') }}
            </div>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-desc">DESCRIPTION</th>
                    <th class="col-rate">RATE</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-amount">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $items = $customDetails['items'] ?? []; 
                @endphp
                @foreach($items as $item)
                    @php
                        $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                        $unit = $item['unit_price'] ?? ($item['price'] ?? (isset($item['total']) && $qty ? $item['total'] / $qty : 0));
                        $total = $item['total'] ?? ($unit * $qty);
                    @endphp
                <tr>
                    <td>
                        <div class="item-main">{{ Helper::formatCustomizedTitle($item['product_title'] ?? $item['description'] ?? 'Custom Item') }}</div>
                        <div class="item-sub">
                             @if(isset($item['description'])) {{ Helper::formatCustomizedDescription($item['description']) }} <br> @endif
                             @if(isset($item['description2'])) {{ Helper::formatCustomizedDescription($item['description2']) }} <br> @endif
                             @if(isset($item['material'])) {{ $item['material'] }} <br> @endif
                             {{-- Placeholder for extra details if available --}}
                        </div>
                        <div class="item-sku">{{ $item['sku'] ?? '' }}</div>
                    </td>
                    <td class="col-rate">{{ isset($currencyCode) ? $currencyCode : 'KD' }} {{ number_format($unit, 2) }}</td>
                    <td class="col-qty">{{ $qty }}</td>
                    <td class="col-amount">{{ isset($currencyCode) ? $currencyCode : 'KD' }} {{ number_format($total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal</td>
                <td class="totals-value">{{ $currencyCode }} {{ number_format($customOrder->order_price, 3) }}</td>
            </tr>
            @if($customOrder->delivery_price > 0)
            <tr>
                <td class="totals-label">Delivery</td>
                <td class="totals-value">{{ $currencyCode }} {{ number_format($customOrder->delivery_price, 3) }}</td>
            </tr>
            @endif
             @if($customOrder->discount > 0)
            <tr>
                <td class="totals-label">Discount</td>
                <td class="totals-value">-{{ $currencyCode }} {{ number_format($customOrder->discount, 3) }}</td>
            </tr>
            @endif
            <tr>
                <td class="totals-label" style="border-top: 1px solid #ddd;"><strong>Total</strong></td>
                <td class="totals-value" style="border-top: 1px solid #ddd;"><strong>{{ $currencyCode }} {{ number_format($customOrder->order_total, 3) }}</strong></td>
            </tr>
            @if($customOrder->paid_amount > 0)
            <tr>
                <td class="totals-label">Advance Paid</td>
                <td class="totals-value">-{{ $currencyCode }} {{ number_format($customOrder->paid_amount, 3) }}</td>
            </tr>
            @endif
            <tr>
                <td class="totals-label" style="border-top: 2px solid #ddd; padding-top: 5px; color: #000;"><strong>AMOUNT DUE</strong></td>
                <td class="totals-value" style="border-top: 2px solid #ddd; padding-top: 5px; color: #000;"><strong>{{ $currencyCode }} {{ number_format($customOrder->pending_amount, 3) }}</strong></td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>
</body>
</html>
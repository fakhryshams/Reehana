<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 36px;
            letter-spacing: 6px;
            font-weight: 600;
        }

        .subtitle {
            font-size: 12px;
            color: #888;
        }

        .quotation-number {
            font-weight: 700;
        }

        .box {
            /* border: 1px solid #ddd; */
            /* padding: 12px; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th,
        td {
            border: 1px solid #e6e6e6;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        th {
            background: #f7f7f7;
        }

        .footer {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
        }

        .small {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .label {
            font-weight: bold;
            font-size: 13px;
            color: #555;
        }
        
        .value {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div style="padding: 20px 0;">
                <img src="https://reehana.com/front/img/logo.png" alt="REEHANA">
            </div>
            <div class="subtitle">Reehana General Trading &amp; Contracting Co.</div>
        </div>

        <!-- Info Section Table -->
        <table style="width: 100%; border: none; margin-bottom: 20px; margin-top: 0;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0; padding-bottom: 6px;">
                    <div style="display: inline-block; width: 80px;" class="label">QUOTATION</div> 
                    <div style="display: inline-block;" class="quotation-number">#{{ $order->id }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0; padding-bottom: 6px;">
                </td>
            </tr>
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <div style="display: inline-block; width: 80px;" class="label">Date</div> 
                    <div style="display: inline-block;" class="value">{{ date('Y-m-d') }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none; padding: 0;">
                    <div style="display: inline-block; width: 80px; text-align: left;" class="label">Phone</div>
                    <div style="display: inline-block; text-align: left;" class="value">{{ $customDetails['customer']['phone'] ?? $shipping_address->phone ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="vertical-align: top; border: none; padding: 0;">
                    <div>
                        <div style="display: inline-block; width: 80px; vertical-align: top;" class="label">Name</div> 
                        <div style="display: inline-block; width: calc(100% - 90px); vertical-align: top;" class="value">
                            {{ $customDetails['customer']['name'] ?? ($shipping_address->first_name . ' ' . $shipping_address->last_name ?? 'Customer') }}
                        </div>
                    </div>
                    @php
                        $addr = [];
                        // Try custom details first
                        if (isset($customDetails['customer'])) {
                            $c = $customDetails['customer'];
                            if (!empty($c['block'])) $addr[] = 'Block ' . $c['block'];
                            if (!empty($c['street'])) $addr[] = 'St ' . $c['street'];
                            if (!empty($c['house_number'])) $addr[] = 'House ' . $c['house_number'];
                            if (!empty($c['floor'])) $addr[] = 'Floor ' . $c['floor'];
                        }
                        // Fallback to shipping address if empty
                        if (empty($addr) && isset($shipping_address)) {
                            if($shipping_address->block) $addr[] = 'Block ' . $shipping_address->block;
                            if($shipping_address->street) $addr[] = 'St ' . $shipping_address->street;
                            if($shipping_address->house) $addr[] = 'House ' . $shipping_address->house;
                        }
                    @endphp
                    @if(isset($addr) && count($addr) > 0)
                    <div>
                       <div style="display: inline-block; width: 80px; vertical-align: top;" class="label">Address</div>
                       <div style="display: inline-block; width: calc(100% - 90px); vertical-align: top;" class="value">
                           {{ implode(', ', $addr) }}
                       </div>
                    </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="box">
            <table>
                <thead>
                    <tr>
                        <th style="width:55%">{{__('web.item')}}</th>
                        <th style="width:15%">{{__('web.price')}}</th>
                        <th style="width:15%">{{__('web.quantity')}}</th>
                        <th style="width:15%">{{__('web.total')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $items = $customDetails['items'] ?? []; @endphp
                    @if(count($items) == 0)
                    <tr>
                        <td colspan="4">No items</td>
                    </tr>
                    @else
                    @foreach($items as $it)
                    @php
                    $qty = isset($it['quantity']) ? (int)$it['quantity'] : 1;
                    $unit = $it['unit_price'] ?? ($it['price'] ?? (isset($it['total']) && $qty ? round($it['total'] / $qty, 3) : null));
                    $total = $it['total'] ?? ($unit !== null ? $unit * $qty : '-');
                    @endphp
                    <?php
                        $productName =
                            $it['product_title'] ?? ($it['item_code'] ?? null)
                            ?: ($it['description'] ?? null)
                            ?: ($it['product_title'] ?? null)
                            ?: 'Custom Item';
                    ?>
                    <tr>
                        <td>{{ $productName }}</td>
                        <td>{{ $unit !== null ? Helper::formatNumber($unit) . ' ' . ($order->currency_code ?? '') : '-' }}</td>
                        <td>{{ $qty }}</td>
                        <td>{{ $total !== '-' ? Helper::formatNumber($total) . ' ' . ($order->currency_code ?? '') : '-' }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>

            <div style="margin-top: 2rem; text-align:right">
                <div><strong>{{__('web.sub_total')}}:</strong> {{ Helper::formatNumber($order->order_price ?? '0.00') }} {{ $order->currency_code ?? '' }}</div>
                <div><strong>{{__('web.shipping')}}:</strong> {{ Helper::formatNumber($order->delivery_price ?? '0.00') }} {{ $order->currency_code ?? '' }}</div>
                <div><strong>{{__('web.tax')}}:</strong> {{ Helper::formatNumber($order->tax ?? '0.00') }} {{ $order->currency_code ?? '' }}</div>
                <div style="font-size:16px; margin-top:6px;"><strong>{{__('web.total')}}: {{ Helper::formatNumber($order->order_total ?? '0.00') }} {{ $order->currency_code ?? '' }}</strong></div>
            </div>
        </div>

        <div style="margin-top:10px; font-size:12px; color:#888;">
            For inquiries contact: {{ $customerCareEmail ?? '' }} | {{ $admin_mail ?? '' }}
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
                <span style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="{{$instagramLink}}" target="_blank"><img src="{{asset('images/mail/in.png')}}"></a></span>
                @endif
                @if($whatsappLink)
                <span style="display: inline-block; background: #ffffff; padding: 0 15px; position: relative; top: 0; margin: 0 10%"><a href="{{$whatsappLink}}" target="_blank"><img src="{{asset('images/mail/wa.png')}}"></a></span>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
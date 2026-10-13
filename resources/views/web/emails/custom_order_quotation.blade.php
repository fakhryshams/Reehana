<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .subtitle {
            font-size: 13px;
            margin-top: 5px;
        }

        .label {
            font-size: 13px;
            font-weight: normal;
        }

        .value-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 200px;
            padding-bottom: 3px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 13px;
        }

        table.items th {
            background: none;
            font-weight: normal;
        }

        .signature-table td {
            padding-top: 40px;
            font-size: 13px;
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
        <table style="width:100%; margin-bottom:20px;">
            <tr>
                <td style="width:50%;">
                    <span class="label">QUOTATION</span>
                    <span style="margin-left:24px; font-weight:bold; color:#b30000;">#{{ $order->id }}</span>
                </td>
                <td style="width:50%; text-align:right;">
                    <span class="label">Phone Number</span>
                    <span class="value-line" style="margin-left:24px;">
                        {{ $customDetails['customer']['phone'] ?? $shipping_address->phone ?? '' }}
                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="padding-top:15px;">
                    <span class="label">{{ __('web.date') }}</span>
                    <span class="value-line" style="margin-left:24px;">
                        {{ date('d-m-Y') }}
                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="padding-top:15px;">
                    <span class="label">{{ __('web.name') }}</span>
                    <span class="value-line" style="margin-left:17px; width:91.5%;">
                        {{ $customDetails['customer']['name'] ?? 'Customer' }}
                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="padding-top:15px;">
                    <span class="label">{{ __('web.address') }}</span>
                    <span class="value-line" style="margin-left:5px; width:91.5%;">
                        {{ isset($addr) ? implode(', ', $addr) : '' }}
                    </span>
                </td>
            </tr>
        </table>

        <table class="items">
        <thead>
        <tr>
            <th style="width:50%" align="left">{{__('web.item')}}</th>
            <th style="width:20%">{{__('web.price')}}</th>
            <th style="width:10%">{{__('web.quantity')}}</th>
            <th style="width:20%">{{__('web.total')}}</th>
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
            $productName = Helper::formatCustomizedTitle(
                $it['product_title'] ?? ($it['item_code'] ?? null)
                ?: ($it['description'] ?? null)
                ?: ($it['product_title'] ?? null)
                ?: 'Custom Item'
            );
        ?>
        <tr>
            <td>
                <div>{{ $productName }}</div>
                @if(!empty($it['description']))
                    <div style="font-size:12px; color:#555;">{{ Helper::formatCustomizedDescription($it['description']) }}</div>
                @endif
            </td>
            <td align="right">{{ Helper::formatNumber($unit) }} {{ $order->currency_code ?? '' }}</td>
            <td align="center">{{ $qty }}</td>
            <td align="right">{{ Helper::formatNumber($total) }} {{ $order->currency_code ?? '' }}</td>
        </tr>
        @endforeach
        @endif

        <tr>
            <td colspan="3" align="left">{{__('web.sub_total')}}</td>
            <td align="right"><strong>{{ Helper::formatNumber($order->order_price ?? '0.00') }} {{ $order->currency_code ?? '' }}</strong></td>
        </tr>
        <tr>
            <td colspan="3" align="left">{{__('web.shipping')}}</td>
            <td align="right"><strong>{{ Helper::formatNumber($order->delivery_price ?? '0.00') }} {{ $order->currency_code ?? '' }}</strong></td>
        </tr>
        <tr>
            <td colspan="3" align="left"><strong>{{__('web.total')}}</strong></td>
            <td align="right"><strong>{{ Helper::formatNumber($order->order_total ?? '0.00') }} {{ $order->currency_code ?? '' }}</strong></td>
        </tr>
        </tbody>
        </table>

        <table class="signature-table" width="100%" style="margin-top:40px;">
        <tr>
            <td colspan="2">NAME</td>
        </tr>
        <tr>
            <td width="50%">SIGN</td>
            <td width="50%">APPROVAL</td>
        </tr>
        </table>

        <div style="margin-top:2rem; margin-bottom:2rem; font-size:12px; color:#888;">
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
<style>
    table {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    table td,
    table th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    table tr:hover {
        background-color: #ddd;
    }

    table th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
    }
</style>
<table class="table normal-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.order_no')}}</th>
            <th>{{__('admin.name')}}</th>
            <th>{{__('admin.email')}}</th>
            <th>{{__('admin.total')}}</th>
            <th>{{__('admin.order_status')}}</th>
            <th>{{__('admin.country')}}</th>
            <th>{{__('admin.order-date')}}</th>
            <th>{{__('admin.payment-method')}}</th>
            <th>Payment Id</th>
            <th>{{__('admin.source')}}</th>


        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr>
            <td class="text-center">{{$record->id}}</td>
            <?php
            $user               = $record->getUser();
            $userAddress        = $record->getUserAddress();
            $userAddressCountry = $userAddress ? $userAddress->getUserCountry() : '';
            $orderStatus        = $record->getOrderStatus();
            $orderStatus        = $orderStatus ? $orderStatus->order_status : '';
            $paymentMethod      = $record->getPaymentMethodDetail();
            $badgeColor         = config('order.badge');
            $currency           = Helper::getDefaultCurrency();
            if ($currency) {
                $currency->code = config('custom.currency_convert_default_country');
            }
            $paymentResponse = $record->payment_response;
            $paymentId = "";
            if ($paymentResponse) {
                $paymentResponse = json_decode($paymentResponse);
                $y = date('Y', strtotime($record->created_at));

                $paymentId = isset($paymentResponse->invoice_id) ? $y . ($paymentResponse->PostDate ?? '-') . $paymentResponse->invoice_id : '';
            }
            ?>
            <td>{{$user?ucfirst($user->first_name):''}} {{$user?ucfirst($user->last_name):''}}</td>
            <td>{{$user?$user->email:''}}</td>
            <td class="text-center">{{Helper::formatNumber($record->order_total)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
            <td class="text-center"><span class="badge {{$badgeColor[$record->order_status]}}">{{$orderStatus}}</span></td>
            <td class="text-center">{{$userAddressCountry?$userAddressCountry->country_name:""}}</td>
            <td class="text-center">{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_datetime_format'))}}</td>
            <td class="text-center">{{$paymentMethod?$paymentMethod->payment_method_name:''}}</td>
            <td class="text-center">{{$paymentId}}</td>

            <td class="text-center">{{$record->user_agent_name ? $record->user_agent_name : '-'}}</td>

        </tr>
        @empty
        @endforelse
    </tbody>
</table>
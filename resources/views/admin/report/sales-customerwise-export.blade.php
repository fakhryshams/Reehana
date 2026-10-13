<style>
    table {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    table td, table th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    table tr:nth-child(even){background-color: #f2f2f2;}

    table tr:hover {background-color: #ddd;}

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
            <th>{{__('admin.first_column')}}</th>
            <th>{{__('admin.first_name')}}</th>
            <th>{{__('admin.last_name')}}</th>
            <th>{{__('admin.email')}}</th>
            <th>{{__('admin.mobile')}}</th>
            <th>{{__('admin.total-order')}}</th>
            <th>{{__('admin.total-sales')}}</th>
            <th>{{__('admin.coupon_mail_sent_date')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reportData as $report)
        <?php
        $userDetail = $report->getUser();
        ?>
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{$userDetail?ucfirst($userDetail->first_name):""}}</td>
            <td>{{$userDetail?ucfirst($userDetail->last_name):""}}</td>
            <td>{{$userDetail?$userDetail->email:""}}</td>
            <td>{{$userDetail?$userDetail->mobile:""}}</td>
            <td class="text-center">{{$report->no_of_order}}</td>
            <td class="text-center">{{Helper::formatNumber($report->sum_order_total)}} &nbsp; <small>  {{$currency?$currency->code:config('custom.currency')}}</small></td>
            <td class="text-center">{{$userDetail?($userDetail->coupon_mail_sent_date?\Carbon\Carbon::parse($userDetail->coupon_mail_sent_date)->format(config('custom.date-time')):''):""}}</td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
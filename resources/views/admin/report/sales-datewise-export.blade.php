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
            <th>{{__('admin.total_sales')}}</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sum = 0;
        ?>
        @forelse($reportData as $report)
        <tr>
            <td class="">{{$loop->iteration}}</td>
            <td class="text-center">{{\Carbon\Carbon::parse($report->created_at)->format(config('custom.php_date_format'))}}</td>
            <td class="text-center">{{Helper::formatNumber($report->order_total)}} <small> {{$currency?$currency->code:config('custom.currency')}}</small></td>
        </tr>
        <?php $sum += $report->order_total; ?>
        @if($loop->last)
        <tr>
            <td class=""></td>
            <td class="text-center"><strong>{{__('admin.total_sales')}} : </strong></td>
            <td class="text-center"><strong>{{Helper::formatNumber($sum)}} &nbsp;<small> {{$currency?$currency->code:config('custom.currency')}}</small></strong></td>
        </tr>
        @endif
        @empty
        @endforelse
    </tbody>
</table>
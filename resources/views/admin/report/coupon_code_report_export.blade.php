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
            <th>{{__('admin.coupon')}}</th>
            <th>{{__('admin.total-order')}}</th>
            <!--<th>{{__('admin.view')}}</th>-->
        </tr>
    </thead>
    <tbody>
        @forelse($reportData as $report)
        <?php
        $coupon     = $report->getCouponDetails();
        $couponName = "";
        $couponID   = "";
        if ($coupon)
        {
            $couponID   = $coupon->id;
            $couponName = $coupon->coupon_code;
        }
        ?>
        <tr>
            <td class="text-center">{{$loop->iteration}}</td>
            <td class="text-center"><a style="text-decoration:underline;" href="{{route('admin.order.index',['coupon'=> $couponID])}}" target="_blank" > {{$couponName}}</a></td>
            <td class="text-center">{{$report->no_of_order}}</td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
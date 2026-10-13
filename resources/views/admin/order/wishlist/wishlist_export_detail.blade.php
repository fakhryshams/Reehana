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
            <th> {{__('admin.serial')}}</th>
            <th>{{__('admin.first-name')}}</th>
            <th>{{__('admin.last-name')}}</th>
            <th>{{__('admin.email')}}</th>
            <th>{{__('admin.product')}}</th>
            <th>{{__('admin.update_date')}}</th>
            <th>{{__('admin.email_send_date')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        @php
        $user = $record->getUserData();
        $product     = $record->getProductData();
        $productName = "";
        if ($product)
        {
        $productName = $product->getProductDescription();
        }
        @endphp
        <tr>
            <td class="text-center">{{$loop->iteration}}</td>
            <td>{{$user?$user->first_name:''}}</td>
            <td>{{$user?$user->last_name:''}}</td>
            <td>{{$user?$user->email:''}}</td>
            <td>{{$productName?$productName->products_name:''}}</td>
            <td>{{$record->updated_at?\Carbon\Carbon::parse($record->updated_at)->format(config('custom.php_date_format')):''}}</td>
            <td>{{$record->mail_sent_date?\Carbon\Carbon::parse($record->mail_sent_date)->format(config('custom.php_datetime_format')):''}}</td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
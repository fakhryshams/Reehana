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
            <th class="text-center">{{__('admin.month')}}</th>
            <?php
            for ($i = $filter['fromDate']; $i <= $filter['toDate']; $i++)
            {
                ?>
                <th class="text-center">{{$i}}</th>
                <?php
            }
            ?>
        </tr>
    </thead>
    <tbody>
        @foreach($response as $data)
        <tr>
            @foreach($data as $innerData)
            <td class="text-center">
                @if(!$loop->first)
                {{Helper::formatNumber($innerData)}} &nbsp;<small>{{$currency?$currency->code:config('custom.currency')}}</small>
                @else
                {!! $innerData !!} 
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
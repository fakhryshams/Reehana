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
<table data-model="AdminCountry" class="table sorting-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.country')}}</th>
            <th>{{__('admin.shipping-charge')}}</th>
            <th>{{__('admin.delivery_time')}}</th>
            <th>{{__('admin.status')}}</th>

        </tr>
    </thead>
    <tbody>
        <?php
        $currency = Helper::getDefaultCurrency();
        ?>
        @forelse($data as $record)
        <tr id="{{$record->id}}">
            <td class="">{{$record->sorting}}</td>
            @php
            $description = $record->getCountryDescription();
            @endphp
            <td class=''>{{$description?ucfirst($description->country_name):''}}</td>
            <td class='text-center'>{{Helper::formatNumber($record->shipping_charge)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
            <td class='text-center'>{{$description?ucfirst($description->delivery_time):''}}</td>
            <td class="text-center">
                @if($record->is_active)
                <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                @else
                <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                @endif
            </td>

        </tr>
        @empty
        @endforelse
    </tbody>
</table>
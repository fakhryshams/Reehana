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
            <th>{{__('admin.product')}}<input @if($sortKey!='product_id')disabled=""@endif  type="hidden" name="product_id" value="@if($sortKey=='product_id'){{$sortVal}}@endif"></th>
            <th>{{__('admin.total_quantity')}}</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sum          = 0;
        ?>
        @forelse($reportData as $report)
        <?php
        $productName  = $report->getProduct();
        $defaultImage = $report->getProductImage();
        ?>
        <tr>
            <td class="">{{$loop->iteration}}</td>
            <td class="">{{$productName?ucfirst($productName->products_name):''}}</td>
            <td class="">{{$report->total_quantity}}</td>
        </tr>
        <?php $sum          += $report->total_quantity; ?>
        @if($loop->last)
        <tr>
            <td class=""></td>
            <td class="text-center"><strong>{{__('admin.total')}} : </strong></td>
            <td class="text-center"><strong>{{$sum}}</strong></td>
        </tr>
        @endif
        @empty
        @endforelse
    </tbody>
</table>
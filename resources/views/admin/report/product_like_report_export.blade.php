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
            <th>{{__('admin.product')}}</th>
            <th>{{__('admin.liked_count')}}</th>

        </tr>
    </thead>
    <tbody>
        @forelse($reportData as $report)
        <?php
        $product     = $report->getProductData();
        $productName = "";
        if ($product)
        {
            $productName = $product->getProductDescription();
        }
        ?>
        <tr>
            <td>{{$productName?ucfirst($productName->products_name):''}}</td>
            <td>{{$report->no_of_product}}</td>
        </tr>
        @empty
        @endforelse
    </tbody>
</table>
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
<table data-model="AdminProduct" class="table sorting-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th  >{{__('admin.name')}}</th>
            <th>{{__('admin.sku')}}</th>
            <th>{{__('admin.regular_price')}}</th>
            <th>{{__('admin.selling_price')}}</th>
            <th>{{__('admin.product_quantity_index')}}</th>
            <th>{{__('admin.category')}}</th>
            <th>{{__('admin.new_arrival-export')}}</th>
            <th>{{__('admin.latest_collection-export')}}</th>
            <th>{{__('admin.status')}}</th>

        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr id="{{$record->id}}">
            <td class="text-center">{{$record->sorting}}</td>
            <?php
            $productName     = $record->getProductDescription();
            $productCategory = $record->getProductCategories();
            $defaultImage    = $record->getDefaultImage();
            $currency        = config('custom.currency');
            ?>
            <td>{{$productName?ucfirst($productName->products_name):''}}</td>
            <td>{{$record->product_sku}}</td>
            <td class="text-center">{{$record->product_regular_price." ".$currency}}</td>
            <td class="text-center">{{$record->is_product_sale_price_null?" - ":$record->product_sale_price. " " .$currency}}</td>
            <td class="text-center">
                @if($record->product_quantity)
                {{$record->product_quantity}}
                @else
                <span class='badge badge-danger'>{{__('admin.out_of_stock')}}</span>
                @endif
            </td>
            <td>
                @foreach($productCategory as $category)
                <?php
                $categoryDetail  = $category->getCategory();

                if ($categoryDetail)
                {
                    $categoryDescription = $categoryDetail->getCategoryDescription();
                    if ($categoryDescription)
                    {
                         $string = str_replace("&","and",$categoryDescription->categories_name);
                        echo $string . "<br/>";
                    }
                }
                ?>
                @endforeach
            </td>

            <td class="text-center">
                @if($record->is_new_arrival)
                <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.yes')}}</span>
                @else
                <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.no')}}</span> 
                @endif
            </td>
            <td class="text-center">
                @if($record->is_latest_collection)
                <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.yes')}}</span>
                @else
                <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.no')}}</span> 
                @endif
            </td>
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
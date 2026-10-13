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
<table data-model="AdminSize" class="table sorting-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.brand')}}</th>
            <th>{{__('admin.category')}}</th>
            <th>{{__('admin.subcategory')}}</th>
            <th>{{__('admin.status')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $key => $record)
        <tr id="{{$record->id}}">

            <td class="text-center">{{$key + 1}}</td>
            <?php
            $cat = $record->category_id;
            $record = \App\AdminModel\Brand\AdminBrand::where('id', $record->brand_id)->first();

            $description = $record->getBrandDescription();
            $brandCatogory = $record->getBrandCategory(1);
            $category = \App\AdminModel\Product\AdminCategoryDescription::where('categories_id', $cat)->where('language_id', 1)->first();
            $pattern_category = '';
            if ($category)
            {
                $category_id = \App\AdminModel\Product\AdminCategory::where('id', $brandCatogory)->first();

                $pattern_category = \App\AdminModel\Product\AdminCategoryDescription::where('categories_id', $category_id->parent_id)->where('language_id', 1)->first();
            }
            ?>
            <td>{{$description?$description->brand_title:''}}</td>
            <td>{{$pattern_category?$pattern_category->categories_name:'-'}}</td>
            <td>
                {{$category ? $category->categories_name:'-' }}  
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


<table data-model="AdminCategory" class="table sorting-table table-head-fixed text-nowrap table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.category_name')}}</th>
            <th>{{__('admin.sub_category_name')}}</th>
            <th>{{__('admin.status')}}</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $record)
        <tr id="{{$record->id}}">
            <td class="text-center">{{$record->sorting}}</td>
            <?php
            $categoryName = $record->getCategoryDescription();
            $parent       = $record->parent_id;
            if ($parent)
            {
                $parent        = $record->getParent($parent);
                $subCategoryOf = $parent->getCategoryDescription();
            }
            ?>
            <td>
                {{$subCategoryOf?ucfirst($subCategoryOf->categories_name):''}}
            </td>
            <td>
                {{$categoryName?ucfirst($categoryName->categories_name):''}}
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
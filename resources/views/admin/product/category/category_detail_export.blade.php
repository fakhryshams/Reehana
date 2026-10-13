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
<table data-model="AdminCategory" class="table sorting-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.category_name')}}</th>
            <th>{{__('admin.status')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr id="{{$record->id}}">
            <td class="text-center">{{$record->sorting}}</td>
            @php
            $categoryName = $record->getCategoryDescription();
            @endphp
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
            @if(false)
            <td class="text-center">
                <div class="row">
                    <div class="col-md-12">
                        <span class="text-primary">{{__('admin.added_on')}} : {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.date-time'))}}</span>
                    </div>
                    @if($record->updated_by)
                    <div class="col-md-12">
                        <span class="text-success">{{__('admin.modified_on')}} : {{\Carbon\Carbon::parse($record->updated_at)->format(config('custom.date-time'))}}</span>
                    </div>
                    @endif
                </div>
            </td>
            @endif

        </tr>
        @empty
        @endforelse
    </tbody>
</table>

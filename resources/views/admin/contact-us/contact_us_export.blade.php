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
<table class="table table-head-fixed table-striped">
    <thead>
        <tr>
            <th class="text-center">{{__('admin.first_column')}}</th>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.last_name')}}</th>
            <th>{{__('admin.email')}}</th>
            <th>{{__('admin.mobile')}}</th>
            @if(false)
            <th>{{__('admin.subject')}}</th>
            @endif
            <th>{{__('admin.message')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr>
            <td class="text-center">{{$loop->iteration}}</td>
            <td>{{$record->first_name}}</td>
            <td>{{$record->last_name}}</td>
            <td>{{$record->email}}</td>
            <td>{{$record->mobile}}</td>
            @if(false)
            <td>{!!$record->subject!!}</td>
            @endif
            <td style="width: 50%">{!!$record->message!!}</td>
        </tr>
        @empty
        <tr>
            <td class="text-danger" colspan="7">{{__('admin.no_record_found')}}</td>
        </tr>
        @endforelse
    </tbody>
</table>
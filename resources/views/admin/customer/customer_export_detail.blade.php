<table class="table normal-table table-striped">
    <thead>
        <tr>
            <th>{{__('admin.serial')}}</th>
            <th>{{__('admin.first_name')}}</th>
            <th>{{__('admin.last_name')}}</th>
            <th>{{__('admin.email')}}</th>
            <th>{{__('admin.mobile')}}</th>
            <th>{{__('admin.login_type')}}</th>
            <th>{{__('admin.last_login')}}</th>
            <th>{{__('admin.register_date')}}</th>
            <th>{{__('admin.status')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr>
            <td class="text-center">{{$loop->iteration}}</td>
            <td>{{ucfirst($record->first_name)}}</td>
            <td>{{ucfirst($record->last_name)}}</td>
            <td>{{$record->email}}</td>
            <td>{{$record->mobile}}</td>
            <td>{{ucwords($record->login_type)}}</td>
            <td>{{$record->last_login?\Carbon\Carbon::parse($record->last_login)->format(config('custom.date-time')):''}}</td>
            <td>{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.date-time'))}}</td>
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
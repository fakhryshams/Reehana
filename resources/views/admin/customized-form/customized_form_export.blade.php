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
            <th>{{__('admin.customized_form_gift_type')}}</th>
            <th>{{__('admin.customized_form_sender_name')}}</th>
            <th>{{__('admin.customized_form_receiver_name')}}</th>
            <th>{{__('admin.customized_form_email')}}</th>
            <th>{{__('admin.customized_form_mobile')}}</th>
            <th>{{__('admin.customized_form_gift_occasion')}}</th>
            <th>{{__('admin.customized_form_number_of_guests')}}</th>
            <th>{{__('admin.customized_form_gift_options')}}</th>
            <th>{{__('admin.customized_form_name_on_card')}}</th>
            <th>{{__('admin.customized_form_gift_budget')}}</th>
            <th>{{__('admin.customized_form_wrapping_budget')}}</th>
            <th>{{__('admin.customized_form_preferred_delivery_date')}}</th>
            <th>{{__('admin.customized_form_message')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $record)
        <tr>
            <td class="text-center">{{$loop->iteration}}</td>
            <td>{{ucfirst($record->gift_type)}}</td>
            <td>{{ucfirst($record->sender_name)}}</td>
            <td>{{ucfirst($record->receiver_name)}}</td>
            <td>{{$record->email}}</td>
            <td>{{$record->mobile}}</td>
            <td>{{ucfirst($record->gift_occasion)}}</td>
            <td>{{ucfirst($record->number_of_guests)}}</td>
            <?php
            $category            = explode(",", $record->gift_options);
            $categoryDescription = "";
            foreach ($category as $category)
            {
                $categoryData = $record->getCategory($category);
                if ($categoryData)
                {
                    $description = $categoryData->getCategoryDescription();
                    if ($description)
                    {
                        $categoryDescription .= ucfirst($description->categories_name) . ", ";
                    }
                }
            }
            $categoryDescription = rtrim($categoryDescription, ", ");
            ?>
            <td>{{$categoryDescription}}</td>
            <td>{{ucfirst($record->name_on_card)}}</td>
            <td>{{ucfirst($record->gift_budget)}} {{config('custom.currency')}}</td>
            <td>{{ucfirst($record->wrapping_budget)}} {{config('custom.currency')}}</td>
            <td>{{$record->preferred_delivery_date?\Carbon\Carbon::parse($record->preferred_delivery_date)->format(config('custom.date')):''}}</td>
            <td style="max-width: 50%">{!!$record->message!!}</td>
            <td>{{$record->photo_reference?asset(Storage::url($record->photo_reference)):''}}></td>
        </tr>
        @empty
        <tr>
            <td class="text-danger" colspan="16">{{__('admin.no_record_found')}}</td>
        </tr>
        @endforelse
    </tbody>
</table>
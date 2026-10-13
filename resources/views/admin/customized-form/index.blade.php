@extends('layout/main')
@section('page_title',  trans('admin.customized_form'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.customized_form')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customized-form.index')}}">{{__('admin.customized_form')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.list')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- /.row -->
            <div class="row">
                <div class="col-12">
                    <form class="list-table" method="get" action="{{route('admin.customized-form.index')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <select name="request_type[]" class="form-control select2 select-type {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                    @foreach($requestType as $key=>$type)
                                                    <option value="{{$key}}" {{ (collect($requestTypeFilter)->contains($key)) ? 'selected':'' }}>{{$type}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-toolbar float-right">
                                                <div class="btn-group">
                                                    <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.search')}}">{{__('admin.search')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <!--<h4 class="card-title ml-3">{{__('admin.list')}}</h4>-->
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.customized-form.export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='gift_type'){{$sortVal}}@endif">{{__('admin.customized_form_gift_type')}}<input @if($sortKey!='gift_type')disabled=""@endif  type="hidden" name="gift_type" value="@if($sortKey=='gift_type'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='sender_name'){{$sortVal}}@endif">{{__('admin.customized_form_sender_name')}}<input @if($sortKey!='sender_name')disabled=""@endif  type="hidden" name="sender_name" value="@if($sortKey=='sender_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='receiver_name'){{$sortVal}}@endif">{{__('admin.customized_form_receiver_name')}}<input @if($sortKey!='receiver_name')disabled=""@endif  type="hidden" name="receiver_name" value="@if($sortKey=='receiver_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.customized_form_email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='mobile'){{$sortVal}}@endif">{{__('admin.customized_form_mobile')}}<input @if($sortKey!='mobile')disabled=""@endif  type="hidden" name="mobile" value="@if($sortKey=='mobile'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='gift_occasion'){{$sortVal}}@endif">{{__('admin.customized_form_gift_occasion')}}<input @if($sortKey!='gift_occasion')disabled=""@endif  type="hidden" name="gift_occasion" value="@if($sortKey=='gift_occasion'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='gift_options'){{$sortVal}}@endif">{{__('admin.customized_form_gift_options')}}<input @if($sortKey!='gift_options')disabled=""@endif  type="hidden" name="gift_options" value="@if($sortKey=='gift_options'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='number_of_guests'){{$sortVal}}@endif">{{__('admin.customized_form_number_of_guests')}}<input @if($sortKey!='number_of_guests')disabled=""@endif  type="hidden" name="number_of_guests" value="@if($sortKey=='number_of_guests'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='name_on_card'){{$sortVal}}@endif">{{__('admin.customized_form_name_on_card')}}<input @if($sortKey!='name_on_card')disabled=""@endif  type="hidden" name="name_on_card" value="@if($sortKey=='name_on_card'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='gift_budget'){{$sortVal}}@endif">{{__('admin.customized_form_gift_budget')}}<input @if($sortKey!='gift_budget')disabled=""@endif  type="hidden" name="gift_budget" value="@if($sortKey=='gift_budget'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='wrapping_budget'){{$sortVal}}@endif">{{__('admin.customized_form_wrapping_budget')}}<input @if($sortKey!='wrapping_budget')disabled=""@endif  type="hidden" name="wrapping_budget" value="@if($sortKey=='wrapping_budget'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='preferred_delivery_date'){{$sortVal}}@endif">{{__('admin.customized_form_preferred_delivery_date')}}<input @if($sortKey!='preferred_delivery_date')disabled=""@endif  type="hidden" name="preferred_delivery_date" value="@if($sortKey=='preferred_delivery_date'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='message'){{$sortVal}}@endif">{{__('admin.customized_form_message')}}<input @if($sortKey!='message')disabled=""@endif  type="hidden" name="message" value="@if($sortKey=='message'){{$sortVal}}@endif"></th>
                                        <th class="text-center" id="@if($sortKey=='photo_reference'){{$sortVal}}@endif">{{__('admin.customized_form_photo_reference')}}<input @if($sortKey!='photo_reference')disabled=""@endif  type="hidden" name="photo_reference" value="@if($sortKey=='photo_reference'){{$sortVal}}@endif"></th>
                                        @endif
                                        @canany([config('custom_middleware.delete_customized_form'),config('custom_middleware.view_customized_form')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
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
                                        @if(false)
                                        <td>{{ucfirst($record->number_of_guests)}}</td>
                                        <td>{{ucfirst($record->name_on_card)}}</td>
                                        <td>{{ucfirst($record->gift_budget)}} {{config('custom.currency')}}</td>
                                        <td>{{ucfirst($record->wrapping_budget)}} {{config('custom.currency')}}</td>
                                        <td>{{$record->preferred_delivery_date?\Carbon\Carbon::parse($record->preferred_delivery_date)->format(config('custom.date')):''}}</td>
                                        <td style="max-width: 50%">{!!$record->message!!}</td>
                                        <td class="text-center"><img class="logo-table text-center" src="{{asset(Storage::url($record->photo_reference))}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'">
                                            @endif
                                            @canany([config('custom_middleware.delete_customized_form'),config('custom_middleware.view_customized_form')])
                                        <td class="text-center">
                                            <a href="{{route('admin.customized-form.view',['id' => $record->id])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.view')}}"><i class="fas fa-eye"></i></button></a>
                                            <a onclick="showSweetAlert('{{route('admin.customized-form.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                        </td>
                                        @endcanany
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$data->appends(request()->input())->links()}}
                        </div>
                    </form>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('common/sorting')
@include('layout/sweetalert')
@include('layout/datatable')
@include('layout/image_preview')
@include('layout/select2') 
<script>
    $(document).ready(function ()
    {
    $(".select-type").select2({
    placeholder: "{{__('admin.filter_by_request_type')}}",
            allowClear: true
    });
    });
</script>
@endsection

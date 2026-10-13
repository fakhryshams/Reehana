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
                    <h1>{{__('admin.view')}} {{__('admin.customized_form')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customized-form.index')}}">{{__('admin.customized_form')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.view')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <!-- Main content -->
                    <div class="invoice p-3 mb-3">
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-12">
                                <div class="col-12 no-print">
                                    <p class="lead float-left">{{__('admin.customized_form')}}</p>
                                    <div class="row no-print float-right mb-3">
                                        <a onclick="window.print();" class="float-right btn btn-default"><i class="fas fa-print"></i> Print</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table view-table">
                                        <tr>
                                            <th>{{__('admin.customized_form_gift_type')}}</th>
                                            <td>{{ucfirst($record->gift_type)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_sender_name')}}</th>
                                            <td>{{ucfirst($record->sender_name)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_receiver_name')}}</th>
                                            <td>{{ucfirst($record->receiver_name)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_email')}}</th>
                                            <td>{{ucfirst($record->email)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_mobile')}}</th>
                                            <td>{{ucfirst($record->email)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_message')}}</th>
                                            <td>{!! $record->message !!}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_gift_occasion')}}</th>
                                            <td>{{ucfirst($record->gift_occasion)}}</td>
                                        </tr>
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
                                        <tr>
                                            <th>{{__('admin.customized_form_gift_options')}}</th>
                                            <td>{{$categoryDescription}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_gift_budget')}}</th>
                                            <td>{{Helper::formatNumber($record->gift_budget)}} {{config('custom.currency')}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_wrapping_budget')}}</th>
                                            <td>{{Helper::formatNumber($record->wrapping_budget)}} {{config('custom.currency')}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_photo_reference')}}</th>
                                            <td><img class="logo-table text-center" src="{{asset(Storage::url($record->photo_reference))}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"></td>
                                        </tr>
                                        <tr>
                                            <th>{{__('admin.customized_form_preferred_delivery_date')}}</th>
                                            <td>{{$record->preferred_delivery_date?\Carbon\Carbon::parse($record->preferred_delivery_date)->format(config('custom.php_date_format')):''}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <!-- /.col -->
                        </div>

                        <!-- /.row -->
                        <!-- this row will not appear when printing -->

                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
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

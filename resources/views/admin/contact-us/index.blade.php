@extends('layout/main')
@section('page_title',  trans('admin.contact_us'))
@section('additional_css')
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
                    <h1>{{__('admin.contact_us')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.contact-us.index')}}">{{__('admin.contact_us')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.contact-us.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <!--<h4 class="card-title ml-3">{{__('admin.list')}}</h4>-->
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.contact-us.export')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='first_name'){{$sortVal}}@endif">{{__('admin.first_name')}}<input @if($sortKey!='first_name')disabled=""@endif  type="hidden" name="first_name" value="@if($sortKey=='first_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='last_name'){{$sortVal}}@endif">{{__('admin.last_name')}}<input @if($sortKey!='last_name')disabled=""@endif  type="hidden" name="last_name" value="@if($sortKey=='last_name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='email'){{$sortVal}}@endif">{{__('admin.email')}}<input @if($sortKey!='email')disabled=""@endif  type="hidden" name="email" value="@if($sortKey=='email'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='mobile'){{$sortVal}}@endif">{{__('admin.mobile')}}<input @if($sortKey!='mobile')disabled=""@endif  type="hidden" name="mobile" value="@if($sortKey=='mobile'){{$sortVal}}@endif"></th>
                                        @if(false)
                                        <th class="asc-desc" id="@if($sortKey=='subject'){{$sortVal}}@endif">{{__('admin.subject')}}<input @if($sortKey!='subject')disabled=""@endif  type="hidden" name="subject" value="@if($sortKey=='subject'){{$sortVal}}@endif"></th>
                                        '@endif
                                        <th class="asc-desc" id="@if($sortKey=='message'){{$sortVal}}@endif">{{__('admin.message')}}<input @if($sortKey!='message')disabled=""@endif  type="hidden" name="message" value="@if($sortKey=='message'){{$sortVal}}@endif"></th>
                                        <th class="text-center">{{__('admin.action')}}</th>
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
                                        <td style="max-width: 50%">{!!$record->message!!}</td>
                                        <td class="text-center">
                                            <a onclick="showSweetAlert('{{route('admin.contact-us.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                        </td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$data->links('pagination::bootstrap-4')}}
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
@endsection

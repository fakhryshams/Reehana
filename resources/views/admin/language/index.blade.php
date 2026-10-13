@extends('layout/main')
@section('page_title',  trans('admin.language'))
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
                    <h1>{{__('admin.language')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.language.index')}}">{{__('admin.language')}}</a></li>
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
                    <form class="list-table" method="get" id="defaultradio" action="{{route('admin.language.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0"> 
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @canany([config('custom_middleware.edit_setting')])
                                        <a class="pl-2" href="{{route('admin.language.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table  class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"> {{__('admin.first_column')}}</th>
                                        <th>Default</th>
                                        <th class="asc-desc" id="@if($sortKey=='name'){{$sortVal}}@endif">{{__('admin.name')}}<input @if($sortKey!='name')disabled=""@endif  type="hidden" name="name" value="@if($sortKey=='name'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='code'){{$sortVal}}@endif">{{__('admin.language_code')}}<input @if($sortKey!='code')disabled=""@endif  type="hidden" name="code" value="@if($sortKey=='code'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_setting')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($languages as $record)
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>    <label>
                                                <input type="radio" name="languages_id" value="{{ $record->id}}"  class="default_language" {{ ($record->is_default=="1")? "checked" : "" }} >
                                            </label></td>
                                        <td>{{ucwords($record->name)}}</td>
                                        <td>{{$record->code}}</td>

                                        @canany([config('custom_middleware.edit_setting')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_setting'))
                                            <a href="{{route('admin.language.edit',['id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @endcan
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
                            {{$languages->appends(request()->input())->links()}}
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
<script>
    $('.default-div').hide();
    $(document).on('click', '.default_language', function ()
    {
        $("#defaultradio").submit();
    });
</script>
@endsection

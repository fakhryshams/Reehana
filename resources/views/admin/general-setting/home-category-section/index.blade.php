@extends('layout/main')
@section('page_title',  trans('admin.menu_home_category_section'))
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
                    <h1>{{__('admin.menu_home_category_section')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">  
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.home-category-section.index')}}">{{__('admin.menu_home_category_section')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.general-setting.home-category-section.index')}}">
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        @canany([config('custom_middleware.edit_general_setting_home_categories_section')])
                                        <a class="pl-2" href="{{route('admin.general-setting.home-category-section.edit')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.edit')}}">{{__('admin.edit')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-cener">{{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='setting_key'){{$sortVal}}@endif">{{__('admin.setting_name')}}<input @if($sortKey!='setting_key')disabled=""@endif  type="hidden" name="setting_key" value="@if($sortKey=='setting_key'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='setting_val'){{$sortVal}}@endif">{{__('admin.setting_value')}}<input @if($sortKey!='setting_val')disabled=""@endif  type="hidden" name="setting_val" value="@if($sortKey=='setting_val'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='description'){{$sortVal}}@endif">{{__('admin.setting_desc')}}<input @if($sortKey!='description')disabled=""@endif  type="hidden" name="description" value="@if($sortKey=='description'){{$sortVal}}@endif"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($settings as $record)
                                    <tr>
                                        <td class="text-cener">{{$loop->iteration}}</td>
                                        <td>{{ucwords($record->setting_key)}}</td>
                                        @if($record->is_multi_lang)
                                        <td>
                                            <?php
                                            $settingLang = $record->getDescription(0, true);
                                            if ($settingLang)
                                            {
                                                foreach ($settingLang as $settingLangVal)
                                                {
                                                    $langTitle = $settingLangVal->getLangTitle();
                                                    echo $langTitle ? $langTitle->name . " : " : '';
                                                    echo isset($settingLangVal->setting_val) ? $settingLangVal->setting_val . "<br/>" : '';
                                                }
                                            }
                                            ?>
                                        </td>
                                        @else
                                        @if($record->type == 'file')
                                        <td><img class="logo-table" src="{{ asset(Storage::url($record->setting_val))}}" title="{{$record->setting_key}}"  onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/></td>
                                        @else
                                        <td title="{{ucwords($record->setting_val)}}">{!! ucwords(strlen($record->setting_val) > 100 ? substr($record->setting_val,0,100)."..." : $record->setting_val) !!}</td>
                                        @endif
                                        @endif
                                        <td>{!! ucwords($record->description) !!}</td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$settings->appends(request()->input())->links()}}
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
@include('layout/image_preview')
@include('layout/datatable')
@endsection

@extends('layout/main')
@section('page_title',  trans('admin.size'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.size')}}</h1>  
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.size.index')}}">{{__('admin.size')}}</a></li>
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
                    <form class="list-table" method="get" action="{{route('admin.master.size.index')}}">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <select name="_c[]"  class="w-100 form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="">
                                                    @foreach($categories as $category)
                                                    @php
                                                    $categoryName = $category->getCategoryDescription();
                                                    @endphp
                                                    <option value="{{$category->id}}" {{ (collect($filterCategory)->contains($category->id)) ? 'selected':'' }}>{{ucfirst($categoryName->categories_name)}}</option>
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
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                            <a class="pl-2" href="{{route('admin.master.size.export-size')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.export')}}">{{__('admin.export')}}</button></a>
                                        </div>
                                        @canany([config('custom_middleware.create_size')])
                                        <a class="pl-2" href="{{route('admin.master.size.add')}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary')}} add-new-btn" title="{{__('admin.add')}}">{{__('admin.add')}}</button></a>
                                        @endcanany
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->

                            <table data-model="AdminSize" class="table sorting-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="asc-desc text-center" id="@if($sortKey=='sorting'){{$sortVal}}@endif">{{__('admin.index')}}<input @if($sortKey!='sorting')disabled=""@endif  type="hidden" name="sorting" value="@if($sortKey=='sorting'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='size_title'){{$sortVal}}@endif">{{__('admin.size-title')}}<input @if($sortKey!='size_title')disabled=""@endif  type="hidden" name="size_title" value="@if($sortKey=='size_title'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='category'){{$sortVal}}@endif">{{__('admin.category')}}<input @if($sortKey!='category')disabled=""@endif  type="hidden" name="category" value="@if($sortKey=='category'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc text-center" id="@if($sortKey=='is_active'){{$sortVal}}@endif">{{__('admin.status')}}<input @if($sortKey!='is_active')disabled=""@endif  type="hidden" name="is_active" value="@if($sortKey=='is_active'){{$sortVal}}@endif"></th>
                                        @canany([config('custom_middleware.edit_size')])
                                        <th class="text-center">{{__('admin.action')}}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($size as $record)
                                    <tr id="{{$record->id}}">
                                        <td class="text-center">{{$record->sorting}}</td>
                                        <?php
                                        $description    = $record->getSizeDescription();
                                        $category       = $record->getSizeCategory();
                                        ?>
                                        <td>{{$description?$description->size_title:''}}</td>
                                        <td>
                                            @foreach($category as $category)
                                            <?php
                                            $categoryDetail = $category->getCategoryDetails();
                                            ?>
                                            {!!$categoryDetail?$categoryDetail->categories_name:''!!}
                                            @if(!$loop->last)
                                            ,<br>
                                            @endif
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            @if($record->is_active)
                                            <span class="{{config('custom.badge-success','badge bg-success')}}">{{__('admin.active')}}</span>
                                            @else
                                            <span class="{{config('custom.badge-danger','badge bg-danger')}}">{{__('admin.in_active')}}</span> 
                                            @endif
                                        </td>
                                        @canany([config('custom_middleware.edit_size')])
                                        <td class="text-center">
                                            @can(config('custom_middleware.edit_size'))
                                            <a href="{{route('admin.master.size.edit',['id' => $record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                            @if($record->is_active)
                                            <a href="{{route('admin.master.size.change-status',['id'=>$record->id,'active'=>0]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                            @else
                                            <a href="{{route('admin.master.size.change-status',['id'=>$record->id,'active'=>1]).'?'.http_build_query($_GET)}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                            @endif
                                            @endcan
                                            @can(config('custom_middleware.delete_size'))
                                            <a onclick="showSweetAlert('{{route('admin.master.size.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
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
                            {{$size->links('pagination::bootstrap-4')}}
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
@include('layout/select2')
<script>
    $(function () {
    $(".select-category").select2({
    placeholder: "{{__('admin.filter_by_category')}}",
            allowClear: true,
            selectionTitleAttribute: false

    });
    });
</script>
@endsection

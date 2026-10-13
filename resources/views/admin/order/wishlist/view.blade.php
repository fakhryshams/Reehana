@extends('layout/main')
@section('page_title',  trans('admin.view-wish-list'))
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
                    <h1>{{__('admin.view-wish-list')}} ({{$user?$user->first_name:''}}  {{$user?$user->last_name:''}})</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.index')}}">{{__('admin.order')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.wishlist.index')}}">{{__('admin.wish-list')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.view-wish-list')}}</li>

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
                    <form class="list-table" method="get" action="{{route('admin.order.wishlist.view',['id' => $id])}}">
                        @if(false)
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card {{config('custom.card-primary')}}">
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <select name="products[]" class="form-control select2 select-product {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" multiple="" style="width: 100%">
                                                    @foreach($productFilter as $product)
                                                    <?php
                                                    $productName = $product->getProductDescription();
                                                    ?>
                                                    <option value="{{$product->id}}"  {{ (collect($requestProductFilter)->contains($product->id)) ? 'selected':'' }}>{{$productName?ucfirst($productName->products_name):''}}</option>
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
                        @endif
                        <div class="card {{config('custom.card-primary')}}">
                            <div class="table-card card-header pl-0">
                                <div class="card-tools w-100">
                                    @include('common/paginate')
                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="{{__('admin.search')}}" value="{{isset($searchText)?$searchText:''}}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="{{__('admin.search')}}"><i class="fas fa-search"></i></button>
                                        </div>
                                        <a class="pl-2 a-submit"  href="{{route('admin.order.wishlist.mail',['id' => $id])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success')}} add-new-btn" title="{{__('admin.send_email')}}">{{__('admin.send_email')}}</button></a>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <table class="table normal-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"> {{__('admin.first_column')}}</th>
                                        <th class="asc-desc" id="@if($sortKey=='product_id'){{$sortVal}}@endif">{{__('admin.product-name')}}<input @if($sortKey!='product_id')disabled=""@endif  type="hidden" name="product_id" value="@if($sortKey=='product_id'){{$sortVal}}@endif"></th>
                                        <th class='text-center'>{{__('admin.image')}}</th>
                                        <th class="text-center asc-desc" id="@if($sortKey=='created_at'){{$sortVal}}@endif">{{__('admin.added-on')}}<input @if($sortKey!='created_at')disabled=""@endif  type="hidden" name="created_at" value="@if($sortKey=='created_at'){{$sortVal}}@endif"></th>
                                        <th class="asc-desc" id="@if($sortKey=='mail_sent_date'){{$sortVal}}@endif">{{__('admin.email_send_date')}}<input @if($sortKey!='mail_sent_date')disabled=""@endif  type="hidden" name="mail_sent_date" value="@if($sortKey=='mail_sent_date'){{$sortVal}}@endif"></th>
                                        <th class="text-center">{{__('admin.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($wishlist as $record)
                                    <?php
                                    $product     = $record->getProductData();
                                    $productName = "";
                                    if ($product)
                                    {
                                        $productName = $product->getProductDescription();
                                    }
                                    $defaultImage = $product ? $product->getProductImages() : '';
                                    ?>
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>{{$productName?ucfirst($productName->products_name):''}}</td>
                                        <td class="text-center"><img class="logo-table text-center" alt='' src="{{asset(Storage::url($defaultImage?$defaultImage->image_thumb_url:''))}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/>
                                        <td class="text-center">{{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_date_format'))}}</td>
                                        <td>{{$record->mail_sent_date?\Carbon\Carbon::parse($record->mail_sent_date)->format(config('custom.php_datetime_format')):''}}</td>
                                        <td class="text-center"> <a class="pl-2 a-submit"  href="{{route('admin.order.wishlist.mail',['id' => $id,'w_id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success')}} add-new-btn" title="{{__('admin.send_email')}}">{{__('admin.send_email')}}</button></a></td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- /.card-body -->
                        </div>
                        <div class="table-pagination pt20 float-right">
                            {{$wishlist->appends(request()->input())->links()}}
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
@include('layout/datatable')
@include('layout/image_preview')
@include('layout/select2') 
<script>
    $(document).ready(function ()
    {
        $(".select-product").select2({
            placeholder: "{{__('admin.filter_by_product')}}",
            allowClear: true
        });
    });
</script>
@endsection

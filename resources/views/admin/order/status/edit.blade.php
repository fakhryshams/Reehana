@extends('layout/main')
@section('page_title',  trans('admin.order_status'))

@section('additional_css')
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.order_status')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.status.index')}}">{{__('admin.order_status')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.add_new')}}</h3>
                </div>
                <form method="post" action="{{route('admin.order.status.update',['id'=> $orderStatus->id])}}">
                    @csrf
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.order_status')}}</label><span class="required"></span>
                                    <input name="order_status" type="text" class="form-control  {{ $errors->has('order_status') ?'is-invalid':'' }}" placeholder="{{__('admin.order_status')}}" value="{{old('order_status',$orderStatus->order_status)}}" maxlength="100">
                                    @error('order_status')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$orderStatus->sorting!=0?$orderStatus->sorting:$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.order.status.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@section('page_specific_js')


@endsection

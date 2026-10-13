@extends('layout/main')
@section('page_title',  trans('admin.shipping_method'))

@section('additional_css')
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.shipping_method')}}</h1>
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
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.shipping.update',['id'=> $shippingMethod->id])}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $record = $shippingMethod->getShippingMethodDescription($language->id);
                            @endphp
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.shipping_name')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="shipping_name_{{$language->id}}" type="text" class="form-control  {{ $errors->has('shipping_name_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.order_status')}} ({{ucfirst($language->name)}})" value="{{old('shipping_name_'.$language->id,$record?$record->shipping_name:'')}}" maxlength="100">
                                    @error('shipping_name_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @if($shippingMethod->id == config('custom.shippingMethod',1))
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.price')}}</label><span class="required"></span>
                                    <input class="form-control"  name="price" type="text" value="{{ old('price',$shippingMethod->price)}}">
                                </div>
                            </div>
                            @endif

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

@extends('layout/main')
@section('page_title',  trans('admin.shipping-policy'))
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.shipping-policy')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.page.shipping-policy.add')}}">{{__('admin.shipping-policy')}}</a></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!--code to set button text and form action-->
    @php
    if($shippingPolicy)
    {
    $path = 'admin.page.shipping-policy.update';
    }
    else
    {
    $path = 'admin.page.shipping-policy.add';
    }
    @endphp

    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <form method="post" action="{{route($path)}}">
                    @csrf
                    <input type = "hidden" name="ID" value="{{$shippingPolicy?$shippingPolicy->id:''}}">
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $record = $shippingPolicy?$shippingPolicy->getShippingPolicyDescription($language->id):'';
                            @endphp
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.shipping-policy')}}  ({{ucfirst($language->name)}})<span class="required"></span></label>
                                    <textarea  class="textarea form-control {{ $errors->has('shipping_policy_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.shipping-policy')}} ({{ucfirst($language->name)}})" name="shipping_policy_{{$language->id}}" rows="4" cols="300">{{ old('shipping_policy_'.$language->id,$record?$record->shipping_policy:'') }}</textarea>
                                    @error('shipping_policy_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
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
@include('layout/ckeditor')
@endsection
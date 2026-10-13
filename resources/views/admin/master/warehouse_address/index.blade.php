@extends('layout/main')
@section('page_title', trans('admin.warehouse-address'))
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.warehouse-address')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.warehouse-address')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-body">
                    @if($address)
                    <table class="table table-bordered">
                        @php
                            $language = Helper::getDefaultLanguage();
                            $countryDesc = $address->countryDetail
                            ? $address->countryDetail->getCountryDescription($language->id)
                            : null;
                        @endphp
                        <tr><th>{{__('admin.country')}}</th><td>{{$countryDesc ? $countryDesc->country_name : '-'}}</td></tr>
                        @php
                            $language = Helper::getDefaultLanguage();
                            $cityDesc = $address->areaDetail
                            ? $address->areaDetail->getCityDescription($language->id)
                            : null;
                        @endphp
                        <tr><th>{{__('admin.area')}}</th><td>{{$cityDesc ? $cityDesc->city_name : '-'}}</td></tr>
                        <tr><th>{{__('admin.block')}}</th><td>{{$address->block ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.street')}}</th><td>{{$address->street ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.number')}}</th><td>{{$address->number ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.floor')}}</th><td>{{$address->floor ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.address_detail')}}</th><td>{{$address->address_detail ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.phone')}}</th><td>{{$address->phone ?? '-'}}</td></tr>
                        <tr><th>{{__('admin.pin_code')}}</th><td>{{$address->pin_code ?? '-'}}</td></tr>
                    </table>
                    <a href="{{ route('admin.master.warehouse-address.edit', ['id' => $address->id]) }}" class="btn btn-primary mt-2 float-right">{{__('admin.edit')}}</a>
                    @else
                    <div class="alert alert-info">{{__('admin.no_record_found')}}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

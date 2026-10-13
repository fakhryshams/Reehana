@extends('layout/main')
@section('page_title',  trans('admin.city'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.city')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.city.index')}}">{{__('admin.city')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.add')}}</li>
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
                <form method="post" action="{{route('admin.master.city.add')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @if(false)
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_country')}}<span class="required"></span></label>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="country_id">
                                        @foreach($country as $val)
                                        @php
                                        $description = $val->getCountryDescription();
                                        @endphp
                                        <option value="{{$val->id}}" {{ (collect(old('country_id'))->contains($val->id)) ? 'selected':'' }}>{{ucfirst($description->country_name)}}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @foreach($languageList as $language)
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.city')}} ({{ucfirst($language->name)}})<span class="required"></span></label>
                                    <input name="city_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('city_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.city')}} ({{ucfirst($language->name)}})" value="{{old('city_'.$language->id)}}">
                                    @error('city_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
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
                                <a href="{{route('admin.master.city.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
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
@include('layout/select2')
@endsection

@extends('layout/main')
@section('page_title',  trans('admin.currency'))

@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.currency')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.currency.index')}}">{{__('admin.currency')}}</a></li>
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
                <form method="post" action="{{route('admin.currency.add')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.currency')}}</label><span class="required"></span>
                                    <select class="form-control field-validate select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="country_id">
                                        @foreach($country as $val)
                                        <option value="{{$val->id}}" {{ old('country_id') == $val->id  ? 'selected':'' }}>{{$val->country_name.' ('.$val->symbol.')' }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.currency_position')}}</label><span class="required"></span>
                                    <select class="form-control field-validate" name="position">

                                        @foreach(config('custom.currency-possition',[]) as $val)
                                        <option value="{{$val}}" {{ old('position') == $val  ? 'selected':'' }}>{{$val}}</option>
                                        @endforeach
                                    </select>
                                    @error('position')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.thousand_separator')}}</label><span class="required"></span>
                                    <input name="thousands_seperator" type="text" class="form-control {{ $errors->has('thousand_separator') ?'is-invalid':'' }}" placeholder="{{__('admin.thousand_separator')}}" value="{{old('thousands_seperator',',')}}">
                                    @error('thousands_seperator')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.decimal_separator')}}</label><span class="required"></span>
                                    <input name="decimal_seperator" type="text" class="form-control {{ $errors->has('decimal_separator') ?'is-invalid':'' }}" placeholder="{{__('admin.decimal_separator')}}" value="{{old('decimal_seperator','.')}}">
                                    @error('decimal_seperator')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.number_of_decimal')}}</label><span class="required"></span>
                                    <input name="decimal_point" type="text" class="form-control decimal {{ $errors->has('decimal_point') ?'is-invalid':'' }}" placeholder="{{__('admin.number_of_decimal')}}" value="{{old('decimal_point','0')}}" maxlength="1">
                                    @error('decimal_point')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.currency_value')}}</label><span class="required"></span>
                                    <input name="value" type="text" class="form-control pointVal {{ $errors->has('value') ?'is-invalid':'' }}" placeholder="{{__('admin.currency_value')}}" value="{{old('value','0.0')}}" maxlength="6">
                                    @error('value')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.currency.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
@include('layout/decimal')


@endsection

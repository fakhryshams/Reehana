@extends('layout/main')
@section('page_title',  trans('admin.customer'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.customer')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.index')}}">{{__('admin.customer')}}</a></li>
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
                    <h3 class="card-title">{{__('admin.add')}}</h3>
                </div>
                <form method="post" action="{{route('admin.customer.add')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.first_name')}}<span class="required"></span></label>
                                    <input class="form-control length-20" placeholder="{{__('admin.first_name')}}" name="first_name" type="text" value="{{old('first_name')}}">
                                    @error('first_name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.last_name')}} <span class="required"></span></label>
                                    <input class="form-control length-10" placeholder="{{__('admin.last_name')}}" name="last_name" type="text" value="{{old('last_name')}}">
                                    @error('last_name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.customer_email')}}<span class="required"></span></label>
                                    <input class="form-control length-100" placeholder="{{__('admin.customer_email')}}" name="email" type="text" value="{{old('email')}}">
                                    @error('email')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.customer_password')}}<span class="required"></span></label>
                                    <input class="form-control length-30" placeholder="{{__('admin.customer_password')}}" name="password" type="password" value="{{old('password')}}">
                                    @error('password')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.customer_mobile')}}<span class="required"></span></label>
                                    <input class="form-control number-input length-20" placeholder="{{__('admin.customer_mobile')}}" name="mobile" type="text" value="{{old('mobile')}}">
                                    @error('mobile')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.status')}} <span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.customer_status_note')}}</small></label>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active'))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_active')
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
                                <a href="{{route('admin.customer.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
                            </div>
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
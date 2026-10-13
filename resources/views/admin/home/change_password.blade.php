@extends('layout/main')
@section('page_title',  trans('admin.profile'))
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.profile')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.home.profile')}}">{{__('admin.profile')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.change_password')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.change_password')}}</h3>
                </div>
                <form method="post" action="{{route('admin.home.update-password')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.current_password')}}</label><span class="required"></span>
                                    <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ?'is-invalid':'' }}" placeholder="{{__('admin.current_password')}}" value="">
                                    @error('current_password')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.new_password')}}</label><span class="required"></span>
                                    <input type="password" name="password" class="form-control {{ $errors->has('password') ?'is-invalid':'' }}" placeholder="{{__('admin.new_password')}}" value="">
                                    @error('password')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.password_confirmation')}}</label><span class="required"></span>
                                    <input type="password" name="password_confirmation" class="form-control {{ $errors->has('password_confirmation') ?'is-invalid':'' }}" placeholder="{{__('admin.password_confirmation')}}" value="">
                                    @error('password_confirmation')
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
                                <a href="{{route('admin.home.profile')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
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
@endsection
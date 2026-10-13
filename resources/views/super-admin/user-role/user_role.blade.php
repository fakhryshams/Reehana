@extends('layout/main')
@section('page_title',  trans('admin.user_role'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
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
                    <h1>{{__('admin.user_role')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.user-role.index')}}">{{__('admin.user_role')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.add')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.assign_role_user')}}</h3>
                </div>
                <form method="post" action="{{route('super-admin.user-role.save')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_user')}}</label> <span class="required"></span> <small class="{{config('custom.text-note-css')}}"> {{__('admin.select_user_note')}} </small>
                                    <select name="user_id[]" multiple="" class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($users as $record)
                                        @if(!$record->getRoleNames()->count())
                                        <option value="{{$record->id}}" {{ (collect(old('user_id'))->contains($record->id)) ? 'selected':'' }}>{{ucfirst($record->name)}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_role')}}</label><span class="required"></span>
                                    <select name="role_id[]" multiple="" class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($roles as $record)
                                        <option value="{{$record->id}}" {{ (collect(old('role_id'))->contains($record->id)) ? 'selected':'' }}>{{ucfirst($record->name)}}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('super-admin.user-role.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
@endsection

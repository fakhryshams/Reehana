@extends('layout/main')
@section('page_title',  trans('admin.user_role'))
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
                    <h1>{{__('admin.user_role')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.user-role.index')}}">{{__('admin.user_role')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.add_new')}}</h3>
                </div>
                <form method="post" action="{{route('super-admin.user-role.save')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.user')}}</label><span class="required"></span>
                                    <select class="select2 select-user {{ $errors->has('user_id') ?'error':'' }}" name="user_id[]">
                                        @foreach  ($userList as $record)
                                        @if((collect(old('user_id',$user->id))->contains($record->id)))
                                        <option value="{{$record->id}}" {{ (collect(old('user_id',$user->id))->contains($record->id)) ? 'selected':'' }}>{{$record ? ucfirst($record->name). ' ' .ucfirst($record->last_name) .'('.$record->email.')' : ''}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <?php
                            $role = $user->getRoleNames();
                            ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.role')}}</label><span class="required"></span>
                                    <select class="select2 select-role {{ $errors->has('role_id') ?'error':'' }}" name="role_id[]" multiple="">
                                        @forelse  ($roleList as $record)
                                        <option value="{{$record->id}}" {{ (collect(old('role_id',$role))->contains($record->name)) ? 'selected':'' }}>{{$record ? ucfirst($record->name) :''}}</option>
                                        @empty
                                        <option></option>
                                        @endforelse
                                    </select>
                                    @error('role_id')
                                    <div class="text-red">{{$message}}</div>
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
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
<script>
    $(function ()
    {
        $(".select-user").select2({
            placeholder: "{{__('admin.select_user')}}",
            selectionTitleAttribute: true,
            enable: false
        });
        $(".select-role").select2({
            placeholder: "{{__('admin.select_role')}}",
            allowClear: true,
            selectionTitleAttribute: true
        });
    });
</script>
@endsection

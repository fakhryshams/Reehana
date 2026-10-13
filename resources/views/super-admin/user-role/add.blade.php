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
                    <h1>{{__('admin.user')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.user-role.index')}}">{{__('admin.user_role')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.user')}}</li>
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
                <form method="post" action="{{route('super-admin.user-role.add')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.role')}}</label><span class="required"></span>
                                    <select class="select2 select-role {{ $errors->has('role_id') ?'error':'' }}" name="role_id[]" multiple="">
                                        @forelse  ($roleList as $record)
                                        <option value="{{$record->id}}" {{ (collect(old('role_id'))->contains($record->id)) ? 'selected':'' }}>{{$record ? ucfirst($record->name) :''}}</option>
                                        @empty
                                        <option></option>
                                        @endforelse
                                    </select>
                                    @error('role_id')
                                    <div class="text-red">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.name')}}</label><span class="required"></span>
                                    <input name="name" type="text" class="form-control length-30  {{ $errors->has('name') ?'is-invalid':'' }}" placeholder="{{__('admin.name')}}" value="{{old('name')}}">
                                    @error('name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.user_name')}}</label><span class="required"></span>
                                    <input name="username" type="text" class="form-control length-30 {{ $errors->has('username') ?'is-invalid':'' }}" placeholder="{{__('admin.user_name')}}" value="{{old('username')}}">
                                    @error('username')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.email')}}</label><span class="required"></span>
                                    <input name="email" type="email" class="form-control length-100 {{ $errors->has('email') ?'is-invalid':'' }}" placeholder="{{__('admin.email')}}" value="{{old('email')}}">
                                    @error('email')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.password')}}</label><span class="required"></span>
                                    <input name="password" type="password" class="form-control length-30 {{ $errors->has('password') ?'is-invalid':'' }}" placeholder="{{__('admin.password')}}" value="{{old('password')}}">
                                    @error('password')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.password_confirmation')}}</label><span class="required"></span>
                                    <input name="password_confirmation" type="password" class="form-control length-30 {{ $errors->has('password_confirmation') ?'is-invalid':'' }}" placeholder="{{__('admin.password_confirmation')}}" value="{{old('password_confirmation')}}">
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
        $(".select-role").select2({
            placeholder: "{{__('admin.select_role')}}",
            allowClear: true,
            selectionTitleAttribute: true
        });
    });
</script>
@endsection


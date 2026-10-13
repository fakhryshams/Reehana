@extends('layout/main')
@section('page_title',  trans('admin.language'))
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.language')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.language.index')}}">{{__('admin.language')}}</a></li>
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
              
                <form method="post" action="{{route('admin.language.update',['id'=>$language->id])}}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$language->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.name')}}</label><span class="required"></span>
                                    <input name="name" type="text" class="form-control {{ $errors->has('name') ?'is-invalid':'' }}" placeholder="{{__('admin.language_name_placeholder')}}" value="{{old('name',ucwords($language->name))}}">
                                    @error('name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.language_code')}}</label><span class="required"></span>
                                    <input name="code" type="text" class="form-control {{ $errors->has('code') ?'is-invalid':'' }}" placeholder="{{__('admin.language_code_placeholder')}}" value="{{old('code',$language->code)}}">
                                    @error('code')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.language_direction')}}</label><span class="required"></span>
                                    <select class="form-control field-validate" name="direction">
                                        <option value="rtl" {{ (collect(old('direction',$language->direction))->contains('rtl')) ? 'selected':'' }}>Right To Left</option>
                                        <option value="ltr" {{ (collect(old('direction',$language->direction))->contains('ltr')) ? 'selected':'' }}>Left To Right</option>
                                    </select>

                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>{{__('admin.language_icon')}}</label>
                                    <div class="input-group">
                                        <div class="custom-file ">
                                            <input type="file" name="image" class="custom-file-input">
                                            <label class="custom-file-label">{{__('admin.language_icon')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table text-center" src="{{ Storage::url($language->image)}}"/></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.language.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script src="{{asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
<script>
$(function ()
{
    bsCustomFileInput.init();
    $("input[type=file]").change(function ()
    {
        readURL(this);
    });
});
function readURL(input)
{
    if (input.files && input.files[0])
    {
        var reader = new FileReader();

        reader.onload = function (e)
        {
            $('.logo-table').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

@extends('layout/main')
@section('page_title',  trans('admin.setting'))
@section('content')
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.setting')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.general-setting.setting.index')}}">{{__('admin.setting')}}</a></li>
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
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.general-setting.setting.update-setting')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @foreach($settings as $record)
                        <div class="row">
                            @if($record->is_multi_lang)
                            @foreach($languageList as $language)
                            <?php
                            $settingLang = $record->getDescription($language->id);
                            ?>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{$record->setting_key}} ({{ucfirst($language->name)}}) 
                                        @if($record->is_require==1)
                                        <span class="required"></span>
                                        @endif
                                        <small class="text-primary"> {!! $record->description? "(".$record->description.")" :'' !!} </small>
                                    </label>
                                    @if($record->type=='textarea')
                                    <textarea name="setting_{{$record->id}}_{{$language->id}}" class="form-control textarea {{ $errors->has('setting_'.$record->id.'_'.$language->id) ?'is-invalid':'' }}" placeholder="{{$record->setting_key}} ({{ucfirst($language->name)}})">{{old('setting_'.$record->id.'_'.$language->id,isset($settingLang->setting_val)?$settingLang->setting_val:'')}}</textarea>
                                    @else
                                    <input name="setting_{{$record->id}}_{{$language->id}}" type="text" class="setting-text form-control {{ $errors->has('setting_'.$record->id.'_'.$language->id) ?'is-invalid':'' }}" placeholder="{{$record->setting_key}} ({{ucfirst($language->name)}})" value="{{old('setting_'.$record->id.'_'.$language->id,isset($settingLang->setting_val)?$settingLang->setting_val:'')}}">
                                    @endif
                                    @error('setting_'.$record->id.'_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{$record->setting_key}} 
                                        @if($record->is_require==1)
                                        <span class="required"></span>
                                        @endif
                                        <small class="text-primary"> {!! $record->description? "(".$record->description.")" :'' !!} </small> 
                                        @if(isset($record->id) && $record->id==41 || $record->id==44)
                                        <small class="text-primary">{{__('admin.gift_image_dimention')}}, {{__('admin.imageformat1')}}</small>
                                        @endif
                                    </label>
                                    @if($record->id ==2 && $record->type=='file')
                                    <!--Logo-->
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="setting_{{$record->id}}" class="custom-file-input" onchange="readURL(this, 'default-image-logo')">
                                            <label class="custom-file-label">{{$record->setting_key}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class=""><img class="logo-table default-image-logo" src="{{ asset(Storage::url($record->setting_val))}}" title="{{$record->setting_key}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/></span>
                                    </div>
                                    @elseif($record->id ==3 && $record->type=='file')
                                    <!--Favicon-->
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="setting_{{$record->id}}" class="custom-file-input" onchange="readURL(this, 'default-image-favicon')">
                                            <label class="custom-file-label">{{$record->setting_key}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class=""><img class="logo-table default-image-favicon" src="{{ asset(Storage::url($record->setting_val))}}" title="{{$record->setting_key}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/></span>
                                    </div>
                                    @elseif($record->type=='file')
                                    <!--Other Images-->
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="setting_{{$record->id}}" class="custom-file-input" onchange="readURL(this, 'default-image-{{$record->id}}')">
                                            <label class="custom-file-label">{{$record->setting_key}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class=""><img class="logo-table default-image-{{$record->id}}" src="{{ asset(Storage::url($record->setting_val))}}" title="{{$record->setting_key}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/></span>
                                    </div>
                                    @elseif($record->type=='textarea')
                                    <textarea name="setting_{{$record->id}}" class="form-control textarea {{ $errors->has('setting_'.$record->id) ?'is-invalid':'' }}" placeholder="{{$record->setting_key}}">{{old('setting_val',$record->setting_val)}}</textarea>
                                    @elseif($record->type=='radio')
                                    <div class="form-group clearfix">
                                        @foreach($radioValue as $key=>$radio)
                                        <div class="icheck-primary d-inline">
                                            <input type="radio" id="{{$radio}}" value="{{$key}}" name="setting_{{$record->id}}" {{ (collect(old('setting_'.$record->id,$record->setting_val,$record->setting_val))->contains($key)) ? 'checked':'' }}>
                                            <label for="{{$radio}}">{{ucfirst($radio)}}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <input name="setting_{{$record->id}}" type="text" class="setting-text form-control {{ $errors->has('setting_'.$record->id) ?'is-invalid':'' }} {{ $errors->has('setting_val') ?'is-invalid':'' }} @if($record->id==33 || $record->id==34) float-input @endif" " placeholder="{{$record->setting_key}}" value="{{old('setting_'.$record->id,$record->setting_val)}}">
                                    @endif
                                    @error('setting_'.$record->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.general-setting.setting.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
@include('layout/ckeditor')
@include('layout/fileupload')
@include('layout/image_preview')
<script>
    function readURL(input, imageClass)
    {
        if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e)
            {
                $('.' + imageClass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection

@extends('layout/main')
@section('page_title',  trans('admin.home_slider'))
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
                    <h1>{{__('admin.home_slider')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.page.home-slider.index')}}">{{__('admin.home_slider')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.page.home-slider.update',['id' => $id])}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            @foreach($languageList as $language)
                            @php
                            $record = $singleRecord->getHomeSliderDescription($language->id);
                            @endphp
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.title')}} ({{ucfirst($language->name)}})</label> <small class="{{config('custom.text-note-css')}}">( {{__('admin.character_length')}} 50 )</small>
                                    <input name="title_{{$language->id}}" type="text" class="form-control {{ $errors->has('title_'.$language->id) ?'is-invalid':'' }} length-50" placeholder="{{__('admin.title')}} ({{ucfirst($language->name)}})" value="{{old('title_'.$language->id,$record?$record->banner_header:'')}}">
                                    @error('title_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.btn-text')}} ({{ucfirst($language->name)}})</label> <small class="{{config('custom.text-note-css')}}">( {{__('admin.character_length')}} 14 )</small><span class="required"></span>
                                    <input name="button_text_{{$language->id}}" type="text" class="form-control {{ $errors->has('button_text_'.$language->id) ?'is-invalid':'' }} length-14" placeholder="{{__('admin.btn-text')}} ({{ucfirst($language->name)}})" value="{{old('button_text_'.$language->id,$record?$record->button_title:'')}}">
                                    @error('button_text_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <!--</div>-->
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <div class="form-group"> 
                                        <label>{{__('admin.sorting_order')}}</label> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small><span class="required"></span> 
                                        <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$singleRecord->sorting!=0?$singleRecord->sorting:$sortingNumber)}}">
                                        @error('sorting')
                                        <div class="text-red">{!! $message !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>{{__('admin.link')}}</label> <small class="{{config('custom.text-note-css')}}">({{__('admin.url_hint')}})</small><span class="required"></span>
                                    <input name="link" type="text" class="form-control {{ $errors->has('link') ?'is-invalid':'' }} length-1000" placeholder="{{__('admin.link')}}" value="{{old('link',$singleRecord->button_link)}}">
                                    @error('link')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>{{__('admin.status')}}</label> <span class="required"></span>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active',$singleRecord->is_active))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_active')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.photo')}}</label><small class="{{config('custom.text-note-css')}}"> {{__('admin.default_slider_photo_size')}} </small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="photo" class="custom-file-input {{ $errors->has('photo') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image4')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('photo')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image4 text-center mt-1" src="{{ asset(Storage::url($singleRecord->banner_image)) }}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.page.home-slider.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
@include('layout/fileupload')
@include('layout/select2')
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
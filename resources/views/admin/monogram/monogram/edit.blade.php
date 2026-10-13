@extends('layout/main')
@section('page_title',  trans('admin.monogram_master'))
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
                    <h1>{{__('admin.monogram_master')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.monogram.monogram.index')}}">{{__('admin.monogram_master')}}</a></li>
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
                <form method="post" action="{{route('admin.monogram.monogram.update',['id'=>$singleRecord->id])}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.placement_name')}}</label><span class="required"></span>
                                    <input class="form-control {{ $errors->has('placement_name') ?'is-invalid':'' }}" placeholder="{{__('admin.placement_name')}}" name="placement_name" type="text" value="{{old('placement_name',$singleRecord->placement_name)}}">
                                    @error('placement_name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.placement_desc')}}</label><span class="required"></span>
                                    <input class="form-control {{ $errors->has('placement_desc') ?'is-invalid':'' }}" placeholder="{{__('admin.placement_desc')}}" name="placement_desc" type="text" value="{{old('placement_desc',$singleRecord->placement_desc)}}">
                                    @error('placement_desc')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.placement_image')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.placement_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="placement_image" class="custom-file-input" onchange="readURL(this, 'default-image')">
                                            <label class="custom-file-label">{{__('admin.placement_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('placement_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image text-center mt-1" src="{{asset(Storage::url($singleRecord->placement_image))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.monogram.monogram.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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

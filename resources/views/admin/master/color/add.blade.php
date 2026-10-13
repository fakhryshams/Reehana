@extends('layout/main')
@section('page_title',  trans('admin.color'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/tags/bootstrap-tagsinput.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<style>
    .bootstrap-tagsinput .tag{
        margin-right: 2px;
        color: #fff;
        background-color: #CAA42F !important;
        padding: 3px;
        border-radius: 6px;
    }
    .bootstrap-tagsinput{
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.color')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.color.index')}}">{{__('admin.color')}}</a></li>
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
                <form method="post" action="{{route('admin.master.color.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.color')}} ({{ucfirst($language->name)}})</label><span class="required"></span> <span class="{{config('custom.text-note-css')}}">{{__('admin.add_multiple_help_text')}}</span>
                                    <input data-role="tagsinput" name="color_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('color_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.color')}} ({{ucfirst($language->name)}})" value="{{old('color_'.$language->id)}}">
                                    @error('color_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-4 col-sm-12 hidden">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.color.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script src="{{asset('plugins/tags/bootstrap-tagsinput.min.js')}}"></script>
<script>
$(function () {
    
});
</script>
@endsection

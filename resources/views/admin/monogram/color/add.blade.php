@extends('layout/main')
@section('page_title',  trans('admin.monogram_color'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.monogram_color')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.monogram.color.index')}}">{{__('admin.monogram_color')}}</a></li>
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
                <form method="post" action="{{route('admin.monogram.color.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.color_hash')}}</label><span class="required"></span>
                                    <!--<div class="input-group my-colorpicker2 colorpicker-element" data-colorpicker-id="2">-->
                                    <div class="input-group">
                                        <input class="form-control {{ $errors->has('color_hash') ?'is-invalid':'' }} mycolor" placeholder="{{__('admin.color_hash')}}" name="color_hash" type="text" value="{{old('color_hash')}}">
                                        <div class="input-group-append">
                                            <span class="input-group-text "><i class="fas fa-square colorcode"></i></span>
                                        </div>
                                    </div>
                                    @error('color_hash')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.color_name')}}</label><span class="required"></span>
                                    <input class="form-control {{ $errors->has('color_name') ?'is-invalid':'' }}" placeholder="{{__('admin.color_name')}}" name="color_name" type="text" value="{{old('color_name')}}">
                                    @error('color_name')
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

                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.monogram.color.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script src="{{asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
<script>
$('.my-colorpicker2').colorpicker()
$('.my-colorpicker2').on('colorpickerChange', function (event)
{
//    console.log(event.color);
    $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
});


$(".mycolor").blur(function () {

    $('.colorcode').css("color", $(".mycolor").val());
});
$(".mycolor").change(function () {

    $('.colorcode').css("color", $(".mycolor").val());
});
</script>
@endsection

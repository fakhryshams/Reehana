@extends('layout/main')
@section('page_title',  trans('admin.color'))

@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.color')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.color.index')}}">{{__('admin.color')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <form method="post" action="{{route('admin.master.color.update',['id' => $singleRecord->id])}}" enctype="multipart/form-data">
                <div class="card {{config('custom.card-primary')}}">
                    <div class="card-header">
                        <h3 class="card-title">{{__('admin.edit')}}</h3>
                    </div>
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $record = $singleRecord->getColorDescription($language->id);
                            @endphp
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.color')}} ({{ucfirst($language->name)}})<span class="required"></span></label>
                                    <input name="color_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('color_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.color')}} ({{ucfirst($language->name)}})" value="{{old('color_'.$language->id,$record?$record->color_title:'')}}">
                                    @error('color_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}<span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small></label>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$singleRecord->sorting!=0?$singleRecord->sorting:$sortingNumber)}}">
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
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </section>
</div>
@endsection

@section('page_specific_js')
@include('layout/select2')
@include('layout/decimal')


@endsection

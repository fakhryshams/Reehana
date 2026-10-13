@extends('layout/main')
@section('page_title',  trans('admin.zone'))
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
                    <h1>{{__('admin.zone')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.zone.index')}}">{{__('admin.zone')}}</a></li>
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
                <form method="post" action="{{route('admin.master.zone.update',['id'=>$singleRecord->id])}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.zone_title')}} </label><span class="required"></span>
                                    <input name="title" type="text" class="form-control length-30 {{ $errors->has('title') ?'is-invalid':'' }}" placeholder="{{__('admin.zone_title')}}" value="{{old('title',$singleRecord->title)}}">
                                    @error('title')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.zone_delivery_charge')}} </label><span class="required"></span>
                                    <input name="delivery_charge" type="text" class="form-control float-input {{ $errors->has('delivery_charge') ?'is-invalid':'' }}" placeholder="{{__('admin.zone_delivery_charge')}}" value="{{old('delivery_charge',Helper::formatNumber($singleRecord->delivery_charge,2))}}">
                                    @error('delivery_charge')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row hidden">
                            <div class="col-md-12">
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
                            <div class="col-md-12">
                                <div class="form-group m-0"> 
                                    <label>{{__('admin.select_zone_area')}}</label><span class="required"></span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <select name="from[]" id="lstview" class="form-control" size="13" multiple="multiple">
                                    @foreach($areaList as $record)
                                    <?php
                                    $description = $record->getCityDescription();
                                    ?>
                                    <option value="{{$record->id}}" {{collect($selectedZoneArea)->contains($record->id)?'selected':''}}>{{$description?ucfirst($description->city_name):''}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="lstview_undo" class="btn btn-danger btn-block">Undo</button>
                                <button type="button" id="lstview_rightAll" class="btn btn-default btn-block"><i class="fa fa-forward"></i></button>
                                <button type="button" id="lstview_rightSelected" class="btn btn-default btn-block"><i class="fa fa-chevron-right"></i></button>
                                <button type="button" id="lstview_leftSelected" class="btn btn-default btn-block"><i class="fa fa-chevron-left"></i></button>
                                <button type="button" id="lstview_leftAll" class="btn btn-default btn-block"><i class="fa fa-backward"></i></button>
                                <button type="button" id="lstview_redo" class="btn btn-warning btn-block">Redo</button>
                            </div>
                            <div class="col-md-5">
                                <select name="to[]" id="lstview_to" class="form-control" size="13" multiple="multiple">
                                    @foreach($areaList as $record)
                                    <?php
                                    $description = $record->getCityDescription();
                                    ?>
                                    @if(collect($selectedZoneArea)->contains($record->id))
                                    <option value="{{$record->id}}">{{$description?ucfirst($description->city_name):''}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.zone.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script src="{{asset('plugins/multiselect/multiselect.js')}}"></script>
<script>
$(document).ready(function () {

    $('#lstview').multiselect();
});
</script>
@endsection

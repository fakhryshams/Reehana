@extends('web/layout/main')
@section('page_title',  trans('web.media'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container-fluid">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.media')}}</a></span>
    </div>
</section>
<section class="general-info">
    <h3>{{__('web.media')}}</h3>
    <div class="media-articles">
        @foreach($media as $record)
        <div class="media">
            <div class="img"><img src="{{asset(Storage::url($record->image_url))}}"></div>
            <div class="detail">
                <span class="title">{{$record->title}}</span>
                <span class="text">{{$record->text}}</span>
                <span><a href="{{$record->link_url}}" target="_blank">{{__('web.read_article')}}</a></span>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection

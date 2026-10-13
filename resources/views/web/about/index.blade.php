@extends('web/layout/main')
@section('page_title',  trans('web.about_us'))
@section('body_class',  'menubg')
@section('page_meta')
<meta name="title" content="{{$aboutUs->meta_title}}">
<meta name="description" content="{{$aboutUs->meta_description}}">
<meta name="keyword" content="{{$aboutUs->meta_keyword}}">
@endsection
@section('content')

<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.about_us')}}</a></span>
    </div>
</section>
<?php $language = Helper::getLanguage(); ?>
<section class="about-us">
    <div class="container">
        <div class="grey-background h-flex">
            <div class="col-md-7">
                <div class="row images">
                    <div class="about-image">
                        <img src="{{asset(Storage::url($aboutUs ? $aboutUs->image_url_1 : 'front/img/about-01.jpg' ))}}">
                    </div>
                    <div class="about-image">
                        <img src="{{asset(Storage::url($aboutUs ? $aboutUs->image_url_2 : 'front/img/about-02.jpg' ))}}">
                    </div>
                </div>
                <div class="row images">
                    <div class="about-image">
                        <img src="{{asset(Storage::url($aboutUs ? $aboutUs->image_url_3 : 'front/img/about-03.jpg' ))}}">
                    </div>
                    <div class="about-image">
                        <img src="{{asset(Storage::url($aboutUs ? $aboutUs->image_url_4 : 'front/img/about-04.jpg' ))}}">
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row">
                    <div class="content">
                        <h2>{{__('web.about_us')}}</h2>
                        {!! $aboutUs? $aboutUs->getAboutUsEditDescription($language->id)->note: ''!!}
                        
                        <div class="d-flex">
                            <a href="{{$aboutUs ? $aboutUs->button_link:'#'}}" class="btn btn-primary mr-2">
                                {{__('web.shop_now')}}
                            </a>

                            <a href="{{route("web.contact")}}" class="btn btn-primary">
                                {{__('web.contact_us')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection

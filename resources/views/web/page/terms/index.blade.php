@extends('web/layout/main')
@section('page_title',  trans('web.terms_conditions'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.terms')}}">{{__('web.terms_conditions')}}</a></span>
    </div>
</section>
<section class="general-info">
    <div class="grey-background">
        <div class="white-background">
            <h2>{{__('web.terms_conditions')}}</h2>
            <?php $language = Helper::getLanguage(); ?>
            {!! $shippingPolicy ? $shippingPolicy->getShippingPolicyDescription($language->id)->shipping_policy : '' !!}

        </div>
    </div>
</section>
@endsection

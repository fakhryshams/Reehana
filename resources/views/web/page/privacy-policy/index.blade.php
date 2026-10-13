@extends('web/layout/main')
@section('page_title',  trans('web.privacy_policy'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.privacy-policy')}}">{{__('web.privacy_policy')}}</a></span>
    </div>
</section>
<section class="general-info">
    <div class="grey-background">
        <div class="white-background">
            <h2>Privacy</h2>
            <?php $language = Helper::getLanguage(); ?>
            {!! $shippingPolicy ? $shippingPolicy->getShippingPolicyDescription($language->id)->shipping_policy : '' !!}
        </div>
    </div>
</section>
@endsection

@extends('web/layout/main')
@section('page_title',  trans('web.return_policies'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container-fluid">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.returns')}}">{{__('web.return_policies')}}</a></span>
    </div>
</section>
<section class="general-info">
    <h3>{!!__('web.return_exchange_title')!!}</h3>
    <div>
        <?php $language = Helper::getLanguage(); ?>
        {!! $returnPolicy ? $returnPolicy->getReturnPolicyDescription($language->id)->return_policy : '' !!}
    </div>
</section>
@endsection

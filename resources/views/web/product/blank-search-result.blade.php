@extends('web.layout.main')
@section('page_title',  trans('web.search'))
@section('additional_css')
<style>
    .products-and-filters .products{
        max-width: 100%;
    }
</style>
@endsection
@section('wrapper_class',  '')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.search')}}</a></span>
    </div>
</section>
<section class="products-and-filters">
    <div class="container-fluid">
        <div  style="text-align: center">
            <div class="products-list">

                <div class="products-list h-flex">
                    <div class="noresults">
                        <span class="icon"></span>
                        <h1>No Results Found</h1>
                        <h2>Try using a more general search term</h2>
                        <!--<a href='{{route("web.product.index")}}'><button class="btn btn-primary">Clear All Filters</button></a>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_js')
@endsection


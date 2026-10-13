@extends('web/layout/main')
@section('page_title',  trans('web.404_title'))
@section('body_class',  'menubg')
@section('404_class',  'p404')
@section('content')
<div class="pg404">
    <div class="container">
        <div class="grey-background">
            <div class="h-flex">
                <h1>404</h1>
                <h2>Error</h2>
            </div>
            <p>Oops! Page not found.</p>
        </div>
    </div>
</div>
@endsection
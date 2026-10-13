<div class="col-md-3 typeA">
    <a href="{{$response['url']}}"><img class="recent-order-img img-circle" src="{{$response['image']}}"></a>
    <a href="{{$response['url']}}">
        <span class="title">{{$response['user']}}</span>
        <p>{{__('web.homepage_order_desc')}} {{$response['product']}}</p>
    </a>
</div>

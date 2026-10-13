@extends('layout/main')
@section('page_title',  trans('admin.home'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{__('admin.dashboard')}}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.dashboard')}}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-sm-12">
                    <!-- New Order -->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['newOrder']}}</h3>
                            <p>{{__('admin.new-order')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-cart-plus"></i></div>
                        <a href="{{route('admin.order.index',['_os'=>$defaultStatus])}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                    <!--Completed Order-->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['completedOrder']}}</h3>
                            <p>{{__('admin.complete-order')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-truck"></i></div>
                        <a href="{{route('admin.order.index',['_os'=>$orderStatus['delivered']])}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                    <!--Total Money Earned-->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{!! $dashboard['totalMoneyEarned'] !!} <small>{{$currency?$currency->code:config('custom.currency')}}</small></h3>
                            <p>{{__('admin.total-money-earned')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-hand-holding-usd"></i></div>
                        <a href="{{route('admin.report.sales-customer')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                    <!--Total Products-->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['totalProduct']}}</h3>
                            <p>{{__('admin.total-product')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-coins"></i></div>
                        <a href="{{route('admin.product.index')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <!-- Out of Stock Products -->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['outOfStock']}}</h3>
                            <p>{{__('admin.out-stock')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-layer-group"></i></div>
                        <a href="{{route('admin.product.index',['OOSFilter'=>1])}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <!-- Out of Stock Products -->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['poutOfStock']}}</h3>
                            <p>Partially Out Of Stock (Products)</p>
                        </div>
                        <div class="icon"><i class="fa fa-layer-group"></i></div>
                        <a href="{{route('admin.product.out-of-stock')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <!-- small box -->
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['totalCustomer']}}</h3>
                            <p>{{__('admin.customer-registration')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-users"></i></div>
                        <a href="{{route('admin.customer.index')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['totalContactForm']}}</h3>
                            <p>{{__('admin.dashboard_contact_form')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-comment"></i></div>
                        <a href="{{route('admin.contact-us.index')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="small-box" style="background-color: #D3DEE8">
                        <div class="inner">
                            <h3>{{$dashboard['totalWishList']}}</h3>
                            <p>{{__('admin.dashboard_wishlist')}}</p>
                        </div>
                        <div class="icon"><i class="fa fa-heart"></i></div>
                        <a href="{{route('admin.order.wishlist.index')}}" class="small-box-footer">{{__('admin.view-all')}} <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <!-- ./col -->
            </div>
            <!-- /.row -->

            <div class="row">
                <div class="col-md-4 col-sm-12 p-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-transparent">
                                <h3 class="card-title">{{__('admin.new-customer')}}</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <table class="table normal-table">
                                    <thead>
                                        <tr>
                                            <th>{{__('admin.name')}}</th>
                                            <th class="">{{__('admin.last_login')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dashboard['newCustomers'] as $record)
                                        <tr>
                                            <td>{{ucwords($record->first_name." ".$record->last_name)}} ({{$record->email}})</td>
                                            <td class="">{{$record->last_login?\Carbon\Carbon::parse($record->last_login)->format(config('custom.php_datetime_format')):''}}</td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer text-center">
                                <a href="{{route('admin.customer.index')}}" class="uppercase">{{__('admin.view-all')}}</a>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                    </div>
                    <div class="col-12">
                        <!-- PRODUCT LIST -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{__('admin.recently-add-product')}}</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <ul class="products-list product-list-in-card pl-2 pr-2">
                                    @forelse($dashboard['recentProduct'] as $record)
                                    <?php
                                    $productName     = $record->getProductDescription();
                                    $productCategory = $record->getProductCategories();
                                    $defaultImage    = $record->getDefaultImage();
                                    ?>
                                    <li class="item">
                                        <div class="product-img pl-2">
                                            <img src="{{asset(Storage::url($defaultImage->image_thumb_url))}}"  class="img-size-50">
                                        </div>
                                        <div class="product-info pl-2">
                                            <a href="{{route('admin.product.index',["id"=>$record->id])}}" class="product-title">{{$productName?ucfirst($productName->products_name):''}}
                                                <span class="badge badge-warning float-right">{{$record->product_sale_price}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></span></a>
                                            <span class="product-description">
                                                @foreach($productCategory as $category)
                                                <?php
                                                $categoryDetail  = $category->getCategory();
                                                if ($categoryDetail)
                                                {
                                                    $categoryDescription = $categoryDetail->getCategoryDescription();
                                                    if ($categoryDescription)
                                                    {
                                                        echo $categoryDescription->categories_name . "<br/>";
                                                    }
                                                }
                                                ?>
                                                @endforeach
                                            </span>
                                        </div>
                                    </li>
                                    @empty
                                    @endforelse
                                </ul>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer text-center">
                                <a href="{{route('admin.product.index')}}" class="uppercase">{{__('admin.view-all')}}</a>
                            </div>
                            <!-- /.card-footer -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <div class="col-md-8">
                    <!-- TABLE: LATEST ORDERS -->
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">{{__('admin.new-order')}}</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <table class="table normal-table">
                                <thead>
                                    <tr>
                                        <th class="">{{__('admin.order_no')}}</th>
                                        <th>{{__('admin.customer-name')}}</th>
                                        <th>{{__('admin.product-name')}}</th>
                                        <th class="text-center">{{__('admin.total-price')}}</th>
                                        <th class="text-center">{{__('admin.status')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dashboard['newOrders'] as $record)
                                    <?php
                                    $user               = $record->getUser();
                                    $orderProductDetail = $record->getOrderProductDetail();
                                    $orderStatus        = $record->getOrderStatus();
                                    $orderStatus        = $orderStatus ? $orderStatus->order_status : '';
                                    ?>
                                    <tr>
                                        <td class="">{{$record->id}}</td>
                                        <td>{{$user?ucfirst($user->first_name)." ".ucfirst($user->last_name):''}} ({{$user->email}})</td>
                                        <td>
                                            @foreach($orderProductDetail as $orderProduct)
                                            {{ucfirst($orderProduct)}} <br>
                                            @endforeach
                                        </td>
                                        <td class="text-center">{{Helper::formatNumber($record->order_total)}} <small>{{$currency?$currency->code:config('custom.currency')}}</small></td>
                                        <td class="text-center "><span class="badge {{$badgeColor[$record->order_status]}}">{{$orderStatus}}</span></td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{route('admin.order.index')}}" class="uppercase">{{__('admin.view-all')}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('layout/datatable')
@endsection
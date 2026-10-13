@extends('layout/main')
@section('page_title',  trans('admin.coupon'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.coupon-user')}}{{" (" .$user->username." )"}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.coupon.index')}}">{{__('admin.coupon-user')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.send-coupon')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <form method="post" action="{{route('admin.master.coupon.send-coupon')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <input type="hidden" name="user_id" value="{{$user->id}}">
                            <input type="hidden" name="page" value="{{$redirectPage}}">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <?php
                                    $userData   = str_replace("#username", $user->username, $couponDesc);
                                    ?>
                                    <input type="hidden" name="username" value="{{$user->username}}">
                                    <label for="name" class="control-label">{{__('admin.code')}}</label><span class="required"></span>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" id="coupon_id" data-dropdown-css-class="{{config('custom.select2-css')}}" name="coupon_id">
                                        @foreach($coupon as $val)
                                        <?php
                                        $userData   = str_replace("#username#", $user->first_name, $couponDesc);
                                        $couponCode = str_replace("#coupon-code#", $val->coupon_code, $userData);
                                        $from       = \Carbon\Carbon::parse($val->from_date)->format(config('custom.php_date_format'));
                                        $to         = \Carbon\Carbon::parse($val->to_date)->format(config('custom.php_date_format'));
                                        $startDate  = str_replace("#from-date#", $from, $couponCode);

                                        $today      = time();
                                        $exp        = strtotime($val->to_date);
                                        $difference = $exp - $today;
                                        $days       = floor($difference / 86400);
//                                        $toDate     = str_replace("#to-date", $days, $startDate);
                                        $toDate     = str_replace("#end-date#", $to, $startDate);
//                                        $toDate = str_replace("#to-date", $to, $startDate);
                                        ?>
                                        <option value="{{$val->id}}"  coupon-end ="{{$toDate}}"  from-date="{{$from}}" to-date="{{$to}}" {{ (collect(old('coupon_id'))->contains($val->id)) ? 'selected':'' }}>{{ucfirst($val->coupon_code)}}</option>
                                        @endforeach
                                    </select>
                                    @error('coupon_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                    @error('user_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                    @error('username')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.from_date')}}</label>
                                    <input class="form-control float-input length-10" readonly="" placeholder="{{__('admin.from_date')}}" type="text" id="from_date">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.to_date')}}</label>
                                    <input class="form-control float-input length-10" readonly="" placeholder="{{__('admin.to_date')}}" type="text" id="to_date">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.coupon-description')}}</label> <span class="help-block text-danger"> ({{__('admin.coupon-description-note')}})</span>
                                    <textarea  class="textarea form-control" name="coupon_desc" placeholder="{{__('admin.coupon-description')}} "  id="coupon_desc" rows="4" cols="300">{!! $couponDesc !!}</textarea>
                                    @error('coupon_desc')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.report.sales-customer')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.send-coupon')}}">{{__('admin.send-coupon')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
@include('layout/ckeditor')
<script>
    $(document).ready(function ()
    {
        setTextAreaValue();

        $("#coupon_id").change(function ()
        {
            setTextAreaValue();
        });
        function setTextAreaValue()
        {
            var data = $("#coupon_id").find('option:selected');
            var coupon = data.attr('coupon-end');
            $("#coupon_desc").val(coupon);
            $("#from_date").val(data.attr('from-date'));
            $("#to_date").val(data.attr('to-date'));
//            
        }
    });
</script>
@endsection

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
                    <h1>{{__('admin.coupon')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.coupon.index')}}">{{__('admin.coupon')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.master.coupon.update',['id'=>$singleRecord->id])}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.code')}}</label><span class="required"></span>
                                    <input class="form-control field-validate {{ $errors->has('coupon_code') ?'is-invalid':'' }}" placeholder="{{__('admin.code')}}"  id="code" name="coupon_code" type="text" value="{{old('coupon_code',$singleRecord->coupon_code)}}">
                                    @error('coupon_code')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.coupon-percentage')}}</label>
                                    <span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.coupon-percentage-note')}}</small>
                                    <input class="form-control field-validate float-input length-2 {{ $errors->has('coupon_discount') ?'is-invalid':'' }}" placeholder="{{__('admin.coupon-percentage')}}" name="coupon_discount" type="text" value="{{old('coupon_discount',$singleRecord->coupon_discount)}}">
                                    @error('coupon_discount')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class=" control-label">{{__('admin.coupon-start')}}</label><span class="required"></span>
                                    <input readonly="" data-toggle="datetimepicker" data-target=".start-date" class="start-date form-control datetimepicker-input" placeholder="{{__('admin.coupon-start')}}" name="from_date" type="text" value="{{old('from_date')}}">
                                    @error('from_date')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class=" control-label">{{__('admin.coupon-end')}}</label><span class="required"></span>
                                    <input readonly="" data-toggle="datetimepicker" data-target=".end-date" class="end-date form-control datetimepicker-input" placeholder="{{__('admin.coupon-end')}}" name="to_date" type="text" value="{{old('to_date')}}">
                                    @error('to_date')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row hidden">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.coupon-desc')}}</label><span class=""></span>
                                    <textarea class="form-control {{ $errors->has('coupon_description') ?'is-invalid':'' }}" placeholder="{{__('admin.coupon-desc')}}" rows="5" id="description" name="coupon_description" cols="50">{{old('coupon_description',$singleRecord->coupon_description)}}</textarea>
                                    @error('coupon_description')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.status')}}</label><span class="required"></span>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active',$singleRecord->is_active))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_active')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div class="form-group text-center"> 
                                    <label>{{__('admin.allow-multiple')}}</label>
                                    <div class="icheck-primary ">
                                        <input name="allow_multiple" type="checkbox" value="1" id="allow_multiple" {{ (collect(old('allow_multiple',$singleRecord->allow_multiple))->contains("1")) ? 'checked':'' }}>                                        
                                        <label for="allow_multiple">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div class="form-group text-center"> 
                                    <label>{{__('admin.all-allow')}}</label>
                                    <div class="icheck-primary ">
                                        <input name="allow_all" type="checkbox" value="1" id="allow_all" {{ (collect(old('allow_all',$singleRecord->allow_all))->contains("1")) ? 'checked':'' }}>                                        
                                        <label for="allow_all">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @if(false)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="control-label">Discount Type  </label><span class="required"></span>
                                    <select name="discount_type" class="form-control select2 {{config('custom.select2-css')}}"">
                                        <option value="fixed_cart" selected="">Cart Discount</option>
                                        <option value="percent">Cart % Discount</option>
                                        <option value="fixed_product">Product Discount</option>
                                        <option value="percent_product">Product % Discount</option>
                                    </select>
                                    @error('discount_type')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.coupon.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
<script src="{{asset('plugins/moment/moment.min.js')}}" crossorigin="anonymous"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-4.js')}}" crossorigin="anonymous"></script>
<script>
$(document).ready(function ()
{
    /*
     * Datepicker
     */
    startDateTime();
    endDateTime();
    $(".start-date").on('change.datetimepicker', function (e)
    {
        $(".end-date").datetimepicker("destroy");
        $(".end-time").datetimepicker("destroy");
        $(".end-date").val("");
        $(".end-time").val("");
        endDateTime();
        $(".end-date").datetimepicker('minDate', e.date);
    });

});
function startDateTime()
{
    $(".start-date").datetimepicker({
        format: "{{config('custom.js_date_format')}}",
        ignoreReadonly: true,
        buttons: {
            showClear: false,
        },
        icons: {
            clear: 'fa fa-trash',
        },
    });
    $(".start-date").val("{{old('from_date',date('d-m-Y',strtotime($singleRecord->from_date)))}}");
}
function endDateTime()
{
    $(".end-date").datetimepicker({
        format: "{{config('custom.js_date_format')}}",
        minDate: moment("{{date('Y-m-d')}}"),
        ignoreReadonly: true,
        buttons: {
            showClear: false,
        },
        icons: {
            clear: 'fa fa-trash',
        }
    });
    $(".end-date").val("{{old('to_date',date('d-m-Y',strtotime($singleRecord->to_date)))}}");
}
</script>
@endsection

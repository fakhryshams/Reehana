@extends('layout/main')
@section('page_title',  trans('admin.country'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.country')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.master.country.index')}}">{{__('admin.country')}}</a></li>
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
                <form method="post" action="{{route('admin.master.country.update',['id' => $singleRecord->id])}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $record = $singleRecord->getCountryDescription($language->id);
                            @endphp
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.country')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="country_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('country_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.country')}} ({{ucfirst($language->name)}})" value="{{old('country_'.$language->id,$record?$record->country_name:'')}}">
                                    @error('country_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.delivery_time')}} ({{ucfirst($language->name)}})</label><span class="required"></span>
                                    <input name="delivery_time_{{$language->id}}" type="text" class="form-control length-30 {{ $errors->has('delivery_time_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.delivery_time')}} ({{ucfirst($language->name)}})" value="{{old('delivery_time_'.$language->id,$record?$record->delivery_time:'')}}">
                                    @error('delivery_time_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.country_code')}}</label><span class="required"></span>
                                    <input name="country_code" type="text" class="text-uppercase form-control length-4 {{ $errors->has('shipping_charge') ?'is-invalid':'' }}" placeholder="{{__('admin.country_code')}}" value="{{old('country_code',$singleRecord->country_code)}}">
                                    @error('country_code')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.currency_code')}}</label><span class="required"></span>
                                    <input name="currency_code" type="text" class="text-uppercase form-control length-4 {{ $errors->has('shipping_charge') ?'is-invalid':'' }}" placeholder="{{__('admin.currency_code')}}" value="{{old('currency_code',$singleRecord->currency_code)}}">
                                    @error('currency_code')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.currency_rate')}}</label><span class="required"></span>
                                    <input name="currency_rate" type="text" class="form-control number-input length-100 {{ $errors->has('currency_rate') ?'is-invalid':'' }}" placeholder="{{__('admin.currency_rate')}}" value="{{old('currency_rate',$singleRecord->currency_rate)}}">
                                    @error('currency_rate')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.shipping-charge')}}</label><span class="required"></span>
                                    <input name="shipping_charge" type="text" class="form-control number-input length-10 {{ $errors->has('shipping_charge') ?'is-invalid':'' }}" placeholder="{{__('admin.shipping-charge')}}" value="{{old('shipping_charge',$singleRecord->shipping_charge)}}">
                                    @error('shipping_charge')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Free Shipping Amount</label>
                                    <input name="free_shipping_amount" type="text" class="form-control number-input length-10 {{ $errors->has('free_shipping_amount') ?'is-invalid':'' }}" placeholder="Free Shipping Amount" value="{{old('free_shipping_amount',$singleRecord->free_shipping_amount)}}">
                                    @error('free_shipping_amount')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.status')}}</label><span class="required"></span>
                                    <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                        @foreach($isActive as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_active',$singleRecord->is_active))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_active')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group"> 
                                    <label>{{__('admin.sorting_order')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small>
                                    <input type="text" readonly="" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$singleRecord->sorting!=0?$singleRecord->sorting:$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(false)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="is_default" id="default" {{ (collect(old('is_default',$singleRecord->is_default))->contains("1")) ? 'checked':'' }} value="1">
                                        <label for="default">
                                            {{__('admin.is_default_country')}} 
                                        </label>
                                        <small class="{{config('custom.text-note-css')}}">({{__('admin.default_country_note')}})</small>
                                    </div>
                                    @error('is_default')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.country.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script>
    $(function () {
        $("input[name=currency_code]").blur(function () {
            $.ajax({
                type: "POST",
                url: "{{route('admin.master.country.currency-rate')}}",
                beforeSend: function () {
                    // Handle the beforeSend event
                    $("#pageloader").fadeIn();
                },
                complete: function () {
                    // Handle the complete event
                    $("#pageloader").fadeOut();
                },
                data: {currencyCode: $(this).val(), '_token': "{{ csrf_token() }}"},
                success: function (res)
                {
                    if (res)
                    {
                        $("input[name=currency_rate]").val(res);
                    }
                }
            });
        });
    });
</script>
@include('layout/select2')
@endsection

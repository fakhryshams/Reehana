@extends('layout/main')
@section('page_title',  trans('admin.customer_address'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.customer_address')}} {{$user?'('.ucfirst($user->first_name).' '.ucfirst($user->last_name).')':''}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.index')}}">{{__('admin.customer')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.customer.address.index',['id'=>$user->id])}}">{{__('admin.customer_address')}}</a></li>
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
                <form method="post" action="{{route('admin.customer.address.update',['id'=>$singleRecord->id])}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{old('user_id',$user->id)}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_title')}}</label> <span class="required"></span>
                                    <input type="text" name="address_title" placeholder="{{__('admin.customer_address_title')}}" class="form-control {{ $errors->has('address_title') ?'is-invalid':'' }}" value="{{old('address_title',$singleRecord->address_title)}}">
                                    @error('address_title')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_first_name')}}</label> <span class="required"></span>
                                    <input type="text" name="first_name" placeholder="{{__('admin.customer_address_first_name')}}" class="form-control {{ $errors->has('first_name') ?'is-invalid':'' }}" value="{{old('first_name',$singleRecord->first_name)}}">
                                    @error('first_name')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_last_name')}}</label> <span class="required"></span>
                                    <input type="text" name="last_name" placeholder="{{__('admin.customer_address_last_name')}}" class="form-control {{ $errors->has('last_name') ?'is-invalid':'' }}" value="{{old('last_name',$singleRecord->last_name)}}">
                                    @error('last_name')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_email')}}</label> <span class="required"></span>
                                    <input type="text" name="email" placeholder="{{__('admin.customer_address_email')}}" class="form-control {{ $errors->has('email') ?'is-invalid':'' }}" value="{{old('email',$singleRecord->email)}}">
                                    @error('email')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.customer_address_country')}} </label> <span class="required"></span>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="country_id" id="countryId">
                                        @foreach($country as $country)
                                        <?php
                                        $countryName = $country->getCountryDescription();
                                        ?>
                                        <option value="{{$country->id}}" {{ (collect(old('country_id',$singleRecord->country_id))->contains($country->id)) ? 'selected':'' }}>{{$countryName?ucfirst($countryName->country_name):''}}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group" >
                                    <label>{{__('admin.customer_address_city')}} </label> <span class="required"></span>
                                    <div id="cityId">
                                        <input type="text" name="city" placeholder="{{__('admin.customer_address_city')}}" class="form-control {{ $errors->has('city') ?'is-invalid':'' }}" value="{{old('city',$singleRecord->city)}}">
                                    </div>
                                    @error('city')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                    @error('city_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if(false)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.customer_address_area')}}</label> <span class="required"></span>
                                    <input type="text" name="area" placeholder="{{__('admin.customer_address_area')}}" class="form-control {{ $errors->has('area') ?'is-invalid':'' }}" value="{{old('area',$singleRecord->area)}}">
                                    @error('area')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.customer_address_street')}}</label> <span class="required"></span>
                                    <input type="text" name="street" placeholder="{{__('admin.customer_address_street')}}" class="form-control {{ $errors->has('street') ?'is-invalid':'' }}" value="{{old('street',$singleRecord->street)}}">
                                    @error('street')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_house_number')}}</label> <span class="required"></span>
                                    <input type="text" name="house_number" placeholder="{{__('admin.customer_address_house_number')}}" class="form-control {{ $errors->has('house_number') ?'is-invalid':'' }}" value="{{old('house_number',$singleRecord->house_number)}}">
                                    @error('house_number')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_floor')}}</label>
                                    <input type="text" name="floor" placeholder="{{__('admin.customer_address_floor')}}" class="form-control {{ $errors->has('floor') ?'is-invalid':'' }}" value="{{old('floor',$singleRecord->floor)}}">
                                    @error('floor')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_apartment')}}</label>
                                    <input type="text" name="appartment" placeholder="{{__('admin.customer_address_apartment')}}" class="form-control {{ $errors->has('appartment') ?'is-invalid':'' }}" value="{{old('appartment',$singleRecord->appartment)}}">
                                    @error('appartment')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.customer_address_phone')}}</label> <span class="required"></span>
                                    <input type="text" name="phone" placeholder="{{__('admin.customer_address_phone')}}" class="form-control number-input {{ $errors->has('phone') ?'is-invalid':'' }}" value="{{old('phone',$singleRecord->phone)}}">
                                    @error('phone')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"> 
                                    <label>{{__('admin.customer_address_detail')}}</label>
                                    <input type="text" name="address_detail" placeholder="{{__('admin.customer_address_detail')}}" class="form-control {{ $errors->has('address_detail') ?'is-invalid':'' }}" value="{{old('address_detail',$singleRecord->address_detail)}}">
                                    @error('address_detail')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.customer_address_default')}} <span class="required"></span></label>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_default">
                                        @foreach($isDefault as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('is_default',$singleRecord->is_default))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('is_default')
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
                                <a href="{{route('admin.customer.address.index',['id'=>$user->id])}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
<script>
    $(document).ready(function ()
    {
        $('#countryId').on('change', function ()
        {
            var countryId = $(this).val();
            getCity(countryId);
        });
        getCity($("[name=country_id]").find("option:selected").val());
    });

    function getCity(countryID)
    {
        if (countryID)
        {
            $.ajax({
                type: "POST",
                url: "{{route('admin.customer.address.get-city')}}",
                data: {'countryID': countryID, '_token': "{{ csrf_token() }}"},
                success: function (res)
                {
                    if (res)
                    {
                        $("#cityId").empty();
                        $("#cityId").append(res);
                        if ($("select[name=city_id]").length)
                        {
                            $("select[name=city_id]").val("{{old('city_id',$singleRecord->city_id)}}");
                            $('select[name=city_id]').trigger('change');
                        }
                        else
                        {
                            $("input[name=city]").val("{{old('city',$singleRecord->city)}}");
                        }
                    }
                    else
                    {
                        $("#cityId").empty();
                    }
                }
            });
        }
        else
        {
            $("#cityId").empty();
        }
    }
</script>
@endsection
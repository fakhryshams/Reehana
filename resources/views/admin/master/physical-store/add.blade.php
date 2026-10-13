@extends('layout/main')
@section('page_title', trans('admin.physical-store'))
@section('content')

@include('layout/toaster')

<?php
$language = Helper::getLanguage();
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.physical-store') }}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.home.index') }}">{{ __('admin.home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.master.physical-store.index') }}">{{ __('admin.physical-store') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.add') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card {{ config('custom.card-primary') }}">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.add') }}</h3>
                </div>

                <form method="POST" action="{{ route('admin.master.physical-store.add') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{-- Store Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.store_name') }}</label><span class="required"></span>
                                    <input type="text"
                                        name="store_name"
                                        class="form-control {{ $errors->has('store_name') ? 'is-invalid' : '' }}"
                                        value="{{ old('store_name') }}"
                                        required
                                        placeholder="{{ __('admin.store_name') }}">
                                    @error('store_name')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Store Code --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.store_code') }}</label><span class="required"></span>
                                    <input type="text"
                                        name="store_code"
                                        class="form-control {{ $errors->has('store_code') ? 'is-invalid' : '' }}"
                                        value="{{ old('store_code') }}"
                                        required
                                        placeholder="{{ __('admin.store_code') }}">
                                    @error('store_code')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Address Line 1 --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.address_line_1') }}</label><span class="required"></span>
                                    <textarea name="address_line_1"
                                        class="form-control {{ $errors->has('address_line_1') ? 'is-invalid' : '' }}"
                                        rows="2" placeholder="{{ __('admin.address_line_1') }}" required>{{ old('address_line_1') }}</textarea>
                                    @error('address_line_1')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Address Line 2 --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.address_line_2') }}</label>
                                    <textarea name="address_line_2"
                                        class="form-control"
                                        rows="2" placeholder="{{ __('admin.address_line_2') }}">{{ old('address_line_2') }}</textarea>
                                </div>
                            </div>

                            {{-- Country --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.country') }}</label>
                                    <input type="hidden" name="is_city" class="is_city" value="">
                                    <select class="form-control select2" id="country_id" name="country_id">
                                        <option disabled selected value="">{{ __('admin.select_country') }}</option>
                                        @foreach($country as $record)
                                        @php $desc = $record->getCountryDescription($language->id); @endphp
                                        <option value="{{ $record->id }}"
                                            {{ old('country_id') == $record->id ? 'selected' : '' }}>
                                            {{ ucfirst($desc->country_name) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('country_id')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- City Dropdown --}}
                            <div class="col-md-6 city_list">
                                <div class="form-group">
                                    <label>{{ __('admin.area') }}</label>
                                    <select class="form-control select2 city_id" name="area">
                                        <option disabled selected value="">{{ __('admin.select_area') }}</option>
                                    </select>
                                    @error('area')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Manual City --}}
                            <div class="col-md-6 city_text d-none">
                                <div class="form-group">
                                    <label>{{ __('admin.city') }}</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label>{{ __('admin.block') }}</label>
                                <input type="text" name="block" class="form-control" value="{{ old('block') }}" placeholder="{{ __('admin.block') }}">
                            </div>

                            <div class="col-md-3">
                                <label>{{ __('admin.street') }}</label>
                                <input type="text" name="street" class="form-control" value="{{ old('street') }}" placeholder="{{ __('admin.street') }}">
                            </div>

                            {{-- Pincode --}}
                            <div class="col-md-3">
                                <label>{{ __('admin.pin_code') }}</label>
                                <input type="text"
                                    name="pin_code"
                                    class="form-control"
                                    value="{{ old('pin_code') }}"
                                    placeholder="{{ __('admin.pin_code') }}"
                                    maxlength="6"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);">

                            </div>

                            <div class="col-md-3">
                                <label>{{ __('admin.map_location') }}</label>
                                <input type="text"
                                    name="map"
                                    class="form-control {{ $errors->has('map') ? 'is-invalid' : '' }}"
                                    placeholder="Google Map URL or Lat,Lng (23.0225,72.5714)"
                                    value="{{ old('map') }}">
                                @error('map')
                                    <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.physical-store.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
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
    $(document).ready(function() {
        $("#country_id").change(function() {
            getCity();
        });

        function changeBillingCountry() {
            $("#billing_country_id").change(function() {
                getCity('billing', $(this).val());
            });
        }

        function getCity(type = '', countryId = '', cityId = '') {
            $.ajax({
                type: 'POST',
                url: "{{route('admin.master.physical-store.get_city')}}",
                data: {
                    'country_id': countryId ? countryId : $("#country_id").val(),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data) {
                        $('.city_id').html('');
                        $('.city_id').append(data);
                        $('.city_text').hide();
                        $('.city_text').val('');
                        $('.city_list').show();
                        if (oldCity) {
                            $('.city_id').val(oldCity);
                        }
                        $('.is_city').val(0);

                        $("input[name=city]").removeClass('required');
                        $(".city_text").removeClass('mandatory');
                    } else {
                        $("input[name=city]").val("");
                        $("input[name=city]").addClass('required');
                        $("input[name=city]").val("{{old('city')}}");
                        $('.is_city').val(1);
                        $('.city_id').html('');
                        $('.city_text').show();
                        $('.city_list').hide();
                    }
                }
            });
        }
    });
</script>
@endsection
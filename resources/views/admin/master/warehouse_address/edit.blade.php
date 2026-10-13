@extends('layout/main')
@section('page_title', trans('admin.warehouse-address'))
@section('content')

@include('layout/toaster')

<?php
$language = Helper::getLanguage();
$selectedArea = old('area', $singleRecord->area);
$hasAreaOptions = isset($city) && count($city) > 0;
$selectedAreaExistsInOptions = $hasAreaOptions
    ? $city->contains(function ($record) use ($selectedArea) {
        return (string) $record->id === (string) $selectedArea;
    })
    : false;
$useManualArea = old('city', null) !== null
    ? !old('area')
    : (!$selectedAreaExistsInOptions && !empty($singleRecord->area));
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.warehouse-address') }}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.home.index') }}">{{ __('admin.home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.master.warehouse-address.index') }}">{{ __('admin.warehouse-address') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card {{ config('custom.card-primary') }}">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.edit') }}</h3>
                </div>

                <form method="POST" action="{{ route('admin.master.warehouse-address.update', $singleRecord->id) }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{-- Country --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.country') }}</label>

                                    <input type="hidden" name="is_city" class="is_city" value="{{ $useManualArea ? 1 : 0 }}">

                                    <select class="form-control select2" id="country_id" name="country_id">
                                        <option value="" disabled {{ $singleRecord->country_id ? '' : 'selected' }}>{{ __('admin.select_country') }}</option>
                                        @foreach($country as $record)
                                            @php $desc = $record->getCountryDescription($language->id); @endphp
                                            <option value="{{ $record->id }}" data-country-code="{{ strtoupper($record->country_code ?? '') }}" {{ old('country_id', $singleRecord->country_id) == $record->id ? 'selected' : '' }}>{{ ucfirst($desc->country_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('country_id')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            {{-- Area Dropdown --}}
                            <div class="col-md-6 city_list">
                                <div class="form-group">
                                    <label>{{ __('admin.area') }}</label>
                                    <select class="form-control select2 city_id" name="area">
                                        <option value="">{{ __('admin.select_area') }}</option>
                                        @if($hasAreaOptions)
                                            @foreach($city as $areaRecord)
                                                @php
                                                    $cityDescription = $areaRecord->getCityDescription($language->id);
                                                @endphp
                                                <option value="{{ $areaRecord->id }}" {{ (string) $selectedArea === (string) $areaRecord->id ? 'selected' : '' }}>
                                                    {{ $cityDescription->city_name ?? $areaRecord->id }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('area')<div class="text-red">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Manual Area Text --}}
                            <div class="col-md-6 city_text {{ $useManualArea ? '' : 'd-none' }}">
                                <div class="form-group">
                                    <label>{{ __('admin.area') }}</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $useManualArea ? $singleRecord->area : '') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label>{{ __('admin.block') }}</label>
                                <input type="text" name="block" class="form-control" value="{{ old('block', $singleRecord->block) }}" placeholder="{{ __('admin.block') }}" required>
                                @error('block')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label>{{ __('admin.street') }}</label>
                                <input type="text" name="street" class="form-control" value="{{ old('street', $singleRecord->street) }}" placeholder="{{ __('admin.street') }}" required>
                                @error('street')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label>{{ __('admin.number') }}</label>
                                <input type="text" name="number" class="form-control" value="{{ old('number', $singleRecord->number) }}" placeholder="{{ __('admin.number') }}" required>
                                @error('number')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mt-3">
                                <label>{{ __('admin.floor') }}</label>
                                <input type="text" name="floor" class="form-control" value="{{ old('floor', $singleRecord->floor) }}" placeholder="{{ __('admin.floor') }}" required>
                                @error('floor')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8 mt-3">
                                <label>{{ __('admin.address_detail') }}</label>
                                <textarea name="address_detail" class="form-control" placeholder="{{ __('admin.address_detail') }}" required>{{ old('address_detail', $singleRecord->address_detail) }}</textarea>
                                @error('address_detail')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.phone') }}</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $singleRecord->phone) }}" placeholder="{{ __('admin.phone') }}" required>
                                @error('phone')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.pin_code') }}</label>
                                <input type="text" name="pin_code" class="form-control" value="{{ old('pin_code', $singleRecord->pin_code) }}" placeholder="{{ __('admin.pin_code') }}" maxlength="6" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required>
                                @error('pin_code')<div class="text-red">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.master.warehouse-address.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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
        let oldCity = "{{ $selectedAreaExistsInOptions ? $selectedArea : old('area') }}";
        let manualCity = @json(old('city', $useManualArea ? $singleRecord->area : ''));
        const kuwaitCountryId = "{{ config('custom.CountryID_Kuwait', 2) }}";

        function isKuwaitSelected() {
            const selectedOption = $('#country_id option:selected');
            const selectedId = $('#country_id').val();
            const countryCode = String(selectedOption.data('country-code') || '').toUpperCase();

            return selectedId === kuwaitCountryId || countryCode === 'KW';
        }

        function syncAreaMode(hasDropdownData) {
            if (!isKuwaitSelected()) {
                $('.city_id').val('').prop('required', false);
                $("input[name=city]").val('').prop('required', false);
                $('.city_text').hide();
                $('.city_list').hide();
                $('.is_city').val(0);
                return;
            }

            if (hasDropdownData) {
                $('.city_text').hide();
                $('.city_list').show();
                $('.is_city').val(0);
                $('.city_id').prop('required', true);
                $("input[name=city]").removeClass('required');
                $(".city_text").removeClass('mandatory');
                $("input[name=city]").prop('required', false);
            } else {
                $("input[name=city]").val(manualCity || '');
                $("input[name=city]").addClass('required');
                $('.is_city').val(1);
                $('.city_text').show();
                $('.city_list').hide();
                $('.city_id').prop('required', false);
                $("input[name=city]").prop('required', true);
            }
        }

        $("#country_id").change(function() {
            if (isKuwaitSelected()) {
                getCity();
            } else {
                syncAreaMode(false);
            }
        });

        if ($("#country_id").val() && isKuwaitSelected() && $('.city_id option').length <= 1) {
            getCity('', $("#country_id").val());
        } else if ($("#country_id").val() && isKuwaitSelected() && $('.city_id option').length > 1) {
            syncAreaMode(true);
        } else {
            syncAreaMode(false);
        }

        function getCity(type = '', countryId = '', cityId = '') {
            $.ajax({
                type: 'POST',
                url: "{{route('web.user.get_city')}}",
                data: {
                    'country_id': countryId ? countryId : $("#country_id").val(),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data) {
                        $('.city_id').html('');
                        $('.city_id').append(data);
                        syncAreaMode(true);
                        if (oldCity) {
                            $('.city_id').val(oldCity);
                        }
                    } else {
                        $('.city_id').html('');
                        syncAreaMode(false);
                    }
                }
            });
        }
    });
</script>
@endsection

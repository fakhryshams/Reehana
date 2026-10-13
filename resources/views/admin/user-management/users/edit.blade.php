@extends('layout/main')
@section('page_title', trans('admin.user-management'))
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
                    <h1>{{ __('admin.user-management') }}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.home.index') }}">{{ __('admin.home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.user-management.index') }}">{{ __('admin.user-management') }}</a>
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

                <form method="POST" action="{{ route('admin.user-management.update', $user->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{--
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('admin.physical-store') }}</label><span class="required"></span>
                                    <select name="physical_store_id" class="form-control select2 required">
                                        <option value="">{{ __('admin.select_store') }}</option>
                                        @foreach($stores as $store)
                                        <option value="{{ $store->id }}"
                                            {{ old('physical_store_id', $user->physical_store_id) == $store->id ? 'selected' : '' }}>
                                        {{ $store->store_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('physical_store_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            --}}

                            {{-- First Name --}}
                            <div class="col-md-6">
                                <label>{{ __('admin.first_name') }}</label><span class="required"></span>
                                <input type="text" name="first_name" class="form-control required"
                                    value="{{ old('first_name', $user->first_name) }}" placeholder="{{ __('admin.first_name') }}">
                                @error('first_name')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div class="col-md-6">
                                <label>{{ __('admin.last_name') }}</label><span class="required"></span>
                                <input type="text" name="last_name" class="form-control required"
                                    value="{{ old('last_name', $user->last_name) }}" placeholder="{{ __('admin.last_name') }}">
                                @error('last_name')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.email') }}</label><span class="required"></span>
                                <input type="email" name="email" class="form-control required"
                                    value="{{ old('email', $user->email) }}" placeholder="{{ __('admin.email') }}">
                                @error('email')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mobile --}}
                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.mobile') }}</label><span class="required"></span>
                                <input type="text"
                                    name="mobile"
                                    class="form-control required {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                                    value="{{ old('mobile', $user->mobile) }}"
                                    placeholder="{{ __('admin.mobile') }}"
                                    maxlength="15"
                                    inputmode="numeric"
                                    pattern="[0-9]{8,15}"
                                    title="Please enter a valid mobile number (8 to 15 digits)"
                                    required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
                                @error('mobile')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.user-management.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
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

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
                        <li class="breadcrumb-item active">{{ __('admin.change_password') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card {{ config('custom.card-primary') }}">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.change_password') }}</h3>
                </div>

                <form method="POST" action="{{ route('admin.user-management.update-password', $user->id) }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            {{-- Password --}}
                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.new_password') }}</label><span class="required"></span>
                                <input type="password"
                                    name="password"
                                    class="form-control required {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                    placeholder="{{ __('admin.new_password') }}"
                                    autocomplete="new-password"
                                    minlength="8"
                                    pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}"
                                    title="Password must contain at least 8 characters, including uppercase, lowercase, number, and special character">
                                @error('password')
                                <div class="text-red">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div class="col-md-6 mt-3">
                                <label>{{ __('admin.password_confirmation') }}</label><span class="required"></span>
                                <input type="password"
                                    name="password_confirmation"
                                    class="form-control required"
                                    placeholder="{{ __('admin.password_confirmation') }}"
                                    autocomplete="new-password">
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
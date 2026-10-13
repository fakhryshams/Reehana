@extends('web/layout/main')
@section('page_title',  trans('web.register'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.register')}}">{{__('web.register')}}</a></span>
    </div>
</section>
<section class="register">
    <div class="container">
        <div class="registration-form grey-background">
            <div class="white-background">
                <h1>{!! __('web.signup_now') !!} </h1>


                <form id="register" action="{{route('web.user.register')}}" method="post">
                    @csrf
                    @honeypot
                    <input type="hidden" name="url" value="{{$url}}">
                    <div class="form-section row">
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="alphabetsWSpaces" id="pfirstname" name="first_name" class="required" value="{{old('first_name')}}" placeholder="{{__('web.type_first_name')}}">
                            <i class="error"></i>
                            @error('first_name')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="alphabetsWSpaces" id="plastname" name="last_name" value="{{old('last_name')}}"  class="required" placeholder="{{__('web.type_last_name')}}">
                            <i class="error"></i>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="email" data-type="email" id="pEmail" name="email"  class="required" value="{{old('email')}}" placeholder="{{__('web.enter_your_email')}}" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            @error('email')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="numbers" id="pMobile" name="mobile" class="required" placeholder="{{__('web.type_mobile_number')}}" value="{{old('mobile')}}"  minlength="{{config('custom_web.mobile_minlength',8)}}" maxlength="{{config('custom_web.mobile_maxlength',16)}}">
                            <i class="error"></i>
                            @error('mobile')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="password" data-type="password" id="pMobile" name="password" class="required" value="{{old('password')}}"  placeholder="{{__('web.set_password')}}">
                            <i class="error"></i>
                            @error('password')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="password" data-type="password" id="pMobile" name="password_confirmation" value="{{old('password_confirmation')}}" class="required" placeholder="{{__('web.retype_password')}}">
                            <i class="error"></i>
                            @error('password_confirmation')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="consent">
                            <div class="form-item col-md-6 col-sm-12 checkbox">
                                <input type="checkbox" name="t_and_c" class="required">
                                <label>{{__('web.by_creating_an_account')}}</label>
                            </div>

                            <span>
                                <a href="{{route('web.terms')}}" target="_blank">{{__('web.terms_conditions')}}</a> {{__('web.and')}} <a href="{{route('web.privacy-policy')}}" target="_blank">{{__('web.privacy_policy')}}</a>

                            </span>
                            <br>
                            @error('t_and_c')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif


                        </div>
                        <div class="col-md-12">
                            <div class="clear separator"></div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">{{__('web.register')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_js')
@include('web.layout.captcha_script')
@endsection


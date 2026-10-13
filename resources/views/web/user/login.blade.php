@extends('web/layout/main')
@section('page_title',  trans('web.login'))
@section('body_class',  'menubg')
@section('content')
@section('profile_class',  'login-page')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.login')}}">{{__('web.sign_in')}}</a></span>
    </div>
</section>

<section class="login">
    <div class="container">
        <div class="h-flex grey-background">
            <div class="login-form user-login white-background">
                <div class="title"><h4>{{__('web.login_to_your_account')}}</h4></div>
                
                    <div class="form-section row">
                   <form id="user-login" action="{{route('web.user.login')}}" method="post">
                    @csrf
                    <input type="hidden" name="url" value="{{$url}}">
                        <div class="form-item col-sm-12">
                            <input type="email" data-type="email" id="uEmail" name="email" class="required" placeholder="{{__('web.email')}}" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            @error('email')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-sm-12">
                            <input type="password" data-type="password" id="upassword" name="password" class="required" placeholder="{{__('web.password')}}">
                            <i class="error"></i>
                            @error('password')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <p class="error login_email_error">
                         

                        </p>
                        <div class="v-flex col-sm-12">
                            <div class="h-flex form-item">
                                <div class="checkbox flex1">
                                    <input type="checkbox" name="remember">
                                    <label>{{__('web.remember_me')}}</label>
                                </div>
                                <div class="forgot">
                                    <span class="link-button" id="resetPassword" data-toggle="modal" data-target="#resetPwdPopup">{{__('web.forgot_passsword')}}?</span>
                                </div>
                            </div>
                            <div class="action">
                                <button  type="submit" class="btn btn-primary">{{__('web.login')}}</button>
                            </div>
                            <div class="text-center mTop20">
                                OR
                            </div>
                        </div>
                        </form>
                        <div class="h-flex form-item actions col-sm-12 mTop20">
                            <button class="btn btn-secondary fb  fb-btn"><i class="facebook"></i> {{__('web.login_with_facebook')}}</button>
                            <button class="btn btn-secondary gl google-btn"><i class="google"></i> {{__('web.login_with_google')}}</button>
                        </div>
                    </div>
                
                <div class="separator"></div>
                <div class="text-center create-account">
                    {{__('web.dont_have_account')}} <a href="{{route('web.user.register')}}" class="link-button">{!! __('web.signup_now') !!}!</a>
                </div>
            </div>
            <!--if(session('cart'))-->
            <div class="guest grey-background">
                <div class="login-form">
                    <div class="title"><h4>{{__('web.guest_checkout')}}</h4></div>
                    <form id="guest-checkout" method="post"  action="{{route('web.user.guest_login')}}">
                        @csrf
                        <p>{{__('web.in_a_hurry')}}:</p>
                        <div class="form-section row">
                            <div class="form-item col-sm-12">
                                <input type="email" data-type="email" id="gEmail" name="gEmail" class="required" placeholder="{{__('web.email')}}*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                                <i class="error"></i>
                                @error('gEmail')
                                <div class="text-red"> {{ $message }}</div> 
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-primary">{{__('web.continue')}}</button>
                    </form>
                </div>
            </div>
            <!--endif-->
        </div>
    </div>
</section>
@endsection
@section('page_specific_html')
@include('web/user/forgot_password')
@endsection

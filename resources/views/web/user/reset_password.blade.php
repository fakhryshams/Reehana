@extends('web/layout/main')
@section('page_title',  trans('web.reset_password'))
@section('body_class',  'menubg')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="javascript:void(0)">{{__('web.reset_password')}}</a></span>
    </div>
</section>
<section class="register">
    <div class="container">
        <div class="registration-form grey-background">
            <div class="white-background">
                <form id="register" action="{{route('web.user.reset_password')}}"  method="post">
                    @csrf
                    <input type="hidden" name="id" value="{{$user_id}}">
                    <input type="hidden" name="token" value="{{$token}}">
                    <div class="form-item col-sm-6">
                        <label class="required">{{__('web.new_password')}}<b>*</b>:</label>
                        <input type="password" data-type="password" id="pMobile" name="password" class="required"  value="{{old('password')}}" placeholder="{{__('web.set_password')}}">
                        <i class="error"></i>
                        @error('password')
                        <div class="text-red"> {{ $message }}</div> 
                        @endif
                    </div>
                    <div class="clear"></div>
                    <div class="form-item col-sm-6">
                        <label class="required">{{__('web.retype_password_lable')}}<b>*</b>:</label>
                        <input type="password" data-type="password" id="pMobile" name="password_confirmation" class="required"  value="{{old('password_confirmation')}}" placeholder="{{__('web.retype_password')}}">
                        <i class="error"></i>
                        @error('password_confirmation')
                        <div class="text-red"> {{ $message }}</div> 
                        @endif
                    </div>
                    <div class="clear separator"></div>
                    <div class="form-item col-sm-12 form-actions">
                        <button class="btn btn-primary">{{__('web.reset_password')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_html')
@endsection
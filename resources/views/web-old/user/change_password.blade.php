@extends('web/layout/main')
@section('page_title',  trans('web.change_password'))
@section('body_class',  '')
@section('profile_class',  'profile-page')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_account')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.change_password')}}">{{__('web.change_password')}}</a></span>
    </div>
</section>
<section class="profile">
    <div class="container-fluid">
        <div class="grey-background">
            <div class="mobile-white-background">
                @include('web/user/profile_menu')
                <div class="tab-content">
                    <div id="password" class="show">
                        <div class="title">
                            <h1>{{__('web.change_password')}}</h1>
                        </div>
                        <div class="form-section row edit-mode">
                            <form  id="update-password" action="{{route('web.user.change_password')}}"  method="post">
                                @csrf
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.confirm_password')}}<span>*</span>:</label>
                                    <input type="password" data-type="password" id="cPassword1" name="old_password" value="{{old('old_password')}}" class="required" placeholder="{{__('web.type_password_here')}}">
                                    <i class="error"></i>
                                    @error('old_password')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="clear"></div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.type_new_password')}}<span>*</span>:</label>
                                    <input type="password" data-type="password" id="cPassword2" name="password" class="required" value="{{old('password')}}" placeholder="{{__('web.type_new_password_here')}}">
                                    <i class="error"></i>
                                    @error('password')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="clear"></div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.retype_new_password')}}<span>*</span>:</label>
                                    <input type="password" data-type="password" id="cPassword3" name="password_confirmation" class="required" placeholder="{{__('web.retype_password_here')}}">
                                    <i class="error"></i>
                                    @error('password_confirmation')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="form-actions col-sm-12">
                                    <div class="separator"></div>
                                    <button class="btn btn-primary">{{__('web.change_password')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
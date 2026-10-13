@extends('web/layout/main')
@section('page_title',  trans('web.profile'))
@section('body_class',  '')
@section('profile_class',  'profile-page')
@section('content')
<section class="breadcrumbs sm-hidde   ">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_account')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{route('web.user.profile')}}">{{__('web.my_profile')}}</a></span>
    </div>
</section>
<section class="profile">
    <div class="container-fluid">
        <div class="grey-background">
            <div class="mobile-white-background">
                @include('web/user/profile_menu')
                <div class="tab-content">
                    <div id="profile" class="show">
                        <div class="title">
                            <h1>{{__('web.profile')}}</h1>
                        </div>

                        @if(session()->has('profile_success'))
                        <div class="alert alert-success" >
                            {{ session()->get('profile_success') }}
                        </div>
                        @endif
                        @if(session()->has('profile_error'))
                        <div class="alert alert-danger" >
                            {{ session()->get('profile_error') }}
                        </div>
                        @endif

                        <form id="register"action="{{route('web.user.update_profile')}}"  method="post">
                            @csrf
                            <div id="user-details" class="form-section view-mode row">
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.first_name')}}<span>*</span>: <span class="value">{{ucfirst(Auth::user()->first_name)}}</span></label>
                                    <input type="text" data-type="alphabetsWSpaces" id="pfirstname" name="first_name" class="required" value="{{old('first_name',Auth::user()->first_name)}}" placeholder="{{__('web.type_first_name')}}">
                                    <i class="error"></i>
                                    @error('first_name')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.last_name')}}<span>*</span>: <span class="value">{{ucfirst(Auth::user()->last_name)}}</span></label>
                                    <input type="text" data-type="alphabetsWSpaces" id="plastname" name="last_name" class="required" value="{{old('last_name',Auth::user()->last_name)}}" placeholder="{{__('web.type_last_name')}}">
                                    <i class="error"></i>
                                    @error('last_name')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="clear"></div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.date_of_birth')}}<b>*</b>: <span class="value">{{Auth::user()->birth_date ? date("d-m-Y", strtotime(Auth::user()->birth_date)) : ''}}</span></label>
                                    <input type="text" data-type="date" id="dob" name="birth_date" class="required" placeholder="{{__('web.dob')}}"  value="{{old('birth_date',Auth::user()->birth_date?date("d-m-Y",strtotime(Auth::user()->birth_date)):'')}}">
                                    <i class="error"></i>
                                    @error('birth_date')
                                    <div class="text-red"> {{ $message }}</div>     
                                    @endif
                                </div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <?php
                                    $language = Helper::getLanguage();
                                    ?>
                                    <label class="required">{{__('web.country')}}<span>*</span>: <span class="value">{{Auth::user()->country?Auth::user()->getCountryDescription($language->id)->country_name : '-'}}</span></label>
                                    <select class="select-component untouched required"  name="country">
                                        <option value="0">{{__('web.select_country')}}</option>
                                        @foreach($country as $record)
                                        <?php
                                        $countryDescription = $record->getCountryDescription($language->id)
                                        ?>
                                        <option {{ (collect(old('country',Auth::user()->country))->contains($record->id)) ? 'selected':'' }} value="{{$record->id}}">{{ucfirst($countryDescription->country_name)}}</option>
                                        @endforeach
                                    </select>
                                    <i class="error"></i>
                                </div>
                                <div class="clear"></div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.email')}}<span>*</span>: <span class="value">{{Auth::user()->email}}</span></label>
                                    <input type="email" data-type="email" id="pEmail" name="email" class="required" placeholder="{{__('web.enter_your_email')}}" value="{{old('email',Auth::user()->email)}}" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                                    <i class="error"></i>
                                    @error('email')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                                <div class="form-item col-md-6 col-sm-12">
                                    <label class="required">{{__('web.mobile')}}<span>*</span>: <span class="value">{{Auth::user()->mobile}}</span></label>
                                    <input type="text" data-type="numbers" id="pMobile" name="mobile" class="required" value="{{old('mobile',Auth::user()->mobile)}}" minlength="{{config('custom_web.mobile_minlength',8)}}" maxlength="{{config('custom_web.mobile_maxlength',12)}}">
                                    <i class="error"></i>
                                    @error('mobile')
                                    <div class="text-red"> {{ $message }}</div> 
                                    @endif
                                </div>
                            </div>
                            <div class="separator"></div>
                            <div class="form-actions">
                                <button class="btn btn-primary edit_btn edit-save" style="display:none" id="">{{__('web.update_profile')}}</button>
                                <span  class="btn btn-primary edit_btn2 edit-save" id="edit-save">{{__('web.edit_profile')}}</span>
                            </div>
                        </form> 
                    </div>
                </div>
            </div>
        </div>
    </div>  
</section>
@endsection
@section('page_specific_js')

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{asset('front/js/jquery.inputmask.min.js')}}"></script>
<script>
$(".edit_btn").hide();
$(function ()
{
    $("#edit-save").click(function (e)
    {
        $(".edit_btn2").hide();
        $(".edit_btn").show();
    });
    $("#dob").datepicker({
        dateFormat: 'dd-mm-yy',
        maxDate: new Date()
    }).on('change', function (ev)
    {
        $(this).parent().addClass("valid").removeClass("invalid").removeClass("mandatory");
    }).inputmask("99-99-9999");
<?php
if ($errors->has('birth_date'))
{
    ?>
        $("#user-details").removeClass("view-mode").addClass("edit-mode");
        $(".edit_btn2").hide();
        $(".edit_btn").show();
    <?php
}
?>

});
</script>
@endsection
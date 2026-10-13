@extends('web/layout/main')
@section('page_title',  trans('web.contact'))
@section('body_class',  'contact-page')
@section('content')
<section class="breadcrumbs">
    <div class="container">
        <span><a href="{{route('web.index')}}">{{__('web.home')}}</a></span>
        <span class="sep"></span>
        <span><a href="{{url()->current()}}">{{__('web.contact_us')}}</a></span>
    </div>
</section>
<section class="contact-us">
    <div class="container">
        <?php
//        echo "<pre>";
//        print_r($page->address);
//        print_r($pageDesc);
//        echo "</pre>";
//        die;
        ?>
        <div class="grey-background v-flex">
            <div class="white-background h-flex">
                <div class="col-md-6 contactInfo">
                    <h1>{!!__('web.contact_us_title')!!}</h1>
                    <div class="h-flex">
                        <div class="info">
                            <label>{{__('web.venue')}}</label>
                            <h4>{!! $page->address !!}</h4>
                        </div>
                        <div class="info">
                            <label>{{__('web.phone')}}</label>
                            <h4>{!! $page->phone !!}</h4>
                        </div>
                        <div class="info">
                            <label>{{__('web.email')}}</label>
                            <h4><a target="_blank" href="mailto:{{$page->email}}">{{$page->email}}</a></h4>
                        </div>
                        <div class="info">
                            <label>{{__('web.social')}} </label>
                            <ul class="share">
                                @if($page->whatsapp_url)
                                <li><a target="_blank" href="{{$page->whatsapp_url}}"><span class="whatsapp"></span></a></li>
                                @endif
                                @if($page->instagram_url)
                                <li><a target="_blank" href="{{$page->instagram_url}}"><span class="instagram"></span></a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 maps">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3478.0271576086993!2d47.97705351457636!3d29.3401998584511!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3fcf9b38fa59e1fb%3A0xba05d8150244a5e0!2sAl-Faiha%20Middle%20School%20For%20Girls!5e0!3m2!1sen!2sin!4v1607510678125!5m2!1sen!2sin" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                </div>
            </div>
            <div class="contact-form">
                <div class="mBot20">
                    <p>{{__('web.any_query')}}</p>
                    <p>{{__('web.for_any_query')}}</p>
                </div>
                <form id="contact-form" action="{{route('web.contact_us')}}"  method="post">
                    @csrf
                    @honeypot
                    <div class="form-section row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-item">
                                <label class="required">{{__('web.first_name')}}</label>
                                <input type="text" data-type="alphabetsWSpaces" name="first_name" class="required" placeholder="{{__('web.first_name')}}*"  value="{{old('first_name')}}">
                                <i class="error"></i>
                                @error('first_name')
                                <div class="text-red"> {{ $message }}</div> 
                                @endif
                            </div>
                            <div class="form-item">
                                <label class="required">{{__('web.email')}}</label>
                                <input type="email" data-type="email" name="email" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$" value="{{old('email')}}" class="required" placeholder="{{__('web.your_email')}}*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                                <i class="error"></i>
                                @error('email')
                                <div class="text-red"> {{ $message }}</div> 
                                @endif
                            </div>
                            <div class="form-item">
                                <label class="required">{{__('web.mobile_no')}}</label>
                                <input type="text" data-type="numbers" name="mobile" class="required numberonly" placeholder="{{__('web.phone')}}*"  minlength="{{config('custom_web.mobile_minlength',8)}}" maxlength="{{config('custom_web.mobile_maxlength',12)}}"  value="{{old('mobile')}}">
                                <i class="error"></i>
                                @error('mobile')
                                <div class="text-red"> {{ $message }}</div> 
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-item">
                                <label class="required">{{__('web.last_name')}}</label>
                                <input type="text" data-type="alphabetsWSpaces" name="last_name" class="required" value="{{old('last_name')}}" placeholder="{{__('web.last_name')}}*">
                                <i class="error"></i>
                                @error('last_name')
                                <div class="text-red"> {{ $message }}</div> 
                                @endif
                            </div>
                            <div class="form-item">
                                <label class="required">{{__('web.your_message')}}</label>
                                <textarea id="message" name="message" class="required" placeholder="{{__('web.type_your_message')}}*"></textarea>
                                <i class="error"></i>
                            </div>
                        </div>
                        <div class="clear"></div>

                        <div class="form-item col-md-2">
                            <p class="refereshrecapcha"></p>
                            @if(false)
                            <div class="g-recaptcha" data-sitekey="6LcP4bUaAAAAAMV3a3NgKpf7LVLTZ6HosX4vUj4K" data-callback="correctCaptcha"></div>
                            @endif
                        </div>
                        <div class="form-item col-md-10">
                            <input type="text" name="captcha" placeholder="{{__('web.enter_captcha')}}*">
                            @error('captcha')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>

                        <div class="clear"></div>
                        <div class="form-item col-sm-12 form-actions">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_specific_js')
@if(false)
<script src="https://www.google.com/recaptcha/api.js"></script>
@endif
@include('web.layout.captcha_script')
@endsection


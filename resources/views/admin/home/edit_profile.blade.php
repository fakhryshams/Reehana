@extends('layout/main')
@section('page_title',  trans('admin.profile'))
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.profile')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.home.profile')}}">{{__('admin.profile')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.edit')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.home.update-profile')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>{{__('admin.name')}}</label><span class="required"></span>
                                    <input name="name" type="text" class="form-control {{ $errors->has('name') ?'is-invalid':'' }}" placeholder="{{__('admin.name')}}" value="{{old('name',$user->name)}}">
                                    @error('name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>{{__('admin.profile_picture')}}</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="profile_picture" class="custom-file-input">
                                            <label class="custom-file-label">{{__('admin.profile_picture')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('profile_picture')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="profile_preview">{{__('admin.current_profile_picture')}}</label>
                                <div class="text-center">
                                    <img class="logo-table text-center" src="{{Helper::getUserProfilePicture()}}" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'"/></span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.home.profile')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
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
@include('layout/image_preview')
<script src="{{asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
<script>
                                        $(function ()
                                        {
                                            bsCustomFileInput.init();
                                            $("input[type=file]").change(function ()
                                            {
                                                readURL(this);
                                            });
                                        });

                                        function readURL(input)
                                        {
                                            if (input.files && input.files[0])
                                            {
                                                var reader = new FileReader();

                                                reader.onload = function (e)
                                                {
                                                    $('.logo-table').attr('src', e.target.result);
                                                    $(".profile_preview").text('{{__("admin.updated_profile_picture")}}')
                                                }

                                                reader.readAsDataURL(input.files[0]);
                                            }
                                        }


</script>

@endsection

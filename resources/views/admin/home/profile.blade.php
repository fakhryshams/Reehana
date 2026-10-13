@extends('layout/main')
@section('page_title',  trans('admin.profile'))
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.profile')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.profile')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- /.row -->
            <div class="row">
                <div class="col-12">
                    <div class="card {{config('custom.card-primary')}}">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img onclick="showDetails(this.src)" class="img-circle elevation-3 profile-user-img" src="{{Helper::getUserProfilePicture()}}" alt="{{__('admin.profile_pic_alt')}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" >
                            </div>
                            <h3 class="profile-username text-center">{{Auth::user()->name}}</h3>
                        </div>
                        <div class="card-footer">
                            <div class="btn-toolbar float-right">
                                <div class="btn-group mr-2">
                                    <a href="{{route('admin.home.edit-profile')}}" class="{{config('custom.btn-success-form')}}">{{__('admin.edit_profile')}}</a>
                                </div>
                                <div class="btn-group">
                                    <a href="{{route('admin.home.change-password')}}" class="{{config('custom.btn-primary-form')}}">{{__('admin.change_password')}}</a>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('layout/image_preview')
@endsection

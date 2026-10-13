<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{Helper::getAppName()?Helper::getAppName():''}} | {{__('admin.login')}}</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- icheck bootstrap -->
        <link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">
        <link rel="stylesheet" href="{{asset('custom/css/custom.css')}}">
        <!-- Google Font: Source Sans Pro -->
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
        <style>
            .page-logo
            {
/*                height: 100px;
                width: 100px;*/
            }
            .login-page, .register-page{
                background: #FFF;
            }
        </style>
    </head>
    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo">
                <a href="#"><img src="{{Helper::getAppLogo()?Helper::getAppLogo(): ''}}" alt="{{Helper::getAppName()?Helper::getAppName():''}}" class="page-logo"></a>
            </div>
            <!-- /.login-logo -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">{{__('admin.login_text')}}</p>
                    @include('layout/toaster')
                    @if($errors->has('email') || $errors->has('password'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{$errors->first('email')?$errors->first('email'):$errors->first('password')}}
                    </div>
                    @endif
                    <form action="{{route('admin.login')}}" method="post">
                        @csrf
                        <div class="input-group mb-3">
                            <input name="email" type="text" class="form-control {{ $errors->has('email') ?'is-invalid':'' }}" placeholder="{{__('admin.email_username')}}" value="{{old('email')}}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input name="password" type="password" class="form-control {{ $errors->has('password') ?'is-invalid':'' }}" placeholder="{{__('admin.password')}}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if(false)
                            <div class="col-8">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="remember">
                                    <label for="remember">
                                        {{__('admin.remember_me')}}
                                    </label>
                                </div>
                            </div>
                            @endif
                            <!-- /.col -->
                            <div class="col-8">
                                <p class="mt-2">
                                    <a href="{{route('admin.forgot-password')}}">{{__('admin.forgot_password')}}</a>
                                </p>
                            </div>
                            <div class="col-4">
                                <button type="submit" class="{{config('custom.btn-primary-form')}} btn-block">{{__('admin.login')}}</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                    @if($socialLogin=false)                        
                    <div class="social-auth-links text-center mb-3">
                        <p>- OR -</p>
                        <a href="#" class="btn btn-block btn-primary">
                            <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                        </a>
                        <a href="#" class="btn btn-block btn-danger">
                            <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                        </a>
                    </div>
                    <!-- /.social-auth-links -->
                    @endif
                    @if(false)
                    <p class="mb-0">
                        <a href="#">{{__('admin.forgot_password')}}</a>
                    </p>
                    @endif
                    @if($newUser = false)
                    <p class="mb-0">
                        <a href="register.html" class="text-center">Register a new membership</a>
                    </p>
                    @endif
                </div>
                <!-- /.login-card-body -->
            </div>
        </div>
        <!-- /.login-box -->
        <!-- jQuery -->
        <script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <!-- AdminLTE App -->
        <script src="{{asset('dist/js/adminlte.min.js')}}"></script>
    </body>
</html>

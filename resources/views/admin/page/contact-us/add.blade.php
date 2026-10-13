@extends('layout/main')
@section('page_title',  trans('admin.contact_us'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.contact_us')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.page.contact-us.add')}}">{{__('admin.contact_us')}}</a></li>

                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                @php
                if($contactUs)
                {
                $path = 'admin.page.contact-us.update';
                }
                else
                {
                $path = 'admin.page.contact-us.add';
                }
                @endphp
                <form method="post" action="{{route($path)}}" enctype="multipart/form-data">
                    @csrf
                    <input type='hidden' name='ID' value='{{$contactUs?$contactUs->id:''}}'>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.address')}}</label> <span class="required"></span> 
                                    <textarea name="address" required="" class="form-control {{ $errors->has('address') ?'is-invalid':'' }}" placeholder="address">{{$contactUs? $contactUs->address : ''}}</textarea>
                                    @error('address')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.phone')}}</label> <span class="required"></span> 
                                    <input type="text" name="phone" required="" class="form-control {{ $errors->has('phone') ?'is-invalid':'' }}" placeholder="{{__('admin.phone')}}" value="{{$contactUs? $contactUs->phone : ''}}">
                                    @error('phone')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.email')}}</label> <span class="required"></span> 
                                    <input type="email" name="email" required="" class="form-control {{ $errors->has('email') ?'is-invalid':'' }}" placeholder="{{__('admin.email')}}" value="{{$contactUs? $contactUs->email : ''}}">
                                    @error('phone')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.whatsapp_url')}}</label> <span class="required"></span> 
                                    <input type="url" name="whatsapp_url" required="" class="form-control {{ $errors->has('whatsapp_url') ?'is-invalid':'' }}" placeholder="{{__('admin.whatsapp_url')}}" value="{{$contactUs? $contactUs->whatsapp_url : ''}}">
                                    @error('phone')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.instagram_url')}}</label> <span class="required"></span> 
                                    <input type="url" name="instagram_url" required="" class="form-control {{ $errors->has('instagram_url') ?'is-invalid':'' }}" placeholder="{{__('admin.instagram_url')}}" value="{{$contactUs? $contactUs->instagram_url : ''}}">
                                    @error('phone')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!--</div>-->
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
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
@include('layout/fileupload')
@include('layout/image_preview')
<script>
    function readURL(input, imageClass)
    {
        if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e)
            {
                $('.' + imageClass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
<script src="{{asset('plugins/ckeditor/adapters/jquery.js')}}"></script>
<script>
    $(function ()
    {
        $(".textarea").ckeditor();
        CKEDITOR.dtd.$removeEmpty.span = 0;
    });
</script>
@endsection
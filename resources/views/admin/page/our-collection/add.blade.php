@extends('layout/main')
@section('page_title',  trans('admin.our-collection'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.our-collection')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.page.our-collection.index')}}">{{__('admin.our-collection')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.add')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.add')}}</h3>
                </div>
                <form method="post" action="{{route('admin.page.our-collection.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        
                        <!--</div>-->
                        <div class="row">
                            <div class="col-md-6 p-0">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>{{__('admin.collection_title')}}</label>  <small class="{{config('custom.text-note-css')}}">( {{__('admin.character_length')}} 28 )</small><span class="required"></span>
                                        <input name="title" type="text" class="form-control length-28 {{ $errors->has('title') ?'is-invalid':'' }}" placeholder="{{__('admin.collection_title')}}" value="{{old('title')}}">
                                        @error('title')
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>{{__('admin.collection_description')}}</label>  <small class="{{config('custom.text-note-css')}}">( {{__('admin.character_length')}} 82 )</small><span class="required"></span>
                                        <input name="description" type="text" class="form-control length-82 {{ $errors->has('link') ?'is-invalid':'' }}" placeholder="{{__('admin.collection_description')}}" value="{{old('description')}}">
                                        @error('description')
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 p-0">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group"> 
                                        <label>{{__('admin.sorting_order')}}</label> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small><span class="required"></span>
                                        <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                        @error('sorting')
                                        <div class="text-red">{!! $message !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 p-0">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>{{__('admin.collection_link')}}</label> <small class="{{config('custom.text-note-css')}}">({{__('admin.url_hint')}})</small><span class="required"></span>
                                        <input name="link" type="url" class="form-control length-1000 {{ $errors->has('link') ?'is-invalid':'' }}" placeholder="{{__('admin.collection_link')}}" value="{{old('link')}}">
                                        @error('link')
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 p-0">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label>{{__('admin.status')}}</label> <span class="required"></span>
                                        <select class="form-control select2 select-is-feature {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_active">
                                            @foreach($isActive as $key=>$status)
                                            <option value="{{$key}}" {{ (collect(old('is_active'))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                            @endforeach
                                        </select>
                                        @error('is_active')
                                        <div class="text-red">{{ $message }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.collection_photo')}}</label> <small class="{{config('custom.text-note-css')}}"> {{__('admin.collection_photo_size')}} </small><span class="required"></span>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="photo" class="custom-file-input {{ $errors->has('photo') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image4')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('photo')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image4 text-center mt-1" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.page.our-collection.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
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
@include('layout/select2')
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
@endsection
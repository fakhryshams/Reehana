@extends('layout/main')
@section('page_title',  trans('admin.about_us'))
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
                    <h1>{{__('admin.about_us')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.page.about-us.add')}}">{{__('admin.about_us')}}</a></li>

                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                @php
                if($aboutUs)
                {
                $path = 'admin.page.about-us.update';
                }
                else
                {
                $path = 'admin.page.about-us.add';
                }
                @endphp
                <form method="post" action="{{route($path)}}" enctype="multipart/form-data">
                    @csrf
                    <input type='hidden' name='ID' value='{{$aboutUs?$aboutUs->id:''}}'>
                    <div class="card-body">
                        <div class="row">
                            @foreach($languageList as $language)
                            @php
                            $record = $aboutUs?$aboutUs->getAboutUsEditDescription($language->id):'';
                            @endphp
                            <!--<div class="col-6 pl-0">-->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.about_us_description')}}  ({{ucfirst($language->name)}})</label>
                                    <textarea  class="textarea form-control {{ $errors->has('about_us_description_'.$language->id) ?'is-invalid':'' }}" placeholder="{{__('admin.about_us_description')}} ({{ucfirst($language->name)}})" name="about_us_description_{{$language->id}}" rows="4" cols="300">{{old('about_us_description_'.$language->id,$record?$record->note:'')}}</textarea>
                                    @error('about_us_description_'.$language->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <!--</div>-->
                            @endforeach
                        </div>
                        <!--</div>-->

                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.about_us_image_1')}}<small class="{{config('custom.text-note-css')}}"> {{__('admin.about-us-default-image')}} </small></label><span class=""></span>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="about_us_image_1" class="custom-file-input {{ $errors->has('about_us_image_1') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image1')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('about_us_image_1')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image1 text-center mt-1" src="{{asset(Storage::url($aboutUs?$aboutUs->image_url_1:''))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.about_us_image_2')}}<small class="{{config('custom.text-note-css')}}"> {{__('admin.about-us-default-image')}} </small></label><span class=""></span>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="about_us_image_2" class="custom-file-input {{ $errors->has('about_us_image_2') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image2')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('about_us_image_2')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image2 text-center mt-1" src="{{asset(Storage::url($aboutUs?$aboutUs->image_url_2:''))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.about_us_image_3')}}<small class="{{config('custom.text-note-css')}}"> {{__('admin.about-us-default-image')}} </small></label><span class=""></span>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="about_us_image_3" class="custom-file-input {{ $errors->has('about_us_image_3') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image3')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('about_us_image_3')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image3 text-center mt-1" src="{{asset(Storage::url($aboutUs?$aboutUs->image_url_3:''))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group"> 
                                    <label>{{__('admin.about_us_image_4')}}<small class="{{config('custom.text-note-css')}}"> {{__('admin.about-us-default-image')}} </small></label><span class=""></span>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="about_us_image_4" class="custom-file-input {{ $errors->has('about_us_image_4') ?'is-invalid':'' }}"  onchange="readURL(this, 'default-image3')">
                                            <label class="custom-file-label">{{__('admin.upload-photo')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('about_us_image_4')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <label class="profile_preview">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image3 text-center mt-1" src="{{asset(Storage::url($aboutUs?$aboutUs->image_url_3:''))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.link')}}</label> <small class="{{config('custom.text-note-css')}}">({{__('admin.url_hint')}})</small><span class="required"></span> 
                                    <input type="url" name="button_link" class="form-control {{ $errors->has('button_link') ?'is-invalid':'' }}" placeholder="{{__('admin.link')}}" value="{{old('button_link',$aboutUs?$aboutUs->button_link:'')}}">
                                    @error('button_link')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{__('admin.meta_title')}}  </label>
                                <input required="" type="text" name="meta_title" class="form-control {{ $errors->has('meta_title') ?'is-invalid':'' }}" placeholder="{{__('admin.meta_title')}}" value="{{old('meta_title',$aboutUs?$aboutUs->meta_title:'')}}">
                                @error('image_meta_title')
                                <div class="text-red">{{ $message }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{__('admin.meta_description')}}  </label>
                                <input required="" type="text" name="meta_description" class="form-control {{ $errors->has('meta_description') ?'is-invalid':'' }}" placeholder="{{__('admin.meta_description')}}" value="{{old('meta_description',$aboutUs?$aboutUs->meta_description:'')}}">
                                @error('image_meta_description_'.$language->id)
                                <div class="text-red">{{ $message }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{__('admin.meta_keyword')}}  </label>
                                <input required="" type="text" name="meta_keyword" class="form-control {{ $errors->has('meta_keyword') ?'is-invalid':'' }}" placeholder="{{__('admin.meta_keyword')}}" value="{{old('meta_keyword',$aboutUs?$aboutUs->meta_keyword:'')}}">
                                @error('image_meta_keyword_'.$language->id)
                                <div class="text-red">{{ $message }}</div>
                                @endif
                            </div>
                        </div>
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
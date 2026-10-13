@extends('layout/main')
@section('page_title',  trans('admin.monogram_font_style'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
@include('layout/toaster')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.monogram_font_style')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.monogram.font-style.index')}}">{{__('admin.monogram_font_style')}}</a></li>
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
                    <h3 class="card-title">{{__('admin.add_new')}}</h3>
                </div>
                <form method="post" action="{{route('admin.monogram.font-style.add')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="control-label">{{__('admin.style_name')}}</label><span class="required"></span>
                                    <input class="form-control {{ $errors->has('style_name') ?'is-invalid':'' }}" placeholder="{{__('admin.style_name')}}" name="style_name" type="text" value="{{old('style_name')}}">
                                    @error('style_name')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label >{{__('admin.has_varient')}} </label> &nbsp;&nbsp;<i style="cursor:pointer;color:#007bff" class="far fa-lightbulb " data-toggle="tooltip" data-placement="top" title="Image File: SVG "></i>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="has_varient">
                                        @foreach($booleanDD as $key=>$status)
                                        <option value="{{$key}}" {{ (collect(old('has_varient'))->contains($key)) ? 'selected':'' }}>{{$status}}</option>
                                        @endforeach
                                    </select>
                                    @error('has_varient')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label>{{__('admin.sorting_order')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">({{__('admin.highest_sorting')}} : {{$sortingNumber}})</small>
                                    <input type="text" name="sorting" placeholder="{{__('admin.sorting_order')}}" class="form-control number-input {{ $errors->has('sorting') ?'is-invalid':'' }}" value="{{old('sorting',$sortingNumber)}}">
                                    @error('sorting')
                                    <div class="text-red">{!! $message !!}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group">
                                    <label>{{__('admin.style_image')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input required="" type="file" name="style_image" class="custom-file-input default-image" onchange="readURL(this, 'default-image','{{config('custom.monogram_style_image_dimensions_width')}}','{{config('custom.monogram_style_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.style_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="text-red default-image-msg"></div>
                                    @error('style_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image text-center mt-1" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-11">
                                <div class="form-group">
                                    <label>{{__('admin.style_image_svg')}}</label><span class="required"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention')}},{{__('admin.imageformat2')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input required="" type="file" name="style_image_svg" class="custom-file-input default-image-svg" onchange="readURL(this, 'default-image-svg','{{config('custom.monogram_style_image_dimensions_width')}}','{{config('custom.monogram_style_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.style_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    <div class="text-red default-image-msg"></div>
                                    @error('style_image_svg')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                    <img class="logo-table default-image-svg text-center mt-1" src="" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.select_initial')}}</label><span class="required"></span>
                                    <select class="form-control select2 select_initial {{config('custom.select2-css')}}" multiple="" data-dropdown-css-class="{{config('custom.select2-css')}}" name="initials[]">
                                        @foreach($initials as $value)
                                        <option value="{{$value->id}}" {{ (collect(old('initials'))->contains($value->id)) ? 'selected':'' }}>{{$value->letter_count_name}}</option>
                                        @endforeach
                                    </select>
                                    @error('initials')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.select_placement')}}</label><span class="required"></span>
                                    <select class="form-control select2 select_placement {{config('custom.select2-css')}}" multiple="" data-dropdown-css-class="{{config('custom.select2-css')}}" name="placements[]">
                                        @foreach($placements as $value)
                                        <option value="{{$value->id}}" {{ (collect(old('placements'))->contains($value->id)) ? 'selected':'' }}>{{$value->placement_name}} {{$value->placement_size}}"</option>
                                        @endforeach
                                    </select>
                                    @error('placements')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.select_color')}}</label><span class="required"></span>
                                    <select class="form-control select2 select_color {{config('custom.select2-css')}}" multiple="" data-dropdown-css-class="{{config('custom.select2-css')}}" name="colors[]">
                                        <option value="all" id='select_all'>Select All</option>
                                        @foreach($colors as $value)
                                        <option value="{{$value->id}}" {{ (collect(old('colors'))->contains($value->id)) ? 'selected':'' }}>{{$value->color_name}}</option>
                                        @endforeach
                                    </select>
                                    @error('colors')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                   <div class="callout callout-info">
<p>Letter Image Support SVG Extension</p>
</div>
                        <div class="alphabets">
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('admin.monogram.font-style.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}} form-submit-btn" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
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
@include('layout/select2')
@include('layout/fileupload')
@include('layout/image_preview')
@include('layout/sweetalert')
<script>
//    function readURL(input, imageClass)
//    {
//        if (input.files && input.files[0])
//        {
//            var reader = new FileReader();
//            reader.onload = function (e)
//            {
//                $('.' + imageClass).attr('src', e.target.result);
//            }
//            reader.readAsDataURL(input.files[0]);
//        }
//    }
    $(function ()
    {
    $(".select_initial").val([1, 2, 3]);
    $(".select_placement > option").prop("selected", "selected"); // Select All Options
    // $(".select_color > option").prop("selected", "selected"); // Select All Options
    bindHtml();
    $("select[name='initials[]'],select[name=has_varient]").change(function ()
    {
    bindHtml()
    });
    $(".select_initial").select2({
    placeholder: "{{__('admin.select_initial')}}",
            allowClear: true
    });
    $(".select_placement").select2({
    placeholder: "{{__('admin.select_placement')}}",
            allowClear: true
    });
    $(".select_color").select2({
    placeholder: "{{__('admin.select_color')}}",
            allowClear: true
    });


    $('.select_color').on("select2:select", function (e) {

           var data = e.params.data.text;
                      if(data=='Select All'){
            $(".select_color > option").prop("selected","selected");
            $("#select_all").prop("selected",false);
            $(".select_color").trigger("change");

           }
      });



    });
    function bindHtml()
    {
    var hasVarient = $("select[name=has_varient]").val();
    var initialsVal = $(".select_initial").select2("val");
//            alert("Varient or Initials Changed");
    $.ajax({
    type: "post",
            url: "{{route('admin.monogram.font-style.get-view')}}",
            data: {'_token': "{{ csrf_token() }}", "hasVarient": hasVarient, "initialsVal": initialsVal},
            success: function (res)
            {
            if (res)
            {
            $(".alphabets").html(res);
            $('.form-submit-btn').removeAttr('disabled');
            bsCustomFileInput.init();
            }
            else
            {
            $(".alphabets").html("");
            }
            },
            beforeSend: function ()
            {
            $("#pageloader").fadeIn();
            },
            complete: function ()
            {
            $("#pageloader").fadeOut();
            }
    });
    }

    function readURL(input, imageClass, requiredWidth, requiredHeight)
    {
    if (input.files && input.files[0])
    {
    var inputFile = input.files[0];
    var file = $('.' + imageClass);
    if (isImage(file.val()))
    {
    var reader = new FileReader();
    reader.onload = function (e)
    {
    var image = new Image();
    image.src = e.target.result;
    image.onload = function ()
    {
    var height = this.height;
    var width = this.width;
    //alert(width + "," + height);

    if (width == requiredWidth && height == requiredHeight)
    {
    $('.' + imageClass).attr('data-res', 'true');
    $('.' + imageClass).removeClass('is-invalid');
    $('.' + imageClass + '-msg').html('');
    $('.' + imageClass).attr('src', e.target.result);
    validateFiles();
    }
    else
    {
    $('.' + imageClass).addClass('is-invalid');
    $('.' + imageClass).attr('src', "{{ asset(Storage::url('default.png')) }}");
    $('.' + imageClass).attr('data-res', 'false');
    show_error("Required Dimension is " + requiredWidth + "x" + requiredHeight);
    $('.' + imageClass + '-msg').html("{{__('admin.media_invalid')}}");
    $('.form-submit-btn').attr('disabled', 'disabled');
    validateFiles();
    }
    };
    }
    reader.readAsDataURL(input.files[0]);
    }
    else
    {
    $('.' + imageClass).attr('data-res', 'false');
    show_error("{{__('admin.error-mime')}}");
    $('.form-submit-btn').attr('disabled', 'disabled')
            validateFiles();
    }
    }
    }

    function validateFiles()
    {
    var filesData = $("input[type=file]");
    $.each(filesData, function(){
    var att = $(this).attr('data-res');
    if (att == "false")
    {
    $('.form-submit-btn').attr('disabled', 'disabled');
    return false; // breaks
    }
    else
    {
    $('.form-submit-btn').removeAttr('disabled');
    }
    });
    }

    function getExtension(filename)
    {
    var parts = filename.split('.');
    return parts[parts.length - 1];
    }

    function isImage(filename)
    {
    var ext = getExtension(filename);
    switch (ext.toLowerCase())
    {
    case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'bmp':
            case 'png':
            case 'svg':
            //etc
            return true;
    }
    return false;
    }
</script>
@endsection

@extends('layout/main')
@section('page_title',  trans('admin.product'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
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
                    <h1>{{__('admin.product')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.index')}}">{{__('admin.product')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.manage_stock')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">

        <form method="post" action="{{route('admin.product.save-color-size',['id'=>$product->id])}}" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <div class="card {{config('custom.card-primary')}}">
                    <div class="card-header">
                        <?php
                        $productName = $product->getProductDescription();
                        ?>
                        <h3 class="card-title">({{$product->sorting}}) - {{$productName?ucfirst($productName->products_name):''}} </h3>
                        <div class="card-tools float-right">
                            @can(config('custom_middleware.edit_product_product'))
                            <a href="{{route('admin.product.edit',['id'=>$product->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card {{config('custom.card-primary')}}">
                    <div class="card-header">
                        <h3 class="card-title">{{__('admin.select_color_size')}}</h3>                                
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.color')}}</label>
                                    <select name="color_id[]"  class="form-control select2 select-color {{config('custom.select2-css')}}" multiple="" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($color as $data)
                                        @php
                                        $colorName = $data->getDescription();
                                        @endphp
                                        <option value="{{$data->id}}" {{ (collect(old('color_id',$selectedColor))->contains($data->id)) ? 'selected':'' }}>{{$colorName->color_title?ucfirst($colorName->color_title):''}}</option>
                                        @endforeach
                                    </select>
                                    @error('color_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>{{__('admin.size')}}</label>
                                    <select name="size_id[]"  class="form-control select2 select-size {{config('custom.select2-css')}}" multiple="" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($size as $data)
                                        @php
                                        $sizeDescription = $data->getSizeDescription();
                                        @endphp
                                        <option value="{{$data->id}}" {{ (collect(old('size_id',$selectedSizes))->contains($data->id)) ? 'selected':'' }}>{{$sizeDescription?ucfirst($sizeDescription->size_title):''}}</option>
                                        @endforeach
                                    </select>
                                    @error('size_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-toolbar float-right">
                            <div class="btn-group">
                                <button type="button" class="add_attr {{config('custom.btn-success-form')}}" title="{{__('admin.save')}}">{{__('admin.save')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bind-html"></div>
            </div>
        </form>
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
@include('layout/ckeditor')
@include('layout/fileupload')
@include('layout/image_preview')
@include('layout/sweetalert')
<script>
    $(function ()
    {
        $(".select-color").select2({
            placeholder: "{{__('admin.select_color')}}",
            allowClear: true
        });
        $(".select-size").select2({
            placeholder: "{{__('admin.select_size')}}",
            allowClear: true
        });
        /*
         * OnLoad
         */
        var colorId = $(".select-color").select2("val")
        var sizeId = $(".select-size").select2("val")
        if (colorId || sizeId)
        {
            getColorSizeHTML(colorId, sizeId);
        }

        $(".add_attr").click(function ()
        {
            var colorId = $(".select-color").select2("val");
            var sizeId = $(".select-size").select2("val");
            if (colorId != "" || sizeId != "")
            {
                getColorSizeHTML(colorId, sizeId);
            }
            else
            {
                show_error("{{__('admin.please_select_color_size')}}");
            }
        });
    });

    function getColorSizeHTML(colorId, sizeId, onlyColorId, onlySizeId, keepOpenColorId)
    {
        $.ajax({
            type: 'POST',
            url: "{{route('admin.product.getSizeColorHTML')}}",
            beforeSend: function ()
            {
                if (!onlyColorId && !onlySizeId) {
                    $("input").attr('disabled', true);
                }
            },
            complete: function ()
            {
                if (!onlyColorId && !onlySizeId) {
                    $("input").removeAttr('disabled');
                }
            },
            data: {
                '_token': '{{ csrf_token()}}',
                'colorId': colorId,
                'sizeId': sizeId,
                'productId': '{{$product->id}}',
                'onlyColorId': onlyColorId,
                'onlySizeId': onlySizeId
            },
            success: (data) => {
                if (onlyColorId) {
                    $('#color-section-' + onlyColorId).replaceWith(data);
                    // Restore the accordion open state after replaceWith has injected new DOM
                    if (keepOpenColorId) {
                        var $collapseEl = $('#color-section-' + keepOpenColorId + ' .collapse');
                        $collapseEl.addClass('show');
                        $('#color-section-' + keepOpenColorId + ' [data-toggle="collapse"]')
                            .removeClass('collapsed')
                            .attr('aria-expanded', 'true');
                    }
                } else if (onlySizeId) {
                    $('#size-section-' + onlySizeId).replaceWith(data);
                } else {
                    $(".bind-html").html(data);
                }
                setDiscount();
                bindValidation();
                changeButtonLable("{{__('admin.modify')}}");
            },
            error: function (xhr)
            {

            }
        });
    }

    function setDiscount()
    {
        $(".size-discount").unbind('change');
        $(".size-discount").change(function ()
        {
            var selectedSizeId = $(this).attr('data-size');
            var selectedColorId = $(this).attr('data-color');

            var isOnlyColor = $(this).attr('data-only-color');
            if (isOnlyColor == 1 && selectedColorId)
            {
                /*
                 * Color Only
                 */
                var price = $("input[name=price" + "_" + selectedColorId + "]").val();
                var discount = $("input[name=discount" + "_" + selectedColorId + "]").val();
            }
            else if (selectedColorId && selectedSizeId)
            {
                /*
                 * Size and Color Both
                 */
                var price = $("input[name=price_" + selectedSizeId + "_" + selectedColorId + "]").val();
                var discount = $("input[name=discount_" + selectedSizeId + "_" + selectedColorId + "]").val();
            }
            else
            {
                /*
                 * Size Only
                 */
                var price = $("input[name=price_" + selectedSizeId + "]").val();
                var discount = $("input[name=discount_" + selectedSizeId + "]").val();
            }
            var calculatedDiscount = "";
            var discountIn = "{{config('custom.discount_in')}}";
            var numberFixed = "{{config('custom.number_fixed')}}";
            if (discountIn == "P")
            {
                if (discount && discount > 0)
                {
                    calculatedDiscount = (price * discount) / 100;
                    calculatedDiscount = price - calculatedDiscount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }
            else if (discountIn == "F")
            {
                if (discount && discount > 0)
                {
                    calculatedDiscount = price - discount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }

            if (calculatedDiscount)
            {
                if (isOnlyColor == 1 && selectedColorId)
                {
                    $("input[name=discount_price" + "_" + selectedColorId + "]").val(calculatedDiscount);
                }
                else if (selectedColorId && selectedSizeId)
                {
                    $("input[name=discount_price_" + selectedSizeId + "_" + selectedColorId + "]").val(calculatedDiscount);
                }
                else
                {
                    $("input[name=discount_price_" + selectedSizeId + "]").val(calculatedDiscount);
                }
            }
            else
            {
                if (isOnlyColor == 1 && selectedColorId)
                {
                    $("input[name=discount_price" + "_" + selectedColorId + "]").val("");
                }
                else if (selectedColorId && selectedSizeId)
                {
                    $("input[name=discount_price_" + selectedSizeId + "_" + selectedColorId + "]").val("");
                }
                else
                {
                    $("input[name=discount_price_" + selectedSizeId + "]").val("");
                }
            }
        });
    }

    function changeButtonLable(text)
    {
        $(".add_attr").attr("title", text);
        $(".add_attr").text(text);
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
        $.each(filesData, function ()
        {
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
                //case 'svg':
                //etc
                return true;
        }
        return false;
    }

    $(document).on('click', '.btn-save-color', function () {
        var btn = $(this);
        var colorId = btn.data('color-id');
        var container = $('#color-section-' + colorId);

        btn.prop('disabled', true).text('{{__("admin.saving")}}...');

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('id', '{{ $product->id }}');
        formData.append('color_id[]', colorId);

        var allColors = $(".select-color").val() || [];
        allColors.forEach(function (cid) {
            formData.append('all_color_ids[]', cid);
        });

        var allSizes = $(".select-size").val() || [];
        allSizes.forEach(function (sid) {
            formData.append('all_size_ids[]', sid);
            formData.append('size_id[]', sid);
        });

        if (container.find('input[name=is_color_only]').length) {
            formData.append('is_color_only', container.find('input[name=is_color_only]').val());
        }
        if (container.find('input[name=is_price_required]').length) {
            formData.append('is_price_required', container.find('input[name=is_price_required]').val());
        }

        container.find('input, select, textarea').each(function () {
            var input = $(this);
            var name = input.attr('name');
            if (!name) return;

            if (name === 'color_id[]' || name === 'size_id[]') return;

            if (input.attr('type') === 'file') {
                if (input[0].files.length > 0) {
                    formData.append(name, input[0].files[0]);
                }
            } else if (input.attr('type') === 'checkbox' || input.attr('type') === 'radio') {
                if (input.is(':checked')) {
                    formData.append(name, input.val());
                }
            } else {
                formData.append(name, input.val());
            }
        });

        $.ajax({
            url: "{{ route('admin.product.save-color-size', ['id' => $product->id]) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                btn.prop('disabled', false).text('Save');
                if (response.status === 'success') {
                    show_success(response.message);
                    // Refresh only the saved color section, keeping its accordion open
                    getColorSizeHTML(allColors, allSizes, colorId, null, colorId);
                } else {
                    show_error('{{__("admin.swal_error")}}', response.message);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Save');
                show_error('{{__("admin.swal_error")}}', '{{__("admin.swal_error_message")}}');
            }
        });
    });

    $(document).on('click', '.btn-save-size', function () {
        var btn = $(this);
        var sizeId = btn.data('size-id');
        var container = $('#size-section-' + sizeId);

        btn.prop('disabled', true).text('{{__("admin.saving")}}...');

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('id', '{{ $product->id }}');
        formData.append('size_id[]', sizeId);

        var allSizes = $(".select-size").val() || [];
        allSizes.forEach(function (sid) {
            formData.append('all_size_ids[]', sid);
        });

        var allColors = $(".select-color").val() || [];
        allColors.forEach(function (cid) {
            formData.append('all_color_ids[]', cid);
        });

        if (container.find('input[name=is_size_only]').length) {
            formData.append('is_size_only', container.find('input[name=is_size_only]').val());
        }
        if (container.find('input[name=is_price_required]').length) {
            formData.append('is_price_required', container.find('input[name=is_price_required]').val());
        }

        container.find('input, select, textarea').each(function () {
            var input = $(this);
            var name = input.attr('name');
            if (!name) return;

            if (name === 'color_id[]' || name === 'size_id[]') return;

            if (input.attr('type') === 'file') {
                if (input[0].files.length > 0) {
                    formData.append(name, input[0].files[0]);
                }
            } else if (input.attr('type') === 'checkbox' || input.attr('type') === 'radio') {
                if (input.is(':checked')) {
                    formData.append(name, input.val());
                }
            } else {
                formData.append(name, input.val());
            }
        });

        $.ajax({
            url: "{{ route('admin.product.save-color-size', ['id' => $product->id]) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                btn.prop('disabled', false).text('Save');
                if (response.status === 'success') {
                    show_success(response.message);
                    getColorSizeHTML(allColors, allSizes, null, sizeId);
                } else {
                    show_error('{{__("admin.swal_error")}}', response.message);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Save');
                show_error('{{__("admin.swal_error")}}', '{{__("admin.swal_error_message")}}');
            }
        });
    });

</script>
@endsection
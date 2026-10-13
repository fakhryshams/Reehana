<div class="row">
    <div class="col-md-11">
        <div class="form-group"> 
            <div class="input-group">
                <div class="custom-file">
                    @if(false)
                    <input type="file" name="product_images[]" class="custom-file-input" onchange="readURL(this,'other-image-{{$count}}')">
                    @endif
                    <input type="file" name="product_images[]" class="custom-file-input other-image-{{$count}}" onchange="readURL(this, 'other-image-{{$count}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                    <label class="custom-file-label">{{__('admin.product_images')}}</label>
                </div>
                <div class="input-group-append">
                    <!--<span class="input-group-text">Upload</span>-->
                </div>
            </div>
            <div class="text-red other-image-{{$count}}"></div>
            @error('product_images.*')
            <div class="text-red">{{ $message }}</div>
            @endif
        </div>
    </div>
    <div class="col-md-1 text-center">
        <div class="btn-toolbar float-right">
            <div class="btn-group mr-2">
                <div class="">
                    <img class="logo-table text-center other-image-{{$count}}" src="" onclick="showDetails(this.src)" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'"/>
                </div>
            </div>
            <div class="btn-group">
                <button type="button" class="remove-btn {{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.remove')}}"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
</div>


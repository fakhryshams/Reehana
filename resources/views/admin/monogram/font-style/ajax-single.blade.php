@foreach($alphabets as $letter)
<?php
$singleImage = "";
foreach ($savedImages as $image)
{
    if ($image->letter == $letter)
    {
        if ($image->single_image)
        {
            $singleImage = asset(Storage::url($image->single_image));
        }
        break;
    }
}
if ($isImageRequired == 0 && $singleImage == "")
{
    $isImageRequired = true;
}
?>
<div class="row">
    <div class="col-1">
        <div class="form-group"> 
            <label class="@if($loop->first) mt-34 @else mt-5px @endif">{{__('admin.letter_image')}} {{$letter}}</label><span class="{{$isImageRequired?'required':''}}"></span>
        </div>
    </div>
    <div class="col-11">
        <div class="row">
            <div class="col-11">
                <div class="form-group"> 
                    @if($loop->first)
                    <label>{{__('admin.single_image')}}</label><span class="{{$isImageRequired?'required':''}}"></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention_letter')}}</small>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input @if($isImageRequired) required="" @endif type="file" name="single_{{$letter}}" class="custom-file-input single_{{$letter}}" onchange="readURL(this, 'single_{{$letter}}','{{config('custom.monogram_style_image_dimensions_width_letter')}}','{{config('custom.monogram_style_image_dimensions_height_letter')}}')">
                                    <label class="custom-file-label">{{__('admin.single_image')}} :: {{$letter}}</label>
                        </div>
                        <div class="input-group-append">
                            <!--<span class="input-group-text">Upload</span>-->
                        </div>
                    </div>
                    <div class="text-red single_{{$letter}}"></div>
                    @error('single_'.$letter)
                    <div class="text-red">{{ $message }}</div>
                    @endif
                </div>
            </div>
            <div class="col-1 text-center p-0">
                @if($loop->first)
                <label class="profile_preview p-0">&nbsp;</label>
                @endif
                <div class="text-center">
                    <img class="logo-table single_{{$letter}} text-center mt-1" src="{{$singleImage}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@foreach($alphabets as $letter)
<?php
$centerImage = "";
foreach ($savedImages as $image)
{
    if ($image->letter == $letter)
    {
        if ($image->center_image)
        {
            $centerImage = asset(Storage::url($image->center_image));
        }

        break;
    }
}
$centerImageRequired = $isImageRequired;

if ($isImageRequired == 0 && $centerImage == "")
{
    $centerImageRequired = true;
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
                    <label>{{__('admin.center_image')}}</label><span class="{{$centerImageRequired?'required':''}}"></span>  <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention_letter_3')}}</small>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input @if($centerImageRequired) required="" @endif type="file" name="center_{{$letter}}" class="custom-file-input center_{{$letter}}" onchange="readURL(this, 'center_{{$letter}}','{{config('custom.monogram_style_image_dimensions_width_letter_3')}}','{{config('custom.monogram_style_image_dimensions_height_letter_3')}}')">
                                    <label class="custom-file-label">{{__('admin.center_image')}} :: {{$letter}}</label>
                        </div>
                        <div class="input-group-append">
                            <!--<span class="input-group-text">Upload</span>-->
                        </div>
                    </div>
                    <div class="text-red center_{{$letter}}"></div>
                    @error('center_'.$letter)
                    <div class="text-red">{{ $message }}</div>
                    @endif
                </div>
            </div>
            <div class="col-1 text-center p-0">
                @if($loop->first)
                <label class="profile_preview p-0">&nbsp;</label>
                @endif
                <div class="text-center">
                    <img class="logo-table center_{{$letter}} text-center mt-1" src="{{$centerImage}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
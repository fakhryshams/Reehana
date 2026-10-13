@foreach($alphabets as $letter)
<?php
$leftImage   = "";
$rightImage  = "";
foreach ($savedImages as $image)
{
    if ($image->letter == $letter)
    {
        if ($image->left_image)
        {
            $leftImage = asset(Storage::url($image->left_image));
        }
        
        if ($image->right_image)
        {
            $rightImage = asset(Storage::url($image->right_image));
        }
        break;
    }
}
$leftImageRequired   = $isImageRequired;
$rightImageRequired  = $isImageRequired;

if ($isImageRequired == 0 && $leftImage == "")
{
    $leftImageRequired = true;
}
if ($isImageRequired == 0 && $rightImage == "")
{
    $rightImageRequired = true;
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
            <div class="col-5">
                <div class="form-group"> 
                    @if($loop->first)
                    <label>{{__('admin.left_image')}}</label><span class="{{$leftImageRequired?'required':''}}"></span>   <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention_letter_3')}}</small>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input @if($leftImageRequired) required="" @endif type="file" name="left_{{$letter}}" class="custom-file-input left_{{$letter}}" onchange="readURL(this, 'left_{{$letter}}','{{config('custom.monogram_style_image_dimensions_width_letter_3')}}','{{config('custom.monogram_style_image_dimensions_height_letter_3')}}')">
                            <label class="custom-file-label">{{__('admin.left_image')}} :: {{$letter}}</label>
                        </div>
                        <div class="input-group-append">
                            <!--<span class="input-group-text">Upload</span>-->
                        </div>
                    </div>
                    <div class="text-red left_{{$letter}}"></div>
                    @error('left_'.$letter)
                    <div class="text-red">{{ $message }}</div>
                    @endif
                </div>
            </div>
            <div class="col-1 text-center p-0">
                @if($loop->first)
                <label class="profile_preview p-0">&nbsp;</label>
                @endif
                <div class="text-center">
                    <img class="logo-table left_{{$letter}} text-center mt-1" src="{{$leftImage}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                </div>
            </div>
            <div class="col-5">
                <div class="form-group"> 
                    @if($loop->first)
                    <label>{{__('admin.right_image')}}</label><span class="{{$rightImageRequired?'required':''}}"></span>   <small class="{{config('custom.text-note-css')}}">{{__('admin.style_image_dimention_letter_3')}}SVG</small>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input @if($rightImageRequired) required="" @endif type="file" name="right_{{$letter}}" class="custom-file-input right_{{$letter}}" onchange="readURL(this, 'right_{{$letter}}','{{config('custom.monogram_style_image_dimensions_width_letter_3')}}','{{config('custom.monogram_style_image_dimensions_height_letter_3')}}')">
                            <label class="custom-file-label">{{__('admin.right_image')}} :: {{$letter}}</label>
                        </div>
                        <div class="input-group-append">
                            <!--<span class="input-group-text">Upload</span>-->
                        </div>
                    </div>
                    <div class="text-red right_{{$letter}}"></div>
                    @error('right_'.$letter)
                    <div class="text-red">{{ $message }}</div>
                    @endif
                </div>
            </div>
            <div class="col-1 text-center p-0">
                @if($loop->first)
                <label class="profile_preview p-0">&nbsp;</label>
                @endif
                <div class="text-center">
                    <img class="logo-table right_{{$letter}} text-center mt-1" src="{{$rightImage}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
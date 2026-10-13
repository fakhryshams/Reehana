@if(count($cartProduct) > 0)

<?php
if (count($cartProduct['monogram']) > 0)
{
    $monocount = $cartProduct['monogram'];
    end($monocount);         // move the internal pointer to the end of the array
    $lastkey = key($monocount);
}
else
{
    $lastkey = 1;
}

$monogramPrice = \App\Helpers\Helper::getMonogramPrice();
$currency = Helper::getDefaultCurrency();
$currencyCode = $currency ? $currency->code : config('custom.currency');
$color_id = $cartProduct['color_id'];
$sizeId = $cartProduct['size_id'];
$productId = $cartProduct['product_id'];
?>
<div class="monogramSummary monogramSummary-{{$id}}" data-total="{{$lastkey}}">
    @foreach($cartProduct['monogram'] as $key =>  $monogramVal)
    <?php
    $font = \App\Helpers\Helper::getMonogramStyle($monogramVal['font']);
    $initials = \App\Helpers\Helper::getInitials($monogramVal['initials_count'], $monogramVal['left_text'], $monogramVal['center_text'], $monogramVal['right_text'], $monogramVal['text4']);
    $colors = \App\Helpers\Helper::getMonogramColor($monogramVal['monogram_color_id']);
    $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);
    $placement = \App\Helpers\Helper::getPlacement($monogramVal['placement']);

    $user = Auth::user();
    ?>                                
    <div class="monogram-information  monogram-information-{{$id}}   " id="monogramSummary-{{$id}}">
        <div class="h-flex">
            <div class="h-flex">
                <label>Selected Monogram</label>
                <span class="monogram-price nowrap"> {{$monogramPrice >0  ? '(+'.Helper::formatNumber($monogramPrice * $currency->value).' '.$currencyCode.')' : ''}}</span>
            </div>
            <span class="link-button edit_monogram2" data-productId="{{$productId}}" data-id="{{$id}}" data-edit-id="{{$user ? $monogramVal['id'] : '' }}"   data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-color="{{$color_id ? $color_id : 'c'}}"  data-size="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{$key}}">Edit</span>
            <span class="link-button removeMonogram2" data-productid="{{$productId}}" data-id="{{$id}}"  data-colorexist="{{ $color_id ? 1 : 0}}" data-sizeexist="{{$sizeId ? 1 : 0}}" data-colorId="{{$color_id ? $color_id : 'c'}}"  data-sizeId="{{$sizeId ? $sizeId : 's'}}" data-monogram="{{$monogramVal['id']}}">Remove</span>
        </div>
        <div class="border-panel">


            <input type="hidden" name="product_cart_id_{{$id}}_{{$key}}" value="{{$monogramVal['id']}}">

            <div class="h-flex">
                <div class="monogram-options">
                    <label>Style</label>
                    <div class="info " id="styleInfo_{{$id}}_{{$key}}"><img src="{{asset(Storage::url($font ? $font->style_image_svg : ''))}}"></div>
                </div>
                <div class="monogram-options">
                    <label>Initials</label>
                    <div class="info" id="alphabetsInfo_{{$id}}_{{$key}}">{{$initials? $initials : ''}}</div>
                </div>
                <div class="monogram-options">
                    <label>Color</label>
                    <div class="info" id="colorInfo_{{$id}}_{{$key}}">
                        <span class="color-plate" style="background: {{$colors? $colors->color_hash : ''}};"></span>
                        {{$colors?$colors->color_name : ''}}
                    </div>
                </div>
                <div class="monogram-options">
                    <label>Placement</label>
                    <div class="info"  id="placementInfo_{{$id}}_{{$key}}">{{$placement ?$placement->placement_desc:''}}</div>
                </div>
            </div>

            <div class="separator underline underline_{{$id}}_{{$key}} {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}"></div>
            <div class="comment monogramComment monogramComment_{{$id}}_{{$key}}  {{$monogramVal['monogram_note'] ? 'show' : 'hide'}}">
                <p>Comment:{{$monogramVal['monogram_note'] ?  $monogramVal['monogram_note'] : ''}}</p>
            </div>
        </div>


    </div>
    @endforeach
</div>
@endif
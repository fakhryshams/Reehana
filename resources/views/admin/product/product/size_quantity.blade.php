@foreach($size as $size)
<?php
$sizeDescription           = $size->getSizeDescription();
$productAttributes         = $record ? $record->getProductAttributes($size->id) : [];
$productAttributeQuantity  = $productAttributes ? $productAttributes->quantity : '';
?>
@if($haveSizePriceVariant)
<?php
$productAttributePrice     = $productAttributes ? $productAttributes->product_regular_price : '';
$productAttributeSalePrice = $productAttributes ? $productAttributes->product_sale_price : '';
$productAttributeDiscount  = $productAttributes ? $productAttributes->discount : '';

if ($productAttributeSalePrice && $productAttributeDiscount)
{
    $productAttributeSalePrice = Helper::formatNumber($productAttributeSalePrice);
}
else
{
    $productAttributeSalePrice = "";
}
?>
<input type="hidden" name="is_price_required" value="0">

<!--Quantity-->
<div class="col-xs-12 col-md-3">
    <div class="form-group">
        <label>{{__('admin.qty')}} ({{$sizeDescription?ucfirst($sizeDescription->size_title):''}})</label>
        <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
        <input type="text" name="size_{{$size->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}} {{__('admin.for')}} {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}">
        @error('size_'.$size->id)
        <div class="text-red">{{ $message }}</div>
        @endif
    </div>
</div>

<!--Price-->
<div class="col-xs-12 col-md-3">
    <div class="form-group">
        <label>{{__('admin.price')}} ({{$sizeDescription?ucfirst($sizeDescription->size_title):''}})</label>
        <input type="text" name="price_{{$size->id}}" value="{{old('price_'.$size->id,$productAttributePrice)}}" data-size="{{$size->id}}" class="size-quantity-price form-control size-discount size-quantity float-input length-10" placeholder="{{__('admin.price')}} {{__('admin.for')}} {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}">
        @error('price_'.$size->id)
        <div class="text-red">{{ $message }}</div>
        @endif
    </div>
</div>

<!--Discount-->
<div class="col-xs-12 col-md-3">
    <div class="form-group">
        <input type="hidden" name="discount_in" value="{{config('custom.discount_in')}}">
        <label>({{config('custom.discount_title')}}) {{__('admin.discount')}} ({{$sizeDescription?ucfirst($sizeDescription->size_title):''}})</label>
        <input type="text" name="discount_{{$size->id}}" value="{{old('discount_'.$size->id,$productAttributeDiscount)}}" data-size="{{$size->id}}" class="form-control size-discount size-quantity float-input length-2" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}} {{__('admin.for')}} {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}">
        @error('discount_'.$size->id)
        <div class="text-red">{{ $message }}</div>
        @endif
    </div>
</div>

<!--Discounted Price-->
<div class="col-xs-12 col-md-3">
    <div class="form-group">
        <label>{{__('admin.discount_price')}} ({{$sizeDescription?ucfirst($sizeDescription->size_title):''}})</label>
        <input readonly="" type="text" name="discount_price_{{$size->id}}" value="{{old('discount_price_'.$size->id,$productAttributeSalePrice)}}" class="form-control size-quantity float-input length-10" placeholder="{{__('admin.discount_price')}} {{__('admin.for')}} {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}">
        @error('discount_price_'.$size->id)
        <div class="text-red">{{ $message }}</div>
        @endif
    </div>
</div>


@else
<input type="hidden" name="is_price_required" value="1">
<div class="col-xs-12 col-md-12">
    <div class="form-group">
        <label>{{__('admin.qty')}} ({{$sizeDescription?ucfirst($sizeDescription->size_title):''}})</label>
        <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
        <input type="text" name="size_{{$size->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}} {{__('admin.for')}} {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}">
        @error('size_'.$size->id)
        <div class="text-red">{{ $message }}</div>
        @endif
    </div>
</div>
@endif
@endforeach
@error('size.*')
<div class="text-red">{{ $message }}</div>
@endif
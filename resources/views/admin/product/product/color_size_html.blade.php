<div class="binded-html">
    @if($colors)
    @foreach($colors as $colorKey => $color)
    @if(isset($onlyColorId) && $onlyColorId && $onlyColorId != $color->id)
        @continue
    @endif
    <?php
    $colorDesc = $color->getColorDescription();
    $selectedColorImages = $product->getGalleryImage(false, $color->id);
//    echo "<pre>";
//    print_r($selectedColorImages);
//    echo "</pre>";
    $selectedImage1 = false;
    $selectedImage2 = false;
    $selectedImage3 = false;
    $selectedImage1Record = false;
    $selectedImage2Record = false;
    $selectedImage3Record = false;
    
      foreach ($selectedColorImages as $key => $img_record)
    {

        if ($img_record->number == 1)
        {
            $selectedImage1Record = $img_record;
            $selectedImage1       = asset(Storage::url($img_record->image_url));
        }
        if ($img_record->number == 2)
        {
            $selectedImage2Record = $img_record;
            $selectedImage2       = asset(Storage::url($img_record->image_url));
        }
        if ($img_record->number == 3)
        {
            $selectedImage3Record = $img_record;
            $selectedImage3 = asset(Storage::url($img_record->image_url));
        }
    }
  //  if ($selectedColorImages && isset($selectedColorImages[0]))
 //   {
  //      $selectedImage1 = asset(Storage::url($selectedColorImages[0]->image_url));
  //  }
  //  if ($selectedColorImages && isset($selectedColorImages[1]))
 //   {
 //       $selectedImage2 = asset(Storage::url($selectedColorImages[1]->image_url));
 //   }
 //   if ($selectedColorImages && isset($selectedColorImages[2]))
  //  {
 //       $selectedImage3 = asset(Storage::url($selectedColorImages[2]->image_url));
  //  }
    ?>
    <div id="color-section-{{$color->id}}" class="color-section-wrapper">
        <div id="accordion-{{$color->id}}">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header position-relative">
                    <h4 class="card-title w-100">
                        <a class="d-block w-100 collapsed" data-toggle="collapse" href="#collapseOne{{$colorKey}}"
                            aria-expanded="false">
                        <b>Color - {{$colorDesc?$colorDesc->color_title:''}}</b>&nbsp;&nbsp;&nbsp;({{__('admin.imageformat1')}})               
                        </a>
                    </h4>
                    <button type="button" class="btn btn-danger btn-sm btn-save-color-header" data-color-id="{{$color->id}}" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);display:none;width: 10rem;">Save</button>
                </div>
                <div id="collapseOne{{$colorKey}}" class="collapse" data-parent="#accordion-{{$color->id}}" style="">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_image_1')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_image_1[{{$color->id}}]" class="custom-file-input product_image_1_{{$color->id}}" onchange="readURL(this, 'product_image_1_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_image_1')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('product_default_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                @if($selectedImage1Record)
                                    <a class="a_product_image_1_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedImage1Record->id])}}','product_image_1_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_image_1_{{$color->id}} text-center mt-1" src="{{$selectedImage1}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_image_2')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_image_2[{{$color->id}}]" class="custom-file-input product_image_2_{{$color->id}}" onchange="readURL(this, 'product_image_2_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_image_2')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('product_default_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                @if($selectedImage2Record)
                                    <a class="a_product_image_2_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedImage2Record->id])}}','product_image_2_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_image_2_{{$color->id}} text-center mt-1" src="{{$selectedImage2}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_image_3')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_image_3[{{$color->id}}]" class="custom-file-input product_image_3_{{$color->id}}" onchange="readURL(this, 'product_image_3_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_image_3')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class="input-group-text">Upload</span>-->
                                        </div>
                                    </div>
                                    @error('product_default_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                @if($selectedImage3Record)
                                    <a class="a_product_image_3_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedImage3Record->id])}}','product_image_3_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_image_3_{{$color->id}} text-center mt-1" src="{{$selectedImage3}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        @if($sizes)
            
                        <?php
                        $record = $product;
                        $haveSizePriceVariant = true;
                        ?>
                        @foreach($sizes as $k => $size)
                        <?php
                        #Size Images
                        $selectedSizeImages = $product->getGallerySizeImage(false, $size->id, $color->id);
                        $sizeSelectedImage1 = false;
                        $sizeSelectedImage2 = false;
                        $sizeSelectedImage3 = false;
                        $selectedsizeImage1Record = false;
                        $selectedsizeImage2Record = false;
                        $selectedsizeImage3Record = false;
                    
            
                        foreach( $selectedSizeImages as $key => $img_record)
                        {
                        
                            
                            
                            if ($img_record->number == 1)
                            {
                            $selectedsizeImage1Record  = $img_record;
                                $sizeSelectedImage1       = asset(Storage::url($img_record->image_url));
                            }
                            if ($img_record->number == 2)
                            {
                                $selectedsizeImage2Record = $img_record;
                                $sizeSelectedImage2          = asset(Storage::url($img_record->image_url));
                            }
                            if ($img_record->number == 3)
                            {
                                $selectedsizeImage3Record = $img_record;
                                $sizeSelectedImage3    = asset(Storage::url($img_record->image_url));
                            }
                        }
            
                    
            
                        $sizeDescription = $size->getSizeDescription();
                        $productAttributes = $record ? $record->getProductAttributes($size->id, $color->id, false) : [];
                        if (in_array($color->id, $colorId) && in_array($size->id, $sizeId))
                        {
                            $productAttributeQuantity = $productAttributes ? $productAttributes->quantity : 0;
                        }
                        else
                        {
                            $productAttributeQuantity = 0;
                        }
                        $productAttributeWeight = $productAttributes ? $productAttributes->weight : $product->product_weight;
                        $productAttributeLength = $productAttributes ? $productAttributes->product_length : $product->product_length;
                        $productAttributeWidth = $productAttributes ? $productAttributes->product_width : $product->product_width;
                        $productAttributeHeight = $productAttributes ? $productAttributes->product_height : $product->product_height;
                        $productAttributeDimensionUnit = $productAttributes ? $productAttributes->dimension_unit : ($product->dimension_unit ?? 'cm');
                        $productAttributeWeightUnit = $productAttributes ? $productAttributes->weight_unit : ($product->weight_unit ?? 'kg');
                        $productAttributeStyle = $productAttributes ? $productAttributes->style : $product->style;
                        $productAttributeSku = $productAttributes ? $productAttributes->sku : $product->product_sku;
                        $productAttributeBarcode = $productAttributes ? $productAttributes->barcode : $product->product_barcode;
                        $productAttributesorting = $productAttributes ? $productAttributes->sorting : '';
                        $productAttributeHsCode = $productAttributes ? $productAttributes->hs_code : '';
                        
                        
                        ?>
                        @if($haveSizePriceVariant)
                        <?php
                        $productAttributePrice = $productAttributes ? $productAttributes->product_regular_price : $product->product_regular_price;
                        $productAttributePrice = Helper::formatNumber($productAttributePrice);
                        $productAttributeSalePrice = $productAttributes ? $productAttributes->product_sale_price : $product->product_sale_price;
                        $productAttributeDiscount = $productAttributes ? $productAttributes->discount : $product->discount;
            
                        if ($productAttributeSalePrice && $productAttributeDiscount)
                        {
                            $productAttributeSalePrice = Helper::formatNumber($productAttributeSalePrice);
                        }
                        else
                        {
                            $productAttributeSalePrice = "";
                        }
                    
                        if( $productAttributeQuantity == 0)
                        {
                        $cl = 'rgba(255, 99, 71, 0.2);';
                        }           
                        else
                        {
                            if($k%2 == 1)
                        {
                        $cl = 'rgb(224,224,224);';  
                        }
                        else
                        {
                        $cl ='white;'; 
                        }
                        
                        }  
                        ?>
                        <div class="row"  style="background-color: {{$cl}}">
                            <input type="hidden" name="is_price_required" value="0">
                            <!--Quantity-->
                            <div class="col-xs-12 col-md-12"><label><h5><b>{{__('admin.size')}} : {{$sizeDescription?ucfirst($sizeDescription->size_title):''}} </b></h5></label></div>
            
            
                            <div class="col-xs-12 col-md-1"    >
                                <div class="form-group">
                                    <label>{{__('admin.qty')}}</label>
                                    <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
                                    <input type="text" name="size_{{$size->id}}_{{$color->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}}">
                                    @error('size_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Price-->
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>{{__('admin.price')}}</label>
                                    <input type="text" name="price_{{$size->id}}_{{$color->id}}" value="{{old('price_'.$size->id,$productAttributePrice)}}" data-only-color="0" data-size="{{$size->id}}" data-color="{{$color->id}}" class="size-quantity-price form-control size-discount size-quantity float-input length-10" placeholder="{{__('admin.price')}}">
                                    @error('price_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Discount-->
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <input type="hidden" name="discount_in" value="{{config('custom.discount_in')}}">
                                    <label>({{config('custom.discount_title')}}) {{__('admin.discount')}}</label>
                                    <input type="text" name="discount_{{$size->id}}_{{$color->id}}" value="{{old('discount_'.$size->id,$productAttributeDiscount)}}" data-only-color="0" data-size="{{$size->id}}" data-color="{{$color->id}}" class="form-control size-discount size-quantity float-input length-2" maxlength="2" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}}">
                                    @error('discount_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Sale Price-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.discount_price')}}</label>
                                    <input readonly="" type="text" name="discount_price_{{$size->id}}_{{$color->id}}" value="{{old('discount_price_'.$size->id,$productAttributeSalePrice)}}" class="form-control size-quantity float-input length-10" placeholder="{{__('admin.discount_price')}}">
                                    @error('discount_price_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Style-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_style')}}</label>
                                    <input type="text" name="style_{{$size->id}}_{{$color->id}}" value="{{old('style_'.$size->id,$productAttributeStyle)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_style')}}">
                                    @error('style_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--SKU-->
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>{{__('admin.product_sku')}}</label>
                                    <input type="text" name="sku_{{$size->id}}_{{$color->id}}" value="{{old('sku_'.$size->id,$productAttributeSku)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_sku')}}">
                                    @error('sku_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Barcode-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.barcode')}}</label>
                                    <input type="text" name="barcode_{{$size->id}}_{{$color->id}}" value="{{old('barcode_'.$size->id,$productAttributeBarcode)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.barcode')}}">
                                    @error('barcode_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            
                            <!--Sorting_-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.sorting')}}</label>
                                    <input type="text" name="sorting_{{$size->id}}_{{$color->id}}" value="{{old('sorting_'.$size->id,$productAttributesorting)}}" class="form-control number-input length-10" placeholder="{{__('admin.sorting')}}">
                                </div>
                            </div>
                            @else
                            <input type="hidden" name="is_price_required" value="1">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.qty')}}</label>
                                    <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
                                    <input type="text" name="size_{{$size->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}}">
                                    @error('size_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="row"  style="background-color: {{$cl}}">
                            <!--Weight-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.weight')}}</label>
                                    <input type="text" name="weight_{{$size->id}}_{{$color->id}}" value="{{old('weight_'.$size->id,$productAttributeWeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.weight')}}">
                                    @error('weight_'.$size->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Length</label>
                                    <input type="text" name="length_{{$size->id}}_{{$color->id}}" value="{{old('length_'.$size->id.'_'.$color->id,$productAttributeLength)}}" class="form-control number-input length-10" placeholder="{{__('admin.length')}}">
                                    @error('length_'.$size->id.'_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Width</label>
                                    <input type="text" name="width_{{$size->id}}_{{$color->id}}" value="{{old('width_'.$size->id.'_'.$color->id,$productAttributeWidth)}}" class="form-control number-input length-10" placeholder="{{__('admin.width')}}">
                                    @error('width_'.$size->id.'_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input type="text" name="height_{{$size->id}}_{{$color->id}}" value="{{old('height_'.$size->id.'_'.$color->id,$productAttributeHeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.height')}}">
                                    @error('height_'.$size->id.'_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Dim Unit</label>
                                    <select class="form-control select2" name="dimension_unit_{{$size->id}}_{{$color->id}}">
                                        <option value="cm" {{old('dimension_unit_'.$size->id.'_'.$color->id,$productAttributeDimensionUnit) == 'cm' ? 'selected' : ''}}>cm</option>
                                        <option value="in" {{old('dimension_unit_'.$size->id.'_'.$color->id,$productAttributeDimensionUnit) == 'in' ? 'selected' : ''}}>in</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Weight Unit</label>
                                    <select class="form-control select2" name="weight_unit_{{$size->id}}_{{$color->id}}">
                                        <option value="kg" {{old('weight_unit_'.$size->id.'_'.$color->id,$productAttributeWeightUnit) == 'kg' ? 'selected' : ''}}>kg</option>
                                        <option value="lb" {{old('weight_unit_'.$size->id.'_'.$color->id,$productAttributeWeightUnit) == 'lb' ? 'selected' : ''}}>lb</option>
                                    </select>
                                </div>
                            </div>
                            <!--HS Code-->
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>HS Code</label>
                                    <input type="text" name="hs_code_{{$size->id}}_{{$color->id}}" value="{{old('hs_code_'.$size->id.'_'.$color->id,$productAttributeHsCode)}}" class="form-control size-quantity length-30" placeholder="HS Code">
                                    @error('hs_code_'.$size->id.'_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row"style="background-color: {{$cl}}">
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_size_image_1')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_size_image_1[{{$color->id}}][{{$size->id}}]" class="custom-file-input product_size_image_1_{{$size->id}}_{{$color->id}}" onchange="readURL(this, 'product_size_image_1_{{$size->id}}_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_size_image_1')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
            
                                    @if($selectedsizeImage1Record)
                                    <a class="a_product_size_image_1_{{$size->id}}_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage1Record->id])}}','product_size_image_1_{{$size->id}}_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_size_image_1_{{$size->id}}_{{$color->id}} text-center mt-1" src="{{$sizeSelectedImage1}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_size_image_2')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_size_image_2[{{$color->id}}][{{$size->id}}]" class="custom-file-input product_size_image_2_{{$size->id}}_{{$color->id}}" onchange="readURL(this, 'product_size_image_2_{{$size->id}}_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_size_image_2')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
            
                                    @if($selectedsizeImage2Record)
                                    <a class="a_product_size_image_2_{{$size->id}}_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage2Record->id])}}','product_size_image_2_{{$size->id}}_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_size_image_2_{{$size->id}}_{{$color->id}} text-center mt-1" src="{{$sizeSelectedImage2}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group"> 
                                    <label>{{__('admin.product_size_image_3')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="product_size_image_3[{{$color->id}}][{{$size->id}}]" class="custom-file-input product_size_image_3_{{$size->id}}_{{$color->id}}" onchange="readURL(this, 'product_size_image_3_{{$size->id}}_{{$color->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                            <label class="custom-file-label">{{__('admin.product_size_image_3')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-1 text-center p-0">
                                <label class="profile_preview p-0">&nbsp;</label>
                                <div class="text-center">
                                    @if($selectedsizeImage3Record)
                                    <a class="a_product_size_image_3_{{$size->id}}_{{$color->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage3Record->id])}}','product_size_image_3_{{$size->id}}_{{$color->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                    @endif
                                    <img class="logo-table product_size_image_3_{{$size->id}}_{{$color->id}} text-center mt-1" src="{{$sizeSelectedImage3}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                        <hr class="hr-custom mt-2">
                        @endif
                        @endforeach
                        @error('size.*')
                        <div class="text-red">{{ $message }}</div>
                        @endif
            
                        @else
                        <!--Color Only-->
                        <div class="row">
                            <input type="hidden" name="is_color_only" value="1">
                            <?php
                            $record = $product;
                            $haveSizePriceVariant = true;
                            ?>
                            <?php
                            $sizeDescription = null;
                            $productAttributes = $record ? $record->getProductAttributes(null, $color->id, false) : [];
                            if ($productAttributes && count($productAttributes))
                            {
                                $productAttributes = $productAttributes[0];
                            }
                            if (in_array($color->id, $colorId))
                            {
                                $productAttributeQuantity = $productAttributes ? $productAttributes->quantity : 0;
                            }
                            else
                            {
                                $productAttributeQuantity = 0;
                            }

                            if ($productAttributeQuantity == 0)
                            {
                                $cl = 'rgba(255, 99, 71, 0.2);';
                            }
                            else
                            {
                                $cl = 'white;';
                            }

                            $productAttributeWeight = $productAttributes ? $productAttributes->weight : $product->product_weight;
                            $productAttributeLength = $productAttributes ? $productAttributes->product_length : $product->product_length;
                            $productAttributeWidth = $productAttributes ? $productAttributes->product_width : $product->product_width;
                            $productAttributeHeight = $productAttributes ? $productAttributes->product_height : $product->product_height;
                            $productAttributeDimensionUnit = $productAttributes ? $productAttributes->dimension_unit : ($product->dimension_unit ?? 'cm');
                            $productAttributeWeightUnit = $productAttributes ? $productAttributes->weight_unit : ($product->weight_unit ?? 'kg');
                            $productAttributeStyle = $productAttributes ? $productAttributes->style : $product->style;
                            $productAttributeSku = $productAttributes ? $productAttributes->sku : $product->product_sku;
                            $productAttributeBarcode = $productAttributes ? $productAttributes->barcode : $product->product_barcode;
                            $productAttributesorting = $productAttributes ? $productAttributes->sorting : '';
                            $productAttributeHsCode = $productAttributes ? $productAttributes->hs_code : '';
                                            
                        ?>
                            @if($haveSizePriceVariant)
                            <?php
                            $productAttributePrice = $productAttributes ? $productAttributes->product_regular_price : $product->product_regular_price;
                            $productAttributePrice = Helper::formatNumber($productAttributePrice);
                            $productAttributeSalePrice = $productAttributes ? $productAttributes->product_sale_price : $product->product_sale_price;
                            $productAttributeDiscount = $productAttributes ? $productAttributes->discount : $product->discount;
            
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
            
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>{{__('admin.qty')}}</label>
                                    <input type="text" name="size_{{$color->id}}" value="{{old('size_'.$color->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}}">
                                    @error('size_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>{{__('admin.price')}}</label>
                                    <input type="text" name="price_{{$color->id}}" value="{{old('price_'.$color->id,$productAttributePrice)}}" data-only-color="1" data-size="{{$color->id}}" data-color="{{$color->id}}" class="size-quantity-price form-control size-discount size-quantity float-input length-10" placeholder="{{__('admin.price')}}">
                                    @error('price_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <input type="hidden" name="discount_in" value="{{config('custom.discount_in')}}">
                                    <label>({{config('custom.discount_title')}}) {{__('admin.discount')}}</label>
                                    <input type="text" name="discount_{{$color->id}}" value="{{old('discount_'.$color->id,$productAttributeDiscount)}}" data-only-color="1" data-size="{{$color->id}}" data-color="{{$color->id}}" class="form-control size-discount size-quantity float-input length-2" maxlength="2" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}}">
                                    @error('discount_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.discount_price')}}</label>
                                    <input readonly="" type="text" name="discount_price_{{$color->id}}" value="{{old('discount_price_'.$color->id,$productAttributeSalePrice)}}" class="form-control size-quantity float-input length-10" placeholder="{{__('admin.discount_price')}}">
                                    @error('discount_price_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Style-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.product_style')}} </label>
                                    <input type="text" name="style_{{$color->id}}" value="{{old('style_'.$color->id,$productAttributeStyle)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_style')}}">
                                    @error('style_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--SKU-->
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>{{__('admin.product_sku')}} </label>
                                    <input type="text" name="sku_{{$color->id}}" value="{{old('sku_'.$color->id,$productAttributeSku)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_sku')}}">
                                    @error('sku_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
                            <!--Barcode-->
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.barcode')}}</label>
                                    <input type="text" name="barcode_{{$color->id}}" value="{{old('barcode_'.$color->id,$productAttributeBarcode)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.barcode')}}">
                                    @error('barcode_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
            
            
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.sorting')}}</label>
                                    <input type="text" name="sorting_{{$color->id}}" value="{{old('sorting_'.$color->id,$productAttributesorting)}}" class="form-control number-input length-10" placeholder="{{__('admin.sorting')}}">
                            
                                </div>
                            </div>

                        </div>

                        <div class="row" style="background-color: {{$cl}}">
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>{{__('admin.weight')}}</label><small class="{{config('custom.text-note-css')}}">{{__('admin.weightkg')}}</small>
                                    <input type="text" name="weight_{{$color->id}}" value="{{old('weight_'.$color->id,$productAttributeWeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.weight')}}">
                                    @error('weight_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Length</label>
                                    <input type="text" name="length_{{$color->id}}" value="{{old('length_'.$color->id,$productAttributeLength)}}" class="form-control number-input length-10" placeholder="{{__('admin.length')}}">
                                    @error('length_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Width</label>
                                    <input type="text" name="width_{{$color->id}}" value="{{old('width_'.$color->id,$productAttributeWidth)}}" class="form-control number-input length-10" placeholder="{{__('admin.width')}}">
                                    @error('width_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <label>Height</label>
                                    <input type="text" name="height_{{$color->id}}" value="{{old('height_'.$color->id,$productAttributeHeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.height')}}">
                                    @error('height_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Dim Unit</label>
                                    <select class="form-control select2" name="dimension_unit_{{$color->id}}">
                                        <option value="cm" {{old('dimension_unit_'.$color->id,$productAttributeDimensionUnit ?? 'cm') == 'cm' ? 'selected' : ''}}>cm</option>
                                        <option value="in" {{old('dimension_unit_'.$color->id,$productAttributeDimensionUnit ?? 'cm') == 'in' ? 'selected' : ''}}>in</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Weight Unit</label>
                                    <select class="form-control select2" name="weight_unit_{{$color->id}}">
                                        <option value="kg" {{old('weight_unit_'.$color->id,$productAttributeWeightUnit ?? 'kg') == 'kg' ? 'selected' : ''}}>kg</option>
                                        <option value="lb" {{old('weight_unit_'.$color->id,$productAttributeWeightUnit ?? 'kg') == 'lb' ? 'selected' : ''}}>lb</option>
                                    </select>
                                </div>
                            </div>
                            <!--HS Code-->
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>HS Code</label>
                                    <input type="text" name="hs_code_{{$color->id}}" value="{{old('hs_code_'.$color->id,$productAttributeHsCode)}}" class="form-control size-quantity length-30" placeholder="HS Code">
                                    @error('hs_code_'.$color->id)
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                            @endif
                            @error('size.*')
                            <div class="text-red">{{ $message }}</div>
                            @endif
                        </div>
                        @endif
                        
                    <div class="row mt-3 justify-content-center {{ !empty($onlyColorId) || empty($sizes) ? 'mb-3' : '' }}">
                        <div class="col-4 col-xs-12">
                            <button type="button" class="btn btn-success form-submit-btn btn-save-color w-100" data-color-id="{{$color->id}}">
                                <span class="text-bold text-uppercase">Save {{ $colorDesc ? $colorDesc->color_title : '' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
    </div>
    @endforeach
    @else

    <!--Size Only-->
    <!-- @if(empty($onlySizeId))
    <input type="hidden" name="is_size_only" value="1">
    <div class="card {{config('custom.card-primary')}}">
        <div class="card-header">
            <h3 class="card-title"><b>Size Only</b></h3>                                
        </div>
        <div class="card-body">
    @endif -->
            <?php
            $record = $product;
            $haveSizePriceVariant = true;
            ?>
            @foreach($sizes as $kl => $size)
            @if(isset($onlySizeId) && $onlySizeId && $onlySizeId != $size->id)
                @continue
            @endif
            <?php
            #Size Images
            $selectedSizeImages = $product->getGallerySizeImage(false, $size->id);
            $sizeSelectedImage1 = false;
            $sizeSelectedImage2 = false;
            $sizeSelectedImage3 = false;
            $selectedsizeImage1Record = false;
            $selectedsizeImage2Record = false;
            $selectedsizeImage3Record = false;

            foreach( $selectedSizeImages as $key => $img_record)
            {
                if ($img_record->number == 1)
                {
                    $selectedsizeImage1Record  = $img_record;
                    $sizeSelectedImage1       = asset(Storage::url($img_record->image_url));
                }
                if ($img_record->number == 2)
                {
                    $selectedsizeImage2Record = $img_record;
                    $sizeSelectedImage2          = asset(Storage::url($img_record->image_url));
                }
                if ($img_record->number == 3)
                {
                    $selectedsizeImage3Record = $img_record;
                    $sizeSelectedImage3    = asset(Storage::url($img_record->image_url));
                }
            }

            $sizeDescription = $size->getSizeDescription();
            $productAttributes = $record ? $record->getProductAttributes($size->id, null, false) : [];

            if (in_array($size->id, $sizeId))
            {
                $productAttributeQuantity = $productAttributes ? $productAttributes->quantity : 0;
            }
            else
            {
                $productAttributeQuantity = 0;
            }

            if( $productAttributeQuantity == 0)
            {
                $cl = 'rgba(255, 99, 71, 0.2);';
            }
            else
            {
                if($kl%2 == 1)
                {
                    $cl = 'rgb(224,224,224);';
                }
                else
                {
                    $cl ='white;';
                }
            }
            $productAttributeWeight = $productAttributes ? $productAttributes->weight : $product->product_weight;
            $productAttributeLength = $productAttributes ? $productAttributes->product_length : $product->product_length;
            $productAttributeWidth = $productAttributes ? $productAttributes->product_width : $product->product_width;
            $productAttributeHeight = $productAttributes ? $productAttributes->product_height : $product->product_height;
            $productAttributeDimensionUnit = $productAttributes ? $productAttributes->dimension_unit : ($product->dimension_unit ?? 'cm');
            $productAttributeWeightUnit = $productAttributes ? $productAttributes->weight_unit : ($product->weight_unit ?? 'kg');
            $productAttributeStyle = $productAttributes ? $productAttributes->style : $product->style;
            $productAttributeSku = $productAttributes ? $productAttributes->sku : $product->product_sku;
            $productAttributeBarcode = $productAttributes ? $productAttributes->barcode : $product->product_barcode;
            $productAttributesorting = $productAttributes ? $productAttributes->sorting : '';
            $productAttributeHsCode = $productAttributes ? $productAttributes->hs_code : '';
            ?>
            <div id="size-section-{{$size->id}}" class="size-section-wrapper">
                <div id="accordion-size-{{$size->id}}">
                    <div class="card {{config('custom.card-primary')}}">
                        <div class="card-header position-relative">
                            <h4 class="card-title w-100">
                                <a class="d-block w-100 collapsed" data-toggle="collapse" href="#collapseSize{{$kl}}" aria-expanded="false">
                                    <b>Size - {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}</b>&nbsp;&nbsp;&nbsp;({{__('admin.imageformat1')}})
                                </a>
                            </h4>
                            <button type="button" class="btn btn-danger btn-sm btn-save-size-header" data-size-id="{{$size->id}}" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);display:none;width: 10rem;">Save</button>
                        </div>
                        <div id="collapseSize{{$kl}}" class="collapse" data-parent="#accordion-size-{{$size->id}}" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>{{__('admin.product_size_image_1')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="product_size_image_1[{{$size->id}}]" class="custom-file-input product_size_image_1_{{$size->id}}" onchange="readURL(this, 'product_size_image_1_{{$size->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                                    <label class="custom-file-label">{{__('admin.product_size_image_1')}}</label>
                                                </div>
                                                <div class="input-group-append">
                                                </div>
                                            </div>
                                            @error('product_default_image')
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-1 text-center p-0">
                                        <label class="profile_preview p-0">&nbsp;</label>
                                        <div class="text-center">
                                            @if($selectedsizeImage1Record)
                                            <a class="a_product_size_image_1_{{$size->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage1Record->id])}}','product_size_image_1_{{$size->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                            @endif
                                            <img class="logo-table product_size_image_1_{{$size->id}} text-center mt-1" src="{{$sizeSelectedImage1}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>{{__('admin.product_size_image_2')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="product_size_image_2[{{$size->id}}]" class="custom-file-input product_size_image_2_{{$size->id}}" onchange="readURL(this, 'product_size_image_2_{{$size->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                                    <label class="custom-file-label">{{__('admin.product_size_image_2')}}</label>
                                                </div>
                                                <div class="input-group-append">
                                                </div>
                                            </div>
                                            @error('product_default_image')
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-1 text-center p-0">
                                        <label class="profile_preview p-0">&nbsp;</label>
                                        <div class="text-center">
                                            @if($selectedsizeImage2Record)
                                            <a class="a_product_size_image_2_{{$size->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage2Record->id])}}','product_size_image_2_{{$size->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                            @endif
                                            <img class="logo-table product_size_image_2_{{$size->id}} text-center mt-1" src="{{$sizeSelectedImage2}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>{{__('admin.product_size_image_3')}}</label><span class=""></span> <small class="{{config('custom.text-note-css')}}">{{__('admin.product_image_dimention')}}</small>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="product_size_image_3[{{$size->id}}]" class="custom-file-input product_size_image_3_{{$size->id}}" onchange="readURL(this, 'product_size_image_3_{{$size->id}}','{{config('custom.product_image_dimensions_width')}}','{{config('custom.product_image_dimensions_height')}}')">
                                                    <label class="custom-file-label">{{__('admin.product_size_image_3')}}</label>
                                                </div>
                                                <div class="input-group-append">
                                                </div>
                                            </div>
                                            @error('product_default_image')
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-1 text-center p-0">
                                        <label class="profile_preview p-0">&nbsp;</label>
                                        <div class="text-center">
                                            @if($selectedsizeImage3Record)
                                            <a class="a_product_size_image_3_{{$size->id}}" title="{{__('admin.remove_gallery_image')}}" onclick="showSweetAlertAttrImage('{{route('admin.product.remove-attr-image',['id'=>$selectedsizeImage3Record->id])}}','product_size_image_3_{{$size->id}}')"><span class="notify-badge-attr"><i class="fa fa-times"></i></span></a>
                                            @endif
                                            <img class="logo-table product_size_image_3_{{$size->id}} text-center mt-1" src="{{$sizeSelectedImage3}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)"/>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                $productAttributePrice = $productAttributes ? $productAttributes->product_regular_price : $product->product_regular_price;
                                $productAttributePrice = Helper::formatNumber($productAttributePrice);
                                $productAttributeSalePrice = $productAttributes ? $productAttributes->product_sale_price : $product->product_sale_price;
                                $productAttributeDiscount = $productAttributes ? $productAttributes->discount : $product->discount;

                                if ($productAttributeSalePrice && $productAttributeDiscount)
                                {
                                    $productAttributeSalePrice = Helper::formatNumber($productAttributeSalePrice);
                                }
                                else
                                {
                                    $productAttributeSalePrice = "";
                                }
                                ?>
                                @if($haveSizePriceVariant)
                                <input type="hidden" name="is_price_required" value="0">
                                <div class="row" style="background-color: {{$cl}}">
                                    <div class="col-xs-12 col-md-12"><label><h5><b>{{__('admin.size')}} : {{$sizeDescription?ucfirst($sizeDescription->size_title):''}}</b></h5></label></div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>{{__('admin.qty')}}</label>
                                            <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
                                            <input type="text" name="size_{{$size->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}}">
                                            @error('size_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>{{__('admin.price')}}</label>
                                            <input type="text" name="price_{{$size->id}}" value="{{old('price_'.$size->id,$productAttributePrice)}}" data-only-color="0" data-size="{{$size->id}}" class="size-quantity-price form-control size-discount size-quantity float-input length-10" placeholder="{{__('admin.price')}}">
                                            @error('price_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <input type="hidden" name="discount_in" value="{{config('custom.discount_in')}}">
                                            <label>({{config('custom.discount_title')}}) {{__('admin.discount')}}</label>
                                            <input type="text" name="discount_{{$size->id}}" value="{{old('discount_'.$size->id,$productAttributeDiscount)}}" data-only-color="0" data-size="{{$size->id}}" class="form-control size-discount size-quantity float-input length-2" maxlength="2" placeholder="({{config('custom.discount_title')}}) {{__('admin.discount')}}">
                                            @error('discount_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>{{__('admin.discount_price')}}</label>
                                            <input readonly="" type="text" name="discount_price_{{$size->id}}" value="{{old('discount_price_'.$size->id,$productAttributeSalePrice)}}" class="form-control size-quantity float-input length-10" placeholder="{{__('admin.discount_price')}}">
                                            @error('discount_price_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>{{__('admin.product_style')}}</label>
                                            <input type="text" name="style_{{$size->id}}" value="{{old('style_'.$size->id,$productAttributeStyle)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_style')}}">
                                            @error('style_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>{{__('admin.product_sku')}}</label>
                                            <input type="text" name="sku_{{$size->id}}" value="{{old('sku_'.$size->id,$productAttributeSku)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.product_sku')}}">
                                            @error('sku_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>{{__('admin.barcode')}}</label>
                                            <input type="text" name="barcode_{{$size->id}}" value="{{old('barcode_'.$size->id,$productAttributeBarcode)}}" class="form-control size-quantity length-30" placeholder="{{__('admin.barcode')}}">
                                            @error('barcode_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>{{__('admin.sorting')}}</label>
                                            <input type="text" name="sorting_{{$size->id}}" value="{{old('sorting_'.$size->id,$productAttributesorting)}}" class="form-control number-input length-10" placeholder="{{__('admin.sorting')}}">
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="is_price_required" value="1">
                                    <div class="col-xs-12 col-md-12">
                                        <div class="form-group">
                                            <label>{{__('admin.qty')}}</label>
                                            <input type="hidden" name="size[]" value="{{$size->id}}" class="size-quantity">
                                            <input type="text" name="size_{{$size->id}}" value="{{old('size_'.$size->id,$productAttributeQuantity)}}" class="form-control size-quantity number-input length-10" placeholder="{{__('admin.qty')}}">
                                            @error('size_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="row" style="background-color: {{$cl}}">
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>{{__('admin.weight')}}</label>
                                            <input type="text" name="weight_{{$size->id}}" value="{{old('weight_'.$size->id,$productAttributeWeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.weight')}}">
                                            @error('weight_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>Length</label>
                                            <input type="text" name="length_{{$size->id}}" value="{{old('length_'.$size->id,$productAttributeLength)}}" class="form-control number-input length-10" placeholder="{{__('admin.length')}}">
                                            @error('length_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>Width</label>
                                            <input type="text" name="width_{{$size->id}}" value="{{old('width_'.$size->id,$productAttributeWidth)}}" class="form-control number-input length-10" placeholder="{{__('admin.width')}}">
                                            @error('width_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-1">
                                        <div class="form-group">
                                            <label>Height</label>
                                            <input type="text" name="height_{{$size->id}}" value="{{old('height_'.$size->id,$productAttributeHeight)}}" class="form-control number-input length-10" placeholder="{{__('admin.height')}}">
                                            @error('height_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>Dim Unit</label>
                                            <select class="form-control select2" name="dimension_unit_{{$size->id}}">
                                                <option value="cm" {{old('dimension_unit_'.$size->id,$productAttributeDimensionUnit) == 'cm' ? 'selected' : ''}}>cm</option>
                                                <option value="in" {{old('dimension_unit_'.$size->id,$productAttributeDimensionUnit) == 'in' ? 'selected' : ''}}>in</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-2">
                                        <div class="form-group">
                                            <label>Weight Unit</label>
                                            <select class="form-control select2" name="weight_unit_{{$size->id}}">
                                                <option value="kg" {{old('weight_unit_'.$size->id,$productAttributeWeightUnit) == 'kg' ? 'selected' : ''}}>kg</option>
                                                <option value="lb" {{old('weight_unit_'.$size->id,$productAttributeWeightUnit) == 'lb' ? 'selected' : ''}}>lb</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-3">
                                        <div class="form-group">
                                            <label>HS Code</label>
                                            <input type="text" name="hs_code_{{$size->id}}" value="{{old('hs_code_'.$size->id,$productAttributeHsCode)}}" class="form-control size-quantity length-30" placeholder="HS Code">
                                            @error('hs_code_'.$size->id)
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3 justify-content-center">
                                    <div class="col-4 col-xs-12">
                                        <button type="button" class="btn btn-success form-submit-btn btn-save-size w-100" data-size-id="{{$size->id}}">
                                            <span class="text-bold text-uppercase">Save Size {{$sizeDescription ? $sizeDescription->size_title : ''}}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @error('size.*')
            <div class="text-red">{{ $message }}</div>
            @endif

            <!-- @if(empty($onlySizeId))
        </div>
    </div>
    @endif -->
     @endif
    @if(empty($onlyColorId) && empty($onlySizeId))
    <div class="card {{config('custom.card-success')}}">
        <div class="card-footer">
            <div class="btn-toolbar float-right">
                <div class="btn-group mr-2">
                    <a href="{{route('admin.product.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                </div>
                <div class="btn-group">
                    <button type="submit" style="display: none;" class="{{config('custom.btn-success-form')}} form-submit-btn" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

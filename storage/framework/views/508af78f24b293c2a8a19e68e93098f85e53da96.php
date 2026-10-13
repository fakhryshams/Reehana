
<?php $__env->startSection('page_title', trans('web.product')); ?>
<?php $__env->startSection('body_class', 'products-detail configMonogram'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

<?php $__env->startSection('page_meta'); ?>
<meta property="og:site_name" content="<?php echo ucfirst($appName); ?>,<?php echo $contactEmail; ?>">
<meta property="og:title" content="<?php echo ucfirst($productDetail->products_name); ?>" />
<meta property="og:description" content="<?php echo strip_tags($productDetail->products_description); ?>" />
<?php if($productDefaultImage): ?>
<meta property="og:image" itemprop="image" content="<?php echo e(asset(Storage::url($productDefaultImage->image_thumb_url))); ?>">
<?php endif; ?>
<meta property="og:type" content="website" />
<meta property="og:updated_time" content="<?php echo strtotime(date("Y-m-d H:i:s T")); ?>" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="<?php echo e(route('web.index')); ?>"><?php echo e(__('web.home')); ?></a></span>
        <span class="sep"></span>
        <?php if(isset($parentCategory)): ?>
        <span><a href="<?php echo e(route("web.product.index",['slug'=>$parentCategory->categories_slug])); ?>"><?php echo e(ucfirst($parentCategoryName->categories_name)); ?></a></span>
        <?php endif; ?>
        <?php if($categoryName): ?>
        <span class="sep"></span>
        <span><a href="<?php echo e(route("web.product.index",['slug'=>$category->categories_slug])); ?>"><?php echo e(ucfirst($categoryName->categories_name)); ?></a></span>
        <?php endif; ?>
    </div>
</section>
<?php $currency = Helper::getDefaultCurrency(); ?>
<section class="products-detail">
    <div class="container">
        <div class="row">



            <div class="col-sm-12 imageSection  prodImageSection">
                <?php echo $__env->make('web/product/product-image', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="main-info" id="detail">
                    <h1><?php echo e($productDetail->products_name); ?></h1>
                    <div class="product-pricing-details">
                        <span class="prod-price"><?php echo e(!$product->is_product_sale_price_null ? Helper::formatNumber($product->product_sale_price) : Helper::formatNumber($product->product_regular_price)); ?> <?php echo e($currencyCode); ?> </span> <span class="striked"><?php echo e(!$product->is_product_sale_price_null ? Helper::formatNumber($product->product_regular_price).' '.$currencyCode : ''); ?></span>
                    </div>
                    <?php
                    $brand =  \App\AdminModel\Brand\AdminBrand::where('id', $product->brand_id)->first();
                    if ($brand) {
                        $brand_desc = $brand->getBrandDescription($language->id);
                    }
                    ?>

                    <?php if($brand): ?>
                    <h5 class="brand"><a href="<?php echo e(route('web.product.shop_by_brand', (['b[]'=>$brand_desc ? $brand_desc->brand_name : '']))); ?>"><?php echo e($brand_desc->brand_name); ?></a></h5>
                    <?php endif; ?>
                </div>
                <div class="additional-information sm-hidden">
                    <div class="acc-block expanded">
                        <div class="acc-title">
                            <span class="pull-right">
                                <i class="icon-minus expand"></i>
                                <i class="icon-plus collapse"></i>
                            </span>
                            <span><?php echo e(__('web.product_description')); ?></span>
                        </div>
                        <div class="acc-content">
                            <?php echo $productDetail->products_description; ?>

                        </div>
                    </div>
                    <div class="acc-block collapsed">
                        <div class="acc-title">
                            <span class="pull-right">
                                <i class="icon-minus expand"></i>
                                <i class="icon-plus collapse"></i>
                            </span>
                            <span><?php echo e(__('web.shipping_information')); ?></span>
                        </div>
                        <div class="acc-content">
                            <?php echo trim($productDetail->product_return_exchange_policy)?$productDetail->product_return_exchange_policy:Helper::getCommonReturnExchangePolicy($language->id); ?>


                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1 col-sm-12"></div>
            <div class="col-md-5 col-sm-12">
                <?php
                $color = [];
                $sizes = [];
                $has_qty = true;
                $has_attr= false;

                if ($product->have_color) {
                    $color = $product->getProductColors($language->id, '', $product->have_color, $product->have_size);
                    if (count($color) == 0) {
                        $has_qty = false;
                    }
                }

                if ($product->have_size) {

                    $sizes = $product->getProductSize($language->id, '', $product->have_color, $product->have_size);
                    if (count($sizes) == 0) {
                        $has_qty = false;
                    }
                }


                $is_monogram = Helper::isMonogranm($product->id);
                ?>
                <?php if($product->product_quantity > 0 && $has_qty == true ): ?>
                <div class="product-options pd v-flex mBot20">

                    <?php if($product->have_color && $has_qty == true): ?>
                    <div class="form-item color_select w-100">
                        <label><?php echo e(__('web.color')); ?></label>
                        <select class="select-component untouched required mRight20 selectColor select-color" name="color_<?php echo e($product->id); ?>" data-id="<?php echo e($product->id); ?>">
                            <option value="0" selected="selected" disabled="">Select</option>
                            <?php $__currentLoopData = $color; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							
							<?php
							$sizess =  \App\AdminModel\Product\AdminProductAttribute::where('color_id', $record->color_id)->where('products_id',$product->id)->where('quantity','>',0)->pluck('size_id')->toArray();
 							
							$sizeCount =  \App\AdminModel\Size\AdminSize::whereIn('id', $sizess)->where('is_active',1)->count();
							?>							
							<?php if($sizeCount > 0 || !$product->have_size): ?>						
				   
                            <option value="<?php echo e($record->color_id); ?>"><?php echo e($record->color_title); ?></option>
							<?php endif; ?>                           

						   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <i class="error"></i>
                    </div>
                    <?php $has_attr =true ?>
                    <?php endif; ?>

                    <div class="h-flex flex-nowrap w-100">
                        <?php
                        $isExistInCart = Helper::getCartProductStatus($user, $product->id, count($sizes) > 0 ? true : false, '', count($color) > 0 ? true : false, '');
                        //mRight30
                        ?>
                        <?php if(count($sizes) > 0 && $product->have_size && $has_qty == true ): ?>
                        <div class="form-item size_select w-100  sizeshow">
                            <label><?php echo e(__('web.size')); ?></label>

                            <select class="select-component untouched required select-size" name="size_<?php echo e($product->id); ?>" data-id="<?php echo e($product->id); ?>">
                                <option value="0" selected="selected" disabled=""><?php echo e(__('web.select')); ?></option>
                                <?php
                                if (count($color) == 0 && $product->have_size) {
                                ?>
                                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    ?>
                                    <option value="<?php echo e($size->size_id); ?>"><?php echo e($size?ucfirst($size->size_title):''); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php
                                }



                                   $has_attr =true;
                                ?>

                            </select>
                            <i class="error"></i>
                        </div>
                    </div>
                    <div class="h-flex flex-nowrap w-100">
                        <?php endif; ?>
                        <?php
                        $ppid = $product->id;
                        $add_class ='';
                        if (!$product->have_size && !$product->have_color) {

                            $ppid = $product->id . '_s_c';
                            $add_class ='select-size';
                            
                        }
                        ?>

                        <?php if($productQuantity && $has_qty == true ): ?>

                        <?php $max = $productQuantity ?>
                        <?php
                        
                         if($has_attr == true)
                         {
                             $max =1;
                         }
                        $min = 1;
                        if ($productQuantity <= config('custom_web.reamin_min_quantity', 10) && $productQuantity > 0) {
                            $datashow = $productQuantity;
                        } else {
                            $datashow = config('custom_web.reamin_min_quantity', 10);
                        }
                        ?>
                        <div class="form-item  counter2  <?php echo e((count($sizes) > 0  && $product->have_size) ? 'w-100' :'w-100'); ?>">
                            <label class=""><?php echo e(__('web.quantity')); ?>

                                <!--<span class="remains remains_detail"></span>-->
                            </label>
                            <div class="counter" data-maximum="<?php echo e($max); ?>" data-show="<?php echo e($datashow); ?>">
                            <?php if($add_class): ?>

                            <input type="hidden" class="<?php echo e($add_class); ?>" data-id='<?php echo e($product->id); ?>'>   
                            <?php endif; ?>
                            <div class="h-flex">
                                    <button class="decrease" data-id="<?php echo e($ppid); ?>" data-maximum="<?php echo e($max); ?>" data-productid="<?php echo e($ppid); ?>"><i class="icon-minus"></i></button>
                                    <input type="text" name="quantity_<?php echo e($ppid); ?>" data-id="<?php echo e($ppid); ?>" data-productid="<?php echo e($ppid); ?>" class="prod-count  quantity number-input" value="<?php echo e($min); ?>">
                                    <button class="increase" data-id="<?php echo e($ppid); ?>" data-maximum="<?php echo e($max); ?>" data-productid="<?php echo e($ppid); ?>"><i class="icon-plus"></i></button>
                                </div>
                                <!-- <span class="remains show" data-show="10">(Only 5 left)</span> -->
                            </div>
                        </div>
                        <i class="error"></i>
                        <div class="qty-error form-item text-red custom-error qty-error-<?php echo e($product->id); ?>"></div>
                        <?php endif; ?>
                    </div>
                    <?php if($is_monogram && $has_qty == true): ?>
                    <div class="w-100">
                        <button class="addMonogramButton" data-page="detail" data-productId="<?php echo e($product->id); ?>" data-sizeexist="<?php echo e(count($sizes) > 0 ? 1 : 0); ?>" data-colorexist="<?php echo e(count($color) > 0 ? 1 : 0); ?>">Add a Monogram</button>
                        <!-- <div class="options">
                                <div class="checkbox">
                                        <input type="checkbox" name="monogram" value="monogram">
                                        <label>Add a Monogram</label>
                                </div>
                        </div> -->
                    </div>
                    <div class="applyMonogramStyles applyMonogramStyles2 hide">
                        <div class="checkbox">
                            <input type="checkbox" name="applyAll" class="apply_same">
                            <label>Apply same Monogram Styles to all quantities.</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>


                <?php
                $size_exist = count($sizes) > 0 ? 1 : 0;
                $color_exist = count($color) > 0 ? 1 : 0;
                $user = Auth::user();
                $cartproduct = Helper::getCartProduct($user, $product->id, $size_exist, 's', $color_exist, 'c');
                $monogramPrice = \App\Helpers\Helper::getMonogramPrice();
                $font = [];
                $initials = [];
                $colors = [];
                $placement = [];


                if (count($cartproduct) > 0) {
                    if ($user) {
                        if ($cartproduct['is_monogram'] == 1) {
                            $cartproduct['monogram'] = $cartproduct;
                        } else {
                            $cartproduct['monogram'] = [];
                        }
                    }
                    if ($cartproduct['is_monogram'] == 1) {

                        foreach ($cartproduct['monogram'] as  $value) {

                            $font = \App\Helpers\Helper::getMonogramStyle($value['font']);
                            $initials = \App\Helpers\Helper::getInitials($value['initials_count'], $value['left_text'], $value['center_text'], $value['right_text'], $value['text4']);
                            $colors = \App\Helpers\Helper::getMonogramColor($value['monogram_color_id']);
                            $placement = \App\Helpers\Helper::getPlacement($value['placement']);
                        }
                    }
                }
                $is_mono = false;

                if (count($cartproduct) > 0) {
                    if ($cartproduct['is_monogram']) {
                        $is_mono = true;
                    }
                }
                ?>

                <div id="mogogram_list">
                    <div class="monogramSummary monogramSummary-<?php echo e($product->id); ?>" data-total="0">

                    </div>
                </div>
                <!--<div class="monogramSummary monogramSummary-<?php echo e($product->id); ?>" data-total="0"></div>-->
                <input type="hidden" name="monogram_data" id="monogram_data" value="0" />

                <div class="additional-information sm-visible">
                    <div class="acc-block collapsed">
                        <div class="acc-title">
                            <span class="pull-right">
                                <i class="icon-minus expand"></i>
                                <i class="icon-plus collapse"></i>
                            </span>
                            <span><?php echo e(__('web.product_description')); ?></span>
                        </div>
                        <div class="acc-content">
                            <?php echo $productDetail->products_description; ?>

                        </div>
                    </div>
                    <div class="acc-block collapsed">
                        <div class="acc-title">
                            <span class="pull-right">
                                <i class="icon-minus expand"></i>
                                <i class="icon-plus collapse"></i>
                            </span>
                            <span><?php echo e(__('web.shipping_information')); ?></span>
                        </div>
                        <div class="acc-content">
                            <?php echo $productDetail->product_return_exchange_policy; ?>

                        </div>
                    </div>
                </div>

                <div class="product-actions mBot20">

                    <?php if($product->product_quantity <= 0 || $has_qty==false): ?> <button class="btn btn-primary add-cart-<?php echo e($product->id); ?>" disabled=""><?php echo e(__('web.sold_out')); ?></button>
                        <?php elseif($isExistInCart): ?>
                        <button class="btn btn-primary add_to_cart add-cart-<?php echo e($product->id); ?>" data-mcart-id="<?php echo e($product->id); ?>" data-page="detail" data-id="<?php echo e($product->id); ?>" data-size="<?php echo e(count($sizes)>0?1:0); ?>" data-color="<?php echo e(count($color)>0?1:0); ?>"><?php echo e(__('web.add_to_cart')); ?></button>
                        <?php else: ?>

                        <button class="btn btn-primary add-cart-<?php echo e($product->id); ?>" data-mcart-id="<?php echo e($product->id); ?>" disabled="" data-page="detail" data-id="<?php echo e($product->id); ?>" data-size="<?php echo e(count($sizes)>0?1:0); ?>" data-color="<?php echo e(count($color)>0?1:0); ?>"><?php echo e(__('web.added_to_cart')); ?></button>
                        <?php endif; ?>
                        <?php if($wishList): ?>
                        <!--Added to WishList-->
                        <?php if(false): ?>
                        <!--Code Working for Add-Remove Vice-Versa-->
                        <!--<button class="btn btn-primary add-wishlist" type="button" data-id='<?php echo e($product->id); ?>' data-page='detail'><?php echo e(__('web.added_to_wishlist')); ?></button>-->
                        <?php endif; ?>
                        <button class="btn btn-secondary favorite wishlist-product favorite marked" type="button" data-page='detail'> <i class="icon-wishlist"></i> <?php echo e(__('web.added_to_wishlist')); ?></button>
                        <?php else: ?>
                        <!--Not Added to WishList-->
                        <button class="btn btn-secondary favorite  add-wishlist wishlist-product" type="button" data-image="" data-id='<?php echo e($product->id); ?>' data-page='detail'> <i class="icon-wishlist"></i> <?php echo e(__('web.add_to_wishlist')); ?></button>
                        <?php endif; ?>

                </div>
                <div class="clearfix">
                    <ul class="share">
                        <li><?php echo e(__('web.share')); ?></li>
                        <li><a target="_blank" href="https://wa.me/?text=<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>"><span class="whatsapp"></span></a></li>
                        <li><a target="_blank" title="Facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>"><span class="facebook"></span></a></li>
                        <li><a target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo e($productDetail->products_name); ?>&url=<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>"><span class="twitter"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="similar-products">
    <?php
    $relatedProduct = $product->getProductCategory($product->id);
    $relatedProductCount = $product->getRelatedProductCount($product->id);

    ?>
    <?php if(count($relatedProduct) >0 && $relatedProductCount > 0): ?>
    <div class="container-fluid">
        <h1><?php echo __('web.related_products'); ?></h1>
        <div id="similarProducts" class="slider">
            <?php $__currentLoopData = $relatedProduct; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $product = $record->getProduct();
            if (!$product) {
                continue;
            }
            if ($product && !Helper::isProductVisibleForCurrentUser($product)) {
                continue;
            }
            $getWishlist = '';
            if (Auth::user()) {
                $getWishlist = \App\AdminModel\Product\Wishlist::where('user_id', Auth::user()->id)->where('product_id', $product->id)->first();
            }
            $relatedproductDetail = $record->getProductDescription($language->id);
            $productDefaultImage = $product->getDefaultImage();
            /*
             *  Convert Currency Rate
             */
            if ($currency) {
                $product_sale_price = $product->product_sale_price * $currency->value;
                $product_regular_price = $product->product_regular_price * $currency->value;
            }
            if ($product->product_quantity == 0) {
                $class = 'sold';
            } elseif (!$product->is_product_sale_price_null) {
                $class = 'sale';
            } elseif ($product->is_new_arrival) {
                $class = 'new';
            } else {
                $class = '';
            }
            ?>
            <div class="panel">
                <div class="product <?php echo e($class); ?>">
                    <div class="product-image">
                        <a href="<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>">
                            <img class="image_<?php echo e($product->id); ?>" src="<?php echo e(asset(Storage::url($productDefaultImage ? $productDefaultImage->image_thumb_url : ''))); ?>">
                            <img src="<?php echo e(asset('front/img/prod-transparent.png')); ?>" class="transparent">
                        </a>
                        <span class="icon"><i class="icon-wishlist  add-wishlist2 <?php echo e($getWishlist ? 'selected':''); ?>" data-page="product" data-id='<?php echo e($product->id); ?>'></i></span>
                    </div>
                    <div class="prod-title">
                        <span><a href="<?php echo e(route('web.product.product_detail',['slug'=> $product->products_slug])); ?>"><?php echo e($relatedproductDetail ? ucfirst($relatedproductDetail->products_name):''); ?></a></span>
                    </div>

                         <?php  $det = strlen($relatedproductDetail->products_description) > 50 ? substr($relatedproductDetail->products_description,0,50)."..." : $relatedproductDetail->products_description;
                         $det = str_replace("<strong>","", $det);
                         $det = str_replace("</strong>","", $det);

                         ?>
                    <p class="prod-desc"><?php echo $det; ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_specific_js'); ?>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
      Fancybox.bind('[data-fancybox="gallery"]', {
        //
      });    
    </script>
<script>
    $('.acc-block > .acc-title').on('click', function() {

        var ele = $(this).parent();
        if (ele.hasClass('expanded')) {
            ele.removeClass('expanded').addClass('collapsed');
            $(this).find("span.ecicon > span").removeClass('minus-circle').addClass('plus-circle');
        } else {
            ele.addClass('expanded').removeClass('collapsed');
            $(this).find("span.ecicon > span").addClass('minus-circle').removeClass('plus-circle');
        }
    });
</script>
<?php echo $__env->make('web.product.page-ajax', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/product_detail.blade.php ENDPATH**/ ?>
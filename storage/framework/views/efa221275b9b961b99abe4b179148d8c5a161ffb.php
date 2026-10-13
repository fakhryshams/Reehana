<?php
$searchedProductIds = isset($searchedProductIds) ? $searchedProductIds : false;
$cuurent_category   = '';
$search             = isset($appliedFilter['search']) ? $appliedFilter['search'] : '';
if (isset($appliedFilter))
{
    $cuurent_category = $appliedFilter['subCategoryId'];
    if ($appliedFilter['c'] == 'clear')
    {
        $appliedFilter['category'] = [];
    }
    else
    {
        if ($appliedFilter['category'])
        {

            $cate = \App\AdminModel\Product\AdminCategory::whereIn('id', $appliedFilter['category'])->first();
            if ($cate)
            {

                if ($cate->parent_id != 0)
                {
                    $cuurent_category = $cate->parent_id;
                }
                else
                {
                    $cuurent_category = $cate->id;
                }
            }
        }
    }
}

if (!isset($activeTabId))
{
    $activeTabId = 'f_category';
}

if (isset($appliedFilter['isAll']) && $appliedFilter['isAll'])
{
    $appliedFilter['category'] = 'all_' . $appliedFilter['subCategoryId'];
}
//    
//echo "<pre>";
//print_r($appliedFilter);
//echo "</pre>";
//die;
//dd($categories);
?>
<?php if(count($categories)>0): ?>
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(isset($appliedFilter['sidebarSelectedCategoryId']) && $appliedFilter['sidebarSelectedCategoryId'] && $category->id != $appliedFilter['sidebarSelectedCategoryId']): ?>
<?php continue; ?>
<?php endif; ?>
<div class="filter-group acc-block my_cat   <?php echo e($cuurent_category ?   $cuurent_category == $category->id ? 'expanded' : 'collapsed' : ''); ?>     <?php echo e($category->categories_slug); ?>">
    <?php
    $categoryName  = $category->getCategoryDescription($language->id);
//    $subCategories = $category->getChildFront($category->id, false, $appliedFilter['type'], $search);
    $subCategories = $category->getChildFront2($category->id, false, $appliedFilter['type'], '', $appliedFilter, $searchedProductIds);
//    $subCategories = $category->getChildFront($category->id, false, $appliedFilter['type'], '');
    $allCategoryId = [];

    $isSingleCategorySelected = is_array($appliedFilter['category']) ? true : false;
    if ($isSingleCategorySelected)
    {
        $isSingleCategorySelected = count($appliedFilter['category']) == 1 ? true : false;
    }
    ?>
    <?php if(count($subCategories)): ?>
    <div class="title acc-title">
        <span><?php echo e($categoryName->categories_name?ucfirst($categoryName->categories_name):''); ?></span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        <?php $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $cat = \App\AdminModel\Product\AdminCategory::find($subCategory->id); 
        $subCategoryName = $cat->getCategoryDescription($language->id);
        $allCategoryId[]  = $subCategory->id;
        ?>
        <div class="radio">
            <input  class="input-filter category-filter" type="radio" name="category[]" value="<?php echo e($subCategory->id); ?>" <?php echo e(isset($appliedFilter['category']) && $isSingleCategorySelected? ((collect($appliedFilter['category'])->contains($subCategory->id)) ? 'checked':''):''); ?>>
            <label><?php echo e($subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''); ?></label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="radio">
            <input  class="input-filter category-filter"  type="radio" name="category[]" value="all_<?php echo e($category->id); ?>" <?php echo e(isset($appliedFilter['category'])? ((collect($appliedFilter['category'])->contains('all_'.$category->id)) ? 'checked':''):''); ?>>
            <label><?php echo e(__('web.all')); ?></label>
        </div>
        <div class="more category_more"><i class="icon-plus"></i><?php echo e(__('web.more')); ?></div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php if($filterColor && count($filterColor['data'])>0): ?>
<div class="filter-group acc-block acc-block-color collapsed">
    <div class="title acc-title">
        <span><?php echo e(__('web.color')); ?></span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        <?php $__currentLoopData = $filterColor['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $colorDetail      = $color->getColorDescription($language->id);
        $filterCountColor = $color->getColorCount($filterColor['productIds']);
        if ($filterCountColor)
        {
            //$filterCountColor = $filterCountColor['filter_count'];
        }
        else
        {
            continue;
        }
        ?>
        <div class="checkbox">
            <input name="color[]" class="input-filter" data-filter-type="color"  type="checkbox" value="<?php echo e($color->id); ?>" <?php echo e(isset($appliedFilter['color'])? ((collect($appliedFilter['color'])->contains($color->id)) ? 'checked':''):''); ?>>
            <label><?php echo e($colorDetail?ucfirst($colorDetail->color_title):''); ?> (<?php echo e($filterCountColor); ?>)</label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="more color_more" data-count="<?php echo e(count($filterColor['data'])); ?>"><i class="icon-plus"></i><?php echo e(__('web.more')); ?></div>
    </div>
</div>
<?php endif; ?>
<?php if($filterSize && count($filterSize['data'])>0): ?>
<?php
//dd($filterSize);
?>
<div class="filter-group acc-block acc-block-size collapsed">
    <div class="title acc-title">
        <span><?php echo e(__('web.size')); ?></span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content ">
        <?php $__currentLoopData = $filterSize['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $sizeDescription = $size->getSizeDescription($language->id);
        $filterCountSize = $size->getSizeCount($filterSize['productIds']);
        if ($filterCountSize)
        {
            //$filterCountColor = $filterCountColor['filter_count'];
        }
        else
        {
            continue;
        }
        ?>
        <div class="checkbox">
            <input type="checkbox" class="input-filter" data-filter-type="size" name="size[]" value="<?php echo e($size->id); ?>" <?php echo e(isset($appliedFilter['size'])? ((collect($appliedFilter['size'])->contains($size->id)) ? 'checked':''):''); ?>>
            <label><?php echo e($sizeDescription?ucfirst($sizeDescription->size_title):''); ?> (<?php echo e($filterCountSize); ?>)</label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="more size_more" data-count="<?php echo e(count($filterSize['data'])); ?>"><i class="icon-plus"></i><?php echo e(__('web.more')); ?></div>
    </div>
</div>
<?php endif; ?>
<!--Brand-->
<?php if(count($filterBrand)>0): ?>
<div class="filter-group acc-block acc-block-brand collapsed">
    <div class="title acc-title">
        <span><?php echo e(__('web.brand')); ?></span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        <?php $__currentLoopData = $filterBrand; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
        $brandDetail = $brand->getProductBrand($language->id);
        ?>
        <div class="checkbox">
            <input type="checkbox" class="input-filter" data-filter-type="brand" name="brand[]" value="<?php echo e($brand->brand_id); ?>" <?php echo e(isset($appliedFilter['brand'])? ((collect($appliedFilter['brand'])->contains($brand->brand_id)) ? 'checked':''):''); ?>>
            <label><?php echo e($brandDetail?ucfirst($brandDetail->brand_name):''); ?> (<?php echo e($brand->filter_count); ?>)</label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="more brand_more" data-count="<?php echo e(count($filterBrand)); ?>"><i class="icon-plus"></i><?php echo e(__('web.more')); ?></div>
    </div>
</div>
<?php endif; ?>

<!--Price-->
<?php if(count($filterPrice)>0): ?>
<div class="filter-group acc-block acc-block-price collapsed">
    <div class="title acc-title">
        <span><?php echo e(__('web.price')); ?></span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        <?php $__currentLoopData = $filterPrice; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priceRange): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="radio">
            <input type="radio" class="input-filter" name="price" value="<?php echo e($priceRange['value']); ?>" <?php echo e(isset($appliedFilter['price'])? ((collect($appliedFilter['price'])->contains($priceRange['value'])) ? 'checked':''):''); ?>>
            <label><?php echo e($priceRange['title']); ?> (<span><?php echo e($priceRange['count']); ?></span>)</label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="more  price_more" data-count="<?php echo e(count($filterPrice)); ?>"><i class="fa fa-plus"></i><?php echo e(__('web.more')); ?></div>
    </div>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/filter.blade.php ENDPATH**/ ?>
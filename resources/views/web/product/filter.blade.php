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
@if(count($categories)>0)
@foreach($categories as $category)
@if(isset($appliedFilter['sidebarSelectedCategoryId']) && $appliedFilter['sidebarSelectedCategoryId'] && $category->id != $appliedFilter['sidebarSelectedCategoryId'])
@continue
@endif
<div class="filter-group acc-block my_cat   {{$cuurent_category ?   $cuurent_category == $category->id ? 'expanded' : 'collapsed' : '' }}     {{$category->categories_slug}}">
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
    @if(count($subCategories))
    <div class="title acc-title">
        <span>{{$categoryName->categories_name?ucfirst($categoryName->categories_name):''}}</span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        @foreach($subCategories as $subCategory)
        <?php
        $cat = \App\AdminModel\Product\AdminCategory::find($subCategory->id); 
        $subCategoryName = $cat->getCategoryDescription($language->id);
        $allCategoryId[]  = $subCategory->id;
        ?>
        <div class="radio">
            <input  class="input-filter category-filter" type="radio" name="category[]" value="{{$subCategory->id}}" {{ isset($appliedFilter['category']) && $isSingleCategorySelected? ((collect($appliedFilter['category'])->contains($subCategory->id)) ? 'checked':''):'' }}>
            <label>{{$subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''}}</label>
        </div>
        @endforeach
        <div class="radio">
            <input  class="input-filter category-filter"  type="radio" name="category[]" value="all_{{$category->id}}" {{ isset($appliedFilter['category'])? ((collect($appliedFilter['category'])->contains('all_'.$category->id)) ? 'checked':''):'' }}>
            <label>{{__('web.all')}}</label>
        </div>
        <div class="more category_more"><i class="icon-plus"></i>{{__('web.more')}}</div>
    </div>
    @endif
</div>
@endforeach
@endif
@if($filterColor && count($filterColor['data'])>0)
<div class="filter-group acc-block acc-block-color collapsed">
    <div class="title acc-title">
        <span>{{__('web.color')}}</span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        @foreach($filterColor['data'] as $color)
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
            <input name="color[]" class="input-filter" data-filter-type="color"  type="checkbox" value="{{$color->id}}" {{ isset($appliedFilter['color'])? ((collect($appliedFilter['color'])->contains($color->id)) ? 'checked':''):'' }}>
            <label>{{$colorDetail?ucfirst($colorDetail->color_title):''}} ({{$filterCountColor}})</label>
        </div>
        @endforeach
        <div class="more color_more" data-count="{{count($filterColor['data'])}}"><i class="icon-plus"></i>{{__('web.more')}}</div>
    </div>
</div>
@endif
@if($filterSize && count($filterSize['data'])>0)
<?php
//dd($filterSize);
?>
<div class="filter-group acc-block acc-block-size collapsed">
    <div class="title acc-title">
        <span>{{__('web.size')}}</span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content ">
        @foreach($filterSize['data'] as $size)
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
            <input type="checkbox" class="input-filter" data-filter-type="size" name="size[]" value="{{$size->id}}" {{ isset($appliedFilter['size'])? ((collect($appliedFilter['size'])->contains($size->id)) ? 'checked':''):'' }}>
            <label>{{$sizeDescription?ucfirst($sizeDescription->size_title):''}} ({{$filterCountSize}})</label>
        </div>
        @endforeach
        <div class="more size_more" data-count="{{count($filterSize['data'])}}"><i class="icon-plus"></i>{{__('web.more')}}</div>
    </div>
</div>
@endif
<!--Brand-->
@if(count($filterBrand)>0)
<div class="filter-group acc-block acc-block-brand collapsed">
    <div class="title acc-title">
        <span>{{__('web.brand')}}</span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        @foreach($filterBrand as $brand)
        <?php
        $brandDetail = $brand->getProductBrand($language->id);
        ?>
        <div class="checkbox">
            <input type="checkbox" class="input-filter" data-filter-type="brand" name="brand[]" value="{{$brand->brand_id}}" {{ isset($appliedFilter['brand'])? ((collect($appliedFilter['brand'])->contains($brand->brand_id)) ? 'checked':''):'' }}>
            <label>{{$brandDetail?ucfirst($brandDetail->brand_name):''}} ({{$brand->filter_count}})</label>
        </div>
        @endforeach
        <div class="more brand_more" data-count="{{count($filterBrand)}}"><i class="icon-plus"></i>{{__('web.more')}}</div>
    </div>
</div>
@endif

<!--Price-->
@if(count($filterPrice)>0)
<div class="filter-group acc-block acc-block-price collapsed">
    <div class="title acc-title">
        <span>{{__('web.price')}}</span>
        <span class="pull-right">
            <i class="icon-down expand"></i>
            <i class="icon-up collapse"></i>
        </span>
    </div>
    <div class="options acc-content">
        @foreach($filterPrice as $priceRange)
        <div class="radio">
            <input type="radio" class="input-filter" name="price" value="{{$priceRange['value']}}" {{ isset($appliedFilter['price'])? ((collect($appliedFilter['price'])->contains($priceRange['value'])) ? 'checked':''):'' }}>
            <label>{{$priceRange['title']}} (<span>{{$priceRange['count']}}</span>)</label>
        </div>
        @endforeach
        <div class="more  price_more" data-count="{{count($filterPrice)}}"><i class="fa fa-plus"></i>{{__('web.more')}}</div>
    </div>
</div>
@endif
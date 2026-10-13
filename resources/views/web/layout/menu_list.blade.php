<?php
$language = Helper::getLanguage();
$user = Auth::user();
$categories = Helper::getFrontHeader('', '', '', '', '', '', '', '', false, false, $user);
?>

@foreach ($categories as $category)
<?php
$categoryName = $category->getCategoryDescription($language->id);
$subCategories = $category->getChildFront2($category->id, false, 'product', '', [], false, $user);

?>
@if(count($subCategories))
<li class="{{count($subCategories) > 0 ? 'dropdown' : ''}} ">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
        <span>{{$categoryName->categories_name?ucfirst($categoryName->categories_name):''}}</span>
        @if(count($subCategories) > 0)
        <i class="icon-plus collapsed"></i>
        <i class="icon-minus expanded"></i>
        @endif
    </a>

    @if(count($subCategories) > 0)
    <ul class="dropdown-menu">
        <li><a href="{{route("web.product.index",['slug'=>$subCategories->first()->categories_slug, 'isAll'=>1, 'sCId'=>$category->id])}}"><span>{{__('web.all')}}</span></a></li>
        @foreach($subCategories as $subCategory)
        <?php
         $cat = \App\AdminModel\Product\AdminCategory::find($subCategory->id); 
$subCategoryName = $cat->getCategoryDescription($language->id);

?>
        <li><a href="{{route("web.product.index",['slug'=>$subCategory->categories_slug])}}"><span>{{$subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''}}</span></a></li>
        @if($loop->last)
        <!--<li><a href="{{route("web.category.category",['slug'=>$category->categories_slug])}}"><span>{{__('web.all')}}</span></a></li>-->
        @endif
        @endforeach
    </ul>
    @endif

</li>
@endif
<!--<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
        <span>Bath</span>
        <i class="icon-plus collapsed"></i>
        <i class="icon-minus expanded"></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="products.html">Bath Towels</a></li>
        <li><a href="products.html">Beach Towels</a></li>
        <li><a href="products.html">Bath Rugs</a></li>
    </ul>
</li>
<li><a href="products.html"><span>Gift</span></a></li>-->

@endforeach

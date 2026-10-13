<?php
$language = Helper::getLanguage();
$categories = Helper::getFrontHeader();
?>

<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
$categoryName = $category->getCategoryDescription($language->id);
$subCategories = $category->getChildFront2($category->id, false, 'product');

?>
<?php if(count($subCategories)): ?>
<li class="<?php echo e(count($subCategories) > 0 ? 'dropdown' : ''); ?> ">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown-submenu" aria-expanded="false">
        <span><?php echo e($categoryName->categories_name?ucfirst($categoryName->categories_name):''); ?></span>
        <?php if(count($subCategories) > 0): ?>
        <i class="icon-plus collapsed"></i>
        <i class="icon-minus expanded"></i>
        <?php endif; ?>
    </a>

    <?php if(count($subCategories) > 0): ?>
    <ul class="dropdown-menu">
        <li><a href="<?php echo e(route("web.product.index",['slug'=>$subCategories->first()->categories_slug, 'isAll'=>1, 'sCId'=>$category->id])); ?>"><span><?php echo e(__('web.all')); ?></span></a></li>
        <?php $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
         $cat = \App\AdminModel\Product\AdminCategory::find($subCategory->id); 
$subCategoryName = $cat->getCategoryDescription($language->id);

?>
        <li><a href="<?php echo e(route("web.product.index",['slug'=>$subCategory->categories_slug])); ?>"><span><?php echo e($subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''); ?></span></a></li>
        <?php if($loop->last): ?>
        <!--<li><a href="<?php echo e(route("web.category.category",['slug'=>$category->categories_slug])); ?>"><span><?php echo e(__('web.all')); ?></span></a></li>-->
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <?php endif; ?>

</li>
<?php endif; ?>
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

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/menu_list.blade.php ENDPATH**/ ?>
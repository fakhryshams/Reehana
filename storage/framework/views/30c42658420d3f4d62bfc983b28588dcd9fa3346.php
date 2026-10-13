
<?php $__env->startSection('page_title', trans('web.product')); ?>
<?php $__env->startSection('body_class', 'listing'); ?>
<?php $__env->startSection('content'); ?>
<section class="breadcrumbs">
    <div class="container">
        <span><a href="<?php echo e(route('web.index')); ?>"><?php echo e(__('web.home')); ?></a></span>
        <span class="sep"></span>
        <?php if(request()->routeIs('web.product.new_arrivals')): ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__('web.new_arrivals')); ?></a></span>
        <?php elseif(request()->routeIs('web.product.latestCollection')): ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__('web.latest-collection')); ?></a></span>
        <?php elseif(request()->routeIs('web.product.sale')): ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__('web.sale')); ?></a></span>
        <?php elseif(request()->routeIs('web.product.list_by_tag')): ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__("web.$tag")); ?></a></span>
        <?php elseif(request()->routeIs('web.product.shop_by_brand')): ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__("web.brand")); ?></a></span>
        <?php elseif(isset($isAll) && $isAll==1 && isset($mainCategoryId) && $mainCategoryId): ?>
        <?php
        $mainCategory = "";
        $mainCategoryName = "";
        $mainCategory = Helper::getCategoryDetails($mainCategoryId);
        $mainCategoryName = $mainCategory->getCategoryDescription($language->id);
        ?>
        <?php if(isset($appliedFilter) && $appliedFilter['c'] && $appliedFilter['c'] != 'clear'): ?>
        <?php
        $cat = \App\AdminModel\Product\AdminCategory::whereIn('categories_slug', $appliedFilter['c'])->first();
        $mainCategory = Helper::getCategoryDetails($cat->parent_id);
        $mainCategoryName = $mainCategory->getCategoryDescription($language->id);
        ?>
        <?php endif; ?>
        <?php if($mainCategoryName): ?>
        <span class="catbreadcrumbs"><a href="<?php echo e(route("web.product.index",['slug'=>$mainCategory->categories_slug])); ?>"><?php echo e($mainCategoryName->categories_name?ucfirst($mainCategoryName->categories_name):''); ?></a></span>
        <?php endif; ?>
        <?php elseif(request()->routeIs('web.product.index')): ?>
        <?php if(isset($category) && $category): ?>
        <?php
        $mainCategory = "";
        $mainCategoryName = "";
        $subCategoryName = $category->getCategoryDescription($language->id);
        if (isset($category) && $category->parent_id) {
            $mainCategory = Helper::getCategoryDetails($category->parent_id);
            $mainCategoryName = $mainCategory->getCategoryDescription($language->id);
        }
        ?>
        <?php if(isset($appliedFilter) && $appliedFilter['c'] && $appliedFilter['c'] != 'clear'): ?>
        <?php
        $cat = \App\AdminModel\Product\AdminCategory::whereIn('categories_slug', $appliedFilter['c'])->first();
        $mainCategory = Helper::getCategoryDetails($cat->parent_id);
        $mainCategoryName = $mainCategory->getCategoryDescription($language->id);
        ?>
        <span class="catbreadcrumbs"><a href="<?php echo e(route("web.product.index",['slug'=>$mainCategory->categories_slug])); ?>"><?php echo e($mainCategoryName->categories_name?ucfirst($mainCategoryName->categories_name):''); ?></a></span>
        <span class="sep sep2"></span>
        <?php elseif($mainCategoryName): ?>
        <span class="catbreadcrumbs"><a href="<?php echo e(route("web.product.index",['slug'=>$mainCategory->categories_slug])); ?>"><?php echo e($mainCategoryName->categories_name?ucfirst($mainCategoryName->categories_name):''); ?></a></span>
        <span class="sep sep2"></span>
        <?php endif; ?>
        <?php if(isset($appliedFilter) && $appliedFilter['c'] && $appliedFilter['c'] != 'clear'): ?>


        <?php
        $cat = \App\AdminModel\Product\AdminCategory::whereIn('categories_slug', $appliedFilter['c'])->first();
        $subCategoryName = $cat->getCategoryDescription($language->id);
        ?>
        <span class="newbreadcrumbs"><a href="<?php echo e(route("web.product.index",['slug'=>$category->categories_slug])); ?>"><?php echo e($subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''); ?></a></span>

        <?php elseif($subCategoryName): ?>
        <span class="newbreadcrumbs"><a href="<?php echo e(route("web.product.index",['slug'=>$category->categories_slug])); ?>"><?php echo e($subCategoryName->categories_name?ucfirst($subCategoryName->categories_name):''); ?></a></span>
        <?php endif; ?>
        <?php else: ?>
        <span><a href="<?php echo e(url()->current()); ?>"><?php echo e(__('web.product')); ?></a></span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<section class="products-and-filters">
    <div class="container-fluid">
        <div class="h-flex">
            <div class="col-md-3 filters" id="filters-handheld">
                <div class="filter-title">
                    <span><?php echo e(__('web.filters')); ?></span>
                    <span class="pull-right link-button clear-button hide sm-hidden clear-all"><?php echo e(__('web.clear_all')); ?></span>
                    <span class="pull-right link-button sm-visible" id="closeFilters"><i class="icon-close"></i></span>
                </div>
                <div class="filters-section">
                    <div class="filter-body fancy">
                        <?php echo $filterPage; ?>

                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-secondary clear-all"><?php echo e(__('web.clear')); ?></button>
                    <button class="btn btn-secondary"><?php echo e(__('web.show')); ?></button>
                </div>
            </div>
            <div id="filters-overlay" class="filters-overlay"></div>
            <div class="col-md-9 products">
                <div class="h-flex">
                    <div id="apply-filters">
                        <span><?php echo e(__('web.filters_caps')); ?></span>
                        <img src="<?php echo e(asset('front/img/icon-filter.png')); ?>">
                    </div>
                    <div class="gap"></div>
                    <div class="sorting">
                        <select class="select-component sort" name="filter" id="filter_group">
                            <?php $productFilter = config('custom_web.productFilter' . $language->id) ? config('custom_web.productFilter' . $language->id) : []; ?>
                            <?php $__currentLoopData = $productFilter; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(($key == $sortBy)?'selected':''); ?>><?php echo e($record); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="products-list">
                    <div class="row">
                        <?php echo $__env->make('web/product/products', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
                <?php
                $page = 1;
                if (Request::get('page')) {
                    $page = Request::get('page');
                }
                ?>
                <div class="load-more show_more_btn" style="display:none">
                    <button class="btn btn-secondary show_more" page-productcount="<?php echo e($pageProductCount); ?>" total-product="<?php echo e($productCount); ?>" data-page="<?php echo e($page); ?>"><?php echo e(__('web.show_more')); ?></button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_specific_js'); ?>
<script>
    var categoryFilter;
    var colorFilter;
    var sizeFilter;
    var applyscroll = true;
    var sizemore = false;
    var category_more = false;
    var color_more = false;
    var brand_more = false;
    var price_more = false;

    var pageLim = "<?php echo e(config('custom_web.product_limit', 30)); ?>";
    $(document).ready(function() {



        /*
         * Filter Clear button
         */
        collapseExpand();
        checkSizeMore();
        manageFilter();
        bindFilterUIClick();
        $("#apply").click(function() {
            $(".products-and-filters").removeClass("showFilters").addClass("hideFilters");
            $("body").removeClass("no-scroll");
        });
        $("select[name=filter]").change(function() {
            applyFilter(true);
        });
        showMore('<?php echo e($pageLimit); ?>');
        setImageInlineCSS();
        <?php if(request()->routeIs('web.product.new_arrivals')): ?>
        history.replaceState(null, null, '/whats-new');
        <?php endif; ?>
    });

    $(window).scroll(function() {
        if ($(window).scrollTop() > ($(document).height() - $(window).height() - 300)) {
            var pageLimit = $('.show_more').attr('page-productcount');
            var totalProduct = $('.show_more').attr('total-product');


            var value = $(".show_more").attr('data-page');

            var currentlimit = parseInt(value) * parseInt(pageLim);

            var tot = parseInt(totalProduct) - parseInt(currentlimit);
            console.log(tot);
            if (tot > 0 && applyscroll) {
                applyscroll = false;
                $("#preloader").fadeIn();
                $(".show_more").attr('data-page', parseInt(value) + 1);
                applyFilter(false);
            }

        }
    });

    function checkSizeMore() {

        $(document).on('click', 'body', function() {

            if ($('.size_more i').hasClass('icon-minus')) {
                sizemore = true;

            }
            if ($('.category_more i').hasClass('icon-minus')) {
                category_more = true;

            }
            if ($('.color_more i').hasClass('icon-minus')) {
                color_more = true;

            }
            if ($('.brand_more i').hasClass('icon-minus')) {
                brand_more = true;

            }
            if ($('.price_more i').hasClass('icon-minus')) {
                price_more = true;

            }
        });

    }


    function showMore(pageLimit) {
        $('.show_more_btn').hide();
        var pageLimit = pageLimit;
        if (pageLimit > 1) {
            //   $('.show_more_btn').show();
        }


        $(".show_more").unbind('click');
        $(".show_more").click(function() {
            //var value = $(".show_more").attr('data-page');
            // $(".show_more").attr('data-page', parseInt(value) + 1);
            //applyFilter();
        });
    }

    function applyFilter(resetPage) {
        if (resetPage) {
            $(".show_more").attr('data-page', 1);
        }
        $.ajax({
            type: 'POST',
            url: "<?php echo e(route('web.product.apply-filter')); ?>",
            beforeSend: function() {
                $("input").attr('disabled', true);
            },
            complete: function() {
                $("input").removeAttr('disabled');
                setImageInlineCSS();
            },
            data: {
                '_token': '<?php echo e(csrf_token()); ?>',
                'page': $(".show_more").attr('data-page'),
                'sortby': $("select[name=filter]").val(),
                'filterType': '<?php echo e($filterType); ?>',
                'slug': '<?php echo e($slug); ?>',
                'showIn': '<?php echo e(isset($showIn)?$showIn:""); ?>',
                'category': getFilterValues("category[]"),
                'color': getFilterValues("color[]"),
                'price': $("input[name='price']:checked").val(),
                'size': getFilterValues("size[]"),
                'subCategoryId': '<?php echo e($filteredCategoryId); ?>',
                'mainCategoryId': '<?php echo e($mainCategoryId); ?>',
                'search': '<?php echo e(isset($search)?base64_encode($search):""); ?>',
                'brand': getFilterValues("brand[]"),
            },
            success: (data) => {
                if (data.catbreadcrumbs) {

                    $('.catbreadcrumbs').html(data.catbreadcrumbs);
                }
                if (data.newbreadcrumbs) {

                    $('.newbreadcrumbs').html(data.newbreadcrumbs);
                    $('.newbreadcrumbs').show();
                    $('.sep2').show();
                } else {
                    $('.newbreadcrumbs').hide();
                    $('.sep2').hide();

                }
                if (data.pageLimit) {
                    $('.show_more').attr('page-productcount', data.pageProductCount);
                }

                $(".filter-body").html(data.filterPage);
                if (resetPage) {
                    $(".products-list .row").html(data.productPage);
                } else {
                    var tempContainer = $('<div>').html(data.productPage);
                    tempContainer.find('.col-md-4').each(function() {
                        var productId;
                        var hiddenInput = $(this).find('input[type="hidden"]');
                        if (hiddenInput.length) {
                            var cls = hiddenInput.attr('class') || '';
                            var match = cls.match(/image_(\d+)/);
                            if (match) {
                                productId = match[1];
                            }
                        }
                        if (!productId || $(".products-list .row").find('.image_' + productId).length === 0) {
                            $(".products-list .row").append(this);
                        }
                    });
                }
                $(".results").text(data.productCountText);
                binding();
                manageFilter();
                showMore(data.pageLimit);
                bindFilterUIClick();
                changeCategory('<?php echo e($filterType); ?>');

                /*
                 * For responsive view - active tab
                 */
                var activeTabId = $("#filters-handheld ul.filter-tabs > li.active").attr("id");
                $("#filters-handheld .filter-group").removeClass("show");
                $("." + activeTabId).addClass("show");
                //                if(lastClick)
                //                {
                //                    $(".acc-block-"+lastClick).removeClass("collapsed");
                //                    $(".acc-block-"+lastClick).addClass("expanded");
                //                }

                collapseExpand();
                bindWishListClick();
                if ('<?php echo e($filterType); ?>' == 'shop_by_brand') {
                    //window.location.reload();
                }

                if (data.main_category_slug) {
                    //                    alert(data.main_category_slug);
                    $('.my_cat').removeClass('expanded');
                    $('.my_cat').addClass('collapsed');
                    $('.' + data.main_category_slug).removeClass('collapsed');
                    $('.' + data.main_category_slug).addClass('expanded');
                }

                $('.show_more').attr('total-product', data.productCount);

                if (sizemore && parseInt(data.sizeCount) > 4) {
                    $(".size_more").trigger("click");
                }

                if (!category_more && parseInt($(".category_more").attr('data-count')) > 4) {
                    $(".category_more").trigger("click");
                }
                console.log(parseInt(data.colorCount));
                if (color_more && parseInt(data.colorCount) > 4) {
                    $(".color_more").trigger("click");
                }
                if (brand_more && parseInt(data.brandCount) > 4) {
                    $(".brand_more").trigger("click");
                }
                if (price_more && parseInt(data.priceCount) > 4) {
                    
                    $(".price_more").trigger("click");
                }
                $("#preloader").fadeOut("slow");
                applyscroll = true;
            },
            error: function(xhr) {

            }
        });
    }

    function collapseExpand() {
        var showClearAllVal = false;
        if (getFilterValues("color[]") != "") {
            $(".acc-block-color").removeClass("collapsed");
            $(".acc-block-color").addClass("expanded");
            showClearAllVal = true;
        } else {
            $(".acc-block-color").removeClass("expanded");
            $(".acc-block-color").addClass("collapsed");
        }

        if (getFilterValues("size[]") != "") {
            $(".acc-block-size").removeClass("collapsed");
            $(".acc-block-size").addClass("expanded");
            showClearAllVal = true;
        } else {
            $(".acc-block-size").removeClass("expanded");
            $(".acc-block-size").addClass("collapsed");
        }

        if (getFilterValues("brand[]") != "") {
            $(".acc-block-brand").removeClass("collapsed");
            $(".acc-block-brand").addClass("expanded");
            showClearAllVal = true;
        } else {
            $(".acc-block-brand").removeClass("expanded");
            $(".acc-block-brand").addClass("collapsed");
        }

        if (getFilterValues("price") != "") {
            $(".acc-block-price").removeClass("collapsed");
            $(".acc-block-price").addClass("expanded");
            showClearAllVal = true;
        } else {
            $(".acc-block-price").removeClass("expanded");
            $(".acc-block-price").addClass("collapsed");
        }

        if (getFilterValues("category[]") != "") {
            //showClearAllVal = true;
        }

        if (showClearAllVal) {
            showClearAll(true);
        } else {
            showClearAll(false);
        }
    }

    function getFilterValues(inputName) {
        var values = $("input[name='" + inputName + "']:checked")
            .map(function() {
                return $(this).val();
            }).get();
        return values;
    }

    function manageFilter() {
        /*
         * Sorting
         */

        /*
         * Filter
         */
        $(".clear-all").unbind('click');
        $(".clear-all").click(function() {
			
			var filtertype = '<?php echo e($filterType); ?>';
            //            $("input[name='category[]']").prop('checked', false);
            $("input[name='color[]']").prop('checked', false);
            $("input[name='price']").prop('checked', false);
            $("input[name='size[]']").prop('checked', false);
			if(filtertype != 'shop_by_brand')
			{
            $("input[name='brand[]']").prop('checked', false);
            }
			applyFilter(true);
            $(".products-and-filters").removeClass("showFilters").addClass("hideFilters");
            $("body").removeClass("no-scroll");

            $(".size_more").trigger("click");
            $(".category_more").trigger("click");
            $(".color_more").trigger("click");
            $(".brand_more").trigger("click");
            $(".price_more").trigger("click");
        });

        $(".clear-category").unbind('click');
        $(".clear-category").click(function() {
            $("input[name='category[]']").prop('checked', false);
            applyFilter(true);
        });
        $(".clear-color").unbind('click');
        $(".clear-color").click(function() {
            $("input[name='color[]']").prop('checked', false);
            applyFilter(true);
        });
        $(".clear-price").unbind('click');
        $(".clear-price").click(function() {
            $("input[name='price']").prop('checked', false);
            applyFilter(true);
        });
        $(".clear-size").unbind('click');
        $(".clear-size").click(function() {
            $("input[name='size[]']").prop('checked', false);
            applyFilter(true);
        });
        /*
         * /.Filter Clear button
         */

        /*
         * On click of any filter
         */
        $(".check-group-header-filter").unbind('change');
        $(".check-group-header-filter").change(function() {
            if ($(this).find("input").is(":checked")) {
                $(this).next().children().find("input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $(this).next().children().find("input").each(function() {
                    $(this).prop('checked', false);
                });
            }
            applyFilter(true);
        });

        $(".check-group .checkbox").unbind('change');
        $(".check-group .checkbox").change(function() {
            var x = $(this).parent().children().length;
            var c = 0;
            $(this).parent().find("input").each(function() {
                if ($(this).is(":checked")) {
                    c++;
                }
            });
            if (c == x) {
                $(this).parent().prev(".check-group-header-filter").find("input").prop("checked", true);
            } else {
                $(this).parent().prev(".check-group-header-filter").find("input").prop("checked", false);
            }
            applyFilter(true);
        });

        $(".input-filter").unbind('change');
        $(".input-filter").change(function() {
            //lastClick = $(this).attr('data-filter-type');
            applyFilter(true);
        });

        $(".filters .acc-content .more").unbind('click');
        $(".filters .acc-content .more").click(function() {
            if ($(this).parents(".acc-content").hasClass("showall")) {
                $(this).parents(".acc-content").removeClass("showall");
                $(this).html('<i class="icon-plus"></i>more');
            } else {
                $(this).parents(".acc-content").addClass("showall");
                $(this).html('<i class="icon-minus"></i>less');
            }
        });
    }

    function changeCategory(filterType = false) {
        $("input[name='category[]").unbind('change');
        $("input[name='category[]").change(function() {
            if (filterType != 'shop_by_brand') {
                $("input[name='brand[]']").prop('checked', false);
            }

            $("input[name='color[]']").prop('checked', false);
            $("input[name='size[]']").prop('checked', false);
            $("input[name='price[]']").prop('checked', false);
            applyFilter(true);
        });

    }

    function showClearAll(show = false) {
        if (show) {
            $(".filters .filter-title span.clear-button").removeClass("hide");
            $(".filters .filter-title span.clear-button").fadeIn();
        } else {
            $(".filters .filter-title span.clear-button").fadeOut();
        }
    }

    function setImageInlineCSS() {
        //        $(".product .product-image").each(function ()
        //        {
        //            var ht = $(this).parent().width() * 1.2;
        //            ht = "max-height: " + ht + "px; height: " + ht + "px";
        //            $(this).attr("style", ht);
        //        });
    }
</script>
<?php echo $__env->make('web.product.page-ajax', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('web/layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/product/index.blade.php ENDPATH**/ ?>
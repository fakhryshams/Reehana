

var incremnt_id = 1;

$(window).on('load', function () {
    $("#preloader").fadeOut("slow");
});


var base_url = $('#base_url').val();
$(document).ready(function () {

  $("span.tt").click( function() {
        $(this).find(".tt-text").addClass("show-tt");
        setTimeout(() => {
            $(this).find(".tt-text").removeClass("show-tt");
        }, 5000);
    });
    removeMonogram();
    $(".cancel_monogram").click(function () {
        $("#preloader").fadeIn();
        $('#addMonogram').modal('hide');
        var product_id = $('.cancel_monogram').attr('data-productId');
        var colorExist = $('.cancel_monogram').attr('data-colorexist');
        var sizeExist = $('.cancel_monogram').attr('data-sizeexist');
        var monogramNo = $('.cancel_monogram').attr('data-monogram');
        var color = $('.cancel_monogram').attr('data-color');
        var id = $('.cancel_monogram').attr('data-id');
        var size = $('.cancel_monogram').attr('data-size');

        var is_edit = sessionStorage.getItem("is_edit");

        //        if (!is_edit)
        //        {

        Monogram(product_id, sizeExist, size, colorExist, color, false, '', monogramNo);
        //        }
        $("#preloader").fadeOut("slow");
    });

    $("span.tt").click(function () {
        $(this).find(".tt-text").addClass("show-tt");
        setTimeout(() => {
            $(this).find(".tt-text").removeClass("show-tt");
        }, 5000);
    });

    var vht = $(window).height() + 350;
    if ($(document).height() > vht) {
        $(".menu .navbar").addClass("fixedNav");
    }

    var product_id = $('.addMonogramButton').attr('data-productid');
    var issize = $('.addMonogramButton').attr('data-sizeexist');
    var iscolor = $('.addMonogramButton').attr('data-colorexist');
    if (product_id) {
        Monogram(product_id);
    }



    $(".addMonogramLink").click(function () {
        var product_id = $(this).attr('data-productId');
        var productId = $(this).attr('data-productId');
        var id = $(this).attr('data-id');
        var total_monogram = $(".monogramSummary-" + id).attr('data-total');
        var monogram_id = parseInt(total_monogram) + 1;
        $(".monogramSummary").attr('data-total', monogram_id);
        var page = $(this).attr('data-page');
        var colorExist = $(this).attr('data-colorexist');
        var sizeExist = $(this).attr('data-sizeexist');
        var size = $(this).attr('data-size');
        var color = $(this).attr('data-color');
        $('.save_monogram').attr('data-monogramexist', 1);
        $('.save_monogram').attr('data-id', id);
        $('.save_monogram').attr('data-sizeexist', sizeExist);
        $('.save_monogram').attr('data-colorexist', colorExist);
        $('.save_monogram').attr('data-size', size);
        var productKey = '';
        if (sizeExist == 1 && colorExist == 1) {

            productKey = productId + '_' + size + '_' + color;
        } else if (sizeExist == 1) {
            productKey = productId + '_' + size + '_c';
        } else if (colorExist == 1) {
            productKey = productId + '_s_' + color;
        } else {
            productKey = productId + '_s_c';
        }

        var qty = $('input[name="quantity_' + productKey + '"]').val();

        $('.save_monogram').attr('data-id', productId);

        $('.save_monogram').attr('data-mono-id', productKey + '_' + monogram_id);
        $('.save_monogram').attr('data-update-id', productKey);
        $('.save_monogram').removeAttr('data-editmonogram');


        Monogram(product_id, sizeExist, size, colorExist, color, false, id);


        if (page == 'cart') {
            $('.save_monogram').attr('data-updateqty', 1);
        } else {
            $('.save_monogram').attr('data-updateqty', 0);
        }
        $('#addMonogram').modal({
            backdrop: 'static',
            show: true
        });
    });
    //------------ monogram begin-----
    //   
    //------------ monogram end-----




    $(document).on("click", "input[name='font']", function (e) {

        var product_id = $(this).attr('data-id');
        var sizeExist = $(this).attr('data-size');
        var colorExist = $(this).attr('data-color');
        var size = $(this).attr('data-size-id');
        var color = $(this).attr('data-color-id');
        getInitials($("input[name='font'][checked='checked']").val(), product_id, sizeExist, size, colorExist, color, '');

        getPlacementColor($("input[name='font'][checked='checked']").val(), '', '', product_id);
    });

    $(".initials-inputs .initials-input input").focus(function () {
        $(this).select();
    });



    $("#sidenav-overlay, #closeMenu, #menu-handheld #close, .menu .quick-menu a").click(function () {
        if ($("#menu-handheld").hasClass("showMenu")) {
            $("#menu-handheld").removeClass("showMenu").addClass("hideMenu");
            $("#sidenav-overlay").removeClass("show");
            $(".navbar-toggler").removeClass("opened");
        }
    });
    $("#cart-overlay, #closeCart").click(function () {
        $("#cart").removeClass("showCart").addClass("hideCart");
        $("#cart-overlay").removeClass("show").addClass("hide");
        addScroll();
    });
    $(".navbar-toggler").click(function () {
        if ($(this).hasClass("opened")) {
            $("#menu-handheld").removeClass("showMenu").addClass("hideMenu");
            $(this).removeClass("opened");
        } else {
            $("#menu-handheld").removeClass("hideMenu").addClass("showMenu");
            $(this).addClass("opened");
        }
    });

    $("#filters-overlay, #closeFilters, #filters-handheld .filter-actions .btn:last-child").click(function () {
        $(".products-and-filters").removeClass("showFilters").addClass("hideFilters");
        addScroll();
    });
    $("#apply-filters").click(function () {
        $(".products-and-filters").removeClass("hideFilters").addClass("showFilters");
        remScroll();
    });

    $(".address-item").click(function () {
        $(".address-item").each(function () {
            $(this).removeClass("selected");
        });
        $(this).addClass("selected");
    });

    $("#trendingProducts").slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        dots: !1,
        arrows: !0,
        responsive: [{
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });

    $("#slider").slick({
        autoplay: true,
        infinite: !0,
        speed: 1500,
        fade: !0,
        dots: !0,
        arrows: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        appendArrows: '.homepage-slider > .container',
        prevArrow: "<span class='back'></span>",
        nextArrow: "<span class='forward'></span>",
        responsive: [{
            breakpoint: 767,
            settings: {
                dots: !0,
                arrows: !1
            }
        }]
    });
    $("#productSlider").slick({
        infinite: !1,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !0,
        dots: !0,
        appendArrows: '#arrowsContainer',
        asNavFor: "#prodThumbs",
        prevArrow: "<span class='icon-left'><i class='fa fa-chevron-left'></i></span>",
        nextArrow: "<span class='icon-right'><i class='fa fa-chevron-right'></i></span>",
        responsive: [{
            breakpoint: 768,
            settings: {
                dots: !0
            }
        }]
    }), $("#prodThumbs").slick({
        infinite: !1,
        slidesToShow: 100,
        slidesToScroll: 1,
        vertical: !0,
        asNavFor: "#productSlider",
        dots: !1,
        prevArrow: "<a href='javascript:;' class='icon-left'><i class='fa fa-caret-up'></i></a>",
        nextArrow: "<a href='javascript:;' class='icon-right'><i class='fa fa-caret-down'></i></a>",
        focusOnSelect: !0
    });
    setTimeout(() => {
        $("#productSlider, #prodThumbs").slick("slickUnfilter");
        $("#productSlider, #prodThumbs").slick("slickFilter", ".general");
        filterImages();
    }, 1500);
    $("select.selectColor").on("change", function (e) {
        var filterClass = "." + $(this).val();
        $("#productSlider, #prodThumbs").slick("slickUnfilter");
        $("#productSlider, #prodThumbs").slick("slickFilter", filterClass);
        //        filterImages();
    });


    function filterImages() {
        var $slider = $('#productSlider, #prodThumbs');
        $slider.each(function (index, slide) {
            s_id = 0;
            //console.log(index,slide,s_id);
            $(".slick-slide:not(.slick-cloned)", slide).each(function (s_index, s_slide) {
                $(this).attr("data-slick-index", s_id);
                s_id = s_id + 1;
                if (index == 0) {
                    if ($(this).hasClass("slick-current")) {
                        active_index = s_id;
                    }
                }
                if (index == 1) {
                    if (s_id == active_index) {
                        $(this).addClass("slick-current");
                    } else {
                        $(this).removeClass("slick-current");
                    }
                }
            });
        });
    }

    $("#similarProducts").slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        responsive: [{
            breakpoint: 980,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1
            }
        }, {
            breakpoint: 610,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        }]
    });


    initMobileSlider();
    function initMobileSlider() {
        $(document).on('click', '.cloud-zoom', function () {
            remScroll();
            var img = $(this).attr('data-zoom-url');

            var html = '<div class="mProductZooming">';
            html += '<span class="closeZooming"><i class="fas fa-times"></i></span>';
            html += '<img class="mProduct" src="' + img + '">';
            html += '</div>';
            $('body').append(html);
        });

        $(document).on('click', '.closeZooming', function () {
            addScroll();
            $('.mProductZooming').remove();
        });
    }

    $(".addComment .link-button, .addNote-actions .addNote").click(function (e) {
        var ic = $(this).find("i");
        if (ic.hasClass("icon-plus"))
            ic.removeClass("icon-plus").addClass("icon-minus");
        else
            ic.removeClass("icon-minus").addClass("icon-plus");

        var el = $(this).parent().find(".addNoteInput");
        if (el.hasClass("show"))
            el.removeClass("show");
        else
            el.addClass("show").removeClass('hide');
    });
    $(".addNoteInput .actions .btn-secondary").click(function (e) {
        var el = $(this).closest(".addNoteInput");
        if (el.hasClass("show"))
            el.removeClass("show");
        else
            el.addClass("show");
    });
    $(".textarea > .actions > .link-button-grey").click(function (e) {
        $(this).closest(".textarea").removeClass("show");
    });

    $(".profile ul.order-tabs > li").click(function (e) {
        var el = $(this).prop("class");
        $(".profile ul.order-tabs > li").each(function () {
            $(this).removeClass("active");
        });
        $(".profile .orders-content > div").each(function () {
            $(this).removeClass("show");
        });
        $(this).addClass("active");
        $(".profile .tab-content").find("div#" + el).addClass("show");
    });
    $("#save-profile").click(function () {
        $(".edit-mode").removeClass("show");
        $(".view-mode").addClass("show");
    });
    $(".fav").click(function () {
        $(this).toggleClass("far fas");
    });
    $(".faq-section h5").click(function () {
        if ($(this).parent().hasClass("show")) {
            $(this).parent().removeClass("show");
        } else {
            $(".question").each(function () {
                $(this).removeClass("show");
            });
            $(this).parent().addClass("show");
        }
    });

    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (window.matchMedia("(min-width: 767px)").matches) {
            $(".menu .quick-menu .dropdown-menu").fadeOut();
            setTimeout(function () {
                $(".menu .quick-menu .dropdown-menu").removeAttr("style");
                $(".menu .quick-menu .dropdown-toggle").attr("aria-expanded", "false");
                $(".menu .quick-menu .dropdown").removeClass("open");
            }, 3000);
        }
    });

    /*
     * Commented by Mitesh
     $('.acc-block > .acc-title').on('click', function () {
     
     var ele = $(this).parent();
     if (ele.hasClass('expanded')) {
     ele.removeClass('expanded').addClass('collapsed');
     $(this).find("span.ecicon > span").removeClass('minus-circle').addClass('plus-circle');
     } else {
     ele.addClass('expanded').removeClass('collapsed');
     $(this).find("span.ecicon > span").addClass('minus-circle').removeClass('plus-circle');
     }
     });
     */

    function addScroll() {
        $("body").removeClass("no-scroll");
    }
    function remScroll() {
        $("body").addClass("no-scroll");
    }
    $("#gifts .options .radio").click(function () {
        var opt = $("#gifts .options input[name='rType1']:checked").val();
        $(".customized").removeClass("gifts").addClass("giveaways");
        $("#giveaways")[0].reset();
        $("#giveaways > div").children(".form-item").each(function () {
            $(this).removeClass("valid").removeClass("invalid").removeClass("mandatory");
            $(this).children("select").removeClass("mandatory");
            $(this).children("i").empty();
        });
    });
    $("#giveaways .options .radio").click(function () {
        var opt = $("#giveaways .options input[name='rType2']:checked").val();
        $(".customized").removeClass("giveaways").addClass("gifts");
        $("#gifts")[0].reset();
        $("#gifts > div").children(".form-item").each(function () {
            $(this).removeClass("valid").removeClass("invalid").removeClass("mandatory");
            $(this).children("select").removeClass("mandatory");
            $(this).children("i").empty();
        });
    });
    $("#billing").change(function () {
        var billing = `<div class="form-item col-md-6 col-sm-12">
                            <label class="required">Mobile No</label>
                            <input type="text" data-type="numbers" id="addMobile" name="pMobile" class="required" placeholder="Phone*" maxlength="10">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Email</label>
                            <input type="email" data-type="email" id="addEmail" name="pEmail" class="required" placeholder="youremail@email.com" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">First Name</label>
                            <input type="text" data-type="alphabetsWSpaces" id="addFirstname" name="pfirstname" class="required" placeholder="First Name*">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Last Name</label>
                            <input type="text" data-type="alphabetsWSpaces" id="addLastname" name="plastname" class="required" placeholder="Last Name*">
                            <i class="error"></i>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Country</label>
                            <select class="select-component untouched required" id="countrySelect">
                                <option value="0">Select Country</option>
                                <option value="Kuwait" selected="selected">Kuwait</option>
                                <option value="Dubai">Dubai</option>
                                <option value="India">India</option>
                            </select>
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12" id="citySelect">
                            <label>City</label>
                            <select class="select-component">
                                <option value="0">Select City</option>
                                <option value="1">City 1</option>
                                <option value="2">City 2</option>
                                <option value="3">City 3</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 hide" id="cityInput">
                            <label>City</label>
                            <input type="text" placeholder="Enter City">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 hide" id="areaSelect">
                            <label>Area</label>
                            <select class="select-component">
                                <option value="0">Select Area</option>
                                <option value="1">Area 1</option>
                                <option value="2">Area 2</option>
                                <option value="3">Area 3</option>
                            </select>
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 hide" id="areaInput">
                            <label>Area</label>
                            <input type="text" data-type="others" id="addArea" name="area" class="required" placeholder="Area">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Block</label>
                            <input type="text" data-type="others" id="addBlock" name="block" class="required" placeholder="Block">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Street</label>
                            <input type="text" data-type="others" id="addStreet" name="street" class="required" placeholder="Street*">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">House #<span>*</span>:</label>
                            <input type="text" data-type="others" id="addHouse" name="house" class="required" placeholder="House Number*">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label class="required">Floor / Flat</label>
                            <input type="text" data-type="others" id="addFloor" name="floor" placeholder="Floor / Flat">
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-12 col-sm-12">
                            <label class="required">Address Details</label>
                            <input type="text" data-type="others" id="addDetail" name="detail" placeholder="Address Detail">
                            <i class="error"></i>
                        </div>`;
        $(document).on("change", "select.select-component", function (e) {
            var x = $(this).children("option:selected").val();
            $(this).removeClass("untouched");
            if ($(this).hasClass("required")) {
                var x = $(this).children("option:selected").val();
                if (x == 0) {
                    $(this).next("i.error").html('mandatory field');
                    $(this).addClass("mandatory");
                } else if (x == "others") {
                    $(this).removeClass("mandatory");
                    $("#cityInput1").removeClass("hide").addClass("untouched").addClass("required");
                    $("#cityInput1 label").addClass("required");
                } else if (x == "Dubai") {
                    $("#citySelect1").removeClass("hide");
                    $("#citySelect1 select").addClass("untouched").addClass("required");
                    $("#citySelect1 label").addClass("required");
                    $("#areaSelect1, #areaInput1, #cityInput1").addClass("hide").removeClass("untouched").removeClass("required");
                    $("#areaSelect1 select, #cityInput1 select").addClass("untouched").addClass("required");
                    $("#areaSelect1 label, #areaInput1 label, #cityInput1 label").removeClass("required");
                } else if (x == "Kuwait") {
                    $("#areaSelect1").removeClass("hide");
                    $("#areaSelect1 select").addClass("untouched").addClass("required");
                    $("#areaSelect1 label").addClass("required");
                    $("#areaInput1, #citySelect1, #cityInput1").addClass("hide");
                    $("#citySelect1 select, #cityInput1").removeClass("untouched").removeClass("required");
                    $("#areaInput label, #citySelect1 label, #cityInput1 label").removeClass("required");
                } else if (x == "India") {
                    $("#cityInput1").removeClass("hide").addClass("untouched").addClass("required");
                    $("#cityInput1 label").addClass("required");
                    $("#areaSelect1, #areaInput1, #citySelect1").addClass("hide");
                    $("#citySelect1 select, #areaSelect1 select").removeClass("untouched").removeClass("required");
                    $("#citySelect1 label, #areaInput1 label, #areaSelect1 label").removeClass("required");
                }
            }
        });
        if ($(this).is(':checked')) {
            $("#billing-address").empty();
            $(".billing-address .address-item").addClass("hide");
            $("#same-address").removeClass("hide");
        } else {
            $("#billing-address").html(billing);
            $(".billing-address .address-item").removeClass("hide");
            $("#same-address").addClass("hide");
        }
    });

    // Stepper Component
    $(".increase").click(function () {

        var id = $(this).attr('data-id');
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = parseInt($(this).closest(".counter").find(".prod-count").val());
        if (isNaN(x)) {
            $(this).closest(".counter").find(".prod-count").val("1");
        } else if (x >= qty) {
            $(this).closest(".form-item").find(".remains").addClass("red");
            return;
        } else {
            $(this).closest(".counter").find(".prod-count").val(x + 1);
            $(this).closest(".counter").attr("data-quantity", $(this).closest(".counter").find('input[name="quantity_' + id + '"]').val());
            if ($(this).closest(".cart-item").length > 0) {
                if ($('input[name="applyAll_' + id + '"]').is(":checked")) {

                    $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
                } else {

                    $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
                }

            } else if ($(this).closest(".product-options").length > 0) {
                //$(".addMonogramButton").removeAttr("disabled");
                if ($('input[name="applyAll_' + id + '"]').is(":checked")) {

                    $(".addMonogramButton").attr("disabled", "disabled");
                } else {

                    $(".addMonogramButton").removeAttr("disabled");
                }
            }
            $(this).closest(".counter").find(".decrease").removeAttr("disabled");
        }
        if (parseInt($(this).closest(".counter").attr("data-quantity")) > 1 && $(".products-detail .monogramSummary .monogram-information-" + id).length > 1) {

            $(this).closest(".applyMonogramStyles").removeClass("hide");
        } else {
            $(this).closest(".applyMonogramStyles").addClass("hide");
        }
        var selMonograms = 0;
        if ($(this).closest(".cart-item").length > 0) {
            selMonograms = $(this).closest(".cart-item").find(".monogram-information-" + id).length;
        } else if ($(this).closest(".product-options").length > 0) {
         
            selMonograms = $(this).closest(".products-detail").find(".monogram-information-" + id).length;
        }
        // alert(id);
        var finalQty = $(this).closest(".counter").find('input[name="quantity_' + id + '"]').val();
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        /* if (finalQty > 1 && selMonograms == 1) {
         
         $(".applyMonogramStyles").removeClass("hide");
         
         } else {
         
         $(".applyMonogramStyles").addClass("hide");
         
         } */


        if (finalQty >= 1 && selMonograms == 1) {

            $(this).closest('.pd').find(".applyMonogramStyles").removeClass("hide");
            if ($('input[name="applyAll_' + id + '"]').is(":checked")) {
                $(".addMonogramButton").attr("disabled", "disabled");
            } else {
                $(".addMonogramButton").removeAttr("disabled");
            }
        } else {

            $(this).closest('.pd').find(".applyMonogramStyles").addClass("hide");
        }
    });
    $(".decrease").click(function () {
        var selectedQty = 0;
        var selMonograms = 0;
        var id = $(this).attr('data-id');
        if ($(this).closest(".cart-item").length > 0) {
            selectedQty = parseInt($(this).closest(".counter").find('input[name="quantity_' + id + '"]').val());
            selMonograms = $(this).closest(".cart-item").find(".monogram-information").length;
        } else if ($(this).closest(".product-options").length > 0) {
            selectedQty = parseInt($('input[name="quantity_' + id + '"]').val());
            selMonograms = $(".monogram-information-" + id).length;
        }
        if (selectedQty == selMonograms) {
            alert("Please remove a Monogram Style before decreasing the quantity");
            if ($(this).closest(".counter").find(".prod-count").val() == 1) {
                $(this).attr("disabled", "disabled");
            }
        } else {
            var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
            var x = parseInt($(this).closest(".counter").find(".prod-count").val());
            if (isNaN(x)) {

                $(this).closest(".counter").find(".prod-count").val("1");
                $(this).closest(".form-item").find(".remains").removeClass("red");

            } else if (x > 1) {
                $(this).closest(".form-item").find(".remains").removeClass("red");
                $(this).closest(".counter").find(".prod-count").val(x - 1);
                $(this).closest(".counter").attr("data-quantity", $(this).closest(".counter").find('input[name="quantity_' + id + '"]').val());
                if ($(this).closest(".counter").find(".prod-count").val() == 1) {
                    $(this).attr("disabled", "disabled");
                }
            } else {
                return;
            }
        }

        if (parseInt($(this).closest(".counter").attr("data-quantity")) > 1 && $(".monogram-information-" + id).length == 1) {
            $(this).closest('.pd').find(".applyMonogramStyles").removeClass("hide");

        } else if (parseInt($(this).closest(".counter").attr("data-quantity")) == $(".monogram-information-" + id).length) {

            $(".addMonogramButton").attr("disabled", "disabled");
            $(this).closest(".monogram-information-" + id).find(".addMonogramLink ").attr("disabled", "disabled");
            $(this).closest('.pd').find(".applyMonogramStyles").addClass("hide");
            $('input[name="applyAll_' + id + '"]').attr('checked', false);
            $('input[name="applyAll_' + id + '"]').prop('checked', false);
            $('input[name="applyAll_' + id + '"]').removeAttr('checked');


            var qty = $('.quantity_' + id).val();
            qty_update(id, qty);
        } else {


            if (--selectedQty == 1 && parseInt($(".monogram-information-" + id).length) == 0) {
                $('input[name="applyAll_' + id + '"]').removeAttr('checked');
                $(this).closest('.pd').find(".applyMonogramStyles").addClass("hide");
            }
        }

        if (parseInt($('input[name="quantity_' + id + '"]').val()) == selMonograms) {

            if ($(this).closest(".cart-item").length > 0) {
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
            } else if ($(this).closest(".product-options").length > 0) {
                $(".addMonogramButton").attr("disabled", "disabled");
            }
        }


        if (selectedQty == 1 && parseInt($(".monogram-information-" + id).length) == 0) {

            $(".addMonogramButton").removeAttr("disabled");
        }
    });
    $(".counter input.prod-count").keyup(function () {
        var id = $(this).attr("data-id");
        var productid = $(this).attr("data-productid");
        var q = 1;
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));

        var x = $(this).val();
        var selMonograms = 0;
        if ($(this).closest(".cart-item").length > 0) {
            selMonograms = $(".monogram-information-" + id).length;
        } else if ($(this).closest(".product-options").length > 0) {
            selMonograms = $(".monogram-information-" + id).length;
        }
        //        alert(selMonograms);
        //        alert(x);
        if ($.isNumeric(x)) {
            $(this).closest(".form-item").find(".remains").removeClass("red");
            //            if (x >= 1 && x < qty) {
            if (true) {
                if (x < selMonograms) {
                    alert("Please remove a Monogram Style before decreasing the quantity");
                    $(this).val($(this).closest(".counter").attr("data-quantity"));
                } else if (x == selMonograms) {

                    if ($(this).closest(".cart-item").length > 0) {
                        $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
                    } else if ($(this).closest(".product-options").length > 0) {
                        $(".addMonogramButton").attr("disabled", "disabled");
                        //                        if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                        //                            $(".addMonogramButton").attr("disabled", "disabled");
                        //                        } else {
                        //                            $(".addMonogramButton").removeAttr("disabled");
                        //                        }
                    }
                    $(this).closest(".counter").find(".decrease").removeAttr("disabled");
                    $(this).closest(".counter").attr("data-quantity", x);
                } else if (x >= selMonograms) {
                    $(this).closest(".counter").find(".decrease").removeAttr("disabled");
                    if ($(this).closest(".cart-item").length > 0) {
                        $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
                    } else if ($(this).closest(".product-options").length > 0) {
                        //$(".addMonogramButton").removeAttr("disabled");
                        if ($('input[name="applyAll_' + id + '"]').is(":checked")) {
                            $(".addMonogramButton").attr("disabled", "disabled");
                        } else {
                            $(".addMonogramButton").removeAttr("disabled");
                        }
                    }
                    $(this).closest(".counter").attr("data-quantity", x);
                }

                if (x == 1) {


                    $('input[name="applyAll_' + id + '"]').prop('checked', false);
                    $('input[name="applyAll_' + id + '"]').removeAttr('checked');
                    $('.applyMonogramStyles2').addClass("hide");
                    $('.applyMonogramStyles_' + id).addClass("hide");

                } else if (selMonograms == 1) {
                    $('.applyMonogramStyles2').removeClass("hide");
                    $('.applyMonogramStyles_' + id).removeClass("hide");
                }


                if (selMonograms > 1) {
                    $('input[name="applyAll_' + id + '"]').prop('checked', false);
                    $('input[name="applyAll_' + id + '"]').removeAttr('checked');
                    $('.applyMonogramStyles_' + id).addClass("hide");
                    $('.applyMonogramStyles2').addClass("hide");
                }

            }


            if (x >= 1 && x < qty) {
                q = x;
            } else {
                $(this).closest(".form-item").find(".remains").addClass("show").addClass("red");
                $(this).val(qty);
                q = qty
            }
        } else {
            $(this).closest(".counter").find(".remains").addClass("red");
            $(this).val("");
            //alert("The number of quantities should be a number and cannot exceed the maximum available number of quantities.");
        }
    });
    $(".counter input.prod-count").blur(function () {
        var x = $(this).val();
        if (x >= 1) {
            return;
        } else {
            $(this).val("");
        }
    });
    $(".remains").each(function () {
        var remainsShow = parseInt($(this).closest(".form-item").find(".counter").attr("data-show")); // data-show is the qty when we need to show the remaining qty
        var remains = parseInt($(this).closest(".form-item").find(".counter").attr("data-maximum"));
        $(this).html(`(Only ${remains} left)`);
        if (remainsShow >= remains) {
            $(this).addClass("show");
        }
    });

    /************* PAGE LINKING *************/

    $(".cart-products .cart-actions button.secondary").click(function () {
        // window.location.href = 'products.html';
    });
    $(".cart-bag .actions button:first-child").click(function () {
        // window.location.href = 'cart.html';
    });
    $(".cart-bag .actions .btn-secondary").click(function () {
        // window.location.href = 'cart.html';
    });
    $(".cart-bag .actions .btn-primary").click(function () {
        // window.location.href = 'signin.html';
    });
    $(".cart .page-actions button").click(function () {
        // window.location.href = 'checkout.html';
    });
    $(".cart-checkout .page-actions button").click(function () {
        // window.location.href = 'cart-review.html';
    });
    $(".cart-review .page-actions button").click(function () {
        //  window.location.href = 'order-summary.html';
    });
    $(".login .guest .login-form.new-customer button").click(function (e) {
        // window.location.href = 'register.html';
    });
    $(".registerBtn").click(function (e) {
        // window.location.href = 'register.html';
    });
    $(".guest-address .actions button").click(function (e) {
        // window.location.href = 'guest-shipping.html';
    });
    $(".guest-address .page-actions button").click(function (e) {
        // window.location.href = 'order-review.html';
    });
    $(".order-review .page-actions button").click(function (e) {
        // window.location.href = 'order-summary.html';
    });
    $(".order .page-actions .btn-secondary:first-child").click(function () {
        //   window.location.href = 'profile-orders.html';
    });
    $(".searchContainer input[type=text]").autocomplete({
        source: ["Bath", "Beds", "Quilts", "Towels", "Covers", "Sham", "Sheet", "Mats"]
    });
    $(".searchContainer input[type=submit]").click(function (e) {
        //e.preventDefault();
        // window.location.href = 'search.html';
    });

    $(".action button, .form-actions button, #guest-checkout .btn, #reset-password .btn").click(function (e) {
        e.preventDefault();
        var form = $(this).closest("form");
        var formName = $(this).closest('form').attr('id')
        if (validate(form)) {
            if (formName != 'address-form') {
                $(form).submit();
            }

        }
    });

    /***********************************************************/

    function validate(form) {
        $(form).find("input.required").each(function () {
            var data = $.trim($(this).val());
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find(".options.required").each(function () {
            var data = '';
            $(this).find("input[type=radio]").each(function () {
                if ($(this).is(":checked")) {
                    data = $(this).val();
                    $(this).parents(".options").addClass('valid').removeClass('invalid').removeClass('mandatory');
                }
            });

            if (data == "" || data == null) {
                $(this).removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).find("i.error").html('mandatory field');
            }
        });
        $(form).find("textarea.required").each(function () {
            var data = $(this).val();
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find("select.required").each(function () {
            var x = $(this).children("option:selected").val();
            if (x == 0) {
                $(this).next("i.error").html('mandatory field');
                $(this).addClass("mandatory");
            } else if (x == "others") {
                $(this).removeClass("mandatory");
                $("#cityInput").removeClass("hide");
                $("#cityInput label").addClass("required");
            } else {
                $(this).next("i.error").html("");
                $(this).removeClass("mandatory");
            }
        });
        if ($(form).find(".mandatory").length > 0 || $(form).find(".invalid").length > 0) {
            //            alert("Fields marked (*) are mandatory.\nEnter all fields with valid data.");
            return false;
        } else {
            return true;
        }
    }

    function getMsg(type, cls) {
        if (cls == 'mandatory') {
            return 'mandatory field';
        } else if (cls == 'invalid') {
            if (type == 'alphabets') {
                return 'alphabets only allowed';
            } else if (type == 'numbers') {
                return 'numbers only allowed';
            } else if (type == 'alphanumeric') {
                return 'alpha-numeric are allowed';
            } else if (type == 'date') {
                return 'enter in dd/mm/yyyy format';
            } else if (type == 'email') {
                return 'enter valid email';
            } else if (type == 'initials') {
                return 'only alphabets and (.) dot are allowed';
            } else {
                return 'invalid';
            }
        } else {
            return '';
        }
    }
    function alphabetsCheck(e) {
        if ((/^[a-zA-Z]+$/.test(e)))
            return 'valid';
        else
            return 'invalid';
    }
    function initialsCheck(e) {
        let regex1 = /\./s;
        //return regex.test(input);
        if (regex1.test(e))
            return 'valid';
        else
            return 'invalid';
    }
    function alphabetsWSpacesCheck(e) {
        if ((/^[a-zA-Z ]*$/.test(e)))
            return 'valid';
        else
            return 'invalid';
    }
    function numbersCheck(e) {
        if ((/^[0-9]+$/.test(e)))
            return 'valid';
        else
            return 'invalid';
    }
    function alphanumericCheck(e) {
        /*var regex = new RegExp(/^[0-9a-z]+$/);
         if (e.match(regex))
         return 'valid';
         else
         */
        return 'valid';
    }
    function dateCheck(e) {
        if ((/^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/.test(e)))
            return 'valid';
        else
            return 'invalid';
    }
    function emailCheck(e) {
        var regex = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
        if (e.match(regex))
            return 'valid';
        else
            return 'invalid';
    }

    $("select").on("change", function (e) {
        var x = $(this).children("option:selected").val();
        if (x == 0 || x == "general") {
            $(this).addClass("untouched");
        } else {
            $(this).removeClass("untouched");
        }
        if ($(this).hasClass("required")) {
            var x = $(this).children("option:selected").val();
            if (x == 0) {
                $(this).next("i.error").html('mandatory field');
                $(this).addClass("mandatory");
            } else if (x == "others") {
                $(this).removeClass("mandatory");
                $("#cityInput").removeClass("hide").addClass("untouched").addClass("required");
                $("#cityInput label").addClass("required");
            } else {
                $(this).next("i.error").html("");
                $(this).removeClass("mandatory");
            }
        }
    });
    $("input.required").blur(function () {
        var type = $(this).attr("data-type");
        var data = $.trim($(this).val());
        var cls = '';

        if (data == "")
            cls = 'mandatory';
        else if (type == 'alphabets')
            cls = alphabetsCheck(data);
        else if (type == 'alphabetsWSpaces')
            cls = alphabetsWSpacesCheck(data);
        else if (type == 'numbers')
            cls = numbersCheck(data);
        else if (type == 'alphanumeric')
            cls = alphanumericCheck(data);
        else if (type == 'date')
            cls = dateCheck(data);
        else if (type == 'email')
            cls = emailCheck(data);
        else if (type == 'initials') {
            console.log("initials");
            cls = initialsCheck(data);
        } else
            cls = '';

        $(this).parent().removeClass('valid').removeClass('invalid').removeClass('mandatory').addClass(cls);
        $(this).next("i.error").html(getMsg(type, cls));
    });
    $("textarea.required").blur(function () {
        var data = $(this).val();
        var cls = '';
        if (data == "")
            cls = 'mandatory';
        else
            cls = '';

        $(this).parent().removeClass('valid').removeClass('invalid').removeClass('mandatory').addClass(cls);
        $(this).next("i.error").html('mandatory field');
    });
    $("#edit-save").click(function (e) {
        e.preventDefault();
        if ($("#user-details").hasClass("edit-mode")) {
            $("#user-details input.required").each(function () {
                var data = $(this).val();
                if (data == "" || data == null) {
                    $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                    $(this).next("i.error").html('mandatory field');
                }
            });
            $("#user-details select.required").each(function () {
                var x = $(this).children("option:selected").val();
                if (x == 0) {
                    $(this).next("i.error").html('mandatory field');
                    $(this).addClass("mandatory");
                } else {
                    $(this).next("i.error").html("");
                    $(this).removeClass("mandatory");
                }
            });
            if ($("#user-details .mandatory").length > 0 || $("#user-details .invalid").length > 0) {
                //                alert("Fields marked (*) are mandatory.\nEnter all fields with valid data.");
                return;
            }
            $("#user-details").removeClass("edit-mode").addClass("view-mode");
            $("#edit-save").text("Edit Profile");
        } else {
            $("#user-details").removeClass("view-mode").addClass("edit-mode");
            $("#edit-save").text("Update Profile");
        }
    });
    $(".check-group-header").change(function () {
        if ($(this).find("input").is(":checked")) {
            $(this).next().children().find("input").each(function () {
                $(this).prop('checked', true);
            });
        } else {
            $(this).next().children().find("input").each(function () {
                $(this).prop('checked', false);
            });
        }
    });
    $(".check-group .checkbox").change(function () {
        var x = $(this).parent().children().length;
        var c = 0;
        $(this).parent().find("input").each(function () {
            if ($(this).is(":checked")) {
                c++;
            }
        });
        if (c == x - 1) {
            $(this).parent().prev(".check-group-header").find("input").prop("checked", true);
        } else {
            $(this).parent().prev(".check-group-header").find("input").prop("checked", false);
        }
    });
    $("#viewCart").click(function () {
        $("#cart").removeClass("hideCart").addClass("showCart");
        $("#cart-overlay").addClass("show").removeClass("hide");
        remScroll();
    });
    $("#clear").click(function () {
        $(".searchContainer input[type=text]").val('');
        $('#clear').addClass('hide');
    });
    $("#print").click(function () {
        //        window.print();
    });
    $("#filters-handheld .filter-actions button.btn-primary").click(function () {
        $(".products-and-filters").removeClass("showFilters");
        addScroll();
    });

    $("input[name='giftnote']").click(function () {
        if ($(this).is(':checked')) {
            $(this).parents(".giftnote").find(".giftnoteInput").removeClass("hide");
            $(this).parents(".cart-item").find(".giftnoteInput").removeClass("hide");
        } else {
            $(this).parents(".giftnote").find(".giftnoteInput").addClass("hide");
            $(this).parents(".cart-item").find(".giftnoteInput").addClass("hide");
        }
    });
    $(".searchContainer form input[type=text]").keydown(function () {
        var txt = $(".searchContainer form input[type=text]").val().length;
        if (txt > 0) {
            $(this).next("#clear").removeClass("hide");
        } else {
            $(this).next("#clear").addClass("hide");
        }
    });
    $(".tab-title").click(function () {
        $(".tab-title").each(function () {
            $(this).parent().removeClass("active");
        });
        $(this).parent().addClass("active");
    });
    $("#countrySelect").on("change", function () {
        var x = $(this).children("option:selected").val();
        if (x == "Dubai") {
            $("#citySelect").removeClass("hide");
            $("#citySelect select").addClass("untouched").addClass("required");
            $("#citySelect label").addClass("required");
            $("#areaSelect, #areaInput, #cityInput").addClass("hide").removeClass("untouched").removeClass("required");
            $("#areaSelect select, #cityInput select").addClass("untouched").addClass("required");
            $("#areaSelect label, #areaInput label, #cityInput label").removeClass("required");
        } else if (x == "Kuwait") {
            $("#areaSelect").removeClass("hide");
            $("#areaSelect select").addClass("untouched").addClass("required");
            $("#areaSelect label").addClass("required");
            $("#areaInput, #citySelect, #cityInput").addClass("hide");
            $("#citySelect select, #cityInput").removeClass("untouched").removeClass("required");
            $("#areaInput label, #citySelect label, #cityInput label").removeClass("required");
        } else if (x == "India") {
            $("#cityInput").removeClass("hide").addClass("untouched").addClass("required");
            $("#cityInput label").addClass("required");
            $("#areaSelect, #areaInput, #citySelect").addClass("hide");
            $("#citySelect select, #areaSelect select").removeClass("untouched").removeClass("required");
            $("#citySelect label, #areaInput label, #areaSelect label").removeClass("required");
        }
    });
    $("#agreed").attr('checked', false);
    $("#agreed").change(function () {
        if ($(this).is(':checked')) {
            $("#signupBtn").removeAttr("disabled");
        } else {
            $("#signupBtn").attr({ "disabled": "disabled" });
        }
    });
    //    $(".prod-actions .btn, .product-actions .link-button").click(function () {
    //        var text = $(this).html();
    //        if (text == "Add to Cart") {
    //            $("#itemAdd").fadeIn();
    //            setTimeout(() => {
    //                $("#itemAdd").fadeOut();
    //            }, 3000);
    //        }
    //        if ($(this).hasClass("trash")) {
    //            $("#itemRem").fadeIn();
    //            setTimeout(() => {
    //                $("#itemRem").fadeOut();
    //            }, 3000);
    //        }
    //    });
    //    $("#addToCart").click(function () {
    //        $(".products-detail .product-options").find("select").each(function () {
    //            if ($(this).hasClass("untouched") || $(this).hasClass("mandatory")) {
    //                $(this).closest(".form-item").addClass("invalid");
    //                $(this).next(".error").html("mandatory field").addClass("show");
    //            } else {
    //                $(this).closest(".form-item").removeClass("invalid");
    //                $(this).next(".error").html("").removeClass("show");
    //
    //                var text = $("#addToCart").html();
    //                if (text == "Add to Cart") {
    //                    $("#itemAdd").fadeIn();
    //                    setTimeout(() => {
    //                        $("#itemAdd").fadeOut();
    //                    }, 3000);
    //                }
    //            }
    //        });
    //    });
    //    $(".icon-wishlist").click(function () {
    //        if ($(this).hasClass("selected")) {
    //            $(this).removeClass("selected");
    //            $("#favRem").fadeIn();
    //            setTimeout(() => {
    //                $("#favRem").fadeOut();
    //            }, 3000);
    //        } else {
    //            $(this).addClass("selected");
    //            $("#favAdd").fadeIn();
    //            setTimeout(() => {
    //                $("#favAdd").fadeOut();
    //            }, 3000);
    //        }
    //    });
    //    $(".favorite").click(function () {
    //        var ele = $(this).find(".icon-wishlist");
    //        if ($(this).parents().hasClass("products-detail")) {
    //            if ($(this).hasClass("marked")) {
    //                $(this).removeClass("marked").empty();
    //                $(this).append(`<i class="icon-wishlist"></i> Add to Wishlist`);
    //                $("#favRem").fadeIn();
    //                setTimeout(() => {
    //                    $("#favRem").fadeOut();
    //                }, 3000);
    //            } else {
    //                $(this).addClass("marked").empty();
    //                $(this).append(`<i class="icon-wishlist"></i> Added to Wishlist`);
    //                $("#favAdd").fadeIn();
    //                setTimeout(() => {
    //                    $("#favAdd").fadeOut();
    //                }, 3000);
    //            }
    //        } else {
    //            if (ele.hasClass("selected")) {
    //                ele.removeClass("selected");
    //                $("#favRem").fadeIn();
    //                setTimeout(() => {
    //                    $("#favRem").fadeOut();
    //                }, 3000);
    //            } else {
    //                ele.addClass("selected");
    //                $("#favAdd").fadeIn();
    //                setTimeout(() => {
    //                    $("#favAdd").fadeOut();
    //                }, 3000);
    //            }
    //        }
    //    });
    var selectedFont = "";
    var selectedCount = 3;
    var selectedColor = "";
    var selectedPlacement = "";



    $(".addMonogramButton").click(function () {

        var sizeExist = $(this).attr('data-sizeexist');
        var colorExist = $(this).attr('data-colorexist');
        var productId = $(this).attr("data-productId");

        var selMonograms = $(this).find(".monogram-information-".productId).length;
        var total_monogram = $(".monogramSummary").attr('data-total');
        var monogram_id = parseInt(total_monogram) + 1;
        $(".monogramSummary").attr('data-total', monogram_id);
        if (colorExist != 0) {
            /*
             * Select Size Validation
             */

            var colorId = $("select[name=color_" + productId + "]").val();
            //            alert(colorId);
            if (!colorId) {
                $(".custom-error").show();

                $(".products-detail .product-options").find("select").each(function () {
                    if ($(this).hasClass("untouched") || $(this).hasClass("mandatory")) {
                        $(this).closest(".form-item").addClass("invalid");
                        $(this).next(".error").html("mandatory field").addClass("show");
                    } else {
                        $(this).closest(".form-item").removeClass("invalid");
                        $(this).next(".error").html("").removeClass("show");

                    }
                });
                // show_message('error', '{{__("web.error-cart-req-color")}}');
                $(".color-error-" + productId).text("{{__('web.error-cart-req-color')}}");

                return false;
            }
        }

        if (sizeExist != 0) {

            /*
             * Select Size Validation
             */

            var sizeId = $("select[name=size_" + productId + "]").val();
            if (!sizeId) {
                $(".custom-error").show();
                //show_message('error', '{{__("web.error-cart-req-size")}}');
                $(".products-detail .product-options").find("select").each(function () {
                    if ($(this).hasClass("untouched") || $(this).hasClass("mandatory")) {
                        $(this).closest(".form-item").addClass("invalid");
                        $(this).next(".error").html("mandatory field").addClass("show");
                    } else {
                        $(this).closest(".form-item").removeClass("invalid");
                        $(this).next(".error").html("").removeClass("show");

                    }
                });
                $(".size-error-" + productId).text("{{__('web.error-cart-req-size')}}");
                return false;
            }
        }
        var productKey = '';

        if (sizeExist == 1 && colorExist == 1) {

            productKey = productId + '_' + sizeId + '_' + colorId;
        } else if (sizeExist == 1) {       
            productKey = productId + '_'+sizeId + '_c';
        } else if (colorExist == 1) {
            productKey = productId + '_s_' + colorId;
        } else {
            productKey = productId + '_s_c';
        }
       

        var qty = $('input[name="quantity_' + productKey + '"]').val();

        $('.save_monogram').attr('data-id', productId);
        $('.save_monogram').attr('data-sizeexist', sizeExist > 0 ? 1 : 0);

        $('.save_monogram').attr('data-size', sizeId ? sizeId : '');
        $('.save_monogram').attr('data-colorexist', colorExist > 0 ? 1 : 0);
        $('.save_monogram').attr('data-color', colorId ? colorId : '');

        $('.save_monogram').attr('data-mono-id', productKey + '_' + monogram_id);
        $('.cancel_monogram').attr('data-mono-id', productKey + '_' + monogram_id);

      
        $('.save_monogram').attr('data-update-id', productKey);
        $('.cancel_monogram').attr('data-update-id', productKey);
        $('.cancel_monogram').attr('data-monogram', monogram_id);
        $('.save_monogram').removeAttr('data-editmonogram');

        Monogram(productId, sizeExist, sizeId, colorExist, colorId, false, monogram_id);
        var sel = $(this).val();
        var page = $(this).attr('data-page');

        $('#addMonogram').modal({
            show: true,
            backdrop: 'static'
        });
        $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
        $(".monogram-attribs-selection").each(function () {
            $(this).removeClass("show").addClass("hide");
        });
        $('.save_monogram').attr('data-monogramexist', 1);
        selectedFont = $("input[name='font'][checked='checked']").val();
        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();
        var desc = $(".placementDesc_" + selectedPlacement).html();

        $("#placementInfo").html(desc);
        console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
        applyColor(1);



    });



    /* $("input[name='monogram']").click( function() {
     var sel = $(this).val();
     if($(this).is(":checked")) {
     $('#addMonogram').modal({
     show: true
     });
     $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
     $(".monogram-attribs-selection").each( function() {
     $(this).removeClass("show").addClass("hide");
     });
     } else {
     $("#monogramSummary").removeClass("show").addClass("hide");
     }
     selectedFont = $("input[name='font'][checked='checked']").val();
     selectedCount = $("input[name='count'][checked='checked']").val();
     selectedColor = $("input[name='color'][checked='checked']").val();
     selectedPlacement = $("input[name='placement'][checked='checked']").val();
     console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
     applyColor();
     }); */
    $(".monogram-attribs .attrib").click(function () {
        selectedFont = $("input[name='font'][checked='checked']").val();

        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();
        console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);

        $(this).closest(".monogram-attribs").addClass("hide");
        var tgt = "#" + $(this).attr("class").slice(7);
        $(".monogram-attribs-selection").each(function () {
            $(this).removeClass("show").addClass("hide");
            $(this).closest(".modal").addClass("interim-actions");
        });

        $(tgt).removeClass("hide").addClass("show");
    });
    $(".right-panel span.back").click(function () {
        $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
        $(".monogram-attribs-selection").each(function () {
            $(this).removeClass("show").addClass("hide");
            $(this).closest(".modal").removeClass("interim-actions");
        });
        $("input[name='font']").each(function () {
            if ($(this).val() == selectedFont) {
                console.log("font :: ", $(this).val(), " | ", selectedFont);
                $(this).attr("checked", "checked");
                applyFont(1);
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='count']").each(function () {
            if ($(this).val() == selectedCount) {
                console.log("font :: ", $(this).val(), " | ", selectedCount);
                $(this).attr("checked", "checked");
                applyFont(2);
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='color']").each(function () {
            if ($(this).val() == selectedColor) {
                console.log("color :: ", $(this).val(), " | ", selectedColor);
                $(this).attr("checked", "checked");
                applyColor(2);
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='placement']").each(function () {
            if ($(this).val() == selectedPlacement) {
                console.log("placement :: ", $(this).val(), " | ", selectedPlacement);
                $(this).attr("checked", "checked");
            } else {
                $(this).removeAttr("checked");
            }
        });
    });
    $("#addMonogram .inner-action .btn").click(function (e) {
        selectedFont = $("input[name='font'][checked='checked']").val();
        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();

        var inputone = $("#monogram-initials .initials-input.one input");
        var inputtwo = $("#monogram-initials .initials-input.two input");
        var inputthree = $("#monogram-initials .initials-input.three input");
        var inputfour = $("#monogram-initials .initials-input.four input");
        var valid = "false";

        if (selectedCount == "one") {
            if (inputtwo.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputtwo.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "two") {
            if (inputone.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputone.focus();
            } else if (inputthree.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputthree.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "three") {
            if (inputone.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputone.focus();
            } else if (inputtwo.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputtwo.focus();
            } else if (inputthree.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputthree.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "four") {
            if (inputfour.val() == "") {
                //                alert("Please enter with an input");
                valid = "false";
                inputfour.focus();
            } else {
                valid = "true";
            }
        }
        if (valid == "true") {
            $("#initial-04").empty();
            $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
            $(".monogram-attribs-selection").each(function () {
                $(this).removeClass("show").addClass("hide");
                $(this).closest(".modal").removeClass("interim-actions");
            });
            applyFont(3);
            console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
        }
    });
    $(document).on('change', "#monogram-initials input", function () {
        var ele = $(this);
        if (!this.value.match(/[a-zA-Z.]/g)) {
            ele.val("");
            $("#monogram-initials #error span").html("only alphabets and (.) dot are allowed");
            $("#monogram-initials #error").removeClass("hide").addClass("show");
            ele.focus();
        }
    });

    $(".apply_same").click(function () {
        var cart_id = $(this).attr('data-id');

        var monocount = $('.monogram-information-' + cart_id).length;
        if (monocount > 0) {

            if ($(this).is(":checked")) {

                var monocount = $('.monogram-information-' + cart_id).length;
                if (monocount == 1) {
                    $(this).closest(".product-options").find(".addMonogramButton").attr("disabled", "disabled");
                }
            } else {
                $(this).closest(".product-options").find(".addMonogramButton").removeAttr("disabled");
            }


            $.ajax({
                type: 'GET',
                url: $('#apply_same_url').val() + '/' + cart_id,
                success: (data) => {
                    if (data.status) {


                    }



                }
            });
        } else {
            alert('please add monogram first');
            $(this).prop('checked', false);
            $(this).removeAttr('checked');
        }
    });




    $("input[name='add-monogram']").click(function () {
        if ($(this).is(":checked")) {
            $(this).closest(".h-flex").find(".link-button").addClass("show").removeClass("hide");
            $(this).closest(".h-flex").next(".monogram-information").addClass("show").removeClass("hide");
        } else {

            $(this).closest(".h-flex").find(".link-button").addClass("hide").removeClass("show");
            $(this).closest(".h-flex").next(".monogram-information").addClass("hide").removeClass("show");
        }
    });



});


function getInitials(id, product_id = '', sizeExist = '', size = '', colorExist = '', color = '', monogram, monogrammNo = '') {

    //    alert(monogram)
    console.log(monogram);

    $.ajax({
        url: $('#initials_url').val(),
        data: {
            'id': id,
            'product_id': product_id ? product_id : '',
            'sizeExist': sizeExist ? sizeExist : 0,
            'monogrammNo': monogrammNo ? monogrammNo : '',
            'size': sizeExist ? size : '',
            'colorExist': colorExist ? colorExist : 0,
            'color': colorExist ? color : '',
        },
        success: (data) => {
            $('.setinitials').html('');
            $('.setinitials').html(data.view);

            $('.myinitials').attr("checked", false);
            $('#initials_' + data.selected_id).attr("checked", "checked");
            //            alert(data.selected);
            if (data.selected == "one") {
                $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
                $("#monogram-initials .initials-input.one, #monogram-initials .initials-input.three, #monogram-initials .initials-input.four").removeClass("required").removeClass("mandatory").addClass("hide");
                $(".initials-inputs").removeClass("letters-two").removeClass("letters-three").removeClass("letters-four").addClass("letters-one");
                $(".initials-inputs .initials-input.two input").focus().select();
                $("#initial-01").removeClass("hide");
            }
            if (data.selected == "two") {

                //                alert(data.selected);
                $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
                $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
                $(".initials-inputs .initials-input.two, .initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory").addClass("hide");
                $(".initials-inputs").removeClass("letters-one").removeClass("letters-three").removeClass("letters-four").addClass("letters-two");
                $(".initials-inputs .initials-input.one input").focus();
                $("#initial-01, #initial-03").removeClass("hide");
            }

            if (data.selected == "three") {

                $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
                $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
                $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
                $(".initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory").addClass("hide");
                ;
                $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-four").addClass("letters-three");
                $(".initials-inputs .initials-input.one input").focus();
                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
            }
            if (data.selected == "four") {

                $(".initials-inputs .initials-input").each(function () {
                    $(this).removeClass("show").addClass("hide").removeClass("required").removeClass("mandatory");
                });
                $(".initials-inputs .initials-input.four").removeClass("hide").addClass("show").addClass("required").removeClass("mandatory");
                $(".monogram-attribs .attrib.monogram-initials .monogram").addClass("four");
                $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-three").addClass("letters-four");
                $(".initials-inputs .initials-input.four input").focus();
                var variants = $("input[name='font'][checked='checked']").attr("data-variants");
                var folder = $("input[name='font'][checked='checked']").attr("data-folder");
                if (variants == "yes") {
                    var svg1 = "url(" + base_url + '/monogram/' + folder + "/Left_A" + ".svg)";
                    var svg2 = "url(" + base_url + '/monogram/' + folder + "/Center_B" + ".svg)";
                    var svg3 = "url(" + base_url + '/monogram/' + folder + "/Right_C" + ".svg)";

                } else {
                    var svg1 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_A" + ".svg)";
                    var svg2 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_B" + ".svg)";
                    var svg3 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_C" + ".svg)";
                    //        alert(svg1);
                }
                $("#initial-01").css({ "mask-image": svg1, "-webkit-mask-image": svg1 });
                $("#initial-02").css({ "mask-image": svg2, "-webkit-mask-image": svg2 });
                $("#initial-03").css({ "mask-image": svg3, "-webkit-mask-image": svg3 });


                var txt1 = $(".initials-inputs .initials-input.one input").val('A');
                var txt2 = $(".initials-inputs .initials-input.two input").val('B');
                var txt3 = $(".initials-inputs .initials-input.three input").val('C');
                var txt4 = $(".initials-inputs .initials-input.four input").val();
                var preview = $("input[name='font'][checked='checked']").attr("data-preview");

                if (preview == "no") {
                    $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                } else {
                    $("#initial-04").removeClass("hide");
                }

            }


            $("input[name='count']").unbind('click');
            $("input[name='count']").click(function () {

                $("#monogram-initials #error").removeClass("show").addClass("hide");
                $(this).closest("form").find("input[name='count']").each(function () {
                    $(this).removeAttr("checked");
                });
                $(this).attr("checked", "checked");
                var sel = $(this).val();


                $("#initial-01, #initial-02, #initial-03, #initial-04").addClass("hide");
                $(".initials-inputs .initials-input").each(function () {
                    $(this).removeClass("show").addClass("hide");
                });

                $(".monogram-attribs .attrib.monogram-initials .monogram").removeClass("four");
                if (sel == "one") {
                    $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
                    $("#monogram-initials .initials-input.one, #monogram-initials .initials-input.three, #monogram-initials .initials-input.four").removeClass("required").removeClass("mandatory");
                    $(".initials-inputs").removeClass("letters-two").removeClass("letters-three").removeClass("letters-four").addClass("letters-one");
                    $(".initials-inputs .initials-input.two input").focus().select();
                    $("#initial-01").removeClass("hide");
                }
                if (sel == "two") {
                    $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
                    $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
                    $(".initials-inputs .initials-input.two, .initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory");
                    $(".initials-inputs").removeClass("letters-one").removeClass("letters-three").removeClass("letters-four").addClass("letters-two");
                    $(".initials-inputs .initials-input.one input").focus();
                    $("#initial-01, #initial-03").removeClass("hide");
                }
                if (sel == "three") {
                    $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
                    $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
                    $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
                    $(".initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory");
                    $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-four").addClass("letters-three");
                    $(".initials-inputs .initials-input.one input").focus();
                    $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                }
                if (sel == "four") {

                    $(".initials-inputs .initials-input").each(function () {
                        $(this).removeClass("show").addClass("hide").removeClass("required").removeClass("mandatory");
                    });
                    $(".initials-inputs .initials-input.four").removeClass("hide").addClass("show").addClass("required").removeClass("mandatory");
                    $(".monogram-attribs .attrib.monogram-initials .monogram").addClass("four");
                    $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-three").addClass("letters-four");
                    $(".initials-inputs .initials-input.four input").focus();
                    var variants = $("input[name='font'][checked='checked']").attr("data-variants");
                    var folder = $("input[name='font'][checked='checked']").attr("data-folder");
                    if (variants == "yes") {
                        var svg1 = "url(" + base_url + '/monogram/' + folder + "/Left_A" + ".svg)";
                        var svg2 = "url(" + base_url + '/monogram/' + folder + "/Center_B" + ".svg)";
                        var svg3 = "url(" + base_url + '/monogram/' + folder + "/Right_C" + ".svg)";

                    } else {
                        var svg1 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_A" + ".svg)";
                        var svg2 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_B" + ".svg)";
                        var svg3 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_C" + ".svg)";
                        //        alert(svg1);
                    }
                    $("#initial-01").css({ "mask-image": svg1, "-webkit-mask-image": svg1 });
                    $("#initial-02").css({ "mask-image": svg2, "-webkit-mask-image": svg2 });
                    $("#initial-03").css({ "mask-image": svg3, "-webkit-mask-image": svg3 });


                    var txt1 = $(".initials-inputs .initials-input.one input").val('A');
                    var txt2 = $(".initials-inputs .initials-input.two input").val('B');
                    var txt3 = $(".initials-inputs .initials-input.three input").val('C');
                    var txt4 = $(".initials-inputs .initials-input.four input").val();
                    var preview = $("input[name='font'][checked='checked']").attr("data-preview");

                    if (preview == "no") {
                        $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                    } else {
                        $("#initial-04").removeClass("hide");
                    }




                }

            });

            $(".initials-inputs .initials-input.four input").unbind('keyup');
            $(".initials-inputs .initials-input.four input").keyup(function () {

                var ele = $(this);
                console.log("four ", this.value);
                if (/^[a-zA-Z.]*$/.test(this.value) == false) {
                    ele.val($.trim(ele.val()).slice(0, -1));
                    $("#monogram-initials #error span").html("only alphabets and (.) dot are allowed");
                    $("#monogram-initials #error").removeClass("hide").addClass("show");
                    ele.focus();
                }
                var preview = $("input[name='font'][checked='checked']").attr("data-preview");


                if (preview == "no") {

                    var folder = $("input[name='font'][checked='checked']").attr("data-folder");

                    //                    var svg1 = "url(" + base_url + '/monogram/' + folder + "/Left_A" + ".svg)";
                    //                    var svg2 = "url(" + base_url + '/monogram/' + folder + "/Center_B" + ".svg)";
                    //                    var svg3 = "url(" + base_url + '/monogram/' + folder + "/Right_C" + ".svg)";
                    //                    $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1}).removeClass("dot");
                    //                    $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2}).removeClass("dot");
                    //                    $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3}).removeClass("dot");

                    $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                    $("#initial-04").removeClass("hide");
                    $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
                    $("#nopreview-info").removeClass("hide").addClass("show");
                } else {

                    $("#initial-04").empty();
                    $("#nopreview-info").removeClass("show").addClass("hide");
                    var folder = $("input[name='font'][checked='checked']").attr("data-folder");
                    var txt4 = $(".initials-inputs .initials-input.four input").val();
                    character = txt4;
                    var len = txt4.length;
                    console.log("length :: ", len);
                    for (let i = 0; i < len; i++) {
                        str = txt4.charAt(i).toUpperCase();

                        console.log("Char At ", i, " ::: ", str);
                        if (str == ".") {
                            $("#initial-04").append(`<span class="dot"></span>`);
                        } else {
                            svg = "url(" + base_url + "/monogram/" + folder + "/" + folder + "_" + str + ".svg)";
                            //                        alert(svg);
                            $("#initial-04").append(`<span style="mask-image: ${svg}; -webkit-mask-image: ${svg}"></span>`);
                        }
                    }
                }
                applyColor(3);
            });

            $(".initials-inputs .initials-input.one input, .initials-inputs .initials-input.two input, .initials-inputs .initials-input.three input").unbind('keyup');
            $(".initials-inputs .initials-input.one input, .initials-inputs .initials-input.two input, .initials-inputs .initials-input.three input").keyup(function () {

                var sel = $("input[name='count'][checked='checked']").val();

                var ele = $(this);
                console.log("four ", this.value);

                if (/^[a-zA-Z.]*$/.test(this.value) == false || $(this).val() == "") {
                    ele.val($.trim(ele.val()).slice(0, -1));
                    $("#monogram-initials #error span").html("only alphabets and (.) dot are allowed");
                    $("#monogram-initials #error").removeClass("hide").addClass("show");
                    ele.focus();
                } else {
                    if ($(this).val() == ".") {
                        if ($(this).closest(".initials-input").hasClass("one")) {
                            $("#initial-01").addClass("dot");
                        } else if ($(this).closest(".initials-input").hasClass("two")) {
                            $("#initial-02").addClass("dot");
                        } else if ($(this).closest(".initials-input").hasClass("three")) {
                            $("#initial-03").addClass("dot");
                        }
                    } else {
                        if ($(this).closest(".initials-input").hasClass("one")) {
                            $("#initial-01").removeClass("dot");
                        } else if ($(this).closest(".initials-input").hasClass("two")) {
                            $("#initial-02").removeClass("dot");
                        } else if ($(this).closest(".initials-input").hasClass("three")) {
                            $("#initial-03").removeClass("dot");
                        } else if ($(this).closest(".initials-input").hasClass("four")) {
                            $("#initial-01, #initial-02, #initial-03").removeClass("dot");
                        }
                    }
                    $("#monogram-initials .initials-inputs .initials-input").each(function () {
                        $(this).removeClass('mandatory');
                    });
                    $("#monogram-initials .initials-inputs .required").each(function () {
                        var data = $(this).find("input").val();
                        if (data == "" || data == null) {
                            $(this).closest(".initials-input").removeClass('valid').removeClass('invalid').addClass('mandatory');
                        } else {
                            $(this).closest(".initials-input").removeClass('mandatory');
                        }
                    });
                    if ($("#monogram-initials .mandatory").length > 0 || $("#monogram-initials .invalid").length > 0) {
                        $("#monogram-initials #error span").html("Fields marked in red are mandatory.");
                        $("#monogram-initials #error").removeClass("hide").addClass("show");
                    } else {
                        $("#monogram-initials #error span").html("");
                        $("#monogram-initials #error").removeClass("show").addClass("hide");
                        applyFont(6);
                    }
                    var letters = $(this).closest(".initials-inputs").attr("class").slice("24");
                    console.log("letters :: ", letters);
                    if (letters == "two") {
                        console.log("two");
                        if ($(this).parent().hasClass("one")) {
                            console.log("two 1");
                            var inputText = $(this).parent().find("input");
                            if (!isTextSelected(inputText)) {
                                $(".initials-inputs .initials-input.three input").focus();
                                $(".initials-inputs .initials-input.three input").select();
                            }
                        }
                    } else if (letters == "three") {
                        console.log("three");

                        if ($(this).parent().hasClass("one")) {
                            console.log("three 1");
                            var inputText = $(this).parent().find("input");
                            if (!isTextSelected(inputText)) {
                                $(".initials-inputs .initials-input.two input").focus();
                                $(".initials-inputs .initials-input.two input").select();
                            }

                        }
                        if ($(this).parent().hasClass("two")) {
                            console.log("three 2");
                            var inputText = $(this).parent().find("input");
                            if (!isTextSelected(inputText)) {
                                $(".initials-inputs .initials-input.three input").focus();
                                $(".initials-inputs .initials-input.three input").select();
                            }
                        }
                    }
                }
            });


            if (!jQuery.isEmptyObject(monogram)) {
                $("input[name='text1']").val(monogram.left_text);
                $("input[name='text2']").val(monogram.center_text);
                $("input[name='text3']").val(monogram.right_text);
                $("input[name='text4']").val(monogram.text4);
                $('#initials_' + data.selected_id).trigger("click");
            }


            applyFont(9);


        }
    });

}


function applyFont(no = '') {

    var sel = $("input[name='count'][checked='checked']").val();
    // alert(sel);


    var fontImg = $("input[name='font'][checked='checked']").parent().find("img").attr("src");
    var folder = $("input[name='font'][checked='checked']").attr("data-folder");
    //    alert(folder);

    var variants = $("input[name='font'][checked='checked']").attr("data-variants");

    var hasBorder = $("input[name='font'][checked='checked']").attr("data-border");
    var preview = $("input[name='font'][checked='checked']").attr("data-preview");

    $("#nopreview-info").removeClass("show").addClass("hide");

    var txt1 = $(".initials-inputs .initials-input.one input").val();
    var txt2 = $(".initials-inputs .initials-input.two input").val();
    var txt3 = $(".initials-inputs .initials-input.three input").val();
    var txt4 = $(".initials-inputs .initials-input.four input").val();

    if (variants == "yes") {
        var svg1 = "url(" + base_url + '/monogram/' + folder + "/Left_" + txt1.toUpperCase() + ".svg)";
        var svg2 = "url(" + base_url + '/monogram/' + folder + "/Center_" + txt2.toUpperCase() + ".svg)";
        var svg3 = "url(" + base_url + '/monogram/' + folder + "/Right_" + txt3.toUpperCase() + ".svg)";

    } else {
        var svg1 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_" + txt1.toUpperCase() + ".svg)";
        var svg2 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_" + txt2.toUpperCase() + ".svg)";
        var svg3 = "url(" + base_url + '/monogram/' + folder + "/" + folder + "_" + txt3.toUpperCase() + ".svg)";

    }

    var character = "";

    $("#initial-01").css({ "mask-image": svg1, "-webkit-mask-image": svg1 });
    $("#initial-02").css({ "mask-image": svg2, "-webkit-mask-image": svg2 });
    $("#initial-03").css({ "mask-image": svg3, "-webkit-mask-image": svg3 });


    if (txt1 == ".") {
        $("#initial-01").addClass("dot");
    } else {
        $("#initial-01").removeClass("dot");
    }

    if (txt2 == ".") {
        $("#initial-02").addClass("dot");
    } else {
        $("#initial-02").removeClass("dot");
    }

    if (txt3 == ".") {
        $("#initial-03").addClass("dot");
    } else {
        $("#initial-03").removeClass("dot");
    }




    if (sel == "one") {
        character = txt2;
        $("#initial-04").empty();
        $("#initial-01, #initial-03, #initial-04").addClass("hide");
        $("#initial-02").removeClass("hide");
        $("#monogramPreview").removeClass("two").removeClass("three").removeClass("four").addClass("one");
    }
    if (sel == "two") {
        character = txt1 + "-" + txt3;
        $("#initial-04").empty();
        $("#initial-02, #initial-04").addClass("hide").removeClass("show");
        $("#initial-01, #initial-03").removeClass("hide").addClass("show");
        $("#monogramPreview").removeClass("one").removeClass("three").removeClass("four").addClass("two");
    }
    if (sel == "three") {
        character = txt1 + "-" + txt2 + "-" + txt3;
        $("#initial-04").empty();
        $("#initial-04").addClass("hide");
        $("#initial-01, #initial-02, #initial-03").removeClass("hide");
        $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
    }


    if (sel == "four") {
        $("#initial-04").empty();
        character = txt4;

        if (preview == "no") {
            //            alert(111);
            //            var folder = $("input[name='font'][checked='checked']").attr("data-folder");
            //            var svg1 = "url(" + base_url + '/monogram/' + folder + "/Left_A" + ".svg)";
            //            var svg2 = "url(" + base_url + '/monogram/' + folder + "/Center_B" + ".svg)";
            //            var svg3 = "url(" + base_url + '/monogram/' + folder + "/Right_C" + ".svg)";
            //
            //            $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
            //            $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
            //            $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});

            $("#initial-01, #initial-02, #initial-03").removeClass("hide");
            $("#initial-04").removeClass("hide");
            $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
            $("#nopreview-info").removeClass("hide").addClass("show");
        } else {
            $("#nopreview-info").removeClass("show").addClass("hide");
            var len = txt4.length;
            console.log("length :: ", len);

            for (let i = 0; i < len; i++) {
                str = txt4.charAt(i).toUpperCase();
                console.log("Char At ", i, " ::: ", str);
                if (str == ".") {
                    $("#initial-04").append(`
                            <span class="dot"></span>
                        `);
                } else {
                    svg = "url(" + base_url + "/monogram/" + folder + "/" + folder + "_" + str + ".svg)";
                    $("#initial-04").append(`
                        <span style="mask-image: ${svg}; -webkit-mask-image: ${svg}"></span>
                    `);
                }
            }
            $("#initial-01, #initial-02, #initial-03").addClass("hide");
            $("#initial-04").removeClass("hide");
            $("#monogramPreview").removeClass("one").removeClass("two").removeClass("three").addClass("four");
        }
    }
    applyColor(4);
    var id = $('.save_monogram').attr('data-mono-id');
    if (id) {
        $("#styleInfo_" + id + " img").attr("src", fontImg);
        $("#alphabetsInfo_" + id).html(character.toUpperCase());
        //  $(".monogram-attribs .attrib.monogram-style .monogram img").attr("src", fontImg);

    } else {


        //        $("#styleInfo img").attr("src", fontImg);
        //        $("#alphabetsInfo").html(character);

    }

    $(".monogram-attribs .attrib.monogram-style .monogram img").attr("src", fontImg);
    $(".monogram-attribs .attrib.monogram-initials .monogram span").html(character);


}
function applyColor(no) {
    //    alert(no);
    var name = $("input[name='color'][checked='checked']").val();
    //    alert(name);
    var hex = $("input[name='color'][checked='checked']").attr("data-color");
    $("#color").css("background-color", hex);
    $("#initial-01, #initial-02, #initial-03, span.dot").css("background", hex);
    $("#initial-04 > span").each(function () {
        $(this).css("background", hex);
    });
    var id = $('.save_monogram').attr('data-mono-id');
    $("#colorName").html(name);

    if (id && typeof name != "undefined") {

        $("#colorInfo_" + id).empty().append(`<span class="color-plate" style="background: ${hex}"></span> ${name}`);
    } else {
        //$("#colorInfo").empty().append(`<span class="color-plate" style="background: ${hex}"></span> ${name}`);
    }
}


function Monogram(product_id = '', sizeExist = '', size = '', colorExist = '', color = '', isEdit, sel_id = "", monogramNo = '') {


    if ($("body").hasClass("configMonogram")) {
        var monogram_url = $('#murl').val();
        $.ajax({
            url: monogram_url,
            type: 'POST',
            dataType: "json",
            data: {
                'product_id': product_id ? product_id : '',
                'sizeExist': sizeExist ? sizeExist : 0,
                'size': sizeExist ? size : '',
                'colorExist': colorExist ? colorExist : 0,
                'color': colorExist ? color : '',
                'monogramNo': monogramNo ? monogramNo : '',
                ' _token': $('#token').val(),
            },
            success: function (content) {
                //                             alert(JSON.stringify(content));
                $("#preloader").fadeOut("slow");
                $.each(content[0], function (index, obj1) {

                    var fonts = $.map(obj1, function (newObj) {
                        return {
                            fontId: newObj.fontId,
                            preview: newObj.preview,
                            categories: newObj.categories,
                            folderName: newObj.folderName,
                            variants: newObj.variants,
                            defaultImage: newObj.defaultImage,
                            hasBorder: newObj.hasBorder,
                            size_id: newObj.size_id,
                            color_id: newObj.color_id,
                            sizeExist: newObj.sizeExist,
                            colorExist: newObj.colorExist,
                            product_id: newObj.product_id
                        }
                    });
                    console.log("fonts.length :: ", fonts.length);
                    var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                    console.log("categ :: ", categ);
                    $("#monogram-style .options-list").html('');
                    $.each(fonts, function (index, obj) {
                        if ($.inArray(categ, obj.categories) != -1) {
                            $("#monogram-style .options-list").append(
                                `<div class="option">
                                    <div class="radio">
                                        <input type="radio" name="font" data-preview="${obj.preview}" value="${obj.fontId}" data-folder="${obj.folderName}" data-border="${obj.hasBorder}" data-variants="${obj.variants}" data-id="${obj.product_id}" data-size="${obj.sizeExist}" data-size-id="${obj.size_id}"  data-color="${obj.colorExist}" data-color-id="${obj.color_id}" >
                                        <label>
                                            <img src="` + `${obj.defaultImage}">
                                        </label>
                                    </div>
                                </div>`
                            );
                        }
                        $(document).on("click", "input[name='font']", function (e) {
                            $(this).closest("form").find("input[name='font']").each(function () {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                        });
                        $("#monogram-style .options-list .option input[name='font']:first").attr("checked", "checked");
                    });
                    if (!jQuery.isEmptyObject(content[3]['editmonogram'])) {
                        getInitials(content[3]['editmonogram'].font, product_id, sizeExist, size, colorExist, color, content[3]['editmonogram'], monogramNo);
                    } else {
                        getInitials($("input[name='font'][checked='checked']").val(), product_id, sizeExist, size, colorExist, color, content[3]['editmonogram'], monogramNo);
                    }

                    //                    alert(color)
                    $('.save_monogram').attr('data-productid', product_id);
                    $('.save_monogram').attr('data-sizeexist', sizeExist > 0 ? 1 : 0);

                    $('.save_monogram').attr('data-size', size ? size : '');
                    $('.save_monogram').attr('data-colorexist', colorExist > 0 ? 1 : 0);
                    $('.save_monogram').attr('data-color', color ? color : '');

                    $('.cancel_monogram').attr('data-productid', product_id);
                    $('.cancel_monogram').attr('data-sizeexist', sizeExist > 0 ? 1 : 0);

                    $('.cancel_monogram').attr('data-size', size ? size : '');
                    $('.cancel_monogram').attr('data-colorexist', colorExist > 0 ? 1 : 0);
                    $('.cancel_monogram').attr('data-color', color ? color : '');
                    //  $('.removeMonogram').attr('data-color', color ? color : '');


                });
                console.log(content[1]);
                $.each(content[1], function (index, obj2) {
                    var colors = $.map(obj2, function (newObj) {
                        return {
                            colorName: newObj.colorName,
                            hexCode: newObj.hexCode,
                            id: newObj.id
                        }
                    });
                    console.log("colors.length :: ", colors.length);
                    $("#monogram-color .colors-list").html('');
                    $.each(colors, function (index, obj) {
                        $("#monogram-color .colors-list").append(
                            `<div class="radio color">
                                <input type="radio" name="color" value="${obj.colorName}" data-color="${obj.hexCode}" data-id="${obj.id}">
                                <label>
                                    <span class="color-plate" style="background-color: ${obj.hexCode}"></span>
                                    <span class="color-name">${obj.colorName}</span>
                                </label>
                            </div>`
                        );
                        $(document).on("click", "input[name='color']", function (e) {
                            $(this).closest("form").find("input[name='color']").each(function () {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            var hex = $("input[name='color'][checked='checked']").attr("data-color");
                            $("#color").css("background-color", hex);
                            $("#initial-01, #initial-02, #initial-03").css("background", hex);
                            $("#initial-04 > span").each(function () {
                                $(this).css("background", hex);
                            });
                            var ss = $("input[name='color'][checked='checked']").val();
                            //                            console.log(" | selectedColor :: ", ss, " | ", selectedColor);
                        });


                        $("#monogram-color .colors-list input[name='color']:first").attr("checked", "checked");


                    });
                });
                $.each(content[2], function (index, obj3) {

                    var placements = $.map(obj3, function (newObj) {
                        return {
                            placementId: newObj.placementId,
                            placementImage: newObj.placementImage,
                            categories: newObj.categories,
                            placementSize: newObj.placementSize,
                            placementDesc: newObj.placementDesc
                        }
                    });
                    var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                    console.log("categ :: ", categ);
                    $("#monogram-placement .placements-list .options").html('');
                    $.each(placements, function (index, obj) {
                        console.log(obj);
                        if ($.inArray(categ, obj.categories) != -1) {
                            $("#monogram-placement .placements-list .options").append(
                                `<div class="radio w-33">
                                    <input type="radio" name="placement" value="${obj.placementId}">
                                    <label>
                                        <img src="` + `${obj.placementImage}">
                                        <span> ${obj.placementSize}</span>
                                    </label>
                                    <div class="placementDesc  placementDesc_` + `${obj.placementId} ">${obj.placementDesc}</div>
                                </div>`
                            );
                        }
                        $(document).on("click", "input[name='placement']", function (e) {
                            $(this).closest("form").find("input[name='placement']").each(function () {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            var desc = $(this).closest(".radio").find(".placementDesc").html();

                            var placeImg = $(this).closest(".radio").find("img").attr("src");
                            $("#monogram-form .attrib.monogram-placement .monogram img").attr("src", placeImg);
                            $("#monogram-form .attrib.monogram-placement .monogram .placementDesc").html(desc);
                            var id = $('.save_monogram').attr('data-mono-id');

                            if (id) {
                                $("#placementInfo_" + id).html(desc);
                            } else {
                                $("#placementInfo").html(desc);
                            }
                        });
                    });
                });
                if (isEdit) {
                    $('#addMonogram').modal({
                        show: true,
                        backdrop: 'static'
                    });
                }


                if (!jQuery.isEmptyObject(content[3]['editmonogram'])) {


                    //                    $('.addMonogramButton').addClass('hide');
                    $("input[name='font']").attr("checked", false);
                    $("input[name='font'][value='" + content[3]['editmonogram'].font + "']").attr("checked", "checked");
                    //  alert(content[3]['editmonogram'].mongram_text);
                    $("input[name='text1']").val(content[3]['editmonogram'].left_text);
                    $("input[name='text2']").val(content[3]['editmonogram'].center_text);
                    $("input[name='text3']").val(content[3]['editmonogram'].right_text);
                    $("input[name='text4']").val(content[3]['editmonogram'].text4);



                    $(".monogram-attribs .attrib.monogram-initials .monogram span").html(content[3]['editmonogram'].mongram_text);
                    $("input[name='color']").attr("checked", false);
                    $("input[name='color'][value='" + content[3]['editmonogram'].monogram_color + "']").attr("checked", "checked");
                    $("input[name='placement']").attr("checked", false);
                    $("input[name='placement'][value='" + content[3]['editmonogram'].placement + "']").attr("checked", "checked");
                    var desc = $(".placementDesc_" + content[3]['editmonogram'].placement).html();
                    var id = $('.save_monogram').attr('data-mono-id');

                    if (id) {
                        $("#placementInfo_" + id).html(desc);
                    } else {
                        $("#placementInfo").html(desc);
                    }
                    if (content[3]['editmonogram'].monogram_note) {
                        $("#monogramComment p").text('Comment: ' + content[3]['editmonogram'].monogram_note);
                    } else {
                        $("#monogramComment p").text('');
                    }

                    $('.removeMonogram').attr('data-sizeId', content[3]['editmonogram'].size_id ? content[3]['editmonogram'].size_id : 's');
                    $('.removeMonogram').attr('data-colorId', content[3]['editmonogram'].color_id ? content[3]['editmonogram'].color_id : 'c');
                    $('.save_monogram').attr('data-edit', 'true');
                    $('.save_monogram').attr('data-productid', content[3]['editmonogram'].product_id);
                    //$('.save_monogram').attr('data-size', content[3]['editmonogram'].size_id);
                    // $('.save_monogram').attr('data-color', content[3]['editmonogram'].color_id);
                    $('#monogramSummary').removeClass("hide").addClass("show");
                    $('#monogramSummary-' + sel_id).removeClass("hide").addClass("show");

                    if (content[3]['editmonogram'].monogram_note) {
                        $("#monogramComment p").text('Comment: ' + content[3]['editmonogram'].monogram_note);
                        $('#monogramComment').removeClass('hide').addClass("show");
                        $('.addNoteInput2').removeClass('hide').addClass("show");
                        $('#monogramNote').val(content[3]['editmonogram'].monogram_note);
                        var ic = $(".addComment .link-button, .addNote-actions .addNote")
                        ic = ic.find("i");
                        ic.removeClass("icon-plus").addClass("icon-minus");

                    } else {
                        $("#monogramComment p").text('');
                        $('.addNoteInput').addClass('hide').removeClass("show");
                        $('#monogramNote').val('');
                        var ic = $(".addComment .link-button, .addNote-actions .addNote")
                        ic = ic.find("i");
                        ic.removeClass("icon-minus").addClass("icon-plus");
                    }
                    getPlacementColor(content[3]['editmonogram'].font, content[3]['editmonogram'].monogram_color, content[3]['editmonogram'].placement, product_id);
                    getInitials($("input[name='font'][checked='checked']").val(), product_id, sizeExist, size, colorExist, color, content[3]['editmonogram'], monogramNo);


                } else {
                    $("#initial-01, #initial-02, #initial-03").removeClass("dot");

                    $('#monogramSummary').removeClass("show").addClass("hide");
                    //$('#monogramSummary-' + sel_id).removeClass("show").addClass("hide");
                    $('#monogram_data').val(0);
                    $('.addMonogramButton').removeClass('hide');
                    $("#monogram-placement .placements-list .options input[name='placement']:first").attr("checked", "checked");
                    $("#monogramComment p").text('');
                    $('.addNoteInput').addClass('hide').removeClass("show");
                    $('#monogramNote').val('');

                    var ic = $(".addComment .link-button, .addNote-actions .addNote")
                    ic = ic.find("i");
                    ic.removeClass("icon-minus").addClass("icon-plus");
                }
                $(".save_monogram").unbind('click');
                $(".save_monogram").click(function () {

                    var updateId = $(this).attr('data-update-id');
                    var monid = $(this).attr('data-mono-id');
                    var monogram_id = $(".monogramSummary").attr('data-total');

                    var productId = $('.save_monogram').attr('data-productid');

                    var sizeExist = $(this).attr('data-sizeexist');
                    var colorExist = $(this).attr('data-colorexist');
                    var productId = $('.save_monogram').attr('data-productid');
                    var sizeId = $(this).attr('data-size');
                    var colorId = $(this).attr('data-color');
                    var comment = $.trim($('#monogramNote').val());
                    var monopricedata = $.trim($('#monoPriceData').val());

                    var monogramNos = $('.save_monogram').attr('data-editmonogram');


                    if (!monogramNos) {
                        incremnt_id = parseInt(incremnt_id) + 1;
                        var monogramClone = `<div class="monogram-information monogram-information-` + updateId + ` cloned  monogram-` + monogram_id + `" >
                            <div class="h-flex">
                            <div class="h-flex">
                                <label>Selected Monogram</label>
                                <span class="monogram-price nowrap">(+` + monopricedata + ` KD)</span>
                            </div>
                            <span class="link-button edit_monogram" data-id='` + monid + `' data-productId='` + productId + `' data-colorexist='` + colorExist + `' data-sizeexist='` + sizeExist + `' data-size='` + size + `' data-color='` + color + `' data-monogram='` + monogram_id + `'  >Edit</span>
                            <span class="link-button removeMonogram removeMonogram_` + monid + `" data-productId="` + productId + `"data-monogramId="` + monogram_id + `"data-monogram="` + monogram_id + `">Remove</span>
                        </div>
                          <input type="hidden" name="product_cart_id_` + monid + `" value="">
                        <div class="border-panel">
                            <div class="h-flex">
                                <div class="monogram-options">
                                    <label>Style</label>
                                    <div class="info" id="styleInfo_` + monid + `"><img src="../monogram/rounded.svg"></div>
                                </div>
                                <div class="monogram-options">
                                    <label>Initials</label>
                                    <div class="info" id="alphabetsInfo_` + monid + `">A-B-C</div>
                                </div>
                                <div class="monogram-options">
                                    <label>Color</label>
                                    <div class="info" id="colorInfo_` + monid + `">
                                        <span class="color-plate" style="background: #204d70;"></span>
                                        Prussian Blue
                                    </div>
                                </div>
                                <div class="monogram-options">
                                    <label>Placement</label>
                                    <div class="info" id="placementInfo_` + monid + `">Centered at the top</div>
                                </div>
                            </div>
                           <div class="separator underline underline_` + monid + `"></div>
                            <div class="comment monogramComment monogramComment_` + monid + `">
                                <p></p>
                            </div>
                        </div>
                    </div>`;
                        $(".filters .checkbox input").click(function () {
                            var c = 0;
                            $(".filters .checkbox").find("input").each(function () {
                                if ($(this).is(":checked")) {
                                    c++;
                                }
                            });
                            //console.log("filters :: ", c);
                            if (c > 0) {
                                $(".filters .filter-title span.clear-button").fadeIn();
                            } else {
                                $(".filters .filter-title span.clear-button").fadeOut();
                            }
                        });

                        if ($(".cart-item").length > 0) {

                            $(".cart-item").find(".monogramSummary-" + updateId).append(monogramClone);
                            if (comment) {
                                $('.underline_' + monid).show();
                            }
                            $(".cart-item .monogramSummary .monogram-information").last().find(".monogramComment_ " + monid).find("p").text(comment);
                            var selectedQty = $('input[name="quantity_' + updateId + '"]').val();
                            var selMonograms = $(".monogram-information-" + updateId).length;

                            if (parseInt(selectedQty) == parseInt(selMonograms)) {
                                $(".target-item .addMonogramLink > .link-button").attr("disabled", "disabled");
                                $(".addMonogramLink_" + updateId).find(".link-button").attr("disabled", "disabled");
                            }
                        } else if ($(".product-options").length > 0) {


                            $(".monogramSummary").append(monogramClone);
                            if (comment) {
                                $('.underline_' + monid).show();
                                $(".monogramComment_" + monid).html("<p>Comment: " + comment + "</p>");

                                $(".monogramComment_" + monid).addClass("show").removeClass("hide");
                            } else {
                                $('.underline_' + monid).hide();
                                $(".monogramComment_" + monid).addClass("hide").removeClass("show");
                            }
                            var selectedQty = $('input[name="quantity_' + updateId + '"]').val();
                            var selMonograms = $(".monogramSummary .monogram-information-" + updateId).length;

                            if (parseInt(selectedQty) == parseInt(selMonograms)) {
                                $(".addMonogramButton").attr("disabled", "disabled");
                            }
                        }
                        var placement_text = $("input[name='placement'][checked='checked']").closest('.radio').find(".placementDesc").html();
                        $("#placementInfo_" + monid).html(placement_text);

                        if ($('input[name="applyAll_' + updateId + '"]').is(":checked")) {
                            $(".addMonogramButton").attr("disabled", "disabled");
                        }

                        if ($(".products-detail .monogramSummary .monogram-information-" + monid).length == 1 && parseInt($(".products-detail .counter").attr("data-quantity")) > 1) {
                            $('.monogramSummary-' + updateId).find(".applyMonogramStyles").removeClass("hide");
                            $('.applyMonogramStyles2').removeClass("hide");
                            $('.applyMonogramStyles_' + updateId).removeClass("hide");

                        } else {

                            $('.monogramSummary-' + updateId).find(".applyMonogramStyles").addClass("hide");
                            $('.applyMonogramStyles2').addClass("hide");
                            $('.applyMonogramStyles_' + updateId).addClass("hide");
                        }
                        $(".target-item").removeClass("target-item");
                        var finalQty = 0;
                       // alert(updateId);
                        if ($(document).find(".cart-item").length > 0) {
                            selMonograms = $(".monogram-information-" + updateId).length;
                            finalQty = $("input[name=quantity_" + updateId + "]").val();
                        } else if ($(document).find(".product-options").length > 0) {
                            selMonograms = $(".monogram-information-" + updateId).length;
                            finalQty = $("input[name=quantity_" + updateId + "]").val();
                        }
                        //                    applySameMonogram(quantity, id) 
                        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
                        if (finalQty > 1 && selMonograms < 2) {
                            $('.monogramSummary-' + updateId).find(".applyMonogramStyles").removeClass("hide");
                            $('.applyMonogramStyles2').removeClass("hide");
                            $('.applyMonogramStyles_' + updateId).removeClass("hide");
                        } else {

                            $('.monogramSummary-' + updateId).find(".applyMonogramStyles").addClass("hide");
                            $('.applyMonogramStyles2').addClass("hide");
                            $('.applyMonogramStyles_' + updateId).addClass("hide");
                        }
                        $(".edit_monogram").unbind("click");
                        $(".edit_monogram").click(function () {
                            //        alert(2020);
                            var product_id = $(this).attr('data-productId');
                            var colorExist = $(this).attr('data-colorexist');
                            var sizeExist = $(this).attr('data-sizeexist');
                            var color = $(this).attr('data-color');
                            var id = $(this).attr('data-id');
                            var monogram = $(this).attr('data-monogram');
                            $('.save_monogram').attr('data-editmonogram', monogram);
                            $('.save_monogram').attr('data-monogramexist', 1);
                            $('.save_monogram').attr('data-mono-id', id);
                            $('.save_monogram').attr('data-id', id);
                            $('.cancel_monogram').attr('data-monogram', monogram);
                            var size = $(this).attr('data-size');
                            Monogram(product_id, sizeExist, size, colorExist, color, true, '', monogram);

                        });


                        $(".removeMonogram").unbind("click");
                        $(".removeMonogram").click(function () {

                            sessionStorage.setItem("is_edit", '');
                            sessionStorage.removeItem("is_edit");
                            var product_id = $(this).attr('data-productid');
                            var monogramNo = $(this).attr('data-monogram');
                            var size_id = $(this).attr('data-sizeId');
                            var color_id = $(this).attr('data-colorId');
                            $.ajax({
                                url: $('#remove_monogram_url').val() + "/" + product_id + "/" + size_id + "/" + color_id + "/" + monogramNo,
                                success: (data) => {
                                    //                                    aalert(data);
                                    var product_id = $('.addMonogramButton').attr('data-productid');
                                    if (!product_id) {
                                        product_id = '';
                                    }

                                    var qty = $('.quantity_' + updateId).val();
                                    qty_update(updateId, qty);
                                    //                                    Monogram(product_id);
                                }
                            });
                            $(this).closest(".monogram-information").remove();

                            var selectedQty = $('input[name="quantity_' + updateId + '"]').val();

                            var selMonograms = $(".monogram-information-" + updateId).length;

                            $('.addMonogramLink_' + updateId).find(".link-button").removeAttr('disabled');
                            if (parseInt(selectedQty) > 1 && parseInt(selMonograms) == 1) {
                                $('.applyMonogramStyles_' + updateId).removeClass("hide").addClass("show");
                                $('.applyMonogramStyles2').removeClass("hide").addClass("show");

                            }
                            if (parseInt(selectedQty) >= 1 && parseInt(selMonograms) == 0) {
                                $('.addMonogramButton').removeAttr('disabled');
                            }
                            if (parseInt(selectedQty) > parseInt(selMonograms)) {
                                $('.addMonogramButton').removeAttr('disabled');
                            }

                            if (parseInt(selMonograms) == 0) {

                                $('.applyMonogramStyles2').removeClass("show").addClass("hide");
                                $('.applyMonogramStyles_' + updateId).removeClass("show").addClass("hide");

                                $('input[name="applyAll_' + updateId + '"]').prop('checked', false);
                                $('input[name="applyAll_' + updateId + '"]').removeAttr('checked');
                            }


                            //   
                            //        $(this).closest(".monogram-information").removeClass("show").addClass("hide");

                        });

                        removeMonogram();

                        applyFont(4);

                    }

                    //var comment = $("#monogram-form .addComment .addNoteInput textarea").val();

                    if (comment) {
                        $('.underline_' + monid).removeClass('hide');
                        $('.underline_' + monid).show();
                        $(".monogramComment_" + monid).html("<p>Comment: " + comment + "</p>");

                        $(".monogramComment_" + monid).addClass("show").removeClass("hide");
                    } else {
                        $('.underline_' + monid).hide();
                        $(".monogramComment_" + monid).addClass("hide").removeClass("show");
                    }
                    if ($(this).hasClass("btn-primary")) {
                        $('#monogram_data').val(1);
                        $(this).closest(".h-flex").next(".monogram-information").addClass("show").removeClass("hide");
                    } else {
                        //$('#monogram_data').val(0);

                        // $("#monogramSummary").removeClass("show").addClass("hide");
                    }

                    var is_edit = $(this).attr('data-edit');

                    var sizeExist = $(this).attr('data-sizeexist');
                    var colorExist = $(this).attr('data-colorexist');
                    var productId = $('.save_monogram').attr('data-productid');
                    var sizeId = $(this).attr('data-size');
                    var colorId = $(this).attr('data-color');
                    var monogramexist = $(this).attr('data-monogramexist');
                    var updateqty = $(this).attr('data-updateqty');
                    var monogramNos = $('.save_monogram').attr('data-editmonogram');
                    var product_cart_id = $("input[name='product_cart_id_" + monid + "']").val();
                    if (monogramNos) {
                        monogram_id = monogramNos;
                    }
                    //                    alert(monogramNos);
                    //                    if (is_edit == 'true')
                    //                    {
                    $.ajax({
                        url: $('#update_monogram_url').val(),
                        type: "get",
                        data: {
                            'monogram_id': monogram_id,
                            'id': productId,
                            'is_edit': is_edit,
                            'product_cart_id': product_cart_id,
                            'sizeExist': sizeExist,
                            'colorExist': colorExist,
                            'color': colorExist == 1 ? colorId : 'c',
                            'size': sizeExist == 1 ? sizeId : 's',
                            // ----monogrm----
                            'monogramExist': monogramexist,
                            'font': $("input[name='font'][checked='checked']").val(),
                            'initials_count': $("input[name='count'][checked='checked']").val(),
                            'text1': $("input[name='text1']").val(),
                            'text2': $("input[name='text2']").val(),
                            'text3': $("input[name='text3']").val(),
                            'text4': $("input[name='text4']").val(),
                            'monogram_color': $("input[name='color'][checked='checked']").val(),
                            'monogram_color_hash': $("input[name='color'][checked='checked']").attr('data-color'),
                            'monogram_color_id': $("input[name='color'][checked='checked']").attr('data-id'),
                            'placement': $("input[name='placement'][checked='checked']").val(),
                            'monogram_note': comment,
                        },
                        success: (data) => {
                            //                            alert(JSON.stringify(data));

                            $("input[name='product_cart_id_" + monid + "']").val(data.id);
                            $(".removeMonogram_" + monid).attr('data-monogram', data.id);

                            sessionStorage.setItem("is_edit", true);
                            //                            $('.addMonogramButton').addClass('hide');


                            applyFont(22);
                            if (updateqty == 1) {
                                var qty = $('.quantity_' + data.key).val();
                                    qty_update(data.key, qty);
                                var placement = $("input[name='placement'][checked='checked']").val();
                                var desc = $(".placementDesc_" + placement).html()
                                var id = $('.save_monogram').attr('data-mono-id');

                                if (id) {
                                    $("#placementInfo_" + id).html(desc);
                                } else {
                                    $("#placementInfo").html(desc);
                                }

                            }
                            if ($.trim($('textarea#monogramNote').val())) {
                                //                                var id = $('.save_monogram').attr('data-mono-id');
                                //                                $(".comment_" + id).removeClass("hide").addClass("show");
                                //                                $(".comment_" + id + " p").text('Comment: ' + comment);
                            } else {
                                //                                var id = $('.save_monogram').attr('data-mono-id');
                                //
                                //                                $(".comment_" + id).removeClass('show').addClass('hide');


                            }
                            $('.removeMonogram').attr('data-sizeId', sizeExist == 1 ? sizeId : 's');
                            $('.removeMonogram').attr('data-colorId', colorExist == 1 ? colorId : 'c');
                            if (id) {

                                // $('.monogram-information_' + data).removeClass("hide").addClass("show");
                            } else {
                                //    $('.monogram-information').removeClass("hide").addClass("show");
                            }

                        }
                    });
                    //                    }
                });
                $("input[name='font']").unbind('click');
                $("input[name='font']").click(function () {
                    $("input[name='font']").attr("checked", false);
                    $(this).attr('checked', true)
                    applyFont(10);
                });

            }
        })
    }
    binding();
}
function checkInputOne() {


    $(".initials-inputs .initials-input.one input").focus();
    $(".initials-inputs .initials-input.one input").select();

}
function checkInputTwo() {

    $(".initials-inputs .initials-input.two input").focus();
    $(".initials-inputs .initials-input.two input").select();

}
function checkInputThree() {
    $(".initials-inputs .initials-input.three input").focus();
    $(".initials-inputs .initials-input.three input").select();

}

function isTextSelected(input) {
    var selecttxt = '';
    if (window.getSelection) {
        selecttxt = window.getSelection();
    } else if (document.getSelection) {
        selecttxt = document.getSelection();
    } else if (document.selection) {
        selecttxt = document.selection.createRange().text;
    }
    if (selecttxt == '') {
        return false;
    }
    return true;
}

function getPlacementColor(fontStyle, color = '', placement = '', product_id) {
    //    alert(fontStyle);
    $.ajax({
        url: $('#placement_url').val() + "/" + fontStyle + "/" + product_id,
        success: (data) => {

            $.each(data[0], function (index, obj3) {

                var placements = $.map(obj3, function (newObj) {
                    return {
                        placementId: newObj.placementId,
                        placementImage: newObj.placementImage,
                        categories: newObj.categories,
                        placementSize: newObj.placementSize,
                        placementDesc: newObj.placementDesc
                    }
                });
                var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                console.log("categ :: ", categ);
                $("#monogram-placement .placements-list .options").html('');
                $.each(placements, function (index, obj) {
                    if ($.inArray(categ, obj.categories) != -1) {
                        $("#monogram-placement .placements-list .options").append(
                            `<div class="radio w-33">
                                    <input type="radio" name="placement" value="${obj.placementId}">
                                    <label>
                                        <img src="` + `${obj.placementImage}">
                                        <span> ${obj.placementSize}</span>
                                    </label>
                                    <div class="placementDesc  placementDesc_` + `${obj.placementId} ">${obj.placementDesc}</div>
                                </div>`
                        );
                    }
                    $(document).on("click", "input[name='placement']", function (e) {
                        $(this).closest("form").find("input[name='placement']").each(function () {
                            $(this).removeAttr("checked");
                        });
                        $(this).attr("checked", "checked");
                        var desc = $(this).closest(".radio").find(".placementDesc").html();

                        var placeImg = $(this).closest(".radio").find("img").attr("src");
                        $("#monogram-form .attrib.monogram-placement .monogram img").attr("src", placeImg);
                        $("#monogram-form .attrib.monogram-placement .monogram .placementDesc").html(desc);
                        var id = $('.save_monogram').attr('data-mono-id');

                        if (id) {
                            $("#placementInfo_" + id).html(desc);
                        } else {
                            $("#placementInfo").html(desc);
                        }
                    });
                });
            });
            $("input[name='placement']").attr("checked", false);
            if (placement) {
                $("input[name='placement'][value='" + placement + "']").attr("checked", "checked");
            } else {
                $("#monogram-placement .placements-list .options input[name='placement']:first").attr("checked", "checked");
            }

            $.each(data[1], function (index, obj2) {
                var colors = $.map(obj2, function (newObj) {
                    return {
                        colorName: newObj.colorName,
                        hexCode: newObj.hexCode,
                        id: newObj.id
                    }
                });
                console.log("colors.length :: ", colors.length);
                $("#monogram-color .colors-list").html('');
                $.each(colors, function (index, obj) {
                    $("#monogram-color .colors-list").append(
                        `<div class="radio color">
                                <input type="radio" name="color" value="${obj.colorName}" data-color="${obj.hexCode}" data-id="${obj.id}">
                                <label>
                                    <span class="color-plate" style="background-color: ${obj.hexCode}"></span>
                                    <span class="color-name">${obj.colorName}</span>
                                </label>
                            </div>`
                    );
                    $(document).on("click", "input[name='color']", function (e) {
                        $(this).closest("form").find("input[name='color']").each(function () {
                            $(this).removeAttr("checked");
                        });
                        $(this).attr("checked", "checked");
                        var hex = $("input[name='color'][checked='checked']").attr("data-color");
                        $("#color").css("background-color", hex);
                        $("#initial-01, #initial-02, #initial-03").css("background", hex);
                        $("#initial-04 > span").each(function () {
                            $(this).css("background", hex);
                        });
                        var ss = $("input[name='color'][checked='checked']").val();
                        //                            console.log(" | selectedColor :: ", ss, " | ", selectedColor);
                    });

                    $("input[name='color']").attr("checked", false);
                    if (color) {
                        $("input[name='color'][value='" + color + "']").attr("checked", "checked");
                    } else {
                        $("#monogram-color .colors-list input[name='color']:first").attr("checked", "checked");
                    }
                });
            });

        }
    });
}


function applySameMonogram(quantity, id) {


    alert('id = ' + id);
    var selMonograms = $(".monogram-information-" + id).length;
    alert(selMonograms);
    alert(quantity);


    if (quantity >= 1) {
        $(".applyMonogramStyles").removeClass("hide");
        if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
           // alert(1111);
            $(".addMonogramButton").attr("disabled", "disabled");
        } else {
            //alert(2222);
            $(".addMonogramButton").removeAttr("disabled");
        }
    } else {
        $(".applyMonogramStyles").addClass("hide");
    }
    if (parseInt(quantity) > parseInt(selMonograms)) {
        $(".addMonogramButton").removeAttr("disabled");
        if (quantity > 1 && selMonograms < 2) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
    } else {

        $('.applyMonogramStyles').addClass('hide');
    }
}


function removeMonogram() {

    $(".removeMonogram2").unbind("click");
    $(".removeMonogram2").click(function () {
        var product_id = $(this).attr('data-productid');
        var size_id = $(this).attr('data-sizeId');
        var color_id = $(this).attr('data-colorId');
        var monogramNo = $(this).attr('data-monogram');

        var id = $(this).attr('data-id');
        var qty = $('.quantity_' + id).val();
        $.ajax({
            url: $('#remove_monogram_url').val() + "/" + product_id + "/" + size_id + "/" + color_id + "/" + monogramNo,
            success: (data) => {
                var product_id = $('.addMonogramButton').attr('data-productid');
                if (!product_id) {
                    product_id = '';
                }

                $(this).closest(".monogram-information").remove();
                $('.addMonogramLink_' + id).find(".link-button").removeAttr('disabled');
                //                Monogram(product_id);

                var selectedQty = $('input[name="quantity_' + id + '"]').val();

                var selMonograms = $(".monogram-information-" + id).length;

                if (parseInt(selectedQty) > 1 && parseInt(selMonograms) == 1) {
                    $('.applyMonogramStyles_' + id).removeClass("hide").addClass("show");
                }

                if (parseInt(selMonograms) == 0) {
                    $('.applyMonogramStyles_' + id).removeClass("show").addClass("hide");
                    $('input[name="applyAll_' + id + '"]').prop('checked', false);
                    $('input[name="applyAll_' + id + '"]').removeAttr('checked');
                }


                qty_update(id, qty);
            }
        });

        $('.addMonogramButton').removeClass('hide');

        $(this).closest(".monogram-information").removeClass("show").addClass("hide");

    });

    $(".edit_monogram2").unbind("click");
    $(".edit_monogram2").click(function () {
        //        alert(2020);
        var product_id = $(this).attr('data-productId');
        var colorExist = $(this).attr('data-colorexist');
        var sizeExist = $(this).attr('data-sizeexist');
        var color = $(this).attr('data-color');
        var monogram = $(this).attr('data-monogram');
        $('.save_monogram').attr('data-editmonogram', monogram);
        var id = $(this).attr('data-id');
        $('.save_monogram').attr('data-mono-id', id + '_' + monogram);
        $('.save_monogram').attr('data-monogramexist', 1);
        $('.save_monogram').attr('data-id', id);
        $('.cancel_monogram').attr('data-monogram', monogram);


        var size = $(this).attr('data-size');


        //  alert($(".save_monogram").attr('data-edit'));
        Monogram(product_id, sizeExist, size, colorExist, color, true, '', monogram);


    });
    
   
}






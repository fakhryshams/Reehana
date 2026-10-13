$(window).on('load', function () {
	$("#preloader").fadeOut("slow");
});
$(document).ready( function () {
    var vht = $(window).height() + 350;
    if ($(document).height() > vht) {
        $(".menu .navbar").addClass("fixedNav");
    }

    if ($("body").hasClass("configMonogram")) {
        $.ajax({
            url: '../monogram/data.json',
            success: function(content) {
                $.each(content[0], function(index, obj1) {
                    var fonts = $.map(obj1, function(newObj) {
                        return {
                            fontId: newObj.fontId,
                            preview: newObj.preview,
                            categories: newObj.categories,
                            folderName: newObj.folderName,
                            variants: newObj.variants,
                            defaultImage: newObj.defaultImage,
                            hasBorder: newObj.hasBorder
                        }
                    });
                    var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                    $.each(fonts, function(index, obj) {
                        if ($.inArray(categ, obj.categories) != -1) {
                            $("#monogram-style .options-list").append(
                                `<div class="option">
                                    <div class="radio">
                                        <input type="radio" name="font" data-preview="${obj.preview}" value="${obj.fontId}" data-folder="${obj.folderName}" data-border="${obj.hasBorder}" data-variants="${obj.variants}">
                                        <label>
                                            <img src="../monogram/${obj.defaultImage}">
                                        </label>
                                    </div>
                                </div>`
                            );
                        }
                        $(document).on("click", "input[name='font']", function(e) {
                            $(this).closest("form").find("input[name='font']").each( function() {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            applyFont();
                        });
                        $("#monogram-style .options-list .option input[name='font']:first").attr("checked", "checked");
                    });
                });
                $.each(content[1], function(index, obj2) {
                    var colors = $.map(obj2, function(newObj) {
                        return {
                            colorName: newObj.colorName,
                            hexCode: newObj.hexCode
                        }
                    });
                    $.each(colors, function(index, obj) {
                        $("#monogram-color .colors-list").append(
                            `<div class="radio color">
                                <input type="radio" name="color" value="${obj.colorName}" data-color="${obj.hexCode}">
                                <label>
                                    <span class="color-plate" style="background-color: ${obj.hexCode}"></span>
                                    <span class="color-name">${obj.colorName}</span>
                                </label>
                            </div>`
                        );
                        $(document).on("click", "input[name='color']", function(e) {
                            $(this).closest("form").find("input[name='color']").each( function() {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            var hex = $("input[name='color'][checked='checked']").attr("data-color");
                            $("#color").css("background-color", hex);
                            $("#initial-01, #initial-02, #initial-03").css("background", hex);
                            $("#initial-04 > span").each( function() {
                                $(this).css("background", hex);
                            });
                            var ss = $("input[name='color'][checked='checked']").val();
                        });
                        $("#monogram-color .colors-list input[name='color']:first").attr("checked", "checked");
                    });
                });
                $.each(content[2], function(index, obj3) {
                    var placements = $.map(obj3, function(newObj) {
                        return {
                            placementId: newObj.placementId,
                            placementImage: newObj.placementImage,
                            categories: newObj.categories,
                            placementSize: newObj.placementSize,
                            placementDesc: newObj.placementDesc
                        }
                    });
                    var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                    $.each(placements, function(index, obj) {
                        if ($.inArray(categ, obj.categories) != -1) {
                            $("#monogram-placement .placements-list .options").append(
                                `<div class="radio w-33">
                                    <input type="radio" name="placement" value="${obj.placementId}">
                                    <label>
                                        <img src="../monogram/${obj.placementImage}">
                                        <span>Monogram<br>size: ${obj.placementSize}"</span>
                                    </label>
                                    <div class="placementDesc">${obj.placementDesc}</div>
                                </div>`
                            );
                        }
                        $(document).on("click", "input[name='placement']", function(e) {
                            $(this).closest("form").find("input[name='placement']").each( function() {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            var desc = $(this).closest(".radio").find(".placementDesc").html();
                            var placeImg = $(this).closest(".radio").find("img").attr("src");
                            $("#monogram-form .attrib.monogram-placement .monogram img").attr("src", placeImg);
                            $("#monogram-form .attrib.monogram-placement .monogram .placementDesc").html(desc);
                            $("#placementInfo").html(desc);
                        });
                        $("#monogram-placement .placements-list .options input[name='placement']:first").attr("checked", "checked");
                    });
                });
            }
        })
    }

    function applyFont() {
        var sel = $("input[name='count'][checked='checked']").val();
        var fontImg = $("input[name='font'][checked='checked']").parent().find("img").attr("src");
        var folder = $("input[name='font'][checked='checked']").attr("data-folder");
        var variants = $("input[name='font'][checked='checked']").attr("data-variants");
        var hasBorder = $("input[name='font'][checked='checked']").attr("data-border");
        var preview = $("input[name='font'][checked='checked']").attr("data-preview");
        $("#nopreview-info").removeClass("show").addClass("hide");
        
        var txt1 = $(".initials-inputs .initials-input.one input").val();
        var txt2 = $(".initials-inputs .initials-input.two input").val();
        var txt3 = $(".initials-inputs .initials-input.three input").val();
        var txt4 = $(".initials-inputs .initials-input.four input").val();

        if(variants == "yes") {
            var svg1 = "url(../monogram/" + folder + "/Left_" + txt1.toUpperCase() + ".svg)";
            var svg2 = "url(../monogram/" + folder + "/Center_" + txt2.toUpperCase() + ".svg)";
            var svg3 = "url(../monogram/" + folder + "/Right_" + txt3.toUpperCase() + ".svg)";
        } else {
            var svg1 = "url(../monogram/" + folder + "/" + folder + "_" + txt1.toUpperCase() + ".svg)";
            var svg2 = "url(../monogram/" + folder + "/" + folder + "_" + txt2.toUpperCase() + ".svg)";
            var svg3 = "url(../monogram/" + folder + "/" + folder + "_" + txt3.toUpperCase() + ".svg)";
        }
        var character = "";

        $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
        $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
        $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});

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
            $("#initial-02, #initial-04").addClass("hide");
            $("#initial-01, #initial-03").removeClass("hide");
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
                var folder = $("input[name='font'][checked='checked']").attr("data-folder");
                var svg1 = "url(../monogram/" + folder + "/Left_A.svg)";
                var svg2 = "url(../monogram/" + folder + "/Center_B.svg)";
                var svg3 = "url(../monogram/" + folder + "/Right_C.svg)";

                $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
                $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
                $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});

                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                $("#initial-04").removeClass("hide");
                $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
                $("#nopreview-info").removeClass("hide").addClass("show");
            } else {
                $("#nopreview-info").removeClass("show").addClass("hide");
                var len = txt4.length;
                /* for (let i = 0; i < len; i++) {
                    str = txt4.charAt(i).toUpperCase();
                    svg = "url(../monogram/" + folder + "/" + folder + "_" + str + ".svg)";
                    $("#initial-04").append(`
                        <span style="mask-image: ${svg}; -webkit-mask-image: ${svg}"></span>
                    `);
                } */
                for (let i = 0; i < len; i++) {
                    str = txt4.charAt(i).toUpperCase();
                    if (str == ".") {
                        $("#initial-04").append(`
                            <span class="dot"></span>
                        `);
                    } else {
                        svg = "url(../monogram/" + folder + "/" + folder + "_" + str + ".svg)";
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
        applyColor();
        $("#styleInfo img").attr("src", fontImg);
        $("#alphabetsInfo").html(character);
        $(".monogram-attribs .attrib.monogram-style .monogram img").attr("src", fontImg);
        $(".monogram-attribs .attrib.monogram-initials .monogram span").html(character);
    }
    function applyColor() {
        var name = $("input[name='color'][checked='checked']").val();
        var hex = $("input[name='color'][checked='checked']").attr("data-color");
        $("#color").css("background-color", hex);
        $("#initial-01, #initial-02, #initial-03, span.dot").css("background", hex);
        $("#initial-04 > span").each( function() {
            $(this).css("background", hex);
        });
        $("#colorName").html(name);
        $("#colorInfo").empty().append(`<span class="color-plate" style="background: ${hex}"></span> ${name}`);
    }
    $(".initials-inputs .initials-input input").focus( function() {
        if (!$(this).parent().hasClass("four"))
            $(this).select();
    });
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
    $(".initials-inputs .initials-input.one input, .initials-inputs .initials-input.two input, .initials-inputs .initials-input.three input").keyup( function(e) {
        /* if (e.keyCode == 27) {
            return;
        } */
        var sel = $("input[name='count'][checked='checked']").val();
        if($(this).val() == ".") {
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
        $("#monogram-initials .initials-inputs .initials-input").each( function() {
            $(this).removeClass('mandatory');
        });
        $("#monogram-initials .initials-inputs .required").each( function() {
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
            applyFont();
        }
        var letters = $(this).closest(".initials-inputs").attr("class").slice("24");
        if (letters == "two") {
            if ($(this).parent().hasClass("one")) {
                var inputText = $(this).parent().find("input");
                if (!isTextSelected(inputText)) {
                    $(".initials-inputs .initials-input.three input").focus();
                }
            }
        } else if (letters == "three") {
            if ($(this).parent().hasClass("one")) {
                var inputText = $(this).parent().find("input");
                if (!isTextSelected(inputText)) {
                    $(".initials-inputs .initials-input.two input").focus();
                }
            }
            if ($(this).parent().hasClass("two")) {
                var inputText = $(this).parent().find("input");
                if (!isTextSelected(inputText)) {
                    $(".initials-inputs .initials-input.three input").focus();
                }
            }
        }
    });
    $(".initials-inputs .initials-input.four input").keyup( function() {
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
            var svg1 = "url(../monogram/" + folder + "/Left_A.svg)";
            var svg2 = "url(../monogram/" + folder + "/Center_B.svg)";
            var svg3 = "url(../monogram/" + folder + "/Right_C.svg)";
            $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1}).removeClass("dot");
            $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2}).removeClass("dot");
            $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3}).removeClass("dot");

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
            console.log("character :: ", character);
            var len = txt4.length;
            for (let i = 0; i < len; i++) {
                str = txt4.charAt(i).toUpperCase();
                if (str == ".") {
                    $("#initial-04").append(`
                        <span class="dot"></span>
                    `);
                } else {
                    svg = "url(../monogram/" + folder + "/" + folder + "_" + str + ".svg)";
                    $("#initial-04").append(`
                        <span style="mask-image: ${svg}; -webkit-mask-image: ${svg}"></span>
                    `);
                }
            }
        }
        applyColor();
    });
    
    $("#sidenav-overlay, #closeMenu, #menu-handheld #close, .menu .quick-menu a").click( function() {
        if ($("#menu-handheld").hasClass("showMenu")) {
            $("#menu-handheld").removeClass("showMenu").addClass("hideMenu");
            $("#sidenav-overlay").removeClass("show");
            $(".navbar-toggler").removeClass("opened");
        }
    });
    $("#cart-overlay, #closeCart").click( function() {
        $("#cart").removeClass("showCart").addClass("hideCart");
        $("#cart-overlay").removeClass("show").addClass("hide");
        addScroll();
    });
    $(".navbar-toggler").click( function() {
        if ($(this).hasClass("opened")) {
            $("#menu-handheld").removeClass("showMenu").addClass("hideMenu");
            $(this).removeClass("opened");
        } else {
            $("#menu-handheld").removeClass("hideMenu").addClass("showMenu");
            $(this).addClass("opened");
        }
    });

    $("#filters-overlay, #closeFilters, #filters-handheld .filter-actions .btn:last-child").click( function() {
        $(".products-and-filters").removeClass("showFilters").addClass("hideFilters");
        addScroll();
    });
    $("#apply-filters").click( function() {
        $(".products-and-filters").removeClass("hideFilters").addClass("showFilters");
        remScroll();
    });
    
    $(".address-item").click( function() {
        $(".address-item").each(function(){
            $(this).removeClass("selected");
        });
        $(this).addClass("selected");
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
    $("select.selectColor").on("change", function(e) {
        var filterClass = "." + $(this).val();
        $("#productSlider, #prodThumbs").slick("slickUnfilter");
        $("#productSlider, #prodThumbs").slick("slickFilter", filterClass);
        filterImages();
        $(".monogramSummary").empty();
        $(this).closest(".product-options").find("input[name=quantityCount]").val(1);
        $(this).closest(".product-options").find(".counter").attr("data-quantity", $(this).closest(".product-options").find("input[name=quantityCount]").val());
    });
    function filterImages() {
        var $slider = $('#productSlider, #prodThumbs');
        $slider.each(function(index,slide){
            s_id = 0;
            //console.log(index,slide,s_id);
            $(".slick-slide:not(.slick-cloned)",slide).each(function(s_index,s_slide) {
                $(this).attr("data-slick-index",s_id);
                s_id = s_id+1;
                if (index==0) {
                    if ($(this).hasClass("slick-current")) {
                        active_index = s_id;
                    }
                }
                if (index==1) {
                    if (s_id==active_index) {
                        $(this).addClass("slick-current");
                    } else {
                        $(this).removeClass("slick-current");
                    }
                }
            });
        });
    }
    $("#productSliderMobile").slick({
        infinite: !1,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: !0,
        dots: !0,
        appendArrows: '#arrowsContainerMobile',
        prevArrow: "<span class='icon-left'><i class='fa fa-chevron-left'></i></span>",
        nextArrow: "<span class='icon-right'><i class='fa fa-chevron-right'></i></span>",
        responsive: [{
            breakpoint: 768,
            settings: {
                dots: !0
            }
        }]
    });
    
    $("select.selectColor").on("change", function(e) {
        var filterClassMobile = "." + $(this).val();
        $("#productSliderMobile").slick("slickUnfilter");
        $("#productSliderMobile").slick("slickFilter", filterClassMobile);
        filterImagesMobile();
        $(".monogramSummary").empty();
        $(this).closest(".product-options").find("input[name=quantityCount]").val(1);
        $(this).closest(".product-options").find(".counter").attr("data-quantity", $(this).closest(".product-options").find("input[name=quantityCount]").val());
    });
    function filterImagesMobile() {
        var $sliderMobile = $('#productSliderMobile');
        $sliderMobile.each(function(index,slide){
            s_id = 0;
            //console.log(index,slide,s_id);
            $(".slick-slide:not(.slick-cloned)",slide).each(function(s_index,s_slide) {
                $(this).attr("data-slick-index",s_id);
                s_id = s_id+1;
                if (index==0) {
                    if ($(this).hasClass("slick-current")) {
                        active_index = s_id;
                    }
                }
                if (index==1) {
                    if (s_id==active_index) {
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

    initMobileSlider();
    function initMobileSlider() {
        $(document).on('click', '.cloud-zoom', function (){
            remScroll();
            var img = $(this).attr('data-zoom-url');

            var html = '<div class="mProductZooming">';
                html += '<span class="closeZooming"><i class="fas fa-times"></i></span>';
                html += '<img class="mProduct" src="'+img+'">';
            html += '</div>';
            $('body').append(html);
        });

        $(document).on('click', '.closeZooming', function (){
            addScroll();
            $('.mProductZooming').remove();
        });
    }

    $(".addComment .link-button, .addNote-actions .addNote").click( function(e) {
        var ic = $(this).find("i");
        if (ic.hasClass("icon-plus"))
            ic.removeClass("icon-plus").addClass("icon-minus");
        else
            ic.removeClass("icon-minus").addClass("icon-plus");

        var el = $(this).parent().find(".addNoteInput");
        if(el.hasClass("show"))
            el.removeClass("show");
        else
            el.addClass("show");
    });
    $(".addNoteInput .actions .btn-secondary").click( function(e) {
        var el = $(this).closest(".addNoteInput");
        if(el.hasClass("show"))
            el.removeClass("show");
        else
            el.addClass("show");
    });
    $(".textarea > .actions > .link-button-grey").click( function(e) {
        $(this).closest(".textarea").removeClass("show");
    });
    $("#filters-handheld ul.filter-tabs > li").click( function(e) {
        var el = $(this).index();
        $("#filters-handheld ul.filter-tabs > li").each( function(){
            $(this).removeClass("active");
        });
        $("#filters-handheld .filter-group").each( function(){
            $(this).removeClass("show");
        });
        $(this).addClass("active");
        $("#filters-handheld .filter-group").eq(el).addClass("show");
    });
    $(".profile ul.order-tabs > li").click( function(e) {
        var el = $(this).prop("class");
        $(".profile ul.order-tabs > li").each( function(){
            $(this).removeClass("active");
        });
        $(".profile .orders-content > div").each( function(){
            $(this).removeClass("show");
        });
        $(this).addClass("active");
        $(".profile .tab-content").find("div#"+el).addClass("show");
    });
    $("#save-profile").click( function() {
        $(".edit-mode").removeClass("show");
        $(".view-mode").addClass("show");
    });
    $(".fav").click( function() {
        $(this).toggleClass("far fas");
    });
    $(".faq-section h5").click( function() {
        if($(this).parent().hasClass("show")) {
            $(this).parent().removeClass("show");
        } else {
            $(".question").each( function() {
                $(this).removeClass("show");
            });
            $(this).parent().addClass("show");
        }
    });

    $(window).scroll( function() {
        var scroll = $(window).scrollTop();
        if (window.matchMedia("(min-width: 767px)").matches) {
            $(".menu .quick-menu .dropdown-menu").fadeOut();
            setTimeout(function() {
                $(".menu .quick-menu .dropdown-menu").removeAttr("style");
                $(".menu .quick-menu .dropdown-toggle").attr("aria-expanded", "false");
                $(".menu .quick-menu .dropdown").removeClass("open");
            }, 3000);
        }
    });
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
    function addScroll() {
        $("body").removeClass("no-scroll");
    }
    function remScroll() {
        $("body").addClass("no-scroll");
    }
    $("#gifts .options .radio").click(function() {
        var opt = $("#gifts .options input[name='rType1']:checked").val();
        $(".customized").removeClass("gifts").addClass("giveaways");
        $("#giveaways")[0].reset();
        $("#giveaways > div").children(".form-item").each( function() {
            $(this).removeClass("valid").removeClass("invalid").removeClass("mandatory");
            $(this).children("select").removeClass("mandatory");
            $(this).children("i").empty();
        });
    });
    $("#giveaways .options .radio").click(function() {
        var opt = $("#giveaways .options input[name='rType2']:checked").val();
        $(".customized").removeClass("giveaways").addClass("gifts");
        $("#gifts")[0].reset();
        $("#gifts > div").children(".form-item").each( function() {
            $(this).removeClass("valid").removeClass("invalid").removeClass("mandatory");
            $(this).children("select").removeClass("mandatory");
            $(this).children("i").empty();
        });
    });
    $("#billing").change( function() {
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
                        </div>
						<div class="form-item col-md-12 col-sm-12">
							<label class="required">Shipping Location (Google maps link)</label>
							<input type="text" data-type="url" id="userLocation" name="userLocation" placeholder="Shipping Location (Google maps link)">
							<i class="error"></i>
						</div>`;
        $(document).on("change", "select.select-component", function(e) {
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
    $(".counter").each( function() {
        $(this).attr("data-quantity", $(this).find("input[name=quantityCount]").val());
    });
    $(".increase").click( function(){
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = parseInt($(this).closest(".counter").find(".prod-count").val());
        if(isNaN(x)) {
            $(this).closest(".counter").find(".prod-count").val("1");
        } else if(x >= qty) {
            $(this).closest(".form-item").find(".remains").addClass("red");
            return;
        } else {
            $(this).closest(".counter").find(".prod-count").val(x+1);
            $(this).closest(".counter").attr("data-quantity", $(this).closest(".counter").find("input[name=quantityCount]").val());
            
            if ($(this).closest(".cart-item").length > 0) {
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
            } else if ($(this).closest(".product-options").length > 0) {
                //$(".addMonogramButton").removeAttr("disabled");
                if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                    $(".addMonogramButton").attr("disabled", "disabled");
                } else {
                    $(".addMonogramButton").removeAttr("disabled");
                }
            }
            $(this).closest(".counter").find(".decrease").removeAttr("disabled");
        }
        if (parseInt($(this).closest(".counter").attr("data-quantity")) > 1 && $(".products-detail .monogramSummary .monogram-information").length > 1) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
        var selMonograms = 0;
        if ($(this).closest(".cart-item").length > 0) {
            selMonograms = $(this).closest(".cart-item").find(".monogram-information").length;
        } else if ($(this).closest(".product-options").length > 0) {
            selMonograms = $(this).closest(".products-detail").find(".monogram-information").length;
        }
        var finalQty = $(this).closest(".counter").find("input[name=quantityCount]").val();
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        /* if (finalQty > 1 && selMonograms == 1) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        } */
        if (finalQty >= 1 && selMonograms <= 1) {
            $(".applyMonogramStyles").removeClass("hide");
            if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                $(".addMonogramButton").attr("disabled", "disabled");
            } else {
                $(".addMonogramButton").removeAttr("disabled");
            }
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
    });
    $(".decrease").click( function(){
        var selectedQty = 0;
        var selMonograms = 0;
        if ($(this).closest(".cart-item").length > 0) {
            selectedQty = parseInt($(this).closest(".cart-item").find(".counter").attr("data-quantity"));
            selMonograms = $(this).closest(".cart-item").find(".monogram-information").length;
        } else if ($(this).closest(".product-options").length > 0) {
            selectedQty = parseInt($(".product-options .counter").attr("data-quantity"));
            selMonograms = $(".monogramSummary .monogram-information").length;
        }
        if (selectedQty == selMonograms) {
            alert("Please remove a Monogram Style before decreasing the quantity");
            if( $(this).closest(".counter").find(".prod-count").val() == 1) {
                $(this).attr("disabled", "disabled");
            }
        } else {
            var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
            var x = parseInt($(this).closest(".counter").find(".prod-count").val());
            if(isNaN(x)) {
                $(this).closest(".counter").find(".prod-count").val("1");
                $(this).closest(".form-item").find(".remains").removeClass("red");
            } else if(x>1) {
                $(this).closest(".form-item").find(".remains").removeClass("red");
                $(this).closest(".counter").find(".prod-count").val(x-1);
                $(this).closest(".counter").attr("data-quantity", $(this).closest(".counter").find("input[name=quantityCount]").val());
                if( $(this).closest(".counter").find(".prod-count").val() == 1) {
                    $(this).attr("disabled", "disabled");
                }
            } else {
                return;
            }
        }
        if (selectedQty == ++selMonograms) {
            if ($(this).closest(".cart-item").length > 0) {
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
            } else if ($(this).closest(".product-options").length > 0) {
                $(".addMonogramButton").attr("disabled", "disabled");
            }
        }
        /* if (selectedQty == ++selMonograms) {
            if ($(this).closest(".cart-item").length > 0) {
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
            } else if ($(this).closest(".product-options").length > 0) {
                //$(".addMonogramButton").attr("disabled", "disabled");
                if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                    $(".addMonogramButton").attr("disabled", "disabled");
                } else {
                    $(".addMonogramButton").removeAttr("disabled");
                }
            }
        }
        if ($(this).closest(".cart-item").length > 0) {
            selMonograms = $(this).closest(".cart-item").find(".monogram-information").length;
        } else if ($(this).closest(".product-options").length > 0) {
            selMonograms = $(this).closest(".products-detail").find(".monogram-information").length;
        }
        var finalQty = $(this).closest(".counter").find("input[name=quantityCount]").val();
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        if (finalQty >= 1 && selMonograms <= 1) {
            $(".applyMonogramStyles").removeClass("hide");
            if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                $(".addMonogramButton").attr("disabled", "disabled");
            } else {
                $(".addMonogramButton").removeAttr("disabled");
            }
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
        if (finalQty == selMonograms) {
            $(".addMonogramButton").attr("disabled", "disabled");
        } else {
            $(".addMonogramButton").removeAttr("disabled");
        } */
    });
    $(".counter input.prod-count").keyup( function(){
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = $(this).val();
        var selMonograms = 0;
        if ($(this).closest(".cart-item").length > 0) {
            selMonograms = $(this).closest(".cart-item").find(".monogram-information").length;
        } else if ($(this).closest(".product-options").length > 0) {
            selMonograms = $(".monogramSummary .monogram-information").length;
        }
        if ($.isNumeric(x)) {
            $(this).closest(".form-item").find(".remains").removeClass("red");
            if(x >= 1 && x < qty) {
                if(x < selMonograms) {
                    alert("Please remove a Monogram Style before decreasing the quantity");
                    $(this).val($(this).closest(".counter").attr("data-quantity"));
                } else if(x == selMonograms) {
                    if ($(this).closest(".cart-item").length > 0) {
                        $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
                    } else if ($(this).closest(".product-options").length > 0) {
                        //$(".addMonogramButton").attr("disabled", "disabled");
                        if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                            $(".addMonogramButton").attr("disabled", "disabled");
                        } else {
                            $(".addMonogramButton").removeAttr("disabled");
                        }
                    }
                    $(this).closest(".counter").find(".decrease").removeAttr("disabled");
                    $(this).closest(".counter").attr("data-quantity", x);
                } else if(x >= selMonograms) {
                    $(this).closest(".counter").find(".decrease").removeAttr("disabled");
                    if ($(this).closest(".cart-item").length > 0) {
                        $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
                    } else if ($(this).closest(".product-options").length > 0) {
                        //$(".addMonogramButton").removeAttr("disabled");
                        if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                            $(".addMonogramButton").attr("disabled", "disabled");
                        } else {
                            $(".addMonogramButton").removeAttr("disabled");
                        }
                    }
                    $(this).closest(".counter").attr("data-quantity", x);
                }
            } else {
                $(this).closest(".form-item").find(".remains").addClass("show").addClass("red");
                $(this).val(qty);
                $(this).closest(".counter").attr("data-quantity", qty);
                if ($(this).closest(".cart-item").length > 0) {
                    $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
                } else if ($(this).closest(".product-options").length > 0) {
                    //$(".addMonogramButton").removeAttr("disabled");
                    if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                        $(".addMonogramButton").attr("disabled", "disabled");
                    } else {
                        $(".addMonogramButton").removeAttr("disabled");
                    }
                }
            }
        } else {
            $(this).closest(".counter").find(".remains").addClass("red");
            $(this).val($(this).closest(".counter").attr("data-quantity"));
            alert("The number of quantities should be a number and cannot exceed the maximum available number of quantities.");
        }
        var finalQty = $(this).closest(".counter").find("input[name=quantityCount]").val();
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        if (finalQty > 1 && selMonograms == 1) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
        if (finalQty == 1 && selMonograms == 1) {
            $(".applyMonogramStyles").addClass("hide");
            $(".addMonogramButton").attr("disabled", "disabled");
        }
    });
    $(".counter input.prod-count").blur( function(){
        var x = $(this).val();
        if (x>=1) {
            return;
        } else {
            $(this).val("1");
        }
    });
    $(".remains").each( function() {
        var remainsShow = parseInt($(this).closest(".form-item").find(".counter").attr("data-show")); // data-show is the qty when we need to show the remaining qty
        var remains = parseInt($(this).closest(".form-item").find(".counter").attr("data-maximum"));
        $(this).html(`(Only ${remains} left)`);
        if (remainsShow >= remains) {
            $(this).addClass("show");
        }
    });

    
    /* Old Stepper Component
    
    $(".increase").click( function(){
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = parseInt($(this).closest(".counter").find(".prod-count").val());
        if(isNaN(x)) {
            $(this).closest(".counter").find(".prod-count").val("1");
        } else if(x >= qty) {
            $(this).closest(".form-item").find(".remains").addClass("red");
            return;
        } else {
            $(this).closest(".counter").find(".prod-count").val(x+1);
        }
    });
    $(".decrease").click( function(){
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = parseInt($(this).closest(".counter").find(".prod-count").val());
        if(isNaN(x)) {
            $(this).closest(".counter").find(".prod-count").val("1");
            $(this).closest(".form-item").find(".remains").removeClass("red");
        } else if(x>1) {
            $(this).closest(".form-item").find(".remains").removeClass("red");
            $(this).closest(".counter").find(".prod-count").val(x-1);
        } else {
            return;
        }
    });
    $(".counter input.prod-count").keyup( function(){
        var qty = parseInt($(this).closest(".counter").attr("data-maximum"));
        var x = $(this).val();
        if ($.isNumeric(x)) {
            $(this).closest(".form-item").find(".remains").removeClass("red");
            if(x >= 1 && x < qty) {
                return;
            } else {
                $(this).closest(".form-item").find(".remains").addClass("show").addClass("red");
                $(this).val(qty);
            }
        } else {
            $(this).closest(".counter").find(".remains").addClass("red");
            $(this).val("1");
            alert("The number of quantities should be a number and cannot exceed the maximum available number of quantities.");
        }
    });
    $(".counter input.prod-count").blur( function(){
        var x = $(this).val();
        if (x>=1) {
            return;
        } else {
            $(this).val("1");
        }
    });
    $(".remains").each( function() {
        var remainsShow = parseInt($(this).closest(".form-item").find(".counter").attr("data-show")); // data-show is the qty when we need to show the remaining qty
        var remains = parseInt($(this).closest(".form-item").find(".counter").attr("data-maximum"));
        $(this).html(`(Only ${remains} left)`);
        if (remainsShow >= remains) {
            $(this).addClass("show");
        }
    });
    /************* PAGE LINKING *************/

    $(".cart-products .cart-actions button.secondary").click( function() {
        window.location.href='products.html';
    });
    $(".cart-bag .actions button:first-child").click( function() {
        window.location.href='cart.html';
    });
    $(".cart-bag .actions .btn-secondary").click( function() {
        window.location.href='cart.html';
    });
    $(".cart-bag .actions .btn-primary").click( function() {
        window.location.href='signin.html';
    });
    $(".cart .page-actions button").click( function() {
        window.location.href='checkout.html';
    });
    $(".cart-checkout .page-actions button").click( function() {
        window.location.href='cart-review.html';
    });
    $(".cart-review .page-actions button").click( function() {
        window.location.href='order-summary.html';
    });
    $(".login .guest .login-form.new-customer button").click( function(e) {
        window.location.href='register.html';
    });
    $(".registerBtn").click( function(e) {
        window.location.href='register.html';
    });
    $(".guest-address .actions button").click( function(e) {
        window.location.href='guest-shipping.html';
    });
    $(".guest-address .page-actions button").click( function(e) {
        window.location.href='order-review.html';
    });
    $(".order-review .page-actions button").click( function(e) {
        window.location.href='order-summary.html';
    });
    $(".order .page-actions .btn-secondary:first-child").click( function() {
        window.location.href='profile-orders.html';
    });
    $(".searchContainer input[type=text]").autocomplete({
        source: ["Bath", "Beds", "Quilts", "Towels", "Covers", "Sham", "Sheet", "Mats"]
    });
    $(".searchContainer input[type=submit]").click( function(e) {
        e.preventDefault();
        window.location.href='search.html';
    });

    $(".action button, .form-actions button, #guest-checkout .btn, #reset-password .btn").click( function(e) {
        e.preventDefault();
        var form = $(this).closest("form");
        if(validate(form)) {
            $(form).submit();
        }
    });

    /***********************************************************/

    function validate(form) {
        $(form).find("input.required").each( function() {
            var data = $(this).val();
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find(".options.required").each( function() {
            var data = '';
            $(this).find("input[type=radio]").each(function() {
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
        $(form).find("textarea.required").each( function() {
            var data = $(this).val();
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find("select.required").each( function() {
            var x = $(this).children("option:selected").val();
            if (x == 0) {
                $(this).next("i.error").html('mandatory field');
                $(this).addClass("mandatory");
            } else if (x == "others") {
                $(this).removeClass("mandatory");
                $("#cityInput").removeClass("hide");
                $("#cityInput label").addClass("required");
            } else  {
                $(this).next("i.error").html("");
                $(this).removeClass("mandatory");
            }
        });
        if ($(form).find(".mandatory").length > 0 || $(form).find(".invalid").length > 0) {
            alert("Fields marked (*) are mandatory.\nEnter all fields with valid data.");
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
                return 'alphabets only alloweddf';
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
        let regex = /abcdefghijklmnopqrstuvwxyz/;
        //return regex.test(input);
        if (regex.test(e))
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

    $("select").on("change", function(e) {
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
    $("input.required").blur( function() {
        var type = $(this).attr("data-type");
        var data = $(this).val();
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
        }
        else 
            cls = '';
        
        $(this).parent().removeClass('valid').removeClass('invalid').removeClass('mandatory').addClass(cls);
        $(this).next("i.error").html(getMsg(type, cls));
    });
    $("textarea.required").blur( function() {
        var data = $(this).val();
        var cls = '';
        if (data == "")
            cls = 'mandatory';
        else 
            cls = '';
        
        $(this).parent().removeClass('valid').removeClass('invalid').removeClass('mandatory').addClass(cls);
        $(this).next("i.error").html('mandatory field');
    });
    $("#edit-save").click( function(e) {
        e.preventDefault();
        if($("#user-details").hasClass("edit-mode")) {
            $("#user-details input.required").each( function() {
                var data = $(this).val();
                if (data == "" || data == null) {
                    $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                    $(this).next("i.error").html('mandatory field');
                }
            });
            $("#user-details select.required").each( function() {
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
                alert("Fields marked (*) are mandatory.\nEnter all fields with valid data.");
                return;
            }
            $("#user-details").removeClass("edit-mode").addClass("view-mode");
            $("#edit-save").text("Edit Profile");
        } else {
            $("#user-details").removeClass("view-mode").addClass("edit-mode");
            $("#edit-save").text("Update Profile");
        }
    });
    $(".check-group-header").change( function() {
        if ($(this).find("input").is(":checked")) {
            $(this).next().children().find("input").each( function() {
                $(this).prop('checked', true);
            });
        } else {
            $(this).next().children().find("input").each( function() {
                $(this).prop('checked', false);
            });
        }
    });
    $(".check-group .checkbox").change( function() {
        var x = $(this).parent().children().length;
        var c = 0;
        $(this).parent().find("input").each( function() {
            if ($(this).is(":checked")) {
                c++;
            }
        });
        if(c == x-1) {
            $(this).parent().prev(".check-group-header").find("input").prop("checked", true);
        } else {
            $(this).parent().prev(".check-group-header").find("input").prop("checked", false);
        }
    });
    $("#viewCart").click( function() {
        $("#cart").removeClass("hideCart").addClass("showCart");
        $("#cart-overlay").addClass("show").removeClass("hide");
        remScroll();
    });
    $("#clear").click( function() {
        $(".searchContainer input[type=text]").val('');
    });
    $("#print").click( function() {
        window.print();
    });
    $("#filters-handheld .filter-actions button.btn-primary").click( function() {
        $(".products-and-filters").removeClass("showFilters");
        addScroll();
    });
    $(".filters .acc-content .more").click( function() {
        if ($(this).parents(".acc-content").hasClass("showall")) {
            $(this).parents(".acc-content").removeClass("showall");
            $(this).html('<i class="icon-plus"></i>more');
        } else {
            $(this).parents(".acc-content").addClass("showall");
            $(this).html('<i class="icon-minus"></i>less');
        }
    });
    $("input[name='giftnote']").click( function() {
        if($(this).is(':checked')) {
            $(this).parents(".giftnote").find(".giftnoteInput").removeClass("hide");
            $(this).parents(".cart-item").find(".giftnoteInput").removeClass("hide");
        } else {
            $(this).parents(".giftnote").find(".giftnoteInput").addClass("hide");
            $(this).parents(".cart-item").find(".giftnoteInput").addClass("hide");
        }
    });
    $(".searchContainer form input[type=text]").keydown( function() {
        var txt = $(".searchContainer form input[type=text]").val().length;
        if (txt > 0) {
            $(this).next("#clear").removeClass("hide");
        } else {
            $(this).next("#clear").addClass("hide");
        }
    });
    $(".tab-title").click(function() {
        $(".tab-title").each( function() {
            $(this).parent().removeClass("active");
        });
        $(this).parent().addClass("active");
    });
    $("#countrySelect").on("change", function() {
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
    $("#agreed").change( function() {
        if ($(this).is(':checked')) {
            $("#signupBtn").removeAttr("disabled");
        } else {
            $("#signupBtn").attr({"disabled": "disabled"});
        }
    });
    $(".prod-actions .btn, .product-actions .link-button").click( function() {
        var text = $(this).html();
        if (text == "Add to Cart") {
            $("#itemAdd").fadeIn();
            setTimeout(() => {
                $("#itemAdd").fadeOut();
            }, 3000);
        }
        if ($(this).hasClass("trash")) {
            $("#itemRem").fadeIn();
            setTimeout(() => {
                $("#itemRem").fadeOut();
            }, 3000);
        }
    });
    $("#addToCart").click( function() {
        $(".products-detail .product-options").find("select").each( function() {
            if($(this).hasClass("untouched") || $(this).hasClass("mandatory")) {
                $(this).closest(".form-item").addClass("invalid");
                $(this).next(".error").html("mandatory field").addClass("show");
            } else {
                $(this).closest(".form-item").removeClass("invalid");
                $(this).next(".error").html("").removeClass("show");

                var text = $("#addToCart").html();
                if (text == "Add to Cart") {
                    $("#itemAdd").fadeIn();
                    setTimeout(() => {
                        $("#itemAdd").fadeOut();
                    }, 3000);
                }
            }
        });
    });
    $(".icon-wishlist").click( function() {
        if ($(this).hasClass("selected")) {
            $(this).removeClass("selected");
            $("#favRem").fadeIn();
            setTimeout(() => {
                $("#favRem").fadeOut();
            }, 3000);
        } else {
            $(this).addClass("selected");
            $("#favAdd").fadeIn();
            setTimeout(() => {
                $("#favAdd").fadeOut();
            }, 3000);
        }
    });
    $(".favorite").click( function() {
        var ele = $(this).find(".icon-wishlist");
        if ($(this).parents().hasClass("products-detail")) {
            if($(this).hasClass("marked")) {
                $(this).removeClass("marked").empty();
                $(this).append(`<i class="icon-wishlist"></i> Add to Wishlist`);
                $("#favRem").fadeIn();
                setTimeout(() => {
                    $("#favRem").fadeOut();
                }, 3000);
            } else {
                $(this).addClass("marked").empty();
                $(this).append(`<i class="icon-wishlist"></i> Added to Wishlist`);
                $("#favAdd").fadeIn();
                setTimeout(() => {
                    $("#favAdd").fadeOut();
                }, 3000);
            }
        } else {
            if (ele.hasClass("selected")) {
                ele.removeClass("selected");
                $("#favRem").fadeIn();
                setTimeout(() => {
                    $("#favRem").fadeOut();
                }, 3000);
            } else {
                ele.addClass("selected");
                $("#favAdd").fadeIn();
                setTimeout(() => {
                    $("#favAdd").fadeOut();
                }, 3000);
            }
        }
    });
    var selectedFont = "";
    var selectedCount = 3;
    var selectedColor = "";
    var selectedPlacement = "";

    $(".addMonogramButton").click( function() {
        var productColor = $(".products-detail .product-options select.selectColor option:selected").val();
        if (productColor == "general") {
            alert("Please select Color");
        } else {
            var sel = $(this).val();
            $('#addMonogram').modal({
                show: true
            });
            $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
            $(".monogram-attribs-selection").each( function() {
                $(this).removeClass("show").addClass("hide");
            });
            selectedFont = $("input[name='font'][checked='checked']").val();
            selectedCount = $("input[name='count'][checked='checked']").val();
            selectedColor = $("input[name='color'][checked='checked']").val();
            selectedPlacement = $("input[name='placement'][checked='checked']").val();
            console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
            applyColor();
        }
    });
    $(".addMonogramLink > .link-button").click( function() {
        $(this).closest(".cart-item").addClass("target-item");
        var sel = $(this).val();
        $('#addMonogram').modal({
            show: true
        });
        $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
        $(".monogram-attribs-selection").each( function() {
            $(this).removeClass("show").addClass("hide");
        });
        selectedFont = $("input[name='font'][checked='checked']").val();
        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();
        console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
        applyColor();
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
            $(".monogramSummary").removeClass("show").addClass("hide");
        }
        selectedFont = $("input[name='font'][checked='checked']").val();
        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();
        console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
        applyColor();
    }); */
    $(".monogram-attribs .attrib").click( function() {
        selectedFont = $("input[name='font'][checked='checked']").val();
        selectedCount = $("input[name='count'][checked='checked']").val();
        selectedColor = $("input[name='color'][checked='checked']").val();
        selectedPlacement = $("input[name='placement'][checked='checked']").val();
        console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);

        $(this).closest(".monogram-attribs").addClass("hide");
        var tgt = "#" + $(this).attr("class").slice(7);
        $(".monogram-attribs-selection").each( function() {
            $(this).removeClass("show").addClass("hide");
            $(this).closest(".modal").addClass("interim-actions");
        });
        $(tgt).addClass("show");
    });
    $(".right-panel span.back").click( function() {
        $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
        $(".monogram-attribs-selection").each( function() {
            $(this).removeClass("show").addClass("hide");
            $(this).closest(".modal").removeClass("interim-actions");
        });
        $("input[name='font']").each( function() {
            if ($(this).val() == selectedFont) {
                console.log("font :: ", $(this).val(), " | ", selectedFont);
                $(this).attr("checked", "checked");
                applyFont();
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='count']").each( function() {
            if ($(this).val() == selectedCount) {
                console.log("font :: ", $(this).val(), " | ", selectedCount);
                $(this).attr("checked", "checked");
                applyFont();
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='color']").each( function() {
            if ($(this).val() == selectedColor) {
                console.log("color :: ", $(this).val(), " | ", selectedColor);
                $(this).attr("checked", "checked");
                applyColor();
            } else {
                $(this).removeAttr("checked");
            }
        });
        $("input[name='placement']").each( function() {
            if ($(this).val() == selectedPlacement) {
                console.log("placement :: ", $(this).val(), " | ", selectedPlacement);
                $(this).attr("checked", "checked");
            } else {
                $(this).removeAttr("checked");
            }
        });
    });
    $("#addMonogram .inner-action .btn").click( function(e) {
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
                alert("Please enter with an input");
                valid = "false";
                inputtwo.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "two") {
            if (inputone.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputone.focus();
            } else if (inputthree.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputthree.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "three") {
            if (inputone.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputone.focus();
            } else if (inputtwo.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputtwo.focus();
            } else if (inputthree.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputthree.focus();
            } else {
                valid = "true";
            }
        }
        if (selectedCount == "four") {
            if (inputfour.val() == "") {
                alert("Please enter with an input");
                valid = "false";
                inputfour.focus();
            } else {
                valid = "true";
            }
        }
        if (valid == "true") {
            $("#initial-04").empty();
            $(".right-panel .monogram-attribs").removeClass("hide").addClass("show");
            $(".monogram-attribs-selection").each( function() {
                $(this).removeClass("show").addClass("hide");
                $(this).closest(".modal").removeClass("interim-actions");
            });
            applyFont();
            console.log("selectedFont :: ", selectedFont, " | selectedColor :: ", selectedColor, " | selectedPlacement :: ", selectedPlacement);
        }
    });
    $(document).on('keyup', "#monogram-initials input", function (e) {
        var ele = $(this);
        if (!this.value.match(/[a-zA-Z.]/g)) {
            ele.val("");
            $("#monogram-initials #error span").html("only alphabets and (.) dot are allowed");
            $("#monogram-initials #error").removeClass("hide").addClass("show");
            ele.focus();
        }
	});

    /* $(document).keyup(function(e) {
        if(e.key === "Escape") {
            e.preventDefault();
        }
    }); */

    $("input[name='count']").click( function() {
        $("#monogram-initials #error").removeClass("show").addClass("hide");
        $(this).closest("form").find("input[name='count']").each( function() {
            $(this).removeAttr("checked");
        });
        $(this).attr("checked", "checked");
        var sel = $(this).val();
        
        $("#initial-01, #initial-02, #initial-03, #initial-04").addClass("hide");
        $(".initials-inputs .initials-input").each( function() {
            $(this).removeClass("show").addClass("hide");
        });
        $(".monogram-attribs .attrib.monogram-initials .monogram").removeClass("four");
        if(sel == "one") {
            $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
            $("#monogram-initials .initials-input.one, #monogram-initials .initials-input.three, #monogram-initials .initials-input.four").removeClass("required").removeClass("mandatory");
            $(".initials-inputs").removeClass("letters-two").removeClass("letters-three").removeClass("letters-four").addClass("letters-one");
            $(".initials-inputs .initials-input.two input").focus().select();
        }
        if(sel == "two") {
            $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
            $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
            $(".initials-inputs .initials-input.two, .initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory");
            $(".initials-inputs").removeClass("letters-one").removeClass("letters-three").removeClass("letters-four").addClass("letters-two");
            $(".initials-inputs .initials-input.one input").focus();
        }
        if(sel == "three") {
            $(".initials-inputs .initials-input.one").removeClass("hide").addClass("show").addClass("required");
            $(".initials-inputs .initials-input.two").removeClass("hide").addClass("show").addClass("required");
            $(".initials-inputs .initials-input.three").removeClass("hide").addClass("show").addClass("required");
            $(".initials-inputs .initials-input.four").removeClass("required").removeClass("mandatory");
            $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-four").addClass("letters-three");
            $(".initials-inputs .initials-input.one input").focus();
        }
        if(sel == "four") {
            $(".initials-inputs .initials-input").each( function() {
                $(this).removeClass("show").addClass("hide").removeClass("required").removeClass("mandatory");
            });
            $(".initials-inputs .initials-input.four").removeClass("hide").addClass("show").addClass("required").removeClass("mandatory");
            $(".monogram-attribs .attrib.monogram-initials .monogram").addClass("four");
            $(".initials-inputs").removeClass("letters-one").removeClass("letters-two").removeClass("letters-three").addClass("letters-four");
            $(".initials-inputs .initials-input.four input").focus();
        }
        applyFont();
    });

    $(".cart .monogramSummary, .cart-review .monogramSummary").each( function() {
        var selectedQty = parseInt($(this).closest(".cart-item").find(".counter").attr("data-quantity"));
        var selMonograms1 = $(this).find(".monogram-information").length;
        console.log("Cart Load ::: ", selectedQty, " | ", selMonograms1);
        if (selectedQty == selMonograms1) {
            $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
        } else {
            $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
        }
    });
    $("#addMonogram .modal-footer .actions .btn-primary").click( function() {
        applyFont();
        var monogramClone = `<div class="monogram-information cloned">
            <div class="h-flex">
                <div class="h-flex">
                    <label>Selected Monogram</label>
                    <span class="monogram-price nowrap">(+20.000 KD)</span>
                </div>
                <span class="link-button" data-toggle="modal" data-target="#addMonogram">Edit</span>
                <span class="link-button removeMonogram">Remove</span>
            </div>
            <div class="border-panel">
                <div class="h-flex">
                    <div class="monogram-options">
                        <label>Style</label>
                        <div class="info" id="styleInfo"><img src="../monogram/rounded.svg"></div>
                    </div>
                    <div class="monogram-options">
                        <label>Initials</label>
                        <div class="info" id="alphabetsInfo">A-B-C</div>
                    </div>
                    <div class="monogram-options">
                        <label>Color</label>
                        <div class="info" id="colorInfo">
                            <span class="color-plate" style="background: #204d70;"></span>
                            Prussian Blue
                        </div>
                    </div>
                    <div class="monogram-options">
                        <label>Placement</label>
                        <div class="info" id="placementInfo">Centered at the top</div>
                    </div>
                </div>
                <div class="separator"></div>
                <div class="comment monogramComment">
                    <p>Comment: Please deliver the package with extra safe bubble covers.</p>
                </div>
            </div>
        </div>`;
        var comment = $("#monogram-form .addComment .addNoteInput textarea").val();
        if ($(".cart-item").length > 0) {
            $(".target-item").find(".monogramSummary").append(monogramClone);
            $(".target-item .monogramSummary .monogram-information").last().find(".monogramComment").find("p").text(comment);
            var selectedQty = $(".target-item").find("input[name=quantityCount]").val();
            var selMonograms = $(".target-item").find(".monogramSummary").find(".monogram-information").length;
            if (selectedQty == selMonograms) {
                $(".target-item .addMonogramLink > .link-button").attr("disabled", "disabled");
            }
        } else if ($(".product-options").length > 0) {
            $(".monogramSummary").append(monogramClone);
            $(".monogramSummary .monogram-information").last().find(".monogramComment").find("p").text(comment);
            var selectedQty = $(".products-detail .product-options input[name=quantityCount]").val();
            var selMonograms = $(".monogramSummary .monogram-information").length;
            if (selectedQty == selMonograms) {
                $(".addMonogramButton").attr("disabled", "disabled");
            }
        }
        if ($(".products-detail .monogramSummary .monogram-information").length > 1 && parseInt($(".products-detail .counter").attr("data-quantity")) > 1) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }

        $(".target-item").removeClass("target-item");
        var finalQty = 0;
        if ($(document).find(".cart-item").length > 0) {
            selMonograms = $(".target-item").find(".monogram-information").length;
            finalQty = $(".target-item").find("input[name=quantityCount]").val();
        } else if ($(document).find(".product-options").length > 0) {
            selMonograms = $(document).find(".products-detail").find(".monogram-information").length;
            finalQty = $(document).find(".counter").find("input[name=quantityCount]").val();
        }
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        if (finalQty > 1 && selMonograms < 2) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }

        /* $(document).on("click", ".removeMonogram", function() {
            var selectedQty = $(this).closest(".prod-details").find(".counter").attr("data-quantity");
            var selMonograms1 = $(this).closest(".cart-item").find(".monogramSummary").find(".monogram-information").length-1;
            console.log("selMonogram34 ::: ", selectedQty, " | ", selMonograms1);
            if (selectedQty == selMonograms1) {
                $(this).closest(".product-options").find(".addMonogramButton").attr("disabled", "disabled");
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
            } else {
                $(".addMonogramButton").removeAttr("disabled");
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
            }
            $(this).closest(".monogram-information").remove();
        }); */
        /* $(".monogramComment p").text(comment);
        if ($(this).hasClass("btn-primary")) {
            $("input[name='monogram']").attr("checked", "checked");
            $(".monogramSummary").removeClass("hide").addClass("show");
        } else {
            $("input[name='monogram']").removeAttr("checked");
            $(".monogramSummary").removeClass("show").addClass("hide");
        } */
    });

    $(document).on("click", ".removeMonogram", function() {
        var selectedQty = 0;
        var selMonograms1 = 0;
        if ($(".cart-item").length > 0) {
            selectedQty = $(this).closest(".prod-details").find(".counter").attr("data-quantity");
            selMonograms1 = $(this).closest(".cart-item").find(".monogramSummary").find(".monogram-information").length-1;
        } else if ($(".product-options").length > 0) {
            selectedQty = $(this).closest(".product-options").find(".counter").attr("data-quantity");
            selMonograms1 = $(this).closest(".product-options").find(".monogramSummary").find(".monogram-information").length;
        }
        console.log("selMonograms4 ::: ", selectedQty, " | ", selMonograms1);
        if (selectedQty == selMonograms1) {
            $(this).closest(".product-options").find(".addMonogramButton").attr("disabled", "disabled");
            $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
        } else {
            $(".addMonogramButton").removeAttr("disabled");
            $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
        }
        $(this).closest(".monogram-information").remove();

        var finalQty = 0;
        if ($(document).find(".cart-item").length > 0) {
            selMonograms = $(".target-item").find(".monogram-information").length;
            finalQty = $(".target-item").find("input[name=quantityCount]").val();
        } else if ($(document).find(".product-options").length > 0) {
            selMonograms = $(document).find(".products-detail").find(".monogram-information").length;
            finalQty = $(document).find(".counter").find("input[name=quantityCount]").val();
        }
        console.log("finalQty | selMonograms :::", finalQty, " | ", selMonograms);
        if (finalQty > 1 && selMonograms < 2) {
            $(".applyMonogramStyles").removeClass("hide");
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
        if (finalQty >= 1 && selMonograms <= 1) {
            $(".applyMonogramStyles").removeClass("hide");
            if ($(".products-detail .applyMonogramStyles input[name='applyAll']").is(":checked")) {
                $(".addMonogramButton").attr("disabled", "disabled");
            } else {
                $(".addMonogramButton").removeAttr("disabled");
            }
        } else {
            $(".applyMonogramStyles").addClass("hide");
        }
        if (selMonograms == 0) {
            $(".applyMonogramStyles").addClass("hide");
            $(".addMonogramButton").removeAttr("disabled");
        }
    });
    $(".products-detail .applyMonogramStyles input[name='applyAll']").click( function() {
        if ($(this).is(":checked")) {
            $(this).closest(".product-options").find(".addMonogramButton").attr("disabled", "disabled");
        } else {
            $(this).closest(".product-options").find(".addMonogramButton").removeAttr("disabled");
        }
    });

    $("input[name='add-monogram']").click( function() {
        if ($(this).is(":checked")) {
            $(this).closest(".h-flex").find(".link-button").addClass("show").removeClass("hide");
            $(this).closest(".h-flex").next(".monogram-information").addClass("show").removeClass("hide");
        } else {
            $(this).closest(".h-flex").find(".link-button").addClass("hide").removeClass("show");
            $(this).closest(".h-flex").next(".monogram-information").addClass("hide").removeClass("show");
        }
    });
    $("input[name='enableDots']").click( function() {
        if ($(this).is(":checked")) {
            /* $("#monogramPreview span.dot").each( function() {
                $(this).remove();
            }); */
            $(".preview-panel").addClass("addDots");
            /* $("#monogramPreview span").each( function() {
                $(this).after("<span class='dot'></span>")
            }); */
        } else {
            $(".preview-panel").removeClass("addDots");
        }
    });
    $(".filters .checkbox input").click( function() {
        var c = 0;
        $(".filters .checkbox").find("input").each (function(){
            if ($(this).is(":checked")) {
                c++;
            }
        });
        if(c > 0) {
            $(".filters .filter-title span.clear-button").fadeIn();
        } else {
            $(".filters .filter-title span.clear-button").fadeOut();
        }
    });
    $("span.tt").click( function() {
        $(this).find(".tt-text").addClass("show-tt");
        setTimeout(() => {
            $(this).find(".tt-text").removeClass("show-tt");
        }, 5000);
    });
});
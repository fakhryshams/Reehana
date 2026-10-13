$(function() {
    if ($("body").hasClass("configMonogram")) {
        $.ajax({
            url: '../monogram/data.json',
            success: function(content) {
                $.each(content[0], function(index, obj1) {
                    console.log("JSON loaded ::");
                    var fonts = $.map(obj1, function(newObj) {
                        return {
                            fontId: newObj.fontId,
                            font: newObj.font,
                            folderName: newObj.folderName,
                            position: newObj.position,
                            preview: newObj.preview,
                            categories: newObj.categories,
                            leftClassName: newObj.leftClassName,
                            centerClassName: newObj.centerClassName,
                            rightClassName: newObj.rightClassName,
                            defaultImage: newObj.defaultImage,
                            hasBorder: newObj.hasBorder
                        }
                    });
                    console.log("fonts.length :: ", fonts.length);
                    var categ = $("#addMonogram .right-panel").attr("class").slice(21);
                    console.log("categ :: ", categ);
                    $.each(fonts, function(index, obj) {
                        if ($.inArray(categ, obj.categories) != -1) {
                            $("#monogram-style .options-list").append(
                                `<div class="option" data-preview="${obj.preview}">
                                    <div class="radio">
                                        <input type="radio" name="font" value="${obj.fontId}" data-folder="${obj.folderName}" data-border="${obj.hasBorder}">
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

                            /* var id = $(this).val();
                            var sel = $("input[name='count']:checked").val();
                            var folder = $("input[name='font']:checked").attr("data-folder");
                            var hasBorder = $("input[name='font']:checked").attr("data-border");
                            var txt1 = $(".monogram-options .initials-inputs .initials-input.one input").val();
                            var txt2 = $(".monogram-options .initials-inputs .initials-input.two input").val();
                            var txt3 = $(".monogram-options .initials-inputs .initials-input.three input").val();
                            var txt4 = $(".monogram-options .initials-inputs .initials-input.four input").val();
                            var svg1 = "url(../monogram/" + folder + "/" + txt1 + "-Left.svg)";
                            var svg2 = "url(../monogram/" + folder + "/" + txt2 + "-Center.svg)";
                            var svg3 = "url(../monogram/" + folder + "/" + txt3 + "-Right.svg)";

                            if (hasBorder == "yes") {
                                var border = "url(../monogram/" + folder + "/Border.svg)";
                                $("#monogram-border").css({"mask-image": border, "-webkit-mask-image": border}).removeClass("hide");
                            } else {
                                $("#monogram-border").css({"mask-image": "none", "-webkit-mask-image": "none"}).addClass("hide");
                            }

                            if (sel == "one" && obj.fontId == id) {
                                $("#initial-01, #initial-03, #initial-04").addClass("hide");
                                $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
                                $("#initial-02").removeClass("hide");
                                $("#monogramPreview").removeClass("two").removeClass("three").removeClass("four").addClass("one");
                            }
                            if (sel == "two" && obj.fontId == id) {
                                $("#initial-02, #initial-04").addClass("hide");
                                $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
                                $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});
                                $("#initial-01, #initial-03").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("three").removeClass("four").addClass("two");
                            }
                            if (sel == "three" && obj.fontId == id) {
                                $("#initial-04").addClass("hide");
                                $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
                                $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
                                $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});
                                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
                            }
                            if (sel == "four" && obj.fontId == id) {
                                $("#initial-01, #initial-02, #initial-03").addClass("hide");
                                $("#initial-04").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("two").removeClass("three").addClass("four");
                            } */
                            applyFont();
                        });
                        function applyFont() {
                            var sel = $("input[name='count']:checked").val();
                            var folder = $("input[name='font'][checked='checked']").attr("data-folder");
                            var hasBorder = $("input[name='font'][checked='checked']").attr("data-border");
                            var txt1 = $(".monogram-options .initials-inputs .initials-input.one input").val();
                            var txt2 = $(".monogram-options .initials-inputs .initials-input.two input").val();
                            var txt3 = $(".monogram-options .initials-inputs .initials-input.three input").val();
                            var txt4 = $(".monogram-options .initials-inputs .initials-input.four input").val();
                            var svg1 = "url(../monogram/" + folder + "/" + txt1 + "-Left.svg)";
                            var svg2 = "url(../monogram/" + folder + "/" + txt2 + "-Center.svg)";
                            var svg3 = "url(../monogram/" + folder + "/" + txt3 + "-Right.svg)";
                    
                            if (hasBorder == "yes") {
                                var border = "url(../monogram/" + folder + "/Border.svg)";
                                $("#monogram-border").css({"mask-image": border, "-webkit-mask-image": border}).removeClass("hide");
                            } else {
                                $("#monogram-border").css({"mask-image": "none", "-webkit-mask-image": "none"}).addClass("hide");
                            }
                    
                            if (sel == "one") {
                                $("#initial-01, #initial-03, #initial-04").addClass("hide");
                                $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
                                $("#initial-02").removeClass("hide");
                                $("#monogramPreview").removeClass("two").removeClass("three").removeClass("four").addClass("one");
                            }
                            if (sel == "two") {
                                $("#initial-02, #initial-04").addClass("hide");
                                $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
                                $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});
                                $("#initial-01, #initial-03").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("three").removeClass("four").addClass("two");
                            }
                            if (sel == "three") {
                                $("#initial-04").addClass("hide");
                                $("#initial-01").css({"mask-image": svg1, "-webkit-mask-image": svg1});
                                $("#initial-02").css({"mask-image": svg2, "-webkit-mask-image": svg2});
                                $("#initial-03").css({"mask-image": svg3, "-webkit-mask-image": svg3});
                                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("two").removeClass("four").addClass("three");
                            }
                            if (sel == "four") {
                                $("#initial-01, #initial-02, #initial-03").addClass("hide");
                                $("#initial-04").removeClass("hide");
                                $("#monogramPreview").removeClass("one").removeClass("two").removeClass("three").addClass("four");
                            }
                        }
                        $("#monogram-style .options-list .option input[name='font']:first").attr("checked", "checked");
                        /* $(document).on("click", "input[name='font']", function(e) {
                            var id = $(this).val();
                            var sel = $("input[name='count']:checked").val();
                            console.log("obj.fontId :: ", obj.fontId);
                            var imgSrc = $(this).next("label").find("img").attr("src");
                            $("#monogram-form .monogram-style .monogram img").attr("src", imgSrc);
                            if (sel == "one" && obj.fontId == id) {
                                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                                $("#initial-01").attr("class", "").addClass(obj.leftClassName);
                                $("#initial-02").attr("class", "").addClass(obj.centerClassName);
                                $("#initial-03").attr("class", "").addClass(obj.rightClassName);
                                $("#initial-04").attr("class", "").addClass(obj.rightClassName);
                                $("#initial-01, #initial-03").addClass("hide");
                            } else if (sel == "two" && obj.fontId == id) {
                                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                                $("#initial-01").attr("class", "").addClass(obj.leftClassName);
                                $("#initial-02").attr("class", "").addClass(obj.centerClassName);
                                $("#initial-03").attr("class", "").addClass(obj.rightClassName);
                                $("#initial-04").attr("class", "").addClass(obj.rightClassName);
                                $("#initial-02").addClass("hide");
                            } else if (sel == "three" && obj.fontId == id) {
                                $("#initial-01, #initial-02, #initial-03").removeClass("hide");
                                console.log("obj.fontId 2    :: ", obj.fontId, " | ", obj.leftClassName, " | ", obj.centerClassName, " | ", obj.rightClassName);
                                $("#initial-01").attr("class", "").addClass(obj.leftClassName);
                                $("#initial-02").attr("class", "").addClass(obj.centerClassName);
                                $("#initial-03").attr("class", "").addClass(obj.rightClassName);
                                $("#initial-04").addClass(obj.rightClassName);
                            } else if (sel == "four" && obj.fontId == id) {
                                $("#initial-01, #initial-02, #initial-03").addClass("hide");
                                if(obj.preview == "yes") {
                                    var txt = $(".monogram-attribs .attrib.monogram-initials .monogram span").html();
                                    $("#initial-01, #initial-02, #initial-03").addClass("hide");
                                    $("#initial-04").attr("class", "").removeClass("hide").addClass(obj.rightClassName).html(txt);
                                } else {
                                    $("#initial-01").attr("class", "").addClass(obj.leftClassName);
                                    $("#initial-02").attr("class", "").addClass(obj.centerClassName);
                                    $("#initial-03").attr("class", "").addClass(obj.rightClassName);
                                    $("#initial-04").attr("class", "").addClass(obj.rightClassName);
                                    $("#initial-04").addClass("hide");
                                }
                            }
                        }); */
                    });
                });
                $.each(content[1], function(index, obj2) {
                    console.log("JSON loaded ::");
                    var colors = $.map(obj2, function(newObj) {
                        return {
                            colorName: newObj.colorName,
                            hexCode: newObj.hexCode
                        }
                    });
                    console.log("colors.length :: ", colors.length);
                    $.each(colors, function(index, obj) {
                        $("#monogram-color .colors-list").append(
                            `<div class="radio color">
                                <input type="radio" name="color" value="${obj.colorName}">
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
                            var name = $(this).closest(".color").find(".color-name").html();
                            var hex = $(this).closest(".color").find(".color-plate").attr("style").slice(17);
                            $("#color").css("background-color", hex);
                            $("#monogram-border, #initial-01, #initial-02, #initial-03, #initial-04").css("background", hex);
                            $("#colorName").html(name);
                        });
                        $("#monogram-color .colors-list input[name='color']:first").attr("checked", "checked");
                    });
                });
                $.each(content[2], function(index, obj3) {
                    console.log("JSON loaded ::");
                    var placements = $.map(obj3, function(newObj) {
                        return {
                            placementId: newObj.placementId,
                            placementImage: newObj.placementImage,
                            placementSize: newObj.placementSize,
                            placementDesc: newObj.placementDesc
                        }
                    });
                    $.each(placements, function(index, obj) {
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
                        $(document).on("click", "input[name='placement']", function(e) {
                            $(this).closest("form").find("input[name='placement']").each( function() {
                                $(this).removeAttr("checked");
                            });
                            $(this).attr("checked", "checked");
                            var desc = $(this).closest(".radio").find(".placementDesc").html();
                            var placeImg = $(this).closest(".radio").find("img").attr("src");
                            $("#monogram-form .attrib.monogram-placement .monogram img").attr("src", placeImg);
                            $("#monogram-form .attrib.monogram-placement .monogram .placementDesc").html(desc);
                        });
                        $("#monogram-placement .placements-list .options input[name='placement']:first").attr("checked", "checked");
                    });
                });
            }
        })
    }
});
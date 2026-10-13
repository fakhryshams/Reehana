<script>
    var addMoreLimit = "{{config('admin.product.addMoreLimit')}}";
    var addMoreCount = '<?php echo session()->get('form_count'); ?>';
    <?php
    if (isset($galleryImage)) {
    ?>
        addMoreCount = "{{count($galleryImage)+session()->get('form_count')}}";
    <?php
    }
    ?>

    var ecount = "{{$emcount}}";

    var mcount = "{{$mcount}}";
    var dynamicClass = 0;
    $(document).ready(function() {
		 $('.newarr').select2();
		  $('.newarr').on('change', function () {
			 
			 if($(this).val() == 1)
			 {
				 $('.newArrivalSorting').show();
				 
			 }
			 else
			 {
				 $('.newArrivalSorting').hide();
				 
				 
			 }
			 
			 
		});	 

        $(".view_all").click(function() {

            if ($(".view_all").hasClass("hide")) {
                $('.view_all').text('Hide');
                $('.mm').show();
                $(".view_all").removeClass('hide');
            } else {

                $('.view_all').text('View');

                $('.mm').hide();

                $(".view_all").addClass('hide');

            }



        });
        if (ecount != mcount) {
            $("#select_all").removeAttr('checked');
        }

        $("#select_all").click(function() {
            if ($('#select_all').is(':checked')) {
                $('input[name="monogram[]"]').each(function(index, elem) {
                    var $elem = $(elem);
                    this.checked = true;
                    $('.m-' + index).prop('required', true);
                });
            } else {
                $(".monogram").prop('required', false);
                $('input[name="monogram[]"]').each(function(index, elem) {
                    var $elem = $(elem);
                    this.checked = false;
                    $('.m-' + index).prop('required', false);
                    if ($(this).val()) {
                        $('.mono-' + $(this).val()).val([]).trigger('change');
                    }
                });
            }
        });

        $(".monogram").click(function() {
            if ($(this).is(':checked')) {
                $(this)
                if ($(this).val()) {
                    $('.mono-' + $(this).val()).prop('required', true);
                }
                $(this).attr('checked', 'checked');
            } else {
                $(this).prop('required', false);
                if ($(this).val()) {
                    $('.mono-' + $(this).val()).prop('required', false);
                    $('.mono-' + $(this).val()).val([]).trigger('change');
                }
                $(this).removeAttr('checked');

            }

        });


        //checkCategoryForSizeQuantity();
        $(".select-category").change(function() {
            //checkCategoryForSizeQuantity();
        });
        changeCount();
        setMainDiscount();
        /*
         * For delete old input image 
         */
        $(".return_exchange_field").each(function() {
            if ($(this).val()) {
                $("input[name=add_return_exchange]").prop('checked', true);
                returnExchangeFields($("input[name=add_return_exchange]"));
                return;
            }
        });
        removeElement();
        $(".add_button").click(function() {
            addMoreCount++;
            dynamicClass++;
            if (addMoreCount <= addMoreLimit) {
                bindHtml(dynamicClass);
                if (addMoreCount == addMoreLimit) {
                    $(this).attr("disabled", "disabled");
                    $(this).hide();
                }
            } else {
                $(this).attr("disabled", "disabled");
                $(this).hide();
            }
        });

        if (addMoreCount == addMoreLimit) {
            $(".add_button").attr("disabled", "disabled");
            $(".add_button").hide();
        } else {
            //            $(".add_button").removeAttr("disabled");
            //            $(".add_button").show();
        }

        $(".select-category").select2({
            placeholder: "{{__('admin.select_category')}}",
            allowClear: true
        });
        $(".select-sub-category").select2({
            placeholder: "{{__('admin.select_subcategory')}}",
            allowClear: true
        });

        $(".select-brand").select2({
            placeholder: "{{__('admin.select_brand')}}",
            allowClear: true
        });

        /*
         *OnLoad 
         */
        seoFields($("#product_seo"));
        returnExchangeFields($("input[name=add_return_exchange]"));
        intlShipping($("#intl_shipping"));

        $("#category_id").change(function() {
            getSubCategory();
        });
        getSubCategory();

        /*
         * OnChange
         */
        $("#product_seo").change(function() {
            seoFields($(this));
        });
        $("input[name=add_return_exchange]").change(function() {
            returnExchangeFields($(this));
        });
        $("#intl_shipping").change(function() {
            intlShipping($(this));
        });

        $(document).on('change', 'select[name^="placement"]', function() {
            var $select = $(this);

            var $selectAll = $select.find('option[value="select_all"]');

            if ($selectAll.length && $selectAll.is(':selected')) {
                $selectAll.prop('selected', false);
                $select.find('option').each(function() {
                    if ($(this).val() !== 'select_all') {
                        $(this).prop('selected', true);
                    }
                });
                $select.trigger('change.select2');
            }

            var monogramId = (this.className.match(/mono-(\d+)/) || [])[1];
            if (monogramId) {
                var hasSelection = $select.find('option:selected').length > 0;
                $('input.monogram[value="' + monogramId + '"]').prop('checked', hasSelection);
                $select.prop('required', hasSelection);
            }
        });
    });

    function removeElement() {
        $(".remove-btn").click(function() {
            $(this).closest('div.row').remove(); //Remove field html
            addMoreCount--; //Decrease added count
            $(".add-more-limit").text(addMoreLimit - addMoreCount) //Change count text
            $(".add_button").removeAttr("disabled"); //enable button
            $(".add_button").show();
        });
    }

    function returnExchangeFields(flag) {
        if (flag.prop('checked')) {
            $(".return_exchange").show();
        } else {
            //$(".return_exchange_field").val("");
            $(".return_exchange").hide();
        }
    }

    function seoFields(seo) {
        if (seo.prop('checked')) {
            $(".product_seo_hidden").attr('disabled', 'disabled');
            $(".seo-fields").removeAttr('disabled');
            $(".seo-fields-box").show();
            $("input[name=product_seo_title]").val($("input[name=product_name_1]").val());
            $(".product_seo_description").html($("[name=product_description_1]").val());
            $("input[name=product_seo_description]").val($(".product_seo_description").text());
        } else {
            $(".product_seo_hidden").removeAttr('disabled');
            $(".seo-fields").attr('disabled', 'disabled');
            $(".seo-fields-box").hide();
            $(".seo-fields").val("");

        }
    }

    function intlShipping(status) {
        if (status.prop('checked')) {
            $(".intl_shipping_hidden").attr('disabled', 'disabled');
        } else {
            $(".intl_shipping_hidden").removeAttr('disabled');
        }
    }

    function readURL(input, imageClass, requiredWidth, requiredHeight) {
        if (input.files && input.files[0]) {
            var inputFile = input.files[0];
            var file = $('.' + imageClass);
            if (isImage(file.val())) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var image = new Image();
                    image.src = e.target.result;
                    image.onload = function() {
                        var height = this.height;
                        var width = this.width;
                        //alert(width + "," + height);

                        if (width == requiredWidth && height == requiredHeight) {
                            $('.' + imageClass).attr('data-res', 'true');
                            $('.' + imageClass).removeClass('is-invalid');
                            $('.' + imageClass + '-msg').html('');
                            $('.' + imageClass).attr('src', e.target.result);
                            validateFiles();
                        } else {
                            $('.' + imageClass).addClass('is-invalid');
                            $('.' + imageClass).attr('src', "{{ asset(Storage::url('default.png')) }}");
                            $('.' + imageClass).attr('data-res', 'false');
                            show_error("Required Dimension is " + requiredWidth + "x" + requiredHeight);
                            $('.' + imageClass + '-msg').html("{{__('admin.media_invalid')}}");
                            $('.form-submit-btn').attr('disabled', 'disabled');
                            validateFiles();
                        }
                    };
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                $('.' + imageClass).attr('data-res', 'false');
                show_error("{{__('admin.error-mime')}}");
                $('.form-submit-btn').attr('disabled', 'disabled')
                validateFiles();
            }
        }
    }

    function validateFiles() {
        var filesData = $("input[type=file]");
        $.each(filesData, function() {
            var att = $(this).attr('data-res');
            if (att == "false") {
                $('.form-submit-btn').attr('disabled', 'disabled');
                return false; // breaks
            } else {
                $('.form-submit-btn').removeAttr('disabled');
            }
        });
    }

    function getExtension(filename) {
        var parts = filename.split('.');
        return parts[parts.length - 1];
    }

    function isImage(filename) {
        var ext = getExtension(filename);
        switch (ext.toLowerCase()) {
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'bmp':
            case 'png':
                //case 'svg':
                //etc
                return true;
        }
        return false;
    }
    /*
     * Bind Image Div
     */
    function bindHtml(countHtml) {
        $.ajax({
            type: "POST",
            url: "{{route('admin.product.bind-html')}}",
            data: {
                count: countHtml,
                '_token': "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res) {
                    $(".add-more-append").append(res);
                    changeCount();
                    /*
                     * Bind file input for newly added element
                     */
                    bsCustomFileInput.init();
                    /*
                     * Unbind previously binded event and bind it again
                     */
                    $(".remove-btn").unbind('click');
                    removeElement();
                }
            }
        });
    }

    /*
     * Change Addmore Count
     */
    function changeCount() {
        $(".add-more-limit").text(addMoreLimit - addMoreCount)
    }

    function setDiscount() {
        $(".size-discount").unbind('change');
        $(".size-discount").change(function() {
            var selectedSizeId = $(this).attr('data-size');
            var price = $("input[name=price_" + selectedSizeId + "]").val();
            var discount = $("input[name=discount_" + selectedSizeId + "]").val();
            var calculatedDiscount = "";
            var discountIn = "{{config('custom.discount_in')}}";
            var numberFixed = "{{config('custom.number_fixed')}}";

            if (discountIn == "P") {
                if (discount && discount > 0) {
                    calculatedDiscount = (price * discount) / 100;
                    calculatedDiscount = price - calculatedDiscount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            } else if (discountIn == "F") {
                if (discount && discount > 0) {
                    calculatedDiscount = price - discount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }

            if (calculatedDiscount) {
                $("input[name=discount_price_" + selectedSizeId + "]").val(calculatedDiscount);
            } else {
                $("input[name=discount_price_" + selectedSizeId + "]").val("");
            }
        });
    }

    function setMainDiscount() {
        $(".main-price").unbind('change');
        $(".main-price").change(function() {
            var price = $("input[name=product_regular_price]").val();
            var discount = $("input[name=main_discount]").val();
            var calculatedDiscount = "";
            var discountIn = "{{config('custom.discount_in')}}";
            var numberFixed = "{{config('custom.number_fixed')}}";

            if (discountIn == "P") {
                if (discount && discount > 0) {
                    calculatedDiscount = (price * discount) / 100;
                    calculatedDiscount = price - calculatedDiscount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            } else if (discountIn == "F") {
                if (discount && discount > 0) {
                    calculatedDiscount = price - discount;
                    calculatedDiscount = calculatedDiscount.toFixed(numberFixed);
                }
            }

            if (calculatedDiscount) {
                $("input[name=product_sale_price").val(calculatedDiscount);
            } else {
                $("input[name=product_sale_price]").val("");
            }
        });
    }

    function getSubCategory() {
        var categoryID = $("#category_id").find("option:selected").val();
        var oldSubCategory = '<?php echo json_encode(old("sub_category_id", $productCategory)); ?>';
        if (categoryID) {
            $.ajax({
                type: "post",
                url: "{{route('admin.product.get-subcategory')}}",
                data: {
                    'categoryID': categoryID,
                    oldSubCategory: oldSubCategory,
                    '_token': "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res) {
                        $("#subcategory_id").empty();
                        $("#subcategory_id").append(res);
                    } else {
                        $("#subcategory_id").empty();
                    }
                }
            });
        }
    }
</script>
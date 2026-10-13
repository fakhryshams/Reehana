<?php
$setting      = \App\Helpers\Helper::getSetting();
$currency     = Helper::getDefaultCurrency();
$gift_price   = ($setting['32']->setting_val > 0 ? Helper::formatNumber($setting['32']->setting_val) : '0.000') * $currency->value;
$currencyCode = $currency ? $currency->code : config('custom.currency');
$giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();
$barcodecount = 0;
$bcolor = '';
$bsize = '';
if(isset($barcode) && $barcode)
{

 $barcodeSearch = \App\AdminModel\Product\AdminProductAttribute::where('barcode', $barcode)->get();

 if(count($barcodeSearch) >0)
 {
      if(count($barcodeSearch) > 1)
      {
        $barcode = '';
      }
      else
      {
      $bcolor = $barcodeSearch[0]->color_id;
      $bsize = $barcodeSearch[0]->size_id;
      }
}

}
 
?>
<script>
    var currency = "{{$currency->code}}";
    var sizeExist = "{{isset($sizes) && count($sizes)> 0?1:0}}";
    var colorExist = "{{isset($color) && count($color)>0?1:0}}";
    var barcode  = "{{isset($barcode)?$barcode:''}}";
    var bcolor  =  "{{isset($bcolor)?$bcolor:''}}";
    var bsize  =  "{{isset($bsize)?$bsize:''}}";
    var checksizes = true;
    
    $(document).ready(function ()
    {
		
		
    
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
    
      
      if(barcode && bcolor > 0)
      {
        // Color pre-selection for barcode navigation.
         // geSize() is async; size pre-selection (when both color+size exist)
         // is handled inside geSize()'s success callback.
         $('.select-color')
	    .val(bcolor)
	    .trigger('change');
	     var productId = $('.selectColor').attr("data-id");
	      geSize(productId, 1, bsize);
            var color = $("select[name=color_" + productId + "]").val();
            $('.edit_monogram').attr('data-colorexist', 1);
            $('.edit_monogram').attr('data-color', color ? color : '');
            getProductImage(productId, color, size,1);
      }
      
      if(barcode && bsize > 0 && !(bcolor > 0))
      {
        // Size-only pre-selection: only fires when there is NO color variant
         // (i.e. product has size but not color). When both exist, geSize()
         // handles the size selection after the AJAX response.       
         $('.select-size')
	    .val(bsize)
	    .trigger('change');
	    
	     var productId = $('.select-size').attr("data-id");
            checkCart(productId, sizeExist);
            binding();
            $(".size_select").removeClass("invalid");
            var size = $("select[name=size_" + productId + "]").val();
            var color = $("select[name=color_" + productId + "]").val();
            getProductImage(productId, color, size, 2);
      }
     

        $("#preloader").addClass("show");
        /*
         * Onload Check Cart (Wheter product with same attribtute exist in cart or not)
         */
        checkCart($(".select-size").attr("data-id"), sizeExist);
       
        binding();
        bindWishListClick();
        timeOut();
        $(".select-size").change(function ()
        {
            var productId = $(this).attr("data-id");
            checkCart(productId, sizeExist);
            binding();
            $(".size_select").removeClass("invalid");
            updateProductVariant(productId);
        });
        $(".select-color").change(function ()
        {
            var productId = $(this).attr("data-id");
            var color = $("select[name=color_" + productId + "]").val();
            $('.edit_monogram').attr('data-colorexist', 1);
            $('.edit_monogram').attr('data-color', color ? color : '');
            geSize(productId,2);
        });
    });
    function checkCart(productId, sizeExist)
    {
        var qt = 1;
        if (!productId)
        {
            return;
        }
        $('#selected_color_id').val(colorExist ? $("select[name=color_" + productId + "]").val() : '');
        $('#selected_size_id').val(sizeExist ? $("select[name=size_" + productId + "]").val() : '');
        $('#selected_product_id').val(productId);
        $('.decrease').attr("disabled", "disabled");
        $('.increase').attr("disabled", "disabled");
        $('.prod-count').attr("disabled", "disabled");

        $.ajax({
            type: 'POST',
            url: "{{route('web.product.check-cart')}}",
            data: {
                'product_id': productId,
                'sizeExist': sizeExist ? sizeExist : 0,
                'colorExist': colorExist ? colorExist : 0,
                'color': colorExist ? $("select[name=color_" + productId + "]").val() : '',
                'size': sizeExist ? $("select[name=size_" + productId + "]").val() : '',
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {
                if (data.cartStatus)
                {
                    var pid = data.qtyKey;
                    $('.add-cart-' + productId).addClass('add_to_cart');
                    $('.add-cart-' + productId).removeClass('update_cart');
                    $('.add-cart-' + productId).attr('data-id', productId);
                    $('.add-cart-' + productId).removeAttr('disabled');
                    $('.add-cart-' + productId).text("{{__('web.add_to_cart')}}");
                    $('.applyMonogramStyles').addClass('hide');
                    $('.apply_same').attr('checked', false);
                    Monogram(productId, sizeExist, $("select[name=size_" + productId + "]").val(), colorExist, $("select[name=color_" + productId + "]").val());
                    $('.decrease').removeClass('decrease_cart');
                    $('.increase').removeClass('increase_cart');
                    $('.increase').attr('data-product', '');
                    $('.decrease').attr('data-product', '');
                    $('.monogram-information').remove();
                    $('.addMonogramButton').removeAttr('disabled');
                    $('.apply_same').prop('checked', false);
                    $('.apply_same').removeAttr('checked');
                    $('.applyMonogramStyles2').addClass("hide");
                    $('.monogramSummary').attr('data-total', 0);
                    $('.monogramSummary').html('');
                    binding();
                } else
                {
                    qt = parseInt(data.cart.quantity);
                    $('.add-cart-' + productId).addClass('update_cart');
                    $('.add-cart-' + productId).removeClass('add_to_cart');
                    $('.add-cart-' + productId).attr('data-id', data.qtyKey);
                    $('.add-cart-' + productId).removeAttr('disabled');
                    $('.add-cart-' + productId).text("{{__('web.update_cart')}}");
                    if (parseInt(data.cart.quantity) == parseInt(data.monogramCount))
                    {
                        $('.addMonogramButton').attr('disabled', true);
                    } else
                    {
                        $('.addMonogramButton').removeAttr('disabled');
                    }
                    if (parseInt(data.cart.quantity) > 1 && parseInt(data.monogramCount) == 1)
                    {
                        $('.applyMonogramStyles').removeClass('hide');
                    } else
                    {
                        $('.applyMonogramStyles').addClass('hide');
                    }
                    $('.decrease').addClass('decrease_cart');
                    $('.increase').addClass('increase_cart');
                    $('.prod-count').addClass('qty_add');
                    $('.increase').attr('data-product', data.qtyKey);
                    $('.decrease').attr('data-product', data.qtyKey);
                    $('#mogogram_list').html('');
                    $('#mogogram_list').html(data.monogram_list);
                    $(".increase_cart").off('click.cartUpdate').on('click.cartUpdate', function () {
                        var product_id = $(this).attr("data-product");
                        var x = parseInt($('input[name="quantity_' + product_id + '"]').val());

                        qty_update(product_id, x);
                    });
                    $(".decrease_cart").off('click.cartUpdate').on('click.cartUpdate', function () {
                        var product_id = $(this).attr("data-product");
                        var x = parseInt($('input[name="quantity_' + product_id + '"]').val());
                        qty_update(product_id, x);
                    });
                    $(".qty_add").unbind('keyup');
                    $(".qty_add").keyup(function ()
                    {

                        var quantity = 1;
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
//                            if (x >= 1 && x < qty) {
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

                                } else if (selMonograms > 1)
                                {
                                    $('.applyMonogramStyles2').removeClass("hide");
                                    $('.applyMonogramStyles_' + id).removeClass("hide");
                                }


                                if (selMonograms > 1)
                                {
                                    $('input[name="applyAll_' + id + '"]').prop('checked', false);
                                    $('input[name="applyAll_' + id + '"]').removeAttr('checked');
                                    $('.applyMonogramStyles_' + id).addClass("hide");
                                    $('.applyMonogramStyles2').addClass("hide");
                                }

                            }


                            if (x >= 1 && x < qty) {
                                quantity = x;
                            } else {
                                $(this).closest(".form-item").find(".remains").addClass("show").addClass("red");
                                $(this).val(qty);

                                quantity = qty
                            }
                        } else {
                            $(this).closest(".counter").find(".remains").addClass("red");
                            $(this).val("");
                            //alert("The number of quantities should be a number and cannot exceed the maximum available number of quantities.");
                        }

                        qty_update($(this).attr('data-id'), quantity);

                    });



                    $(".edit_monogram2").click(function () {
//        alert(2020);

                        var id = $(this).attr('data-id');
                        var product_id = $(this).attr('data-productId');
                        var colorExist = $(this).attr('data-colorexist');
                        var sizeExist = $(this).attr('data-sizeexist');
                        var color = $(this).attr('data-color');
                        var monogram = $(this).attr('data-monogram');
                        $('.save_monogram').attr('data-editmonogram', monogram);
                        $('.save_monogram').attr('data-mono-id', id + '_' + monogram);
                        $('.save_monogram').attr('data-monogramexist', 1);
                        $('.save_monogram').attr('data-id', id);
                        $('.cancel_monogram').attr('data-monogram', monogram);
                        var size = $(this).attr('data-size');


                        //  alert($(".save_monogram").attr('data-edit'));
                        Monogram(product_id, sizeExist, size, colorExist, color, true, '', monogram);


                    });

                    $(".removeMonogram2").click(function () {


                        var product_id = $(this).attr('data-productid');
                        var size_id = $(this).attr('data-sizeId');
                        var color_id = $(this).attr('data-colorId');
                        var monogramNo = $(this).attr('data-monogram');

                        var id = $(this).attr('data-id');

                        var qty = $('input[name="quantity_' + id + '"]').val();
                        $.ajax({
                            url: $('#remove_monogram_url').val() + "/" + product_id + "/" + size_id + "/" + color_id + "/" + monogramNo,
                            success: (data) => {
                                var product_id = $('.addMonogramButton').attr('data-productid');
                                if (!product_id)
                                {
                                    product_id = '';
                                }
//                Monogram(product_id);
                                $(this).closest(".monogram-information").remove();
                                $('.addMonogramLink_' + id).find(".link-button").removeAttr('disabled');


                                if (parseInt(selectedQty) > 1 && parseInt(selMonograms) == 1) {
                                    $('.applyMonogramStyles_' + id).removeClass("hide").addClass("show");
                                }
                                if (parseInt(selMonograms) == 0)
                                {
                                    $('.applyMonogramStyles_' + id).removeClass("show").addClass("hide");
                                }
                                qty_update(id, qty);

                            }
                        });

                        $('.addMonogramButton').removeClass('hide');

                        var selectedQty = $('input[name="quantity_' + id + '"]').val();

                        var selMonograms = $(".monogram-information-" + id).length;



                    });


                }
                /*
                 * Available Quantity
                 */

                var data_show = 5;
                $(".counter").attr("data-maximum", data.availableQuantity);
                if (data.availableQuantity < 5)
                {
                    data_show = data.availableQuantity;
                }


                $(".counter").attr("data-show", data_show);
                $('.quantity').html(data.quantityOptionView);

                if (data.availableQuantity > 0)
                {
                    $(".prod-count").val(qt);
                }
                $('.remains_detail').html('(Only ' + data.availableQuantity + ' left)');
//                if (data.availableQuantity > 0)
//                {
//                    $(".remains").text("{{__('web.only')}} " + data.availableQuantity + " {{__('web.left')}}");
//                }
//                else
//                {
//                    $(".remains").text("{{__('web.sold_out')}}");
//                }




                $(".product-pricing-details").html(data.priceDetails);
                $(".wishlist-product-pricing-details").html(data.priceDetailsWishlist);

                $('.prod-count').attr('name', 'quantity_' + data.qtyKey);
                $('.apply_same').attr('name', 'applyAll_' + data.qtyKey);


                if (data.cart.is_same == '1')
                {
                    $('.addMonogramButton').attr('disabled', true);
                    $('input[name="applyAll_' + data.qtyKey + '"]').attr('checked', true);

                } else
                {
                    $('input[name="applyAll_' + data.qtyKey + '"]').attr('checked', false);
                }


                $('.apply_same').attr('data-id', data.qtyKey);
                $('.prod-count').attr('data-id', data.qtyKey);

                $(".decrease").attr('data-id', data.qtyKey);
                $(".increase").attr('data-id', data.qtyKey);
                $('.add-cart-' + productId).attr('data-mcart-id', data.qtyKey);

                $('.decrease').removeAttr("disabled");
                $('.increase').removeAttr("disabled");
                $('.prod-count').removeAttr("disabled");
                binding();
            }


        });
    }

    function qty_update(product_id, qty)
    {
        var giftMinPrice = "{{$giftMinPrice *  $currency->value}}";
        var currency = "{{$currencyCode}}";
        var gift_price = "{{$gift_price }}";
        $.ajax({
            type: 'POST',
            url: "{{route('web.product.update_quantity')}}",
            data: {
                'id': product_id,
                'is_gift': $("#gift_wrap").is(":checked") ? 1 : 0,
                'quantity': qty,
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {
                if (data)
                {
                    if (data.status)
                    {
                        for (var i = 0; i < data.data.length; i++)
                        {
                            var sizeId = data.data[i].size_id;
                            var colorId = data.data[i].color_id;
                            if (!sizeId)
                            {
                                sizeId = "s";
                            }
                            if (!colorId)
                            {
                                colorId = "c";
                            }
                            var productPriceHtml = data.data[i].price + ' ' + currency;
                            if (data.data[i].is_active == 0)
                            {
                                productPriceHtml = "<s>" + productPriceHtml + "</s>";
                            }

                            if (parseFloat(data.data[i].total) < parseFloat(giftMinPrice))
                            {


                                $('.gift_wrap_price').html('');
                                $('.gift_wrap_price').html(parseFloat(gift_price).toFixed(3) + ' ' + currency);
                                $('.gift-amount').html('');
                                $('.gift-amount').html(parseFloat(gift_price).toFixed(3) + ' ' + currency);

                            } else
                            {
                                $('.gift_wrap_price').html('');
                                $('.gift-amount').html('');
                                $('.gift_wrap_price').html(parseFloat(0.000).toFixed(3) + ' ' + currency);
                                $('.gift-amount').html(parseFloat(0.000).toFixed(3) + ' ' + currency);
                            }

                            $('.product_price_' + data.data[i].id + '_' + sizeId + '_' + colorId).html(productPriceHtml);
                            $('.sub_total').html(data.data[i].id).html(data.data[i].total + ' ' + currency);
                            $('.sub_total').attr('data-subtotal', data.data[i].total);
                            $('.grand-total').html(data.data[i].final_total + ' ' + currency);
                            $('.grand-total').attr('data-grand-total', data.data[i].final_total);
                            $('.coupon-final-discount').html(data.data[i].couponDiscountFinal + ' ' + currency);
                            $('.shipping-price').html(data.shipping + ' ' + currency);

                            $('.tax').html(data.data[i].tax + ' ' + currency);
                        }
                        $('.user_cart').html(data.page.view);
                        cartActions();
                    } else
                    {

                    }
                }
            },
            error: function (xhr)
            {
            }
        });
    }

    function geSize(productId, number='', barcodeSizeId=0)
    {

	// alert(number);


        $.ajax({
            type: 'POST',
            url: "{{route('web.product.get-size')}}",
            data: {
                'product_id': productId,
                'colorExist': 1,
                'color': $("select[name=color_" + productId + "]").val(),
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {
                $(".color_select").removeClass("invalid");
                if (data.sizeOptionView && data.is_size)
                {
                    $('.add_to_cart').attr('data-size', 1);
                    $('.sizeshow').show();
                    $('.select-size').html(data.sizeOptionView);
                    $(".size_select").removeClass("invalid");

                    // Barcode navigation: if a specific size was requested,
                    // pre-select it now that the options have been populated.
                    if (barcodeSizeId > 0)
                    {
                        $('.select-size').val(barcodeSizeId);
                        $(".size_select").removeClass("invalid");
                        var color = $("select[name=color_" + productId + "]").val();
                        var size  = $("select[name=size_"  + productId + "]").val();
                        $('.edit_monogram').attr('data-sizeexist', 1);
                        $('.edit_monogram').attr('data-size', size ? size : '');
                        checkCart(productId, sizeExist);
                        binding();
                        getProductImage(productId, color, size, 6);
                        return;
                    }

                    var size = $("select[name=size_" + productId + "]").val();
                    $('.edit_monogram').attr('data-sizeexist', 1);
                    $('.edit_monogram').attr('data-size', size ? size : '');
                } else
                {
                    $('.add_to_cart').attr('data-size', 0);
                    $('.select-size').html('');
                    $('.sizeshow').hide();
                }
                var price = '';
                if (data.product)
                {

                    if (data.product.is_product_sale_price_null == 1)
                    {
                        price = '<span class="prod-price">' + data.product.product_regular_price + ' ' + currency + '</span>';
                    } else
                    {
                        price = '<span class="prod-price">' + data.product.product_sale_price + ' ' + currency + '</span> <span class="striked">' + data.product.product_regular_price + ' ' + currency + '</span>';
                    }
                    $('.product-pricing-details').html(price);
                }
                updateProductVariant(productId);

                checkCart(productId, sizeExist);
                binding();
                 
                 
           //         if(barcode && bsize > 0 && checksizes  )
        	  //     {
        	       
        		 // $('.select-size')
        		 //    .val(bsize)
        		 //    .trigger('change');
        		    
        		 //    checksizes =false;
        		    
        		 //     var productId = $('.select-size').attr("data-id");
        		 //    checkCart(productId, sizeExist);
        		 //    binding();
        		 //    $(".size_select").removeClass("invalid");
        		 //    var size = $("select[name=size_" + productId + "]").val();
        		 //    var color = $("select[name=color_" + productId + "]").val();
        		 //    getProductImage(productId, color, size,6);
        	  //     }
                 
                 





            }
        });
        
		 
    }


    function bindWishListClick()
    {
        $(".add-wishlist").unbind('click');
        $(".add-wishlist").click(function ()
        {

            var productId = $(this).attr("data-id");
            $.ajax({
                type: 'POST',
                url: "{{route('web.wishlist.add_to_wishlist')}}",
                data: {
                    'id': productId,
                    'page': $(this).attr("data-page"),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if ($(this).attr("data-page") == 'product')
                    {
                        if (data.status == 'add')
                        {
                            $(this).addClass("selected")

                            var img = $('.image_' + productId).val();
                            $(".wishlist_image").attr("src", img);

                            $("#favAdd").fadeIn();
                            setTimeout(() => {
                                $("#favAdd").fadeOut();
                            }, 3000);
                            //show_message('success', '{{__("web.move-to-wishlist-message")}}');
                        } else if (data.status == 'remove')
                        {
                            var img = $('.image_' + productId).val();
                            $(".remove_wishlist_image").attr("src", img);
                            $(this).removeClass("selected");
                            $("#favRem").fadeIn();
                            setTimeout(() => {
                                $("#favRem").fadeOut();
                            }, 3000);
                            //show_message('success', '{{__("web.product-remove-wishlist-message")}}');
                        } else
                        {
                            //show_message('error', '{{__("web.error-message")}}');
                            /*
                             * Error
                             */
                        }

                        $('.user_cart').html('');
                        $('.user_cart').html(data.view);
                    } else
                    {
                        /*
                         * From Detail Page
                         */
                        if (data.status == 'add')
                        {
                            $(this).html("<i class='icon-wishlist'></i>" + "{{__('web.added_to_wishlist')}}");
                            $(this).removeClass("add-wishlist");

//                            $(this).removeClass("btn-primary");
//                            $(this).addClass("btn-secondary");
//                            $(this).addClass("favorite");
                            $(this).addClass("marked");

                            //  $(this).attr("disabled", "disabled");
//                            show_message('success', '{{__("web.move-to-wishlist-message")}}');
                            if (data.product_qty > 0)
                            {
                                $('.add-cart-' + productId).addClass('add_to_cart');
                                $('.add-cart-' + productId).attr('data-id', productId);
                                $('.add-cart-' + productId).removeAttr('disabled');
                                $('.add-cart-' + productId).html(" {{__('web.add_to_cart')}}");
                                cartActions();
                                $('.user_cart').html('');
                                $('.user_cart').html(data.view);
                            }

                            var img = $('.image_' + productId).val();


                            $(this).unbind('click');

                            $(".wishlist_image").attr("src", img);
                            $("#favAdd").fadeIn();
                            setTimeout(() => {
                                $("#favAdd").fadeOut();
                            }, 3000);
                        } else if (data.status == 'remove')
                        {
                            $(this).text("{{__('web.add_to_wishlist')}}");
                            $(this).addClass("add-wishlist");
                            $(this).addClass("btn-secondary");
                            $(this).removeClass("btn-primary");
                            $(this).removeAttr("disabled");
                            //      show_message('success', '{{__("web.product-remove-wishlist-message")}}');
                        } else
                        {
                            // show_message('error', '{{__("web.error-message")}}');
                            /*
                             * Error
                             */
                        }
                    }
                    $("#preloader").fadeOut("slow");
                },
                error: function (xhr)
                {
                    if (xhr.status == '401')
                    {
                        window.location.href = "{{route('web.user.login')}}";
//                        show_message_url("{{route('web.user.login')}}", '{{__("web.please-login")}}')
                    }
                }
            });
        });
    }

    function bindFilterUIClick()
    {
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

        $("#filters-handheld ul.filter-tabs > li").unbind('click');
        $("#filters-handheld ul.filter-tabs > li").click(function (e) {
            var el = $(this).index();
            $("#filters-handheld ul.filter-tabs > li").each(function () {
                $(this).removeClass("active");
            });
            $("#filters-handheld .filter-group").each(function () {
                $(this).removeClass("show");
            });
            $(this).addClass("active");
            $("#filters-handheld .filter-group").eq(el).addClass("show");
        });
    }
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

    var currentProductImageRequest = null;

    function updateProductVariant(productId)
    {
        var size = $("select[name=size_" + productId + "]").val();
        var color = $("select[name=color_" + productId + "]").val();
        getProductImage(productId, color, size);
    }

    function getProductImage(productId, color, size , number='')
    {

 if (currentProductImageRequest) {
            currentProductImageRequest.abort();
        }

        currentProductImageRequest = $.ajax({
            type: 'POST',
            url: "<?php echo e(route('web.product.get-product-imaage')); ?>",
            data: {
                'product_id': productId,
                'color': color,
                'size': size,
                '_token': '<?php echo e(csrf_token()); ?>'
            },
            success: (data) => {
                currentProductImageRequest = null;
            $('#productSlider').hasClass("slick-current")
          	 $('#productSlider').slick('unslick');
          	  $('#prodThumbs').slick('unslick');
                $('.prodImageSection').html('');
                $('.prodImageSection').html(data);
                
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
                
                  $("#productSlider, #prodThumbs").slick("slickUnfilter");
        $("#productSlider, #prodThumbs").slick("slickFilter", ".general");
               //filterImages();
                $("#preloader").fadeOut("slow");
            },
            error: (xhr, status, error) => {
                if (status === 'abort') {
                    return; // Ignore aborted request
                }
                currentProductImageRequest = null;
                $("#preloader").fadeOut("slow");
            }
        });
    }


    function timeOut()
    {
        setTimeout(function ()
        {
            $('.size-error').hide();
            $('.success_msg').hide();
            $('.error_msg').hide();
            $("#preloader").fadeOut("slow");
        }, 3000);
    }
</script>

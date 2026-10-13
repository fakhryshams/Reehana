
<?php
$setting = \App\Helpers\Helper::getSetting();
$currency = Helper::getDefaultCurrency();
$gift_price = ($setting['32']->setting_val > 0 ? Helper::formatNumber($setting['32']->setting_val) : '0.000') * $currency->value;

$giftMinPrice = \App\Helpers\Helper::getGiftMinPrice();

    
            
              
?>
<script>

    $(document).ready(function ()
    {

        

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
                if (x >= 1 && x < qty) {
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

                    } else
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

            qty_update($(this).attr('data-product'), quantity);

        });

        $(".qty_add").blur(function () {
            var quantity = 1;
            var id = $(this).attr("data-id");
            var productid = $(this).attr("data-productid");


            var selMonograms = 0;
            if ($(this).closest(".cart-item").length > 0) {
                selMonograms = $(".monogram-information-" + id).length;
            } else if ($(this).closest(".product-options").length > 0) {
                selMonograms = $(".monogram-information-" + id).length;
            }
            if ($(this).val() == "")
            {
                if (selMonograms > 0)
                {
                    quantity = selMonograms;
                }
                $(this).val(quantity);
            }
            qty_update($(this).attr('data-product'), quantity);

        });

        $(".increase_cart").click(function () {
            var product_id = $(this).attr("data-product");
            var x = parseInt($('.quantity_' + product_id).val());
            qty_update($(this).attr('data-product'), x);
        });
        $(".decrease_cart").click(function () {
            var product_id = $(this).attr("data-product");
            var x = parseInt($('.quantity_' + product_id).val());
            qty_update($(this).attr('data-product'), x);
        });

    });

    function binding()
    {

    }

    function qty_update(product_id, qty)
    {
        var giftMinPrice = "{{$giftMinPrice *  $currency->value}}";
        var currency = "{{$currencyCode}}";
        var gift_price = "{{$gift_price }}";
        $.ajax({
            type: 'POST',
            url: "{{route('web.product.update_quantity')}}",
            beforeSend: function ()
            {
                $("#preloader").stop(true, true).removeAttr("style").addClass("show").show();
            },
            complete: function ()
            {
                $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
            },
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
                            var shipping = data.shipping;
                           
                            $('.product_price_' + data.data[i].id + '_' + sizeId + '_' + colorId).html(productPriceHtml);
                            $('.sub_total').html(data.data[i].id).html(data.data[i].total + ' ' + currency);
                            $('.sub_total').attr('data-subtotal', data.data[i].total);
                            $('.grand-total').html(data.data[i].final_total + ' ' + currency);
                            $('.grand-total').attr('data-grand-total', data.data[i].final_total);
                            $('.coupon-final-discount').html(data.data[i].couponDiscountFinal + ' ' + currency);
                            $('.shipping-price').html(shipping + ' ' + currency);
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


        var selectedQty = $('input[name="quantity_' + product_id + '"]').val();

        var selMonograms = $(".monogram-information-" + product_id).length;


        if (parseInt(selectedQty) > 1 && parseInt(selMonograms) == 1) {
            $('.applyMonogramStyles_' + product_id).removeClass("hide").addClass("show");
        }
    }



    $(".apply_same2").click(function () {
        var cart_id = $(this).attr('data-id')
        var monocount = $('.monogram-information-' + cart_id).length;
        if (monocount > 0)
        {
            if ($(this).is(":checked")) {


                if (monocount == 1)
                {
                    $(this).closest(".product-options").find(".addMonogramButton").attr("disabled", "disabled");
                    $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").attr("disabled", "disabled");
                }
            } else {
                $(this).closest(".product-options").find(".addMonogramButton").removeAttr("disabled");
                $(this).closest(".cart-item").find(".addMonogramLink").find(".link-button").removeAttr("disabled");
            }


            $.ajax({
                type: 'GET',
                url: $('#apply_same_url').val() + '/' + cart_id,
                success: (data) => {
                    if (data.status)
                    {
                        var qty = parseInt($('.quantity_' + cart_id).val());

                        qty_update(cart_id, qty)
                    }
                }
            });
        } else
        {
            alert('please add monogram first');
            $(this).prop('checked', false);
            $(this).removeAttr('checked');
        }
    });




    $("#gift_wrap").change(function ()
    {
        giftwrap(0);
    });
    $(".place_order2").click(function ()
    {
        giftwrap(1);

    });

    $(".buy_now2").click(function ()
    {
        giftwrap(2);

    });



    $(".save_note").click(function ()
    {
        var id = $(this).attr("data-id");
        var note = $.trim($('.note_' + id).val());
        if (note == '')
        {
            $(".note-error-" + id).text("{{__('web.note-add-error-message')}}");
            $("#preloader").removeClass("show");

            setTimeout(function ()
            {
                $(".note-error-" + id).text("");
            }, 3000);
            return false;
        }

        $.ajax({
            type: 'POST',
            url: "{{route('web.product.add_note')}}",
            data: {
                'id': id,
                'note': note,
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {
                if (data)
                {

                    hideNote($("#addNote-" + id), id);
                    $(".custom-error2").show();
                    $(".note-success-" + id).text("{{__('web.note-add-message')}}");
                    // show_success("{{__('web.note-add-message')}}");
                } else
                {
                    //show_error("{{__('web.note-error-message')}}");
                }
            },
            error: function (xhr)
            {
            }
        });

    });

    $(".close-note").click(function ()
    {
        var id = $(this).attr("data-id");
        hideNote2($("#addNote-" + id), id);
    });


    $(".move_to_wishlist").click(function ()
    {
        var url = $(this).attr("data-href");
        window.location.href = url;
    });
    $(".delete_trash").click(function ()
    {
        var id = $(this).attr("data-id");
        var url = $(this).attr("data-href");
        var page = $(this).attr("data-page");
        var giftMinPrice = "{{$giftMinPrice *  $currency->value}}";
        var gift_price = "{{$gift_price }}";
        $.ajax({
            url: url,
            success: (data) => {
                if (data.status)
                {

                    var intlShippingStatusCount = parseInt($('#intlShippingStatusCount').val());
                    var intshipping =      $(this).attr('data-intshipping');
                    if(intshipping == 1)
                    {

                         if(intlShippingStatusCount >= 1)
                         {

                            intlShippingStatusCount = intlShippingStatusCount - 1;

                            $('#intlShippingStatusCount').val(intlShippingStatusCount);

                         }
                         if(intlShippingStatusCount == 0)
                            {

                                $('.not_deliver').hide();
                                $('.not_deliver').removeClass('sm-visible')


                            }

                    }

                    if(intlShippingStatusCount == 0)
                    {
                            $(".show_intl_box").unbind('click');
                            $('.show_intl_box_data').removeClass('show_intl_box');
                            $('.show_intl_box_data').addClass('buy_now2');
                              $(".buy_now2").unbind('click');
                              $(".buy_now2").click(function (e)
                                {
                                    e.preventDefault();

                                    giftwrap(2);

                                });



                    }
                    var img = $('.image_' + id).attr('src');
                    $(".cart_remove_image").attr("src", img);
                    $("#itemRem").fadeIn();
                    setTimeout(() => {
                        $("#itemRem").fadeOut();
                    }, 3000);
                    $(".cart_" + id).remove();
                    if (data.count == 0)
                    {
                        if (page == 'cart')
                        {
                            $('.cart-list').html('<p> Shopping Cart is Empty</p>');
                            $('.gift-wrap-item').addClass('hide');
                            $('.cart-summary').addClass('hide');
                        } else
                        {
                            location.reload();
                        }

                    } else
                    {
                        for (var i = 0; i < data.data.length; i++)
                        {
                            var sizeId = data.data[i].size_id;
                            if (!sizeId)
                            {
                                sizeId = "";
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
                                $('.gift-amount').html(parseFloat(gift_price).toFixed(3) + ' ' + currency);

                            }
                            $('.product_price_' + data.data[i].id + '_' + sizeId).html(productPriceHtml);
                            $('.sub_total').html(data.data[i].id).html(data.data[i].total + ' ' + currency);
                            $('.grand-total').html(data.data[i].final_total + ' ' + currency);
                            $('.shipping-price').html(data.data[i].shipping + ' ' + currency);


                            $('.coupon-final-discount').html(data.data[i].couponDiscountFinal + ' ' + currency);

                            $('.tax').html(data.data[i].tax + ' ' + currency);
                        }
                    }
                    $('.user_cart').html('');
                    $('.user_cart').html(data.view);
                }
            },
            error: function (xhr)
            {
            }
        });
        // window.location.href = url;
    });


    function hideNote(currentElement, id)
    {

        var ic = currentElement.children("i");
        ic.removeClass("icon-minus").addClass("icon-plus");
        var el = currentElement.parent().next(".textarea");


        $(".textarea_" + id).removeClass("show");
    }
    function hideNote2(currentElement, id)
    {

        var ic = currentElement.children("i");
        ic.removeClass("icon-minus").addClass("icon-plus");
        var el = currentElement.parent().next(".textarea");



    }


    function giftwrap(place_order)
    {
        var gift_price = "{{$gift_price}}";
        var giftMinPrice = "{{$giftMinPrice *  $currency->value}}";
        var currencyCode = "{{$currencyCode}}";
        var is_gift = 0;

        if ($("#gift_wrap").prop("checked"))
        {
            is_gift = 1;
        } else
        {
            is_gift = 0;
        }

        $.ajax({
            type: 'POST',
            url: "{{route('web.product.update_gift_warp')}}",
            beforeSend: function ()
            {
                $("#preloader").stop(true, true).removeAttr("style").addClass("show").show();
            },
            complete: function ()
            {
                $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
            },
            data: {
                'is_gift': is_gift,
                'title': $('.gift_title').val(),
                'message': $('.gift_message').val(),
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {

                if ($("#gift_wrap").prop("checked"))
                {

                    var product_grand_total = $('.grand-total').attr('data-grand-total');
                    var product_total = $('.sub_total').attr('data-subtotal');



                    if (parseFloat(product_total) >= parseFloat(giftMinPrice))
                    {
                        gift_price = 0;
                    }
                    // alert(gift_price);
                    var total = parseFloat(product_grand_total) + parseFloat(gift_price);

                    $('.grand-total').html(total.toFixed(3) + ' ' + currencyCode);
                    is_gift = 1;
                    $('.gift_wrap_item').removeClass('hide');
                    $('.gift_wrap_item').addClass('show');


                } else
                {
                    $('.gift_wrap_item').removeClass('show');
                    $('.gift_wrap_item').addClass('hide');
                    for (var i = 0; i < data.data.length; i++)
                    {



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


                        $('.sub_total').html(data.data[i].id).html(data.data[i].total + ' ' + currency);
                        $('.sub_total').attr('data-subtotal', data.data[i].total);
                        $('.grand-total').html(data.data[i].final_total + ' ' + currency);
                        $('.grand-total').attr('data-grand-total', data.data[i].final_total);
                        $('.coupon-final-discount').html(data.data[i].couponDiscountFinal + ' ' + currency);
                        $('.shipping-price').html(data.shipping + ' ' + currency);

                        $('.tax').html(data.data[i].tax + ' ' + currency);
                    }
                    is_gift = 0;
                    $('.gift_title').val('');
                    $('.gift_message').val('');

                }

                if (place_order == 1)
                {
                    window.location.href = "{{route('web.checkout.index')}}";
                }
                if (place_order == 2)
                {

                    $('#cart_review_form').submit();
                }



            },
            error: function (xhr)
            {
            }
        });
    }
</script>

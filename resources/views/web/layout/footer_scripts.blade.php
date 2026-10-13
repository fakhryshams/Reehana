<script src="{{asset('front/js/jquery.min.js')}}"></script>
<script src="{{asset('front/js/bootstrap.min.js')}}"></script>
<script src="{{asset('front/js/smooth-scroll.js')}}"></script>
<script src="{{asset('front/js/nav-dropdown.js')}}"></script>
<script src="{{asset('front/js/navbar-dropdown.js')}}"></script>
<script src="{{asset('front/js/jquery.touch-swipe.min.js')}}"></script>
<script src="{{asset('front/js/jquery-ui.js')}}"></script>
<script src="{{asset('front/js/slick.min.js')}}"></script>
<script src="{{asset('front/js/custom.js')}}"></script>
@yield('page_specific_js')
<?php $currency = Helper::getDefaultCurrency();
?>
@if(request()->routeIs('web.index'))
<script>

$(document).ready(function ()
{




    $("#preloader").fadeOut("slow");
    $(window).scroll(function () {
        var ht = $(window).height();
        var scroll = $(window).scrollTop();
        var secCollections1 = $(".collections-holder:nth-child(2)").position().top;
        var secCollections2 = $(".collections-holder:nth-child(3)").position().top;
        var secCollections3 = $(".collections-holder:last-child").position().top;
        var secTrending = $(".trending").position().top;
        var secFooter = $(".footer").position().top;

        if (window.matchMedia("(min-width: 992px)").matches) {
            if (secCollections1 <= scroll) {
                $(".collections-holder:nth-child(2)").addClass("initiated");
            }
            if (secCollections2 <= scroll) {
                $(".collections-holder:nth-child(3)").addClass("initiated");
            }
            if (secCollections3 <= scroll) {
                $(".collections-holder:last-child").addClass("initiated");
            }
            if (secTrending <= scroll + ht / 1.5) {
                $(".trending").addClass("initiated");
            }
            if (secFooter <= scroll + 330 * 2) {
                $(".footer").addClass("initiated");
            }
        }
    });

});
</script>
@endif
<script>
    var currency = "{{$currency->code}}";

    $(document).ready(function ()
    {
		
		


         $(".fb-btn").click(function ()
    {
        window.location.href = "{{route('web.user.facebook_login')}}";
    })
    $(".google-btn").click(function ()
    {
        window.location.href = "{{route('web.user.google_login')}}";
    })

        $(".print_summary").click(function ()
        {
            var url = $(this).attr('data-href');
            window.location.href = url

        });

        $(".select-currency").change(function ()
        {
            window.location.href = $('option:selected', this).attr('data-href');
        });
        $(".coupon_apply_web").click(function ()
        {
            var couponCode = "";
            $(".coupon_code_web").each(function ()
            {
                if ($(this).val())
                {
                    couponCode = $(this).val();
                    //return false; // breaks
                }
            });
            $(".coupon_code").val(couponCode);
            applyCoupon(couponCode);

        });

        $(".remove-coupon-code").click(function ()
        {
            $.ajax({
                type: 'POST',
                url: "{{route('web.product.check_coupon_code')}}",
                data: {
                    'remove_coupon': true,
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data.status)
                    {
                        if (data.data && data.data.length > 0)
                        {
                            //$(".cc-msg-success-text").text(data.message);
                            $(".cc-msg-success").show();
                            $(".cc-msg-fail").text("");
                            $(".cc-msg-fail").hide();
                            $(".coupon-total").removeClass('hide');

                            for (var i = 0; i < data.cart_data.length; i++)
                            {

                                $('.grand-total').html(data.cart_data[i].final_total + ' ' + currency);
                                $('.grand-total').attr('data-grand-total', data.cart_data[i].final_total);
                                $('.coupon-final-discount').html(data.cart_data[i].couponDiscountFinal + ' ' + currency);
                            }
                        } else
                        {
                            $(".cc-msg-success").hide("");
                            $(".coupon_code ").val("");
                            $(".coupon-total").addClass('hide');
                            $('.coupon-final-discount').html("");
                            for (var i = 0; i < data.cart_data.length; i++)
                            {

                                $('.grand-total').html(data.cart_data[i].final_total + ' ' + currency);
                                $('.grand-total').attr('data-grand-total', data.cart_data[i].final_total);
                                $('.coupon-final-discount').html(data.cart_data[i].couponDiscountFinal + ' ' + currency);
                            }
                        }


                    } else
                    {
                        if (data.cart_data)
                        {

                            for (var i = 0; i < data.cart_data.length; i++)
                            {
                                $('.grand-total').html(data.cart_data[i].final_total + ' ' + currency);
                                $('.grand-total').attr('data-grand-total', data.cart_data[i].final_total);
                            }
                        }
                        //$(".cc-msg-success-text").text("");
                        $(".cc-msg-success").hide("");
//                    $(".cc-msg-fail").text(data.message);
                        $(".cc-msg-fail").show();
                        //$(".coupon-total").hide();
                        $(".coupon-total").addClass('hide');
                        $('.coupon-final-discount').html("");
                    }
                }
            });
        });

        $(".resetBtn2").click(function (e)
        {

            $(".email_error").text("");
            e.preventDefault();
            if ($("#email").val())
            {

                var regex = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
                if ($("#email").val().match(regex))
                {
                    $("#reset-password2").submit();
                } else
                {
                    $(".email_error").text("enter valid email");
                }
            } else
            {
                e.preventDefault();
                $(".email_error").text("mandatory field");
            }
        });

        $(".number-input").keypress(function (e)
        {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57))
            {
                event.preventDefault();
            }
        });

<?php
if (session()->has('email_error'))
{
    ?>
            $("#resetPwdPopup").modal("show");
            $(".email_error").text("{{ session()->get('email_error') }}");
    <?php
}
?>
<?php
if (session()->has('email_suceess'))
{
    ?>
            $("#resetPwdPopup").modal("show");
            $(".email_suceess").text("{{ session()->get('email_suceess') }}");
    <?php
}
?>
<?php
if (session()->has('login_error'))
{
    ?>
            $('.login_email_error').show();
            $(".login_email_error").html("<i class='fa fa-info-circle'></i>{{ session()->get('login_error') }}");
    <?php
}
?>

        cartActions();
        $("#search_bind").keyup(function ()
        {
            var text = $.trim($("#search_bind").val());
            if (text == '')
            {
                $('#clear').addClass('hide');
            } else {
                $('#clear').removeClass('hide');
            }
        });

        var searchAutocomplete = $(".searchContainer input, .search-results .search-input input").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'POST',
                    url: "{{route('web.product.searchtext')}}",
                    data: {
                        'search': request.term,
                        '_token': '{{ csrf_token()}}'
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            select: function (event, ui)
            {
                if (ui.item.type === 'product') {
                    $(this).val(ui.item.value);
                    $("#search-form").submit();
                } else if (ui.item.url) {
                    window.location.href = ui.item.url;
                    return false;
                }
            },
            open: function(event, ui) {
                var $input = $(this);
                var rect = $input[0].getBoundingClientRect();
                $input.autocomplete("widget").css({
                    top: rect.bottom + 'px',
                    left: rect.left + 'px',
                    width: rect.width + 'px'
                });
            }
        });

        searchAutocomplete.each(function() {
            var instance = $(this).autocomplete("instance") || $(this).data("ui-autocomplete") || $(this).data("autocomplete");
            if (instance) {
                if (instance.menu && instance.menu.option) {
                    instance.menu.option("items", "> :not(.ui-autocomplete-category)");
                }

                instance._renderMenu = function (ul, items) {
                    var self = this;
                    var currentCategory = "";
                    $.each(items, function (index, item) {
                        var li;
                        if (item.category !== currentCategory) {
                            ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                            currentCategory = item.category;
                        }
                        li = self._renderItemData(ul, item);
                        if (item.category) {
                            li.attr("aria-label", item.category + " : " + item.label);
                        }
                    });
                };
            }
        });

        $(".searchContainer input, .search-results .search-input input").autocomplete("widget").addClass("sidebar-search-autocomplete");

        $(document).on("mousewheel DOMMouseScroll", ".sidebar-search-autocomplete", function (e) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;

            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        });



        $(".add-wishlist2").click(function ()
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

                            var img = $('.image_' + productId).attr('src');

                            $(".wishlist_image").attr("src", img);

                            $("#favAdd").fadeIn();
                            setTimeout(() => {
                                $("#favAdd").fadeOut();
                            }, 3000);
                            //show_message('success', '{{__("web.move-to-wishlist-message")}}');
                        } else if (data.status == 'remove')
                        {
                            var img = $('.image_' + productId).attr('src');
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
                            $('.add-cart-' + productId).addClass('add_to_cart');
                            $('.add-cart-' + productId).attr('data-id', productId);
                            $('.add-cart-' + productId).removeAttr('disabled');
                            $('.add-cart-' + productId).html(" {{__('web.add_to_cart')}}");
                            cartActions();
                            $('.user_cart').html('');
                            $('.user_cart').html(data.view);

                            var img = $('.image_' + productId).attr('src');
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
    });
    function cartActions()
    {
        $(".view_cart").unbind("click");
        $(".place_order").unbind("click");

//    View Cart
        $(".view_cart").click(function ()
        {
            window.location.href = "{{route('web.cart.index')}}";
        });

//Place Order
        $(".place_order").click(function ()
        {

            window.location.href = "{{route('web.checkout.index')}}";
        });
// ---------- go to checkout page ----------
        $(".cart-checkout .page-actions button").click(function ()
        {
			
			 let isPickupAddress = $('input[name=is_default]:checked').attr("data-is-pickup");
            if (isPickupAddress == 1) {
                let addressId = $('input[name=is_default]:checked').attr("data-addressid");
				
                let cPhone = $('.c_phone_' + addressId).val();
                if (!cPhone) {
                    show_error("{{__('web.enter_customer_phone')}}");
                } else if (cPhone.length < 8) {
                    show_error("{{__('web.cphone_validate',['digit'=>8])}}");
                } else {
                    window.location.href = "{{route('web.checkout.cart_review')}}" + "?cphone=" + cPhone;
                }
            } else {
                window.location.href = "{{route('web.checkout.cart_review')}}";
            }
            
        });
        $(".order_summary").click(function (e)
        {
            window.location.href = "{{route('web.checkout.create_order')}}";
        });


        $('.numberonly').keypress(function (e) {

            var charCode = (e.which) ? e.which : event.keyCode

            if (String.fromCharCode(charCode).match(/[^0-9]/g))
                return false;

        });
    }


    function binding()
    {
        $(".add_to_cart").unbind('click');
        $(".add_to_cart").click(function ()
        {
            var page = $(this).attr('data-page');
            //var sizeExist = "{{(isset($productAttributes)&&$productAttributes)?1:0}}";
            var sizeExist = $(this).attr('data-size');
            var colorExist = $(this).attr('data-color');
            var productId = $(this).attr("data-id");
            var mId = $(this).attr("data-mcart-id");
            var quantity = $("[name=quantity_" + mId + "]").val();
            var sizeId = '';
            var colorId = '';
            var selMonograms = $(".monogram-information-" + mId).length;

            if (quantity <= 0)
            {

                $(".custom-error").show();
                $(".qty-error-" + productId).text("{{__('web.error-cart-req-qty-error')}}");
                if (selMonograms > 0)
                {
                    $('.quantity').val(selMonograms);
                } else
                {
                    $('.quantity').val(1);


                }
                setTimeout(function ()
                {
                    $('.qty-error').hide();
                }, 3000);
                return false;
            }

            if (quantity == undefined)
            {
                quantity = 1;
            }

            if (colorExist != 0)
            {
                /*
                 * Select Size Validation
                 */

                colorId = $("select[name=color_" + productId + "]").val();
                if (!colorId)
                {
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
                    timeOut();
                    return false;
                }
            }
            if (sizeExist != 0)
            {
                /*
                 * Select Size Validation
                 */

                sizeId = $("select[name=size_" + productId + "]").val();
                if (!sizeId)
                {
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
                    timeOut();
                    return false;
                }
            }
            if (page == 'detail')
            {
                $(".color_select").removeClass("invalid");
                $(".size_select").removeClass("invalid");
            }
            var is_same = 0;
            if ($("input[name='applyAll_" + mId + "']").is(":checked")) {
                is_same = 1;
            } else {
                is_same = 0;
            }

            $.ajax({
                type: 'POST',
                url: "{{route('web.product.add_to_cart')}}",
                data: {
                    'id': productId,
                    'quantity': quantity,
                    'sizeExist': sizeExist,
                    'colorExist': colorExist,
                    'color': colorId,
                    'size': sizeExist ? sizeId : '',
                    // ----monogrm----
                    'monogramExist': $('#monogram_data').val(),
                    'is_same': is_same,
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
                    'monogram_note': $('textarea#monogramNote').val(),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data)
                    {
//                        if ($('#monogram_data').val() == 1)
//                        {
//
//                            $('.addMonogramButton').addClass('hide');
//                        }

                        if (data.status == false)
                        {
                            $('.error_msg').show();
                            $(".page-error").text(data.message);
                            show_message('error', data.message);
                        } else if (data.view)
                        {
                            $('.user_cart').html('');
                            $('.user_cart').html(data.view);
                            if (page == 'detail')
                            {
                                $(this).addClass('add_to_cart');
                                $(this).attr('data-id', productId);
                                $(this).removeAttr('disabled');
                                $(this).text("{{__('web.update_cart')}}");
                            }
                            else
                            {
                                $(this).removeClass('add_to_cart');
                                $(this).removeAttr('data-id');
                                $(this).attr('disabled', 'disabled');
                                $(this).text("{{__('web.added_to_cart')}}");
                            }
                            cartActions();
                            //show_message('success', '{{__("web.add_success")}}');
//                            $('.user_cart').addClass("open");
                            if (page == 'wishlist')
                            {
                                /*
                                 * If page == Wishlist then remove div
                                 */
                                $(".cart-item-" + productId).remove();
                            } else if (page == 'detail')
                            {
                                $(".wishlist-product").text("{{__('web.add_to_wishlist')}}");
                                $(".wishlist-product").addClass("add-wishlist");
                                $(".wishlist-product").addClass("btn-secondary");
                                $(".wishlist-product").removeClass("btn-primary");
                                $(".wishlist-product").removeAttr("disabled");
                                $(".wishlist-product").attr("data-id", productId);
                                bindWishListClick();
                            }
                        }
                        var img = $('.image_' + productId).val();
                        $(".cart_image").attr("src", img);

                        $("#itemAdd").fadeIn();
                        setTimeout(() => {
                            $("#itemAdd").fadeOut();
                        }, 3000);

                        $(".save_monogram").attr('data-edit', 'true');
                        if (page == 'detail')
                        {
                            $('.decrease').addClass('decrease_cart');
                            $('.increase').addClass('increase_cart');
                            $('.prod-count').addClass('qty_add');
                            $('.increase').attr('data-product', productId);
                            $('.decrease').attr('data-product', productId);

                            $(".increase_cart").click(function () {
                                var product_id = $(this).attr("data-product");
                                var id = $(this).attr("data-id");
                                var x = parseInt($('input[name="quantity_' + id + '"]').val());
                                qty_update($(this).attr('data-id'), x);
                            });
                            $(".decrease_cart").click(function () {
                                var product_id = $(this).attr("data-product");
                                var id = $(this).attr("data-id");
                                var x = parseInt($('input[name="quantity_' + id + '"]').val());
                                qty_update($(this).attr('data-id'), x);
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

                                        } else if(selMonograms > 1)
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

                        }

                        timeOut();
                    }
                },
                error: function (xhr)
                {
                    $('.error_msg').show();
                    if (xhr.status == 422)
                    {
                        $.each(xhr.responseJSON.errors, function (key, value)
                        {
                            $('.page-error').text(value);
                        });
                        timeOut();
                    }

                }
            });
        });
        $(".product_detail").unbind('click');
        $(".product_detail").click(function ()
        {

            window.location.href = $(this).attr("data-url");
        });
        $(".remove_wishlist").unbind('click');
        $(".remove_wishlist").click(function ()
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

                    if (data.status == 'remove')
                    {
                        var img = $('.image_' + productId).attr('src');

                        $(".remove_wishlist_image").attr("src", img);
                        $("#favRem").fadeIn();
                        setTimeout(() => {
                            $("#favRem").fadeOut();
                        }, 3000);
                        $('.cart-item-' + productId).remove();
                        if (data.count == 0)
                        {
                            $('.products-list').html("  <div class=''>{{__('web.no-record-message')}}  </div> ");
                        }
                        $('.wish_count').html("{{__('web.my_wishlist')}} (" + data.count + ")");

                    }
                },
                error: function (xhr)
                {
                    if (xhr.status == '401')
                    {
                        show_message_url("{{route('web.user.login')}}", '{{__("web.please-login")}}')
                    }
                }
            });

        });
        $(".move_to_cart").unbind('click');
        $(".move_to_cart").click(function ()
        {

            $(this).attr('data-page');
            var sizeExist = $(this).attr('data-size');
            var colorExist = $(this).attr('data-color');

            $.ajax({
                type: 'POST',
                url: "{{route('web.product.add_to_cart')}}",
                data: {
                    'id': productId,
                    'quantity': 1,
                    'sizeExist': sizeExist,
                    'colorExist': colorExist,
                    'color': '',
                    'size': '',
                    'monogramExist': 0,
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data)
                    {

                        if (data.view)
                        {
                            $('.user_cart').html('');
                            $('.user_cart').html(data.view);
                            $(this).removeClass('add_to_cart');
                            $(this).removeAttr('data-id');
                            $(this).attr('disabled', 'disabled');
                            $(this).text("{{__('web.added_to_cart')}}");
                            cartActions();
                            //show_message('success', '{{__("web.add_success")}}');
//                            $('.user_cart').addClass("open");
                            if (page == 'wishlist')
                            {
                                /*
                                 * If page == Wishlist then remove div
                                 */
                                $(".cart-item-" + productId).remove();
                            } else if (page == 'detail')
                            {
                                $(".wishlist-product").text("{{__('web.add_to_wishlist')}}");
                                $(".wishlist-product").addClass("add-wishlist");
                                $(".wishlist-product").addClass("btn-secondary");
                                $(".wishlist-product").removeClass("btn-primary");
                                $(".wishlist-product").removeAttr("disabled");
                                $(".wishlist-product").attr("data-id", productId);
                                bindWishListClick();
                            }
                        }
                        var img = $('.image_' + productId).val();
                        $(".cart_image").attr("src", img);

                        $("#itemAdd").fadeIn();
                        setTimeout(() => {
                            $("#itemAdd").fadeOut();
                        }, 3000);


                        timeOut();
                    }
                },
                error: function (xhr)
                {
                    $('.error_msg').show();
                    if (xhr.status == 422)
                    {
                        $.each(xhr.responseJSON.errors, function (key, value)
                        {
                            $('.page-error').text(value);
                        });
                        timeOut();
                    }

                }
            });
        });
    }
	
	
	
	function isMobileDevice() {
      return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    }

    // Function to set the default selected radio option based on the device type
    function setDefaultRadioOption() {
      var paymentMethodRadios = document.getElementsByClassName('paymentMethod');

      if (isMobileDevice()) {
		  
        // Select the 3rd radio option for mobile
        paymentMethodRadios[2].checked = true;
      } else {
        // Select the 1st radio option for desktop
        paymentMethodRadios[0].checked = true;
      }
    }

    // Set the default selected radio option when the page loads
    setDefaultRadioOption();

    function applyCoupon(couponCode)
    {
        //alert(couponCode);
        /*
         * Step 1: Validate coupon code
         */
        $.ajax({
            type: 'POST',
            url: "{{route('web.product.check_coupon_code')}}",
            data: {
                'coupon_code': couponCode,
                '_token': '{{ csrf_token()}}'
            },
            success: (data) => {
                if (data.status)
                {
                    //$(".cc-msg-success-text").text(data.message);
                    $(".cc-msg-success").show();
                    $(".cc-msg-fail").text("");
                    $(".cc-msg-fail").hide();
                    $(".coupon_name").html(couponCode);
                    $(".coupon-total").removeClass('hide');



                    for (var i = 0; i < data.cart_data.length; i++)
                    {
                        $('.grand-total').html(data.cart_data[i].final_total + ' ' + currency);
                        $('.grand-total').attr('data-grand-total', data.cart_data[i].final_total);
                        $('.coupon-final-discount').html(data.cart_data[i].couponDiscountFinal + ' ' + currency);
                    }

                } else
                {
                    if (data.cart_data)
                    {
                        for (var i = 0; i < data.cart_data.length; i++)
                        {
                            $('.grand-total').html(data.cart_data[i].final_total + ' ' + currency);
                            $('.grand-total').attr('data-grand-total', data.cart_data[i].final_total);
                        }
                    }
                    //$(".cc-msg-success-text").text("");
                    $(".cc-msg-success").hide("");
                    $(".cc-msg-fail").html('<i class="icon-close"></i> ' + data.message);
                    $(".cc-msg-fail").show();
                    //$(".coupon-total").hide();
                    $(".coupon-total").addClass('hide');

                    $('.coupon-final-discount').html("");
                }
            }
        });
    }
</script>
@include('web.layout.sweetalert')

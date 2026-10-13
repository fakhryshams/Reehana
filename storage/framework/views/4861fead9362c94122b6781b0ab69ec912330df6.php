<link rel="stylesheet" href="<?php echo e(asset('front/sweetalert2/sweetalert2.min.css')); ?>">
<script src="<?php echo e(asset('front/sweetalert2/sweetalert2.min.js')); ?>"></script>
<style>
    
    .swal2-styled.swal2-confirm{
        background-color: #caa42f !important
    }

    .continue-action-button[disabled] {
        opacity: .6;
        cursor: not-allowed;
        pointer-events: none;
    }
    
</style>
<script>
<?php
$shouldShowCheckoutError = request()->routeIs('web.cart.index')
    || request()->routeIs('web.checkout.index')
    || request()->routeIs('web.checkout.cart_review')
    || request()->routeIs('web.index');
?>
window.checkoutFlowBlocked = window.checkoutFlowBlocked || false;
<?php if ($shouldShowCheckoutError && session()->has('checkout_error')) { ?>
window.checkoutFlowBlocked = true;
<?php } ?>

function setContinueButtonsDisabled(disabled)
{
    window.checkoutFlowBlocked = disabled;
    $('.continue-action-button').prop('disabled', disabled);
}

<?php
if (session()->has('success'))
{
    ?>
    show_success("<?php echo e(session()->get('success')); ?>");
    <?php
}
if (isset($status) && !empty($status) && $status == 'success')
{
    ?>
    show_success("<?php echo e($message_text); ?>");
    <?php
}
if ($shouldShowCheckoutError && session()->has('checkout_error'))
{
    ?>
    if (window.checkoutErrorRedirectUrl) {
        show_error_redirect("<?php echo e(session()->get('checkout_error')); ?>", window.checkoutErrorRedirectUrl);
    } else {
        show_error("<?php echo e(session()->get('checkout_error')); ?>");
    }
    <?php
}
if (session()->has('error'))
{
    ?>
    show_error("<?php echo e(session()->get('error')); ?>");
    <?php
}
if (isset($status) && !empty($status) && $status == 'error')
{
    ?>
    show_error(" <?php echo e($message_text); ?>");
    <?php
}
?>

function show_error(message)
{
    setContinueButtonsDisabled(true);
    // Ensure loader is cleared before and after alert-driven validation flows.
    $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    Swal.fire('', message, "error").then(() => {
        $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    });
}
function show_error_redirect(message, url)
{
    setContinueButtonsDisabled(true);
    $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    Swal.fire({
        title: '',
        text: message,
        icon: 'error',
        showCancelButton: false,
        confirmButtonText: "<?php echo e(__('web.ok')); ?>"
    }).then((result) => {
        $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
        if (result.value && url) {
            window.location.href = url;
        }
    });
}
function show_success(message)
{
    Swal.fire('', message, "success");
}
function show_message(type, message)
{
    Swal.fire('', message, type);
}
function show_message_url(url, title)
{
    Swal.fire({
        title: '',
        text: title,
        icon: 'warning',
        showCancelButton: false,
        confirmButtonText: "<?php echo e(__('web.ok')); ?>",
        //cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.value)
        {
            window.location.href = url;
        }
    })
}
function cart_review_intl_box(title)
{
    if (window.checkoutFlowBlocked) {
        return;
    }
    $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    Swal.fire({
        title: '',
        text: title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "<?php echo e(__('web.ok')); ?>",
        cancelButtonText: "<?php echo e(__('web.cancel')); ?>"
    }).then((result) => {
        if (result.value)
        {
            var price = parseFloat($('.sub_total_price_detail').attr('data-subtotal'))

            if (price > 0)
            {
                $("#preloader").stop(true, true).removeAttr("style").addClass("show").show();
                $("form[name=cart_review_form]").submit();
            }
        } else {
            $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
        }
    })
}

$(function () {
    setContinueButtonsDisabled(!!window.checkoutFlowBlocked);
});
</script>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/sweetalert.blade.php ENDPATH**/ ?>
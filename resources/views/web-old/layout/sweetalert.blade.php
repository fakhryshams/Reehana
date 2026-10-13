<link rel="stylesheet" href="{{asset('front/sweetalert2/sweetalert2.min.css')}}">
<script src="{{asset('front/sweetalert2/sweetalert2.min.js')}}"></script>
<style>
    
    .swal2-styled.swal2-confirm{
        background-color: #caa42f !important
    }
    
</style>
<script>
<?php
if (session()->has('success'))
{
    ?>
    show_success("{{ session()->get('success') }}");
    <?php
}
if (isset($status) && !empty($status) && $status == 'success')
{
    ?>
    show_success("{{$message_text}}");
    <?php
}
if (session()->has('error'))
{
    ?>
    show_error("{{ session()->get('error') }}");
    <?php
}
if (isset($status) && !empty($status) && $status == 'error')
{
    ?>
    show_error(" {{ $message_text }}");
    <?php
}
?>

function show_error(message)
{
    // Ensure loader is cleared before and after alert-driven validation flows.
    $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    Swal.fire('', message, "error").then(() => {
        $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
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
        confirmButtonText: "{{__('web.ok')}}",
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
    $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
    Swal.fire({
        title: '',
        text: title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "{{__('web.ok')}}",
        cancelButtonText: "{{__('web.cancel')}}"
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
</script>

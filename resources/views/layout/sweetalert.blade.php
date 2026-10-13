<link rel="stylesheet" href="{{asset('plugins/sweetalert2/sweetalert2.min.css')}}">
<script src="{{asset('plugins/sweetalert2/sweetalert2.min.js')}}"></script>
<script>

function permission_denied()
{
    Swal.fire("Alert", "Permission Denied", "error");
}

function showSweetAlert(url)
{
    Swal.fire({
        title: '{{__("admin.are_you_sure")}}',
        text: '{{__("admin.not_able_to_recover")}}',
        type: 'danger',
        showCancelButton: true,
        confirmButtonText: '{{__("admin.yes_delete")}}',
        cancelButtonText: '{{__("admin.no_keep_it")}}'
    }).then((result) => {
        if (result.value)
        {
            window.location.href = url;
        }
    });
}

function categorySweetAlert(url, $deactivatedProductCount)
{
    if ($deactivatedProductCount)
    {
        Swal.fire({
            title: 'There are ' + $deactivatedProductCount + ' other \'In Active\' product in this category',
            text: '{{__("admin.want_to_activate_all")}}',
            type: 'danger',
            showCancelButton: true,
            confirmButtonText: '{{__("admin.yes_activate")}}',
            cancelButtonText: '{{__("admin.no_keep_it")}}'
        }).then((result) => {
            if (result.value)
            {
                window.location.href = url + "/1";
            }
            else
            {
                window.location.href = url;
            }
        });
    }
    else
    {
        window.location.href = url;
    }
}
function showSweetAlertCancel(url)
{
    Swal.fire({
        title: '{{__("admin.are_you_sure_cancel")}}',
        text: '{{__("admin.not_able_to_recover_order")}}',
        type: 'danger',
        showCancelButton: true,
        confirmButtonText: '{{__("admin.yes_cancel")}}',
        cancelButtonText: '{{__("admin.no_keep_it")}}'
    }).then((result) => {
        if (result.value)
        {
            window.location.href = url;
        }
    });
}
function showSweetAlertImage(url, id = '')
{
    Swal.fire({
        title: '{{__("admin.are_you_sure")}}',
        text: '{{__("admin.not_able_to_recover_file")}}',
        type: 'danger',
        showCancelButton: true,
        confirmButtonText: '{{__("admin.yes_delete")}}',
        cancelButtonText: '{{__("admin.no_keep_it")}}'
    }).then((result) => {
        if (result.value)
        {
            $("#pageloader").fadeIn();
            $.ajax({
                type: 'GET',
                url: url,
                success: (data) => {
                    $("#" + id).remove();
                    if (data.status)
                    {
                        addMoreCount--;
                        changeCount();
                        show_success(data.message);
                    }
                    else
                    {
                        show_error('{{__("admin.swal_error")}}', data.message);
                    }
                },
                error: function (xhr)
                {
                    show_error('{{__("admin.swal_error")}}', '{{__("admin.swal_error_message")}}');
                },
                complete: function (data)
                {
                    $("#pageloader").fadeOut();
                }
            });
        }
    });
}

function showSweetAlertAttrImage(url, id = '')
{
    Swal.fire({
        title: '{{__("admin.are_you_sure")}}',
        text: '{{__("admin.not_able_to_recover_file")}}',
        type: 'danger',
        showCancelButton: true,
        confirmButtonText: '{{__("admin.yes_delete")}}',
        cancelButtonText: '{{__("admin.no_keep_it")}}'
    }).then((result) => {
        if (result.value)
        {
            $("#pageloader").fadeIn();
            $.ajax({
                type: 'GET',
                url: url,
                success: (data) => {
                    $("." + id).attr("src","{{ asset(Storage::url('default.png')) }}");
                    $(".a_" + id).remove();
                    if (data.status)
                    {
                        show_success(data.message);
                    } else
                    {
                        show_error('{{__("admin.swal_error")}}', data.message);
                    }
                },
                error: function (xhr)
                {
                    show_error('{{__("admin.swal_error")}}', '{{__("admin.swal_error_message")}}');
                },
                complete: function (data)
                {
                    $("#pageloader").fadeOut();
                }
            });
        }
    });
}
function show_error(error, message)
{
    Swal.fire(error, message, "error");
}

function show_success(message)
{
    Swal.fire('{{__("admin.swal_success")}}', message, 'success');
}

function showSweetAlertRecoverOrder(url)
{
    Swal.fire({
        title: '{{__("admin.are_you_sure_cancel")}}',
        text: '{{__("admin.recover_order")}}',
        type: 'danger',
        showCancelButton: true,
        confirmButtonText: '{{__("admin.yes_cancelled_order")}}',
        cancelButtonText: '{{__("admin.no_keep_it")}}'
    }).then((result) => {
        if (result.value)
        {
            window.location.href = url;
        }
    });
}
</script>

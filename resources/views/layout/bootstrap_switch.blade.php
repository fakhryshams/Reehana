<script src="{{asset('plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}"></script>
<script>
$(function ()
{
    $("input[data-bootstrap-switch]").each(function ()
    {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
    $(".alert-checkbox").on('switchChange.bootstrapSwitch', function (event, state)
    {
        //alert($(this).prop('checked'));
        //alert($(this).attr('data-id'));

        $.ajax({
            type: "POST",
            url: "{{route('admin.general-setting.alert-setting.update')}}",
            data: {'setting_val': $(this).prop('checked'), 'id': $(this).attr('data-id'), '_token': "{{ csrf_token() }}"},
            success: function (res)
            {
                if (res.status)
                {
                    //window.location.reload();
                    //window.location = response.url;
                    show_success(res.message);
                }
                else
                {
                    show_error('Oops!', res.message);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
                show_error('Oops!', errorThrown);
            }
        });
    });
});
</script>
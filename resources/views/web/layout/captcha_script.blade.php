<script>
    $(function ()
    {
        refreshCaptcha();
        $(".refereshrecapcha").click(function ()
        {
            refreshCaptcha();
        });
    });

    function refreshCaptcha()
    {
        $.ajax({
            url: "{{route('web.refereshcapcha')}}",
            type: 'get',
            dataType: 'html',
            success: function (json)
            {
                $('.refereshrecapcha').html(json);
            },
            error: function (data)
            {
                //alert('Try Again.');
            }
        });
    }
</script>
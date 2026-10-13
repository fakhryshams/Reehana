<script>
    $(".asc-desc").click(function (e)
    {
        e.preventDefault();
        $("#pageloader").fadeIn();
        $(".asc-desc").not($(this)).removeAttr("id");
        $(".asc-desc").not($(this)).children().attr("disabled", 'disabled');

        if ($(this).attr("id") == 'asc')
        {
            $(this).attr("id", "desc");
            $(this).children().eq(0).removeAttr("disabled");
            $(this).children().eq(0).val("desc");
        }
        else if ($(this).attr("id") == 'desc')
        {
            $(this).attr("id", "asc");
            $(this).children().eq(0).removeAttr("disabled");
            $(this).children().eq(0).val("asc");
        }
        else
        {
            $(this).attr("id", "asc");
            $(this).children().eq(0).removeAttr("disabled");
            $(this).children().eq(0).val("asc");
        }

        $(".list-table").submit();
    });
    $("select[name=pageLength]").change(function (e)
    {
        e.preventDefault();
        $("#pageloader").fadeIn();
        $(".list-table").submit();
    });
</script>
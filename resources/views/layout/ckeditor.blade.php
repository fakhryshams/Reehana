<script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
<script src="{{asset('plugins/ckeditor/adapters/jquery.js')}}"></script>
<script>
$(function ()
{
    $(".textarea").ckeditor();
    CKEDITOR.config.autoParagraph = false;
});
</script>
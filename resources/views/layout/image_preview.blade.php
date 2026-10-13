<div class="modal fade" id="imagePreviewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('admin.image_preview')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img title="Preview" width="100%" class="image_upload_preview" src="" alt="Preview"  onerror="this.onerror=null;this.src='';">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script>
    function showDetails(record)
    {
        $(".image_upload_preview").attr('src', record);
        $("#imagePreviewModal").modal('show');
    }
</script>
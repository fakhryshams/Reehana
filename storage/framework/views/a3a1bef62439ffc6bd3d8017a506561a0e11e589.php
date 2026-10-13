<div id="addMonogram" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <span class="close cancel_monogram" ><span class="icon-close"></span></span>
            <div class="modal-body fancy">
                <div class="h-flex">
                    <div class="left-panel">
                        <h1>Add Monogram</h1>
                        <div class="preview-panel" id="monogramPreview">
                            <div>
                                <span id="initial-01"  style="mask-image: url(); -webkit-mask-image: url();"></span>
                                <span id="initial-02" style="mask-image: url(); -webkit-mask-image: url();"></span>
                                <span id="initial-03" style="mask-image: url(); -webkit-mask-image: url();"></span>
                                <span id="initial-04" class="hide"></span>
                            </div>
                        </div>
                    </div>
                    <!-- The classname as 'category-covers' in this DIV element below should be binding
                            from the category type as we load the detail page <div class="right-panel category-covers"> -->
                    <div class="right-panel category-all">
                        <form id="monogram-form" action="">
                            <input type="hidden" name="selected_color_id" id="selected_color_id" />
                            <input type="hidden" name="selected_size_id" id='selected_size_id' />
                            <input type="hidden" name="selected_product_id" id='selected_product_id' />

                            <div class="monogram-attribs">
                                <div class="attrib monogram-style">
                                    <h4>Monogram Style</h4>
                                    <div class="monogram">
                                        <img src="" onerror="this.src='<?php echo e(asset('front/img/loader.gif')); ?>'">
                                    </div>
                                    <span class="showOptions"><i class="icon-down"></i></span>
                                </div>
                                <div class="attrib monogram-initials">
                                    <h4>Initials</h4>
                                    <div class="monogram">
                                        <span>A-B-C</span>
                                    </div>
                                    <span class="showOptions"><i class="icon-down"></i></span>
                                </div>
                                <div class="attrib monogram-color">
                                    <h4>Color</h4>
                                    <div class="monogram">
                                        <span id="color" style="background-color: #000000;"></span>
                                        <span id="colorName">Black</span>
                                    </div>
                                    <span class="showOptions"><i class="icon-down"></i></span>
                                </div>
                                <div class="attrib monogram-placement">
                                    <h4>Placement</h4>
                                    <div class="monogram">
                                        <img src="<?php echo e(asset('front/monogram/placement1.png')); ?>" onerror="this.src='<?php echo e(asset('front/img/loader.gif')); ?>'">
                                        <div id="placementDesc" class="placementDesc"></div>
                                    </div>
                                    <span class="showOptions"><i class="icon-down"></i></span>
                                </div>
                                <div class="form-item addComment">
                                    <span class="link-button"><i class="icon-plus"></i>Add a Comment</span>
                                    <div class="addNoteInput addNoteInput2">
                                        <textarea maxlength="200" name="monogram_comment" id="monogramNote"> </textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="monogram-attribs-selection" id="monogram-style">
                                <span class="back"><i class="fa fa-arrow-left"></i> Back</span>
                                <div class="monogram-options">
                                    <h3>Monogram Style</h3>
                                    <!--- Monogram Style  -->
                                    <div class="options-list">

                                    </div>
                                </div>
                            </div>
                            <div class="monogram-attribs-selection" id="monogram-initials">
                                <span class="back"><i class="fa fa-arrow-left"></i> Back</span>
                                <div class="monogram-options setinitials">


                                </div>
                            </div>
                            <div class="monogram-attribs-selection" id="monogram-color">
                                <span class="back"><i class="fa fa-arrow-left"></i> Back</span>
                                <div class="monogram-options">
                                    <h3>Color</h3>
                                    <div class="options colors-list">
                                        <!--  -->
                                    </div>
                                </div>
                            </div>
                            <div class="monogram-attribs-selection" id="monogram-placement">
                                <span class="back"><i class="fa fa-arrow-left"></i> Back</span>
                                <div class="monogram-options placements-list">
                                    <h3>Placements</h3>
                                    <div class="form-item">
                                        <div class="options">
                                            <!--  -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="actions">
                    <button class="btn btn-secondary cancel_monogram"  >Cancel</button>
                    <button class="btn btn-primary save_monogram" data-edit="false" data-id="" data-size="" data-color="" data-editmonogram="" data-dismiss="modal">Save</button>
                </div>
                <div class="inner-action">
                    <button class="btn btn-primary">Apply</button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/monogram.blade.php ENDPATH**/ ?>
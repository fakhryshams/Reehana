<h3>Initials</h3>

<div class="form-item">
    <label>Letter Count</label>
    <div class="options">
        <?php $__currentLoopData = $initials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php if($val->id == 1): ?>
        <div class="radio w-50">
            <input type="radio" name="count" id="initials_1" class="one myinitials" value="one" <?php echo e($selected[0]->value1 == 1 ? 'checked="checked"' : ''); ?>>
            <label>Single Letter</label>
        </div>
        <?php endif; ?>

        <?php if($val->id == 2): ?>
        <div class="radio w-50">
            <input type="radio" name="count" id="initials_2" class="two myinitials"  value="two"  <?php echo e($selected[0]->value1 == 2 ? 'checked="checked"' : ''); ?>>
            <label>Two Letters</label>
        </div>
        <?php endif; ?>

        <?php if($val->id == 3): ?>
        <div class="clear"></div>
        <div class="radio w-50">
            <input type="radio" name="count" id="initials_3" class="three myinitials"  value="three"   <?php echo e($selected[0]->value1 == 3 ? 'checked="checked"' : ''); ?>>
            <label>Three Letters</label>
        </div>
        <?php endif; ?>

        <?php if($val->id == 4): ?>
        <div class="radio w-50">
            <input type="radio" name="count"  id="initials_4"  class="four" value="four" <?php echo e($selected[0]->value1 == 4 ? 'checked="checked"' : ''); ?>  >
            <label>More than 3</label>
        </div
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
</div>

<div class="form-item">
    <label>Type your initials below</label>

    <div class="initials-inputs letters-three">
        <div class="initials-input one show required">
            <input type="text" data-type="initials" onclick="checkInputOne()"   name="text1" maxlength="1" value="A">
            <span>Left</span>
        </div>
        <div class="initials-input two show required">
            <input type="text" data-type="initials" onclick="checkInputTwo()"   name="text2" maxlength="1" value="B">
            <span>Center</span>
        </div>
        <div class="initials-input three show required">
            <input type="text" data-type="initials" onclick="checkInputThree()"   name="text3" maxlength="1" value="C">
            <span>Right</span>
        </div>
        <div class="initials-input four hide">
            <input type="text" data-type="initials" onclick="checkInput()"   name="text4" maxlength="11">
            <span>(max 11 characters)</span>
        </div>
    </div>
</div>
<div class="gap">
    <div class="error hide" id="error">
        <i class="fa fa-info-circle"></i>
        <span></span>
    </div>
</div>

<!--  -->
<p id="nopreview-info" class="hide"><i class="fa fa-info-circle"></i> Preview for this option will not be available for this style.</p>
<p class="arabic-fonts-info">Arabic fonts available, kindly contact the store for more information and customization.</p><?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/initials.blade.php ENDPATH**/ ?>
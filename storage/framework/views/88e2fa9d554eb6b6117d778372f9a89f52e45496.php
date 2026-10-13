<!DOCTYPE html>
<html>

    <!--Head-->
    <?php echo $__env->make('layout/head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!--/ Head-->
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Navbar -->
            <?php echo $__env->make('layout/navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <!-- /.navbar -->
            <!-- Main Sidebar Container -->
            <?php echo $__env->make('layout/sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <!-- Content Wrapper. Contains page content -->
            <div id="pageloader">
                <i class="fa fa-spinner fa-spin fa-5x fa-fw text-primary"></i><span class="sr-only">Loading...</span>
            </div>
            <?php echo $__env->yieldContent('content'); ?>
            <!-- /.content-wrapper -->
            <!--Footer-->
            <?php echo $__env->make('layout/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <!--/ Footer-->
            <!-- Control Sidebar -->
            <?php echo $__env->make('layout/control_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <!-- /.control-sidebar -->
        </div>
        <!-- ./wrapper -->
        <!-- jQuery -->
        <!--Common Scripts-->
        <?php echo $__env->make('layout/common_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->yieldContent('page_specific_js'); ?>
        <script>
            $(document).ready(function ()
            {
                setTimeout(function ()
                {
                    $('.alert').fadeOut('slow');
                }, 5000); // <-- time in milliseconds

                $("form").on("submit", function (e)
                {
                    $("#pageloader").fadeIn();
                });//submit
                $(".a-submit").on("click", function (e)
                {
                    $("#pageloader").fadeIn();
                });//submit
                /*
                 * Input must be a number only - Common Validation
                 */
                bindValidation();

            });

            function bindValidation()
            {

                $(".number-input").keypress(function (e)
                {
                    if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57))
                    {
                        event.preventDefault();
                    }
                });

                /*
                 * Input must be anumber or float only  - Custom validation
                 */
                $('.float-input').bind('paste', function ()
                {
                    var self = this;
                    setTimeout(function ()
                    {
                        if (!/^\d*(\.\d{1,3})+$/.test($(self).val()))
                            $(self).val('');
                    }, 0);
                });

                $('.float-input').keypress(function (e)
                {
                    var character = String.fromCharCode(e.keyCode)
                    var newValue = this.value + character;
                    //if (isNaN(newValue) || hasDecimalPlace(newValue, 4))
                    if (isNaN(newValue))    
                    {
                        e.preventDefault();
                        return false;
                    }
                });

                function hasDecimalPlace(value, x)
                {
                    var pointIndex = value.indexOf('.');
                    return  pointIndex >= 0 && pointIndex < value.length - x;
                }

                /*
                 * Input must be sting only - Custom validation
                 */
                $('.string-input').bind('keyup blur', function ()
                {
                    $(this).val($(this).val().replace(/[^a-z]/g, ''));
                });

                /*
                 * Input must be sting with special character - Custom validation
                 */
                $('.special-char-input').bind('keyup blur', function ()
                {
                    $(this).val($(this).val().replace(/[^A-Za-z_@./#&+-]*$/, ''));
                });

                /*
                 * Input max length
                 */
                $("input[type=text]").attr('maxlength', 100);
                $("input[type=password]").attr('maxlength', 20);
                $("input[type=number]").attr('maxlength', 15);
                $(".length-2").attr('maxlength', 2);
                $(".length-3").attr('maxlength', 3);
                $(".length-4").attr('maxlength', 4);
                $(".length-5").attr('maxlength', 5);
                $(".length-6").attr('maxlength', 6);
                $(".length-8").attr('maxlength', 8);
                $(".length-9").attr('maxlength', 9);
                $(".length-10").attr('maxlength', 10);
                $(".length-20").attr('maxlength', 20);
                $(".length-28").attr('maxlength', 28);
                $(".length-11").attr('maxlength', 11);
                $(".length-15").attr('maxlength', 15);
                $(".length-25").attr('maxlength', 25);
                $(".length-30").attr('maxlength', 30);
                $(".length-40").attr('maxlength', 40);
                $(".length-28").attr('maxlength', 28);
                $(".length-27").attr('maxlength', 27);
                $(".length-82").attr('maxlength', 82);
                $(".length-70").attr('maxlength', 70);
                $(".length-75").attr('maxlength', 75);
                $(".length-50").attr('maxlength', 50);
                $(".length-100").attr('maxlength', 100);
                $(".length-150").attr('maxlength', 150);
                $(".length-500").attr('maxlength', 500);
                $(".length-1000").attr('maxlength', 1000);
                $(".required").next().attr('required', 'required');
                $(".setting-text").attr('maxlength', false);
            }
        </script>
        <!--/ Common Scripts-->
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/layout/main.blade.php ENDPATH**/ ?>
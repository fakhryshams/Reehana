<link rel="stylesheet" href="<?php echo e(asset('plugins/datatables-rowreorder/css/rowReorder.bootstrap4.min.css')); ?>">
<script src="<?php echo e(asset('plugins/datatables/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/datatables-rowreorder/js/dataTables.rowReorder.min.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')); ?>"></script>
<script>
$(document).ready(function ()
{
    var oTable = $('.normal-table').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "oLanguage": {
            "sEmptyTable": "<?php echo e(__('admin.no_record_found')); ?>"
        }
    });

    var oTable = $('.sorting-table').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "rowReorder": {
            "update": false,
            selector: 'td:not(:first-child):not(:last-child):not(.reorder-exclude)'
        },
        "oLanguage": {
            "sEmptyTable": "<?php echo e(__('admin.no_record_found')); ?>"
        }
    });

<?php
$na =0;
if(isset($newArrivalFilter))
{
	
	$na= $newArrivalFilter;
}
	

if (isset($sortVal))
{
    ?>
        oTable.on('row-reorder', function (e, diff, edit)
        {
            var ids = new Array();
            for (var i = 0; i < diff.length; i++)
            {
                var firstCell = diff[i].node.children[0];
                var input = firstCell.querySelector('input');
                var sorting_id = input ? input.value : firstCell.innerHTML.trim();
                ids.push({'sorting_id': sorting_id, 'id': diff[i].node.id, 'order': (i + 1)});
            }
            if (ids)
            {
                $.ajax({
                    type: "POST",
                    url: "<?php echo e(route('sorting.change-order')); ?>",
                    data: {'sorting': ids, '_token': "<?php echo e(csrf_token()); ?>", 'order': '<?php echo e($sortVal); ?>', 'model': $(".sorting-table").attr('data-model'),'newarrival' : '<?php echo e($na); ?>'},
                    success: function (res)
                    {
                        if (res.status)
                        {
                            window.location.reload();
                            //show_success(res.message);
                        }
                        else
                        {
                            show_error('Oops!', res.message);
                        }

                        oTable.draw();
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        show_error('Oops!', errorThrown);
                    }
                });
            }
        });

    <?php
}
?>
});
</script><?php /**PATH C:\xampp\htdocs\reehana\resources\views/layout/datatable.blade.php ENDPATH**/ ?>
<?php $__env->startSection('page_title', __('admin.temporary-orders')); ?>
<?php $__env->startSection('additional_css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('plugins/select2/css/select2.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <?php echo $__env->make('layout/toaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?php echo e(__('admin.temporary-orders')); ?></h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.home.index')); ?>"><?php echo e(__('admin.home')); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.order.index')); ?>"><?php echo e(__('admin.order')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(__('admin.temporary-orders')); ?></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- /.row -->
            <div class="row">
                <div class="col-12">
                    <form class="list-table" method="get" action="<?php echo e(route('admin.order.temporary-orders')); ?>">
                        <div class="row">
                            <div class="col-12">
                                <section class="content">
                                    <div class="card <?php echo e(config('custom.card-primary')); ?>">
                                        <div class="card-body pb-0">
                                            <div class="row">
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <select name="_cty[]" class="form-control select2 select-type <?php echo e(config('custom.select2-css')); ?>" data-dropdown-css-class="<?php echo e(config('custom.select2-css')); ?>" multiple="" style="width: 100%">
                                                        <?php $__currentLoopData = $orderType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($key); ?>" <?php echo e((collect($filter['customOrderType'] ?? [])->contains($key)) ? 'selected':''); ?>><?php echo e(ucfirst($value)); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <select name="_os[]" class="form-control select2 select-status <?php echo e(config('custom.select2-css')); ?>" data-dropdown-css-class="<?php echo e(config('custom.select2-css')); ?>" multiple="" style="width: 100%">
                                                        <?php $__currentLoopData = $orderStatusList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sKey => $sVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($sKey); ?>" <?php echo e((collect($filter['orderStatus'] ?? [])->contains($sKey)) ? 'selected':''); ?>><?php echo e($sVal); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                            </div>  
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-toolbar float-right">
                                                <div class="btn-group">
                                                    <button type="submit" class="<?php echo e(config('custom.btn-success-form')); ?>" title="<?php echo e(__('admin.search')); ?>"><?php echo e(__('admin.search')); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="card <?php echo e(config('custom.card-primary')); ?>">
                            <div class="table-card card-header pl-0">   
                                <div class="card-tools w-100">
                                    <?php echo $__env->make('common/paginate', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    <?php echo Helper::printPageRecord($orders); ?>

                                    <div class="input-group input-group-sm float-right custom-search">
                                        <input type="text" name="q" class="form-control float-right float-sm-right" placeholder="<?php echo e(__('admin.search')); ?>" value="<?php echo e(isset($searchText)?$searchText:''); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" title="<?php echo e(__('admin.search')); ?>"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="table-responsive">
                                <table class="table normal-table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="asc-desc text-center" id="<?php if($sortKey=='id'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.temp_order_id')); ?><input <?php if($sortKey!='id'): ?>disabled=""<?php endif; ?> type="hidden" name="id" value="<?php if($sortKey=='id'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="asc-desc" id="<?php if($sortKey=='name'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.name')); ?><input <?php if($sortKey!='name'): ?>disabled=""<?php endif; ?> type="hidden" name="name" value="<?php if($sortKey=='name'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="asc-desc" id="<?php if($sortKey=='email'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.email')); ?><input <?php if($sortKey!='email'): ?>disabled=""<?php endif; ?> type="hidden" name="email" value="<?php if($sortKey=='email'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="asc-desc" id="<?php if($sortKey=='phone'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.phone')); ?><input <?php if($sortKey!='phone'): ?>disabled=""<?php endif; ?> type="hidden" name="phone" value="<?php if($sortKey=='phone'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='type'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.type')); ?><input <?php if($sortKey!='type'): ?>disabled=""<?php endif; ?> type="hidden" name="type" value="<?php if($sortKey=='type'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='custom_order_number'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.po_no')); ?>/<?php echo e(__('admin.quotation_no')); ?><input <?php if($sortKey!='custom_order_number'): ?>disabled=""<?php endif; ?> type="hidden" name="custom_order_number" value="<?php if($sortKey=='custom_order_number'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='status'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.order_status')); ?><input <?php if($sortKey!='status'): ?>disabled=""<?php endif; ?> type="hidden" name="status" value="<?php if($sortKey=='status'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='total'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.total')); ?><input <?php if($sortKey!='total'): ?>disabled=""<?php endif; ?> type="hidden" name="total" value="<?php if($sortKey=='total'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='advance'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.advance_payment')); ?><input <?php if($sortKey!='advance'): ?>disabled=""<?php endif; ?> type="hidden" name="advance" value="<?php if($sortKey=='advance'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='pending'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.pending_amount')); ?><input <?php if($sortKey!='pending'): ?>disabled=""<?php endif; ?> type="hidden" name="pending" value="<?php if($sortKey=='pending'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center asc-desc" id="<?php if($sortKey=='created_at'): ?><?php echo e($sortVal); ?><?php endif; ?>"><?php echo e(__('admin.order-date')); ?><input <?php if($sortKey!='created_at'): ?>disabled=""<?php endif; ?> type="hidden" name="created_at" value="<?php if($sortKey=='created_at'): ?><?php echo e($sortVal); ?><?php endif; ?>"></th>
                                            <th class="text-center"><?php echo e(__('admin.actual_order_id')); ?></th>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any([config('custom_middleware.view_order'), config('custom_middleware.view_order_customized_order_list')])): ?>
                                            <th class="text-center"><?php echo e(__('admin.action')); ?></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $linkedOrder = $record->getLinkedOrder();
                                            $custName = $record->customer_name ?: ($record->user ? ($record->user->first_name . ' ' . $record->user->last_name) : '');
                                            $custEmail = $record->customer_email ?: ($record->user ? $record->user->email : '');
                                            $custPhone = $record->customer_phone ?: ($record->user ? $record->user->mobile : '');
                                            $currCode = $currency ? $currency->code : config('custom.currency', 'KWD');
                                        ?>
                                        <tr>
                                            <td class="text-center font-weight-bold">#<?php echo e($record->id); ?></td>
                                            <td><?php echo e($custName ?: '-'); ?></td>
                                            <td><?php echo e($custEmail ?: '-'); ?></td>
                                            <td><?php echo e($custPhone ?: '-'); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo e($record->type_badge_class); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $record->type))); ?></span>
                                            </td>
                                            <td class="text-center font-weight-bold"><?php echo e($record->formatted_document_number); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo e($record->status_badge_class); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $record->status))); ?></span>
                                            </td>
                                            <td class="text-center font-weight-bold"><?php echo e(Helper::formatNumber($record->grand_total)); ?> <small><?php echo e($currCode); ?></small></td>
                                            <td class="text-center"><?php echo e(Helper::formatNumber($record->advance_price)); ?> <small><?php echo e($currCode); ?></small></td>
                                            <td class="text-center text-danger font-weight-bold"><?php echo e(Helper::formatNumber($record->pending_amount)); ?> <small><?php echo e($currCode); ?></small></td>
                                            <td class="text-center"><?php echo e(\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_datetime_format'))); ?></td>
                                            <td class="text-center">
                                                <?php if($linkedOrder): ?>
                                                    <a href="<?php echo e(route('admin.order.customized-pending-order.vieworder', ['id' => $linkedOrder->id])); ?>" title="<?php echo e(__('admin.view_order')); ?>">
                                                        <span class="badge badge-success font-weight-bold">#<?php echo e($linkedOrder->id); ?></span>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any([config('custom_middleware.view_order'), config('custom_middleware.view_order_customized_order_list')])): ?>
                                            <td class="text-center" style="white-space: nowrap;">
                                                <a href="<?php echo e(route('admin.order.temporary-orders.vieworder', ['id' => $record->id])); ?>" title="<?php echo e(__('admin.view_order')); ?>">
                                                    <button type="button" class="<?php echo e(config('custom.btn-primary', 'btn btn-outline-primary btn-sm')); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </a>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(config('custom_middleware.delete_order_order'))): ?>
                                                <a onclick="showSweetAlert('<?php echo e(route('admin.order.temporary-orders.delete', ['id' => $record->id])); ?>')" title="<?php echo e(__('admin.delete')); ?>" style="cursor: pointer;">
                                                    <button type="button" class="<?php echo e(config('custom.btn-danger', 'btn btn-outline-danger btn-sm')); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="13" class="text-center py-4 text-muted"><?php echo e(__('admin.no_record_found')); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        
                        <div class="table-pagination pt20 float-right">
                            <?php echo e($orders->appends(request()->except('page'))->links('pagination::bootstrap-4')); ?>

                        </div>
                    </form>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_specific_js'); ?>
<?php echo $__env->make('common/sorting', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layout/datatable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layout/sweetalert', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layout/select2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> 
<script>
    $(document).ready(function () {
        $(".select-status").select2({
            placeholder: "<?php echo e(__('admin.filter_by_order_status')); ?>",
            allowClear: true
        });
        $(".select-type").select2({
            placeholder: "<?php echo e(__('admin.filter_by_order_type')); ?>",
            allowClear: true
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout/main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\reehana\resources\views/admin/order/order/temporary-orders-data.blade.php ENDPATH**/ ?>
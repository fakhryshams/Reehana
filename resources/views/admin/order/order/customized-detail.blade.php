@extends('layout/main')
@section('page_title',__('admin.customized-order-detail'))
    <style>
        .subtotal-table td,
        .subtotal-table th {
            padding: 0.40rem !important;
        }
    </style>
<?php
$existingCust = json_decode($orderDetail->custom_order_details, true);
$existingCust['shipping'] = $orderDetail->delivery_price ?? ($existingCust['shipping'] ?? 0);
$existingCust['discount_type'] = $orderDetail->discount_type
    ?: ($orderDetail->discount ?: ($existingCust['discount_type'] ?? 0));
$existingCust['discount_val'] = $orderDetail->discount_type == 'percentage'
    ? $orderDetail->discount_per
    : (
        (!empty($orderDetail->discount_value) && $orderDetail->discount_value != 0)
            ? $orderDetail->discount_value
            : (
                (!empty($orderDetail->discount))
                    ? $orderDetail->discount
                    : ($existingCust['discount_val'] ?? 0)
            )
    );

if (!$existingCust) {
    $existingCust = [
        'customer' => [],
        'items' => [],
        'shipping' => $orderDetail->delivery_price ?? 0,
        'discount_type' => 'flat',
        'discount_val' => 0
    ];
}
// Ensure items array exists
if (!isset($existingCust['items']) || empty($existingCust['items'])) {
    // Attempt to map from orderProductDetail if JSON is empty but items exist (e.g. migration case)
    if ($orderProductDetail && count($orderProductDetail) > 0) {
        foreach($orderProductDetail as $op) {
            $existingCust['items'][] = [
                'item_code' => '', // Cannot infer
                'product_title' => $op->product_name,
                'description'   => $op->note,
                'color'         => '', // Cannot infer easily without parsing attrs
                'size'          => '',
                'price'         => $op->product_price,
                'quantity'      => $op->quantity,
                'total'         => $op->total_price
            ];
        }
    } else {
        // Minimum one empty item for the form to render correctly
         $existingCust['items'][] = [
            'item_code' => '',
            'product_title' => '',
            'description' => '',
            'color' => '',
            'size' => '',
            'price' => '',
            'quantity' => '',
            'total' => ''
         ];
    }
}
$storedReferenceImage = !empty($existingCust['reference_image'])
    ? preg_replace('/^public\//', '', $existingCust['reference_image'])
    : null;
$defaultReferenceImageUrl = asset(Storage::url('default.png'));
?>
<?php
$jsAddresses = $addresses->map(function ($addr) {
    return [
        'id' => $addr->id,
        'address_title' => $addr->address_title,
        'first_name' => $addr->first_name,
        'last_name' => $addr->last_name,
        'country_id' => $addr->country_id,
        'city_id' => $addr->city_id,
        'city' => $addr->city,
        'block' => $addr->block,
        'street' => $addr->street,
        'house_number' => $addr->house_number,
        'floor' => $addr->floor,
        'address_detail' => $addr->address_detail,
    ];
})->keyBy('id');
?>
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<style>
    .custom-top-field {
        min-height: 64px;
    }
    .custom-top-label {
        min-width: 92px;
        white-space: nowrap;
    }
    .custom-top-field .form-control {
        flex: 1 1 auto;
        min-width: 0;
    }
    .custom-photo-field {
        flex-wrap: nowrap;
    }
    .custom-photo-control {
        min-width: 0;
    }
    .custom-photo-preview {
        flex: 0 0 48px;
    }
    .custom-photo-input {
        flex: 1 1 auto;
        min-width: 0;
    }
    
    /* Payment History Table Fixes */
    .table-bordered td {
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal !important;
    }
    
    .table-bordered td input[type="text"] {
        width: 100% !important;
        max-width: 100% !important;
        word-wrap: break-word;
        word-break: break-all;
        white-space: normal !important;
        overflow: visible !important;
        height: auto !important;
        min-height: 34px;
        padding: 6px 12px;
    }
    
    .modal-custom-order-summary-box {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #d9d9d9;
    }
    .modal-custom-order-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px 0;
        width: 100%;
        max-width: 320px;
        margin-left: auto;
    }
    .modal-custom-order-summary-item {
        display: flex;
        align-items: baseline;
        justify-content: flex-start;
        gap: 12px;
        min-width: 0;
    }
    .modal-custom-order-summary-item label {
        display: block;
        flex: 0 0 190px;
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #111;
        white-space: nowrap;
    }
    .modal-custom-order-summary-item span {
        display: block;
        flex: 1 1 auto;
        font-size: 16px;
        color: #222;
        white-space: nowrap;
        text-align: right;
    }
    .modal-custom-order-summary-item small {
        display: inline-block;
        margin-left: 4px;
        color: #777;
        font-size: 12px;
    }
    .responsive-field-label {
        display: none;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .custom-items-wrapper .modal-custom-item-row {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 14px;
        margin-bottom: 14px;
    }
    .custom-items-wrapper .modal-custom-item-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    @media (max-width: 575.98px) {
        .modal-custom-item-row > .col-md-5,
        .modal-custom-item-row > .col-md-3,
        .modal-custom-item-row > .col-md-1,
        .modal-custom-item-row > .col-md-2,
        .modal-custom-item-row > .col-md-12 {
            max-width: 100%;
            width: 100%;
            flex: 0 0 100%;
        }
        .modal-custom-item-row > .custom-item-fields-row {
            order: 4;
        }
        .modal-custom-item-row > .custom-item-actions-col {
            order: 5;
            margin-top: 8px;
        }
        .modal-custom-item-row > .custom-item-actions-col .icon-btn {
            justify-content: flex-end;
            min-height: 24px;
        }
    }
    @media (min-width: 768px) and (max-width: 1024px) {
        .modal-custom-order-summary-grid {
            max-width: 430px;
            margin-left: auto;
        }
        .modal-custom-order-summary-item label {
            flex-basis: 180px;
            white-space: nowrap;
            line-height: 1.25;
        }
        .modal-custom-item-row > .col-md-5,
        .modal-custom-item-row > .col-md-3,
        .modal-custom-item-row > .col-md-1,
        .modal-custom-item-row > .col-md-2,
        .modal-custom-item-row > .col-md-12 {
            max-width: 100%;
            width: 100%;
            flex: 0 0 100%;
        }
        .modal-custom-item-row > .custom-item-fields-row {
            order: 4;
        }
        .modal-custom-item-row > .custom-item-actions-col {
            order: 5;
            margin-top: 8px;
        }
        .modal-custom-item-row > .custom-item-actions-col .icon-btn {
            justify-content: flex-end;
        }
        .modal-custom-item-row .custom-item-image-field {
            align-items: center;
        }
        .responsive-field-label {
            display: block;
        }
        .modal-custom-item-row > .custom-item-fields-row .row > .col-md-3,
        .modal-custom-item-row > .custom-item-fields-row .row > .col-md-2,
        .modal-custom-item-row > .custom-item-fields-row .row > .col-md-1 {
            max-width: 50%;
            width: 50%;
            flex: 0 0 50%;
        }
        .modal-custom-item-row > .custom-item-fields-row .row > .col-md-1:last-child,
        .modal-custom-item-row > .custom-item-fields-row .row > .col-md-2:last-child {
            max-width: 50%;
            width: 50%;
            flex: 0 0 50%;
        }
    }
</style>
<?php

use App\Helpers\Helper;

$orderActionCount = count($orderProductDetail);
$conversationRate = 1;
if ($currency && $orderDetail->currency_code) {
    $currency->code = $orderDetail->currency_code;
    $conversationRate = $orderDetail->currency_rate;
} else {
    $orderDetail->currency_code = config('custom.currency_convert_default_country');
    $currency->code = config('custom.currency_convert_default_country');
}
$allPaymentMethods = Helper::getPaymentMethod();
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$relatedPendingPaymentLink = $relatedPendingPaymentLink ?? null;
$currencyCode = $currency ? $currency->code : config('custom.currency');
$calc = \App\Services\PaymentCalculationService::calculateOrderPayments($orderDetail);
$successfulPaidAmount = $calc['paid_amount'];
$advancePaymentAmount = ($successfulPaidAmount > 0) ? $successfulPaidAmount : (float)($orderDetail->paid_amount ?? 0);
$calculatedPendingAmount = max(0.0, round((float)($orderDetail->order_total ?? 0) - $advancePaymentAmount, 3));
$effectivePaidAmount = $advancePaymentAmount;
$hasPaidPayment = $successfulPaidAmount > 0;
$hasPendingPaymentLink = isset($paymentLink) && $paymentLink->where('status', 'pending')->count() > 0;
$hasRelatedPendingPaymentLink = isset($relatedPendingPaymentLink) && $relatedPendingPaymentLink;
$pendingPaymentLink = $hasPendingPaymentLink ? $paymentLink->where('status', 'pending')->sortByDesc('id')->first() : null;
$isFullyPaid = $calculatedPendingAmount <= 0;
$hasBlockingPendingPaymentLink = $hasRelatedPendingPaymentLink && !$isFullyPaid;
$shouldHidePaymentActions = $isFullyPaid;
$canGeneratePaymentLink = !$shouldHidePaymentActions && $calculatedPendingAmount > 0;
$isInitialAdvanceStage = $orderDetail->custom_order_type === "purchase_order"
    && $orderDetail->is_advance_payment_done == 0
    && $successfulPaidAmount <= 0;

$modalSubTotal = 0;
if (isset($existingCust['items']) && is_array($existingCust['items'])) {
    foreach ($existingCust['items'] as $item) {
        $modalSubTotal += (float) ($item['total'] ?? 0);
    }
}
$modalShipping = round((float) ($existingCust['shipping'] ?? ($orderDetail->delivery_price ?? 0)), 3);
$modalDiscountType = $existingCust['discount_type'] ?? '';
$modalDiscountVal = round((float) ($existingCust['discount_val'] ?? 0), 3);
$modalDiscountAmount = 0;
if ($modalDiscountType === 'percentage') {
    $modalDiscountAmount = ($modalSubTotal * $modalDiscountVal) / 100;
} elseif ($modalDiscountType === 'flat') {
    $modalDiscountAmount = $modalDiscountVal;
}
$modalDiscountAmount = min($modalDiscountAmount, $modalSubTotal);
$modalTotalOrderValue = max(0, $modalSubTotal + $modalShipping - $modalDiscountAmount);
$modalAdvancePaid = round($effectivePaidAmount, 3);
$modalPendingAmount = max(0, $modalTotalOrderValue - $modalAdvancePaid);
$modalDiscountLabel = '';
if ($modalDiscountType === 'percentage') {
    $modalDiscountLabel = __('web.discount_percentage');
} elseif ($modalDiscountType === 'flat') {
    $modalDiscountLabel = __('web.discount_flat');
}
?>
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.customized-order')}}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.order.index')}}">{{__('admin.order')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.view_order')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Main content -->
                    <div class="invoice p-3 mb-3">
                        <!-- title row -->
                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    <i class="fa fa-cart-plus"></i>
                                    @if($orderDetail->custom_order_type === "purchase_order")
                                    {{__('admin.po_no')}}
                                    @else
                                    {{__('admin.quotation_no')}}
                                    @endif
                                    {{$orderDetail->id}}
                                    <span class="badge {{ $customizedBadgeColor[$orderDetail->custom_order_type] ?? 'badge-secondary' }} ml-2" style="font-size: 0.6em; vertical-align: middle;">
                                        {{ ucfirst(str_replace('_', ' ', $orderDetail->custom_order_type)) }}
                                    </span> |
                                    <small>
                                        @if($orderDetail->custom_order_type === "purchase_order")
                                        {{__('admin.po-date')}} :
                                        @else
                                        {{__('admin.quotation-date')}} :
                                        @endif
                                        {{\Carbon\Carbon::parse($orderDetail->created_at)->format(config('custom.php_datetime_format'))}}</small>
                                    @if($orderDetail->is_add_to_po_order == 1) | <span class="badge badge-secondary ml-1" style="font-size: 0.6em; vertical-align: middle;">{{ __('admin.converted-to-order') }}</span>@endif
                                    @if($orderDetail->order_status != config('custom_web.Pending'))
                                    @if($orderDetail->order_status != config('custom_web.cancel'))
                                    <a class="cancel_order_btn hidden" onclick="showSweetAlertCancel('{{route('admin.order.cancel-order',['id'=>base64_encode($orderDetail->id)])}}')"><button type="button" class="{{config('custom.btn-danger-form','btn btn-danger')}} float-right" title="{{__('admin.cancel_order_title')}}">{{__('admin.cancel_order')}}</button></a>
                                    @else
                                    <a class="cancel_order_btn" onclick="showSweetAlertRecoverOrder('{{route('admin.order.recover-order',['id'=>base64_encode($orderDetail->id)])}}')">
                                    <button type="button" class="cancel_order_btn btn btn-outline-warning ml-2 float-right" title="{{__('admin.recover_order')}}">{{__('admin.recover_order_btn')}}</button>
                                    </a>
                                    <button type="button" class="{{config('custom.btn-danger-form','btn btn-danger')}} float-right" title="{{__('admin.cancelled_order')}}">{{__('admin.cancelled_order')}}</button>
                                    @endif
                                    @endif
                                    @if($orderDetail->order_status == config('custom_web.Pending'))
                                        @if($orderDetail->custom_order_type !== "quotation" && $orderDetail->is_advance_payment_done == 1)
                                        <button type="button" class="btn btn-primary btn-sm ml-2 float-right" id="openCustomOrderModalBtn" title="Edit Order">
                                        <i class="fas fa-edit"></i> Edit Purchase Order
                                        </button>
                                        @elseif($orderDetail->custom_order_type === "quotation" && $orderDetail->is_add_to_po_order == 0)
                                        <button type="button" class="btn btn-primary btn-sm ml-2 float-right" id="openCustomOrderModalBtn" title="Edit Order">
                                        <i class="fas fa-edit"></i> Edit Quotation
                                        </button>
                                        @endif
                                    @endif
                                </h4>
                            </div>
                            <!-- /.col -->
                        </div>
                        <?php
                        $user = $orderDetail->getUser();
                        $userAddress = $orderDetail->getUserAddress();
                        ?>

                        <!-- info row -->
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <b>{{__('admin.customer-info')}}</b>
                                <address>
                                    {{$user?ucfirst($user->first_name):''}} {{$user?ucfirst($user->last_name):''}}<br>
                                    {{__('admin.email')}} : {{$user->email}}<br>
                                    <strong>{{__('admin.phone')}} : </strong>{{$userAddress->phone ?? ''}}
                                </address>
                            </div>

                            <!-- /.col -->
                            <div class="{{ $orderDetail->custom_order_type === "purchase_order" ? 'col-sm-4' : 'col-sm-4' }} invoice-col">
                                <b>{{__('admin.billing-info')}}</b>
                                <?php
                                $customOrderDetails = json_decode($orderDetail->custom_order_details, true);
                                $customerInfo = isset($customOrderDetails['customer']) ? $customOrderDetails['customer'] : null;
                                ?>
                                @if($customerInfo)
                                <address>
                                    {{ isset($customerInfo['name']) ? ucfirst($customerInfo['name']) : '' }}<br>
                                    <?php
                                    $cust = $customerInfo;
                                    // Country name lookup
                                    $countryName = '';
                                    if (isset($cust['country_id']) && $cust['country_id']) {
                                        $cn = App\AdminModel\AdminCountryDescription::where('country_id', $cust['country_id'])->first();
                                        if ($cn) $countryName = $cn->country_name;
                                    }
                                    ?>
                                    @if(isset($cust['block']) && $cust['block'])<div><strong>Appartment :</strong> {{ e($cust['block']) }}</div>@endif
                                    @if(isset($cust['floor']) && $cust['floor'])<div><strong>Floor :</strong> {{ e($cust['floor']) }}</div>@endif
                                    @if(isset($cust['house_number']) && $cust['house_number'])<div><strong>House Number :</strong> {{ e($cust['house_number']) }}</div>@endif
                                    @if(isset($cust['city']) && $cust['city'])<div><strong>City :</strong> {{ e(ucfirst($cust['city'])) }}</div>@endif
                                    @if($countryName)<div><strong>Country :</strong> {{ e($countryName) }}</div>@endif
                                    @if(isset($cust['address_detail']) && $cust['address_detail'])<div><strong>Address Detail :</strong> {{ e($cust['address_detail']) }}</div>@endif
                                    @if(isset($cust['note']) && $cust['note'])<div><strong>Note :</strong> {!! nl2br(e($cust['note'])) !!} </div>@endif
                                    <strong>{{__('admin.email')}} : </strong>{{ $customerInfo['email'] ?? '' }}<br>
                                    <strong>{{__('admin.phone')}} : </strong>{{ $customerInfo['phone'] ?? '' }}<br>
                                </address>
                                @elseif(is_array($shippingAddress))
                                <address>
                                    {{$user ? ucfirst($user->first_name).' '.ucfirst($user->last_name) : ''}}<br>
                                    @if(!empty($shippingAddress['block']))<div><strong>Appartment :</strong> {{ e($shippingAddress['block']) }}</div>@endif
                                    @if(!empty($shippingAddress['floor']))<div><strong>Floor :</strong> {{ e($shippingAddress['floor']) }}</div>@endif
                                    @if(!empty($shippingAddress['house_number']))<div><strong>House Number :</strong> {{ e($shippingAddress['house_number']) }}</div>@endif
                                    @if(!empty($shippingAddress['street']))<div><strong>Street :</strong> {{ e($shippingAddress['street']) }}</div>@endif
                                    @if(!empty($shippingAddress['city']))<div><strong>City :</strong> {{ e(ucfirst($shippingAddress['city'])) }}</div>@endif
                                    @if(!empty($shippingAddress['country']))<div><strong>Country :</strong> {{ e($shippingAddress['country']) }}</div>@endif
                                    @if(!empty($shippingAddress['address_detail']))<div><strong>Address Detail :</strong> {{ e($shippingAddress['address_detail']) }}</div>@endif
                                    @if(!empty($user->email))<strong>{{__('admin.email')}} : </strong>{{ $user->email }}<br>@endif
                                    @if(!empty($userAddress->phone))<strong>{{__('admin.phone')}} : </strong>{{ $userAddress->phone }}<br>@endif
                                </address>
                                @elseif(is_object($shippingAddress))
                                <address>
                                    {{$user ? ucfirst($user->first_name).' '.ucfirst($user->last_name) : ''}}<br>
                                    @if(!empty($shippingAddress->block))<div><strong>Appartment :</strong> {{ e($shippingAddress->block) }}</div>@endif
                                    @if(!empty($shippingAddress->floor))<div><strong>Floor :</strong> {{ e($shippingAddress->floor) }}</div>@endif
                                    @if(!empty($shippingAddress->house_number))<div><strong>House Number :</strong> {{ e($shippingAddress->house_number) }}</div>@endif
                                    @if(!empty($shippingAddress->street))<div><strong>Street :</strong> {{ e($shippingAddress->street) }}</div>@endif
                                    @if(!empty($shippingAddress->city))<div><strong>City :</strong> {{ e(ucfirst($shippingAddress->city)) }}</div>@endif
                                    @if(!empty($shippingAddress->address_detail))<div><strong>Address Detail :</strong> {{ e($shippingAddress->address_detail) }}</div>@endif
                                    @if(!empty($user->email))<strong>{{__('admin.email')}} : </strong>{{ $user->email }}<br>@endif
                                    @if(!empty($shippingAddress->phone))<strong>{{__('admin.phone')}} : </strong>{{ $shippingAddress->phone }}<br>@endif
                                </address>
                                @endif
                            </div>

                            @if($orderDetail->custom_order_type === "purchase_order")
                            <div class="col-sm-4 invoice-col">
                                @if($addressQr)
                                <img class="text-center p-0" width="300" src="{{$addressQr}}">
                                @endif
                            </div>
                            @endif
                            <!-- /.col -->
                        </div>

                        <form method="POST" action="{{route('admin.order.update-order')}}" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-xs-12 table-responsive">
                                    <table class="table table-striped normal-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle" style="width: 100px;">{{ __('admin.image') }}</th>
                                                <th>{{__('admin.product-name')}}</th>
                                                <th>{{ __('web.item_code') }}</th>
                                                <th>{{ __('admin.color') }}</th>
                                                <th>{{ __('admin.size') }}</th>
                                                <th>{{__('admin.price')}}</th>
                                                <th class="text-center">{{__('admin.qty')}}</th>
                                                <th>{{__('admin.total')}}</th>
                                                @if($orderDetail->order_status != config('custom_web.Pending'))
                                                <th class="text-center">{{__('admin.action')}}
                                                    <div class="icheck-primary float-right custom-icheck hidden">
                                                        <input type="checkbox" id="chk_{{'all'}}" class="check_all">
                                                        <label title="{{__('admin.check_all')}}" class="custom-checkbox-lable" for="chk_{{'all'}}">
                                                        </label>
                                                    </div>
                                                </th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <?php
                                        $productPrice = [];
                                        ?>

                                        <tbody>
                                            @forelse($orderProductDetail as $recordIndex => $record)
                                            <?php
                                            $pricetotal = $record->quantity * $record->product_price;

                                            // Handle Custom Attributes
                                            $customAttributes = [];
                                            if (!empty($record->custom_attributes)) {
                                                $customAttributes = json_decode($record->custom_attributes, true);
                                                if (!is_array($customAttributes)) {
                                                    $customAttributes = []; // Fallback if invalid JSON
                                                }
                                            }
                                            // Fallback for product name if product_id is null
                                            $productName =
                                                $record->product_name
                                                ?: ($customAttributes['item_code'] ?? null)
                                                ?: ($customAttributes['description'] ?? null)
                                                ?: 'Custom Item'; // Assuming 'product_name' column exists, logic based on user request.

                                            if (empty($productName) && $record->getProduct()) {
                                                $productName = $record->getProduct()->products_name;
                                            }

                                            $itemImage = $customAttributes['image'] ?? ($existingCust['items'][$recordIndex]['image'] ?? null);
                                            $itemImage = $itemImage ? preg_replace('/^public\//', '', $itemImage) : null;
                                            ?>
                                            <tr>
                                                <td width="8%" class="text-center">
                                                    <span style="display: inline-flex; justify-content: center;">
                                                        @if($itemImage)
                                                            <img class="logo-table" src="{{ asset(Storage::url($itemImage)) }}" onclick="showDetails(this.src)" />
                                                        @else
                                                            <img class="logo-table" src="{{ asset(Storage::url('default.png')) }}" onclick="showDetails(this.src)" />
                                                        @endif
                                                    </span>
                                                </td>
                                                <td width="20%">
                                                    <b>{{ Helper::formatCustomizedTitle($productName ?: 'Custom Item') }}</b>
                                                    @if(!empty($record->note))
                                                        <br />
                                                        <span class="text-muted small">{{ Helper::formatCustomizedDescription($record->note) }}</span>
                                                    @elseif(!empty($customAttributes['description']))
                                                        <br />
                                                        <span class="text-muted small">{{ Helper::formatCustomizedDescription($customAttributes['description']) }}</span>
                                                    @endif
                                                </td>
                                                <td width="12%" class="barcode-cell">
                                                    {{ !empty($customAttributes['item_code']) ? $customAttributes['item_code'] : '-' }}
                                                </td>
                                                <td width="10%">
                                                    {{ !empty($customAttributes['color']) ? $customAttributes['color'] : '-' }}
                                                </td>
                                                <td width="10%">
                                                    {{ !empty($customAttributes['size']) ? $customAttributes['size'] : '-' }}
                                                </td>
                                                <td>{{Helper::formatNumber($record->product_price)}} <small>{{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                <td class="text-center">{{$record->quantity}}</td>
                                                <td>{{Helper::formatNumber($pricetotal)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>

                                                @if($orderDetail->order_status != config('custom_web.Pending'))
                                                <td class="text-center">
                                                        @if($record->is_active && !in_array($orderDetail->order_status,config('custom_web.cancel_return_status')) && $orderActionCount)
                                                        <div class="icheck-primary">
                                                            <input {{ (collect(old('cancel_return'))->contains($record->id)) ? 'checked':'' }} name="cancel_return[]" value="{{$record->id}}" type="checkbox" class="cancel_return_checkbox cancel_return chk_{{$record->id}}" id="chk_{{$record->id}}">
                                                            <label for="chk_{{$record->id}}">
                                                            </label>
                                                        </div>
                                                        @elseif($record->order_status)
                                                        <?php
                                                        $orderActionCount--;
                                                        $orderStatusPastForm = config('order.pastForm');
                                                        $orderStatusText = $orderStatusPastForm[$record->order_status];
                                                        ?>
                                                        <span class="badge {{$badgeColor[$record->order_status]}}">{{$orderStatusText}}</span>
                                                        @endif
                                                </td>
                                                @endif
                                            </tr>

                                            <?php
                                            if ($record->is_active) {
                                                $productPrice[] = $pricetotal;
                                            }
                                            ?>

                                            @empty
                                            <tr>
                                                <td colspan="{{ $orderDetail->order_status != config('custom_web.Pending') ? 9 : 8 }}" class="text-center">No items found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <?php


                                        $sumProductPrice = array_sum($productPrice);
                                        if ($orderDetail->gift_wrap) {
                                            $sumProductPrice += $orderDetail->gift_wrap;
                                        }


                                        $shippingTax = $sumProductPrice * $tax / 100;
                                        $deliveryPrice = $orderDetail->delivery_price;
                                        $discount = $orderDetail->discount;

                                        $total = $sumProductPrice + $shippingTax + $deliveryPrice - $discount;
                                        $paymentResponse = $orderDetail->payment_response;
                                        $paymentId = "";


                                        if ($paymentResponse) {
                                            $paymentResponse = json_decode($paymentResponse);


                                            $y = date('Y', strtotime($orderDetail->created_at));

                                            $postDate =  !empty($paymentResponse->post_date) ? $paymentResponse->post_date : '';
                                            $paymentId = isset($paymentResponse->invoice_id) ? $y . $postDate . $paymentResponse->invoice_id : '';
                                        }
                                        ?>
                                    </table>
                                </div>


                                <!-- /.col -->
                            </div>
                            <hr>
                            <div class="row mt-2">
                                <!-- accepted payments column -->
                                <div class="col-md-6">
                                    @if($orderDetail->custom_order_type === "purchase_order")
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{__('admin.payment-method')}}</label>
                                            <?php
                                            $paymentMethod = $orderDetail->getPaymentMethodDetail();
                                            ?>
                                            <input type="text" class="form-control" readonly="" value="{{ ($orderDetail->payment_method ?? null) == 6 ? 'Pay At Shop' : ($paymentMethod?$paymentMethod->payment_method_name:'') }}" disabled="" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 float-left">
                                        <div class="form-group">
                                            <label>{{__('admin.ref-number')}}</label>
                                            <input type="text" class="form-control" readonly="" value="{{$orderDetail->transaction_id}}" disabled="" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 float-left">
                                        <div class="form-group">
                                            <label>{{__('admin.payment-id')}}</label>
                                            <input type="text" class="form-control" readonly="" value="{{$paymentId}}" disabled="" placeholder="">
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <!-- /.col -->
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table subtotal-table">
                                            <tbody>

                                                @if($orderDetail->custom_order_type === "purchase_order")
                                                <tr>
                                                    <th class="th-td-boarder-none">{{__('admin.subtotal')}}</th>
                                                    <td class="th-td-boarder-none">{{Helper::formatNumber($sumProductPrice)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                @else
                                                <tr>
                                                    <th class="th-td-boarder-none">{{__('admin.subtotal')}}</th>
                                                    <td class="th-td-boarder-none">{{Helper::formatNumber($sumProductPrice)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>{{__('admin.shipping')}}</th>
                                                    <td>{{Helper::formatNumber($orderDetail->delivery_price)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small> </td>
                                                </tr>
                                                @if($discount && $discount > 0)
                                                <tr>
                                                    <th>{{__('web.discount_type')}}</th>
                                                    <td>{{ ucfirst($orderDetail->discount_type) }}</td>
                                                </tr>
                                                @if($orderDetail->discount_type == 'percentage')
                                                <tr>
                                                    <th>{{__('web.discount_value')}}</th>
                                                    <td>{{ $orderDetail->discount_per }}%</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>{{__('admin.discount')}}</th>
                                                    <td>{{Helper::formatNumber($discount)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small> </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th>{{__('admin.total')}}</th>
                                                    <td>{{Helper::formatNumber($total)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                @if($orderDetail->custom_order_type === "purchase_order")
                                                <tr>
                                                    <th class="">{{__('admin.advance_payment')}}</th>
                                                    <td class="">{{Helper::formatNumber($advancePaymentAmount)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                <tr>
                                                    <th>{{__('admin.pending_amount')}}</th>
                                                    <td class="text-danger font-weight-bold">{{Helper::formatNumber($calculatedPendingAmount)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                @endif
                                                @if($currency && $currency->code!=config('custom.currency_convert_default_country'))
                                                <tr>
                                                    <th>{{__('admin.total')}} in {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</th>
                                                    <td>{{Helper::formatNumber($total)}}<small> {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}</small></td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- /.col -->
                            </div>
                            <div class="row">
                                @if($orderDetail->custom_order_type === "purchase_order" && !in_array($orderDetail->order_status,config('custom_web.cancel_return_status')))
                                <div class="col-md-4">
                                    @if($orderDetail->order_status == config('custom_web.Pending'))
                                    <div class="col-12 p-0">
                                        <div class="form-group">
                                            <label>{{__('admin.po-status')}}</label>
                                            <select disabled="" class="select2 select-status" id="status_id" name="orders_status" style="width: 100%;
                                                        " tabindex="-1" aria-hidden="true">
                                                @foreach($allOrderStatus as $record)
                                                <option value="{{$record->id}}" {{$orderDetail->order_status == $record->id ? 'selected':''}}> {{$record->order_status}}</option>
                                                @endforeach
                                            </select>
                                            @error('orders_status')
                                            <div class="text-red">{{ $message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @else
                                    @if(!in_array($orderDetail->order_status,config('custom_web.cancel_return_status')) && $orderActionCount)
                                    <input class="form-control" id="orders_id" name="orders_id" type="hidden" value="{{$orderDetail->id}}">
                                    <!--<p class="lead">{{__('admin.change-status')}}</p>-->
                                    <div class="row">
                                        <div class="col-md-4 col-sm-12 order-status-div">
                                            <div class="form-group">
                                                <label>{{__('admin.po-status')}}</label>
                                                <select class="select2 select-status" id="status_id" name="orders_status" style="width: 100%;
                                                            " tabindex="-1" aria-hidden="true">
                                                    @foreach($orderStatus as $record)
                                                    <option data-show="{{$record->show_on_cancel_return}}" value="{{$record->id}}" {{$orderDetail->order_status == $record->id ? 'selected':''}}> {{$record->order_status}}</option>
                                                    @endforeach
                                                </select>
                                                @error('orders_status')
                                                <div class="text-red">{{ $message }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12 hide-cancel-return">
                                            <div class="form-group">
                                                <label>{{__('admin.courier-company')}}</label>
                                                <select class="select2 select-courier" name="courier_id" style="width: 100%;
                                                            " tabindex="-1" aria-hidden="true">
                                                    <option selected="" disabled="">{{__('admin.select-courier')}}</option>
                                                    @foreach($courier as $record)
                                                    <option value="{{$record->id}}" {{$record->id == $orderDetail->courier_id ? 'selected':''}}> {{ucfirst($record->company_name)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12 hide-cancel-return">
                                            <div class="form-group">
                                                <label>{{__('admin.tracking_number')}}</label>
                                                <input type="text" class="form-control" name='tracking_number' value="{{$orderDetail->tracking_number?$orderDetail->tracking_number:""}}" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="{{config('custom.btn-success-form')}} float-right" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                                    @endif
                                    @endif
                                </div>
                                @endif
                                <!-- this row will not appear when printing -->
                                <div class="{{ $orderDetail->custom_order_type != 'purchase_order' ? 'col-md-6' : 'col-md-4' }}">
                                    <form method="POST" action="{{route('admin.order.save-note')}}" class="form-horizontal">
                                        <div class="row">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{$orderDetail->id}}">
                                            <div class="col-md-12">
                                                <p class="mb-2"><strong>
                                                        @if($orderDetail->custom_order_type === "purchase_order")
                                                        PO Note
                                                        @else
                                                        Quotation Note
                                                        @endif
                                                    </strong></p>
                                                <div class="form-group">
                                                    <textarea class="textarea form-control " required="" placeholder="{{ $orderDetail->custom_order_type === "purchase_order" ? "Order Note" : "Quotation Note" }}" name="order_note" rows="2" cols="300">{{$orderDetail->order_note?$orderDetail->order_note:""}}</textarea>
                                                </div>
                                                <button type="submit" class="{{config('custom.btn-success-form')}} float-right" title="Update">Update Note</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>  
                                <div class="{{ $orderDetail->custom_order_type != 'purchase_order' ? 'col-md-6' : 'col-md-4' }}">
                                    <form method="POST" action="{{route('admin.order.update-address')}}" class="form-horizontal">
                                        <div class="row">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{$orderDetail->id}}">
                                            <div class="col-md-12">
                                                <p class="mb-2"><strong>Address URL</strong></p>
                                                <div class="form-group">
                                                    <input type="url" class="form-control" name="address_url" placeholder="" value="{{$orderDetail->address_url}}">
                                                </div>
                                                <button type="submit" class="{{config('custom.btn-success-form')}} float-right" title="Update">Update Address</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </form>

                        @if($orderDetail->order_status != config('custom_web.cancel'))
                        @if($orderDetail->custom_order_type === "purchase_order" && $calculatedPendingAmount > 0 && $orderDetail->is_advance_payment_done == 1)
                        <hr>
                        <form method="POST" action="{{ route('admin.order.customized-order.update-discount', ['id' => $orderDetail->id]) }}" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>{{__('admin.discount_type')}}</label>
                                        <select name="discount_type" class="form-control">
                                            <option value="">Select</option>
                                            <option value="flat" {{ $orderDetail->discount_type === 'flat' ? 'selected' : '' }}>Flat</option>
                                            <option value="percentage" {{ $orderDetail->discount_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                        </select>
                                        @error('discount_type') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>{{__('admin.discount_value')}}</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="discount_value"
                                            class="form-control"
                                            value="{{ $orderDetail->discount_type === 'percentage' ? $orderDetail->discount_value ?? '' : $orderDetail->discount ?? '' }}"
                                            placeholder="Enter discount">
                                        @error('discount_value') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="{{config('custom.btn-success-form')}} btn-block" title="Update Discount">Update Discount</button>
                                </div>
                            </div>
                        </form>
                        @endif
                        @endif

                        @if($orderDetail->order_status != config('custom_web.cancel'))
                        @if($orderDetail->custom_order_type === "purchase_order" && $calculatedPendingAmount > 0 && $orderDetail->is_advance_payment_done == 1)
                        <hr>
                        <form method="POST" action="{{ route('admin.order.customized-order.update-shipping', ['id' => $orderDetail->id]) }}" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>{{__('admin.shipping')}}</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="delivery_price"
                                            class="form-control"
                                            value="{{ $orderDetail->delivery_price ?? '' }}"
                                            placeholder="Enter shipping charge">
                                        @error('delivery_price') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="{{config('custom.btn-success-form')}} btn-block" title="Update Shipping">Update Shipping</button>
                                </div>
                            </div>
                        </form>
                        @endif
                        @endif

                        @if($orderDetail->order_status != config('custom_web.cancel'))
                        @if($orderDetail->custom_order_type === "purchase_order" && !$shouldHidePaymentActions)
                        <hr>
                        @if($hasBlockingPendingPaymentLink)
                        <div class="alert alert-warning">
                            A pending payment link already exists
                            @if(isset($relatedPendingPaymentLink) && (int) $relatedPendingPaymentLink->order_id !== (int) $orderDetail->id)
                                for the parent order.
                            @else
                                for this order.
                            @endif
                            Please complete or cancel it before generating a new payment link.
                        </div>
                        @endif
                        <form method="POST"
                            action="{{route('admin.order.customized-order.record-manual-payment',['id'=>$orderDetail->id])}}"
                            class="form-horizontal mb-3">
                            @csrf

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{__('admin.manual-payment-amount')}}</label>
                                        <input type="number"
                                            step="0.001"
                                            min="0.001"
                                            name="manual_payment_amount"
                                            class="form-control"
                                            value="{{ old('manual_payment_amount') }}"
                                            placeholder="{{__('admin.enter-manual-payment-amount')}}">
                                        @error('manual_payment_amount') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{__('admin.payment-method')}}</label>
                                        <select name="manual_payment_type" class="form-control">
                                            <option value="">Select</option>
                                            @foreach($allPaymentMethods as $method)
                                                @if((int) $method->id === 4)
                                                    @continue
                                                @endif
                                                <option value="{{$method->id}}" {{ old('manual_payment_type') == $method->id ? 'selected' : '' }}>
                                                    {{ (int) $method->id === 6 ? 'Pay At Shop' : ($method->getDescription($language->id)?$method->getDescription($language->id)->payment_method_name:'') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('manual_payment_type') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{__('admin.manual-paid-at')}}</label>
                                        <input type="datetime-local"
                                            name="manual_paid_at"
                                            class="form-control"
                                            value="{{ old('manual_paid_at') }}">
                                        @error('manual_paid_at') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                @if($isInitialAdvanceStage && !$shouldHidePaymentActions)
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" id="is_advance_payment" name="is_advance_payment" value="1" {{ old('is_advance_payment') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_advance_payment">{{__('admin.is-advance-payment')}}</label>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{__('admin.manual-reference-no')}}</label>
                                        <input type="text"
                                            name="manual_reference_no"
                                            class="form-control"
                                            value="{{ old('manual_reference_no') }}"
                                            placeholder="{{__('admin.enter-manual-reference-no')}}">
                                        @error('manual_reference_no') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="{{ (!$isInitialAdvanceStage || $shouldHidePaymentActions) ? 'col-md-9' : 'col-md-6' }}">
                                    <div class="form-group">
                                        <label>{{__('admin.note')}}</label>
                                        <input type="text"
                                            name="manual_note"
                                            class="form-control"
                                            value="{{ old('manual_note') }}"
                                            placeholder="{{__('admin.enter-note')}}">
                                        @error('manual_note') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="submit"
                                            class="{{ config('custom.btn-success-form') }} btn-block"
                                            title="{{__('admin.record-manual-payment')}}">
                                        {{__('admin.record-manual-payment')}}
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(!$shouldHidePaymentActions && $calculatedPendingAmount > 0)
                        <form method="POST"
                            action="{{route('admin.order.customized-order.generate-payment-url',['id'=>$orderDetail->id])}}"
                            class="form-horizontal">
                            @csrf

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>{{ $isInitialAdvanceStage ? __('admin.advance_payment') : __('admin.payment-amount') }}</label>
                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            max="{{ $calculatedPendingAmount }}"
                                            name="advance_payment"
                                            class="form-control"
                                            value="{{ old('advance_payment') }}"
                                            placeholder="{{ Helper::formatNumber($calculatedPendingAmount) }}">
                                        <small class="text-muted">Leave empty for full pending ({{ Helper::formatNumber($calculatedPendingAmount) }})</small>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center" style="margin-top: 1.2rem;">
                                    <button type="submit"
                                            class="{{ config('custom.btn-success-form') }} btn-block"
                                            title="{{__('admin.generate-payment-link')}}">
                                        {{__('admin.generate-payment-link')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                        @endif
                        @endif
                        @endif

                        @if($orderDetail->order_status != config('custom_web.cancel') && $orderDetail->custom_order_type !== "quotation")
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-3"><strong>{{__('admin.payment-history')}}</strong></p>
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{__('admin.date')}}</th>
                                            <th>{{__('admin.manual-paid-at')}}</th>
                                            <th>{{__('admin.payment-type')}}</th>
                                            <th>{{__('admin.payment-amount')}}</th>
                                            <th>{{__('admin.payment-link')}}</th>
                                            <th>{{__('admin.emails-sent')}}</th>
                                            <th>{{__('admin.last-email-at')}}</th>
                                            <th>{{__('admin.status')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (($paymentHistoryLinks ?? $paymentLink) as $item)
                                        <tr>
                                            @php
                                            $gatewayResponse = is_array($item->gateway_response) ? $item->gateway_response : (json_decode($item->gateway_response, true) ?: []);
                                            $isManualEntry = isset($gatewayResponse['source']) && $gatewayResponse['source'] === 'manual_admin';
                                            $isFrontendPayAtStore = isset($gatewayResponse['source']) && $gatewayResponse['source'] === 'frontend_pay_at_store';
                                            $isSplitFromParent = isset($gatewayResponse['source']) && $gatewayResponse['source'] === 'split_from_parent_payment';
                                            $manualReference = $gatewayResponse['reference_no'] ?? '';
                                            $resolvedPaymentType = Helper::getPaymentMethodByID($item->payment_type);
                                            $resolvedPaymentType = !empty($resolvedPaymentType) ? $resolvedPaymentType : ($gatewayResponse['payment_method_label'] ?? '-');
                                            @endphp
                                            <td>
                                                {{\Carbon\Carbon::parse($item->created_at)->format(config('custom.php_datetime_format'))}}
                                            </td>
                                            <td>
                                                {{ $item->paid_at ? \Carbon\Carbon::parse($item->paid_at)->format(config('custom.php_datetime_format')) : '-' }}
                                            </td>
                                            <td>
                                                {{ $resolvedPaymentType }}
                                                @if($isSplitFromParent)
                                                    <div class="small text-muted mt-1"><span class="badge badge-info">Split from Checkout</span></div>
                                                @endif
                                            </td>
                                            <td>
                                                {{Helper::formatNumber($item->amount)}} {{ $record->currency_code 
    ?? ($currency ? $currency->code : config('custom.currency')) }}
                                            </td>
                                            <td>
                                                @php
                                                $phone = $customerInfo['phone'] ?? null;
                                                if ($phone) {
                                                     $phone = preg_replace('/\D+/', '', $phone);
                                                }
                                                $hasEmail = !empty($customerInfo['email']);
                                                @endphp
                                                @if($isManualEntry)
                                                    <span class="badge badge-info">{{__('admin.manual-payment')}}</span>
                                                    @if(!empty($manualReference))
                                                        <div class="small text-muted mt-1">{{__('admin.reference')}}: {{ $manualReference }}</div>
                                                    @endif
                                                @elseif($isFrontendPayAtStore)
                                                    <span class="badge badge-secondary">Pay At Shop</span>
                                                    <div class="small text-muted mt-1">Created from frontend checkout</div>
                                                @elseif($isSplitFromParent)
                                                    <span class="badge badge-success">Combined Payment</span>
                                                    <div class="small text-muted mt-1">Paid during combined checkout</div>
                                                @else
                                                    <input type="text"
                                                        class="form-control form-control-sm d-inline-block border-0 bg-transparent payment-link-input"
                                                        id="payment-link-{{ $item->id }}"
                                                        value="{{ rtrim(config('custom.web_url', config('app.url')), '/') }}/payment-review/{{ $item->payment_request_id }}"
                                                        readonly>

                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary ml-1 border-0"
                                                        onclick="copyPaymentLink('{{ $item->id }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>

                                                    <a href="https://web.whatsapp.com/send?{{ $phone ? 'phone='.$phone.'&' : '' }}text={{ urlencode('Please complete your payment using this link: ' . rtrim(config('custom.web_url', config('app.url')), '/') . '/payment-review/' . $item->payment_request_id) }}"
                                                        target="_blank"
                                                        title="Send via WhatsApp"
                                                        class="ml-2 text-success">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>

                                                    @if($hasEmail)
                                                        <form method="POST"
                                                            action="{{ route('admin.order.customized-order.send-payment-email', ['paymentLinkId' => $item->id]) }}"
                                                            class="d-inline-block ml-1">
                                                            @csrf
                                                            <input type="hidden" name="email" value="{{ $customerInfo['email'] }}">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0" title="{{__('admin.send-email')}}">
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{ $item->email_sent_count ?? 0 }}</td>
                                            <td>
                                                {{ $item->last_email_sent_at ? \Carbon\Carbon::parse($item->last_email_sent_at)->format(config('custom.php_datetime_format')) : '-' }}
                                            </td>
                                            <td>
                                                @if($isFrontendPayAtStore)
                                                    Success
                                                @else
                                                    {{ ucfirst($item->status) }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($orderDetail->order_status != config('custom_web.Pending'))
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-3"><strong>{{__('admin.po-status-history')}}</strong></p>
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{__('admin.date')}}</th>
                                            <th>{{__('admin.time')}}</th>
                                            <th>{{__('admin.status')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orderStatusHistory as $record)
                                        <?php
                                        $orderStatus = $record->getOrderStatus();
                                        $orderStatus = $orderStatus ? $orderStatus->order_status : '';
                                        ?>
                                        <tr>
                                            <td>
                                                {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_date_format'))}}
                                            </td>
                                            <td>
                                                {{\Carbon\Carbon::parse($record->created_at)->format(config('custom.php_time_format'))}}
                                            </td>
                                            <td>
                                                <span class="badge {{$badgeColor[$record->order_status_id]}}">{{$orderStatus}}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.row -->
                        <!-- this row will not appear when printing -->
                        <div class="row no-print">
                            <div class="col-12">
                                <a target="_blank" href="{{route('admin.order.print-order',['id'=>$orderDetail->id])}}" class="btn btn-default print"><i class="fas fa-print"></i> {{__('admin.print')}}</a>
                                <a target="_blank" href="{{route('admin.order.delivery-print-order',['id'=>$orderDetail->id])}}" class="btn btn-default print"><i class="fas fa-print"></i> Delivery Print</a>

                            </div>

                        </div>
                        @else

                        @if($orderDetail->order_type === "regular")
                        <div class="row">
                            <div class="col-12">
                                @if($hasBlockingPendingPaymentLink)
                                    <button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}} float-right" title="Payment link is still pending" disabled><i class="fas fa-check"></i> {{__('admin.process-order')}}</button>
                                @else
                                    <a href="{{route('admin.order.process-order',['id'=>$orderDetail->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}} float-right" title="{{__('admin.process-order')}}"><i class="fas fa-check"></i> {{__('admin.process-order')}}</button></a>
                                @endif
                            </div>
                        </div>
                        @elseif($orderDetail->custom_order_type === "purchase_order")
                        <div class="row">
                            <div class="col-12">
                                @if($hasBlockingPendingPaymentLink)
                                    <button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}} float-right" title="Payment link is still pending" disabled><i class="fas fa-check"></i> {{__('admin.process-order')}}</button>
                                @else
                                    <a href="{{route('admin.order.process-order',['id'=>$orderDetail->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}} float-right" title="{{__('admin.process-order')}}"><i class="fas fa-check"></i> {{__('admin.process-order')}}</button></a>
                                @endif
                            </div>
                        </div>
                        @else
                        @if($orderDetail->custom_order_type === "quotation" && in_array($orderDetail->order_status, [config('custom_web.Pending'), config('custom_web.approved')]) && $orderDetail->is_add_to_po_order == 0)
                        <div class="row">
                            <div class="col-12">
                                <a href="{{route('admin.order.convert-to-order',['id'=>$orderDetail->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}} float-right" title="{{__('admin.convert-to-order')}}"> {{__('admin.convert-to-order')}}</button></a>
                            </div>
                        </div>
                        @endif
                        @endif

                        @endif
                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Customized Order Modal -->
<div id="customizedOrderModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h4 class="address_title mb-2">{{__('web.add_customized_order')}}</h4>
                <form id="customized_order_payload_form" method="POST" action="{{ route('admin.order.customized-order.update-full-order') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $orderDetail->id }}">

                    <!-- Customer Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;" class="mt-2">{{__('web.customer_details')}}</h5>
                    <div class="row">
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_phone" value="{{ isset($existingCust['customer']['phone']) ? $existingCust['customer']['phone'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_phone_number')}}*" data-req-msg="{{__('web.error-req-phone-number')}}" {{ (isset($existingCust['customer']['phone']) && $existingCust['customer']['phone']) ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_email" value="{{ isset($existingCust['customer']['email']) ? $existingCust['customer']['email'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_email')}}" data-req-msg="{{__('web.error-req-email')}}" {{ (isset($existingCust['customer']['email']) && $existingCust['customer']['email']) ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-12 form-item mb-2" id="address_dropdown_wrapper" style="">
                            <select class="form-control form-control-sm" id="customer_address_select" name="customer_address_id">
                                <option value="">{{ __('web.select_address') }}</option>
                                @foreach ($addresses as $address)
                                    <option value="{{ $address->id }}">{{ $address->address_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_name" value="{{ isset($existingCust['customer']['name']) ? $existingCust['customer']['name'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_name')}}" data-req-msg="{{__('web.error-req-name')}}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <select name="cust_country_id" class="form-control form-control-sm" data-req-msg="{{__('web.error-req-country')}}">
                                <option value="">{{__('web.select_country')}}</option>
                                <?php 
                                $language = Helper::getLanguage();
                                ?>
                                @foreach($country as $c)
                                <?php $cDesc = $c->getCountryDescription($language->id);
                                $sel = (isset($existingCust['customer']['country_id']) && $existingCust['customer']['country_id'] == $c->id) ? 'selected' : ''; ?>
                                <option value="{{$c->id}}" {{$sel}}>{{ $cDesc ? $cDesc->country_name : $c->currency_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <div class="cust_city_list">
                                <select name="cust_city_id" class="form-control cust_city_id select-component untouched">
                                    <option value="">{{__('web.select_area')}}</option>
                                </select>
                            </div>
                            <div class="cust_city_text" style="display:none; margin-top:5px;">
                                <input type="text" name="cust_city" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_city')}}" data-req-msg="{{__('web.error-req-city')}}">
                            </div>
                            <input type="hidden" id="existing_custom_city_id" value="{{ isset($existingCust['customer']['city_id']) ? $existingCust['customer']['city_id'] : '' }}">
                            <input type="hidden" id="existing_custom_city_name" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_block" value="{{ isset($existingCust['customer']['block']) ? $existingCust['customer']['block'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.block')}}" data-req-msg="{{__('web.error-req-block')}}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_street" value="{{ isset($existingCust['customer']['street']) ? $existingCust['customer']['street'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.street')}}" data-req-msg="{{__('web.error-req-street')}}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_house_number" value="{{ isset($existingCust['customer']['house_number']) ? $existingCust['customer']['house_number'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.house_number')}}" data-req-msg="{{__('web.error-req-house')}}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_floor" value="{{ isset($existingCust['customer']['floor']) ? $existingCust['customer']['floor'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.floor')}}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <input type="text" name="cust_address_detail" value="{{ isset($existingCust['customer']['address_detail']) ? $existingCust['customer']['address_detail'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.address_detail')}}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <input type="text" name="cust_address_url" value="{{ isset($existingCust['customer']['address_url']) ? $existingCust['customer']['address_url'] : '' }}" class="form-control form-control-sm" placeholder="{{__('web.address_url')}}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <textarea name="cust_note" class="form-control form-control-sm" placeholder="{{ __('web.note') }}" rows="3" style="resize: vertical;">{{ isset($existingCust['customer']['note']) ? $existingCust['customer']['note'] : '' }}</textarea>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;" class="mt-2">{{__('web.order_details')}}</h5>
                    <div class="custom-items-wrapper">
                        <div id="modal_custom_items_list">
                            @if(isset($existingCust['items']) && count($existingCust['items']) > 0)
                                @foreach($existingCust['items'] as $index => $item)
                                <div class="row modal-custom-item-row">
                                    <div class="col-md-5 form-item pr-0 mb-2">
                                <div class="d-flex align-items-start custom-item-image-field">
                                    <input type="hidden" name="custom_item_existing_image[]" value="{{ $item['image'] ?? '' }}">
                                    <div class="custom-item-image-preview text-left mr-2" style="flex:0 0 48px;">
                                        <img
                                            src="{{ !empty($item['image']) ? asset(Storage::url($item['image'])) : $defaultReferenceImageUrl }}"
                                            alt="{{ __('web.photo_reference') }}"
                                            style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px; margin-top: 1rem;"
                                            @if(!empty($item['image']))
                                                onerror="this.onerror=null;this.src='{{ $defaultReferenceImageUrl }}'"
                                            @endif
                                        >
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="d-block mb-1">{{ __('web.photo_reference') }}</label>
                                        <input type="file" name="custom_item_image[]" class="form-control form-control-sm custom-item-image-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    </div>
                                </div>
                                    </div>
                                    <div class="col-md-3 form-item pr-0 mb-2">
                                        @if($index === 0)
                                            <label class="d-block mb-1">{{ __('web.product_title') }}</label>
                                        @else
                                            <label class="d-block mb-1 responsive-field-label">{{ __('web.product_title') }}</label>
                                        @endif
                                        <input type="text" name="custom_product_title[]" value="{{ $item['product_title'] ?? '' }}"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('web.enter_product_title') }}"
                                            data-req-msg="{{ __('web.error-req-product-title') }}">
                                    </div>
                                    <div class="col-md-3 form-item pr-0 mb-2">
                                        @if($index === 0)
                                            <label class="d-block mb-1">{{ __('web.description') }}</label>
                                        @else
                                            <label class="d-block mb-1 responsive-field-label">{{ __('web.description') }}</label>
                                        @endif
                                        <textarea name="custom_desc[]" class="form-control form-control-sm" placeholder="{{__('web.enter_description')}}" data-req-msg="{{__('web.error-req-description')}}" rows="2" style="resize: none;">{{ $item['description'] }}</textarea>
                                    </div>
                                    <div class="col-md-1 form-item pr-0 mb-2 custom-item-actions-col">
                                        @if($index === 0) <label class="d-block mb-1">&nbsp;</label> @endif
                                        <div class="icon-btn d-flex align-items-center" style="gap: 2rem; min-height: 38px; padding: 0.5rem;">
                                            @if($loop->last)
                                            <span class="add-modal-custom-item" style="cursor:pointer; font-size: 16px; display:block;" title="{{__('web.add_item')}}"><i class="fa fa-plus"></i></span>
                                            @endif
                                            @if($index > 0)
                                            <span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{__('web.remove_item')}}"><i class="fa fa-trash"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12 custom-item-fields-row mt-2">
                                        <div class="row">
                                            <div class="col-md-3 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.item_code') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.item_code') }}</label>
                                                @endif
                                                <input type="text" name="custom_item_code[]" value="{{ $item['item_code'] ?? '' }}"
                                                    class="form-control form-control-sm"
                                                    placeholder="{{ __('web.enter_item_code') }}"
                                                    data-req-msg="{{ __('web.error-req-item-code') }}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.color') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.color') }}</label>
                                                @endif
                                                <input type="text" name="custom_color[]" value="{{ $item['color'] ?? '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_color')}}" data-req-msg="{{__('web.error-req-color')}}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.size') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.size') }}</label>
                                                @endif
                                                <input type="text" name="custom_size[]" value="{{ $item['size'] ?? '' }}" class="form-control form-control-sm" placeholder="{{__('web.enter_size')}}" data-req-msg="{{__('web.error-req-size')}}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.price') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.price') }}</label>
                                                @endif
                                                <input type="number" name="custom_price[]" value="{{ $item['price'] ?? '' }}" class="form-control form-control-sm custom-price" placeholder="{{__('web.enter_price')}}" step="0.001" data-req-msg="{{__('web.error-req-price')}}">
                                            </div>
                                            <div class="col-md-1 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.quantity') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.quantity') }}</label>
                                                @endif
                                                <input type="number" name="custom_qty[]" value="{{ $item['quantity'] ?? '' }}" class="form-control form-control-sm custom-qty" placeholder="{{__('web.enter_quantity')}}" min="1" data-req-msg="{{__('web.error-req-quantity')}}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.total_price') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.total_price') }}</label>
                                                @endif
                                                <input type="text" name="custom_total[]" value="{{ $item['total'] ?? '0.000' }}" class="form-control form-control-sm custom-total" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <!-- <div style="margin-top: 10px;">
                             <button type="button" id="modal_add_item_btn" class="link-button"><i class="icon-plus"></i> {{__('web.add_item')}}</button>
                         </div> -->
                        <div id="shipping_section">
                            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{__('web.shipping_details')}}</h5>
                            <div class="row">
                                <div class="col-md-12 form-item"> <label>{{__('web.shipping_charges')}}</label> <input type="number" name="custom_shipping" class="form-control form-control-sm" placeholder="0.000" step="0.001" value="{{ isset($existingCust['shipping']) ? $existingCust['shipping'] : '' }}"> </div>
                            </div>
                        </div> <!-- Discount Details -->

                        <div id="discount_section">
                            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{__('web.discount_details')}}</h5>
                            <div class="row justify-content-end">
                                <div class="col-md-6 form-item"> <label>{{__('web.discount_type')}}</label> <select name="custom_discount_type" class="form-control form-control-sm">
                                        <option value="">{{__('web.select')}}</option>
                                        <option value="flat" {{ (isset($existingCust['discount_type']) && $existingCust['discount_type'] == 'flat') ? 'selected' : '' }}>Flat</option>
                                        <option value="percentage" {{ (isset($existingCust['discount_type']) && $existingCust['discount_type'] == 'percentage') ? 'selected' : '' }}>Percentage</option>
                                    </select> </div>
                                <div class="col-md-6 form-item"> <label>{{__('web.discount_value')}}</label> <input type="number" name="custom_discount_val" class="form-control form-control-sm" placeholder="0.000" step="0.001" value="{{ isset($existingCust['discount_val']) ? $existingCust['discount_val'] : '' }}"> </div>
                            </div>
                        </div>

                        <div class="modal-custom-order-summary-box">
                            <div class="modal-custom-order-summary-grid">
                                <div class="modal-custom-order-summary-item">
                                    <label>{{ __('web.sub_total') }}</label>
                                    <span id="modal_sub_total">{{ Helper::formatNumber($modalSubTotal) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item">
                                    <label>{{ __('web.shipping_charges') }}</label>
                                    <span id="modal_shipping_charge">{{ Helper::formatNumber($modalShipping) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item">
                                    <label id="modal_discount_label">
                                        {{ __('web.discount') }}
                                        @if($modalDiscountLabel)
                                            <small>({{ $modalDiscountLabel }})</small>
                                        @endif
                                    </label>
                                    <span id="modal_discount_amount">- {{ Helper::formatNumber($modalDiscountAmount) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item">
                                    <label>{{ __('web.total_order_value') }}</label>
                                    <span id="modal_grand_total">{{ Helper::formatNumber($modalTotalOrderValue) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item">
                                    <label>{{ __('web.advance_paid') }}</label>
                                    <span id="modal_advance_paid">- {{ Helper::formatNumber($modalAdvancePaid) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item">
                                    <label>{{ __('web.pending_amount') }}</label>
                                    <span id="modal_pending_amount">{{ Helper::formatNumber($modalPendingAmount) }} {{ $currencyCode }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submit_customized_order">{{__('web.submit')}}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('web.close')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_specific_js')
@include('common/sorting')
@include('layout/sweetalert')
@include('layout/datatable')
@include('layout/image_preview')
@include('layout/select2')
<script>
    function copyPaymentLink(id) {
        const input = document.getElementById('payment-link-' + id);

        input.removeAttribute('readonly'); // needed for iOS
        input.select();
        input.setSelectionRange(0, 99999); // mobile support
        input.setAttribute('readonly', true);

        try {
            const success = document.execCommand('copy');
            if (success) {
                toastr.success('Payment link copied to clipboard');
            } else {
                toastr.info('Press Ctrl+C (or long press) to copy');
            }
        } catch (e) {
            toastr.info('Press Ctrl+C (or long press) to copy');
        }
    }

    window.customizedOrderCurrencyCode = @json($currencyCode);
    window.customizedOrderAdvanceAmount = @json($modalAdvancePaid);

    var currentOrderStatus = $("select[name=orders_status]").val();
    $(function() {
        $(".check_all").change(function() {
            if ($(this).is(':checked')) {
                $(".cancel_return_checkbox").prop('checked', true);
            } else {
                $(".cancel_return_checkbox").prop('checked', false);
            }
            orderStatus();
        });
        $(".cancel_return_checkbox").change(function() {
            if ($('.cancel_return_checkbox:checked').length == $('.cancel_return_checkbox').length) {
                $(".check_all").prop('checked', true);
            } else {
                $(".check_all").prop('checked', false);
            }
        });
        <?php
        if (!in_array($orderDetail->order_status, config('custom_web.cancel_return_status')) && $orderActionCount) {
        ?>
            $(".cancel_order_btn").show();
            $(".custom-icheck").show();
        <?php
        }
        ?>
        orderStatus();
        $(".select-courier").select2({
            placeholder: "{{__('admin.select-courier')}}",
            allowClear: true
        });
        $("select[name=orders_status]").select2({
            placeholder: "{{__('admin.select-po-status')}}"
        });
        $(".cancel_return").change(function() {
            orderStatus();
        });
    });

    function orderStatus() {
        if ($('.cancel_return:checkbox:checked').length) {
            $(".hide-cancel-return").hide();
            $("select[name=orders_status]>option[data-show=0]").attr("disabled", "disabled");
            $("select[name=orders_status]>option[data-show=1]").removeAttr("disabled");
            //    $(".order-status-div").removeClass("col-md-3");
            //    $(".order-status-div").addClass("col-md-12");
            $("select[name=orders_status]").val("").trigger("change");
        } else {
            $(".hide-cancel-return").show();
            $("select[name=orders_status]>option[data-show=0]").removeAttr("disabled");
            $("select[name=orders_status]>option[data-show=1]").attr("disabled", "disabled");
            //    $(".order-status-div").removeClass("col-md-12");
            //    $(".order-status-div").addClass("col-md-3");
            $("select[name=orders_status]").val(currentOrderStatus);
        }
    }

    let isResettingAddress = false;
    window.customerAddresses = @json($jsAddresses);

    $("#openCustomOrderModalBtn").click(function() {
        $("#customizedOrderModal").modal("show");
    });

    $(function() {
        // When modal shown, if country already set (e.g., autofill), refresh city list
        $('#customizedOrderModal').on('shown.bs.modal', function() {
            var country = $('select[name="cust_country_id"]').val();
            if (country) {
                // Prefer existing stored city id (hidden), fallback to select DOM value
                var existingCityId = $('#existing_custom_city_id').val() || $('.cust_city_id').val();
                var existingCityName = $('#existing_custom_city_name').val() || $('input[name="cust_city"]').val();
                // If we have a city name but no id, pass undefined so getCustomCity will try matching by text
                getCustomCity(country, existingCityId ? existingCityId : null);
            }
        });

        $(document).on('change', '#customer_address_select', function () {
            let val = $(this).val();

            if (window.customerAddresses && window.customerAddresses[val]) {
                fillAddress(window.customerAddresses[val]);
            }
        });

        function fillAddress(addr) {

            $('input[name="cust_name"]').val(addr?.first_name + ' ' + addr?.last_name || '');
            $('input[name="cust_block"]').val(addr.block || '');
            $('input[name="cust_street"]').val(addr.street || '');
            $('input[name="cust_house_number"]').val(addr.house_number || '');
            $('input[name="cust_floor"]').val(addr.floor || '');
            $('input[name="cust_address_detail"]').val(addr.address_detail || '');

            // Set country first
            $('select[name="cust_country_id"]')
                .val(addr.country_id)
                .trigger('change');

            // Store city values temporarily
            $('#existing_custom_city_id').val(addr.city_id || '');
            $('#existing_custom_city_name').val(addr.city || '');

            // Load city list and auto-select city
            setTimeout(function () {
                getCustomCity(addr.country_id, addr.city_id);
            }, 300);
        }

        function enableManualAddress() {
            $('input[name="cust_name"]').val('');
            $('input[name="cust_block"]').val('');
            $('input[name="cust_street"]').val('');
            $('input[name="cust_house_number"]').val('');
            $('input[name="cust_floor"]').val('');
            $('input[name="cust_city"]').val('');
        }

        // Helper to fetch city list for customized modal and optionally select a city by id
        function getCustomCity(countryId, cityId) {
             if (isResettingAddress) return; // 🚫 STOP auto city fill during reset
            if (!countryId) return;
            $.ajax({
                url: "{{route('web.user.get_city')}}",
                method: "POST",
                data: {
                    country_id: countryId,
                    _token: "{{csrf_token()}}"
                },
                success: function(data) {
                    if (!data) {
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                        return;
                    }

                    // Fill select with returned options
                    $('.cust_city_id').html(data);
                    // Create temp select to inspect options
                    var tmp = $('<select>').html(data);
                    var opts = tmp.find('option');
                    if (opts.length > 1) {
                        // Show select and hide text input
                        $('.cust_city_list').show();
                        $('.cust_city_text').hide();

                        // Determine which option to select
                        var didSelect = false;
                        // 1) If explicit cityId provided and matching option exists, select it
                        if (cityId) {
                            var optByVal = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (optByVal.length) {
                                optByVal.prop('selected', true);
                                didSelect = true;
                            }
                        }

                        // 2) If not selected and cust_city text input has a value, try to match by option text (case-insensitive)
                        if (!didSelect) {
                            var desiredText = $('input[name="cust_city"]').val();
                            if (desiredText && desiredText.trim() !== '') {
                                desiredText = desiredText.trim().toLowerCase();
                                var matched = null;
                                $('.cust_city_id option').each(function() {
                                    var t = $(this).text().trim().toLowerCase();
                                    if (t === desiredText) {
                                        matched = $(this);
                                        return false;
                                    }
                                });
                                if (matched && matched.length) {
                                    matched.prop('selected', true);
                                    didSelect = true;
                                }
                            }
                        }

                        // 3) Fallback: choose first real option
                        if (!didSelect) {
                            var firstVal = opts.eq(1).attr('value');
                            if (firstVal) $('.cust_city_id').val(firstVal);
                        }

                        // Ensure cust_city text input contains the selected option text for server-side
                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) $('input[name="cust_city"]').val(selectedText).trigger('input');

                        // After city list loads
                        if (cityId) {
                            var opt = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (opt.length) {
                                opt.prop('selected', true);
                            }
                        }

                        // Sync text field
                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) {
                            $('input[name="cust_city"]').val(selectedText);
                        }

                        // trigger change so any listeners update
                        $('.cust_city_id').trigger('change');
                    } else {
                        // No city list available: show text input
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                    }
                }
            });
        }

        function resetAddressFields() {

            isResettingAddress = true;

            // Clear text fields
            $('input[name="cust_name"]').val('');
            $('input[name="cust_block"]').val('');
            $('input[name="cust_street"]').val('');
            $('input[name="cust_house_number"]').val('');
            $('input[name="cust_floor"]').val('');
            $('input[name="cust_city"]').val('');
            $('input[name="cust_address_detail"]').val('');
            $('input[name="cust_address_url"]').val('');
            $('textarea[name="cust_note"]').val('');

            // Clear country & city
            $('select[name="cust_country_id"]').val('');
            $('.cust_city_id').empty().append('<option value="">Select Area</option>');

            // Hide dropdowns
            $('.cust_city_list').hide();
            $('.cust_city_text').show();

            $('#address_dropdown_wrapper').hide();

            // Clear temp city memory
            $('#existing_custom_city_id').val('');
            $('#existing_custom_city_name').val('');

            // Clear JS cache
            window.customerAddresses = [];

            // Allow async handlers to finish, then unlock
            setTimeout(() => {
                isResettingAddress = false;
            }, 100);
        }

        // Bind country change to fetch cities
        $('select[name="cust_country_id"]').change(function() {
            var countryId = $(this).val();
            getCustomCity(countryId);
        });
        // When city select changes, copy its text into cust_city input so server always receives a city name
        $(document).on('change', '.cust_city_id', function() {
            if (isResettingAddress) return;
            var txt = $(this).find('option:selected').text().trim();
            if (txt) {
                $('input[name="cust_city"]').val(txt);
            }
        });

        // Add Item
        $(document).on('click', '.add-modal-custom-item', function() {
            addModalCustomItemRow();
        });

        function syncModalCustomItemButtons() {
            var $rows = $('#modal_custom_items_list .modal-custom-item-row');
            if (!$rows.length) {
                return;
            }

            $rows.each(function() {
                $(this).find('.add-modal-custom-item').remove();
                $(this).find('.remove-modal-custom-item').show();
            });

            var $lastIconBtn = $rows.last().find('.icon-btn').first();
            if ($lastIconBtn.length && $lastIconBtn.find('.add-modal-custom-item').length === 0) {
                $lastIconBtn.prepend(`
                    <span class="add-modal-custom-item mr-2"
                        style="cursor:pointer; font-size:16px; display:block;"
                        title="{{__('web.add_item')}}">
                        <i class="fa fa-plus"></i>
                    </span>
                `);
            }

        }

        // Remove Item (if last row ? clear inputs)
        $(document).on('click', '.remove-modal-custom-item', function () {
            var $list = $('#modal_custom_items_list');
            var totalRows = $list.find('.modal-custom-item-row').length;
            var $row = $(this).closest('.modal-custom-item-row');

            if (totalRows > 1) {
                $row.remove();
            } else {
                $row.find('textarea, input').each(function () {
                    if ($(this).hasClass('custom-total')) {
                        $(this).val('0.000');
                    } else if ($(this).attr('type') === 'file') {
                        $(this).val('');
                    } else if ($(this).attr('type') === 'hidden' && $(this).attr('name') === 'custom_item_existing_image[]') {
                        $(this).val('');
                    } else {
                        $(this).val('');
                    }
                });

                $row.find('.custom-item-image-preview').html(`
                    <img src="{{ $defaultReferenceImageUrl }}"
                        alt="{{ __('web.photo_reference') }}"
                        style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px;">
                `);
            }

            syncModalCustomItemButtons();
            calculateModalTotal();           
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-price, .custom-qty', function() {
            var row = $(this).closest('.modal-custom-item-row');
            var qty = parseFloat(row.find('.custom-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-price').val()) || 0;
            var total = qty * price;

            row.find('.custom-total').val(total.toFixed(3));
            calculateModalTotal();
        });

        function addModalCustomItemRow() {
            var $list = $('#modal_custom_items_list');
            var totalRows = $list.find('.modal-custom-item-row').length;
            var isFirst = totalRows === 0;
            var deleteBtn = '<span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{__('web.remove_item')}}"><i class="fa fa-trash"></i></span>';
            var addBtn = '<span class="add-modal-custom-item mr-2" style="cursor:pointer; font-size: 16px; display:block;" title="{{__('web.add_item')}}"><i class="fa fa-plus"></i></span>';

            var html = `
                <div class="row modal-custom-item-row">
                    <div class="col-md-5 form-item pr-0">
                        <div class="d-flex align-items-start custom-item-image-field">
                            <input type="hidden" name="custom_item_existing_image[]" value="">
                            <div class="custom-item-image-preview text-left mr-2" style="flex:0 0 48px;">
                                <img src="{{ $defaultReferenceImageUrl }}" alt="{{ __('web.photo_reference') }}" style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px; margin-top: 1rem;">
                            </div>
                            <div class="flex-grow-1">
                                <label class="d-block mb-1">{{ __('web.photo_reference') }}</label>
                                <input type="file" name="custom_item_image[]" class="form-control form-control-sm custom-item-image-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 form-item pr-0">
                        <label class="d-block mb-1 responsive-field-label">{{ __('web.product_title') }}</label>
                        <input type="text" name="custom_product_title[]" class="form-control form-control-sm"
                            placeholder="{{ __('web.enter_product_title') }}"
                            data-req-msg="{{ __('web.error-req-product-title') }}">
                    </div>
                    <div class="col-md-3 form-item pr-0">
                        <label class="d-block mb-1 responsive-field-label">{{ __('web.description') }}</label>
                        <textarea name="custom_desc[]" class="form-control form-control-sm" placeholder="{{__('web.enter_description')}}" data-req-msg="{{__('web.error-req-description')}}" rows="2" style="resize: none;"></textarea>
                    </div>
        <div class="col-md-1 form-item pr-0 custom-item-actions-col">
            <label class="d-block mb-1">&nbsp;</label>
            <div class="icon-btn d-flex align-items-center" style="gap: 2rem; min-height: 38px; padding: 0.5rem;">
                ${addBtn}
                ${deleteBtn}
            </div>
        </div>
        <div class="col-md-12 custom-item-fields-row mt-2">
            <div class="row">
                            <div class="col-md-3 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.item_code') }}</label>
                                <input type="text" name="custom_item_code[]" class="form-control form-control-sm"
                                    placeholder="{{ __('web.enter_item_code') }}"
                                    data-req-msg="{{ __('web.error-req-item-code') }}">
                            </div>
                            <div class="col-md-2 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.color') }}</label>
                                <input type="text" name="custom_color[]" class="form-control form-control-sm" placeholder="{{__('web.enter_color')}}" data-req-msg="{{__('web.error-req-color')}}">
                            </div>
                            <div class="col-md-2 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.size') }}</label>
                                <input type="text" name="custom_size[]" class="form-control form-control-sm" placeholder="{{__('web.enter_size')}}" data-req-msg="{{__('web.error-req-size')}}">
                            </div>
                            <div class="col-md-2 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.price') }}</label>
                                <input type="number" name="custom_price[]" class="form-control custom-price form-control-sm" placeholder="{{__('web.enter_price')}}" step="0.001" data-req-msg="{{__('web.error-req-price')}}">
                            </div>
                            <div class="col-md-1 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.quantity') }}</label>
                                <input type="number" name="custom_qty[]" class="form-control custom-qty form-control-sm" placeholder="{{__('web.enter_quantity')}}" min="1" data-req-msg="{{__('web.error-req-quantity')}}">
                            </div>
                            <div class="col-md-2 form-item pr-0">
                                <label class="responsive-field-label">{{ __('web.total_price') }}</label>
                                <input type="text" name="custom_total[]" class="form-control custom-total form-control-sm" readonly value="0.000">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#modal_custom_items_list').append(html);
            syncModalCustomItemButtons();
        }

        function renderCustomItemImagePreview($input) {
            var $field = $input.closest('.custom-item-image-field');
            if (!$field.length) {
                $field = $input.closest('.form-item').find('.custom-item-image-field').first();
            }
            var $preview = $field.find('.custom-item-image-preview').first();
            var file = $input[0].files && $input[0].files[0];

            if (!file) {
                return;
            }

            if (!file.type || file.type.indexOf('image/') !== 0) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var imageHtml = '<a href="' + e.target.result + '" target="_blank" class="custom-item-image-link d-inline-block">' +
                        '<img src="' + e.target.result + '" alt="Image Preview" style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px; margin-top: 1rem;">' +
                    '</a>';
                $preview.html(imageHtml);
            };
            reader.readAsDataURL(file);
        }

        $(document).on('change', '.custom-item-image-input', function() {
            renderCustomItemImagePreview($(this));
        });

        // Remove Row
        $(document).on('click', '.remove-custom-item', function() {
            $(this).closest('.custom-item-row').remove();
            calculateCustomOrderTotal();
        });

        // Calculate Row Total and Grand Total
        $(document).on('input', '.custom-item-qty, .custom-item-price', function() {
            var row = $(this).closest('.custom-item-row');
            var qty = parseFloat(row.find('.custom-item-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-item-price').val()) || 0;
            var total = qty * price;

            // Update row total field
            row.find('.custom-item-total').val(total.toFixed(3));

            calculateCustomOrderTotal();
        });

        // Show validation message on click/focus
        $(document).on('click focus', '.mandatory', function() {
            var input = $(this);
            // Remove any existing error message and reset border
            input.next('.text-red').remove();
            input.css('border', '1px solid #e1e1e1');

            // Check if empty
            if (input.val().trim() === '') {
                var msg = input.attr('data-req-msg');
                if (msg) {
                    input.after('<div class="text-red" style="font-size:12px; margin-top:5px;">' + msg + '</div>');
                    input.css('border', '1px solid red');
                }
            }
        });

        $(document).on('input change', 'select[name="custom_discount_type"], input[name="custom_discount_val"]', function() {

            let discountType = $('select[name="custom_discount_type"]').val();
            let discountVal = parseFloat($('input[name="custom_discount_val"]').val()) || 0;

            let subTotal = 0;
            $('.custom-total').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });

            // Clear old error
            $('input[name="custom_discount_val"]').next('.text-red').remove();
            $('input[name="custom_discount_val"]').css('border', '1px solid #e1e1e1');

            // Percentage validation
            if (discountType === 'percentage' && discountVal > 100) {
                showDiscountError("Percentage discount cannot be more than 100%");
                return;
            }

            // Flat discount validation
            if (discountType === 'flat' && discountVal > subTotal) {
                showDiscountError("Discount amount cannot be greater than order total");
                return;
            }
        });

        function showDiscountError(msg) {
            let input = $('input[name="custom_discount_val"]');
            input.after('<div class="text-red" style="font-size:12px;margin-top:5px;">' + msg + '</div>');
            input.css('border', '1px solid red');
        }

        function formatModalCurrency(amount) {
            return (parseFloat(amount) || 0).toFixed(3) + ' ' + window.customizedOrderCurrencyCode;
        }

        function updateModalSummary() {
            let subTotal = 0;
            $('.custom-total').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });

            let shipping = parseFloat($('input[name="custom_shipping"]').val()) || 0;
            let discountType = $('select[name="custom_discount_type"]').val();
            let discountVal = parseFloat($('input[name="custom_discount_val"]').val()) || 0;
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subTotal * discountVal) / 100;
            } else if (discountType === 'flat') {
                discountAmount = discountVal;
            }

            if (discountAmount > subTotal) {
                discountAmount = subTotal;
            }

            let grandTotal = subTotal + shipping - discountAmount;
            if (grandTotal < 0) grandTotal = 0;

            let advancePaid = parseFloat(window.customizedOrderAdvanceAmount) || 0;
            let pendingAmount = grandTotal - advancePaid;
            if (pendingAmount < 0) pendingAmount = 0;

            let discountLabel = '{{ __('web.discount') }}';
            if (discountType === 'percentage') {
                discountLabel += ' <small>({{ __('web.discount_percentage') }})</small>';
            } else if (discountType === 'flat') {
                discountLabel += ' <small>({{ __('web.discount_flat') }})</small>';
            }

            $('#modal_sub_total').text(formatModalCurrency(subTotal));
            $('#modal_shipping_charge').text(formatModalCurrency(shipping));
            $('#modal_discount_amount').text('- ' + formatModalCurrency(discountAmount));
            $('#modal_grand_total').text(formatModalCurrency(grandTotal));
            $('#modal_advance_paid').text('- ' + formatModalCurrency(advancePaid));
            $('#modal_pending_amount').text(formatModalCurrency(pendingAmount));
            $('#modal_discount_label').html(discountLabel);
        }

        function calculateModalTotal() {
            updateModalSummary();
        }

        $(document).on('input change',
            '.custom-price, .custom-qty, input[name="custom_shipping"], input[name="custom_discount_val"], select[name="custom_discount_type"]',
            calculateModalTotal
        );
        calculateModalTotal();

        // Submit Logic
        $('#submit_customized_order').click(function() {
            var formData = new FormData($('#customized_order_payload_form')[0]);
            formData.append('_token', "{{csrf_token()}}");
            $.ajax({
                url: "{{route('admin.order.customized-order.update-full-order')}}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        // alert(response.message);
                    }
                },
                error: function() {
                    // alert("{{__('web.error_something_went_wrong')}}");
                }
            });
        });
    });
</script>
@endsection








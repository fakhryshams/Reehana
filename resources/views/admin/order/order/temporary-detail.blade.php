@extends('layout/main')
@section('page_title', __('admin.temporary-order-detail'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<style>
    .subtotal-table td,
    .subtotal-table th {
        padding: 0.40rem !important;
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
@endsection

@php
    use App\Helpers\Helper;

    $currCode = $currency ? $currency->code : config('custom.currency', 'KWD');
    $currencyCode = $currCode;
    $custName = $tempOrder->customer_name ?: ($tempOrder->user ? ($tempOrder->user->first_name . ' ' . $tempOrder->user->last_name) : '-');
    $custEmail = $tempOrder->customer_email ?: ($tempOrder->user ? $tempOrder->user->email : '');
    $custPhone = $tempOrder->customer_phone ?: ($tempOrder->user ? $tempOrder->user->mobile : '');
    $countryName = $tempOrder->country ? $tempOrder->country->country_name : '';
    $statusBadge = $tempOrder->status_badge_class ?: 'badge-secondary';
    $typeBadge = $customizedBadgeColor[$tempOrder->type] ?? ($tempOrder->type_badge_class ?: 'badge-secondary');

    $existingCust = [
        'customer' => [
            'name' => $tempOrder->customer_name,
            'email' => $tempOrder->customer_email,
            'phone' => $tempOrder->customer_phone,
            'country_id' => $tempOrder->country_id,
            'city_id' => $tempOrder->city_id,
            'city' => $tempOrder->city,
            'block' => $tempOrder->block,
            'street' => $tempOrder->street,
            'house_number' => $tempOrder->house_number,
            'floor' => $tempOrder->floor,
            'address_detail' => $tempOrder->address_detail,
            'address_url' => $tempOrder->address_url,
            'note' => $tempOrder->note,
        ],
        'items' => [],
        'shipping' => $tempOrder->shipping_price ?? 0,
        'discount_type' => $tempOrder->discount_type ?: 'flat',
        'discount_val' => $tempOrder->discount_val ?? 0,
    ];

    if ($tempOrder->details && count($tempOrder->details) > 0) {
        foreach ($tempOrder->details as $d) {
            $existingCust['items'][] = [
                'item_code' => $d->item_code,
                'product_title' => $d->product_name,
                'description' => $d->description,
                'color' => $d->color,
                'size' => $d->size,
                'price' => $d->price,
                'quantity' => $d->quantity,
                'total' => $d->total_price,
                'image' => $d->image ? preg_replace('/^public\//', '', $d->image) : null,
            ];
        }
    } else {
        $existingCust['items'][] = [
            'item_code' => '',
            'product_title' => '',
            'description' => '',
            'color' => '',
            'size' => '',
            'price' => '',
            'quantity' => '',
            'total' => '',
            'image' => null,
        ];
    }

    $defaultReferenceImageUrl = asset(Storage::url('default.png'));
    $addresses = $addresses ?? collect();
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

    $modalSubTotal = (float) $tempOrder->subtotal;
    $modalShipping = (float) ($tempOrder->shipping_price ?? 0);
    $modalDiscountType = $tempOrder->discount_type ?? '';
    $modalDiscountVal = (float) ($tempOrder->discount_val ?? 0);
    $modalDiscountAmount = (float) ($tempOrder->discount_amount ?? 0);
    $modalTotalOrderValue = (float) $tempOrder->grand_total;
    $modalAdvancePaid = (float) ($tempOrder->advance_price ?? 0);
    $modalPendingAmount = (float) ($tempOrder->pending_amount ?? 0);
    $modalDiscountLabel = '';
    if ($modalDiscountType === 'percentage') {
        $modalDiscountLabel = __('web.discount_percentage');
    } elseif ($modalDiscountType === 'flat') {
        $modalDiscountLabel = __('web.discount_flat');
    }
@endphp

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.customized-order') }}</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home.index') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.order.temporary-orders') }}">{{ __('admin.temporary-orders') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.view_order') }}</li>
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
                                    {{ $tempOrder->type === 'quotation' ? 'T-QO' : 'T-PO' }} #{{ $tempOrder->id }}
                                    <span class="badge {{ $typeBadge }} ml-2" style="font-size: 0.6em; vertical-align: middle;">
                                        {{ ucfirst(str_replace('_', ' ', $tempOrder->type)) }}
                                    </span>
                                    <span class="badge {{ $statusBadge }} ml-1" style="font-size: 0.6em; vertical-align: middle;">
                                        {{ ucfirst(str_replace('_', ' ', $tempOrder->status)) }}
                                    </span> |
                                    <small>
                                        {{ $tempOrder->type === 'quotation' ? __('admin.quotation-date') : __('admin.po-date') }} :
                                        {{ \Carbon\Carbon::parse($tempOrder->created_at)->format(config('custom.php_datetime_format')) }}
                                    </small>
                                    <button type="button" class="btn btn-primary btn-sm ml-2 float-right" id="openCustomOrderModalBtn" title="{{ $tempOrder->type === 'quotation' ? 'Edit Quotation' : 'Edit Purchase Order' }}">
                                        <i class="fas fa-edit"></i> {{ $tempOrder->type === 'quotation' ? 'Edit Quotation' : 'Edit Purchase Order' }}
                                    </button>
                                </h4>
                            </div>
                            <!-- /.col -->
                        </div>

                        <!-- info row -->
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <b>{{ __('admin.customer-info') }}</b>
                                <address>
                                    {{ $custName }}<br>
                                    @if($custEmail)
                                    {{ __('admin.email') }} : {{ $custEmail }}<br>
                                    @endif
                                    @if($custPhone)
                                    <strong>{{ __('admin.phone') }} : </strong>{{ $custPhone }}<br>
                                    @endif
                                    @if($tempOrder->user_id)
                                    <span class="badge badge-info mt-1">{{ __('admin.user_account') }}: #{{ $tempOrder->user_id }}</span>
                                    @endif
                                </address>
                            </div>

                            <!-- /.col -->
                            <div class="col-sm-4 invoice-col">
                                <b>{{ __('admin.billing-info') }}</b>
                                <address>
                                    {{ $custName }}<br>
                                    @if($tempOrder->address_title_text)<div><strong>{{ __('admin.address_title') }} :</strong> {{ e($tempOrder->address_title_text) }}</div>@endif
                                    @if($tempOrder->block)<div><strong>Appartment :</strong> {{ e($tempOrder->block) }}</div>@endif
                                    @if($tempOrder->floor)<div><strong>Floor :</strong> {{ e($tempOrder->floor) }}</div>@endif
                                    @if($tempOrder->house_number)<div><strong>House Number :</strong> {{ e($tempOrder->house_number) }}</div>@endif
                                    @if($tempOrder->street)<div><strong>Street :</strong> {{ e($tempOrder->street) }}</div>@endif
                                    @if($tempOrder->city)<div><strong>City :</strong> {{ e(ucfirst($tempOrder->city)) }}</div>@endif
                                    @if($countryName)<div><strong>Country :</strong> {{ e($countryName) }}</div>@endif
                                    @if($tempOrder->address_detail)<div><strong>Address Detail :</strong> {{ e($tempOrder->address_detail) }}</div>@endif
                                    @if($tempOrder->address_url)<div><strong>Map Location :</strong> <a href="{{ $tempOrder->address_url }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-location-arrow"></i> {{ __('admin.view_map') }}</a></div>@endif
                                    @if($tempOrder->note)<div><strong>Note :</strong> {!! nl2br(e($tempOrder->note)) !!} </div>@endif
                                    @if($custEmail)<strong>{{ __('admin.email') }} : </strong>{{ $custEmail }}<br>@endif
                                    @if($custPhone)<strong>{{ __('admin.phone') }} : </strong>{{ $custPhone }}<br>@endif
                                </address>
                            </div>

                            <!-- /.col -->
                        </div>

                        <!-- Table row -->
                        <div class="row">
                            <div class="col-xs-12 col-12 table-responsive">
                                <table class="table table-striped normal-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle" style="width: 100px;">{{ __('admin.image') }}</th>
                                            <th>{{ __('admin.product-name') }}</th>
                                            <th>{{ __('web.item_code') }}</th>
                                            <th>{{ __('admin.color') }}</th>
                                            <th>{{ __('admin.size') }}</th>
                                            <th>{{ __('admin.price') }}</th>
                                            <th class="text-center">{{ __('admin.qty') }}</th>
                                            <th>{{ __('admin.total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tempOrder->details as $recordIndex => $record)
                                        @php
                                            $itemImage = $record->image ? preg_replace('/^public\//', '', $record->image) : null;
                                            $pricetotal = $record->total_price ?: ($record->quantity * $record->price);
                                        @endphp
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
                                                <b>{{ Helper::formatCustomizedTitle($record->product_name ?: 'Custom Item') }}</b>
                                                @if(!empty($record->description))
                                                    <br />
                                                    <span class="text-muted small">{{ Helper::formatCustomizedDescription($record->description) }}</span>
                                                @endif
                                            </td>
                                            <td width="12%" class="barcode-cell">
                                                {{ !empty($record->item_code) ? $record->item_code : '-' }}
                                            </td>
                                            <td width="10%">
                                                {{ !empty($record->color) ? $record->color : '-' }}
                                            </td>
                                            <td width="10%">
                                                {{ !empty($record->size) ? $record->size : '-' }}
                                            </td>
                                            <td>{{ Helper::formatNumber($record->price) }} <small>{{ $currCode }}</small></td>
                                            <td class="text-center">{{ $record->quantity }}</td>
                                            <td>{{ Helper::formatNumber($pricetotal) }} <small>{{ $currCode }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">{{ __('admin.no_record_found') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.col -->
                        </div>
                        <hr>
                        <div class="row mt-2">
                            <!-- accepted payments column -->
                            <div class="col-md-6">
                                @if($tempOrder->payment_type)
                                <div class="col-md-12 p-0">
                                    <div class="form-group">
                                        <label>{{ __('admin.payment-method') }}</label>
                                        <input type="text" class="form-control" readonly="" value="{{ ucfirst(str_replace('_', ' ', $tempOrder->payment_type)) }}" disabled="">
                                    </div>
                                </div>
                                @endif
                            </div>
                            <!-- /.col -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table subtotal-table">
                                        <tbody>
                                            <tr>
                                                <th class="th-td-boarder-none">{{ __('admin.subtotal') }}</th>
                                                <td class="th-td-boarder-none">{{ Helper::formatNumber($tempOrder->subtotal) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('admin.shipping') }}</th>
                                                <td>{{ Helper::formatNumber($tempOrder->shipping_price) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            @if($tempOrder->discount_amount > 0 || $tempOrder->discount_val > 0)
                                            <tr>
                                                <th>{{ __('web.discount_type') }}</th>
                                                <td>{{ ucfirst($tempOrder->discount_type) }}</td>
                                            </tr>
                                            @if($tempOrder->discount_type == 'percentage')
                                            <tr>
                                                <th>{{ __('web.discount_value') }}</th>
                                                <td>{{ $tempOrder->discount_val }}%</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>{{ __('admin.discount') }}</th>
                                                <td>- {{ Helper::formatNumber($tempOrder->discount_amount) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>{{ __('admin.total') }}</th>
                                                <td>{{ Helper::formatNumber($tempOrder->grand_total) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            @if($tempOrder->type === "purchase_order" || $tempOrder->advance_price > 0)
                                            <tr>
                                                <th>{{ __('admin.advance_payment') }}</th>
                                                <td>{{ Helper::formatNumber($tempOrder->advance_price) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('admin.pending_amount') }}</th>
                                                <td class="text-danger font-weight-bold">{{ Helper::formatNumber($tempOrder->pending_amount) }}<small> {{ $currCode }}</small></td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /.col -->
                        </div>
                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
</div>

<!-- Customized Order Modal -->
<div id="customizedOrderModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h4 class="address_title mb-2" id="modal_order_title">{{ $tempOrder->type === 'quotation' ? 'Edit Quotation' : 'Edit Purchase Order' }}</h4>
                <form id="customized_order_payload_form" method="POST" action="{{ route('admin.order.temporary-orders.update', ['id' => $tempOrder->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="temporary_order_id" value="{{ $tempOrder->id }}">

                    <!-- Request Type Selector -->
                    <div class="row align-items-center mb-2 mt-2">
                        <div class="col-md-12 form-item h-flex">
                            <label class="mr-3 font-weight-bold">{{ __('web.request_type') }}*</label>
                            <div class="radio mr-3 d-inline-block">
                                <input type="radio" id="req_type_po" name="custom_req_type" value="purchase_order" {{ $tempOrder->type === 'purchase_order' ? 'checked' : '' }}>
                                <label for="req_type_po">{{ __('web.purchase_order') }}</label>
                            </div>
                            <div class="radio d-inline-block">
                                <input type="radio" id="req_type_qt" name="custom_req_type" value="quotation" {{ $tempOrder->type === 'quotation' ? 'checked' : '' }}>
                                <label for="req_type_qt">{{ __('web.quotation') }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;" class="mt-2">{{ __('web.customer_details') }}</h5>
                    <div class="row">
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_phone" value="{{ isset($existingCust['customer']['phone']) ? $existingCust['customer']['phone'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_phone_number') }}*" data-req-msg="{{ __('web.error-req-phone-number') }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_email" value="{{ isset($existingCust['customer']['email']) ? $existingCust['customer']['email'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_email') }}" data-req-msg="{{ __('web.error-req-email') }}">
                        </div>
                        @if(isset($addresses) && count($addresses) > 0)
                        <div class="col-md-12 form-item mb-2" id="address_dropdown_wrapper">
                            <select class="form-control form-control-sm" id="customer_address_select" name="customer_address_id">
                                <option value="">{{ __('web.select_address') }}</option>
                                @foreach ($addresses as $address)
                                    <option value="{{ $address->id }}">{{ $address->address_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_name" value="{{ isset($existingCust['customer']['name']) ? $existingCust['customer']['name'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_name') }}" data-req-msg="{{ __('web.error-req-name') }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <select name="cust_country_id" class="form-control form-control-sm" data-req-msg="{{ __('web.error-req-country') }}">
                                <option value="">{{ __('web.select_country') }}</option>
                                @php
                                    $language = Helper::getLanguage();
                                @endphp
                                @foreach($country as $c)
                                    @php
                                        $cDesc = $c->getCountryDescription($language->id);
                                        $sel = (isset($existingCust['customer']['country_id']) && $existingCust['customer']['country_id'] == $c->id) ? 'selected' : '';
                                    @endphp
                                    <option value="{{ $c->id }}" {{ $sel }}>{{ $cDesc ? $cDesc->country_name : $c->currency_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <div class="cust_city_list">
                                <select name="cust_city_id" class="form-control cust_city_id select-component untouched">
                                    <option value="">{{ __('web.select_area') }}</option>
                                </select>
                            </div>
                            <div class="cust_city_text" style="display:none; margin-top:5px;">
                                <input type="text" name="cust_city" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_city') }}" data-req-msg="{{ __('web.error-req-city') }}">
                            </div>
                            <input type="hidden" id="existing_custom_city_id" value="{{ isset($existingCust['customer']['city_id']) ? $existingCust['customer']['city_id'] : '' }}">
                            <input type="hidden" id="existing_custom_city_name" value="{{ isset($existingCust['customer']['city']) ? $existingCust['customer']['city'] : '' }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_block" value="{{ isset($existingCust['customer']['block']) ? $existingCust['customer']['block'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.block') }}" data-req-msg="{{ __('web.error-req-block') }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_street" value="{{ isset($existingCust['customer']['street']) ? $existingCust['customer']['street'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.street') }}" data-req-msg="{{ __('web.error-req-street') }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_house_number" value="{{ isset($existingCust['customer']['house_number']) ? $existingCust['customer']['house_number'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.house_number') }}" data-req-msg="{{ __('web.error-req-house') }}">
                        </div>
                        <div class="col-md-6 form-item mb-2">
                            <input type="text" name="cust_floor" value="{{ isset($existingCust['customer']['floor']) ? $existingCust['customer']['floor'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.floor') }}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <input type="text" name="cust_address_detail" value="{{ isset($existingCust['customer']['address_detail']) ? $existingCust['customer']['address_detail'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.address_detail') }}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <input type="text" name="cust_address_url" value="{{ isset($existingCust['customer']['address_url']) ? $existingCust['customer']['address_url'] : '' }}" class="form-control form-control-sm" placeholder="{{ __('web.address_url') }}">
                        </div>
                        <div class="col-md-12 form-item mb-2">
                            <textarea name="cust_note" class="form-control form-control-sm" placeholder="{{ __('web.note') }}" rows="3" style="resize: vertical;">{{ isset($existingCust['customer']['note']) ? $existingCust['customer']['note'] : '' }}</textarea>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px;" class="mt-2">{{ __('web.order_details') }}</h5>
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
                                        <textarea name="custom_desc[]" class="form-control form-control-sm" placeholder="{{ __('web.enter_description') }}" data-req-msg="{{ __('web.error-req-description') }}" rows="2" style="resize: none;">{{ $item['description'] }}</textarea>
                                    </div>
                                    <div class="col-md-1 form-item pr-0 mb-2 custom-item-actions-col">
                                        @if($index === 0) <label class="d-block mb-1">&nbsp;</label> @endif
                                        <div class="icon-btn d-flex align-items-center" style="gap: 2rem; min-height: 38px; padding: 0.5rem;">
                                            @if($loop->last)
                                            <span class="add-modal-custom-item" style="cursor:pointer; font-size: 16px; display:block;" title="{{ __('web.add_item') }}"><i class="fa fa-plus"></i></span>
                                            @endif
                                            @if($index > 0)
                                            <span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{ __('web.remove_item') }}"><i class="fa fa-trash"></i></span>
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
                                                <input type="text" name="custom_color[]" value="{{ $item['color'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_color') }}" data-req-msg="{{ __('web.error-req-color') }}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.size') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.size') }}</label>
                                                @endif
                                                <input type="text" name="custom_size[]" value="{{ $item['size'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('web.enter_size') }}" data-req-msg="{{ __('web.error-req-size') }}">
                                            </div>
                                            <div class="col-md-2 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.price') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.price') }}</label>
                                                @endif
                                                <input type="number" name="custom_price[]" value="{{ $item['price'] ?? '' }}" class="form-control form-control-sm custom-price" placeholder="{{ __('web.enter_price') }}" step="0.001" data-req-msg="{{ __('web.error-req-price') }}">
                                            </div>
                                            <div class="col-md-1 form-item pr-0 mb-2">
                                                @if($index === 0)
                                                    <label>{{ __('web.quantity') }}</label>
                                                @else
                                                    <label class="responsive-field-label">{{ __('web.quantity') }}</label>
                                                @endif
                                                <input type="number" name="custom_qty[]" value="{{ $item['quantity'] ?? '' }}" class="form-control form-control-sm custom-qty" placeholder="{{ __('web.enter_quantity') }}" min="1" data-req-msg="{{ __('web.error-req-quantity') }}">
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

                        <!-- Financial sections -->
                        <div class="row">
                            <div class="col-md-6" id="advance_payment_section" style="{{ $tempOrder->type === 'quotation' ? 'display:none;' : '' }}">
                                <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{ __('web.payment_details') }}</h5>
                                <div class="form-item">
                                    <label>{{ __('web.advance_payment') }}</label>
                                    <input type="number" name="advance_payment" class="form-control form-control-sm" placeholder="0.000" step="0.001" min="0" value="{{ $tempOrder->advance_price }}">
                                </div>
                                @php
                                    $savedCustomDetails = json_decode($tempOrder->custom_order_details, true) ?? [];
                                    $savedGenPending = !empty($savedCustomDetails['payment']['generate_pending_link']) || !empty($savedCustomDetails['generate_pending_link']);
                                @endphp
                                <div class="form-item" id="generate_pending_link_wrapper" style="margin-top: 10px;">
                                    <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="generate_pending_payment_link" id="generate_pending_payment_link" value="1" {{ $savedGenPending ? 'checked' : '' }}>
                                        <span>{{ __('web.generate_pending_amount_payment_link') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="{{ $tempOrder->type === 'quotation' ? 'col-md-12' : 'col-md-6' }}" id="shipping_section_wrapper">
                                <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{ __('web.shipping_details') }}</h5>
                                <div class="form-item">
                                    <label>{{ __('web.shipping_charges') }}</label>
                                    <input type="number" name="custom_shipping" class="form-control form-control-sm" placeholder="0.000" step="0.001" value="{{ $tempOrder->shipping_price }}">
                                </div>
                            </div>
                        </div>

                        <div id="discount_section">
                            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">{{ __('web.discount_details') }}</h5>
                            <div class="row justify-content-end">
                                <div class="col-md-6 form-item">
                                    <label>{{ __('web.discount_type') }}</label>
                                    <select name="custom_discount_type" class="form-control form-control-sm">
                                        <option value="">{{ __('web.select') }}</option>
                                        <option value="flat" {{ ($existingCust['discount_type'] == 'flat') ? 'selected' : '' }}>Flat</option>
                                        <option value="percentage" {{ ($existingCust['discount_type'] == 'percentage') ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-item">
                                    <label>{{ __('web.discount_value') }}</label>
                                    <input type="number" name="custom_discount_val" class="form-control form-control-sm" placeholder="0.000" step="0.001" value="{{ $existingCust['discount_val'] }}">
                                </div>
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
                                <div class="modal-custom-order-summary-item" id="modal_advance_paid_row" style="{{ $tempOrder->type === 'quotation' ? 'display:none;' : '' }}">
                                    <label>{{ __('web.advance_paid') }}</label>
                                    <span id="modal_advance_paid">- {{ Helper::formatNumber($modalAdvancePaid) }} {{ $currencyCode }}</span>
                                </div>
                                <div class="modal-custom-order-summary-item" id="modal_pending_amount_row" style="{{ $tempOrder->type === 'quotation' ? 'display:none;' : '' }}">
                                    <label>{{ __('web.pending_amount') }}</label>
                                    <span id="modal_pending_amount">{{ Helper::formatNumber($modalPendingAmount) }} {{ $currencyCode }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submit_customized_order">{{ __('web.submit') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('web.close') }}</button>
            </div>
        </div>
    </div>
</div>

@include('layout/image_preview')
@endsection

@section('page_specific_js')
@include('common/sorting')
@include('layout/sweetalert')
@include('layout/datatable')
@include('layout/select2')
<script>
    window.customizedOrderCurrencyCode = @json($currencyCode);

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
                var existingCityId = $('#existing_custom_city_id').val() || $('.cust_city_id').val();
                getCustomCity(country, existingCityId ? existingCityId : null);
            }
        });

        // Request type change handler
        $('input[name="custom_req_type"]').change(function() {
            var val = $(this).val();
            if (val === 'quotation') {
                $('#modal_order_title').text('Edit Quotation');
                $('#advance_payment_section').hide();
                $('#shipping_section_wrapper').removeClass('col-md-6').addClass('col-md-12');
                $('#modal_advance_paid_row').hide();
                $('#modal_pending_amount_row').hide();
            } else {
                $('#modal_order_title').text('Edit Purchase Order');
                $('#advance_payment_section').show();
                $('#shipping_section_wrapper').removeClass('col-md-12').addClass('col-md-6');
                $('#modal_advance_paid_row').show();
                $('#modal_pending_amount_row').show();
            }
            calculateModalTotal();
        });

        $(document).on('change', '#customer_address_select', function () {
            let val = $(this).val();
            if (window.customerAddresses && window.customerAddresses[val]) {
                fillAddress(window.customerAddresses[val]);
            }
        });

        function fillAddress(addr) {
            $('input[name="cust_name"]').val((addr.first_name || '') + ' ' + (addr.last_name || '')).trigger('input');
            $('input[name="cust_block"]').val(addr.block || '').trigger('input');
            $('input[name="cust_street"]').val(addr.street || '').trigger('input');
            $('input[name="cust_house_number"]').val(addr.house_number || '').trigger('input');
            $('input[name="cust_floor"]').val(addr.floor || '').trigger('input');
            $('input[name="cust_address_detail"]').val(addr.address_detail || '').trigger('input');

            // Set country
            $('select[name="cust_country_id"]')
                .val(addr.country_id)
                .trigger('change');

            $('#existing_custom_city_id').val(addr.city_id || '');
            $('#existing_custom_city_name').val(addr.city || '');

            setTimeout(function () {
                getCustomCity(addr.country_id, addr.city_id);
            }, 300);
        }

        function getCustomCity(countryId, cityId) {
            if (isResettingAddress) return;
            if (!countryId) return;
            $.ajax({
                url: "{{ route('web.user.get_city') }}",
                method: "POST",
                data: {
                    country_id: countryId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    if (!data) {
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                        return;
                    }

                    $('.cust_city_id').html(data);
                    var tmp = $('<select>').html(data);
                    var opts = tmp.find('option');
                    if (opts.length > 1) {
                        $('.cust_city_list').show();
                        $('.cust_city_text').hide();

                        var didSelect = false;
                        if (cityId) {
                            var optByVal = $('.cust_city_id').find('option[value="' + cityId + '"]');
                            if (optByVal.length) {
                                optByVal.prop('selected', true);
                                didSelect = true;
                            }
                        }

                        if (!didSelect) {
                            var desiredText = $('input[name="cust_city"]').val();
                            if (desiredText && desiredText.trim() !== '') {
                                desiredText = desiredText.trim().toLowerCase();
                                $('.cust_city_id option').each(function() {
                                    if ($(this).text().trim().toLowerCase() === desiredText) {
                                        $(this).prop('selected', true);
                                        didSelect = true;
                                        return false;
                                    }
                                });
                            }
                        }

                        if (!didSelect) {
                            var firstVal = opts.eq(1).attr('value');
                            if (firstVal) $('.cust_city_id').val(firstVal);
                        }

                        var selectedText = $('.cust_city_id option:selected').text().trim();
                        if (selectedText) $('input[name="cust_city"]').val(selectedText).trigger('input');
                        $('.cust_city_id').trigger('change');
                    } else {
                        $('.cust_city_list').hide();
                        $('.cust_city_text').show();
                    }
                }
            });
        }

        $('select[name="cust_country_id"]').change(function() {
            var countryId = $(this).val();
            getCustomCity(countryId);
        });

        $(document).on('change', '.cust_city_id', function() {
            if (isResettingAddress) return;
            var txt = $(this).find('option:selected').text().trim();
            if (txt) {
                $('input[name="cust_city"]').val(txt);
            }
        });

        $(document).on('click', '.add-modal-custom-item', function() {
            addModalCustomItemRow();
        });

        function syncModalCustomItemButtons() {
            var $rows = $('#modal_custom_items_list .modal-custom-item-row');
            if (!$rows.length) return;

            $rows.each(function() {
                $(this).find('.add-modal-custom-item').remove();
                $(this).find('.remove-modal-custom-item').show();
            });

            var $lastIconBtn = $rows.last().find('.icon-btn').first();
            if ($lastIconBtn.length && $lastIconBtn.find('.add-modal-custom-item').length === 0) {
                $lastIconBtn.prepend(`
                    <span class="add-modal-custom-item mr-2"
                        style="cursor:pointer; font-size:16px; display:block;"
                        title="{{ __('web.add_item') }}">
                        <i class="fa fa-plus"></i>
                    </span>
                `);
            }
        }

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

        $(document).on('input', '.custom-price, .custom-qty', function() {
            var row = $(this).closest('.modal-custom-item-row');
            var qty = parseFloat(row.find('.custom-qty').val()) || 0;
            var price = parseFloat(row.find('.custom-price').val()) || 0;
            var total = qty * price;

            row.find('.custom-total').val(total.toFixed(3));
            calculateModalTotal();
        });

        function addModalCustomItemRow() {
            var deleteBtn = '<span class="remove-modal-custom-item" style="cursor:pointer; color:#d9534f; font-size: 16px; display:block;" title="{{ __('web.remove_item') }}"><i class="fa fa-trash"></i></span>';
            var addBtn = '<span class="add-modal-custom-item mr-2" style="cursor:pointer; font-size: 16px; display:block;" title="{{ __('web.add_item') }}"><i class="fa fa-plus"></i></span>';

            var html = `
                <div class="row modal-custom-item-row">
                    <div class="col-md-5 form-item pr-0 mb-2">
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
                    <div class="col-md-3 form-item pr-0 mb-2">
                        <label class="d-block mb-1 responsive-field-label">{{ __('web.product_title') }}</label>
                        <input type="text" name="custom_product_title[]" class="form-control form-control-sm"
                            placeholder="{{ __('web.enter_product_title') }}"
                            data-req-msg="{{ __('web.error-req-product-title') }}">
                    </div>
                    <div class="col-md-3 form-item pr-0 mb-2">
                        <label class="d-block mb-1 responsive-field-label">{{ __('web.description') }}</label>
                        <textarea name="custom_desc[]" class="form-control form-control-sm" placeholder="{{ __('web.enter_description') }}" data-req-msg="{{ __('web.error-req-description') }}" rows="2" style="resize: none;"></textarea>
                    </div>
                    <div class="col-md-1 form-item pr-0 mb-2 custom-item-actions-col">
                        <label class="d-block mb-1">&nbsp;</label>
                        <div class="icon-btn d-flex align-items-center" style="gap: 2rem; min-height: 38px; padding: 0.5rem;">
                            ${addBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                    <div class="col-md-12 custom-item-fields-row mt-2">
                        <div class="row">
                            <div class="col-md-3 form-item pr-0 mb-2">
                                <label class="responsive-field-label">{{ __('web.item_code') }}</label>
                                <input type="text" name="custom_item_code[]" class="form-control form-control-sm"
                                    placeholder="{{ __('web.enter_item_code') }}"
                                    data-req-msg="{{ __('web.error-req-item-code') }}">
                            </div>
                            <div class="col-md-2 form-item pr-0 mb-2">
                                <label class="responsive-field-label">{{ __('web.color') }}</label>
                                <input type="text" name="custom_color[]" class="form-control form-control-sm" placeholder="{{ __('web.enter_color') }}" data-req-msg="{{ __('web.error-req-color') }}">
                            </div>
                            <div class="col-md-2 form-item pr-0 mb-2">
                                <label class="responsive-field-label">{{ __('web.size') }}</label>
                                <input type="text" name="custom_size[]" class="form-control form-control-sm" placeholder="{{ __('web.enter_size') }}" data-req-msg="{{ __('web.error-req-size') }}">
                            </div>
                            <div class="col-md-2 form-item pr-0 mb-2">
                                <label class="responsive-field-label">{{ __('web.price') }}</label>
                                <input type="number" name="custom_price[]" class="form-control custom-price form-control-sm" placeholder="{{ __('web.enter_price') }}" step="0.001" data-req-msg="{{ __('web.error-req-price') }}">
                            </div>
                            <div class="col-md-1 form-item pr-0 mb-2">
                                <label class="responsive-field-label">{{ __('web.quantity') }}</label>
                                <input type="number" name="custom_qty[]" class="form-control custom-qty form-control-sm" placeholder="{{ __('web.enter_quantity') }}" min="1" data-req-msg="{{ __('web.error-req-quantity') }}">
                            </div>
                            <div class="col-md-2 form-item pr-0 mb-2">
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

            if (!file || !file.type || file.type.indexOf('image/') !== 0) return;

            var reader = new FileReader();
            reader.onload = function(e) {
                var imageHtml = '<img src="' + e.target.result + '" alt="Image Preview" style="width:48px; height:48px; object-fit:cover; border:1px solid #ddd; border-radius:4px; padding:2px; margin-top: 1rem;">';
                $preview.html(imageHtml);
            };
            reader.readAsDataURL(file);
        }

        $(document).on('change', '.custom-item-image-input', function() {
            renderCustomItemImagePreview($(this));
        });

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

            let isQuotation = $('input[name="custom_req_type"]:checked').val() === 'quotation';
            let advancePaid = isQuotation ? 0 : (parseFloat($('input[name="advance_payment"]').val()) || 0);
            let pendingAmount = isQuotation ? 0 : Math.max(0, grandTotal - advancePaid);

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
            '.custom-price, .custom-qty, input[name="custom_shipping"], input[name="custom_discount_val"], select[name="custom_discount_type"], input[name="advance_payment"]',
            calculateModalTotal
        );
        calculateModalTotal();

        // Submit Logic
        $('#submit_customized_order').click(function() {
            var formData = new FormData($('#customized_order_payload_form')[0]);
            formData.append('_token', "{{ csrf_token() }}");
            $.ajax({
                url: "{{ route('admin.order.temporary-orders.update', ['id' => $tempOrder->id]) }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Error occurred');
                        } else {
                            alert(response.message || 'Error occurred');
                        }
                    }
                },
                error: function(xhr) {
                    var errorMsg = "{{ __('web.error_something_went_wrong') }}";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });
    });
</script>
@endsection

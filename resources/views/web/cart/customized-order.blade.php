@if(isset(auth()->user()->user_type) && auth()->user()->user_type == "physical_store_user" && count($cart ?? []) > 0)
    {{-- Desktop: shown inside right column (cart-summary), hidden on mobile --}}
    <div class="sm-hidden" style="margin-bottom: 1.2rem">
        <div class="d-flex align-items-center justify-content-between">
            <!-- LEFT SIDE -->
            <div class="customized-order-toggle-row">
                <a href="javascript:void(0)" class="btn-add-customized-order d-inline-flex align-items-center" style="color: #111; text-decoration: none; font-size: inherit; gap: 6px; font-weight: 700; font-size: 16px !important; line-height: 1 !important;">
                    <span style="font-size: 18px; line-height: 1; font-weight: 700;">+</span>
                    <span>{{ __('web.add_customized_order') }}</span>
                </a>
            </div>

            <!-- RIGHT SIDE -->
            {{-- Edit icon commented out so cart always acts as fresh customized order entry --}}
            {{-- @if(session('customized_order_details'))
                <a href="javascript:void(0)"
                onclick="$('#customizedOrderModal').modal('show')"
                title="{{ __('web.edit') }}"
                class="customized-order-edit-link">
                    <i class="fas fa-edit customized-order-edit-icon"></i>
                </a>
            @endif --}}
        </div>
    </div>
@endif

<div class="discount">
    <div class="h-flex">
        <input class="coupon_code coupon_code_mobile" type="text" placeholder="{{__('web.coupon_placeholder')}}"
               value="<?php
               if (isset($appliedCoupon) && isset($appliedCoupon['user_id']))
                   echo isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';
               ?>">
        <button type="button" class="btn btn-primary coupon_apply coupon_apply_mobile">{{__('web.coupon_apply')}}</button>
    </div>
    <span class="cc-msg-fail failed" style="display: none"></span>
    @if(isset($appliedCoupon) && isset($appliedCoupon['user_id']))
    <span class="cc-msg-success success">{{__('web.coupon_applied')}}<a href="javascript:void(0)" class="remove-coupon-code"><i class="fa fa-times"></i></a></span>
    @else
    <span class="cc-msg-success success" style="display: none">{{__('web.coupon_applied')}}<a href="javascript:void(0)" class="remove-coupon-code"><i class="fa fa-times"></i></a></span>
    @endif
    @if(false)
    <span class="failed">{{__('web.coupon_invalid')}}</span>
    <span class="failed">{{__('web.coupon_already_redeemed')}}</span>
    <span class="success">{{__('web.coupon_applied')}} <a href="#"><i class="fa fa-times"></i></a></span>
    @endif
</div>
<div class="separator"></div>
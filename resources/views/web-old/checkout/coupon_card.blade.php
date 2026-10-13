<div class="coupons">
    <h4>{{__('web.coupon_code')}}</h4>
    <div class="coupon-input">
        <input class="coupon_code coupon_code_web" type="text" placeholder=""
               value="<?php
               if (isset($appliedCoupon))
                   echo isset($appliedCoupon['coupon_code']) ? $appliedCoupon['coupon_code'] : '';
               ?>">
        <button type="button" class="btn btn-primary apply coupon_apply coupon_apply_web">{{__('web.coupon_apply')}}</button>
    </div>
    <span class="danger cc-msg-fail" style="display: none"><i class="icon-close"></i>{{__('web.coupon_invalid')}}</span>

    @if(isset($appliedCoupon)  && count($appliedCoupon) > 0)
    <span class="cc-msg-success success"><i class="icon-tick"></i>{{__('web.coupon_applied')}}</span>
    @else
    <span class="cc-msg-success success" style="display: none"><i class="icon-tick"></i>{{__('web.coupon_applied')}}</span>
    @endif
    @if(false)
    <span class="danger">{{__('web.coupon_invalid')}}</span>
    <span class="danger">{{__('web.coupon_already_redeemed')}}</span>
    <span class="success">{{__('web.coupon_applied')}} <a href="#"><i class="fa fa-times"></i></a></span>
    @endif
   <!--<span class="success"><i class="icon-tick"></i>Gift Coupon applied</span>-->
</div>
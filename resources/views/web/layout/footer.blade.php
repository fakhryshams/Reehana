<div class="footer">
    <div class="footer-container">
        <div class="line line1"></div>
        <div class="line line2"></div>
        <div class="line line3"></div>
        <div class="footer-logo logo">
                <!-- <span>R</span>
                <span class="E">
                        <span></span>
                        <span></span>
                        <span></span>
                </span>
                <span>H</span>
                <span>A</span>
                <span>N</span>
                <span>A</span> -->
            <span class="logoR"></span>
            <span class="E">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="logoH"></span>
            <span class="logoA"></span>
            <span class="logoN"></span>
            <span class="logoA"></span>
        </div>
        <div class="links">
            <ul class="footer-links">
                <li><a href="{{route('web.privacy-policy')}}">{{__('web.privacy-policy')}}</a></li>
                <li><a href="{{route('web.shipping')}}">{{__('web.shipping')}}</a></li>
                <li><a href="{{route('web.about')}}">{{__('web.about_us')}}</a></li>
                <li><a href="{{route('web.contact')}}">{{__('web.contact_us')}}</a></li>
            </ul>
        </div>
        <div class="social-links">
            <ul class="share">
                @if(Helper::getInstagramUrl())
                <li><a target="_blank" href="{{Helper::getInstagramUrl()}}"><span class="instagram"></span></a></li>
                @endif
                @if(Helper::getInstagramUrl2())
                <li><a target="_blank" href="{{Helper::getInstagramUrl2()}}"><span class="instagram"></span></a></li>
                @endif
                @if(Helper::getWhatsappUrl())
                <li><a target="_blank"  href="{{Helper::getWhatsappUrl()}}"><span class="whatsapp"></span></a></li>
                @endif
            </ul>
        </div>
        <div class="copyright">
            <span>{!! Helper::getWebFooterCompanyName() !!}</span>
            <!-- <span class="gap"></span>
            <span>Designed &amp; Developed by <a href="http://www.uno-digital.com/" target="_blank">UNO Digital</a></span> -->
        </div>
    </div>
</div>
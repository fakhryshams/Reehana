<style>
    p{
        margin-bottom: 0px;
    }
</style>
@php
$setting       = Helper::getSettingById(21,1);
$footer   = isset($setting) ? trim($setting->setting_val) : "";
$company       = Helper::getSettingById(22,1);
$company   = isset($company) ? trim($company->setting_val) : "";
@endphp
<footer class="main-footer">
    <strong><span>&copy; {{__('web.copyright')}} <?php echo date("Y") ?> {{Helper::getAppName()}}</span>
    </strong> <strong class="float-right"><span class="no-print"> {!!$company!!}</span></strong>
</footer>
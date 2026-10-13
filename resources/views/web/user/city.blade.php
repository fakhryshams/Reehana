<?php
$language = Helper::getLanguage();
?>
<option selected="" disabled="" value="">{{__('web.select_city')}}</option>
@foreach($city as $reccord)
<option value="{{$reccord->id}}">{{$reccord->getCityDescription($language->id)?ucfirst($reccord->getCityDescription($language->id)->city_name) : ''}}</option>
@endforeach
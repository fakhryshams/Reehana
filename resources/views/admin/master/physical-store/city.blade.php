<?php
$language = Helper::getLanguage();
?>
<option selected="" disabled="" value="">{{__('admin.select_area')}}</option>
@foreach($city as $record)
<option value="{{$record->id}}">{{$record->getCityDescription($language->id) ? ucfirst($record->getCityDescription($language->id)->city_name) : ''}}</option>
@endforeach

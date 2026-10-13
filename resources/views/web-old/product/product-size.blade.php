@if(!$productSize)
<option value="0" selected="selected" disabled="">{{__('web.select')}}</option>
@endif
@foreach($productSize as $key=> $val)
<?php
$sizeDesc = $val->getSizeDescription();
?>
<option value="{{$val->id}}" {{$key == 0 ? 'selected':''}} >{{$sizeDesc->size_title}}</option>
@endforeach
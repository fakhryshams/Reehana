@if(!$productQuantity)
<option value="0" selected="selected" disabled="">{{__('web.select')}}</option>
@endif
@for($quantityLoop=1;$quantityLoop<=$productQuantity;$quantityLoop++)
<option value="{{$quantityLoop}}" {{$cartQuantity==$quantityLoop?'selected="selected"':''}}>{{$quantityLoop}}</option>
@endfor
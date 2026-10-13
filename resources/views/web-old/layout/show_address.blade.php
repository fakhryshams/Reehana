@if($record)
<?php
$language = Helper::getLanguage();
?>
@if($isBilling)
<?php
$city = $record->billing_city;
if ($record->billing_city_id)
{
    $city = $record->getUserBillingCity($language->id) ? $record->getUserBillingCity($language->id)->city_name : '';
}
?>
<p>
    {{__('web.email')}}: {{$record->billing_email}}<br>
    @if($record->getUserBillingCountry($language->id))
    {{__('web.country')}}: {{$record->getUserBillingCountry($language->id)->country_name}}{{$record->pin_code?" - ".$record->pin_code:''}}<br>
    @endif
    @if($city)
    {{__('web.city')}}: {{$city}}<br>
    @endif
    @if($record->billing_area)
    {{__('web.area')}}: {{$record->billing_area}}<br>
    @endif
    @if($record->billing_block)
    {{__('web.block')}}: {{$record->billing_block}}<br>
    @endif
    @if($record->billing_street)
    {{__('web.street')}}: {{$record->billing_street}}<br>
    @endif
    @if($record->billing_house_number)
    {{__('web.house')}}: {{$record->billing_house_number}}<br>
    @endif
    @if($record->billing_floor)
    {{__('web.floor')}}: {{$record->billing_floor}}<br>
    @endif
    {{__('web.contact')}}: {{$record->billing_phone}}<br>
    @if($record->billing_address_detail)
    {{__('web.address_detail')}}: {!! $record->billing_address_detail !!}
    @endif

</p>
@if(false)
<p>{{$record->email}}, {{$record->billing_phone}},<p>
<p>{{$record->billing_house_number?$record->billing_house_number.", ":''}}{{$record->billing_appartment?$record->billing_appartment.", ":''}}{{$record->billing_floor?$record->billing_floor.", ":''}}{{$record->billing_street?$record->billing_street.", ":''}}<br>
    {{$record->billing_area?$record->billing_area.", ":''}}{{$city?$city.", ":''}}{{$record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''}}{{$record->pin_code?" - ".$record->pin_code:''}}
    {!!$record->address_detail?"<br>".$record->address_detail:''!!}
</p>
@endif
@else
<?php
$city = $record->city;
$area = '';
if ($record->city_id)
{
    $area = $record->getUserCity($language->id) ? $record->getUserCity($language->id)->city_name : '';
}
?>
@if($page=='my_address')

<p>
    <em>{{__('web.contact')}}</em>: {{$record->phone}},<br>
    <em>{{__('web.email')}}</em>: {{$record->email}},<br>
    @if($record->getUserCountry($language->id))
    <em>{{__('web.country')}}</em>: {{$record->getUserCountry($language->id)->country_name}}{{$record->pin_code?" - ".$record->pin_code:''}}<br>
    @endif
    @if($city && $record->id != 43)
    <em>{{__('web.city')}} #</em>: {{$city}},<br>
    @endif
    @if($area)
    <em>{{__('web.area')}} #</em>: {{$area}},<br>
    @endif
    @if($record->block)
    <em>{{__('web.block')}} #</em>: {{$record->block}},<br>
    @endif
    @if($record->street)
    <em>{{__('web.street')}}</em>: {{$record->street}},<br>
    @endif
    @if($record->house_number)
    <em>{{__('web.house')}} #</em>: {{$record->house_number}},<br>
    @endif
    @if($record->floor)
    <em>{{__('web.floor')}}</em>: {{$record->floor}},<br>
    @endif
    @if($record->address_detail)
    <em>{{__('web.address_detail')}}</em>: {!! $record->address_detail !!},<br>
    @endif
	@if(!request()->routeIs('web.checkout.index'))
	@if($record->pickupstore==1 && session('cphone'))
		<em>Customer Phone </em>: {!! session('cphone') !!}<br>
	@endif
    @if($record->address_url)

    
    <?php
     $url = $record->address_url;
    if (!str_contains($record->address_url, 'http')) { 
   
    
 $url = 'https://'.$record->address_url;    
}
      ?>
    <em>{{__('web.shipping_location')}}</em>: <a href="{!! $url !!}" target="_blank" class="shippingLocationLink">Map Link</a> 
    @endif
	@endif

    @if(false)
    {{$record->email}},<br>
    {{$record->phone}}<br>
    {{$record->house_number?$record->house_number.", ":''}}{{$record->appartment?$record->appartment.", ":''}}{{$record->floor?$record->floor.", ":''}}{{$record->street?$record->street.", ":''}}<br>
    {{$record->area?$record->area.", ":''}}{{$city?$city.", ":''}}{{$record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''}}{{$record->pin_code?" - ".$record->pin_code:''}}
    {!!$record->address_detail?"<br>".$record->address_detail:''!!}
    @endif
</p>
@elseif($page == 'mail')

<p>
    {{__('web.contact')}}: {{$record->phone}},<br>
    {{__('web.email')}}: {{$record->email}},<br>
    @if($record->getUserCountry($language->id))
    {{__('web.country')}}: {{$record->getUserCountry($language->id)->country_name}}{{$record->pin_code?" - ".$record->pin_code:''}}<br>
    @endif
    @if($city)
    {{__('web.city')}} #: {{$city}},<br>
    @endif
    @if($area)
    {{__('web.area')}} #: {{$area}},<br>
    @endif
    @if($record->block)
    {{__('web.block')}} #: {{$record->block}},<br>
    @endif
    @if($record->street)
    {{__('web.street')}}: {{$record->street}},<br>
    @endif
    @if($record->house_number)
    {{__('web.house')}} #: {{$record->house_number}},<br>
    @endif
    @if($record->floor)
    {{__('web.floor')}}: {{$record->floor}},<br>
    @endif
    @if($record->address_detail)
    {{__('web.address_detail')}}: {!! $record->address_detail !!}<br>
    @endif
	
    @if($record->address_url)
    {{__('web.shipping_location')}}: <a href="{!! $record->address_url !!}" target="_blank" style="color: #caa42f;" >Map Link</a> 
    @endif

    @if(false)
    {{$record->email}},<br>
    {{$record->phone}}<br>
    {{$record->house_number?$record->house_number.", ":''}}{{$record->appartment?$record->appartment.", ":''}}{{$record->floor?$record->floor.", ":''}}{{$record->street?$record->street.", ":''}}<br>
    {{$record->area?$record->area.", ":''}}{{$city?$city.", ":''}}{{$record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''}}{{$record->pin_code?" - ".$record->pin_code:''}}
    {!!$record->address_detail?"<br>".$record->address_detail:''!!}
    @endif
</p>

@elseif($page == 'cart_review')
<p>{{$record->email}},<br> 
    {{$record->phone}}</p>
<p>{{$record->house_number?$record->house_number.", ":''}}{{$record->appartment?$record->appartment.", ":''}}{{$record->floor?$record->floor.", ":''}}{{$record->street?$record->street.", ":''}}
    {{$record->area?$record->area.", ":''}}<br>{{$city?$city.", ":''}}{{$record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''}}{{$record->pin_code?" - ".$record->pin_code:''}}
    {!!$record->address_detail?"<br>".$record->address_detail:''!!}
</p>
@elseif($page == 'email')
{!! __('web.email').': '.$record->email.'<br>' !!}
{!! __('web.contact').': '.$record->phone.'<br>'!!}
{!! $record->house_number? __('web.house_number').' #: '.$record->house_number.'<br>':''!!}
{!! $record->appartment? __('web.apartment').':'.$record->appartment.'<br>':'' !!}
{!! $record->floor?__('web.floor').' #: '.$record->floor.'<br>':'' !!}
{!! $record->street?__('web.street').': '.$record->street.'<br>':''!!}
{!! $record->area?__('web.area').': '.$record->area.'<br>':'' !!}
{!! $city? __('web.city').': '.$city.'<br>':'' !!}
{!! $record->getUserCountry($language->id) ? __('web.country').': '.$record->getUserCountry($language->id)->country_name .'<br>' : ''!!}
{!! $record->pin_code?__('web.pin_code').': '.$record->pin_code .'<br>' :''!!}
{!!$record->address_detail? __('web.address_detail').': '.$record->address_detail:''!!}
@endif
@endif
@endif

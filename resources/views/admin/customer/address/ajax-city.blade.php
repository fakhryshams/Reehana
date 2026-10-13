@if(count($city)>0)
<select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="city_id">
    @foreach($city as $city)
    <?php
    $cityName = $city->getCityDescription(1, "", 1);
    ?>
    <option value="{{$city->id}}" >{{$cityName?ucfirst($cityName->city_name):''}}</option>
    @endforeach
</select>
@error('city_id')
<div class="text-red">{{ $message }}</div>
@endif
<script>
    $('select[name=city_id]').select2({
        placeholder: "{{__('admin.select_city')}}",
    });
</script>
@else
<input type="text" name="city" placeholder="{{__('admin.customer_address_city')}}" class="form-control {{ $errors->has('city') ?'is-invalid':'' }}" value="{{old('city')}}">
@endif
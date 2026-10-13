<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.first_name')}}<b>*</b>:</label>
    <input type="text" data-type="alphabetsWSpaces" id="changeFirstname" name="billing_first_name" class="required" placeholder="{{__('web.first_name')}}*">
    <i class="error"></i>
    @error('billing_first_name')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.last_name')}}<b>*</b>:</label>
    <input type="text" data-type="alphabetsWSpaces" id="changeLastname" name="billing_last_name" class="required" placeholder="{{__('web.last_name')}}*">
    <i class="error"></i>
    @error('billing_last_name')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="clear"></div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.mobile')}}<b>*</b>:</label>
    <input type="text" data-type="numbers" id="changeMobile" name="billing_phone" class="required" placeholder="{{__('web.mobile')}}*" maxlength="{{config('custom_web.mobile_maxlength', 8)}}">
    <i class="error"></i>
    @error('billing_phone')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.country')}}<b>*</b>:</label>
    <input type="hidden" name="is_billing_city" class="is_billing_city" value="">
    <select class="select-component untouched required" id="billing_country_id" name="billing_country_id">
        <option selected="" disabled="">{{__('web.select_country')}}</option>
        <?php
        $language           = \App\Helpers\Helper::getLanguage();
        ?>
        @foreach($country as $record)
        <?php
        $countryDescription = $record->getCountryDescription($language->id)
        ?>
        <option {{old('country_id') == $record->id ? 'selected': '' }} value="{{$record->id}}">{{$countryDescription->country_name}}</option>
        @endforeach
    </select>
    <i class="error"></i>
    @error('billing_country_id')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="clear"></div>
<div class="form-item col-md-6 col-sm-12 billing_city_list" >
    <label class="City">{{__('web.city')}}<b>*</b>:</label>
    <select class="select-component untouched  billing_city_id" name="billing_city_id">
        <option selected="" disabled="">{{__('web.select_city')}}</option>
    </select>
    <i class="error"></i>
    @error('billing_city_id')
    <div class="text-red"> {{ $message }}</div> 
    @endif

</div>

<div class="form-item col-md-6 col-sm-12 billing_city_text">
    <label class="City">{{__('web.city')}}<b>*</b>:</label>
    <input type="text" data-type="others" id="changeArea"  name="city"  value="{{old('city')}}" class="r city" placeholder="{{__('web.city')}}">
    <i class="error"></i>
    @error('city')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.area')}}<b>*</b>:</label>
    <input type="text" data-type="others" id="changeArea" name="billing_area" class="required" placeholder="{{__('web.area')}}">
    <i class="error"></i>
    @error('billing_area')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="clear"></div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.street')}}<b>*</b>:</label>
    <input type="text" data-type="others" id="changeStreet" name="billing_street" class="required" placeholder="{{__('web.street')}}*">
    <i class="error"></i>
    @error('billing_street')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="form-item col-md-6 col-sm-12">
    <label class="required">{{__('web.house_number')}} #<b>*</b>:</label>
    <input type="text" data-type="others" id="changeHouse" name="billing_house_number" class="required" placeholder="{{__('web.house_number')}}*">
    <i class="error"></i>
    @error('billing_house_number')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="clear"></div>
<div class="form-item col-md-6 col-sm-12">
    <label>{{__('web.floor')}}:</label>
    <input type="text" data-type="others" id="changeFloor" name="billing_floor" placeholder="{{__('web.floor')}}">
    <i class="error"></i>
    @error('billing_floor')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
<div class="form-item col-md-6 col-sm-12">
    <label>{{__('web.apartment')}} #:</label>
    <input type="text" data-type="others" id="changeApartment" name="billing_appartment" placeholder="{{__('web.apartment')}} #">
    <i class="error"></i>
    @error('billing_appartment')
    <div class="text-red"> {{ $message }}</div> 
    @endif
</div>
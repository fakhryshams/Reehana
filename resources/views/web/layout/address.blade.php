<div id="address" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <span class="close" data-dismiss="modal"><span class="icon-plus"></span></span>
            <div class="modal-header">{{__('web.add_new_address')}}</div>
            <div class="modal-body fancy">
                <form id="address-form" action="{{route('web.user.save_address')}}"  method="post">
                    @csrf
                    <div class="row">
                        <div class="form-item col-md-6 col-sm-12">
                            <input type='hidden' name="address_id" id="address_id" value="{{old('address_id')}}"/>
                            <input type="text" data-type="alphabets" id="changeTitle" name="address_title" class="required address_title"  value="{{old('address_title')}}" placeholder="{{__('web.address_title')}}*">
                            <i class="error"></i>
                            @error('address_title')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="email" data-type="email" id="changeEmail" name="email" class="required user_email"  value="{{old('email')}}" placeholder="{{__('web.your_email')}}*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            @error('email')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="alphabetsWSpaces" id="changeFirstname" name="first_name" value="{{old('first_name')}}"  class="required first_name" placeholder="{{__('web.first_name')}}*">
                            <i class="error"></i>
                            @error('first_name')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="alphabetsWSpaces" id="changeLastname" name="last_name" value="{{old('last_name')}}"  class="required last_name" placeholder="{{__('web.last_name')}}*">
                            <i class="error"></i>
                            @error('last_name')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="hidden" name="is_city" class="is_city" value="">
                            <select class="select-component untouched required" id="country_id" name="country_id">
                                <option selected="" disabled="">{{__('web.select_country')}}</option>
                                <?php
                                $language           = Helper::getLanguage();
                                $currency           = Helper::getDefaultCurrency();
                                $currencycode       = $currency ? $currency->code : config('custom.currency');
                                ?>
                                @foreach($country as $record)
                                <?php
                                $countryDescription = $record->getCountryDescription($language->id)
                                ?>
                                <option {{old('country_id') == $record->id ? 'selected': '' }} value="{{$record->id}}">{{ucfirst($countryDescription->country_name)}}</option>
                                @endforeach

                            </select>
                            @error('country_id')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_list">
                            <select class="select-component untouched  city_id required" name="city_id">
                                <option selected="" disabled="">{{__('web.select_city')}}</option>
                            </select>
                            @error('city_id')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_text">
                            <input type="text" data-type="others" id="city_name"  name="city"  value="{{old('city')}}" class="required city" placeholder="{{__('web.city')}}*">
                            <i class="error"></i>
                            @error('city')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="others" id="changeArea"  name="area"  value="{{old('area')}}" class="required area" placeholder="{{__('web.area')}}*">
                            <i class="error"></i>
                            @error('area')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="others" id="changeStreet" name="street" value="{{old('street')}}" class="required street" placeholder="{{__('web.street')}}*">
                            <i class="error"></i>
                            @error('street')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="others" id="changeHouse" name="house_number" value="{{old('house_number')}}" class="required house_number" placeholder="{{__('web.house_number')}}*">
                            <i class="error"></i>
                            @error('house_number')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="others" id="changeFloor"  class="floor" name="floor" value="{{old('floor')}}"  placeholder="{{__('web.floor')}}">
                            <i class="error"></i>
                            @error('floor')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="others" id="changeApartment" class="appartment" name="apartment" value="{{old('apartment')}}" placeholder="{{__('web.apartment')}} #">
                            <i class="error"></i>
                            @error('apartment')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <input type="text" data-type="numbers" id="changeMobile" name="phone" value="{{old('phone')}}"  class="required phone " placeholder="{{__('web.phone')}}*" maxlength="{{config('custom_web.mobile_maxlength', 16)}}" minlength="{{config('custom_web.mobile_minlength', 8)}}">
                            <i class="error"></i>
                            @error('phone')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <textarea type="text" data-type="others" id="changeDetail" name="address_detail" class="address_detail" placeholder="{{__('web.address_detail')}}">{{old('address_detail')}}</textarea>
                            <i class="error"></i>
                            @error('address_detail')
                            <div class="text-red"> {{ $message }}</div> 
                            @endif
                        </div>
                        <input type="hidden" name="check_billing_address" id="check_billing_address" value="3">

                        <!--                        <div class="form-item col-md-6 col-sm-12">
                                                    <input type="text" data-type="others" id="changeDetail" name="pin_code" class="pin_code" value="{{old('pin_code')}}" name="detail" placeholder="{{__('web.pin_code')}}">
                                                    <i class="error"></i>
                                                    @error('pin_code')
                                                    <div class="text-red"> {{ $message }}</div> 
                                                    @endif
                                                </div>-->
                    </div>
                    <div class="separator"></div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary save-address">{{__('web.save_address')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('page_specific_js')
<script>
    $(document).ready(function ()
    {
        $('#address_id').val("{{old('address_id')}}");

        $(".modal-header").text("{{__('web.add_new_address')}}");
        $(".save-address").text("{{__('web.save_address')}}");

        $('.success_msg').hide();
        $('.error_msg').hide();
        $('.is_city').val(0);
        $('.city_text').hide();
        var oldCity = '';
<?php
if (old('city_id'))
{
    ?>
            oldCity = "{{old('city_id')}}";
<?php } ?>
<?php
if (old('country_id'))
{
    ?>
            getCity();
<?php } ?>
<?php
if ($errors->has('city_id'))
{
    ?>
            $('.city_text').hide();
            $('.city_list').show();
<?php } ?>
<?php
if ($errors->has('city'))
{
    ?>
            $('.city_text').show();
            $('.city_list').hide();
<?php } ?>
<?php
if ($errors->any())
{
    ?>
            $('#address').modal('show');
            if ($('#address_id').val())
            {
                $(".modal-header").text("{{__('web.update_address')}}");
                $(".save-address").text("{{__('web.update_address')}}");
            }
<?php } ?>
        $("#country_id").change(function ()
        {
            getCity();
        });
        function getCity()
        {
            $.ajax({
                type: 'POST',
                url: "{{route('web.user.get_city')}}",
                data: {
                    'country_id': $("#country_id").val(),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data)
                    {
                        $('.city_id').html('');
                        $('.city_id').append(data);
                        $('.city_text').hide();
                        $('.city_text').val('');
                        $('.city_list').show();
                        if (oldCity)
                        {
                            $('.city_id').val(oldCity);
                        }
                        $('.is_city').val(0);

                        $("input[name=city]").removeClass('required');
                        $(".city_text").removeClass('mandatory');
                    } else
                    {
                        $("input[name=city]").val("");
                        $("input[name=city]").addClass('required');
                        $('.is_city').val(1);
                        $('.city_id').html('');
                        $('.city_text').show();
                        $('.city_list').hide();
                    }
                }
            });
        }
        $(".edit_address").click(function ()
        {
            $('#address').find(".mandatory").removeClass("mandatory");
            $('#address').find(".invalid").removeClass("invalid");

            $(".modal-header").text("{{__('web.update_address')}}");
            $(".save-address").text("{{__('web.update_address')}}");
            $.ajax({
                type: 'POST',
                url: "{{route('web.user.get_address_detail')}}",
                data: {
                    'address_id': $(this).attr('data-edit'),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data)
                    {
                        $('#address_id').val(data.id);
                        $('.address_title').val(data.address_title);
                        $('.user_email').val(data.email);
                        $('.first_name').val(data.first_name);
                        $('.last_name').val(data.last_name);
                        $('.area').val(data.area);
                        $('.street').val(data.street);
                        $('.house_number').val(data.house_number);
                        $('.floor').val(data.floor);
                        $('.appartment').val(data.appartment);
                        $('.city').val(data.city);
                        $('.phone').val(data.phone);
                        $('.address_detail').val(data.address_detail);
                        $('.pin_code').val(data.pin_code);
                        $('#country_id').val(data.country_id);
                        if (data.city_id)
                        {
                            oldCity = data.city_id;
                            getCity();
                        } else
                        {
                            $('.is_city').val(1);
                            $('.city_id').html('');
                            $('.city_text').show();
                            $('.city_list').hide();
                            $("input[name=city]").removeClass('required');
                            $(".city_text").removeClass('mandatory');
                            $("input[name=city]").addClass('required');
                        }
                        $('#address').modal('show');
                    } else
                    {
                    }
                }
            });
        });
        $(".add_new_address").click(function ()
        {
            $('#address').find(".mandatory").removeClass("mandatory");
            $('#address').find(".invalid").removeClass("invalid");

            $('#address-form').find('input:text').val('');
            $('#address-form').each(function ()
            {
                this.reset();
            });
            $('#address_id').val("");
            $(".modal-header").text("{{__('web.add_new_address')}}");
            $(".save-address").text("{{__('web.save_address')}}");

            $('#address').modal('show');
        });

        $('input[type=radio][name=is_default]').change(function ()
        {
            window.location.href = $(this).attr('data-default');
        });

    });
</script>
@endsection
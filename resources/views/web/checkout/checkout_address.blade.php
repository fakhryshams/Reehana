<div id="address" class="modal fade" role="dialog">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h1 class="address_title">Add Address</h1>
                <form id="address-form" action=""  method="post">
                    @csrf
                    <div class="row">
                        <div class="form-item col-md-2 col-sm-12">
                            <label>{{__('web.address_title')}} </label>
                            <input type='hidden' name="address_id" id="address_id" value="{{old('address_id')}}"/>
                            <input type="text" data-type="ss" id="changeTitle" maxlength="12" name="address_title" class="required address_title"  value="{{old('address_title')}}" placeholder="{{__('web.address_title')}}*" maxlength="20">
                            <i class="error"></i>
                            @error('address_title')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="form-item col-md-4 col-sm-12">
                            <label>{{__('web.mobile_no')}}</label>
                            <input pattern="^(?=.*[0-9])[- +()0-9]+$" type="text" data-type="alphanumeric" id="changeMobile" name="phone" value="{{old('phone')}}"  class="required phone number-input" placeholder="{{__('web.phone')}}*" minlength="{{config('custom_web.mobile_minlength',8)}}" maxlength="{{config('custom_web.mobile_maxlength',16)}}">
                            <i class="error"></i>
                            @error('phone')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.email')}}</label>
                            <input type="email" data-type="email" id="changeEmail" name="email" class="required user_email"  value="{{old('email')}}" placeholder="{{__('web.your_email')}}*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            @error('email')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.first_name')}}</label>
                            <input type="text" data-type="alphabetsWSpaces" id="changeFirstname" name="first_name" value="{{old('first_name')}}"  class="required first_name" placeholder="{{__('web.first_name')}}*">
                            <i class="error"></i>
                            @error('first_name')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.last_name')}}</label>
                            <input type="text" data-type="alphabetsWSpaces" id="changeLastname" name="last_name" value="{{old('last_name')}}"  class="required last_name" placeholder="{{__('web.last_name')}}*">
                            <i class="error"></i>
                            @error('last_name')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.country')}}</label>
                            <input type="hidden" name="is_city" class="is_city" value="">
                            <select class="select-component untouched required" id="country_id" name="country_id">
                                <option selected="" disabled="" value="">{{__('web.select_country')}}</option>
                                <?php
$language = Helper::getLanguage();
?>
                                @foreach($country as $record)
                                <?php
$countryDescription = $record->getCountryDescription($language->id)
?>
                                <option data-code="{{$record->country_code}}" {{old('country_id') == $record->id ? 'selected': '' }} value="{{$record->id}}">{{ucfirst($countryDescription->country_name)}}</option>
                                @endforeach
                            </select>
                            @error('country_id')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_list">
                            <label>{{__('web.area')}}</label>
                            <select class="select-component untouched  city_id required" name="city_id">
                                <option selected="" disabled=""  value="">{{__('web.select_area')}}</option>
                            </select>
                            <i class="error"></i>
                            @error('city_id')
                            <label class="text-red label-w-100"> {{ $message }}</label>
                            @endif
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_text">
                            <label>{{__('web.city')}}</label>
                            <input type="text" data-type="others" id="city_name"  name="city"  value="{{old('city')}}" class="required city" placeholder="{{__('web.city')}}*">
                            <i class="error"></i>
                            @error('city')
                            <label class="text-red"> {{ $message }}</label>
                            @endif
                        </div>
                         <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.state')}}</label>
                            <input type="text" data-type="others" id="state" name="state" value="{{old('state')}}" class="required state" placeholder="{{__('web.state')}}*">
                            <i class="error"></i>
                            @error('state')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                         <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.pin_code')}}</label>
                            <input type="text" data-type="others" id="pin_code" name="pin_code" value="{{old('pin_code')}}" class="required pin_code" placeholder="{{__('web.pin_code')}}*">
                            <i class="error"></i>
                            @error('pin_code')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="clear"></div>
                        <!--                        <div class="form-item col-md-6 col-sm-12">
                                                    <label>{{__('web.area')}}</label>
                                                    <input type="text" data-type="others" id="changeArea"  name="area"  value="{{old('area')}}" class="required area" placeholder="{{__('web.area')}}*">
                                                    <i class="error"></i>
                                                    @error('area')
                                                    <div class="text-red"> {{ $message }}</div>
                                                    @endif
                                                </div>-->
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.block')}}</label>
                            <input type="text" data-type="others" id="addBlock" name="block" value="{{old('block')}}" class="required block" placeholder="{{__('web.block')}}*">
                            <i class="error"></i>
                            @error('block')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>

                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.street')}}</label>
                            <input type="text" data-type="others" id="changeStreet" name="street" value="{{old('street')}}" class="required street" placeholder="{{__('web.street')}}*">
                            <i class="error"></i>
                            @error('street')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>

                        @if(false)
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.apartment')}}</label>
                            <input type="text" data-type="others" id="changeApartment" class="appartment" name="apartment" value="{{old('apartment')}}" placeholder="{{__('web.apartment')}}">
                            <i class="error"></i>
                            @error('apartment')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        @endif

                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.house')}} #</label>
                            <input type="text" data-type="others" id="changeHouse" name="house_number" value="{{old('house_number')}}" class="required house_number" placeholder="{{__('web.house_number')}}*">
                            <i class="error"></i>
                            @error('house_number')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>

                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.floor')}}</label>
                            <input type="text" data-type="others" id="changeFloor"  class="floor" name="floor" value="{{old('floor')}}"  placeholder="{{__('web.floor')}}">
                            <i class="error"></i>
                            @error('floor')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="clear"></div>



                        <div class="form-item col-md-12 col-sm-12">
                            <label>{{__('web.address_detail')}}</label>
                            <input type="text" data-type="others" id="addDetail" name="address_detail"  class="address_detail"  placeholder="{{__('web.address_detail')}}" value="{{old('address_detail')}}">
                            <i class="error"></i>
                            @error('address_detail')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-12 col-sm-12">
                            <label>{{__('web.address_url')}}</label>
                            <input type="text" data-type="url" id="addDetail" name="address_url" id="userLocation"  class="address_url"  placeholder="{{__('web.address_url')}}" value="{{old('address_detail')}}">
                            <i class="error"></i>
                            @error('address_url')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>

                        @if(false)
                        <div class="form-item col-md-6 col-sm-12">
                            <label>{{__('web.apartment')}}</label>
                            <input maxlength="{{config('custom_web.apartment_number_maxlength',6)}}"  type="text" data-type="others" id="changeApartment" class="appartment" name="apartment" value="{{old('apartment')}}" placeholder="{{__('web.apartment')}} #">
                            <i class="error"></i>
                            @error('apartment')
                            <div class="text-red"> {{ $message }}</div>
                            @endif
                        </div>
                        @endif
                        <!--                        <div class="form-item col-md-6 col-sm-12">
                                                    <input type="text" data-type="others" id="changeDetail" name="pin_code" class="pin_code" value="{{old('pin_code')}}" name="detail" placeholder="{{__('web.pin_code')}}">
                                                    <i class="error"></i>
                                                    @error('pin_code')
                                                    <div class="text-red"> {{ $message }}</div>
                                                    @endif
                                                </div>-->
                    </div>
                    @if( !request()->routeIs('web.user.my_address'))
                    <div class="separator"></div>
                    <div class="h-flex billing">
                        <div class="checkbox">
                            <?php
$checked = 'checked ';
if (old('check_billing_address') == 1) {
	$checked = 'checked';
}
if (old('check_billing_address') == 2) {
	$checked = '';
}
?>
                            <input type="checkbox"  value="remember"  name="is_billing_address" id="billing-form" {{$checked}}  >
                            <label>{{__('web.same_as_delivery')}}</label>
                        </div>
                        <input type="hidden"  name="check_billing_address" id="check_billing_address"  value="1" >
                    </div>
                    <div class="row billing-form-data">
                        <div class="h-flex" id="billing-address">
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.mobile_no')}}<b>*</b>:</label>
                                <input pattern="^(?=.*[0-9])[- +()0-9]+$" type="text" data-type="alphanumeric" id="bchangeMobile" name="billing_phone" class="bill-address billing_phone phone billing-required number-input" placeholder="{{__('web.phone')}}*" value="{{old('billing_phone')}}" minlength="{{config('custom_web.mobile_minlength',8)}}" maxlength="{{config('custom_web.mobile_maxlength',12)}}">
                                <i class="error"></i>
                                @error('billing_phone')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required">Email</label>
                                <input type="email" data-type="email" id="addEmail" name="pEmail" class="required" value="{{old('pEmail')}}" placeholder="youremail@email.com" pattern="^(([-wd]+)(.[-wd]+)*@([-wd]+)(.[-wd]+)*(.([a-zA-Z]{2,5}|[d]{1,3})){1,2})$">
                                <i class="error"></i>
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required">{{__('web.first_name')}}<b>*</b>:</label>
                                <input type="text" data-type="alphabetsWSpaces" id="bchangeFirstname" name="billing_first_name" class="bill-address billing_first_name billing-required" placeholder="{{__('web.first_name')}}*"  value="{{old('billing_first_name')}}">
                                <i class="error"></i>
                                @error('billing_first_name')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.last_name')}}<b>*</b>:</label>
                                <input type="text" data-type="alphabetsWSpaces" id="bchangeLastname" name="billing_last_name" class="bill-address billing_last_name billing-required" placeholder="{{__('web.last_name')}}*"  value="{{old('billing_last_name')}}">
                                <i class="error"></i>
                                @error('billing_last_name')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="clear left_lable"></div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.country')}}<b>*</b>:</label>
                                <input type="hidden" name="is_billing_city" class="is_billing_city" value="">
                                <select class="select-component untouched billing-required" id="billing_country_id" name="billing_country_id">
                                    <option selected="" disabled=""  value="">{{__('web.select_country')}}</option>
                                    <?php
$language = Helper::getLanguage();
?>
                                    @foreach($country as $record)
                                    <?php
$countryDescription = $record->getCountryDescription($language->id)
?>
                                    <option data-code="{{$record->country_code}}" {{old('billing_country_id') == $record->id ? 'selected': '' }} value="{{$record->id}}">{{ucfirst($countryDescription->country_name)}}</option>
                                    @endforeach
                                </select>
                                <i class="error"></i>
                                @error('billing_country_id')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="form-item col-md-6 col-sm-12 billing_city_list">
                                <label class="City left_lable">{{__('web.city')}}<b>*</b>:</label>
                                <select class="select-component untouched  billing_city_id billing-required" name="billing_city_id">
                                    <option selected="" disabled="" value="">{{__('web.select_area')}}</option>
                                </select>
                                @error('billing_city_id')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                                <i class="error"></i>
                            </div>
                            <div class="form-item col-md-6 col-sm-12 billing_city_text">
                                <label class="City left_lable">{{__('web.city')}}<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangecity"  name="billing_city"  value="{{old('billing_city')}}" class="r city billing_city billing-required" placeholder="{{__('web.city')}}*">
                                <i class="error"></i>
                                @error('city')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                             <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('State')}}<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeState" name="billing_state" value="{{old('billing_state')}}" class="bill-address billing_state billing-required" placeholder="{{__('State')}}">
                                <i class="error"></i>
                                @error('billing_state')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                             <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.pin_code')}}<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangePinCode" name="billing_pin_code" value="{{old('billing_pin_code')}}" class="bill-address billing_pin_code billing-required" placeholder="{{__('web.pin_code')}}">
                                <i class="error"></i>
                                @error('billing_pin_code')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="clear"></div>
                            <!--                            <div class="form-item col-md-6 col-sm-12">
                                                            <label class="required left_lable">{{__('web.area')}}<b>*</b>:</label>
                                                            <input type="text" data-type="others" id="bchangeArea" name="billing_area" value="{{old('billing_area')}}" class="bill-address billing_area billing-required" placeholder="{{__('web.area')}}">
                                                            <i class="error"></i>
                                                            @error('billing_area')
                                                            <label class="text-red"> {{ $message }}</label>
                                                            @endif
                                                        </div>-->
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.block')}}<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeBlock" name="billing_block" value="{{old('billing_block')}}" class="bill-address billing_block billing-required" placeholder="{{__('web.block')}}">
                                <i class="error"></i>
                                @error('billing_block')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>

                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.street')}}<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeStreet" name="billing_street" class="bill-address billing_street billing-required" placeholder="{{__('web.street')}}*" value="{{old('billing_street')}}">
                                <i class="error"></i>
                                @error('billing_street')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="clear"></div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable">{{__('web.house')}} #<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeHouse" name="billing_house_number" value="{{old('billing_house_number')}}" class="bill-address billing_house_number billing-required" placeholder="{{__('web.house_number')}}*">
                                <i class="error"></i>
                                @error('billing_house_number')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>

                            <div class="form-item col-md-6 col-sm-12">
                                <label class="left_lable">{{__('web.floor')}}:</label>
                                <input type="text" data-type="others" id="bchangeFloor" class="billing_floor" name="billing_floor" value="{{old('billing_floor')}}" placeholder="{{__('web.floor')}}">
                                <i class="error"></i>
                                @error('billing_floor')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            <div class="clear"></div>

                            @if(false)
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="left_lable">{{__('web.apartment')}} #:</label>
                                <input maxlength="{{config('custom_web.apartment_number_maxlength',6)}}"  type="text" data-type="others" id="bchangeApartment" name="billing_appartment" class="billing_appartment" value="{{old('billing_appartment')}}" placeholder="{{__('web.apartment')}} #">
                                <i class="error"></i>
                                @error('billing_appartment')
                                <label class="text-red"> {{ $message }}</label>
                                @endif
                            </div>
                            @endif
                            <div class="clear"></div>
                            <div class="form-item col-md-12 col-sm-12">
                                <label>{{__('web.address_detail')}}</label>
                                <input type="text" data-type="others" id="changeDetail" name="billing_address_detail"  class="billing_address_detail"  placeholder="{{__('web.address_detail')}}" value="{{old('billing_address_detail')}}">

                                <i class="error"></i>
                                @error('billing_address_detail')
                                <div class="text-red"> {{ $message }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="separator"></div>
                    <input type="hidden" name="is_myaddress" value="0">

                    @else

                    <input type="hidden" name="is_myaddress" value="1">

                    @endif
                    <div class="alert alert-success alert-success-save" style="display: none" >
                    </div>

                    <div class="alert alert-danger alert-danger-save" style="display: none" >

                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary save-address">{{__('web.save_address')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $currency = Helper::getDefaultCurrency();
?>
@section('page_specific_js')
<script>
    var currency = "{{$currency->code}}"
    $(document).ready(function ()
    {
         function toggleFields(countryCode, isBilling = false, isEdit = false) {
             var prefix = isBilling ? 'billing_' : '';
             var block = $('.' + prefix + 'block');
             var street = $('.' + prefix + 'street');
             var state = $('.' + prefix + 'state');
             var pinCode = $('.' + prefix + 'pin_code');
             
             if((countryCode === 'KW' || countryCode === 'kuwait') && !isEdit) { // Check both code and potential text value just in case
                block.addClass('required').closest('.form-item').addClass('mandatory');
                street.addClass('required').closest('.form-item').addClass('mandatory');
                // state.addClass('required').closest('.form-item').addClass('mandatory');
                // pinCode.addClass('required').closest('.form-item').addClass('mandatory');
             } else {
                block.removeClass('required').closest('.form-item').removeClass('mandatory');
                street.removeClass('required').closest('.form-item').removeClass('mandatory');
                // state.removeClass('required').closest('.form-item').removeClass('mandatory');
                // pinCode.removeClass('required').closest('.form-item').removeClass('mandatory');
             }
         }

         $('#country_id').change(function(){
              var code = $(this).find(':selected').data('code');
              toggleFields(code, false);
         });

         $('#billing_country_id').change(function(){
              var code = $(this).find(':selected').data('code');
              toggleFields(code, true);
         });

         $(document).on('change', '#country_id', function(){
              var code = $(this).find(':selected').data('code');
              toggleFields(code, false); 
         })

         // Trigger on load if value selected
         if($('#country_id').val()){
             var code = $('#country_id').find(':selected').data('code'); // This might be undefined if loaded dynamically via ajax? But here options are static foreach
             toggleFields(code, false);
         }
         if($('#billing_country_id').val()){
             var code = $('#billing_country_id').find(':selected').data('code');
             toggleFields(code, true);
         }

        $(".remove_address").click(function (e)
        {
            $("#preloader").addClass("show");
            var address_id = $(this).attr('data-delete');
            $.ajax({
                type: 'POST',
                url: "{{route('web.user.remove_address')}}",
                data: {
                    'id': address_id,
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {


                    $("#preloader").removeClass("show");
                    $("html").scrollTop(100);
                    if (data.status)
                    {
                        $('.alert').hide();
                        $('.my_address_' + address_id).hide();
                        $('.alert-success-remove').show();
                        $('.alert-success-remove').text(data.message);
                    } else
                    {
                        $('.alert').hide();
                        $('.alert-danger-remove').show();
                        $('.alert-danger-remove').text(data.message);
                    }
                },
                error: function (xhr)
                {

                }
            });
        });
        $(".show_intl_box").click(function ()
        {
            $("#preloader").stop(true, true).removeClass("show").fadeOut("slow");
            var finalTotalValue = $("input[name=sub_total_price_detail]").val();
            var modalText = "{{__('web.intl_ship_message_empty_cart')}}";
            if (parseFloat(finalTotalValue) > 0)
            {
                modalText = "{{__('web.intl_ship_message')}}";
            }
            cart_review_intl_box(modalText);
        });

        $('#address_id').val("{{old('address_id')}}");
        $(".modal-header").text("{{__('web.add_address')}}");
        $(".save-address").text("{{__('web.save_address')}}");

        $('.success_msg').hide();
        $('.error_msg').hide();
        $('.is_city').val(0);
        $('.is_billing_city').val(2);
        $('.city_text').hide();
        $('.billing_city_text').hide();
        var oldCity = '';
        var oldbillingCity = '';
        billingAddress($("#billing-form"));
<?php
if (old('city_id')) {
	?>
            oldCity = "{{old('city_id')}}";
    <?php
}
?>
<?php
if (old('billing_city_id')) {
	?>
            oldbillingCity = "{{old('billing_city_id')}}";
    <?php
}
?>

<?php
if (old('country_id')) {
	?>
            getCity();
<?php }?>
<?php
if (old('billing_country_id')) {
	?>
            getCity('billing');
<?php }?>
<?php
if ($errors->has('city_id')) {
	?>
            $('.city_text').hide();
            $('.city_list').show();
<?php }?>
<?php
if ($errors->has('city')) {
	?>
            $('.city_text').show();
            $('.city_list').hide();
<?php }?>
<?php
if ($errors->has('billing_city_id')) {
	?>
            $('.billing_city_text').hide();
            $('.billing_city_list').show();
<?php }?>
<?php
if ($errors->has('billing_city')) {
	?>
            $('.billing_city_text').show();
            $('.billing_city_list').hide();
<?php }?>
<?php
if ($errors->any()) {
	?>
            $('#address').modal('show');
            if ($('#address_id').val())
            {
                $(".modal-header").text("{{__('web.update_address')}}");
                $(".save-address").text("{{__('web.update_address')}}");
            }
<?php }?>
        $("#country_id").change(function ()
        {
            getCity();
        });

        function changeBillingCountry()
        {
            $("#billing_country_id").change(function ()
            {
                getCity('billing', $(this).val());
            });
        }
        function getCity(type = '', countryId = '', cityId = '')
        {
            $.ajax({
                type: 'POST',
                url: "{{route('web.user.get_city')}}",
                data: {
                    'country_id': countryId ? countryId : $("#country_id").val(),
                    '_token': '{{ csrf_token()}}'
                },
                success: (data) => {
                    if (data)
                    {
                        if (type == 'billing')
                        {

                            $('.billing_city_id').html('');
                            $('.billing_city_id').append(data);
                            $('.billing_city_text').hide();
                            $('.billing_city_text').val('');
                            $('.billing_city_list').show();
                            if (oldbillingCity)
                            {
                                $('.billing_city_id').val(oldbillingCity);
                            }
                            $('.is_billing_city').val(0);

                            $("input[name=billing_city]").removeClass('required');
                            $(".billing_city_text").removeClass('mandatory');

                            if (cityId)
                            {
                                $(".billing_city_id").val(cityId);
                            }
                        } else
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
                        }
                    } else
                    {
                        if (type == 'billing')
                        {
                            $("input[name=billing_city]").val("");
                            $("input[name=billing_city]").addClass('required');
                            $('.is_billing_city').val(1);
                            $('.billing_city_id').html('');
                            $('.billing_city_text').show();
                            $('.billing_city_list').hide();
                        } else
                        {
                            $("input[name=city]").val("");
                            $("input[name=city]").addClass('required');
                            $("input[name=city]").val("{{old('city')}}");
                            $('.is_city').val(1);
                            $('.city_id').html('');
                            $('.city_text').show();
                            $('.city_list').hide();
                        }
                    }
                }
            });
        }
        $(".edit_address").click(function ()
        {

            $('.alert').hide();
            $('#address').find(".mandatory").removeClass("mandatory");
            $('#address').find(".invalid").removeClass("invalid");

            $(".modal-header").text("{{__('web.update_address')}}");
            $(".save-address").text("{{__('web.update_address')}}");
            if ($(this).attr('data-page') == 'cart_review')
            {
                $('.address_title').html('Update Address')

            } else
            {
                $('.address_title').html('Add Address')
            }



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
                        toggleFields(data?.country_info?.country_code, false, true);
                        $('#address_id').val(data.id);
                        $('.address_title').val(data.address_title);
                        $('.user_email').val(data.email);
                        $('.first_name').val(data.first_name);
                        $('.last_name').val(data.last_name);
                        $('.area').val(data.area);
                        $('.block').val(data.block);
                        $('.street').val(data.street);
                        $('.house_number').val(data.house_number);
                        $('.floor').val(data.floor);
                        $('.appartment').val(data.appartment);
                        $('.city').val(data.city);
                        $('.state').val(data?.state_or_province_code ?? '');
                        $('.phone').val(data.phone);
                        $('.address_detail').val(data.address_detail);
                        $('.address_url').val(data.address_url);
                        $('.pin_code').val(data.pin_code);
                        $('#country_id').val(data.country_id);
                        var code = $('#country_id').find(':selected').data('code');
                        toggleFields(code, false, true);
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

                        if (data.is_billing_address == 1)
                        {
                            $("input[name=is_billing_address]").prop('checked', true);
                            $("input[name=check_billing_address]").val(1);
                            $(".billing-form-data input").val("");
                            $(".billing-form-data select").val("");
                            $("[name=billing_country_id]").val($("[name=billing_country_id] option:first").val());
                            $("[name=billing_city_id]").val($("[name=billing_city_id] option:first").val());
                        } else
                        {
                            $("input[name=is_billing_address]").prop('checked', false);
                            $("input[name=check_billing_address]").val(2);
                            $("input[name=billing_first_name]").val(data.billing_first_name);
                            $("input[name=billing_last_name]").val(data.billing_last_name);
                            $("input[name=billing_area]").val(data.billing_area);
                            $("input[name=billing_block]").val(data.billing_block);
                            $("input[name=billing_street]").val(data.billing_street);
                            $("input[name=billing_phone]").val(data.billing_phone);
                            $("input[name=billing_house_number]").val(data.billing_house_number);
                            $("input[name=billing_floor]").val(data.billing_floor);
                            $("input[name=billing_appartment]").val(data.billing_appartment);
                            $("[name=billing_country_id]").val(data.billing_country_id);
                            var code = $("#billing_country_id").find(':selected').data('code');
                            toggleFields(code, true, true);
                            $("[name=billing_city]").val(data.billing_city);
                            $("[name=billing_address_detail]").val(data.billing_address_detail);
                            $("[name=pEmail]").val(data.billing_email);

                            if (data.billing_city_id)
                            {
                                getCity('billing', data.billing_country_id, data.billing_city_id);
                            } else
                            {
                                $('.is_billing_city').val(2);
                                $('.billing_city_id').html('');
                                $('.billing_city_text').show();
                                $('.billing_city_list').hide();
                                $("input[name=billing_city]").removeClass('required');
                                $(".billing_city_text").removeClass('mandatory');
                                $("input[name=billing_city]").addClass('required');
                            }

                        }
                        billingAddress($("#billing-form"));

                        $('#address').modal('show');
                    } else
                    {
                    }
                }
            });
        });
        $(".add_new_address").click(function ()
        {
            $('.alert').hide();
            $('.address_title').html('Add Address')
            $("#billing-address").addClass("hide");
            $('#address').find(".mandatory").removeClass("mandatory");
            $('#address').find(".invalid").removeClass("invalid");

            $('#address-form').find('input:text').val('');
            $('#address-form').each(function ()
            {
                this.reset();
            });
            $('#address_id').val("");
            $(".modal-header").text("{{__('web.add_address')}}");
            $(".save-address").text("{{__('web.save_address')}}");

            $('#address').modal('show');
        });

        $('input[type=radio][name=is_default]').change(function ()
        {
            window.location.href = $(this).attr('data-default');
        });

        $("#billing-form").change(function ()
        {
            billingAddress($(this));
        });

        function billingAddress(currentElement)
        {
            changeBillingDetails();
            if (currentElement.is(':checked'))
            {
                $("#billing-address").addClass("hide");
                $("#billing-address .address-item").addClass("hide");
                $("#check_billing_address").val("1");
//                $("#same-address").removeClass("hide");
                $(".billing-required").removeClass('required');
                $("#billing-address div").removeClass('mandatory');
                $('.is_billing_city').val(2);
//                alert(1);
            } else
            {
//                alert(2);
                $("#billing-address").removeClass("hide");
                $("#check_billing_address").val("2");
                $("#billing-address .address-item").removeClass("hide");
////                $("#same-address").addClass("hide");
                $(".billing-required").addClass('required');
                $('.is_billing_city').val(1);
                changeBillingCountry();

            }
        }

        function changeBillingDetails()
        {
            $(".billing-required").unbind('change');
            $(".billing-required").change(function ()
            {
                if ($(this).val())
                {
                    $(this).removeClass('required');
                    $(this).parent().removeClass('mandatory');
                } else
                {
                    $(this).addClass('required');
                    $(this).parent().addClass('mandatory');

                }
//                $("#billing-address div").removeClass('mandatory');
//                $(this).addClass('required');
            });
        }


        $(".save-address").click(function (e)
        {
            e.preventDefault();
            var form = $('.address_data').closest("form");
            if (validate(form)) {

                $('.save-address').attr('disabled', 'disabled');
                $("#preloader").addClass("show");
                var formData = $('form').serialize();
                
                 var formData = $('form').serializeArray();
                
          var phone = $('#changeMobile').val().replace(/ /g,'');   
          var billing_phone = '';

if ($('#bchangeMobile').length > 0) {
    billing_phone = $('#bchangeMobile').val().replace(/ /g, '');
}     
	formData.find(function(input) { 
	    return input.name == 'phone';
	}).value = phone;
	
	/*formData.find(function(input) { 
	    return input.name == 'billing_phone';
	}).value = billing_phone;*/

    // Update billing phone field safely
    var billingPhoneField = formData.find(function (input) {
        return input.name === 'billing_phone';
    });
    if (billingPhoneField) {
        billingPhoneField.value = billing_phone;
    }
	
                $.ajax({
                    type: 'POST',
                    url: "{{route('web.user.save_address')}}",
                    data: formData,
                    success: (data) => {
                        $("#preloader").removeClass("show");
                        if (data.status)
                        {
                            $('.alert-success-save').show();
                            $('.alert-success-save').text(data.message);
                            setTimeout(function ()
                            {
                                window.location.reload();
                            }, 2000);
                        } else
                        {
                            $('.alert-danger-save').show();
                            $('.alert-danger-save').text(data.message);
                        }

                    },
                    error: function (xhr)
                    {
                        if (xhr.status == '401')
                        {
                            show_message_url("{{route('web.user.login')}}", '{{__("web.please-login")}}')
                        }
                        $('.save-address').attr('disabled', false);
                        $("#preloader").removeClass("show");
                    }
                });

            }
        });
    });
    function validate(form) {
        $(form).find("input.required").each(function () {
            var data = $(this).val();
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find(".options.required").each(function () {
            var data = '';
            $(this).find("input[type=radio]").each(function () {
                if ($(this).is(":checked")) {
                    data = $(this).val();
                    $(this).parents(".options").addClass('valid').removeClass('invalid').removeClass('mandatory');
                }
            });

            if (data == "" || data == null) {
                $(this).removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).find("i.error").html('mandatory field');
            }
        });
        $(form).find("textarea.required").each(function () {
            var data = $(this).val();
            if (data == "" || data == null) {
                $(this).parent().removeClass('valid').removeClass('invalid').addClass('mandatory');
                $(this).next("i.error").html('mandatory field');
            }
        });
        $(form).find("select.required").each(function () {
            var x = $(this).children("option:selected").val();
            if (x == 0) {
                $(this).next("i.error").html('mandatory field');
                $(this).addClass("mandatory");
            } else if (x == "others") {
                $(this).removeClass("mandatory");
                $("#cityInput").removeClass("hide");
                $("#cityInput label").addClass("required");
            } else {
                $(this).next("i.error").html("");
                $(this).removeClass("mandatory");
            }
        });
        if ($(form).find(".mandatory").length > 0 || $(form).find(".invalid").length > 0) {
//            alert("Fields marked (*) are mandatory.\nEnter all fields with valid data.");
            return false;
        } else {
            return true;
        }
    }

    $(function ()
    {

        $(".sub_total_cart").text($(".sub_total_price_detail").text());
    });


</script>
@include('web.cart.save-note')
@endsection

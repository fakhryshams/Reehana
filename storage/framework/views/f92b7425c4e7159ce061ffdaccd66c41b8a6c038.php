<div id="address" class="modal fade" role="dialog">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <span class="close" data-dismiss="modal">
                <i class="icon-close"></i>
            </span>
            <div class="modal-body fancy">
                <h1 class="address_title">Add Address</h1>
                <form id="address-form" action=""  method="post">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="form-item col-md-2 col-sm-12">
                            <label><?php echo e(__('web.address_title')); ?> </label>
                            <input type='hidden' name="address_id" id="address_id" value="<?php echo e(old('address_id')); ?>"/>
                            <input type="text" data-type="ss" id="changeTitle" maxlength="12" name="address_title" class="required address_title"  value="<?php echo e(old('address_title')); ?>" placeholder="<?php echo e(__('web.address_title')); ?>*" maxlength="20">
                            <i class="error"></i>
                            <?php $__errorArgs = ['address_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-item col-md-4 col-sm-12">
                            <label><?php echo e(__('web.mobile_no')); ?></label>
                            <input pattern="^(?=.*[0-9])[- +()0-9]+$" type="text" data-type="alphanumeric" id="changeMobile" name="phone" value="<?php echo e(old('phone')); ?>"  class="required phone number-input" placeholder="<?php echo e(__('web.phone')); ?>*" minlength="<?php echo e(config('custom_web.mobile_minlength',8)); ?>" maxlength="<?php echo e(config('custom_web.mobile_maxlength',16)); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.email')); ?></label>
                            <input type="email" data-type="email" id="changeEmail" name="email" class="required user_email"  value="<?php echo e(old('email')); ?>" placeholder="<?php echo e(__('web.your_email')); ?>*" pattern="^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$">
                            <i class="error"></i>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.first_name')); ?></label>
                            <input type="text" data-type="alphabetsWSpaces" id="changeFirstname" name="first_name" value="<?php echo e(old('first_name')); ?>"  class="required first_name" placeholder="<?php echo e(__('web.first_name')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.last_name')); ?></label>
                            <input type="text" data-type="alphabetsWSpaces" id="changeLastname" name="last_name" value="<?php echo e(old('last_name')); ?>"  class="required last_name" placeholder="<?php echo e(__('web.last_name')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.country')); ?></label>
                            <input type="hidden" name="is_city" class="is_city" value="">
                            <select class="select-component untouched required" id="country_id" name="country_id">
                                <option selected="" disabled="" value=""><?php echo e(__('web.select_country')); ?></option>
                                <?php
$language = Helper::getLanguage();
?>
                                <?php $__currentLoopData = $country; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
$countryDescription = $record->getCountryDescription($language->id)
?>
                                <option data-code="<?php echo e($record->country_code); ?>" <?php echo e(old('country_id') == $record->id ? 'selected': ''); ?> value="<?php echo e($record->id); ?>"><?php echo e(ucfirst($countryDescription->country_name)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['country_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                            <i class="error"></i>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_list">
                            <label><?php echo e(__('web.area')); ?></label>
                            <select class="select-component untouched  city_id required" name="city_id">
                                <option selected="" disabled=""  value=""><?php echo e(__('web.select_area')); ?></option>
                            </select>
                            <i class="error"></i>
                            <?php $__errorArgs = ['city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="text-red label-w-100"> <?php echo e($message); ?></label>
                            <?php endif; ?>
                        </div>
                        <div class="form-item col-md-6 col-sm-12 city_text">
                            <label><?php echo e(__('web.city')); ?></label>
                            <input type="text" data-type="others" id="city_name"  name="city"  value="<?php echo e(old('city')); ?>" class="required city" placeholder="<?php echo e(__('web.city')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <label class="text-red"> <?php echo e($message); ?></label>
                            <?php endif; ?>
                        </div>
                         <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.state')); ?></label>
                            <input type="text" data-type="others" id="state" name="state" value="<?php echo e(old('state')); ?>" class="required state" placeholder="<?php echo e(__('web.state')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                         <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.pin_code')); ?></label>
                            <input type="text" data-type="others" id="pin_code" name="pin_code" value="<?php echo e(old('pin_code')); ?>" class="required pin_code" placeholder="<?php echo e(__('web.pin_code')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['pin_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <!--                        <div class="form-item col-md-6 col-sm-12">
                                                    <label><?php echo e(__('web.area')); ?></label>
                                                    <input type="text" data-type="others" id="changeArea"  name="area"  value="<?php echo e(old('area')); ?>" class="required area" placeholder="<?php echo e(__('web.area')); ?>*">
                                                    <i class="error"></i>
                                                    <?php $__errorArgs = ['area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="text-red"> <?php echo e($message); ?></div>
                                                    <?php endif; ?>
                                                </div>-->
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.block')); ?></label>
                            <input type="text" data-type="others" id="addBlock" name="block" value="<?php echo e(old('block')); ?>" class="required block" placeholder="<?php echo e(__('web.block')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['block'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.street')); ?></label>
                            <input type="text" data-type="others" id="changeStreet" name="street" value="<?php echo e(old('street')); ?>" class="required street" placeholder="<?php echo e(__('web.street')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['street'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if(false): ?>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.apartment')); ?></label>
                            <input type="text" data-type="others" id="changeApartment" class="appartment" name="apartment" value="<?php echo e(old('apartment')); ?>" placeholder="<?php echo e(__('web.apartment')); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['apartment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.house')); ?> #</label>
                            <input type="text" data-type="others" id="changeHouse" name="house_number" value="<?php echo e(old('house_number')); ?>" class="required house_number" placeholder="<?php echo e(__('web.house_number')); ?>*">
                            <i class="error"></i>
                            <?php $__errorArgs = ['house_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.floor')); ?></label>
                            <input type="text" data-type="others" id="changeFloor"  class="floor" name="floor" value="<?php echo e(old('floor')); ?>"  placeholder="<?php echo e(__('web.floor')); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['floor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>



                        <div class="form-item col-md-12 col-sm-12">
                            <label><?php echo e(__('web.address_detail')); ?></label>
                            <input type="text" data-type="others" id="addDetail" name="address_detail"  class="address_detail"  placeholder="<?php echo e(__('web.address_detail')); ?>" value="<?php echo e(old('address_detail')); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['address_detail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="clear"></div>
                        <div class="form-item col-md-12 col-sm-12">
                            <label><?php echo e(__('web.address_url')); ?></label>
                            <input type="text" data-type="url" id="addDetail" name="address_url" id="userLocation"  class="address_url"  placeholder="<?php echo e(__('web.address_url')); ?>" value="<?php echo e(old('address_detail')); ?>">
                            <i class="error"></i>
                            <?php $__errorArgs = ['address_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if(false): ?>
                        <div class="form-item col-md-6 col-sm-12">
                            <label><?php echo e(__('web.apartment')); ?></label>
                            <input maxlength="<?php echo e(config('custom_web.apartment_number_maxlength',6)); ?>"  type="text" data-type="others" id="changeApartment" class="appartment" name="apartment" value="<?php echo e(old('apartment')); ?>" placeholder="<?php echo e(__('web.apartment')); ?> #">
                            <i class="error"></i>
                            <?php $__errorArgs = ['apartment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-red"> <?php echo e($message); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <!--                        <div class="form-item col-md-6 col-sm-12">
                                                    <input type="text" data-type="others" id="changeDetail" name="pin_code" class="pin_code" value="<?php echo e(old('pin_code')); ?>" name="detail" placeholder="<?php echo e(__('web.pin_code')); ?>">
                                                    <i class="error"></i>
                                                    <?php $__errorArgs = ['pin_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <div class="text-red"> <?php echo e($message); ?></div>
                                                    <?php endif; ?>
                                                </div>-->
                    </div>
                    <?php if( !request()->routeIs('web.user.my_address')): ?>
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
                            <input type="checkbox"  value="remember"  name="is_billing_address" id="billing-form" <?php echo e($checked); ?>  >
                            <label><?php echo e(__('web.same_as_delivery')); ?></label>
                        </div>
                        <input type="hidden"  name="check_billing_address" id="check_billing_address"  value="1" >
                    </div>
                    <div class="row billing-form-data">
                        <div class="h-flex" id="billing-address">
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.mobile_no')); ?><b>*</b>:</label>
                                <input pattern="^(?=.*[0-9])[- +()0-9]+$" type="text" data-type="alphanumeric" id="bchangeMobile" name="billing_phone" class="bill-address billing_phone phone billing-required number-input" placeholder="<?php echo e(__('web.phone')); ?>*" value="<?php echo e(old('billing_phone')); ?>" minlength="<?php echo e(config('custom_web.mobile_minlength',8)); ?>" maxlength="<?php echo e(config('custom_web.mobile_maxlength',12)); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required">Email</label>
                                <input type="email" data-type="email" id="addEmail" name="pEmail" class="required" value="<?php echo e(old('pEmail')); ?>" placeholder="youremail@email.com" pattern="^(([-wd]+)(.[-wd]+)*@([-wd]+)(.[-wd]+)*(.([a-zA-Z]{2,5}|[d]{1,3})){1,2})$">
                                <i class="error"></i>
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required"><?php echo e(__('web.first_name')); ?><b>*</b>:</label>
                                <input type="text" data-type="alphabetsWSpaces" id="bchangeFirstname" name="billing_first_name" class="bill-address billing_first_name billing-required" placeholder="<?php echo e(__('web.first_name')); ?>*"  value="<?php echo e(old('billing_first_name')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.last_name')); ?><b>*</b>:</label>
                                <input type="text" data-type="alphabetsWSpaces" id="bchangeLastname" name="billing_last_name" class="bill-address billing_last_name billing-required" placeholder="<?php echo e(__('web.last_name')); ?>*"  value="<?php echo e(old('billing_last_name')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="clear left_lable"></div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.country')); ?><b>*</b>:</label>
                                <input type="hidden" name="is_billing_city" class="is_billing_city" value="">
                                <select class="select-component untouched billing-required" id="billing_country_id" name="billing_country_id">
                                    <option selected="" disabled=""  value=""><?php echo e(__('web.select_country')); ?></option>
                                    <?php
$language = Helper::getLanguage();
?>
                                    <?php $__currentLoopData = $country; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
$countryDescription = $record->getCountryDescription($language->id)
?>
                                    <option data-code="<?php echo e($record->country_code); ?>" <?php echo e(old('billing_country_id') == $record->id ? 'selected': ''); ?> value="<?php echo e($record->id); ?>"><?php echo e(ucfirst($countryDescription->country_name)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_country_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="form-item col-md-6 col-sm-12 billing_city_list">
                                <label class="City left_lable"><?php echo e(__('web.city')); ?><b>*</b>:</label>
                                <select class="select-component untouched  billing_city_id billing-required" name="billing_city_id">
                                    <option selected="" disabled="" value=""><?php echo e(__('web.select_area')); ?></option>
                                </select>
                                <?php $__errorArgs = ['billing_city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                                <i class="error"></i>
                            </div>
                            <div class="form-item col-md-6 col-sm-12 billing_city_text">
                                <label class="City left_lable"><?php echo e(__('web.city')); ?><b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangecity"  name="billing_city"  value="<?php echo e(old('billing_city')); ?>" class="r city billing_city billing-required" placeholder="<?php echo e(__('web.city')); ?>*">
                                <i class="error"></i>
                                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                             <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('State')); ?><b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeState" name="billing_state" value="<?php echo e(old('billing_state')); ?>" class="bill-address billing_state billing-required" placeholder="<?php echo e(__('State')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                             <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.pin_code')); ?><b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangePinCode" name="billing_pin_code" value="<?php echo e(old('billing_pin_code')); ?>" class="bill-address billing_pin_code billing-required" placeholder="<?php echo e(__('web.pin_code')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_pin_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>
                            <!--                            <div class="form-item col-md-6 col-sm-12">
                                                            <label class="required left_lable"><?php echo e(__('web.area')); ?><b>*</b>:</label>
                                                            <input type="text" data-type="others" id="bchangeArea" name="billing_area" value="<?php echo e(old('billing_area')); ?>" class="bill-address billing_area billing-required" placeholder="<?php echo e(__('web.area')); ?>">
                                                            <i class="error"></i>
                                                            <?php $__errorArgs = ['billing_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <label class="text-red"> <?php echo e($message); ?></label>
                                                            <?php endif; ?>
                                                        </div>-->
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.block')); ?><b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeBlock" name="billing_block" value="<?php echo e(old('billing_block')); ?>" class="bill-address billing_block billing-required" placeholder="<?php echo e(__('web.block')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_block'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>

                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.street')); ?><b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeStreet" name="billing_street" class="bill-address billing_street billing-required" placeholder="<?php echo e(__('web.street')); ?>*" value="<?php echo e(old('billing_street')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_street'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="required left_lable"><?php echo e(__('web.house')); ?> #<b>*</b>:</label>
                                <input type="text" data-type="others" id="bchangeHouse" name="billing_house_number" value="<?php echo e(old('billing_house_number')); ?>" class="bill-address billing_house_number billing-required" placeholder="<?php echo e(__('web.house_number')); ?>*">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_house_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>

                            <div class="form-item col-md-6 col-sm-12">
                                <label class="left_lable"><?php echo e(__('web.floor')); ?>:</label>
                                <input type="text" data-type="others" id="bchangeFloor" class="billing_floor" name="billing_floor" value="<?php echo e(old('billing_floor')); ?>" placeholder="<?php echo e(__('web.floor')); ?>">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_floor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>

                            <?php if(false): ?>
                            <div class="form-item col-md-6 col-sm-12">
                                <label class="left_lable"><?php echo e(__('web.apartment')); ?> #:</label>
                                <input maxlength="<?php echo e(config('custom_web.apartment_number_maxlength',6)); ?>"  type="text" data-type="others" id="bchangeApartment" name="billing_appartment" class="billing_appartment" value="<?php echo e(old('billing_appartment')); ?>" placeholder="<?php echo e(__('web.apartment')); ?> #">
                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_appartment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <label class="text-red"> <?php echo e($message); ?></label>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="clear"></div>
                            <div class="form-item col-md-12 col-sm-12">
                                <label><?php echo e(__('web.address_detail')); ?></label>
                                <input type="text" data-type="others" id="changeDetail" name="billing_address_detail"  class="billing_address_detail"  placeholder="<?php echo e(__('web.address_detail')); ?>" value="<?php echo e(old('billing_address_detail')); ?>">

                                <i class="error"></i>
                                <?php $__errorArgs = ['billing_address_detail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-red"> <?php echo e($message); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="separator"></div>
                    <input type="hidden" name="is_myaddress" value="0">

                    <?php else: ?>

                    <input type="hidden" name="is_myaddress" value="1">

                    <?php endif; ?>
                    <div class="alert alert-success alert-success-save" style="display: none" >
                    </div>

                    <div class="alert alert-danger alert-danger-save" style="display: none" >

                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary save-address"><?php echo e(__('web.save_address')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $currency = Helper::getDefaultCurrency();
?>
<?php $__env->startSection('page_specific_js'); ?>
<script>
    var currency = "<?php echo e($currency->code); ?>"
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
                url: "<?php echo e(route('web.user.remove_address')); ?>",
                data: {
                    'id': address_id,
                    '_token': '<?php echo e(csrf_token()); ?>'
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
            var modalText = "<?php echo e(__('web.intl_ship_message_empty_cart')); ?>";
            if (parseFloat(finalTotalValue) > 0)
            {
                modalText = "<?php echo e(__('web.intl_ship_message')); ?>";
            }
            cart_review_intl_box(modalText);
        });

        $('#address_id').val("<?php echo e(old('address_id')); ?>");
        $(".modal-header").text("<?php echo e(__('web.add_address')); ?>");
        $(".save-address").text("<?php echo e(__('web.save_address')); ?>");

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
            oldCity = "<?php echo e(old('city_id')); ?>";
    <?php
}
?>
<?php
if (old('billing_city_id')) {
	?>
            oldbillingCity = "<?php echo e(old('billing_city_id')); ?>";
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
                $(".modal-header").text("<?php echo e(__('web.update_address')); ?>");
                $(".save-address").text("<?php echo e(__('web.update_address')); ?>");
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
                url: "<?php echo e(route('web.user.get_city')); ?>",
                data: {
                    'country_id': countryId ? countryId : $("#country_id").val(),
                    '_token': '<?php echo e(csrf_token()); ?>'
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
                            $("input[name=city]").val("<?php echo e(old('city')); ?>");
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

            $(".modal-header").text("<?php echo e(__('web.update_address')); ?>");
            $(".save-address").text("<?php echo e(__('web.update_address')); ?>");
            if ($(this).attr('data-page') == 'cart_review')
            {
                $('.address_title').html('Update Address')

            } else
            {
                $('.address_title').html('Add Address')
            }



            $.ajax({
                type: 'POST',
                url: "<?php echo e(route('web.user.get_address_detail')); ?>",
                data: {
                    'address_id': $(this).attr('data-edit'),
                    '_token': '<?php echo e(csrf_token()); ?>'
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
            $(".modal-header").text("<?php echo e(__('web.add_address')); ?>");
            $(".save-address").text("<?php echo e(__('web.save_address')); ?>");

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
                    url: "<?php echo e(route('web.user.save_address')); ?>",
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
                            show_message_url("<?php echo e(route('web.user.login')); ?>", '<?php echo e(__("web.please-login")); ?>')
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
<?php echo $__env->make('web.cart.save-note', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/checkout/checkout_address.blade.php ENDPATH**/ ?>
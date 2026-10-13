<?php if($record): ?>
<?php
$language = Helper::getLanguage();
?>
<?php if($isBilling): ?>
<?php
$city = $record->billing_city;
if ($record->billing_city_id)
{
    $city = $record->getUserBillingCity($language->id) ? $record->getUserBillingCity($language->id)->city_name : '';
}
?>
<p>
    <?php echo e(__('web.email')); ?>: <?php echo e($record->billing_email); ?><br>
    <?php if($record->getUserBillingCountry($language->id)): ?>
    <?php echo e(__('web.country')); ?>: <?php echo e($record->getUserBillingCountry($language->id)->country_name); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?><br>
    <?php endif; ?>
    <?php if($city): ?>
    <?php echo e(__('web.city')); ?>: <?php echo e($city); ?><br>
    <?php endif; ?>
    <?php if($record->billing_area): ?>
    <?php echo e(__('web.area')); ?>: <?php echo e($record->billing_area); ?><br>
    <?php endif; ?>
    <?php if($record->billing_block): ?>
    <?php echo e(__('web.block')); ?>: <?php echo e($record->billing_block); ?><br>
    <?php endif; ?>
    <?php if($record->billing_street): ?>
    <?php echo e(__('web.street')); ?>: <?php echo e($record->billing_street); ?><br>
    <?php endif; ?>
    <?php if($record->billing_house_number): ?>
    <?php echo e(__('web.house')); ?>: <?php echo e($record->billing_house_number); ?><br>
    <?php endif; ?>
    <?php if($record->billing_floor): ?>
    <?php echo e(__('web.floor')); ?>: <?php echo e($record->billing_floor); ?><br>
    <?php endif; ?>
    <?php echo e(__('web.contact')); ?>: <?php echo e($record->billing_phone); ?><br>
    <?php if($record->billing_address_detail): ?>
    <?php echo e(__('web.address_detail')); ?>: <?php echo $record->billing_address_detail; ?>

    <?php endif; ?>

</p>
<?php if(false): ?>
<p><?php echo e($record->email); ?>, <?php echo e($record->billing_phone); ?>,<p>
<p><?php echo e($record->billing_house_number?$record->billing_house_number.", ":''); ?><?php echo e($record->billing_appartment?$record->billing_appartment.", ":''); ?><?php echo e($record->billing_floor?$record->billing_floor.", ":''); ?><?php echo e($record->billing_street?$record->billing_street.", ":''); ?><br>
    <?php echo e($record->billing_area?$record->billing_area.", ":''); ?><?php echo e($city?$city.", ":''); ?><?php echo e($record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?>

    <?php echo $record->address_detail?"<br>".$record->address_detail:''; ?>

</p>
<?php endif; ?>
<?php else: ?>
<?php
$city = $record->city;
$area = '';
if ($record->city_id)
{
    $area = $record->getUserCity($language->id) ? $record->getUserCity($language->id)->city_name : '';
}
?>
<?php if($page=='my_address'): ?>

<p>
    <em><?php echo e(__('web.contact')); ?></em>: <?php echo e($record->phone); ?>,<br>
    <em><?php echo e(__('web.email')); ?></em>: <?php echo e($record->email); ?>,<br>
    <?php if($record->getUserCountry($language->id)): ?>
    <em><?php echo e(__('web.country')); ?></em>: <?php echo e($record->getUserCountry($language->id)->country_name); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?><br>
    <?php endif; ?>
    <?php if($record->state_or_province_code): ?>
    <em><?php echo e(__('web.state')); ?></em>: <?php echo e($record->state_or_province_code); ?>,<br>
    <?php endif; ?>
    <?php if($city && $record->id != 43): ?>
    <em><?php echo e(__('web.city')); ?> #</em>: <?php echo e($city); ?>,<br>
    <?php endif; ?>
    <?php if($area): ?>
    <em><?php echo e(__('web.area')); ?> #</em>: <?php echo e($area); ?>,<br>
    <?php endif; ?>
    <?php if($record->block): ?>
    <em><?php echo e(__('web.block')); ?> #</em>: <?php echo e($record->block); ?>,<br>
    <?php endif; ?>
    <?php if($record->street): ?>
    <em><?php echo e(__('web.street')); ?></em>: <?php echo e($record->street); ?>,<br>
    <?php endif; ?>
    <?php if($record->house_number): ?>
    <em><?php echo e(__('web.house')); ?> #</em>: <?php echo e($record->house_number); ?>,<br>
    <?php endif; ?>
    <?php if($record->floor): ?>
    <em><?php echo e(__('web.floor')); ?></em>: <?php echo e($record->floor); ?>,<br>
    <?php endif; ?>
    <?php if($record->pin_code): ?>
    <em><?php echo e(__('web.pin_code')); ?></em>: <?php echo e($record->pin_code); ?>,<br>
    <?php endif; ?>
    <?php if($record->address_detail): ?>
    <em><?php echo e(__('web.address_detail')); ?></em>: <?php echo $record->address_detail; ?>,<br>
    <?php endif; ?>
	<?php if(!request()->routeIs('web.checkout.index')): ?>
	<?php if($record->pickupstore==1 && session('cphone')): ?>
		<em>Customer Phone </em>: <?php echo session('cphone'); ?><br>
	<?php endif; ?>
    <?php if($record->address_url): ?>

    
    <?php
     $url = $record->address_url;
    if (!str_contains($record->address_url, 'http')) { 
   
    
 $url = 'https://'.$record->address_url;    
}
      ?>
    <em><?php echo e(__('web.shipping_location')); ?></em>: <a href="<?php echo $url; ?>" target="_blank" class="shippingLocationLink">Map Link</a> 
    <?php endif; ?>
	<?php endif; ?>

    <?php if(false): ?>
    <?php echo e($record->email); ?>,<br>
    <?php echo e($record->phone); ?><br>
    <?php echo e($record->house_number?$record->house_number.", ":''); ?><?php echo e($record->appartment?$record->appartment.", ":''); ?><?php echo e($record->floor?$record->floor.", ":''); ?><?php echo e($record->street?$record->street.", ":''); ?><br>
    <?php echo e($record->area?$record->area.", ":''); ?><?php echo e($city?$city.", ":''); ?><?php echo e($record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?>

    <?php echo $record->address_detail?"<br>".$record->address_detail:''; ?>

    <?php endif; ?>
</p>
<?php elseif($page == 'mail'): ?>

<p>
    <?php echo e(__('web.contact')); ?>: <?php echo e($record->phone); ?>,<br>
    <?php echo e(__('web.email')); ?>: <?php echo e($record->email); ?>,<br>
    <?php if($record->getUserCountry($language->id)): ?>
    <?php echo e(__('web.country')); ?>: <?php echo e($record->getUserCountry($language->id)->country_name); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?><br>
    <?php endif; ?>
    <?php if($city): ?>
    <?php echo e(__('web.city')); ?> #: <?php echo e($city); ?>,<br>
    <?php endif; ?>
    <?php if($area): ?>
    <?php echo e(__('web.area')); ?> #: <?php echo e($area); ?>,<br>
    <?php endif; ?>
    <?php if($record->block): ?>
    <?php echo e(__('web.block')); ?> #: <?php echo e($record->block); ?>,<br>
    <?php endif; ?>
    <?php if($record->street): ?>
    <?php echo e(__('web.street')); ?>: <?php echo e($record->street); ?>,<br>
    <?php endif; ?>
    <?php if($record->house_number): ?>
    <?php echo e(__('web.house')); ?> #: <?php echo e($record->house_number); ?>,<br>
    <?php endif; ?>
    <?php if($record->floor): ?>
    <?php echo e(__('web.floor')); ?>: <?php echo e($record->floor); ?>,<br>
    <?php endif; ?>
    <?php if($record->address_detail): ?>
    <?php echo e(__('web.address_detail')); ?>: <?php echo $record->address_detail; ?><br>
    <?php endif; ?>
	
    <?php if($record->address_url): ?>
    <?php echo e(__('web.shipping_location')); ?>: <a href="<?php echo $record->address_url; ?>" target="_blank" style="color: #caa42f;" >Map Link</a> 
    <?php endif; ?>

    <?php if(false): ?>
    <?php echo e($record->email); ?>,<br>
    <?php echo e($record->phone); ?><br>
    <?php echo e($record->house_number?$record->house_number.", ":''); ?><?php echo e($record->appartment?$record->appartment.", ":''); ?><?php echo e($record->floor?$record->floor.", ":''); ?><?php echo e($record->street?$record->street.", ":''); ?><br>
    <?php echo e($record->area?$record->area.", ":''); ?><?php echo e($city?$city.", ":''); ?><?php echo e($record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?>

    <?php echo $record->address_detail?"<br>".$record->address_detail:''; ?>

    <?php endif; ?>
</p>

<?php elseif($page == 'cart_review'): ?>
<p><?php echo e($record->email); ?>,<br> 
    <?php echo e($record->phone); ?></p>
<p><?php echo e($record->house_number?$record->house_number.", ":''); ?><?php echo e($record->appartment?$record->appartment.", ":''); ?><?php echo e($record->floor?$record->floor.", ":''); ?><?php echo e($record->street?$record->street.", ":''); ?>

    <?php echo e($record->area?$record->area.", ":''); ?><br><?php echo e($city?$city.", ":''); ?><?php echo e($record->getUserCountry($language->id) ? $record->getUserCountry($language->id)->country_name: ''); ?><?php echo e($record->pin_code?" - ".$record->pin_code:''); ?>

    <?php echo $record->address_detail?"<br>".$record->address_detail:''; ?>

</p>
<?php elseif($page == 'email'): ?>
<?php echo __('web.email').': '.$record->email.'<br>'; ?>

<?php echo __('web.contact').': '.$record->phone.'<br>'; ?>

<?php echo $record->house_number? __('web.house_number').' #: '.$record->house_number.'<br>':''; ?>

<?php echo $record->appartment? __('web.apartment').':'.$record->appartment.'<br>':''; ?>

<?php echo $record->floor?__('web.floor').' #: '.$record->floor.'<br>':''; ?>

<?php echo $record->street?__('web.street').': '.$record->street.'<br>':''; ?>

<?php echo $record->area?__('web.area').': '.$record->area.'<br>':''; ?>

<?php echo $city? __('web.city').': '.$city.'<br>':''; ?>

<?php echo $record->getUserCountry($language->id) ? __('web.country').': '.$record->getUserCountry($language->id)->country_name .'<br>' : ''; ?>

<?php echo $record->pin_code?__('web.pin_code').': '.$record->pin_code .'<br>' :''; ?>

<?php echo $record->address_detail? __('web.address_detail').': '.$record->address_detail:''; ?>

<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\reehana\resources\views/web/layout/show_address.blade.php ENDPATH**/ ?>
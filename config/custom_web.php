<?php

/*
 * Custom config file
 * 
 */

return [
    /*
     * product limit
     */
    'php_date_format'            => env('WEB_PHP_DATE_FORMAT', 'd M, Y'),
    'product_limit'              => 18,
    'productFilter1'             => array(
        'd'  => 'Sort by',
        't'  => 'Trending',
        'no' => 'New to Old',
        'on' => 'Old to New',
        'lh' => 'Price Low to High',
        'hl' => 'Price High to Low'
    ),
    'productFilter2'             => array(
        'd'  => 'اختر',
        't'  => 'الأكثر طلبا',
        'no' => 'من الأجدد إلى الأقدم',
        'on' => 'من الأقدم إلى الأجدد',
        'lh' => 'من الأرخص إلى الأغلى',
        'hl' => 'من الأغلى إلى الأرخص'
    ),
    'query'                      => array('Delivery', 'Orders', 'Others'),
    'recived'                    => 1, // order recived status
    'fail'                       => 7, // order fail status
    'cod'                        => 3, //payment method
    'shop_from_store'            => 4, //payment method
    'bank_transfer'              => 4, //payment method
    'Pending'                    => 7, //order pending status
    'process'                    => 1, //order process status
    'language_ar'                => 2, //ar language id
    'mobile_minlength'           => 8,
    'mobile_maxlength'           => 20,
    'house_number_maxlength'     => 6,
    'floor_number_maxlength'     => 6,
    'apartment_number_maxlength' => 6,
    'online_payment'             => [1, 2],
    'cancel'                     => 5, //order cancel status
    'return'                     => 6, //order return status
    'delivered'                  => 4, //order status delivered
    'exclude_staus'              => [5, 7],
    'cancel_return_status'       => [5, 6],
    'can_delete_status'          => [5, 7],
    /*
     * Code By - M
     */
    'top_name'                   => 'Stay Home and Be Safe! We Ship Worldwide! Shop now!',
    'company_name'               => '<span>Designed by <a href="http://www.uno-digital.com/" target="_blank">UNO Digital</a></span>',
    'hesabe_payment_method'      => [
        0 => 2, //2=Visa
        1 => 1, //1=Knet
        2 => 2, //2=Visa
        4 => 4, //4=Pay At Shop
		5 => 5,
    ],
    /*
     * Phone Nuumber max lenght for payment gateway
     */
    'phone_min_length_payment'   => 11,
    'reamin_min_quantity'        => 3,
    'apply_discount'             => true,
    'discount'                   => 0, //Discount in Perccentage
    /*
     * Coupon Card
     */
    'show_coupon_card'           => true, //true=Show,false=Hide
    'initials'                   => [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four'],
    'initials_front'             => ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4],
    'payment_type'               => ['1' => 'knet', '2' => 'cc', '4' => 'Pay At Shop', '5' => 'Digital Payment', '6' => 'Pay At Shop'],
    /* Fedex Status */
    'fedex_status'               => [
        'HL' => 'Hold at Location',
        'OC' => 'Ready to Ship',
        'PU' => 'Picked Up',
        'DP' => 'Departed',
        'AF' => 'At facility',
        'IT' => 'In Transit',
        'DL' => 'Delivered',
        'CA' => 'Cancelled',
        'RE' => 'Return',
        'RS' => 'Return',
    ],
];


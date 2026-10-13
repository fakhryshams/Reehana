<?php

/*
 * Custom config file
 * 
 */

return [
    'CountryID_Kuwait'                                => 2, #From Database 
    /*
     * Pagination
     */
    'per_page'                                        => env('PER_PAGE', 10),
    /*
     * Common Date Format
     */
    'js_date_format'                                  => env('JS_DATE_FORMAT', 'DD-MM-YYYY'),
    'js_date_format_month'                            => env('JS_MONTH_FORMAT', 'MM'),
    'js_date_format_year'                             => env('JS_YEAR_FORMAT', 'YYYY'),
    'js_time_format'                                  => env('JS_TIME_FORMAT', 'LTS'),
    'php_date_format'                                 => env('PHP_DATE_FORMAT', 'd-m-Y'),
    'php_month_format'                                => env('PHP_MONTH_FORMAT', 'M-Y'),
    'php_time_format'                                 => env('PHP_TIME_FORMAT', 'h:i A'),
    'php_datetime_format'                             => env('PHP_DATETIME_FORMAT', 'd-M-Y h:i:s'),
    /*
     * Button CSS
     */
    'btn-primary'                                     => 'btn btn-outline-primary btn-sm',
    'btn-danger'                                      => 'btn btn-outline-danger btn-sm',
    'btn-info'                                        => 'btn btn-outline-info btn-sm',
    'btn-warning'                                     => 'btn btn-outline-warning btn-sm',
    'btn-success'                                     => 'btn btn-outline-success btn-sm',
    'btn-primary-form'                                => 'btn btn-outline-primary',
    'btn-success-form'                                => 'btn btn-outline-success',
    'btn-danger-form'                                 => 'btn btn-outline-danger',
    /*
     * Badge CSS
     */
    'badge-primary'                                   => 'badge bg-primary view-role',
    'badge-success'                                   => 'badge bg-success view-role',
    'badge-danger'                                    => 'badge bg-danger view-role',
    /*
     * Card CSS
     */
    'card-primary'                                    => 'card-primary card-outline',
    /*
     * Text-Css
     */
    'text-note-css'                                   => 'text-primary',
    /*
     * Dropdown Select2 Css
     */
    'select2-css'                                     => 'select2-primary',
    /*
     * Currency position
     */
    'currency-possition'                              => ['Left', 'Right', 'Left With Space', 'Right With Space'],
    /*
     * Currency position
     */
    /*
     * Date Formats
     */
    'date-time'                                       => 'd-m-Y h:i:s A',
    'date'                                            => 'd-m-Y',
    /*
     * Order Status
     */
    'order_status_is_defaul'                          => ['No', 'Yes'],
    'status'                                          => ['Active', 'In Active'],
    /*
     * Sort function
     */
    'sortLanguageID'                                  => [1], // 1 = English
    'shippingMethod'                                  => 1,
    'limit'                                           => 10,
    /*
     * Product image 
     */
    'product_image_dimensions_width'                  => 1750,
    'product_image_dimensions_height'                 => 1094,
    /*
     * Product thumb image
     */
    'product_resize_image_dimensions_width'           => 1080,
    'product_resize_image_dimensions_height'          => 1080,
    'product_thumb_image_dimensions_width'            => 300,
    'product_thumb_image_dimensions_height'           => 375,
    /*
     * Product Cart Image
     */
    'product_cart_image_dimensions_width'             => 232,
    'product_cart_image_dimensions_height'            => 232,
    /*
     * Category image
     */
    'category_image_dimensions_width'                 => 270,
    'category_image_dimensions_height'                => 270,
    /*
     * Category icon
     */
    'category_icon_dimensions_width'                  => 75,
    'category_icon_dimensions_height'                 => 75,
    /*
     * About Us Image
     */
    'about_us_image_dimensions_width'                 => 308,
    'about_us_image_dimensions_height'                => 308,
//        small Image
    'about_us_small_image_dimensions_width'           => 362,
    'about_us_small_image_dimensions_height'          => 334,
    //Home Category Section Image
    'category_section_dimensions_width'               => 295,
    'category_section_dimensions_height'              => 392,
    /*
     * Home Latest Collection
     */
    'home_latest_collection_yes_width'                => 535,
    'home_latest_collection_yes_height'               => 595,
    'home_latest_collection_no_width'                 => 250,
    'home_latest_collection_no_height'                => 250,
    'max_data_display'                                => 5,
    /*
     * banner image
     */
    'banner_image_dimensions_width'                   => 1280,
    'banner_image_dimensions_height'                  => 964,
    /*
     * Collection image
     */
    'collection_image_dimensions_width'               => 665,
    'collection_image_dimensions_height'              => 412,
    /*
     * Media Images
     */
    'media_min_width'                                 => 252,
    'media_min_height'                                => 221,
    /*
     * Currency
     */
    'currency'                                        => 'KD',
    /*
     * Customer Address - Default
     */
    'customer_address_default'                        =>
    [
        "0" => 'No',
        "1" => 'Yes',
    ],
    /*
     * Customized Form - Request Type
     */
    'customized_form_request_type'                    =>
    [
        "gift"      => 'Gift',
        "giveaways" => 'Giveaways',
    ],
    'admin_default_language_id'                       => 1,
    'category_show_on_homepage_limit'                 => 7,
    /*
     * following code is for setting seeder
     */
    'app_logo_width'                                  => 1800,
    'app_logo_height'                                 => 1800,
    'app_logo_png_width'                              => 200,
    'app_logo_png_height'                             => 100,
    'favicon_width'                                   => 20,
    'favicon_height'                                  => 20,
    'contact_us_width'                                => 492,
    'contact_us_height'                               => 365,
    /*
     * category section 
     */
    'max_section_data_display'                        => 2,
    /*
     * Order Note Length
     */
    'order_note_length'                               => '75',
    'discount_in'                                     => 'P', //P=Percentage,F=Flat
//    'discount_in'                            => 'F', //P=Percentage,F=Flat
    'discount_title'                                  => '%', //P=Percentage,F=Flat
//    'discount_title'                         => 'Flat', //P=Percentage,F=Flat
    'number_fixed'                                    => 3,
    'order_date_format'                               => 'd/m/Y h:i:s A',
    'common_date_format'                              => 'd/m/Y',
    'url_hint'                                        => env('APP_URL', 'http://salmamusab.com'),
    'category_collections'                            => 'clothing',
    'category_accessories'                            => 'accessories',
    'category_show_in'                                => [
        'clothing'    => 'Clothing',
        'accessories' => 'Accessories',
    ],
    /*
     * Used : When partial cancel/return
     * 
     * Have to get order Last Status, If Last order status is -- Shipped or Ready to Shipped
     * ... then don't deduct Shipping Charge.... 
     * ... else Deduct Shipping Charge
     */
    'deductShippingChargeExcept'                      => [
        2, 3
    ],
    'coupon_desc'                                     => 'Dear #username#, <br/><br/>We like to offer you below coupon code to get additional discount on your order.<br/><br/>Coupon Code : <b>#coupon-code#</b><br/><br/>
Coupon code is expired on #end-date#, So please use your coupon code before expired.<br><br>',
    /*
     * Convert Currency
     */
    'currency_convert_api_key'                        => '711d2830bdf340829089f6d5b1a77220', //From Mitesh Email ID
    'currency_convert_default_country'                => 'KWD',
    /*
     * MonoGram
     */
    'monogram_style_image_dimensions_width'           => 100,
    'monogram_style_image_dimensions_height'          => 80,
    'monogram_style_image_dimensions_width_letter'    => 100,
    'monogram_style_image_dimensions_height_letter'   => 100,
    'monogram_style_image_dimensions_width_letter_3'  => 29,
    'monogram_style_image_dimensions_height_letter_3' => 100,
    'monogram_placement_image_dimensions_width'       => 104,
    'monogram_placement_image_dimensions_height'      => 133,
    "alphabets"                                       => ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"],
//    "alphabets"                                       => ["A", "B"]
    'monogram_size_title_default_val'                             => 'Monogram Size'
];


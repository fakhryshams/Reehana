<?php

return [
    'status'   => [
        'process'     => 1,
        'readyToShip' => 2,
        'shipped'     => 3,
        'delivered'   => 4,
        'cancel'      => 5,
        'return'      => 6,
        'pending'     => 7,
    ],
    'badge'    => [
        1 => 'badge-primary',
        2 => 'badge-warning',
        3 => 'badge-info',
        4 => 'badge-success',
        5 => 'badge-danger',
        6 => 'badge-warning',
        7 => 'badge-warning',
    ],
    'pastForm' => [
        1 => 'Processing',
        2 => 'Ready to Ship',
        3 => 'Shipped',
        4 => 'Delivered',
        5 => 'Cancelled',
        6 => 'Returned',
        7 => 'Pending',
    ]
];

<?php

use Illuminate\Support\Str;

return [
    'account' => [
        'singular' => env('ACCOUNT_TYPE_TEXT', 'Manager'),
        'plural' => env('ACCOUNT_TYPE_TEXT_PLURAL', Str::plural(env('ACCOUNT_TYPE_TEXT', 'Manager'))),
    ],
    'affiliate' => [
        'singular' => env('AFFILIATE_TYPE_TEXT', 'Affiliate'),
        'plural' => env('AFFILIATE_TYPE_TEXT_PLURAL', Str::plural(env('AFFILIATE_TYPE_TEXT', 'Affiliate'))),
    ],
];

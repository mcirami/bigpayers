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
    'login' => [
        'page_text' => env('LOGIN_PAGE_TEXT', 'Login'),
        'button_text' => env('LOGIN_PAGE_BUTTON_TEXT', 'Login Now'),
        'forgot_password_link_text' => env('FORGOT_PASS_LINK_TEXT', 'Forgot password?'),
        'forgot_password_page_text' => env('FORGOT_PASS_PAGE_TEXT', 'Forgot password'),
        'forgot_password_button_text' => env('FORGOT_PASS_PAGE_BUTTON_TEXT', 'Reset password'),
    ],
];

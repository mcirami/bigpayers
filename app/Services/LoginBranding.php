<?php

namespace App\Services;

class LoginBranding
{
    public static function pageText(): string
    {
        return (string) config('branding.login.page_text');
    }

    public static function buttonText(): string
    {
        return (string) config('branding.login.button_text');
    }

    public static function forgotPasswordLinkText(): string
    {
        return (string) config('branding.login.forgot_password_link_text');
    }

    public static function forgotPasswordPageText(): string
    {
        return (string) config('branding.login.forgot_password_page_text');
    }

    public static function forgotPasswordButtonText(): string
    {
        return (string) config('branding.login.forgot_password_button_text');
    }

    public static function returnToLoginText(): string
    {
        return self::buttonText() === 'Login Now' ? 'Back to login' : 'Return to login';
    }
}

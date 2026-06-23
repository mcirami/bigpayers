<?php

namespace App\Support;

class NativeRequest
{
    public static function hasQuery(string $key): bool
    {
        return isset($_GET[$key]);
    }

    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function queryAll(): array
    {
        return $_GET;
    }

    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public static function postAll(): array
    {
        return $_POST;
    }

    public static function server(string $key, $default = null)
    {
        return $_SERVER[$key] ?? $default;
    }

    public static function host($default = null)
    {
        return self::server('HTTP_HOST', $default);
    }

    public static function requestUri($default = null)
    {
        return self::server('REQUEST_URI', $default);
    }

    public static function currentUrl(string $protocol = 'http://'): string
    {
        return $protocol . self::host('') . self::requestUri('');
    }

    public static function clientIp($default = null)
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            $value = self::server($key);

            if ($value === null || $value === '') {
                continue;
            }

            return trim(explode(',', (string) $value)[0]);
        }

        return $default;
    }

    public static function hasCookie(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    public static function cookie(string $key, $default = null)
    {
        return $_COOKIE[$key] ?? $default;
    }

    public static function mergePost(array $data): void
    {
        $_POST = array_merge($_POST, $data);
    }
}

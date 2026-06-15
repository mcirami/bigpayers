<?php

namespace App\Support;

class NativeRequest
{
    public static function hasQuery(string $key): bool
    {
        return isset($_GET[$key]);
    }

    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public static function server(string $key, $default = null)
    {
        return $_SERVER[$key] ?? $default;
    }

    public static function mergePost(array $data): void
    {
        $_POST = array_merge($_POST, $data);
    }
}

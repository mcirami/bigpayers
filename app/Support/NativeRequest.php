<?php

namespace App\Support;

class NativeRequest
{
    public static function hasQuery(string $key): bool
    {
        return isset($_GET[$key]);
    }

    public static function mergePost(array $data): void
    {
        $_POST = array_merge($_POST, $data);
    }
}

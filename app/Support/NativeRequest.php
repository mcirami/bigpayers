<?php

namespace App\Support;

class NativeRequest
{
    public static function mergePost(array $data): void
    {
        $_POST = array_merge($_POST, $data);
    }
}

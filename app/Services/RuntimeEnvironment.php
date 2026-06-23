<?php

namespace App\Services;

class RuntimeEnvironment
{
    public static function runsProductionSnippets(): bool
    {
        return ! (bool) config('app.debug') && config('app.env') === 'production';
    }
}

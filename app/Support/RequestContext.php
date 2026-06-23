<?php

namespace App\Support;

use Illuminate\Http\Request;

class RequestContext
{
    public static function schemeAndHttpHost(?Request $request = null): string
    {
        return self::current($request)->getSchemeAndHttpHost();
    }

    public static function httpHost(?Request $request = null): string
    {
        return self::current($request)->getHttpHost();
    }

    public static function host(?Request $request = null): string
    {
        return self::current($request)->getHost();
    }

    public static function path(?Request $request = null): string
    {
        return self::current($request)->path();
    }

    public static function hasQuery(string $key, ?Request $request = null): bool
    {
        return self::current($request)->query->has($key);
    }

    public static function query(string $key, $default = null, ?Request $request = null)
    {
        return self::current($request)->query($key, $default);
    }

    public static function cookie(string $key, $default = null, ?Request $request = null)
    {
        return self::current($request)->cookie($key, $default);
    }

    public static function expectsJson(?Request $request = null): bool
    {
        return self::current($request)->expectsJson();
    }

    public static function serverAddress(?Request $request = null): string
    {
        $request = self::current($request);

        return (string) ($request->server('SERVER_ADDR') ?: $request->ip());
    }

    public static function clientIp(?Request $request = null): string
    {
        $request = self::current($request);
        $ip = $request->server('HTTP_CLIENT_IP')
            ?: $request->server('HTTP_X_FORWARDED_FOR')
            ?: $request->server('REMOTE_ADDR', $request->ip());

        return trim(explode(',', (string) $ip)[0]);
    }

    public static function current(?Request $request = null): Request
    {
        return $request ?: request();
    }
}

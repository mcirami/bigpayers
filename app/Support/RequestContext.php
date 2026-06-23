<?php

namespace App\Support;

use Illuminate\Http\Request;

class RequestContext
{
    public static function schemeAndHttpHost(?Request $request = null): string
    {
        return self::request($request)->getSchemeAndHttpHost();
    }

    public static function httpHost(?Request $request = null): string
    {
        return self::request($request)->getHttpHost();
    }

    public static function host(?Request $request = null): string
    {
        return self::request($request)->getHost();
    }

    public static function serverAddress(?Request $request = null): string
    {
        $request = self::request($request);

        return (string) ($request->server('SERVER_ADDR') ?: $request->ip());
    }

    public static function clientIp(?Request $request = null): string
    {
        $request = self::request($request);
        $ip = $request->server('HTTP_CLIENT_IP')
            ?: $request->server('HTTP_X_FORWARDED_FOR')
            ?: $request->server('REMOTE_ADDR', $request->ip());

        return trim(explode(',', (string) $ip)[0]);
    }

    private static function request(?Request $request = null): Request
    {
        return $request ?: request();
    }
}

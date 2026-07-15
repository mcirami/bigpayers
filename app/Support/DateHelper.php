<?php

namespace App\Support;

use Carbon\Carbon;

class DateHelper
{
    public static function addHis(&$dateFrom, &$dateTo): void
    {
        $dateFrom .= ' 00:00:00';
        $dateTo .= ' 23:59:59';
    }

    public static function today(): string
    {
        $timezone = NativeRequest::cookie('timezone', 'America/New_York');

        return Carbon::today($timezone)->format('Y-m-d');
    }

    public static function convertDateTimezone($date, $timezone = 'America/New_York', $format = 'Y-m-d H:i:s', $newFormat = null): string
    {
        $newFormat ??= $format;

        return Carbon::createFromFormat($format, $date, $timezone)
            ->setTimezone('UTC')
            ->format($newFormat);
    }

    public static function convertToEST($date): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')
            ->setTimezone('America/New_York');
    }

    public static function tomorrow(): string
    {
        $timezone = NativeRequest::cookie('timezone', 'America/New_York');

        return Carbon::tomorrow($timezone)->format('Y-m-d');
    }

    public static function getSalesWeek(): array
    {
        $thisWeek = Carbon::createFromFormat('U', date('U'));
        $monday = self::convertDateTimezone($thisWeek->startOfWeek()->format('Y-m-d H:i:s'));
        $sunday = self::convertDateTimezone($thisWeek->endOfWeek()->format('Y-m-d H:i:s'));

        return ['start' => $monday, 'end' => $sunday];
    }

    public static function convertTimestampToEpoch($date): string
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('U');
    }

    public static function getSalesWeekEpoch(): array
    {
        $thisWeek = Carbon::createFromFormat('U', date('U'));
        $monday = self::convertDateTimezone($thisWeek->startOfWeek()->format('U'), 'PST', 'U', 'U');
        $sunday = self::convertDateTimezone($thisWeek->endOfWeek()->format('U'), 'PST', 'U', 'U');

        return ['start' => $monday, 'end' => $sunday];
    }
}

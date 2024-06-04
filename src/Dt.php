<?php

namespace meriksk\DateTime;

use Exception;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * DateTime helper class file. Extends custom functionality to Carbon library.
 * @package Base
 * @category Helpers
 * @link http://carbon.nesbot.com/docs/
 */
class Dt extends Carbon
{

    const FORMAT_DATE = 'date';
    const FORMAT_DATE_SHORT = 'date_short';
    const FORMAT_DATETIME = 'datetime';
    const FORMAT_DATETIME_SHORT = 'datetime_short';
    const FORMAT_TIME = 'time';
    const FORMAT_TIME_SHORT = 'time_short';

    const TARGET_CLIB = 'clib';
    const TARGET_ICU = 'icu';              // http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
    const TARGET_CARBON = 'carbon';        // https://carbon.nesbot.com/docs/#api-localization
    const TARGET_YII2 = 'yii2';            // http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
    const TARGET_MOMENT = 'moment';        // http://momentjs.com/docs/#/displaying/


    /**
     * @var string Language
     */
    public static $locale = 'en';

    /**
     * @var string Timezone
     */
    public static $timezone;

    /**
     * @var bool
     */
    public static $strippLeadingZeros = true;

    /**
     * @var array
     */
    public static $allowedTimeUnits = ['s', 'm', 'h', 'd', 'w', 'M', 'mo', 'y'];
    private static $lastMicroseconds = null;

    /**
     * Date formats [ PHP => format ]
     * @var array
     */
    private static $conversionTable = [
        self::TARGET_ICU => [
            // day
            'j'            => 'd',        // 1 to 31
            'd'            => 'dd',    // 01 to 31
            'D'            => 'eee',    // Mon through Sun
            'l'            => 'EEEE',    // Sunday through Saturday
            'N'            => 'cc',    // 1 (for Monday) through 7 (for Sunday)
            'w'            => 'cc',    // 0 (for Sunday) through 6 (for Saturday)
            'z'            => 'D',        // 0 through 365
            // week
            'W'            => 'ww',    // Examples: 42 (the 42nd week in the year)
            // month
            'F'            => 'MMMM',    // January through December
            'm'            => 'MM',    // 01 through 12
            'M'            => 'MMM',    // Jan through Dec
            'n'            => 'M',        // 1 through 12
            't'            => '',        // 28 through 31
            // year
            't'            => 'Y',        // Examples: 1999 or 2003 (ISO-8601 week-numbering year)
            'Y'            => 'yyyy',    // Examples: 1999 or 2003
            'y'            => 'yy',    // Examples: 99 or 03
            // time
            'a'            => 'a',        // am or pm
            'A'            => 'a',        // AM or PM
            'B'            => 'SSS',    // 000 through 999 (fractional second)
            'g'            => 'h',        // 1 through 12
            'G'            => 'k',        // 0 through 23
            'h'            => 'hh',    // 01 through 12
            'H'            => 'HH',    // 00 through 23
            'i'            => 'mm',    // 00 to 59
            's'            => 'ss',    // 00 to 59
            // timezone
            'e'            => 'VV',    // Examples: UTC, GMT, Atlantic/Azores
            'O'            => 'ZZZ',    // Example: +0200 (Difference to Greenwich time (GMT) in hours)
            'P'            => 'xxx',    // Example: +02:00
            'T'            => 'zz',    // Examples: EST, MDT (Timezone abbreviation)
        ],
        self::TARGET_CARBON => [
            // day
            'j'            => 'D',        // 1 to 31
            'd'            => 'DD',    // 01 to 31
            'D'            => 'ddd',    // Mon through Sun
            'l'            => 'dddd',    // Sunday through Saturday
            'N'            => 'E',        // 1 (for Monday) through 7 (for Sunday)
            'w'            => 'e',        // 0 (for Sunday) through 6 (for Saturday)
            'z'            => '',        // 0 through 365
            // week
            'W'            => 'w',        // Examples: 42 (the 42nd week in the year)
            // month
            'F'            => 'MMMM',    // January through December
            'm'            => 'MM',    // 01 through 12
            'M'            => 'MMM',    // Jan through Dec
            'n'            => 'M',        // 1 through 12
            't'            => '',        // 28 through 31
            // year
            't'            => 'Y',        // Examples: 1999 or 2003 (ISO-8601 week-numbering year)
            'Y'            => 'YYYY',    // Examples: 1999 or 2003
            'y'            => 'YY',    // Examples: 99 or 03
            // time
            'a'            => 'a',        // am or pm
            'A'            => 'A',        // AM or PM
            'B'            => 'SSS',    // 000 through 999 (fractional second)
            'g'            => 'h',        // 1 through 12
            'G'            => 'H',        // 0 through 23
            'h'            => 'hh',    // 01 through 12
            'H'            => 'HH',    // 00 through 23
            'i'            => 'mm',    // 00 to 59
            's'            => 'ss',    // 00 to 59
            // timezone
            'e'            => 'zz',    // Examples: UTC, GMT, Atlantic/Azores
            'O'            => 'ZZ',    // Example: +0200 (Difference to Greenwich time (GMT) in hours)
            'P'            => 'Z',        // Example: +02:00
            'T'            => 'z',        // Examples: EST, MDT (Timezone abbreviation)
        ],
        self::TARGET_CLIB => [
            // day
            'j'            => '%e',    // 1 to 31
            'd'            => '%d',    // 01 to 31
            'D'            => '%a',    // Mon through Sun
            'l'            => '%A',    // Sunday through Saturday
            'N'            => '%u',    // 1 (for Monday) through 7 (for Sunday)
            'w'            => '%w',    // 0 (for Sunday) through 6 (for Saturday)
            'z'            => '',        // 0 through 365
            // week
            'W'            => '%W',    // Examples: 42 (the 42nd week in the year)
            // month
            'F'            => '%B',    // January through December
            'm'            => '%m',    // 01 through 12
            'M'            => '%b',    // Jan through Dec
            'n'            => '%m',    // 1 through 12
            't'            => '',        // 28 through 31
            // year
            't'            => '%Y',    // Examples: 1999 or 2003 (ISO-8601 week-numbering year)
            'Y'            => '%Y',    // Examples: 1999 or 2003
            'y'            => '%y',    // Examples: 99 or 03
            // time
            'a'            => '%P',    // am or pm
            'A'            => '%p',    // AM or PM
            'B'            => '',        // 000 through 999 (fractional second)
            'g'            => '%I',    // 1 through 12 - "%l" does not work :(
            'G'            => '%k',    // 0 through 23
            'h'            => '%I',    // 01 through 12
            'H'            => '%H',    // 00 through 23
            'i'            => '%M',    // 00 to 59
            's'            => '%S',    // 00 to 59
            // timezone
            'e'            => '%Z',    // Examples: UTC, GMT, Atlantic/Azores
            'O'            => '%z',    // Example: +0200 (Difference to Greenwich time (GMT) in hours)
            'P'            => '%z',    // Example: +02:00
            'T'            => '%Z',    // Examples: EST, MDT (Timezone abbreviation)
        ],
        self::TARGET_MOMENT => [
            'j'            => 'D',        // 1 to 31
            'd'            => 'DD',    // 01 to 31
            'D'            => 'ddd',    // Mon through Sun
            'l'            => 'dddd',    // Sunday through Saturday
            'N'            => 'E',        // 1 (for Monday) through 7 (for Sunday)
            'w'            => 'd',        // 0 (for Sunday) through 6 (for Saturday)
            'z'            => 'DDD',    // 0 through 365
            // week
            'W'            => 'w',        // Examples: 42 (the 42nd week in the year)
            // month
            'F'            => 'MMMM',    // January through December
            'm'            => 'MM',    // 01 through 12
            'M'            => 'MMM',    // Jan through Dec
            'n'            => 'M',        // 1 through 12
            't'            => '',        // 28 through 31
            // year
            't'            => 'Y',        // Examples: 1999 or 2003 (ISO-8601 week-numbering year)
            'Y'            => 'YYYY',    // Examples: 1999 or 2003
            'y'            => 'YY',        // Examples: 99 or 03
            // time
            'a'            => 'a',        // am or pm
            'A'            => 'A',        // AM or PM
            'B'            => 'SSS',    // 000 through 999 (fractional second)
            'g'            => 'h',        // 1 through 12
            'G'            => 'H',        // 0 through 23
            'h'            => 'hh',    // 01 through 12
            'H'            => 'HH',    // 00 through 23
            'i'            => 'mm',    // 00 to 59
            's'            => 'ss',    // 00 to 59
            // timezone
            'e'            => 'zz',    // Examples: UTC, GMT, Atlantic/Azores
            'O'            => 'ZZ',    // Example: +0200 (Difference to Greenwich time (GMT) in hours)
            'P'            => 'Z',        // Example: +02:00
            'T'            => 'zz',    // Examples: EST, MDT (Timezone abbreviation)
        ],
        self::TARGET_YII2 => [],
    ];

    /**
     * Get locale format aliases
     * @param string $locale
     * @param string $alias
     * @return string
     */
    public static function getFormatByAlias(string $alias, string $locale = 'en_US'): string
    {
        $def = [];

        // find aliases
        switch (true) {
            case in_array($locale, ['en', 'us']):
            default:
                $def = [
                    'date'                    => 'n/j/Y',
                    'date_short'            => 'n/j/Y',
                    'date_medium'            => 'M j, Y',
                    'date_long'                => 'F j, Y',
                    'date_full'                => 'F j, Y',
                    'date_time'                => 'n/j/Y g:i:s A',
                    'date_time_short'        => 'n/j/Y g:i A',
                    'date_time_short_tz'    => 'n/j/Y g:i A T',
                    'date_time_medium'        => 'M j, Y g:i A',
                    'date_time_long'        => 'F j, Y g:i:s A',
                    'date_time_full'        => 'F j, Y g:i:s.u A T',
                    'date_time_tz'            => 'n/j/Y g:i:s A T',
                    'date_time_ms'            => 'n/j/Y g:i:s.u A',
                    'date_human'            => 'D, M j',
                    'time'                    => 'g:i:s A',
                    'time_short'            => 'g:i A',
                    'time_short_tz'            => 'g:i A T',
                    'time_medium'            => 'g:i:s A',
                    'time_long'                => 'g:i:s A',
                    'time_full'                => 'g:i:s A T',
                    'time_ms'                => 'g:i.u A',
                    'xxx'                    => 'g:i.u A',
                ];
                break;

            case in_array($locale, ['sk', 'cs', 'da', 'de', 'es', 'fr', 'hu']):
                $def = [
                    'date'                    => 'j.n.Y',
                    'date_short'            => 'j.n.Y',
                    'date_medium'            => 'j M Y',
                    'date_long'                => 'j F Y',
                    'date_full'                => 'j F Y e',
                    'date_time'                => 'j.n.Y H:i:s',
                    'date_time_short'        => 'j.n.Y H:i',
                    'date_time_short_tz'    => 'j.n.Y H:i T',
                    'date_time_medium'        => 'j. M Y H:i',
                    'date_time_long'        => 'j. F Y H:i:s',
                    'date_time_full'        => 'j. F Y H:i:s T',
                    'date_time_tz'            => 'j.n.Y H:i:s T',
                    'date_time_ms'            => 'j.n.Y H:i:s.u',
                    'date_human'            => 'j.n.Y',
                    'time'                    => 'H:i:s',
                    'time_short'            => 'H:i',
                    'time_short_tz'            => 'H:i T',
                    'time_medium'            => 'H:i:s',
                    'time_long'                => 'H:i:s',
                    'time_full'                => 'H:i:s T',
                    'time_ms'                => 'H:i:s.u',
                ];
                break;
        } //switch

        return $def[$alias] ?? $alias;
    }

    /**
     * Is OS windows?
     * @return bool
     */
    private static function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Format date-time
     * @param string $format
     * @param int|string|DateTime $timestamp
     * @param string $timezone
     * @param string $locale
     * @return bool|string
     */
    public static function f($format, $timestamp = null, $timezone = null, $locale = null): bool|string
    {
        $tz = $timezone ? $timezone : self::$timezone;

        if (is_numeric($timestamp)) {
            $dt = static::createFromTimestamp($timestamp, $tz);
        } elseif (is_object($timestamp) && $timestamp instanceof Dt) {
            $dt = clone $timestamp;
            if ($tz) {
                $dt->setTimezone($tz);
            }
        } else {
            $dt = new Dt($timestamp, $tz);
        }

        if (!$dt) {
            return false;
        }

        if ($locale && is_string($locale)) {
            $f = self::getFormat($format, self::TARGET_MOMENT, $locale);
            $dt->setLocale($locale);
            return $dt->isoFormat($f, $format);
        } else {
            $f = self::getFormat($format);
            return $dt->format($f);
        }
    }

    /**
     * Create a Dt instance from a string.
     * @param string $datetime
     * @param \DateTimeZone|string|null $tz
     * @return static
     * @throws InvalidArgumentException
     */
    public static function p($datetime, $format = null, $tz = null, $locale = null)
    {
        // invalid date
        if (!is_string($datetime) || empty($datetime)) {
            return false;
        }

        // parsed datetime
        $dt = false;
        // timezone
        $tz = $tz ? $tz : self::$timezone;
        // locale
        $locale = !empty($locale) ? trim(strtolower($locale)) : self::$locale;
        // expected format
        $format = static::getFormat($format, null, $locale);

        try {
            $dt = parent::createFromFormat($format, $datetime, $tz);
            //$dt = parent::parseFromLocale($datetime, $locale, $tz);
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
        }

        return $dt;
    }

    /**
     * Get date-time format
     * @param string $format
     * @param string $targetFormat
     * @param string $locale
     * @return string
     */
    public static function getFormat($format, $targetFormat = null, $locale = null)
    {
        if (empty($format)) {
            return null;
        }

        // set locale
        $locale = !empty($locale) ? trim($locale) : self::$locale;

        // convert format
        if (!empty($targetFormat)) {
            $f = static::convertFormat($format, $targetFormat, $locale);
            // without conversion
        } else {
            $f = self::getFormatByAlias($format, $locale);
        }

        // Check for Windows to find and replace the %e
        // modifier correctly
        if ($targetFormat === self::TARGET_CLIB && self::isWindows()) {
            $f = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $f); // @codeCoverageIgnore
        }

        return $f;
    }

    /**
     * Converts format
     * @param string $alias
     * @param string $targetFormat
     * @return string date format
     */
    public static function convertFormat($format, $targetFormat, $locale = null): string
    {
        if (empty($format)) {
            return null;
        }

        // YII2 === ICU
        if ($targetFormat === self::TARGET_YII2) {
            $targetFormat = self::TARGET_ICU;
        }

        // unknown conversion format
        if (!isset(self::$conversionTable[$targetFormat])) {
            return $format;
        }

        // set locale
        $locale = !empty($locale) ? trim($locale) : self::$locale;

        // defs
        $f = self::getFormatByAlias($format, $locale);

        // convert
        $f = strtr($f, self::$conversionTable[$targetFormat]);

        // remove extra padding
        if ($targetFormat === self::TARGET_CLIB) {
            $f = str_replace('%l ', '%l', $f);

            // stripping leading zeros
            if (self::$strippLeadingZeros === true) {
                $replace = self::isWindows() ? ['%#e', '%#m'] : ['%-e', '%-m'];
                $f = str_replace(['%e', '%m'], $replace, $f);
            }
        }

        return $f;
    }

    /**
     * Get timestamp of given date
     * @see DateTime::boundaries()
     * @return int
     */
    public static function createTimestamp($date, $timezone = null)
    {
        $tz = $timezone ? $timezone : self::$timezone;
        $dt = new Dt($date, $tz);
        return $dt ? $dt->getTimestamp() : false;
    }

    /**
     * Check timezone
     * @param string|DateTimeZone $timezone
     * @return DateTimeZone
     * @throws InvalidArgumentException
     */
    public static function checkTimezone($timezone)
    {
        if (is_string($timezone)) {
            return new DateTimeZone($timezone);
        } elseif (is_object($timezone) && $timezone instanceof DateTimeZone) {
            return $timezone;
        } else {
            throw new InvalidArgumentException('Invalid timezone.');
        }
    }

    /**
     * @see getBoundaries()
     */
    public static function getTime($period, $returnObject = true, $baseTime = null, $timezone = null)
    {
        $b = self::getBoundaries($period, true, $baseTime, $timezone);
        if ($b) {
            return $returnObject === true ? $b[0] : $b[0]->getTimestamp();
        } else {
            return null;
        }
    }

    /**
     * Get relative time
     * @param string $period
     * @param int|float $value the $value count passed in
     * @param bool $returnObject
     * @param int|\DateTime $baseTime
     * @param string $timezone
     * @return int|\meriksk\DateTime\Dt
     */
    public static function getRelativeTime($period, $value = 1, $returnObject = true, $baseTime = null, $timezone = null)
    {
        $tz = $timezone ? $timezone : self::$timezone;

        if (is_numeric($baseTime)) {
            $dt = static::createFromTimestamp($baseTime, $tz);
        } elseif (is_object($baseTime) && $baseTime instanceof Dt) {
            $dt = clone $baseTime;
            if ($tz) {
                $dt->setTimezone($tz);
            }
        } else {
            $dt = new Dt($baseTime, $tz);
        }

        if (!$dt) {
            return false;
        }

        switch ($period) {
                // second
            case 's':
            case 'sec':
            case 'second':
            case 'seconds':
                $dt->subSeconds($value);
                break;
                // minute
            case 'm':
            case 'min':
            case 'minute':
            case 'minutes':
                $dt->subMinutes($value);
                break;
                // hour
            case 'h':
            case 'hour':
            case 'hours':
                $dt->subHours($value);
                break;
                // day
            case 'd':
            case 'day':
            case 'days':
                $dt->subDays($value);
                break;
                // week
            case 'w':
            case 'week':
            case 'weeks':
                $dt->subWeeks($value);
                break;
                // month
            case 'mo':
            case 'M':
            case 'month':
            case 'months':
                $dt->subMonths((int)$value);
                break;
                // year
            case 'y':
            case 'year':
            case 'years':
                $dt->subYears((int)$value);
                break;
                // default modifier
            default:
                $dt->modify($period);
        }

        // return
        return $returnObject === true ? $dt : $dt->getTimestamp();
    }

    /**
     * Calculate boundaries for given timeframe.
     * @param string $period
     *
     * Possible parameters:<br>
     * 's', 'sec', 'second'<br>
     * 'm', 'min', 'minute'<br>
     * 'h', 'hour'<br>
     * 'd', 'day', 'today'<br>
     * '-1d', 'yesterday', 'previous day', 'previous_day'<br>
     * '+1d', 'tomorrow', 'next day', 'next_day'<br>
     * 'w', 'week'<br>
     * '-1w', 'previous week', 'week ago', 'week_ago'<br>
     * '+1w', 'next week', 'next_week'<br>
     * 'M', 'mo', 'month'<br>
     * '-1M', '-1mo', 'previous month', 'month ago', 'month_ago'<br>
     * '+1M', '+1mo', 'next month', 'next_month'<br>
     * 'y', 'year', 'this year', 'this_year', 'current year', 'current_year'<br>
     * '-1y', 'previous year', 'year ago', 'year_ago'<br>
     * '+1y', 'next year', 'next_year'<br>
     * 
     * @param bool $returnObject
     * @param int|string|Dt $baseTime 
     * @param string $timezone
     * @return bool|array
     * @throws \Exception
     * @link https://gist.github.com/sepehr/6351425
     */
    public static function getBoundaries(string $period, bool $returnObject = true, int|string|Dt $baseTime = null, $timezone = null)
    {
        // time range aliases
        switch ($period) {
            case 'sec':
            case 'second':
                $period = 'now/s';
                break;
            case 'min':
            case 'minute':
                $period = 'now/m';
                break;
            case 'hour':
                $period = 'now/h';
                break;
            case 'day':
            case 'today':
                $period = 'now/d';
                break;
            case 'yesterday':
            case 'previous day':
            case 'previous_day':
                $period = '-1d/d';
                break;
            case 'tomorrow':
            case 'next day':
            case 'next_day':
                $period = '+1d/d';
                break;
            case 'week':
                $period = 'now/w';
                break;
                break;
            case 'previous week':
            case 'week ago':
            case 'week_ago':
                $period = '-1w/w';
                break;
            case 'next week':
            case 'next_week':
                $period = '+1w/w';
                break;
            case 'mo':
            case 'month':
                $period = 'now/M';
                break;
            case 'previous month':
            case 'month ago':
            case 'month_ago':
                $period = '-1M/M';
                break;
            case 'next month':
            case 'next_month':
                $period = '+1M/M';
                break;
            case 'year':
                $period = 'now/y';
                break;
            case 'previous year':
            case 'year ago':
            case 'year_ago':
                $period = '-1y/y';
                break;
            case 'next year':
            case 'next_year':
                $period = '+1y/y';
                break;
            default:
                // absolute time range (1d, -1d, +1w, ...)        
                if (preg_match('/^([\+\-])?(\d+)?([a-zA-Z]{1,2})$/', $period, $m)) {
                    //$modifyOperator = $m[1] === '-' ? '-' : '+';
                    //$count = (int)$m[2];
                    $timeUnit = $m[3];

                    if ($timeUnit === 'mo') {
                        $timeUnit = 'M';
                    }

                    // -2d => -2d/d
                    $period = $period . '/' . $timeUnit;
                }
                break;
        }

        $dt0 = self::resolveTime($period, false, $baseTime, $timezone);
        $dt1 = self::resolveTime($period, true, $baseTime, $timezone);

        return $returnObject === true ? [$dt0, $dt1] : [$dt0->getTimestamp(), $dt1->getTimestamp()];
    }

    /**
     * Get relative time
     * @param string $period
     * @param bool $returnObject
     * @param int|float $value the $value count passed in
     * @param int|\DateTime $baseTime Start time
     * @param string $timezone
     * @return boolean|\meriksk\DateTime\Dt[]
     * @throws InvalidIntervalException
     */
    public static function getRelativeTimeBoundaries($period, $value = 1, $returnObject = true, $baseTime = null, $timezone = null)
    {
        $tz = $timezone ? $timezone : self::$timezone;
        $dt0 = self::getRelativeTime($period, $value, true, $baseTime, $tz);
        $dt1 = null;

        if (is_numeric($baseTime)) {
            $dt1 = static::createFromTimestamp($baseTime, $tz);
        } elseif (is_object($baseTime) && $baseTime instanceof Dt) {
            $dt1 = clone $baseTime;
            if ($tz) {
                $dt1->setTimezone($tz);
            }
        } else {
            $dt1 = new Dt($baseTime, $tz);
        }

        if (!$dt0 || !$dt1) {
            return false;
        }

        // return
        return $returnObject === true ? [$dt0, $dt1] : [$dt0->getTimestamp(), $dt1->getTimestamp()];
    }

    /**
     * We supports the following time units: 
     * s (seconds), m (minutes), h (hours), d (days), w (weeks), M (months) and y (years).
     * 
     * The minus operator enables you to step back in time, relative to the current date and time, or now. 
     * If you want to display the full period of the unit (day, week, month, etc…), append /<time unit> to the end. 
     * The plus operator enables you to step forward in time, relative to now. 
     * For example, you can use this feature to look at predicted data in the future.
     * 
     * Last 5 minutes:	    now-5m
     * The day so far:	    now/d
     * This week:           w
     * Previous week:       now-1w
     * This month:          M
     * Previous Month:      now-1M or -1M
     * This Year:           y
     *
     * @param integer|string|DateTime|null $datetime
     * @param bool $endOfTimeRange
     * @param string|DateTimeZone $timezone
     * @param int|string|Dt $baseTime
     * @return Dt|null
     */
    public static function resolveTime(int|string $datetime = null, bool $endOfTimeRange = false, int|string|Dt $baseTime = null, string|DateTimeZone $timezone = null): ?Dt
    {
        if (!$datetime) {
            return null;
        }

        $tz = $timezone ? $timezone : self::$timezone;

        // number (timestamp or miliseconds)
        // example: 
        // 1716558968
        // 1716558968965
        if (is_numeric($datetime)) {

            $len = strlen($datetime);
            if ($len > 11) {
                $ts = (int)($datetime / 1000);
                $miliseconds = $datetime % 1000;
            } else {
                $ts = (int)$datetime;
                $miliseconds = 0;
            }

            $dt = static::createFromFormat('U.v', "{$ts}.{$miliseconds}");
            if ($dt) {
                if ($tz) {
                    $dt->setTimezone($tz);
                }

                return $dt;
            }
        }

        // DateTime object
        if (is_object($datetime) && $datetime instanceof Dt) {
            return $datetime;
        }

        // absolute time
        // example: 
        // -1d/d - -1d/d ... Yesterday
        // now/d - now/d ... The day so far	
        $absoluteTime = false;
        $absoluteTimeUnit = null;

        if (strpos($datetime, '/') !== false) {
            $parts = explode('/', urldecode($datetime));
            if (count($parts) === 2) {
                $absoluteTime = true;
                $datetime = trim($parts[0]);
                if ($datetime === 'now') {
                    $datetime = 'd';
                }

                $absoluteTimeUnit = $parts[1] ?? null;
            }
        }

        // now
        // example: 
        // now
        // now/d
        if (is_string($datetime) && strtolower($datetime) === 'now') {
            $dt = new Dt('now', $tz);
            return self::setDatetimeBoundary($dt, $absoluteTimeUnit, $endOfTimeRange);
        }

        // exact time in format yyyy-MM-ddThh:mm:ss(.mmmmmm)
        // example: 2024-05-24T15:55:30.555555
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(T(\d{2}):(\d{2}):(\d{2})(\.(\d{1,6}))?)?$/', $datetime, $m)) {
            $dt = new Dt($datetime, $tz);
            return self::setDatetimeBoundary($dt, $absoluteTimeUnit, $endOfTimeRange);
        }

        $datetime = preg_replace('/[^a-zA-Z0-9\+\-\s]/', '', $datetime);
        $modifyOperator = '+';
        $count = 1;
        $timeUnit = '';

        // base time
        if ($baseTime) {
            if (is_numeric($baseTime)) {
                $dt = static::createFromTimestamp($baseTime, $tz);
            } elseif (is_object($baseTime) && $baseTime instanceof DateTime) {
                $dt = clone $baseTime;
            } else {
                $dt = new Dt($baseTime, $tz);
            }
        } else {
            $dt = new Dt('now', $tz);
        }

        // absolute time range (1d, -1d, +1w, ...)        
        if (preg_match('/^([\+\-])?(\d+)?([a-zA-Z]{1,2})$/', $datetime, $m)) {

            $modifyOperator = $m[1] === '-' ? '-' : '+';
            $count = (int)$m[2];
            $timeUnit = $m[3];

            // relative range (now-1d, now+1d, ...)
        } elseif (preg_match('/^now([\+\-\s])(\d+)([a-zA-Z]{1,2})$/', $datetime, $m)) {

            $modifyOperator = $m[1] === '-' ? '-' : '+';
            $count = (int)$m[2];
            $timeUnit = $m[3];
        }

        // check time unit        
        if (!in_array($timeUnit, self::$allowedTimeUnits)) {
            throw new Exception('Not supported time unit. List of supported time units: s (seconds), m (minutes), h (hours), d (days), w (weeks), M (months), mo (monts) and y (years)');
        }

        if ($modifyOperator === '-') {
            switch ($timeUnit) {
                case 's':
                    $dt->subSeconds($count);
                    break;
                case 'm':
                    $dt->subMinutes($count);
                    break;
                case 'h':
                    $dt->subHours($count);
                    break;
                case 'd':
                    $dt->subDays($count);
                    break;
                case 'w':
                    $dt->subWeeks($count);
                    break;
                case 'M':
                case 'mo':
                    $dt->subMonthsNoOverflow($count);
                    break;
                case 'y':
                    $dt->subYearsNoOverflow($count);
                    break;
            }
        } else {
            switch ($timeUnit) {
                case 's':
                    $dt->addSeconds($count);
                    break;
                case 'm':
                    $dt->addMinutes($count);
                    break;
                case 'h':
                    $dt->addHours($count);
                    break;
                case 'd':
                    $dt->addDays($count);
                    break;
                case 'w':
                    $dt->addWeeks($count);
                    break;
                case 'M':
                case 'mo':
                    $dt->addMonthsNoOverflow($count);
                    break;
                case 'y':
                    $dt->addYearsNoOverflow($count);
                    break;
            }
        }

        if ($absoluteTime) {
            $dt = self::setDatetimeBoundary($dt, $absoluteTimeUnit, $endOfTimeRange);
        } else {
            if (self::$lastMicroseconds) {
                $dt->setTime($dt->format('G'), $dt->format('i'), $dt->format('s'), self::$lastMicroseconds);
            }
        }

        if (!$dt) {
            throw new Exception('Invalid date format.');
        }

        return $dt;
    }

    /**
     * Set datetime setDatetimeBoundary
     * The following time units are supported:
     * s (seconds), m (minutes), h (hours), d (days), w (weeks), M (months), and y (years)
     * 
     * @param Dt $dt
     * @param ?string $timeUnit The following time units are supported:
     * @param bool $endOfRange
     * @return DateTime
     */
    public static function setDatetimeBoundary(Dt $dt, ?string $timeUnit, bool $endOfRange = false)
    {
        switch ($timeUnit) {
            case 's':
                if ($endOfRange) {
                    $dt->endOfSecond();
                    //$dt->setTime($dt->format('G'), $dt->format('i'), $dt->format('s'), 999999);
                } else {
                    $dt->startOfSecond();
                    //$dt->setTime($dt->format('G'), $dt->format('i'), $dt->format('s'), 0);
                }
                break;

            case 'm':
                if ($endOfRange) {
                    $dt->endOfMinute();
                    //$dt->setTime($dt->format('G'), $dt->format('i'), 59, 999999);
                } else {
                    $dt->startOfMinute();
                    //$dt->setTime($dt->format('G'), $dt->format('i'), 0, 0);
                }
                break;

            case 'h':
                if ($endOfRange) {
                    $dt->setTime($dt->format('G'), 59, 59, 999999);
                } else {
                    $dt->setTime($dt->format('G'), 0, 0, 0);
                }
                break;

            case 'd':
                if ($endOfRange) {
                    $dt->setTime(23, 59, 59, 999999);
                } else {
                    $dt->setTime(0, 0, 0, 0);
                }
                break;

            case 'w':
                if ($endOfRange) {
                    $dt->modify('sunday this week');
                    $dt->setTime(23, 59, 59, 999999);
                } else {
                    $dt->modify('monday this week');
                    $dt->setTime(0, 0, 0, 0);
                }
                break;

            case 'M':
            case 'mo':
                if ($endOfRange) {
                    $dt->endOfMonth();
                    //$dt->modify('last day of this month');
                    //$dt->setTime(23, 59, 59, 999999);
                } else {
                    $dt->startOfMonth();
                    //$dt->modify('first day of this month');
                    //$dt->setTime(0, 0, 0, 0);
                }
                break;

            case 'y':
                if ($endOfRange) {
                    $dt->setDate($dt->format('Y'), 12, 31);
                    $dt->setTime(23, 59, 59, 999999);
                } else {
                    $dt->setDate($dt->format('Y'), 1, 1);
                    $dt->setTime(0, 0, 0, 0);
                }
                break;
        }

        return $dt;
    }

    /**
     * Converts seconds to hours minutes and seconds
     * @param int $seconds
     * @return string
     */
    public static function secondsToWords($seconds): string
    {
        if (intval($seconds) <= 0) {
            return '0s';
        }

        $h = floor($seconds / 3600);
        $m = floor(($seconds - ($h * 3600)) / 60);
        $s = $seconds - ($h * 3600) - ($m * 60);

        if ($seconds >= 3600) {
            return $h . 'h ' . $m . 'm ' . $s . 's';
        } elseif ($seconds >= 60) {
            return $m . 'm ' . $s . 's';
        } else {
            return $s . 's';
        }
    }

    /**
     * Converts a MySQL Timestamp to Unix
     *
     * @param int MySQL timestamp YYYY-MM-DD HH:MM:SS
     * @return int Unix timestamp
     */
    public static function mysqlToUnix(string $time = '')
    {
        // We'll remove certain characters for backward compatibility
        // since the formatting changed with MySQL 4.1
        // YYYY-MM-DD HH:MM:SS
        $time = str_replace(['-', ':', ' '], '', $time);

        // YYYYMMDDHHMMSS
        return mktime(
            substr($time, 8, 2),
            substr($time, 10, 2),
            substr($time, 12, 2),
            substr($time, 4, 2),
            substr($time, 6, 2),
            substr($time, 0, 4)
        );
    }

    /**
     * Checks if instance is day time (05:00 - 21:00)
     * @param int|string|DateTime $datetime
     * @param string|DateTimeZone $timezone String or DateTimeZone object Desired timezone
     * @param int|string $start Start of the day (number of minutes past midnight), default: 5:00 AM
     * @param int|string $end End of the day (number of minutes past midnight), default: 9:00 PM
     * @return boolean or null if error occurred
     */
    public static function isDay($datetime = 'now', $timezone = null, $start = 300, $end = 1260): bool
    {
        $tz = $timezone ? $timezone : self::$timezone;

        if (is_numeric($datetime)) {
            $dt = static::createFromTimestamp($datetime, $tz);
        } elseif (is_object($datetime) && $datetime instanceof Dt) {
            $dt = clone $datetime;
            if ($tz) {
                $dt->setTimezone($tz);
            }
        } else {
            $dt = new Dt($datetime, $tz);
        }

        // support time as string ("05:00")
        if (is_string($start) && preg_match('/^([0-9]{2}):([0-9]{2})$/', $start, $m)) {
            $start = ((int)$m[1] * 60) + (int)$m[2];
        }
        if (is_string($end) && preg_match('/^([0-9]{2}):([0-9]{2})$/', $end, $m)) {
            $end = ((int)$m[1] * 60) + (int)$m[2];
        }

        // convert to seconds
        $start = $start * 60;
        $end = $end * 60;

        // calculate seconds from the midnight
        $seconds = ($dt->hour * 3600) + ($dt->minute * 60) + $dt->second;

        // (00:00 <-> 05:00) AND (21:00 <-> 23:59)
        return $seconds >= $start && $seconds < $end;
    }

    /**
     * Checks if instance is night time (21:00 - 05:00)
     * @param int|string|DateTime $datetime
     * @param string|DateTimeZone $timezone String or DateTimeZone object
     * @param int $start Start of the day (number of minutes past midnight), default: 5:00 AM
     * @param int $end End of the day (number of minutes past midnight), default: 9:00 PM
     * @return bool or null if error occurred
     */
    public static function isNight($datetime = 'now', $timezone = null, $start = 300, $end = 1260): bool
    {
        return self::isDay($datetime, $timezone, $start, $end) === false;
    }

    /**
     * Generate list - days in week
     * @param string $width Available formats: narrow, wide
     * @return array
     */
    public static function listDays(string $width = 'narrow', string $locale = 'en'): array
    {
        $arr = [];
        $dt = new Dt('first monday of 2020');
        $dt->locale($locale);

        $format = $width == 'long' ? 'dddd' : 'ddd';
        for ($m = 0; $m < 7; ++$m) {
            $arr[$m] = $dt->isoFormat($format);
            $dt->addDay();
        }

        return $arr;
    }

    /**
     * Generate list - months in year
     * @param string $width Available formats: narrow, wide
     * @return array
     */
    public static function listMonths(string $width = 'narrow', string $locale = 'en'): array
    {
        $arr = [];
        $dt = Dt::createFromDate(2020, 1, 1);
        $dt->locale($locale);

        $format = $width == 'long' ? 'MMMM' : 'MMM';
        for ($m = 1; $m <= 12; ++$m) {
            $arr[$m] = $dt->isoFormat($format);
            $dt->addMonth();
        }

        return $arr;
    }

    /**
     * List Years
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function listYears(int $from = null, int $to = null): array
    {
        $arr = [];
        $from = (int)$from;
        $to = (int)$to;

        if (empty($from)) {
            $from = date('Y') - 100;
        }

        if (empty($to) || $to <= $from) {
            $to = date('Y');
        }

        for ($i = $from; $i <= $to; $i++) {
            $arr[$i] = $i;
        }

        return $arr;
    }

    /**
     * @deprecated
     * @see listYears()
     */
    public static function getYearOptions(int $from = null, int $to = null): array
    {
        return self::listYears($from, $to);
    }
}

<?php

declare(strict_types=1);

namespace meriksk\DateTime\Tests;

use DateTime;
use DateTimeZone;
use meriksk\DateTime\Dt;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /** @var DateTime */
    protected $now;

    /** @var DateTime */
    protected $dt;

    /** @var DateTimeZone */
    protected static $tzUtc;

    /** @var DateTimeZone */
    protected static $tzLocal;

    /** @var string */
    protected static $tzLocalName;

    /** @var int */
    protected static $TS_2021_12_31_143000;

    /** @var float (float) */
    protected static $TS_2021_12_31_143000_500;

    /** @var string */
    protected static $DATE_2021_12_31_143000 = '2021-12-31T14:30:00';

    /** @var string */
    protected static $DATE_2021_12_31_143000_UTC = '2021-12-31T14:30:00+00:00';

    /** @var DateTime */
    protected static $DT_2021_12_31_143000_UTC;

    /** @var DateTime */
    protected static $DT_2021_12_31_143000_EU_BRATISLAVA;



    public static function setUpBeforeClass(): void
    {
        // php settings
        ini_set('memory_limit', -1);
        ini_set('display_errors', E_ALL);
        ini_set('log_errors_max_len', 0);
        ini_set('zend.assertions', 1);
        ini_set('assert.exception', 1);
        ini_set('xdebug.show_exception_trace', 0);

        // timezones
        self::$tzLocalName = date_default_timezone_get();
        self::$tzLocal = new DateTimeZone(date_default_timezone_get());
        self::$tzUtc = new DateTimeZone('UTC');

        // set test date
        self::$TS_2021_12_31_143000 = 1640961000;
        self::$TS_2021_12_31_143000_500 = 1640961000.500;

        self::$DT_2021_12_31_143000_UTC = new Dt('now', 'UTC');
        self::$DT_2021_12_31_143000_UTC->setDate(2021, 12, 31)->setTime(14, 30, 0);

        self::$DT_2021_12_31_143000_EU_BRATISLAVA = new Dt();
        self::$DT_2021_12_31_143000_EU_BRATISLAVA->setTimezone(new DateTimeZone('Europe/Bratislava'));
        self::$DT_2021_12_31_143000_EU_BRATISLAVA->setDate(2021, 12, 31)->setTime(14, 30, 00);
    }

    protected function assertDateTime(DateTime $dt, $year, $month, $day, $hour = null, $minute = null, $second = null)
    {
        $actual = ['years' => $year, 'months' => $month, 'day' => $day];
        $expected = ['years' => $dt->year, 'months' => $dt->month, 'day' => $dt->day];

        if ($hour !== null) {
            $expected['hours'] = $dt->hour;
            $actual['hours'] = $hour;
        }
        if ($minute !== null) {
            $expected['minutes'] = $dt->minute;
            $actual['minutes'] = $minute;
        }
        if ($second !== null) {
            $expected['seconds'] = $dt->second;
            $actual['seconds'] = $second;
        }

        $this->assertSame($expected, $actual);
    }
}

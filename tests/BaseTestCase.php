<?php

declare(strict_types=1);

namespace meriksk\DateTime\Tests;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use meriksk\DateTime\Dt;


abstract class BaseTestCase extends TestCase
{

    /**
     * @var DateTime
     */
    protected $now;

    /**
     * @var DateTime
     */
    protected $dt;

    /**
     * @var int
     */
    protected static $TIMESTAMP_2021_12_31_143000;

    /**
     * @var float (float)
     */
    protected static $TIMESTAMP_2021_12_31_143000_500;

    /**
     * @var DateTime UTC timezone
     */
    protected static $DATETIME_2021_12_31_143000;

    /**
     * @var DateTime UTC timezone
     */
    protected static $DATETIME_2021_12_31_143000_EU_BRATISLAVA;

    /**
     * @var string
     */
    protected static $default_timezone;


    public static function setUpBeforeClass(): void
    {
        // save current timezone
        self::$default_timezone = date_default_timezone_get();

        // reset timezone
        date_default_timezone_set('UTC');

        // php settings
        ini_set('memory_limit', -1);
        ini_set('display_errors', E_ALL);
        ini_set('log_errors_max_len', 0);
        ini_set('zend.assertions', 1);
        ini_set('assert.exception', 1);
        ini_set('xdebug.show_exception_trace', 0);

        // set test date
        self::$TIMESTAMP_2021_12_31_143000 = 1640961000;
        self::$TIMESTAMP_2021_12_31_143000_500 = 1640961000.500;

        self::$DATETIME_2021_12_31_143000 = new Dt();
        self::$DATETIME_2021_12_31_143000->setDate(2021, 12, 31)->setTime(14, 30, 0);

        self::$DATETIME_2021_12_31_143000_EU_BRATISLAVA = new Dt();
        self::$DATETIME_2021_12_31_143000_EU_BRATISLAVA->setTimezone(new DateTimeZone('Europe/Bratislava'));
        self::$DATETIME_2021_12_31_143000_EU_BRATISLAVA->setDate(2021, 12, 31)->setTime(14, 30, 00);
    }

    public static function tearDownAfterClass(): void
    {
        date_default_timezone_set(self::$default_timezone);
    }

    protected function tearDown(): void
    {
        //date_default_timezone_set(self::$default_timezone);
        //self::$dt = NULL;
    }

    public function getDefaultTimezone()
    {
        return self::$default_timezone;
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

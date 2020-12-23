<?php

namespace meriksk\DateTime\Tests;

use PHPUnit\Framework\TestCase;
use DateTime;
use DateTimeZone;
use meriksk\DateTime\Dt;

class BaseTestCase extends TestCase
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
     * @var DateTime UTC timezone
     */
	protected $DATETIME_2021_12_31_143000_UTC;

 	/**
     * @var DateTime UTC timezone
     */
	protected $DATETIME_2021_12_31_143000_EU_BRATISLAVA;
	
    /**
     * @var string
     */
    private $default_timezone;



    protected function setUp()
    {
        // save current timezone
        $this->default_timezone = date_default_timezone_get();

		// set test date
		$this->DATETIME_2021_12_31_143000_UTC = new DateTime();
		$this->DATETIME_2021_12_31_143000_UTC->setTimezone(new DateTimeZone('UTC'));
		$this->DATETIME_2021_12_31_143000_UTC->setDate(2021, 12, 31)->setTime(14, 30, 00);

		$this->DATETIME_2021_12_31_143000_EU_BRATISLAVA = new DateTime();
		$this->DATETIME_2021_12_31_143000_EU_BRATISLAVA->setTimezone(new DateTimeZone('Europe/Bratislava'));
		$this->DATETIME_2021_12_31_143000_EU_BRATISLAVA->setDate(2021, 12, 31)->setTime(14, 30, 00);
    }

    protected function tearDown()
    {
        //date_default_timezone_set($this->default_timezone);
		//$this->dt = NULL;
    }
	
	public function getDefaultTimezone()
	{
		return $this->default_timezone;
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
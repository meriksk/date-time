<?php

namespace meriksk\DateTime\Tests;

use Exception;
use DateTimeZone;
use meriksk\DateTime\Tests\BaseTestCase;
use meriksk\DateTime\Dt;


class DateTimeTest extends BaseTestCase
{

	public function testConstruct()
	{
		$dt = new Dt();
		$this->assertIsObject($dt);
		$this->assertEquals($this->getDefaultTimezone(), $dt->getTimezone()->getName());
	}

	public function testCreate()
	{
		// no parameters (system default)
		$dt = Dt::now();
		$this->assertIsObject($dt);
		$this->assertEquals($this->getDefaultTimezone(), $dt->getTimezone()->getName());

		// now + passed timezone
		$dt = new Dt('now', 'Europe/Bratislava');
		$this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());

		// passed timestamp + timezone
		$dt = new Dt($this->DATETIME_2021_12_31_143000_UTC, 'US/Central');
		$this->assertEquals('US/Central', $dt->getTimezone()->getName());
		$this->assertEquals('08:30:00', $dt->format('H:i:s'));

		// passed time + timezone
		$dt = new Dt('16:30', 'Europe/Bratislava');
		$this->assertEquals('16:30:00', $dt->format('H:i:s'));
		$dt->setTimezone('UTC');
		$this->assertEquals('15:30:00', $dt->format('H:i:s'));

		// timestamp
		$dt = new Dt($this->TIMESTAMP_2021_12_31_143000, 'Europe/Bratislava');
		$dt->setTimezone('UTC');
		$this->assertEquals('13:30:00', $dt->format('H:i:s'));
	}

	public function testCheckTimezone()
	{
		// string (valid timezone)
		$tz = Dt::checkTimezone('UTC');
		$this->assertIsObject($tz);
		$this->assertInstanceOf('DateTimeZone', $tz);
		$this->assertEquals('UTC', $tz->getName());

		// object (valid timezone)
		$tz = Dt::checkTimezone(new DateTimeZone('Europe/Bratislava'));
		$this->assertIsObject($tz);
		$this->assertInstanceOf('DateTimeZone', $tz);
		$this->assertEquals('Europe/Bratislava', $tz->getName());

		// invalid timezone
		$this->expectException(Exception::class);
		$tz = Dt::checkTimezone(123);
	}

	public function testCreateTimestamp()
	{
		$ts = Dt::createTimestamp('31.12.2021 08:30:00', 'Europe/Bratislava');
		$this->assertIsInt($ts);
		$this->assertEquals(1640935800, $ts);
	}

	public function testGetFormat()
	{
		// default locale (en)
		$f = Dt::getFormat('date_time');
		$this->assertEquals('n/j/Y g:i:s A', $f);

		// slovak locale
		$f = Dt::getFormat('date_time', null, 'sk');
		$this->assertEquals('j.n.Y H:i:s', $f);

		// slovak locale + convert MOMENT
		$f = Dt::getFormat('date_time', Dt::TARGET_MOMENT, 'sk');
		$this->assertEquals('D.M.YYYY HH:mm:ss', $f);

		// slovak locale + convert YII2
		$f = Dt::getFormat('date_time', Dt::TARGET_YII2, 'sk');
		$this->assertEquals('d.M.yyyy HH:mm:ss', $f);

	}

	public function testConvertFormat()
	{
		// named format (n/j/Y => M/d/yyyy)
		$f = Dt::convertFormat('date', Dt::TARGET_ICU);
		$this->assertEquals('M/d/yyyy', $f);

		// named format (sk) (j.n.Y => d.M.yyyy)
		$f = Dt::convertFormat('date', Dt::TARGET_YII2, 'sk');
		$this->assertEquals('d.M.yyyy', $f);
	}

    public function testF()
    {
		// timezone UTC
		$_dt = clone $this->DATETIME_2021_12_31_143000_UTC;
		$_ts = $_dt->getTimestamp();

		// timezone Europe/Bratislava
		$_dt_ba = clone $this->DATETIME_2021_12_31_143000_EU_BRATISLAVA;
		$_ts_ba = $_dt_ba->getTimestamp();

		// en (default locale)
		$d = Dt::f('date', $_ts);
		$this->assertEquals('12/31/2021', $d);
		$d = Dt::f('date_time', $_ts);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);
		$d = Dt::f('date_time', $_ts, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 3:30:00 PM', $d);

		// timestamp, utc
		$d = Dt::f('date_time', $_ts);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// timestamp, Europe/Bratislava
		$d = Dt::f('date_time', $_ts, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 3:30:00 PM', $d);

		// use DateTime object, utc
		$d = Dt::f('date_time', $_dt);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// use DateTime object, change timezone -> utc
		$d = Dt::f('date_time', $_dt_ba, 'UTC');
		$this->assertEquals('12/31/2021 1:30:00 PM', $d);

		// use DateTime object, Europe/Bratislava
		$d = Dt::f('date_time', $_dt_ba, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// sk
		$d = Dt::f('date', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_time', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00', $d);
		$d = Dt::f('date_time', $_ts, 'Europe/Bratislava', 'sk');
		$this->assertEquals('31.12.2021 15:30:00', $d);

		// sk (custom format)
		$d = Dt::f('l, D, F, j.n.Y H:i', $_ts, 'Europe/Bratislava', 'sk');
		$this->assertEquals('piatok, pi, december, 31.12.2021 15:30', $d);

		// named formats
		$d = Dt::f('date', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_short', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_medium', $_ts, null, 'sk');
		$this->assertEquals('31 dec 2021', $d);
		$d = Dt::f('date_long', $_ts, null, 'sk');
		$this->assertEquals('31 december 2021', $d);
		$d = Dt::f('date_full', $_ts, null, 'sk');
		$this->assertEquals('31 december 2021 UTC', $d);

		$d = Dt::f('date_time', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00', $d);
		$d = Dt::f('date_time_short', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30', $d);
		$d = Dt::f('date_time_short_tz', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30 UTC', $d);
		$d = Dt::f('date_time_medium', $_ts, null, 'sk');
		$this->assertEquals('31. dec 2021 14:30', $d);
		$d = Dt::f('date_time_long', $_ts, null, 'sk');
		$this->assertEquals('31. december 2021 14:30:00', $d);
		$d = Dt::f('date_time_full', $_ts, null, 'sk');
		$this->assertEquals('31. december 2021 14:30:00 UTC', $d);
		$d = Dt::f('date_time_tz', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00 UTC', $d);
		//$d = Dt::f('date_time_ms', $_ts, null, 'sk');
		//$this->assertEquals('31.12.2021 14:30', $d);
		$d = Dt::f('date_human', $_ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);

		$d = Dt::f('time', $_ts, null, 'sk');
		$this->assertEquals('14:30:00', $d);
		$d = Dt::f('time_short', $_ts, null, 'sk');
		$this->assertEquals('14:30', $d);
		$d = Dt::f('time_short_tz', $_ts, null, 'sk');
		$this->assertEquals('14:30 UTC', $d);
		$d = Dt::f('time_medium', $_ts, null, 'sk');
		$this->assertEquals('14:30:00', $d);
		//$d = Dt::f('time_long', $_ts, null, 'sk');
		//$this->assertEquals('14:30:00.u', $d);
		$d = Dt::f('time_full', $_ts, null, 'sk');
		$this->assertEquals('14:30:00 UTC', $d);
		//$d = Dt::f('time_ms', $_ts, null, 'sk');
		//$this->assertEquals('14:30:00.u'', $d);
    }

    public function testGetTime()
    {
		// base time
		$baseDt = clone $this->DATETIME_2021_12_31_143000_UTC;

		$dt = Dt::getTime('day', $baseDt);
		$this->assertEquals('2021-12-31 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('-1d', $baseDt);
		$this->assertEquals('2021-12-30 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('+1d', $baseDt);
		$this->assertEquals('2022-01-01 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('+1d', $baseDt);
		$this->assertEquals('2022-01-01 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('year', $baseDt);
		$this->assertEquals('2021-01-01 00:00:00', $dt->toDateTimeString());

		// custom modifier
		$dt = Dt::getTime('-2 hour -5 minutes', $baseDt);
		$this->assertEquals('2021-12-31 12:25:00', $dt->toDateTimeString());
	}

    public function testGetBoundaries()
    {
		// 2021-12-31 14:30:00
		$dt = clone $this->DATETIME_2021_12_31_143000_UTC;
		$ts = $dt->getTimestamp();

		// object
		$b = Dt::getBoundaries('day', true, $this->DATETIME_2021_12_31_143000_EU_BRATISLAVA->getTimestamp(), 'Europe/Bratislava');
		$this->assertEquals('2021-12-31 00:00:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString());

		// 'm', 'minute'
		$b = Dt::getBoundaries('minute', true, $ts);
		$this->assertEquals('2021-12-31 14:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:59', $b[1]->toDateTimeString());

		// 'h', 'hour'
		$b = Dt::getBoundaries('hour', true, $ts);
		$this->assertEquals('2021-12-31 14:00:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:59:59', $b[1]->toDateTimeString());

		// 'day', 'today'
		$b = Dt::getBoundaries('day', true, $ts);
		$this->assertEquals('2021-12-31 00:00:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString());

		// '-1d', '-1 day', '1 day ago', 'day ago', 'yesterday', 'previous day'
		$b = Dt::getBoundaries('-1d', true, $ts);
		$this->assertEquals('2021-12-30 00:00:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-30 23:59:59', $b[1]->toDateTimeString());

		// '+1d', '+1 day', 'tomorrow', 'next day'
		$b = Dt::getBoundaries('+1d', true, $ts);
		$this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString());
		$this->assertEquals('2022-01-01 23:59:59', $b[1]->toDateTimeString());

		// 'w', 'week', 'this week', 'current week'
		$b = Dt::getBoundaries('week', true, $ts);
		$this->assertEquals('2021-12-27 00:00:00', $b[0]->toDateTimeString()); // monday
		$this->assertEquals('2022-01-02 23:59:59', $b[1]->toDateTimeString()); // sunday

		// '-1w', '-1 week', 'previous week', 'week ago', 'one week ago'
		$b = Dt::getBoundaries('-1 week', true, $ts);
		$this->assertEquals('2021-12-20 00:00:00', $b[0]->toDateTimeString()); // monday
		$this->assertEquals('2021-12-26 23:59:59', $b[1]->toDateTimeString()); // sunday

		// '+1w', '+1 week', 'next week'
		$b = Dt::getBoundaries('+1 week', true, $ts);
		$this->assertEquals('2022-01-03 00:00:00', $b[0]->toDateTimeString()); // monday
		$this->assertEquals('2022-01-09 23:59:59', $b[1]->toDateTimeString()); // sunday

		// 'month', 'this month', 'current month'
		$b = Dt::getBoundaries('month', true, $ts);
		$this->assertEquals('2021-12-01 00:00:00', $b[0]->toDateTimeString()); // start of month
		$this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString()); // end of month

		// '-1 month', '1 month ago', 'month ago', 'one month ago'
		$b = Dt::getBoundaries('-1 month', true, $ts);
		$this->assertEquals('2021-11-01 00:00:00', $b[0]->toDateTimeString()); // start of month
		$this->assertEquals('2021-11-30 23:59:59', $b[1]->toDateTimeString()); // end of month

		// '+1 month', 'next month'
		$b = Dt::getBoundaries('+1 month', true, $ts);
		$this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString()); // start of month
		$this->assertEquals('2022-01-31 23:59:59', $b[1]->toDateTimeString()); // end of month

		// 'year', 'this year', 'current year'
		$b = Dt::getBoundaries('year', true, $ts);
		$this->assertEquals('2021-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
		$this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year

		// '-1y', '-1 year', 'previous year', 'year ago', 'one year ago'
		$b = Dt::getBoundaries('-1 year', true, $ts);
		$this->assertEquals('2020-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
		$this->assertEquals('2020-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year

		// '+1y', '+1 year', 'next year'
		$b = Dt::getBoundaries('+1 year', true, $ts);
		$this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
		$this->assertEquals('2022-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year
    }

    public function testGetRelativeTime()
    {
		// 2021-12-31 14:30:00
		$dt = clone $this->DATETIME_2021_12_31_143000_UTC;
		
		$rt = Dt::getRelativeTime('minute', 1, true, $dt);
		$this->assertEquals('2021-12-31 14:29:00', $rt->toDateTimeString());

		$rt = Dt::getRelativeTime('hour', 1, true, $dt);
		$this->assertEquals('2021-12-31 13:30:00', $rt->toDateTimeString());
				
		$rt = Dt::getRelativeTime('day', 1, true, $dt);
		$this->assertEquals('2021-12-30 14:30:00', $rt->toDateTimeString());
				
		$rt = Dt::getRelativeTime('week', 1, true, $dt);
		$this->assertEquals('2021-12-24 14:30:00', $rt->toDateTimeString());
				
		$rt = Dt::getRelativeTime('month', 1, true, $dt);
		$this->assertEquals('2021-12-01 14:30:00', $rt->toDateTimeString());

		$rt = Dt::getRelativeTime('year', 1, true, $dt);
		$this->assertEquals('2020-12-31 14:30:00', $rt->toDateTimeString());
		
		// timezone
		$rt = Dt::getRelativeTime('minute', 1, true, $dt, 'Europe/Bratislava');
		$this->assertEquals('2021-12-31 15:29:00', $rt->toDateTimeString());
	}
	
    public function testGetRelativeTimeBoundaries()
    {
		// 2021-12-31 14:30:00
		$dt = clone $this->DATETIME_2021_12_31_143000_UTC;
		
		$b = Dt::getRelativeTimeBoundaries('minute', 1, true, $dt);
		$this->assertEquals('2021-12-31 14:29:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());

		$b = Dt::getRelativeTimeBoundaries('hour', 1, true, $dt);
		$this->assertEquals('2021-12-31 13:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());
				
		$b = Dt::getRelativeTimeBoundaries('day', 1, true, $dt);
		$this->assertEquals('2021-12-30 14:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());
				
		$b = Dt::getRelativeTimeBoundaries('week', 1, true, $dt);
		$this->assertEquals('2021-12-24 14:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());
				
		$b = Dt::getRelativeTimeBoundaries('month', 1, true, $dt);
		$this->assertEquals('2021-12-01 14:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());

		$b = Dt::getRelativeTimeBoundaries('year', 1, true, $dt);
		$this->assertEquals('2020-12-31 14:30:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());
		
		// timezone
		$b = Dt::getRelativeTimeBoundaries('minute', 1, true, $dt, 'Europe/Bratislava');
		$this->assertEquals('2021-12-31 15:29:00', $b[0]->toDateTimeString());
		$this->assertEquals('2021-12-31 15:30:00', $b[1]->toDateTimeString());
	}
	
	public function testSecondsToWords()
	{
		$this->assertEquals('1s', Dt::secondsToWords(1));
		$this->assertEquals('59s', Dt::secondsToWords(59));
		$this->assertEquals('1m 0s', Dt::secondsToWords(60));
		$this->assertEquals('2m 5s', Dt::secondsToWords(125));
		$this->assertEquals('5m 30s', Dt::secondsToWords(330));
		$this->assertEquals('1h 0m 10s', Dt::secondsToWords(3610));
	}

	public function testP()
	{

		// custom format
		$dt = Dt::p('12_24_2017 14:30:00', 'n_j_Y H:i:s');
		$this->assertIsObject($dt);
		$this->assertEquals('2017-12-24 14:30:00', $dt->toDateTimeString());

		// named format, en
		$dt = Dt::p('12/24/2017', 'date')->setTime(14, 30, 0);
		$this->assertIsObject($dt);
		$this->assertEquals('2017-12-24 14:30:00', $dt->toDateTimeString());

		// named format, tz, en
		$dt = Dt::p('12/24/2017 4:30:15 PM', 'date_time', 'Europe/Bratislava', 'en');
		$this->assertEquals('2017-12-24 16:30:15', $dt->toDateTimeString());
		$this->assertEquals('Europe/Bratislava', $dt->tzName);

		// named format, tz, sk
		$dt = Dt::p('24.12.2017 16:30:15', 'date_time', 'Europe/London', 'sk');
		$this->assertEquals('2017-12-24 16:30:15', $dt->toDateTimeString());
		$this->assertEquals('Europe/London', $dt->tzName);

		// invalid format
		$dt = Dt::p('12/24/2017 4:30:15 PM', 'date_time', 'Europe/London', 'sk');
		$this->assertFalse($dt);
	}

	public function testFormat()
	{
		$dt = Dt::p('12/24/2017 4:30:00 AM', 'date_time', 'UTC');
		$this->assertEquals($dt->format('r'), 'Sun, 24 Dec 2017 04:30:00 +0000');
	}

	/*
	public function testYear()
	{
		$dt = self::now();
		$dt->year(2020);
		$this->assertSame(2020, $dt->year);
	}

	public function testYearSetter()
	{
		$dt = Datetime::now();
		$dt->year = 2020;
		$this->assertSame(2020, $dt->year);
	}
	*/

	public function testIsDay()
	{
		// is day (05:00 - 21:00)

		// no timezone
		$dt1 = Dt::create('2017-05-01 04:59:59', 'Europe/Bratislava');
		$dt2 = Dt::create('2017-05-01 05:00:00', 'Europe/Bratislava');
		$dt3 = Dt::create('2017-05-01 20:59:59', 'Europe/Bratislava');
		$dt4 = Dt::create('2017-05-01 21:00:00', 'Europe/Bratislava');

		$this->assertFalse($dt1->isDay());
		$this->assertTrue($dt2->isDay());
		$this->assertTrue($dt3->isDay());
		$this->assertFalse($dt4->isDay());

		// custom time-frame (02:00 - 03:00)
		$dt1 = Dt::create('2017-05-01 01:59:59', 'Europe/Bratislava');
		$dt2 = Dt::create('2017-05-01 02:00:00', 'Europe/Bratislava');
		$dt3 = Dt::create('2017-05-01 02:59:59', 'Europe/Bratislava');
		$dt4 = Dt::create('2017-05-01 03:00:00', 'Europe/Bratislava');

		$this->assertFalse($dt1->isDay(null, 120, 180));
		$this->assertFalse($dt1->isDay(null, '02:00', '03:00'));
		$this->assertTrue($dt2->isDay(null, 120, 180));
		$this->assertTrue($dt2->isDay(null, '02:00', '03:00'));
		$this->assertTrue($dt3->isDay(null, 120, 180));
		$this->assertTrue($dt3->isDay(null, '02:00', '03:00'));
		$this->assertFalse($dt4->isDay(null, 120, 180));
		$this->assertFalse($dt4->isDay(null, '02:00', '03:00'));

		// diferent timezone
		// UTC (6:30 AM) -> US/Central (01:30 AM)
		$dt = Dt::create('2017-05-01 6:30:00 AM', 'UTC');
		$this->assertFalse($dt->isDay('US/Central'));
	}

	public function testIsNight()
	{
		// is night (21:00 - 05:00)

		// no timezone
		$dt1 = Dt::create('2017-05-01 20:59:59', 'Europe/Bratislava');
		$dt2 = Dt::create('2017-05-01 21:00:00', 'Europe/Bratislava');
		$dt3 = Dt::create('2017-05-01 04:59:59', 'Europe/Bratislava');
		$dt4 = Dt::create('2017-05-01 05:00:00', 'Europe/Bratislava');

		$this->assertFalse($dt1->isNight());
		$this->assertTrue($dt2->isNight());
		$this->assertTrue($dt3->isNight());
		$this->assertFalse($dt4->isNight());
	}

}
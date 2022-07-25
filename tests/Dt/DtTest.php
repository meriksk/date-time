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
		// default timezone
		$dt = new Dt();
		$this->assertIsObject($dt);
		$this->assertEquals('UTC', $dt->getTimezone()->getName());

		// custom timezone (object)
		$dt = new Dt('now', new DateTimeZone('Europe/Bratislava'));
		$this->assertIsObject($dt);
		$this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());

		// custom timezone (string)
		$dt = new Dt('now', 'Europe/Bratislava');
		$this->assertIsObject($dt);
		$this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());
	}

	public function testCreate()
	{
		// no parameters (system default)
		$dt = Dt::now();
		$this->assertIsObject($dt);
		$this->assertEquals('UTC', $dt->getTimezone()->getName());

		// UTC date
		$dt = new Dt('2021-12-31 14:30:00');
		$this->assertEquals('UTC', $dt->getTimezone()->getName());
		$this->assertEquals('14:30:00', $dt->format('H:i:s'));

		// UTC date - different timezone
		$dt = new Dt('2021-12-31 14:30:00');		
		$this->assertEquals('14:30:00', $dt->format('H:i:s'));
		$this->assertEquals('UTC', $dt->getTimezone()->getName());
		$dt->setTimezone('Europe/Bratislava');
		$this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());
		$this->assertEquals('15:30:00', $dt->format('H:i:s'));

		// passed time
		$dt = new Dt('16:30');
		$this->assertEquals('16:30:00', $dt->format('H:i:s'));

		// timestamp
		$dt = new Dt(self::$TIMESTAMP_2021_12_31_143000);
		$this->assertEquals('14:30:00', $dt->format('H:i:s'));
		$dt->setTimezone('Europe/Bratislava');
		$this->assertEquals('15:30:00', $dt->format('H:i:s'));
		
		// timestamp (float)
		$dt = new Dt(self::$TIMESTAMP_2021_12_31_143000_500);
		$this->assertEquals('14:30:00.500000', $dt->format('H:i:s.u'));
		$dt->setTimezone('Europe/Bratislava');
		$this->assertEquals('15:30:00.500000', $dt->format('H:i:s.u'));
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

		// timezone (system)
		$dt = new Dt('2021-12-31 14:30:00');
		$ts = $dt->getTimestamp();

		// timezone (Europe/Bratislava)
		$dt_ba = new Dt('2021-12-31 14:30:00', 'Europe/Bratislava');
		$ts_ba = $dt_ba->getTimestamp();

		// en (default locale)
		$d = Dt::f('date', $ts);
		$this->assertEquals('12/31/2021', $d);
		$d = Dt::f('date_time', $ts);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);
		$d = Dt::f('date_time', $ts, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 3:30:00 PM', $d);

		// timestamp, utc
		$d = Dt::f('date_time', $ts);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// timestamp, Europe/Bratislava
		$d = Dt::f('date_time', $ts, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 3:30:00 PM', $d);

		// use DateTime object, utc
		$d = Dt::f('date_time', $dt);
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// use DateTime object, change timezone -> utc
		$d = Dt::f('date_time', $dt_ba, 'UTC');
		$this->assertEquals('12/31/2021 1:30:00 PM', $d);

		// use DateTime object, Europe/Bratislava
		$d = Dt::f('date_time', $dt_ba, 'Europe/Bratislava');
		$this->assertEquals('12/31/2021 2:30:00 PM', $d);

		// sk
		$d = Dt::f('date', $ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_time', $ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00', $d);
		$d = Dt::f('date_time', $ts, 'Europe/Bratislava', 'sk');
		$this->assertEquals('31.12.2021 15:30:00', $d);

		// sk (custom format)
		$d = Dt::f('l, D, F, j.n.Y H:i', $ts, 'Europe/Bratislava', 'sk');
		$this->assertEquals('piatok, pia, december, 31.12.2021 15:30', $d);

		// named formats
		$d = Dt::f('date', $ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_short', $ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);
		$d = Dt::f('date_medium', $ts, null, 'sk');
		$this->assertEquals('31 dec 2021', $d);
		$d = Dt::f('date_long', $ts, null, 'sk');
		$this->assertEquals('31 december 2021', $d);
		$d = Dt::f('date_full', $ts, null, 'sk');
		$this->assertEquals('31 december 2021 UTC', $d);

		$d = Dt::f('date_time', $ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00', $d);
		$d = Dt::f('date_time_short', $ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30', $d);
		$d = Dt::f('date_time_short_tz', $ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30 UTC', $d);
		$d = Dt::f('date_time_medium', $ts, null, 'sk');
		$this->assertEquals('31. dec 2021 14:30', $d);
		$d = Dt::f('date_time_long', $ts, null, 'sk');
		$this->assertEquals('31. december 2021 14:30:00', $d);
		$d = Dt::f('date_time_full', $ts, null, 'sk');
		$this->assertEquals('31. december 2021 14:30:00 UTC', $d);
		$d = Dt::f('date_time_tz', $ts, null, 'sk');
		$this->assertEquals('31.12.2021 14:30:00 UTC', $d);
		//$d = Dt::f('date_time_ms', $ts, null, 'sk');
		//$this->assertEquals('31.12.2021 14:30', $d);
		$d = Dt::f('date_human', $ts, null, 'sk');
		$this->assertEquals('31.12.2021', $d);

		$d = Dt::f('time', $ts, null, 'sk');
		$this->assertEquals('14:30:00', $d);
		$d = Dt::f('time_short', $ts, null, 'sk');
		$this->assertEquals('14:30', $d);
		$d = Dt::f('time_short_tz', $ts, null, 'sk');
		$this->assertEquals('14:30 UTC', $d);
		$d = Dt::f('time_medium', $ts, null, 'sk');
		$this->assertEquals('14:30:00', $d);
		//$d = Dt::f('time_long', $ts, null, 'sk');
		//$this->assertEquals('14:30:00.u', $d);
		$d = Dt::f('time_full', $ts, null, 'sk');
		$this->assertEquals('14:30:00 UTC', $d);
		//$d = Dt::f('time_ms', $ts, null, 'sk');
		//$this->assertEquals('14:30:00.u'', $d);
    }

    public function testGetTime()
    {
		// test date
		$baseDt = new Dt('2021-12-31 14:30:00');

		$dt = Dt::getTime('day', true, $baseDt);
		$this->assertEquals('2021-12-31 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('-1d', true, $baseDt);
		$this->assertEquals('2021-12-30 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('+1d', true, $baseDt);
		$this->assertEquals('2022-01-01 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('+1d', true, $baseDt);
		$this->assertEquals('2022-01-01 00:00:00', $dt->toDateTimeString());

		$dt = Dt::getTime('year', true, $baseDt);
		$this->assertEquals('2021-01-01 00:00:00', $dt->toDateTimeString());

		// timezone
		$rt = Dt::getTime('hour', true, $baseDt, 'Europe/Bratislava');
		$this->assertEquals('2021-12-31 15:00:00', $rt->toDateTimeString());
	}

    public function testGetBoundaries()
    {
		// test date
		$dt = new Dt('2021-12-31 14:30:00');
		$ts = $dt->getTimestamp();

		// object
		$b = Dt::getBoundaries('day', true, self::$DATETIME_2021_12_31_143000_EU_BRATISLAVA->getTimestamp(), 'Europe/Bratislava');
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
		// test date
		$dt = new Dt('2021-12-31 14:30:00');

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
		// test date
		$dt = new Dt('2021-12-31 14:30:00');
		
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
		$b = Dt::getRelativeTimeBoundaries('hour', 1, true, $dt, 'Europe/Bratislava');
		$this->assertEquals('2021-12-31 14:30:00', $b[0]->toDateTimeString());
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
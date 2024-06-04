<?php

declare(strict_types=1);

namespace meriksk\DateTime\Tests\unit;

use DateTimeZone;
use InvalidArgumentException;
use meriksk\DateTime\Tests\BaseTestCase;
use meriksk\DateTime\Dt;

class DtTest extends BaseTestCase
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
        $this->assertEquals('UTC', $dt->getTimezone()->getName());
        $dt->setTimezone('Europe/Bratislava');
        $this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());
        $this->assertEquals('15:30:00', $dt->format('H:i:s'));

        // passed time
        $dt = new Dt('16:30');
        $this->assertEquals('16:30:00', $dt->format('H:i:s'));

        // timestamp
        $dt = new Dt(self::$TIMESTAMP_2021_12_31_143000, 'Europe/Bratislava');
        $this->assertEquals('14:30:00', $dt->format('H:i:s'));

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

        // summer time (2h diff)
        $dt = new Dt('2021-08-15 14:30:00', 'Europe/Bratislava');
        $this->assertEquals('Europe/Bratislava', $dt->getTimezone()->getName());
        $this->assertEquals('2021-08-15 14:30:00', $dt->format('Y-m-d H:i:s'));
        $dt->setTimezone('UTC');
        $this->assertEquals('2021-08-15 12:30:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testCheckTimezone(): void
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
        $this->expectException(InvalidArgumentException::class);
        $tz = Dt::checkTimezone(123);
    }

    public function testCreateTimestamp()
    {
        // default timezone
        $ts = Dt::createTimestamp('31.12.2021 08:30:00');
        $this->assertIsInt($ts);
        $this->assertEquals(1640939400, $ts);

        // different timezone
        $ts = Dt::createTimestamp('31.12.2021 08:30:00', 'Europe/Bratislava');
        $this->assertIsInt($ts);
        $this->assertEquals(1640935800, $ts);
    }

    public function testGetFormat()
    {
        // default locale (en)
        $f = Dt::getFormat('date_time');
        $this->assertEquals('n/j/Y g:i:s A', $f);

        // specific locale - without conversion
        $f = Dt::getFormat('date_time', null, 'sk');
        $this->assertEquals('j.n.Y H:i:s', $f);

        // specific locale - format conversion
        $f = Dt::getFormat('date_time', Dt::TARGET_ICU, 'sk');
        $this->assertEquals('d.M.yyyy HH:mm:ss', $f);

        // specific locale + format conversion (MomentJs)
        $f = Dt::getFormat('date_time', Dt::TARGET_MOMENT, 'sk');
        $this->assertEquals('D.M.YYYY HH:mm:ss', $f);
    }

    public function testConvertFormat()
    {
        // alias (PHP/ICU: n/j/Y => M/d/yyyy)
        $f = Dt::convertFormat('date', Dt::TARGET_ICU);
        $this->assertEquals('M/d/yyyy', $f);

        // alias (PHP/ICU: j.n.Y => d.M.yyyy) - sk
        $f = Dt::convertFormat('date', Dt::TARGET_ICU, 'sk');
        $this->assertEquals('d.M.yyyy', $f);

        // alias (PHP/MOMENT: j.n.Y => d.M.yyyy) - sk
        $f = Dt::convertFormat('date', Dt::TARGET_MOMENT, 'sk');
        $this->assertEquals('D.M.YYYY', $f);

        // custom format (PHP/MOMENT: m/d/Y H:i:s)
        $f = Dt::convertFormat('m/d/Y H:i:s', Dt::TARGET_MOMENT);
        $this->assertEquals('MM/DD/YYYY HH:mm:ss', $f);
    }

    public function testF()
    {
        // timezone (system)
        $dtUtc = new Dt('2021-12-31 14:30:00', 'UTC');
        $ts = $dtUtc->getTimestamp();

        // timezone (Europe/Bratislava)
        $dtEurope = new Dt('2021-12-31 14:30:00', 'Europe/Bratislava');
        $ts_ba = $dtEurope->getTimestamp();

        // timestamp, no timezone
        $d = Dt::f('Y-m-d H:i:s e', $dtUtc->getTimestamp());
        $this->assertSame('2021-12-31 14:30:00 +00:00', $d);

        // timestamp, timezone
        $d = Dt::f('Y-m-d H:i:s e', $dtUtc->getTimestamp(), 'Europe/Bratislava');
        $this->assertSame('2021-12-31 15:30:00 Europe/Bratislava', $d);

        // DateTime object, no timezone conversion
        $d = Dt::f('Y-m-d H:i:s e', $dtUtc);
        $this->assertSame('2021-12-31 14:30:00 UTC', $d);

        // DateTime object, no timezone conversion
        $d = Dt::f('Y-m-d H:i:s e', $dtEurope);
        $this->assertSame('2021-12-31 14:30:00 Europe/Bratislava', $d);

        // DateTime object, timezone conversion UTC -> Europe
        $d = Dt::f('Y-m-d H:i:s e', $dtUtc, 'Europe/Bratislava');
        $this->assertSame('2021-12-31 15:30:00 Europe/Bratislava', $d);

        // use DateTime object, Europe/Bratislava -> Europe/Bratislava
        $d = Dt::f('Y-m-d H:i:s e', $dtEurope, 'Europe/Bratislava');
        $this->assertSame('2021-12-31 14:30:00 Europe/Bratislava', $d);

        // use DateTime object, Europe/Bratislava -> UTC
        $d = Dt::f('Y-m-d H:i:s e', $dtEurope, 'UTC');
        $this->assertSame('2021-12-31 13:30:00 UTC', $d);

        // specific locale
        $d = Dt::f('date_time', $ts, null, 'en');
        $this->assertEquals('12/31/2021 2:30:00 PM', $d);
        $d = Dt::f('date_time', $ts, null, 'sk');
        $this->assertEquals('31.12.2021 14:30:00', $d);
        $d = Dt::f('date_time', $dtUtc, 'Europe/Bratislava', 'sk');
        $this->assertEquals('31.12.2021 15:30:00', $d);

        // specific locale - translations - summer time
        $d = Dt::f('l, D, M, F, j.n.Y H:i', $dtUtc, 'Europe/Bratislava', 'sk');
        $this->assertEquals('piatok, pia, dec, december, 31.12.2021 15:30', $d);

        // specific locale - translations - winter time
        $d = Dt::f('l, D, M, F, j.n.Y H:i', $dtUtc, 'Europe/Bratislava', 'de');
        $this->assertEquals('Freitag, Fr., Dez, Dezember, 31.12.2021 15:30', $d);

        // specific locale - translations - winter time
        $d = Dt::f('l, D, M, F, j.n.Y H:i', $dtUtc, 'Europe/Bratislava', 'cs');
        $this->assertEquals('pátek, pát, pro, prosince, 31.12.2021 15:30', $d);

        // specific locale - translations - winter time
        $d = Dt::f('l, D, M, F, j.n.Y H:i', $dtUtc, 'Europe/Bratislava', 'cs_CZ');
        $this->assertEquals('pátek, pát, pro, prosince, 31.12.2021 15:30', $d);
    }

    public function testP()
    {
        // custom format
        $dt = Dt::p('12_24_2017 14:30:00', 'n_j_Y H:i:s');
        $this->assertIsObject($dt);
        $this->assertSame('2017-12-24 14:30:00 UTC', $dt->format('Y-m-d H:i:s e'));

        // named format, no locale
        $dt = Dt::p('12/24/2017', 'date')->setTime(14, 30, 0);
        $this->assertSame('2017-12-24 14:30:00 UTC', $dt->format('Y-m-d H:i:s e'));

        // named format, no tz, sk locale        
        $dt = Dt::p('24.12.2017', 'date', null, 'sk')->setTime(14, 30, 30);
        $this->assertSame('2017-12-24 14:30:30 UTC', $dt->format('Y-m-d H:i:s e'));

        // named format, tz, no locale
        $dt = Dt::p('12/24/2017 4:30:15 PM', 'date_time', 'Europe/Bratislava');
        $this->assertSame('2017-12-24 16:30:15 Europe/Bratislava', $dt->format('Y-m-d H:i:s e'));

        // named format, tz, sk
        $dt = Dt::p('24.12.2017 16:30:15', 'date_time', 'America/Chicago', 'sk');
        $this->assertSame('2017-12-24 16:30:15 America/Chicago', $dt->format('Y-m-d H:i:s e'));

        // invalid format
        $dt = Dt::p('12/24/2017', 'date', null, 'sk');
        $this->assertFalse($dt);
    }

    public function testResolveTime()
    {
        $now = new Dt('now');
        $yesterday = (new Dt('now'))->modify('-1 day');

        // now
        $dt0 = Dt::resolveTime('now');
        $this->assertIsObject($dt0);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $dt0->format('Y-m-d H:i:s'));
        // start of the minute
        $dt0 = Dt::resolveTime('now/m');
        $this->assertEquals($now->format('Y-m-d H:i:00'), $dt0->format('Y-m-d H:i:s'));
        // start of the current hour
        $dt0 = Dt::resolveTime('now/h');
        $this->assertEquals($now->format('Y-m-d H:00:00'), $dt0->format('Y-m-d H:i:s'));
        // start of the current day
        $dt0 = Dt::resolveTime('now/d');
        $this->assertEquals($now->format('Y-m-d 00:00:00'), $dt0->format('Y-m-d H:i:s'));
        // start of the current month
        $dt0 = Dt::resolveTime('now/M');
        $this->assertEquals($now->format('Y-m-01 00:00:00'), $dt0->format('Y-m-d H:i:s'));
        // start of the current year
        $dt0 = Dt::resolveTime('now/y');
        $this->assertEquals($now->format('Y-01-01 00:00:00'), $dt0->format('Y-m-d H:i:s'));
        // end of the current day
        $dt0 = Dt::resolveTime('now/d', true);
        $this->assertEquals($now->format('Y-m-d 23:59:59'), $dt0->format('Y-m-d H:i:s'));
        // yesterday
        $dt0 = Dt::resolveTime('-1d');
        $this->assertEquals($yesterday->format('Y-m-d H:i:s'), $dt0->format('Y-m-d H:i:s'));
        // yesterday - start of the day
        $dt0 = Dt::resolveTime('-1d/d');
        $this->assertEquals($yesterday->format('Y-m-d 00:00:00'), $dt0->format('Y-m-d H:i:s'));
        // yesterday - end of the day
        $dt0 = Dt::resolveTime('-1d/d', true);
        $this->assertEquals($yesterday->format('Y-m-d 23:59:59'), $dt0->format('Y-m-d H:i:s'));

        // timezone
        $dt0 = Dt::resolveTime('now/d', false, null, 'America/New_York');
        $this->assertEquals('America/New_York', $dt0->getTimezone()->getName());

        // base time
        // start of the day
        $dt0 = Dt::resolveTime('now/d', false, self::$DATETIME_2021_12_31_143000);
        $this->assertEquals('2021-12-31 00:00:00', $dt0->format('Y-m-d H:i:s'));
        // end of the day
        $dt0 = Dt::resolveTime('now/d', true, self::$DATETIME_2021_12_31_143000);
        $this->assertEquals('2021-12-31 23:59:59', $dt0->format('Y-m-d H:i:s'));
    }

    public function testGetTime()
    {
        // test date
        $baseDt = self::$DATETIME_2021_12_31_143000;

        $dt = Dt::getTime('day', true, $baseDt);
        $this->assertEquals('2021-12-31 00:00:00', $dt->toDateTimeString());

        $dt = Dt::getTime('-1d', true, $baseDt);
        $this->assertEquals('2021-12-30 00:00:00', $dt->toDateTimeString());

        $dt = Dt::getTime('+1d', true, $baseDt);
        $this->assertEquals('2022-01-01 00:00:00', $dt->toDateTimeString());

        $dt = Dt::getTime('+1w', true, $baseDt);
        $this->assertEquals('2022-01-03 00:00:00', $dt->toDateTimeString());

        $dt = Dt::getTime('year', true, $baseDt);
        $this->assertEquals('2021-01-01 00:00:00', $dt->toDateTimeString());

        // timezone
        $rt = Dt::getTime('hour', true, $baseDt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 14:00:00', $rt->toDateTimeString());
    }

    public function testGetBoundaries()
    {
        // test date
        $baseTime = self::$DATETIME_2021_12_31_143000;

        // object
        $b = Dt::getBoundaries('day', true, self::$DATETIME_2021_12_31_143000_EU_BRATISLAVA, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 00:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString());

        // 's', 'minute'
        $b = Dt::getBoundaries('second', true, $baseTime);
        $this->assertEquals('2021-12-31 14:30:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());

        // 'm', 'minute'
        $b = Dt::getBoundaries('minute', true, $baseTime);
        $this->assertEquals('2021-12-31 14:30:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 14:30:59', $b[1]->toDateTimeString());

        // '-2m'
        $b = Dt::getBoundaries('-2m', true, $baseTime);
        $this->assertEquals('2021-12-31 14:28:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 14:28:59', $b[1]->toDateTimeString());

        // 'h', 'hour'
        $b = Dt::getBoundaries('hour', true, $baseTime);
        $this->assertEquals('2021-12-31 14:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 14:59:59', $b[1]->toDateTimeString());

        // '-5h'
        $b = Dt::getBoundaries('-5h', true, $baseTime);
        $this->assertEquals('2021-12-31 09:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 09:59:59', $b[1]->toDateTimeString());

        // '+5h'
        $b = Dt::getBoundaries('+5h', true, $baseTime);
        $this->assertEquals('2021-12-31 19:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 19:59:59', $b[1]->toDateTimeString());

        // '+5h'
        $b = Dt::getBoundaries('+12h', true, $baseTime);
        $this->assertEquals('2022-01-01 02:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2022-01-01 02:59:59', $b[1]->toDateTimeString());

        // 'day', 'today'
        $b = Dt::getBoundaries('day', true, $baseTime);
        $this->assertEquals('2021-12-31 00:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString());

        // '-1d', 'yesterday'
        $b = Dt::getBoundaries('-1d', true, $baseTime);
        $this->assertEquals('2021-12-30 00:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-30 23:59:59', $b[1]->toDateTimeString());

        // '-3d', 'yesterday'
        $b = Dt::getBoundaries('-3d', true, $baseTime);
        $this->assertEquals('2021-12-28 00:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-28 23:59:59', $b[1]->toDateTimeString());

        // '+1d', 'tomorrow'
        $b = Dt::getBoundaries('+1d', true, $baseTime);
        $this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString());
        $this->assertEquals('2022-01-01 23:59:59', $b[1]->toDateTimeString());

        // 'w', 'week', 'this week', 'current week'    
        $b = Dt::getBoundaries('week', true, $baseTime);
        $this->assertEquals('2021-12-27 00:00:00', $b[0]->toDateTimeString()); // monday
        $this->assertEquals('2022-01-02 23:59:59', $b[1]->toDateTimeString()); // sunday

        // '-1w', 'previous week', 'week ago'
        $b = Dt::getBoundaries('-2w', true, $baseTime);
        $this->assertEquals('2021-12-13 00:00:00', $b[0]->toDateTimeString()); // monday
        $this->assertEquals('2021-12-19 23:59:59', $b[1]->toDateTimeString()); // sunday

        // '+1w', 'next week'
        $b = Dt::getBoundaries('+1w', true, $baseTime);
        $this->assertEquals('2022-01-03 00:00:00', $b[0]->toDateTimeString()); // monday
        $this->assertEquals('2022-01-09 23:59:59', $b[1]->toDateTimeString()); // sunday

        // 'month', 'this month', 'current month'
        $b = Dt::getBoundaries('month', true, $baseTime);
        $this->assertEquals('2021-12-01 00:00:00', $b[0]->toDateTimeString()); // start of month
        $this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString()); // end of month

        // '-1M', '-1mo', 'month ago'
        $b = Dt::getBoundaries('-1M', true, $baseTime);
        $this->assertEquals('2021-11-01 00:00:00', $b[0]->toDateTimeString()); // start of month
        $this->assertEquals('2021-11-30 23:59:59', $b[1]->toDateTimeString()); // end of month

        // '+1M', '+1mo', 'next month'
        $b = Dt::getBoundaries('+1M', true, $baseTime);
        $this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString()); // start of month
        $this->assertEquals('2022-01-31 23:59:59', $b[1]->toDateTimeString()); // end of month

        // 'y', 'year'
        $b = Dt::getBoundaries('year', true, $baseTime);
        $this->assertEquals('2021-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
        $this->assertEquals('2021-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year

        // '-1y', 'previous year', 'year ago'
        $b = Dt::getBoundaries('-1y', true, $baseTime);
        $this->assertEquals('2020-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
        $this->assertEquals('2020-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year

        // '+1y', 'next year'
        $b = Dt::getBoundaries('+1y', true, $baseTime);
        $this->assertEquals('2022-01-01 00:00:00', $b[0]->toDateTimeString()); // start of year
        $this->assertEquals('2022-12-31 23:59:59', $b[1]->toDateTimeString()); // end of year
    }

    public function testGetRelativeTime()
    {
        // test date
        $dt = new Dt('2021-12-31 14:30:00');

        $rdt = Dt::getRelativeTime('minute', 1, true, $dt);
        $this->assertEquals('2021-12-31 14:29:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('hour', 1, true, $dt);
        $this->assertEquals('2021-12-31 13:30:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('day', 1, true, $dt);
        $this->assertEquals('2021-12-30 14:30:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('week', 1, true, $dt);
        $this->assertEquals('2021-12-24 14:30:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('month', 1, true, $dt);
        $this->assertEquals('2021-12-01 14:30:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('year', 1, true, $dt);
        $this->assertEquals('2020-12-31 14:30:00', $rdt->toDateTimeString());

        // timezone
        $rdt = Dt::getRelativeTime('minute', 1, true, $dt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 15:29:00', $rdt->toDateTimeString());

        // more than one
        $rdt = Dt::getRelativeTime('hour', 2, true, $dt);
        $this->assertEquals('2021-12-31 12:30:00', $rdt->toDateTimeString());

        $rdt = Dt::getRelativeTime('hour', 2, true, $dt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 13:30:00', $rdt->toDateTimeString());
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

        // more than one
        $b = Dt::getRelativeTimeBoundaries('hour', 2, true, $dt);
        $this->assertEquals('2021-12-31 12:30:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 14:30:00', $b[1]->toDateTimeString());

        // timezone
        $b = Dt::getRelativeTimeBoundaries('hour', 1, true, $dt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 14:30:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 15:30:00', $b[1]->toDateTimeString());

        $b = Dt::getRelativeTimeBoundaries('hour', 2, true, $dt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 13:30:00', $b[0]->toDateTimeString());
        $this->assertEquals('2021-12-31 15:30:00', $b[1]->toDateTimeString());
    }

    public function testSecondsToWords()
    {
        $this->assertEquals('1s', Dt::secondsToWords(1));
        $this->assertEquals('59s', Dt::secondsToWords(59));
        $this->assertEquals('1m 0s', Dt::secondsToWords(60));
        $this->assertEquals('2m 5s', Dt::secondsToWords(125));
        $this->assertEquals('5m 30s', Dt::secondsToWords(330));
        $this->assertEquals('1h 1m 0s', Dt::secondsToWords(3660));
        $this->assertEquals('1h 0m 10s', Dt::secondsToWords(3610));
    }

    public function testMysqlToUnix()
    {
        $ts = Dt::mysqlToUnix('2021-12-31 14:30:00');
        $this->assertEquals($ts, 1640961000);
    }

    public function testIsDay()
    {
        // is day (05:00 - 21:00)
        $this->assertFalse(Dt::isDay('2017-05-01 04:59:59'));
        $this->assertTrue(Dt::isDay('2017-05-01 05:00:00'));
        $this->assertTrue(Dt::isDay('2017-05-01 20:59:59'));
        $this->assertFalse(Dt::isDay('2017-05-01 21:00:00'));
        $this->assertTrue(Dt::isDay('2017-05-01 6:30:00 AM', 'US/Central'));

        // object
        $dt = Dt::create('2017-05-01 05:00:00', 'Europe/Bratislava');
        $this->assertTrue(Dt::isDay($dt));

        // custom time-frame (02:00 - 03:00)
        $this->assertFalse(Dt::isDay('2017-05-01 01:59:59', 'Europe/Bratislava', 120, 180));
        $this->assertFalse(Dt::isDay('2017-05-01 01:59:59', 'Europe/Bratislava', '02:00', '03:00'));
        $this->assertTrue(Dt::isDay('2017-05-01 02:00:00', 'Europe/Bratislava', 120, 180));
        $this->assertTrue(Dt::isDay('2017-05-01 02:00:00', 'Europe/Bratislava', '02:00', '03:00'));
        $this->assertTrue(Dt::isDay('2017-05-01 02:59:59', 'Europe/Bratislava', 120, 180));
        $this->assertTrue(Dt::isDay('2017-05-01 02:59:59', 'Europe/Bratislava', '02:00', '03:00'));
        $this->assertFalse(Dt::isDay('2017-05-01 03:00:00', 'Europe/Bratislava', 120, 180));
        $this->assertFalse(Dt::isDay('2017-05-01 03:00:00', 'Europe/Bratislava', '02:00', '03:00'));
    }

    public function testIsNight()
    {
        // is night (21:00 - 05:00)
        $this->assertFalse(Dt::isNight('2017-05-01 20:59:59'));
        $this->assertTrue(Dt::isNight('2017-05-01 21:00:00'));
        $this->assertTrue(Dt::isNight('2017-05-01 04:59:59'));
        $this->assertFalse(Dt::isNight('2017-05-01 05:00:00'));
    }

    public function testListDays()
    {
        $d = Dt::listDays();
        $this->assertEquals(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], $d);

        $d = Dt::listDays('narrow', 'de');
        $this->assertEquals(['Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'], $d);

        $d = Dt::listDays('long', 'de');
        $this->assertEquals(['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'], $d);

        $d = Dt::listDays('long', 'sk');
        $this->assertEquals(['pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa'], $d);
    }

    public function testListMonths()
    {
        $m = Dt::listMonths();
        $this->assertEquals([
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ], $m);

        $m = Dt::listMonths('narrow', 'de');
        $this->assertEquals([
            1 => 'Jan', 2 => 'Feb', 3 => 'Mär', 4 => 'Apr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dez'
        ], $m);

        $m = Dt::listMonths('long', 'de');
        $this->assertEquals([
            1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April', 5 => 'Mai', 6 => 'Juni',
            7 => 'Juli', 8 => 'August', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember'
        ], $m);
    }

    public function testListYears()
    {
        $m = Dt::listYears();
        $r = range(date('Y') - 100, date('Y'));
        $r = array_combine(array_values($r), array_values($r));
        $this->assertEquals($r, $m);

        $m = Dt::listYears(2000, 2010);
        $r = range(2000, 2010);
        $r = array_combine(array_values($r), array_values($r));
        $this->assertEquals($r, $m);

        $m = Dt::listYears(1990);
        $r = range(1990, date('Y'));
        $r = array_combine(array_values($r), array_values($r));
        $this->assertEquals($r, $m);
    }
}

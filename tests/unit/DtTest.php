<?php

declare(strict_types=1);

namespace meriksk\DateTime\Tests\unit;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use meriksk\DateTime\Dt;
use meriksk\DateTime\Tests\BaseTestCase;

class DtTest extends BaseTestCase
{
    /**
     * @group create
     */
    public function testConstruct()
    {
        // default timezone
        $dt = new Dt();
        $this->assertIsObject($dt);
        $this->assertEquals(self::$tzLocalName, $dt->getTimezone()->getName());

        // custom timezone (string)
        $dt = new Dt('now', 'Europe/London');
        $this->assertIsObject($dt);
        $this->assertEquals('Europe/London', $dt->getTimezone()->getName());

        // custom timezone (object)
        $dt = new Dt('now', new DateTimeZone('Europe/London'));
        $this->assertIsObject($dt);
        $this->assertEquals('Europe/London', $dt->getTimezone()->getName());

        // define time
        $dt = new Dt('16:30');
        $this->assertEquals('16:30:00', $dt->format('H:i:s'));
        $this->assertEquals(self::$tzLocalName, $dt->getTimezone()->getName());

        $dt = new Dt('2024-09-06 12:20:55');
        $this->assertEquals('2024-09-06 12:20:55.000 ' . self::$tzLocalName, $dt->format('Y-m-d H:i:s.v e'));

        $dt = new Dt('2024-09-06 12:20:55', 'UTC');
        $this->assertEquals('2024-09-06 12:20:55.000 UTC', $dt->format('Y-m-d H:i:s.v e'));

        // timestamp
        $dt = new Dt(self::$TS_2021_12_31_143000);
        $this->assertEquals('2021-12-31 14:30:00.000 +00:00', $dt->format('Y-m-d H:i:s.v e'));
        $dt = new Dt(self::$TS_2021_12_31_143000, 'UTC');
        $this->assertEquals('2021-12-31 14:30:00.000 +00:00', $dt->format('Y-m-d H:i:s.v e'));
        $dt = Dt::createFromTimestamp(self::$TS_2021_12_31_143000, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31 15:30:00.000 Europe/Bratislava', $dt->format('Y-m-d H:i:s.v e'));

        // change the timezone
        // timestamp -> local
        $dt = new Dt(self::$TS_2021_12_31_143000); // timestamp is utc
        $this->assertEquals('14:30:00', $dt->format('H:i:s'));
        $dt->setTimezone('Europe/Bratislava');
        $this->assertEquals('15:30:00', $dt->format('H:i:s'));

        // local -> utc
        $dt = new Dt(self::$DATE_2021_12_31_143000, 'Europe/Bratislava');
        $this->assertEquals('14:30:00', $dt->format('H:i:s'));
        $dt->setTimezone('UTC');
        $this->assertEquals('13:30:00', $dt->format('H:i:s'));

        // utc -> local
        $dt = new Dt(self::$DATE_2021_12_31_143000, 'UTC');
        $this->assertEquals('14:30:00', $dt->format('H:i:s'));
        $dt->setTimezone('Europe/Bratislava');
        $this->assertEquals('15:30:00', $dt->format('H:i:s'));
    }

    /**
     * @group create
     */
    public function testCreate()
    {
        // no parameters (default timezone)
        $dt = Dt::create(2024, 9, 6, 10, 20, 55);
        $this->assertIsObject($dt);
        $this->assertEquals(self::$tzLocalName, $dt->getTimezone()->getName());
        $this->assertEquals('2024-09-06 10:20:55.000 ' . self::$tzLocalName, $dt->format('Y-m-d H:i:s.v e'));

        // UTC date
        $dt = Dt::create(2024, 9, 6, 10, 20, 55, 'UTC');
        $this->assertEquals('UTC', $dt->getTimezone()->getName());
        $this->assertEquals('2024-09-06 10:20:55.000 UTC', $dt->format('Y-m-d H:i:s.v e'));

        // UTC date - different timezone
        $dt = Dt::create(2024, 9, 6, 10, 20, 55, 'Europe/Bratislava');
        $this->assertEquals('2024-09-06 10:20:55.000 Europe/Bratislava', $dt->format('Y-m-d H:i:s.v e'));
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
        // default timezone (local)
        $ts = Dt::createTimestamp('31.12.2025 08:30:00');
        $this->assertIsInt($ts);
        $this->assertEquals(1767166200, $ts);

        // different timezone
        $ts = Dt::createTimestamp('31.12.2025 08:30:00', 'UTC');
        $this->assertIsInt($ts);
        $this->assertEquals(1767169800, $ts);

        // offset
        $ts = Dt::createTimestamp('2025-12-31T08:30:00+01:00');
        $this->assertIsInt($ts);
        $this->assertEquals(1767166200, $ts);

        // offset
        $ts = Dt::createTimestamp('2025-12-31T08:30:00+04:00');
        $this->assertIsInt($ts);
        $this->assertEquals(1767155400, $ts);
    }

    public function testGetFormat()
    {
        // default locale (en)
        $f = Dt::getFormat('date_time');
        $this->assertEquals('n/j/Y g:i:s A', $f);

        // specific locale
        $f = Dt::getFormat('date_time', null, 'sk');
        $this->assertEquals('j.n.Y H:i:s', $f);

        // specific locale
        $f = Dt::getFormat('date_time', null, 'en');
        $this->assertEquals('n/j/Y g:i:s A', $f);
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

        // custom format (PHP/MOMENT: m/d/Y H:i:s)
        $f = Dt::convertFormat('date_time', Dt::TARGET_MOMENT, 'sk');
        $this->assertEquals('D.M.YYYY HH:mm:ss', $f);
    }

    public function testF()
    {
        // timezone (system)
        $dtUtc = self::$DT_2021_12_31_143000_UTC;
        $tsUtc = $dtUtc->getTimestamp();

        // timezone (Europe/Bratislava)
        $dtLocal = self::$DT_2021_12_31_143000_EU_BRATISLAVA;
        $tsLocal = $dtLocal->getTimestamp();

        // timestamp

        $d = Dt::f('c', $tsUtc);
        $this->assertSame('2021-12-31T14:30:00+00:00', $d);

        // timestamp, different timezone
        $d = Dt::f('c', $tsLocal);
        $this->assertSame('2021-12-31T13:30:00+00:00', $d);

        // timestamp, different timezone
        $d = Dt::f('c', $tsUtc, 'Europe/Bratislava');
        $this->assertSame('2021-12-31T15:30:00+01:00', $d);

        // timestamp, different timezone
        $d = Dt::f('c', $tsLocal, 'Europe/Bratislava');
        $this->assertSame('2021-12-31T14:30:00+01:00', $d);

        // object

        // DateTime object, no timezone conversion
        $d = Dt::f('c', $dtUtc);
        $this->assertSame('2021-12-31T14:30:00+00:00', $d);

        // DateTime object, no timezone conversion
        $d = Dt::f('c', $dtLocal);
        $this->assertSame('2021-12-31T14:30:00+01:00', $d);

        // DateTime object, timezone conversion UTC -> Europe
        $d = Dt::f('c', $dtUtc, 'Europe/Bratislava');
        $this->assertSame('2021-12-31T15:30:00+01:00', $d);

        // use DateTime object, Europe/Bratislava -> Europe/Bratislava
        $d = Dt::f('c', $dtLocal, 'Europe/Bratislava');
        $this->assertSame('2021-12-31T14:30:00+01:00', $d);

        // use DateTime object, Europe/Bratislava -> UTC
        $d = Dt::f('c', $dtLocal, 'UTC');
        $this->assertSame('2021-12-31T13:30:00+00:00', $d);

        // specific locale

        $d = Dt::f('date_time', $tsUtc, null, 'en');
        $this->assertEquals('12/31/2021 2:30:00 PM', $d);
        $d = Dt::f('date_time', $tsUtc, null, 'sk');
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
        $this->assertEquals('pátek, pát, pro, prosinec, 31.12.2021 15:30', $d);

        // specific locale - translations - winter time
        $d = Dt::f('l, D, M, F, j.n.Y H:i', $dtUtc, 'Europe/Bratislava', 'cs_CZ');
        $this->assertEquals('pátek, pát, pro, prosinec, 31.12.2021 15:30', $d);
    }

    public function testP()
    {
        $localTzName = self::$tzLocalName;

        // default timezone

        // custom format
        $dt = Dt::p('12_24_2017 14:30:00', 'n_j_Y H:i:s');
        $this->assertIsObject($dt);
        $this->assertSame('2017-12-24 14:30:00 ' . $localTzName, $dt->format('Y-m-d H:i:s e'));

        // named format, no locale
        $dt = Dt::p('12/24/2017', 'date')->setTime(14, 30, 0);
        $this->assertSame('2017-12-24 14:30:00 ' . $localTzName, $dt->format('Y-m-d H:i:s e'));

        // named format, no tz, sk locale
        $dt = Dt::p('24.12.2017', 'date', null, 'sk')->setTime(14, 30, 30);
        $this->assertSame('2017-12-24 14:30:30 ' . $localTzName, $dt->format('Y-m-d H:i:s e'));

        // UTC timezone
        $dt = Dt::p('12_24_2017 14:30:00', 'n_j_Y H:i:s', 'UTC');
        $this->assertIsObject($dt);
        $this->assertSame('2017-12-24T14:30:00+00:00', $dt->format('c'));

        // named format, tz, en
        $dt = Dt::p('12/24/2017 4:30:15 PM', 'date_time', 'America/Chicago', 'en');
        $this->assertSame('2017-12-24T16:30:15-06:00', $dt->format('c'));

        // named format, tz, sk
        $dt = Dt::p('24.12.2017 16:30:15', 'date_time', 'America/Chicago', 'sk');
        $this->assertSame('2017-12-24T16:30:15-06:00', $dt->format('c'));

        // invalid format
        $dt = Dt::p('12/24/2017', 'date', null, 'sk');
        $this->assertFalse($dt);
    }

    public function testResolveTime()
    {

        $now = new Dt('now');
        $yesterday = (new Dt('now'))->modify('-1 day');
        $localTzName = self::$tzLocalName;
        $monthNumDays = date('t');
        $mondayDay = date('d', strtotime('monday this week'));
        $sundayDay = date('d', strtotime('sunday this week'));

        // empty datetime
        $dt0 = Dt::resolveTime('');
        $this->assertEquals($now->format('c'), $dt0->format('c'));

        // timestamp
        $dt0 = Dt::resolveTime(self::$TS_2021_12_31_143000);
        $this->assertEquals('2021-12-31T14:30:00+00:00', $dt0->format('c'));

        // miliseconds
        $dt0 = Dt::resolveTime((self::$TS_2021_12_31_143000 * 1000) + 999);
        $this->assertEquals('2021-12-31T14:30:00.999+00:00', $dt0->format('Y-m-d\TH:i:s.vP'));

        // now
        $dt0 = Dt::resolveTime('now');
        $this->assertIsObject($dt0);
        $this->assertEquals($now->format('c'), $dt0->format('c'));

        // timezone
        $dt0 = Dt::resolveTime('now/d', false, self::$DT_2021_12_31_143000_EU_BRATISLAVA, 'America/New_York');
        $this->assertSame('2021-12-31 00:00:00 America/New_York', $dt0->format('Y-m-d H:i:s e'));

        $dt0 = Dt::resolveTime('now/M', false, self::$DT_2021_12_31_143000_EU_BRATISLAVA);
        $this->assertSame('2021-12-01 00:00:00 ' . $localTzName, $dt0->format('Y-m-d H:i:s e'));

        // current time
        $dt0 = Dt::resolveTime('now/m');
        $this->assertEquals($now->format('Y-m-d H:i:00.000000'), $dt0->format('Y-m-d H:i:s.u'));
        // current time - end
        $dt0 = Dt::resolveTime('now/m', true);
        $this->assertEquals($now->format('Y-m-d H:i:59.999999'), $dt0->format('Y-m-d H:i:s.u'));
        // current time - end -  exclusive
        $dt0 = Dt::resolveTime('now/m', true, exclusive: true);
        $this->assertEquals((clone $now)->modify('+1 MINUTE')->format('Y-m-d H:i:00.000000'), $dt0->format('Y-m-d H:i:s.u'));

        // custom time
        $dtTest = new DateTime('2026-03-18T14:31:31+01:00');

        // start of the minute
        $dt0 = Dt::resolveTime('now/m', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 14:31:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of the minute
        $dt0 = Dt::resolveTime('now/m', true, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 14:31:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of the minute - exclusive
        $dt0 = Dt::resolveTime('now/m', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-03-18 14:32:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // start of hour
        $dt0 = Dt::resolveTime('now/h', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 14:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of hour
        $dt0 = Dt::resolveTime('now/h', true, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 14:59:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of hour - exclusive
        $dt0 = Dt::resolveTime('now/h', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-03-18 15:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // start of day
        $dt0 = Dt::resolveTime('now/d', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of day
        $dt0 = Dt::resolveTime('now/d', true, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-18 23:59:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of day -  exclusive
        $dt0 = Dt::resolveTime('now/d', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-03-19 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // start of week
        $dt0 = Dt::resolveTime('now/w', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-16 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of week
        $dt0 = Dt::resolveTime('now/w', true, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-22 23:59:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of week exclusive
        $dt0 = Dt::resolveTime('now/w', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-03-23 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // start of month
        $dt0 = Dt::resolveTime('now/M', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-01 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of month
        $dt0 = Dt::resolveTime('now/M', true, $dtTest, exclusive: false);
        $this->assertEquals('2026-03-31 23:59:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of month - exclusive
        $dt0 = Dt::resolveTime('now/M', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-04-01 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // start of year
        $dt0 = Dt::resolveTime('now/Y', false, $dtTest, exclusive: false);
        $this->assertEquals('2026-01-01 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // end of year
        $dt0 = Dt::resolveTime('now/Y', true, $dtTest, exclusive: false);
        $this->assertEquals($now->format('2026-12-31 23:59:59.999999'), $dt0->format('Y-m-d H:i:s.u'));

        //  end of year - exclusive
        $dt0 = Dt::resolveTime('now/Y', true, $dtTest, exclusive: true);
        $this->assertEquals('2027-01-01 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        // relative time

        // yesterday
        $dt0 = Dt::resolveTime('-1d');
        $this->assertEquals($yesterday->format('Y-m-d H:i:s'), $dt0->format('Y-m-d H:i:s'));

        // yesterday - start of the day
        $dt0 = Dt::resolveTime('-1d/d');
        $this->assertEquals($yesterday->format('Y-m-d 00:00:00.000000'), $dt0->format('Y-m-d H:i:s.u'));

        // yesterday - end of the day
        $dt0 = Dt::resolveTime('-1d/d', true);
        $this->assertEquals($yesterday->format('Y-m-d 23:59:59.999999'), $dt0->format('Y-m-d H:i:s.u'));

        // relative time + base time

        $dt0 = Dt::resolveTime('-1d', false, $dtTest);
        $this->assertEquals('2026-03-17 14:31:31.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        $dt0 = Dt::resolveTime('-1d/d', false, $dtTest);
        $this->assertEquals('2026-03-17 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        $dt0 = Dt::resolveTime('-1d/d', true, $dtTest);
        $this->assertEquals('2026-03-17 23:59:59.999999+01:00', $dt0->format('Y-m-d H:i:s.uP'));

        $dt0 = Dt::resolveTime('-1d/d', true, $dtTest, exclusive: true);
        $this->assertEquals('2026-03-18 00:00:00.000000+01:00', $dt0->format('Y-m-d H:i:s.uP'));
    }

    public function testGetTime()
    {
        // test date
        $baseDt = self::$DT_2021_12_31_143000_UTC;

        $dt = Dt::getTime('day', true, $baseDt);
        $this->assertEquals('2021-12-31T00:00:00+00:00', $dt->format('c'));

        $dt = Dt::getTime('d', true, $baseDt);
        $this->assertEquals('2021-12-31T00:00:00+00:00', $dt->format('c'));

        $dt = Dt::getTime('-1d', true, $baseDt);
        $this->assertEquals('2021-12-30T00:00:00+00:00', $dt->format('c'));

        $dt = Dt::getTime('+1d', true, $baseDt);
        $this->assertEquals('2022-01-01T00:00:00+00:00', $dt->format('c'));

        $dt = Dt::getTime('+1w', true, $baseDt);
        $this->assertEquals('2022-01-03T00:00:00+00:00', $dt->format('c'));

        $dt = Dt::getTime('year', true, $baseDt);
        $this->assertEquals('2021-01-01T00:00:00+00:00', $dt->format('c'));

        // timezone
        $rt = Dt::getTime('hour', true, $baseDt, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31T15:00:00+01:00', $rt->format('c'));
    }

    public function testGetBoundaries()
    {
        $now = new Dt('now');
        $nowUtc = new Dt('now', 'UTC');

        // now - local tz
        $b = Dt::getBoundaries('day', true);
        $this->assertEquals($now->format('Y-m-d') . 'T00:00:00+01:00', $b[0]->format('c'));
        $this->assertEquals($now->format('Y-m-d') . 'T23:59:59+01:00', $b[1]->format('c'));

        // now - local tz (string)
        $b = Dt::getBoundaries('hour', true, timezone: self::$tzLocal);
        $this->assertEquals($now->format('Y-m-d\TH') . ':00:00+01:00', $b[0]->format('c'));
        $this->assertEquals($now->format('Y-m-d\TH') . ':59:59+01:00', $b[1]->format('c'));

        // now - utc
        $b = Dt::getBoundaries('hour', true, timezone: self::$tzUtc);
        $this->assertEquals($nowUtc->format('Y-m-d\TH') . ':00:00+00:00', $b[0]->format('c'));
        $this->assertEquals($nowUtc->format('Y-m-d\TH') . ':59:59+00:00', $b[1]->format('c'));

        // basetime

        // object - utc
        $b = Dt::getBoundaries('hour', true, self::$DT_2021_12_31_143000_UTC);
        $this->assertEquals('2021-12-31T14:00:00+00:00', $b[0]->format('c'));
        $this->assertEquals('2021-12-31T14:59:59+00:00', $b[1]->format('c'));

        // object - local tz
        $b = Dt::getBoundaries('hour', true, self::$DT_2021_12_31_143000_EU_BRATISLAVA);
        $this->assertEquals('2021-12-31T14:00:00+01:00', $b[0]->format('c'));
        $this->assertEquals('2021-12-31T14:59:59+01:00', $b[1]->format('c'));

        // object - utc
        $b = Dt::getBoundaries('hour', true, self::$DT_2021_12_31_143000_UTC);
        $this->assertEquals('2021-12-31T14:00:00+00:00', $b[0]->format('c'));
        $this->assertEquals('2021-12-31T14:59:59+00:00', $b[1]->format('c'));

        // object - different tz - utc -> ba
        $b = Dt::getBoundaries('hour', true, self::$DT_2021_12_31_143000_UTC, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31T15:00:00+01:00', $b[0]->format('c'));
        $this->assertEquals('2021-12-31T15:59:59+01:00', $b[1]->format('c'));

        // different units

        $baseTime = new Dt('2021-12-31T14:30:30.123456+01:00');

        // 's', 'second'
        $b = Dt::getBoundaries('second', true, $baseTime);
        $this->assertEquals('2021-12-31T14:30:30.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T14:30:30.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // 'm', 'minute'
        $b = Dt::getBoundaries('minute', true, $baseTime);
        $this->assertEquals('2021-12-31T14:30:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T14:30:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // 'h', 'hour'
        $b = Dt::getBoundaries('hour', true, $baseTime);
        $this->assertEquals('2021-12-31T14:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T14:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // 'd', 'day'
        $b = Dt::getBoundaries('day', true, $baseTime);
        $this->assertEquals('2021-12-31T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // 'w', 'week'
        $b = Dt::getBoundaries('week', true, $baseTime);
        $this->assertEquals('2021-12-27T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2022-01-02T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // 'M', 'month'
        $b = Dt::getBoundaries('month', true, $baseTime);
        $this->assertEquals('2021-12-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // modify time

        // '-2m'
        $b = Dt::getBoundaries('-2m', true, $baseTime);
        $this->assertEquals('2021-12-31T14:28:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T14:28:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-2m' - exclusive
        $b = Dt::getBoundaries('-2m', true, $baseTime, exclusive: true);
        $this->assertEquals('2021-12-31T14:28:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T14:29:00.000000+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-5h'
        $b = Dt::getBoundaries('-5h', true, $baseTime);
        $this->assertEquals('2021-12-31T09:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T09:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-5h' - exclusive
        $b = Dt::getBoundaries('-5h', true, $baseTime, exclusive: true);
        $this->assertEquals('2021-12-31T09:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T10:00:00.000000+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+5h'
        $b = Dt::getBoundaries('+5h', true, $baseTime);
        $this->assertEquals('2021-12-31T19:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T19:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+5h' - exclusive
        $b = Dt::getBoundaries('+5h', true, $baseTime, exclusive: true);
        $this->assertEquals('2021-12-31T19:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-31T20:00:00.000000+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-1d', 'yesterday'
        $b = Dt::getBoundaries('-1d', true, $baseTime);
        $this->assertEquals('2021-12-30T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-30T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+1d', 'tomorrow'
        $b = Dt::getBoundaries('+1d', true, $baseTime);
        $this->assertEquals('2022-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2022-01-01T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+1d', 'tomorrow' - exclusive
        $b = Dt::getBoundaries('+1d', true, $baseTime, exclusive: true);
        $this->assertEquals('2022-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2022-01-02T00:00:00.000000+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-1w', 'previous week', 'week ago'
        $b = Dt::getBoundaries('-1w', true, $baseTime);
        $this->assertEquals('2021-12-20T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-12-26T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-1M', '-1mo', 'month ago'
        $b = Dt::getBoundaries('-1M', true, $baseTime);
        $this->assertEquals('2021-11-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2021-11-30T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+1M', '+1mo', 'next month'
        $b = Dt::getBoundaries('+1M', true, $baseTime);
        $this->assertEquals('2022-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2022-01-31T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '-1y', 'previous year', 'year ago'
        $b = Dt::getBoundaries('-1y', true, $baseTime);
        $this->assertEquals('2020-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2020-12-31T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+1y', 'next year'
        $b = Dt::getBoundaries('+1y', true, $baseTime);
        $this->assertEquals('2022-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2022-12-31T23:59:59.999999+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));

        // '+1y', 'next year' - exclusive
        $b = Dt::getBoundaries('+1y', true, $baseTime, exclusive: true);
        $this->assertEquals('2022-01-01T00:00:00.000000+01:00', $b[0]->format('Y-m-d\TH:i:s.uP'));
        $this->assertEquals('2023-01-01T00:00:00.000000+01:00', $b[1]->format('Y-m-d\TH:i:s.uP'));
    }

    public function testGetRelativeTime()
    {
        $dtUtc = self::$DT_2021_12_31_143000_UTC;
        //$dtLocal = self::$DT_2021_12_31_143000_EU_BRATISLAVA;

        // fraction of seconds

        $baseTime = new Dt('2021-12-31T14:15:30.112233+01:00');

        $dt = Dt::getRelativeTime('second', 1, true, $baseTime);
        $this->assertEquals('2021-12-31T14:15:29.112233+01:00', $dt->format('Y-m-d\TH:i:s.uP'));

        $dt = Dt::getRelativeTime('minute', 1, true, $baseTime);
        $this->assertEquals('2021-12-31T14:14:30.112233+01:00', $dt->format('Y-m-d\TH:i:s.uP'));

        // UTC

        $dt = Dt::getRelativeTime('hour', 1, true, $dtUtc);
        $this->assertEquals('2021-12-31T13:30:00+00:00', $dt->format('c'));

        $dt = Dt::getRelativeTime('day', 1, true, $dtUtc);
        $this->assertEquals('2021-12-30T14:30:00+00:00', $dt->format('c'));

        $dt = Dt::getRelativeTime('week', 1, true, $dtUtc);
        $this->assertEquals('2021-12-24T14:30:00+00:00', $dt->format('c'));

        $dt = Dt::getRelativeTime('month', 1, true, $dtUtc);
        $this->assertEquals('2021-11-30T14:30:00+00:00', $dt->format('c'));

        $dt = Dt::getRelativeTime('year', 1, true, $dtUtc);
        $this->assertEquals('2020-12-31T14:30:00+00:00', $dt->format('c'));

        // timezone
        $dt = Dt::getRelativeTime('minute', 1, true, $dtUtc, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31T15:29:00+01:00', $dt->format('c'));

        // more than one
        $dt = Dt::getRelativeTime('hour', 2, true, $dtUtc);
        $this->assertEquals('2021-12-31T12:30:00+00:00', $dt->format('c'));

        $dt = Dt::getRelativeTime('hour', 2, true, $dtUtc, 'Europe/Bratislava');
        $this->assertEquals('2021-12-31T13:30:00+01:00', $dt->format('c'));
    }

    /**
     * ?????????????????????????????????????
     *
     * @return void
     */
    public function testGetRelativeTimeBoundaries()
    {
        // test date
        $dtNow = new Dt('now', 'UTC');
        // custom time
        $dtTest = new DateTime('2026-03-18T14:31:31+00:00');

        $b = Dt::getRelativeTimeBoundaries('minute', 1, true, 'UTC');
        $this->assertEquals((clone $dtNow)->modify('-1 MINUTE')->format('Y-m-d\TH:i:sP'), $b[0]->format('Y-m-d\TH:i:sP'));
        $this->assertEquals((clone $dtNow)->format('Y-m-d\TH:i:sP'), $b[1]->format('Y-m-d\TH:i:sP'));

        $b = Dt::getRelativeTimeBoundaries('minute', 1, true, $dtTest);
        $this->assertEquals('2026-03-18T14:30:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('hour', 1, true, $dtTest);
        $this->assertEquals('2026-03-18T13:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('day', 1, true, $dtTest);
        $this->assertEquals('2026-03-17T14:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('week', 1, true, $dtTest);
        $this->assertEquals('2026-03-11T14:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('month', 1, true, $dtTest);
        $this->assertEquals('2026-02-18T14:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('year', 1, true, $dtTest);
        $this->assertEquals('2025-03-18T14:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        // more than one
        $b = Dt::getRelativeTimeBoundaries('hour', 2, true, $dtTest);
        $this->assertEquals('2026-03-18T12:31:31+00:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T14:31:31+00:00', $b[1]->format('c'));

        // timezone
        $b = Dt::getRelativeTimeBoundaries('hour', 1, true, $dtTest, 'Europe/Bratislava');
        $this->assertEquals('2026-03-18T14:31:31+01:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T15:31:31+01:00', $b[1]->format('c'));

        $b = Dt::getRelativeTimeBoundaries('hour', 2, true, $dtTest, 'Europe/Bratislava');
        $this->assertEquals('2026-03-18T13:31:31+01:00', $b[0]->format('c'));
        $this->assertEquals('2026-03-18T15:31:31+01:00', $b[1]->format('c'));
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
        $ts = Dt::mysqlToUnix('2021-12-31 14:30:00', true);
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

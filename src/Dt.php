<?php
namespace meriksk\DateTime;

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
	const TARGET_ICU = 'icu';			// http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
	const TARGET_CARBON = 'carbon';		// https://carbon.nesbot.com/docs/#api-localization
	const TARGET_YII2 = 'yii2';			// http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
	const TARGET_MOMENT = 'moment';		// http://momentjs.com/docs/#/displaying/


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
	 * Date formats [ PHP => format ]
	 * @var array
	 */
	private static $conversionTable = [
		self::TARGET_ICU => [
			// day
			'j'			=> 'd',		// 1 to 31
			'd'			=> 'dd',	// 01 to 31
			'D'			=> 'eee',	// Mon through Sun
			'l'			=> 'EEEE',	// Sunday through Saturday
			'N'			=> 'cc',	// 1 (for Monday) through 7 (for Sunday)
			'w'			=> 'cc',	// 0 (for Sunday) through 6 (for Saturday)
			'z'			=> 'D',		// 0 through 365
			// week
			'W'			=> 'ww',	// Examples: 42 (the 42nd week in the year)
			// month
			'F'			=> 'MMMM',	// January through December
			'm'			=> 'MM',	// 01 through 12
			'M'			=> 'MMM',	// Jan through Dec
			'n'			=> 'M',		// 1 through 12
			't'			=> '',		// 28 through 31
			// year
			't'			=> 'Y',		// Examples: 1999 or 2003 (ISO-8601 week-numbering year)
			'Y'			=> 'yyyy',	// Examples: 1999 or 2003
			'y'			=> 'yy',	// Examples: 99 or 03
			// time
			'a'			=> 'a',		// am or pm
			'A'			=> 'a',		// AM or PM
			'B'			=> 'SSS',	// 000 through 999 (fractional second)
			'g'			=> 'h',		// 1 through 12
			'G'			=> 'k',		// 0 through 23
			'h'			=> 'hh',	// 01 through 12
			'H'			=> 'HH',	// 00 through 23
			'i'			=> 'mm',	// 00 to 59
			's'			=> 'ss',	// 00 to 59
			// timezone
			'e'			=> 'VV',	// Examples: UTC, GMT, Atlantic/Azores
			'O'			=> 'ZZZ',	// Example: +0200 (Difference to Greenwich time (GMT) in hours)
			'P'			=> 'xxx',	// Example: +02:00
			'T'			=> 'zz',	// Examples: EST, MDT (Timezone abbreviation)
		],
		self::TARGET_CARBON => [
			// day
			'j'			=> 'D',		// 1 to 31
			'd'			=> 'DD',	// 01 to 31
			'D'			=> 'ddd',	// Mon through Sun
			'l'			=> 'dddd',	// Sunday through Saturday
			'N'			=> 'E',		// 1 (for Monday) through 7 (for Sunday)
			'w'			=> 'e',		// 0 (for Sunday) through 6 (for Saturday)
			'z'			=> '',		// 0 through 365
			// week
			'W'			=> 'w',		// Examples: 42 (the 42nd week in the year)
			// month
			'F'			=> 'MMMM',	// January through December
			'm'			=> 'MM',	// 01 through 12
			'M'			=> 'MMM',	// Jan through Dec
			'n'			=> 'M',		// 1 through 12
			't'			=> '',		// 28 through 31
			// year
			't'			=> 'Y',		// Examples: 1999 or 2003 (ISO-8601 week-numbering year)
			'Y'			=> 'YYYY',	// Examples: 1999 or 2003
			'y'			=> 'YY',	// Examples: 99 or 03
			// time
			'a'			=> 'a',		// am or pm
			'A'			=> 'A',		// AM or PM
			'B'			=> 'SSS',	// 000 through 999 (fractional second)
			'g'			=> 'h',		// 1 through 12
			'G'			=> 'H',		// 0 through 23
			'h'			=> 'hh',	// 01 through 12
			'H'			=> 'HH',	// 00 through 23
			'i'			=> 'mm',	// 00 to 59
			's'			=> 'ss',	// 00 to 59
			// timezone
			'e'			=> 'zz',	// Examples: UTC, GMT, Atlantic/Azores
			'O'			=> 'ZZ',	// Example: +0200 (Difference to Greenwich time (GMT) in hours)
			'P'			=> 'Z',		// Example: +02:00
			'T'			=> 'z',		// Examples: EST, MDT (Timezone abbreviation)
		],
		self::TARGET_CLIB => [
			// day
			'j'			=> '%e',	// 1 to 31
			'd'			=> '%d',	// 01 to 31
			'D'			=> '%a',	// Mon through Sun
			'l'			=> '%A',	// Sunday through Saturday
			'N'			=> '%u',	// 1 (for Monday) through 7 (for Sunday)
			'w'			=> '%w',	// 0 (for Sunday) through 6 (for Saturday)
			'z'			=> '',		// 0 through 365
			// week
			'W'			=> '%W',	// Examples: 42 (the 42nd week in the year)
			// month
			'F'			=> '%B',	// January through December
			'm'			=> '%m',	// 01 through 12
			'M'			=> '%b',	// Jan through Dec
			'n'			=> '%m',	// 1 through 12
			't'			=> '',		// 28 through 31
			// year
			't'			=> '%Y',	// Examples: 1999 or 2003 (ISO-8601 week-numbering year)
			'Y'			=> '%Y',	// Examples: 1999 or 2003
			'y'			=> '%y',	// Examples: 99 or 03
			// time
			'a'			=> '%P',	// am or pm
			'A'			=> '%p',	// AM or PM
			'B'			=> '',		// 000 through 999 (fractional second)
			'g'			=> '%I',	// 1 through 12 - "%l" does not work :(
			'G'			=> '%k',	// 0 through 23
			'h'			=> '%I',	// 01 through 12
			'H'			=> '%H',	// 00 through 23
			'i'			=> '%M',	// 00 to 59
			's'			=> '%S',	// 00 to 59
			// timezone
			'e'			=> '%Z',	// Examples: UTC, GMT, Atlantic/Azores
			'O'			=> '%z',	// Example: +0200 (Difference to Greenwich time (GMT) in hours)
			'P'			=> '%z',	// Example: +02:00
			'T'			=> '%Z',	// Examples: EST, MDT (Timezone abbreviation)
		],
		self::TARGET_MOMENT => [
			'j'			=> 'D',		// 1 to 31
			'd'			=> 'DD',	// 01 to 31
			'D'			=> 'ddd',	// Mon through Sun
			'l'			=> 'dddd',	// Sunday through Saturday
			'N'			=> 'E',		// 1 (for Monday) through 7 (for Sunday)
			'w'			=> 'd',		// 0 (for Sunday) through 6 (for Saturday)
			'z'			=> 'DDD',	// 0 through 365
			// week
			'W'			=> 'w',		// Examples: 42 (the 42nd week in the year)
			// month
			'F'			=> 'MMMM',	// January through December
			'm'			=> 'MM',	// 01 through 12
			'M'			=> 'MMM',	// Jan through Dec
			'n'			=> 'M',		// 1 through 12
			't'			=> '',		// 28 through 31
			// year
			't'			=> 'Y',		// Examples: 1999 or 2003 (ISO-8601 week-numbering year)
			'Y'			=> 'YYYY',	// Examples: 1999 or 2003
			'y'			=> 'YY',		// Examples: 99 or 03
			// time
			'a'			=> 'a',		// am or pm
			'A'			=> 'A',		// AM or PM
			'B'			=> 'SSS',	// 000 through 999 (fractional second)
			'g'			=> 'h',		// 1 through 12
			'G'			=> 'H',		// 0 through 23
			'h'			=> 'hh',	// 01 through 12
			'H'			=> 'HH',	// 00 through 23
			'i'			=> 'mm',	// 00 to 59
			's'			=> 'ss',	// 00 to 59
			// timezone
			'e'			=> 'zz',	// Examples: UTC, GMT, Atlantic/Azores
			'O'			=> 'ZZ',	// Example: +0200 (Difference to Greenwich time (GMT) in hours)
			'P'			=> 'Z',		// Example: +02:00
			'T'			=> 'zz',	// Examples: EST, MDT (Timezone abbreviation)
		],
		self::TARGET_YII2 => [],
	];

	/**
	 * Get locale format aliases
	 * @param string $locale
	 * @param string $alias
	 * @return array
	 */
	public static function getFormatByAlias($alias, $locale = 'en_US')
	{
		$def = [];

		// find aliases
		switch (true) {
			case in_array($locale, ['en', 'us']):
			default:
				$def = [
					'date'					=> 'n/j/Y',
					'date_short'			=> 'n/j/Y',
					'date_medium'			=> 'M j, Y',
					'date_long'				=> 'F j, Y',
					'date_full'				=> 'F j, Y',
					'date_time'				=> 'n/j/Y g:i:s A',
					'date_time_short'		=> 'n/j/Y g:i A',
					'date_time_short_tz'    => 'n/j/Y g:i A T',
					'date_time_medium'		=> 'M j, Y g:i A',
					'date_time_long'		=> 'F j, Y g:i:s A',
					'date_time_full'		=> 'F j, Y g:i:s.u A T',
					'date_time_tz'			=> 'n/j/Y g:i:s A T',
					'date_time_ms'			=> 'n/j/Y g:i:s.u A',
					'date_human'			=> 'D, M j',
					'time'					=> 'g:i:s A',
					'time_short'			=> 'g:i A',
					'time_short_tz'			=> 'g:i A T',
					'time_medium'			=> 'g:i:s A',
					'time_long'				=> 'g:i:s A',
					'time_full'				=> 'g:i:s A T',
					'time_ms'				=> 'g:i.u A',
					'xxx'					=> 'g:i.u A',
				];
				break;

			case in_array($locale, ['sk', 'cs', 'da', 'de', 'es', 'fr', 'hu']):
				$def = [
					'date'					=> 'j.n.Y',
					'date_short'			=> 'j.n.Y',
					'date_medium'			=> 'j M Y',
					'date_long'				=> 'j F Y',
					'date_full'				=> 'j F Y e',
					'date_time'				=> 'j.n.Y H:i:s',
					'date_time_short'		=> 'j.n.Y H:i',
					'date_time_short_tz'	=> 'j.n.Y H:i T',
					'date_time_medium'		=> 'j. M Y H:i',
					'date_time_long'		=> 'j. F Y H:i:s',
					'date_time_full'		=> 'j. F Y H:i:s T',
					'date_time_tz'			=> 'j.n.Y H:i:s T',
					'date_time_ms'			=> 'j.n.Y H:i:s.u',
					'date_human'			=> 'j.n.Y',
					'time'					=> 'H:i:s',
					'time_short'			=> 'H:i',
					'time_short_tz'			=> 'H:i T',
					'time_medium'			=> 'H:i:s',
					'time_long'				=> 'H:i:s',
					'time_full'				=> 'H:i:s T',
					'time_ms'				=> 'H:i:s.u',
				];
				break;
		}//switch

		return $def[$alias] ?? $alias;
	}

	/**
	 * Is OS windows?
	 * @return bool
	 */
	private static function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3))==='WIN';
	}

	/**
	 * Format date-time
	 * @param string $format
	 * @param int|string|DateTime $timestamp
	 * @param string $timezone
	 * @param string $locale
	 * @return bool|string
	 */
	public static function f($format, $timestamp = null, $timezone = null, $locale = null)
	{
		$tz = $timezone ? $timezone : self::$timezone;
		
		if (is_numeric($timestamp)) {
			$dt = static::createFromTimestamp($timestamp, $tz);
		} elseif (is_object($timestamp) && $timestamp instanceof Dt) {
			$dt = clone $timestamp;
			if ($tz) { $dt->setTimezone($tz); }
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
		} catch (\Carbon\Exceptions\InvalidFormatException $e) {}

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
		if ($targetFormat===self::TARGET_CLIB && self::isWindows()) {
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
	public static function convertFormat($format, $targetFormat, $locale = null)
	{
		if (empty($format)) {
			return null;
		}

		// YII2 === ICU
		if ($targetFormat===self::TARGET_YII2) {
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
		if ($targetFormat===self::TARGET_CLIB) {
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
		} elseif (is_object ($timezone) && $timezone instanceof DateTimeZone) {
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
			return $returnObject===true ? $b[0] : $b[0]->getTimestamp();
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
			if ($tz) { $dt->setTimezone($tz); }
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
		return $returnObject===true ? $dt : $dt->getTimestamp();
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
	 * '-1d', '-1 day', 'day ago', 'day_ago', 'yesterday', 'previous day', 'previous_day'<br>
	 * '+1d', '+1 day', 'tomorrow', 'next day', 'next_day'<br>
	 * 'w', 'week', 'this week', 'this_week', 'current week', 'current_week'<br>
	 * '-1w', '-1 week', 'last week', 'last_week', 'previous week', 'previous_week', 'week ago', 'week_ago'<br>
	 * '+1w', '+1 week', 'next week', 'next_week'<br>
	 * 'mo', 'month', 'this month', 'this_month', 'current month', 'current_month'<br>
	 * '-1mo', '-1 month', 'last month', 'last_month', 'previous month', 'previous_month', 'month ago', 'month_ago'<br>
	 * '+1mo', '+1 month', 'next month', 'next_month'<br>
	 * 'y', 'year', 'this year', 'this_year', 'current year', 'current_year'<br>
	 * '-1y', '-1 year', 'last year', 'last_year', 'previous year', 'previous_year', 'year ago', 'year_ago'<br>
	 * '+1y', '+1 year', 'next year', 'next_year'<br>
	 * 
	 * @param bool $returnObject
	 * @param int|\DateTime $baseTime
	 * @param string $timezone
	 * @return bool|array
	 * @throws \Exception
	 * @link https://gist.github.com/sepehr/6351425
	 */
	public static function getBoundaries($period, $returnObject = true, $baseTime = null, $timezone = null)
	{
		$tz = $timezone ? $timezone : self::$timezone;
		
		if (is_numeric($baseTime)) {
			$dt0 = static::createFromTimestamp($baseTime, $tz);
		} elseif (is_object($baseTime) && $baseTime instanceof Dt) {
			$dt0 = clone $baseTime;
			if ($tz) { $dt0->setTimezone($tz); }
		} else {
			$dt0 = new Dt($baseTime, $tz);
		}

		if (!$dt0) {
			return false;
		}

		switch ($period) {
			// seconds
			case 's':
			case 'sec':
			case 'second':
				$dt0->startOfSecond();
				$dt1 = clone $dt0;
				$dt1->endOfSecond();
				break;
			// minutes
			case 'm':
			case 'min':
			case 'minute':
				$dt0->startOfMinute();
				$dt1 = clone $dt0;
				$dt1->endOfMinute();
				break;
			// hours
			case 'h':
			case 'hour':
				$dt0->startOfHour();
				$dt1 = clone $dt0;
				$dt1->endOfHour();
				break;
			// today
			case 'd':
			case 'day':
			case 'today':
				$dt0->startOfDay();
				$dt1 = clone $dt0;
				$dt1->endOfDay();
				break;
			// yesterday
			case '-1d':
			case '-1 day':
			case 'day ago':
			case 'day_ago':
			case 'yesterday':
			case 'previous day':
			case 'previous_day':
				$dt0->subDay()->startOfDay();
				$dt1 = clone $dt0;
				$dt1->endOfDay();
				break;
			// tomorrow
			case '+1d':
			case '+1 day':
			case 'tomorrow':
			case 'next day':
			case 'next_day':
				$dt0->addDay()->startOfDay();
				$dt1 = clone $dt0;
				$dt1->endOfDay();
				break;
			// this week
			case 'w':
			case 'week':
			case 'this week':
			case 'this_week':
			case 'current week':
			case 'current_week':
				$dt0->startOfWeek();
				$dt1 = clone $dt0;
				$dt1->endOfWeek();
				break;
			// previous week
			case '-1w':
			case '-1 week':
			case 'last week':
			case 'last_week':
			case 'previous week':
			case 'previous_week':
			case 'week ago':
			case 'week_ago':
				$dt0->subWeek()->startOfWeek();
				$dt1 = clone $dt0;
				$dt1->endOfWeek();
				break;
			// next week
			case '+1w':
			case '+1 week':
			case 'next week':
			case 'next_week':			
				$dt0->addWeek()->startOfWeek();
				$dt1 = clone $dt0;
				$dt1->endOfWeek();
				break;
			// month
			case 'mo':
			case 'month':
			case 'this month':
			case 'this_month':
			case 'current month':
			case 'current_month':
				$dt0->startOfMonth();
				$dt1 = clone $dt0;
				$dt1->endOfMonth();
				break;
			// previous month
			case '-1mo':
			case '-1 month':
			case 'last month':
			case 'last_month':
			case 'previous month':
			case 'previous_month':
			case 'month ago':
			case 'month_ago':
				$dt0->subMonthsWithNoOverflow()->startOfMonth();
				$dt1 = clone $dt0;
				$dt1->endOfMonth();
				break;
			// next month
			case '+1mo':
			case '+1 month':
			case 'next month':
			case 'next_month':
				$dt0->addMonthNoOverflow()->startOfMonth();
				$dt1 = clone $dt0;
				$dt1->endOfMonth();
				break;
			// year
			case 'y':
			case 'year':
			case 'this year':
			case 'this_year':
			case 'current year':
			case 'current_year':
				$dt0->startOfYear();
				$dt1 = clone $dt0;
				$dt1->endOfYear();
				break;
			// previous year
			case '-1y':
			case '-1 year':
			case 'last year':
			case 'last_year':
			case 'previous year':
			case 'previous_year':
			case 'year ago':
			case 'year_ago':
				$dt0->subYearWithNoOverflow()->startOfYear();
				$dt1 = clone $dt0;
				$dt1->endOfYear();
				break;
			// next year
			case '+1y':
			case '+1 year':
			case 'next year':
			case 'next_year':
				$dt0->addYearWithNoOverflow()->startOfYear();
				$dt1 = clone $dt0;
				$dt1->endOfYear();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Invalid period definition: %s', $period));
		}

		// return
		return $returnObject===true ? [$dt0, $dt1] : [$dt0->getTimestamp(), $dt1->getTimestamp()];
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
			if ($tz) { $dt1->setTimezone($tz); }
		} else {
			$dt1 = new Dt($baseTime, $tz);
		}

		if (!$dt0 || !$dt1) {
			return false;
		}

		// return
		return $returnObject===true ? [$dt0, $dt1] : [$dt0->getTimestamp(), $dt1->getTimestamp()];
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
			if ($tz) { $dt->setTimezone($tz); }
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

		$format = $width=='long' ? 'dddd' : 'ddd';
		for ($m=0; $m<7; ++$m) {
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

		$format = $width=='long' ? 'MMMM' : 'MMM';
		for ($m=1; $m<=12; ++$m) {
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
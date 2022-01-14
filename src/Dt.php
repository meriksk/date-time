<?php
namespace meriksk\DateTime;

use DateTimeZone;
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
	private static $convertTable = [
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
	 * Get locale format definition
	 * @param string $locale
	 * @param string $format
	 * @return array
	 */
	public static function getFormatDefinition($locale = 'en', $format = NULL)
	{
		$arr = [
			'en' => [
				'date'					=> 'n/j/Y',
				'date_short'			=> 'n/j/Y',
				'date_medium'			=> 'M j, Y',
				'date_long'				=> 'F j, Y',
				'date_full'				=> 'F j, Y',
				'date_time'				=> 'n/j/Y g:i:s A',
				'date_time_short'		=> 'n/j/Y g:i A',
				'date_time_short_tz'	=> 'n/j/Y g:i A T',
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
			],
			'sk' => [
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
			],
		];

		$output = [];
		if (isset($arr[$locale])) {
			if($format!==NULL) {
				$output = isset($arr[$locale][$format]) ? $arr[$locale][$format] : NULL;
			} else {
				$output = $arr[$locale];
			}
		}

		return $output ? $output : $arr['en'];
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
	public static function f($format, $timestamp = NULL, $timezone = NULL, $locale = NULL)
	{
		if (is_numeric($timestamp)) {
			$dt = static::createFromTimestamp($timestamp, $timezone);
		} elseif (is_object($timestamp) && $timestamp instanceof \Carbon) {
			$dt = new Dt($timestamp);
		} else {
			$dt = new Dt($timestamp, $timezone);
		}

		$f = self::getFormat($format, self::TARGET_CARBON, $locale);
		$dt->setLocale($locale);

		return $dt ->isoFormat($f, $format);
	}

	/**
	 * Create a Dt instance from a string.
	 * @param string $datetime
	 * @param \DateTimeZone|string|null $tz
	 * @return static
	 * @throws InvalidArgumentException
	 */
	public static function p($datetime, $format = NULL, $tz = NULL, $locale = NULL)
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
	 * @param string $alias
	 * @param string $targetFormat
	 * @param string $locale
	 * @return string
	 */
	public static function getFormat($alias, $targetFormat = NULL, $locale = NULL)
	{
		// output format
		$f = null;

		// set locale
		$locale = !empty($locale) ? trim(strtolower($locale)) : self::$locale;

		// get conversion table
		$conversionTable = self::getFormatDefinition($locale);

		// look for predefined format (date, datetime,...)
		if (isset($conversionTable[$alias])) {
			$f = $conversionTable[$alias];
		} else {
			$f = $alias;
		}

		// convert format
		if (!empty($targetFormat)) {
			$f = static::convertFormat($f, $targetFormat);
		} else {
			// Check for Windows to find and replace the %e
			// modifier correctly
			if ($targetFormat===self::TARGET_CLIB && self::isWindows()) {
				$f = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format); // @codeCoverageIgnore
			}
		}

		return $f;
	}

	/**
	 * Converts format
	 * @param string $format
	 * @param string $targetFormat
	 * @return string date format
	 */
	public static function convertFormat($format, $targetFormat, $locale = NULL)
	{
		// YII2 === ICU
		if ($targetFormat===self::TARGET_YII2) {
			$targetFormat = self::TARGET_ICU;
		}

		// set locale
		$locale = !empty($locale) ? trim(strtolower($locale)) : self::$locale;

		// defs
		$defs = self::getFormatDefinition($locale);
		if (isset($defs[$format])) {
			$format = $defs[$format];
		}

		if (isset(self::$convertTable[$targetFormat])) {

			$format = strtr($format, self::$convertTable[$targetFormat]);

			// remove extra padding
			if ($targetFormat===self::TARGET_CLIB) {
				$format = str_replace('%l ', '%l', $format);

				// stripping leading zeros
				if (self::$strippLeadingZeros === true) {
					$replace = self::isWindows() ? ['%#e', '%#m'] : ['%-e', '%-m'];
					$format = str_replace(['%e', '%m'], $replace, $format);
				}
			}
		}

		return $format;
	}

	/**
	 * Get timestamp of given date
	 * @see DateTime::boundaries()
	 * @return int
	 */
	public static function createTimestamp($date, $timezone = NULL)
	{
		$dt = new Dt($date, $timezone);
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
	 * Calculate boundaries for given timeframe.
	 * @param string $period
	 *
	 * Possible parameters:<br>
	 * 's', 'second'<br>
	 * 'm', 'minute'<br>
	 * 'h', 'hour'<br>
	 * 'd', 'day', 'today', 'start of day', 'morning'<br>
	 * 'midnight', 'today midnight', 'end of day'<br>
	 * '-1d', '-1 day', '1 day ago', 'day ago', 'yesterday', 'previous day'<br>
	 * '+1d', '+1 day', 'tomorrow', 'next day'<br>
	 * 'w', 'week', 'this week', 'current week', 'start of week'<br>
	 * 'end of week'<br>
	 * '-1w', '-1 week', 'previous week', 'week ago', 'one week ago'<br>
	 * '+1w', '+1 week', 'next week'<br>
	 * 'mo', 'month', 'this month', 'current month', 'start of month'<br>
	 * '-1mo', '-1 month', '1 month ago', 'month ago', 'one month ago'<br>
	 * '+1mo', '+1 month', 'next month'<br>
	 * 'y', 'year', 'this year', 'current year', 'start of year'<br>
	 * '-1y', '-1 year', 'previous year', 'year ago', 'one year ago'<br>
	 * '+1y', '+1 year', 'next year'<br>
	 *
	 * @param bool $returnObject
	 * @param int|\DateTime $baseTime Base time
	 * @return DateTime object
	 * @param string $timezone
	 * @throws \InvalidArgumentException
	 */
	public static function getTime($period, $returnObject = true, $baseTime = null, $timezone = null)
	{
		// custom time
		if (is_numeric($baseTime) && $baseTime > 0) {
			$dt = new Dt((float)$baseTime, $timezone);
		// now
		} else {
			$dt = new Dt($baseTime, $timezone);
		}

		if (!$dt) {
			return false;
		}

		switch ($period) {
			// seconds
			case 's':
			case 'second':
				$dt->startOfSecond();
				break;
			// minutes
			case 'm':
			case 'minute':
				$dt->startOfMinute();
				break;
			// hours
			case 'h':
			case 'hour':
				$dt->startOfHour();
				break;
			// today
			case 'd':
			case 'day':
			case 'today':
			case 'morning':
			case 'start of day':
				$dt->startOfDay();
				break;
			// today midnight
			case 'midnight':
			case 'today midnight':
			case 'end of day':
				$dt->endOfDay();
				break;
			// yesterday
			case '-1d':
			case '-1 day':
			case 'day ago':
			case '1 day ago':
			case 'yesterday':
			case 'previous day':
				$dt->subDay()->startOfDay();
				break;
			// tomorrow
			case '+1d':
			case '+1 day':
			case 'tomorrow':
			case 'next day':
				$dt->addDay()->startOfDay();
				break;
			// this week
			case 'w':
			case 'week':
			case 'this week':
			case 'current week':
			case 'start of week':
				$dt->startOfWeek();
				break;
			// end of this week
			case 'end of week':
				$dt->endOfWeek();
				break;
			// previous week
			case '-1w':
			case '-1 week':
			case 'previous week':
			case 'week ago':
			case 'one week ago':
				$dt->subWeek()->startOfWeek();
				break;
			// next week
			case '+1w':
			case '+1 week':
			case 'next week':
				$dt->addWeek()->startOfWeek();
				break;
			// month
			case 'mo':
			case 'month':
			case 'this month':
			case 'current month':
			case 'start of month':
				$dt->startOfMonth();
				break;
			// previous month
			case '-1mo':
			case '-1 month':
			case '1 month ago':
			case 'month ago':
			case 'one month ago':
				$dt->subMonthsWithNoOverflow()->startOfMonth();
				break;
			// next month
			case '+1mo':
			case '+1 month':
			case 'next month':
				$dt->addMonthNoOverflow()->startOfMonth();
				break;
			// year
			case 'y':
			case 'year':
			case 'this year':
			case 'current year':
			case 'start of year':
				$dt->startOfYear();
				break;
			// previous year
			case '-1y':
			case '-1 year':
			case 'previous year':
			case 'year ago':
			case 'one year ago':
				$dt->subYearWithNoOverflow()->startOfYear();
				break;
			// next year
			case '+1y':
			case '+1 year':
			case 'next year':
				$dt->addYearWithNoOverflow()->startOfYear();
				break;
			default:
				throw new \InvalidArgumentException(sprintf('Invalid period definition: %s', $period));
		}

		// return
		return $returnObject===true ? $dt : $dt->getTimestamp();
	}

	/**
	 * Get relative time
	 * @param string $period
	 * @param int|float $value the $value count passed in
	 * @param bool $returnObject
	 * @param int|\DateTime $time Start time
	 * @param string $timezone
	 * @return int|\meriksk\DateTime\Dt
	 */
	public static function getRelativeTime($period, $value = 1, $returnObject = true, $time = null, $timezone = null)
	{
		// custom time
		if (is_numeric($time) && $time > 0) {
			$dt = new Dt((float)$time, $timezone);
		// now
		} else {
			$dt = new Dt($time, $timezone);
		}

		if (!$dt) {
			return false;
		}

		switch ($period) {
			// second
			case 's':
			case 'second':
			case 'seconds':
				$dt->subSeconds($value);
				break;
			// minute
			case 'm':
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
	 * 's', 'second'<br>
	 * 'm', 'minute'<br>
	 * 'h', 'hour'<br>
	 * 'd', 'day', 'today'<br>
	 * '-1d', '-1 day', '1 day ago', 'day ago', 'yesterday', 'previous day'<br>
	 * '+1d', '+1 day', 'tomorrow', 'next day'<br>
	 * 'w', 'week', 'this week', 'current week'<br>
	 * '-1w', '-1 week', 'previous week', 'week ago', 'one week ago'<br>
	 * '+1w', '+1 week', 'next week'<br>
	 * 'mo', 'month', 'this month', 'current month'<br>
	 * '-1mo', '-1 month', '1 month ago', 'month ago', 'one month ago'<br>
	 * '+1mo', '+1 month', 'next month'<br>
	 * 'y', 'year', 'this year', 'current year'<br>
	 * '-1y', '-1 year', 'previous year', 'year ago', 'one year ago'<br>
	 * '+1y', '+1 year', 'next year'<br>
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

		$dt0 = self::getTime($period, true, $baseTime, $timezone);
		$dt1 = clone $dt0;

		if (!$dt0) {
			return false;
		}

		switch ($period) {
			// seconds
			case 's':
			case 'second':
				$dt1->endOfSecond();
				break;
			// minutes
			case 'm':
			case 'minute':
				$dt1->endOfMinute();
				break;
			// hours
			case 'h':
			case 'hour':
				$dt1->endOfHour();
				break;
			// today
			case 'd':
			case 'day':
			case 'today':
			case 'morning':
			case 'start of day':
				$dt1->endOfDay();
				break;
			// yesterday
			case '-1d':
			case '-1 day':
			case 'day ago':
			case '1 day ago':
			case 'yesterday':
			case 'previous day':
				$dt1->endOfDay();
				break;
			// tomorrow
			case '+1d':
			case '+1 day':
			case 'tomorrow':
			case 'next day':
				$dt1->endOfDay();
				break;
			// this week
			case 'w':
			case 'week':
			case 'this week':
			case 'current week':
			case 'start of week':
				$dt1->endOfWeek();
				break;
			// previous week
			case '-1w':
			case '-1 week':
			case 'previous week':
			case 'week ago':
			case 'one week ago':
				$dt1->endOfWeek();
				break;
			// next week
			case '+1w':
			case '+1 week':
			case 'next week':
				$dt1->endOfWeek();
				break;
			// month
			case 'mo':
			case 'month':
			case 'this month':
			case 'current month':
			case 'start of month':
				$dt1->endOfMonth();
				break;
			// previous month
			case '-1mo':
			case '-1 month':
			case '1 month ago':
			case 'month ago':
			case 'one month ago':
				$dt1->endOfMonth();
				break;
			// next month
			case '+1mo':
			case '+1 month':
			case 'next month':
				$dt1->endOfMonth();
				break;
			// year
			case 'y':
			case 'year':
			case 'this year':
			case 'current year':
			case 'start of year':
				$dt1->endOfYear();
				break;
			// previous year
			case '-1y':
			case '-1 year':
			case 'previous year':
			case 'year ago':
			case 'one year ago':
				$dt1->endOfYear();
				break;
			// next year
			case '+1y':
			case '+1 year':
			case 'next year':
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
		$dt0 = self::getRelativeTime($period, $value, true, $baseTime, $timezone);
		$dt1 = null;

		// custom time
		if (is_numeric($baseTime) && $baseTime > 0) {
			$dt1 = new Dt((float)$baseTime, $timezone);
		// now
		} else {
			$dt1 = new Dt($baseTime, $timezone);
		}

		if (!$dt0) {
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
	public static function secondsToWords($seconds)
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
	 * Get years options (HTML)
	 * @param int $from
	 * @param int $to
	 * @return array
	 */
	public static function getYearOptions($from = NULL, $to = NULL)
	{
		$options = [];
		$from = (int)$from;
		$to = (int)$to;

		if (empty($from)) {
			$from = date('Y') - 100;
		}

		if (empty($to)) {
			$to = date('Y');
		}

		for ($i = $to; $i >= $from; $i--) {
			$options[ $i ] = $i;
		}

		return $options;
	}

	/**
	 * Checks if instance is day time (05:00 - 21:00)
	 * @param string|DateTimeZone $timezone String or DateTimeZone object Desired timezone
	 * @param int|string $start Start of the day (number of minutes past midnight), default: 5:00 AM
	 * @param int|string $end End of the day (number of minutes past midnight), default: 9:00 PM
	 * @return boolean or NULL if error occurred
	 */
	public function isDay($timezone=null, $start=300, $end=1260)
	{

		$dt = clone $this;

		// desired timezone
		if ($timezone) {
			$dt->setTimezone($timezone);
		}

		// support time as string ("05:00")
		if (is_string($start) && preg_match('/^([0-9]{2}):([0-9]{2})$/', $start, $m)) {
			$start = ($m[1] * 60) + (int)$m[2];
		}
		if (is_string($end) && preg_match('/^([0-9]{2}):([0-9]{2})$/', $end, $m)) {
			$end = ($m[1] * 60) + (int)$m[2];
		}

		// calculate seconds from the midnight
		$seconds = ($dt->hour * 60) + $dt->minute;

		// (00:00 <-> 05:00) AND (21:00 <-> 23:59)
		return $seconds >= $start && $seconds < $end;
	}

	/**
	 * Checks if instance is night time (21:00 - 05:00)
	 * @param string|DateTimeZone $timezone String or DateTimeZone object
	 * @param int $start Start of the day (number of minutes past midnight), default: 5:00 AM
	 * @param int $end End of the day (number of minutes past midnight), default: 9:00 PM
	 * @return bool or NULL if error occurred
	 */
	public function isNight($timezone=NULL, $start=300, $end=1260)
	{
		return !$this->isDay($timezone, $start, $end);
	}

}
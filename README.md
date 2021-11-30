# Dt (PHP DateTime Helper)

[![Latest Stable Version](https://img.shields.io/packagist/v/meriksk/date-time.svg?style=flat-square)](https://packagist.org/packages/meriksk/date-time)
[![Total Downloads](https://img.shields.io/packagist/dt/meriksk/date-time.svg?style=flat-square)](https://packagist.org/packages/meriksk/date-time)

An international PHP extension for DateTime. Dt uses Carbon to manage date and time. [http://carbon.nesbot.com](http://carbon.nesbot.com)

## Examples

```php
<?php

use meriksk\DateTime\Dt;

// current time
printf("Right now is %s", Dt::now()->toDateTimeString());
printf("Right now in Vancouver is %s", Dt::now('America/Vancouver'));  //implicit __toString()

// get format - it supports also aliases for date formats, i.e.: "date_time" is shortcut for "n/j/Y g:i:s A".
$format = Dt::getFormat('date'); // 'n/j/Y'
$format = Dt::getFormat('date_time'); // 'n/j/Y g:i:s A'
$format = Dt::getFormat('date_time_short'); // 'n/j/Y g:i A'
$format = Dt::getFormat('date_time_short', null, 'sk'); // 'j.n.Y H:i'

// convert format
$format = Dt::getFormat('date_time', Dt::TARGET_MOMENT); // D.M.YYYY HH:mm:ss

// get time
$dt = Dt::getTime('-1 week'); //  Sub one week to the instance and resets the date to the first day of week
$dt = Dt::getTime('+1 month'); //  Add one month to the instance with overflow explicitly forbidden and resets the date to the first day of month

// get boundaries
$b = Dt::getBoundaries('day', true); // boundaries for today
$b = Dt::getBoundaries('-1 day', true); // boundaries for yestarday
```

## Installation

### With Composer

```
$ composer require meriksk/date-time
```

```json
{
    "require": {
        "meriksk/date-time": "*"
    }
}
```

```php
<?php
require 'vendor/autoload.php';

use meriksk\DateTime\Dt;

printf("Now: %s", Dt::now());
```

## Docs

[http://carbon.nesbot.com/docs](http://carbon.nesbot.com/docs)

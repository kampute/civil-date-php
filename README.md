# Civil Date PHP Package

English | [فارسی](./README.fa.md)

This package handles Jalali, Gregorian, and Islamic dates with one consistent, predictable model. In the Jalali calendar, Nowruz and leap years follow the official Iranian calendar rule and the vernal equinox, not fixed approximate cycles.

## Purpose

Most Jalali date libraries use repeating arithmetic cycles to decide leap years. That is fast, but it is still an approximation. In some years, a cycle can place Nowruz on a different Gregorian day than the official calendar rule.

This library derives each Jalali year from the vernal equinox in Iran civil time. If the equinox occurs before local noon, Nowruz is that civil day; otherwise, Nowruz is the next civil day. Leap years follow from the number of days between one Nowruz and the next.

## Features

- Immutable date classes, one for each of the Jalali, Gregorian, and Islamic calendars.
- Date construction from multiple sources: conversion from another calendar, from a day-of-year, from the nth weekday occurrence in a month or year, and more.
- Formatting and parsing of dates from text, including month and day names, seasons and eras, and numbers as digits or words, matching the locale.
- Date comparison by day, week, month, quarter, and year.
- Creating new dates by adding or subtracting days, months, or years.
- Jalali leap years and year lengths based on astronomical Nowruz.
- Configurable Islamic month lengths for applications that need authoritative 29- or 30-day month data.
- PHP 8.1+ support, PSR-4 autoloading, and no runtime dependencies.

## Installation

Install with Composer:

```bash
composer require kampute/civil-date
```

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\JalaliCalendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\JalaliDate;

$nowruz = JalaliDate::fromGregorianDate(2025, 3, 21);

echo $nowruz;
// 1404/01/01

echo $nowruz->toIso8601DateString();
// 2025-03-21

echo $nowruz->toCalendar(Calendar::Gregorian);
// 2025/03/21

echo $nowruz->isLeapYear ? 'leap' : 'common';
// common

$equinox = JalaliCalendar::instance()->vernalEquinox(1404);

echo $equinox->format('Y-m-d H:i:s P');
// 2025-03-20 12:31:36 +03:30

$firstSaturday = JalaliDate::fromNthDayOfWeekInMonth(
    1404,
    1, // Farvardin
    1, // First occurrence
    DayOfWeek::Saturday
);

echo $firstSaturday;
// 1404/01/02
```

## Formatting and Parsing

Formatting uses a PHP-style pattern language and can combine multiple calendars in a single output. The default locale is Persian, so Jalali output uses Persian digits and names without extra configuration.

```php
use Kampute\CivilDate\JalaliDate;

$date = new JalaliDate(1405, 2, 14);

echo $date->format('l j F Y');
// دوشنبه ۱۴ اردیبهشت ۱۴۰۵

echo $date->format('Y/m/d [gregorian:Y-m-d] [islamic:Y/m/d]');
// ۱۴۰۵/۰۲/۱۴ ۲۰۲۶-۰۵-۰۴ ۱۴۴۷/۱۱/۱۷

echo $date->format('روز R و هفته K سال Y');
// روز چهل و پنجم و هفته هفتم سال ۱۴۰۵

$parsed = JalaliDate::parse('دوشنبه ۱۴ اردیبهشت ۱۴۰۵', 'l j F Y');

echo $parsed;
// 1405/02/14

$lastWednesday = JalaliDate::parse('آخرین چهارشنبه ۱۴۰۵', 'K l Y');

echo $lastWednesday;
// 1405/12/26

$ordinalDate = JalaliDate::parse('⁧روز دویست و پنجاه و ششم سال ۱۴۰۶⁩', '⁧روز R سال Y⁩');

echo $ordinalDate;
// 1406/09/10
```

## Localization

Locale definitions are decoupled from the calendar logic. Persian is enabled by default, English and Afghan Persian (`fa-AF`) are also included, and you can add your own locale if your project needs different wording or another language.

```php
use Kampute\CivilDate\JalaliDate;

$date = new JalaliDate(1405, 2, 14);

echo $date->format('l j F Y', ['locale' => 'en']);
// Monday 14 Ordibehesht 1405

echo $date->format('l j F Y', ['locale' => 'fa-AF']);
// دوشنبه ۱۴ ثور ۱۴۰۵

$parsed = JalaliDate::parse('Monday 14 Ordibehesht 1405', 'l j F Y', ['locale' => 'en']);

echo $parsed;
// 1405/02/14
```

## Islamic Month Lengths

Islamic dates use the tabular civil calendar by default. If your application has authoritative month lengths, configure them during bootstrap before the Islamic calendar is used.

```php
use Kampute\CivilDate\Calendars\IslamicCalendar;

IslamicCalendar::instance()->setAuthoritativeMonthLengths([
    1446 => [
        9 => 29,
        10 => 30,
    ],
]);
```

Months without an entry keep their tabular length. Configured lengths affect the boundary of later Islamic dates, so load the data once at startup.

## Accuracy and Range

Jalali conversions rely on an astronomical estimate of the vernal equinox evaluated in Iran civil time. This approach is designed to match the official calendar rule across a wide historical range while remaining deterministic across different runs.

Only years whose vernal equinox falls very close to noon in Iran can be sensitive. Under the official rule, if the equinox occurs before noon, Nowruz is that same day; if it occurs at noon or later, Nowruz is the next day. As a result, if two astronomical calculations place the equinox on opposite sides of that boundary, they can produce different Nowruz dates.

The supported Jalali range is year `-1622` through `2378`, equivalent to `1001 BCE` through `3000 CE`. Please note that year zero is not defined.

## Documentation

Read the full documentation and API reference at [https://kampute.github.io/civil-date/](https://kampute.github.io/civil-date/).

## Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```

Run static analysis and code style checks:

```bash
composer analyse
composer run cs-check
composer run cs-fix
```

Build the documentation site locally:

```bash
composer docs
```

Then open `.site/index.html`.

## License

[MIT](./LICENSE) © Kampute

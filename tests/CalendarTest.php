<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\GregorianCalendar;
use Kampute\CivilDate\Calendars\IslamicCalendar;
use Kampute\CivilDate\Calendars\JalaliCalendar;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\IslamicDate;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests calendar.
 */
final class CalendarTest extends TestCase
{
    /**
     * Tests calendar metadata.
     */
    #[DataProvider('calendarProvider')]
    public function testCalendarMetadata(Calendar $calendar, string $calendarDateClass, CalendarSystem $calendarSystem): void
    {
        self::assertSame($calendarDateClass, $calendar->dateClass());
        self::assertSame($calendarSystem, $calendar->system());
    }

    /**
     * Provides data for calendar tests.
     *
     * @return array<array{Calendar,string,CalendarSystem}> Provider data sets.
     */
    public static function calendarProvider(): array
    {
        return [
            'Jalali' => [Calendar::Jalali, JalaliDate::class, JalaliCalendar::instance()],
            'Gregorian' => [Calendar::Gregorian, GregorianDate::class, GregorianCalendar::instance()],
            'Islamic' => [Calendar::Islamic, IslamicDate::class, IslamicCalendar::instance()],
        ];
    }
}

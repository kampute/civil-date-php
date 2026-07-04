<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date factories.
 */
final class IslamicDateFactoriesTest extends TestCase
{
    /**
     * Tests today uses islamic test today.
     */
    public function testTodayUsesIslamicTestToday(): void
    {
        $today = new IslamicDate(1446, 9, 1);
        IslamicDate::setTestToday($today);
        try {
            self::assertSame($today, IslamicDate::today());
        } finally {
            IslamicDate::setTestToday(null);
        }
    }

    /**
     * Tests yesterday.
     */
    public function testYesterday(): void
    {
        IslamicDate::setTestToday(new IslamicDate(1446, 9, 1));
        try {
            self::assertSame([1446, 8, 29], IslamicDate::yesterday()->toArray());
        } finally {
            IslamicDate::setTestToday(null);
        }
    }

    /**
     * Tests tomorrow.
     */
    public function testTomorrow(): void
    {
        IslamicDate::setTestToday(new IslamicDate(1446, 9, 1));
        try {
            self::assertSame([1446, 9, 2], IslamicDate::tomorrow()->toArray());
        } finally {
            IslamicDate::setTestToday(null);
        }
    }

    /**
     * Tests today converts a gregorian test date.
     */
    public function testTodayConvertsGregorianTestToday(): void
    {
        GregorianDate::setTestToday(new GregorianDate(2024, 7, 8));
        try {
            self::assertSame([1446, 1, 1], IslamicDate::today()->toArray());
            self::assertSame([1446, 1, 1], IslamicDate::getTestToday()?->toArray());
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Tests from j d n.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromJDNProvider')]
    public function testFromJDN(int $jdn, array $expected): void
    {
        self::assertSame($expected, IslamicDate::fromJDN($jdn)->toArray());
    }

    /**
     * Provides data for from j d n tests.
     *
     * @return array<array{int,array<mixed>}> Provider data sets.
     */
    public static function fromJDNProvider(): array
    {
        return [
            'Islamic epoch' => [1948440, [1, 1, 1]],
            'Day before epoch' => [1948439, [-1, 12, 29]],
            'Muharram 1446' => [2460500, [1446, 1, 1]],
            'Ramadan 1446' => [2460736, [1446, 9, 1]],
            'Muharram 1447' => [2460854, [1447, 1, 1]],
        ];
    }

    /**
     * Tests from j d n round trips.
     */
    #[DataProvider('roundTripProvider')]
    public function testFromJDNRoundTrips(IslamicDate $date): void
    {
        self::assertEquals($date, IslamicDate::fromJDN($date->jdn()));
    }

    /**
     * Provides data for round-trip tests.
     *
     * @return array<array{IslamicDate}> Provider data sets.
     */
    public static function roundTripProvider(): array
    {
        return [[new IslamicDate(-1, 1, 1)], [new IslamicDate(1, 1, 1)], [new IslamicDate(2, 12, 30)], [new IslamicDate(1446, 12, 29)]];
    }

    /**
     * Tests from gregorian date.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('gregorianFactoryProvider')]
    public function testFromGregorianDate(string $isoDate, array $expected): void
    {
        [$year, $month, $day] = array_map('intval', explode('-', $isoDate));
        self::assertSame($expected, IslamicDate::fromGregorianDate($year, $month, $day)->toArray());
    }

    /**
     * Tests from iso8601 date string.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('gregorianFactoryProvider')]
    public function testFromIso8601DateString(string $isoDate, array $expected): void
    {
        self::assertSame($expected, IslamicDate::fromIso8601DateString($isoDate)->toArray());
    }

    /**
     * Tests from date time.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('gregorianFactoryProvider')]
    public function testFromDateTime(string $isoDate, array $expected): void
    {
        self::assertSame($expected, IslamicDate::fromDateTime(new DateTimeImmutable($isoDate . ' 23:30:00 +14:00'))->toArray());
    }

    /**
     * Provides data for gregorian-backed factory tests.
     *
     * @return array<array{string,array<mixed>}> Provider data sets.
     */
    public static function gregorianFactoryProvider(): array
    {
        return [
            'Islamic epoch' => ['0622-07-19', [1, 1, 1]],
            'Muharram 1446' => ['2024-07-08', [1446, 1, 1]],
            'Ramadan 1446' => ['2025-03-01', [1446, 9, 1]],
        ];
    }

    /**
     * Tests from day of month.
     */
    public function testFromDayOfMonth(): void
    {
        self::assertSame([1446, 2, 29], IslamicDate::fromDayOfMonth(1446, 2, 29)->toArray());
    }

    /**
     * Tests from day of year.
     */
    public function testFromDayOfYear(): void
    {
        self::assertSame([1446, 9, 1], IslamicDate::fromDayOfYear(1446, 237)->toArray());
    }

    /**
     * Tests from nth day of week in year.
     */
    public function testFromNthDayOfWeekInYear(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertEquals($date, IslamicDate::fromNthDayOfWeekInYear(1446, $date->dayOfWeekInYear, $date->dayOfWeek));
    }

    /**
     * Tests from nth day of week in month.
     */
    public function testFromNthDayOfWeekInMonth(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertEquals($date, IslamicDate::fromNthDayOfWeekInMonth(1446, 9, $date->dayOfWeekInMonth, $date->dayOfWeek));
    }

    /**
     * Tests from gregorian date rejects invalid input.
     */
    public function testFromGregorianDateRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IslamicDate::fromGregorianDate(2025, 2, 29);
    }

    /**
     * Tests from iso8601 date string rejects invalid input.
     */
    public function testFromIso8601DateStringRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IslamicDate::fromIso8601DateString('2025-02-29');
    }

    /**
     * Tests from day of month rejects invalid input.
     */
    public function testFromDayOfMonthRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IslamicDate::fromDayOfMonth(1446, 2, 30);
    }

    /**
     * Tests from day of year rejects invalid input.
     */
    public function testFromDayOfYearRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IslamicDate::fromDayOfYear(1446, 355);
    }

    /**
     * Tests nth day factory rejects invalid input.
     */
    public function testFromNthDayOfWeekInMonthRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IslamicDate::fromNthDayOfWeekInMonth(1446, 1, 0, DayOfWeek::Friday);
    }
}

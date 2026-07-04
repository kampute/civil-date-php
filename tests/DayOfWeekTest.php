<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests day of week.
 */
final class DayOfWeekTest extends TestCase
{
    /**
     * Tests days until.
     */
    #[DataProvider('daysUntilProvider')]
    public function testDaysUntil(DayOfWeek $origin, DayOfWeek $target, int $expected): void
    {
        self::assertSame($expected, $origin->daysUntil($target));
    }

    /**
     * Provides data for days until tests.
     *
     * @return array<array{DayOfWeek,DayOfWeek,int}> Provider data sets.
     */
    public static function daysUntilProvider(): array
    {
        return [
            'Same day of week' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 0],
            'Saturday to Sunday' => [DayOfWeek::Saturday, DayOfWeek::Sunday, 1],
            'Sunday to Saturday' => [DayOfWeek::Sunday, DayOfWeek::Saturday, 6],
            'Wednesday to Monday' => [DayOfWeek::Wednesday, DayOfWeek::Monday, 5],
            'Friday to Tuesday' => [DayOfWeek::Friday, DayOfWeek::Tuesday, 4],
        ];
    }

    /**
     * Tests days until occurrence.
     */
    #[DataProvider('daysUntilOccurrenceProvider')]
    public function testDaysUntilOccurrence(DayOfWeek $origin, DayOfWeek $target, int $occurrence, int $expected): void
    {
        self::assertSame($expected, $origin->daysUntil($target, $occurrence));
    }

    /**
     * Provides data for days until occurrence tests.
     *
     * @return array<array{DayOfWeek,DayOfWeek,int,int}> Provider data sets.
     */
    public static function daysUntilOccurrenceProvider(): array
    {
        return [
            'Same day first occurrence' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 1, 0],
            'Same day second occurrence' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 2, 7],
            'Saturday to Sunday second occurrence' => [DayOfWeek::Saturday, DayOfWeek::Sunday, 2, 8],
            'Wednesday to Monday third occurrence' => [DayOfWeek::Wednesday, DayOfWeek::Monday, 3, 19],
        ];
    }

    /**
     * Tests days until rejects invalid occurrence.
     */
    #[DataProvider('invalidOccurrenceProvider')]
    public function testDaysUntilRejectsInvalidOccurrence(int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        DayOfWeek::Saturday->daysUntil(DayOfWeek::Sunday, $occurrence);
    }

    /**
     * Tests days since.
     */
    #[DataProvider('daysSinceProvider')]
    public function testDaysSince(DayOfWeek $dayOfWeek, DayOfWeek $origin, int $expected): void
    {
        self::assertSame($expected, $dayOfWeek->daysSince($origin));
    }

    /**
     * Provides data for days since tests.
     *
     * @return array<array{DayOfWeek,DayOfWeek,int}> Provider data sets.
     */
    public static function daysSinceProvider(): array
    {
        return [
            'Same day of week' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 0],
            'Sunday since Saturday' => [DayOfWeek::Sunday, DayOfWeek::Saturday, 1],
            'Saturday since Sunday' => [DayOfWeek::Saturday, DayOfWeek::Sunday, 6],
            'Monday since Wednesday' => [DayOfWeek::Monday, DayOfWeek::Wednesday, 5],
            'Tuesday since Friday' => [DayOfWeek::Tuesday, DayOfWeek::Friday, 4],
        ];
    }

    /**
     * Tests days since occurrence.
     */
    #[DataProvider('daysSinceOccurrenceProvider')]
    public function testDaysSinceOccurrence(DayOfWeek $dayOfWeek, DayOfWeek $origin, int $occurrence, int $expected): void
    {
        self::assertSame($expected, $dayOfWeek->daysSince($origin, $occurrence));
    }

    /**
     * Provides data for days since occurrence tests.
     *
     * @return array<array{DayOfWeek,DayOfWeek,int,int}> Provider data sets.
     */
    public static function daysSinceOccurrenceProvider(): array
    {
        return [
            'Same day first occurrence' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 1, 0],
            'Same day second occurrence' => [DayOfWeek::Saturday, DayOfWeek::Saturday, 2, 7],
            'Sunday since Saturday second occurrence' => [DayOfWeek::Sunday, DayOfWeek::Saturday, 2, 8],
            'Monday since Wednesday third occurrence' => [DayOfWeek::Monday, DayOfWeek::Wednesday, 3, 19],
        ];
    }

    /**
     * Tests days since rejects invalid occurrence.
     */
    #[DataProvider('invalidOccurrenceProvider')]
    public function testDaysSinceRejectsInvalidOccurrence(int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        DayOfWeek::Sunday->daysSince(DayOfWeek::Saturday, $occurrence);
    }

    /**
     * Provides data for invalid occurrence tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function invalidOccurrenceProvider(): array
    {
        return [
            'Zero occurrence' => [0],
            'Negative occurrence' => [-1],
        ];
    }

    /**
     * Tests from j d n.
     */
    #[DataProvider('jdnProvider')]
    public function testFromJDN(int $jdn, DayOfWeek $expected): void
    {
        self::assertSame($expected, DayOfWeek::fromJDN($jdn));
    }

    /**
     * Provides data for jdn tests.
     *
     * @return array<array{int,DayOfWeek}> Provider data sets.
     */
    public static function jdnProvider(): array
    {
        return [
            'Negative JDN' => [-2, DayOfWeek::Saturday],
            'Zero JDN' => [0, DayOfWeek::Monday],
            'End of first cycle' => [5, DayOfWeek::Saturday],
            'Start of second cycle' => [6, DayOfWeek::Sunday],
        ];
    }

    /**
     * Tests count occurrences.
     */
    #[DataProvider('countOccurrencesProvider')]
    public function testCountOccurrences(DayOfWeek $dayOfWeek, int $startJDN, int $endJDN, int $expected): void
    {
        self::assertSame($expected, $dayOfWeek->countOccurrences($startJDN, $endJDN));
    }

    /**
     * Provides data for count occurrences tests.
     *
     * @return array<array{DayOfWeek,int,int,int}> Provider data sets.
     */
    public static function countOccurrencesProvider(): array
    {
        return [
            'Single matching day' => [DayOfWeek::Monday, 0, 0, 1],
            'Single non-matching day' => [DayOfWeek::Tuesday, 0, 0, 0],
            'Full week' => [DayOfWeek::Monday, 0, 6, 1],
            'Eight days with repeated Monday' => [DayOfWeek::Monday, 0, 7, 2],
            'Tuesday in eight-day range' => [DayOfWeek::Tuesday, 0, 7, 1],
        ];
    }

    /**
     * Tests count occurrences rejects reversed range.
     */
    public function testCountOccurrencesRejectsReversedRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DayOfWeek::Monday->countOccurrences(10, 9);
    }

    /**
     * Tests weeks spanned.
     */
    #[DataProvider('weeksSpannedProvider')]
    public function testWeeksSpanned(DayOfWeek $dayOfWeek, int $days, DayOfWeek $firstDayOfWeek, int $expected): void
    {
        self::assertSame($expected, $dayOfWeek->weeksSpanned($days, $firstDayOfWeek));
    }

    /**
     * Provides data for weeks spanned tests.
     *
     * @return array<array{DayOfWeek,int,DayOfWeek,int}> Provider data sets.
     */
    public static function weeksSpannedProvider(): array
    {
        return [
            'Single day starting on week start' => [DayOfWeek::Saturday, 1, DayOfWeek::Saturday, 1],
            'Full Saturday-to-Friday week' => [DayOfWeek::Saturday, 7, DayOfWeek::Saturday, 1],
            'Seven days starting after week start spans two weeks' => [DayOfWeek::Sunday, 7, DayOfWeek::Saturday, 2],
            'Twenty-eight days starting on week start spans four weeks' => [DayOfWeek::Saturday, 28, DayOfWeek::Saturday, 4],
            'Twenty-nine days starting on week start spans five weeks' => [DayOfWeek::Saturday, 29, DayOfWeek::Saturday, 5],
            'Thirty-one days starting Friday spans six Saturday-to-Friday weeks' => [DayOfWeek::Friday, 31, DayOfWeek::Saturday, 6],
            'Custom Sunday-to-Saturday week start' => [DayOfWeek::Sunday, 7, DayOfWeek::Sunday, 1],
        ];
    }

    /**
     * Tests weeks spanned rejects invalid day count.
     */
    #[DataProvider('invalidWeeksSpannedDayCountProvider')]
    public function testWeeksSpannedRejectsInvalidDayCount(int $days, DayOfWeek $firstDayOfWeek): void
    {
        $this->expectException(InvalidArgumentException::class);

        DayOfWeek::Saturday->weeksSpanned($days, $firstDayOfWeek);
    }

    /**
     * Provides data for invalid weeks spanned day count tests.
     *
     * @return array<array{int,DayOfWeek}> Provider data sets.
     */
    public static function invalidWeeksSpannedDayCountProvider(): array
    {
        return [
            'Zero days with Saturday week start' => [0, DayOfWeek::Saturday],
            'Negative days with Sunday week start' => [-1, DayOfWeek::Sunday],
        ];
    }
}

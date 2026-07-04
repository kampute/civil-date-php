<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support;

use InvalidArgumentException;
use Kampute\CivilDate\Support\YearNumbering;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests year numbering.
 */
final class YearNumberingTest extends TestCase
{
    /**
     * Tests astronomical and calendar year conversions.
     */
    #[DataProvider('astronomicalCalendarYearProvider')]
    public function testAstronomicalAndCalendarYearConversions(int $calendar, int $astronomical): void
    {
        self::assertSame($astronomical, YearNumbering::toAstronomicalYear($calendar));
        self::assertSame($calendar, YearNumbering::toCalendarYear($astronomical));
    }

    /**
     * Provides data for astronomical calendar year tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function astronomicalCalendarYearProvider(): array
    {
        return [
            'Year 1' => [1, 1],
            'Year 2' => [2, 2],
            'Year 1404' => [1404, 1404],
            'Year -1 (0 astronomical)' => [-1, 0],
            'Year -2 (-1 astronomical)' => [-2, -1],
            'Year -100' => [-100, -99],
            'Year 100' => [100, 100],
            'Year -1000' => [-1000, -999],
        ];
    }

    /**
     * Tests astronomical year round trips.
     */
    #[DataProvider('astronomicalYearRoundTripProvider')]
    public function testAstronomicalYearRoundTrips(int $year): void
    {
        $astronomical = YearNumbering::toAstronomicalYear($year);
        $calendar = YearNumbering::toCalendarYear($astronomical);
        self::assertSame($year, $calendar, "Round trip failed for year {$year}");
    }

    /**
     * Provides data for astronomical year round trip tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function astronomicalYearRoundTripProvider(): array
    {
        return [
            'Year -100' => [-100],
            'Year -2' => [-2],
            'Year -1' => [-1],
            'Year 1' => [1],
            'Year 2' => [2],
            'Year 100' => [100],
            'Year 1404' => [1404],
        ];
    }

    /**
     * Tests calendar year never returns zero.
     */
    #[DataProvider('astronomicalYearProvider')]
    public function testCalendarYearNeverReturnsZero(int $astronomicalYear): void
    {
        $calendar = YearNumbering::toCalendarYear($astronomicalYear);
        self::assertNotSame(0, $calendar, "Calendar year should never be 0 for astronomical {$astronomicalYear}");
    }

    /**
     * Provides data for astronomical year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function astronomicalYearProvider(): array
    {
        return [
            'Astronomical -2' => [-2],
            'Astronomical -1' => [-1],
            'Astronomical 0' => [0],
            'Astronomical 1' => [1],
            'Astronomical 2' => [2],
        ];
    }

    /**
     * Tests expand two digit year.
     */
    #[DataProvider('twoDigitExpansionProvider')]
    public function testExpandTwoDigitYear(int $twoDigit, int $reference, int $expected): void
    {
        self::assertSame($expected, YearNumbering::expandTwoDigitYear($twoDigit, $reference));
    }

    /**
     * Provides data for two digit expansion tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function twoDigitExpansionProvider(): array
    {
        return [
            // Nearest matching year around reference 1410
            '00 ref 1410' => [0, 1410, 1400],
            '05 ref 1410' => [5, 1410, 1405],
            '50 ref 1410' => [50, 1410, 1450],
            '95 ref 1410' => [95, 1410, 1395],

            // Nearest matching year around reference 1405
            '75 ref 1405' => [75, 1405, 1375],
            '45 ref 1405' => [45, 1405, 1445],

            // Nearest matching year around reference 1590
            '00 ref 1590' => [0, 1590, 1600],
            '05 ref 1590' => [5, 1590, 1605],
            '50 ref 1590' => [50, 1590, 1550],
            '95 ref 1590' => [95, 1590, 1595],

            // Nearest matching year around reference -1410
            '00 ref -1410' => [0, -1410, -1400],
            '05 ref -1410' => [5, -1410, -1405],
            '50 ref -1410' => [50, -1410, -1450],
            '95 ref -1410' => [95, -1410, -1395],

            // Nearest matching year around reference -1590
            '00 ref -1590' => [0, -1590, -1600],
            '05 ref -1590' => [5, -1590, -1605],
            '50 ref -1590' => [50, -1590, -1550],
            '95 ref -1590' => [95, -1590, -1595],

            // First century cannot produce calendar year zero
            '00 ref 1' => [0, 1, 100],
            '05 ref 1' => [5, 1, 5],
            '00 ref -1' => [0, -1, -100],
            '05 ref -1' => [5, -1, -5],
        ];
    }

    /**
     * Tests expanded year never returns zero.
     */
    public function testExpandedYearNeverReturnsZero(): void
    {
        self::assertNotSame(0, YearNumbering::expandTwoDigitYear(0, -1));
        self::assertNotSame(0, YearNumbering::expandTwoDigitYear(0, 1));
        self::assertNotSame(0, YearNumbering::expandTwoDigitYear(50, -25));
    }

    /**
     * Tests expand two digit year rejects invalid inputs.
     */
    #[DataProvider('invalidTwoDigitExpansionProvider')]
    public function testExpandTwoDigitYearRejectsInvalidInputs(int $year, int $referenceYear): void
    {
        $this->expectException(InvalidArgumentException::class);
        YearNumbering::expandTwoDigitYear($year, $referenceYear);
    }

    /**
     * Provides data for invalid two digit expansion tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function invalidTwoDigitExpansionProvider(): array
    {
        return [
            'Negative year' => [-1, 1405],
            'Year 100' => [100, 1405],
            'Large year' => [1405, 1405],
            'Reference year 0' => [10, 0],
            'Year 101' => [101, 1405],
            'Year 999' => [999, 1405],
        ];
    }
}

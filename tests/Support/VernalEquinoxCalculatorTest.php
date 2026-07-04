<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support;

use InvalidArgumentException;
use Kampute\CivilDate\Support\VernalEquinoxCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the vernal equinox calculator.
 */
final class VernalEquinoxCalculatorTest extends TestCase
{
    /**
     * Tests rejects years outside supported range and year zero.
     */
    #[DataProvider('invalidYearProvider')]
    public function testRejectsYearsOutsideSupportedRangeAndYearZero(int $year): void
    {
        $this->expectException(InvalidArgumentException::class);
        VernalEquinoxCalculator::julianDay($year);
    }

    /**
     * Provides data for invalid year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function invalidYearProvider(): array
    {
        return [
            'Far below minimum' => [VernalEquinoxCalculator::MIN_SUPPORTED_YEAR - 100],
            'Below minimum' => [VernalEquinoxCalculator::MIN_SUPPORTED_YEAR - 1],
            'Year zero' => [0],
            'Above maximum' => [VernalEquinoxCalculator::MAX_SUPPORTED_YEAR + 1],
            'Far above maximum' => [VernalEquinoxCalculator::MAX_SUPPORTED_YEAR + 100],
        ];
    }

    /**
     * Tests accepts supported range boundaries.
     */
    #[DataProvider('rangeBoundaryProvider')]
    public function testAcceptsSupportedRangeBoundaries(int $year): void
    {
        self::assertGreaterThan(0, VernalEquinoxCalculator::julianDay($year));
    }

    /**
     * Provides data for range boundary tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function rangeBoundaryProvider(): array
    {
        return [
            'Minimum year' => [VernalEquinoxCalculator::MIN_SUPPORTED_YEAR],
            'Maximum year' => [VernalEquinoxCalculator::MAX_SUPPORTED_YEAR],
            'Near minimum' => [VernalEquinoxCalculator::MIN_SUPPORTED_YEAR + 1],
            'Near maximum' => [VernalEquinoxCalculator::MAX_SUPPORTED_YEAR - 1],
        ];
    }

    /**
     * Tests known vernal equinox instants.
     */
    #[DataProvider('knownVernalEquinoxProvider')]
    public function testKnownVernalEquinoxInstants(int $year, string $expectedUtc, int $toleranceSeconds = 60): void
    {
        $expected = strtotime($expectedUtc) / 86400 + 2440587.5;
        $actual = VernalEquinoxCalculator::julianDay($year);
        $differenceSeconds = abs($actual - $expected) * 86400;

        self::assertLessThanOrEqual(
            $toleranceSeconds,
            $differenceSeconds,
            "Year {$year} vernal equinox should be within {$toleranceSeconds} seconds of {$expectedUtc}. Actual difference: {$differenceSeconds} seconds."
        );
    }

    /**
     * Provides data for known vernal equinox tests.
     *
     * @return array<array{int,string,2?:int}> Provider data sets.
     */
    public static function knownVernalEquinoxProvider(): array
    {
        return [
            '1800' => [1800, '1800-03-20T20:12:00Z'],
            '1900' => [1900, '1900-03-21T01:39:00Z'],
            '2000' => [2000, '2000-03-20T07:35:00Z'],
            '2010' => [2010, '2010-03-20T17:32:00Z'],
            '2020' => [2020, '2020-03-20T03:50:00Z'],
            '2023' => [2023, '2023-03-20T21:24:00Z'],
            '2024' => [2024, '2024-03-20T03:06:00Z'],
            '2025' => [2025, '2025-03-20T09:01:00Z'],
            '2026' => [2026, '2026-03-20T14:46:00Z'],
            '1850' => [1850, '1850-03-20T12:00:00Z', 50000],
            '1950' => [1950, '1950-03-21T04:00:00Z', 15000],
            '1975' => [1975, '1975-03-21T00:00:00Z', 25000],
            '2015' => [2015, '2015-03-20T22:45:00Z'],
            '2030' => [2030, '2030-03-20T14:00:00Z', 25000],
        ];
    }

    /**
     * Tests consecutive years have reasonable equinox difference.
     */
    public function testConsecutiveYearsHaveReasonableEquinoxDifference(): void
    {
        // Vernal equinox should advance roughly 365.24 days each year
        $jd2023 = VernalEquinoxCalculator::julianDay(2023);
        $jd2024 = VernalEquinoxCalculator::julianDay(2024);
        $jd2025 = VernalEquinoxCalculator::julianDay(2025);

        $diff2324 = $jd2024 - $jd2023;
        $diff2425 = $jd2025 - $jd2024;

        // Should be between 365 and 366 days
        self::assertGreaterThanOrEqual(365.0, $diff2324);
        self::assertLessThanOrEqual(366.5, $diff2324);
        self::assertGreaterThanOrEqual(365.0, $diff2425);
        self::assertLessThanOrEqual(366.5, $diff2425);
    }
}

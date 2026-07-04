<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date withers.
 */
final class IslamicDateWithersTest extends TestCase
{
    /**
     * Tests with year.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withYearProvider')]
    public function testWithYear(IslamicDate $date, int $year, array $expected): void
    {
        self::assertSame($expected, $date->withYear($year)->toArray());
    }

    /**
     * Provides data for with year tests.
     *
     * @return array<array{IslamicDate,int,array<mixed>}> Provider data sets.
     */
    public static function withYearProvider(): array
    {
        return [
            'Change year' => [new IslamicDate(1446, 9, 1), 1447, [1447, 9, 1]],
            'Clamp leap day' => [new IslamicDate(2, 12, 30), 1, [1, 12, 29]],
            'Negative year' => [new IslamicDate(1446, 1, 1), -1, [-1, 1, 1]],
        ];
    }

    /**
     * Tests with month.
     */
    public function testWithMonth(): void
    {
        self::assertSame([1446, 2, 29], (new IslamicDate(1446, 1, 30))->withMonth(2)->toArray());
    }

    /**
     * Tests with day.
     */
    public function testWithDay(): void
    {
        self::assertSame([1446, 2, 29], (new IslamicDate(1446, 2, 1))->withDay(29)->toArray());
    }

    /**
     * Tests withers immutability.
     */
    public function testWithersImmutability(): void
    {
        $date = new IslamicDate(1446, 1, 30);

        self::assertNotSame($date, $date->withYear(1447));
        self::assertNotSame($date, $date->withMonth(2));
        self::assertNotSame($date, $date->withDay(29));
        self::assertSame([1446, 1, 30], $date->toArray());
    }
}

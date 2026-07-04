<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support;

use DivisionByZeroError;
use Kampute\CivilDate\Support\EuclideanDivision;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests Euclidean division helpers.
 */
final class EuclideanDivisionTest extends TestCase
{
    /**
     * Tests quotient calculation for positive divisors.
     */
    #[DataProvider('quotientProvider')]
    public function testQuotient(int $dividend, int $divisor, int $expected): void
    {
        self::assertSame($expected, EuclideanDivision::quotient($dividend, $divisor));
    }

    /**
     * Provides data for quotient tests.
     *
     * @return array<string, array{int, int, int}> Provider data sets.
     */
    public static function quotientProvider(): array
    {
        return [
            'Positive exact quotient' => [12, 3, 4],
            'Positive inexact quotient' => [13, 3, 4],
            'Zero dividend' => [0, 5, 0],
            'Negative exact quotient' => [-12, 3, -4],
            'Negative inexact quotient' => [-13, 3, -5],
            'Negative dividend smaller than divisor' => [-1, 3, -1],
        ];
    }

    /**
     * Tests remainder calculation for positive divisors.
     */
    #[DataProvider('remainderProvider')]
    public function testRemainder(int $dividend, int $divisor, int $expected): void
    {
        self::assertSame($expected, EuclideanDivision::remainder($dividend, $divisor));
    }

    /**
     * Provides data for remainder tests.
     *
     * @return array<string, array{int, int, int}> Provider data sets.
     */
    public static function remainderProvider(): array
    {
        return [
            'Positive exact remainder' => [12, 3, 0],
            'Positive remainder' => [13, 3, 1],
            'Zero dividend' => [0, 5, 0],
            'Negative exact remainder' => [-12, 3, 0],
            'Negative non-zero remainder' => [-13, 3, 2],
            'Negative dividend smaller than divisor' => [-1, 3, 2],
        ];
    }

    /**
     * Tests quotient rejects a zero divisor.
     */
    public function testQuotientRejectsZeroDivisor(): void
    {
        $this->expectException(DivisionByZeroError::class);

        EuclideanDivision::quotient(1, 0);
    }

    /**
     * Tests remainder rejects a zero divisor.
     */
    public function testRemainderRejectsZeroDivisor(): void
    {
        $this->expectException(DivisionByZeroError::class);

        EuclideanDivision::remainder(1, 0);
    }
}

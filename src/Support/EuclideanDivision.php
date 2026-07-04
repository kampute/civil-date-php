<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support;

use DivisionByZeroError;

/**
 * Provides Euclidean quotient and remainder operations.
 */
final class EuclideanDivision
{
    /**
     * Disallows construction.
     */
    private function __construct()
    {
    }

    /**
     * Returns the quotient rounded toward negative infinity.
     *
     * @param int $dividend Dividend.
     * @param int $divisor Positive divisor.
     *
     * @return int Euclidean quotient.
     *
     * @throws DivisionByZeroError If the divisor is zero.
     */
    public static function quotient(int $dividend, int $divisor): int
    {
        $quotient = intdiv($dividend, $divisor);
        return $dividend % $divisor < 0 ? $quotient - 1 : $quotient;
    }

    /**
     * Returns the non-negative remainder for a positive divisor.
     *
     * @param int $dividend Dividend.
     * @param int $divisor Positive divisor.
     *
     * @return int Euclidean remainder.
     *
     * @throws DivisionByZeroError If the divisor is zero.
     */
    public static function remainder(int $dividend, int $divisor): int
    {
        $remainder = $dividend % $divisor;
        return $remainder < 0 ? $remainder + $divisor : $remainder;
    }
}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

/**
 * Represents the four seasons of the year.
 */
enum Season: int
{
    /**
     * Spring season.
     */
    case Spring = 1;

    /**
     * Summer season.
     */
    case Summer = 2;

    /**
     * Autumn season.
     */
    case Autumn = 3;

    /**
     * Winter season.
     */
    case Winter = 4;

    /**
     * Returns the season opposite to this one in the annual cycle.
     *
     * This maps equivalent seasons between the northern and southern hemispheres.
     *
     * @return self Opposite season.
     */
    public function opposite(): self
    {
        return match ($this) {
            self::Spring => self::Autumn,
            self::Summer => self::Winter,
            self::Autumn => self::Spring,
            self::Winter => self::Summer,
        };
    }
}

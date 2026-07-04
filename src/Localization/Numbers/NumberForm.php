<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization\Numbers;

/**
 * Identifies the representation used by a parsed number.
 */
enum NumberForm
{
    /**
     * Digit representation.
     */
    case Digits;

    /**
     * Cardinal word representation.
     */
    case Cardinal;

    /**
     * Ordinal word representation.
     */
    case Ordinal;
}

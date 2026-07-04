<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

/**
 * Normalizes localized text for comparison and parsing.
 */
interface TextNormalizer
{
    /**
     * Returns the normalized representation of localized text.
     *
     * @param string $text Text to normalize.
     *
     * @return string Normalized text.
     */
    public function normalize(string $text): string;
}

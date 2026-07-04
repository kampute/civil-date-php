<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use Kampute\CivilDate\Localization\TextNormalizer;

/**
 * Normalizes English localized text.
 */
class EnglishTextNormalizer implements TextNormalizer
{
    /**
     * Normalizes English text for locale lookups.
     *
     * @param string $text Text to normalize.
     *
     * @return string Lowercase text without surrounding whitespace.
     */
    public function normalize(string $text): string
    {
        return strtolower(trim($text));
    }
}

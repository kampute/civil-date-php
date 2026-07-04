<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support;

use InvalidArgumentException;

/**
 * Validates and inspects locale language tags.
 */
final class LanguageTag
{
    /**
     * Locale language tag pattern.
     *
     * @var string
     */
    private const PATTERN = '/^[a-z]{2,8}(?:-(?:[A-Z]{2}|[0-9]{3}))?$/';

    /**
     * Disallows construction.
     */
    private function __construct()
    {
    }

    /**
     * Validates a locale language tag.
     *
     * @param string $languageTag Locale language tag.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the language tag is invalid.
     */
    public static function assertValid(string $languageTag): void
    {
        if (preg_match(self::PATTERN, $languageTag) !== 1) {
            throw new InvalidArgumentException(
                "Invalid locale language tag \"{$languageTag}\": expected language or language-REGION."
            );
        }
    }

    /**
     * Returns the language subtag.
     *
     * @param string $languageTag Locale language tag.
     *
     * @return string Language subtag.
     */
    public static function language(string $languageTag): string
    {
        return explode('-', $languageTag, 2)[0];
    }

    /**
     * Returns the region subtag.
     *
     * @param string $languageTag Locale language tag.
     *
     * @return string|null Region subtag, or null when the tag has no region.
     */
    public static function region(string $languageTag): ?string
    {
        return str_contains($languageTag, '-')
            ? explode('-', $languageTag, 2)[1]
            : null;
    }
}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

use InvalidArgumentException;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Locales\PersianAfghanistan;
use Kampute\CivilDate\Locales\PersianIran;
use Kampute\CivilDate\Support\LanguageTag;

/**
 * Stores locale definitions used by parsing and formatting APIs.
 *
 * Built-in locales are registered on first use. Applications may register
 * custom locales and may change the default locale used when no locale option
 * is supplied.
 */
final class LocaleRegistry
{
    /**
     * Current default locale language tag.
     *
     * @var string
     */
    private static string $defaultLanguageTag = Persian::LANGUAGE_TAG;

    /**
     * Registered locales indexed by language tag.
     *
     * @var array<string,Locale>
     */
    private static array $locales = [];

    /**
     * Disallows construction.
     */
    private function __construct()
    {
    }

    /**
     * Registers or replaces a locale definition.
     *
     * @param Locale $locale Locale definition.
     *
     * @return void
     */
    public static function register(Locale $locale): void
    {
        self::initializeBuiltInLocales();

        self::$locales[$locale->languageTag()] = $locale;
    }

    /**
     * Returns a registered locale.
     *
     * When fallback is enabled, a missing regional tag such as `fa-IR` may
     * resolve to its language-only locale, such as `fa`.
     *
     * @param string $languageTag Locale language tag.
     * @param bool $fallbackToLanguage Whether a missing language-region tag may resolve to its registered language-only locale.
     *
     * @return Locale|null Locale definition or null if not found.
     */
    public static function find(string $languageTag, bool $fallbackToLanguage = true): ?Locale
    {
        self::initializeBuiltInLocales();

        if (isset(self::$locales[$languageTag])) {
            return self::$locales[$languageTag];
        }

        if ($fallbackToLanguage) {
            return LanguageTag::region($languageTag) !== null
                ? self::$locales[LanguageTag::language($languageTag)] ?? null
                : null;
        }

        return null;
    }

    /**
     * Returns the default locale definition.
     *
     * @return Locale Default locale definition.
     */
    public static function default(): Locale
    {
        self::initializeBuiltInLocales();

        return self::$locales[self::$defaultLanguageTag];
    }

    /**
     * Sets the default locale.
     *
     * Passing a Locale instance registers it before making it the default.
     *
     * @param Locale|string $locale Locale language tag or definition.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the locale language tag is not registered.
     */
    public static function setDefault(Locale|string $locale): void
    {
        self::initializeBuiltInLocales();

        if ($locale instanceof Locale) {
            $resolvedLocale = $locale;
            self::register($resolvedLocale);
        } else {
            $resolvedLocale = self::find($locale, false)
                ?? throw new InvalidArgumentException("Locale language tag '{$locale}' is not registered.");
        }

        self::$defaultLanguageTag = $resolvedLocale->languageTag();
    }

    /**
     * Returns all registered locale definitions.
     *
     * @return array<string,Locale> Registered locales indexed by language tag.
     */
    public static function all(): array
    {
        self::initializeBuiltInLocales();

        return self::$locales;
    }

    /**
     * Registers built-in locales if the registry has not been initialized.
     *
     * @return void
     */
    private static function initializeBuiltInLocales(): void
    {
        if (self::$locales) {
            return;
        }

        self::$locales = [
            Persian::LANGUAGE_TAG => new Persian(),
            PersianIran::LANGUAGE_TAG => new PersianIran(),
            PersianAfghanistan::LANGUAGE_TAG => new PersianAfghanistan(),
            English::LANGUAGE_TAG => new English(),
        ];
    }
}

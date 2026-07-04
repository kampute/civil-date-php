<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;
use Kampute\CivilDate\Support\LanguageTag;

/**
 * Represents a regional locale and its localization services.
 *
 * A locale controls localized era names, month names, weekday names,
 * seasons, number words, numeral parsing, text direction, and the first day of
 * the week.
 *
 * @see LocaleBuilder
 * @see LocaleRegistry
 */
abstract class Locale
{
    /**
     * Locale language tag containing a language and optional region.
     *
     * @var string
     */
    protected readonly string $languageTag;

    /**
     * Creates a locale definition.
     *
     * @param string $languageTag Locale language tag containing a language and optional region.
     *
     * @throws InvalidArgumentException If the language tag is invalid.
     */
    protected function __construct(string $languageTag)
    {
        LanguageTag::assertValid($languageTag);

        $this->languageTag = $languageTag;
    }

    /**
     * Returns the locale language tag.
     *
     * @return string Locale language tag.
     */
    public function languageTag(): string
    {
        return $this->languageTag;
    }

    /**
     * Creates a locale builder for another language tag using this locale's behavior.
     *
     * The custom locale inherits all behavior from this locale unless the
     * builder overrides a specific value.
     *
     * @param string $languageTag Language tag for the customized locale.
     *
     * @return LocaleBuilder Locale builder initialized from this locale.
     *
     * @see LocaleBuilder::build()
     * @see CustomLocale
     */
    public function customizeFor(string $languageTag): LocaleBuilder
    {
        return new LocaleBuilder($languageTag, $this);
    }

    /**
     * Returns application-specific localized text for a custom key.
     *
     * @param string $key Custom localization key.
     *
     * @return string Localized text, or the key when no localization is defined.
     */
    public function localize(string $key): string
    {
        return $key;
    }

    /**
     * Returns whether formatted output uses right-to-left text direction.
     *
     * @return bool True for right-to-left output; false for left-to-right output.
     */
    abstract public function isRightToLeft(): bool;

    /**
     * Returns the text normalizer used by this locale.
     *
     * @return TextNormalizer Locale text normalizer.
     */
    abstract public function textNormalizer(): TextNormalizer;

    /**
     * Returns the number localizer used by this locale.
     *
     * @return NumberLocalizer Locale number localizer.
     */
    abstract public function numberLocalizer(): NumberLocalizer;

    /**
     * Formats an era display name.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Localized era name.
     *
     * @see Locale::eraFromName()
     */
    abstract public function eraName(Calendar $calendar): string;

    /**
     * Parses a localized era display name.
     *
     * @param string $name Localized era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see Locale::eraName()
     */
    abstract public function eraFromName(string $name): ?Calendar;

    /**
     * Formats an abbreviated era display name.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Localized abbreviated era name, or the full era name when abbreviations are unsupported.
     *
     * @see Locale::abbreviatedEraFromName()
     * @see Locale::eraName()
     */
    public function abbreviatedEraName(Calendar $calendar): string
    {
        return $this->eraName($calendar);
    }

    /**
     * Parses a localized abbreviated era display name.
     *
     * @param string $name Localized abbreviated era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see Locale::abbreviatedEraName()
     * @see Locale::eraFromName()
     */
    public function abbreviatedEraFromName(string $name): ?Calendar
    {
        return $this->eraFromName($name);
    }

    /**
     * Formats a month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Localized month name.
     *
     * @see Locale::monthFromName()
     * @see Locale::abbreviatedMonthName()
     */
    abstract public function monthName(Calendar $calendar, int $month): string;

    /**
     * Parses a localized month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see Locale::monthName()
     * @see Locale::abbreviatedMonthFromName()
     */
    abstract public function monthFromName(Calendar $calendar, string $name): ?int;

    /**
     * Formats an abbreviated month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Localized abbreviated month name, or the full month name when abbreviations are unsupported.
     *
     * @see Locale::abbreviatedMonthFromName()
     * @see Locale::monthName()
     */
    public function abbreviatedMonthName(Calendar $calendar, int $month): string
    {
        return $this->monthName($calendar, $month);
    }

    /**
     * Parses a localized abbreviated month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized abbreviated month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see Locale::abbreviatedMonthName()
     * @see Locale::monthFromName()
     */
    public function abbreviatedMonthFromName(Calendar $calendar, string $name): ?int
    {
        return $this->monthFromName($calendar, $name);
    }

    /**
     * Formats a day-of-week name.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string Localized day-of-week name.
     *
     * @see Locale::dayOfWeekFromName()
     * @see Locale::abbreviatedDayOfWeekName()
     */
    abstract public function dayOfWeekName(DayOfWeek $dayOfWeek): string;

    /**
     * Parses a localized day-of-week name.
     *
     * @param string $name Localized day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see Locale::dayOfWeekName()
     * @see Locale::abbreviatedDayOfWeekFromName()
     */
    abstract public function dayOfWeekFromName(string $name): ?DayOfWeek;

    /**
     * Formats an abbreviated day-of-week name.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string Localized abbreviated day-of-week name, or the full name when abbreviations are unsupported.
     *
     * @see Locale::abbreviatedDayOfWeekFromName()
     * @see Locale::dayOfWeekName()
     */
    public function abbreviatedDayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return $this->dayOfWeekName($dayOfWeek);
    }

    /**
     * Parses a localized abbreviated day-of-week name.
     *
     * @param string $name Localized abbreviated day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see Locale::abbreviatedDayOfWeekName()
     * @see Locale::dayOfWeekFromName()
     */
    public function abbreviatedDayOfWeekFromName(string $name): ?DayOfWeek
    {
        return $this->dayOfWeekFromName($name);
    }

    /**
     * Formats a season name.
     *
     * @param Season $season Season.
     *
     * @return string Localized season name.
     *
     * @see Locale::seasonFromName()
     */
    abstract public function seasonName(Season $season): string;

    /**
     * Parses a localized season name.
     *
     * @param string $name Localized season name.
     *
     * @return Season|null Matching season, or null when unrecognized.
     *
     * @see Locale::seasonName()
     */
    abstract public function seasonFromName(string $name): ?Season;
}

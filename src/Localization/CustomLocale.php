<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;

/**
 * Locale customized from another locale definition.
 *
 * @see Locale::customizeFor()
 * @see LocaleBuilder::build()
 */
final class CustomLocale extends Locale
{
    /**
     * Base locale whose behavior is extended.
     *
     * @var Locale
     */
    private readonly Locale $locale;

    /**
     * Whether formatted output uses right-to-left direction.
     *
     * @var bool
     */
    private readonly bool $rightToLeft;

    /**
     * Text normalizer used by this locale.
     *
     * @var TextNormalizer
     */
    private readonly TextNormalizer $textNormalizer;

    /**
     * Number localizer used by this locale.
     *
     * @var NumberLocalizer
     */
    private readonly NumberLocalizer $numberLocalizer;

    /**
     * Localized era names.
     *
     * @var array<int,string>
     */
    private readonly array $eraNames;

    /**
     * Localized abbreviated era names.
     *
     * @var array<int,string>
     */
    private readonly array $abbreviatedEraNames;

    /**
     * Localized month names.
     *
     * @var array<int,array<int,string>>
     */
    private readonly array $monthNames;

    /**
     * Localized abbreviated month names.
     *
     * @var array<int,array<int,string>>
     */
    private readonly array $abbreviatedMonthNames;

    /**
     * Localized weekday names.
     *
     * @var array<int,string>
     */
    private readonly array $dayOfWeekNames;

    /**
     * Localized abbreviated weekday names.
     *
     * @var array<int,string>
     */
    private readonly array $abbreviatedDayOfWeekNames;

    /**
     * Localized season names.
     *
     * @var array<int,string>
     */
    private readonly array $seasonNames;

    /**
     * Application-specific localized text.
     *
     * @var array<string,string>
     */
    private readonly array $localizations;

    /**
     * Creates a custom locale that delegates to another locale.
     *
     * @param string $languageTag Language tag for the new locale.
     * @param Locale $locale Base locale whose behavior is delegated.
     * @param array<int,string> $eraNames Localized era name overrides.
     * @param array<int,string> $abbreviatedEraNames Localized abbreviated era name overrides.
     * @param array<int,array<int,string>> $monthNames Localized month name overrides.
     * @param array<int,array<int,string>> $abbreviatedMonthNames Localized abbreviated month name overrides.
     * @param array<int,string> $dayOfWeekNames Localized weekday name overrides.
     * @param array<int,string> $abbreviatedDayOfWeekNames Localized abbreviated weekday name overrides.
     * @param array<int,string> $seasonNames Localized season name overrides.
     * @param array<string,string> $localizations Application-specific localized text overrides.
     * @param bool|null $rightToLeft Whether output uses right-to-left direction, or null to use the base locale.
     * @param NumberLocalizer|null $numberLocalizer Number localizer to use, or null to use the base locale.
     * @param TextNormalizer|null $textNormalizer Text normalizer to use, or null to use the base locale.
     *
     * @see LocaleBuilder::build()
     */
    public function __construct(
        string $languageTag,
        Locale $locale,
        array $eraNames = [],
        array $abbreviatedEraNames = [],
        array $monthNames = [],
        array $abbreviatedMonthNames = [],
        array $dayOfWeekNames = [],
        array $abbreviatedDayOfWeekNames = [],
        array $seasonNames = [],
        array $localizations = [],
        ?bool $rightToLeft = null,
        ?NumberLocalizer $numberLocalizer = null,
        ?TextNormalizer $textNormalizer = null,
    ) {
        parent::__construct($languageTag);

        $this->locale = $locale;
        $this->eraNames = $eraNames;
        $this->abbreviatedEraNames = $abbreviatedEraNames;
        $this->monthNames = $monthNames;
        $this->abbreviatedMonthNames = $abbreviatedMonthNames;
        $this->dayOfWeekNames = $dayOfWeekNames;
        $this->abbreviatedDayOfWeekNames = $abbreviatedDayOfWeekNames;
        $this->seasonNames = $seasonNames;
        $this->localizations = $localizations;
        $this->rightToLeft = $rightToLeft ?? $locale->isRightToLeft();
        $this->textNormalizer = $textNormalizer ?? $locale->textNormalizer();
        $this->numberLocalizer = $numberLocalizer ?? $locale->numberLocalizer();
    }

    /**
     * Returns whether formatted output uses right-to-left direction.
     *
     * @return bool True for right-to-left output; false for left-to-right output.
     */
    public function isRightToLeft(): bool
    {
        return $this->rightToLeft;
    }

    /**
     * Returns the text normalizer used by this locale.
     *
     * @return TextNormalizer Locale text normalizer.
     */
    public function textNormalizer(): TextNormalizer
    {
        return $this->textNormalizer;
    }

    /**
     * Returns the number localizer used by this locale.
     *
     * @return NumberLocalizer Locale number localizer.
     */
    public function numberLocalizer(): NumberLocalizer
    {
        return $this->numberLocalizer;
    }

    /**
     * Returns application-specific localized text for a custom key.
     *
     * @param string $key Custom localization key.
     *
     * @return string Localized text, or base locale result when no override is defined.
     */
    public function localize(string $key): string
    {
        return $this->localizations[$key]
            ?? $this->locale->localize($key);
    }

    /**
     * Formats an era display name.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Localized era name.
     *
     * @see CustomLocale::eraFromName()
     */
    public function eraName(Calendar $calendar): string
    {
        return $this->eraNames[$calendar->value]
            ?? $this->locale->eraName($calendar);
    }

    /**
     * Parses a localized era display name.
     *
     * @param string $name Localized era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see CustomLocale::eraName()
     */
    public function eraFromName(string $name): ?Calendar
    {
        $value = $this->lookupValue($this->eraNames, $name);
        return $value !== null
            ? Calendar::from($value)
            : $this->locale->eraFromName($name);
    }

    /**
     * Formats an abbreviated era display name.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Localized abbreviated era name.
     *
     * @see CustomLocale::abbreviatedEraFromName()
     */
    public function abbreviatedEraName(Calendar $calendar): string
    {
        return $this->abbreviatedEraNames[$calendar->value]
            ?? $this->locale->abbreviatedEraName($calendar);
    }

    /**
     * Parses a localized abbreviated era display name.
     *
     * @param string $name Localized abbreviated era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see CustomLocale::abbreviatedEraName()
     */
    public function abbreviatedEraFromName(string $name): ?Calendar
    {
        $value = $this->lookupValue($this->abbreviatedEraNames, $name);
        return $value !== null
            ? Calendar::from($value)
            : $this->locale->abbreviatedEraFromName($name);
    }

    /**
     * Formats a month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Localized month name.
     *
     * @see CustomLocale::monthFromName()
     */
    public function monthName(Calendar $calendar, int $month): string
    {
        return $this->monthNames[$calendar->value][$month]
            ?? $this->locale->monthName($calendar, $month);
    }

    /**
     * Parses a localized month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see CustomLocale::monthName()
     */
    public function monthFromName(Calendar $calendar, string $name): ?int
    {
        return $this->lookupValue($this->monthNames[$calendar->value] ?? [], $name)
            ?? $this->locale->monthFromName($calendar, $name);
    }

    /**
     * Formats an abbreviated month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Localized abbreviated month name.
     *
     * @see CustomLocale::abbreviatedMonthFromName()
     */
    public function abbreviatedMonthName(Calendar $calendar, int $month): string
    {
        return $this->abbreviatedMonthNames[$calendar->value][$month]
            ?? $this->locale->abbreviatedMonthName($calendar, $month);
    }

    /**
     * Parses a localized abbreviated month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized abbreviated month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see CustomLocale::abbreviatedMonthName()
     */
    public function abbreviatedMonthFromName(Calendar $calendar, string $name): ?int
    {
        return $this->lookupValue($this->abbreviatedMonthNames[$calendar->value] ?? [], $name)
            ?? $this->locale->abbreviatedMonthFromName($calendar, $name);
    }

    /**
     * Formats a day-of-week name.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string Localized day-of-week name.
     *
     * @see CustomLocale::dayOfWeekFromName()
     */
    public function dayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return $this->dayOfWeekNames[$dayOfWeek->value]
            ?? $this->locale->dayOfWeekName($dayOfWeek);
    }

    /**
     * Parses a localized day-of-week name.
     *
     * @param string $name Localized day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see CustomLocale::dayOfWeekName()
     */
    public function dayOfWeekFromName(string $name): ?DayOfWeek
    {
        $value = $this->lookupValue($this->dayOfWeekNames, $name);
        return $value !== null
            ? DayOfWeek::from($value)
            : $this->locale->dayOfWeekFromName($name);
    }

    /**
     * Formats an abbreviated day-of-week name.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string Localized abbreviated day-of-week name.
     *
     * @see CustomLocale::abbreviatedDayOfWeekFromName()
     */
    public function abbreviatedDayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return $this->abbreviatedDayOfWeekNames[$dayOfWeek->value]
            ?? $this->locale->abbreviatedDayOfWeekName($dayOfWeek);
    }

    /**
     * Parses a localized abbreviated day-of-week name.
     *
     * @param string $name Localized abbreviated day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see CustomLocale::abbreviatedDayOfWeekName()
     */
    public function abbreviatedDayOfWeekFromName(string $name): ?DayOfWeek
    {
        $value = $this->lookupValue($this->abbreviatedDayOfWeekNames, $name);
        return $value !== null
            ? DayOfWeek::from($value)
            : $this->locale->abbreviatedDayOfWeekFromName($name);
    }

    /**
     * Formats a season name.
     *
     * @param Season $season Season.
     *
     * @return string Localized season name.
     *
     * @see CustomLocale::seasonFromName()
     */
    public function seasonName(Season $season): string
    {
        return $this->seasonNames[$season->value]
            ?? $this->locale->seasonName($season);
    }

    /**
     * Parses a localized season name.
     *
     * @param string $name Localized season name.
     *
     * @return Season|null Matching season, or null when unrecognized.
     *
     * @see CustomLocale::seasonName()
     */
    public function seasonFromName(string $name): ?Season
    {
        $value = $this->lookupValue($this->seasonNames, $name);
        return $value !== null
            ? Season::from($value)
            : $this->locale->seasonFromName($name);
    }

    /**
     * Finds a normalized localized name in the configured overrides.
     *
     * @param array<int,string> $names Localized names indexed by entity value.
     * @param string $name Name to find.
     *
     * @return int|null Matching entity value, or null when unrecognized.
     */
    private function lookupValue(array $names, string $name): ?int
    {
        $needle = $this->textNormalizer->normalize($name);
        foreach ($names as $value => $localizedName) {
            if ($this->textNormalizer->normalize($localizedName) === $needle) {
                return $value;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;
use Kampute\CivilDate\Support\LanguageTag;

/**
 * Builds a custom locale from an existing locale definition.
 *
 * Use this class to override a small set of localized names or services while
 * keeping the rest of the base locale.
 *
 * Example:
 *
 * ```php
 * $locale = (new \Kampute\CivilDate\Locales\Persian())
 *     ->customizeFor('fa-IR')
 *     ->setMonthName(\Kampute\CivilDate\Calendar::Jalali, 5, 'امرداد')
 *     ->build();
 * ```
 *
 * @see Locale::customizeFor()
 * @see CustomLocale
 */
final class LocaleBuilder
{
    /**
     * Language tag for the custom locale.
     *
     * @var string
     */
    private readonly string $languageTag;

    /**
     * Base locale whose behavior is extended.
     *
     * @var Locale
     */
    private readonly Locale $locale;

    /**
     * Whether formatted output uses right-to-left direction.
     *
     * @var bool|null
     */
    private ?bool $rightToLeft = null;

    /**
     * Text normalizer used by the custom locale.
     *
     * @var TextNormalizer|null
     */
    private ?TextNormalizer $textNormalizer = null;

    /**
     * Number localizer used by the custom locale.
     *
     * @var NumberLocalizer|null
     */
    private ?NumberLocalizer $numberLocalizer = null;

    /**
     * Localized era names.
     *
     * @var array<int,string>
     */
    private array $eraNames = [];

    /**
     * Localized abbreviated era names.
     *
     * @var array<int,string>
     */
    private array $abbreviatedEraNames = [];

    /**
     * Localized month names.
     *
     * @var array<int,array<int,string>>
     */
    private array $monthNames = [];

    /**
     * Localized abbreviated month names.
     *
     * @var array<int,array<int,string>>
     */
    private array $abbreviatedMonthNames = [];

    /**
     * Localized weekday names.
     *
     * @var array<int,string>
     */
    private array $dayOfWeekNames = [];

    /**
     * Localized abbreviated weekday names.
     *
     * @var array<int,string>
     */
    private array $abbreviatedDayOfWeekNames = [];

    /**
     * Localized season names.
     *
     * @var array<int,string>
     */
    private array $seasonNames = [];

    /**
     * Application-specific localized text.
     *
     * @var array<string,string>
     */
    private array $localizations = [];

    /**
     * Creates a locale builder that delegates to another locale.
     *
     * @param string $languageTag Language tag for the custom locale.
     * @param Locale $locale Base locale whose behavior is delegated.
     *
     * @throws InvalidArgumentException If the language tag is invalid.
     *
     * @see Locale::customizeFor()
     */
    public function __construct(string $languageTag, Locale $locale)
    {
        LanguageTag::assertValid($languageTag);

        $this->languageTag = $languageTag;
        $this->locale = $locale;
    }

    /**
     * Builds the custom locale.
     *
     * @return CustomLocale Custom locale.
     *
     * @see CustomLocale
     */
    public function build(): CustomLocale
    {
        return new CustomLocale(
            $this->languageTag,
            $this->locale,
            $this->eraNames,
            $this->abbreviatedEraNames,
            $this->monthNames,
            $this->abbreviatedMonthNames,
            $this->dayOfWeekNames,
            $this->abbreviatedDayOfWeekNames,
            $this->seasonNames,
            $this->localizations,
            $this->rightToLeft,
            $this->numberLocalizer,
            $this->textNormalizer,
        );
    }

    /**
     * Sets text direction.
     *
     * @param bool $rightToLeft Whether output uses right-to-left direction.
     *
     * @return self This builder.
     *
     * @see Locale::isRightToLeft()
     */
    public function setRightToLeft(bool $rightToLeft): self
    {
        $this->rightToLeft = $rightToLeft;
        return $this;
    }

    /**
     * Replaces the text normalizer.
     *
     * @param TextNormalizer $textNormalizer Text normalizer to use.
     *
     * @return self This builder.
     *
     * @see Locale::textNormalizer()
     */
    public function setTextNormalizer(TextNormalizer $textNormalizer): self
    {
        $this->textNormalizer = $textNormalizer;
        return $this;
    }

    /**
     * Replaces the number localizer.
     *
     * @param NumberLocalizer $numberLocalizer Number localizer to use.
     *
     * @return self This builder.
     *
     * @see Locale::numberLocalizer()
     */
    public function setNumberLocalizer(NumberLocalizer $numberLocalizer): self
    {
        $this->numberLocalizer = $numberLocalizer;
        return $this;
    }

    /**
     * Sets application-specific localized text overrides.
     *
     * Existing overrides for omitted keys remain unchanged.
     *
     * @param array<string,string> $localizations Localized text indexed by custom key.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a key or localized text is empty.
     *
     * @see LocaleBuilder::setLocalization()
     * @see Locale::localize()
     */
    public function setLocalizations(array $localizations): self
    {
        foreach ($localizations as $key => $localization) {
            self::assertLocalization($key, $localization);
        }

        $this->localizations = array_replace($this->localizations, $localizations);
        return $this;
    }

    /**
     * Sets one application-specific localized text override.
     *
     * @param string $key Custom localization key.
     * @param string $localization Localized text.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the key or localized text is empty.
     *
     * @see LocaleBuilder::setLocalizations()
     * @see Locale::localize()
     */
    public function setLocalization(string $key, string $localization): self
    {
        self::assertLocalization($key, $localization);
        $this->localizations[$key] = $localization;
        return $this;
    }

    /**
     * Sets localized era name overrides.
     *
     * Existing overrides for omitted calendars remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,string> $eraNames Localized era names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setEraName()
     * @see Locale::eraName()
     */
    public function setEraNames(array $eraNames): self
    {
        foreach ($eraNames as $name) {
            self::assertName($name);
        }

        $this->eraNames = array_replace($this->eraNames, $eraNames);
        return $this;
    }

    /**
     * Sets one localized era name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setEraNames()
     * @see Locale::eraName()
     */
    public function setEraName(Calendar $calendar, string $name): self
    {
        self::assertName($name);
        $this->eraNames[$calendar->value] = $name;
        return $this;
    }

    /**
     * Sets localized abbreviated era name overrides.
     *
     * Existing overrides for omitted calendars remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,string> $eraNames Localized abbreviated era names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedEraName()
     * @see Locale::abbreviatedEraName()
     */
    public function setAbbreviatedEraNames(array $eraNames): self
    {
        foreach ($eraNames as $name) {
            self::assertName($name);
        }

        $this->abbreviatedEraNames = array_replace($this->abbreviatedEraNames, $eraNames);
        return $this;
    }

    /**
     * Sets one localized abbreviated era name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Localized abbreviated name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedEraNames()
     * @see Locale::abbreviatedEraName()
     */
    public function setAbbreviatedEraName(Calendar $calendar, string $name): self
    {
        self::assertName($name);
        $this->abbreviatedEraNames[$calendar->value] = $name;
        return $this;
    }

    /**
     * Sets localized month name overrides.
     *
     * Existing overrides for omitted months remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,array<int,string>> $monthNames Localized month names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setMonthName()
     * @see Locale::monthName()
     */
    public function setMonthNames(array $monthNames): self
    {
        foreach ($monthNames as $names) {
            foreach ($names as $name) {
                self::assertName($name);
            }
        }

        $this->monthNames = array_replace_recursive($this->monthNames, $monthNames);
        return $this;
    }

    /**
     * Sets one localized month name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     * @param string $name Localized name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setMonthNames()
     * @see Locale::monthName()
     */
    public function setMonthName(Calendar $calendar, int $month, string $name): self
    {
        self::assertName($name);
        $this->monthNames[$calendar->value][$month] = $name;
        return $this;
    }

    /**
     * Sets localized abbreviated month name overrides.
     *
     * Existing overrides for omitted months remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,array<int,string>> $monthNames Localized abbreviated month names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedMonthName()
     * @see Locale::abbreviatedMonthName()
     */
    public function setAbbreviatedMonthNames(array $monthNames): self
    {
        foreach ($monthNames as $names) {
            foreach ($names as $name) {
                self::assertName($name);
            }
        }

        $this->abbreviatedMonthNames = array_replace_recursive($this->abbreviatedMonthNames, $monthNames);
        return $this;
    }

    /**
     * Sets one localized abbreviated month name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     * @param string $name Localized abbreviated name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedMonthNames()
     * @see Locale::abbreviatedMonthName()
     */
    public function setAbbreviatedMonthName(Calendar $calendar, int $month, string $name): self
    {
        self::assertName($name);
        $this->abbreviatedMonthNames[$calendar->value][$month] = $name;
        return $this;
    }

    /**
     * Sets localized weekday name overrides.
     *
     * Existing overrides for omitted weekdays remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,string> $dayOfWeekNames Localized weekday names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setDayOfWeekName()
     * @see Locale::dayOfWeekName()
     */
    public function setDayOfWeekNames(array $dayOfWeekNames): self
    {
        foreach ($dayOfWeekNames as $name) {
            self::assertName($name);
        }

        $this->dayOfWeekNames = array_replace($this->dayOfWeekNames, $dayOfWeekNames);
        return $this;
    }

    /**
     * Sets one localized weekday name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param DayOfWeek $dayOfWeek Weekday.
     * @param string $name Localized name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setDayOfWeekNames()
     * @see Locale::dayOfWeekName()
     */
    public function setDayOfWeekName(DayOfWeek $dayOfWeek, string $name): self
    {
        self::assertName($name);
        $this->dayOfWeekNames[$dayOfWeek->value] = $name;
        return $this;
    }

    /**
     * Sets localized abbreviated weekday name overrides.
     *
     * Existing overrides for omitted weekdays remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,string> $dayOfWeekNames Localized abbreviated weekday names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedDayOfWeekName()
     * @see Locale::abbreviatedDayOfWeekName()
     */
    public function setAbbreviatedDayOfWeekNames(array $dayOfWeekNames): self
    {
        foreach ($dayOfWeekNames as $name) {
            self::assertName($name);
        }

        $this->abbreviatedDayOfWeekNames = array_replace($this->abbreviatedDayOfWeekNames, $dayOfWeekNames);
        return $this;
    }

    /**
     * Sets one localized abbreviated weekday name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param DayOfWeek $dayOfWeek Weekday.
     * @param string $name Localized abbreviated name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setAbbreviatedDayOfWeekNames()
     * @see Locale::abbreviatedDayOfWeekName()
     */
    public function setAbbreviatedDayOfWeekName(DayOfWeek $dayOfWeek, string $name): self
    {
        self::assertName($name);
        $this->abbreviatedDayOfWeekNames[$dayOfWeek->value] = $name;
        return $this;
    }

    /**
     * Sets localized season name overrides.
     *
     * Existing overrides for omitted seasons remain unchanged. Overrides take precedence during parsing;
     * otherwise, parsing falls back to the base locale, including its accepted aliases.
     *
     * @param array<int,string> $seasonNames Localized season names to override.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If a name is empty.
     *
     * @see LocaleBuilder::setSeasonName()
     * @see Locale::seasonName()
     */
    public function setSeasonNames(array $seasonNames): self
    {
        foreach ($seasonNames as $name) {
            self::assertName($name);
        }

        $this->seasonNames = array_replace($this->seasonNames, $seasonNames);
        return $this;
    }

    /**
     * Sets one localized season name.
     *
     * The override takes precedence during parsing; otherwise, parsing falls back to the base locale,
     * including its accepted aliases.
     *
     * @param Season $season Season.
     * @param string $name Localized name.
     *
     * @return self This builder.
     *
     * @throws InvalidArgumentException If the name is empty.
     *
     * @see LocaleBuilder::setSeasonNames()
     * @see Locale::seasonName()
     */
    public function setSeasonName(Season $season, string $name): self
    {
        self::assertName($name);
        $this->seasonNames[$season->value] = $name;
        return $this;
    }

    /**
     * Validates a localized name.
     *
     * @param string $name Localized name.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the name is empty.
     */
    private static function assertName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Localized name must not be empty.');
        }
    }

    /**
     * Validates application-specific localized text.
     *
     * @param string $key Custom localization key.
     * @param string $localization Localized text.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the key or localized text is empty.
     */
    private static function assertLocalization(string $key, string $localization): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Localization key must not be empty.');
        }

        if ($localization === '') {
            throw new InvalidArgumentException('Localization must not be empty.');
        }
    }
}

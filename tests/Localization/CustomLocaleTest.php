<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Localization;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Locales\PersianNumerals;
use Kampute\CivilDate\Locales\PersianTextNormalizer;
use Kampute\CivilDate\Localization\CustomLocale;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\TestCase;

/**
 * Tests custom locale behavior.
 */
final class CustomLocaleTest extends TestCase
{
    /**
     * Tests constructor defaults to base locale behavior.
     */
    public function testConstructorDefaultsToBaseLocaleBehavior(): void
    {
        $base = new Persian();
        $locale = new CustomLocale('fa-IR', $base);

        self::assertSame('fa-IR', $locale->languageTag());
        self::assertSame($base->isRightToLeft(), $locale->isRightToLeft());
        self::assertSame($base->textNormalizer(), $locale->textNormalizer());
        self::assertSame($base->numberLocalizer(), $locale->numberLocalizer());
        self::assertSame($base->eraName(Calendar::Gregorian), $locale->eraName(Calendar::Gregorian));
        self::assertSame($base->abbreviatedEraName(Calendar::Gregorian), $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame($base->monthName(Calendar::Gregorian, 1), $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame($base->abbreviatedMonthName(Calendar::Gregorian, 1), $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame($base->dayOfWeekName(DayOfWeek::Monday), $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->abbreviatedDayOfWeekName(DayOfWeek::Monday), $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->seasonName(Season::Winter), $locale->seasonName(Season::Winter));
        self::assertSame($base->localize('app.today'), $locale->localize('app.today'));
    }

    /**
     * Tests constructor applies custom behavior.
     */
    public function testConstructorAppliesCustomBehavior(): void
    {
        $normalizer = new PersianTextNormalizer();
        $numberLocalizer = new PersianNumerals();
        $locale = new CustomLocale(
            'en-US',
            new English(),
            eraNames: [Calendar::Gregorian->value => 'Civil'],
            abbreviatedEraNames: [Calendar::Gregorian->value => 'CE'],
            monthNames: [Calendar::Gregorian->value => [1 => 'First']],
            abbreviatedMonthNames: [Calendar::Gregorian->value => [1 => 'F']],
            dayOfWeekNames: [DayOfWeek::Monday->value => 'Moon-day'],
            abbreviatedDayOfWeekNames: [DayOfWeek::Monday->value => 'M'],
            seasonNames: [Season::Winter->value => 'Cold'],
            localizations: ['app.today' => 'Today'],
            rightToLeft: true,
            numberLocalizer: $numberLocalizer,
            textNormalizer: $normalizer,
        );

        self::assertTrue($locale->isRightToLeft());
        self::assertSame($normalizer, $locale->textNormalizer());
        self::assertSame($numberLocalizer, $locale->numberLocalizer());
        self::assertSame('Civil', $locale->eraName(Calendar::Gregorian));
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('Civil'));
        self::assertSame('CE', $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame(Calendar::Gregorian, $locale->abbreviatedEraFromName('CE'));
        self::assertSame('First', $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame(1, $locale->monthFromName(Calendar::Gregorian, 'First'));
        self::assertSame('F', $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame(1, $locale->abbreviatedMonthFromName(Calendar::Gregorian, 'F'));
        self::assertSame('Moon-day', $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame(DayOfWeek::Monday, $locale->dayOfWeekFromName('Moon-day'));
        self::assertSame('M', $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame(DayOfWeek::Monday, $locale->abbreviatedDayOfWeekFromName('M'));
        self::assertSame('Cold', $locale->seasonName(Season::Winter));
        self::assertSame(Season::Winter, $locale->seasonFromName('Cold'));
        self::assertSame('Today', $locale->localize('app.today'));
    }

    /**
     * Tests constructor falls back to base locale for missing custom names.
     */
    public function testConstructorFallsBackToBaseLocaleForMissingCustomNames(): void
    {
        $base = new English();
        $locale = new CustomLocale(
            'en-US',
            $base,
            eraNames: [Calendar::Gregorian->value => 'Civil'],
        );

        self::assertSame('Civil', $locale->eraName(Calendar::Gregorian));
        self::assertSame('Solar Hijri', $locale->eraName(Calendar::Jalali));
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('Common Era'));
        self::assertSame('CE', $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame(Calendar::Gregorian, $locale->abbreviatedEraFromName('CE'));
        self::assertSame('Farvardin', $locale->monthName(Calendar::Jalali, 1));
        self::assertSame(1, $locale->monthFromName(Calendar::Jalali, 'Farvardin'));
        self::assertSame('Sun', $locale->abbreviatedDayOfWeekName(DayOfWeek::Sunday));
        self::assertSame(Season::Autumn, $locale->seasonFromName('Fall'));
    }

    /**
     * Tests constructor falls back to base locale for missing custom localizations.
     */
    public function testConstructorFallsBackToBaseLocaleForMissingCustomLocalizations(): void
    {
        $base = new class () extends English {
            /**
             * Returns application-specific localized text for a custom key.
             *
             * @param string $key Custom localization key.
             *
             * @return string Localized text, or the key when no localization is defined.
             */
            public function localize(string $key): string
            {
                return match ($key) {
                    'app.tomorrow' => 'Tomorrow',
                    default => parent::localize($key),
                };
            }
        };
        $locale = new CustomLocale(
            'en-US',
            $base,
            localizations: ['app.today' => 'Today'],
        );

        self::assertSame('Today', $locale->localize('app.today'));
        self::assertSame('Tomorrow', $locale->localize('app.tomorrow'));
        self::assertSame('app.yesterday', $locale->localize('app.yesterday'));
    }

    /**
     * Tests constructor rejects invalid language tag.
     */
    public function testConstructorRejectsInvalidLanguageTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomLocale('en-us', new English());
    }
}

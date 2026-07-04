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
use Kampute\CivilDate\Localization\LocaleBuilder;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\TestCase;

/**
 * Tests fluent custom locale building.
 */
final class LocaleBuilderTest extends TestCase
{
    /**
     * Tests customizing a locale for another language tag.
     */
    public function testLocaleCustomizesForLanguageTag(): void
    {
        $base = new English();
        $builder = $base->customizeFor('en-GB');
        $locale = $builder->build();

        self::assertNotSame($base, $locale);
        self::assertInstanceOf(CustomLocale::class, $locale);
        self::assertSame('en-GB', $locale->languageTag());
        self::assertSame($base->isRightToLeft(), $locale->isRightToLeft());
        self::assertSame($base->eraName(Calendar::Gregorian), $locale->eraName(Calendar::Gregorian));
        self::assertSame($base->abbreviatedEraName(Calendar::Gregorian), $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame($base->monthName(Calendar::Gregorian, 1), $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame($base->abbreviatedMonthName(Calendar::Gregorian, 1), $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame($base->dayOfWeekName(DayOfWeek::Monday), $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->abbreviatedDayOfWeekName(DayOfWeek::Monday), $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->seasonName(Season::Winter), $locale->seasonName(Season::Winter));
        self::assertSame($base->textNormalizer(), $locale->textNormalizer());
        self::assertSame($base->numberLocalizer(), $locale->numberLocalizer());
        self::assertSame($base->localize('app.today'), $locale->localize('app.today'));
    }

    /**
     * Tests constructor copies base locale.
     */
    public function testConstructorCopiesBaseLocale(): void
    {
        $base = new English();
        $locale = (new LocaleBuilder('en-GB', $base))->build();

        self::assertSame('en-GB', $locale->languageTag());
        self::assertSame($base->isRightToLeft(), $locale->isRightToLeft());
        self::assertSame($base->eraName(Calendar::Gregorian), $locale->eraName(Calendar::Gregorian));
        self::assertSame($base->abbreviatedEraName(Calendar::Gregorian), $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame($base->monthName(Calendar::Gregorian, 1), $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame($base->abbreviatedMonthName(Calendar::Gregorian, 1), $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame($base->dayOfWeekName(DayOfWeek::Monday), $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->abbreviatedDayOfWeekName(DayOfWeek::Monday), $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame($base->seasonName(Season::Winter), $locale->seasonName(Season::Winter));
        self::assertSame($base->textNormalizer(), $locale->textNormalizer());
        self::assertSame($base->numberLocalizer(), $locale->numberLocalizer());
        self::assertSame($base->localize('app.today'), $locale->localize('app.today'));
    }

    /**
     * Tests constructor preserves base locale behavior.
     */
    public function testConstructorPreservesBaseLocaleBehavior(): void
    {
        $locale = (new LocaleBuilder('fa-IR', new Persian()))->build();

        self::assertSame('−۱۲', $locale->numberLocalizer()->formatDigits(-12));
        self::assertSame(12, $locale->numberLocalizer()->parseDigits('١٢'));
        self::assertSame(1, $locale->numberLocalizer()->parseCardinal('یِک'));
    }

    /**
     * Tests constructor rejects invalid language tag.
     */
    public function testConstructorRejectsInvalidLanguageTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocaleBuilder('en-us', new English());
    }

    /**
     * Tests build returns a snapshot of the builder configuration.
     */
    public function testBuildReturnsSnapshot(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $locale1 = $builder
            ->setEraName(Calendar::Gregorian, 'Civil')
            ->build();
        $locale2 = $builder
            ->setEraName(Calendar::Gregorian, 'Western')
            ->build();

        self::assertSame('Civil', $locale1->eraName(Calendar::Gregorian));
        self::assertSame('Western', $locale2->eraName(Calendar::Gregorian));
    }

    /**
     * Tests build snapshots custom localizations.
     */
    public function testBuildSnapshotsCustomLocalizations(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $locale1 = $builder
            ->setLocalization('app.today', 'Today')
            ->build();
        $locale2 = $builder
            ->setLocalization('app.today', 'Current day')
            ->build();

        self::assertSame('Today', $locale1->localize('app.today'));
        self::assertSame('Current day', $locale2->localize('app.today'));
    }

    /**
     * Tests set right to left.
     */
    public function testSetRightToLeft(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setRightToLeft(true));
        self::assertTrue($builder->build()->isRightToLeft());
    }

    /**
     * Tests set text normalizer.
     */
    public function testSetTextNormalizer(): void
    {
        $builder = (new LocaleBuilder('en-US', new English()))
            ->setEraName(Calendar::Gregorian, 'فارسي');
        $normalizer = new PersianTextNormalizer();

        self::assertSame($builder, $builder->setTextNormalizer($normalizer));

        $locale = $builder->build();
        self::assertSame($normalizer, $locale->textNormalizer());
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('فارسی'));
    }

    /**
     * Tests set number localizer.
     */
    public function testSetNumberLocalizer(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $numberLocalizer = new PersianNumerals();

        self::assertSame($builder, $builder->setNumberLocalizer($numberLocalizer));

        $locale = $builder->build();
        self::assertSame($numberLocalizer, $locale->numberLocalizer());
        self::assertSame('یک', $locale->numberLocalizer()->formatCardinal(1));
    }

    /**
     * Tests set localizations.
     */
    public function testSetLocalizations(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setLocalization('app.tomorrow', 'Tomorrow');
        $localizations = [
            'app.today' => 'Today',
        ];

        self::assertSame($builder, $builder->setLocalizations($localizations));

        $locale = $builder->build();
        self::assertSame('Today', $locale->localize('app.today'));
        self::assertSame('Tomorrow', $locale->localize('app.tomorrow'));
        self::assertSame('app.yesterday', $locale->localize('app.yesterday'));
    }

    /**
     * Tests set localizations rejects an empty key.
     */
    public function testSetLocalizationsRejectsEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setLocalizations(['' => 'Today']);
    }

    /**
     * Tests set localizations rejects an empty localization.
     */
    public function testSetLocalizationsRejectsEmptyLocalization(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setLocalizations(['app.today' => '']);
    }

    /**
     * Tests set localization.
     */
    public function testSetLocalization(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setLocalization('app.today', 'Today'));

        $locale = $builder->build();
        self::assertSame('Today', $locale->localize('app.today'));
        self::assertSame('app.tomorrow', $locale->localize('app.tomorrow'));
    }

    /**
     * Tests set localization rejects empty key.
     */
    public function testSetLocalizationRejectsEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setLocalization('', 'Today');
    }

    /**
     * Tests set localization rejects empty localization.
     */
    public function testSetLocalizationRejectsEmptyLocalization(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setLocalization('app.today', '');
    }

    /**
     * Tests set era names.
     */
    public function testSetEraNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setEraName(Calendar::Gregorian, 'Civil');
        $names = [
            Calendar::Jalali->value => 'Solar',
        ];

        self::assertSame($builder, $builder->setEraNames($names));

        $locale = $builder->build();
        self::assertSame('Solar', $locale->eraName(Calendar::Jalali));
        self::assertSame('Civil', $locale->eraName(Calendar::Gregorian));
        self::assertSame('Hijri', $locale->eraName(Calendar::Islamic));
    }

    /**
     * Tests set era names rejects an empty name.
     */
    public function testSetEraNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setEraNames([Calendar::Jalali->value => '']);
    }

    /**
     * Tests set era name.
     */
    public function testSetEraName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setEraName(Calendar::Gregorian, 'Civil'));

        $locale = $builder->build();
        self::assertSame('Civil', $locale->eraName(Calendar::Gregorian));
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('Civil'));
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('Common Era'));
    }

    /**
     * Tests set era name rejects empty name.
     */
    public function testSetEraNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setEraName(Calendar::Gregorian, '');
    }

    /**
     * Tests set abbreviated era names.
     */
    public function testSetAbbreviatedEraNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setAbbreviatedEraName(Calendar::Gregorian, 'C');
        $names = [
            Calendar::Jalali->value => 'S',
        ];

        self::assertSame($builder, $builder->setAbbreviatedEraNames($names));

        $locale = $builder->build();
        self::assertSame('S', $locale->abbreviatedEraName(Calendar::Jalali));
        self::assertSame('C', $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame('AH', $locale->abbreviatedEraName(Calendar::Islamic));
    }

    /**
     * Tests set abbreviated era names rejects an empty name.
     */
    public function testSetAbbreviatedEraNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setAbbreviatedEraNames([Calendar::Jalali->value => '']);
    }

    /**
     * Tests set abbreviated era name.
     */
    public function testSetAbbreviatedEraName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setAbbreviatedEraName(Calendar::Gregorian, 'C'));

        $locale = $builder->build();
        self::assertSame('C', $locale->abbreviatedEraName(Calendar::Gregorian));
        self::assertSame(Calendar::Gregorian, $locale->abbreviatedEraFromName('C'));
        self::assertSame(Calendar::Gregorian, $locale->abbreviatedEraFromName('CE'));
        self::assertSame('Common Era', $locale->eraName(Calendar::Gregorian));
    }

    /**
     * Tests set abbreviated era name rejects empty name.
     */
    public function testSetAbbreviatedEraNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setAbbreviatedEraName(Calendar::Gregorian, '');
    }

    /**
     * Tests set month names.
     */
    public function testSetMonthNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setMonthName(Calendar::Gregorian, 2, 'Feb');
        $names = [Calendar::Gregorian->value => [1 => 'Jan']];

        self::assertSame($builder, $builder->setMonthNames($names));

        $locale = $builder->build();
        self::assertSame('Jan', $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame('Feb', $locale->monthName(Calendar::Gregorian, 2));
        self::assertSame('Farvardin', $locale->monthName(Calendar::Jalali, 1));
    }

    /**
     * Tests set month names rejects an empty name.
     */
    public function testSetMonthNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setMonthNames([Calendar::Gregorian->value => [1 => '']]);
    }

    /**
     * Tests set month name.
     */
    public function testSetMonthName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setMonthName(Calendar::Gregorian, 1, 'Jan'));

        $locale = $builder->build();
        self::assertSame('Jan', $locale->monthName(Calendar::Gregorian, 1));
        self::assertSame(1, $locale->monthFromName(Calendar::Gregorian, 'Jan'));
        self::assertSame(1, $locale->monthFromName(Calendar::Gregorian, 'January'));
    }

    /**
     * Tests set month name rejects empty name.
     */
    public function testSetMonthNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setMonthName(Calendar::Gregorian, 1, '');
    }

    /**
     * Tests set abbreviated month names.
     */
    public function testSetAbbreviatedMonthNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setAbbreviatedMonthName(Calendar::Gregorian, 2, 'F');
        $names = [Calendar::Gregorian->value => [1 => 'J']];

        self::assertSame($builder, $builder->setAbbreviatedMonthNames($names));

        $locale = $builder->build();
        self::assertSame('J', $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame('F', $locale->abbreviatedMonthName(Calendar::Gregorian, 2));
        self::assertSame('Far', $locale->abbreviatedMonthName(Calendar::Jalali, 1));
    }

    /**
     * Tests set abbreviated month names rejects an empty name.
     */
    public function testSetAbbreviatedMonthNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setAbbreviatedMonthNames([Calendar::Gregorian->value => [1 => '']]);
    }

    /**
     * Tests set abbreviated month name.
     */
    public function testSetAbbreviatedMonthName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setAbbreviatedMonthName(Calendar::Gregorian, 1, 'J'));

        $locale = $builder->build();
        self::assertSame('J', $locale->abbreviatedMonthName(Calendar::Gregorian, 1));
        self::assertSame(1, $locale->abbreviatedMonthFromName(Calendar::Gregorian, 'J'));
        self::assertSame(1, $locale->abbreviatedMonthFromName(Calendar::Gregorian, 'Jan'));
        self::assertSame('January', $locale->monthName(Calendar::Gregorian, 1));
    }

    /**
     * Tests set abbreviated month name rejects empty name.
     */
    public function testSetAbbreviatedMonthNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setAbbreviatedMonthName(Calendar::Gregorian, 1, '');
    }

    /**
     * Tests set day of week names.
     */
    public function testSetDayOfWeekNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setDayOfWeekName(DayOfWeek::Tuesday, 'Tue');
        $names = [DayOfWeek::Monday->value => 'Mon'];

        self::assertSame($builder, $builder->setDayOfWeekNames($names));

        $locale = $builder->build();
        self::assertSame('Mon', $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame('Tue', $locale->dayOfWeekName(DayOfWeek::Tuesday));
        self::assertSame('Wednesday', $locale->dayOfWeekName(DayOfWeek::Wednesday));
    }

    /**
     * Tests set day of week names rejects an empty name.
     */
    public function testSetDayOfWeekNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setDayOfWeekNames([DayOfWeek::Monday->value => '']);
    }

    /**
     * Tests set day of week name.
     */
    public function testSetDayOfWeekName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setDayOfWeekName(DayOfWeek::Monday, 'Mon'));

        $locale = $builder->build();
        self::assertSame('Mon', $locale->dayOfWeekName(DayOfWeek::Monday));
        self::assertSame(DayOfWeek::Monday, $locale->dayOfWeekFromName('Mon'));
        self::assertSame(DayOfWeek::Monday, $locale->dayOfWeekFromName('Monday'));
    }

    /**
     * Tests set day of week name rejects empty name.
     */
    public function testSetDayOfWeekNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setDayOfWeekName(DayOfWeek::Monday, '');
    }

    /**
     * Tests set abbreviated day of week names.
     */
    public function testSetAbbreviatedDayOfWeekNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setAbbreviatedDayOfWeekName(DayOfWeek::Tuesday, 'T');
        $names = [DayOfWeek::Monday->value => 'M'];

        self::assertSame($builder, $builder->setAbbreviatedDayOfWeekNames($names));

        $locale = $builder->build();
        self::assertSame('M', $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame('T', $locale->abbreviatedDayOfWeekName(DayOfWeek::Tuesday));
        self::assertSame('Wed', $locale->abbreviatedDayOfWeekName(DayOfWeek::Wednesday));
    }

    /**
     * Tests set abbreviated day of week names rejects an empty name.
     */
    public function testSetAbbreviatedDayOfWeekNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setAbbreviatedDayOfWeekNames([DayOfWeek::Monday->value => '']);
    }

    /**
     * Tests set abbreviated day of week name.
     */
    public function testSetAbbreviatedDayOfWeekName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setAbbreviatedDayOfWeekName(DayOfWeek::Monday, 'M'));

        $locale = $builder->build();
        self::assertSame('M', $locale->abbreviatedDayOfWeekName(DayOfWeek::Monday));
        self::assertSame(DayOfWeek::Monday, $locale->abbreviatedDayOfWeekFromName('M'));
        self::assertSame(DayOfWeek::Monday, $locale->abbreviatedDayOfWeekFromName('Mon'));
        self::assertSame('Monday', $locale->dayOfWeekName(DayOfWeek::Monday));
    }

    /**
     * Tests set abbreviated day of week name rejects empty name.
     */
    public function testSetAbbreviatedDayOfWeekNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setAbbreviatedDayOfWeekName(DayOfWeek::Monday, '');
    }

    /**
     * Tests set season names.
     */
    public function testSetSeasonNames(): void
    {
        $builder = new LocaleBuilder('en-US', new English());
        $builder->setSeasonName(Season::Autumn, 'Fall');
        $names = [Season::Winter->value => 'Cold'];

        self::assertSame($builder, $builder->setSeasonNames($names));

        $locale = $builder->build();
        self::assertSame('Cold', $locale->seasonName(Season::Winter));
        self::assertSame('Fall', $locale->seasonName(Season::Autumn));
        self::assertSame('Spring', $locale->seasonName(Season::Spring));
    }

    /**
     * Tests set season names rejects an empty name.
     */
    public function testSetSeasonNamesRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))
            ->setSeasonNames([Season::Winter->value => '']);
    }

    /**
     * Tests set season name.
     */
    public function testSetSeasonName(): void
    {
        $builder = new LocaleBuilder('en-US', new English());

        self::assertSame($builder, $builder->setSeasonName(Season::Winter, 'Cold'));

        $locale = $builder->build();
        self::assertSame('Cold', $locale->seasonName(Season::Winter));
        self::assertSame(Season::Winter, $locale->seasonFromName('Cold'));
        self::assertSame(Season::Winter, $locale->seasonFromName('Winter'));
    }

    /**
     * Tests set season name rejects empty name.
     */
    public function testSetSeasonNameRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocaleBuilder('en-US', new English()))->setSeasonName(Season::Winter, '');
    }
}

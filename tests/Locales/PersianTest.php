<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Locales\PersianNumerals;
use Kampute\CivilDate\Locales\PersianTextNormalizer;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the built-in Persian locale.
 */
final class PersianTest extends TestCase
{
    /**
     * Tests configuration.
     */
    public function testConfiguration(): void
    {
        $locale = new Persian();

        self::assertSame(Persian::LANGUAGE_TAG, $locale->languageTag());
        self::assertTrue($locale->isRightToLeft());
        self::assertInstanceOf(PersianTextNormalizer::class, $locale->textNormalizer());
        self::assertInstanceOf(PersianNumerals::class, $locale->numberLocalizer());
    }

    /**
     * Tests constructor accepts language region tag.
     */
    public function testConstructorAcceptsLanguageRegionTag(): void
    {
        self::assertSame('fa-IR', (new Persian('fa-IR'))->languageTag());
    }

    /**
     * Tests constructor rejects invalid language tag.
     */
    public function testConstructorRejectsInvalidLanguageTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Persian('FA-ir');
    }

    /**
     * Tests era name.
     */
    #[DataProvider('eraNameProvider')]
    public function testEraName(Calendar $calendar, string $name): void
    {
        self::assertSame($name, (new Persian(Persian::LANGUAGE_TAG))->eraName($calendar));
    }

    /**
     * Tests era from name.
     */
    #[DataProvider('eraFromNameProvider')]
    public function testEraFromName(Calendar $calendar, string $name): void
    {
        self::assertSame($calendar, (new Persian(Persian::LANGUAGE_TAG))->eraFromName($name));
    }

    /**
     * Tests abbreviated era name.
     */
    #[DataProvider('abbreviatedEraNameProvider')]
    public function testAbbreviatedEraName(Calendar $calendar, string $name): void
    {
        self::assertSame($name, (new Persian())->abbreviatedEraName($calendar));
    }

    /**
     * Tests abbreviated era from name.
     */
    #[DataProvider('abbreviatedEraFromNameProvider')]
    public function testAbbreviatedEraFromName(Calendar $calendar, string $name): void
    {
        self::assertSame($calendar, (new Persian())->abbreviatedEraFromName($name));
    }

    /**
     * Tests abbreviated era parsing falls back to full names when abbreviation not recognized.
     */
    #[DataProvider('eraFromNameProvider')]
    public function testAbbreviatedEraFromNameFallsBackToFullNames(Calendar $calendar, string $name): void
    {
        self::assertSame($calendar, (new Persian())->abbreviatedEraFromName($name));
    }

    /**
     * Tests month name.
     */
    #[DataProvider('monthNameProvider')]
    public function testMonthName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($name, (new Persian(Persian::LANGUAGE_TAG))->monthName($calendar, $month));
    }

    /**
     * Tests month from name.
     */
    #[DataProvider('monthFromNameProvider')]
    public function testMonthFromName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($month, (new Persian(Persian::LANGUAGE_TAG))->monthFromName($calendar, $name));
    }

    /**
     * Tests abbreviated month name falls back to full name.
     */
    #[DataProvider('monthNameProvider')]
    public function testAbbreviatedMonthNameFallsBackToFullName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($name, (new Persian())->abbreviatedMonthName($calendar, $month));
    }

    /**
     * Tests abbreviated month parsing falls back to full names.
     */
    #[DataProvider('monthFromNameProvider')]
    public function testAbbreviatedMonthFromNameFallsBackToFullNames(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($month, (new Persian())->abbreviatedMonthFromName($calendar, $name));
    }

    /**
     * Tests day of week name.
     */
    #[DataProvider('dayOfWeekNameProvider')]
    public function testDayOfWeekName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($name, (new Persian(Persian::LANGUAGE_TAG))->dayOfWeekName($dayOfWeek));
    }

    /**
     * Tests day of week from name.
     */
    #[DataProvider('dayOfWeekFromNameProvider')]
    public function testDayOfWeekFromName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($dayOfWeek, (new Persian(Persian::LANGUAGE_TAG))->dayOfWeekFromName($name));
    }

    /**
     * Tests abbreviated day of week name falls back to full name.
     */
    #[DataProvider('dayOfWeekNameProvider')]
    public function testAbbreviatedDayOfWeekNameFallsBackToFullName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($name, (new Persian())->abbreviatedDayOfWeekName($dayOfWeek));
    }

    /**
     * Tests abbreviated day of week parsing falls back to full names.
     */
    #[DataProvider('dayOfWeekFromNameProvider')]
    public function testAbbreviatedDayOfWeekFromNameFallsBackToFullNames(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($dayOfWeek, (new Persian())->abbreviatedDayOfWeekFromName($name));
    }

    /**
     * Tests season name.
     */
    #[DataProvider('seasonNameProvider')]
    public function testSeasonName(Season $season, string $name): void
    {
        self::assertSame($name, (new Persian(Persian::LANGUAGE_TAG))->seasonName($season));
    }

    /**
     * Tests season from name.
     */
    #[DataProvider('seasonFromNameProvider')]
    public function testSeasonFromName(Season $season, string $name): void
    {
        self::assertSame($season, (new Persian(Persian::LANGUAGE_TAG))->seasonFromName($name));
    }

    /**
     * Tests name lookup ignores arabic diacritics.
     */
    public function testNameLookupIgnoresArabicDiacritics(): void
    {
        $locale = new Persian(Persian::LANGUAGE_TAG);

        self::assertSame(Calendar::Jalali, $locale->eraFromName('جَلّالی'));
        self::assertSame(Calendar::Gregorian, $locale->eraFromName('مِیلادی'));
    }

    /**
     * Provides data for era name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function eraNameProvider(): iterable
    {
        yield 'Jalali' => [Calendar::Jalali, 'خورشیدی'];
        yield 'Gregorian' => [Calendar::Gregorian, 'میلادی'];
        yield 'Islamic' => [Calendar::Islamic, 'قمری'];
    }

    /**
     * Provides data for era from name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function eraFromNameProvider(): iterable
    {
        yield from self::eraNameProvider();
        yield 'Jalali alias' => [Calendar::Jalali, 'جلالی'];
        yield 'Solar' => [Calendar::Jalali, 'شمسی'];
        yield 'Sun-based' => [Calendar::Jalali, 'خورشیدی'];
        yield 'Solar Hijri' => [Calendar::Jalali, 'خورشیدی'];
        yield 'Sun-based Hijri' => [Calendar::Jalali, 'هجری خورشیدی'];
        yield 'Lunar' => [Calendar::Islamic, 'قمری'];
        yield 'Islamic alias' => [Calendar::Islamic, 'اسلامی'];
    }

    /**
     * Provides data for abbreviated era name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function abbreviatedEraNameProvider(): iterable
    {
        yield 'Solar Hijri' => [Calendar::Jalali, 'ه.ش'];
        yield 'Common Era' => [Calendar::Gregorian, 'م'];
        yield 'Hijri' => [Calendar::Islamic, 'ه.ق'];
    }

    /**
     * Provides data for abbreviated era from name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function abbreviatedEraFromNameProvider(): iterable
    {
        yield from self::abbreviatedEraNameProvider();
        yield 'Solar Hijri with space' => [Calendar::Jalali, 'ه ش'];
        yield 'Solar Hijri with tatweel' => [Calendar::Jalali, 'هـ.ش'];
        yield 'Solar Hijri with tatweel and space' => [Calendar::Jalali, 'هـ ش'];
        yield 'Common Era with dot' => [Calendar::Gregorian, 'م.'];
        yield 'Hijri with space' => [Calendar::Islamic, 'ه ق'];
        yield 'Hijri with tatweel' => [Calendar::Islamic, 'هـ.ق'];
        yield 'Hijri with tatweel and space' => [Calendar::Islamic, 'هـ ق'];
    }

    /**
     * Provides data for month name tests.
     *
     * @return iterable<string,array{Calendar,int,string}>
     */
    public static function monthNameProvider(): iterable
    {
        $names = [
            Calendar::Jalali->value => [
                'فروردین',
                'اردیبهشت',
                'خرداد',
                'تیر',
                'مرداد',
                'شهریور',
                'مهر',
                'آبان',
                'آذر',
                'دی',
                'بهمن',
                'اسفند',
            ],
            Calendar::Gregorian->value => [
                'ژانویه',
                'فوریه',
                'مارس',
                'آوریل',
                'مه',
                'ژوئن',
                'جولای',
                'اوت',
                'سپتامبر',
                'اکتبر',
                'نوامبر',
                'دسامبر',
            ],
            Calendar::Islamic->value => [
                'محرم',
                'صفر',
                'ربیع‌الاول',
                'ربیع‌الثانی',
                'جمادی‌الاول',
                'جمادی‌الثانی',
                'رجب',
                'شعبان',
                'رمضان',
                'شوال',
                'ذی‌القعده',
                'ذی‌الحجه',
            ],
        ];

        foreach (Calendar::cases() as $calendar) {
            foreach ($names[$calendar->value] as $index => $name) {
                yield "{$calendar->name} {$name}" => [$calendar, $index + 1, $name];
            }
        }
    }

    /**
     * Provides data for month from name tests.
     *
     * @return iterable<string,array{Calendar,int,string}>
     */
    public static function monthFromNameProvider(): iterable
    {
        yield from self::monthNameProvider();
        yield 'Amordad' => [Calendar::Jalali, 5, 'امرداد'];
        yield 'Afghan Persian Hamal' => [Calendar::Jalali, 1, 'حمل'];
        yield 'Afghan Persian Sawr' => [Calendar::Jalali, 2, 'ثور'];
        yield 'Afghan Persian Jawza' => [Calendar::Jalali, 3, 'جوزا'];
        yield 'Afghan Persian Saratan' => [Calendar::Jalali, 4, 'سرطان'];
        yield 'Afghan Persian Asad' => [Calendar::Jalali, 5, 'اسد'];
        yield 'Afghan Persian Sonbola' => [Calendar::Jalali, 6, 'سنبله'];
        yield 'Afghan Persian Mizan' => [Calendar::Jalali, 7, 'میزان'];
        yield 'Afghan Persian Aqrab' => [Calendar::Jalali, 8, 'عقرب'];
        yield 'Afghan Persian Qaws' => [Calendar::Jalali, 9, 'قوس'];
        yield 'Afghan Persian Jadi' => [Calendar::Jalali, 10, 'جدی'];
        yield 'Afghan Persian Dalwa' => [Calendar::Jalali, 11, 'دلو'];
        yield 'Afghan Persian Hut' => [Calendar::Jalali, 12, 'حوت'];
        yield 'Gregorian January Dari form' => [Calendar::Gregorian, 1, 'جنوری'];
        yield 'Gregorian February Dari form' => [Calendar::Gregorian, 2, 'فبروری'];
        yield 'Gregorian March Dari form' => [Calendar::Gregorian, 3, 'مارچ'];
        yield 'Gregorian April alternate transliteration' => [Calendar::Gregorian, 4, 'اپریل'];
        yield 'Gregorian May alternate transliteration' => [Calendar::Gregorian, 5, 'می'];
        yield 'Gregorian June Dari form' => [Calendar::Gregorian, 6, 'جون'];
        yield 'Gregorian July alternate transliteration' => [Calendar::Gregorian, 7, 'ژوئیه'];
        yield 'Gregorian August transliteration' => [Calendar::Gregorian, 8, 'آگوست'];
        yield 'Gregorian August shortened transliteration' => [Calendar::Gregorian, 8, 'آگست'];
        yield 'Gregorian September Dari form' => [Calendar::Gregorian, 9, 'سپتمبر'];
        yield 'Gregorian October Dari form' => [Calendar::Gregorian, 10, 'اکتوبر'];
        yield 'Gregorian November Dari form' => [Calendar::Gregorian, 11, 'نومبر'];
        yield 'Gregorian December Dari form' => [Calendar::Gregorian, 12, 'دسمبر'];
        yield 'Islamic Rabi al-Awwal with space' => [Calendar::Islamic, 3, 'ربیع الاول'];
        yield 'Islamic Rabi al-Thani with space' => [Calendar::Islamic, 4, 'ربیع الثانی'];
        yield 'Islamic Jumada al-Awwal with space' => [Calendar::Islamic, 5, 'جمادی الاول'];
        yield 'Islamic Jumada al-Thani with space' => [Calendar::Islamic, 6, 'جمادی الثانی'];
        yield 'Islamic Dhu al-Qadah with space' => [Calendar::Islamic, 11, 'ذی القعده'];
        yield 'Islamic Dhu al-Hijjah with space' => [Calendar::Islamic, 12, 'ذی الحجه'];
        yield 'Islamic Afghan Dhu al-Qadah' => [Calendar::Islamic, 11, 'ذوالقعده'];
        yield 'Islamic Afghan Dhu al-Qadah with space' => [Calendar::Islamic, 11, 'ذو القعده'];
        yield 'Islamic Afghan Dhu al-Hijjah' => [Calendar::Islamic, 12, 'ذوالحجه'];
        yield 'Islamic Afghan Dhu al-Hijjah with space' => [Calendar::Islamic, 12, 'ذو الحجه'];
    }

    /**
     * Provides data for day of week name tests.
     *
     * @return iterable<string,array{DayOfWeek,string}>
     */
    public static function dayOfWeekNameProvider(): iterable
    {
        yield 'Sunday' => [DayOfWeek::Sunday, 'یکشنبه'];
        yield 'Monday' => [DayOfWeek::Monday, 'دوشنبه'];
        yield 'Tuesday' => [DayOfWeek::Tuesday, 'سه‌شنبه'];
        yield 'Wednesday' => [DayOfWeek::Wednesday, 'چهارشنبه'];
        yield 'Thursday' => [DayOfWeek::Thursday, 'پنجشنبه'];
        yield 'Friday' => [DayOfWeek::Friday, 'جمعه'];
        yield 'Saturday' => [DayOfWeek::Saturday, 'شنبه'];
    }

    /**
     * Provides data for day of week from name tests.
     *
     * @return iterable<string,array{DayOfWeek,string}>
     */
    public static function dayOfWeekFromNameProvider(): iterable
    {
        yield from self::dayOfWeekNameProvider();
        yield 'Sunday with ZWNJ' => [DayOfWeek::Sunday, 'یک‌شنبه'];
        yield 'Sunday with space' => [DayOfWeek::Sunday, 'یک شنبه'];
        yield 'Monday with ZWNJ' => [DayOfWeek::Monday, 'دو‌شنبه'];
        yield 'Monday with space' => [DayOfWeek::Monday, 'دو شنبه'];
        yield 'Tuesday without ZWNJ' => [DayOfWeek::Tuesday, 'سهشنبه'];
        yield 'Tuesday with space' => [DayOfWeek::Tuesday, 'سه شنبه'];
        yield 'Wednesday with ZWNJ' => [DayOfWeek::Wednesday, 'چهار‌شنبه'];
        yield 'Wednesday with space' => [DayOfWeek::Wednesday, 'چهار شنبه'];
        yield 'Thursday with ZWNJ' => [DayOfWeek::Thursday, 'پنج‌شنبه'];
        yield 'Thursday with space' => [DayOfWeek::Thursday, 'پنج شنبه'];
    }

    /**
     * Provides data for season name tests.
     *
     * @return iterable<string,array{Season,string}>
     */
    public static function seasonNameProvider(): iterable
    {
        yield 'Spring' => [Season::Spring, 'بهار'];
        yield 'Summer' => [Season::Summer, 'تابستان'];
        yield 'Autumn' => [Season::Autumn, 'پاییز'];
        yield 'Winter' => [Season::Winter, 'زمستان'];
    }

    /**
     * Provides data for season from name tests.
     *
     * @return iterable<string,array{Season,string}>
     */
    public static function seasonFromNameProvider(): iterable
    {
        yield from self::seasonNameProvider();
        yield 'Autumn literary synonym' => [Season::Autumn, 'خزان'];
    }
}

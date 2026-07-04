<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\EnglishNumerals;
use Kampute\CivilDate\Locales\EnglishTextNormalizer;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the built-in English locale.
 */
final class EnglishTest extends TestCase
{
    /**
     * Tests configuration.
     */
    public function testConfiguration(): void
    {
        $locale = new English();

        self::assertSame(English::LANGUAGE_TAG, $locale->languageTag());
        self::assertFalse($locale->isRightToLeft());
        self::assertInstanceOf(EnglishTextNormalizer::class, $locale->textNormalizer());
        self::assertInstanceOf(EnglishNumerals::class, $locale->numberLocalizer());
    }

    /**
     * Tests constructor accepts language region tag.
     */
    public function testConstructorAcceptsLanguageRegionTag(): void
    {
        self::assertSame('en-GB', (new English('en-GB'))->languageTag());
    }

    /**
     * Tests constructor rejects invalid language tag.
     */
    public function testConstructorRejectsInvalidLanguageTag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new English('EN-gb');
    }

    /**
     * Tests era name.
     */
    #[DataProvider('eraNameProvider')]
    public function testEraName(Calendar $calendar, string $name): void
    {
        self::assertSame($name, (new English())->eraName($calendar));
    }

    /**
     * Tests era from name.
     */
    #[DataProvider('eraFromNameProvider')]
    public function testEraFromName(Calendar $calendar, string $name): void
    {
        self::assertSame($calendar, (new English())->eraFromName($name));
    }

    /**
     * Tests abbreviated era name.
     */
    #[DataProvider('abbreviatedEraNameProvider')]
    public function testAbbreviatedEraName(Calendar $calendar, string $name): void
    {
        self::assertSame($name, (new English())->abbreviatedEraName($calendar));
    }

    /**
     * Tests era from abbreviated name.
     */
    #[DataProvider('abbreviatedEraNameProvider')]
    public function testAbbreviatedEraFromName(Calendar $calendar, string $name): void
    {
        self::assertSame($calendar, (new English())->abbreviatedEraFromName($name));
    }

    /**
     * Tests month name.
     */
    #[DataProvider('monthNameProvider')]
    public function testMonthName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($name, (new English())->monthName($calendar, $month));
    }

    /**
     * Tests month from name.
     */
    #[DataProvider('monthNameProvider')]
    public function testMonthFromName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($month, (new English())->monthFromName($calendar, $name));
    }

    /**
     * Tests abbreviated month name.
     */
    #[DataProvider('abbreviatedMonthNameProvider')]
    public function testAbbreviatedMonthName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($name, (new English())->abbreviatedMonthName($calendar, $month));
    }

    /**
     * Tests month from abbreviated name.
     */
    #[DataProvider('abbreviatedMonthNameProvider')]
    public function testAbbreviatedMonthFromName(Calendar $calendar, int $month, string $name): void
    {
        self::assertSame($month, (new English())->abbreviatedMonthFromName($calendar, $name));
    }

    /**
     * Tests day of week name.
     */
    #[DataProvider('dayOfWeekNameProvider')]
    public function testDayOfWeekName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($name, (new English())->dayOfWeekName($dayOfWeek));
    }

    /**
     * Tests day of week from name.
     */
    #[DataProvider('dayOfWeekNameProvider')]
    public function testDayOfWeekFromName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($dayOfWeek, (new English())->dayOfWeekFromName($name));
    }

    /**
     * Tests abbreviated day of week name.
     */
    #[DataProvider('abbreviatedDayOfWeekNameProvider')]
    public function testAbbreviatedDayOfWeekName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($name, (new English())->abbreviatedDayOfWeekName($dayOfWeek));
    }

    /**
     * Tests day of week from abbreviated name.
     */
    #[DataProvider('abbreviatedDayOfWeekNameProvider')]
    public function testAbbreviatedDayOfWeekFromName(DayOfWeek $dayOfWeek, string $name): void
    {
        self::assertSame($dayOfWeek, (new English())->abbreviatedDayOfWeekFromName($name));
    }

    /**
     * Tests season name.
     */
    #[DataProvider('seasonNameProvider')]
    public function testSeasonName(Season $season, string $name): void
    {
        self::assertSame($name, (new English())->seasonName($season));
    }

    /**
     * Tests season from name.
     */
    #[DataProvider('seasonFromNameProvider')]
    public function testSeasonFromName(Season $season, string $name): void
    {
        self::assertSame($season, (new English())->seasonFromName($name));
    }

    /**
     * Tests name lookups reject unknown names.
     */
    public function testNameLookupsRejectUnknownNames(): void
    {
        $locale = new English();

        self::assertNull($locale->eraFromName('unknown'));
        self::assertNull($locale->abbreviatedEraFromName('unknown'));
        self::assertNull($locale->monthFromName(Calendar::Gregorian, 'unknown'));
        self::assertNull($locale->abbreviatedMonthFromName(Calendar::Gregorian, 'January'));
        self::assertNull($locale->dayOfWeekFromName('unknown'));
        self::assertNull($locale->abbreviatedDayOfWeekFromName('Sunday'));
        self::assertNull($locale->seasonFromName('unknown'));
    }

    /**
     * Provides data for era name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function eraNameProvider(): iterable
    {
        yield 'Solar Hijri' => [Calendar::Jalali, 'Solar Hijri'];
        yield 'Common Era' => [Calendar::Gregorian, 'Common Era'];
        yield 'Hijri alias' => [Calendar::Islamic, 'Hijri'];
    }

    /**
     * Provides data for era from name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function eraFromNameProvider(): iterable
    {
        yield from self::eraNameProvider();
        yield 'Persian' => [Calendar::Jalali, 'Persian'];
        yield 'Jalali' => [Calendar::Jalali, 'Jalali'];
        yield 'Hijri' => [Calendar::Islamic, 'Hijri'];
        yield 'Islamic Civil' => [Calendar::Islamic, 'Islamic Civil'];
    }

    /**
     * Provides data for abbreviated era name tests.
     *
     * @return iterable<string,array{Calendar,string}>
     */
    public static function abbreviatedEraNameProvider(): iterable
    {
        yield 'Solar Hijri' => [Calendar::Jalali, 'SH'];
        yield 'Common Era' => [Calendar::Gregorian, 'CE'];
        yield 'Hijri' => [Calendar::Islamic, 'AH'];
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
                'Farvardin',
                'Ordibehesht',
                'Khordad',
                'Tir',
                'Mordad',
                'Shahrivar',
                'Mehr',
                'Aban',
                'Azar',
                'Dey',
                'Bahman',
                'Esfand',
            ],
            Calendar::Gregorian->value => [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
            Calendar::Islamic->value => [
                'Muharram',
                'Safar',
                'Rabi al-Awwal',
                'Rabi al-Thani',
                'Jumada al-Awwal',
                'Jumada al-Thani',
                'Rajab',
                "Sha'ban",
                'Ramadan',
                'Shawwal',
                'Dhu al-Qadah',
                'Dhu al-Hijjah',
            ],
        ];

        foreach (Calendar::cases() as $calendar) {
            foreach ($names[$calendar->value] as $index => $name) {
                yield "{$calendar->name} {$name}" => [$calendar, $index + 1, $name];
            }
        }
    }

    /**
     * Provides data for abbreviated month name tests.
     *
     * @return iterable<string,array{Calendar,int,string}>
     */
    public static function abbreviatedMonthNameProvider(): iterable
    {
        $jalaliNames = ['Far', 'Ord', 'Kho', 'Tir', 'Mor', 'Sha', 'Meh', 'Aba', 'Aza', 'Dey', 'Bah', 'Esf'];
        foreach ($jalaliNames as $index => $name) {
            yield "Jalali {$name}" => [Calendar::Jalali, $index + 1, $name];
        }

        $names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach ($names as $index => $name) {
            yield "Gregorian {$name}" => [Calendar::Gregorian, $index + 1, $name];
        }

        $names = ['Muh', 'Saf', 'Rab-I', 'Rab-II', 'Jum-I', 'Jum-II', 'Raj', 'Sha', 'Ram', 'Shaw', 'Dhu-Q', 'Dhu-H'];
        foreach ($names as $index => $name) {
            yield "Islamic {$name}" => [Calendar::Islamic, $index + 1, $name];
        }
    }

    /**
     * Provides data for day of week name tests.
     *
     * @return iterable<string,array{DayOfWeek,string}>
     */
    public static function dayOfWeekNameProvider(): iterable
    {
        yield 'Sunday' => [DayOfWeek::Sunday, 'Sunday'];
        yield 'Monday' => [DayOfWeek::Monday, 'Monday'];
        yield 'Tuesday' => [DayOfWeek::Tuesday, 'Tuesday'];
        yield 'Wednesday' => [DayOfWeek::Wednesday, 'Wednesday'];
        yield 'Thursday' => [DayOfWeek::Thursday, 'Thursday'];
        yield 'Friday' => [DayOfWeek::Friday, 'Friday'];
        yield 'Saturday' => [DayOfWeek::Saturday, 'Saturday'];
    }

    /**
     * Provides data for abbreviated day of week name tests.
     *
     * @return iterable<string,array{DayOfWeek,string}>
     */
    public static function abbreviatedDayOfWeekNameProvider(): iterable
    {
        yield 'Sunday' => [DayOfWeek::Sunday, 'Sun'];
        yield 'Monday' => [DayOfWeek::Monday, 'Mon'];
        yield 'Tuesday' => [DayOfWeek::Tuesday, 'Tue'];
        yield 'Wednesday' => [DayOfWeek::Wednesday, 'Wed'];
        yield 'Thursday' => [DayOfWeek::Thursday, 'Thu'];
        yield 'Friday' => [DayOfWeek::Friday, 'Fri'];
        yield 'Saturday' => [DayOfWeek::Saturday, 'Sat'];
    }

    /**
     * Provides data for season name tests.
     *
     * @return iterable<string,array{Season,string}>
     */
    public static function seasonNameProvider(): iterable
    {
        yield 'Spring' => [Season::Spring, 'Spring'];
        yield 'Summer' => [Season::Summer, 'Summer'];
        yield 'Autumn' => [Season::Autumn, 'Autumn'];
        yield 'Winter' => [Season::Winter, 'Winter'];
    }

    /**
     * Provides data for season from name tests.
     *
     * @return iterable<string,array{Season,string}>
     */
    public static function seasonFromNameProvider(): iterable
    {
        yield from self::seasonNameProvider();
        yield 'Fall' => [Season::Autumn, 'Fall'];
    }
}

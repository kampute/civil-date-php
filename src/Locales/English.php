<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Localization\NumberLocalizer;
use Kampute\CivilDate\Localization\TextNormalizer;
use Kampute\CivilDate\Season;

/**
 * English calendar localization.
 */
class English extends Locale
{
    /**
     * Language tag for English localization.
     *
     * @var string
     */
    public const LANGUAGE_TAG = 'en';

    /**
     * Text normalizer for English names.
     *
     * @var TextNormalizer
     */
    private readonly TextNormalizer $textNormalizer;

    /**
     * Number localizer for English digits and words.
     *
     * @var NumberLocalizer
     */
    private readonly NumberLocalizer $numberLocalizer;

    /**
     * Creates an English locale.
     *
     * @param string $languageTag Locale language tag.
     */
    public function __construct(string $languageTag = self::LANGUAGE_TAG)
    {
        parent::__construct($languageTag);

        $this->textNormalizer = new EnglishTextNormalizer();
        $this->numberLocalizer = new EnglishNumerals($this->textNormalizer);
    }

    /**
     * Returns whether English text is written right to left.
     *
     * @return bool Always false.
     */
    public function isRightToLeft(): bool
    {
        return false;
    }

    /**
     * Returns the English text normalizer.
     *
     * @return TextNormalizer Text normalizer for English names.
     */
    public function textNormalizer(): TextNormalizer
    {
        return $this->textNormalizer;
    }

    /**
     * Returns the English number localizer.
     *
     * @return NumberLocalizer Number localizer for English digits and words.
     */
    public function numberLocalizer(): NumberLocalizer
    {
        return $this->numberLocalizer;
    }

    /**
     * Formats an era name in English.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string English era name.
     *
     * @see English::eraFromName()
     */
    public function eraName(Calendar $calendar): string
    {
        return match ($calendar) {
            Calendar::Jalali => 'Solar Hijri',
            Calendar::Gregorian => 'Common Era',
            Calendar::Islamic => 'Hijri',
        };
    }

    /**
     * Parses an English era name.
     *
     * @param string $name Era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see English::eraName()
     */
    public function eraFromName(string $name): ?Calendar
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'solar hijri' => Calendar::Jalali,
            'jalali' => Calendar::Jalali,
            'persian' => Calendar::Jalali,
            'sh' => Calendar::Jalali,
            'common era' => Calendar::Gregorian,
            'ce' => Calendar::Gregorian,
            'gregorian' => Calendar::Gregorian,
            'hijri' => Calendar::Islamic,
            'ah' => Calendar::Islamic,
            'islamic' => Calendar::Islamic,
            'islamic civil' => Calendar::Islamic,
            'lunar hijri' => Calendar::Islamic,
            default => null,
        };
    }

    /**
     * Formats an abbreviated era name in English.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string English abbreviated era name.
     *
     * @see English::abbreviatedEraFromName()
     * @see English::eraName()
     */
    public function abbreviatedEraName(Calendar $calendar): string
    {
        return match ($calendar) {
            Calendar::Jalali => 'SH',
            Calendar::Gregorian => 'CE',
            Calendar::Islamic => 'AH',
        };
    }

    /**
     * Parses an English abbreviated era name.
     *
     * @param string $name Abbreviated era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see English::abbreviatedEraName()
     * @see English::eraFromName()
     */
    public function abbreviatedEraFromName(string $name): ?Calendar
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'sh' => Calendar::Jalali,
            'ce' => Calendar::Gregorian,
            'ah' => Calendar::Islamic,
            default => null,
        };
    }

    /**
     * Formats a month name in English.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string English month name.
     *
     * @throws InvalidArgumentException If the month number is invalid for the calendar.
     *
     * @see English::monthFromName()
     * @see English::abbreviatedMonthName()
     */
    public function monthName(Calendar $calendar, int $month): string
    {
        return match ($calendar) {
            Calendar::Jalali => match ($month) {
                1 => 'Farvardin',
                2 => 'Ordibehesht',
                3 => 'Khordad',
                4 => 'Tir',
                5 => 'Mordad',
                6 => 'Shahrivar',
                7 => 'Mehr',
                8 => 'Aban',
                9 => 'Azar',
                10 => 'Dey',
                11 => 'Bahman',
                12 => 'Esfand',
                default => throw new InvalidArgumentException("Invalid Jalali month number: {$month}."),
            },
            Calendar::Gregorian => match ($month) {
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
                default => throw new InvalidArgumentException("Invalid Gregorian month number: {$month}."),
            },
            Calendar::Islamic => match ($month) {
                1 => 'Muharram',
                2 => 'Safar',
                3 => 'Rabi al-Awwal',
                4 => 'Rabi al-Thani',
                5 => 'Jumada al-Awwal',
                6 => 'Jumada al-Thani',
                7 => 'Rajab',
                8 => "Sha'ban",
                9 => 'Ramadan',
                10 => 'Shawwal',
                11 => 'Dhu al-Qadah',
                12 => 'Dhu al-Hijjah',
                default => throw new InvalidArgumentException("Invalid Islamic month number: {$month}."),
            },
        };
    }

    /**
     * Parses an English month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see English::monthName()
     * @see English::abbreviatedMonthFromName()
     */
    public function monthFromName(Calendar $calendar, string $name): ?int
    {
        $name = $this->textNormalizer()->normalize($name);
        return match ($calendar) {
            Calendar::Jalali => match ($name) {
                'farvardin' => 1,
                'ordibehesht' => 2,
                'khordad' => 3,
                'tir' => 4,
                'mordad' => 5,
                'shahrivar' => 6,
                'mehr' => 7,
                'aban' => 8,
                'azar' => 9,
                'dey' => 10,
                'bahman' => 11,
                'esfand' => 12,
                default => null,
            },
            Calendar::Gregorian => match ($name) {
                'january' => 1,
                'february' => 2,
                'march' => 3,
                'april' => 4,
                'may' => 5,
                'june' => 6,
                'july' => 7,
                'august' => 8,
                'september' => 9,
                'october' => 10,
                'november' => 11,
                'december' => 12,
                default => null,
            },
            Calendar::Islamic => match ($name) {
                'muharram' => 1,
                'safar' => 2,
                'rabi al-awwal' => 3,
                'rabi al-thani' => 4,
                'jumada al-awwal' => 5,
                'jumada al-thani' => 6,
                'rajab' => 7,
                "sha'ban" => 8,
                'ramadan' => 9,
                'shawwal' => 10,
                'dhu al-qadah' => 11,
                'dhu al-hijjah' => 12,
                default => null,
            },
        };
    }

    /**
     * Formats an abbreviated month name in English.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string English abbreviated month name.
     *
     * @throws InvalidArgumentException If the month number is invalid for the calendar.
     *
     * @see English::abbreviatedMonthFromName()
     * @see English::monthName()
     */
    public function abbreviatedMonthName(Calendar $calendar, int $month): string
    {
        return match ($calendar) {
            Calendar::Jalali => match ($month) {
                1 => 'Far',
                2 => 'Ord',
                3 => 'Kho',
                4 => 'Tir',
                5 => 'Mor',
                6 => 'Sha',
                7 => 'Meh',
                8 => 'Aba',
                9 => 'Aza',
                10 => 'Dey',
                11 => 'Bah',
                12 => 'Esf',
                default => throw new InvalidArgumentException("Invalid Jalali month number: {$month}."),
            },
            Calendar::Gregorian => match ($month) {
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Aug',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dec',
                default => throw new InvalidArgumentException("Invalid Gregorian month number: {$month}."),
            },
            Calendar::Islamic => match ($month) {
                1 => 'Muh',
                2 => 'Saf',
                3 => 'Rab-I',
                4 => 'Rab-II',
                5 => 'Jum-I',
                6 => 'Jum-II',
                7 => 'Raj',
                8 => 'Sha',
                9 => 'Ram',
                10 => 'Shaw',
                11 => 'Dhu-Q',
                12 => 'Dhu-H',
                default => throw new InvalidArgumentException("Invalid Islamic month number: {$month}."),
            },
        };
    }

    /**
     * Parses an English abbreviated month name.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Abbreviated month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see English::abbreviatedMonthName()
     * @see English::monthFromName()
     */
    public function abbreviatedMonthFromName(Calendar $calendar, string $name): ?int
    {
        $name = $this->textNormalizer()->normalize($name);
        return match ($calendar) {
            Calendar::Jalali => match ($name) {
                'far' => 1,
                'ord' => 2,
                'kho' => 3,
                'tir' => 4,
                'mor' => 5,
                'sha' => 6,
                'meh' => 7,
                'aba' => 8,
                'aza' => 9,
                'dey' => 10,
                'bah' => 11,
                'esf' => 12,
                default => null,
            },
            Calendar::Gregorian => match ($name) {
                'jan' => 1,
                'feb' => 2,
                'mar' => 3,
                'apr' => 4,
                'may' => 5,
                'jun' => 6,
                'jul' => 7,
                'aug' => 8,
                'sep' => 9,
                'oct' => 10,
                'nov' => 11,
                'dec' => 12,
                default => null,
            },
            Calendar::Islamic => match ($name) {
                'muh' => 1,
                'saf' => 2,
                'rab-i' => 3,
                'rab-ii' => 4,
                'jum-i' => 5,
                'jum-ii' => 6,
                'raj' => 7,
                'sha' => 8,
                'ram' => 9,
                'shaw' => 10,
                'dhu-q' => 11,
                'dhu-h' => 12,
                default => null,
            },
        };
    }

    /**
     * Formats a day-of-week name in English.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string English day-of-week name.
     *
     * @see English::dayOfWeekFromName()
     * @see English::abbreviatedDayOfWeekName()
     */
    public function dayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            DayOfWeek::Sunday => 'Sunday',
            DayOfWeek::Monday => 'Monday',
            DayOfWeek::Tuesday => 'Tuesday',
            DayOfWeek::Wednesday => 'Wednesday',
            DayOfWeek::Thursday => 'Thursday',
            DayOfWeek::Friday => 'Friday',
            DayOfWeek::Saturday => 'Saturday',
        };
    }

    /**
     * Parses an English day-of-week name.
     *
     * @param string $name Day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see English::dayOfWeekName()
     * @see English::abbreviatedDayOfWeekFromName()
     */
    public function dayOfWeekFromName(string $name): ?DayOfWeek
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'sunday' => DayOfWeek::Sunday,
            'monday' => DayOfWeek::Monday,
            'tuesday' => DayOfWeek::Tuesday,
            'wednesday' => DayOfWeek::Wednesday,
            'thursday' => DayOfWeek::Thursday,
            'friday' => DayOfWeek::Friday,
            'saturday' => DayOfWeek::Saturday,
            default => null,
        };
    }

    /**
     * Formats an abbreviated day-of-week name in English.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string English abbreviated day-of-week name.
     *
     * @see English::abbreviatedDayOfWeekFromName()
     * @see English::dayOfWeekName()
     */
    public function abbreviatedDayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            DayOfWeek::Sunday => 'Sun',
            DayOfWeek::Monday => 'Mon',
            DayOfWeek::Tuesday => 'Tue',
            DayOfWeek::Wednesday => 'Wed',
            DayOfWeek::Thursday => 'Thu',
            DayOfWeek::Friday => 'Fri',
            DayOfWeek::Saturday => 'Sat',
        };
    }

    /**
     * Parses an English abbreviated day-of-week name.
     *
     * @param string $name Abbreviated day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see English::abbreviatedDayOfWeekName()
     * @see English::dayOfWeekFromName()
     */
    public function abbreviatedDayOfWeekFromName(string $name): ?DayOfWeek
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'sun' => DayOfWeek::Sunday,
            'mon' => DayOfWeek::Monday,
            'tue' => DayOfWeek::Tuesday,
            'wed' => DayOfWeek::Wednesday,
            'thu' => DayOfWeek::Thursday,
            'fri' => DayOfWeek::Friday,
            'sat' => DayOfWeek::Saturday,
            default => null,
        };
    }

    /**
     * Formats a season name in English.
     *
     * @param Season $season Season.
     *
     * @return string English season name.
     *
     * @see English::seasonFromName()
     */
    public function seasonName(Season $season): string
    {
        return match ($season) {
            Season::Spring => 'Spring',
            Season::Summer => 'Summer',
            Season::Autumn => 'Autumn',
            Season::Winter => 'Winter',
        };
    }

    /**
     * Parses an English season name.
     *
     * @param string $name Season name.
     *
     * @return Season|null Matching season, or null when unrecognized.
     *
     * @see English::seasonName()
     */
    public function seasonFromName(string $name): ?Season
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'spring' => Season::Spring,
            'summer' => Season::Summer,
            'autumn' => Season::Autumn,
            'fall' => Season::Autumn,
            'winter' => Season::Winter,
            default => null,
        };
    }
}

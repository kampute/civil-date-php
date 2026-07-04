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
 * Persian calendar localization.
 */
class Persian extends Locale
{
    /**
     * Language tag for base Persian localization.
     *
     * @var string
     */
    public const LANGUAGE_TAG = 'fa';

    /**
     * Text normalizer for Persian names.
     *
     * @var TextNormalizer
     */
    private readonly TextNormalizer $textNormalizer;

    /**
     * Number localizer for Persian digits and words.
     *
     * @var NumberLocalizer
     */
    private readonly NumberLocalizer $numberLocalizer;

    /**
     * Creates a Persian locale.
     *
     * @param string $languageTag Locale language tag.
     */
    public function __construct(string $languageTag = self::LANGUAGE_TAG)
    {
        parent::__construct($languageTag);

        $this->textNormalizer = new PersianTextNormalizer();
        $this->numberLocalizer = new PersianNumerals($this->textNormalizer);
    }

    /**
     * Returns whether Persian text is written right to left.
     *
     * @return bool Always true.
     */
    public function isRightToLeft(): bool
    {
        return true;
    }

    /**
     * Returns the Persian text normalizer.
     *
     * @return TextNormalizer Text normalizer for Persian names.
     */
    public function textNormalizer(): TextNormalizer
    {
        return $this->textNormalizer;
    }

    /**
     * Returns the Persian number localizer.
     *
     * @return NumberLocalizer Number localizer for Persian digits and words.
     */
    public function numberLocalizer(): NumberLocalizer
    {
        return $this->numberLocalizer;
    }

    /**
     * Formats an era name in Persian.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Persian era name.
     *
     * @see Persian::eraFromName()
     * @see Persian::abbreviatedEraName()
     */
    public function eraName(Calendar $calendar): string
    {
        return match ($calendar) {
            Calendar::Jalali => 'خورشیدی',
            Calendar::Gregorian => 'میلادی',
            Calendar::Islamic => 'قمری',
        };
    }

    /**
     * Parses a Persian era name.
     *
     * @param string $name Era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see Persian::eraName()
     */
    public function eraFromName(string $name): ?Calendar
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'جلالی' => Calendar::Jalali,
            'خورشیدی' => Calendar::Jalali,
            'هجری خورشیدی' => Calendar::Jalali,
            'شمسی' => Calendar::Jalali,
            'هجری شمسی' => Calendar::Jalali,
            'میلادی' => Calendar::Gregorian,
            'اسلامی' => Calendar::Islamic,
            'هجری قمری' => Calendar::Islamic,
            'قمری' => Calendar::Islamic,
            'هجری' => Calendar::Islamic,
            default => null,
        };
    }

    /**
     * Formats an abbreviated era name in Persian.
     *
     * @param Calendar $calendar Calendar identifier.
     *
     * @return string Persian abbreviated era name.
     *
     * @see Persian::abbreviatedEraFromName()
     * @see Persian::eraName()
     */
    public function abbreviatedEraName(Calendar $calendar): string
    {
        return match ($calendar) {
            Calendar::Jalali => 'ه.ش',
            Calendar::Gregorian => 'م',
            Calendar::Islamic => 'ه.ق',
        };
    }

    /**
     * Parses a Persian abbreviated era name.
     *
     * @param string $name Abbreviated era name.
     *
     * @return Calendar|null Matching calendar, or null when unrecognized.
     *
     * @see Persian::abbreviatedEraName()
     * @see Persian::eraFromName()
     */
    public function abbreviatedEraFromName(string $name): ?Calendar
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'ه.ش' => Calendar::Jalali,
            'ه ش' => Calendar::Jalali,
            'هـ.ش' => Calendar::Jalali,
            'هـ ش' => Calendar::Jalali,
            'م' => Calendar::Gregorian,
            'م.' => Calendar::Gregorian,
            'ه.ق' => Calendar::Islamic,
            'ه ق' => Calendar::Islamic,
            'هـ.ق' => Calendar::Islamic,
            'هـ ق' => Calendar::Islamic,
            default => $this->eraFromName($name),
        };
    }

    /**
     * Formats a month name in Persian.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Persian month name.
     *
     * @throws InvalidArgumentException If the month number is invalid for the calendar.
     *
     * @see Persian::monthFromName()
     */
    public function monthName(Calendar $calendar, int $month): string
    {
        return match ($calendar) {
            Calendar::Jalali => match ($month) {
                1 => 'فروردین',
                2 => 'اردیبهشت',
                3 => 'خرداد',
                4 => 'تیر',
                5 => 'مرداد',
                6 => 'شهریور',
                7 => 'مهر',
                8 => 'آبان',
                9 => 'آذر',
                10 => 'دی',
                11 => 'بهمن',
                12 => 'اسفند',
                default => throw new InvalidArgumentException("Invalid Jalali month number: {$month}."),
            },
            Calendar::Gregorian => match ($month) {
                1 => 'ژانویه',
                2 => 'فوریه',
                3 => 'مارس',
                4 => 'آوریل',
                5 => 'مه',
                6 => 'ژوئن',
                7 => 'جولای',
                8 => 'اوت',
                9 => 'سپتامبر',
                10 => 'اکتبر',
                11 => 'نوامبر',
                12 => 'دسامبر',
                default => throw new InvalidArgumentException("Invalid Gregorian month number: {$month}."),
            },
            Calendar::Islamic => match ($month) {
                1 => 'محرم',
                2 => 'صفر',
                3 => 'ربیع‌الاول',
                4 => 'ربیع‌الثانی',
                5 => 'جمادی‌الاول',
                6 => 'جمادی‌الثانی',
                7 => 'رجب',
                8 => 'شعبان',
                9 => 'رمضان',
                10 => 'شوال',
                11 => 'ذی‌القعده',
                12 => 'ذی‌الحجه',
                default => throw new InvalidArgumentException("Invalid Islamic month number: {$month}."),
            },
        };
    }

    /**
     * Parses a Persian month name.
     *
     * This method recognizes both Iranian and Afghan month names for a given calendar.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param string $name Month name.
     *
     * @return int|null Matching month number, or null when unrecognized.
     *
     * @see Persian::monthName()
     */
    public function monthFromName(Calendar $calendar, string $name): ?int
    {
        $name = $this->textNormalizer()->normalize($name);
        return match ($calendar) {
            Calendar::Jalali => match ($name) {
                // Iranian month names
                'فروردین' => 1,
                'اردیبهشت' => 2,
                'خرداد' => 3,
                'تیر' => 4,
                'مرداد' => 5,
                'امرداد' => 5,
                'شهریور' => 6,
                'مهر' => 7,
                'آبان' => 8,
                'آذر' => 9,
                'دی' => 10,
                'بهمن' => 11,
                'اسفند' => 12,
                // Afghan month names
                'حمل' => 1,
                'ثور' => 2,
                'جوزا' => 3,
                'سرطان' => 4,
                'اسد' => 5,
                'سنبله' => 6,
                'میزان' => 7,
                'عقرب' => 8,
                'قوس' => 9,
                'جدی' => 10,
                'دلو' => 11,
                'حوت' => 12,
                default => null,
            },
            Calendar::Gregorian => match ($name) {
                'ژانویه' => 1,
                'جنوری' => 1,
                'فوریه' => 2,
                'فبروری' => 2,
                'مارس' => 3,
                'مارچ' => 3,
                'آوریل' => 4,
                'اپریل' => 4,
                'مه' => 5,
                'می' => 5,
                'ژوئن' => 6,
                'جون' => 6,
                'جولای' => 7,
                'ژوئیه' => 7,
                'اوت' => 8,
                'آگوست' => 8,
                'آگست' => 8,
                'سپتامبر' => 9,
                'سپتمبر' => 9,
                'اکتبر' => 10,
                'اکتوبر' => 10,
                'نوامبر' => 11,
                'نومبر' => 11,
                'دسامبر' => 12,
                'دسمبر' => 12,
                default => null,
            },
            Calendar::Islamic => match ($name) {
                'محرم' => 1,
                'صفر' => 2,
                'ربیع‌الاول' => 3,
                'ربیع الاول' => 3,
                'ربیع‌الثانی' => 4,
                'ربیع الثانی' => 4,
                'جمادی‌الاول' => 5,
                'جمادی الاول' => 5,
                'جمادی‌الثانی' => 6,
                'جمادی الثانی' => 6,
                'رجب' => 7,
                'شعبان' => 8,
                'رمضان' => 9,
                'شوال' => 10,
                'ذی‌القعده' => 11,
                'ذی القعده' => 11,
                'ذوالقعده' => 11,
                'ذو القعده' => 11,
                'ذی‌الحجه' => 12,
                'ذی الحجه' => 12,
                'ذوالحجه' => 12,
                'ذو الحجه' => 12,
                default => null,
            },
        };
    }

    /**
     * Formats a day-of-week name in Persian.
     *
     * @param DayOfWeek $dayOfWeek Day of week.
     *
     * @return string Persian day-of-week name.
     *
     * @see Persian::dayOfWeekFromName()
     */
    public function dayOfWeekName(DayOfWeek $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            DayOfWeek::Sunday => 'یکشنبه',
            DayOfWeek::Monday => 'دوشنبه',
            DayOfWeek::Tuesday => 'سه‌شنبه',
            DayOfWeek::Wednesday => 'چهارشنبه',
            DayOfWeek::Thursday => 'پنجشنبه',
            DayOfWeek::Friday => 'جمعه',
            DayOfWeek::Saturday => 'شنبه',
        };
    }

    /**
     * Parses a Persian day-of-week name.
     *
     * @param string $name Day-of-week name.
     *
     * @return DayOfWeek|null Matching day of week, or null when unrecognized.
     *
     * @see Persian::dayOfWeekName()
     */
    public function dayOfWeekFromName(string $name): ?DayOfWeek
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'شنبه' => DayOfWeek::Saturday,
            'یکشنبه' => DayOfWeek::Sunday,
            'یک‌شنبه' => DayOfWeek::Sunday,
            'یک شنبه' => DayOfWeek::Sunday,
            'دوشنبه' => DayOfWeek::Monday,
            'دو‌شنبه' => DayOfWeek::Monday,
            'دو شنبه' => DayOfWeek::Monday,
            'سه‌شنبه' => DayOfWeek::Tuesday,
            'سهشنبه' => DayOfWeek::Tuesday,
            'سه شنبه' => DayOfWeek::Tuesday,
            'چهارشنبه' => DayOfWeek::Wednesday,
            'چهار‌شنبه' => DayOfWeek::Wednesday,
            'چهار شنبه' => DayOfWeek::Wednesday,
            'پنجشنبه' => DayOfWeek::Thursday,
            'پنج‌شنبه' => DayOfWeek::Thursday,
            'پنج شنبه' => DayOfWeek::Thursday,
            'جمعه' => DayOfWeek::Friday,
            default => null,
        };
    }

    /**
     * Formats a season name in Persian.
     *
     * @param Season $season Season.
     *
     * @return string Persian season name.
     *
     * @see Persian::seasonFromName()
     */
    public function seasonName(Season $season): string
    {
        return match ($season) {
            Season::Spring => 'بهار',
            Season::Summer => 'تابستان',
            Season::Autumn => 'پاییز',
            Season::Winter => 'زمستان',
        };
    }

    /**
     * Parses a Persian season name.
     *
     * @param string $name Season name.
     *
     * @return Season|null Matching season, or null when unrecognized.
     *
     * @see Persian::seasonName()
     */
    public function seasonFromName(string $name): ?Season
    {
        return match ($this->textNormalizer()->normalize($name)) {
            'بهار' => Season::Spring,
            'تابستان' => Season::Summer,
            'پاییز' => Season::Autumn,
            'خزان' => Season::Autumn,
            'زمستان' => Season::Winter,
            default => null,
        };
    }
}

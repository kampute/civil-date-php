<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;

/**
 * Persian localization for Afghanistan.
 */
class PersianAfghanistan extends Persian
{
    /**
     * Language tag for Persian as used in Afghanistan.
     *
     * @var string
     */
    public const LANGUAGE_TAG = 'fa-AF';

    /**
     * Creates an Afghan Persian locale.
     */
    public function __construct()
    {
        parent::__construct(self::LANGUAGE_TAG);
    }

    /**
     * Formats a month name in Afghan Persian.
     *
     * @param Calendar $calendar Calendar identifier.
     * @param int $month Month number.
     *
     * @return string Afghan Persian month name.
     *
     * @throws InvalidArgumentException If the month number is invalid for the calendar.
     */
    public function monthName(Calendar $calendar, int $month): string
    {
        return match ($calendar) {
            Calendar::Jalali => match ($month) {
                1 => 'حمل',
                2 => 'ثور',
                3 => 'جوزا',
                4 => 'سرطان',
                5 => 'اسد',
                6 => 'سنبله',
                7 => 'میزان',
                8 => 'عقرب',
                9 => 'قوس',
                10 => 'جدی',
                11 => 'دلو',
                12 => 'حوت',
                default => throw new InvalidArgumentException("Invalid Jalali month number: {$month}."),
            },
            Calendar::Gregorian => match ($month) {
                1 => 'جنوری',
                2 => 'فبروری',
                3 => 'مارچ',
                4 => 'اپریل',
                5 => 'می',
                6 => 'جون',
                7 => 'جولای',
                8 => 'آگست',
                9 => 'سپتمبر',
                10 => 'اکتوبر',
                11 => 'نومبر',
                12 => 'دسمبر',
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
                11 => 'ذوالقعده',
                12 => 'ذوالحجه',
                default => throw new InvalidArgumentException("Invalid Islamic month number: {$month}."),
            },
        };
    }

    /**
     * Formats a season name in Afghan Persian.
     *
     * @param Season $season Season.
     *
     * @return string Afghan Persian season name.
     */
    public function seasonName(Season $season): string
    {
        return match ($season) {
            Season::Spring => 'بهار',
            Season::Summer => 'تابستان',
            Season::Autumn => 'خزان',
            Season::Winter => 'زمستان',
        };
    }
}

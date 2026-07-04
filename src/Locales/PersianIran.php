<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use Kampute\CivilDate\DayOfWeek;

/**
 * Persian localization for Iran.
 */
class PersianIran extends Persian
{
    /**
     * Language tag for Persian as used in Iran.
     *
     * @var string
     */
    public const LANGUAGE_TAG = 'fa-IR';

    /**
     * Creates an Iranian Persian locale.
     */
    public function __construct()
    {
        parent::__construct(self::LANGUAGE_TAG);
    }
}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Locales\PersianIran;
use Kampute\CivilDate\Localization\LocaleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the built-in Iranian Persian locale.
 */
final class PersianIranTest extends TestCase
{
    /**
     * Tests configuration and built-in registration.
     */
    public function testConfiguration(): void
    {
        $locale = new PersianIran();

        self::assertSame(PersianIran::LANGUAGE_TAG, $locale->languageTag());
        self::assertTrue($locale->isRightToLeft());
        self::assertInstanceOf(PersianIran::class, LocaleRegistry::find(PersianIran::LANGUAGE_TAG, false));
    }
}

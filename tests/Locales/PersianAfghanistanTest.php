<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Locales\PersianAfghanistan;
use Kampute\CivilDate\Localization\LocaleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the built-in Afghan Persian locale.
 */
final class PersianAfghanistanTest extends TestCase
{
    /**
     * Tests configuration and built-in registration.
     */
    public function testConfiguration(): void
    {
        $locale = new PersianAfghanistan();

        self::assertSame(PersianAfghanistan::LANGUAGE_TAG, $locale->languageTag());
        self::assertTrue($locale->isRightToLeft());
        self::assertInstanceOf(PersianAfghanistan::class, LocaleRegistry::find(PersianAfghanistan::LANGUAGE_TAG, false));
    }
}

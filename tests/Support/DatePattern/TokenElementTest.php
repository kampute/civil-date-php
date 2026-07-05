<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Support\DatePattern\TokenElement;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern token elements.
 */
final class TokenElementTest extends TestCase
{
    /**
     * Tests token elements expose scope, rule, and formatting behavior.
     */
    public function testTokenElementBehavior(): void
    {
        $rule = new NumberDigit('year');
        $element = new TokenElement('Y', Calendar::Gregorian, $rule);
        $locale = new English();

        self::assertSame('Y', $element->symbol());
        self::assertSame(Calendar::Gregorian, $element->calendar());
        self::assertSame($rule, $element->rule());
        self::assertSame('2025', $element->format(new GregorianDate(2025, 3, 21), $locale));
    }

    /**
     * Tests unscoped token elements format the provided date without calendar conversion.
     */
    public function testUnscopedTokenElementFormatsDateWithoutCalendarConversion(): void
    {
        $rule = new NumberDigit('year');
        $element = new TokenElement('Y', null, $rule);
        $locale = new English();

        self::assertSame('2025', $element->format(new GregorianDate(2025, 3, 21), $locale));
        self::assertSame('1404', $element->format(new JalaliDate(1404, 1, 1), $locale));
    }

    /**
     * Tests scoped token elements format the provided date with calendar conversion.
     */
    public function testScopedTokenElementFormatsDateWithCalendarConversion(): void
    {
        $rule = new NumberDigit('year');
        $element = new TokenElement('Y', Calendar::Gregorian, $rule);
        $locale = new English();

        self::assertSame('2025', $element->format(new GregorianDate(2025, 3, 21), $locale));
        self::assertSame('2025', $element->format(new JalaliDate(1404, 1, 1), $locale));
    }
}

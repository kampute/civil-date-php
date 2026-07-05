<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Support\DatePattern\Tokens\MonthName;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized month-name token rules.
 */
final class MonthNameTest extends TestCase
{
    /**
     * Tests formatting month names.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $rule = new MonthName(abbreviated: false);
        $abbreviatedRule = new MonthName(abbreviated: true);

        self::assertSame('March', $rule->format($date, new English()));
        self::assertSame('Mar', $abbreviatedRule->format($date, new English()));
    }

    /**
     * Tests parsing month names.
     */
    public function testParse(): void
    {
        $rule = new MonthName(abbreviated: false);
        $abbreviatedRule = new MonthName(abbreviated: true);

        self::assertSame(3, $rule->parse('March', Calendar::Gregorian, new English()));
        self::assertSame(3, $abbreviatedRule->parse('Mar', Calendar::Gregorian, new English()));
        self::assertSame(3, $rule->parse('Rabi al-Awwal', Calendar::Islamic, new English()));
        self::assertSame(3, $rule->parse('ربیع الاول', Calendar::Islamic, new Persian()));
    }

    /**
     * Tests parsing rejects unknown month names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized month name');

        $rule = new MonthName(abbreviated: false);
        $rule->parse('Unknown', Calendar::Gregorian, new English());
    }
}

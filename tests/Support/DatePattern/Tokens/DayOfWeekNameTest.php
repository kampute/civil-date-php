<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Support\DatePattern\Tokens\DayOfWeekName;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized day-of-week-name token rules.
 */
final class DayOfWeekNameTest extends TestCase
{
    /**
     * Tests formatting day-of-week names.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $rule = new DayOfWeekName(abbreviated: false);
        $abbreviatedRule = new DayOfWeekName(abbreviated: true);

        self::assertSame('Friday', $rule->format($date, new English()));
        self::assertSame('Fri', $abbreviatedRule->format($date, new English()));
    }

    /**
     * Tests parsing day-of-week names.
     */
    public function testParse(): void
    {
        $rule = new DayOfWeekName(abbreviated: false);
        $abbreviatedRule = new DayOfWeekName(abbreviated: true);

        self::assertSame(5, $rule->parse('Friday', Calendar::Gregorian, new English()));
        self::assertSame(5, $abbreviatedRule->parse('Fri', Calendar::Gregorian, new English()));
        self::assertSame(2, $rule->parse('سه شنبه', Calendar::Gregorian, new Persian()));
    }

    /**
     * Tests parsing rejects unknown day-of-week names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized day of week name');

        $rule = new DayOfWeekName(abbreviated: false);
        $rule->parse('Unknown', Calendar::Gregorian, new English());
    }
}

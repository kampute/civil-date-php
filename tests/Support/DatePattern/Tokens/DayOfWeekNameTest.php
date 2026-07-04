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
 * Tests localized day-of-week-name token definitions.
 */
final class DayOfWeekNameTest extends TestCase
{
    /**
     * Tests formatting day-of-week names.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $tokenDefinition = new DayOfWeekName(abbreviated: false);
        $abbreviatedTokenDefinition = new DayOfWeekName(abbreviated: true);

        self::assertSame('Friday', $tokenDefinition->format($date, new English()));
        self::assertSame('Fri', $abbreviatedTokenDefinition->format($date, new English()));
    }

    /**
     * Tests parsing day-of-week names.
     */
    public function testParse(): void
    {
        $tokenDefinition = new DayOfWeekName(abbreviated: false);
        $abbreviatedTokenDefinition = new DayOfWeekName(abbreviated: true);

        self::assertSame(5, $tokenDefinition->parse('Friday', Calendar::Gregorian, new English()));
        self::assertSame(5, $abbreviatedTokenDefinition->parse('Fri', Calendar::Gregorian, new English()));
        self::assertSame(2, $tokenDefinition->parse('سه شنبه', Calendar::Gregorian, new Persian()));
    }

    /**
     * Tests parsing rejects unknown day-of-week names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized day of week name');

        $tokenDefinition = new DayOfWeekName(abbreviated: false);
        $tokenDefinition->parse('Unknown', Calendar::Gregorian, new English());
    }
}

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
 * Tests localized month-name token definitions.
 */
final class MonthNameTest extends TestCase
{
    /**
     * Tests formatting month names.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $tokenDefinition = new MonthName(abbreviated: false);
        $abbreviatedTokenDefinition = new MonthName(abbreviated: true);

        self::assertSame('March', $tokenDefinition->format($date, new English()));
        self::assertSame('Mar', $abbreviatedTokenDefinition->format($date, new English()));
    }

    /**
     * Tests parsing month names.
     */
    public function testParse(): void
    {
        $tokenDefinition = new MonthName(abbreviated: false);
        $abbreviatedTokenDefinition = new MonthName(abbreviated: true);

        self::assertSame(3, $tokenDefinition->parse('March', Calendar::Gregorian, new English()));
        self::assertSame(3, $abbreviatedTokenDefinition->parse('Mar', Calendar::Gregorian, new English()));
        self::assertSame(3, $tokenDefinition->parse('Rabi al-Awwal', Calendar::Islamic, new English()));
        self::assertSame(3, $tokenDefinition->parse('ربیع الاول', Calendar::Islamic, new Persian()));
    }

    /**
     * Tests parsing rejects unknown month names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized month name');

        $tokenDefinition = new MonthName(abbreviated: false);
        $tokenDefinition->parse('Unknown', Calendar::Gregorian, new English());
    }
}

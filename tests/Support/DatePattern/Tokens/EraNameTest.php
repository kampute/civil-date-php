<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Support\DatePattern\Tokens\EraName;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized era-name token definitions.
 */
final class EraNameTest extends TestCase
{
    /**
     * Tests formatting era names.
     */
    public function testFormat(): void
    {
        $tokenDefinition = new EraName();
        $abbreviatedTokenDefinition = new EraName(abbreviated: true);

        self::assertSame('Common Era', $tokenDefinition->format(new GregorianDate(2025, 3, 21), new English()));
        self::assertSame('CE', $abbreviatedTokenDefinition->format(new GregorianDate(2025, 3, 21), new English()));
    }

    /**
     * Tests parsing era names.
     */
    public function testParse(): void
    {
        $tokenDefinition = new EraName();
        $abbreviatedTokenDefinition = new EraName(abbreviated: true);

        self::assertSame(2, $tokenDefinition->parse('Common Era', Calendar::Gregorian, new English()));
        self::assertSame(3, $tokenDefinition->parse('Hijri', Calendar::Gregorian, new English()));
        self::assertSame(2, $abbreviatedTokenDefinition->parse('CE', Calendar::Gregorian, new English()));
        self::assertSame(3, $abbreviatedTokenDefinition->parse('AH', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects unknown era names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized era name');

        $tokenDefinition = new EraName();
        $tokenDefinition->parse('Unknown', Calendar::Gregorian, new English());
    }
}

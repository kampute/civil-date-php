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
 * Tests localized era-name token rules.
 */
final class EraNameTest extends TestCase
{
    /**
     * Tests formatting era names.
     */
    public function testFormat(): void
    {
        $rule = new EraName();
        $abbreviatedRule = new EraName(abbreviated: true);

        self::assertSame('Common Era', $rule->format(new GregorianDate(2025, 3, 21), new English()));
        self::assertSame('CE', $abbreviatedRule->format(new GregorianDate(2025, 3, 21), new English()));
    }

    /**
     * Tests parsing era names.
     */
    public function testParse(): void
    {
        $rule = new EraName();
        $abbreviatedRule = new EraName(abbreviated: true);

        self::assertSame(2, $rule->parse('Common Era', Calendar::Gregorian, new English()));
        self::assertSame(3, $rule->parse('Hijri', Calendar::Gregorian, new English()));
        self::assertSame(2, $abbreviatedRule->parse('CE', Calendar::Gregorian, new English()));
        self::assertSame(3, $abbreviatedRule->parse('AH', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects unknown era names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized era name');

        $rule = new EraName();
        $rule->parse('Unknown', Calendar::Gregorian, new English());
    }
}

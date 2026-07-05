<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberWord;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized number-word token rules.
 */
final class NumberWordTest extends TestCase
{
    /**
     * Tests formatting cardinal and ordinal number words.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $cardinalRule = new NumberWord('year', ordinal: false);
        $ordinalRule = new NumberWord('day', ordinal: true);

        self::assertSame('two thousand twenty-five', $cardinalRule->format($date, new English()));
        self::assertSame('twenty-first', $ordinalRule->format($date, new English()));
    }

    /**
     * Tests formatting rejects non-integer properties.
     */
    public function testFormatRejectsNonIntegerProperty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not an integer');

        $rule = new NumberWord('calendar', ordinal: false);
        $rule->format(new GregorianDate(2025, 3, 21), new English());
    }

    /**
     * Tests parsing number words.
     */
    public function testParse(): void
    {
        $cardinalRule = new NumberWord('year', ordinal: false);
        $ordinalRule = new NumberWord('day', ordinal: true);

        self::assertSame(2025, $cardinalRule->parse('two thousand twenty-five', Calendar::Gregorian, new English()));
        self::assertSame(21, $ordinalRule->parse('twenty-first', Calendar::Gregorian, new English()));
        self::assertSame(-1, $ordinalRule->parse('last', Calendar::Gregorian, new English()));
        self::assertSame(-2, $ordinalRule->parse('second-to-last', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects invalid number words.
     */
    public function testParseRejectsInvalidValue(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $rule = new NumberWord('month', ordinal: false);
        $rule->parse('invalid', Calendar::Gregorian, new English());
    }

    /**
     * Tests cardinal parsing rejects ordinal words.
     */
    public function testCardinalParseRejectsOrdinalWords(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $rule = new NumberWord('month', ordinal: false);
        $rule->parse('third', Calendar::Gregorian, new English());
    }

    /**
     * Tests ordinal parsing accepts cardinal words.
     */
    public function testOrdinalParseAcceptsCardinalWords(): void
    {
        $rule = new NumberWord('day', ordinal: true);
        $this->assertSame(3, $rule->parse('three', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects digit values.
     */
    public function testParseRejectsDigits(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid year value');

        $rule = new NumberWord('year', ordinal: false);
        $rule->parse('2025', Calendar::Gregorian, new English());
    }
}

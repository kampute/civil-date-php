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
 * Tests localized number-word token definitions.
 */
final class NumberWordTest extends TestCase
{
    /**
     * Tests formatting cardinal and ordinal number words.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $tokenDefinition = new NumberWord('year', ordinal: false);
        $ordinalTokenDefinition = new NumberWord('day', ordinal: true);

        self::assertSame('two thousand twenty-five', $tokenDefinition->format($date, new English()));
        self::assertSame('twenty-first', $ordinalTokenDefinition->format($date, new English()));
    }

    /**
     * Tests formatting rejects non-integer properties.
     */
    public function testFormatRejectsNonIntegerProperty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not an integer');

        $tokenDefinition = new NumberWord('calendar', ordinal: false);
        $tokenDefinition->format(new GregorianDate(2025, 3, 21), new English());
    }

    /**
     * Tests parsing number words.
     */
    public function testParse(): void
    {
        $tokenDefinition = new NumberWord('year', ordinal: false);
        $ordinalTokenDefinition = new NumberWord('day', ordinal: true);

        self::assertSame(2025, $tokenDefinition->parse('two thousand twenty-five', Calendar::Gregorian, new English()));
        self::assertSame(21, $ordinalTokenDefinition->parse('twenty-first', Calendar::Gregorian, new English()));
        self::assertSame(-1, $ordinalTokenDefinition->parse('last', Calendar::Gregorian, new English()));
        self::assertSame(-2, $ordinalTokenDefinition->parse('second-to-last', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects invalid number words.
     */
    public function testParseRejectsInvalidValue(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $tokenDefinition = new NumberWord('month', ordinal: false);
        $tokenDefinition->parse('invalid', Calendar::Gregorian, new English());
    }

    /**
     * Tests cardinal parsing rejects ordinal words.
     */
    public function testCardinalParseRejectsOrdinalWords(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $tokenDefinition = new NumberWord('month', ordinal: false);
        $tokenDefinition->parse('third', Calendar::Gregorian, new English());
    }

    /**
     * Tests ordinal parsing accepts cardinal words.
     */
    public function testOrdinalParseAcceptsCardinalWords(): void
    {
        $tokenDefinition = new NumberWord('day', ordinal: true);
        $this->assertSame(3, $tokenDefinition->parse('three', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects digit values.
     */
    public function testParseRejectsDigits(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid year value');

        $tokenDefinition = new NumberWord('year', ordinal: false);
        $tokenDefinition->parse('2025', Calendar::Gregorian, new English());
    }
}

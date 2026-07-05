<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized number-digit token rules.
 */
final class NumberDigitTest extends TestCase
{
    /**
     * Tests formatting localized digits.
     */
    public function testFormat(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        $rule = new NumberDigit('month', minimumDigits: 2);

        self::assertSame('03', $rule->format($date, new English()));
        self::assertSame('۰۳', $rule->format($date, new Persian()));
    }

    /**
     * Tests formatting rejects non-integer properties.
     */
    public function testFormatRejectsNonIntegerProperty(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('not an integer');

        $rule = new NumberDigit('calendar');
        $rule->format(new GregorianDate(2025, 3, 21), new English());
    }

    /**
     * Tests capture regex matches only localized digits.
     */
    #[DataProvider('captureRegexProvider')]
    public function testCaptureRegex(bool $signed, string $input, int $expected): void
    {
        $rule = new NumberDigit('month', signed: $signed);
        $regex = '~^' . $rule->captureRegex() . '$~u';

        self::assertSame($expected, preg_match($regex, $input));
    }

    /**
     * Provides data for capture regex tests.
     *
     * @return array<string,array{bool,string,int}> Provider data sets.
     */
    public static function captureRegexProvider(): array
    {
        return [
            'Unsigned Latin digits' => [false, '3', 1],
            'Unsigned Persian digits' => [false, '۳', 1],
            'Unsigned Arabic-Indic digits' => [false, '٣', 1],
            'Unsigned English word' => [false, 'three', 0],
            'Unsigned alphanumeric' => [false, '3a', 0],
            'Unsigned negative number' => [false, '-3', 0],
            'Signed Latin digits' => [true, '100', 1],
            'Signed negative Latin digits' => [true, '-100', 1],
            'Signed negative Persian digits' => [true, '-۱۰۰', 1],
            'Signed English words' => [true, 'minus one hundred', 0],
            'Signed alphanumeric' => [true, '-100a', 0],
        ];
    }

    /**
     * Tests parsing localized digits.
     */
    public function testParse(): void
    {
        $rule = new NumberDigit('month');

        self::assertSame(3, $rule->parse('۳', Calendar::Gregorian, new Persian()));
    }

    /**
     * Tests parsing rejects invalid digits.
     */
    public function testParseRejectsInvalidValue(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $rule = new NumberDigit('month');
        $rule->parse('invalid', Calendar::Gregorian, new English());
    }

    /**
     * Tests parsing rejects localized number words.
     */
    public function testParseRejectsNumberWords(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid month value');

        $rule = new NumberDigit('month');
        $rule->parse('three', Calendar::Gregorian, new English());
    }
}

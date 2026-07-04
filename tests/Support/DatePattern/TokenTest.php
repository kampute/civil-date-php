<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Support\DatePattern\Token;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern tokens.
 */
final class TokenTest extends TestCase
{
    /**
     * Tests tokens expose scope, capture, formatting, and parsing behavior.
     */
    public function testTokenBehavior(): void
    {
        $tokenDefinition = new NumberDigit('year');
        $token = new Token(Calendar::Gregorian, $tokenDefinition);
        $locale = new English();

        self::assertSame(Calendar::Gregorian, $token->calendar());
        self::assertSame($tokenDefinition->property(), $token->property());
        self::assertSame($tokenDefinition->captureRegex(), $token->captureRegex());
        self::assertSame('2025', $token->format(new GregorianDate(2025, 3, 21), $locale));
        self::assertSame('2025', $token->format(new JalaliDate(1404, 1, 1), $locale));
        self::assertSame(1403, $token->parse('1403', Calendar::Gregorian, $locale));
    }

    /**
     * Tests unscoped tokens format the provided date without calendar conversion.
     */
    public function testUnscopedTokenFormatsProvidedDate(): void
    {
        $token = new Token(null, new NumberDigit('year'));

        self::assertSame('1404', $token->format(new JalaliDate(1404, 1, 1), new English()));
    }
}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Localization\LocaleRegistry;
use Kampute\CivilDate\Support\DatePattern\Literal;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern literals.
 */
final class LiteralTest extends TestCase
{
    /**
     * Tests literals own their formatting and capture behavior.
     */
    public function testLiteralBehavior(): void
    {
        $literal = new Literal('~/.');
        $locale = LocaleRegistry::default();

        self::assertSame('~/.', $literal->text());
        self::assertSame('~/.', $literal->format(new GregorianDate(2025, 3, 21), $locale));
        self::assertSame('\\~/\\.', $literal->captureRegex());
        self::assertSame(1, preg_match('~^'.$literal->captureRegex().'$~u', '~/.'));
        self::assertSame(0, preg_match('~^'.$literal->captureRegex().'$~u', '~/x'));
    }

    /**
     * Tests appending literal text returns a new literal.
     */
    public function testAppendedReturnsNewLiteral(): void
    {
        $literal = new Literal('Date: ');
        $appended = $literal->appended('2025');

        self::assertNotSame($literal, $appended);
        self::assertSame('Date: ', $literal->text());
        self::assertSame('Date: 2025', $appended->text());
    }
}

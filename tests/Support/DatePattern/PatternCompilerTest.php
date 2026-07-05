<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\PatternCompiler;
use Kampute\CivilDate\Support\DatePattern\TokenRegistry;
use Kampute\CivilDate\Support\DatePattern\TokenRule;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern compilation.
 */
final class PatternCompilerTest extends TestCase
{
    /**
     * Tests compiled patterns format dates.
     */
    public function testCompileCreatesFormattedPatterns(): void
    {
        $result = PatternCompiler::shared()
            ->compile('Y/m/d [gregorian:Y-m-d]')
            ->format(new GregorianDate(2025, 3, 21), new English());

        self::assertSame('2025/03/21 2025-03-21', $result);
    }

    /**
     * Tests compilation creates typed matches.
     */
    public function testCompileCreatesTypedMatches(): void
    {
        $matches = PatternCompiler::shared()
            ->compile('Y/m/d [gregorian:Y-m-d]')
            ->match('۱۴۰۲/۰۶/۳۱ 2023-09-22');

        self::assertNotFalse($matches);

        $matchedValues = [];
        foreach ($matches as $match) {
            $matchedValues[] = [$match[0]->property(), $match[1]];
        }

        self::assertSame(
            [
                ['year', '۱۴۰۲'],
                ['month', '۰۶'],
                ['day', '۳۱'],
                ['year', '2023'],
                ['month', '09'],
                ['day', '22'],
            ],
            $matchedValues,
        );
    }

    /**
     * Tests compilation resolves explicit calendar scopes.
     */
    public function testCompileResolvesCalendarScopes(): void
    {
        $matches = PatternCompiler::shared()
            ->compile('[gregorian:Y]-[jalali:Y]')
            ->match('2025-1404');

        self::assertNotFalse($matches);
        self::assertSame('Gregorian', $matches[0][0]->calendarScope()?->name);
        self::assertSame('Jalali', $matches[1][0]->calendarScope()?->name);
    }

    /**
     * Tests quoted text and escapes are merged into compiled text elements.
     */
    public function testQuotedTextAndEscapesAreCompiledAsText(): void
    {
        $compiledPattern = PatternCompiler::shared()->compile("'Date: 'Y-\\m-d ' [text]'");

        self::assertSame('Date: 2025-m-21  [text]', $compiledPattern->format(new GregorianDate(2025, 3, 21), new English()));
        self::assertNotFalse($compiledPattern->match('Date: 2025-m-21  [text]'));
    }

    /**
     * Tests compilation anchors the pattern and escapes literal regular-expression characters.
     */
    public function testCompileEscapesTextAndRequiresAFullMatch(): void
    {
        $compiledPattern = PatternCompiler::shared()->compile('Y.m');

        self::assertNotFalse($compiledPattern->match('2025.03'));
        self::assertFalse($compiledPattern->match('2025x03'));
        self::assertFalse($compiledPattern->match(' 2025.03'));
        self::assertFalse($compiledPattern->match('2025.03/'));
    }

    /**
     * Tests compiler cache uses the token registry revision.
     */
    public function testCompileUsesRegistryRevision(): void
    {
        $tokenRegistry = new TokenRegistry();
        $compiler = new PatternCompiler($tokenRegistry);
        $initialRevision = $compiler->revision();

        $compiledPattern = $compiler->compile('X');
        self::assertFalse($compiledPattern->match('2025'));

        $tokenRegistry->register('X', new NumberDigit('year'));
        self::assertSame($initialRevision + 1, $compiler->revision());

        $compiledPattern = $compiler->compile('X');
        self::assertNotFalse($compiledPattern->match('2025'));
    }

    /**
     * Tests formatting-only rules can format but cannot parse.
     */
    public function testFormattingOnlyRuleFormatsAndRejectsParsing(): void
    {
        $tokenRegistry = new TokenRegistry();
        $tokenRegistry->register('X', self::formattingOnlyRule());
        $compiledPattern = (new PatternCompiler($tokenRegistry))->compile('Y-X');

        self::assertSame('2025-custom', $compiledPattern->format(new GregorianDate(2025, 3, 21), new English()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date-pattern token "X" cannot be used for parsing.');

        $compiledPattern->match('2025-custom');
    }

    /**
     * Tests calendar date formatting can use formatting-only shared token rules.
     */
    public function testCalendarDateFormatAcceptsFormattingOnlyRule(): void
    {
        TokenRegistry::shared()->register('x', self::formattingOnlyRule());

        try {
            self::assertSame('custom', (new GregorianDate(2025, 3, 21))->format('x'));
        } finally {
            TokenRegistry::shared()->remove('x');
        }
    }

    /**
     * Tests malformed patterns are rejected.
     */
    #[DataProvider('malformedPatternProvider')]
    public function testRejectsMalformedPatterns(string $pattern, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        PatternCompiler::shared()->compile($pattern);
    }

    /**
     * Provides malformed patterns.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function malformedPatternProvider(): array
    {
        return [
            'malformed scope without colon' => ['[gregorian Y-m-d]', 'Malformed scope'],
            'empty scope' => ['[:Y/m/d]', 'Invalid scope'],
            'unsupported scope' => ['[unknown:Y/m/d]', 'Unsupported calendar scope'],
            'unclosed scope' => ['[gregorian:Y-m-d', 'Unclosed scope'],
            'nested scope' => ['[gregorian:Y-[jalali:m]-d]', 'Nested calendar scopes are not supported'],
            'unmatched closing bracket' => ['Y/m/d]', 'Unmatched closing bracket'],
            'unclosed single quote' => ["Y 'month' m 'day", 'Unclosed quoted literal'],
            'unclosed double quote' => ['Y "month" m "day', 'Unclosed quoted literal'],
        ];
    }

    /**
     * Returns a formatting-only token rule.
     *
     * @return TokenRule Formatting-only token rule.
     */
    private static function formattingOnlyRule(): TokenRule
    {
        return new class () implements TokenRule {
            /**
             * Formats a fixed custom token value.
             *
             * @param CalendarDate $date Date being formatted.
             * @param Locale $locale Locale definition.
             *
             * @return string Formatted token value.
             */
            public function format(CalendarDate $date, Locale $locale): string
            {
                return 'custom';
            }
        };
    }
}

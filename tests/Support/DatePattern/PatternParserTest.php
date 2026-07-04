<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Support\DatePattern\Literal;
use Kampute\CivilDate\Support\DatePattern\PatternParser;
use Kampute\CivilDate\Support\DatePattern\Segment;
use Kampute\CivilDate\Support\DatePattern\Token;
use Kampute\CivilDate\Support\DatePattern\TokenRegistry;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern parsing.
 */
final class PatternParserTest extends TestCase
{
    /**
     * Tests parsing produces semantic tokens and literals.
     */
    public function testParseProducesSemanticSegments(): void
    {
        self::assertSegments([
            ['token', null, 'year'],
            ['literal', '/'],
            ['token', null, 'month'],
            ['literal', '/'],
            ['token', null, 'day'],
        ], PatternParser::shared()->parse('Y/m/d'));
    }

    /**
     * Tests parsing resolves explicit calendar scopes.
     */
    public function testParseResolvesCalendarScopes(): void
    {
        self::assertSegments([
            ['token', 'Gregorian', 'year'],
            ['literal', '-'],
            ['token', 'Jalali', 'year'],
        ], PatternParser::shared()->parse('[gregorian:Y]-[jalali:Y]'));
    }

    /**
     * Tests quoted literals and escapes are merged.
     */
    public function testQuotedLiteralsAndEscapesAreMerged(): void
    {
        self::assertSegments([
            ['literal', 'Date: '],
            ['token', null, 'year'],
            ['literal', '-m-'],
            ['token', null, 'day'],
            ['literal', '  [literal]'],
        ], PatternParser::shared()->parse("'Date: 'Y-\\m-d ' [literal]'"));
    }

    /**
     * Tests parser cache uses the token registry revision.
     */
    public function testParseUsesTokenRegistryRevision(): void
    {
        $tokenRegistry = new TokenRegistry();
        $parser = new PatternParser($tokenRegistry);

        self::assertSegments([
            ['literal', 'X'],
        ], $parser->parse('X'));

        $tokenRegistry->register('X', new NumberDigit('year'));

        self::assertSame(1, $parser->revision());
        self::assertSegments([
            ['token', null, 'year'],
        ], $parser->parse('X'));

        $tokenRegistry->remove('X');

        self::assertSame(2, $parser->revision());
        self::assertSegments([
            ['literal', 'X'],
        ], $parser->parse('X'));
    }

    /**
     * Tests malformed patterns are rejected.
     */
    #[DataProvider('malformedPatternProvider')]
    public function testRejectsMalformedPatterns(string $pattern, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        PatternParser::shared()->parse($pattern);
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
     * Asserts parsed segments against their expected type and values.
     *
     * @param list<array{'literal',string}|array{'token',string|null,string}> $expected Expected segments.
     * @param list<Segment> $actual Parsed segments.
     */
    private static function assertSegments(array $expected, array $actual): void
    {
        self::assertCount(count($expected), $actual);

        foreach ($expected as $index => $expectedSegment) {
            $actualSegment = $actual[$index];

            if ($expectedSegment[0] === 'literal') {
                self::assertInstanceOf(Literal::class, $actualSegment);
                self::assertSame($expectedSegment[1], $actualSegment->text());
                continue;
            }

            self::assertInstanceOf(Token::class, $actualSegment);
            self::assertSame($expectedSegment[1], $actualSegment->calendar()?->name);
            self::assertSame($expectedSegment[2], $actualSegment->property());
        }
    }
}

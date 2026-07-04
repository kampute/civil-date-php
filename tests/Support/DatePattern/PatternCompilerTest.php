<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use Kampute\CivilDate\Support\DatePattern\PatternCompiler;
use Kampute\CivilDate\Support\DatePattern\PatternParser;
use Kampute\CivilDate\Support\DatePattern\TokenRegistry;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern compilation.
 */
final class PatternCompilerTest extends TestCase
{
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
     * Tests compilation anchors the pattern and escapes literal regular-expression characters.
     */
    public function testCompileEscapesLiteralsAndRequiresAFullMatch(): void
    {
        $compiledPattern = PatternCompiler::shared()->compile('Y.m');

        self::assertNotFalse($compiledPattern->match('2025.03'));
        self::assertFalse($compiledPattern->match('2025x03'));
        self::assertFalse($compiledPattern->match(' 2025.03'));
        self::assertFalse($compiledPattern->match('2025.03/'));
    }

    /**
     * Tests compiler cache uses the parser revision.
     */
    public function testCompileUsesParserRevision(): void
    {
        $tokenRegistry = new TokenRegistry();
        $compiler = new PatternCompiler(new PatternParser($tokenRegistry));
        $initialRevision = $compiler->revision();

        $compiledPattern = $compiler->compile('X');
        self::assertFalse($compiledPattern->match('2025'));

        $tokenRegistry->register('X', new NumberDigit('year'));
        self::assertSame($initialRevision + 1, $compiler->revision());

        $compiledPattern = $compiler->compile('X');
        self::assertNotFalse($compiledPattern->match('2025'));
    }
}

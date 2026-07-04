<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;

/**
 * Parses date-pattern strings used by date parsing and formatting.
 *
 * A pattern combines date-field tokens with text that must appear as written.
 * Known token symbols select date fields. Other characters are treated as
 * literal text. Single or double quotes can group literal text, and a backslash
 * escapes the following character.
 *
 * A calendar scope is a bracketed sub-pattern evaluated in another calendar.
 * Scope syntax is `[Calendar:pattern]`, for example `[Gregorian:Y-m-d]`.
 * Scope names match `Calendar` case names case-insensitively. Scopes cannot be
 * nested.
 *
 * @see PatternCompiler
 * @see TokenRegistry
 * @see \Kampute\CivilDate\CalendarDate::format()
 */
final class PatternParser
{
    /**
     * Shared parser for date parsing and formatting.
     *
     * @var self|null
     */
    private static ?self $shared = null;

    /**
     * Parsed segments indexed by source pattern.
     *
     * @var array<string,list<Segment>>
     */
    private array $cache = [];

    /**
     * Token registry.
     *
     * @var TokenRegistry
     */
    private readonly TokenRegistry $tokens;

    /**
     * Creates a pattern parser.
     *
     * @param TokenRegistry|null $tokens Optional token registry. If not provided, a default registry is used.
     *
     * @see TokenRegistry::shared()
     */
    public function __construct(?TokenRegistry $tokens = null)
    {
        $this->tokens = $tokens ?? TokenRegistry::shared();
    }

    /**
     * Returns the shared parser.
     *
     * @return self Pattern parser.
     */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /**
     * Parses a date pattern.
     *
     * @param string $pattern Pattern to parse.
     *
     * @return list<Segment> Parsed segments.
     *
     * @throws InvalidArgumentException If the pattern syntax is invalid.
     *
     * @see PatternCompiler::compile()
     * @see Segment
     */
    public function parse(string $pattern): array
    {
        $cacheKey = $this->cacheKey($pattern);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $segments = [];
        $this->appendSegments($pattern, null, $segments);
        return $this->cache[$cacheKey] = $segments;
    }

    /**
     * Returns the current parser revision.
     *
     * @return int Parser revision.
     */
    public function revision(): int
    {
        return $this->tokens->revision();
    }

    /**
     * Returns the cache key for a pattern.
     *
     * @param string $pattern Pattern to parse.
     *
     * @return string Cache key.
     */
    private function cacheKey(string $pattern): string
    {
        return $this->revision() . "\0" . $pattern;
    }

    /**
     * Appends parsed segments using the specified scope.
     *
     * @param string $pattern Pattern to parse.
     * @param Calendar|null $calendar Calendar for tokens in this pattern.
     * @param list<Segment> $segments Destination segment list.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the pattern syntax is invalid.
     */
    private function appendSegments(string $pattern, ?Calendar $calendar, array &$segments): void
    {
        $literal = '';
        $length = strlen($pattern);

        for ($i = 0; $i < $length; ++$i) {
            $symbol = $pattern[$i];

            if ($symbol === '[') {
                $block = $this->readScopeBlock($pattern, $i);
                $this->flushLiteral($literal, $segments);
                $this->appendSegments($block['body'], self::calendarForScope($block['scope']), $segments);
                $i = $block['endIndex'];
                continue;
            }

            if ($symbol === ']') {
                throw new InvalidArgumentException("Unmatched closing bracket in date pattern at index {$i}. Escape it or put it inside a quoted literal.");
            }

            if ($symbol === '\\') {
                $literal .= $i + 1 >= $length ? '\\' : $pattern[$i++ + 1];
                continue;
            }

            if ($symbol === '"' || $symbol === "'") {
                $closingQuoteIndex = strpos($pattern, $symbol, $i + 1);
                if ($closingQuoteIndex === false) {
                    throw new InvalidArgumentException("Unclosed quoted literal starting at index {$i}.");
                }

                $literal .= substr($pattern, $i + 1, $closingQuoteIndex - $i - 1);
                $i = $closingQuoteIndex;
                continue;
            }

            $definition = $this->tokens->find($symbol);
            if ($definition !== null) {
                $this->flushLiteral($literal, $segments);
                $segments[] = new Token($calendar, $definition);
                continue;
            }

            $literal .= $symbol;
        }

        $this->flushLiteral($literal, $segments);
    }

    /**
     * Flushes a literal buffer into the segment list.
     *
     * @param string $literal Literal buffer, cleared after flushing.
     * @param list<Segment> $segments Destination segment list.
     *
     * @return void
     */
    private function flushLiteral(string &$literal, array &$segments): void
    {
        if ($literal === '') {
            return;
        }

        $lastIndex = count($segments) - 1;
        if ($lastIndex >= 0 && $segments[$lastIndex] instanceof Literal) {
            $segments[$lastIndex] = $segments[$lastIndex]->appended($literal);
        } else {
            $segments[] = new Literal($literal);
        }

        $literal = '';
    }

    /**
     * Reads a scope block beginning at the given pattern index.
     *
     * @param string $pattern Pattern containing the scope block.
     * @param int $index Opening-bracket index.
     *
     * @return array{scope:string,body:string,endIndex:int} Parsed scope block.
     *
     * @throws InvalidArgumentException If the scope block syntax is invalid.
     */
    private function readScopeBlock(string $pattern, int $index): array
    {
        $colonIndex = strpos($pattern, ':', $index + 1);
        $closingBracketIndex = strpos($pattern, ']', $index + 1);

        if ($colonIndex === false || ($closingBracketIndex !== false && $closingBracketIndex < $colonIndex)) {
            throw new InvalidArgumentException("Malformed scope at index {$index}. Expected [scope:...].");
        }

        $scope = trim(substr($pattern, $index + 1, $colonIndex - $index - 1));
        if ($scope === '') {
            throw new InvalidArgumentException("Invalid scope \"{$scope}\" at index {$index}.");
        }

        $length = strlen($pattern);
        $bodyStart = $colonIndex + 1;

        for ($i = $bodyStart; $i < $length; ++$i) {
            $symbol = $pattern[$i];

            if ($symbol === '\\') {
                ++$i;
                continue;
            }

            if ($symbol === '"' || $symbol === "'") {
                $closingQuoteIndex = strpos($pattern, $symbol, $i + 1);
                if ($closingQuoteIndex === false) {
                    throw new InvalidArgumentException("Unclosed quoted literal starting at index {$i}.");
                }

                $i = $closingQuoteIndex;
                continue;
            }

            if ($symbol === '[') {
                throw new InvalidArgumentException("Nested calendar scopes are not supported at index {$i}.");
            }

            if ($symbol === ']') {
                return [
                    'scope' => $scope,
                    'body' => substr($pattern, $bodyStart, $i - $bodyStart),
                    'endIndex' => $i,
                ];
            }
        }

        throw new InvalidArgumentException("Unclosed scope starting at index {$index}.");
    }

    /**
     * Resolves an explicit pattern scope to a calendar.
     *
     * @param string $scope Pattern scope.
     *
     * @return Calendar Resolved calendar.
     *
     * @throws InvalidArgumentException If the scope is unsupported.
     */
    private static function calendarForScope(string $scope): Calendar
    {
        foreach (Calendar::cases() as $calendar) {
            if (strcasecmp($calendar->name, $scope) === 0) {
                return $calendar;
            }
        }

        throw new InvalidArgumentException("Unsupported calendar scope \"{$scope}\".");
    }
}

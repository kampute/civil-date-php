<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;

/**
 * Compiles date-pattern strings.
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
 * @see CompiledPattern
 * @see TokenRegistry
 * @see \Kampute\CivilDate\CalendarDate::format()
 * @see \Kampute\CivilDate\CalendarDate::parse()
 */
final class PatternCompiler
{
    /**
     * Shared compiler for date patterns.
     *
     * @var self|null
     */
    private static ?self $shared = null;

    /**
     * Compiled patterns indexed by source pattern.
     *
     * @var array<string,CompiledPattern>
     */
    private array $cache = [];

    /**
     * Token registry.
     *
     * @var TokenRegistry
     */
    private readonly TokenRegistry $tokens;

    /**
     * Creates a pattern compiler.
     *
     * @param TokenRegistry|null $tokens Optional token registry. If not provided, the shared registry is used.
     *
     * @see TokenRegistry::shared()
     */
    public function __construct(?TokenRegistry $tokens = null)
    {
        $this->tokens = $tokens ?? TokenRegistry::shared();
    }

    /**
     * Returns the shared pattern compiler.
     *
     * @return self Pattern compiler.
     */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /**
     * Compiles a date pattern.
     *
     * @param string $pattern Pattern to compile.
     *
     * @return CompiledPattern Compiled pattern.
     *
     * @throws InvalidArgumentException If the pattern syntax is invalid.
     *
     * @see CompiledPattern
     */
    public function compile(string $pattern): CompiledPattern
    {
        $cacheKey = $this->cacheKey($pattern);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $elements = [];
        $parseParts = [];
        $this->appendElements($pattern, null, $elements, $parseParts);
        return $this->cache[$cacheKey] = new CompiledPattern($elements, $parseParts);
    }

    /**
     * Returns the current compiler revision.
     *
     * @return int Compiler revision.
     */
    public function revision(): int
    {
        return $this->tokens->revision();
    }

    /**
     * Returns the cache key for a pattern.
     *
     * @param string $pattern Pattern to compile.
     *
     * @return string Cache key.
     */
    private function cacheKey(string $pattern): string
    {
        return $this->revision() . "\0" . $pattern;
    }

    /**
     * Appends compiled pattern elements using the specified scope.
     *
     * @param string $pattern Pattern to compile.
     * @param Calendar|null $calendar Calendar for tokens in this pattern.
     * @param list<PatternElement> $elements Destination element list.
     * @param list<string|array{symbol:string,calendar:Calendar|null,rule:TokenRule}> $parseParts Destination parse part list.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the pattern syntax is invalid.
     */
    private function appendElements(string $pattern, ?Calendar $calendar, array &$elements, array &$parseParts): void
    {
        $text = '';
        $length = strlen($pattern);

        for ($i = 0; $i < $length; ++$i) {
            $symbol = $pattern[$i];

            if ($symbol === '[') {
                $block = $this->readScopeBlock($pattern, $i);
                $this->flushText($text, $elements, $parseParts);
                $this->appendElements($block['body'], self::calendarForScope($block['scope']), $elements, $parseParts);
                $i = $block['endIndex'];
                continue;
            }

            if ($symbol === ']') {
                throw new InvalidArgumentException("Unmatched closing bracket in date pattern at index {$i}. Escape it or put it inside a quoted literal.");
            }

            if ($symbol === '\\') {
                $text .= $i + 1 >= $length ? '\\' : $pattern[$i++ + 1];
                continue;
            }

            if ($symbol === '"' || $symbol === "'") {
                $closingQuoteIndex = strpos($pattern, $symbol, $i + 1);
                if ($closingQuoteIndex === false) {
                    throw new InvalidArgumentException("Unclosed quoted literal starting at index {$i}.");
                }

                $text .= substr($pattern, $i + 1, $closingQuoteIndex - $i - 1);
                $i = $closingQuoteIndex;
                continue;
            }

            $rule = $this->tokens->find($symbol);
            if ($rule !== null) {
                $this->flushText($text, $elements, $parseParts);
                $elements[] = new TokenElement($symbol, $calendar, $rule);
                $parseParts[] = [
                    'symbol' => $symbol,
                    'calendar' => $calendar,
                    'rule' => $rule,
                ];
                continue;
            }

            $text .= $symbol;
        }

        $this->flushText($text, $elements, $parseParts);
    }

    /**
     * Flushes a text buffer into the element list.
     *
     * @param string $text Text buffer, cleared after flushing.
     * @param list<PatternElement> $elements Destination element list.
     * @param list<string|array{symbol:string,calendar:Calendar|null,rule:TokenRule}> $parseParts Destination parse part list.
     *
     * @return void
     */
    private function flushText(string &$text, array &$elements, array &$parseParts): void
    {
        if ($text === '') {
            return;
        }

        $lastIndex = count($elements) - 1;
        if ($lastIndex >= 0 && $elements[$lastIndex] instanceof TextElement) {
            $elements[$lastIndex] = $elements[$lastIndex]->appended($text);
        } else {
            $elements[] = new TextElement($text);
        }

        $lastPartIndex = count($parseParts) - 1;
        if ($lastPartIndex >= 0 && is_string($parseParts[$lastPartIndex])) {
            $parseParts[$lastPartIndex] .= preg_quote($text, '~');
        } else {
            $parseParts[] = preg_quote($text, '~');
        }

        $text = '';
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

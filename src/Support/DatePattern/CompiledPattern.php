<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents a compiled date pattern.
 *
 * @see PatternCompiler::compile()
 * @see PatternElement
 */
final class CompiledPattern
{
    /**
     * Full-match regular expression, built when parsing is first requested.
     *
     * @var string|null
     */
    private ?string $regex = null;

    /**
     * Parse captures in matching order, built when parsing is first requested.
     *
     * @var list<ParseCapture>|null
     */
    private ?array $captures = null;

    /**
     * Creates a compiled date pattern.
     *
     * @param list<PatternElement> $elements Compiled pattern elements.
     * @param list<string|array{symbol:string,calendar:Calendar|null,rule:TokenRule}> $parseParts Regular-expression fragments and token parse parts in parse order.
     */
    public function __construct(
        private readonly array $elements,
        private readonly array $parseParts
    ) {
    }

    /**
     * Formats a date with this pattern.
     *
     * @param CalendarDate $date Date to format.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted date.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        $result = '';
        foreach ($this->elements as $element) {
            $result .= $element->format($date, $locale);
        }

        return $result;
    }

    /**
     * Matches input text and returns captured token values.
     *
     * @param string $input Input text to match.
     *
     * @return list<array{0:ParseCapture,1:string}>|false Matched captures and their values, or false if no match.
     *
     * @see ParseCapture::parse()
     */
    public function match(string $input): array|false
    {
        [$regex, $captures] = $this->parsePattern();
        if (preg_match($regex, $input, $matches) !== 1) {
            return false;
        }

        $result = [];
        foreach ($captures as $index => $capture) {
            $value = $matches[$index + 1] ?? '';
            if ($value !== '') {
                $result[] = [$capture, $value];
            }
        }
        return $result;
    }

    /**
     * Returns the cached parse regex and captures.
     *
     * @return array{0:string,1:list<ParseCapture>} Parse regex and captures.
     */
    private function parsePattern(): array
    {
        if ($this->regex !== null && $this->captures !== null) {
            return [$this->regex, $this->captures];
        }

        $regex = '';
        $captures = [];
        foreach ($this->parseParts as $part) {
            if (is_string($part)) {
                $regex .= $part;
                continue;
            }

            $capture = $this->captureFor($part['symbol'], $part['calendar'], $part['rule']);
            $regex .= $capture->regex();
            $captures[] = $capture;
        }

        $this->regex ??= '~^' . $regex . '$~iu';
        $this->captures = $captures;
        return [$this->regex, $this->captures];
    }

    /**
     * Returns a parse capture for a token parse part.
     *
     * @param string $symbol Pattern token symbol.
     * @param Calendar|null $calendarScope Explicit calendar scope, or null when unscoped.
     * @param TokenRule $rule Token rule.
     *
     * @return ParseCapture Parse capture.
     *
     * @throws InvalidArgumentException If the token rule does not support parsing.
     */
    private function captureFor(string $symbol, ?Calendar $calendarScope, TokenRule $rule): ParseCapture
    {
        if (!$rule instanceof ParsableTokenRule) {
            throw new InvalidArgumentException("Date-pattern token \"{$symbol}\" cannot be used for parsing.");
        }

        return new ParseCapture($calendarScope, $rule);
    }
}

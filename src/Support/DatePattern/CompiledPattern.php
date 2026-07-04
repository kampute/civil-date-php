<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

/**
 * Represents a date pattern compiled for parsing.
 *
 * @see PatternCompiler::compile()
 * @see Token
 */
final class CompiledPattern
{
    /**
     * Creates a compiled date pattern.
     *
     * @param string $regex Full-match regular expression.
     * @param list<Token> $captures Capturing tokens in matching order.
     */
    public function __construct(
        private readonly string $regex,
        private readonly array $captures,
    ) {
    }

    /**
     * Matches input text and returns captured token values.
     *
     * @param string $input Input text to match.
     *
     * @return list<array{0:Token,1:string}>|false Matched tokens and their values, or false if no match.
     *
     * @see Token::parse()
     */
    public function match(string $input): array|false
    {
        if (preg_match($this->regex, $input, $matches) !== 1) {
            return false;
        }

        $result = [];
        foreach ($this->captures as $index => $token) {
            $value = $matches[$index + 1] ?? null;
            if ($value !== null && $value !== '') {
                $result[] = [$token, $value];
            }
        }
        return $result;
    }
}

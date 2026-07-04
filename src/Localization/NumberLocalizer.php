<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization;

use InvalidArgumentException;
use Kampute\CivilDate\Localization\Numbers\NumberForm;
use Kampute\CivilDate\Localization\Numbers\NumberParseResult;

/**
 * Formats and parses localized integer representations.
 *
 * Implementations provide language-specific number words. The base class
 * handles digit padding and parsing normalized Latin digits.
 *
 * @see Locale::numberLocalizer()
 */
abstract class NumberLocalizer
{
    /**
     * Text normalizer applied before parsing numbers.
     *
     * @var TextNormalizer
     */
    protected readonly TextNormalizer $textNormalizer;

    /**
     * Creates a number localizer.
     *
     * @param TextNormalizer $textNormalizer Text normalizer applied before parsing.
     */
    public function __construct(TextNormalizer $textNormalizer)
    {
        $this->textNormalizer = $textNormalizer;
    }

    /**
     * Formats an integer using Latin digits.
     *
     * @param int $value Integer value to format.
     * @param int $minimumDigits Minimum digit width excluding the sign.
     *
     * @return string Latin digit representation.
     *
     * @throws InvalidArgumentException If the minimum digit width is less than one.
     *
     * @see NumberLocalizer::parseDigits()
     */
    public function formatDigits(int $value, int $minimumDigits = 1): string
    {
        if ($minimumDigits < 1) {
            throw new InvalidArgumentException(
                "Minimum digit width must be at least 1, got {$minimumDigits}."
            );
        }

        $digits = ltrim((string) $value, '-');
        if ($minimumDigits > strlen($digits)) {
            $digits = str_pad($digits, $minimumDigits, '0', STR_PAD_LEFT);
        }

        return $value < 0 ? '-' . $digits : $digits;
    }

    /**
     * Formats an integer as localized cardinal words.
     *
     * @param int $value Integer value to format.
     *
     * @return string Localized cardinal word representation.
     *
     * @see NumberLocalizer::parseCardinal()
     */
    abstract public function formatCardinal(int $value): string;

    /**
     * Formats a non-zero integer as localized ordinal words.
     *
     * Positive values are counted from the start (first, second, ...) and negative
     * values are counted from the end (..., second-to-last, last) of a sequence.
     *
     * Zero is not a valid ordinal value.
     *
     * @param int $value Non-zero integer value to format.
     *
     * @return string Localized ordinal word representation.
     *
     * @throws InvalidArgumentException If the value is zero.
     *
     * @see NumberLocalizer::parseOrdinal()
     */
    abstract public function formatOrdinal(int $value): string;

    /**
     * Parses an integer from Latin digits.
     *
     * @param string $text Digit representation to parse.
     *
     * @return int|null Parsed integer, or null when invalid.
     *
     * @see NumberLocalizer::formatDigits()
     * @see NumberLocalizer::parse()
     */
    public function parseDigits(string $text): ?int
    {
        return preg_match('/^\s*[+-]?[0-9]+\s*$/', $text) === 1 ? (int) $text : null;
    }

    /**
     * Parses an integer from localized cardinal words.
     *
     * @param string $text Cardinal words to parse.
     *
     * @return int|null Parsed integer, or null when invalid or not cardinal.
     *
     * @see NumberLocalizer::parseWords()
     * @see NumberLocalizer::formatCardinal()
     */
    public function parseCardinal(string $text): ?int
    {
        $result = $this->parseWords($text);
        return $result->form() === NumberForm::Cardinal
            ? $result->value()
            : null;
    }

    /**
     * Parses an integer from localized ordinal words.
     *
     * @param string $text Ordinal words to parse.
     *
     * @return int|null Parsed integer, or null when invalid or not ordinal.
     *
     * @see NumberLocalizer::parseWords()
     * @see NumberLocalizer::formatOrdinal()
     */
    public function parseOrdinal(string $text): ?int
    {
        $result = $this->parseWords($text);
        return $result->form() === NumberForm::Ordinal
            ? $result->value()
            : null;
    }

    /**
     * Parses an integer from localized cardinal or ordinal words.
     *
     * @param string $text Number words to parse.
     *
     * @return NumberParseResult Parse result.
     *
     * @see NumberLocalizer::parseCardinal()
     * @see NumberLocalizer::parseOrdinal()
     * @see NumberLocalizer::parse()
     */
    abstract public function parseWords(string $text): NumberParseResult;

    /**
     * Parses an integer from localized digits or words.
     *
     * Digit parsing is tried before word parsing.
     *
     * @param string $text Number representation to parse.
     *
     * @return NumberParseResult Parse result.
     *
     * @see NumberLocalizer::parseDigits()
     * @see NumberLocalizer::parseWords()
     */
    public function parse(string $text): NumberParseResult
    {
        $value = $this->parseDigits($text);
        if ($value !== null) {
            return NumberParseResult::success($value, NumberForm::Digits);
        }

        return $this->parseWords($text);
    }
}

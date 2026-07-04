<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Localization\NumberLocalizer;
use Kampute\CivilDate\Localization\Numbers\NumberParseFailureReason;
use Kampute\CivilDate\Localization\Numbers\NumberForm;
use Kampute\CivilDate\Localization\Numbers\NumberParseResult;
use Kampute\CivilDate\Localization\TextNormalizer;

/**
 * Converts integers to and from English number words.
 */
class EnglishNumerals extends NumberLocalizer
{
    /**
     * English word used as a negative sign before cardinal number words.
     *
     * @var string
     */
    private const NEGATIVE = 'minus';

    /**
     * English word for zero.
     *
     * @var string
     */
    private const ZERO = 'zero';

    /**
     * English cardinal words for ones values, indexed by their numeric value.
     *
     * @var array<int,string>
     */
    private const ONES = [
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
    ];

    /**
     * English cardinal words for teen values, indexed by their numeric value.
     *
     * @var array<int,string>
     */
    private const TEENS = [
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
    ];

    /**
     * English tens words for tens values, indexed by their numeric value.
     *
     * @var array<int,string>
     */
    private const TENS = [
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety',
    ];

    /**
     * English word for the hundreds multiplier.
     *
     * @var string
     */
    private const HUNDRED = 'hundred';

    /**
     * English group names for large number values (thousands and above),
     * indexed by their numeric value in descending order.
     *
     * @var array<int,string>
     */
    private const GROUPS = [
        1000000000000000000 => 'quintillion',
        1000000000000000 => 'quadrillion',
        1000000000000 => 'trillion',
        1000000000 => 'billion',
        1000000 => 'million',
        1000 => 'thousand',
    ];

    /**
     * English conjunction used to separate hundreds from tens and ones.
     *
     * @var string
     */
    private const CONJUNCTION = 'and';

    /**
     * Suffix appended to regular English cardinal forms to form ordinals.
     *
     * @var string
     */
    private const ORDINAL_SUFFIX = 'th';

    /**
     * English phrases (represented as an array of words) that indicate ordinal
     * values are counted from the end of a sequence.
     *
     * @var array<array<string>>
     */
    private const ORDINAL_REVERSALS = [
        ['from', 'last'],
        ['to', 'last'],
    ];

    /**
     * English word for the last ordinal position.
     *
     * @var string
     */
    private const LAST_ORDINAL = 'last';

    /**
     * English irregular ordinal forms that do not follow the regular pattern
     * of appending the ordinal suffix, indexed by their numeric value.
     *
     * @var array<int,string>
     */
    private const IRREGULAR_ORDINALS = [
        1 => 'first',
        2 => 'second',
        3 => 'third',
        5 => 'fifth',
        8 => 'eighth',
        9 => 'ninth',
        12 => 'twelfth',
        20 => 'twentieth',
        30 => 'thirtieth',
        40 => 'fortieth',
        50 => 'fiftieth',
        60 => 'sixtieth',
        70 => 'seventieth',
        80 => 'eightieth',
        90 => 'ninetieth',
    ];

    /**
     * Bit flag for the ones place (values 1-9) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_ONES = 1;

    /**
     * Bit flag for the teens place (values 10-19) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_TEEN = 2;

    /**
     * Bit flag for the tens place (values 20-90) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_TENS = 4;

    /**
     * Bit flag for the hundreds place within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_HUNDREDS = 8;

    /**
     * English cardinal values indexed by normalized word.
     *
     * @var array<string,int>|null
     */
    private static ?array $cardinals = null;

    /**
     * English irregular ordinal values indexed by normalized word.
     *
     * @var array<string,int>|null
     */
    private static ?array $irregularOrdinals = null;

    /**
     * Creates an English numeral localizer.
     *
     * @param TextNormalizer|null $textNormalizer Text normalizer, or null to use English normalization.
     */
    public function __construct(?TextNormalizer $textNormalizer = null)
    {
        parent::__construct($textNormalizer ?? new EnglishTextNormalizer());
    }

    // ================================================================
    // Formatting functions
    // ================================================================

    /**
     * Formats an integer as English cardinal words.
     *
     * @param int $value Integer value to format.
     *
     * @return string English cardinal word representation.
     *
     * @see EnglishNumerals::parseWords()
     *
     * @override
     */
    public function formatCardinal(int $value): string
    {
        if ($value === 0) {
            return self::ZERO;
        }

        $components = [];
        self::appendNumberWords(abs($value), $components);
        $cardinalForm = implode(' ', $components);

        return $value < 0
            ? self::NEGATIVE . ' ' . $cardinalForm
            : $cardinalForm;
    }

    /**
     * Formats a non-zero integer as English ordinal words.
     *
     * Positive values are counted from the start of a sequence. Negative values
     * are counted from the end of a sequence.
     *
     * @param int $value Non-zero integer value to format.
     *
     * @return string English ordinal word representation.
     *
     * @throws InvalidArgumentException If the value is zero.
     *
     * @see EnglishNumerals::parseWords()
     *
     * @override
     */
    public function formatOrdinal(int $value): string
    {
        if ($value === 0) {
            throw new InvalidArgumentException('Ordinal value must not be zero.');
        }

        if ($value === -1) {
            return self::LAST_ORDINAL;
        }

        $components = [];
        self::appendNumberWords(abs($value), $components);

        $lastIndex = count($components) - 1;
        $components[$lastIndex] = self::cardinalToOrdinal($components[$lastIndex]);

        if ($value < 0) {
            array_push($components, ...self::ORDINAL_REVERSALS[0]);
        }

        return implode(' ', $components);
    }

    /**
     * Builds an English number word representation for a given positive integer.
     *
     * @param int $value Positive integer to format.
     * @param string[] $components The list of number word components built so far.
     *
     * @return void
     */
    private static function appendNumberWords(int $value, array &$components): void
    {
        foreach (self::GROUPS as $groupValue => $groupName) {
            if ($value >= $groupValue) {
                self::appendNumberWords(intdiv($value, $groupValue), $components);
                $components[] = $groupName;
                $value %= $groupValue;
            }
        }

        if ($value >= 100) {
            $components[] = self::ONES[intdiv($value, 100)];
            $components[] = self::HUNDRED;
            $value %= 100;
        }

        if ($value >= 20) {
            $tens = self::TENS[intdiv($value, 10) * 10];
            $value = $value % 10;
            if ($value != 0) {
                $components[] = $tens . '-' . self::ONES[$value];
            } else {
                $components[] = $tens;
            }
            return;
        }

        if ($value >= 10) {
            $components[] = self::TEENS[$value];
        } elseif ($value > 0) {
            $components[] = self::ONES[$value];
        }
    }

    /**
     * Converts an English cardinal number word to its ordinal form.
     *
     * @param string $cardinalForm The cardinal word to convert to ordinal.
     *
     * @return string The ordinal form of the given cardinal word.
     */
    private static function cardinalToOrdinal(string $cardinalForm): string
    {
        if (str_contains($cardinalForm, '-')) {
            [$prefix, $suffix] = explode('-', $cardinalForm, 2);
            return $prefix . '-' . self::cardinalToOrdinal($suffix);
        }

        $value = self::parseCardinalWord($cardinalForm);
        if ($value !== null && isset(self::IRREGULAR_ORDINALS[$value])) {
            return self::IRREGULAR_ORDINALS[$value];
        }

        return $cardinalForm . self::ORDINAL_SUFFIX;
    }

    // ================================================================
    // Parsing functions
    // ================================================================

    /**
     * Parses English cardinal or ordinal words into an integer.
     *
     * A valid input is an English cardinal number such as "zero", "forty-two",
     * or "one million two hundred thousand three hundred forty-two". Hyphens
     * in compound tens are treated as token separators, and input is parsed
     * case-insensitively.
     *
     * The final component may be an ordinal form, such as "first", "hundredth",
     * "forty-second", or "one thousandth". The words "last" and phrases such
     * as "second from last" or "second-to-last" are parsed as ordinal positions
     * counted from the end, resulting in negative integer values.
     *
     * A leading "minus" is accepted for cardinal numbers only, excluding zero.
     *
     * @param string $text The string to parse.
     *
     * @return NumberParseResult Parse result.
     *
     * @see EnglishNumerals::formatCardinal()
     * @see EnglishNumerals::formatOrdinal()
     *
     * @override
     */
    public function parseWords(string $text): NumberParseResult
    {
        $text = $this->textNormalizer->normalize($text);
        if (!$tokens = preg_split('/[\s-]+/u', $text, -1, PREG_SPLIT_NO_EMPTY)) {
            return NumberParseResult::failure(NumberParseFailureReason::EmptyInput);
        }

        if ($tokens === [self::ZERO]) {
            return NumberParseResult::success(0, NumberForm::Cardinal);
        }

        if ($tokens === [self::LAST_ORDINAL]) {
            return NumberParseResult::success(-1, NumberForm::Ordinal);
        }

        $form = null;
        $isNegative = false;

        if ($tokens[0] === self::NEGATIVE) {
            if (count($tokens) === 1) {
                return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $tokens[0]);
            }

            array_shift($tokens);
            $form = NumberForm::Cardinal;
            $isNegative = true;
        }

        if ($reverseCount = self::getReverseOrdinalTokenCount($tokens)) {
            if ($isNegative) {
                return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, self::NEGATIVE);
            }

            if (count($tokens) === $reverseCount) {
                return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $tokens[0]);
            }

            array_splice($tokens, -$reverseCount);
            $form = NumberForm::Ordinal;
            $isNegative = true;
        }

        $state = [
            'total' => 0,
            'current' => 0,
            'lastGroupValue' => PHP_INT_MAX,
            'usedPlaces' => 0,
        ];
        $previousWasConjunction = false;

        for ($i = 0, $lastTokenIndex = count($tokens) - 1; $i <= $lastTokenIndex; ++$i) {
            $token = $tokens[$i];

            if ($token === self::CONJUNCTION) {
                if ($previousWasConjunction || $i == 0 || $i === $lastTokenIndex) {
                    return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $token);
                }

                $previousWasConjunction = true;
                continue;
            }

            $value = self::parseCardinalWord($token);
            if ($value === null) {
                $value = self::parseOrdinalWord($token);
                if ($value === null) {
                    return NumberParseResult::failure(NumberParseFailureReason::UnrecognizedWord, $token);
                }
                if ($i !== $lastTokenIndex || $form === NumberForm::Cardinal) {
                    return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $token);
                }
                $form = NumberForm::Ordinal;
            }

            if (!self::applyNumberComponent($state, $value, $previousWasConjunction)) {
                return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $token);
            }

            $previousWasConjunction = false;
        }

        $value = $state['total'] + $state['current'];
        return NumberParseResult::success($isNegative ? -$value : $value, $form ?? NumberForm::Cardinal);
    }

    /**
     * Applies a parsed number component to the running English number parse state.
     *
     * @param array{
     *     total: int,
     *     current: int,
     *     lastGroupValue: int,
     *     usedPlaces: int
     * } $state Running parse state, mutated in place when the component is valid.
     * @param int $value Numeric value of the parsed component.
     * @param bool $precededByConjunction Whether the component was preceded by the conjunction.
     *
     * @return bool True if the component was valid and applied; false otherwise.
     */
    private static function applyNumberComponent(array &$state, int $value, bool $precededByConjunction): bool
    {
        if (isset(self::GROUPS[$value])) {
            if ($precededByConjunction || $value >= $state['lastGroupValue'] || $state['current'] === 0 || $state['current'] >= 1000) {
                return false;
            }

            $state['total'] += $state['current'] * $value;
            $state['current'] = 0;
            $state['lastGroupValue'] = $value;
            $state['usedPlaces'] = 0;
            return true;
        }

        if ($value === 100) {
            if ($precededByConjunction || $state['current'] < 1 || $state['current'] > 9 || $state['usedPlaces'] !== self::PLACE_ONES) {
                return false;
            }

            $state['current'] *= 100;
            $state['usedPlaces'] = self::PLACE_HUNDREDS;
            return true;
        }

        if ($precededByConjunction && $state['current'] < 100 && $state['total'] === 0) {
            return false;
        }

        if ($value >= 20) {
            $place = self::PLACE_TENS;
            $conflicts = self::PLACE_TENS | self::PLACE_TEEN | self::PLACE_ONES;
        } elseif ($value >= 10) {
            $place = self::PLACE_TEEN;
            $conflicts = self::PLACE_TENS | self::PLACE_TEEN | self::PLACE_ONES;
        } else {
            $place = self::PLACE_ONES;
            $conflicts = self::PLACE_TEEN | self::PLACE_ONES;
        }

        if (($state['usedPlaces'] & $conflicts) !== 0) {
            return false;
        }

        $state['usedPlaces'] |= $place;
        $state['current'] += $value;
        return true;
    }

    /**
     * Parses a single English cardinal number word into its integer value.
     *
     * @param string $word The English cardinal word to parse.
     *
     * @return int|null The integer value of the cardinal word, or null if the word is not a valid cardinal number word.
     */
    private static function parseCardinalWord(string $word): ?int
    {
        self::$cardinals ??= array_merge(
            array_flip(self::ONES),
            array_flip(self::TEENS),
            array_flip(self::TENS),
            [self::HUNDRED => 100],
            array_flip(self::GROUPS),
        );

        return self::$cardinals[$word] ?? null;
    }

    /**
     * Parses a single English ordinal number word into its integer value.
     *
     * @param string $word The English ordinal word to parse.
     *
     * @return int|null The integer value of the ordinal word, or null if the word is not a valid ordinal number word.
     */
    private static function parseOrdinalWord(string $word): ?int
    {
        self::$irregularOrdinals ??= array_flip(self::IRREGULAR_ORDINALS);

        if (isset(self::$irregularOrdinals[$word])) {
            return self::$irregularOrdinals[$word];
        }

        if (str_ends_with($word, self::ORDINAL_SUFFIX)) {
            $cardinalForm = substr($word, 0, -strlen(self::ORDINAL_SUFFIX));
            return self::parseCardinalWord($cardinalForm);
        }

        return null;
    }

    /**
     * Counts the number of tokens consumed by a valid English ordinal reversal phrase at the end of the token list.
     *
     * @param array<string> $tokens The tokens to check.
     * @return int The number of tokens consumed by the reversal phrase, or 0 if no valid reversal phrase is found.
     */
    private static function getReverseOrdinalTokenCount(array &$tokens): int
    {
        $tokenLength = count($tokens);
        foreach (self::ORDINAL_REVERSALS as $reversalPhrase) {
            $phraseLength = count($reversalPhrase);
            if ($tokenLength < $phraseLength) {
                continue; // Not enough tokens to match this reversal phrase
            }

            $tokenIndex = $tokenLength - $phraseLength;
            for ($i = 0; $i < $phraseLength; ++$i) {
                if ($tokens[$tokenIndex + $i] !== $reversalPhrase[$i]) {
                    continue 2; // Move to the next reversal phrase
                }
            }

            return $phraseLength;
        }

        return 0;
    }
}

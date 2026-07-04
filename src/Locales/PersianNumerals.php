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
 * Converts integers to and from Persian number words.
 */
class PersianNumerals extends NumberLocalizer
{
    /**
     * Persian word used as a negative sign before cardinal number words.
     *
     * @var string
     */
    private const NEGATIVE = 'منفی';

    /**
     * Persian word for zero.
     *
     * @var string
     */
    private const ZERO = 'صفر';

    /**
     * Persian cardinal words for ones values, indexed by their numeric value.
     *
     * @var array<int, string>
     */
    private const ONES = [
        1 => 'یک',
        2 => 'دو',
        3 => 'سه',
        4 => 'چهار',
        5 => 'پنج',
        6 => 'شش',
        7 => 'هفت',
        8 => 'هشت',
        9 => 'نه',
    ];

    /**
     * Persian cardinal words for teen values, indexed by their numeric value.
     *
     * @var array<int, string>
     */
    private const TEENS = [
        10 => 'ده',
        11 => 'یازده',
        12 => 'دوازده',
        13 => 'سیزده',
        14 => 'چهارده',
        15 => 'پانزده',
        16 => 'شانزده',
        17 => 'هفده',
        18 => 'هجده',
        19 => 'نوزده',
    ];

    /**
     * Persian cardinal words for tens values, indexed by their numeric value.
     *
     * @var array<int, string>
     */
    private const TENS = [
        20 => 'بیست',
        30 => 'سی',
        40 => 'چهل',
        50 => 'پنجاه',
        60 => 'شصت',
        70 => 'هفتاد',
        80 => 'هشتاد',
        90 => 'نود',
    ];

    /**
     * Persian cardinal words for hundreds values, indexed by their numeric value.
     *
     * @var array<int, string>
     */
    private const HUNDREDS = [
        100 => 'صد',
        200 => 'دویست',
        300 => 'سیصد',
        400 => 'چهارصد',
        500 => 'پانصد',
        600 => 'ششصد',
        700 => 'هفتصد',
        800 => 'هشتصد',
        900 => 'نهصد',
    ];

    /**
     * Persian cardinal words for large number groups (thousands and above),
     * indexed by their numeric value in descending order.
     *
     * @var array<int, string>
     */
    private const GROUPS = [
        1000000000000000000 => 'کوینتیلیون',
        1000000000000000 => 'کوادریلیون',
        1000000000000 => 'تریلیون',
        1000000000 => 'میلیارد',
        1000000 => 'میلیون',
        1000 => 'هزار',
    ];

    /**
     * Persian word between components in a multi-word number.
     *
     * @var string
     */
    private const CONJUNCTION = 'و';

    /**
     * Suffix appended to the last cardinal component to form regular ordinals.
     *
     * @var string
     */
    private const ORDINAL_SUFFIX = 'م';

    /**
     * Suffix appended to the last cardinal component to form superlative ordinals.
     *
     * @var string
     */
    private const SUPERLATIVE_SUFFIX = 'ین';

    /**
     * Persian phrases (represented as an array of words) that indicate ordinal
     * values are counted from the end of a sequence.
     *
     * @var array<array<string>>
     */
    private const ORDINAL_REVERSALS = [
        ['از', 'آخر'],
        ['از', 'پایان'],
        ['از', 'انتها'],
        ['از', 'انتهای'],
    ];

    /**
     * Persian word for the last ordinal position.
     *
     * @var string
     */
    private const LAST_ORDINAL = 'آخر';

    /**
     * Persian irregular ordinal forms that do not follow the regular pattern of
     * appending the ordinal suffix to a cardinal form, indexed by their numeric value.
     *
     * @var array<int,string>
     */
    private const IRREGULAR_ORDINALS = [
        3 => 'سوم',
        30 => 'سی‌ام',
        1000000 => 'میلیونیم',
        1000000000000 => 'تریلیونیم',
        1000000000000000 => 'کوادریلیونیم',
        1000000000000000000 => 'کوینتیلیونیم',
    ];

    /**
     * Irregular ordinal forms that are only valid when they are the only component of the number.
     *
     * The key is the numeric value of the ordinal, and the value is an array of the valid forms.
     *
     * @var array<int,array<string>>
     */
    private const IRREGULAR_ORDINALS_STANDALONE_ONLY = [
        1 => ['اول', 'نخست'],
    ];

    /**
     * Bit flag for the ones place (values 1–9) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_ONES = 1;

    /**
     * Bit flag for the teens place (values 10–19) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_TEEN = 2;

    /**
     * Bit flag for the tens place (values 20–90) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_TENS = 4;

    /**
     * Bit flag for the hundreds place (values 100–900) within a sub-1000 segment.
     *
     * @var int
     */
    private const PLACE_HUNDREDS = 8;

    /**
     * Persian cardinal values indexed by normalized word.
     *
     * @var array<string,int>|null
     */
    private static ?array $cardinals = null;

    /**
     * Persian irregular ordinal values indexed by normalized word.
     *
     * @var array<string,int>|null
     */
    private static ?array $irregularOrdinals = null;

    /**
     * Creates a Persian numeral localizer.
     *
     * @param TextNormalizer|null $textNormalizer Text normalizer, or null to use Persian normalization.
     */
    public function __construct(?TextNormalizer $textNormalizer = null)
    {
        parent::__construct($textNormalizer ?? new PersianTextNormalizer());
    }

    // ================================================================
    // Formatting functions
    // ================================================================

    /**
     * Formats an integer as a string of Persian digits, with an optional minimum
     * number of digits padded with leading zeros.
     *
     * @param int $value Integer to format.
     * @param int $minimumDigits Minimum number of digits to include, padded with leading zeros if necessary.
     *
     * @return string The formatted string of Persian digits.
     *
     * @throws InvalidArgumentException If minimum number of digits is less than 1.
     *
     * @see PersianNumerals::parseDigits()
     *
     * @override
     */
    public function formatDigits(int $value, int $minimumDigits = 1): string
    {
        return strtr(parent::formatDigits($value, $minimumDigits), [
            '-' => '−',
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    }

    /**
     * Formats an integer as Persian cardinal words.
     *
     * @param int $value Integer to format.
     *
     * @return string Persian cardinal word representation.
     *
     * @see PersianNumerals::parseWords()
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
     * Formats a non-zero integer as Persian ordinal words.
     *
     * Positive values are counted from the start of a sequence. Negative values
     * are counted from the end of a sequence.
     *
     * @param int $value Non-zero integer to format.
     *
     * @return string Persian ordinal word representation.
     *
     * @throws InvalidArgumentException If value is zero.
     *
     * @see PersianNumerals::parseWords()
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
        $components[$lastIndex] = self::cardinalToOrdinal(
            cardinalForm: $components[$lastIndex],
            standalone: count($components) === 1,
        );

        if ($value < 0) {
            array_push($components, ...self::ORDINAL_REVERSALS[0]);
        }

        return implode(' ', $components);
    }

    /**
     * Builds a Persian number word representation for a given integer.
     *
     * @param int $value The integer to format.
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
            self::appendNumberWord(self::HUNDREDS[intdiv($value, 100) * 100], $components);
            $value %= 100;
        }

        if ($value >= 20) {
            self::appendNumberWord(self::TENS[intdiv($value, 10) * 10], $components);
            $value %= 10;
        }

        if ($value >= 10) {
            self::appendNumberWord(self::TEENS[$value], $components);
        } elseif ($value > 0) {
            self::appendNumberWord(self::ONES[$value], $components);
        }
    }

    /**
     * Appends a Persian number word to the components list, adding a conjunction if needed.
     *
     * @param string $word The Persian number word to append.
     * @param string[] $components The list of number word components built so far.
     *
     * @return void
     */
    private static function appendNumberWord(string $word, array &$components): void
    {
        if (!empty($components)) {
            $components[] = self::CONJUNCTION;
        }

        $components[] = $word;
    }

    /**
     * Converts a cardinal Persian number word to its ordinal form.
     *
     * @param string $cardinalForm The cardinal word to convert to ordinal.
     * @param bool $standalone Whether the cardinal form is the only component of the number.
     *
     * @return string The ordinal form of the given cardinal word.
     */
    private static function cardinalToOrdinal(string $cardinalForm, bool $standalone = false): string
    {
        $value = self::parseCardinalWord($cardinalForm);
        if ($value !== null) {
            if ($standalone && isset(self::IRREGULAR_ORDINALS_STANDALONE_ONLY[$value])) {
                return self::IRREGULAR_ORDINALS_STANDALONE_ONLY[$value][0];
            }
            if (isset(self::IRREGULAR_ORDINALS[$value])) {
                return self::IRREGULAR_ORDINALS[$value];
            }
        }

        return $cardinalForm . self::ORDINAL_SUFFIX;
    }

    // ================================================================
    // Parsing functions
    // ================================================================

    /**
     * Parses Persian, Arabic, or Latin digits into an integer.
     *
     * Invalid inputs return null.
     *
     * @param string $text Digit representation to parse.
     *
     * @return int|null The parsed integer, or null if the input cannot be parsed as a valid number.
     *
     * @see PersianNumerals::formatDigits()
     * @see PersianNumerals::parse()
     *
     * @override
     */
    public function parseDigits(string $text): ?int
    {
        return parent::parseDigits(strtr($text, [
            '−' => '-',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]));
    }

    /**
     * Parses Persian number words into an integer.
     *
     * A valid input is a Persian cardinal number such as "صفر", "چهل و دو",
     * or "یک میلیون و دو هزار و سه". Components must appear in descending
     * numeric order, large groups must be used from larger to smaller, and
     * the conjunction "و" must separate adjacent components where Persian
     * number grammar requires it. Whitespace and ZWNJ are both accepted as
     * token separators, so forms such as "بیست‌و‌یک" and "سی ام" are accepted.
     * Arabic variants of ی and ک are normalized before parsing.
     *
     * The final component may be an ordinal or superlative ordinal form, such
     * as "یکم", "اول", "سی‌ام", or "یکمین". The forms "اول" and "نخست" are
     * accepted only when they are the whole ordinal; compound first ordinals
     * must use the regular "یکم" form, such as "بیست و یکم". The words "آخر"
     * and "آخرین", and phrases such as "دوم از آخر", are parsed as ordinal
     * positions counted from the end, resulting in negative integer values.
     *
     * A leading "منفی" is accepted for cardinal numbers only, including zero,
     * but not for ordinals.
     *
     * @param string $text The string to parse.
     *
     * @return NumberParseResult Parse result.
     *
     * @see PersianNumerals::formatCardinal()
     * @see PersianNumerals::formatOrdinal()
     *
     * @override
     */
    public function parseWords(string $text): NumberParseResult
    {
        $text = $this->textNormalizer->normalize($text);
        if (!$tokens = preg_split('/[\s\x{200C}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY)) {
            return NumberParseResult::failure(NumberParseFailureReason::EmptyInput);
        }

        if ($tokens === [self::ZERO]) {
            return NumberParseResult::success(0, NumberForm::Cardinal);
        }

        if ($tokens === [self::LAST_ORDINAL] || $tokens === [self::LAST_ORDINAL . self::SUPERLATIVE_SUFFIX]) {
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
                if ($previousWasConjunction || $i === 0 || $i === $lastTokenIndex) {
                    return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $token);
                }

                $previousWasConjunction = true;
                continue;
            }

            $parsed = self::parseNumberToken($tokens, $i);
            if ($parsed === null) {
                return NumberParseResult::failure(
                    self::parseOrdinalWord($token, true) === null
                        ? NumberParseFailureReason::UnrecognizedWord
                        : NumberParseFailureReason::InvalidSyntax,
                    $token
                );
            }

            if ($parsed['form'] === NumberForm::Ordinal) {
                if ($isNegative && $form === NumberForm::Cardinal) {
                    return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, self::NEGATIVE);
                }

                $form = NumberForm::Ordinal;
            }

            // Only the first value of a token may follow a conjunction; the extra
            // value of a compact pair such as "یکصد" never has its own conjunction.
            foreach ($parsed['values'] as $value) {
                if (!self::applyNumberComponent($state, $value, $previousWasConjunction)) {
                    return NumberParseResult::failure(NumberParseFailureReason::InvalidSyntax, $token);
                }

                $previousWasConjunction = false;
            }

            $i += $parsed['skip'];
        }

        $value = $state['total'] + $state['current'];
        return NumberParseResult::success($isNegative ? -$value : $value, $form ?? NumberForm::Cardinal);
    }

    /**
     * Parses the token at the given index into the numeric values it represents.
     *
     * A single token usually yields one value, but compact spellings such as
     * "یکصد" and "یکصدم" yield the pair represented by their cardinal prefix
     * and cardinal or ordinal suffix. The final token may be ordinal, and an
     * ordinal whose word is split across two tokens, such as "سی ام" for
     * "سی‌ام", is recognized by rejoining them with a ZWNJ.
     *
     * @param string[] $tokens Normalized number-word tokens.
     * @param int $index Index of the token to parse.
     *
     * @return array{values: int[], form: NumberForm, skip: int}|null Parsed values,
     *      number form, and number of extra tokens consumed, or null if the token is not
     *      a valid number component.
     */
    private static function parseNumberToken(array $tokens, int $index): ?array
    {
        $token = $tokens[$index];
        $lastIndex = count($tokens) - 1;

        // An ordinal form can be made of two tokens if the ordinal suffix is separated
        // by a space, such as "سی ام". To allow this, we try joining the current token
        // with the next one using a ZWNJ and parsing the result as an ordinal, but only
        // when we are at the second-to-last token.
        if ($index + 1 === $lastIndex) {
            $joined = $token . "\u{200C}" . $tokens[$index + 1];
            $parsed = self::parseCardinalOrOrdinalWord($joined, $index === 0);
            if ($parsed !== null) {
                return ['values' => [$parsed['value']], 'form' => $parsed['form'], 'skip' => 1];
            }
        }

        // The final token may be an ordinal form.
        if ($index === $lastIndex) {
            $parsed = self::parseCardinalOrOrdinalWord($token, $index === 0);
            if ($parsed !== null) {
                return ['values' => [$parsed['value']], 'form' => $parsed['form'], 'skip' => 0];
            }
        }

        // Try parsing the token as a cardinal number.
        $value = self::parseCardinalWord($token);
        if ($value !== null) {
            return ['values' => [$value], 'form' => NumberForm::Cardinal, 'skip' => 0];
        }

        // Try splitting the token into two parts, where the first part is a cardinal
        // and the second part is either a cardinal or ordinal. This allows for compact
        // spellings such as "یکصد" and "یکصدم".
        $length = mb_strlen($token);
        for ($split = 1; $split < $length; ++$split) {
            $first = self::parseCardinalWord(mb_substr($token, 0, $split));
            if ($first === null) {
                continue;
            }

            $second = self::parseCardinalOrOrdinalWord(mb_substr($token, $split));
            if ($second === null) {
                continue;
            }

            if ($second['form'] === NumberForm::Ordinal && $index !== $lastIndex) {
                return null;
            }

            return ['values' => [$first, $second['value']], 'form' => $second['form'], 'skip' => 0];
        }

        return null;
    }

    /**
     * Applies a parsed number component to the running Persian number parse state.
     *
     * The state tracks the accumulated large groups, the current under-1000 segment,
     * the last large group value seen, and which places have already appeared in the
     * current segment so invalid combinations can be rejected.
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
            if ($value >= $state['lastGroupValue']) {
                return false;
            }

            if ($state['current'] === 0 && ($precededByConjunction || $value !== 1000 || $state['total'] !== 0)) {
                return false;
            }

            if ($state['current'] > 0 && ($precededByConjunction || $state['current'] >= 1000)) {
                return false;
            }

            $state['total'] += ($state['current'] === 0 ? 1 : $state['current']) * $value;
            $state['current'] = 0;
            $state['lastGroupValue'] = $value;
            $state['usedPlaces'] = 0;

            return true;
        }

        if (!$precededByConjunction && $value === 100 && $state['current'] > 0 && $state['current'] < 10 && $state['usedPlaces'] === self::PLACE_ONES) {
            $state['current'] *= 100;
            $state['usedPlaces'] = self::PLACE_HUNDREDS;

            return true;
        }

        if (!$precededByConjunction && ($state['current'] > 0 || $state['total'] > 0)) {
            return false;
        }

        if ($value >= 100) {
            $place = self::PLACE_HUNDREDS;
            $conflicts = self::PLACE_HUNDREDS | self::PLACE_TENS | self::PLACE_TEEN | self::PLACE_ONES;
        } elseif ($value >= 20) {
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
     * Parses a single Persian cardinal or ordinal number word into its numeric value and form.
     *
     * @param string $word The Persian cardinal or ordinal word to parse.
     * @param bool $isStandalone Whether the word is the only component of the number.
     *
     * @return array{value: int, form: NumberForm}|null The numeric value and form of the word, or null if the word is not valid.
     */
    private static function parseCardinalOrOrdinalWord(string $word, bool $isStandalone = false): ?array
    {
        $value = self::parseCardinalWord($word);
        if ($value !== null) {
            return ['value' => $value, 'form' => NumberForm::Cardinal];
        }

        $value = self::parseOrdinalWord($word, $isStandalone);
        if ($value !== null) {
            return ['value' => $value, 'form' => NumberForm::Ordinal];
        }

        return null;
    }

    /**
     * Parses a single Persian cardinal number word into its numeric value.
     *
     * @param string $word The Persian cardinal word to parse.
     *
     * @return int|null The numeric value of the cardinal word, or null if the word is not a valid cardinal number.
     */
    private static function parseCardinalWord(string $word): ?int
    {
        self::$cardinals ??= array_merge(
            array_flip(self::ONES),
            array_flip(self::TEENS),
            array_flip(self::TENS),
            array_flip(self::HUNDREDS),
            array_flip(self::GROUPS),
        );

        return self::$cardinals[$word] ?? null;
    }

    /**
     * Parses a single Persian ordinal number word into its numeric value.
     *
     * @param string $word The Persian ordinal word to parse.
     *
     * @return int|null The numeric value of the ordinal word, or null if the word is not a valid ordinal number.
     */
    private static function parseOrdinalWord(string $word, bool $isStandalone = false): ?int
    {
        self::$irregularOrdinals ??= array_flip(self::IRREGULAR_ORDINALS);

        if (str_ends_with($word, self::SUPERLATIVE_SUFFIX)) {
            $word = mb_substr($word, 0, -mb_strlen(self::SUPERLATIVE_SUFFIX));
        }

        if (isset(self::$irregularOrdinals[$word])) {
            return self::$irregularOrdinals[$word];
        }

        if (str_ends_with($word, self::ORDINAL_SUFFIX)) {
            $cardinalForm = mb_substr($word, 0, -mb_strlen(self::ORDINAL_SUFFIX));
            return self::parseCardinalWord($cardinalForm);
        }

        if ($isStandalone) {
            foreach (self::IRREGULAR_ORDINALS_STANDALONE_ONLY as $value => $forms) {
                if (in_array($word, $forms, true)) {
                    return $value;
                }
            }
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

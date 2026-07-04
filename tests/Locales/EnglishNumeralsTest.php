<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Localization\Numbers\NumberParseFailureReason;
use Kampute\CivilDate\Localization\Numbers\NumberForm;
use Kampute\CivilDate\Locales\EnglishNumerals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests english numerals.
 */
final class EnglishNumeralsTest extends TestCase
{
    /**
     * Tests format digits.
     */
    #[DataProvider('formatDigitsProvider')]
    public function testFormatDigits(int $value, int $minimumDigits, string $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->formatDigits($value, $minimumDigits));
    }

    /**
     * Provides data for format digits tests.
     *
     * @return array<string, array{int, int, string}>
     */
    public static function formatDigitsProvider(): array
    {
        return [
            'zero default width' => [0, 1, '0'],
            'zero padded' => [0, 3, '000'],
            'positive default width' => [42, 1, '42'],
            'positive padded digits' => [42, 4, '0042'],
            'positive exact width' => [42, 2, '42'],
            'positive wider than minimum' => [1234, 2, '1234'],
            'negative default width' => [-42, 1, '-42'],
            'negative padded digits' => [-42, 4, '-0042'],
            'negative exact width' => [-42, 2, '-42'],
            'negative wider than minimum' => [-1234, 2, '-1234'],
        ];
    }

    /**
     * Tests format digits rejects invalid minimum digits.
     */
    #[DataProvider('invalidMinimumDigitsProvider')]
    public function testFormatDigitsRejectsInvalidMinimumDigits(int $minimumDigits): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnglishNumerals())->formatDigits(1, $minimumDigits);
    }

    /**
     * Provides invalid minimum digit widths.
     *
     * @return array<string, array{int}>
     */
    public static function invalidMinimumDigitsProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
        ];
    }

    /**
     * Tests format cardinal.
     */
    #[DataProvider('formatCardinalProvider')]
    public function testFormatCardinal(int $value, string $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->formatCardinal($value));
    }

    /**
     * Provides data for format cardinal tests.
     *
     * @return array<string, array{int, string}>
     */
    public static function formatCardinalProvider(): array
    {
        return [
            'zero' => [0, 'zero'],
            'teen' => [17, 'seventeen'],
            'tens' => [40, 'forty'],
            'compound' => [42, 'forty-two'],
            'hundred' => [300, 'three hundred'],
            'hundred compound' => [342, 'three hundred forty-two'],
            'thousand' => [1000, 'one thousand'],
            'thousand compound' => [1200342, 'one million two hundred thousand three hundred forty-two'],
            'large group' => [1000000000000000000, 'one quintillion'],
            'negative' => [-42, 'minus forty-two'],
            'negative large group' => [-1000001, 'minus one million one'],
        ];
    }

    /**
     * Tests format cardinal round trips through parse.
     */
    #[DataProvider('formatCardinalRoundTripProvider')]
    public function testFormatCardinalRoundTripsThroughParse(int $value): void
    {
        $numbers = new EnglishNumerals();
        self::assertSame($value, $numbers->parseCardinal($numbers->formatCardinal($value)));
    }

    /**
     * Provides data for format cardinal round trip tests.
     *
     * @return array<string, array{int}>
     */
    public static function formatCardinalRoundTripProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1000001],
            'small' => [19],
            'compound' => [342],
            'large' => [1200342],
            'quintillion' => [1000000000000000000],
        ];
    }

    /**
     * Tests format ordinal.
     */
    #[DataProvider('formatOrdinalProvider')]
    public function testFormatOrdinal(int $value, string $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->formatOrdinal($value));
    }

    /**
     * Provides data for format ordinal tests.
     *
     * @return array<string, array{int, string}>
     */
    public static function formatOrdinalProvider(): array
    {
        return [
            'first' => [1, 'first'],
            'fourth' => [4, 'fourth'],
            'twelfth' => [12, 'twelfth'],
            'twentieth' => [20, 'twentieth'],
            'twenty first' => [21, 'twenty-first'],
            'hundredth' => [300, 'three hundredth'],
            'hundred compound' => [342, 'three hundred forty-second'],
            'thousandth' => [1000, 'one thousandth'],
            'thousand compound ordinal' => [1200342, 'one million two hundred thousand three hundred forty-second'],
            'large group ordinal' => [1000000000000000000, 'one quintillionth'],
            'last' => [-1, 'last'],
            'second from last' => [-2, 'second from last'],
            'twenty first from last' => [-21, 'twenty-first from last'],
        ];
    }

    /**
     * Tests format ordinal rejects zero.
     */
    public function testFormatOrdinalRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EnglishNumerals())->formatOrdinal(0);
    }

    /**
     * Tests format ordinal round trips through parse.
     */
    #[DataProvider('formatOrdinalRoundTripProvider')]
    public function testFormatOrdinalRoundTripsThroughParse(int $value): void
    {
        $numbers = new EnglishNumerals();
        self::assertSame($value, $numbers->parseOrdinal($numbers->formatOrdinal($value)));
    }

    /**
     * Provides data for format ordinal round trip tests.
     *
     * @return array<string, array{int}>
     */
    public static function formatOrdinalRoundTripProvider(): array
    {
        return [
            'first' => [1],
            'regular small' => [4],
            'compound' => [342],
            'group' => [1000],
            'large' => [1200342],
            'quintillion' => [1000000000000000000],
            'last' => [-1],
            'negative small' => [-2],
            'negative compound' => [-21],
        ];
    }

    /**
     * Tests parse digits.
     */
    #[DataProvider('parseDigitsProvider')]
    public function testParseDigits(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->parseDigits($input));
    }

    /**
     * Provides data for parse digits tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseDigitsProvider(): array
    {
        return [
            'zero' => ['0', 0],
            'padded digits' => ['0042', 42],
            'negative digits with whitespace' => [' -42 ', -42],
            'positive sign' => ['+42', 42],
            'negative sign only' => ['-', null],
            'positive sign only' => ['+', null],
            'double sign' => ['--42', null],
            'embedded whitespace' => ['4 2', null],
            'thousands separator' => ['1,000', null],
            'empty string' => ['', null],
            'words' => ['forty-two', null],
            'decimal' => ['4.2', null],
        ];
    }

    /**
     * Tests parse cardinal accepts only cardinal words.
     */
    #[DataProvider('parseCardinalProvider')]
    public function testParseCardinal(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->parseCardinal($input));
    }

    /**
     * Provides data for parse cardinal tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseCardinalProvider(): array
    {
        return [
            'cardinal words' => ['forty-two', 42],
            'ordinal words' => ['forty-second', null],
            'digits' => ['42', null],
        ];
    }

    /**
     * Tests parse ordinal accepts only ordinal words.
     */
    #[DataProvider('parseOrdinalProvider')]
    public function testParseOrdinal(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->parseOrdinal($input));
    }

    /**
     * Provides data for parse ordinal tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseOrdinalProvider(): array
    {
        return [
            'ordinal words' => ['forty-second', 42],
            'last' => ['last', -1],
            'second from last' => ['second from last', -2],
            'second from last alternative' => ['second-to-last', -2],
            'compound from last' => ['twenty-first from last', -21],
            'compound from last alternative' => ['twenty-first-to-last', -21],
            'cardinal words' => ['forty-two', null],
            'digits' => ['42', null],
        ];
    }

    /**
     * Tests parse words.
     */
    #[DataProvider('parseWordsProvider')]
    public function testParseWords(string $input, ?int $expected): void
    {
        $result = (new EnglishNumerals())->parseWords($input);

        if ($expected === null) {
            self::assertFalse($result->succeeded());
            return;
        }

        self::assertTrue($result->succeeded());
        self::assertSame($expected, $result->value());
    }

    /**
     * Provides data for parse words tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseWordsProvider(): array
    {
        return [
            'cardinal with space' => ['forty two', 42],
            'cardinal with hyphen' => ['forty-two', 42],
            'ordinal with space' => ['forty second', 42],
            'ordinal with hyphen' => ['forty-second', 42],
            'case insensitive' => ['Forty Second', 42],
            'zero' => ['zero', 0],
            'hundred with and' => ['one hundred and five', 105],
            'hundred ordinal' => ['one hundredth', 100],
            'large cardinal' => ['one million two hundred thousand three hundred forty-two', 1200342],
            'large cardinal with spaces' => ['one million two hundred thousand three hundred forty two', 1200342],
            'large cardinal with and' => ['one million and five', 1000005],
            'large ordinal' => ['one million two hundred thousand three hundred forty-second', 1200342],
            'large ordinal with spaces' => ['one million two hundred thousand three hundred forty second', 1200342],
            'ordinal group' => ['one hundred thousandth', 100000],
            'last' => ['last', -1],
            'second from last' => ['second from last', -2],
            'negative ordinal with hyphens' => ['twenty-first from last', -21],
            'negative ordinal with spaces' => ['twenty first from last', -21],
            'negative cardinal' => ['minus one million one', -1000001],
            'negative ordinal unsupported' => ['minus forty second', null],
            'out of order groups' => ['one thousand one million', null],
            'duplicate place' => ['twenty thirty', null],
            'ordinal before final word' => ['first thousand', null],
            'bare group' => ['million', null],
            'zero combined with group' => ['one million zero', null],
            'trailing and' => ['one hundred and', null],
            'digits unsupported' => ['42', null],
            'double negative' => ['minus minus one', null],
            'negative ordinal unsupported with hyphen' => ['minus forty-second', null],
            'bare reversal phrase' => ['from last', null],
            'last from last' => ['last from last', null],
            'missing reversal end word' => ['second from', null],
            'missing reversal preposition' => ['second last', null],
            'repeated reversal phrase' => ['second from last from last', null],
            'double and' => ['one hundred and and five', null],
        ];
    }

    /**
     * Tests parse words reports number forms.
     */
    #[DataProvider('parseWordsFormProvider')]
    public function testParseWordsReportsForm(string $input, NumberForm $expected): void
    {
        self::assertSame($expected, (new EnglishNumerals())->parseWords($input)->form());
    }

    /**
     * Provides data for parse words form tests.
     *
     * @return array<string, array{string, NumberForm}>
     */
    public static function parseWordsFormProvider(): array
    {
        return [
            'cardinal' => ['forty-two', NumberForm::Cardinal],
            'negative cardinal' => ['minus forty-two', NumberForm::Cardinal],
            'ordinal' => ['forty-second', NumberForm::Ordinal],
            'negative ordinal' => ['second from last', NumberForm::Ordinal],
        ];
    }

    /**
     * Tests parse words reports failure reasons.
     */
    #[DataProvider('parseWordsFailureProvider')]
    public function testParseWordsReportsFailureReason(
        string $input,
        NumberParseFailureReason $expectedReason,
        ?string $expectedWord
    ): void {
        $result = (new EnglishNumerals())->parseWords($input);

        self::assertFalse($result->succeeded());
        self::assertSame($expectedReason, $result->failureReason());
        self::assertSame($expectedWord, $result->invalidWord());
    }

    /**
     * Provides data for parse words failure tests.
     *
     * @return array<string, array{string, NumberParseFailureReason, string|null}>
     */
    public static function parseWordsFailureProvider(): array
    {
        return [
            'empty input' => ['', NumberParseFailureReason::EmptyInput, null],
            'invalid syntax' => ['first thousand', NumberParseFailureReason::InvalidSyntax, 'first'],
            'bare reversal phrase' => ['from last', NumberParseFailureReason::InvalidSyntax, 'from'],
            'negative end-relative ordinal' => ['minus second from last', NumberParseFailureReason::InvalidSyntax, 'minus'],
            'unrecognized word' => ['forty tree', NumberParseFailureReason::UnrecognizedWord, 'tree'],
        ];
    }

    /**
     * Tests parse.
     */
    #[DataProvider('parseProvider')]
    public function testParse(string $input, int $expectedValue, NumberForm $expectedForm): void
    {
        $result = (new EnglishNumerals())->parse($input);

        self::assertTrue($result->succeeded());
        self::assertSame($expectedValue, $result->value());
        self::assertSame($expectedForm, $result->form());
    }

    /**
     * Provides data for parse tests.
     *
     * @return array<string, array{string, int, NumberForm}>
     */
    public static function parseProvider(): array
    {
        return [
            'digits' => ['42', 42, NumberForm::Digits],
            'cardinal words' => ['forty-two', 42, NumberForm::Cardinal],
            'ordinal words' => ['forty-second', 42, NumberForm::Ordinal],
            'negative ordinal words' => ['second from last', -2, NumberForm::Ordinal],
        ];
    }

    /**
     * Tests parse returns failure results for invalid strings.
     */
    #[DataProvider('invalidParseProvider')]
    public function testParseReturnsFailureForInvalidStrings(string $input): void
    {
        self::assertFalse((new EnglishNumerals())->parse($input)->succeeded());
    }

    /**
     * Provides invalid parse inputs.
     *
     * @return array<string, array{string}>
     */
    public static function invalidParseProvider(): array
    {
        return [
            'empty string' => [''],
            'space only' => [' '],
            'unknown word' => ['unknown'],
            'digit word mix' => ['42 forty'],
            'word digit mix' => ['forty 42'],
            'invalid ordinal placement' => ['first thousand'],
            'negative ordinal' => ['minus first'],
            'negative end-relative ordinal' => ['minus second from last'],
            'bare reversal phrase' => ['from last'],
            'last from last' => ['last from last'],
            'missing reversal end word' => ['second from'],
            'missing reversal preposition' => ['second last'],
            'repeated reversal phrase' => ['second from last from last'],
            'punctuation separator' => ['forty,two'],
        ];
    }
}

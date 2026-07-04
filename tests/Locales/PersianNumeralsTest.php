<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use InvalidArgumentException;
use Kampute\CivilDate\Localization\Numbers\NumberForm;
use Kampute\CivilDate\Localization\Numbers\NumberParseFailureReason;
use Kampute\CivilDate\Locales\PersianNumerals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests persian numerals.
 */
final class PersianNumeralsTest extends TestCase
{
    /**
     * Tests format digits.
     */
    #[DataProvider('formatDigitsProvider')]
    public function testFormatDigits(int $value, int $minimumDigits, string $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->formatDigits($value, $minimumDigits));
    }

    /**
     * Provides data for format digits tests.
     *
     * @return array<string, array{int, int, string}>
     */
    public static function formatDigitsProvider(): array
    {
        return [
            'zero default width' => [0, 1, '۰'],
            'zero padded' => [0, 3, '۰۰۰'],
            'positive default width' => [42, 1, '۴۲'],
            'positive padded digits' => [42, 4, '۰۰۴۲'],
            'positive exact width' => [42, 2, '۴۲'],
            'positive wider than minimum' => [1234, 2, '۱۲۳۴'],
            'negative default width' => [-42, 1, '−۴۲'],
            'negative padded digits' => [-42, 4, '−۰۰۴۲'],
            'negative exact width' => [-42, 2, '−۴۲'],
            'negative wider than minimum' => [-1234, 2, '−۱۲۳۴'],
        ];
    }

    /**
     * Tests format digits rejects invalid minimum digits.
     */
    #[DataProvider('invalidMinimumDigitsProvider')]
    public function testFormatDigitsRejectsInvalidMinimumDigits(int $minimumDigits): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PersianNumerals())->formatDigits(1, $minimumDigits);
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
        self::assertSame($expected, (new PersianNumerals())->formatCardinal($value));
    }

    /**
     * Provides data for format cardinal tests.
     *
     * @return array<array{int,string}> Provider data sets.
     */
    public static function formatCardinalProvider(): array
    {
        return [
            'Zero' => [0, 'صفر'],
            'One' => [1, 'یک'],
            'Nine' => [9, 'نه'],
            'Ten' => [10, 'ده'],
            'Eleven' => [11, 'یازده'],
            'Nineteen' => [19, 'نوزده'],
            'Twenty' => [20, 'بیست'],
            'Twenty one' => [21, 'بیست و یک'],
            'Thirty' => [30, 'سی'],
            'Forty-two' => [42, 'چهل و دو'],
            'Ninety nine' => [99, 'نود و نه'],
            'Nine hundred ninety nine' => [999, 'نهصد و نود و نه'],
            'One hundred ten' => [110, 'صد و ده'],
            'One hundred' => [100, 'صد'],
            'Two hundred' => [200, 'دویست'],
            'One hundred one' => [101, 'صد و یک'],
            'One hundred one thousand' => [101000, 'صد و یک هزار'],
            'One thousand' => [1000, 'یک هزار'],
            'One thousand ten' => [1010, 'یک هزار و ده'],
            'Two thousand' => [2000, 'دو هزار'],
            'One million' => [1000000, 'یک میلیون'],
            'One million and one' => [1000001, 'یک میلیون و یک'],
            'One million two thousand three' => [1002003, 'یک میلیون و دو هزار و سه'],
            'One billion' => [1000000000, 'یک میلیارد'],
            'One million two hundred thousand five hundred sixty' => [1200560, 'یک میلیون و دویست هزار و پانصد و شصت'],
            'Negative one' => [-1, 'منفی یک'],
            'Negative forty-two' => [-42, 'منفی چهل و دو'],
            'Negative one million two thousand three' => [-1002003, 'منفی یک میلیون و دو هزار و سه'],
        ];
    }

    /**
     * Tests format cardinal round trips through parse.
     */
    #[DataProvider('formatCardinalRoundTripProvider')]
    public function testFormatCardinalRoundTripsThroughParse(int $value): void
    {
        $numbers = new PersianNumerals();
        self::assertSame($value, $numbers->parseCardinal($numbers->formatCardinal($value)));
    }

    /**
     * Provides data for format cardinal round trip tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function formatCardinalRoundTripProvider(): array
    {
        return [
            'Zero' => [0],
            'One' => [1],
            'Ten' => [10],
            'Nineteen' => [19],
            'Twenty' => [20],
            'Twenty one' => [21],
            'Ninety' => [90],
            'One hundred' => [100],
            'One hundred twenty three' => [123],
            'Nine hundred ninety nine' => [999],
            'One thousand' => [1000],
            'One thousand ten' => [1010],
            'One million' => [1000000],
            'One million one' => [1000001],
            'One million two thousand three' => [1002003],
            'One million two hundred thousand five hundred sixty' => [1200560],
            'Multi-group cardinal' => [123456789],
            'Negative one' => [-1],
            'Negative forty-two' => [-42],
            'Negative multi-group cardinal' => [-123456789],
        ];
    }

    /**
     * Tests format ordinal.
     */
    #[DataProvider('formatOrdinalProvider')]
    public function testFormatOrdinal(int $value, string $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->formatOrdinal($value));
    }

    /**
     * Provides data for format ordinal tests.
     *
     * @return array<array{int,string}> Provider data sets.
     */
    public static function formatOrdinalProvider(): array
    {
        return [
            'First' => [1, 'اول'],
            'Second' => [2, 'دوم'],
            'Third irregular' => [3, 'سوم'],
            'Thirtieth irregular' => [30, 'سی‌ام'],
            'Twenty first' => [21, 'بیست و یکم'],
            'Eleventh' => [11, 'یازدهم'],
            'Nineteenth' => [19, 'نوزدهم'],
            'Thirty' => [30, 'سی‌ام'],
            'Hundred and first' => [101, 'صد و یکم'],
            'Hundred and tenth' => [110, 'صد و دهم'],
            'One hundredth' => [100, 'صدم'],
            'Thousands ordinal' => [123456, 'صد و بیست و سه هزار و چهارصد و پنجاه و ششم'],
            'Last' => [-1, 'آخر'],
            'Alternate last' => [-1, 'آخر'],
            'Second from last' => [-2, 'دوم از آخر'],
            'Twenty first from last' => [-21, 'بیست و یکم از آخر'],
        ];
    }

    /**
     * Tests format ordinal rejects zero.
     */
    public function testFormatOrdinalRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PersianNumerals())->formatOrdinal(0);
    }

    /**
     * Tests format ordinal round trips through pars.
     */
    #[DataProvider('formatOrdinalRoundTripProvider')]
    public function testFormatOrdinalRoundTripsThroughPars(int $value): void
    {
        $numbers = new PersianNumerals();
        self::assertSame($value, $numbers->parseOrdinal($numbers->formatOrdinal($value)));
    }

    /**
     * Provides data for format ordinal round trip tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function formatOrdinalRoundTripProvider(): array
    {
        return [
            'First' => [1],
            'Second' => [2],
            'Third' => [3],
            'Tenth' => [10],
            'Nineteenth' => [19],
            'Twentieth' => [20],
            'Twenty first' => [21],
            'Thirtieth' => [30],
            'One hundredth' => [100],
            'One hundred first' => [101],
            'One hundred twenty third' => [123],
            'Nine hundred ninety ninth' => [999],
            'One thousandth' => [1000],
            'One millionth' => [1000000],
            'Multi-group ordinal' => [123456],
            'Last' => [-1],
            'Negative small ordinal' => [-2],
            'Negative compound ordinal' => [-21],
        ];
    }

    /**
     * Tests parse digits.
     */
    #[DataProvider('parseDigitsProvider')]
    public function testParseDigits(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->parseDigits($input));
    }

    /**
     * Provides data for parse digits tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseDigitsProvider(): array
    {
        return [
            'persian zero' => ['۰', 0],
            'padded persian digits' => ['۰۰۴۲', 42],
            'negative persian digits' => ['−۴۲', -42],
            'negative latin sign' => ['-۴۲', -42],
            'positive persian sign' => ['+۴۲', 42],
            'persian digits with whitespace' => [' ۴۲ ', 42],
            'arabic-indic digits' => ['٤٢', 42],
            'latin digits' => ['42', 42],
            'negative sign only' => ['−', null],
            'positive sign only' => ['+', null],
            'double sign' => ['−−۴۲', null],
            'embedded whitespace' => ['۴ ۲', null],
            'thousands separator' => ['۱٬۰۰۰', null],
            'empty string' => ['', null],
            'words' => ['چهل و دو', null],
            'decimal' => ['۴.۲', null],
            'arabic decimal separator' => ['۴٫۲', null],
        ];
    }

    /**
     * Tests parse cardinal accepts only cardinal words.
     */
    #[DataProvider('parseCardinalProvider')]
    public function testParseCardinal(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->parseCardinal($input));
    }

    /**
     * Provides data for parse cardinal tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseCardinalProvider(): array
    {
        return [
            'cardinal words' => ['چهل و دو', 42],
            'ordinal words' => ['چهل و دوم', null],
            'digits' => ['۴۲', null],
        ];
    }

    /**
     * Tests parse ordinal accepts only ordinal words.
     */
    #[DataProvider('parseOrdinalProvider')]
    public function testParseOrdinal(string $input, ?int $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->parseOrdinal($input));
    }

    /**
     * Provides data for parse ordinal tests.
     *
     * @return array<string, array{string, int|null}>
     */
    public static function parseOrdinalProvider(): array
    {
        return [
            'ordinal words' => ['چهل و دوم', 42],
            'last' => ['آخر', -1],
            'alternate last' => ['آخرین', -1],
            'second from last' => ['دوم از آخر', -2],
            'compound from last' => ['بیست و یکم از آخر', -21],
            'cardinal words' => ['چهل و دو', null],
            'digits' => ['۴۲', null],
        ];
    }

    /**
     * Tests parse words.
     */
    #[DataProvider('parseWordsProvider')]
    public function testParseWords(string $input, int $expected): void
    {
        $result = (new PersianNumerals())->parseWords($input);

        self::assertTrue($result->succeeded());
        self::assertSame($expected, $result->value());
    }

    /**
     * Provides data for parse words tests.
     *
     * @return array<array{string,int}> Provider data sets.
     */
    public static function parseWordsProvider(): array
    {
        return [
            // Persian cardinal words - Basic (0-9)
            'Word zero' => ['صفر', 0],
            'Word one' => ['یک', 1],
            'Negative one' => ['منفی یک', -1],

            // Persian cardinal words - Teens (10-19)
            'Word ten' => ['ده', 10],
            'Word nineteen' => ['نوزده', 19],

            // Persian cardinal words - Tens (20-90)
            'Word twenty' => ['بیست', 20],
            'Word ninety' => ['نود', 90],

            // Persian cardinal words - Hundreds (100-900)
            'Word two hundred' => ['دویست', 200],
            'Word four hundred' => ['چهار صد', 400],
            'Word nine hundred' => ['نهصد', 900],

            // Persian cardinal words - Large groups
            'Word one thousand' => ['یک هزار', 1000],
            'Word one million' => ['یک میلیون', 1000000],
            'Word one quintillion' => ['یک کوینتیلیون', 1000000000000000000],

            // Persian cardinal words - Compound numbers
            'Word twenty one' => ['بیست و یک', 21],
            'Negative word forty-two' => ['منفی چهل و دو', -42],
            'Word one hundred twenty three' => ['صد و بیست و سه', 123],
            'Word one million two hundred thousand five hundred sixty' => ['یک میلیون و دویست هزار و پانصد و شصت', 1200560],
            'Word multi-group cardinal' => ['صد و بیست و سه هزار و چهارصد و پنجاه و شش', 123456],

            // Persian ordinal words - Regular forms
            'Ordinal first (یکم)' => ['یکم', 1],
            'Ordinal second' => ['دوم', 2],
            'Ordinal tenth' => ['دهم', 10],
            'Ordinal nineteenth' => ['نوزدهم', 19],
            'Ordinal twentieth' => ['بیستم', 20],
            'Ordinal hundredth' => ['صدم', 100],
            'Ordinal thousandth' => ['هزارم', 1000],

            // Persian ordinal words - Irregular forms
            'Ordinal first (اول)' => ['اول', 1],
            'Ordinal first (نخست)' => ['نخست', 1],
            'Ordinal third (سوم)' => ['سوم', 3],
            'Ordinal thirtieth (سی‌ام)' => ['سی‌ام', 30],

            // Persian ordinal words - Compound ordinals
            'Ordinal twenty first' => ['بیست و یکم', 21],
            'Ordinal one hundred twenty third' => ['صد و بیست و سوم', 123],
            'Ordinal multi-group ordinal' => ['صد و بیست و سه هزار و چهارصد و پنجاه و ششم', 123456],

            // Persian end-anchored ordinal words
            'Ordinal last' => ['آخر', -1],
            'Ordinal last alternate' => ['آخرین', -1],
            'Ordinal second from last' => ['دوم از آخر', -2],
            'Ordinal twenty first from last' => ['بیست و یکم از آخر', -21],
            'Ordinal thirtieth from last with space' => ['سی ام از آخر', -30],

            // Persian superlative ordinals
            'Superlative first (یکمین)' => ['یکمین', 1],
            'Superlative first (اولین)' => ['اولین', 1],
            'Superlative first (نخستین)' => ['نخستین', 1],
            'Superlative thirtieth' => ['سی‌امین', 30],
            'Superlative twenty first' => ['بیست و یکمین', 21],
            'Superlative one hundred first' => ['صد و یکمین', 101],

            // Separator variations - ZWNJ vs space
            'Thirtieth with space' => ['سی ام', 30],
            'Thirtieth superlative with space' => ['سی امین', 30],
            'Compound with ZWNJ' => ['بیست‌و‌یک', 21],
            'Negative with ZWNJ' => ['منفی‌بیست و یک', -21],
            'Mixed separators' => ['بیست‌و یک', 21],

            // Whitespace variations
            'Multiple spaces between words' => ['بیست  و  یک', 21],
            'Tabs and newlines in words' => ["\tبیست\nو\tیک\n", 21],
            'Multiple ZWNJ' => ["بیست\u{200C}\u{200C}و\u{200C}\u{200C}یک", 21],

            // Hundred variations - different forms
            'Hundred cardinal variant 1' => ['صد', 100],
            'Hundred cardinal variant 2' => ['یکصد', 100],
            'Hundred cardinal variant 3' => ['یک‌صد', 100],
            'Hundred cardinal variant 4' => ['یک صد', 100],
            'Hundred ordinal variant 1' => ['صدم', 100],
            'Hundred ordinal variant 2' => ['یکصدم', 100],
            'Hundred ordinal variant 3' => ['یک‌صدم', 100],
            'Hundred ordinal variant 4' => ['یک صدم', 100],
            'Hundred superlative variant 1' => ['صدمین', 100],
            'Hundred superlative variant 2' => ['یکصدمین', 100],
            'Hundred superlative variant 3' => ['یک‌صدمین', 100],
            'Hundred superlative variant 4' => ['یک صدمین', 100],
        ];
    }

    /**
     * Tests parse words reports number forms.
     */
    #[DataProvider('parseWordsFormProvider')]
    public function testParseWordsReportsForm(string $input, NumberForm $expected): void
    {
        self::assertSame($expected, (new PersianNumerals())->parseWords($input)->form());
    }

    /**
     * Provides data for parse words form tests.
     *
     * @return array<string, array{string, NumberForm}>
     */
    public static function parseWordsFormProvider(): array
    {
        return [
            'cardinal' => ['چهل و دو', NumberForm::Cardinal],
            'ordinal' => ['چهل و دوم', NumberForm::Ordinal],
            'negative ordinal' => ['دوم از آخر', NumberForm::Ordinal],
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
        $result = (new PersianNumerals())->parseWords($input);

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
            'invalid syntax' => ['بیست و اول', NumberParseFailureReason::InvalidSyntax, 'اول'],
            'missing ordinal before reversal' => ['از آخر', NumberParseFailureReason::InvalidSyntax, 'از'],
            'repeated reversal phrase' => ['دوم از آخر از آخر', NumberParseFailureReason::InvalidSyntax, 'دوم'],
            'unrecognized word' => ['بست و یک', NumberParseFailureReason::UnrecognizedWord, 'بست'],
        ];
    }

    /**
     * Tests parse.
     */
    #[DataProvider('parseProvider')]
    public function testParse(string $input, int $expectedValue, NumberForm $expectedForm): void
    {
        $result = (new PersianNumerals())->parse($input);

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
            'digits' => ['۴۲', 42, NumberForm::Digits],
            'cardinal words' => ['چهل و دو', 42, NumberForm::Cardinal],
            'ordinal words' => ['چهل و دوم', 42, NumberForm::Ordinal],
            'negative ordinal words' => ['دوم از آخر', -2, NumberForm::Ordinal],
        ];
    }

    /**
     * Tests parse returns null for invalid word strings.
     */
    #[DataProvider('invalidParseProvider')]
    public function testParseReturnsNullForInvalidWordStrings(string $input): void
    {
        self::assertFalse((new PersianNumerals())->parseWords($input)->succeeded());
    }

    /**
     * Provides data for invalid parse tests.
     *
     * @return array<array{string}> Provider data sets.
     */
    public static function invalidParseProvider(): array
    {
        return [
            // Empty and whitespace-only strings
            'Empty string' => [''],
            'Space only' => [' '],
            'Tab only' => ["\t"],
            'Newline only' => ["\n"],
            'Multiple spaces only' => ['   '],
            'Multiple tabs and newlines' => ["\t\n\t\n"],

            // Invalid ZWNJ usage
            'ZWNJ only' => ["\u{200C}"],
            'Multiple ZWNJ only' => ["\u{200C}\u{200C}\u{200C}"],

            // Invalid word-only inputs
            'Unknown word' => ['نامشخص'],
            'Partial word' => ['بیس'],
            'Typo in word' => ['یکک'],
            'Mixed Persian and English' => ['یک one'],
            'English word' => ['twenty'],

            // Invalid ordinal placement
            'Ordinal not at end' => ['بیستم و یک'],
            'Ordinal in middle' => ['صد و چهارم و سه'],
            'Multiple ordinals' => ['یکم و دوم'],
            'Ordinal before cardinal group' => ['دهم هزار'],
            'First ordinal not last' => ['اولم و دو'],
            'Third irregular not last' => ['سوم و چهار'],
            'Thirtieth irregular not last' => ['سی‌ام و یک'],
            'First ordinal not standalone in twenty one' => ['بیست و اول'],
            'First ordinal not standalone' => ['چهل و اول'],
            'Thousand ordinal not last' => ['یک هزارم و دو'],

            // Invalid superlative usage
            'Multiple superlatives' => ['یکمینم'],
            'Superlative not at end' => ['یکمین و دو'],
            'Double superlative suffix' => ['یکمینین'],
            'Superlative on non-ordinal' => ['یکین'],

            // Invalid conjunction usage
            'Starting with conjunction' => ['و یک'],
            'Ending with conjunction' => ['یک و'],
            'Conjunction only' => ['و'],
            'Multiple consecutive conjunctions' => ['یک و و دو'],
            'Double conjunction' => ['بیست و و یک'],
            'Triple conjunction' => ['بیست و و و یک'],
            'Conjunction at start' => ['و یک صد'],
            'Conjunction at end' => ['صد و'],
            'Spaces around lone conjunction' => ['  و  '],

            // Invalid number combinations
            'Duplicate group' => ['صد و یکصد'],
            'Conflicting hundreds' => ['صد و دویست'],
            'Conflicting tens' => ['بیست و سی'],
            'Conflicting ones' => ['یک و دو'],
            'Teens with ones' => ['ده و یک'],
            'Wrong order' => ['یک و هزار'],
            'Reversed order' => ['یک و صد و هزار'],
            'Group without value' => ['هزار و میلیون'],

            // Invalid separators
            'Comma as separator' => ['بیست، یک'],
            'Semicolon as separator' => ['بیست؛ یک'],
            'Period as separator' => ['بیست. یک'],
            'Slash as separator' => ['بیست/یک'],
            'Dash as separator' => ['بیست-یک'],

            // Mixed invalid combinations
            'Digits and words mixed' => ['۱۲ بیست'],
            'Digits before words' => ['12 یک'],
            'Digits after words' => ['یک 12'],
            'Digits in middle of words' => ['بیست ۲۱ یک'],
            'Partial digit in words' => ['بیست و ۲۱'],

            // Invalid hundred variations
            'Three hundreds' => ['سه یکصد'],
            'Hundred after hundred' => ['صد صد'],
            'Invalid hundred compound' => ['ده صد'],

            // Invalid spacing patterns
            'No space before conjunction' => ['بیستو یک'],
            'No space after conjunction' => ['بیست ویک'],
            'No spaces around conjunction' => ['بیستویک'],

            // Invalid zero usage
            'Zero with other words' => ['صفر و یک'],
            'Word zero in compound' => ['یک و صفر'],
            'Zero before number' => ['صفر یک'],

            // Invalid signed word numbers
            'Positive word number' => ['مثبت یک'],
            'Negative sign only' => ['منفی'],
            'Repeated negative word' => ['منفی منفی یک'],
            'Glued negative word' => ['منفییک'],
            'Negative ordinal' => ['منفی یکم'],
            'Negative irregular ordinal' => ['منفی اول'],
            'Negative superlative ordinal' => ['منفی اولین'],
            'Negative end-relative ordinal' => ['منفی دوم از آخر'],
            'Bare end-relative phrase' => ['از آخر'],
            'Last from last' => ['آخر از آخر'],
            'Missing end word in reversal phrase' => ['دوم از'],
            'Missing reversal preposition' => ['دوم آخر'],
            'Repeated reversal phrase' => ['دوم از آخر از آخر'],
            'Minus before word' => ['-یک'],
            'Minus sign before word' => ['−یک'],

            // Invalid large number patterns
            'Million without value' => ['میلیون'],
            'Billion without value' => ['میلیارد'],
            'Multiple thousands' => ['یک هزار هزار'],
            'Reversed groups' => ['هزار میلیون'],

            // Edge cases with special characters
            'Persian question mark' => ['بیست؟'],
            'Exclamation mark' => ['بیست!'],
            'Parentheses' => ['(بیست)'],
            'Brackets' => ['[بیست]'],
            'Quotes' => ['"بیست"'],
            'Apostrophe' => ["بیست'"],

            // Invalid ordinal forms
            'Ordinal suffix only' => ['م'],
            'Superlative suffix only' => ['ین'],
            'Both suffixes only' => ['مین'],
            'Invalid ordinal on conjunction' => ['وم'],

            // Unicode edge cases
            'RTL mark' => ["\u{200F}یک"],
            'LTR mark' => ["\u{200E}یک"],
            'Soft hyphen' => ["بی\u{00AD}ست"],
        ];
    }

}

<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use Kampute\CivilDate\Locales\PersianTextNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests persian text normalizer.
 */
final class PersianTextNormalizerTest extends TestCase
{
    /**
     * Tests normalizes arabic character variants.
     */
    #[DataProvider('arabicCharacterProvider')]
    public function testNormalizesArabicCharacterVariants(string $input, string $expected): void
    {
        self::assertSame($expected, (new PersianTextNormalizer())->normalize($input));
    }

    /**
     * Tests removes arabic diacritics.
     */
    #[DataProvider('diacriticProvider')]
    public function testRemovesArabicDiacritics(string $diacritic): void
    {
        self::assertSame('یک', (new PersianTextNormalizer())->normalize("ی{$diacritic}ک"));
    }

    /**
     * Provides data for arabic character tests.
     *
     * @return iterable<string,array{string,string}>
     */
    public static function arabicCharacterProvider(): iterable
    {
        yield 'Arabic yeh' => ['ي', 'ی'];
        yield 'Arabic kaf' => ['ك', 'ک'];
        yield 'combined word' => ['يك', 'یک'];
        yield 'Arabic-Indic digits' => ['٠١٢٣٤٥٦٧٨٩', '۰۱۲۳۴۵۶۷۸۹'];
    }

    /**
     * Provides data for diacritic tests.
     *
     * @return iterable<string,array{string}>
     */
    public static function diacriticProvider(): iterable
    {
        foreach (['ً', 'ٌ', 'ٍ', 'َ', 'ُ', 'ِ', 'ّ', 'ْ', 'ٓ', 'ٔ', 'ٕ', 'ٖ', 'ٗ', '٘', 'ٙ', 'ٚ', 'ٛ', 'ٜ', 'ٝ', 'ٞ', 'ٟ', 'ٰ'] as $diacritic) {
            yield 'U+' . strtoupper(dechex(mb_ord($diacritic))) => [$diacritic];
        }
    }
}

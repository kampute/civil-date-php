<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Locales;

use Kampute\CivilDate\Localization\TextNormalizer;

/**
 * Normalizes Persian localized text and Arabic character variants.
 */
class PersianTextNormalizer implements TextNormalizer
{
    /**
     * Normalizes Persian text for locale lookups.
     *
     * @param string $text Text to normalize.
     *
     * @return string Text with Persian character forms and no Arabic diacritics.
     */
    public function normalize(string $text): string
    {
        return strtr($text, [
            // Translate Arabic characters to Persian equivalents
            'ي' => 'ی',
            'ك' => 'ک',
            // Translate Arabic-Indic digits to Persian digits
            '٠' => '۰',
            '١' => '۱',
            '٢' => '۲',
            '٣' => '۳',
            '٤' => '۴',
            '٥' => '۵',
            '٦' => '۶',
            '٧' => '۷',
            '٨' => '۸',
            '٩' => '۹',
            // Remove Arabic diacritics
            'ً' => '', // U+064B ARABIC FATHATAN
            'ٌ' => '', // U+064C ARABIC DAMMATAN
            'ٍ' => '', // U+064D ARABIC KASRATAN
            'َ' => '', // U+064E ARABIC FATHA
            'ُ' => '', // U+064F ARABIC DAMMA
            'ِ' => '', // U+0650 ARABIC KASRA
            'ّ' => '', // U+0651 ARABIC SHADDA
            'ْ' => '', // U+0652 ARABIC SUKUN
            'ٓ' => '', // U+0653 ARABIC MADDAH ABOVE
            'ٔ' => '', // U+0654 ARABIC HAMZA ABOVE
            'ٕ' => '', // U+0655 ARABIC HAMZA BELOW
            'ٖ' => '', // U+0656 ARABIC SUBSCRIPT ALEF
            'ٗ' => '', // U+0657 ARABIC INVERTED DAMMA
            '٘' => '', // U+0658 ARABIC MARK NOON GHUNNA
            'ٙ' => '', // U+0659 ARABIC ZWARAKAY
            'ٚ' => '', // U+065A ARABIC VOWEL SIGN SMALL V ABOVE
            'ٛ' => '', // U+065B ARABIC VOWEL SIGN INVERTED SMALL V ABOVE
            'ٜ' => '', // U+065C ARABIC VOWEL SIGN DOT BELOW
            'ٝ' => '', // U+065D ARABIC REVERSED DAMMA
            'ٞ' => '', // U+065E ARABIC FATHA WITH TWO DOTS
            'ٟ' => '', // U+065F ARABIC WAVY HAMZA BELOW
            'ٰ' => '', // U+0670 ARABIC LETTER SUPERSCRIPT ALEF
        ]);
    }
}

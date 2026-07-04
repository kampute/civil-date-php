<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Localization\Numbers;

/**
 * Identifies why a localized number parse failed.
 */
enum NumberParseFailureReason
{
    /**
     * Input contained no parseable words.
     */
    case EmptyInput;

    /**
     * Input did not conform to the expected syntax for number words in the locale.
     */
    case InvalidSyntax;

    /**
     * Input contained a word that is not recognized as a valid number word in the locale.
     */
    case UnrecognizedWord;
}

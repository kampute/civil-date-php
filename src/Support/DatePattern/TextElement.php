<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents literal text in a date pattern.
 *
 * @see PatternElement
 * @see PatternCompiler::compile()
 */
final class TextElement implements PatternElement
{
    /**
     * Creates a date-pattern text element.
     *
     * @param string $text Literal text.
     */
    public function __construct(
        private readonly string $text
    ) {
    }

    /**
     * Returns the literal text.
     *
     * @return string Literal text.
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * Returns an element with appended literal text.
     *
     * @param string $text Text to append.
     *
     * @return self Element containing both text fragments.
     */
    public function appended(string $text): self
    {
        return new self($this->text . $text);
    }

    /**
     * Formats this element for a date.
     *
     * @param CalendarDate $date Date being formatted.
     * @param Locale $locale Locale definition.
     *
     * @return string Literal text.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        return $this->text;
    }
}

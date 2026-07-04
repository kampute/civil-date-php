<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents literal text in a date pattern.
 *
 * @see Segment
 * @see PatternParser::parse()
 */
final class Literal implements Segment
{
    /**
     * Creates a date-pattern literal.
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
     * Returns a literal with appended text.
     *
     * @param string $text Text to append.
     *
     * @return self Literal containing both text fragments.
     */
    public function appended(string $text): self
    {
        return new self($this->text . $text);
    }

    /**
     * Returns the regular-expression fragment for parsing this literal.
     *
     * @return string Regular-expression fragment.
     */
    public function captureRegex(): string
    {
        return preg_quote($this->text, '~');
    }

    /**
     * Formats this literal for a date.
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

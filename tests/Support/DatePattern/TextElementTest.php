<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Localization\LocaleRegistry;
use Kampute\CivilDate\Support\DatePattern\TextElement;
use PHPUnit\Framework\TestCase;

/**
 * Tests date-pattern text elements.
 */
final class TextElementTest extends TestCase
{
    /**
     * Tests text elements own their text and formatting behavior.
     */
    public function testTextElementBehavior(): void
    {
        $element = new TextElement('~/.');
        $locale = LocaleRegistry::default();

        self::assertSame('~/.', $element->text());
        self::assertSame('~/.', $element->format(new GregorianDate(2025, 3, 21), $locale));
    }

    /**
     * Tests appending text returns a new text element.
     */
    public function testAppendedReturnsNewTextElement(): void
    {
        $element = new TextElement('Date: ');
        $appended = $element->appended('2025');

        self::assertNotSame($element, $appended);
        self::assertSame('Date: ', $element->text());
        self::assertSame('Date: 2025', $appended->text());
    }
}

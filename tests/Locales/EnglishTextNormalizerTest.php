<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Locales;

use Kampute\CivilDate\Locales\EnglishTextNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Tests english text normalizer.
 */
final class EnglishTextNormalizerTest extends TestCase
{
    /**
     * Tests normalizes case and whitespace.
     */
    public function testNormalizesCaseAndWhitespace(): void
    {
        self::assertSame('english text', (new EnglishTextNormalizer())->normalize('  English Text  '));
    }
}

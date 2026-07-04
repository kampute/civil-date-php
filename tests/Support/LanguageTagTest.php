<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support;

use InvalidArgumentException;
use Kampute\CivilDate\Support\LanguageTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

/**
 * Tests locale language tag support.
 */
final class LanguageTagTest extends TestCase
{
    /**
     * Tests assert valid accepts supported language tags.
     */
    #[DataProvider('validLanguageTagProvider')]
    #[DoesNotPerformAssertions]
    public function testAssertValidAcceptsSupportedLanguageTags(string $languageTag): void
    {
        LanguageTag::assertValid($languageTag);
    }

    /**
     * Provides valid language tags.
     *
     * @return array<string,array{string}> Provider data sets.
     */
    public static function validLanguageTagProvider(): array
    {
        return [
            'language' => ['fa'],
            'extended language' => ['abcdefgh'],
            'region' => ['fa-IR'],
            'numeric region' => ['fa-034'],
        ];
    }

    /**
     * Tests assert valid rejects unsupported language tags.
     */
    #[DataProvider('invalidLanguageTagProvider')]
    public function testAssertValidRejectsUnsupportedLanguageTags(string $languageTag): void
    {
        $this->expectException(InvalidArgumentException::class);

        LanguageTag::assertValid($languageTag);
    }

    /**
     * Provides invalid language tags.
     *
     * @return array<string,array{string}> Provider data sets.
     */
    public static function invalidLanguageTagProvider(): array
    {
        return [
            'one-letter language' => ['f'],
            'too-long language' => ['abcdefghi'],
            'lowercase region' => ['fa-ir'],
            'too-short numeric region' => ['fa-03'],
            'script subtag' => ['fa-Arab'],
        ];
    }

    /**
     * Tests language.
     */
    #[DataProvider('languageProvider')]
    public function testLanguage(string $languageTag, string $expected): void
    {
        self::assertSame($expected, LanguageTag::language($languageTag));
    }

    /**
     * Provides language tags and their language subtags.
     *
     * @return array<string,array{string,string}> Provider data sets.
     */
    public static function languageProvider(): array
    {
        return [
            'language' => ['fa', 'fa'],
            'region' => ['fa-IR', 'fa'],
            'numeric region' => ['fa-034', 'fa'],
        ];
    }

    /**
     * Tests region.
     */
    #[DataProvider('regionProvider')]
    public function testRegion(string $languageTag, ?string $expected): void
    {
        self::assertSame($expected, LanguageTag::region($languageTag));
    }

    /**
     * Provides language tags and their region subtags.
     *
     * @return array<string,array{string,string|null}> Provider data sets.
     */
    public static function regionProvider(): array
    {
        return [
            'language' => ['fa', null],
            'region' => ['fa-IR', 'IR'],
            'numeric region' => ['fa-034', '034'],
        ];
    }
}

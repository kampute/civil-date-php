<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Support\DatePattern\TokenRegistry;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the built-in date-pattern token registry.
 */
final class TokenRegistryTest extends TestCase
{
    /**
     * Tests registered symbols resolve to their semantic properties.
     */
    #[DataProvider('registeredSymbolProvider')]
    public function testRegisteredSymbolsResolveToDefinitions(string $symbol, string $property): void
    {
        $tokenRegistry = new TokenRegistry();
        $definition = $tokenRegistry->find($symbol);
        self::assertNotNull($definition);
        self::assertSame($property, $definition->property());
    }

    /**
     * Tests literal symbols do not resolve to token definitions.
     */
    public function testLiteralSymbolsAreNotRegistered(): void
    {
        $tokenRegistry = new TokenRegistry();
        self::assertNull($tokenRegistry->find('/'));
        self::assertNull($tokenRegistry->find('z'));
    }

    /**
     * Tests a shared token registry is reused.
     */
    public function testSharedReturnsSharedRegistry(): void
    {
        self::assertSame(TokenRegistry::shared(), TokenRegistry::shared());
    }

    /**
     * Tests custom definitions can be provided at construction.
     */
    public function testConstructorAcceptsCustomDefinitions(): void
    {
        $definition = new NumberDigit('year', minimumDigits: 4, signed: true);
        $tokenRegistry = new TokenRegistry(['X' => $definition]);

        self::assertSame($definition, $tokenRegistry->find('X'));
        self::assertSame(['X' => $definition], $tokenRegistry->definitions());
        self::assertNull($tokenRegistry->find('Y'));
        self::assertSame(0, $tokenRegistry->revision());
    }

    /**
     * Tests registering new token definitions.
     */
    public function testRegisterAddsNewDefinition(): void
    {
        $definition = new NumberDigit('year', minimumDigits: 4, signed: true);
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->register('X', $definition));
        self::assertSame($definition, $tokenRegistry->find('X'));
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests registering a duplicate token definition is rejected.
     */
    public function testRegisterRejectsRegisteredSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already registered');

        (new TokenRegistry())->register('Y', new NumberDigit('year'));
    }

    /**
     * Tests replacing existing token definitions.
     */
    public function testReplaceUpdatesExistingDefinition(): void
    {
        $definition = new NumberDigit('day');
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->replace('Y', $definition));
        self::assertSame($definition, $tokenRegistry->find('Y'));
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests replacing an unknown token definition is rejected.
     */
    public function testReplaceRejectsUnknownSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not registered');

        (new TokenRegistry())->replace('X', new NumberDigit('year'));
    }

    /**
     * Tests removing existing token definitions.
     */
    public function testRemoveDeletesExistingDefinition(): void
    {
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->remove('Y'));
        self::assertNull($tokenRegistry->find('Y'));
        self::assertArrayNotHasKey('Y', $tokenRegistry->definitions());
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests removing an unknown token definition is rejected.
     */
    public function testRemoveRejectsUnknownSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not registered');

        (new TokenRegistry())->remove('X');
    }

    /**
     * Tests multi-character token symbols are rejected.
     */
    public function testRejectsInvalidTokenSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly one character');

        (new TokenRegistry())->register('XX', new NumberDigit('year'));
    }

    /**
     * Provides registered symbols and their semantic properties.
     *
     * @return array<string,array{string,string}> Provider data sets.
     */
    public static function registeredSymbolProvider(): array
    {
        return [
            'full year' => ['Y', 'year'],
            'year as words' => ['V', 'year'],
            'two-digit year' => ['y', 'year'],
            'month' => ['n', 'month'],
            'zero-padded month' => ['m', 'month'],
            'month name' => ['F', 'month'],
            'abbreviated month name' => ['M', 'month'],
            'day' => ['j', 'day'],
            'zero-padded day' => ['d', 'day'],
            'ordinal day' => ['J', 'day'],
            'day-of-week name' => ['l', 'dayOfWeek'],
            'abbreviated day-of-week name' => ['D', 'dayOfWeek'],
            'day of week in month' => ['k', 'dayOfWeekInMonth'],
            'day of week in year' => ['K', 'dayOfWeekInYear'],
            'day of year' => ['R', 'dayOfYear'],
            'quarter' => ['q', 'quarter'],
            'season' => ['Q', 'season'],
            'era' => ['C', 'calendar'],
            'abbreviated era' => ['E', 'calendar'],
        ];
    }
}

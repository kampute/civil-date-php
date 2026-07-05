<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;
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
    public function testRegisteredSymbolsResolveToRules(string $symbol, string $property): void
    {
        $tokenRegistry = new TokenRegistry();
        $rule = $tokenRegistry->find($symbol);
        self::assertInstanceOf(ParsableTokenRule::class, $rule);
        self::assertSame($property, $rule->property());
    }

    /**
     * Tests text symbols do not resolve to token rules.
     */
    public function testTextSymbolsAreNotRegistered(): void
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
     * Tests custom rules can be provided at construction.
     */
    public function testConstructorAcceptsCustomRules(): void
    {
        $rule = new NumberDigit('year', minimumDigits: 4, signed: true);
        $tokenRegistry = new TokenRegistry(['X' => $rule]);

        self::assertSame($rule, $tokenRegistry->find('X'));
        self::assertSame(['X' => $rule], $tokenRegistry->rules());
        self::assertNull($tokenRegistry->find('Y'));
        self::assertSame(0, $tokenRegistry->revision());
    }

    /**
     * Tests registering new token rules.
     */
    public function testRegisterAddsNewRule(): void
    {
        $rule = new NumberDigit('year', minimumDigits: 4, signed: true);
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->register('X', $rule));
        self::assertSame($rule, $tokenRegistry->find('X'));
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests registering a duplicate token rule is rejected.
     */
    public function testRegisterRejectsRegisteredSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already registered');

        (new TokenRegistry())->register('Y', new NumberDigit('year'));
    }

    /**
     * Tests replacing existing token rules.
     */
    public function testReplaceUpdatesExistingRule(): void
    {
        $rule = new NumberDigit('day');
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->replace('Y', $rule));
        self::assertSame($rule, $tokenRegistry->find('Y'));
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests replacing an unknown token rule is rejected.
     */
    public function testReplaceRejectsUnknownSymbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not registered');

        (new TokenRegistry())->replace('X', new NumberDigit('year'));
    }

    /**
     * Tests removing existing token rules.
     */
    public function testRemoveDeletesExistingRule(): void
    {
        $tokenRegistry = new TokenRegistry();

        self::assertSame($tokenRegistry, $tokenRegistry->remove('Y'));
        self::assertNull($tokenRegistry->find('Y'));
        self::assertArrayNotHasKey('Y', $tokenRegistry->rules());
        self::assertSame(1, $tokenRegistry->revision());
    }

    /**
     * Tests removing an unknown token rule is rejected.
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

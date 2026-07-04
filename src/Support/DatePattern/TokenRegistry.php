<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use InvalidArgumentException;
use Kampute\CivilDate\Support\DatePattern\Tokens\DayOfWeekName;
use Kampute\CivilDate\Support\DatePattern\Tokens\EraName;
use Kampute\CivilDate\Support\DatePattern\Tokens\MonthName;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberDigit;
use Kampute\CivilDate\Support\DatePattern\Tokens\NumberWord;
use Kampute\CivilDate\Support\DatePattern\Tokens\SeasonName;
use Kampute\CivilDate\Support\DatePattern\Tokens\TwoDigitYear;

/**
 * Stores date-pattern tokens by symbol.
 *
 * The registry provides these default tokens:
 *
 * - `Y`: year number, signed, minimum width 4.
 * - `V`: year number words.
 * - `y`: two-digit year.
 * - `n`: month number.
 * - `m`: month number, minimum width 2.
 * - `F`: name of month.
 * - `M`: abbreviated name of month.
 * - `j`: day of month.
 * - `d`: day of month, minimum width 2.
 * - `J`: day of month ordinal.
 * - `l`: name of day of week.
 * - `D`: abbreviated name of day of week.
 * - `k`: day of week occurrence in month.
 * - `K`: day of week occurrence in year.
 * - `R`: day of year ordinal.
 * - `q`: quarter ordinal.
 * - `Q`: season name.
 * - `C`: era name.
 * - `E`: abbreviated era name.
 *
 * @see TokenDefinition
 * @see PatternParser
 */
final class TokenRegistry
{
    /**
     * Shared token registry.
     *
     * @var self|null
     */
    private static ?self $shared = null;

    /**
     * Definitions indexed by token symbol.
     *
     * @var array<string,TokenDefinition>
     */
    private array $definitions;

    /**
     * Registry revision.
     *
     * @var int
     */
    private int $revision = 0;

    /**
     * Creates a token registry.
     *
     * @param array<string,TokenDefinition>|null $definitions Token definitions indexed by symbol, or null to use the built-in definitions.
     *
     * @see TokenDefinition
     */
    public function __construct(?array $definitions = null)
    {
        $this->definitions = $definitions ?? [
            'Y' => new NumberDigit('year', minimumDigits: 4, signed: true),
            'V' => new NumberWord('year', ordinal: false),
            'y' => new TwoDigitYear(),
            'n' => new NumberDigit('month'),
            'm' => new NumberDigit('month', minimumDigits: 2),
            'F' => new MonthName(abbreviated: false),
            'M' => new MonthName(abbreviated: true),
            'j' => new NumberDigit('day'),
            'd' => new NumberDigit('day', minimumDigits: 2),
            'J' => new NumberWord('day', ordinal: true),
            'l' => new DayOfWeekName(abbreviated: false),
            'D' => new DayOfWeekName(abbreviated: true),
            'k' => new NumberWord('dayOfWeekInMonth', ordinal: true),
            'K' => new NumberWord('dayOfWeekInYear', ordinal: true),
            'R' => new NumberWord('dayOfYear', ordinal: true),
            'q' => new NumberWord('quarter', ordinal: true),
            'Q' => new SeasonName(),
            'C' => new EraName(abbreviated: false),
            'E' => new EraName(abbreviated: true),
        ];
    }

    /**
     * Returns the shared token registry.
     *
     * Changing the shared registry will affect all date formatting and parsing
     * operations of classes derived from `CalendarDate`.
     *
     * @return self Shared token registry.
     *
     * @see PatternParser::shared()
     * @see \Kampute\CivilDate\CalendarDate::format()
     * @see \Kampute\CivilDate\CalendarDate::parse()
     */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /**
     * Registers a token definition.
     *
     * @param string $symbol Pattern symbol.
     * @param TokenDefinition $definition Token definition.
     *
     * @return self This registry.
     *
     * @throws InvalidArgumentException If the symbol is invalid or already registered.
     */
    public function register(string $symbol, TokenDefinition $definition): self
    {
        $this->assertValidSymbol($symbol);
        if (isset($this->definitions[$symbol])) {
            throw new InvalidArgumentException("Date-pattern token \"{$symbol}\" is already registered.");
        }

        $this->definitions[$symbol] = $definition;
        ++$this->revision;
        return $this;
    }

    /**
     * Replaces a token definition.
     *
     * @param string $symbol Pattern symbol.
     * @param TokenDefinition $definition Token definition.
     *
     * @return self This registry.
     *
     * @throws InvalidArgumentException If the symbol is invalid or not registered.
     */
    public function replace(string $symbol, TokenDefinition $definition): self
    {
        $this->assertValidSymbol($symbol);
        if (!isset($this->definitions[$symbol])) {
            throw new InvalidArgumentException("Date-pattern token \"{$symbol}\" is not registered.");
        }

        $this->definitions[$symbol] = $definition;
        ++$this->revision;
        return $this;
    }

    /**
     * Removes a token definition.
     *
     * @param string $symbol Pattern symbol.
     *
     * @return self This registry.
     *
     * @throws InvalidArgumentException If the symbol is invalid or not registered.
     */
    public function remove(string $symbol): self
    {
        $this->assertValidSymbol($symbol);
        if (!isset($this->definitions[$symbol])) {
            throw new InvalidArgumentException("Date-pattern token \"{$symbol}\" is not registered.");
        }

        unset($this->definitions[$symbol]);
        ++$this->revision;
        return $this;
    }

    /**
     * Returns the definition registered for a symbol.
     *
     * @param string $symbol Pattern symbol.
     *
     * @return TokenDefinition|null Token definition, or null when no token is registered for the symbol.
     *
     * @see TokenRegistry::register()
     * @see TokenRegistry::replace()
     */
    public function find(string $symbol): ?TokenDefinition
    {
        return $this->definitions[$symbol] ?? null;
    }

    /**
     * Returns the registered token definitions.
     *
     * @return array<string,TokenDefinition> Token definitions indexed by symbol.
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * Returns the current registry revision.
     *
     * @return int Registry revision.
     */
    public function revision(): int
    {
        return $this->revision;
    }

    /**
     * Asserts a pattern symbol can be registered.
     *
     * @param string $symbol Pattern symbol.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the symbol is invalid.
     */
    private function assertValidSymbol(string $symbol): void
    {
        if (strlen($symbol) !== 1) {
            throw new InvalidArgumentException('Date-pattern token symbols must be exactly one character.');
        }
    }
}

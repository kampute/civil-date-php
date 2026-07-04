<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests season relationships.
 */
final class SeasonTest extends TestCase
{
    /**
     * Verifies that each season maps to its opposite season.
     */
    #[DataProvider('oppositeProvider')]
    public function testOpposite(Season $season, Season $expected): void
    {
        self::assertSame($expected, $season->opposite());
    }

    /**
     * Provides seasons and their opposites.
     *
     * @return array<string,array{Season,Season}> Season and expected opposite pairs.
     */
    public static function oppositeProvider(): array
    {
        return [
            'Spring becomes autumn' => [Season::Spring, Season::Autumn],
            'Summer becomes winter' => [Season::Summer, Season::Winter],
            'Autumn becomes spring' => [Season::Autumn, Season::Spring],
            'Winter becomes summer' => [Season::Winter, Season::Summer],
        ];
    }

    /**
     * Verifies that taking the opposite twice returns the original season.
     */
    #[DataProvider('seasonProvider')]
    public function testOppositeIsInvolutive(Season $season): void
    {
        self::assertSame($season, $season->opposite()->opposite());
    }

    /**
     * Provides every season.
     *
     * @return array<string,array{Season}> Seasons keyed by descriptive names.
     */
    public static function seasonProvider(): array
    {
        return [
            'Spring' => [Season::Spring],
            'Summer' => [Season::Summer],
            'Autumn' => [Season::Autumn],
            'Winter' => [Season::Winter],
        ];
    }
}

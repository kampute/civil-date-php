<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Support\DatePattern\Tokens\SeasonName;
use PHPUnit\Framework\TestCase;

/**
 * Tests localized season-name token definitions.
 */
final class SeasonNameTest extends TestCase
{
    /**
     * Tests formatting season names.
     */
    public function testFormat(): void
    {
        $tokenDefinition = new SeasonName();

        self::assertSame('Spring', $tokenDefinition->format(new GregorianDate(2025, 3, 21), new English()));
    }

    /**
     * Tests parsing season names.
     */
    public function testParse(): void
    {
        $tokenDefinition = new SeasonName();

        self::assertSame(1, $tokenDefinition->parse('Spring', Calendar::Gregorian, new English()));
    }

    /**
     * Tests parsing rejects unknown season names.
     */
    public function testParseRejectsUnknownName(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Unrecognized season name');

        $tokenDefinition = new SeasonName();
        $tokenDefinition->parse('Unknown', Calendar::Gregorian, new English());
    }
}

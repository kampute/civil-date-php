<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use InvalidArgumentException;

/**
 * Compiles date patterns for full-match parsing.
 *
 * @see PatternParser
 * @see CompiledPattern
 * @see \Kampute\CivilDate\CalendarDate::parse()
 */
final class PatternCompiler
{
    /**
     * Shared compiler for date parsing.
     *
     * @var self|null
     */
    private static ?self $shared = null;

    /**
     * Compiled patterns indexed by source pattern.
     *
     * @var array<string,CompiledPattern>
     */
    private array $cache = [];

    /**
     * Parser providing semantic segments.
     *
     * @var PatternParser
     */
    private readonly PatternParser $parser;

    /**
     * Creates a pattern compiler.
     *
     * @param PatternParser|null $parser Optional pattern parser. If not provided, the shared parser is used.
     *
     * @see PatternParser::shared()
     */
    public function __construct(?PatternParser $parser = null)
    {
        $this->parser = $parser ?? PatternParser::shared();
    }

    /**
     * Returns the shared pattern compiler.
     *
     * @return self Pattern compiler.
     */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /**
     * Compiles a date pattern.
     *
     * @param string $pattern Pattern to compile.
     *
     * @return CompiledPattern Compiled parsing pattern.
     *
     * @throws InvalidArgumentException If the pattern syntax is invalid.
     *
     * @see PatternParser::parse()
     * @see CompiledPattern::match()
     */
    public function compile(string $pattern): CompiledPattern
    {
        $cacheKey = $this->cacheKey($pattern);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $regex = '';
        $captures = [];

        foreach ($this->parser->parse($pattern) as $segment) {
            $regex .= $segment->captureRegex();

            if ($segment instanceof Token) {
                $captures[] = $segment;
            }
        }

        return $this->cache[$cacheKey] = new CompiledPattern('~^' . $regex . '$~iu', $captures);
    }

    /**
     * Returns the current compiler revision.
     *
     * @return int Compiler revision.
     */
    public function revision(): int
    {
        return $this->parser->revision();
    }

    /**
     * Returns the cache key for a pattern.
     *
     * @param string $pattern Pattern to compile.
     *
     * @return string Cache key.
     */
    private function cacheKey(string $pattern): string
    {
        return $this->revision() . "\0" . $pattern;
    }
}

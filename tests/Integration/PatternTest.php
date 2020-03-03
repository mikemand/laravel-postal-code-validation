<?php

namespace Axlon\PostalCodeValidation\Tests\Integration;

use PHPUnit\Framework\TestCase;

class PatternTest extends TestCase
{
    /**
     * Provide the patterns.
     *
     * @return array
     */
    public function providePatterns(): array
    {
        return require __DIR__ . '/../../resources/formats.php';
    }

    /**
     * Test if all examples match their respective patterns.
     *
     * @param string|null $example
     * @param string|null $pattern
     * @return void
     * @dataProvider providePatterns
     */
    public function testPatternMatchesExample(?string $example, ?string $pattern): void
    {
        if ($example === null) {
            $this->assertNull($pattern);
        } else {
            $this->assertRegExp($pattern, $example);
        }
    }
}

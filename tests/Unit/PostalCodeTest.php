<?php

namespace Axlon\PostalCodeValidation\Tests\Unit;

use Axlon\PostalCodeValidation\Extensions\PostalCode;
use Axlon\PostalCodeValidation\Validator;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class PostalCodeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * The validator.
     *
     * @var \Axlon\PostalCodeValidation\Validator|\Mockery\MockInterface
     */
    protected $validator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->validator = Mockery::mock(Validator::class);
    }

    /**
     * Test if the replacer properly replaces the placeholders.
     *
     * @return void
     */
    public function testReplacerReplacesPlaceholders(): void
    {
        $countryCode = 'foo';
        $example = 'bar';
        $rule = new PostalCode($this->validator);

        $this->validator->shouldReceive('supports')->once()->with($countryCode)->andReturnTrue();
        $this->validator->shouldReceive('getExample')->once()->with($countryCode)->andReturn($example);

        $this->assertEquals(
            "Country: {$countryCode}, Example: {$example}",
            $rule->replace('Country: :countries, Example: :examples', 'attribute', 'rule', [$countryCode])
        );
    }

    /**
     * Test if the replacer skips over any unsupported countries.
     *
     * @return void
     */
    public function testReplacerSkipsUnsupportedCountries(): void
    {
        $rule = new PostalCode($this->validator);
        $supportedCountryCode = 'foo';
        $unsupportedCountryCode = 'bar';

        $this->validator->shouldReceive('supports')->once()->with($unsupportedCountryCode)->andReturnFalse();
        $this->validator->shouldNotReceive('getExample')->with($unsupportedCountryCode);

        $this->validator->shouldReceive('supports')->once()->with($supportedCountryCode)->andReturnTrue();
        $this->validator->shouldReceive('getExample')->with($supportedCountryCode)->andReturnNull();

        $rule->replace('message', 'attribute', 'rule', [$unsupportedCountryCode, $supportedCountryCode]);
    }

    /**
     * Test if validation fails upon receiving empty input.
     *
     * @return void
     */
    public function testValidationFailsOnEmptyInput(): void
    {
        $countryCode = 'foo';
        $rule = new PostalCode($this->validator);

        $this->assertFalse($rule->validate('attribute', null, [$countryCode]));
        $this->assertFalse($rule->validate('attribute', '', [$countryCode]));
    }

    /**
     * Test if validation fails when no matching countries are found.
     *
     * @return void
     */
    public function testValidationFailsWhenNoMatchesAreFound(): void
    {
        $countryCode = 'foo';
        $postalCode = 'bar';
        $rule = new PostalCode($this->validator);

        $this->validator->shouldReceive('validate')->once()->with($countryCode, $postalCode)->andReturnFalse();
        $this->assertFalse($rule->validate('attribute', $postalCode, [$countryCode]));
    }

    /**
     * Test if validation passes when a country matches.
     *
     * @return void
     */
    public function testValidationPassesWhenMatchesAreFound(): void
    {
        $matchingCountry = 'foo';
        $nonMatchingCountry = 'bar';
        $postalCode = 'baz';
        $rule = new PostalCode($this->validator);
        $skippedCountry = 'qux';

        $this->validator->shouldReceive('validate')->once()->with($nonMatchingCountry, $postalCode)->andReturnFalse();
        $this->validator->shouldReceive('validate')->once()->with($matchingCountry, $postalCode)->andReturnTrue();
        $this->validator->shouldNotReceive('validate')->with($skippedCountry, $postalCode);

        $this->assertTrue($rule->validate('attribute', $postalCode, [
            $nonMatchingCountry,
            $matchingCountry,
            $skippedCountry,
        ]));
    }

    /**
     * Test if validation throws an exception when receiving no parameters.
     *
     * @return void
     */
    public function testValidationThrowsOnEmptyCountryList(): void
    {
        $countryCode = 'foo';
        $rule = new PostalCode($this->validator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation rule \'postal_code\' requires at least 1 parameter.');

        $rule->validate('attribute', $countryCode, []);
    }
}

<?php

namespace Axlon\PostalCodeValidation\Tests\Unit;

use Axlon\PostalCodeValidation\Extensions\PostalCodeFor;
use Axlon\PostalCodeValidation\Validator as PostalCodeValidator;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class PostalCodeForTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * The validation engine.
     *
     * @var \Axlon\PostalCodeValidation\Validator|\Mockery\MockInterface
     */
    protected $engine;

    /**
     * The validator.
     *
     * @var \Illuminate\Validation\Validator|\Mockery\MockInterface
     */
    protected $validator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->engine = Mockery::mock(PostalCodeValidator::class);
        $this->validator = Mockery::mock(Validator::class);
    }

    /**
     * Test if the replacer properly replaces placeholders.
     *
     * @return void
     */
    public function testReplacerReplacesPlaceholders(): void
    {
        $countryCode = 'country_code';
        $example = 'example';
        $field = 'field';
        $message = 'country=:countries, example=:examples, field=:fields';
        $postalCode = 'postal_code';

        $rule = new PostalCodeFor($this->engine);

        $this->validator->shouldReceive('getData')->once()->andReturn([$field => $countryCode]);

        $this->engine->shouldReceive('supports')->andReturnTrue();
        $this->engine->shouldReceive('validate')->andReturnFalse();

        $rule->validate('attribute', $postalCode, [$field], $this->validator);

        $this->engine->shouldReceive('getExample')->once()->with($countryCode)->andReturn($example);

        $this->assertEquals(
            "country={$countryCode}, example={$example}, field={$field}",
            $rule->replace($message, 'attribute', 'rule', [$field])
        );
    }

    /**
     * Test if validation fails when no matches are found.
     *
     * @return void
     */
    public function testValidationFailsWhenNoMatchesAreFound(): void
    {
        $countryCode = 'country_code';
        $field = 'field';
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);

        $this->validator->shouldReceive('getData')->once()->andReturn([$field => $countryCode]);
        $this->engine->shouldReceive('supports')->once()->with($countryCode)->andReturnTrue();
        $this->engine->shouldReceive('validate')->once()->with($countryCode, $postalCode)->andReturnFalse();

        $this->assertFalse($rule->validate('attribute', $postalCode, [$field], $this->validator));
    }

    /**
     * Test if validation passes when a match is found.
     *
     * @return void
     */
    public function testValidationPassesWhenMatchesAreFound(): void
    {
        $fields = ['non_matching', 'matching', 'skipped'];
        $matchingCountryCode = 'non_matching';
        $nonMatchingCountryCode = 'matching';
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);
        $skippedCountryCode = 'skipped';

        $this->validator->shouldReceive('getData')->once()->andReturn([
            'matching' => $matchingCountryCode,
            'non_matching' => $nonMatchingCountryCode,
            'skipped' => $skippedCountryCode,
        ]);

        $this->engine->shouldReceive('supports')->once()->with($nonMatchingCountryCode)->andReturnTrue();
        $this->engine->shouldReceive('validate')->once()->with($nonMatchingCountryCode, $postalCode)->andReturnFalse();

        $this->engine->shouldReceive('supports')->once()->with($matchingCountryCode)->andReturnTrue();
        $this->engine->shouldReceive('validate')->once()->with($matchingCountryCode, $postalCode)->andReturnTrue();

        $this->engine->shouldNotReceive('supports')->with($skippedCountryCode);
        $this->engine->shouldNotReceive('validate')->with($skippedCountryCode, $postalCode);

        $this->assertTrue($rule->validate('attribute', $postalCode, $fields, $this->validator));
    }

    /**
     * Test if validation passes when none of the reference fields are present and filled.
     *
     * @return void
     */
    public function testValidationPassesWhenNoReferencedFieldsArePresent(): void
    {
        $field = 'field';
        $otherField = 'other_field';
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);

        # Field is present, but empty
        $this->validator->shouldReceive('getData')->once()->andReturn([$field => '']);
        $this->assertTrue($rule->validate('attribute', $postalCode, [$field], $this->validator));

        # Field is not present
        $this->validator->shouldReceive('getData')->once()->andReturn([$otherField => 'qux']);
        $this->assertTrue($rule->validate('attribute', $postalCode, [$field], $this->validator));
    }

    /**
     * Test if unsupported country codes are skipped.
     *
     * @return void
     */
    public function testValidationSkipsUnsupportedCountryCodes(): void
    {
        $field = 'field';
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);
        $unsupportedCountryCode = 'unsupported_country';

        $this->validator->shouldReceive('getData')->once()->andReturn([$field => $unsupportedCountryCode]);
        $this->engine->shouldReceive('supports')->once()->with($unsupportedCountryCode)->andReturnFalse();
        $this->engine->shouldNotReceive('validate');

        $this->assertFalse($rule->validate('attribute', $postalCode, [$field], $this->validator));
    }

    /**
     * Test if validation throws an exception when receiving no parameters.
     *
     * @return void
     */
    public function testValidationThrowsOnEmptyParameterList(): void
    {
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation rule \'postal_code_for\' requires at least 1 parameter.');

        $rule->validate('attribute', $postalCode, [], $this->validator);
    }

    /**
     * Test if an exception is thrown if request data cannot be retrieved.
     *
     * @return void
     */
    public function testValidationThrowsWhenUnableToRetrieveRequestData(): void
    {
        $field = 'field';
        $postalCode = 'postal_code';
        $rule = new PostalCodeFor($this->engine);
        $unsupportedValidator = Mockery::mock(ValidatorContract::class);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unsupported validator type, cannot retrieve data');

        $rule->validate('attribute', $postalCode, [$field], $unsupportedValidator);
    }
}

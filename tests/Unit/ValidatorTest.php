<?php

namespace Axlon\PostalCodeValidation\Tests\Unit;

use Axlon\PostalCodeValidation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * Test if examples are retrieved from the rules.
     *
     * @return void
     */
    public function testExampleRetrievedFromRules(): void
    {
        $rules = ['FOO' => ['example' => 'bar']];
        $validator = new Validator($rules);

        $this->assertEquals('bar', $validator->getExample('foo'));
    }

    /**
     * Test if an exception is thrown when a unsupported country is supplied.
     *
     * @return void
     */
    public function testExampleThrowsOnUnsupportedCountryCode(): void
    {
        $validator = new Validator([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country code \'foo\'');

        $validator->getExample('foo');
    }

    /**
     * Test if patterns are retrieved from the rules.
     *
     * @return void
     */
    public function testPatternRetrievedFromRules(): void
    {
        $rules = ['FOO' => ['pattern' => '/^bar$/i']];
        $validator = new Validator($rules);

        $this->assertEquals('/^bar$/i', $validator->getPattern('foo'));
    }

    /**
     * Test if an exception is thrown when a unsupported country is supplied.
     *
     * @return void
     */
    public function testPatternThrowsOnUnsupportedCountryCode(): void
    {
        $validator = new Validator([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country code \'foo\'');

        $validator->getPattern('foo');
    }

    /**
     * Test if validation fails if a invalid postal code is passed.
     *
     * @return void
     */
    public function testValidationFailsOnInvalidPostalCode(): void
    {
        $rules = ['FOO' => ['pattern' => '/^bar$/i']];
        $validator = new Validator($rules);

        $this->assertFalse($validator->validate('foo', 'baz'));
    }

    /**
     * Test if validation passes if the country code's pattern is empty.
     *
     * @return void
     */
    public function testValidationPassesOnEmptyPattern(): void
    {
        $rules = ['FOO' => null];
        $validator = new Validator($rules);

        $this->assertTrue($validator->validate('foo', 'bar'));
    }

    /**
     * Test if validation properly ignores country code casing.
     *
     * @return void
     */
    public function testValidationPassesRegardlessOfCountryCodeCasing(): void
    {
        $rules = ['FOO' => ['pattern' => '/^bar$/i']];
        $validator = new Validator($rules);

        $this->assertTrue($validator->validate('foO', 'bar'));
        $this->assertTrue($validator->validate('Foo', 'bar'));
        $this->assertTrue($validator->validate('FOO', 'bar'));
    }

    /**
     * Test if an exception is thrown when a unsupported country is supplied.
     *
     * @return void
     */
    public function testValidationThrowsOnUnsupportedCountryCode(): void
    {
        $validator = new Validator([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country code \'foo\'');

        $validator->validate('foo', 'bar');
    }
}

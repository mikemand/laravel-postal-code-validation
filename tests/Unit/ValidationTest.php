<?php

namespace Axlon\PostalCodeValidation\Tests\Unit;

use Axlon\PostalCodeValidation\Tests\Constraints\Fails;
use Axlon\PostalCodeValidation\Tests\Constraints\Passes;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

abstract class ValidationTest extends TestCase
{
    /**
     * The validator.
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Factory(new Translator($loader = new ArrayLoader(), 'en'));

        $loader->addMessages('en', 'validation', [
            'postal_code' => 'The :attribute field must be a valid :countries postal code (e.g. :examples).',
            'postal_code_for' => 'The :attribute field must be a valid :countries postal code (e.g. :examples).',
        ]);
    }

    /**
     * Assert that given request data fails validation.
     *
     * @param array $request
     * @param array $rules
     * @return void
     */
    public function assertFails(array $request, array $rules): void
    {
        static::assertThat($request, new Fails($this->factory, $rules));
    }

    /**
     * Assert that given request data passes validation.
     *
     * @param array $request
     * @param array $rules
     * @return void
     */
    public function assertPasses(array $request, array $rules): void
    {
        static::assertThat($request, new Passes($this->factory, $rules));
    }

    /**
     * Get the validation factory.
     *
     * @return \Illuminate\Validation\Factory
     */
    public function getFactory(): Factory
    {
        return $this->factory;
    }
}

<?php

namespace Axlon\PostalCodeValidation\Tests\Constraints;

use Illuminate\Contracts\Validation\Factory;
use PHPUnit\Framework\Constraint\Constraint;

class Fails extends Constraint
{
    /**
     * The validator.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $factory;

    /**
     * The validation rules.
     *
     * @var array
     */
    protected $rules;

    /**
     * Create a new constraint for unsuccessful validation.
     *
     * @param \Illuminate\Contracts\Validation\Factory $factory
     * @param array $rules
     * @return void
     */
    public function __construct(Factory $factory, array $rules)
    {
        $this->factory = $factory;
        $this->rules = $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $success = $this->factory->make($other, $this->rules)->fails();

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return 'fails validation';
    }
}

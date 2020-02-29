<?php

namespace Axlon\PostalCodeValidation\Extensions;

use Axlon\PostalCodeValidation\Validator;
use InvalidArgumentException;

class PostalCode
{
    /**
     * The postal code validator.
     *
     * @var \Axlon\PostalCodeValidation\Validator
     */
    protected $validator;

    /**
     * Create a new PostalCode validator extension.
     *
     * @param \Axlon\PostalCodeValidation\Validator $validator
     * @return void
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Replace error message placeholders.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param string[] $parameters
     * @return string
     */
    public function replace(string $message, string $attribute, string $rule, array $parameters): string
    {
        $countries = [];
        $examples = [];

        foreach ($parameters as $parameter) {
            if (!$this->validator->supports($parameter)) {
                continue;
            }

            $countries[] = $parameter;
            $examples[] = $this->validator->getExample($parameter);
        }

        $countries = implode(', ', array_unique($countries));
        $examples = implode(', ', array_unique(array_filter($examples)));

        return str_replace([':countries', ':examples'], [$countries, $examples], $message);
    }

    /**
     * Validate the given attribute.
     *
     * @param string $attribute
     * @param string|null $value
     * @param string[] $parameters
     * @return bool
     */
    public function validate(string $attribute, ?string $value, array $parameters): bool
    {
        if (empty($parameters)) {
            throw new InvalidArgumentException('Validation rule \'postal_code\' requires at least 1 parameter.');
        }

        if (empty($value)) {
            return false;
        }

        foreach ($parameters as $parameter) {
            if ($this->validator->validate($parameter, $value)) {
                return true;
            }
        }

        return false;
    }
}

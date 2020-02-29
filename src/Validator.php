<?php

namespace Axlon\PostalCodeValidation;

use InvalidArgumentException;

class Validator
{
    /**
     * The validation rules for each country.
     *
     * @var array
     */
    protected $rules;

    /**
     * Create a new postal code validator.
     *
     * @param array $rules
     * @return void
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Get an example postal code for the country.
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getExample(string $countryCode): ?string
    {
        if (!$this->supports($countryCode)) {
            throw new InvalidArgumentException("Unsupported country code '{$countryCode}'");
        }

        return $this->rules[strtoupper($countryCode)]['example'] ?? null;
    }

    /**
     * Get the validation rule for the country.
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getPattern(string $countryCode): ?string
    {
        if (!$this->supports($countryCode)) {
            throw new InvalidArgumentException("Unsupported country code '{$countryCode}'");
        }

        return $this->rules[strtoupper($countryCode)]['pattern'];
    }

    /**
     * Determine if the country code is supported.
     *
     * @param string $countryCode
     * @return bool
     */
    public function supports(string $countryCode): bool
    {
        return array_key_exists(strtoupper($countryCode), $this->rules);
    }

    /**
     * Validate a postal code.
     *
     * @param string $countryCode
     * @param string|null $postalCode
     * @return bool
     */
    public function validate(string $countryCode, ?string $postalCode): bool
    {
        if (!$this->supports($countryCode)) {
            throw new InvalidArgumentException("Unsupported country code '{$countryCode}'");
        }

        if (($pattern = $this->getPattern($countryCode)) === null) {
            return true;
        }

        return preg_match($pattern, (string)$postalCode) === 1;
    }
}

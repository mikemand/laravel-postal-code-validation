<?php

namespace Axlon\PostalCodeValidation\Extensions;

use Axlon\PostalCodeValidation\Validator as PostalCodeValidator;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use UnexpectedValueException;

class PostalCodeFor
{
    /**
     * The replacements.
     *
     * @var array
     */
    protected $replacements;

    /**
     * The postal code validator.
     *
     * @var \Axlon\PostalCodeValidation\Validator
     */
    protected $validator;

    /**
     * Create a new PostalCodeFor validator extension.
     *
     * @param \Axlon\PostalCodeValidation\Validator $validator
     * @return void
     */
    public function __construct(PostalCodeValidator $validator)
    {
        $this->replacements = [];
        $this->validator = $validator;
    }

    /**
     * Add a replacement.
     *
     * @param string $attribute
     * @param string $field
     * @param string $countryCode
     * @return void
     */
    protected function addReplacement(string $attribute, string $field, string $countryCode): void
    {
        $this->replacements[$attribute][$field] = $countryCode;
    }

    /**
     * Extract request data from the validator.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return array
     */
    protected function extractRequestData(ValidatorContract $validator): array
    {
        if (!$validator instanceof Validator) {
            throw new UnexpectedValueException('Unsupported validator type, cannot retrieve data');
        }

        return $validator->getData();
    }

    /**
     * Get replacements for an attribute.
     *
     * @param string $attribute
     * @return array
     */
    protected function getReplacements(string $attribute): array
    {
        return $this->replacements[$attribute] ?? [];
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
        $foundCountryCodes = Arr::only($this->getReplacements($attribute), $parameters);

        $foundExamples = array_map(function (string $countryCode) {
            return $this->validator->getExample($countryCode);
        }, $foundCountryCodes);

        return str_replace([':countries', ':examples', ':fields'], [
            implode(', ', array_unique($foundCountryCodes)),
            implode(', ', array_unique(array_filter($foundExamples))),
            implode(', ', $parameters),
        ], $message);
    }

    /**
     * Validate the given attribute.
     *
     * @param string $attribute
     * @param string|null $value
     * @param string[] $parameters
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return bool
     */
    public function validate(string $attribute, ?string $value, array $parameters, ValidatorContract $validator): bool
    {
        if (empty($parameters)) {
            throw new InvalidArgumentException('Validation rule \'postal_code_for\' requires at least 1 parameter.');
        }

        $data = $this->extractRequestData($validator);

        $parameters = array_filter($parameters, function (string $parameter) use ($data) {
            return $this->verifyExistence($data, $parameter);
        });

        if (empty($parameters)) {
            return true;
        }

        foreach ($parameters as $parameter) {
            $countryCode = Arr::get($data, $parameter);

            if (!$this->validator->supports($countryCode)) {
                continue;
            }

            if ($this->validator->validate($countryCode, $value)) {
                return true;
            }

            $this->addReplacement($attribute, $parameter, $countryCode);
        }

        return false;
    }

    /**
     * Verify that a referenced attribute exists.
     *
     * @param array $data
     * @param string $key
     * @return bool
     * @see \Illuminate\Validation\Validator::validateRequired()
     * @codeCoverageIgnore
     */
    protected function verifyExistence(array $data, string $key): bool
    {
        $value = Arr::get($data, $key);

        if (\is_null($value)) {
            return false;
        } elseif (\is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }
}

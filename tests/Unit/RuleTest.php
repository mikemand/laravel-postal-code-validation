<?php

namespace Axlon\PostalCodeValidation\Tests\Unit;

use Axlon\PostalCodeValidation\Rules\PostalCode;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * Test the creation of dependent postal code rules.
     *
     * @return void
     */
    public function testDependentRuleCreation(): void
    {
        $this->assertEquals('postal_code_for:', (string)PostalCode::forInput());
        $this->assertEquals('postal_code_for:foo', (string)PostalCode::forInput('foo'));
        $this->assertEquals('postal_code_for:bar,baz', (string)PostalCode::forInput('bar')->or('baz'));
    }

    /**
     * Test the creation of explicit postal code rules.
     *
     * @return void
     */
    public function testExplicitRuleCreation(): void
    {
        $this->assertEquals('postal_code:', (string)PostalCode::forCountry());
        $this->assertEquals('postal_code:foo', (string)PostalCode::forCountry('foo'));
        $this->assertEquals('postal_code:bar,baz', (string)PostalCode::forCountry('bar')->or('baz'));
    }
}

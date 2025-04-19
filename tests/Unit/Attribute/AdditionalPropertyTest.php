<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperty;

#[AdditionalProperty(name: 'additionalProperties', value: false)]
#[AdditionalProperty(name: 'maxProperties', value: 10)]
#[AdditionalProperty(name: 'examples', value: [['name' => 'Example', 'value' => 123]])]
final class AdditionalPropertyTest extends TestCase
{
    public function testAdditionalPropertyBooleanValue(): void
    {
        $ref = new \ReflectionClass(self::class);
        $attrs = $ref->getAttributes(AdditionalProperty::class);
        $attr = $attrs[0]->newInstance();

        $this->assertSame('additionalProperties', $attr->name);
        $this->assertFalse($attr->value);
    }

    public function testAdditionalPropertyIntegerValue(): void
    {
        $ref = new \ReflectionClass(self::class);
        $attrs = $ref->getAttributes(AdditionalProperty::class);
        $attr = $attrs[1]->newInstance();

        $this->assertSame('maxProperties', $attr->name);
        $this->assertSame(10, $attr->value);
    }

    public function testAdditionalPropertyArrayValue(): void
    {
        $ref = new \ReflectionClass(self::class);
        $attrs = $ref->getAttributes(AdditionalProperty::class);
        $attr = $attrs[2]->newInstance();

        $this->assertSame('examples', $attr->name);
        $this->assertEquals([['name' => 'Example', 'value' => 123]], $attr->value);
    }
}

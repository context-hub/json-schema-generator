<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ComplexUnionTypes;

final class GeneratorComplexUnionTypeTest extends TestCase
{
    public function testGenerateWithComplexUnionTypes(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ComplexUnionTypes::class);
        $jsonSchema = $schema->jsonSerialize();

        // Check class-level attributes are applied
        $this->assertEquals('Complex Union Types', $jsonSchema['title']);
        $this->assertEquals('Class with complex union type combinations', $jsonSchema['description']);

        // Check properties exist
        $this->assertArrayHasKey('properties', $jsonSchema);
        $this->assertArrayHasKey('nullableUnion', $jsonSchema['properties']);
        $this->assertArrayHasKey('complexProperty', $jsonSchema['properties']);
        $this->assertArrayHasKey('defaultValue', $jsonSchema['properties']);
        $this->assertArrayHasKey('arrayOfUnionTypes', $jsonSchema['properties']);
        $this->assertArrayHasKey('arrayOfObjects', $jsonSchema['properties']);

        // Test nullable union (string|int|null)
        $nullableUnion = $jsonSchema['properties']['nullableUnion'];
        $this->assertEquals('Nullable Union', $nullableUnion['title']);
        $this->assertArrayHasKey('oneOf', $nullableUnion);
        $this->assertCount(3, $nullableUnion['oneOf']);

        // Test complex property (string|array|Movie|null)
        $complexProperty = $jsonSchema['properties']['complexProperty'];
        $this->assertEquals('Complex Property', $complexProperty['title']);
        $this->assertArrayHasKey('oneOf', $complexProperty);
        $this->assertCount(4, $complexProperty['oneOf']);

        // Verify that array, string, and object ref are in the oneOf
        $hasString = false;
        $hasArray = false;
        $hasObjectRef = false;
        $hasNull = false;

        foreach ($complexProperty['oneOf'] as $type) {
            if (isset($type['type']) && $type['type'] === 'string') {
                $hasString = true;
            } elseif (isset($type['type']) && $type['type'] === 'array') {
                $hasArray = true;
            } elseif (isset($type['type']) && $type['type'] === 'null') {
                $hasNull = true;
            } elseif (isset($type['$ref']) && $type['$ref'] === '#/definitions/Movie') {
                $hasObjectRef = true;
            }
        }

        $this->assertTrue($hasString, 'String type not found in complexProperty oneOf');
        $this->assertTrue($hasArray, 'Array type not found in complexProperty oneOf');
        $this->assertTrue($hasObjectRef, 'Movie reference not found in complexProperty oneOf');
        $this->assertTrue($hasNull, 'Null type not found in complexProperty oneOf');

        // Test union type with default value
        $defaultValue = $jsonSchema['properties']['defaultValue'];
        $this->assertEquals('Default Value', $defaultValue['title']);
        $this->assertEquals('Union type with default value', $defaultValue['description']);
        $this->assertArrayHasKey('oneOf', $defaultValue);
        $this->assertCount(2, $defaultValue['oneOf']);
        $this->assertEquals(42, $defaultValue['default']);

        // Test array of union types
        $arrayOfUnionTypes = $jsonSchema['properties']['arrayOfUnionTypes'];
        $this->assertEquals('Array of Union Types', $arrayOfUnionTypes['title']);
        $this->assertEquals('array', $arrayOfUnionTypes['type']);
        $this->assertArrayHasKey('items', $arrayOfUnionTypes);
        $this->assertArrayHasKey('oneOf', $arrayOfUnionTypes['items']);
        $this->assertCount(2, $arrayOfUnionTypes['items']['oneOf']);

        // Test array of objects
        $arrayOfObjects = $jsonSchema['properties']['arrayOfObjects'];
        $this->assertEquals('Array of Objects', $arrayOfObjects['title']);
        $this->assertEquals('array', $arrayOfObjects['type']);
        $this->assertArrayHasKey('items', $arrayOfObjects);
        $this->assertArrayHasKey('oneOf', $arrayOfObjects['items']);
        $this->assertCount(2, $arrayOfObjects['items']['oneOf']);

        // Make sure definitions are included
        $this->assertArrayHasKey('definitions', $jsonSchema);
        $this->assertArrayHasKey('Movie', $jsonSchema['definitions']);
        $this->assertArrayHasKey('Actor', $jsonSchema['definitions']);
    }

    public function testRequiredPropertiesWithDefaultValues(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(ComplexUnionTypes::class);
        $jsonSchema = $schema->jsonSerialize();

        // Check if there are any required properties
        // If all properties have default values, the 'required' key might not exist
        // Only verify that array properties without default values aren't required
        if (isset($jsonSchema['required'])) {
            $this->assertNotContains('nullableUnion', $jsonSchema['required']);
            $this->assertNotContains('complexProperty', $jsonSchema['required']);
            $this->assertNotContains('defaultValue', $jsonSchema['required']);
            $this->assertNotContains('arrayOfUnionTypes', $jsonSchema['required']);
            $this->assertNotContains('arrayOfObjects', $jsonSchema['required']);
        } else {
            // If 'required' key doesn't exist, the test passes as no properties are required
            $this->assertTrue(true);
        }
    }
}

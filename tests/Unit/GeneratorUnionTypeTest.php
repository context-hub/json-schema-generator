<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\UnionTypeExample;

final class GeneratorUnionTypeTest extends TestCase
{
    public function testGenerateWithUnionTypes(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(UnionTypeExample::class);
        $jsonSchema = $schema->jsonSerialize();

        // Check title is set properly
        $this->assertEquals('UnionTypeExample', $jsonSchema['title']);

        // Check properties exist
        $this->assertArrayHasKey('properties', $jsonSchema);
        $this->assertArrayHasKey('stringOrInt', $jsonSchema['properties']);
        $this->assertArrayHasKey('multiType', $jsonSchema['properties']);
        $this->assertArrayHasKey('objectUnion', $jsonSchema['properties']);

        // Check primitive union type (string|int)
        $stringOrInt = $jsonSchema['properties']['stringOrInt'];
        $this->assertEquals('String or Integer', $stringOrInt['title']);
        $this->assertEquals('A value that can be either a string or an integer', $stringOrInt['description']);
        $this->assertArrayHasKey('oneOf', $stringOrInt);
        $this->assertCount(2, $stringOrInt['oneOf']);

        // The order of types in oneOf can vary, so check both options
        $stringIntTypes = [
            ['type' => 'string'],
            ['type' => 'integer'],
        ];
        foreach ($stringOrInt['oneOf'] as $type) {
            $this->assertTrue(\in_array($type, $stringIntTypes));
        }

        // Check multiple types union with null (string|int|bool|null)
        $multiType = $jsonSchema['properties']['multiType'];
        $this->assertEquals('Multiple Types', $multiType['title']);
        $this->assertEquals('A value that can be one of multiple types', $multiType['description']);
        $this->assertArrayHasKey('oneOf', $multiType);
        $this->assertCount(4, $multiType['oneOf']);

        // Check object union type (Movie|Actor|null)
        $objectUnion = $jsonSchema['properties']['objectUnion'];
        $this->assertEquals('Object Union', $objectUnion['title']);
        $this->assertEquals('A value that can be one of multiple object types', $objectUnion['description']);
        $this->assertArrayHasKey('oneOf', $objectUnion);
        $this->assertCount(3, $objectUnion['oneOf']);

        // Check that there are references to Movie and Actor in the oneOf
        $hasMovieRef = false;
        $hasActorRef = false;
        $hasNullType = false;

        foreach ($objectUnion['oneOf'] as $option) {
            if (isset($option['$ref']) && $option['$ref'] === '#/definitions/Movie') {
                $hasMovieRef = true;
            }
            if (isset($option['$ref']) && $option['$ref'] === '#/definitions/Actor') {
                $hasActorRef = true;
            }
            if (isset($option['type']) && $option['type'] === 'null') {
                $hasNullType = true;
            }
        }

        $this->assertTrue($hasMovieRef, 'Movie reference not found in objectUnion oneOf');
        $this->assertTrue($hasActorRef, 'Actor reference not found in objectUnion oneOf');
        $this->assertTrue($hasNullType, 'Null type not found in objectUnion oneOf');

        // Check that definitions are included
        $this->assertArrayHasKey('definitions', $jsonSchema);
        $this->assertArrayHasKey('Movie', $jsonSchema['definitions']);
        $this->assertArrayHasKey('Actor', $jsonSchema['definitions']);

        // Check that required properties are set correctly
        $this->assertArrayHasKey('required', $jsonSchema);
        $this->assertContains('stringOrInt', $jsonSchema['required']);
        $this->assertNotContains('multiType', $jsonSchema['required']);
        $this->assertNotContains('objectUnion', $jsonSchema['required']);
    }
}

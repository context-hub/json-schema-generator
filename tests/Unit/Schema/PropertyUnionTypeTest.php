<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\Type as ParserType;
use Spiral\JsonSchemaGenerator\Parser\UnionType;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class PropertyUnionTypeTest extends TestCase
{
    public function testUnionTypePropertySerialization(): void
    {
        $unionTypes = [
            new ParserType('string', true, false),
            new ParserType('int', true, false),
        ];

        $unionType = new UnionType($unionTypes);

        $property = new Property(
            type: $unionType,
            title: 'String or Integer',
            description: 'A value that can be either a string or an integer',
            required: true,
        );

        $serialized = $property->jsonSerialize();

        $this->assertArrayHasKey('title', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('oneOf', $serialized);
        $this->assertCount(2, $serialized['oneOf']);

        // Check that oneOf contains the correct types
        $this->assertEquals(['type' => 'string'], $serialized['oneOf'][0]);
        $this->assertEquals(['type' => 'integer'], $serialized['oneOf'][1]);
    }

    public function testObjectUnionTypePropertySerialization(): void
    {
        $unionTypes = [
            new ParserType(Movie::class, false, false),
            new ParserType(Actor::class, false, false),
        ];

        $unionType = new UnionType($unionTypes);

        $property = new Property(
            type: $unionType,
            title: 'Movie or Actor',
            description: 'A value that can be either a movie or an actor',
            required: true,
        );

        $serialized = $property->jsonSerialize();

        $this->assertArrayHasKey('title', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('oneOf', $serialized);
        $this->assertCount(2, $serialized['oneOf']);

        // Check that oneOf contains the correct references
        $this->assertEquals(['$ref' => '#/definitions/Movie'], $serialized['oneOf'][0]);
        $this->assertEquals(['$ref' => '#/definitions/Actor'], $serialized['oneOf'][1]);
    }

    public function testMixedUnionTypePropertySerialization(): void
    {
        $unionTypes = [
            new ParserType('string', true, false),
            new ParserType(Movie::class, false, false),
        ];

        $unionType = new UnionType($unionTypes);

        $property = new Property(
            type: $unionType,
            title: 'String or Movie',
            description: 'A value that can be either a string or a movie',
            required: true,
        );

        $serialized = $property->jsonSerialize();

        $this->assertArrayHasKey('oneOf', $serialized);
        $this->assertCount(2, $serialized['oneOf']);

        // Check that oneOf contains the correct types and references
        $this->assertEquals(['type' => 'string'], $serialized['oneOf'][0]);
        $this->assertEquals(['$ref' => '#/definitions/Movie'], $serialized['oneOf'][1]);
    }

    public function testGetDependenciesForUnionType(): void
    {
        $unionTypes = [
            new ParserType('string', true, false),
            new ParserType(Movie::class, false, false),
            new ParserType(Actor::class, false, false),
        ];

        $unionType = new UnionType($unionTypes);

        $property = new Property(
            type: $unionType,
            title: 'String or Object',
            required: true,
        );

        $dependencies = $property->getDependencies();

        $this->assertContains(Movie::class, $dependencies);
        $this->assertContains(Actor::class, $dependencies);
        $this->assertCount(2, $dependencies);
    }
}

<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Parser\UnionType;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class UnionTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $unionType = new UnionType([
            new Type('string', true, false),
            new Type('int', true, false),
        ]);

        $this->assertSame(SchemaType::Union, $unionType->getName());
    }

    public function testGetTypes(): void
    {
        $types = [
            new Type('string', true, false),
            new Type('int', true, false),
            new Type(Movie::class, false, false),
        ];

        $unionType = new UnionType($types);

        $this->assertSame($types, $unionType->getTypes());
        $this->assertCount(3, $unionType->getTypes());
    }

    public function testIsBuiltin(): void
    {
        $unionType = new UnionType([
            new Type('string', true, false),
            new Type('int', true, false),
        ]);

        // Union types are not built-in types
        $this->assertFalse($unionType->isBuiltin());
    }

    public function testAllowsNull(): void
    {
        // Union without null type
        $unionType = new UnionType([
            new Type('string', true, false),
            new Type('int', true, false),
        ]);

        $this->assertFalse($unionType->allowsNull());

        // Union with one nullable type
        $unionType = new UnionType([
            new Type('string', true, false),
            new Type('int', true, true),
        ]);

        $this->assertTrue($unionType->allowsNull());

        // Union with null type
        $unionType = new UnionType([
            new Type('string', true, false),
            new Type('null', true, false),
        ]);

        $this->assertFalse($unionType->allowsNull(), 'Having a null Type does not make it nullable');
    }
}

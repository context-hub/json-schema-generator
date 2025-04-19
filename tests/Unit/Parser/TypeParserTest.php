<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Spiral\JsonSchemaGenerator\Parser\Type;
use Spiral\JsonSchemaGenerator\Parser\TypeParser;
use Spiral\JsonSchemaGenerator\Parser\UnionType;
use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

final class TypeParserTest extends TestCase
{
    public function testFromReflectionTypeWithNamedType(): void
    {
        $reflectionType = $this->createNamedType('string');
        $type = TypeParser::fromReflectionType($reflectionType);

        $this->assertInstanceOf(Type::class, $type);
        $this->assertSame(SchemaType::String, $type->getName());
        $this->assertTrue($type->isBuiltin());
        $this->assertFalse($type->allowsNull());
    }

    public function testFromReflectionTypeWithNullableNamedType(): void
    {
        $reflectionType = $this->createNamedType('string', true);
        $type = TypeParser::fromReflectionType($reflectionType);

        $this->assertInstanceOf(Type::class, $type);
        $this->assertSame(SchemaType::String, $type->getName());
        $this->assertTrue($type->isBuiltin());
        $this->assertTrue($type->allowsNull());
    }

    public function testFromReflectionTypeWithUnionType(): void
    {
        $reflectionType = $this->createUnionType(['string', 'int']);
        $type = TypeParser::fromReflectionType($reflectionType);

        $this->assertInstanceOf(UnionType::class, $type);
        $this->assertSame(SchemaType::Union, $type->getName());
        $this->assertFalse($type->isBuiltin());
        $this->assertFalse($type->allowsNull());

        $types = $type->getTypes();
        $this->assertCount(2, $types);
        $this->assertSame(SchemaType::String, $types[0]->getName());
        $this->assertSame(SchemaType::Integer, $types[1]->getName());
    }

    public function testFromReflectionTypeWithUnionTypeIncludingClass(): void
    {
        $reflectionType = $this->createUnionType(['string', \stdClass::class]);
        $type = TypeParser::fromReflectionType($reflectionType);

        $this->assertInstanceOf(UnionType::class, $type);

        $types = $type->getTypes();
        $this->assertCount(2, $types);
        $this->assertSame(SchemaType::String, $types[0]->getName());
        $this->assertSame(\stdClass::class, $types[1]->getName());
        $this->assertTrue($types[0]->isBuiltin());
        $this->assertFalse($types[1]->isBuiltin());
    }

    public function testFromReflectionTypeWithIntersectionType(): void
    {
        $intersectionType = $this->createMock(\ReflectionIntersectionType::class);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Intersection types are not supported in JSON Schema.');

        TypeParser::fromReflectionType($intersectionType);
    }

    public function testFromReflectionTypeWithUnsupportedType(): void
    {
        $unsupportedType = $this->createMock(\ReflectionType::class);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Unsupported reflection type:');

        TypeParser::fromReflectionType($unsupportedType);
    }

    private function createNamedType(string $typeName, bool $allowsNull = false): \ReflectionNamedType
    {
        $namedType = $this->createMock(\ReflectionNamedType::class);
        $namedType->method('getName')->willReturn($typeName);
        $namedType->method('isBuiltin')->willReturn(\in_array($typeName, ['string', 'int', 'bool', 'float', 'array', 'null']));
        $namedType->method('allowsNull')->willReturn($allowsNull);

        return $namedType;
    }

    private function createUnionType(array $typeNames): \ReflectionUnionType
    {
        $types = [];
        foreach ($typeNames as $typeName) {
            $types[] = $this->createNamedType($typeName);
        }

        $unionType = $this->createMock(\ReflectionUnionType::class);
        $unionType->method('getTypes')->willReturn($types);

        return $unionType;
    }
}

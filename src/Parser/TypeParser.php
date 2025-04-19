<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;

/**
 * Parses PHP reflection types into TypeInterface instances.
 *
 * @internal
 */
final class TypeParser
{
    /**
     * Parse a PHP reflection type into a TypeInterface.
     */
    public static function fromReflectionType(\ReflectionType $reflectionType): TypeInterface
    {
        if ($reflectionType instanceof \ReflectionUnionType) {
            return self::parseUnionType($reflectionType);
        }

        if ($reflectionType instanceof \ReflectionNamedType) {
            return self::parseNamedType($reflectionType);
        }

        // PHP 8.1+ intersection types are not supported in JSON Schema
        if ($reflectionType instanceof \ReflectionIntersectionType) {
            throw new InvalidTypeException('Intersection types are not supported in JSON Schema.');
        }

        throw new InvalidTypeException('Unsupported reflection type: ' . $reflectionType::class);
    }

    /**
     * Parse a union type into a UnionType.
     */
    private static function parseUnionType(\ReflectionUnionType $unionType): UnionType
    {
        $types = [];
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof \ReflectionNamedType) {
                $types[] = self::parseNamedType($type);
            } else {
                throw new InvalidTypeException('Nested union or intersection types are not supported.');
            }
        }

        return new UnionType($types);
    }

    /**
     * Parse a named type into a Type.
     */
    private static function parseNamedType(\ReflectionNamedType $namedType): Type
    {
        return new Type(
            name: $namedType->getName(),
            builtin: $namedType->isBuiltin(),
            nullable: $namedType->allowsNull(),
        );
    }
}

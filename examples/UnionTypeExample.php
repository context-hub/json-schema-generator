<?php

declare(strict_types=1);

namespace Examples;

use Spiral\JsonSchemaGenerator\Attribute\Field;

/**
 * Example DTO with union types to demonstrate the oneOf JSON Schema generation.
 */
class UnionTypeExample
{
    public function __construct(
        #[Field(title: 'String or Integer Value', description: 'A value that can be either a string or an integer')]
        public readonly string|int $stringOrInt,

        #[Field(title: 'Multiple Types', description: 'A value that can be one of multiple types')]
        public readonly string|int|bool|null $multiType = null,

        #[Field(title: 'Object Union', description: 'A value that can be one of multiple object types')]
        public readonly SimpleObject|ComplexObject|null $objectUnion = null,
    ) {}
}

/**
 * Simple object for union type example.
 */
class SimpleObject
{
    public function __construct(
        public readonly string $name,
    ) {}
}

/**
 * Complex object for union type example.
 */
class ComplexObject
{
    public function __construct(
        public readonly string $title,
        public readonly int $count,
    ) {}
}

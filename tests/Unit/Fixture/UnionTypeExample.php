<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

/**
 * Example class with union types for testing.
 */
class UnionTypeExample
{
    public function __construct(
        #[Field(title: 'String or Integer', description: 'A value that can be either a string or an integer')]
        public readonly string|int $stringOrInt,
        #[Field(title: 'Multiple Types', description: 'A value that can be one of multiple types')]
        public readonly string|int|bool|null $multiType = null,
        #[Field(title: 'Object Union', description: 'A value that can be one of multiple object types')]
        public readonly Movie|Actor|null $objectUnion = null,
    ) {}
}

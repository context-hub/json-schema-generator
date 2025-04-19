<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\Definition;

/**
 * Example of complex nested union types.
 */
#[Definition(title: 'Complex Union Types', description: 'Class with complex union type combinations')]
class ComplexUnionTypes
{
    /**
     * @var array<string|int>
     */
    #[Field(title: 'Array of Union Types', description: 'An array that can contain strings or integers')]
    public array $arrayOfUnionTypes = [];

    /**
     * @var array<Movie|Actor>
     */
    #[Field(title: 'Array of Objects', description: 'An array that can contain different object types')]
    public array $arrayOfObjects = [];

    public function __construct(
        #[Field(title: 'Nullable Union', description: 'A nullable union of primitive types')]
        public readonly string|int|null $nullableUnion = null,
        #[Field(title: 'Complex Property', description: 'Union of primitive, array, and object types')]
        public readonly string|array|Movie|null $complexProperty = null,
        #[Field(title: 'Default Value', description: 'Union type with default value')]
        public readonly string|int $defaultValue = 42,
    ) {}
}

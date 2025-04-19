<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

/**
 * Represents a PHP union type (e.g., string|int|null).
 *
 * @internal
 */
final readonly class UnionType implements TypeInterface
{
    /**
     * @param array<TypeInterface> $types
     */
    public function __construct(private array $types) {}

    /**
     * Always returns SchemaType::Union for union types.
     */
    public function getName(): string|SchemaType
    {
        return SchemaType::Union;
    }

    /**
     * @return array<TypeInterface>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Union types are not built-in types.
     */
    public function isBuiltin(): bool
    {
        return false;
    }

    /**
     * Checks if any of the types in the union allows null.
     */
    public function allowsNull(): bool
    {
        foreach ($this->types as $type) {
            if ($type->allowsNull()) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

interface ClassParserInterface
{
    /**
     * @return class-string
     */
    public function getName(): string;

    /**
     * @return non-empty-string
     */
    public function getShortName(): string;

    /**
     * @return array<PropertyInterface>
     */
    public function getProperties(): array;

    public function isEnum(): bool;

    public function getEnumValues(): array;

    /**
     * Find a class-level attribute.
     *
     * @template T
     *
     * @param class-string<T> $name The class name of the attribute.
     *
     * @return T|null The attribute or {@see null}, if the requested attribute does not exist.
     */
    public function findAttribute(string $name): ?object;
}

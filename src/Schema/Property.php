<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;
use Spiral\JsonSchemaGenerator\Parser\UnionType;

final readonly class Property implements \JsonSerializable
{
    public PropertyOptions $options;

    /**
     * @param Type|class-string|UnionType $type
     * @param array<class-string|Type> $options
     */
    public function __construct(
        public Type|string|UnionType $type,
        array $options = [],
        public string $title = '',
        public string $description = '',
        public bool $required = false,
        public mixed $default = null,
    ) {
        if (\is_string($this->type) && !\class_exists($this->type)) {
            throw new InvalidTypeException('Invalid type definition.');
        }

        $this->options = new PropertyOptions($options);
    }

    public function jsonSerialize(): array
    {
        $property = [];
        if ($this->title !== '') {
            $property['title'] = $this->title;
        }

        if ($this->description !== '') {
            $property['description'] = $this->description;
        }

        if ($this->default !== null) {
            $property['default'] = $this->default;
        }

        // Handle UnionType instance
        if ($this->type instanceof UnionType) {
            $unionOptions = [];
            foreach ($this->type->getTypes() as $unionType) {
                $typeName = $unionType->getName();
                if (\is_string($typeName) && !$unionType->isBuiltin()) {
                    // Class reference
                    $unionOptions[] = ['$ref' => (new Reference($typeName))->jsonSerialize()];
                } else {
                    // Primitive type
                    $unionOptions[] = ['type' => $typeName instanceof Type ? $typeName->value : $typeName];
                }
            }
            $property['oneOf'] = $unionOptions;
            return $property;
        }

        if ($this->type === Type::Union) {
            $property['oneOf'] = $this->options->jsonSerialize();
            return $property;
        }

        if (\is_string($this->type)) {
            // this is nested class
            $property['allOf'][] = ['$ref' => (new Reference($this->type))->jsonSerialize()];
            return $property;
        }

        $property['type'] = $this->type->value;

        if ($this->type === Type::Array) {
            if (\count($this->options) === 1) {
                if (\is_string($this->options[0]->value)) {
                    // reference to class
                    $property['items']['$ref'] = (new Reference($this->options[0]->value))->jsonSerialize();
                    return $property;
                }

                $property['items']['type'] = $this->options[0]->value->value;
            } else {
                $property['items']['oneOf'] = $this->options->jsonSerialize();
            }
        }

        return $property;
    }

    public function getDependencies(): array
    {
        $dependencies = [];

        // Extract dependencies from union types
        if ($this->type instanceof UnionType) {
            foreach ($this->type->getTypes() as $unionType) {
                $typeName = $unionType->getName();
                if (!$unionType->isBuiltin() && \is_string($typeName)) {
                    $dependencies[] = $typeName;
                }
            }
        }

        foreach ($this->options->getOptions() as $option) {
            if (\is_string($option->value)) {
                $dependencies[] = $option->value;
            }
        }

        if (\is_string($this->type)) {
            $dependencies[] = $this->type;
        }

        return $dependencies;
    }
}

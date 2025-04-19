<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Schema\Definition;

final class Schema extends AbstractDefinition
{
    private array $definitions = [];
    private string $title = '';
    private string $description = '';
    private ?string $id = null;
    private ?string $schemaVersion = null;
    private array $additionalProperties = [];

    public function addDefinition(string $name, Definition $definition): self
    {
        $this->definitions[$name] = $definition;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setSchemaVersion(?string $schemaVersion): self
    {
        $this->schemaVersion = $schemaVersion;
        return $this;
    }

    public function addAdditionalProperty(string $name, mixed $value): self
    {
        $this->additionalProperties[$name] = $value;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $schema = $this->renderProperties([]);

        if ($this->title !== '') {
            $schema['title'] = $this->title;
        }

        if ($this->description !== '') {
            $schema['description'] = $this->description;
        }

        if ($this->id !== null) {
            $schema['$id'] = $this->id;
        }

        if ($this->schemaVersion !== null) {
            $schema['$schema'] = $this->schemaVersion;
        }

        // Add any additional properties that were specified
        foreach ($this->additionalProperties as $key => $value) {
            $schema[$key] = $value;
        }

        if ($this->definitions !== []) {
            $schema['definitions'] = [];

            foreach ($this->definitions as $name => $definition) {
                $schema['definitions'][$name] = $definition->jsonSerialize();
            }
        }

        return $schema;
    }
}

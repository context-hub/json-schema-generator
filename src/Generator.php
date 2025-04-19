<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperty;
use Spiral\JsonSchemaGenerator\Attribute\Definition as ClassDefinition;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Parser\ClassParserInterface;
use Spiral\JsonSchemaGenerator\Parser\Parser;
use Spiral\JsonSchemaGenerator\Parser\ParserInterface;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Parser\TypeInterface;
use Spiral\JsonSchemaGenerator\Parser\UnionType;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;

class Generator implements GeneratorInterface
{
    protected array $cache = [];

    public function __construct(
        protected readonly ParserInterface $parser = new Parser(),
    ) {}

    /**
     * @param class-string|\ReflectionClass $class
     */
    public function generate(string|\ReflectionClass $class): Schema
    {
        $class = $this->parser->parse($class);

        // check cached
        if (isset($this->cache[$class->getName()])) {
            return $this->cache[$class->getName()];
        }

        $schema = new Schema();

        // Process class-level Definition attribute if present
        $classDefinition = $class->findAttribute(ClassDefinition::class);
        if ($classDefinition !== null) {
            if (!empty($classDefinition->title)) {
                $schema->setTitle($classDefinition->title);
            } else {
                // Use class short name as default title
                $schema->setTitle($class->getShortName());
            }

            if (!empty($classDefinition->description)) {
                $schema->setDescription($classDefinition->description);
            }

            if ($classDefinition->id !== null) {
                $schema->setId($classDefinition->id);
            }

            if ($classDefinition->schemaVersion !== null) {
                $schema->setSchemaVersion($classDefinition->schemaVersion);
            }
        } else {
            // Set title to class name by default if no definition attribute
            $schema->setTitle($class->getShortName());
        }

        // Process additional properties attributes if present
        $this->processAdditionalProperties($class, $schema);

        $dependencies = [];
        // Generating properties
        foreach ($class->getProperties() as $property) {
            $psc = $this->generateProperty($property);
            if ($psc === null) {
                continue;
            }

            // does it refer to any other classes
            $dependencies = [...$dependencies, ...$psc->getDependencies()];

            $schema->addProperty($property->getName(), $psc);
        }

        // Generating dependencies
        $dependencies = \array_unique($dependencies);
        $rollingDependencies = [];
        $doneDependencies = [];

        do {
            foreach ($dependencies as $dependency) {
                $dependency = $this->parser->parse($dependency);
                $definition = $this->generateDefinition($dependency, $rollingDependencies);
                if ($definition === null) {
                    continue;
                }

                $schema->addDefinition($dependency->getShortName(), $definition);
            }

            $doneDependencies = [...$doneDependencies, ...$dependencies];
            $rollingDependencies = \array_diff($rollingDependencies, $doneDependencies);
            if ($rollingDependencies === []) {
                break;
            }

            $dependencies = $rollingDependencies;
        } while (true);

        // caching
        $this->cache[$class->getName()] = $schema;

        return $schema;
    }

    /**
     * Process AdditionalProperty attributes on a class
     */
    protected function processAdditionalProperties(ClassParserInterface $class, Schema $schema): void
    {
        // Get reflection class to extract attributes with \ReflectionClass::getAttributes()
        try {
            $reflectionClass = new \ReflectionClass($class->getName());
            $additionalProperties = $reflectionClass->getAttributes(AdditionalProperty::class);

            foreach ($additionalProperties as $additionalProperty) {
                $instance = $additionalProperty->newInstance();
                $schema->addAdditionalProperty($instance->name, $instance->value);
            }
        } catch (\ReflectionException) {
            // Silently fail, we'll just not have additional properties
        }
    }

    protected function generateDefinition(ClassParserInterface $class, array &$dependencies = []): ?Definition
    {
        $properties = [];

        // Process class-level Definition attribute if present
        $title = $class->getShortName();
        $description = '';

        $classDefinition = $class->findAttribute(ClassDefinition::class);
        if ($classDefinition !== null) {
            if (!empty($classDefinition->title)) {
                $title = $classDefinition->title;
            }

            if (!empty($classDefinition->description)) {
                $description = $classDefinition->description;
            }
        }

        if ($class->isEnum()) {
            return new Definition(
                type: $class->getName(),
                options: $class->getEnumValues(),
                title: $title,
                description: $description,
            );
        }

        // class properties
        foreach ($class->getProperties() as $property) {
            $psc = $this->generateProperty($property);
            if ($psc === null) {
                continue;
            }

            $dependencies = [...$dependencies, ...$psc->getDependencies()];
            $properties[$property->getName()] = $psc;
        }

        return new Definition(
            type: $class->getName(),
            title: $title,
            description: $description,
            properties: $properties,
        );
    }

    protected function generateProperty(PropertyInterface $property): ?Property
    {
        // Looking for Field attribute
        $title = '';
        $description = '';
        $default = null;

        $attribute = $property->findAttribute(Field::class);
        if ($attribute !== null) {
            $title = $attribute->title;
            $description = $attribute->description;
            $default = $attribute->default;
        }

        if ($default === null && $property->hasDefaultValue()) {
            $default = $property->getDefaultValue();
        }

        $type = $property->getType();

        // Handle union types (e.g., string|int|bool)
        if ($type instanceof UnionType) {
            $required = $default === null && !$type->allowsNull();
            return new Property($type, [], $title, $description, $required, $default);
        }

        $options = [];
        if ($property->isCollection()) {
            $options = \array_map(
                static fn(TypeInterface $type) => $type->getName(),
                $property->getCollectionValueTypes(),
            );
        }

        $required = $default === null && !$type->allowsNull();
        if ($type->isBuiltin()) {
            return new Property($type->getName(), $options, $title, $description, $required, $default);
        }

        // Class or enum
        $class = $type->getName();

        return \is_string($class) && \class_exists($class)
            ? new Property($class, [], $title, $description, $required, $default)
            : null;
    }
}

<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Schema;

final class SchemaExtendedTest extends TestCase
{
    public function testSchemaWithMetadata(): void
    {
        $schema = new Schema();
        $schema->setTitle('Test Schema');
        $schema->setDescription('A test schema for unit testing');
        $schema->setId('https://example.com/schemas/test.json');
        $schema->setSchemaVersion('http://json-schema.org/draft-07/schema#');

        $this->assertEquals([
            'title' => 'Test Schema',
            'description' => 'A test schema for unit testing',
            '$id' => 'https://example.com/schemas/test.json',
            '$schema' => 'http://json-schema.org/draft-07/schema#',
        ], $schema->jsonSerialize());
    }

    public function testSchemaWithAdditionalProperties(): void
    {
        $schema = new Schema();
        $schema->setTitle('Test Schema');
        $schema->addAdditionalProperty('additionalProperties', false);
        $schema->addAdditionalProperty('maxProperties', 10);
        $schema->addAdditionalProperty('examples', [
            ['name' => 'Example', 'value' => 123],
        ]);

        $this->assertEquals([
            'title' => 'Test Schema',
            'additionalProperties' => false,
            'maxProperties' => 10,
            'examples' => [
                ['name' => 'Example', 'value' => 123],
            ],
        ], $schema->jsonSerialize());
    }

    public function testSchemaWithMetadataAndProperties(): void
    {
        $schema = new Schema();
        $schema->setTitle('Test Schema');
        $schema->setDescription('A test schema for unit testing');
        $schema->addAdditionalProperty('additionalProperties', false);

        $schema->addProperty(
            'name',
            new Schema\Property(
                type: Schema\Type::String,
                title: 'Name',
                description: 'Name of the entity',
                required: true,
            ),
        );

        $this->assertEquals([
            'title' => 'Test Schema',
            'description' => 'A test schema for unit testing',
            'additionalProperties' => false,
            'properties' => [
                'name' => [
                    'title' => 'Name',
                    'description' => 'Name of the entity',
                    'type' => 'string',
                ],
            ],
            'required' => [
                'name',
            ],
        ], $schema->jsonSerialize());
    }
}

<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Product;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ProductStatus;

final class GeneratorExtendedTest extends TestCase
{
    public function testGenerateWithClassDefinitionAndAdditionalProperties(): void
    {
        $generator = new Generator();
        $schema = $generator->generate(Product::class);

        $expectedSchema = [
            'title' => 'Product Schema',
            'description' => 'A product in the catalog',
            '$id' => 'https://example.com/schemas/product.json',
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'additionalProperties' => false,
            'examples' => [
                [
                    'id' => 123,
                    'name' => 'Sample Product',
                    'price' => 99.99,
                    'inStock' => true,
                ],
            ],
            'properties' => [
                'id' => [
                    'title' => 'Product ID',
                    'description' => 'Unique identifier for the product',
                    'type' => 'integer',
                ],
                'name' => [
                    'title' => 'Product Name',
                    'description' => 'Name of the product',
                    'type' => 'string',
                ],
                'price' => [
                    'title' => 'Product Price',
                    'description' => 'Current price of the product',
                    'type' => 'number',
                ],
                'inStock' => [
                    'title' => 'In Stock',
                    'description' => 'Whether the product is in stock',
                    'type' => 'boolean',
                    'default' => true,
                ],
                'status' => [
                    'title' => 'Product Status',
                    'description' => 'Current status of the product',
                    'allOf' => [
                        [
                            '$ref' => '#/definitions/ProductStatus',
                        ],
                    ],
                ],
            ],
            'required' => [
                'id',
                'name',
                'price',
            ],
            'definitions' => [
                'ProductStatus' => [
                    'title' => 'Product Status',
                    'description' => 'The status of a product in the catalog',
                    'type' => 'string',
                    'enum' => [
                        'Released',
                        'Rumored',
                        'Post Production',
                        'In Production',
                        'Planned',
                        'Canceled',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedSchema, $schema->jsonSerialize());
    }

    public function testGenerateEnumDefinition(): void
    {
        // Test Definition attributes on nested enum definitions
        $generator = new Generator();
        $schema = $generator->generate(Product::class);

        // Extract the ProductStatus definition from the generated schema
        $definitions = $schema->jsonSerialize()['definitions'];
        $this->assertArrayHasKey('ProductStatus', $definitions);

        $productStatusDefinition = $definitions['ProductStatus'];

        // Verify Definition attribute values are applied
        $this->assertEquals('Product Status', $productStatusDefinition['title']);
        $this->assertEquals('The status of a product in the catalog', $productStatusDefinition['description']);

        // Verify the enum values are correct
        $this->assertEquals('string', $productStatusDefinition['type']);
        $this->assertEquals([
            'Released',
            'Rumored',
            'Post Production',
            'In Production',
            'Planned',
            'Canceled',
        ], $productStatusDefinition['enum']);

        // Note: AdditionalProperty attributes on nested definitions aren't currently
        // processed by the Generator class, so we're not testing for them here.
        // Adding support for this would require further modifications to the Generator class.
    }

    public function testGenerateWithNoDefinitionAttributes(): void
    {
        // Test that the generator still works properly with classes that don't have Definition attributes
        $generator = new Generator();
        $schema = $generator->generate(\stdClass::class);

        $this->assertArrayHasKey('title', $schema->jsonSerialize());
        $this->assertEquals('stdClass', $schema->jsonSerialize()['title']);
    }
}

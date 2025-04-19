<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperty;
use Spiral\JsonSchemaGenerator\Attribute\Definition;
use Spiral\JsonSchemaGenerator\Attribute\Field;

#[Definition(
    id: 'https://example.com/schemas/product.json',
    title: 'Product Schema',
    description: 'A product in the catalog',
    schemaVersion: 'http://json-schema.org/draft-07/schema#',
)]
#[AdditionalProperty(name: 'additionalProperties', value: false)]
#[AdditionalProperty(name: 'examples', value: [
    [
        'id' => 123,
        'name' => 'Sample Product',
        'price' => 99.99,
        'inStock' => true,
    ],
])]
final readonly class Product
{
    public function __construct(
        #[Field(title: 'Product ID', description: 'Unique identifier for the product')]
        public int $id,
        #[Field(title: 'Product Name', description: 'Name of the product')]
        public string $name,
        #[Field(title: 'Product Price', description: 'Current price of the product')]
        public float $price,
        #[Field(title: 'In Stock', description: 'Whether the product is in stock')]
        public bool $inStock = true,
        #[Field(title: 'Product Status', description: 'Current status of the product')]
        public ?ProductStatus $status = null,
    ) {}
}

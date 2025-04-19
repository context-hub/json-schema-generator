<?php

declare(strict_types=1);

namespace Examples;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperty;
use Spiral\JsonSchemaGenerator\Attribute\Definition;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Generator;

#[Definition(
    title: 'Product Schema',
    description: 'A schema representing a product in an e-commerce system',
    id: 'https://example.com/schemas/product.json',
    schemaVersion: 'http://json-schema.org/draft-07/schema#',
)]
#[AdditionalProperty(name: 'additionalProperties', value: false)]
#[AdditionalProperty(name: 'examples', value: [
    [
        'id' => 123,
        'name' => 'Sample Product',
        'price' => 99.99,
        'tags' => ['new', 'featured'],
        'status' => 'Active',
    ],
])]
#[AdditionalProperty(name: 'maxProperties', value: 5)]
class Product
{
    public function __construct(
        #[Field(title: 'Product ID', description: 'Unique identifier for the product')]
        public readonly int $id,
        #[Field(title: 'Product Name', description: 'Name of the product')]
        public readonly string $name,
        #[Field(title: 'Product Price', description: 'Current price of the product')]
        public readonly float $price,

        /**
         * @var array<string>
         */
        #[Field(title: 'Product Tags', description: 'List of tags associated with the product')]
        public readonly array $tags = [],
        #[Field(title: 'Product Status', description: 'Current status of the product')]
        public readonly ?ProductStatus $status = null,
    ) {}
}

#[Definition(title: 'Product Status')]
#[AdditionalProperty(name: 'deprecated', value: ['Discontinued'])]
enum ProductStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Discontinued = 'Discontinued';
    case OutOfStock = 'Out of Stock';
}

// Generate the schema
$generator = new Generator();
$schema = $generator->generate(Product::class);

// Output the schema as JSON
\header('Content-Type: application/json');
echo \json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

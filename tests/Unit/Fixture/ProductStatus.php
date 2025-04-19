<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Definition;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperty;

#[Definition(
    id: 'https://example.com/schemas/product-status.json',
    title: 'Product Status',
    description: 'The status of a product in the catalog',
)]
#[AdditionalProperty(name: 'deprecated', value: ['Discontinued'])]
#[AdditionalProperty(name: 'x-enum-varnames', value: [
    'RELEASED', 'RUMORED', 'POST_PRODUCTION', 'IN_PRODUCTION', 'PLANNED', 'CANCELED',
])]
enum ProductStatus: string
{
    case Released = 'Released';
    case Rumored = 'Rumored';
    case PostProduction = 'Post Production';
    case InProduction = 'In Production';
    case Planned = 'Planned';
    case Canceled = 'Canceled';
}

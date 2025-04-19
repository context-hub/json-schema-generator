<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AdditionalProperty
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $value,
    ) {}
}

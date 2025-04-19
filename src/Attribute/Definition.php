<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Definition
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly string $description = '',
        public readonly ?string $id = null,
        public readonly ?string $schemaVersion = null,
    ) {}
}

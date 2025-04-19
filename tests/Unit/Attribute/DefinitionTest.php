<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Attribute\Definition;

#[Definition(
    title: 'Test Title',
    description: 'Test Description',
    id: 'https://example.com/schema.json',
    schemaVersion: 'http://json-schema.org/draft-07/schema#',
)]
final class DefinitionTest extends TestCase
{
    public function testDefinitionWithValues(): void
    {
        $ref = new \ReflectionClass(self::class);
        $attrs = $ref->getAttributes(Definition::class);
        $attr = $attrs[0]->newInstance();

        $this->assertSame('Test Title', $attr->title);
        $this->assertSame('Test Description', $attr->description);
        $this->assertSame('https://example.com/schema.json', $attr->id);
        $this->assertSame('http://json-schema.org/draft-07/schema#', $attr->schemaVersion);
    }
}

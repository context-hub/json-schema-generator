<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class Actor
{
    public function __construct(
        public string $name,
        public int $age,
        #[Field(title: 'Biography', description: 'The biography of the actor')]
        public ?string $bio = null,
        /**
         * @var list<Movie>
         */
        public array $movies = [],
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public ?Movie $bestMovie = null,
    ) {}
}

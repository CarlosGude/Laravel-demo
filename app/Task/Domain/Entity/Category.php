<?php

namespace App\Task\Domain\Entity;

final readonly class Category
{
    private function __construct(
        private ?int $id,
        private string $name,
    ) {

    }

    public static function create(?int $id, string $name): self
    {
        return new self(id: $id, name: $name);
    }

    public static function createNew(string $name): self
    {
        return new self(id: null, name: $name);
    }

    public function createWithId(int $id): self
    {
        return new self(id: $id, name: $this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersistedId(): int
    {
        return $this->id ?? throw new \LogicException('Category has not been persisted yet.');
    }

    public function getName(): string
    {
        return $this->name;
    }
}

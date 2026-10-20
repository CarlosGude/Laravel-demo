<?php

namespace App\Task\Domain\Entity;

final readonly class Category
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private string $name,
    ) {

    }

    public static function create(?int $id, int $userId, string $name): self
    {
        return new self(id: $id, userId: $userId, name: $name);
    }

    public static function createNew(int $userId, string $name): self
    {
        return new self(id: null, userId: $userId, name: $name);
    }

    public function createWithId(int $id): self
    {
        return new self(id: $id, userId: $this->userId, name: $this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersistedId(): int
    {
        return $this->id ?? throw new \LogicException('Category has not been persisted yet.');
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

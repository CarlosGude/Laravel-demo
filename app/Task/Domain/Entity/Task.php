<?php

namespace App\Task\Domain\Entity;

final readonly class Task
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private bool $completed,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    )
    {

    }

    public static function create(
        ?int $id,
        string $name,
        string $description,
        bool $completed,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self
    {
        return new self(
            id: $id,
            name: $name,
            description: $description,
            completed: $completed,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public static function createNew(string $name, string $description): self
    {
        return new self(
            id: null,
            name: $name,
            description: $description,
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function createWithId(int $id): self
    {
        return new self(
            id: $id,
            name: $this->getName(),
            description: $this->getDescription(),
            completed: $this->isCompleted(),
            createdAt: $this->getCreatedAt(),
            updatedAt: $this->getUpdatedAt()
        );
    }

    public function withChanges(
        string $name,
        string $description,
        bool $completed,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $this->id,
            name: $name,
            description: $description,
            completed: $completed,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'completed' => $this->isCompleted(),
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

}

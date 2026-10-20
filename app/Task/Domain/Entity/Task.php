<?php

namespace App\Task\Domain\Entity;

final readonly class Task
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private string $name,
        private string $description,
        private bool $completed,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private CategoryCollection $categories,
    ) {

    }

    // Every field is explicit on purpose: this is the full-reconstitution factory
    // (see docs/ARCHITECTURE.md #3), so splitting it into a parameter object would
    // just move the same fields one level down without reducing real complexity.
    public static function create(// NOSONAR
        ?int $id,
        int $userId,
        string $name,
        string $description,
        bool $completed,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?CategoryCollection $categories = null,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            name: $name,
            description: $description,
            completed: $completed,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            categories: $categories ?? new CategoryCollection(),
        );
    }

    public static function createNew(
        int $userId,
        string $name,
        string $description,
        ?CategoryCollection $categories = null,
    ): self {
        return new self(
            id: null,
            userId: $userId,
            name: $name,
            description: $description,
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
            categories: $categories ?? new CategoryCollection(),
        );
    }

    public function createWithId(int $id): self
    {
        return new self(
            id: $id,
            userId: $this->userId,
            name: $this->getName(),
            description: $this->getDescription(),
            completed: $this->isCompleted(),
            createdAt: $this->getCreatedAt(),
            updatedAt: $this->getUpdatedAt(),
            categories: $this->categories,
        );
    }

    public function withChanges(
        string $name,
        string $description,
        bool $completed,
        \DateTimeImmutable $updatedAt,
        ?CategoryCollection $categories = null,
    ): self {
        return new self(
            id: $this->id,
            userId: $this->userId,
            name: $name,
            description: $description,
            completed: $completed,
            createdAt: $this->createdAt,
            updatedAt: $updatedAt,
            categories: $categories ?? $this->categories,
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersistedId(): int
    {
        return $this->id ?? throw new \LogicException('Task has not been persisted yet.');
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->userId === $userId;
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

    public function getCategories(): CategoryCollection
    {
        return $this->categories;
    }

    /**
     * @return array{id: ?int, userId: int, name: string, description: string, completed: bool, createdAt: string, updatedAt: string, categories: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'userId' => $this->userId,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'completed' => $this->isCompleted(),
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
            'categories' => $this->categories->names(),
        ];
    }

}

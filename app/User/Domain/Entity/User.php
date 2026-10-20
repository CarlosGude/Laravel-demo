<?php

namespace App\User\Domain\Entity;

final readonly class User
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $email,
        private string $passwordHash,
        private UserRole $role,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        ?int $id,
        string $name,
        string $email,
        string $passwordHash,
        UserRole $role,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            passwordHash: $passwordHash,
            role: $role,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public static function createNew(string $name, string $email, string $passwordHash): self
    {
        return new self(
            id: null,
            name: $name,
            email: $email,
            passwordHash: $passwordHash,
            role: UserRole::Regular,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function createWithId(int $id): self
    {
        return new self(
            id: $id,
            name: $this->name,
            email: $this->email,
            passwordHash: $this->passwordHash,
            role: $this->role,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    public function promoteToAdmin(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            passwordHash: $this->passwordHash,
            role: UserRole::Admin,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersistedId(): int
    {
        return $this->id ?? throw new \LogicException('User has not been persisted yet.');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

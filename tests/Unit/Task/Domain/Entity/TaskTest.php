<?php

namespace Tests\Unit\Task\Domain\Entity;

use App\Task\Domain\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function test_create_new_builds_a_task_without_id_and_not_completed(): void
    {
        $task = Task::createNew('Buy milk', 'Whole milk, 2 liters');

        $this->assertNull($task->getId());
        $this->assertSame('Buy milk', $task->getName());
        $this->assertSame('Whole milk, 2 liters', $task->getDescription());
        $this->assertFalse($task->isCompleted());
        $this->assertSame(
            $task->getCreatedAt()->format(DATE_ATOM),
            $task->getUpdatedAt()->format(DATE_ATOM)
        );
    }

    public function test_create_with_id_keeps_the_rest_of_the_data_unchanged(): void
    {
        $task = Task::createNew('Buy milk', 'Whole milk, 2 liters');

        $taskWithId = $task->createWithId(5);

        $this->assertSame(5, $taskWithId->getId());
        $this->assertSame($task->getName(), $taskWithId->getName());
        $this->assertSame($task->getDescription(), $taskWithId->getDescription());
        $this->assertSame($task->isCompleted(), $taskWithId->isCompleted());
        $this->assertSame($task->getCreatedAt(), $taskWithId->getCreatedAt());
        $this->assertSame($task->getUpdatedAt(), $taskWithId->getUpdatedAt());
    }

    public function test_with_changes_updates_fields_but_preserves_id_and_created_at(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $task = Task::create(
            id: 10,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updatedAt = new \DateTimeImmutable('2026-01-02T10:00:00+00:00');
        $changedTask = $task->withChanges(
            name: 'Buy milk and bread',
            description: 'Whole milk and sourdough bread',
            completed: true,
            updatedAt: $updatedAt,
        );

        $this->assertSame(10, $changedTask->getId());
        $this->assertSame('Buy milk and bread', $changedTask->getName());
        $this->assertSame('Whole milk and sourdough bread', $changedTask->getDescription());
        $this->assertTrue($changedTask->isCompleted());
        $this->assertSame($createdAt, $changedTask->getCreatedAt());
        $this->assertSame($updatedAt, $changedTask->getUpdatedAt());
    }

    public function test_to_array_returns_the_expected_shape(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $updatedAt = new \DateTimeImmutable('2026-01-02T10:00:00+00:00');
        $task = Task::create(
            id: 10,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: true,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame([
            'id' => 10,
            'name' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => true,
            'createdAt' => $createdAt->format(DATE_ATOM),
            'updatedAt' => $updatedAt->format(DATE_ATOM),
        ], $task->toArray());
    }
}

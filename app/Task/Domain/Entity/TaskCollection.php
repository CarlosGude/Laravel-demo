<?php

namespace App\Task\Domain\Entity;

/**
 * @implements \IteratorAggregate<int, Task>
 */
final readonly class TaskCollection implements \Countable, \IteratorAggregate
{
    /** @var array<int, Task> */
    private array $tasks;

    public function __construct(Task ...$tasks)
    {
        $this->tasks = array_values($tasks);
    }

    /**
     * @param array<int|string, Task> $tasks
     */
    public static function fromArray(array $tasks): self
    {
        return new self(...array_values($tasks));
    }

    /**
     * @return \ArrayIterator<int, Task>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tasks);
    }

    public function count(): int
    {
        return count($this->tasks);
    }

    /**
     * @return array<int, Task>
     */
    public function toArray(): array
    {
        return $this->tasks;
    }
}
